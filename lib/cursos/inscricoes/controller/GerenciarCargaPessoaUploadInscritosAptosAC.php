<?php
class GerenciarCargaPessoaUploadInscritosAptosAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    public static function carregarInscrito($arrayColunas, $objConf, $objAr, $objEv){

        if($arrayColunas[0]==''){
            throw new Exception("Nome não informado");
        }
        if($arrayColunas[1]==''){
            throw new Exception("CPF não informado");
        }
        $email = trim($arrayColunas[2]);
        if($email==''){
            throw new Exception("E-mail não informado");
        }      
        if(!validarEmail($email)){
            throw new Exception("E-mail inválido");
        }
        
        /*Definindo codigo da forma de cargapessoa a ser associado*/
        if($objAr->getTipoArquivo()=='INSCRITOS-APTOS'){
            /*criar inscrito para a cargapessoa*/
            $objI = new InscritoApto();
            $objI->setCodEvento($objAr->getCodEvento()); 
            $objI->setCodArquivo($objAr->getCodArquivo());
            $objI->setCodInscrito(NULL);
            $objI->setCodPerfil($objAr->getCodPerfil());
            $objI->setCpf(formatCpfInsert(limparCpf($arrayColunas[1])));
            $objI->setNomeInscrito(trim($arrayColunas[0]));
            $objI->setEMail($email);
            $objI->setStatus('ATIVADA');
            $objI->salvar();
        }
        
    }
    
    public static function associarCargaPessoa($arrayColunas, $objConf, $objAr,$debug=false){

        /**QUANDO NÃO PRECISA ASSOCIAR A NADA o restante do código não executa*/
        throw new OKException("Não é preciso associar");
        
    }
}
