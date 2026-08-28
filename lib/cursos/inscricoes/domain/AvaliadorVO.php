<?php
class AvaliadorVO extends PadraoVO
{   
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getNomeAvaliador())or$this->getNomeAvaliador()==""){
            $message .= "Nome do avaliador não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCpf())or$this->getCpf()==""){
            $message .= "Cpf não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getEMail())or$this->getEMail()==""){
            $message .= "E-mail não foi informado.<br>";
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
        if(is_null($this->getCodAvaliador()) or $this->getCodAvaliador()==""){
            $message .= "Código da ocorrência não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>