<?php

class EventoVO extends PadraoVO
{   
    public function getUrlPrefixo(){
        if(isset($this->urlPrefixo) and $this->urlPrefixo!=''){
            return $this->urlPrefixo;
        }else{
            if($this->getGestor()!='' AND $this->getCodEvento()!=''){
                return strtolower($this->getGestor()).'/'.$this->getCodEvento();
            }
        }
    }
    
    public function getNomeEvento(){
        return $this->getTitulo();
    }
    
    public function getTituloEvento(){
        return $this->getTitulo();
    }
    
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getTitulo())or$this->getTitulo()==""){
            $message .= "Informe o titulo do evento.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    
    public function validatorIncluir(){
        $message = "";
        $return  = true;
        if(is_null($this->getTitulo())or$this->getTitulo()==""){
            $message .= "Informe o titulo do evento.<br>";
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
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
}