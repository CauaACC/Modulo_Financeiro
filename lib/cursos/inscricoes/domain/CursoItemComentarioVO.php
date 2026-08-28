<?php

class CursoItemComentarioVO extends PadraoVO {

    public function validatorSalvar() {
        $message = "";
        if (!$this->getCodCursoItem()) $message .= "Item não informado.<br>";
        if (!trim((string) $this->getTexto())) $message .= "Texto do comentário é obrigatório.<br>";
        if (!$this->getCodInscrito() && !$this->getCodUsuario()) {
            $message .= "Comentário sem autor.<br>";
        }
        if ($message) throw new Exception($message);
        return true;
    }
}
