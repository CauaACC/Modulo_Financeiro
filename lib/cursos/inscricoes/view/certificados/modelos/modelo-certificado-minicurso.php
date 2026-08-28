<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Certificado</title>
    <style>
        body {
            font-family: Helvetica, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0px;
        }

        .certificado {
            position: relative;
            width: 297mm;
            /* Largura do A4 */
            height: 210mm;
            /* Altura do A4 */
            background-image: url('https://<?php echo $_SERVER['HTTP_HOST'] ?>/<?php echo WB_GRUPO ?>/<?php echo WB_PROJETO ?>/inscricoes/view/certificados/modelos/img/certificado-frente-unica.php');
            background-size: 3100px;
            /* Reduzir o tamanho da imagem para ficar longe da margem */
            background-position: center;
            background-repeat: no-repeat;
            margin: 0px;
            /* Adicionar margem ao redor da imagem */
        }

        .conteudo {
            /* border: #000 1px solid; */
            position: absolute;
            width: 85%;
            height: 300px;
            margin: 0 auto;
            padding-top: 20px;
            font-size: 17px;
            line-height: 28pt;
            color: #000;
        }

        .data-emissao {
            /*border: #000 1px solid;*/
            position: absolute;
            bottom: 50px;
            font-size: 15px;
            text-align: right;
            width: 80%;
        }

        .qr-container {
            position: absolute;
            /* border: #000 1px solid; */
            height: 200px; 
            text-align: left;
            margin-left:500px;
            margin-top:60px;
            width: 3000px;
        }

        .qr-code {
            position: absolute;
            text-align: left;
            width: 90px;
            height: 90px;
            margin-top: 80px;
        }

        .assinatura {
            position: absolute;
            /*border: #000 1px solid;*/
            height: 180px;
            text-align: left;
       }
    </style>
</head>

<body>
    <div class="certificado">
        <div class="qr-container">
            <img class="qr-code"
                src="https://<?php echo $_SERVER['HTTP_HOST'] ?>/<?php echo WB_GRUPO ?>/<?php echo WB_PROJETO ?>/inscricoes/view/inscricao/qr-code.php?id=<?php echo $fws_['nomeArquivoValidacao']; ?>">

        </div>
        <div class="conteudo">
            <?php
            if (mb_detect_encoding($fws_['texto_certificado'], 'UTF-8, ISO-8859-1') == 'UTF-8') {
                $fws_['texto_certificado'] = utf8_decode($fws_['texto_certificado']);
            }
            echo nl2br($fws_['texto_certificado']);
            ?>
        </div>

        <div class="data-emissao">
            <?php echo $fws_['data_emissao']; ?>
        </div>

        <div class="assinatura">

            <img style="position:absolute;margin-left:100px;width:250px;" border="0"
            src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/<?php echo WB_GRUPO?>/<?php echo WB_PROJETO?>/inscricoes/view/certificados/modelos/img/ass-presidente.php">

                <?php if ($fws_['assinatura_duplicada'] == 1) { ?>
                    <img style="position:absolute;margin-left:50px;width:520px;" border="0"
                    src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/<?php echo WB_GRUPO?>/<?php echo WB_PROJETO?>/inscricoes/view/certificados/modelos/img/ass-coordenador.php?id=<?php echo $fws_['cod_oficina']; ?>">
                <?php }else{ ?>
                    <img style="position:absolute;margin-left:350px;width:250px;" border="0"
                    src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/<?php echo WB_GRUPO?>/<?php echo WB_PROJETO?>/inscricoes/view/certificados/modelos/img/ass-coordenador.php?id=<?php echo $fws_['cod_oficina']; ?>">
                <?php } ?> 
        </div>

        <div style="position: absolute; width:1000px; margin-top: 0px; text-align: center; font-size: 15px;">
            <?php echo utf8_decode("Fundação de Apoio ao Desenvolvimento Tecnológico do Hospital Universitário da Universidade Estadual de Londrina");?>
        </div>
    </div>

</body>

</html>