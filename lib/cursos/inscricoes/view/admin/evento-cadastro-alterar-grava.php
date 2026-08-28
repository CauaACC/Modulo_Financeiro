<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/evento-seguranca.php");
try {
    $debug=false;
    $obj = new Evento();
    $obj->setCodEvento($fws_['cod_evento']);
    $obj->getEventoById($debug);
    $fws_['data_ini'] = str_replace('T', ' ', $fws_['data_ini']);
    $fws_['data_fim'] = str_replace('T', ' ', $fws_['data_fim']);
    $obj->setAll($fws_);
    $obj->salvar($debug);
} catch (Exception $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
//    $CONTENT = "view/admin/evento-cadastro-ok.php";
    $CONTENT = "view/admin/evento-cadastro-alterar.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_evento=" . $obj->getCodEvento();
} catch (OKException $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
//    $CONTENT = "view/admin/evento-cadastro-ok.php";
    $CONTENT = "view/admin/evento-cadastro-alterar.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_evento=" . $obj->getCodEvento();
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
if(!$debug){
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=" . $URL . "\"></HTML>";
}
