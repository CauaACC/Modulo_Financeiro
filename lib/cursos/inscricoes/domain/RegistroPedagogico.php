<?php

class RegistroPedagogico extends RegistroPedagogicoVO {

    private $objDAO;

    function __construct() {
        if ((!isset($_SESSION['_cod_inscrito']) || empty($_SESSION['_cod_inscrito']))and(!isset($_SESSION['_cod_usuario']) || empty($_SESSION['_cod_usuario']))) {
            throw new Exception("Usuario nao esta logado. Por favor, faca login e tente novamente.");
        }
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new RegistroPedagogicoDAO();
    }

    public function RegistroPedagogicoExiste() {
        if (!$this->getCodRegistro()) {
            throw new Exception('Codigo do registro pedagogico invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Registro Pedagogico foi encontrado para o codigo informado: " . $this->getCodRegistro() . ".");
        }
    }

    public function salvar($debug = false, $rascunho = false) {

        if ($rascunho) {
            $this->validatorObjRascunho();
            $msgInsert = "Rascunho salvo. Lembre de completar o registro depois.";
            $msgUpdate = "Rascunho atualizado. Lembre de completar o registro depois.";
        } else {
            $this->validatorObj();
            $msgInsert = "Registro Pedagogico salvo com sucesso.";
            $msgUpdate = "Registro Pedagogico foi atualizado com sucesso.";
        }

        if (is_null($this->getCodRegistro()) or $this->getCodRegistro() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException($msgInsert);
            } else {
                throw new Exception("Novo Registro Pedagogico nao foi salvo.");
            }
        } else {
            $this->RegistroPedagogicoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException($msgUpdate);
            } else {
                throw new Exception("Registro Pedagogico atual nao foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Registro Pedagogico cod:" . $this->getCodRegistro() . " foi excluido com sucesso.");
        } else {
            throw new Exception("Registro Pedagogico cod:" . $this->getCodRegistro() . " nao foi excluido.");
        }
    }

    public function getRegistroPedagogicoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaRegistroPedagogicosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento nao informado.");
        }
        $this->objDAO->carregarListaRegistroPedagogicosByIdEvento($this, $debug);
        if ($this->getListaRegistroPedagogicos()) {
            return $this->getListaRegistroPedagogicos();
        } else {
            throw new Exception("Evento nao possui Registro Pedagogico cadastrado.");
        }
    }

    public function getEstatisticasGerais($debug = false) {
        return $this->objDAO->carregarEstatisticasGerais($this->getCodEvento(), $debug);
    }

    public function salvarPresenca($debug = false) {
        $this->validatorPresenca();
        if (is_null($this->getCodRegistro()) or $this->getCodRegistro() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Presenca registrada com sucesso.");
            } else {
                throw new Exception("Presenca nao foi registrada.");
            }
        } else {
            $this->RegistroPedagogicoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Presenca atualizada com sucesso.");
            } else {
                throw new Exception("Presenca nao foi atualizada.");
            }
        }
    }

    public function getPresencaDia($data, $codSupervisor = null, $debug = false) {
        return $this->objDAO->carregarPresencaDia($this->getCodEvento(), $data, $codSupervisor, $debug);
    }

    public function getRegistroByAlunoData($debug = false) {
        return $this->objDAO->getByAlunoData($this, $debug);
    }

    public function getListaRegistroPedagogicosByCodAluno($debug = false) {
        if (!$this->getCodAluno()) {
            throw new Exception("Codigo do aluno nao informado.");
        }
        $this->objDAO->carregarListaRegistroPedagogicosByCodAluno($this, $debug);
        if ($this->getListaRegistroPedagogicos()) {
            return $this->getListaRegistroPedagogicos();
        } else {
            throw new Exception("O aluno nao possui Registro Pedagogico cadastrado.");
        }
    }

}
