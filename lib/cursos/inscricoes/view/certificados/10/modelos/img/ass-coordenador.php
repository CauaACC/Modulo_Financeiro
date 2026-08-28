<?php
$dir = dirname(__FILE__);
define('COD_EVENTO',  '10');
define('WB_GRUPO', 'hutec');
$_GET['figura'] = true;
require_once($dir."/../../../../../../inscricoes/include/config.php");
$fws_['cod_oficina'] = addslashesopctec($_GET['id']);
$arquivo = "coordenador-".numberPad($fws_['cod_oficina'],7).".jpg";
$filepath = WB_PATH_LOG_CERTIFICADOS."../assinaturas/";
//echo $filepath .$arquivo;
//exit();
if(file_exists($filepath .$arquivo)){
    header("Content-Type: image/png");
    $fn=fopen($filepath .$arquivo,"r");
    fpassthru($fn);
}else{
   echo "Assinatura não encontrada, já estamos contactando o administrador do sistema";
}