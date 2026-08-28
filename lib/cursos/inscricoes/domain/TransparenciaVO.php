<?php
class TransparenciaVO extends PadraoVO
{   

    public function setDatTrabalho($datTrabalho) {
        $this->datTrabalho = formatDateInsert($datTrabalho);
    }
    public function validatorObjAdmin(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;        
    }

    public function validatorObj(){
        $message = "";
        $return  = true;
        $objEvConf = $this->getObjEventoConfiguracao($this->getCodEvento());
        if(is_null($this->getCodEvento())or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
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
        if(is_null($this->getCodEvento())or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getId()) or $this->getCodTrabalho()==""){
            $message .= "Código da ocorrência não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    

}