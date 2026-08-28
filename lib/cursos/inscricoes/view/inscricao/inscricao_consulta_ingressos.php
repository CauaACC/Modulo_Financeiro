<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
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
echo "<div><br>";
try {
               
    if($objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos()){
        throw new Exception("Emissão de recibo bloqueada, ou nenhum registro de pagamento foi encontrado.<br>Caso necessário, entre em contato com a administração do evento para regularizar sua inscrição.");
    }

    if($objInscrito->getTotalPagamentos()>=1){
        $objInscrito->carregarListaPagamentosByIdInscrito();
        if($objInscrito->getListaPagamentos()){
            echo "<ul>";
            foreach ($objInscrito->getListaPagamentos() as $objPagamento) {
                if(($objPagamento->getFormaPagamento()=='BOLETO BANCÁRIO') 
                        or ($objPagamento->getFormaPagamento()=='INEXIGIBILIDADE') 
                        or ($objPagamento->getFormaPagamento()=='EMPENHO') 
                        or ($objPagamento->getFormaPagamento()=='TESOURARIA')
                        or ($objPagamento->getFormaPagamento()=='CARTAO')
                        or ($objPagamento->getFormaPagamento()=='DOACAO')
                        or ($objPagamento->getFormaPagamento()=='APOIO')
                        or ($objPagamento->getFormaPagamento()=='BOLETO A COMPENSAR')
                        or ($objPagamento->getFormaPagamento()=='PIX')
                        or ($objPagamento->getFormaPagamento()=='CARTAO')
                        or ($objPagamento->getFormaPagamento()=='CARTAO_CREDITO')
                        or ($objPagamento->getFormaPagamento()=='TRANSFERENCIA')){
                    echo "<li><a href='javascript:void(0);' onclick=\"gerarReciboPagamento('".$objPagamento->getCodPagamento()."');\">"
                    . "Gerar Ingresso - cód.: ".$objPagamento->getCodPagamento()." - ".textoMaiusculoMinusculo($objPagamento->getFormaPagamento())." - R$ ".formatMoneyView($objPagamento->getValor())."</a></li>";
                }
            }
            echo "</ul>";
        }
    }else{
        echo "<span style='color:red;font-size:20px;font-weight:bold'>Atenção:</span>"
        . "<br><span style='color:red;font-size:16px;'>"
        . "É necessário efetuar o pagamento para liberar emissão do ingresso."
        . "</span>";
        
    }
} catch (Exception $exc) {
    echo $exc->getMessage();
}

echo "</div>";
