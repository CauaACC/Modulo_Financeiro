<?php

class LancamentosContabeisVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorSalvar() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getTipoLancamento()) or $this->getTipoLancamento() == "") {
            $message .= "Tipo do Lançamento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getValor()) or $this->getValor() == "") {
            $message .= "Valor não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getOrigemLancamento()) or $this->getOrigemLancamento() == "") {
            $message .= "Origem do Lançamento não foi informado.<br>";
            $return = false;
        }
        if ((is_null($this->getDebito()) or $this->getDebito() == "") and (is_null($this->getCredito()) or $this->getCredito() == "")) {
            $message .= "Plano de Contas não foi informado.<br>";
            $return = false;
        } else {
            if ($this->getTipoLancamento() == 'PAGAMENTO_FORNECEDOR' or $this->getTipoLancamento() == 'DESPESA_PAGA') {
                $this->setCodPlanoContas($this->getDebito());
                $this->setFlgLancamento('D');
            }
            if ($this->getTipoLancamento() == 'VENDA') {
                $this->setCodPlanoContas($this->getCredito());
                $this->setFlgLancamento('C');
            }

        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorExcluir() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodLancamentosContabeis()) or $this->getCodLancamentosContabeis() == "") {
            $message .= "Código do plano de Contas não foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function getDesConciliado(){
        if ($this->getConciliado() == 1) {
            echo "SIM";
        } else {
            echo "NÃO";
        }
    }
}

?>