<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/taxa-seguranca.php");
$debug=false;
try {
    $fws_['tx_unica_a_vista']=1;
    $obj = new Taxa();
    $obj->setCodTaxa($fws_['cod_taxa']);
    $obj->getTaxaById($debug);
    $fws_['periodo_inicial'] = formatDateHoraTrans($fws_['periodo_inicial'],'YYYY-MM-DDTHH:II','YYYY-MM-DD HH:II');
    $fws_['periodo_final'] = formatDateHoraTrans($fws_['periodo_final'],'YYYY-MM-DDTHH:II','YYYY-MM-DD HH:II');
    $obj->setAll($fws_);
    $obj->salvar($debug);
} catch (Exception $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/perfil-cadastro-ok.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $obj->getCodTaxa();
} catch (OKException $exc) {
    $MSG = $exc->getMessage();
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/perfil-cadastro-ok.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $obj->getCodTaxa();
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
if(!$debug){
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=" . $URL . "\"></HTML>";
}
