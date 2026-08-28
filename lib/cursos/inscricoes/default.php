<?php
$dir = dirname(__FILE__);
require_once($dir."/../inscricoes/include/config.php");
$url = "". WB_URL_VIEW."?content=view/inscricao/inscricao_01.php#inscricao";
//$_GET?   executarLogApp($_GET,  'index-d-get')  : "";
$_POST?  executarLogApp($_POST, 'index-d-post') : "";
$_INPUT = file_get_contents("php://input");
$_INPUT?  executarLogApp($_INPUT, 'index-d-input')   : "";
@header("Location: $url");
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
?>