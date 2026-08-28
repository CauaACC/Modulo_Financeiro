<?php
$dir = dirname(__FILE__);
$_GET['figura'] = true;
require_once($dir."/../../../../../inscricoes/include/config.php");
$fws_['cod_oficina'] = addslashes($_GET['id']);
$arquivo = "coordenador-".numberPad($fws_['cod_oficina'],7).".jpg";
$filepath = WB_PATH_LOG_CERTIFICADOS."../assinaturas/";
executarLog($filepath .$arquivo,"emissaoCertificado",false);
//echo $filepath .$arquivo;
//exit();
if(file_exists($filepath .$arquivo)){
    header("Content-Type: image/png");
    $fn=fopen($filepath .$arquivo,"r");
    fpassthru($fn);
}else{
   echo "Assinatura não encontrada, já estamos contactando o administrador do sistema";
   executarLog($filepath .$arquivo. '-nao-encontrada',"emissaoCertificado",true);
}