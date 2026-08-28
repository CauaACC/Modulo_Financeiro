<?php
if (mb_detect_encoding($fws_['texto_declaracao_presenca'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    $fws_['texto_declaracao_presenca'] = utf8_decode($fws_['texto_declaracao_presenca']);
    $fws_['data_emissao'] = utf8_decode($fws_['data_emissao']);
    $fws_['assinatura']   = utf8_decode($fws_['assinatura']);
    $fws_['cabecalho']    = utf8_decode($fws_['cabecalho']);
    $fws_['nome_tipo_documento'] = utf8_decode('Declaração de Presença');
}
?>
<div style="line-height: 1.6; font-size: 17px; margin: 40px;">

    <!-- Logo -->
    <div style="margin-bottom: 20px;">
        <img src="<?php echo $fws_['url_logo_recibo']?>" style="width: 120px;">
    </div>

    <!-- Cabeçalho -->
    <div style="font-size: 21px; margin-bottom: 30px;">
        <?php echo $fws_['cabecalho']?>
    </div>

    <!-- Título -->
    <div style="font-size: 24px; text-align: center; margin-bottom: 40px;">
        <?php echo $fws_['nome_tipo_documento']?> N. <?php echo $fws_['cod_inscrito'];?>
    </div>

    <!-- Texto principal -->
    <div style="text-align: justify; margin-bottom: 50px;">
        <?php echo $fws_['texto_declaracao_presenca'];?>
    </div>

    <!-- Data -->
    <div style="text-align: right; margin-bottom: 60px;">
        <?php echo $fws_['data_emissao'];?>
    </div>

    <!-- Assinatura -->
    <div style="text-align: center; margin-bottom: 50px;">
        <?php echo $fws_['assinatura'];?>
    </div>

    <!-- Rodapé / Validação -->
    <div style="text-align: center; font-size: 11px;">
        * * *<br>
        <?php echo utf8_decode("A autenticidade deste documento pode ser verificada por semelhança no link:")?><br>
        <?php echo $fws_['link_validacao']?><br>
        * * *
    </div>

</div>
