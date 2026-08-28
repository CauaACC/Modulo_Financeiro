<?php

class Login extends LoginVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new LoginDAO();
    }

    public function LoginExiste() {
        if (!$this->getCodLogin()) {
            throw new Exception('Cód. Login inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Login foi encontrado para o código informado: " . $this->getCodLogin() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodUsuario()) or $this->getCodUsuario() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Login salvo com sucesso.");
            } else {
                throw new Exception("Novo Login não foi salvo.");
            }
        } else {
            $this->LoginExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Login foi atualizado com sucesso.");
            } else {
                throw new Exception("Login atual não foi atualizado.");
            }
        }
    }
    
    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Login cód:" . $this->getCodLogin() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Login cód:" . $this->getCodLogin() . " não foi excluído.");
        }
    }

    public function getLoginByEmail($debug = false) {
        return $this->objDAO->getByEmail($this, $debug);
    }
    
    public function getLoginById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }
    
    public function getLoginByCodConvidado($debug = false) {
        return $this->objDAO->getByCodConvidado($this, $debug);
    }

    public function getListaLoginsByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaLoginsByIdEvento($this, $debug);
        if ($this->getListaLogins()) {
            return $this->getListaLogins();
        } else {
            throw new Exception("Evento não possui Login cadastrada.");
        }
    }
    
    public function carregarListaLogins($condicoes = false , $debug=false){
        $this->objDAO->carregarListaLogins($this, $condicoes, $debug);
    }
    
    public function carregarListaGruposByIdLogin($debug=false){
        $obj = new GrupoSistema();
        $obj->setCodUsuario($this->getCodUsuario());
        $obj->carregarListaGruposByIdLogin($debug);
        $this->setListaGrupos($obj->getListaGrupos());
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}
