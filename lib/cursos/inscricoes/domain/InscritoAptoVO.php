<?php
class InscritoAptoVO extends PadraoVO
{   
    public function getJaInscrito() {
        if($this->getCodInscricao()) {
            return "SIM";
        }
    }
    public function setNomeInscritoApto($nomeInscritoApto){
        $this->setNomeInscrito($nomeInscritoApto);
    }
    public function getNomeInscritoApto() {
        if($this->nomeInscrito==''){
            return $this->nomeParticipante;
        }
        return $this->nomeInscrito;
    }
    
    public function getCodInscritoApto() {
        if($this->codInscritoApto){
           return $this->codInscritoApto; 
        }
        return $this->codInscrito;
    }
    
    public function getCodInscrito() {
        if($this->codInscrito){
           return $this->codInscrito; 
        }
        return $this->codInscritoApto;
    }
    
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
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
        if(is_null($this->getCodInscrito()) or $this->getCodInscrito()==""){
            $message .= "Código do inscrito apto não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}