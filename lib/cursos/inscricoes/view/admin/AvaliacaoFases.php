<?php
/**
 * AvaliacaoFases.php
 *
 * Tela de entrada do modulo Avaliacao.
 *
 * Mostra todas as fases (etapas) do evento (lidas de tab_avaliacao_etapa)
 * ordenadas por ordem_etapa, com:
 *   - Status (Em andamento / Concluida / Aguardando / Inativa)
 *   - Botao "Avaliar" clicavel apenas quando a fase esta ativa E mapeada
 *     a uma tela existente
 *
 * Substitui o redirect direto antigo do menu top "Avaliacao" para a tela
 * da Fase 1, permitindo ao avaliador entender em que momento do processo
 * o evento esta.
 */
// SEGURANCA: deve rodar SEMPRE, antes de qualquer logica/renderizacao.
// O include faz FWA_Autenticacao e redireciona pro login se nao houver sessao valida.
require_once(dirname(__FILE__) . "/../../view/admin/consulta-avaliacao-inscrito-seguranca.php");

$objSRV = new AvaliacaoFasesAC();
$listaFases = $objSRV->carregarFases($debug);
$ordemAtiva = $objSRV->getOrdemAtivaCorrente($listaFases);

// Gestor de avaliação pode CONSULTAR fases concluídas/inativas (read-only).
// Avaliador comum só acessa a fase ativa.
$ehGestorAvaliacao = FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false);
?>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_avaliacao.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="padding:14px 22px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:16px; font-weight:600; color:#333;">
            <i class="fa fa-tasks"></i> Avalia&ccedil;&atilde;o &mdash; Fases do Processo
        </h4>
        <p style="margin:6px 0 0; font-size:13px; color:#666;">
            O processo de avalia&ccedil;&atilde;o &eacute; dividido em fases conforme o cronograma do edital.
            Abaixo, a lista das fases cadastradas para este evento. <strong>Apenas a fase em andamento</strong>
            permite avalia&ccedil;&atilde;o no momento.
        </p>
    </div>

    <div style="padding:18px 22px;">
        <?php if (empty($listaFases)) { ?>
            <div class="alert alert-warning" style="margin:0;">
                <strong>Nenhuma fase de avalia&ccedil;&atilde;o cadastrada para este evento.</strong><br>
                Cadastre as fases em
                <a href="index.php?content=view/admin/GerenciarEtapa.php">Etapas de Avalia&ccedil;&atilde;o</a>.
            </div>
        <?php } else { ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach ($listaFases as $etapa) {
                    $status = $objSRV->getStatusFase($etapa, $ordemAtiva);
                    $tela   = $objSRV->getTelaDaFase($etapa->getOrdemEtapa());
                    $temTela = !empty($tela);
                    // Gestor pode consultar fase Concluida (read-only). Avaliador comum so EM_ANDAMENTO.
                    $podeConsultar = $ehGestorAvaliacao && !empty($status['consultavel']);
                    $botaoClicavel = ($status['btnAtivo'] || $podeConsultar) && $temTela;
                    $modoConsulta  = !$status['btnAtivo'] && $podeConsultar;
                    $rotuloBotao   = $modoConsulta ? 'Consultar' : 'Avaliar';
                ?>
                <div style="border:1px solid #e8e8e8; border-radius:10px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; gap:18px; background:#fff;">
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                            <span style="font-size:13px; font-weight:600; color:#888;">
                                Fase <?php echo (int)$etapa->getOrdemEtapa() ?>
                            </span>
                            <span style="font-size:11px; padding:3px 10px; border-radius:12px; background:<?php echo $status['corBg'] ?>; color:<?php echo $status['cor'] ?>; font-weight:600;">
                                <?php echo $status['label'] ?>
                            </span>
                            <?php if (!$temTela) { ?>
                                <span style="font-size:11px; padding:3px 10px; border-radius:12px; background:#fde8e8; color:#a94442; font-weight:600;" title="Sem tela mapeada para esta fase ainda">
                                    <i class="fa fa-wrench"></i> Em desenvolvimento
                                </span>
                            <?php } ?>
                        </div>
                        <div style="font-size:15px; font-weight:600; color:#333;">
                            <?php echo htmlspecialchars($etapa->getNomeEtapa()) ?>
                        </div>
                        <?php if ($etapa->getDescricao()) { ?>
                            <div style="font-size:13px; color:#666; margin-top:3px;">
                                <?php echo htmlspecialchars($etapa->getDescricao()) ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div>
                        <?php if ($botaoClicavel) {
                            $classeBtn = $modoConsulta ? 'btn btn-default' : 'btn btn-primary modalBtnSalvar';
                        ?>
                            <a href="index.php?content=<?php echo $tela ?>" class="<?php echo $classeBtn ?>" role="button">
                                <?php if ($modoConsulta) { ?><i class="fa fa-eye"></i> <?php } ?>
                                <?php echo $rotuloBotao ?>
                                <?php if (!$modoConsulta) { ?><i class="fa fa-arrow-right"></i><?php } ?>
                            </a>
                        <?php } else { ?>
                            <button type="button" class="btn btn-default modalBtnCancelar" disabled style="opacity:0.6; cursor:not-allowed;">
                                Avaliar
                            </button>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
