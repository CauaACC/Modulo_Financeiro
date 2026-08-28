<?php

class Convidado extends ConvidadoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ConvidadoDAO();
    }

    public function ConvidadoExiste() {
        if (!$this->getCodConvidado()) {
            throw new Exception('Cód. Convidado inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Convidado foi encontrado para o código informado: " . $this->getCodConvidado() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        echo $debug? "salvando convidado<br>" : "";
        $this->validatorObj();
        echo $debug? "salvamento<br>" : "";
        if ((is_null($this->getCodConvidado()) or $this->getCodConvidado() == "")or($this->getForcarCodConvidado()==1)) {
            echo $debug? "inserindo<br>" : "";
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Convidado salvo com sucesso.");
            } else {
                throw new Exception("Novo Convidado não foi salvo.");
            }
        } else {
            $this->ConvidadoExiste($debug);
            echo $debug? "atualizando<br>" : "";
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Convidado foi atualizado com sucesso.");
            } else {
                throw new Exception("Convidado atual não foi atualizado.");
            }
        }
    }
    
    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Convidado cód:" . $this->getCodConvidado() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Convidado cód:" . $this->getCodConvidado() . " não foi excluído.");
        }
    }

    public function getConvidadoById($debug = false) {
        if ($this->getCodConvidado()=='') {
            throw new Exception("Código do convidado não informado.");
        }
        return $this->objDAO->getById($this, $debug);
    }
    public function getConvidadoByCpf($debug = false) {
        if ($this->getCpf()=='') {
            throw new Exception("Cpf do convidado não informado.");
        }
        return $this->objDAO->getByCpf($this, $debug);
    }
    public function getConvidadoByEmail($debug = false) {
        if ($this->getEMail()=='') {
            throw new Exception("Email do convidado não informado.");
        }
        return $this->objDAO->getByEmail($this, $debug);
    }
    public function getConvidadoByEmailDesc($debug = false) {
        if ($this->getEMail()=='') {
            throw new Exception("Email do convidado não informado.");
        }
        return $this->objDAO->getByEmailDesc($this, $debug);
    }
    
    public function carregarObjLogin($debug = false) {
        $obj = new Usuario();
        $obj->setCodConvidado($this->getCodConvidado());
        $obj->getLoginByCodConvidado($debug);
        $this->setObjLogin($obj);
    }
    
    public function getObjLoginByIdUsuario($debug = false) {
        $obj = new Usuario();
        $obj->setCodUsuario($this->getCodUsuario());
        $obj->getUsuarioById($debug);
        return $obj;
    }

    public function getListaConvidadosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaConvidadosByIdEvento($this, $debug);
        if ($this->getListaConvidados()) {
            return $this->getListaConvidados();
        } else {
            throw new Exception("Evento não possui Convidado cadastrada.");
        }
    }
    
    public function carregarListaConvidados($condicoes = false , $debug=false){
        $this->objDAO->carregarListaConvidados($this, $condicoes, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

