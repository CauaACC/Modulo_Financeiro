<?php

class AlunoPublicoVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getNomeCompleto()) or trim($this->getNomeCompleto()) == "") {
            $message .= "Informe o Nome Completo do aluno.<br>";
            $return = false;
        }
        if (is_null($this->getDataNascimento()) or $this->getDataNascimento() == "") {
            $message .= "Informe a Data de Nascimento do aluno.<br>";
            $return = false;
        }
        if (is_null($this->getNivel()) or $this->getNivel() == "") {
            $message .= "Informe o Nivel do aluno.<br>";
            $return = false;
        }
        if (is_null($this->getSerieTurma()) or $this->getSerieTurma() == "") {
            $message .= "Informe a Serie/Turma do aluno.<br>";
            $return = false;
        }
        if (is_null($this->getTurno()) or $this->getTurno() == "") {
            $message .= "Informe o Turno do aluno.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorExcluir() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodAluno()) or $this->getCodAluno() == "") {
            $message .= "Codigo do aluno nao informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}
