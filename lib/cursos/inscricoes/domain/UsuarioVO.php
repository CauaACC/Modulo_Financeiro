<?php

class UsuarioVO extends PadraoVO
{   
    public function validarSenha() {
        $message = "";
        $return = true;
        $senha = $this->getSenhaNova();
        
        if (is_null($senha) || $senha == "") {
            $message .= "Senha não foi informada.<br>";
            $return = false;
        }
        if (strlen($senha) < 8) {
            $message .= "A senha deve ter pelo menos 8 caracteres.<br>";
            $return = false;
        }
        if (!preg_match('/[a-zA-Z]/', $senha)) {
            $message .= "A senha deve conter pelo menos uma letra.<br>";
            $return = false;
        }
        if (!preg_match('/[0-9]/', $senha)) {
            $message .= "A senha deve conter pelo menos um número.<br>";
            $return = false;
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $senha)) {
            $message .= "Incluir ao menos um caractere especial (ex.: @, #, $).<br>";
            $return = false;
        }
    
        if (!$return) {
            throw new Exception($message);
        }
    
        return true;
    }
    
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getLogin())or$this->getLogin()==""){
            $message .= "Nome do login não foi informado.<br>";
            $return = false;
        }
        if((is_null($this->getSenha())or$this->getSenha()=="") && (is_null($this->getSenhaHash())or$this->getSenhaHash()=="")){
            $message .= "Senha não foi informada.<br>";
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
        if(is_null($this->getCodUsuario()) or $this->getCodUsuario()==""){
            $message .= "Código do login não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}