<?php
require_once(dirname(__FILE__)."/../../include/config.php");

session_start();
$_SESSION['_logado'] = false;
$_SESSION = array();
unset($_SESSION);
session_destroy();

$url = "". WB_URL_VIEW."?content=view/admin/default.php";
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$url."\"></HTML>";       
exit();
?>
