<?php
class BloqueioAvaliacaoVO extends PadraoVO
{
    public function validatorObj(){
        $message = "";
        $return  = true;

        if($this->isNullOrEmpty($this->getMes())){
            $message .= "Mês não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getAno())){
            $message .= "Ano não informado.<br>";
            $return = false;
        }
        if(!$this->isNullOrEmpty($this->getMes()) && ($this->getMes() < 1 || $this->getMes() > 12)){
            $message .= "Mês inválido.<br>";
            $return = false;
        }
        if($return == false){
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorExcluir(){
        $message = "";
        $return  = true;

        if(is_null($this->getCodEvento()) or $this->getCodEvento() == ""){
            $message .= "Cód.Evento não foi informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getCodBloqueio())){
            $message .= "Cód.Bloqueio não informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
}
