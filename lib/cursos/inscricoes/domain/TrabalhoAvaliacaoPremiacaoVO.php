<?php
class TrabalhoAvaliacaoPremiacaoVO extends PadraoVO
{   
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
        if(is_null($this->getCriterio1())or$this->getCriterio1()==""){
            $message .= "Critério 1 não informado.<br>";
            $return = false;
        }
        if(is_null($this->getCriterio2())or$this->getCriterio2()==""){
            $message .= "Critério 2 não informado.<br>";
            $return = false;
        }
        if(is_null($this->getCriterio3())or$this->getCriterio3()==""){
            $message .= "Critério 3 não informado.<br>";
            $return = false;
        }
        if(is_null($this->getCriterio4())or$this->getCriterio4()==""){
            $message .= "Critério 4 não informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorLiberacaoObj(){
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