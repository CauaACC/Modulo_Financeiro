<?php

class SistemaVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getNomeSistema()) or $this->getNomeSistema() == "") {
            $message .= "Informe o nome do sistema.<br>";
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
        if (is_null($this->getCodSistema()) or $this->getCodSistema() === "") {
            $message .= "C&oacute;digo do sistema n&atilde;o foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }
}
