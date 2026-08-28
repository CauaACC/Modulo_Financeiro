<?php

class Colegio extends ColegioVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ColegioDAO();
    }

    public function ColegioExiste() {
        if (!$this->getCodColegio()) {
            throw new Exception('Codigo do colegio invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum colegio foi encontrado para o codigo informado: " . $this->getCodColegio() . ".");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodColegio()) or $this->getCodColegio() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Colegio salvo com sucesso.");
            } else {
                throw new Exception("Novo colegio nao foi salvo.");
            }
        } else {
            $this->ColegioExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Colegio foi atualizado com sucesso.");
            } else {
                throw new Exception("Colegio atual nao foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Colegio cod:" . $this->getCodColegio() . " foi excluido com sucesso.");
        } else {
            throw new Exception("Colegio cod:" . $this->getCodColegio() . " nao foi excluido.");
        }
    }

    public function getColegioById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaColegiosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento nao informado.");
        }
        $this->objDAO->carregarListaColegiosByIdEvento($this, $debug);
        if ($this->getListaColegios()) {
            return $this->getListaColegios();
        } else {
            throw new Exception("Evento nao possui colegios cadastrados.");
        }
    }

}
