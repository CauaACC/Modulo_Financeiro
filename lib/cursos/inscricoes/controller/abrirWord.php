<?php
session_start();
//$arquivoPDFFinal = $_SERVER['DOCUMENT_ROOT'] . "../log/sw/insc/certificados/06/01/verso/2015_2353_certificado_11_colorido_verso.doc";
if($_SESSION['nomeArquivoPDFFinal']==''){
    $_SESSION['nomeArquivoPDFFinal'] = "arquivo.doc";
}
header("application/force-download");
header('Content-Type: application/ms-word');
header("Content-Disposition: attachment; filename=".$_SESSION['nomeArquivoPDFFinal']."");
readfile($_SESSION['arquivoPDFFinal']);
$_SESSION['arquivoPDFFinal'] = null;
$_SESSION['nomeArquivoPDFFinal'] = null;
?>