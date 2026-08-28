<?php

class Organizacao extends OrganizacaoVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new OrganizacaoDAO();
    }
    public function OrganizacaoExiste($debug = false) {
        if (!$this->getInscritoId()) {
            throw new Exception('Código do Inscrito inválido ou não informado: '.$this->getInscritoId());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }
    public function carregarOrganizacaoByPrefixo($debug = false) {
        $this->objDAO->getByPrefixo($this, $debug);
    }
    public function carregarListaOrganizacaosByInscritoId($debug = false) {
        $this->objDAO->carregarListaOrganizacaosByInscritoId($this, $debug);
        if ($this->getListaOrganizacaos()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui ingressos associados.");
        }
    }
    public function carregarListaEventosByGestor($debug = false) {
        $obj = new Evento();
        $obj->setGestor($this->getSigla());
        if($obj->carregarListaEventosByGestor($debug)){
            $this->setListaEventos($obj->getListaEventos());
            return true;
        } else {
            throw new Exception("Gestor não possui eventos ativos.");
        }
    }
    public function carregarListaEventos($debug = false) {
        $obj = new Evento();
        if($obj->carregarListaEventos($debug)){
            $this->setListaEventos($obj->getListaEventos());
            return true;
        } else {
            throw new Exception("Lista de eventos não encontrada.");
        }
    }

    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getId()) or $this->getId() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Organizacao gravado com sucesso.");
            } else {
                throw new Exception("Organizacao não foi gravado com sucesso.");
            }
        } else {
            $this->OrganizacaoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Organizacao atualizado com sucesso.");
            } else {
                throw new Exception("Organizacao não foi atualizado.");
            }
        }
    }
    
    public function excluir($debug = false) {
        $this->validatorEcluir();
        $this->OrganizacaoExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Inscrito cód. " . $this->getInscritoId() . " foi excluído com sucesso");
        } else {
            throw new Exception("Inscrito cód. " . $this->getInscritoId() . " NÃO foi excluído");
        }
    }

    public function getOrganizacaoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getEventoId());
        $obj->getEventoById();
        return $obj;
    }

}
