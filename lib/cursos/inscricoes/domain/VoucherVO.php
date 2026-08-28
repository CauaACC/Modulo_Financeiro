<?php
class VoucherVO extends PadraoVO
{   
    public function setDatVoucher($datVoucher) {
        $this->datVoucher = formatDateInsert($datVoucher);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getDataVencimento())or$this->getDataVencimento()==""){
            $message .= "Informe data de vencimento.<br>";
            $return = false;
        }
        if(is_null($this->getSigla())or$this->getSigla()==""){
            $message .= "Informe sigla do patrocinador.<br>";
            $return = false;
        }
        if((is_null($this->getPorcentagem())or$this->getPorcentagem()=="")and(is_null($this->getValor())or$this->getValor()=="")){
            $message .= "Informe um valor ou uma porcentagem de desconto.<br>";
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
        if(is_null($this->getCodVoucher()) or $this->getCodVoucher()==""){
            $message .= "Código da codigo não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>