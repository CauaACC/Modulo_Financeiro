<?php
class ComunicadoEnvioVO extends PadraoVO
{   
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não informado.<br>";
            $return = false;
        }
        if(is_null($this->getTipo())or$this->getTipo()==""){
            $message .= "Tipo do comunicado não informado.<br>";
            $return = false;
        }
        if($this->getEnvioForma()=='EMAIL'){
            if(is_null($this->getDestinatarioEmail())or$this->getDestinatarioEmail()==""){
                $message .= "E-mail não informado.<br>";
                $return = false;
            }
        }
        if($this->getEnvioForma()=='NOTIFICACAO'){
            if(is_null($this->getDestinatarioEmail())or$this->getDestinatarioEmail()==""){
                $message .= "Fone não informado.<br>";
                $return = false;
            }
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
            $message .= "Código do evento não informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodComunicadoEnvio()) or $this->getCodComunicadoEnvio()==""){
            $message .= "Código não informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>