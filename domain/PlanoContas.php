<?php

class PlanoContas extends PlanoContasVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new PlanoContasDAO();
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function PlanoContasExiste() {
        if (!$this->getCodPlanoContas()) {
            throw new Exception('Código do Plano de Contas inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Plano de Contas foi encontrada para o código informado: " . $this->getCodPlanoContas() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function salvar($debug = false) {
        
        $this->validatorSalvar();
        if (is_null($this->getCodPlanoContas()) or $this->getCodPlanoContas() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Plano de Contas salva com sucesso.");
            } else {
                throw new Exception("Novo Plano de Contas não foi salva.");
            }
        } else {
            $this->PlanoContasExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Plano de Contas foi atualizado com sucesso.");
            } else {
                throw new Exception("Plano de Contas atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
            $excluidoPlanoContas          = $this->objDAO->delete($this, $debug);
            if ($excluidoPlanoContas) {
                throw new OKException("Plano de Contas cód:" . $this->getCodPlanoContas() . " foi excluída com sucesso.");
            } else {
                throw new Exception("Plano de Contas cód:" . $this->getCodPlanoContas() . " não foi excluída.");
            }
        }
    }

    public function getPlanoContasById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaPlanoContas($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaPlanoContasByIdEvento($this, $condicoes, $debug);
        if ($this->getListaPlanoContas()) {
        } else {
            throw new Exception("Evento não possui Plano de Contas cadastrado.");
        }
    }
    public function carregarListaPlanoContasDebito($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaPlanoContasByIdEventoDebito($this, $condicoes, $debug);
    }
    public function carregarListaPlanoContasCredito($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaPlanoContasByIdEventoCredito($this, $condicoes, $debug);
    }
    public function carregarListaPlanoContasPai($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaPlanoContasByIdEventoPai($this, $condicoes, $debug);
    }
}
