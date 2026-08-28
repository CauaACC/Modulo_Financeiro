<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$url = "". WB_URL_VIEW."?content=view/anais/anais_pesquisa.php";
@header("Location: $url");
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();

$fws_['ordenar']   = $fws_['ordenar'] == '' ? 'cod_area_tematica' : $fws_['ordenar'];
$fws_['situacao']  = 'ATIVADA';
//$fws_['avaliacao'] = 'aprovado';
$fws_['comunicado']  = '0';
$fws_['content'] = 'view/admin/consulta-dados-congresso-listagem-trabalhos-anais.php'; 
$fws_['perspectiva'] = "anais";

include(dirname(__FILE__)."/../view/admin/consulta-dados-congresso-listagem-trabalhos-padrao.php");
?>