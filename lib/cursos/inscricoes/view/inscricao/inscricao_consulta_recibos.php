<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");

$recibosHabilitado = $objEvConf->getEmitirRecibo();
$nfHabilitada = $objEvConf->getSolicitacaoNfHabilitada() == 1;
?>
<form name="abrirReciboPagamento" id="abrirReciboPagamento" method="post" action="<?php echo WB_URL_ACTION?>?content=view/recibos/recibo.php" target="_blank">
    <input type="hidden" value="" name="cod_pagamento" id="cod_pagamento">
</form>
<script language="JavaScript" type="text/javascript">
    function gerarReciboPagamento(codPagamento){
        $('#cod_pagamento').val(codPagamento);
        $('#abrirReciboPagamento').submit();
    }
</script>
<?php
echo "<div>";
try {

    if($objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos()){
        throw new Exception("Emissão bloqueada, ou nenhum registro de pagamento foi encontrado.<br>Caso necessário, entre em contato com a administração do evento para regularizar sua inscrição.");
    }

    if($objInscrito->getTotalPagamentos()>=1){
        $objInscrito->carregarListaPagamentosByIdInscrito();
        if($objInscrito->getListaPagamentos()){
            echo "<ul>";
            foreach ($objInscrito->getListaPagamentos() as $objPagamento) {
                $formaValida = in_array($objPagamento->getFormaPagamento(), array(
                    'BOLETO BANCÁRIO','INEXIGIBILIDADE','EMPENHO','TESOURARIA','CARTAO',
                    'DEPOSITO CONTA CORRENTE','DOACAO','APOIO','BOLETO A COMPENSAR','BOLETO',
                    'PIX','CARTAO_CREDITO','TRANSFERENCIA'
                ));
                if (!$formaValida || $objPagamento->getStatus() != 'ATIVADA') {
                    continue;
                }

                echo "<li>";
                if ($recibosHabilitado) {
                    echo "<a href='javascript:void(0);' onclick=\"gerarReciboPagamento('".$objPagamento->getCodPagamento()."');\">"
                       . "Recibo de pagamento - cód.: ".$objPagamento->getCodPagamento()
                       . " - ".textoMaiusculoMinusculo($objPagamento->getFormaPagamento())
                       . " - R$ ".formatMoneyView($objPagamento->getValor())."</a>";
                } else {
                    echo "Pagamento - cód.: ".$objPagamento->getCodPagamento()
                       . " - ".textoMaiusculoMinusculo($objPagamento->getFormaPagamento())
                       . " - R$ ".formatMoneyView($objPagamento->getValor());
                }

                if ($nfHabilitada) {
                    require(dirname(__FILE__).'/inscricao_nota_fiscal.php');
                }

                echo "</li>";
            }
            echo "</ul>";
        }
    }else{
        echo "<span><br>É necessário efetuar o pagamento para liberar esta área.</span>";
    }
} catch (Exception $exc) {
    echo $exc->getMessage();
}

echo "</div>";

if ($nfHabilitada) {
    require_once(dirname(__FILE__).'/inscricao_nota_fiscal_modal.php');
}
