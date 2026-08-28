<?php
session_start();
try {

    $token = md5('seg' . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    //Valida a origem do pedido para unificar o PDF
    if (strcmp($_SESSION["param_token_unir_certificado"], $token) != 0) {
        throw new Exception("Origem da solicitação inválida");
    }
    
    $nomeArquivoCertificado = $_SESSION["param_nome_pdf"];
    
    include_once(dirname(__FILE__) . "/../include/PDFMerger/PDFMerger.php");
    $pdf = new PDFMerger;
    $pdf->addPDF('../../certificados/2016/' . $nomeArquivoCertificado);
    /* Adicionando Verso no Certificado */
    $pdf->addPDF("modelos/img/evento-2016-certificado-verso.pdf");

    $arquivoPDFFinal = "../../certificados/2016/" . $nomeArquivoCertificado;
    $pdf->merge("browser", $arquivoPDFFinal);
    unset($pdf);

//    //Abre o arquivo gerado
//    if (file_exists($arquivoPDFFinal)) {
//        $nomeArquivo = pathinfo($arquivoPDFFinal, PATHINFO_BASENAME);
//        header("Content-Type: application/pdf");
//        header("Content-Disposition: inline; filename=".$nomeArquivo);
//        header("Expires: 0");
//        header("Cache-Control: private");
//        readfile($arquivoPDFFinal);
//        //Remover o arquivo temporário
//        if(!unlink($arquivoPDF)) {
//            throw new Exception("Não foi possível remover o arquivo temporário");
//        }
//    } else {
//        throw new Exception("Não foi localizado o arquivo PDF");
//    }
} catch (Exception $exc) {
    echo $exc->getMessage();
}
