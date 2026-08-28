<?php
class TrabalhoVO extends PadraoVO
{   
    public function getPreNome() {
        $preNome = explode(" ", $this->getNomeInscrito());
        return  ucfirst(strtolower($preNome[0]));
    }
    public function getDesFlgPremiado(){
        if($this->getFlgPremiado()==1){
            return "Premiado";
        }
        return "";
    }
    public function setDatTrabalho($datTrabalho) {
        $this->datTrabalho = formatDateInsert($datTrabalho);
    }
    public function validatorObjAdmin(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;        
    }
    public function validatorAlterarFlg(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito())or $this->getCodInscrito()==""){
            $message .= "Cód. inscrito não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodTrabalho())or $this->getCodTrabalho()==""){
            $message .= "Cód.Documento não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true; 
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        $objEvConf = $this->getObjEventoConfiguracao($this->getCodEvento());
        if(is_null($this->getCodEvento())or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito())or $this->getCodInscrito()==""){
            $message .= "Cód. inscrito não foi informado.<br>";
            $return = false;
        }
        if($this->getRecursoTrabalhoSolicitar()==1 or $this->getRecursoTrabalhoSolicitar()===0){
            if(is_null($this->getRecursoTrabalho())or $this->getRecursoTrabalho()==""){
                $message .= "Texto do recurso não informado.<br>";
                $return = false;
            }
        }else{
            /**@todo Envio de Trabalho/arquivo indicar intens do formulário do trabalho que são exigidos ou não */
            if(($this->getTrabalhoTipo()=='Currículo')
            or ($this->getTrabalhoTipo()=='Documento')
            or ($this->getTrabalhoTipo()=='Documentos') 
            or ($this->getTrabalhoTipo()=='Contrato de Prestação de Serviço') 
            or ($this->getTrabalhoTipo()=='Termo de Concessão de Bolsa')
            or ($this->getTrabalhoTipo()=='Tarefa')
            or ($this->getTrabalhoTipo()=='Requerimento de Isenção'))
            {
    //            if(is_null($this->getArquivo())or $this->getArquivo()==""){
                if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] != 0) {
                    $message .= "Arquivo não anexado.<br>";
                    $return = false;
                }
            }else{
                if ($this->getCodEvento()==106){
                   if(is_null($this->getResumoTrabalho())or $this->getResumoTrabalho()==""){
                       $message .= "Solicitação não informada.<br>";
                       $return = false;
                   }
                }else{  
                    if((is_null($this->getTituloTrabalho())or $this->getTituloTrabalho()=="")and($objEvConf->getTrabTitulo()==1)){
                        $message .= "Titulo não informado.<br>";
                        $return = false;
                    }
                    if((is_null($this->getFormatoTexto())or $this->getFormatoTexto()=="")and($objEvConf->getTrabFormatoTexto()==1)){
                        $message .= "Tipo do trabalho não informado.<br>";
                        $return = false;
                    }
                    if((is_null($this->getResumoTrabalho())or $this->getResumoTrabalho()=="")and($objEvConf->getTrabResumoFormulario()==1)){
                       $message .= "Resumo não informado.<br>";
                       $return = false;
                    }
            //        if(is_null($this->getFormatoTexto())or $this->getFormatoTexto()==""){
            //            $message .= "Formato do texto não informado.<br>";
            //            $return = false;
            //        }
                    if((is_null($this->getPalavrasChave())or $this->getPalavrasChave()=="")and($objEvConf->getTrabPalavrasChave()==1)){
                        $message .= "Palavras-chaves não informadas.<br>";
                        $return = false;
                    }
                }
            }
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorObjComprovanteVinculo(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento()) or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito()) or $this->getCodInscrito()==""){
            $message .= "Cód. inscrito não foi informado.<br>";
            $return = false;
        }
        if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] != 0) {
            $message .= "Comprovante de vínculo não anexado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorObjTarefa(){
        $message = "";
        $return  = true;
        $objEvConf = $this->getObjEventoConfiguracao($this->getCodEvento());
        if(is_null($this->getCodEvento())or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito())or $this->getCodInscrito()==""){
            $message .= "Cód. inscrito não foi informado.<br>";
            $return = false;
        }
    //            if(is_null($this->getArquivo())or $this->getArquivo()==""){
        if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] != 0) {
            $message .= "Arquivo não anexado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorEnvioAvaliacao(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito())or $this->getCodInscrito()==""){
            $message .= "Cód. inscrito não foi informado.<br>";
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
        if(is_null($this->getCodEvento())or $this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodTrabalho()) or $this->getCodTrabalho()==""){
            $message .= "Código da ocorrência não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
    public function getUltimaTramitacao($arrayTotais = false){
        switch (true) {
            case ($this->getAvaliacao()=='APROVADO' or $this->getAvaliacaoFinal()=='APROVADO'):
                $arrayTotais['Aprovado']++;
                return "Aprovado";
                break;
            case ($this->getAvaliacao()=='REPROVADO' or $this->getAvaliacaoFinal()=='REPROVADO'):
                $arrayTotaisReprovado++;
                return "Reprovado";
                break;
            case ($this->getFlgEnviadoAvaliacao()==0 and $this->getAvaliacao()=='DEVOLVIDO'):
                $arrayTotais['Devolvido']++;
                return "Devolvido";
                break;
            case ($this->getFlgEnviadoAvaliacao()==1 and $this->getAvaliacao()=='SELECIONADO'):
                $arrayTotais['Selecionado']++;
                return "Em Avaliação";
                break;
            case ($this->getAvaliacaoFinal()=='RE-SUBMETIDO'):
                $arrayTotais['ReSubmetido']++;
                return "Re-Submetido";
                break;
            case ($this->getFlgEnviadoAvaliacao()==1 and (is_null($this->getAvaliacaoFinal()) or $this->getAvaliacaoFinal()=='' or $this->getAvaliacaoFinal()=='SUBMETIDO')):
                $arrayTotais['Submetido']++;
                return "Submetido";
                break;
            case ($this->getAvaliacao()=='' and $this->getFlgEnviadoAvaliacao()==''and $this->getAvaliacaoFinal()==''):
                $arrayTotais['NaoEnviouAvaliacao']++;
                return "Não submetido";
                break;
            default:
                break;
        }
    }
}