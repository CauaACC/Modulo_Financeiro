<?php

class GrupoVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getNomeGrupo()) or $this->getNomeGrupo() == "") {
            $message .= "Informe o nome do grupo.<br>";
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
        if (is_null($this->getCodGrupo()) or $this->getCodGrupo() === "") {
            $message .= "C&oacute;digo do grupo n&atilde;o foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }
}
