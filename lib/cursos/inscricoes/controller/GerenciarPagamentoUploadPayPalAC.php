<?php
class GerenciarPagamentoUploadPayPalAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
    }

    public static function carregarInscrito($arrayColunas, $objConf, $objAr, $objEv){
        throw new OKException("Não é preciso carregar");
        
    }
    
    public static function associarPagamento($arrayColunas, $objConf, $objAr,$debug=false){
        if($arrayColunas[12]==''){
            throw new Exception("Transação id não informada");
        }
        // if($arrayColunas[5]!='Concluído'){
        //     throw new Exception("Pagamento não aprovado ou cancelado");
        // }         
        /*Definindo codigo da forma de pagamento a ser associado*/
        if($objAr->getTipoArquivo()=='PAY-PAL'){
            /*criar inscrito para a pagamento*/
            $objI = new Inscrito();
            $objI->setCodEvento($objAr->getCodEvento()); 
            $objI->setCodArquivo($objAr->getCodArquivo());
            $objI->setTransacaoId($arrayColunas[12]);   
            $objI->setEMail($arrayColunas[10]);
            
            $objP = new Pagamento();
            $objP->setCodEvento($objI->getCodEvento());
            $objP->setTransacaoId($arrayColunas[12]);
            if($objP->getPagamentoByTransacaoId()){
                throw new Exception("Transação ID (".$objP->getTransacaoId()."), já cadastrado");
            }
            if(!$objI->getByEmailExistePopular()){
                throw new Exception("<span style='color:red;'>Transação E-mail ".$objI->getEMail().", (".$arrayColunas[3].")(".$arrayColunas[12].") não localizado</span>");
            }
            $objP->setCodArquivo($objI->getCodArquivo());
            $objP->setCodInscrito($objI->getCodInscrito());

            /**@todo localizar um código de pedido para dar baixa */
            $objPedido = new Pedido();
            $objPedido->setEventoId($objI->getCodEvento()); 
            $objPedido->setInscritoId($objI->getCodInscrito()); 
            $objPedido->getByInscritoId();
            if($objPedido->getId()!=''){
                $objP->setPedidoId($objPedido->getId());
            }    
            
            $numeroFormatado = formatMoneyInsert($arrayColunas[7]);
            $numeroFormatado = number_format($numeroFormatado, 2);
            $objP->setValor($numeroFormatado);
            $objP->setDataPagamento(formatDateInsert(str_replace('"', '', $arrayColunas[0])));
            $objP->setFormaPagamento('CARTAO');
            $objP->setStatus('ATIVADA');
            $objP->salvarCargaPagamento();
            
        }
    }
}
