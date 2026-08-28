<?phpTrabalho
if (mb_detect_encoding($fws_['texto_recibo'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    $fws_['texto_carta_aceite'] = utf8_decode($fws_['texto_carta_aceite']);
    $fws_['data_emissao'] = utf8_decode($fws_['data_emissao']);
    $fws_['assinatura']   = utf8_decode($fws_['assinatura']);
    $fws_['cabecalho']    = utf8_decode($fws_['cabecalho']);
}
?>
<div style="PAGE-BREAK-AFTER: always;line-height: 28px;margin: 20px;">
<div  style="width:120px;margin-left: 10px;margin-top: 0px;">
    <img src="<?php echo $fws_['url_logo_recibo']?>" style="width:140px;">
</div>
<div style="position: absolute;margin-top: 70px;margin-left: 160px;font-size: 18px;width:520px;">
    <?php echo $fws_['cabecalho']?>
</div>
<div style="position: absolute;margin-top: 220px;font-size: 24px;text-align: center;margin-left: 200px; ">
    Carta de Aceite N. <?php echo $fws_['cod_trabalho'];?>
</div>
<div style="position: absolute; width: 650px; margin-top: 300px; margin-left: 35px; font-size: 17px;text-align-last: justify">
    <?php echo $fws_['texto_carta_aceite'];?>
</div>
<div style="position: absolute; width: 650px; margin-top: 640px; text-align: right; font-size: 17px;">
    <?php echo $fws_['data_emissao'];?>
</div>
<div style="position: absolute;width: 650px;  margin-top:800px;font-size: 17px;text-align: center">
    <?php echo $fws_['assinatura'];?>
</div>
<div style="position: absolute;width: 650px;  margin-top:950px;text-align: center;font-size: 11px;">
    * * * <br><?php echo  utf8_decode("A autenticidade deste documento pode ser verificada por semelhança no link:")?>
    <br><?php echo $fws_['link_validacao']?>
    <br> * * *
    <br> <?php echo $fws_['rodape']?>
</div>
</div>