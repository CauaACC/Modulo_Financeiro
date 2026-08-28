<?php
// error_reporting( E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
// ini_set("display_errors", "On");
class GerenciarBoletoAC {

    //put your code here
    public function __construct() {

    }

    public function carregarObjBanco($fws_, $debug = false){
        $fws_['nomeBanco'] = GerenciarProcessoInscricaoAC::definicaoBancoBoleto($fws_, $debug);
        switch ($fws_['nomeBanco']) {
            case 'CAIXA':
                $this->objBanco = new GerenciarBoletoCaixaAC();
                break;
            case 'CAIXAParaEmpresa':
                $this->objBanco = new GerenciarBoletoCaixaParaEmpresaAC();
                break;
            case 'BANCOBRASIL':
                $this->objBanco = new GerenciarBoletoBancoBrasilAC();
                break;
            case 'UNIPRIME':
                $this->objBanco = new GerenciarBoletoUniprimeAC();
                break;
            case 'SISPRIME':
                $this->objBanco = new GerenciarBoletoSisprimeAC();
                break;
            case 'ITAU-BOLECODE':
                $this->objBanco = new GerenciarBoletoItauBolecodeAC();
                break;
            default:
                echo "Opção de banco não encontrada!";
                break;
        }
    }

    public function btnBoletoAvulso($objBancoL, $debug = false) {
        $fws_['tipo_boleto'] = "htmlBoleto";
        $fws_['cod_inscrito']  = $objBancoL->getCodInscrito();
        $fws_['cod_boleto']    = $objBancoL->getCodBoleto();
        $fws_['nome_inscrito'] = $objBancoL->getNomeInscrito();
        $fws_['cpf']           = $objBancoL->getCpfCnpj();
        $fws_['endereco']      = $objBancoL->getEndereco();
        $fws_['numero']        = $objBancoL->getNumero();
        $fws_['complemento']   = $objBancoL->getComplemento();
        $fws_['cidade']        = $objBancoL->getCidade();
        $fws_['estado']        = $objBancoL->getEstado();
        $fws_['cep']           = $objBancoL->getCep();
        $fws_['valor_cobrado'] = $objBancoL->getValorBoleto();
        $fws_['data_vencimento'] = $objBancoL->getDataVencimento();
        $this->carregarObjBanco($fws_, $debug);
        $this->objBanco->btnBoleto($fws_);
    }

    public function btnBoleto($fws_, $debug = false) {
        $this->carregarObjBanco($fws_, $debug);
        $this->objBanco->btnBoleto($fws_);
    }
    
    public function btnBoletoInstitucional($fws_, $debug = false) {
        $this->carregarObjBanco($fws_, $debug);
        $this->objBanco->btnBoletoInstitucional($fws_);
    }

    public function htmlBoleto($fws_, $debug = false) {
        $this->carregarObjBanco($fws_, $debug);
        $this->objBanco->htmlBoleto($fws_);
    }

    public function pdfBoleto($fws_, $debug = false) {
        $this->carregarObjBanco($fws_, $debug);
        $this->objBanco->pdfBoleto($fws_);
    }
    public function htmlBoletoInstitucional($fws_, $debug = false) {
        $this->carregarObjBanco($fws_, $debug);
        $this->objBanco->htmlBoletoInstitucional($fws_);
    }
    public function htmlBoletoAssociacao($fws_, $debug = false) {
        $this->carregarObjBanco($fws_, $debug);
        $this->objBanco->htmlBoletoAssociacao($fws_);
    }

}
