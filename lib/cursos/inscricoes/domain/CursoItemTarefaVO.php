<?php

class CursoItemTarefaVO extends PadraoVO {

    public function validatorSalvar() {
        $message = "";
        if (!$this->getCodCursoItem()) $message .= "Item não informado.<br>";
        if (!$this->getCodInscrito())  $message .= "Inscrito não informado.<br>";
        if (!$this->getArquivoSalvo()) $message .= "Arquivo não informado.<br>";
        if ($message) throw new Exception($message);
        return true;
    }
}
