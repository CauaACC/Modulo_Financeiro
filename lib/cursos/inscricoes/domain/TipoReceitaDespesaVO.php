<?php
class TipoReceitaDespesaVO extends PadraoVO
{      
    public function validarSalvarObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getNomeTipoReceitaDespesa())or$this->getNomeTipoReceitaDespesa()==""){
            $message .= "Informe nome da providência.<br>";
            $return = false;
        }        
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validarEcluirObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodTipoReceitaDespesa()) or $this->getCodTipoReceitaDespesa()==""){
            $message .= "Código da providência não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>