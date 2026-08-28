    <?php
    switch ($fws_['nivel_premiacao']) {
        case 1:
            $urlHeaderCert = "view/certificados/".COD_EVENTO."/modelos/img/certificado-frente-1-lugar.jpg";
            break;
        case 2:
            $urlHeaderCert = "view/certificados/".COD_EVENTO."/modelos/img/certificado-frente-2-lugar.jpg";
            break;
        case 3:
            $urlHeaderCert = "view/certificados/".COD_EVENTO."/modelos/img/certificado-frente-3-lugar.jpg";
            break;
        case 4:
            $urlHeaderCert = "view/certificados/".COD_EVENTO."/modelos/img/certificado-frente-4-lugar.jpg";
            break;
        case "M":
            $urlHeaderCert = "view/certificados/".COD_EVENTO."/modelos/img/certificado-frente-mencao-honrosa.jpg";
            break;
        default:
            $urlHeaderCert = "view/certificados/".COD_EVENTO."/modelos/img/certificado-frente.jpg";
            break;
    }
    ?>
    <div style="PAGE-BREAK-AFTER: always">
    <img style="position:absolute; margin-left:20px; margin-top:35px;" border="1" width="1040"
         src="<?php echo $urlHeaderCert?>">
    <table style="position: absolute; width: 950px; margin-top: 215px; margin-left: 50px;
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
    <img style="position:absolute; margin-left:25px; margin-top:480px;" border="1" width="1050"
    src="view/certificados/<?php echo COD_EVENTO ?>/modelos/img/certificado-frente-rodape.jpg">
    <div style="position: absolute;width: 1045px;  margin-top:725px;text-align: center;font-size: 12px;">
        * * * A autenticidade deste certificado pode ser verificada no link: <?php echo $fws_['link_validacao']?> * * *
    </div>

</div>