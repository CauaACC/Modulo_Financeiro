<?php

class Associado extends AssociadoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new AssociadoDAO();
    }

    public function AssociadoExiste() {
        if (!$this->getCodAssociado()) {
            throw new Exception('Código do associado inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Associado foi encontrada para o código informado: " . $this->getCodAssociado() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodAssociado()) or $this->getCodAssociado() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Associado salva com sucesso.");
            } else {
                throw new Exception("Novo Associado não foi salva.");
            }
        } else {
            $this->AssociadoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Associado foi atualizada com sucesso.");
            } else {
                throw new Exception("Associado atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Associado cód:" . $this->getCodAssociado() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Associado cód:" . $this->getCodAssociado() . " não foi excluída.");
        }
    }

    public function getAssociadoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    
    public function carregarAssociadoByCpf($debug = false) {
        $this->objDAO->getByCpf($this, $debug);
    }

    public function getListaAssociadosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaAssociadosByIdEvento($this, $debug);
        if ($this->getListaAssociados()) {
            return $this->getListaAssociados();
        } else {
            throw new Exception("Evento não possui Associado cadastrada.");
        }
    }

}

