<?php
class IngressoVO extends PadraoVO
{
    public function validatorObj(){
        $message = "";
        $return  = true;

        if(empty($_POST['nome'])){
            $message .= "Nome não foi informado.<br>";
            $return = false;
        }
        if(empty($_POST['rg'])){
            $message .= "RG não foi informado.<br>";
            $return = false;
        }
        if(empty($_POST['categoria'])){
            $message .= "Nenhuma Categoria não foi informada.<br>";
            $return = false;
        }
        if(empty($_POST['valor'])){
            $message .= "Valor não foi informado.<br>";
            $return = false;
        }
        if(empty($_POST['idade'])){
            $message .= "Idade não foi informada.<br>";
            $return = false;
        }
        if(empty($_POST['dias'])){
            $message .= "Dias não foram informados.<br>";
            $return = false;
        }
        if(empty($_POST['quantidade'])){
            $message .= "Quantidade não foi informada.<br>";
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

        if(is_null($this->getEventoId()) or $this->getEventoId() == ""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getId()) or $this->getId() == ""){
            $message .= "Cód. Id não foi informado.<br>";
            $return = false;
        }
        
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}