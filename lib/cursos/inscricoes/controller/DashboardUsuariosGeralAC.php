<?php

/**
 * Controller do dashboard geral de usuarios (cross-event).
 *
 * Acesso restrito ao usuario 668 (super admin). Mesmo padrao de gate usado
 * em CarregarBotoesAC.php:130. Qualquer outro usuario recebe redirect silencioso.
 */
class DashboardUsuariosGeralAC {

    const COD_USUARIO_AUTORIZADO = 668;

    public static function autorizado() {
        return isset($_SESSION['_cod_usuario'])
            && (int) $_SESSION['_cod_usuario'] === self::COD_USUARIO_AUTORIZADO;
    }

    public static function guard() {
        if (!self::autorizado()) {
            $url = WB_URL_VIEW . "?content=view/admin/default.php";
            @header("Location: " . $url);
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
            exit();
        }
    }

    public static function carregarDadosDashboard($diasPadrao = 30, $diasPorEvento = 7, $filtroEmailLogin = '', $deTentativas = '', $ateTentativas = '', $debug = false) {
        self::guard();
        $dao = new DashboardUsuariosGeralDAO();
        return [
            'inscritosPorDia'      => $dao->getInscritosPorDia($diasPadrao, $debug),
            'tentativasDetalhePorDia' => $dao->getTentativasSucessoFalhaPorDia($diasPadrao, $debug), // total/sucesso/falha por dia
            'tentativasPorLogin'   => $dao->getTentativasPorLogin($diasPadrao, $filtroEmailLogin, $deTentativas, $ateTentativas, $debug), // uma linha por e-mail
            'loginsSucessoPorDia'  => $dao->getLoginsPorDia($diasPadrao, 1, $debug),
            'loginsFalhaPorDia'    => $dao->getLoginsPorDia($diasPadrao, 0, $debug),
            'inscritosPorEvento'   => $dao->getInscritosPorEventoUltimosDias($diasPorEvento, $debug),
            'inscritosPorEstado'   => $dao->getInscritosPorEstado($debug),
            'inscritosPorCidadePR' => $dao->getInscritosPorCidadePR($debug),
            'totais'               => $dao->getTotais($diasPorEvento, $debug),
            'diasPadrao'           => (int) $diasPadrao,
            'diasPorEvento'        => (int) $diasPorEvento,
            'filtroEmailLogin'     => $filtroEmailLogin,
            'deTentativas'         => $deTentativas,
            'ateTentativas'        => $ateTentativas,
        ];
    }

    /**
     * Ecoa o interior do painel "Usuarios ativos agora" (contador + tabela).
     * Usado no load da tela E no AJAX de 30s -- assim a atualizacao automatica
     * troca so este bloco, sem recarregar a pagina nem repintar os graficos.
     */
    public static function renderBlocoAtivos() {
        $ativos = PingSessaoAC::listarAtivos(120);
        ?>
        <div class="panel-heading" style="display:flex;justify-content:space-between;align-items:center;">
            <strong>&#128994; Usu&aacute;rios ativos agora
                <span class="badge" style="background:#28a745;margin-left:8px;"><?php echo count($ativos); ?></span>
            </strong>
            <small class="text-muted">Com ping nos &uacute;ltimos 2 min. Atualiza automaticamente a cada 30s.</small>
        </div>
        <div class="panel-body" style="padding:0;">
            <?php if (empty($ativos)): ?>
                <div class="text-muted text-center" style="padding:20px;">Nenhum usu&aacute;rio com ping ativo no momento.</div>
            <?php else: ?>
                <table class="table table-striped table-hover" style="margin-bottom:0;">
                    <thead>
                        <tr><th>Inscrito</th><th>E-mail</th><th>Evento</th><th style="text-align:center;">&Uacute;ltimo ping</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ativos as $u): ?>
                        <?php
                        $urlFiltroInscrito  = 'index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_evento=' . (int) $u['cod_evento'] . '&cod_inscrito=' . (int) $u['cod_inscrito'];
                        $urlDashboardEvento = 'index.php?content=view/admin/default.php&cod_evento=' . (int) $u['cod_evento'];
                        $sec = (int) $u['segundos_desde_ping'];
                        ?>
                        <tr>
                            <td>
                                <small class="text-muted">#<?php echo (int) $u['cod_inscrito']; ?></small>
                                <a href="<?php echo $urlFiltroInscrito; ?>" title="Abrir listagem filtrada por este inscrito"><?php echo htmlspecialchars($u['nome'] ?? ''); ?></a>
                            </td>
                            <td><small><?php echo htmlspecialchars($u['email'] ?? ''); ?></small></td>
                            <td><small><a href="<?php echo $urlDashboardEvento; ?>" title="Abrir dashboard admin deste evento"><?php echo htmlspecialchars($u['titulo_evento'] ?? ('Evento ' . $u['cod_evento'])); ?></a></small></td>
                            <td style="text-align:center;white-space:nowrap;">
                                <?php if ($sec < 35): ?><span style="color:#28a745;font-weight:600;">agora (<?php echo $sec; ?>s)</span>
                                <?php elseif ($sec < 90): ?><span style="color:#f0ad4e;"><?php echo $sec; ?>s atr&aacute;s</span>
                                <?php else: ?><span style="color:#d9534f;"><?php echo $sec; ?>s atr&aacute;s</span><?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?php echo htmlspecialchars($u['ip'] ?? '&mdash;'); ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * AJAX: devolve so o bloco de ativos (para o polling de 30s da tela).
     * Se a sessao expirou/nao autorizado, responde 401 + JSON sessaoExpirada
     * (NAO redireciona -- redirect seria seguido pelo fetch e injetaria a tela de
     * login dentro do painel). O JS trata: para o polling e mostra o overlay.
     */
    public function carregarAtivosAgora($fws_, $debug = false) {
        header('Cache-Control: no-store, no-cache, must-revalidate');
        if (!self::autorizado()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['sessaoExpirada' => true, 'area' => 'admin']);
            exit;
        }
        self::renderBlocoAtivos();
        exit;
    }
}
