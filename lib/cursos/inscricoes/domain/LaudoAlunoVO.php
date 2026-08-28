<?php

class LaudoAlunoVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodAluno()) or $this->getCodAluno() == "") {
            $message .= "Codigo do aluno nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getDataLaudo()) or $this->getDataLaudo() == "") {
            $message .= "Informe a Data do Documento.<br>";
            $return = false;
        }
        if (is_null($this->getFinalidade()) or $this->getFinalidade() == "") {
            $message .= "Informe a Finalidade do Documento.<br>";
            $return = false;
        }
        if (is_null($this->getDescricao()) or trim($this->getDescricao()) == "") {
            $message .= "Informe a Descricao do Documento.<br>";
            $return = false;
        }
        if (is_null($this->getConclusao()) or trim($this->getConclusao()) == "") {
            $message .= "Informe a Conclusao do Documento.<br>";
            $return = false;
        }
        if (is_null($this->getRecomendacao()) or trim($this->getRecomendacao()) == "") {
            $message .= "Informe a Recomendacao do Documento.<br>";
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
        if (is_null($this->getCodLaudo()) or $this->getCodLaudo() == "") {
            $message .= "Codigo do Documento nao informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}
