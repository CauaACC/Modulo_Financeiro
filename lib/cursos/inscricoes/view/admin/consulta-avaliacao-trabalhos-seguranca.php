<?php
require_once(dirname(__FILE__)."/../../include/config.php");
//@session_start();
#$_SESSION['_cod_usuario']  = 1;
#$_SESSION['_nome_usuario'] = "Admin";
@$cod_usuario = $_SESSION['_cod_usuario'];
@$cod_sistema = 4;
@$cod_direito = 5;
@$cod_msg     = 1;
@$redirect = true;
If(@FWA_Autenticacao($cod_usuario,$cod_sistema,$cod_direito,$cod_msg,$redirect)==false){ exit; }

#@$nome_usuario = $_SESSION['_nome_usuario'];

