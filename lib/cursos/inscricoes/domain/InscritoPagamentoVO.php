<?php
class InscritoPagamentoVO extends PadraoVO
{   
    public function getLiberarRecibo(){
        if(($this->getFormaPagamento() == 'BOLETO BANCÁRIO')
        or ($this->getFormaPagamento() == 'INEXIGIBILIDADE')
        or ($this->getFormaPagamento() == 'EMPENHO')
        or ($this->getFormaPagamento() == 'TESOURARIA')
        or ($this->getFormaPagamento() == 'CARTAO')
        or ($this->getFormaPagamento() == 'DOACAO')
        or ($this->getFormaPagamento() == 'APOIO')
        or ($this->getFormaPagamento() == 'BOLETO A COMPENSAR')
        or ($this->getFormaPagamento() == 'PIX')
        or ($this->getFormaPagamento() == 'CARTAO')
        or ($this->getFormaPagamento() == 'CARTAO_CREDITO')
        or ($this->getFormaPagamento() == 'TRANSFERENCIA')) {
            return true;
        }
        return false;
    }

    public function setDatInscritoPagamento($datInscritoPagamento) {
        $this->datInscritoPagamento = formatDateInsert($datInscritoPagamento);
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
        if(is_null($this->getCodOficina())or$this->getCodOficina()==""){
            $message .= "Cód. oficina não foi informado.<br>";
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
}