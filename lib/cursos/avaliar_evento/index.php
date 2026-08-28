<?php
$dir = dirname(__FILE__);
require_once($dir."/../inscricoes/include/config.php");
$fws_['cod_inscrito'] = $fwsGet_['cod_inscrito'];
$objArquivo = new GerenciarCertificadoAC();
$objArquivo->avaliarEvento($fws_);
