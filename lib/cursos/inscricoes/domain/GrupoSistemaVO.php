<?php
class GrupoSistemaVO extends PadraoVO
{
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodGrupo())or$this->getCodGrupo()==""){
            $message .= "Código do grupo não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodSistema())or$this->getCodSistema()==""){
            $message .= "Código do sistema não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodDireito())or$this->getCodDireito()==""){
            $message .= "Código do direito não foi informado.<br>";
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
        if(is_null($this->getCodGrupo())or$this->getCodGrupo()==""){
            $message .= "Código do grupo não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodSistema())or$this->getCodSistema()==""){
            $message .= "Código do sistema não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodDireito())or$this->getCodDireito()==""){
            $message .= "Código do direito não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
}?>