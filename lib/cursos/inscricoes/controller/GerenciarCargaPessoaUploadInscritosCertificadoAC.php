<?php
class GerenciarCargaPessoaUploadInscritosCertificadoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    /**
     * Carga de inscritos somente para certificado.
     * CSV com 1 coluna: nome
     * Cria inscrito com origem_cadastro=CERTIFICADO, sem CPF e sem e-mail.
     */
    public static function carregarInscrito($arrayColunas, $objConf, $objAr, $objEv){

        $nome = isset($arrayColunas[0]) ? trim($arrayColunas[0]) : '';
        if($nome == ''){
            throw new Exception("Nome não informado");
        }

        if($objAr->getTipoArquivo() == 'INSCRITOS-CERTIFICADO'){
            $objI = new Inscrito();
            $objI->setCodEvento($objAr->getCodEvento());
            $objI->setCodArquivo($objAr->getCodArquivo());
            $objI->setCodInscrito(NULL);
            $objI->setCodPerfil($objAr->getCodPerfil());
            $objI->setNomeInscrito($nome);
            $objI->setOrigemCadastro('CERTIFICADO');
            $objI->setStatus('ATIVADA');
            $objI->salvarCarga();
        }
    }

    public static function associarCargaPessoa($arrayColunas, $objConf, $objAr, $debug=false){
        throw new OKException("Não é preciso associar");
    }
}
