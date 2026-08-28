<?php
// DEPRECADO (2026-04-19): este arquivo era a tela de confirmacao do fluxo de
// inscricao legado, com form POST para inscricao_congresso_grava.php (tambem
// deprecado por SQL injection). Intocado desde 2023-07-10.
//
// Substituido pelo fluxo novo: cadastro_dados_pessoais.php postando para
// CommandAC com c=GerenciarInscricao&m=salvarInscricao.
//
// Callers conhecidos ao ser desativado:
//   - erro.php e view/inscricao/erro-.php (link "clique aqui retornar ao processo")
//   - view/inscricao/inscricao_congresso_inicio.php (form hidden content, tambem deprecado)
//   - view/inscricao/inscricao_congresso_consulta_oficina.php (condicional $_SESSION['content'])
//
// Stub redirecionador mantido para proteger callers desconhecidos e acesso direto via URL.
require_once(dirname(__FILE__)."/../../include/config.php");

$url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php";
@header("Location: " . $url);
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
