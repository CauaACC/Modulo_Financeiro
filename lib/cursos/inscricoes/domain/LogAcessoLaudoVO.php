<?php

class LogAcessoLaudoVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodLaudo()) or $this->getCodLaudo() == "") {
            $message .= "Codigo do laudo nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getAcao()) or $this->getAcao() == "") {
            $message .= "Acao nao foi informada.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorExcluir() {
        return true;
    }

}
