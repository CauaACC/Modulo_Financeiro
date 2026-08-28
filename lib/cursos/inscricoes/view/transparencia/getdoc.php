<?php

$id=$_POST['cod'];
if (!is_numeric($id)){
    $id = "0";
}

$filepath = "../artigos/";
$arquivo = $id.".doc";
if (file_exists($filepath.$arquivo)) {
    header("Content-Description: File Transfer");
    header("Cache-Control: private"); // work-around for IE
    header("Content-Type: application/msword");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
    header("Content-Length: " . @filesize($filepath.$arquivo));
    header("Pragma: public");
    readfile($filepath.$arquivo) or die();
} else {
    echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();return false;</script>";
}
?>
