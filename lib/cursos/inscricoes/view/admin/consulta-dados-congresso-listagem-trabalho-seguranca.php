<?php
require_once(dirname(__FILE__)."/../../include/config.php");

//@session_start();
$fwaListarTrabalhos    = @FWA_Autenticacao($_SESSION['_cod_usuario'],1,5,false,false);
$fwaPremiacaoTrabalhos = @FWA_Autenticacao($_SESSION['_cod_usuario'],5,5,false,false);
$fwaAvaliacaoTrabalhos = @FWA_Autenticacao($_SESSION['_cod_usuario'],4,5,false,false);
$fwaListarInscritos   = @FWA_Autenticacao($_SESSION['_cod_usuario'],12,5,false,false);

if(($fwaListarTrabalhos==false) and ($fwaPremiacaoTrabalhos==false) and ($fwaAvaliacaoTrabalhos==false) and ($fwaListarInscritos==false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $MSG = "Não há usuário logado ou o não possui direito de acesso a este módulo do sistema!";
    $_SESSION['MSG'] = $MSG;
    echo "<a href='" . $URL . "'>".$MSG."</a><br>";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . ""."\"></HTML>";
    exit;
}

#@$nome_usuario = $_SESSION['_nome_usuario'];
