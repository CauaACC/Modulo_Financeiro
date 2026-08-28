<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/taxa-seguranca.php");
try {
    $debug = false;
    $obj = new Taxa();
    $fws_['tx_unica_a_vista']=1;
    $obj->setAll($fws_);
    $fws_['cod_perfil']?  $obj->setCodPerfil($fws_['cod_perfil'])   : $obj->setCodPerfil('0');
    $fws_['cod_oficina']? $obj->setCodOficina($fws_['cod_oficina']) : $obj->setCodOficina('0');
    $obj->setAll($fws_);
    $obj->salvar($debug);
} catch (OKException $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/perfil-cadastro-ok.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $obj->getCodTaxa();
} catch (Exception $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/perfil-cadastro-incluir.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $obj->getCodTaxa();
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
if(!$debug){
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=" . $URL . "\"></HTML>";
}
