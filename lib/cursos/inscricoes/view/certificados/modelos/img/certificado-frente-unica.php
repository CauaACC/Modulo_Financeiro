<?php
$dir = dirname(__FILE__);
$_GET['figura'] = true;
require_once($dir."/../../../../../inscricoes/include/config.php");
if(isset($_GET['id']) and is_numeric($_GET['id'])){
    $fws_['cod_oficina'] = addslashesopctec($_GET['id']);
    $codOficina = "-".numberPad($fws_['cod_oficina'],7);
}   
$evArray = array(1,2,3,4,5,6,7,21,61,57,70,98);
if (in_array(COD_EVENTO, $evArray)) {
    $filepath = WB_PATH_LOG_CERTIFICADOS."../midia/";
}else{
    $filepath = WB_ROOT . "../logn/eventos/000/midia/";
}
$arquivo = "certificado-frente-unica".$codOficina.".jpg";
//$filepath = WB_PATH_LOG_CERTIFICADOS."../midia/";
//echo $filepath .$arquivo;
//exit();
if(file_exists($filepath .$arquivo)){
    header("Content-Type: image/png");
    $fn=fopen($filepath .$arquivo,"r");
    fpassthru($fn);
}else{
   echo "Assinatura não encontrada, já estamos contactando o administrador do sistema";
}