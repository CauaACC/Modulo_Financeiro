<?php
/**
 * Valida se a sessao administrativa ainda e valida.
 * Compara o session_token da sessao com o do banco.
 * Se invalido (ex: senha foi trocada em outra sessao), desloga o usuario.
 *
 * Deve ser chamado nas paginas administrativas apos verificar $_SESSION['_logado'].
 * Nao interrompe se session_token ainda nao existe (migracao gradual).
 */
function FWA_ValidarSessaoAdmin() {
    // Somente validar se e admin logado e tem session_token
    if (empty($_SESSION['_logado']) || empty($_SESSION['_administrativo'])) {
        return true;
    }
    // Nao validar token quando esta em modo "logar como" (token e do usuario original)
    if (!empty($_SESSION['_logadoComo'])) {
        return true;
    }
    if (empty($_SESSION['_session_token']) || empty($_SESSION['_cod_usuario'])) {
        return true; // migracao gradual — sessoes antigas sem token continuam funcionando
    }

    try {
        ConexaoDB::getInstance();
        $conex = ConexaoDB::getConexaoDB();

        $sql = sprintf(
            "SELECT session_token FROM sis_usuarios WHERE cod_usuario = %d AND status = 'ATIVADA' LIMIT 1",
            intval($_SESSION['_cod_usuario'])
        );
        $rs = $conex->Execute($sql);

        if (!$rs || $rs->EOF) {
            // Usuario nao encontrado — invalidar
            FWA_InvalidarSessaoAdmin();
            return false;
        }

        $tokenBanco = $rs->fields['session_token'];

        // Se o banco tem token e nao bate com a sessao, invalidar
        if (!empty($tokenBanco) && $tokenBanco !== $_SESSION['_session_token']) {
            FWA_InvalidarSessaoAdmin();
            return false;
        }
    } catch (Exception $e) {
        // Em caso de erro de banco, nao bloquear — apenas logar
        return true;
    }

    return true;
}

/**
 * Invalida a sessao admin e redireciona para login.
 */
function FWA_InvalidarSessaoAdmin() {
    $_SESSION['_logado'] = false;
    $_SESSION['MSG'] = 'Sua sessao foi encerrada. Faca login novamente.';
    $url = WB_URL_VIEW . "?content=view/admin/login.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
    exit();
}
