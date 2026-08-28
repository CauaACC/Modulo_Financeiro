<?php

class Gestor extends GestorVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new GestorDAO();
    }


    public function GestorExiste() {
        if (!$this->getCodGestor()) {
            throw new Exception('Código do Gestor inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Gestor foi encontrada para o código informado: " . $this->getCodGestor() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getId()) or $this->getId() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Gestor salvo com sucesso.");
            } else {
                throw new Exception("Novo Gestor não foi salva.");
            }
        } else {
            $this->GestorExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Gestor foi atualizado com sucesso.");
            } else {
                throw new Exception("Gestor atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Gestor cód:" . $this->getCodGestor() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Gestor cód:" . $this->getCodGestor() . " não foi excluída.");
        }
    }

    public function getGestorById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarListaGestoresByCodUsuario($debug = false,$limit=false) {    
        if ($this->objDAO->getListaGestoresByCodUsuario($this, $debug,$limit)) {
            return true;
        } else {
            throw new Exception("Usuário não possui Gestor associado.");
        }
    }
    public function carregarListaGestoresByIdUsuario($debug = false,$limit=false) {
        if ($this->objDAO->getListaGestoresByIdUsuario($this, $debug,$limit)) {
            return true;
        } else {
            throw new Exception("Usuario não esta associado a Gestor cadastrado.".__LINE__);
        }
    }

}

