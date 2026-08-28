<?php
class InscritoVoucherVO extends PadraoVO
{   
    public function setDatInscritoVoucher($datInscritoVoucher) {
        $this->datInscritoVoucher = formatDateInsert($datInscritoVoucher);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito())or$this->getCodInscrito()==""){
            $message .= "Cód. inscrito não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodVoucher())or$this->getCodVoucher()==""){
            $message .= "Cód. voucher não foi informado.<br>";
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
        
        $this->validatorObj();
        
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>