<?php
class LiberacaoRegistroVO extends PadraoVO
{
    public function validatorObj(){
        $message = "";
        $return  = true;

        if($this->isNullOrEmpty($this->getCodInscrito())){
            $message .= "Inscrito não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getDataInicio())){
            $message .= "Data início não informada.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getDataFim())){
            $message .= "Data fim não informada.<br>";
            $return = false;
        }
        if(!$this->isNullOrEmpty($this->getDataInicio()) && !$this->isNullOrEmpty($this->getDataFim())){
            if($this->getDataFim() < $this->getDataInicio()){
                $message .= "Data fim não pode ser menor que a data início.<br>";
                $return = false;
            }
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
        if($this->isNullOrEmpty($this->getCodLiberacao())){
            $message .= "Cód.Liberação não informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }

}
