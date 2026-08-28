<?php
class GerenciarProcessoInscricaoAC {
    //put your code here
    public static function passo_01($fws_, $debug = false) {
        switch (COD_EVENTO) {
            case 1:
                GerenciarProcessoInscricao_9_CBMP_AC::passo_01($fws_, $debug);
                break;
            case 2:
                GerenciarProcessoInscricao_4_INESCO_AC::passo_01($fws_, $debug);
                break;
            case 3:
                GerenciarProcessoInscricao_5_MOSTRAPARANAENSESAUDE_AC::passo_01($fws_, $debug);
                break;
            case 4:
                GerenciarProcessoInscricao_5_CONGRESSO_AC::passo_01($fws_, $debug);
                break;
            case 5:
                GerenciarProcessoInscricao_5_AC::passo_01($fws_, $debug);
                break;
            default:
                
                GerenciarProcessoInscricao_SELECIONA_PERFIL_AC::passo_01($fws_, $debug);
//                echo "Nenhum evento informado em Gerenciar Processo Inscriçao.";
                break;
        }
    }  
    public static function definicaoBancoBoleto($fws_, $debug = false) {
        switch (COD_EVENTO) {
            case 1:
                return 'CAIXA';
                break;
            case 2:
            case 3:
            case 4:
            case 6:
            case 7:
                return 'UNIPRIME';
                break;
            case 45:
                return 'SISPRIME';
                break;
            default:
                $objEvConf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
                if($objEvConf->getGatewayPagamento()!=''){
                    return $objEvConf->getGatewayPagamento();
                }else{
                    echo "Banco boleto não informado.";
                }
                break;

        }
    }  
}
