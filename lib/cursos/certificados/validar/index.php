<?php
$dir = dirname(__FILE__);
$_GET['figura']=true;
require_once($dir."/../../inscricoes/include/config.php");
$fws_['idArquivo'] = $fwsGet_['id'];
$textoLogo = "Id verificado".$fws_['idArquivo'];
// dd($fws_);
executarLog($textoLogo,"validarCertificado",true);
$objArquivo = new GerenciarCertificadoAC();
$objArquivo->validarCertificado($fws_);
