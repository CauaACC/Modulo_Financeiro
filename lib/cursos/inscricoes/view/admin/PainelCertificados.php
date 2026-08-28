<?php
/**
 * @deprecated 2026-08-24 — Painel de Certificados: virou stub redirecionador.
 * Substituido por view/admin/ConfiguracaoCertificado.php (Regras), a landing
 * do modulo. NAO confundir com o controller PainelCertificadosAC, que segue
 * ativo servindo Assinaturas e Programas.
 */
// DEPRECADO (2026-08-24): o Painel era a visao geral do modulo (cards de aptos,
// tabelas por perfil e por atividade) e a primeira tela de Certificados. Saiu do
// menu porque competia com a configuracao pela porta de entrada: quem vinha
// ajustar o certificado caia num relatorio.
// Substituido por: view/admin/ConfiguracaoCertificado.php (Regras), que agora e
// a landing do modulo.
//
// O que ficou onde:
//   - assinaturas  -> view/admin/PecasCertificado.php
//   - programas    -> view/admin/ProgramasCertificado.php
//   - textos       -> view/admin/TextosCertificado.php
//   - modal do certificado da atividade -> view/admin/certificado-atividade-modal.php
//
// Callers conhecidos ao ser desativado: NENHUM link aponta para ca. Restam so
// deteccoes de pagina ativa por nome (`strpos($pg, 'PainelCertificados')`) em
// area_admin_menu_administracao.php, area_admin_menu_certificados.php e
// area_admin_menu_configuracao.php, que sao string, nao link — e continuam
// funcionando com o redirect.
//
// ATENCAO: o controller PainelCertificadosAC NAO foi desativado. Ele segue
// servindo Assinaturas e Programas (c=PainelCertificados: verPeca, verVerso,
// excluirPrograma, salvarAssinaturaPronta, carregarAtividade, gravarAtividade).
// Nao apagar o controller junto com esta view.
//
// Stub redirecionador mantido para proteger favoritos e acesso direto via URL:
// a tela nao tinha link, mas a URL circulou por semanas e nao da para assumir
// que ninguem a guardou.
require_once(dirname(__FILE__) . "/../../include/config.php");

$url = WB_URL_VIEW . "?content=view/admin/ConfiguracaoCertificado.php";
@header("Location: " . $url);
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
