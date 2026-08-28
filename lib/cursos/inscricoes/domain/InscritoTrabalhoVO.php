<?php
class InscritoTrabalhoVO extends PadraoVO
{   
    public function setDatInscritoTrabalho($datInscritoTrabalho) {
        $this->datInscritoTrabalho = formatDateInsert($datInscritoTrabalho);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito())or$this->getCodInscrito()==""){
            $message .= "Cód. inscrito não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodTrabalho())or$this->getCodTrabalho()==""){
            $message .= "Cód. trabalho não foi informado.<br>";
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
}?>