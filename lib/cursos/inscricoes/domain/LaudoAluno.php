<?php

class LaudoAluno extends LaudoAlunoVO {

    private $objDAO;

    function __construct() {
        if ((!isset($_SESSION['_cod_inscrito']) || empty($_SESSION['_cod_inscrito'])) and (!isset($_SESSION['_cod_usuario']) || empty($_SESSION['_cod_usuario']))) {
            throw new Exception("Usuario nao esta logado. Por favor, faca login e tente novamente.");
        }
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new LaudoAlunoDAO();
    }

    public function LaudoAlunoExiste() {
        if (!$this->getCodLaudo()) {
            throw new Exception('Codigo do laudo invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Laudo foi encontrado para o codigo informado: " . $this->getCodLaudo() . ".");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodLaudo()) or $this->getCodLaudo() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Laudo salvo com sucesso.");
            } else {
                throw new Exception("Novo Laudo nao foi salvo.");
            }
        } else {
            $this->LaudoAlunoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Laudo foi atualizado com sucesso.");
            } else {
                throw new Exception("Laudo atual nao foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Laudo cod:" . $this->getCodLaudo() . " foi excluido com sucesso.");
        } else {
            throw new Exception("Laudo cod:" . $this->getCodLaudo() . " nao foi excluido.");
        }
    }

    public function getLaudoAlunoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaLaudosByCodAluno($debug = false) {
        if (!$this->getCodAluno()) {
            throw new Exception("Codigo do aluno nao informado.");
        }
        $this->objDAO->carregarListaLaudosByCodAluno($this, $debug);
        if ($this->getListaLaudos()) {
            return $this->getListaLaudos();
        } else {
            throw new Exception("O aluno nao possui Laudos cadastrados.");
        }
    }

}
