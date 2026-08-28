<?php

class InscritoTrabalho extends InscritoTrabalhoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new InscritoTrabalhoDAO();
    }

    public function inscritoTrabalhoExiste($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado.');
        }
        if (!$this->getCodTrabalho()) {
            throw new Exception('Código da Trabalho inválido ou não informado.');
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }
    public function carregarListaTrabalhosByIdInscrito($debug = false) {
        $this->objDAO->carregarListaTrabalhosByIdInscrito($this, $debug);
        if ($this->getListaTrabalhos()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui Trabalhos cadastrada.");
        }
    }
    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getCodInscritoTrabalho()) or $this->getCodInscritoTrabalho() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Trabalho associada ao inscrito sucesso.");
            } else {
                throw new Exception("Trabalho não foi associada ao inscrito.");
            }
        } else {
            $this->InscritoTrabalhoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Trabalho associada ao inscrito atualizada com sucesso.");
            } else {
                throw new Exception("Trabalho associada ao inscrito não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Trabalho cód:" . $this->getCodTrabalho() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Trabalho cód:" . $this->getCodTrabalho() . " não foi excluída.");
        }
    }

    public function getInscritoTrabalhoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    
    public function carregarTrabalhoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

?>