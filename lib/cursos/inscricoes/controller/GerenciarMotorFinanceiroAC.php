<?php

/**
 * MotorFinanceiro automatica: transforma os pagamentos do evento (tab_pagamentos)
 * em lancamentos financeiros (fin_lancamentos), reproduzindo a estrutura
 * do balancete da HUTEC:
 *
 *   - 1 credito na conta de receita do evento por pagamento recebido;
 *   - 1 debito na conta de destino da regra de taxa do meio de pagamento,
 *     calculada pelas regras cadastradas em fin_rateios
 *     (PIX 1,89% | cartao 3,99% | boleto 3,99% + R$ 1,00).
 *
 * Nenhuma rubrica e procurada por codigo financeiro: cada evento pode ter o seu
 * proprio plano de contas. A conta de receita sai da conta de origem da regra
 * de Rateios; se o evento so tem uma conta de RECEITA que aceita lancamento, e
 * ela. A conta da taxa sai da conta de destino da regra.
 *
 * CANCELADO/DEVOLVIDO nao gera estorno automatico. Cancelar no sistema nao
 * significa que o dinheiro entrou (as vezes so se cancela o registro) nem que
 * ele voltou (quando volta, o valor devolvido costuma ser outro - ex.: pagou
 * 300, devolveu 225). Por isso esses pagamentos so viram receita quando o
 * retorno HUTEC confirma a entrada (fin_carga_hutec), e a devolucao em si
 * entra como lancamento manual na rubrica 0283.
 *
 * O processo e idempotente: apaga os lancamentos de origem PAGAMENTO_AUTOMATICO
 * ainda nao conciliados e regera. Lancamentos manuais e conciliados nao sao
 * tocados.
 */
class GerenciarMotorFinanceiroAC {

    const ORIGEM = 'PAGAMENTO_AUTOMATICO';

    private $conex;
    private $codEvento;
    private $codGestor;

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        ConexaoDB::getInstance();
        $this->conex = ConexaoDB::getConexaoDB();
        $this->codEvento = COD_EVENTO;
        $this->codGestor = $this->resolverGestor();
    }

    /**
     * Gestor do evento (sis_gestor), para consolidar o relatorio por gestor.
     */
    private function resolverGestor() {
        $sql = sprintf("SELECT g.cod_gestor
                        FROM tab_eventos e
                        INNER JOIN sis_gestor g ON UPPER(g.nome_gestor) = UPPER(e.gestor)
                        WHERE e.cod_evento = %d LIMIT 1", $this->codEvento);
        $rs = $this->conex->Execute($sql);
        return ($rs && !$rs->EOF) ? $rs->fields['cod_gestor'] : null;
    }

    /**
     * Acao chamada pela tela (AJAX). Devolve JSON com o resumo.
     */
    public function gerarLancamentosPagamentos($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        $resumo = array();

        try {
            // 'completo' refaz tudo (usar quando a regra mudou); o padrao e
            // incremental: so lanca o que ainda nao foi lancado, sem mexer no
            // que ja esta gravado -- um pagamento novo nao justifica reescrever
            // centenas de linhas.
            $modo = (isset($fws_['modo']) && $fws_['modo'] == 'completo') ? 'completo' : 'incremental';
            $resumo = $this->executarMotor($debug, $modo);
            $ok = true;
            $msg = $modo == 'completo'
                ? sprintf('MotorFinanceiro refeita: %d pagamento(s), %d lancamento(s).',
                          $resumo['pagamentos'], $resumo['lancamentos'])
                : sprintf('%d pagamento(s) novo(s) lancado(s): %d lancamento(s) acrescentado(s).',
                          $resumo['pagamentos'], $resumo['lancamentos']);
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $this->paraUtf8($msg),
            'resumo'        => $resumo
        ));
        exit();
    }

    /**
     * Previa: nao grava nada, so mostra o que seria gerado.
     */
    public function previaLancamentos($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        $resumo = array();

        try {
            $modo = (isset($fws_['modo']) && $fws_['modo'] == 'completo') ? 'completo' : 'incremental';
            $resumo = $this->calcularResumo($debug, $modo);
            $ok = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $this->paraUtf8($msg),
            'resumo'        => $resumo
        ));
        exit();
    }

    /**
     * O balancete esta desatualizado? Duas coisas deixam ele para tras, e
     * nenhuma delas se corrige sozinha:
     *   - pagamento novo entrou depois da ultima geracao de lancamentos;
     *   - regra de rateio criada/alterada depois dos lancamentos existentes.
     * Tambem informa quantos lancamentos conciliados nao seriam refeitos.
     */
    public function situacaoMotor($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        $dados = array('temLancamentos' => false, 'pagamentosNovos' => 0,
                       'regrasAlteradas' => false, 'conciliados' => 0);

        try {
            $sql = sprintf("SELECT COUNT(*) AS total, MAX(data_inclusao) AS ultimo,
                                   SUM(CASE WHEN conciliado = 'SIM' THEN 1 ELSE 0 END) AS conciliados
                            FROM fin_lancamentos
                            WHERE cod_evento = %d AND origem_lancamento = %s",
                           $this->codEvento, $this->conex->qstr(self::ORIGEM));
            $rs = $this->conex->Execute($sql);
            $total = ($rs && !$rs->EOF) ? intval($rs->fields['total']) : 0;
            $ultimo = ($rs && !$rs->EOF) ? $rs->fields['ultimo'] : null;
            $dados['conciliados'] = ($rs && !$rs->EOF) ? intval($rs->fields['conciliados']) : 0;
            $dados['temLancamentos'] = $total > 0;

            if ($total > 0) {
                // pagamentos elegiveis que ainda nao viraram lancamento
                $sql = sprintf("SELECT COUNT(*) AS n FROM tab_pagamentos p
                                WHERE p.cod_evento = %d
                                  AND p.status = 'ATIVADA'
                                  AND p.data_pagamento IS NOT NULL AND p.valor > 0
                                  AND NOT EXISTS (SELECT 1 FROM fin_lancamentos l
                                                   WHERE l.cod_evento = p.cod_evento
                                                     AND l.cod_pagamento = p.cod_pagamento)",
                               $this->codEvento);
                $rs = $this->conex->Execute($sql);
                $dados['pagamentosNovos'] = ($rs && !$rs->EOF) ? intval($rs->fields['n']) : 0;

                // regra mexida depois do ultimo lancamento gerado
                if ($ultimo) {
                    $sql = sprintf("SELECT COUNT(*) AS n FROM fin_rateios
                                    WHERE cod_evento = %d AND status = 'ATIVADA'
                                      AND COALESCE(data_alteracao, data_inclusao) > %s",
                                   $this->codEvento, $this->conex->qstr($ultimo));
                    $rs = $this->conex->Execute($sql);
                    $dados['regrasAlteradas'] = ($rs && !$rs->EOF) ? intval($rs->fields['n']) > 0 : false;
                }
            }
            $ok = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok, 'message' => $this->paraUtf8($msg), 'dados' => $dados));
        exit();
    }

    // ------------------------------------------------------------------ nucleo

    private function executarMotor($debug = false, $modo = 'incremental') {
        $catalogo = $this->carregarCatalogo();
        $pagamentos = $this->carregarPagamentos($modo == 'incremental');

        if (empty($pagamentos)) {
            throw new Exception($modo == 'incremental'
                ? 'Nenhum pagamento novo para gerar lancamento -- tudo em dia.'
                : 'Nenhum pagamento pago encontrado neste evento para gerar lancamento.');
        }

        $fechamento = $this->dataFechamento();

        if ($modo == 'completo') {
            // Refaz do zero: apaga o que este processo gerou e ainda nao foi
            // conciliado. Usado quando a regra de rateio muda. Periodo fechado
            // fica intocado -- so apaga o que tem data > fechamento.
            $filtroFech = $fechamento
                ? sprintf(" AND (data_competencia IS NULL OR data_competencia > %s)", $this->conex->qstr($fechamento))
                : '';
            $onde = sprintf("cod_evento = %d AND origem_lancamento = %s
                   AND (conciliado IS NULL OR conciliado <> 'SIM') %s",
                $this->codEvento, $this->conex->qstr(self::ORIGEM), $filtroFech);

            $this->conex->debug = $debug;
            $this->registrarExclusao($onde, 'Reprocessamento completo da geracao de lancamentos');
            $this->conex->Execute("DELETE FROM fin_lancamentos WHERE " . $onde);
        }

        $linhas = array();
        $resumo = $this->resumoVazio();
        $agora = date('Y-m-d H:i:s');
        $usuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

        foreach ($pagamentos as $pg) {
            $meio = $this->normalizarMeio($pg['forma_pagamento']);
            $valor = round(floatval($pg['valor']), 2);
            $data = substr($pg['data_pagamento'], 0, 10);
            $nome = trim($pg['nome_inscrito']) ? trim($pg['nome_inscrito']) : 'Inscrito ' . $pg['cod_inscrito'];

            if (!$this->entrouNaConta($pg)) {
                $resumo['ignorados']++;
                continue;
            }

            $resumo['pagamentos']++;
            $resumo['receita'] += $valor;

            // 1) receita bruta
            $linhas[] = $this->montarLinha(array(
                'centro'    => $catalogo['centro'],
                'conta'     => $this->contaDaReceita($catalogo, $meio),
                'pagamento' => $pg['cod_pagamento'],
                'inscrito'  => $pg['cod_inscrito'],
                'fonte'     => $catalogo['fonte'],
                'meio'      => $meio,
                'data'      => $data,
                'tipo'      => 'VENDA',
                'flg'       => 'C',
                'valor'     => $valor,
                'historico' => sprintf('Inscricao %s - %s (%s)', $pg['cod_pagamento'], $nome, $meio),
            ), $agora, $usuario, $ip);

            // 2) taxa do meio de pagamento
            $taxa = $this->calcularTaxa($valor, $meio, $catalogo['regras']);
            if ($taxa !== null && $taxa['valor'] > 0) {
                $resumo['taxa'] += $taxa['valor'];
                $linhas[] = $this->montarLinha(array(
                    'centro'    => $catalogo['centro'],
                    'conta'     => $taxa['conta'],
                    'pagamento' => $pg['cod_pagamento'],
                    'inscrito'  => $pg['cod_inscrito'],
                    'fonte'     => $catalogo['fonte'],
                    'meio'      => $meio,
                    'data'      => $data,
                    'tipo'      => 'DESPESA_PAGA',
                    'flg'       => 'D',
                    'valor'     => $taxa['valor'],
                    'historico' => sprintf('Taxa %s s/ pagamento %s', $meio, $pg['cod_pagamento']),
                ), $agora, $usuario, $ip);
            } else {
                $resumo['sem_regra_taxa']++;
            }

            // 3) deducoes institucionais -- uma linha por pagamento, como no
            //    balancete da fundacao
            foreach ($catalogo['institucionais'] as $inst) {
                $valorInst = round($valor * $inst['percentual'] / 100, 2);
                if ($valorInst <= 0) {
                    continue;
                }
                $resumo['institucional'] += $valorInst;
                $linhas[] = $this->montarLinha(array(
                    'centro'    => $catalogo['centro'],
                    'conta'     => $inst['conta'],
                    'pagamento' => $pg['cod_pagamento'],
                    'inscrito'  => $pg['cod_inscrito'],
                    'fonte'     => $catalogo['fonte'],
                    'meio'      => $meio,
                    'data'      => $data,
                    'tipo'      => 'DESPESA_PAGA',
                    'flg'       => 'D',
                    'valor'     => $valorInst,
                    'historico' => sprintf('%s s/ pagamento %s', $inst['nome'], $pg['cod_pagamento']),
                ), $agora, $usuario, $ip);
            }
        }

        $this->gravarLinhas($linhas, $debug);
        $resumo['lancamentos'] = count($linhas);
        return $this->rotularContas($catalogo, $resumo);
    }

    /**
     * Mesma conta da geracao de lancamentos, mas sem gravar (usado na previa).
     */
    private function calcularResumo($debug = false, $modo = 'incremental') {
        $catalogo = $this->carregarCatalogo();
        $pagamentos = $this->carregarPagamentos($modo == 'incremental');
        $resumo = $this->resumoVazio();

        foreach ($pagamentos as $pg) {
            $meio = $this->normalizarMeio($pg['forma_pagamento']);
            $valor = round(floatval($pg['valor']), 2);

            if (!$this->entrouNaConta($pg)) {
                $resumo['ignorados']++;
                continue;
            }

            $resumo['pagamentos']++;
            $resumo['receita'] += $valor;
            $resumo['lancamentos']++;

            $taxa = $this->calcularTaxa($valor, $meio, $catalogo['regras']);
            if ($taxa !== null && $taxa['valor'] > 0) {
                $resumo['taxa'] += $taxa['valor'];
                $resumo['lancamentos']++;
            } else {
                $resumo['sem_regra_taxa']++;
            }

            foreach ($catalogo['institucionais'] as $inst) {
                $valorInst = round($valor * $inst['percentual'] / 100, 2);
                if ($valorInst > 0) {
                    $resumo['institucional'] += $valorInst;
                    $resumo['lancamentos']++;
                }
            }
        }
        return $this->rotularContas($catalogo, $resumo);
    }

    /**
     * O dinheiro entrou na conta? Só o pagamento ATIVADA conta.
     *
     * A regra anterior deixava CANCELADO/DEVOLVIDO entrar quando o retorno da
     * fundacao trazia o pagamento -- partindo de que o extrato dela provaria a
     * entrada. No evento 122 isso gerou 9 cancelamentos de cartao que
     * nunca liquidaram (no PagSeguro: status "Cancelada", sem taxa retida,
     * liquido = bruto): o retorno veio errado e o sistema o tratou como prova.
     * O status do pagamento e a fonte; o retorno serve para conferir, nao para
     * decidir o que entra.
     */
    private function entrouNaConta($pg) {
        return $pg['status'] == 'ATIVADA';
    }

    private function resumoVazio() {
        return array(
            'pagamentos'               => 0,
            'lancamentos'              => 0,
            'confirmados_pelo_retorno' => 0,
            'ignorados'                => 0,
            'sem_regra_taxa'           => 0,
            'receita'                  => 0,
            'taxa'                     => 0,
            'institucional'            => 0,
            'conta_receita'            => '',
            'conta_taxa'               => '',
        );
    }

    /**
     * O banco devolve ora latin1, ora utf8 (depende do SET NAMES da conexao).
     * So converte o que ainda nao e utf8 valido -- senao acentua duas vezes
     * ("BANCARIAS" virava "BANCAÌ81RIAS").
     */
    private function paraUtf8($texto) {
        if ($texto === null || $texto === '') {
            return '';
        }
        return mb_detect_encoding($texto, 'UTF-8', true) ? $texto : utf8_encode($texto);
    }

    /**
     * Rotulo das rubricas escolhidas, pra tela mostrar em qual conta deste
     * evento cada valor vai cair (nao ha codigo fixo).
     */
    private function rotularContas($catalogo, $resumo) {
        $ids = array();
        if (!empty($catalogo['contaReceita'])) {
            $ids['receita'] = $catalogo['contaReceita'];
        }
        foreach ($catalogo['regras'] as $r) {
            if (!empty($r['conta'])) {
                $ids['taxa'] = $r['conta'];
                break;
            }
        }
        if (empty($ids)) {
            return $resumo;
        }
        $sql = sprintf("SELECT cod_plano_contas, codigo_financeiro, nome_plano_contas
                        FROM fin_plano_contas WHERE cod_plano_contas IN (%s)",
                       implode(',', array_map('intval', $ids)));
        $rs = $this->conex->Execute($sql);
        $nomes = array();
        if ($rs) {
            foreach ($rs as $r) {
                $nomes[$r['cod_plano_contas']] = trim($r['codigo_financeiro'] . ' ' . $r['nome_plano_contas']);
            }
        }
        if (isset($ids['receita']) && isset($nomes[$ids['receita']])) {
            $resumo['conta_receita'] = $this->paraUtf8($nomes[$ids['receita']]);
        }
        if (isset($ids['taxa']) && isset($nomes[$ids['taxa']])) {
            $resumo['conta_taxa'] = $this->paraUtf8($nomes[$ids['taxa']]);
        }
        return $resumo;
    }

    // ------------------------------------------------------------- carregadores

    /**
     * Rubricas, centro de custo, fonte e regras de taxa do evento.
     * Falha com mensagem clara se o catalogo nao estiver cadastrado.
     */
    private function carregarCatalogo() {
        // Contas de RECEITA que aceitam lancamento. Nao se procura por codigo
        // financeiro: cada evento pode ter o seu proprio plano (um usa 0106
        // CURSOS E EVENTOS, outro 0135 MENSALIDADE), entao vale a semantica da
        // propria tabela -- tipo_conta + aceita_lancamento.
        $receitas = array();
        $sql = sprintf("SELECT cod_plano_contas, codigo_financeiro, nome_plano_contas
                        FROM fin_plano_contas
                        WHERE cod_evento = %d AND status = 'ATIVADA'
                          AND tipo_conta = 'RECEITA' AND aceita_lancamento = 1
                          AND natureza_saldo <> 'DEBITO'
                        ORDER BY codigo_financeiro", $this->codEvento);
        $rs = $this->conex->Execute($sql);
        if ($rs) {
            foreach ($rs as $r) {
                $receitas[] = $r;
            }
        }

        $sql = sprintf("SELECT cod_centro_custo FROM fin_centro_custo
                        WHERE cod_evento = %d AND status = 'ATIVADA' ORDER BY cod_centro_custo LIMIT 1", $this->codEvento);
        $rs = $this->conex->Execute($sql);
        $centro = ($rs && !$rs->EOF) ? $rs->fields['cod_centro_custo'] : null;
        if (!$centro) {
            throw new Exception('Nenhum centro de custo cadastrado neste evento. Cadastre o projeto em Financeiro &gt; Financeiro &gt; Centro de Custo.');
        }

        $sql = sprintf("SELECT cod_fontes_recurso FROM fin_fontes_recurso
                        WHERE cod_evento = %d AND status = 'ATIVADA' ORDER BY cod_fontes_recurso LIMIT 1", $this->codEvento);
        $rs = $this->conex->Execute($sql);
        $fonte = ($rs && !$rs->EOF) ? $rs->fields['cod_fontes_recurso'] : null;

        // Regras de taxa por meio de pagamento. Cada regra diz de qual rubrica
        // sai a receita (cod_conta_origem) e em qual entra a taxa
        // (cod_conta_destino) -- e assim que um evento personaliza o plano sem
        // depender dos codigos 0106/0230.
        $regras = array();
        $contaReceitaRegra = null;
        $sql = sprintf("SELECT meio_pagamento, percentual, valor_fixo, cod_conta_origem, cod_conta_destino
                        FROM fin_rateios
                        WHERE cod_evento = %d AND status = 'ATIVADA' AND meio_pagamento IS NOT NULL", $this->codEvento);
        $rs = $this->conex->Execute($sql);
        if ($rs) {
            foreach ($rs as $r) {
                $regras[strtoupper(trim($r['meio_pagamento']))] = array(
                    'percentual' => floatval($r['percentual']),
                    'fixo'       => floatval($r['valor_fixo']),
                    'conta'      => $r['cod_conta_destino'],
                    'receita'    => $r['cod_conta_origem'],
                );
                if (!$contaReceitaRegra && $r['cod_conta_origem']) {
                    $contaReceitaRegra = $r['cod_conta_origem'];
                }
            }
        }

        // Rubrica de receita, em ordem: (1) a conta de origem definida nas
        // regras de Rateios do evento; (2) se o evento tem uma unica conta de
        // receita que aceita lancamento, e ela. Havendo varias e nenhuma regra
        // apontando, o sistema nao adivinha -- pede para definir.
        $contaReceita = $contaReceitaRegra;
        if (!$contaReceita && count($receitas) == 1) {
            $contaReceita = $receitas[0]['cod_plano_contas'];
        }
        if (!$contaReceita) {
            if (empty($receitas)) {
                throw new Exception('Este evento nao tem nenhuma conta de RECEITA que aceite lancamento. '
                    . 'Cadastre a rubrica da receita de inscricoes em Financeiro &gt; Financeiro &gt; Plano de Contas.');
            }
            $nomes = array();
            foreach ($receitas as $r) {
                $nomes[] = trim($r['codigo_financeiro'] . ' ' . $r['nome_plano_contas']);
            }
            throw new Exception('Este evento tem mais de uma conta de receita ('
                . implode(', ', $nomes) . '). Informe em Financeiro &gt; Financeiro &gt; Rateios, '
                . 'na conta de origem da regra, qual delas recebe os pagamentos.');
        }

        // Deducoes institucionais (TAXA HUTEC, taxas de direcao): sao as regras
        // SEM meio de pagamento. Lancam as FOLHAS da hierarquia -- e assim que o
        // balancete da fundacao faz: quando a direcao de 10% se divide em dois
        // departamentos, aparecem duas linhas de 5%, nao uma de 10%.
        $institucionais = array();
        $temFilho = array();
        $sql = sprintf("SELECT cod_rateio, cod_rateio_principal, nome_rateio, percentual, cod_conta_destino
                        FROM fin_rateios
                        WHERE cod_evento = %d AND status = 'ATIVADA'
                          AND (meio_pagamento IS NULL OR meio_pagamento = '')
                          AND percentual > 0
                        ORDER BY cod_rateio", $this->codEvento);
        $rs = $this->conex->Execute($sql);
        $todas = array();
        if ($rs) {
            foreach ($rs as $r) {
                $todas[] = $r;
                if (!empty($r['cod_rateio_principal'])) {
                    $temFilho[$r['cod_rateio_principal']] = true;
                }
            }
        }
        foreach ($todas as $r) {
            if (isset($temFilho[$r['cod_rateio']])) {
                continue;   // e pai: quem lanca sao os filhos
            }
            if (empty($r['cod_conta_destino'])) {
                continue;   // sem rubrica de destino nao ha onde lancar
            }
            $institucionais[] = array(
                'nome'       => $r['nome_rateio'],
                'percentual' => floatval($r['percentual']),
                'conta'      => $r['cod_conta_destino'],
            );
        }

        return array('receitas' => $receitas, 'centro' => $centro, 'fonte' => $fonte,
                     'regras' => $regras, 'contaReceita' => $contaReceita,
                     'institucionais' => $institucionais);
    }

    /**
     * Data ate a qual o periodo esta fechado neste evento (ou null).
     */
    /**
     * Copia para fin_lancamentos_excluidos tudo o que o WHERE alcanca,
     * ANTES do DELETE. O balancete nao guarda historico -- e recalculado a cada
     * abertura --, entao sem isso um reprocesso apaga a evidencia do que a tela
     * mostrava antes. Falha aqui nao bloqueia a operacao: se a tabela de log
     * ainda nao existe no servidor, a geracao de lancamentos segue.
     */
    private function registrarExclusao($onde, $motivo) {
        try {
            $sql = sprintf("INSERT INTO fin_lancamentos_excluidos (
                        cod_lancamentos, cod_evento, cod_gestor, cod_centro_custo,
                        cod_plano_contas, cod_pagamento, cod_inscrito, favorecido,
                        cod_fontes_recurso, meio_pagamento, data_competencia, data_financeira,
                        tipo_lancamento, flg_lancamento, valor, origem_lancamento, historico,
                        num_documento, arquivo_comprovante, conciliado, data_inclusao,
                        data_alteracao, motivo_exclusao, data_exclusao, id_exclusao,
                        ip_exclusao, session_id_exclusao, http_user_agent_exclusao)
                    SELECT cod_lancamentos, cod_evento, cod_gestor, cod_centro_custo,
                        cod_plano_contas, cod_pagamento, cod_inscrito, favorecido,
                        cod_fontes_recurso, meio_pagamento, data_competencia, data_financeira,
                        tipo_lancamento, flg_lancamento, valor, origem_lancamento, historico,
                        num_documento, arquivo_comprovante, conciliado, data_inclusao,
                        data_alteracao, %s, %s, %s, %s, %s, %s
                      FROM fin_lancamentos WHERE %s",
                $this->conex->qstr($motivo),
                $this->conex->qstr(date('Y-m-d H:i:s')),
                $this->conex->qstr(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : ''),
                $this->conex->qstr(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
                $this->conex->qstr(session_id()),
                $this->conex->qstr(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
                $onde);
            $this->conex->Execute($sql);
        } catch (Exception $e) {
            // log indisponivel nao impede a geracao de lancamentos
        }
    }

    private function dataFechamento() {
        $sql = sprintf("SELECT data_fechamento FROM fin_periodo_fechamento WHERE cod_evento = %d", $this->codEvento);
        $rs = $this->conex->Execute($sql);
        return ($rs && !$rs->EOF && $rs->fields['data_fechamento']) ? $rs->fields['data_fechamento'] : null;
    }

    private function carregarPagamentos($somenteNaoLancados = false) {
        $filtro = $somenteNaoLancados
            ? " AND NOT EXISTS (SELECT 1 FROM fin_lancamentos l
                                 WHERE l.cod_evento = p.cod_evento
                                   AND l.cod_pagamento = p.cod_pagamento) "
            : '';
        // periodo fechado: a geracao de lancamentos nao cria/refaz lancamento la dentro
        $fech = $this->dataFechamento();
        if ($fech) {
            $filtro .= sprintf(" AND DATE(p.data_pagamento) > %s ", $this->conex->qstr($fech));
        }
        $sql = sprintf("SELECT p.cod_pagamento, p.cod_inscrito, p.valor, p.data_pagamento,
                               p.forma_pagamento, p.status, i.nome_inscrito
                        FROM tab_pagamentos p
                        LEFT JOIN tab_inscritos i ON i.cod_inscrito = p.cod_inscrito AND i.cod_evento = p.cod_evento
                        WHERE p.cod_evento = %d
                          AND p.status = 'ATIVADA'
                          AND p.data_pagamento IS NOT NULL
                          AND p.valor > 0
                          %s
                        ORDER BY p.data_pagamento, p.cod_pagamento", $this->codEvento, $filtro);

        $rs = $this->conex->Execute($sql);
        $lista = array();
        if ($rs) {
            foreach ($rs as $r) {
                $lista[] = $r;
            }
        }
        return $lista;
    }

    // ----------------------------------------------------------------- calculo

    /**
     * PIX -> PIX | CARTAO_CREDITO/CARTAO_DEBITO/CARTAO -> como cadastrado |
     * BOLETO (inclui variacoes tipo BOLETO_BANCARIO).
     */
    private function normalizarMeio($forma) {
        $f = strtoupper(trim($forma));
        if ($f === '') {
            return 'OUTRO';
        }
        if (strpos($f, 'PIX') !== false)    return 'PIX';
        if (strpos($f, 'BOLETO') !== false) return 'BOLETO';
        if (strpos($f, 'CREDITO') !== false) return 'CARTAO_CREDITO';
        if (strpos($f, 'DEBITO') !== false)  return 'CARTAO_DEBITO';
        if (strpos($f, 'CARTAO') !== false)  return 'CARTAO';
        return $f;
    }

    /**
     * taxa = valor * percentual% + valor_fixo (regra vinda de fin_rateios).
     */
    /**
     * Rubrica onde a receita e lancada. A regra do meio de pagamento pode
     * apontar uma conta propria (evento com plano personalizado); senao usa a
     * conta de receita do evento.
     */
    private function contaDaReceita($catalogo, $meio) {
        if (!empty($catalogo['regras'][$meio]['receita'])) {
            return $catalogo['regras'][$meio]['receita'];
        }
        return $catalogo['contaReceita'];
    }

    private function calcularTaxa($valor, $meio, $regras) {
        if (!isset($regras[$meio])) {
            // cartao generico cai na regra de credito quando so ela existe
            if ($meio == 'CARTAO' && isset($regras['CARTAO_CREDITO'])) {
                $meio = 'CARTAO_CREDITO';
            } else {
                return null;
            }
        }
        $r = $regras[$meio];
        if (empty($r['conta'])) {
            // regra sem rubrica de destino e sem 0230 no plano do evento:
            // nao da pra lancar a taxa em lugar nenhum
            return null;
        }
        $taxa = round($valor * $r['percentual'] / 100 + $r['fixo'], 2);
        return array('valor' => $taxa, 'conta' => $r['conta']);
    }

    // ------------------------------------------------------------------ gravar

    private function montarLinha($d, $agora, $usuario, $ip) {
        return sprintf("(%d, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'NAO', %s, %s, %s)",
            $this->codEvento,
            $this->codGestor ? intval($this->codGestor) : 'NULL',
            $d['centro'] ? intval($d['centro']) : 'NULL',
            $d['conta'] ? intval($d['conta']) : 'NULL',
            intval($d['pagamento']),
            isset($d['inscrito']) && $d['inscrito'] ? intval($d['inscrito']) : 'NULL',
            $d['fonte'] ? intval($d['fonte']) : 'NULL',
            $this->conex->qstr($d['meio']),
            $this->conex->qstr($d['data']),
            $this->conex->qstr($d['data']),
            $this->conex->qstr($d['tipo']),
            $this->conex->qstr($d['flg']),
            number_format($d['valor'], 2, '.', ''),
            $this->conex->qstr(self::ORIGEM),
            $this->conex->qstr($d['historico']),
            $this->conex->qstr($agora),
            $this->conex->qstr($usuario),
            $this->conex->qstr($ip)
        );
    }

    private function gravarLinhas($linhas, $debug = false) {
        if (empty($linhas)) {
            return;
        }
        $colunas = "(cod_evento, cod_gestor, cod_centro_custo, cod_plano_contas, cod_pagamento, cod_inscrito, cod_fontes_recurso,
                     meio_pagamento, data_competencia, data_financeira, tipo_lancamento, flg_lancamento,
                     valor, origem_lancamento, historico, conciliado, data_inclusao, id_inclusao, ip_inclusao)";

        // grava em blocos para nao estourar max_allowed_packet
        foreach (array_chunk($linhas, 200) as $bloco) {
            $sql = "INSERT INTO fin_lancamentos " . $colunas . " VALUES " . implode(",\n", $bloco);
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
        }
    }
}
