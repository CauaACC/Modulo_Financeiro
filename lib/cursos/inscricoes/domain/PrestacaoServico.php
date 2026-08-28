<?php

class PrestacaoServico extends PrestacaoServicoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new PrestacaoServicoDAO();
    }

    function carregarPrestacaoServicoTotal($debug=false) {
        $this->objDAO->carregarPrestacaoServicoTotal($this, $debug);
    }

    public function PrestacaoServicoExiste() {
        if (!$this->getCodPrestacaoServico()) {
            throw new Exception('Código da Prestação Serviço inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Prestação Serviço foi encontrada para o código informado: " . $this->getCodPrestacaoServico() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {

    }

    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getCodPrestacaoServico()) or $this->getCodPrestacaoServico() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Prestação Serviço salva com sucesso.");
            } else {
                throw new Exception("Novo Prestação Serviço não foi salvo.");
            }
        } else {
            $this->PrestacaoServicoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Prestação Serviço foi atualizado com sucesso.");
            } else {
                throw new Exception("Prestação Serviço atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
            if ($this->objDAO->delete($this, $debug)) {
                throw new OKException("Prestação Serviço cód:" . $this->getCodPrestacaoServico() . " foi excluído com sucesso.");
            } else {
                throw new Exception("Prestação Serviço cód:" . $this->getCodPrestacaoServico() . " não foi excluído.");
            }
        }
    }

    public function getPrestacaoServicoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    public function getPrestacaoServicoByCpf($debug = false) {
        $this->objDAO->getByCpf($this, $debug);
    }

    public function carregarListaPrestacaoServico($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaPrestacaoServicoByIdEvento($this, $condicoes, $debug);
        if ($this->getListaPrestacaoServico()) {
        } else {
            throw new Exception("Evento não possui Prestação Serviço cadastrado.");
        }
    }

    public function getListaPrestacaoServicosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaPrestacaoServicoByIdEvento($this, $debug);
        if ($this->getListaPrestacaoServicos()) {
            return $this->getListaPrestacaoServicos();
        } else {
            throw new Exception("Evento não possui Prestação Serviço cadastrada.");
        }
    }
    public function carregarListaPrestacaoServicosByIdEvento($debug = false) {
        $this->objDAO->carregarListaPrestacaoServicoByIdEvento($this, $debug);
        if ($this->getListaPrestacaoServicos()) {
        } else {
            throw new Exception("Evento não possui Prestação Serviço cadastrado.");
        }
    }

    public function carregarListaPerfil($condicoes=false, $debug = false) {
        $obj= new InscritoPerfil();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaPerfilByIdInscrito($debug);
        $this->setListaPerfil($obj->getListaPerfil());
        if ($this->getListaPerfil()) {

        } else {
            throw new Exception("Prestação Serviço não possui perfis cadastrados.");
        }
    }

    public function getEvento() {
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        $obj = new Evento();
        return $obj;
    }
}