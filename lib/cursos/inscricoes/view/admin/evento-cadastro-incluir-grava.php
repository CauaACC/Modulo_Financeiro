<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/evento-seguranca-incluir.php");
try {
    $debug = false;
    $obj = new Evento();
    $fws_['ano_evento'] = $fws_['ano_evento']? Date('Y') : $fws_['ano_evento'];
    // O formulario usa input datetime-local, que manda 2026-08-10T08:00 (ISO).
    // So troca o T pelo espaco, igual ao evento-cadastro-alterar-grava.php.
    $fws_['data_ini'] = !empty($fws_['data_ini'])? str_replace('T', ' ', $fws_['data_ini']) : null;
    $fws_['data_fim'] = !empty($fws_['data_fim'])? str_replace('T', ' ', $fws_['data_fim']) : null;
    $obj->setAll($fws_);
    
    $obj->salvar($debug);
} catch (OKException $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/evento-cadastro-alterar.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_evento=" . $obj->getCodEvento();
} catch (Exception $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/evento-cadastro-incluir.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_evento=" . $obj->getCodEvento();
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
if(!$debug){
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=" . $URL . "\"></HTML>";
}
