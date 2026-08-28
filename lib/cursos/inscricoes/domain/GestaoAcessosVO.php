<?php
class GestaoAcessosVO extends PadraoVO
{
    public function validarAdicionarUsuarioEvento()
    {
        $message = "";
        $return  = true;
        if ($this->isNullOrEmpty($this->getCodEvento())) {
            $message .= "C&oacute;digo do evento n&atilde;o foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getCodUsuario())) {
            $message .= "C&oacute;digo do usu&aacute;rio n&atilde;o foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validarGestor()
    {
        if ($this->isNullOrEmpty($this->getGestor())) {
            throw new Exception("Gestor n&atilde;o foi informado.");
        }
        return true;
    }
}
