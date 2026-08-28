<?php
// DEPRECADO (2026-08-24): a tela "Por atividade" virou a aba "Por atividade"
// dentro de Textos do Certificado, para deixar claro que o que se edita ali e
// TEXTO (titulo, qualificacao, periodo, data, carga horaria) da atividade.
// Substituido por: view/admin/TextosCertificado.php#por-atividade
// A listagem em si nao foi perdida: virou o parcial
// view/admin/certificado-por-atividade-conteudo.php, usado pela aba.
//
// Callers conhecidos ao ser desativado:
//   - view/admin/area_admin_menu_certificados.php (item de menu, ja apontado
//     para a tela nova)
//
// Stub redirecionador mantido para proteger favoritos e acesso direto via URL.
require_once(dirname(__FILE__) . "/../../include/config.php");

$url = WB_URL_VIEW . "?content=view/admin/TextosCertificado.php#por-atividade";
@header("Location: " . $url);
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
