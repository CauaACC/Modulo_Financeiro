<?php

require_once __DIR__ . '/EditalPublicacao.php';

/**
 * ConsolidacaoFase
 *
 * Orquestra a operacao "Consolidar Fase" — apos o prazo de avaliacao
 * e de analise de recurso da etapa, preenche os campos finais em
 * tab_trabalhos_avaliacao_inscricao para cada inscrito da etapa:
 *
 *   - pontos_total_final          = COALESCE(pontos_total_apos_recurso, pontos_total_confirmado)
 *   - classificacao_status_final  = COALESCE(NULLIF(classificacao_status_apos_recurso,''), classificacao_status)
 *   - classificacao_final         = posicao 1..N para DEFERIDOs (H), NULL para INDEFERIDOs (I)
 *
 * O desempate dos DEFERIDOs usa as mesmas regras do EditalPublicacao:
 *   pontos DESC, nivel_titulacao DESC, meses_experiencia DESC, idade DESC
 *
 * A operacao eh IDEMPOTENTE: pode ser re-executada N vezes (cada execucao
 * recalcula tudo do zero e gera nova linha no log sis_log_consolidacao_etapa).
 *
 * Pre-condicao: nao pode haver recurso pendente de analise na etapa.
 * Se houver, lanca Exception listando os pendentes.
 */
class ConsolidacaoFase extends ConsolidacaoFaseVO
{
    private $objDAO;

    public function __construct()
    {
        $this->setCodEvento(COD_EVENTO);
        $this->objDAO = new ConsolidacaoFaseDAO();
    }

    public function setListaRecursosPendentes(array $lista) { $this->listaRecursosPendentes = $lista; }
    public function getListaRecursosPendentes()             { return isset($this->listaRecursosPendentes) ? $this->listaRecursosPendentes : array(); }
    public function setListaRegistros(array $lista)         { $this->listaRegistros = $lista; }
    public function getListaRegistros()                     { return isset($this->listaRegistros) ? $this->listaRegistros : array(); }
    public function setListaConsolidada(array $lista)       { $this->listaConsolidada = $lista; }
    public function getListaConsolidada()                   { return isset($this->listaConsolidada) ? $this->listaConsolidada : array(); }

    /**
     * Lista os recursos sem decisao do avaliador na etapa atual.
     * (recurso_texto preenchido + classificacao_status_apos_recurso vazio)
     */
    public function carregarListaRecursosPendentes($debug = false)
    {
        $this->objDAO->carregarListaRecursosPendentes($this, $debug);
        return $this->getListaRecursosPendentes();
    }

    /**
     * Carrega a ultima execucao registrada (ou null se nunca consolidada).
     */
    public function carregarUltimaConsolidacao($debug = false)
    {
        return $this->objDAO->carregarUltimaConsolidacao($this, $debug);
    }

    /**
     * Carrega a lista consolidada para exibir na view.
     */
    public function carregarListaConsolidada($debug = false)
    {
        $this->objDAO->carregarListaConsolidada($this, $debug);
        return $this->getListaConsolidada();
    }

    /**
     * Executa a consolidacao da etapa setada em $this.
     *
     * Fluxo:
     *   1) Pre-check: nenhum recurso pode estar pendente. Se houver, Exception.
     *   2) Resolve cod_etapa via ordem_etapa. Sem etapa cadastrada -> Exception.
     *   3) Carrega registros da etapa (apenas linhas com cod_avaliacao real,
     *      ou seja, inscritos que foram efetivamente avaliados).
     *   4) Para cada registro:
     *      - Calcula pontos_total_final = COALESCE(apos_recurso, confirmado)
     *      - Calcula classificacao_status_final = COALESCE(apos_recurso, status)
     *      - Determina nivel_titulacao + meses_experiencia (DEFERIDO)
     *   5) Ordena DEFERIDOs por desempate, atribui classificacao_final 1..N
     *   6) UPDATE em cada registro
     *   7) INSERT em sis_log_consolidacao_etapa (auditoria)
     *
     * Retorna array com totais: ['total_inscritos','total_deferidos','total_indeferidos'].
     *
     * @throws Exception em qualquer falha de pre-condicao
     */
    public function consolidar($debug = false)
    {
        $codEvento  = (int) $this->getCodEvento();
        $ordemEtapa = (int) $this->getOrdemEtapa();
        if ($ordemEtapa <= 0) {
            throw new Exception('Ordem da etapa nao informada.');
        }

        // 1) Resolve cod_etapa
        $codEtapa = $this->objDAO->getCodEtapaPorOrdem($codEvento, $ordemEtapa, $debug);
        if (empty($codEtapa)) {
            throw new Exception(sprintf(
                'Etapa de ordem %d nao esta cadastrada (ou nao esta ATIVADA) para o evento %d.',
                $ordemEtapa, $codEvento
            ));
        }
        $this->setCodEtapa($codEtapa);

        // 2) Recursos pendentes bloqueiam a consolidacao
        $pendentes = $this->carregarListaRecursosPendentes($debug);
        if (!empty($pendentes)) {
            $nomes = array();
            foreach ($pendentes as $p) {
                $nomes[] = '#' . $p->getIdPublico() . ' ' . $p->getNomeInscrito();
            }
            $msg = sprintf(
                "Existem %d recurso(s) ainda sem decisao do avaliador. Conclua a analise dos recursos abaixo antes de consolidar:\n\n%s",
                count($pendentes), implode("\n", $nomes)
            );
            throw new Exception($msg);
        }

        // 3) Carrega registros
        $this->objDAO->carregarRegistrosParaConsolidar($this, $debug);
        $registros = $this->getListaRegistros();

        // 4) Classifica cada registro como DEFERIDO / INDEFERIDO / SEM_STATUS
        $deferidos = array();
        $indeferidos = array();
        foreach ($registros as $r) {
            $statusFinal = trim((string) $r->getStatusEfetivo());
            if ($statusFinal === 'H') {
                $avJson = $this->parseAvaliacaoJson($r->getAvaliacaoJson());
                $r->setNivelTitulacao($this->calcTitulacao($avJson));
                $r->setMesesExperiencia($this->calcExperiencia($avJson));
                $deferidos[] = $r;
            } elseif ($statusFinal === 'I') {
                $indeferidos[] = $r;
            }
            // Status vazio: registro existe mas sem avaliacao concluida —
            // nao entra na classificacao final (fica com campos _final NULL).
        }

        // 5) Ordena deferidos pelo desempate
        usort($deferidos, function ($a, $b) {
            $cmp = ((float)$b->getPontosEfetivos()) <=> ((float)$a->getPontosEfetivos());
            if ($cmp !== 0) return $cmp;
            $cmp = ((int)$b->getNivelTitulacao()) <=> ((int)$a->getNivelTitulacao());
            if ($cmp !== 0) return $cmp;
            $cmp = ((int)$b->getMesesExperiencia()) <=> ((int)$a->getMesesExperiencia());
            if ($cmp !== 0) return $cmp;
            return ((int)$b->getIdade()) <=> ((int)$a->getIdade());
        });

        // 6) Persiste os campos finais + criterios de desempate (auditoria)
        $pos = 0;
        foreach ($deferidos as $r) {
            $pos++;
            $this->objDAO->atualizarConsolidacao(
                $r->getCodAvaliacao(),
                $r->getPontosEfetivos(),
                'H',
                $pos,
                $r->getNivelTitulacao(),
                $r->getMesesExperiencia(),
                $r->getIdade(),
                $debug
            );
        }
        foreach ($indeferidos as $r) {
            // Indeferidos nao entram no desempate — campos *_desempate ficam NULL
            $this->objDAO->atualizarConsolidacao(
                $r->getCodAvaliacao(),
                $r->getPontosEfetivos(),
                'I',
                null,
                null,
                null,
                null,
                $debug
            );
        }

        // 7) Auditoria
        $this->setTotalDeferidos(count($deferidos));
        $this->setTotalIndeferidos(count($indeferidos));
        $this->setTotalInscritos(count($deferidos) + count($indeferidos));
        $this->objDAO->registrarLog($this, $debug);

        return array(
            'total_inscritos'   => $this->getTotalInscritos(),
            'total_deferidos'   => $this->getTotalDeferidos(),
            'total_indeferidos' => $this->getTotalIndeferidos(),
        );
    }

    private function parseAvaliacaoJson($raw)
    {
        if (!$raw) return null;
        $s = str_replace('\\"', '"', (string) $raw);
        $decoded = json_decode($s, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function calcTitulacao($avJson)
    {
        if (!is_array($avJson) || !isset($avJson['itens'])) return 0;
        $itens = $avJson['itens'];
        foreach (EditalPublicacao::TITULACAO_RANK as $chave => $rank) {
            $qtd = isset($itens[$chave]['qtd']) ? (int) $itens[$chave]['qtd'] : 0;
            if ($qtd > 0) return $rank;
        }
        return 0;
    }

    private function calcExperiencia($avJson)
    {
        if (!is_array($avJson) || !isset($avJson['itens'])) return 0;
        $itens = $avJson['itens'];
        $total = 0;
        foreach (EditalPublicacao::EXPERIENCIA_CHAVES as $chave) {
            $qtd = isset($itens[$chave]['qtd']) ? (int) $itens[$chave]['qtd'] : 0;
            $total += $qtd;
        }
        return $total;
    }
}
