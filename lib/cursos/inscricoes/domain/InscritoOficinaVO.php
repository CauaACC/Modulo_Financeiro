<?php
class InscritoOficinaVO extends PadraoVO
{   
    public function getTituloCertificado(){
        if($this->tituloCertificado !=''){
            return $this->tituloCertificado;
        }
        if($this->tituloOficina !=''){
            return $this->tituloOficina;
        }
        return $this->titulo;
    }
    public function setDatInscritoOficina($datInscritoOficina) {
        $this->datInscritoOficina = formatDateInsert($datInscritoOficina);
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
        if(is_null($this->getCodOficina())or$this->getCodOficina()==""){
            $message .= "Cód. oficina não foi informado.<br>";
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
        
        $this->validatorObj();
        
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>