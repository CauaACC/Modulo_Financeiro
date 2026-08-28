<?php
class ConvidadoVO extends PadraoVO
{   
//    public function getEmail(){
//         if ($this->email!=''){
//             return $this->email;
//         }
//         return $this->getEMail();
//    }
//    public function setEmail($email){
//         $this->email = $email;
//         $this->setEMail($email);
//    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getNomeConvidado())or$this->getNomeConvidado()==""){
            $message .= "Nome do convidado não foi informado.<br>";
            $return = false;
        }
//        if(is_null($this->getCpf())or$this->getCpf()==""){
//            $message .= "Cpf não foi informado.<br>";
//            $return = false;
//        }
        if(is_null($this->getEMail())or$this->getEMail()==""){
            $message .= "E-mail não foi informado.<br>";
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
        if(is_null($this->getCodConvidado()) or $this->getCodConvidado()==""){
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