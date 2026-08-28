<?php
/**
 * Modelo CONFIGURAVEL da frente do certificado.
 *
 * Mesma estrutura dos modelos antigos (arte de fundo + blocos posicionados),
 * so que as posicoes vem de tab_certificado_modelo em MM, e nao chumbadas em
 * px no CSS. Usado apenas por eventos que apontaram um modelo -- os demais
 * continuam caindo nos modelos de sempre.
 *
 * Recebe do GerenciarCertificadoAC:
 *   $fws_['objModeloCertificado'] -> CertificadoModelo ja carregado
 *   $fws_['texto_certificado'], ['data_emissao'], ['nomeArquivoValidacao'],
 *   ['assinatura_duplicada'], ['logo_suplementar'], ['cod_oficina']
 *
 * IMPORTANTE: as URLs das imagens (certificado-frente-unica.php,
 * ass-presidente.php, ass-coordenador.php, qr-code.php) precisam ser mantidas
 * exatamente nesse formato -- o resolverImagensParaLocal() do
 * GerenciarCertificadoAC troca cada uma pelo arquivo local via regex, para o
 * mPDF nao fazer request HTTP durante a geracao.
 */
$objModelo = $fws_['objModeloCertificado'];
$urlBase   = 'https://' . $_SERVER['HTTP_HOST'] . '/' . WB_GRUPO . '/' . WB_PROJETO . '/inscricoes';

/**
 * O pipeline faz mb_convert_encoding(ISO-8859-1 -> UTF-8) no HTML inteiro.
 * Texto que ja esta em UTF-8 precisa ser convertido para ISO ANTES, senao sai
 * duplamente codificado ("Marco" virou "MarA§o" no primeiro teste).
 */
function certModeloParaIso($txt) {
    if (mb_detect_encoding($txt, 'UTF-8, ISO-8859-1') == 'UTF-8') {
        return utf8_decode($txt);
    }
    return $txt;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Certificado</title>
    <style>
        body {
            font-family: Helvetica, sans-serif;
            margin: 0;
            padding: 0;
        }

        /*
         * position:FIXED, nao absolute.
         *
         * O mPDF nao e navegador: ele ignora `position:absolute` com top/left
         * (medido em 2026-07-22 -- um bloco pedido em x=100mm/y=120mm saiu em
         * x=37.6mm/y=4.9mm). Com `position:fixed` ele acerta o milimetro
         * (x=100mm/y=120.7mm). Ver tests/_test_mpdf_posicionamento.php.
         *
         * Repetir em toda pagina, efeito colateral do fixed, nao e problema:
         * a frente tem 1 pagina so e o verso e anexado depois via FPDI.
         */
        .bloco { position: fixed; }
    </style>
</head>

<body>
    <?php /* Arte de fundo: ocupa a pagina inteira e vem primeiro, para ficar
             atras dos demais blocos. background-image do CSS nao serve -- o
             mPDF nao respeita background-size. */ ?>
    <div class="bloco" style="top:0mm; left:0mm; width:297mm; height:210mm;">
        <img border="0" style="width:297mm; height:210mm;"
             src="<?php echo $urlBase ?>/view/certificados/modelos/img/certificado-frente-unica.php">
    </div>

    <?php /* ATENCAO: os blocos abaixo precisam ser filhos DIRETOS do <body>.
             Envolvi-los num <div> faz o mPDF simplesmente nao renderizar nenhum
             deles -- a pagina sai so com a arte de fundo, sem aviso de erro. */ ?>

        <div class="bloco" style="
            top:   <?php echo $objModelo->getTextoTopo() ?>mm;
            left:  <?php echo $objModelo->getTextoEsquerda() ?>mm;
            width: <?php echo $objModelo->getTextoLargura() ?>mm;
            font-size:   <?php echo $objModelo->getTextoFonteTamanho() ?>pt;
            line-height: <?php echo $objModelo->getTextoAlturaLinha() ?>pt;
            text-align:  <?php echo $objModelo->getTextoAlinhamento() ?>;
            color: #000;">
            <?php echo nl2br(certModeloParaIso($fws_['texto_certificado'])); ?>
        </div>

        <div class="bloco" style="
            top:   <?php echo $objModelo->getDataTopo() ?>mm;
            left:  <?php echo $objModelo->getDataEsquerda() ?>mm;
            width: <?php echo $objModelo->getDataLargura() ?>mm;
            font-size: <?php echo $objModelo->getDataFonteTamanho() ?>pt;
            text-align: center;">
            <?php echo certModeloParaIso($fws_['data_emissao']); ?>
        </div>

        <?php /* IMPORTANTE: toda imagem vai DENTRO de um div.bloco.
                 <img> solto com position:fixed e ignorado pelo mPDF -- a imagem
                 cai em 0,0 e some atras da arte de fundo. Medido em
                 tests/_test_mpdf_img_fixed.php. */ ?>

        <?php /* Selo/logo suplementar: so quando o evento usa (certificado_selo). */ ?>
        <?php if ($fws_['logo_suplementar'] != '') { ?>
        <div class="bloco" style="
            top:   <?php echo $objModelo->getSeloTopo() ?>mm;
            left:  <?php echo $objModelo->getSeloEsquerda() ?>mm;
            width: <?php echo $objModelo->getSeloLargura() ?>mm;">
            <img border="0" style="width:100%;" src="<?php echo $fws_['logo_suplementar'] ?>">
        </div>
        <?php } ?>

        <div class="bloco" style="
            top:   <?php echo $objModelo->getAss1Topo() ?>mm;
            left:  <?php echo $objModelo->getAss1Esquerda() ?>mm;
            width: <?php echo $objModelo->getAss1Largura() ?>mm;">
            <img border="0" style="width:100%;"
                 src="<?php echo $urlBase ?>/view/certificados/modelos/img/ass-presidente.php">
        </div>

        <?php /* Assinatura 2 (coordenador da ATIVIDADE): so quando ha atividade.
                 Sem cod_oficina o resolverImagensParaLocal() nao troca a URL por
                 arquivo local, o mPDF tenta buscar por HTTP e falha -- foi o
                 "X vermelho" que apareceu no primeiro certificado de teste. */ ?>
        <?php if (trim((string) $fws_['cod_oficina']) !== '') { ?>
        <div class="bloco" style="
            top:   <?php echo $objModelo->getAss2Topo() ?>mm;
            left:  <?php echo $objModelo->getAss2Esquerda() ?>mm;
            width: <?php echo $objModelo->getAss2Largura() ?>mm;">
            <img border="0" style="width:100%;"
                 src="<?php echo $urlBase ?>/view/certificados/modelos/img/ass-coordenador.php?id=<?php echo $fws_['cod_oficina']; ?>">
        </div>
        <?php } ?>

        <div class="bloco" style="
            top:   <?php echo $objModelo->getQrTopo() ?>mm;
            left:  <?php echo $objModelo->getQrEsquerda() ?>mm;
            width: <?php echo $objModelo->getQrLargura() ?>mm;">
            <img border="0" style="width:100%;"
                 src="<?php echo $urlBase ?>/view/inscricao/qr-code.php?id=<?php echo $fws_['nomeArquivoValidacao']; ?>">
        </div>
</body>
</html>
