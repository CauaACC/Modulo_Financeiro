<?php
class PagamentoVO extends PadraoVO
{   
    public function setDatPagamento($datPagamento) {
        $this->datPagamento = formatDateInsert($datPagamento);
    }
    
    public function getDescFormaPagamento(){
        switch (removerAcentos($this->getFormaPagamento())) {
            case 'Credito':
                $formaPagamento = 'CARTAO_CREDITO';
                break;
            case 'Debito':
                $formaPagamento = 'CARTAO_DEBITO';
                break;
            case 'Crdito':
                $formaPagamento = 'CARTAO_CREDITO';
                break;
            case 'Dbito':
                $formaPagamento = 'CARTAO_DEBITO';
                break;
            case 'Carto de Crdito':
                $formaPagamento = 'CARTAO_CREDITO';
                break;
            case 'Carto de Dbito':
                $formaPagamento =  'CARTAO_DEBITO';
                break;
            default:
                $formaPagamento = $this->formaPagamento;
                break;
        }
        return $formaPagamento;
    }
    public function validatorObjCarga(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito())or$this->getCodInscrito()==""){
            $message .= "Código do inscrito não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getValor())or$this->getValor()==""){
            $message .= "Valor não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getFormaPagamento())or$this->getFormaPagamento()==""){
            $message .= "Forma de pagamento não foi informado.<br>";
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
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodPagamento())or$this->getCodPagamento()==""){
            $message .= "Código do pagamento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito())or$this->getCodInscrito()==""){
            $message .= "Código do inscrito não foi informado.<br>";
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
        if(is_null($this->getCodPagamento()) or $this->getCodPagamento()==""){
            $message .= "Código da pagamento não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>