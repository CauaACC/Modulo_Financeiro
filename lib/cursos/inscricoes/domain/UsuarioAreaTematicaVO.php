<?php
class UsuarioAreaTematicaVO extends PadraoVO
{   
    public function validarAdicionar(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodUsuario()) or $this->getCodUsuario()==""){
            $message .= "Código do usuário não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodAreaTematica()) or $this->getCodAreaTematica()==""){
            $message .= "Código da áera temática não foi informado.<br>";
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
        if(is_null($this->getCodUsuario()) or $this->getCodUsuario()==""){
            $message .= "Código do usuário não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodAreaTematica()) or $this->getCodAreaTematica()==""){
            $message .= "Código da áera temática não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>