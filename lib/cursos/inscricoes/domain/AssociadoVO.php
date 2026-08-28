<?php

class AssociadoVO extends PadraoVO {

    public function getJaInscrito() {
        if($this->getCodInscricao()) {
            return "SIM";
        }
    }

    public function getNomeInscrito() {
        return $this->nomeAssociado;
    }

    public function setNomeInscrito($nomeInscrito) {
        $this->nomeAssociado = $nomeInscrito;
    }

    public function getCodInscrito() {
        return $this->codAssociado;
    }

    public function setCodInscrito($codInscrito) {
        $this->codAssociado = $codInscrito;
    }

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCpf()) or $this->getCpf() == "") {
            $message .= "Informe cpf do associado<br>";
            $return = false;
        }
        if (is_null($this->getNomeAssociado()) or $this->getNomeAssociado() == "") {
            $message .= "Informe nome do associado.<br>";
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
            $message .= "Código do evento não informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodInscrito()) or $this->getCodInscrito() == "") {
            $message .= "Código do associado não informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}

