<?php

class MunicipioVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getNome()) or $this->getNome() == "") {
            $message .= "Nome do município não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getUf()) or $this->getUf() == "") {
            $message .= "UF não foi informado.<br>";
            $return = false;
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
        if (is_null($this->getMunicipioId()) or $this->getMunicipioId() == "") {
            $message .= "ID do município não foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}
