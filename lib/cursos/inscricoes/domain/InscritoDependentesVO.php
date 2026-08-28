<?php

class InscritoDependentesVO extends PadraoVO {

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
        if (is_null($this->getCodDependente()) or $this->getCodDependente() == "") {
            $message .= "Código do Dependente não foi informado.<br>";
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
        if (is_null($this->getNomeDependente()) or $this->getNomeDependente() == "") {
            $message .= "Nome do Dependente não foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    // public function getNaturezaSaldo() {
    //     switch ($this->getTipoConta()) {
    //         case 'RECEITA':
    //                 return 'CREDITO';
    //             break;
    //         case 'DESPESA':
    //                 return 'DEBITO';
    //             break;
    //         case 'PASSIVO':
    //                 return 'CREDITO';
    //             break;
    //         case 'ATIVO':
    //                 return 'DEBITO';
    //             break;
    //         case 'PATRIMONIO_LIQUIDO':
    //                 return 'CREDITO';
    //             break;
          
    //         default:

    //             break;
    //     }
    // }
    public function getDesPossuiDeficiencia(){
        if ($this->getPossuiDeficiencia() == 1) {
            echo "SIM";
        } else {
            echo "NÃO";
        }
    }
    public function getDesDependenteIrpf(){
        if ($this->getDependenteIrpf() == 1) {
            echo "SIM";
        } else {
            echo "NÃO";
        }
    }
    public function getDesDependentePlanoSaude(){
        if ($this->getDependentePlanoSaude() == 1) {
            echo "SIM";
        } else {
            echo "NÃO";
        }
    }
    public function getDesDependenteSalarioFamilia(){
        if ($this->getDependenteSalarioFamilia() == 1) {
            echo "SIM";
        } else {
            echo "NÃO";
        }
    }
}

?>