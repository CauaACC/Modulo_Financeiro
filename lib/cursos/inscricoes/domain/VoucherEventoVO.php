<?php
class VoucherEventoVO extends PadraoVO
{   
    public function getDataValidade(){
        return date("Y-m-d", strtotime($this->dataValidade));
    }
    
    public function setDatVoucherEvento($datVoucherEvento) {
        $this->datVoucherEvento = formatDateInsert($datVoucherEvento);
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
            $message .= "Informe quantidade de vouchers.<br>";
            $return = false;
        }
        if((is_null($this->getPorcentagem())or$this->getPorcentagem()=="")
            and(is_null($this->getValor())or$this->getValor()=="")
            and(is_null($this->getValorInscricao())or$this->getValorInscricao()=="")
        ){
            $message .= "Informe valor de inscrição, ou valor fixo de desconto, ou a porcentagem de desconto.<br>";
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
        if(is_null($this->getCodVoucherEvento()) or $this->getCodVoucherEvento()==""){
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