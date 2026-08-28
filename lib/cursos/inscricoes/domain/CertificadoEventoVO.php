<?php
class CertificadoEventoVO extends PadraoVO
{   
    public function setDatCertificadoEvento($datCertificadoEvento) {
        $this->datCertificadoEvento = formatDateInsert($datCertificadoEvento);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getDataValidade())or$this->getDataValidade()==""){
            $message .= "Informe data de validade.<br>";
            $return = false;
        }
        if(is_null($this->getSigla())or$this->getSigla()==""){
            $message .= "Informe sigla do patrocinador.<br>";
            $return = false;
        }
        if(is_null($this->getQuantidade())or$this->getQuantidade()==""){
            $message .= "Informe quantidade de certificados.<br>";
            $return = false;
        }
        if((is_null($this->getPorcentagem())or$this->getPorcentagem()=="")and(is_null($this->getValor())or$this->getValor()=="")){
            $message .= "Informe um valor ou uma porcentagem de desconto.<br>";
            $return = false;
        }
        if(is_null($this->getEmailContato())or$this->getEmailContato()==""){
            $message .= "Informe o e-mail do responsável.<br>";
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
        if(is_null($this->getCodCertificadoEvento()) or $this->getCodCertificadoEvento()==""){
            $message .= "Código da ocorrência não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>