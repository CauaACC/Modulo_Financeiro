<?php
if (mb_detect_encoding($fws_['texto_recibo'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    $fws_['texto_recibo'] = utf8_decode($fws_['texto_recibo']);
    $fws_['data_emissao'] = utf8_decode($fws_['data_emissao']);
    $fws_['assinatura']   = utf8_decode($fws_['assinatura']);
    $fws_['cabecalho']    = utf8_decode($fws_['cabecalho']);
}
?>
<!-- <div style="PAGE-BREAK-AFTER: always;line-height: 28px;margin: 20px;"> -->
<div  style="width:120px;margin-left: 0px;margin-top: 0px;">
    <img src="<?php echo $fws_['url_logo_recibo']?>" style="width:120px;">
</div>
<div style="position: absolute;margin-top: 70px;margin-left: 160px;font-size: 21px;width:500px;">
    <?php echo $fws_['cabecalho']?>
</div>
<div style="position: absolute;margin-top: 20px;font-size: 24px;text-align: center;margin-left: 300px;">
    RECIBO N. <?php echo $fws_['cod_pagamento'];?>
</div>
<div style="position: absolute; width: 650px; margin-top: 200px; margin-left: 35px; font-size: 17px;">
    <?php echo $fws_['texto_recibo'];?>
</div>
<div style="position: absolute; width: 650px; margin-top: 530px; text-align: right; font-size: 17px;">
    <?php echo $fws_['data_emissao'];?>
</div>
<div style="position: absolute;width: 650px;  margin-top:650px;font-size: 17px;text-align: center;">
    <?php if ($fws_['url_assinatura']!='') {?>
    <img src="<?php echo $fws_['url_assinatura']?>"  style="width:300px;">
    <?php } ?>
    <br><?php echo $fws_['assinatura'];?>
</div>
<div style="position: absolute;width: 650px;  margin-top:750px;text-align: center;font-size: 12px;">
    <span style="line-height: 1.5;">
    * * *
    <br> <?php echo  utf8_decode("A autenticidade deste documento pode ser verificada por semelhança no link:")?> 
    <br> <?php echo $fws_['link_validacao']?>
    <br> * * *
    <br> <?php echo $fws_['rodape']?>
    </span>
</div>
<!-- </div> -->