<?php

class ModeloContrato extends ModeloContratoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ModeloContratoDAO();
    }

    public function modeloContratoExiste() {
        if (!$this->getCodModeloContrato()) {
            throw new Exception('Código do Modelo de Contrato inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Modelo de Contrato foi encontrado para o código informado: " . $this->getCodModeloContrato() . ".");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodModeloContrato()) or $this->getCodModeloContrato() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Modelo de Contrato salvo com sucesso.");
            } else {
                throw new Exception("Modelo de Contrato não foi salvo.");
            }
        } else {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Modelo de Contrato atualizado com sucesso.");
            } else {
                throw new Exception("Modelo de Contrato não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Modelo de Contrato cód:" . $this->getCodModeloContrato() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Modelo de Contrato cód:" . $this->getCodModeloContrato() . " não foi excluído.");
        }
    }

    public function carregarModeloContratoById($debug = false) {
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaModeloContratoByEvento($debug = false) {
        return $this->objDAO->carregarListaByEvento($this, $debug);
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }
}

?>