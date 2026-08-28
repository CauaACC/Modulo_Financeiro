<?php
$iphone  = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$ipad    = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
$palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
$berry   = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
$ipod    = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
$symbian = strpos($_SERVER['HTTP_USER_AGENT'],"Symbian");

if ($iphone || $ipad || $ipod == true) {
    header('Location: https://apps.apple.com/us/app/5%C2%BA-cpspc/id1484870355#?platform=iphone');
    exit();
    //echo "Olá, sou um iphone";
} else 
if ($android == true) {
    header('Location: https://play.google.com/store/apps/details?id=dev.inesco.congresso');
    exit();
    //echo "Olá, sou um android";
} else {
    header('Location: http://opctec.com.br/inesco/7mostraparanaensesaude');
    exit();
   //echo "Olá, eu sou um computador";
}
?>