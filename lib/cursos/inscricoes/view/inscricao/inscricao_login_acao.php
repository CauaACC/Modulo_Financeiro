<?php
// DEPRECADO: este arquivo continha um login legado com SQL concatenado direto
// (vulnerabilidade de SQL injection). O fluxo foi substituido pelo login unificado
// em view/inscricao/inscricao_01.php que posta para GerenciarLogin::efetuarLogin.
//
// Callers antigos conhecidos ao ser desativado:
//   - view/inscricao/inscricao_login.php (form action)
//   - view/inscricao/inscricao_trabalhos_inicio.php (redirect de sessao expirada)
//   - view/inscricao/inscricao_congresso_verifica_inscrito.php (redirect pos verificacao)
//   - controller/GerenciarBoletoBancoBrasilAC.php (urlRetorno do boleto BB)
//
// Mantido como stub redirecionador para proteger callers desconhecidos.
require_once(dirname(__FILE__)."/../../include/config.php");

$url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php&MSG=Use_o_login_de_e-mail_e_senha.";
@header("Location: " . $url);
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
