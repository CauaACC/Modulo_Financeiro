<?php

class AvaliacaoInicialVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodAluno()) or $this->getCodAluno() == "") {
            $message .= "Selecione o Aluno.<br>";
            $return = false;
        }
        if (is_null($this->getDataAvaliacao()) or $this->getDataAvaliacao() == "") {
            $message .= "Informe a Data da Avaliacao.<br>";
            $return = false;
        }
        if (is_null($this->getNivelSuporte()) or trim($this->getNivelSuporte()) == "") {
            $message .= "Informe o Nivel de Suporte.<br>";
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
        if (is_null($this->getCodAvaliacao()) or $this->getCodAvaliacao() == "") {
            $message .= "Codigo da Avaliacao nao informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}
