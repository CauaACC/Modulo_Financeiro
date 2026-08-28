<?php
/**
 * Ping de sessao - usado pela area do inscrito para:
 *  1. Manter a sessao viva enquanto a aba estiver aberta (keep-alive)
 *  2. Detectar expiracao de sessao via JS e mostrar aviso antes
 *     que o upload falhe silenciosamente.
 *
 * Retorna JSON:
 *  - logado: bool (se SESSION tem _logado=1 e _cod_inscrito)
 *  - restante_seg: int (segundos ate expirar, baseado em gc_maxlifetime)
 *  - cod_inscrito: int|null
 *  - agora: int (timestamp servidor)
 */
class PingSessaoAC {

    /**
     * Grava/atualiza ping do inscrito em sis_sessoes_ativas.
     * Chamado a cada verificacao de ping quando ha sessao valida.
     */
    private static function registrarPing($codInscrito, $codEvento, $ip, $userAgent) {
        try {
            if ($codInscrito <= 0 || $codEvento <= 0) return;
            $conex = FWA_Conexao('OpcTec');
            $ip  = $ip ? substr($ip, 0, 45) : null;
            $ua  = $userAgent ? substr($userAgent, 0, 255) : null;
            $sql = sprintf(
                "INSERT INTO sis_sessoes_ativas (cod_inscrito, cod_evento, ultimo_ping, ip, user_agent)
                 VALUES (%d, %d, NOW(), %s, %s)
                 ON DUPLICATE KEY UPDATE ultimo_ping = NOW(), ip = VALUES(ip), user_agent = VALUES(user_agent)",
                $codInscrito,
                $codEvento,
                $ip  === null ? 'NULL' : "'" . addslashes($ip)  . "'",
                $ua  === null ? 'NULL' : "'" . addslashes($ua)  . "'"
            );
            $conex->Execute($sql);
        } catch (Exception $e) {
            // silencioso: ping nao pode falhar por causa disso
        }
    }

    /**
     * Lista inscritos com ping recente (considerado "ativo agora").
     * $janelaSegundos: quantos segundos sem ping considerar "ativo". Default 120.
     * Retorna array com: cod_inscrito, cod_evento, nome, email, titulo_evento,
     *   ultimo_ping, segundos_desde_ping, ip.
     */
    public static function listarAtivos($janelaSegundos = 120, $debug = false) {
        $conex = FWA_Conexao('OpcTec');
        $janela = (int) $janelaSegundos;
        // Limpa registros antigos (mais velhos que 1h) em cada leitura
        try {
            $conex->Execute("DELETE FROM sis_sessoes_ativas WHERE ultimo_ping < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        } catch (Exception $e) { /* ignora */ }

        $sql = "
            SELECT s.cod_inscrito,
                   s.cod_evento,
                   s.ultimo_ping,
                   TIMESTAMPDIFF(SECOND, s.ultimo_ping, NOW()) AS segundos_desde_ping,
                   s.ip,
                   i.nome_inscrito AS nome,
                   i.e_mail        AS email,
                   e.titulo        AS titulo_evento
            FROM sis_sessoes_ativas s
            LEFT JOIN tab_inscritos i ON i.cod_inscrito = s.cod_inscrito
            LEFT JOIN tab_eventos   e ON e.cod_evento   = s.cod_evento
            WHERE s.ultimo_ping >= DATE_SUB(NOW(), INTERVAL {$janela} SECOND)
            ORDER BY s.ultimo_ping DESC
        ";
        $rs = $conex->Execute($sql);
        $out = [];
        if ($rs) {
            while (!$rs->EOF) {
                $out[] = $rs->fields;
                $rs->MoveNext();
            }
        }
        return $out;
    }

    /**
     * Logout forcado: destroi a sessao PHP imediatamente.
     * Usado pelo JS quando o limite cliente (1h de tela) zera.
     */
    public function logoutForcado($fws_, $debug = false) {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        // Remove o usuario da tabela de sessoes ativas (monitor)
        if (!empty($_SESSION['_cod_inscrito'])) {
            try {
                $conex = FWA_Conexao('OpcTec');
                $conex->Execute(sprintf(
                    "DELETE FROM sis_sessoes_ativas WHERE cod_inscrito = %d",
                    (int) $_SESSION['_cod_inscrito']
                ));
            } catch (Exception $e) { /* ignora */ }
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        @session_destroy();
        echo json_encode(['ok' => true]);
        exit;
    }

    public function verificar($fws_, $debug = false) {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $agora = time();
        $logado = !empty($_SESSION['_logado']) && !empty($_SESSION['_cod_inscrito']);

        // gc_maxlifetime do PHP (ver phpinfo: 1440s = 24min)
        $lifetime = (int) ini_get('session.gc_maxlifetime');
        if ($lifetime <= 0) { $lifetime = 1440; }

        // Ultimo momento conhecido em que a sessao foi 'viva'.
        // GerenciarLoginAC seta _tempo = time() no login.
        // Se nao existir, usa o proprio now (sessao recem criada).
        $ultimoAtivo = !empty($_SESSION['_tempo']) ? (int) $_SESSION['_tempo'] : $agora;
        $idade = $agora - $ultimoAtivo;
        $restante = $lifetime - $idade;
        if ($restante < 0) { $restante = 0; }

        // Atualiza _tempo para keep-alive (cada ping renova a sessao)
        // e grava UPSERT em sis_sessoes_ativas para o monitor de logados
        if ($logado) {
            $_SESSION['_tempo'] = $agora;
            $restante = $lifetime;
            self::registrarPing(
                (int) $_SESSION['_cod_inscrito'],
                (int) (@$_SESSION['_cod_evento'] ?: COD_EVENTO),
                @$_SERVER['REMOTE_ADDR'],
                @$_SERVER['HTTP_USER_AGENT']
            );
        }

        echo json_encode([
            'logado'        => $logado,
            'restante_seg'  => $restante,
            'lifetime_seg'  => $lifetime,
            'cod_inscrito'  => $logado ? (int) $_SESSION['_cod_inscrito'] : null,
            'agora'         => $agora,
        ]);
        exit;
    }

    /**
     * Heartbeat de PRESENCA para o monitor de logados (Dashboard "ativos agora"),
     * usado por TODA tela do inscrito logado (o cabecalho). Diferenca crucial do
     * verificar(): NAO renova a sessao.
     *  - le cod_inscrito/cod_evento (so leitura), NAO seta _tempo;
     *  - session_abort() descarta qualquer alteracao e fecha SEM reescrever o
     *    arquivo da sessao -> o ping nao adia a expiracao por inatividade;
     *  - grava so o ping em sis_sessoes_ativas (banco, nao mexe na sessao).
     * Assim, quem fica inativo com a aba aberta NAO tem o login mantido pelo ping.
     */
    public function heartbeat($fws_, $debug = false) {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $logado      = !empty($_SESSION['_logado']) && !empty($_SESSION['_cod_inscrito']);
        $codInscrito = $logado ? (int) $_SESSION['_cod_inscrito'] : 0;
        $codEvento   = (int) (@$_SESSION['_cod_evento'] ?: (defined('COD_EVENTO') ? COD_EVENTO : 0));
        $ip          = @$_SERVER['REMOTE_ADDR'];
        $ua          = @$_SERVER['HTTP_USER_AGENT'];

        // NAO renovar a sessao: descarta alteracoes e nao da touch no arquivo.
        @session_abort();

        if ($codInscrito > 0) {
            self::registrarPing($codInscrito, $codEvento, $ip, $ua);
        }
        echo json_encode(['ok' => true, 'online' => $codInscrito > 0]);
        exit;
    }
}
