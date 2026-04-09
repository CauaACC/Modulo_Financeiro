<?php

class Rateios extends RateiosVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new RateiosDAO();
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function RateiosExiste() {
        if (!$this->getCodRateio()) {
            throw new Exception('Código do Rateio inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Rateio foi encontrada para o código informado: " . $this->getCodRateio() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodRateio()) or $this->getCodRateio() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Rateio salva com sucesso.");
            } else {
                throw new Exception("Novo Rateio não foi salva.");
            }
        } else {
            $this->RateiosExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Rateio foi atualizado com sucesso.");
            } else {
                throw new Exception("Rateio atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
            $excluidoRateios          = $this->objDAO->delete($this, $debug);
            if ($excluidoRateios) {
                throw new OKException("Rateio cód:" . $this->getCodRateio() . " foi excluída com sucesso.");
            } else {
                throw new Exception("Rateio cód:" . $this->getCodRateio() . " não foi excluída.");
            }
        }
    }

    public function getRateiosById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    public function carregarListaRateiosPai($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaRateiosByIdEventoPai($this, $condicoes, $debug);
    }

    public function carregarListaRateios($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaRateiosByIdEvento($this, $condicoes, $debug);
        if ($this->getListaRateios()) {
        } else {
            throw new Exception("Evento não possui Rateios cadastrados.");
        }
    }
}
