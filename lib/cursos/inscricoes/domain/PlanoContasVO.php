<?php

class PlanoContasVO extends PadraoVO {

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

    public function validatorExcluir() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodPlanoContas()) or $this->getCodPlanoContas() == "") {
            $message .= "Código do plano de Contas não foi informado.<br>";
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
        if (is_null($this->getNomePlanoContas()) or $this->getNomePlanoContas() == "") {
            $message .= "Nome do plano de Conta não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getTipoConta()) or $this->getTipoConta() == "") {
            $message .= "Tipo do plano de Conta não foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function getNaturezaSaldo() {
        switch ($this->getTipoConta()) {
            case 'RECEITA':
                    return 'CREDITO';
                break;
            case 'DESPESA':
                    return 'DEBITO';
                break;
            case 'PASSIVO':
                    return 'CREDITO';
                break;
            case 'ATIVO':
                    return 'DEBITO';
                break;
            case 'PATRIMONIO_LIQUIDO':
                    return 'CREDITO';
                break;
          
            default:

                break;
        }
    }
    public function getDesAceitaLancamento(){
        if ($this->getAceitaLancamento() == 1) {
            echo "SIM";
        } else {
            echo "NÃO";
        }
    }
}

?>