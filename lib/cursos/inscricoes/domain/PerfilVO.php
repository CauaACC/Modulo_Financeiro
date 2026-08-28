<?php
class PerfilVO extends PadraoVO
{   
    public function getTotalInscritosPerfil1(){
        if($this->totalInscritosPerfil1!=''){
            return $this->totalInscritosPerfil1;
        }
        return 0;
    }
    public function getTotalInscritosPerfil2(){
        if($this->totalInscritosPerfil2!=''){
            return $this->totalInscritosPerfil2;
        }
        return 0;
    }
    public function getDesOrdem(){
        return lpad($this->getOrdem(),2,0);
    }
    public function getDesStatus(){
        if(rtrim($this->getStatus())=='ATIVADA' or rtrim($this->getStatus())=='ATIVADO'){
            return "A";
        }else{
            return "D";
        }
    }
    public function carregarPerfilById($debug = false){
        $this->getPerfilById($debug);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getTitulo())or$this->getTitulo()==""){
            $message .= "Informe um titulo para o perfil.<br>";
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
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodPerfil()) or $this->getCodPerfil()==""){
            $message .= "Código da perfil não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}