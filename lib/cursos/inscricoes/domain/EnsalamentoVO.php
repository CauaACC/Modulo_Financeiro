<?php
class EnsalamentoVO extends PadraoVO
{   
    public function validarAdicionar(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodSessao()) or $this->getCodSessao()==""){
            $message .= "Código do usuário não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodTrabalho()) or $this->getCodTrabalho()==""){
            $message .= "Código do grupo não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validarRemover(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodSessao()) or $this->getCodSessao()==""){
            $message .= "Código do usuário não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodTrabalho()) or $this->getCodTrabalho()==""){
            $message .= "Código do grupo não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>