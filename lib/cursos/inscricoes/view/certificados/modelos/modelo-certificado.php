<div style="PAGE-BREAK-AFTER: always">
    <img style="position:absolute; margin-left:25px; margin-top:20px;" border="1" width="1020"
    src="view/certificados/modelos/img/certificado-frente.jpg">
    <table style="position: absolute; font-family: helvetica; color: rgb(0, 0, 0); font-size: 22px; text-align: center; margin-top: 240px; margin-left: 80px;line-height: 25pt; width: 1045px;">
    <tr><td width="1045px">

    <?php
    if (mb_detect_encoding($fws_['texto_certificado'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
        $fws_['texto_certificado'] = utf8_decode($fws_['texto_certificado']);
    }
    echo $fws_['texto_certificado'];
    ?>

    <?php if($fws_['horas']) { ?>, totalizando <?php echo $fws_['horas'] . ($fws_['horas'] > 1?" horas":" hora")?><?php } ?>

    <?php if ($funcao=='Apresentador') {?>

        <?php if($co_autor1!="") echo "<br> Coautor(es):";?>
        <?php if($co_autor1!="")echo "  ".maiusculo($co_autor1); ?>
        <?php if($co_autor2!="")echo ", ".maiusculo($co_autor2); ?>
        <?php if($co_autor3!="")echo ", <br>".maiusculo($co_autor3); ?>
        <?php if($co_autor4!="")echo ", ".maiusculo($co_autor4); ?>
        <?php if($co_autor5!="")echo ", <br>".maiusculo($co_autor5); ?>
        <?php if($co_autor1!="") echo "<br/>";?>
        <?php if($cod_projeto!=1 && $prof['nome_prof']!=""){echo "<br> Orientador(a): <strong>".maiusculo($prof['nome_prof'])."</strong>";}?>

        <?php
        $titulo = maiusculo($titulo_trabalho);
        $titulo = str_replace('&#946;', '&szlig;', $titulo);
        ?>

        <br><br> T&iacute;tulo: <strong><?php echo $titulo ?></strong>

    <?php } ?>
    </td></tr></table>
    <!--<div style="position:absolute; margin-top:600px;margin-left: 320px;font-size: 18px;"><?php if ($data_certificado != "") echo "Londrina, ". $data_certificado;?></div>-->
    <div style="position: absolute;width: 1045px;  margin-top:730px;text-align: center;font-size: 11px;">
        * * * A autenticidade deste certificado pode ser verificada no link: <?php echo WB_URL_VALIDAR_CERTIFICADO?>/<?php echo $fws_['nomeArquivoCertificado']?> * * *
    </div>

</div>