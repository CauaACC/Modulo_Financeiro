<?php

class Desconto extends DescontoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new DescontoDAO();
    }

    function cerregarDescontoTotal($debug=false) {
        $this->objDAO->carregarDescontoTotal($this, $debug);
    }
    
    public function DescontoExiste() {
        if (!$this->getCodDesconto()) {
            throw new Exception('Código da Desconto inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Desconto foi encontrada para o código informado: " . $this->getCodDesconto() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodDesconto()) or $this->getCodDesconto() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Desconto salva com sucesso.");
            } else {
                throw new Exception("Novo Desconto não foi salvo.");
            }
        } else {
            $this->DescontoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Desconto foi atualizado com sucesso.");
            } else {
                throw new Exception("Desconto atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorEcluir()){
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Desconto cód:" . $this->getCodDesconto() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Desconto cód:" . $this->getCodDesconto() . " não foi excluído.");
        }
        }
    }

    public function getDescontoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    public function getDescontoByCpf($debug = false) {
        $this->objDAO->getByCpf($this, $debug);
    }

    public function carregarListaDesconto($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaDescontoByIdEvento($this, $condicoes, $debug);
        if ($this->getListaDesconto()) {
        } else {
            throw new Exception("Evento não possui Desconto cadastrado.");
        }
    }
    
    public function getListaDescontosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaDescontoByIdEvento($this, $debug);
        if ($this->getListaDescontos()) {
            return $this->getListaDescontos();
        } else {
            throw new Exception("Evento não possui Desconto cadastrada.");
        }
    }
    public function carregarListaDescontosByIdEvento($debug = false) {
        $this->objDAO->carregarListaDescontoByIdEvento($this, $debug);
        if ($this->getListaDescontos()) {
        } else {
            throw new Exception("Evento não possui Desconto cadastrada.");
        }
    }
    
    public function carregarListaPerfil($condicoes=false, $debug = false) {
        $obj= new InscritoPerfil();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaPerfilByIdInscrito($debug);
        $this->setListaPerfil($obj->getListaPerfil());
        if ($this->getListaPerfil()) {
            
        } else {
            throw new Exception("Desconto não possui perfis cadastrados.");
        }
    }

    public function getEvento() {
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        $obj = new Evento();
        return $obj;
    }

}

