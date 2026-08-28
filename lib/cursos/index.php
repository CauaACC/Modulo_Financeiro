<?php
require_once(dirname(__FILE__)."/inscricoes/include/config.php");
$url = "". WB_URL_VIEW."?content=view/inscricao/inscricao_01.php";
//$_GET?   executarLogApp($_GET,  'index-r-get')  : "";
$_POST?  executarLogApp($_POST, 'index-r-post') : "";
$_INPUT = file_get_contents("php://input");
$_INPUT?  executarLogApp($_INPUT, 'index-r-input')   : "";
@header("Location: $url");
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
