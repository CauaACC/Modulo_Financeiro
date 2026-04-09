<?php

class LancamentosContabeis extends LancamentosContabeisVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new LancamentosContabeisDAO();
    }
    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function LancamentosContabeisExiste() {
        if (!$this->getCodLancamentosContabeis()) {
            throw new Exception('Código do Centro de Custo inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Centro de Custo foi encontrada para o código informado: " . $this->getCodLancamentosContabeis() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function salvar($debug = false) {
        
        $this->validatorSalvar();
        if (is_null($this->getCodLancamentosContabeis()) or $this->getCodLancamentosContabeis() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Centro de Custo salva com sucesso.");
            } else {
                throw new Exception("Novo Centro de Custo não foi salva.");
            }
        } else {
            $this->LancamentosContabeisExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Centro de Custo foi atualizado com sucesso.");
            } else {
                throw new Exception("Centro de Custo atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
            $excluidoLancamentosContabeis          = $this->objDAO->delete($this, $debug);
            if ($excluidoLancamentosContabeis) {
                throw new OKException("Centro de Custo cód:" . $this->getCodLancamentosContabeis() . " foi excluída com sucesso.");
            } else {
                throw new Exception("Centro de Custo cód:" . $this->getCodLancamentosContabeis() . " não foi excluída.");
            }
        }
    }

    public function getLancamentosContabeisById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaLancamentosContabeis($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaLancamentosContabeisByIdEvento($this, $condicoes, $debug);
        if ($this->getListaLancamentosContabeis()) {
        } else {
            throw new Exception("Evento não possui Centro de Custo cadastrado.");
        }
    }
}
