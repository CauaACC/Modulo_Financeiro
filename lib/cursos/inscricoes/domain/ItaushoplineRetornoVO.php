<?php
class ItaushoplineRetornoVO extends PadraoVO
{   


    public function validarSalvar(){
        $message = "";
        $return  = true;
        if( is_null($this->getCodEvento()) or $this->getCodEvento()=="" or (!is_numeric($this->getCodEvento())) ){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
//        if( is_null($this->getTipoCodPerfil()) or $this->getTipoCodPerfil()=="" ){
//            $message .= "Selecione o perfil a ser associar �s pessoas que ser�o carregadas.<br>";
//            $return = false;
//        }
//        if($this->getTipoCodPerfil() == 'perfiInscricao'){
//            if( is_null($this->getCodFormaPagamento()) or $this->getCodFormaPagamento()=="" or (!is_numeric($this->getFormaPagamento())) ){
//                $message .= "Selecione o perfil a ser associar �s pessoas que ser�o carregadas.<br>";
//                $return = false;
//            }
//        }
//        if($this->getTipoCodPerfil() == 'perfilColaboracao'){
//            if( is_null($this->getCodPerfilColaborador()) or $this->getCodPerfilColaborador()=="" or (!is_numeric($this->getCodPerfilColaborador())) ){
//                $message .= "Selecione o perfil a ser associar �s pessoas que ser�o carregadas.<br>";
//                $return = false;
//            }
//        }
        if( is_null($this->getArquivo()) or $this->getArquivo()=="" ){
            $message .= "Não foi poss�vel identificar o nome do arquivo.<br>";
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
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodArquivo()) or $this->getCodArquivo()==""){
            $message .= "Código do Arquivo não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}