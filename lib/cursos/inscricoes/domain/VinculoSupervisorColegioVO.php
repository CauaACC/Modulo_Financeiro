<?php

class VinculoSupervisorColegioVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodSupervisor()) or $this->getCodSupervisor() == "") {
            $message .= "Informe o supervisor.<br>";
            $return = false;
        }
        if (is_null($this->getCodColegio()) or $this->getCodColegio() == "") {
            $message .= "Informe o col&eacute;gio.<br>";
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
        if (is_null($this->getCodVinculo()) or $this->getCodVinculo() == "") {
            $message .= "Codigo do vinculo nao informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}
