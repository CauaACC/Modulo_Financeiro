<?php
if (mb_detect_encoding($fws_['texto_recibo'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    $fws_['texto_recibo'] = utf8_decode($fws_['texto_recibo']);
    $fws_['data_emissao'] = utf8_decode($fws_['data_emissao']);
    $fws_['assinatura']   = utf8_decode($fws_['assinatura']);
    $fws_['cabecalho']    = utf8_decode($fws_['cabecalho']);
}
?>
<div style="PAGE-BREAK-AFTER: always;line-height: 28px;margin: 20px;">
<div  style="position:absolute; margin-left:200px;  margin-top:55px; width: 630px;">
    <!--<img style="width:450px;" src="resources/image/logo-60X235pxN-9.jpg" >-->
</div>
<div  style="position:absolute; margin-left:40px; margin-top:30px;width: 630px;">
<!--    <img style="" border="1" width="132" z-index="100"
    src="https://www.opctec.net.br/aremac/ingresso/inscricoes/view/inscricao/qr-code.php?string_qr_code=<?php echo $fws_['string_qr_code']; ?>">  -->
 
</div>
<div style="position: absolute;margin-top: 220px;font-size: 24px;text-align: center;margin-left: 300px; ">
    INGRESSO N. <?php echo $fws_['cod_pagamento'];?>
</div>
<div style="position: absolute; width: 650px; margin-top: 300px; margin-left: 35px; font-size: 17px;text-align-last: justify">
    <?php echo $fws_['texto_recibo'];?>
</div>
<div style="position: absolute; width: 650px; margin-top: 730px; text-align: right; font-size: 17px;">
    <?php echo $fws_['data_emissao'];?>
</div>
<div style="position: absolute;width: 650px;  margin-top:900px;text-align: center;font-size: 12px;">
    * * * <?php echo  utf8_decode("A autenticidade deste documento pode ser verificada através da leitura do qr-Code:")?> * * *
</div>
</div>