<?php
class GerenciarCargaPessoaUploadInscritosDescontosAC {

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
        if($email!=''){
            if(!validarEmail($email)){
                throw new Exception("E-mail inválido");
            }
        }
        /*
        $arrayColunas[3] - > Valor fixo
        $arrayColunas[4] - > Valor doado
        $arrayColunas[5] - > Valor desconto
        $arrayColunas[6] - > Porcentagem desconto
        */
        /*Definindo codigo da forma de cargapessoa a ser associado*/
        if($objAr->getTipoArquivo()=='INSCRITOS-DESCONTOS'){
            /*criar inscrito para a cargapessoa*/
            $objI = new Desconto();
            $objI->setCodEvento($objAr->getCodEvento()); 
            $objI->setCodArquivo($objAr->getCodArquivo());
            $objI->setCodDesconto(NULL);
            $objI->setCpf(formatCpfInsert(limparCpf($arrayColunas[1])));
            $objI->setNomeInscrito(trim($arrayColunas[0]));
            $objI->setEMail($email);
            $arrayColunas[3]!=''? $objI->setValorFixo($arrayColunas[3]) : '';
            $arrayColunas[4]!=''? $objI->setValorDoado($arrayColunas[4]) : '';
            $arrayColunas[5]!=''? $objI->setValorDesconto($arrayColunas[5]) : '';
            $arrayColunas[6]!=''? $objI->setPorcentagemDesconto($arrayColunas[6]) : '';
            $objI->setStatus('ATIVADA');
            $objI->salvar();
        }
        
    }
    
    public static function associarCargaPessoa($arrayColunas, $objConf, $objAr,$debug=false){

        /**QUANDO NÃO PRECISA ASSOCIAR A NADA o restante do código não executa*/
        throw new OKException("Não é preciso associar");
    }
}
