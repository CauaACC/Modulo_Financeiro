<?php
class MsgAvisoVO extends PadraoVO
{
    public function validatorObj(){
        $message = "";
        $return  = true;

        if($this->isNullOrEmpty($this->getAssunto())){
            $message .= "Assunto não informado.<br>";
            $return = false; 
        }                 
        if($this->isNullOrEmpty($this->getCorpo())){
            $message .= "Mensagem não informado.<br>";
            $return = false; 
        }
        if($this->getDataPublicacaoFim() < $this->getDataPublicacaoIni() && $this->getDataPublicacaoFim() != NULL){
            $message .= "Data final não pode ser menor que a data incial.<br>";
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
        if($this->isNullOrEmpty($this->getCodMsgAviso())){
            $message .= "Cód.MsgAvisos não informado.<br>";
            $return = false; 
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }

}