<?php

class Municipio extends MunicipioVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new MunicipioDAO();
    }

    public function MunicipioExiste() {
        if (!$this->getMunicipioId()) {
            throw new Exception('ID do município inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum município foi encontrado para o ID informado: " . $this->getMunicipioId() . ".");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getMunicipioId()) or $this->getMunicipioId() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Município salvo com sucesso.");
            } else {
                throw new Exception("Novo município não foi salvo.");
            }
        } else {
            $this->MunicipioExiste($debug);

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
            throw new OKException("Município ID:" . $this->getMunicipioId() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Município ID:" . $this->getMunicipioId() . " não foi excluído.");
        }
        }
    }

    public function getMunicipioById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaMunicipios($debug = false) {
        $this->objDAO->getAll($this, $debug);
        if ($this->getListaMunicipios()) {
            return $this->getListaMunicipios();
        } else {
            throw new Exception("Nenhum município cadastrado.");
        }
    }
}
