<?php
require_once(dirname(__FILE__)."/../../include/config.php");


@$cod_msg     = false;
// Aceita avaliador (SIS_AVALIACAO_INSCRITO) ou gestor (SIS_GERENCIAR_AVALIACAO_TRABALHOS).
// Gestor pode consultar fases concluidas em modo read-only (controlado nas telas).
$temPermissaoAvaliador = FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, $cod_msg, false) !== false;
$temPermissaoGestor    = FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, $cod_msg, false) !== false;
if (!$temPermissaoAvaliador && !$temPermissaoGestor) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<blockquote>";
    echo "<br><br><br>";
    echo $_SESSION['MSG'];
    echo "<br><br><br>";
    echo "<a href='" . $URL . "'>Clique aqui para efetuar login</a>";
    echo "<br><br><br>&nbsp;";
    echo "</blockquote>";

    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

// Regra: permitir alteração de avaliação existente, por evento
// Para habilitar alteração, adicionar o cod_evento no array abaixo
// 129 (Pró-REDE 2025) — Fase 1: a linha em tab_trabalhos_avaliacao_inscricao
// é criada pelo candidato ao solicitar o recurso; o avaliador precisa editar para
// gravar parecer/classificação após recurso.
$eventosPermitidosAlteracaoAvaliacao = [129];
$permitirAlteracaoAvaliacao = in_array(COD_EVENTO, $eventosPermitidosAlteracaoAvaliacao);

