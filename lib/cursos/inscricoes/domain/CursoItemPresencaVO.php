<?php

class CursoItemPresencaVO extends PadraoVO {

    public function validatorRegistrar() {
        $message = "";
        $return = true;
        if (!$this->getCodCursoItem()) {
            $message .= "Item não informado.<br>";
            $return = false;
        }
        if (!$this->getCodInscrito()) {
            $message .= "Inscrito não informado.<br>";
            $return = false;
        }
        $tiposValidos = array('INICIO','PING','CONFIRMACAO_OK','CONFIRMACAO_EXPIRADA','PAUSA','RETOMADA','FIM','FORA_HORARIO');
        if (!in_array($this->getTipo(), $tiposValidos)) {
            $message .= "Tipo de evento de presença inválido.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }
}
