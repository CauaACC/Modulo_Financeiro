<?php
class TaxaVO extends PadraoVO
{   
    public function getValorTaxa(){
        return $this->getTxUnicaAVistaValor();
//        if($this->getTxUnicaAVista()==1){
//            
//        }
    }
    public function getDesStatus(){
        if($this->getStatus()=='ATIVADA' or $this->getStatus()=='ATIVADO'){
            return "A";
        }else{
            return "D";
        }
    }    
    public function getValorComDesconto() {
        $this->valorComDesconto = $this->getValorTaxa()-$this->getValorDesconto();
        return $this->valorComDesconto;
    }
    public function getValorComDesconto1() {
        $this->valorComDesconto = $this->getValorTaxa()-$this->getValorDesconto1();
        return $this->valorComDesconto;
    }
    public function getValorComDesconto2() {
        $this->valorComDesconto = $this->getValorTaxa()-$this->getValorDesconto2();
        return $this->valorComDesconto;
    }
    public function getValorComDesconto3() {
        $this->valorComDesconto = $this->getValorTaxa()-$this->getValorDesconto3();
        return $this->valorComDesconto;
    }

    public function setDatTaxa($datTaxa) {
        $this->datTaxa = formatDateInsert($datTaxa);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
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
        if(is_null($this->getCodTaxa()) or $this->getCodTaxa()==""){
            $message .= "Código da taxa não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}