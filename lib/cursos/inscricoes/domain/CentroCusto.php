<?php

class CentroCusto extends CentroCustoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new CentroCustoDAO();
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function CentroCustoExiste() {
        if (!$this->getCodCentroCusto()) {
            throw new Exception('Código do Centro de Custo inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Centro de Custo foi encontrada para o código informado: " . $this->getCodCentroCusto() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodCentroCusto()) or $this->getCodCentroCusto() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Centro de Custo salva com sucesso.");
            } else {
                throw new Exception("Novo Centro de Custo não foi salva.");
            }
        } else {
            $this->CentroCustoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Centro de Custo foi atualizado com sucesso.");
            } else {
                throw new Exception("Centro de Custo atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
            $excluidoCentroCusto          = $this->objDAO->delete($this, $debug);
            if ($excluidoCentroCusto) {
                throw new OKException("Centro de Custo cód:" . $this->getCodCentroCusto() . " foi excluída com sucesso.");
            } else {
                throw new Exception("Centro de Custo cód:" . $this->getCodCentroCusto() . " não foi excluída.");
            }
        }
    }

    public function getCentroCustoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaCentroCusto($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaCentroCustoByIdEvento($this, $condicoes, $debug);
        if ($this->getListaCentroCusto()) {
        } else {
            throw new Exception("Evento não possui Centro de Custo cadastrado.");
        }
    }

    public function carregarListaCentroCustoPai($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaCentroCustoByIdEventoPai($this, $condicoes, $debug);
    }
}
