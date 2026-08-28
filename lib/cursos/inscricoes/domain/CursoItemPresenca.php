<?php

class CursoItemPresenca extends CursoItemPresencaVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new CursoItemPresencaDAO();
    }

    public function registrar($debug = false) {
        $this->validatorRegistrar();
        if (!$this->getDataHora()) {
            $this->setDataHora(date('Y-m-d H:i:s'));
        }
        if (!$this->getIp()) {
            $this->setIp($_SERVER['REMOTE_ADDR']);
        }
        if (!$this->getUserAgent()) {
            $this->setUserAgent(substr($_SERVER['HTTP_USER_AGENT'], 0, 500));
        }
        if ($this->objDAO->insert($this, $debug)) {
            return true;
        }
        throw new Exception("Não foi possível registrar a presença.");
    }

    public function carregarRelatorioPorItem($codCursoItem, $debug = false) {
        return $this->objDAO->relatorioPorItem($codCursoItem, $debug);
    }

    public function carregarRelatorioPorInscrito($codCursoItem, $codInscrito, $debug = false) {
        return $this->objDAO->relatorioPorInscrito($codCursoItem, $codInscrito, $debug);
    }

    public function carregarRelatorioGeral($codEvento, $debug = false) {
        return $this->objDAO->relatorioGeralPorEvento($codEvento, $debug);
    }

    public function carregarPainelAlunos($codEvento, $codCursoFiltro = null, $debug = false) {
        return $this->objDAO->painelAlunos($codEvento, $codCursoFiltro, $debug);
    }

    public function carregarProgressoAlunoPorCurso($codInscrito, $codEvento, $debug = false) {
        return $this->objDAO->progressoAlunoPorCurso($codInscrito, $codEvento, $debug);
    }

    public function carregarDetalheAlunoPorItem($codInscrito, $codEvento, $debug = false) {
        return $this->objDAO->detalheAlunoPorItem($codInscrito, $codEvento, $debug);
    }

    public function carregarTimelineEventos($codInscrito, $codCursoItem, $codSessao = null, $debug = false) {
        return $this->objDAO->timelineEventos($codInscrito, $codCursoItem, $codSessao, $debug);
    }

    public function carregarFolhaPontoPorAluno($codEvento, $codInscrito = null, $debug = false) {
        return $this->objDAO->folhaPontoPorAluno($codEvento, $codInscrito, $debug);
    }
}
