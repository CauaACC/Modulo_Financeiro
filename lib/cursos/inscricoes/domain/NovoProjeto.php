<?php

class NovoProjeto extends NovoProjetoVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new NovoProjetoDAO();
    }

    public function NovoProjetoExiste() {
        if (!$this->getNovoProjetoId()) {
            throw new Exception('ID do município inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum município foi encontrado para o ID informado: " . $this->getNovoProjetoId() . ".");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getNovoProjetoId()) or $this->getNovoProjetoId() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Município salvo com sucesso.");
            } else {
                throw new Exception("Novo município não foi salvo.");
            }
        } else {
            $this->NovoProjetoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Município foi atualizado com sucesso.");
            } else {
                throw new Exception("Município atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorEcluir()){
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Município ID:" . $this->getNovoProjetoId() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Município ID:" . $this->getNovoProjetoId() . " não foi excluído.");
        }
        }
    }

    public function getNovoProjetoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaNovoProjetos($debug = false) {
        $this->objDAO->getAll($this, $debug);
        if ($this->getListaNovoProjetos()) {
            return $this->getListaNovoProjetos();
        } else {
            throw new Exception("Nenhum município cadastrado.");
        }
    }
}
