<?php
class EnsalamentoInscritoVO extends PadraoVO
{
    public function validarSalvar(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento()) or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito()) or $this->getCodInscrito()==""){
            $message .= "Código do inscrito não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
}?>
