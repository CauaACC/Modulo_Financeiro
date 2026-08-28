<?php
/**
 * Proxy do passo 2 do fluxo AGENDA: grava cod_perfil escolhido na sessão e faz forward para
 * a view de dados pessoais (que continua sendo o passo 3 reaproveitado).
 *
 * O forward via require_once preserva o POST original (cod_oficina[] e cod_perfil) para
 * que inscricao_congresso_cadastro_dados_pessoais.php receba os campos como sempre recebeu.
 */
require_once(dirname(__FILE__) . "/../../include/config.php");

if ($OP_TIPO_FLUXO_INSCRICAO != 'AGENDA') {
    $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
    exit();
}

// Anti-bot: rejeita se honeypot preenchido OU submit muito rápido
if (isset($fws_['hp_website']) && $fws_['hp_website'] !== '') {
    if (function_exists('executarLog')) {
        executarLog('AGENDA honeypot passo2: ip=' . ($_SERVER['REMOTE_ADDR'] ?? '') . ' hp=' . substr($fws_['hp_website'], 0, 50), 'antibot-agenda', true);
    }
    $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">"; exit();
}
if (isset($fws_['hp_t']) && (time() - (int) $fws_['hp_t']) < 2) {
    if (function_exists('executarLog')) {
        executarLog('AGENDA timecheck passo2: ip=' . ($_SERVER['REMOTE_ADDR'] ?? '') . ' delta=' . (time() - (int) $fws_['hp_t']) . 's', 'antibot-agenda', true);
    }
    $url = WB_URL_VIEW . "?content=view/inscricao/agenda_passo2_grupo.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">"; exit();
}

if (isset($fws_['cod_perfil']) && (int) $fws_['cod_perfil'] > 0) {
    $_SESSION['_agenda']['cod_perfil'] = (int) $fws_['cod_perfil'];
}

require_once(dirname(__FILE__) . '/inscricao_congresso_cadastro_dados_pessoais.php');
