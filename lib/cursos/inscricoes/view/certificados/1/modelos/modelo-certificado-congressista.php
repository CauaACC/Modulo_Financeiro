<div style="PAGE-BREAK-AFTER: always">
    <img style="position:absolute; margin-left:25px; margin-top:20px;" border="1" width="1020"
    src="view/certificados/modelos/img/certificado-smb-9congresso-frente.jpg">
    <table style="position: absolute; width: 950px; margin-top: 280px; margin-left: 35px;
           font-family: helvetica; color: rgb(0, 0, 0); font-size: 17px; text-align: center; line-height: 28pt;">
        <tr><td width="910px" align="center">

    <?php
    if (mb_detect_encoding($fws_['texto_certificado'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
        $fws_['texto_certificado'] = utf8_decode($fws_['texto_certificado']);
        $fws_['data_emissao']      = utf8_decode($fws_['data_emissao']);
        $fws_['assinatura']        = utf8_decode($fws_['assinatura']);
    }
    echo "<div style=\"position: absolute;margin-top: 20px; width: 910px;margin-left: 35px;"
    . " font-family: helvetica; color: rgb(0, 0, 0); font-size: 17px; text-align: center; line-height: 28pt;\">";
    echo $fws_['texto_certificado'];
    echo "</div>";
    ?>
    </td></tr></table>
    <div style="position: absolute;width: 1030px;  margin-top:530px;text-align: right;font-size: 17px;">
        <?php echo $fws_['data_emissao'] ?>
    </div>
    <div style="position: absolute;width: 1045px;  margin-top:600px;text-align: center;font-size: 15px;">
        <?php echo $fws_['assinatura'];?>
    </div>
    <div style="position: absolute;width: 1045px;  margin-top:730px;text-align: center;font-size: 12px;">
        * * * A autenticidade deste certificado pode ser verificada no link: <?php echo $fws_['link_validacao']?> * * *
    </div>

</div>