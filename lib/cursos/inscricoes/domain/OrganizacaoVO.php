<?php
class OrganizacaoVO extends PadraoVO
{   

    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getEventoId())or$this->getEventoId()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getInscritoId())or$this->getInscritoId()==""){
            $message .= "Cód. inscrito não foi informado.<br>";
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
        
        $this->validatorObj();
        
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}