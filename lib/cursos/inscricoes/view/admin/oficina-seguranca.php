<?php
require_once(dirname(__FILE__)."/../../include/config.php");

@$cod_msg     = false;
If(@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_GERENCIAR_ATIVIDADES,5,false,true)==false){
    exit; 
}
