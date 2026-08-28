<?php
require_once(dirname(__FILE__)."/../../include/config.php");

//@session_start();
#$_SESSION['_cod_usuario']  = 1;
#$_SESSION['_nome_usuario'] = "Admin";
@$cod_usuario = $_SESSION['_cod_usuario'];
@$cod_direito = 5;
@$cod_msg     = true;
$redirecionar = false;
If((@FWA_Autenticacao($cod_usuario,enumSistema::SIS_GERENCIAR_INSCRITO,$cod_direito,$cod_msg,$redirecionar)==false)
 and(@FWA_Autenticacao($cod_usuario,'13',$cod_direito,$cod_msg,$redirecionar)==false)){
    exit;
}

#@$nome_usuario = $_SESSION['_nome_usuario'];
?>
