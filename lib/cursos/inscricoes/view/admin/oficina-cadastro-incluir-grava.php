<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/taxa-seguranca.php");
try {
    $debug = false;
    $obj = new Oficina();
    $fws_['data_ini'] = $fws_['data_inicio'];
    $obj->setAll($fws_);
    $obj->salvar($debug);
} catch (OKException $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/oficina-cadastro-ok.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_oficina=" . $obj->getCodOficina();
} catch (Exception $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/oficina-cadastro-incluir.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_oficina=" . $obj->getCodOficina();
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
if(!$debug){
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=" . $URL . "\"></HTML>";
}
