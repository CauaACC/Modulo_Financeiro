<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/evento-seguranca.php");
try {
    $debug = false;
    $obj = new EventoConfiguracao();
    $obj->setAll($fws_);
    $obj->salvar($debug);
} catch (OKException $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/evento-configurar-alterar.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $obj->getCodEvento();
} catch (Exception $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/evento-configurar-incluir.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $obj->getCodEvento();
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
if(!$debug){
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=" . $URL . "\"></HTML>";
}
