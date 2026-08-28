<?php

class NotaFiscalSolicitacaoVO extends PadraoVO {

    public function validatorSolicitar() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodPagamento()) or $this->getCodPagamento() == "") {
            $message .= "Codigo do pagamento nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodInscrito()) or $this->getCodInscrito() == "") {
            $message .= "Codigo do inscrito nao foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorAnexar() {
        $message = "";
        $return = true;
        if (is_null($this->getCodSolicitacao()) or $this->getCodSolicitacao() == "") {
            $message .= "Codigo da solicitacao nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getArquivoNf()) or $this->getArquivoNf() == "") {
            $message .= "Arquivo da nota fiscal nao foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorSubstituir() {
        $message = "";
        $return = true;
        if (is_null($this->getCodSolicitacao()) or $this->getCodSolicitacao() == "") {
            $message .= "Codigo da solicitacao nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getMotivoDesativacao()) or trim($this->getMotivoDesativacao()) == "") {
            $message .= "Motivo da substituicao deve ser informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

}
