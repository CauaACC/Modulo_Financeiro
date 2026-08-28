<?php

class EventoProposta extends EventoPropostaVO
{

    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new EventoPropostaDAO();
    }

    public function carregarPorEvento($debug = false)
    {
        return $this->objDAO->getByCodEvento($this, $debug);
    }

    public function existe($debug = false)
    {
        return $this->objDAO->getByCodEventoExiste($this, $debug);
    }

    public function salvar($debug = false)
    {
        $this->validatorObj();
        if ($this->existe($debug)) {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Proposta do evento atualizada com sucesso.");
            }
            throw new Exception("A proposta do evento não foi atualizada.");
        }
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Proposta do evento salva com sucesso.");
        }
        throw new Exception("A proposta do evento não foi salva.");
    }

    /**
     * Formulario de pre-avaliacao do gestor deste evento: opcional e definido
     * por gestor. Retorna array de campos (definicao) — vazio quando o gestor
     * nao usa o formulario ou a definicao esta invalida.
     */
    public function carregarCamposPreAvaliacao($debug = false)
    {
        $cfg = $this->objDAO->getPreAvaliacaoDoGestor($this->getCodEvento(), $debug);
        if (!$cfg || !$cfg['usa'] || $cfg['campos_json'] == '') {
            return array();
        }
        $campos = json_decode($cfg['campos_json'], true);
        if (!is_array($campos)) {
            return array();
        }
        // Descarta entradas sem o minimo (nome do campo e rotulo).
        $validos = array();
        foreach ($campos as $c) {
            if (!empty($c['campo']) && !empty($c['rotulo'])) {
                $validos[] = $c;
            }
        }
        return $validos;
    }

    /** Dados do evento exibidos (e editaveis) na Proposta — vivem em tab_eventos. */
    public function carregarResumoEvento($debug = false)
    {
        return $this->objDAO->getResumoEvento($this->getCodEvento(), $debug);
    }

    /** Grava em tab_eventos os campos de identificacao editados aqui. */
    public function salvarDadosEvento(array $dados, $debug = false)
    {
        return $this->objDAO->atualizarDadosEvento($this->getCodEvento(), $dados, $debug);
    }

    public static function getCamposEventoEditaveis()
    {
        return EventoPropostaDAO::getColunasEventoEditaveis();
    }

    /** Nome do gestor do evento (para exibir de quem e o formulario). */
    public function getNomeGestor($debug = false)
    {
        $cfg = $this->objDAO->getPreAvaliacaoDoGestor($this->getCodEvento(), $debug);
        return $cfg ? $cfg['nome_gestor'] : '';
    }

    /**
     * Sugere o cronograma de tramitacao a partir das datas ja cadastradas —
     * o coordenador ajusta depois. Formato do plano de trabalho: uma linha por
     * atividade, com os meses em que acontece.
     */
    public function sugerirCronograma($dataIni, $dataFim, $debug = false)
    {
        if ($dataIni == '' || $dataFim == '') {
            return '';
        }
        $ini = strtotime($dataIni);
        $fim = strtotime($dataFim);
        if (!$ini || !$fim || $fim < $ini) {
            return '';
        }
        $meses = array('', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                       'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');
        $mesIni = (int) date('n', $ini);
        $mesFim = (int) date('n', $fim);
        $nomeIni = $meses[$mesIni];
        $nomeFim = $meses[$mesFim];
        $periodo = $mesIni == $mesFim ? $nomeIni : $nomeIni . ' a ' . $nomeFim;

        return "Tramitação da proposta: até " . $nomeIni . "\n"
             . "Inscrições: " . $periodo . "\n"
             . "Submissão de trabalhos: " . $periodo . "\n"
             . "Realização do evento: " . $nomeFim;
    }
}
