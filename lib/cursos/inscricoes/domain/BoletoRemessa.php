<?php

class BoletoRemessa extends BoletoRemessaVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new BoletoRemessaDAO();
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodBoletoRemessa()) or $this->getCodBoletoRemessa() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo de remessa salvo com sucesso.");
            } else {
                throw new Exception("Novo Boleto Remessa não foi salvo.");
            }
        } else {
            //$this->BoletoRemessaExiste($debug);
//            if ($this->objDAO->update($this, $debug)) {
//                throw new OKException("BoletoRemessa foi atualizado com sucesso.");
//            } else {
                throw new Exception("Arquivo de remessa atual não foi atualizado.");
//            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo de remessa cód:" . $this->getCodBoletoRemessa() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Arquivo de remessa cód:" . $this->getCodBoletoRemessa() . " não foi excluída.");
        }
    }

    public function getBoletoRemessaById($debug = false) {
        if($this->getCodEvento()==''){
            throw new Exception("codEvento não encontrado.");
        }
        if($this->getCodBoletoRemessa()==''){
            throw new Exception("Código do Arquivo de remessa não encontrado.");
        }    
        return $this->objDAO->getById($this, $debug);
    }

    public function getLastId($debug = false) {
        return $this->objDAO->getLastId($this, $debug);
    }

    public function getLastArquivoId($debug = false) {
        return $this->objDAO->getLastArquivoId($this, $debug);
    }

    public function carregarListaBoletoRemessas($debug = false) {
        $this->objDAO->carregarListaBoletoRemessasByIdEvento($this, $debug);
        if ($this->getListaBoletoRemessas()) {
        } else {
            throw new Exception("Evento não possui arquivos de remessa cadastrada.");
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