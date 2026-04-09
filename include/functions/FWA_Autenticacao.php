<?php
/**
 * Autenticação simplificada para o módulo financeiro standalone.
 * Verifica se o usuário está logado e possui permissão no sistema solicitado.
 */

function FWA_UsuarioLogado($cod_usuario) {
    if ($cod_usuario != "") {
        return true;
    }
    return false;
}

function FWA_DireitoSobreSistema($cod_usuario, $cod_sistema, $cod_direito = 5, $cod_evento = false) {
    try {
        ConexaoDB::getInstance();
        $conex = ConexaoDB::getConexaoDB();

        if ($cod_evento === false) {
            $cod_evento = COD_EVENTO;
        }

        $sql = sprintf("SELECT u.cod_usuario, g.cod_grupo, s.cod_sistema
            FROM sis_usuarios u,
                 sis_ligacao_usuarios_grupos lug,
                 sis_grupos g,
                 sis_direitos_grupos_sistemas dgs,
                 sis_sistemas s
            WHERE u.cod_usuario = '%s'
              AND u.cod_usuario = lug.cod_usuario
              AND lug.cod_evento = '%s'
              AND lug.cod_evento = dgs.cod_evento
              AND lug.cod_grupo = dgs.cod_grupo
              AND dgs.cod_direito = '%s'
              AND dgs.cod_sistema = '%s'
              AND lug.cod_grupo = g.cod_grupo
              AND dgs.cod_sistema = s.cod_sistema",
            $cod_usuario, $cod_evento, $cod_direito, $cod_sistema);

        $rs = $conex->Execute($sql);

        if ((!$rs) or ($rs->EOF)) {
            return false;
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function FWA_Autenticacao($cod_usuario, $cod_sistema, $cod_direito = 5, $cod_msg = false, $redirect = false, $cod_evento = false) {
    if (!FWA_UsuarioLogado($cod_usuario)) {
        if ($redirect) {
            $URL = WB_URL_VIEW . "?content=view/admin/login.php";
            $_SESSION['MSG'] = "Usuário não está logado.";
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
            exit;
        }
        return false;
    }

    if ($cod_evento === 'TODOS') {
        $cod_evento = false;
    }

    return FWA_DireitoSobreSistema($cod_usuario, $cod_sistema, $cod_direito, $cod_evento);
}

function FWA_AutenticacaoGeral($cod_usuario, $cod_sistema, $cod_direito = 5, $cod_msg = false, $redirect = false) {
    return FWA_Autenticacao($cod_usuario, $cod_sistema, $cod_direito, $cod_msg, $redirect, 'TODOS');
}
