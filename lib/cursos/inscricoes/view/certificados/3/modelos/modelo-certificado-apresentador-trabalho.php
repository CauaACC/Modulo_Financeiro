<div style="PAGE-BREAK-AFTER: always">
    <img style="position:absolute; margin-left:10px; margin-top:40px;" border="1" width="1050"
    src="view/certificados/3/modelos/img/certificado-frente.jpg">
    <table style="position: absolute; width: 900px; margin-top: 200px; margin-left: 45px;
           font-family: helvetica; color: rgb(0, 0, 0); font-size: 17px; text-align: center; line-height: 25pt;">
        <tr><td width="900px" align="center">

    <?php
    if (mb_detect_encoding($fws_['texto_certificado'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
        $fws_['texto_certificado'] = utf8_decode($fws_['texto_certificado']);
        $fws_['data_emissao']      = utf8_decode($fws_['data_emissao']);
        $fws_['assinatura']        = utf8_decode($fws_['assinatura']);
    }
    echo "<div style=\"position: absolute;margin-top: 10px; width: 900px;margin-left: 35px;"
    . " font-family: helvetica; color: rgb(0, 0, 0); font-size: 17px; text-align: center; line-height: 25pt;\">";
    echo $fws_['texto_certificado'];
    echo "</div>";
    ?>
    </td></tr></table>
    <img style="position:absolute; margin-left:10px; margin-top:500px;" border="1" width="1050"
    src="view/certificados/3/modelos/img/certificado-frente-rodape.jpg">
    <div style="position: absolute;width: 1045px;  margin-top:730px;text-align: center;font-size: 12px;">
        * * * A autenticidade deste certificado pode ser verificada no link: <?php echo $fws_['link_validacao']?> * * *
    </div>

</div>