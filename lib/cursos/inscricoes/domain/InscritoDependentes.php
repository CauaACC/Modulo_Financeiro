<?php

class InscritoDependentes extends InscritoDependentesVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new InscritoDependentesDAO();
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function InscritoDependentesExiste() {
        if (!$this->getCodDependente()) {
            throw new Exception('Código do Dependente inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Código de Dependente foi encontrada para o código informado: " . $this->getCodDependente() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function salvar($debug = false) {
        
        $this->validatorSalvar();
        if (is_null($this->getCodDependente()) or $this->getCodDependente() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Cadastro de Dependente salvo com sucesso.");
            } else {
                throw new Exception("Novo Cadastro de Dependente não foi salvo.");
            }
        } else {
            $this->InscritoDependentesExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Cadastro de Dependente foi atualizado com sucesso.");
            } else {
                throw new Exception("Cadastro de Dependente atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
            $excluidoInscritoDependentes = $this->objDAO->delete($this, $debug);
            if ($excluidoInscritoDependentes) {
                throw new OKException("Cadastro de Dependente cód:" . $this->getCodDependente() . " foi excluída com sucesso.");
            } else {
                throw new Exception("Cadastro de Dependente cód:" . $this->getCodDependente() . " não foi excluída.");
            }
        }
    }

    public function getInscritoDependentesById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaInscritoDependentes($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaInscritoDependentesByIdEvento($this, $condicoes, $debug);
        if ($this->getListaInscritoDependentes()) {
        } else {
            throw new Exception("Evento não possui Centro de Custo cadastrado.");
        }
    }
}
