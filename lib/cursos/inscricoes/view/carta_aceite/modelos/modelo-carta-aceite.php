<?php
if (mb_detect_encoding($fws_['texto_carta_aceite'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    $fws_['texto_carta_aceite'] = utf8_decode($fws_['texto_carta_aceite']);
    $fws_['data_emissao'] = utf8_decode($fws_['data_emissao']);
    $fws_['assinatura']   = utf8_decode($fws_['assinatura']);
    $fws_['cabecalho']    = utf8_decode($fws_['cabecalho']);
}
?>
<?php /* Layout em FLUXO (sem position:absolute): no mPDF o absolute e' relativo ao
       ponto corrente do fluxo, entao empilhar margin-top grande jogava blocos para a
       pagina seguinte. Timbre = tabela de 2 colunas (logo | dados do organizador).
       Tipografia: uma familia so (sans) e poucos tamanhos — corpo 15px, apoio 12px. */ ?>
<style>
    body, div, td, span { font-family: dejavusanscondensed, sans-serif; color:#222222; }
</style>
<table style="width:100%;border-collapse:collapse;">
    <tr>
        <td style="width:150px;vertical-align:middle;">
            <img src="<?php echo $fws_['url_logo_recibo']?>" style="width:130px;">
        </td>
        <td style="vertical-align:middle;font-size:12px;line-height:1.5;">
            <?php echo $fws_['cabecalho']?>
        </td>
    </tr>
</table>
<div style="margin-top:35px;font-size:20px;text-align:center;">
    Carta de Aceite N. <?php echo $fws_['cod_trabalho'];?>
</div>
<div style="margin-top:30px;margin-left:35px;margin-right:35px;font-size:15px;line-height:1.6;text-align:justify;">
    <?php echo $fws_['texto_carta_aceite'];?>
</div>
<div style="margin-top:35px;margin-right:35px;font-size:15px;text-align:right;">
    <?php echo $fws_['data_emissao'];?>
</div>
<div style="margin-top:40px;font-size:15px;text-align:center;">
    <?php echo $fws_['assinatura'];?>
</div>
<table style="margin-top:35px;width:100%;">
    <tr>
        <td style="text-align:center;font-size:12px;line-height:1.6;">
            * * * <br><?php echo  utf8_decode("A autenticidade deste documento pode ser verificada por semelhança no link:")?>
            <br><?php echo $fws_['link_validacao']?>
            <br> * * *
        </td>
        <?php if (!empty($fws_['qrcode_validacao'])) { ?>
        <td style="width:60px;text-align:left;vertical-align:middle;">
            <img src="<?php echo $fws_['qrcode_validacao']?>" style="width:55px;">
        </td>
        <?php } ?>
    </tr>
</table>
<div style="margin-top:8px;text-align:center;font-size:12px;line-height:1.6;">
    <?php echo $fws_['rodape']?>
</div>
