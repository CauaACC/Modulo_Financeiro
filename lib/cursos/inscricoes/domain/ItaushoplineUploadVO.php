<?php
class ItaushoplineUploadVO extends PadraoVO
{   

    public function getDesTipoCodPerfil(){
        switch ($this->getTipoCodPerfil()) {
            case 'perfilInscricao':
                return 'Perfil de Inscri��o';
                break;
            case 'perfilColaborador':
                return 'Perfil de Colaborador';
                break;
            case 'listagemCursos':
                return 'Listagem de Cursos';
                break;
            default:
                break;
        }
        
    }

    public function validaCarregarLista(){
        $message = "";
        $return  = true;
        if( is_null($this->getCodEvento()) or $this->getCodEvento()=="" or (!is_numeric($this->getCodEvento())) ){
            $message .= "C�digo do evento n�o foi informado.<br>";
            $return = false;
        }
        return true;
    }
    public function validarSalvar(){
        $message = "";
        $return  = true;
        if( is_null($this->getCodEvento()) or $this->getCodEvento()=="" or (!is_numeric($this->getCodEvento())) ){
            $message .= "C�digo do evento n�o foi informado.<br>";
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
        if($this->getTipoCodPerfil() == 'listagemCursos'){
            if( is_null($this->getCodCurso()) or $this->getCodCurso()=="" or (!is_numeric($this->getCodCurso())) ){
                $message .= "Selecione o curso a ser associar �s pessoas que ser�o carregadas.<br>";
                $return = false;
            }
        }
        if( is_null($this->getArquivo()) or $this->getArquivo()=="" ){
            $message .= "N�o foi poss�vel identificar o nome do arquivo.<br>";
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
            $message .= "C�digo do evento n�o foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodItaushoplineArquivo()) or $this->getCodItaushoplineArquivo()==""){
            $message .= "C�digo do Arquivo n�o foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}