<?php

class Grafico extends GraficoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new GraficoDAO();
    }

    public function carregarListaTotalInscritosPorRegionalPR($debug = false) {
        $this->objDAO->carregarListaTotalInscritosPorRegionalPR($this, $debug);
        if ($this->getListaTotalInscritosPorRegionalPR()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por regional.");
        }
    }
    
    public function carregarListaTotalInscritosPorTipoInstituicao($debug = false) {
        $this->objDAO->carregarListaTotalInscritosPorTipoInstituicao($this, $debug);
        if ($this->getListaTotalInscritosPorTipoInstituicao()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por tipo instituição.");
        }
    }
    
    public function carregarListaTotalInscritosPorProfissaoAtuacao($debug = false) {
        $this->objDAO->carregarListaTotalInscritosPorProfissaoAtuacao($this, $debug);
        if ($this->getListaTotalInscritosPorProfissaoAtuacao()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por profissão de atuação.");
        }
    }
    
    public function carregarListaTotalInscritosPorTipoInscricao($debug = false) {
        $this->objDAO->carregarListaTotalInscritosPorTipoInscricao($this, $debug);
        if ($this->getListaTotalInscritosPorTipoInscricao()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por tipo inscrito.");
        }
    }

    public function carregarListaTotalTrabalhosPorAreaTematica($debug = false) {
        $this->objDAO->carregarListaTotalTrabalhosPorAreaTematica($this, $debug);
        if ($this->getListaTotalTrabalhosPorAreaTematica()) {
        } else {
            throw new Exception("Evento não possui trabalhos cadastrado por área temática.");
        }
    }

    public function carregarListaTotalTrabalhosPorFormatoTexto($debug = false) {
        $this->objDAO->carregarListaTotalTrabalhosPorFormatoTexto($this, $debug);
        if ($this->getListaTotalTrabalhosPorFormatoTexto()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por área temática.");
        }
    }

    public function carregarListaTotalTrabalhosPorPremiacao($debug = false) {
        $this->objDAO->carregarListaTotalTrabalhosPorPremiacao($this, $debug);
        if ($this->getListaTotalTrabalhosPorPremiacao()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por área temática.");
        }
    }

    public function carregarListaTotalTrabalhosPorInstituicao($debug = false) {
        $this->objDAO->carregarListaTotalTrabalhosPorInstituicao($this, $debug);
        if ($this->getListaTotalTrabalhosPorInstituicao()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por área temática.");
        }
    }

    public function carregarListaTotalInscritosPorOficina($debug = false) {
        $this->objDAO->carregarListaTotalInscritosPorOficina($this, $debug);
        if ($this->getListaTotalInscritosPorOficina()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por oficina.");
        }
    }
    public function carregarListaTotalInscritosPorSimposio($debug = false) {
        $this->objDAO->carregarListaTotalInscritosPorSimposio($this, $debug);
        if ($this->getListaTotalInscritosPorSimposio()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por Simposio.");
        }
    }
    
    public function carregarListaTotalInscritosPorEventoParalelo($debug = false) {
        $this->objDAO->carregarListaTotalInscritosPorEventoParalelo($this, $debug);
        if ($this->getListaTotalInscritosPorEventoParalelo()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por Evento Paralelo.");
        }
    }
    
    public function carregarListaTotalInscritosPorMacrorregiaoPR($debug = false) {
        $this->objDAO->carregarListaTotalInscritosPorMacrorregiaoPR($this, $debug);
        if ($this->getListaTotalInscritosPorMacrorregiaoPR()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por macrorregião.");
        }
    }
    
    public function carregarListaTotalAvaliadosPorAvaliadorInscrito($debug = false) {
        $this->objDAO->carregarListaTotalAvaliadosPorAvaliadorInscrito($this, $debug);
        if ($this->getListaTotalAvaliadosPorAvaliadorInscrito()) {
        } else {
            throw new Exception("Evento não possui inscritos cadastrado por macrorregião.");
        }
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

