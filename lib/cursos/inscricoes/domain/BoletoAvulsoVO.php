<?php

class BoletoAvulsoVO extends PadraoVO {

    public function dataHojeComparacao(){
        
    }
    public function getDataComparacaoVencimentoBoleto(){
        return  date("d/m/Y", time() + (3 * 86400));
    }
    public function getSitVencimento(){
        if(formatDateCompare($this->getDataVencimento()) < date("d/m/Y", time())){
            return "Vencido";
        }else{
            return "";
        }
    }
    public function getSitVencimentoDesconto(){
        if(formatDateCompare($this->getDataDesconto()) < formatDateCompare(date("d/m/Y", time()))){
            return "Vencido";
        }else{
            return "";
        }
    }
    public function getStatusProcessoRegistro(){
        if($this->getDesFlgRegistrado()!=''){
            echo $this->getDesFlgRegistrado();
        }else{
            echo $this->getDesFlgRemessa();
        }
    }
    public function getDesFlgPagamento() {
        if($this->getFlgPagamento()==1) {
            return "Pago";
        }else{
            if(formatDateCompare($this->getDataVencimento()) < $fws['datComparacaoVencimentoBoleto']){
                return "Aguardando";
            }else{
                return "";
            }
        }
    }
    public function getDesFlgRegistrado() {
        if($this->getFlgRegistrado()==1) {
            return "Registrado";
        }else{
            return "";
        }
    }
    public function getDesFlgRemessa() {
        if($this->getFlgRemessa()==1) {
            return "Anexado a Remessa";
        }else{
            return "";
        }
    }
    public function getDesExtra() {
        if($this->getExtra()==1) {
            return "Adicionado Posteriormente";
        }else{
            return "Emitido na Inscrição";
        }
    }

    public function getDescricao(){
        return $this->getDemonstrativo1()."<br>".$this->getDemonstrativo2()."<br>".$this->getDemonstrativo3();
    }
    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodInscrito()) or $this->getCodInscrito() == "") {
            $message .= "Código do inscrito não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCpfCnpj()) or $this->getCpfCnpj() == "") {
            $message .= "Informe cpf do sacado<br>";
            $return = false;
        }
        if (is_null($this->getSacado()) or $this->getSacado() == "") {
            $message .= "Informe nome do sacado.<br>";
            $return = false;
        }
        if (is_null($this->getValorBoleto()) or $this->getValorBoleto() == "") {
            $message .= "Informe valor do boleto.<br>";
            $return = false;
        }
        if (is_null($this->getDataVencimento()) or $this->getDataVencimento() == "") {
            $message .= "Informe data de vencimento.<br>";
            $return = false;
        }
        if (is_null($this->getDescricao()) or $this->getDescricao() == "") {
            $message .= "Informe descrição.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorEcluir() {
        $message = "";
        $return = true;
        if (($this->getFlgPagamento()==1)) {
            $message .= "Boleto pago não pode ser excluido.<br>";
            $return = false;
        }
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodInscrito()) or $this->getCodInscrito() == "") {
            $message .= "Código do inscrito não informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodBoleto()) or $this->getCodBoleto() == "") {
            $message .= "Código do boleto não informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorDesativar() {
        $message = "";
        $return = true;
        if (($this->getFlgPagamento()==1)) {
            $message .= "Boleto pago não pode ser excluido.<br>";
            $return = false;
        }
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodInscrito()) or $this->getCodInscrito() == "") {
            $message .= "Código do inscrito não informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodBoleto()) or $this->getCodBoleto() == "") {
            $message .= "Código do boleto não informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}

?>