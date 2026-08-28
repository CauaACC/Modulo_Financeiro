<?php

class PrestacaoServicoVO extends PadraoVO
{

    public function getJaInscrito()
    {
        if ($this->getCodInscricao()) {
            return "SIM";
        }
    }

    public function getNomePrestacaoServico()
    {
        if ($this->nomeInscrito == '') {
            return $this->nomeParticipante;
        }
        return $this->nomeInscrito;
    }

//    public function getPreNomePrestacaoServico()
//    {
//        $nomePrestacaoServicoCompleto = $this->getNomePrestacaoServico();
//        $primeiraPalavra = explode(' ', $nomePrestacaoServicoCompleto)[0];
//        return $primeiraPalavra;
//    }

    public function validatorObj()
    {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }

        if (empty($this->nomeIdentificador)) {
            $message .= 'Nome Identificador é obrigatório.<br>';
            $return = false;
        }

        if (empty($this->valorPrestacaoServico)) {
            $message .= 'Valor Prestação Serviço é obrigatório.<br>';
            $return = false;
        }

        if (empty($this->dataVencimento)) {
            $message .= 'Data Vencimento é obrigatório.<br>';
            $return = false;
        }

        if (empty($this->nomePrestador)) {
            $message .= 'Nome do Prestador é obrigatório.<br>';
            $return = false;
        }

        $validSituacaoPago = ['aprovado', 'pendente', 'recusado', 'cancelado', 'estornando'];
        if (!in_array($this->situacaoPago, $validSituacaoPago)) {
            $message .= 'Situação Pago é inválido.<br>';
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
            return false;
        }

        return true;
    }

    public function validatorExcluir()
    {
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
}
?>