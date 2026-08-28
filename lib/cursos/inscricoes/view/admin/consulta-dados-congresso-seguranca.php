<?php
require_once(dirname(__FILE__)."/../../include/config.php");


@$cod_msg     = false;
if ((@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_GERENCIAR_ADM_GERAL,5,$cod_msg,false)==false)
and (@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_GERENCIAR_INSCRITO,5,$cod_msg,false)==false)
and (@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_GERENCIAR_AREAS_TEMATICAS,5,$cod_msg,false)==false)
and (@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_CONSULTAR_PAINEL_BORDO,5,$cod_msg,false)==false)
and (@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_CONSULTAR_INSCRITOS,5,$cod_msg,false)==false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<blockquote>";
    echo "<br><br><br>";
    echo $_SESSION['MSG'];
    echo "<br><br><br>";
    echo "<a href='" . $URL . "'>Clique aqui para efetuar login</a>";
    echo "<br><br><br>&nbsp;";
    echo "</blockquote>";

    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

