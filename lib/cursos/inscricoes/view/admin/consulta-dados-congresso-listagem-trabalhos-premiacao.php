<?php
$fws_['ordenar']   = $fws_['ordenar'] == '' ? 'cod_trabalho' : $fws_['ordenar'];
$fws_['situacao']  = 'ATIVADA';
//$fws_['avaliacao'] = 'aprovado';
$fws_['comunicado']  = '0';
$fws_['content'] = 'view/admin/consulta-dados-congresso-listagem-trabalhos-premiacao.php'; 
$fws_['perspectiva'] = "premiacao";

include(dirname(__FILE__)."/../view/admin/consulta-dados-congresso-listagem-trabalhos-padrao.php");
?>