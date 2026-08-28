<?php
class InteressadoVO extends PadraoVO
{
    public function validatorObj(){
        $message = "";
        $return  = true;

        if(is_null($this->getCodEvento()) or $this->getCodEvento() == ""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getNomeInteressado())){
            $message .= "Nome do interessado não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getEmail())){
            $message .= "Email não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getTelefone())){
            $message .= "Telefone não informado.<br>";
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
        if($this->isNullOrEmpty($this->getCodInteressado())){
            $message .= "Cód.Interessados não informado.<br>";
            $return = false; 
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }

}