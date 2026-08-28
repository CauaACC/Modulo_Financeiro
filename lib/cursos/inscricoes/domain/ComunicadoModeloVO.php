<?php
class ComunicadoModeloVO extends PadraoVO
{   
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCorpo())or$this->getCorpo()==""){
            $message .= "Informe um corpo para mensagem.<br>";
            $return = false;
        }
        if(is_null($this->getAssunto())or$this->getAssunto()==""){
            $message .= "Informe um assunto para a mensagem.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorEcluir(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodComunicadoModelo()) or $this->getCodComunicadoModelo()==""){
            $message .= "Código não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>