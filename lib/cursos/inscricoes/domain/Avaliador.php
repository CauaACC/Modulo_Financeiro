<?php

class Avaliador extends AvaliadorVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new AvaliadorDAO();
    }

    public function AvaliadorExiste() {
        if (!$this->getCodAvaliador()) {
            throw new Exception('Cód. Avaliador inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Avaliador foi encontrado para o código informado: " . $this->getCodAvaliador() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodAvaliador()) or $this->getCodAvaliador() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Avaliador salvo com sucesso.");
            } else {
                throw new Exception("Novo Avaliador não foi salvo.");
            }
        } else {
            $this->AvaliadorExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Avaliador foi atualizado com sucesso.");
            } else {
                throw new Exception("Avaliador atual não foi atualizado.");
            }
        }
    }
    
    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Avaliador cód:" . $this->getCodAvaliador() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Avaliador cód:" . $this->getCodAvaliador() . " não foi excluído.");
        }
    }

    public function getAvaliadorById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }
    public function getAvaliadorByIdPremiacao($debug = false) {
        return $this->objDAO->getByIdPremiacao($this, $debug);
    }
    
    public function carregarObjLogin($debug = false) {
        $obj = new Usuario();
//        $obj->setCodConvidado($this->getCodConvidado());
//        $obj->getLoginByCodConvidado($debug);
        $obj->setCodUsuario($this->getCodUsuario());
        $obj->getLoginByCodUsuario($debug);
        $this->setObjLogin($obj);
    }

    public function carregarListaAvaliadoresByIdEvento($debug = false) {
        $this->objDAO->carregarListaAvaliadoresByIdEvento($this, $debug);
        if ($this->getListaAvaliadores()) {
            return true;
        } else {
            throw new Exception("Evento não possui Avaliadores cadastrados.");
        }
    }
    public function carregarListaAvaliadoresPremiacaoByIdEvento($debug = false) {
        $this->objDAO->carregarListaAvaliadoresPremiacaoByIdEvento($this, $debug);
        if ($this->getListaAvaliadores()) {
            return true;
        } else {
            throw new Exception("Evento não possui Avaliadores para Premiação cadastrados.");
        }
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

?>