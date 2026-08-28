<?php
class RetornoHutecVO extends PadraoVO
{
    public function validatorObj(){
        $message = "";
        $return  = true;

        if($this->isNullOrEmpty($this->getCodEvento())){
            $message .= "Cód.Evento não informado.<br>";
            $return = false; 
        } 
        if($return == false){
            throw new Exception($message);
            return false;
        }
        return true;

    }
    
    public function validatorExcluir(){
        $message = "";
        $return  = true;

        if(is_null($this->getCodEvento()) or $this->getCodEvento() == ""){
            $message .= "Cód.Evento não foi informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getCodRetornoHutec())){
            $message .= "Cód.RetornoHutec não informado.<br>";
            $return = false; 
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorExcluirCarga(){
        $message = "";
        $return  = true;

        if(is_null($this->getCodEvento()) or $this->getCodEvento() == ""){
            $message .= "Cód.Evento não foi informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getCodArquivo())){
            $message .= "Cód.Arquivo não informado.<br>";
            $return = false; 
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }

}