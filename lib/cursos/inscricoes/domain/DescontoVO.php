
<?php

class DescontoVO extends PadraoVO {

    public function getJaInscrito() {
        if ($this->getCodInscricao()) {
            return "SIM";
        }
    }
    
    public function getNomeDesconto() {
        if($this->nomeInscrito==''){
            return $this->nomeParticipante;
        }
        return $this->nomeInscrito;
    }
    
    public function getPreNomeDesconto() {
        $nomeDescontoCompleto = $this->getNomeDesconto();
        $primeiraPalavra = explode(' ', $nomeDescontoCompleto)[0];
        return $primeiraPalavra;
    }

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCpf()) or $this->getCpf() == "") {
            $message .= "Cpf não foi informado.<br>";
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
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodDesconto()) or $this->getCodDesconto() == "") {
            $message .= "Código do desconto não foi informado.<br>";
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