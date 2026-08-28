<div style="PAGE-BREAK-AFTER: always; position: relative; width: 1122px; height: 794px; overflow: hidden;">

    <!-- 1º: Imagem de fundo (base) -->
    <img style="position: absolute; left: 0px; top: 0px; margin-bottom: 60px; z-index: 1;"
         border="0" width="1122"
         src="view/certificados/<?php echo COD_EVENTO ?>/modelos/img/certificado-frente.jpg">

    <!-- 3º: Texto do certificado -->
    <?php
    if (mb_detect_encoding($fws_['texto_certificado'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
        $fws_['texto_certificado'] = utf8_decode($fws_['texto_certificado']);
        $fws_['data_emissao']      = utf8_decode($fws_['data_emissao']);
        $fws_['assinatura']        = utf8_decode($fws_['assinatura']);
    }
    ?>
    <div style="position: absolute; z-index: 3; top: 40px; margin-left: 40px; width: 980px;
                font-family: helvetica; color: rgb(0,0,0); font-size: 17px;
                text-align: center; line-height: 28pt;">
        <?php echo $fws_['texto_certificado']; ?>
    </div>

    <!-- 4º: Data de emissão -->
    <div style="position: absolute; z-index: 3; width: 1050px; left: 40px; top: 720px;
                text-align: right; font-size: 15px; font-family: helvetica;">
        <?php echo $fws_['data_emissao']; ?>.
    </div>

    <!-- 5º: QR Code — canto inferior esquerdo, SOBRE o bloco de logos -->
    <img style="position: absolute; margin-left: 30px; top: 400px; z-index: 10;"
         border="0" width="95"
         src="https://opctec.net.br/<?php echo WB_GRUPO ?>/<?php echo WB_PROJETO ?>/inscricoes/view/inscricao/qr-code.php?id=<?php echo $fws_['nomeArquivoValidacao']; ?>">

    <!-- 2º: Rodapé (bloco de logos) — sobre a imagem de fundo -->
    <img style="position: absolute; left: 0px; top: 400px; z-index: 2;"
         border="0" width="1122"
         src="view/certificados/<?php echo COD_EVENTO ?>/modelos/img/certificado-frente-rodape.jpg">
    <!-- Links de validação (ocultos) -->
    <div style="display: none; position: absolute; left: 130px; top: 278px; font-size: 12px;">
        Verifique autenticidade pelo link:
    </div>
    <div style="display: none; position: absolute; width: 1000px; left: 130px; top: 295px; font-size: 12px;">
        <?php echo $fws_['link_validacao'] ?>
    </div>

</div>