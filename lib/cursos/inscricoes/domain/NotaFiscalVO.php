<?php

class NotaFiscalVO extends PadraoVO {

    public function validatorEmitir() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getCodEmissor())) {
            $message .= "Emissor da nota nao foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getOrigemId())) {
            $message .= "Origem da nota (pedido) nao foi informada.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getReferencia())) {
            $message .= "Referencia da nota nao foi gerada.<br>";
            $return = false;
        }

        $valor = (float) $this->getValorServico();
        if ($valor <= 0) {
            $message .= "Valor do servico deve ser maior que zero.<br>";
            $return = false;
        }

        if ($this->isNullOrEmpty($this->getDescricaoServico())) {
            $message .= "Descricao do servico nao foi informada.<br>";
            $return = false;
        }

        // Tomador: CPF ou CNPJ valido. Documento errado e a causa n.1 de rejeicao.
        $doc = preg_replace('/[^0-9]/', '', (string) $this->getTomadorCpfCnpj());
        if ($doc == "") {
            $message .= "CPF/CNPJ do tomador nao foi informado.<br>";
            $return = false;
        } elseif (strlen($doc) == 11) {
            if (function_exists('validarCPF') && !validarCPF($doc)) {
                $message .= "CPF do tomador e invalido.<br>";
                $return = false;
            }
        } elseif (strlen($doc) == 14) {
            if (function_exists('validarCNPJ') && !validarCNPJ($doc)) {
                $message .= "CNPJ do tomador e invalido.<br>";
                $return = false;
            }
        } else {
            $message .= "CPF/CNPJ do tomador deve ter 11 ou 14 digitos.<br>";
            $return = false;
        }

        if ($this->isNullOrEmpty($this->getTomadorNome())) {
            $message .= "Nome/razao social do tomador nao foi informado.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorCancelar() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getCodNotaFiscal())) {
            $message .= "Codigo da nota fiscal nao foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getMotivoCancelamento())
            || strlen(trim((string) $this->getMotivoCancelamento())) < 15) {
            $message .= "Motivo do cancelamento deve ser informado (minimo 15 caracteres).<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function ehAutorizada() {
        return $this->getStatus() == 'AUTORIZADA';
    }

    public function ehCancelada() {
        return $this->getStatus() == 'CANCELADA';
    }

    public function estaProcessando() {
        return $this->getStatus() == 'PROCESSANDO';
    }

}
