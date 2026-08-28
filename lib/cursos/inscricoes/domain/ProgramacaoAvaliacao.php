<?php

class ProgramacaoAvaliacao extends ProgramacaoAvaliacaoVO {

    private $objDAO;

    function __construct() {
        if ((!isset($_SESSION['_cod_inscrito']) || empty($_SESSION['_cod_inscrito'])) and (!isset($_SESSION['_cod_usuario']) || empty($_SESSION['_cod_usuario']))) {
            throw new Exception("Usuario nao esta logado. Por favor, faca login e tente novamente.");
        }
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ProgramacaoAvaliacaoDAO();
    }

    public function ProgramacaoAvaliacaoExiste() {
        if (!$this->getCodProgramacao()) {
            throw new Exception('Codigo da programacao invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Programacao foi encontrada para o codigo informado: " . $this->getCodProgramacao() . ".");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodProgramacao()) or $this->getCodProgramacao() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Programacao salva com sucesso.");
            } else {
                throw new Exception("Nova Programacao nao foi salva.");
            }
        } else {
            $this->ProgramacaoAvaliacaoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Programacao foi atualizada com sucesso.");
            } else {
                throw new Exception("Programacao nao foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Programacao cod:" . $this->getCodProgramacao() . " foi excluida com sucesso.");
        } else {
            throw new Exception("Programacao cod:" . $this->getCodProgramacao() . " nao foi excluida.");
        }
    }

    public function getProgramacaoAvaliacaoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaProgramacoes($debug = false) {
        $this->objDAO->carregarListaProgramacoes($this, $debug);
        if ($this->getListaProgramacoes_()) {
            return $this->getListaProgramacoes_();
        } else {
            throw new Exception("");
        }
    }

}
