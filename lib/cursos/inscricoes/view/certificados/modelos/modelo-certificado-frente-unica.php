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
            padding: 0;
        }

        .certificado {
            position: relative;
            width: 297mm;
            /* Largura do A4 */
            height: 210mm;
            /* Altura do A4 */
            background-image: url('https://<?php echo $_SERVER['HTTP_HOST'] ?>/<?php echo WB_GRUPO ?>/<?php echo WB_PROJETO ?>/inscricoes/view/certificados/modelos/img/certificado-frente-unica.php');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .conteudo {
            /* border: #000 1px solid; */
            position: absolute;
            width: 85%;
            height: 300px;
            margin: 0 auto;
            padding-top: 215px;
            font-size: 17px;
            line-height: 28pt;
            color: #000;
        }

        .data-emissao {
            position: absolute;
            bottom: 50px;
            right: 50px;
            font-size: 14px;
        }

        .qr-container {
            position: absolute;
            /* border: #000 1px solid; */
            height: 200px; 
            text-align: left;
            margin: 30px;
        }

        .qr-code {
            position: absolute;
            text-align: left;
            width: 100px;
            height: 100px;
            margin-top: 80px;
        }
    </style>
</head>

<body>
    <div class="certificado">
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

        <div class="qr-container">
            <img class="qr-code"
                src="https://<?php echo $_SERVER['HTTP_HOST'] ?>/<?php echo WB_GRUPO ?>/<?php echo WB_PROJETO ?>/inscricoes/view/inscricao/qr-code.php?id=<?php echo $fws_['nomeArquivoValidacao']; ?>">
        </div>

    </div>
</body>

</html>