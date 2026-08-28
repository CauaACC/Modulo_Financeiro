<?php

class ProgramacaoAvaliacaoVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getDescricao()) or trim($this->getDescricao()) == "") {
            $message .= "Informe a Descricao.<br>";
            $return = false;
        }
        if (is_null($this->getDataLiberacao()) or $this->getDataLiberacao() == "") {
            $message .= "Informe a Data de Liberacao.<br>";
            $return = false;
        }
        if (is_null($this->getDataFechamento()) or $this->getDataFechamento() == "") {
            $message .= "Informe a Data de Fechamento.<br>";
            $return = false;
        }
        if ($this->getDataLiberacao() && $this->getDataFechamento() && $this->getDataLiberacao() > $this->getDataFechamento()) {
            $message .= "A Data de Liberacao nao pode ser posterior a Data de Fechamento.<br>";
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
        if (is_null($this->getCodProgramacao()) or $this->getCodProgramacao() == "") {
            $message .= "Codigo da Programacao nao informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}
