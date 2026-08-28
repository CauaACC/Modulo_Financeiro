<?php

class NotaFiscalEmissorVO extends PadraoVO {

    public function validatorGravar() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getCodGestor())) {
            $message .= "Gestor nao foi informado.<br>";
            $return = false;
        }

        $cnpj = preg_replace('/[^0-9]/', '', (string) $this->getCnpj());
        if ($cnpj == "") {
            $message .= "CNPJ do emissor nao foi informado.<br>";
            $return = false;
        } elseif (strlen($cnpj) != 14) {
            $message .= "CNPJ do emissor deve ter 14 digitos.<br>";
            $return = false;
        } elseif (function_exists('validarCNPJ') && !validarCNPJ($cnpj)) {
            $message .= "CNPJ do emissor e invalido.<br>";
            $return = false;
        }

        if ($this->isNullOrEmpty($this->getRazaoSocial())) {
            $message .= "Razao social do emissor nao foi informada.<br>";
            $return = false;
        }

        $codMunicipio = preg_replace('/[^0-9]/', '', (string) $this->getCodigoMunicipio());
        if ($codMunicipio == "") {
            $message .= "Codigo do municipio (IBGE) nao foi informado.<br>";
            $return = false;
        } elseif (strlen($codMunicipio) != 7) {
            $message .= "Codigo do municipio (IBGE) deve ter 7 digitos. Ex: 4113700 para Londrina.<br>";
            $return = false;
        }

        $opSimples = $this->getCodigoOpcaoSimplesNacional();
        if ($this->isNullOrEmpty($opSimples)) {
            $message .= "Opcao pelo Simples Nacional nao foi informada.<br>";
            $return = false;
        } elseif (!in_array((int) $opSimples, array(1, 2, 3))) {
            $message .= "Opcao pelo Simples Nacional invalida (1=Nao optante, 2=MEI, 3=ME/EPP).<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    /**
     * Valida o que a prefeitura exige na hora de emitir — separado do cadastro
     * porque um emissor pode ser cadastrado aos poucos e so depois ficar apto.
     */
    public function validatorAptoParaEmitir() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getCodigoTributacaoNacionalIss())) {
            $message .= "Codigo de tributacao nacional do ISS nao configurado para este emissor.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getTributacaoIss())) {
            $message .= "Tributacao do ISS nao configurada para este emissor.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getTipoRetencaoIss())) {
            $message .= "Tipo de retencao do ISS nao configurado para este emissor.<br>";
            $return = false;
        }
        if ($this->getStatus() != 'ATIVADA') {
            $message .= "Emissor esta inativo.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    /**
     * Aptidao para emitir NF-e de mercadoria (modelo 55).
     *
     * Separado do validatorAptoParaEmitir (NFS-e) de proposito: um emissor
     * pode faturar so servico, so mercadoria ou os dois. Cobrar os campos de
     * NF-e de quem so emite NFS-e travaria um cadastro que ja funciona.
     */
    public function validatorAptoParaEmitirNfe() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getInscricaoEstadual())) {
            $message .= "Inscricao estadual nao informada — a NF-e de mercadoria exige IE.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getCodigoRegimeTributario())) {
            $message .= "Regime tributario (CRT) nao configurado para este emissor.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getSerieNfe())) {
            $message .= "Serie da NF-e nao configurada para este emissor.<br>";
            $return = false;
        }
        // Um CFOP so ja permite emitir: quem vende apenas para fora do estado
        // nunca usa o de dentro. Faltar os DOIS e que impede.
        if ($this->isNullOrEmpty($this->getCfopDentroEstado())
            && $this->isNullOrEmpty($this->getCfopForaEstado())) {
            $message .= "Nenhum CFOP padrao configurado (dentro ou fora do estado).<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getNaturezaOperacao())) {
            $message .= "Natureza da operacao nao configurada para este emissor.<br>";
            $return = false;
        }
        if ($this->getStatus() != 'ATIVADA') {
            $message .= "Emissor esta inativo.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

}
