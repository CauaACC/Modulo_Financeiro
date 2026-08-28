<?php
// error_reporting(E_ALL ^ E_NOTICE);
// ini_set("display_errors", "On");
    
$nomeArquivoCertificado = $_GET['cert'].'-'.$_SESSION['cod'].'.pdf';

// para verificar se o arquivo do certificado j� foi gerado em disco
function arquivoExiste($id) {
    $filepath = "pdf/";
    $arquivo = $id;
    if (file_exists($filepath . $arquivo)) {
        return true;
    } else {
        return false;
    }
}

// r�cup�ration du contenu HTML
ob_start();
include(dirname(__FILE__).'/modelos/modelo-certificado.php');
$content = ob_get_clean();

// conversion HTML => PDF
require_once(dirname(__FILE__).'/../include/html2pdf/html2pdf.class.php');
$html2pdf = new HTML2PDF('L','A4','pt');
$html2pdf->setTestTdInOnePage(false);
//$html2pdf->pdf->SetProtection(array('print'));  // s� permite imprimir
//$html2pdf->pdf->IncludeJS("print(true);");
$html2pdf->WriteHTML($content, isset($_GET['vuehtml']));

#$nomeArquivoCertificado = 'certificado-'.$_GET['cert'].'-'.$_SESSION['cod'].'-'.str_replace(" ", "_",trim($nome_certificado)).'.pdf';
//if (!arquivoExiste($nomeArquivoCertificado)){
if (!file_exists(ANO_EVENTO)) {
    mkdir('../../certificados/'.ANO_EVENTO, 0755);
}
$html2pdf->Output('../../certificados/'.ANO_EVENTO.'/'.$nomeArquivoCertificado,'F'); // grava o arquivo em disco na primeira vez que � visualizado
/*forma antiga removido por zaqueu 2016*/
//$html2pdf->Output($localArquivo.$nomeArquivoCertificado);     // exibe no navegador
//}
//
/*redirecionando para unificar o verso do certificado*/
$_SESSION["param_token_unir_certificado"] = md5('seg' . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
$_SESSION["param_nome_pdf"] = $nomeArquivoCertificado;
header("Location: pdf_certificado.php");              
?>