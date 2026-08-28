<?php
// DEPRECADO (2026-04-19): este arquivo era a tela inicial do fluxo de inscricao
// legado (confirmacao de dados antes de gravar). Postava para
// inscricao_congresso_confirmar.php e depois para inscricao_congresso_grava.php,
// todos com concatenacao de $_SESSION em SQL. Intocado desde 2023-07-10.
//
// Arquivo totalmente ORFAO: nenhuma referencia no codigo aponta para ele.
//
// Substituido pelo fluxo novo: cadastro_dados_pessoais.php postando para
// CommandAC com c=GerenciarInscricao&m=salvarInscricao.
//
// Stub redirecionador mantido apenas para proteger acesso direto via URL.
require_once(dirname(__FILE__)."/../../include/config.php");

$url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php";
@header("Location: " . $url);
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
