<?php
require_once(dirname(__FILE__)."/../../include/config.php");

//@session_start();
@$cod_msg     = false;
if ((@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_GERENCIAR_INSCRITO,5,$cod_msg,false)==false)
and (@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_CONSULTAR_INSCRITOS,5,$cod_msg,false)==false)) {
    echo "<blockquote>";
    echo "<br><br><br>";
    echo "Não há usuário logado, ou usuário logado não tem permissão de acesso";
    echo "<br><br><br>";
    echo "<a href='index.php?content=view/admin/login.php'>Clique aqui para efetuar login</a>";
    echo "<br><br><br>&nbsp;";
    echo "</blockquote>";
    exit;
}

