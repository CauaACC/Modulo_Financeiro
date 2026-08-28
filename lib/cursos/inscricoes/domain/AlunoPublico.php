<?php

class AlunoPublico extends AlunoPublicoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new AlunoPublicoDAO();
    }

    public function AlunoPublicoExiste() {
        if (!$this->getCodAluno()) {
            throw new Exception('Codigo do aluno invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum aluno foi encontrado para o codigo informado: " . $this->getCodAluno() . ".");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodAluno()) or $this->getCodAluno() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Aluno salvo com sucesso.");
            } else {
                throw new Exception("Novo aluno nao foi salvo.");
            }
        } else {
            $this->AlunoPublicoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Aluno foi atualizado com sucesso.");
            } else {
                throw new Exception("Aluno atual nao foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Aluno cod:" . $this->getCodAluno() . " foi excluido com sucesso.");
        } else {
            throw new Exception("Aluno cod:" . $this->getCodAluno() . " nao foi excluido.");
        }
    }

    public function getAlunoPublicoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaAlunoPublicosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento nao informado.");
        }
        $this->objDAO->carregarListaAlunoPublicosByIdEvento($this, $debug);
        if ($this->getListaAlunoPublicos()) {
            return $this->getListaAlunoPublicos();
        } else {
            throw new Exception("Evento nao possui alunos cadastrados.");
        }
    }

    public function getListaAlunoPublicosBySupervisor($idInclusao, $debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento nao informado.");
        }
        $this->objDAO->carregarListaAlunoPublicosBySupervisor($this, $idInclusao, $debug);
        if ($this->getListaAlunoPublicos()) {
            return $this->getListaAlunoPublicos();
        } else {
            throw new Exception("Nenhum aluno cadastrado por este supervisor.");
        }
    }

}
