<?php
class EmailFilaVO extends PadraoVO
{
    public function validatorObj(){
        $message = "";
        $return  = true;

        if($this->isNullOrEmpty($this->getEmailDestinatario())){
            $message .= "E-mail destinatário não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getAssunto())){
            $message .= "Assunto não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getCorpo())){
            $message .= "Corpo do e-mail não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getDatProgramado())){
            $message .= "Data programada não informada.<br>";
            $return = false;
        }
        if($return == false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
}
