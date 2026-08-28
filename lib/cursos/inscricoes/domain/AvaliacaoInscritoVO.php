<?php
class AvaliacaoInscritoVO extends PadraoVO { 
    public function getDescClassificacao(){
        if($this->getClassificacao()=='N'){
            return "Não Classificado";
        }
        return $this->getClassificacao();  
    }
    public function getDescClassificacaoStatus(){
        if(COD_EVENTO==107){
            if($this->getClassificacaoStatus()=='I'){
                return "Indeferido";
            }
            if($this->getClassificacaoStatus()=='H'){
                return "Deferido";
            }
            if($this->getClassificacaoStatus()==''){
                return "";
            }
        }else{
            if($this->getClassificacaoStatus()=='I'){
                return "Inabilitado";
            }
            if($this->getClassificacaoStatus()=='H'){
                return "Habilitado";
            }
            if($this->getClassificacaoStatus()==''){
                return "INDEFINIDO";
            }
        }
        return $this->getClassificacao();  
    }
    public function getDescClassificacaoStatusAposRecurso(){
        if($this->getClassificacaoStatusAposRecurso()=='I'){
            return "Inabilitado";
        }
        if($this->getClassificacaoStatusAposRecurso()=='H'){
            return "Habilitado";
        }
        if($this->getClassificacaoStatusAposRecurso()==''){
            return "INDEFINIDO";
        }
        return $this->getClassificacaoStatusAposRecurso();  
    }
    public function getDescClassificacaoStatusFinal(){
        if($this->getClassificacaoStatusFinal()=='I'){
            return "Inabilitado";
        }
        if($this->getClassificacaoStatusFinal()=='H'){
            return "Habilitado";
        }
        if($this->getClassificacaoStatusFinal()==''){
            return "INDEFINIDO";
        }
        return $this->getClassificacaoStatusFinal();  
    }
    public function getSitPontos(){ 
        if($this->sitPontos!=''){
            return $this->sitPontos;
        }
        return $this->getPontosInformadosTrabalho();
    }
    public function getCodInscrito(){ 
        if($this->codInscrito!=''){
            return $this->codInscrito;
        }
        return $this->getCodInscricao();
    }
    public function getJaInscrito() {
        if ($this->getCodInscrito()) {
            return "SIM";
        }
    }
    
    public function getNomeAvaliacaoInscrito() {
        if($this->nomeInscrito==''){
            return $this->nomeParticipante;
        }
        return $this->nomeInscrito;
    }
    
    public function getPreNomeAvaliacaoInscrito() {
        $nomeAvaliacaoInscritoCompleto = $this->getNomeAvaliacaoInscrito();
        $primeiraPalavra = explode(' ', $nomeAvaliacaoInscritoCompleto)[0];
        return $primeiraPalavra;
    }
    
    public function getDescSitArquivo(){
        if ($this->getSitArquivo()== "Não disponível"){
            return "NÃO anexada";
        }
        if ($this->getSitArquivo()== ""){
            return "NÃO anexada";
        }
        if (confirmaExtensao($this->getSitArquivo())){
            return "Anexado";
        }
        return $this->getSitArquivo();
    }
    
    public function getDescSitPontos(){
        if ($this->getSitPontos()== "None"){
            return "não informada";
        }
        if ($this->getSitPontos()== "N"){
            return "não informada";
        }
        if ($this->getSitPontos()== ""){
            return "não informada";
        }
        return $this->getSitPontos();
    }
    
    public function validatorObj(){
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty('codInscrito')) {
            $message .= "Código Inscrito não foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty('nomeInscrito')) {
            $message .= "Nome inscrito não foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty('sitPontos')) {
            $message .= "Situação dos pontos não foi informada.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty('pontosTotalConfirmado')and($this->getClassificacao()!==0)) {
            $message .= "Pontos Total Confirmado não foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty('classificacao')and($this->getClassificacao()!==0)) {
            $message .= "Ordem de classificação não foi informada.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty('classificacaoStatus')) {
            $message .= "Classificação Status não foi informado.<br>";
            $return = false;
        }
        
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validarLiberarAvaliador(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Cód. evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito())or$this->getCodInscrito()==""){
            $message .= "Cód. inscrito não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodTrabalho())or$this->getCodTrabalho()==""){
            $message .= "Cód. trabalho não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodAvaliador())or$this->getCodAvaliador()==""){
            $message .= "Cód. avaliador não foi informado.<br>";
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
        
        if(is_null($this->getCodAvaliacao())or$this->getCodAvaliacao()==""){
            $message .= "Cód. avaliacao não foi informado.<br>";
            $return = false;
        }
        
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}