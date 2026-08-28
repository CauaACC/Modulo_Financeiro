<?php
class BoletoVO extends PadraoVO
{   

    public function getDescFlgPagamento(){
        if($this->getFlgPagamento()=='1'){
            return "Boleto Pago";
        }else{
            return "Boleto Não Pago";
        }
    }
    
    public function getDescFlgRemessa(){
        if($this->getFlgRemessa()=='1'){
            return "Adicionado a arquivo de remessa: ".$this->getArquivoRemessa();
        }else{
            return "Não adicionado a arquivo de remessa.";
        }
    }

    public function setDatBoleto($datBoleto) {
        $this->datBoleto = formatDateInsert($datBoleto);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getNumeroDocumento())or$this->getNumeroDocumento()==""){
            $message .= "Número do documento não foi informado.<br>";
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
        if(is_null($this->getCodBoleto()) or $this->getCodBoleto()==""){
            $message .= "Código da boleto não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>