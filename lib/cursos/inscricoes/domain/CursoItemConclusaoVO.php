<?php

class CursoItemConclusaoVO extends PadraoVO {

    public function validatorRegistrar() {
        $message = "";
        if (!$this->getCodCursoItem()) $message .= "Item não informado.<br>";
        if (!$this->getCodInscrito()) $message .= "Inscrito não informado.<br>";
        $origensValidas = array('AUTOMATICA', 'MANUAL');
        if (!in_array($this->getOrigem(), $origensValidas)) $message .= "Origem inválida.<br>";
        if ($message) throw new Exception($message);
        return true;
    }
}
