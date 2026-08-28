<?php

class Cidade extends CidadeVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new CidadeDAO();
    }

    public function CidadeExiste() {
        if (!$this->getCodCidade()) {
            throw new Exception('Código da Cidade inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Cidade foi encontrada para o código informado: " . $this->getCodCidade() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodCidade()) or $this->getCodCidade() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Cidade salva com sucesso.");
            } else {
                throw new Exception("Novo Cidade não foi salva.");
            }
        } else {
            $this->CidadeExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Cidade foi atualizada com sucesso.");
            } else {
                throw new Exception("Cidade atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Cidade cód:" . $this->getCodCidade() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Cidade cód:" . $this->getCodCidade() . " não foi excluída.");
        }
    }

    public function getCidadeById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaCidades($debug = false) {
        if ($this->objDAO->carregarCidadesByIdEstado($this, $debug)) {
            return true;
        } else {
            throw new Exception("Evento não possui Cidade cadastrada.");
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