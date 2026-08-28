<div style="PAGE-BREAK-AFTER: always">
    <img style="position:absolute; margin-left:10px; margin-top:20px;" border="1" width="1040"
    src="view/certificados/<?php echo COD_EVENTO ?>/modelos/img/certificado-frente.jpg">
    <table style="position: absolute; width: 950px; margin-top: 215px; margin-left: 45px;
           font-family: helvetica; color: rgb(0, 0, 0); font-size: 17px; text-align: center; line-height: 28pt;">
        <tr><td width="900px" align="center">

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

    <img style="position:absolute; margin-left:435px; margin-top:60px;" border="1" width="140" z-index="99"
         src="https://opctec.net.br/<?php echo WB_GRUPO?>/<?php echo WB_PROJETO?>/inscricoes/view/inscricao/qr-code.php?id=<?php echo $fws_['nomeArquivoValidacao']; ?>">     

    <div style="position: absolute; width:900px; margin-left: 40px; margin-top: 495px; text-align: right; font-size: 17px;">
        <?php echo $fws_['data_emissao'];?>.
    </div>
    
    <img style="position:absolute; margin-left:10px; margin-top:537px;" border="1" width="1050"
    src="view/certificados/<?php echo COD_EVENTO ?>/modelos/img/certificado-frente-rodape.jpg">
    
    <div style="position: absolute; width:1000px; margin-left: 40px; margin-top: 680px; text-align: center; font-size: 14px;"  z-index="98">
        <?php echo utf8_decode("Fundação de Apoio ao Desenvolvimento Tecnológico do Hospital Universitário da Universidade Estadual de Londrina");?>
    </div>
    
    <img style="position:absolute;margin-left:150px;margin-top:510px;width:250px;" border="0" z-index="98"
     src="https://opctec.net.br/<?php echo WB_GRUPO?>/<?php echo WB_PROJETO?>/inscricoes/view/certificados/<?php echo COD_EVENTO ?>/modelos/img/ass-presidente.php">  
    
    <img style="position:absolute;margin-left:650px;margin-top:510px;width:250px;" border="0" z-index="98"
     src="https://opctec.net.br/<?php echo WB_GRUPO?>/<?php echo WB_PROJETO?>/inscricoes/view/certificados/<?php echo COD_EVENTO ?>/modelos/img/ass-coordenador.php?id=<?php echo $fws_['cod_oficina']; ?>">     
     
</div>