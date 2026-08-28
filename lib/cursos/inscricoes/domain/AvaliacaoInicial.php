<?php

class AvaliacaoInicial extends AvaliacaoInicialVO {

    private $objDAO;

    function __construct() {
        if ((!isset($_SESSION['_cod_inscrito']) || empty($_SESSION['_cod_inscrito'])) and (!isset($_SESSION['_cod_usuario']) || empty($_SESSION['_cod_usuario']))) {
            throw new Exception("Usuario nao esta logado. Por favor, faca login e tente novamente.");
        }
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new AvaliacaoInicialDAO();
    }

    public function AvaliacaoInicialExiste() {
        if (!$this->getCodAvaliacao()) {
            throw new Exception('Codigo da avaliacao invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Avaliacao foi encontrada para o codigo informado: " . $this->getCodAvaliacao() . ".");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodAvaliacao()) or $this->getCodAvaliacao() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Avaliacao de Repertorio salva com sucesso.");
            } else {
                throw new Exception("Nova Avaliacao de Repertorio nao foi salva.");
            }
        } else {
            $this->AvaliacaoInicialExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Avaliacao de Repertorio foi atualizada com sucesso.");
            } else {
                throw new Exception("Avaliacao de Repertorio nao foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Avaliacao cod:" . $this->getCodAvaliacao() . " foi excluida com sucesso.");
        } else {
            throw new Exception("Avaliacao cod:" . $this->getCodAvaliacao() . " nao foi excluida.");
        }
    }

    public function getAvaliacaoInicialById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getAlunosComStatusPorProgramacao($tipoAvaliacao, $dataLiberacao, $dataFechamento, $debug = false) {
        return $this->objDAO->carregarAlunosComStatusPorProgramacao($this->getCodEvento(), $tipoAvaliacao, $dataLiberacao, $dataFechamento, $debug);
    }

    public function getListaAvaliacoesByCodAluno($debug = false) {
        if (!$this->getCodAluno()) {
            throw new Exception("Codigo do aluno nao informado.");
        }
        $this->objDAO->carregarListaAvaliacoesByCodAluno($this, $debug);
        if ($this->getListaAvaliacoes()) {
            return $this->getListaAvaliacoes();
        } else {
            throw new Exception("");
        }
    }

}
