<?php

class IsentoVO extends PadraoVO {

    public function getJaInscrito() {
        if ($this->getCodInscricao()) {
            return "SIM";
        }
    }

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCpf()) or $this->getCpf() == "") {
//            $message .= "Cpf não foi informado.<br>";
//            $return = false;
            $this->setCpf('00000000000');
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorEcluir() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodIsento()) or $this->getCodIsento() == "") {
            $message .= "Código do isento não foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}

?>