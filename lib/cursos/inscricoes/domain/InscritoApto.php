<?php

class InscritoApto extends InscritoAptoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new InscritoAptoDAO();
    }

    public function InscritoAptoExiste() {
        if (!$this->getCodInscritoApto()) {
            throw new Exception('Código da InscritoApto inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma InscritoApto foi encontrada para o código informado: " . $this->getCodInscritoApto() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }
    
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodInscritoApto()) or $this->getCodInscritoApto() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("InscritoApto salva com sucesso.");
            } else {
                throw new Exception("Novo InscritoApto não foi salva.");
            }
        } else {
            $this->inscritoaptoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("InscritoApto foi atualizada com sucesso.");
            } else {
                throw new Exception("InscritoApto atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("InscritoApto cód:" . $this->getCodInscritoApto() . " foi excluída com sucesso.");
        } else {
            throw new Exception("InscritoApto cód:" . $this->getCodInscritoApto() . " não foi excluída.");
        }
    }

    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("InscritoApto cód:" . $this->getCodInscritoApto() . " foi excluída com sucesso.");
        } else {
            throw new Exception("InscritoApto cód:" . $this->getCodInscritoApto() . " não foi excluída.");
        }
    }

    public function carregarInscritoAptoById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarInscritoAptoByCpf($condicoes=false,$debug = false) {
        return $this->objDAO->getByCpf($this, $condicoes, $debug);
    }

    public function carregarInscritoAptoByEmail($debug = false) {
        return $this->objDAO->getByEmail($this, $debug);
    }

    public function getListaInscritoAptosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaInscritoAptosByIdEvento($this, $debug);
        if ($this->getListaInscritoAptos()) {
            return $this->getListaInscritoAptos();
        } else {
            throw new Exception("Evento não possui InscritoApto cadastrada.");
        }
    }
    public function carregarListaInscritoAptosByIdEvento($debug = false) {
        $this->objDAO->carregarListaInscritoAptosByIdEvento($this, $debug);
        if ($this->getListaInscritoAptos()) {
        } else {
            throw new Exception("Evento não possui InscritoApto cadastrada.");
        }
    }

    function carregarListaInscritoAptosBy($condicoes, $debug = false){
        $this->objDAO->carregarListaInscritoAptosBy($this, $condicoes, $debug);
        if ($this->getListaInscritoAptos()) {
        } else {
            throw new Exception("Consulta não retornou registros de InscritoApto.");
        }        
    }
    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }
        
}
