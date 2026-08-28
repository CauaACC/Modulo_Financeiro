<?php
class GestorVO extends PadraoVO
{   
    public function setDatGestor($datGestor) {
        $this->datGestor = formatDateInsert($datGestor);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodGestor())or$this->getCodGestor()==""){
            $message .= "Código do gestor não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getNomeGestor())or$this->getNomeGestor()==""){
            $message .= "Informe nome do gestor.<br>";
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
        if(is_null($this->getCodGestor()) or $this->getCodGestor()==""){
            $message .= "Código da codigo não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}