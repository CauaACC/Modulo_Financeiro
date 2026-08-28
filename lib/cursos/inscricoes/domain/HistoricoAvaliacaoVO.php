<?php
class HistoricoAvaliacaoVO extends PadraoVO
{   
    public function getDesAvaliacao() {
        if ($this->getAvaliacao() == 'SELECIONADO'){
            return "EM AVALIAÇÃO";
        }
        return $this->getAvaliacao();
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
    public function validarLiberarAvaliador(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Cód. evento não foi informado.<br>";
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
        if(is_null($this->getCodAvaliador())or$this->getCodAvaliador()==""){
            $message .= "Cód. avaliador não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorExcluir(){
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