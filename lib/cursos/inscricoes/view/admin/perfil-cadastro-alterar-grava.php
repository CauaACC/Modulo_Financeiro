<?php
require_once(dirname(__FILE__)."/../../include/config.php");
try {
    $debug=false;
    $obj = new Perfil();
    $obj->setCodPerfil($fws_['cod_perfil']);
    $obj->getPerfilById($debug);
    $obj->setAll($fws_);
    $obj->salvar($debug);
} catch (Exception $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/perfil-cadastro-ok.php";
    $URL = "index.php?content=" . $CONTENT;
} catch (OKException $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/perfil-cadastro-alterar.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_perfil=" . $obj->getCodPerfil();
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
if(!$debug){
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
}
