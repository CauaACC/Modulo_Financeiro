<?php
// DEPRECADO (2026-04-19): este arquivo continha o gravador da inscricao legado,
// com SQL concatenado de $_SESSION sem escape em SELECT, INSERT e UPDATE
// (multiplas vulnerabilidades de SQL injection). Intocado desde 2023-07-10.
//
// Substituido pelo fluxo novo: cadastro_dados_pessoais.php postando para
// CommandAC com c=GerenciarInscricao&m=salvarInscricao, que usa o padrao
// Entidade/DAO com queries parametrizadas.
//
// Callers conhecidos ao ser desativado:
//   - view/inscricao/inscricao_congresso_confirmar.php (form action, tambem deprecado)
//
// Stub redirecionador mantido para proteger callers desconhecidos e acesso direto via URL.
require_once(dirname(__FILE__)."/../../include/config.php");

$url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php";
@header("Location: " . $url);
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
