<?php
/**
 * GerenciarResultadoFinalFase.php
 *
 * Tela "Resultado Final" — apos o encerramento das janelas de avaliacao e
 * de analise de recurso de uma etapa, o admin geral consolida o resultado
 * preenchendo pontos_total_final, classificacao_status_final e
 * classificacao_final em tab_trabalhos_avaliacao_inscricao.
 *
 * Generica: parametro ?ordemEtapa=N na URL controla qual etapa esta sendo
 * consolidada (padrao 2, que eh a Fase 2 / Anexo II do edital Pro-REDE).
 *
 * Acesso restrito: SIS_GERENCIAR_ADM_GERAL.
 *
 * Pre-condicao para Consolidar: nenhum recurso da etapa pode estar pendente
 * de analise (recurso_texto preenchido + classificacao_status_apos_recurso
 * vazio). O botao executa um pre-check via AJAX antes de abrir o modal de
 * confirmacao.
 *
 * Operacao IDEMPOTENTE: pode ser re-executada quantas vezes precisar (cada
 * execucao gera nova linha em sis_log_consolidacao_etapa, mantendo historico).
 */
require_once(dirname(__FILE__) . "/../../include/config.php");

// Gate especifico: so admin geral entra nesta tela.
if (!FWA_Autenticacao($_SESSION['_cod_usuario'] ?? null, enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
    echo '<div style="padding:30px; font-family:Arial; color:#a94442;">';
    echo 'Acesso negado. Esta tela exige perfil de <strong>Administrador Geral</strong>.';
    echo '</div>';
    return;
}

$ordemEtapa = isset($fwsGet_['ordemEtapa']) ? (int) $fwsGet_['ordemEtapa'] : 2;
if ($ordemEtapa <= 0) {
    $ordemEtapa = 2;
}

$objCF = new ConsolidacaoFase();
$objCF->setOrdemEtapa($ordemEtapa);

$ultima = $objCF->carregarUltimaConsolidacao($debug);
$jaConsolidada = !empty($ultima);

$listaConsolidada = $jaConsolidada ? $objCF->carregarListaConsolidada($debug) : array();

// Contagens para o cabecalho
$qtdTotal = count($listaConsolidada);
$qtdDef   = 0;
$qtdInd   = 0;
foreach ($listaConsolidada as $r) {
    if ($r->getClassificacaoStatusFinal() === 'H') { $qtdDef++; }
    elseif ($r->getClassificacaoStatusFinal() === 'I') { $qtdInd++; }
}

// Filtro de exibicao (apenas client-side)
$filtroSel = isset($fwsGet_['filtro']) ? $fwsGet_['filtro'] : 'todos';
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<?php
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_avaliacao.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px; margin-bottom:30px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid #eee;">
        <div>
            <h4 style="margin:0 0 4px; font-size:16px; font-weight:600; color:#333;">
                <i class="fa fa-flag-checkered" style="color:#1565c0;"></i>
                Resultado Final &mdash; Etapa <?php echo (int) $ordemEtapa ?>
            </h4>
            <small style="color:#777;">
                Consolida pontos e classificacao final apos avaliacao + recurso da etapa.
            </small>
        </div>
        <div>
            <?php if ($jaConsolidada) { ?>
                <button type="button" class="btn btn-default" id="btnGerarEditalFinal" title="Gerar edital DOCX com o resultado consolidado">
                    <i class="fa fa-file-word-o"></i> Gerar Edital DOCX
                </button>
                <button type="button" class="btn btn-warning" id="btnConsolidar" title="Re-executar a consolidacao (substitui os campos finais)">
                    <i class="fa fa-refresh"></i> Re-consolidar
                </button>
            <?php } else { ?>
                <button type="button" class="btn btn-primary" id="btnConsolidar">
                    <i class="fa fa-flag-checkered"></i> Consolidar Etapa <?php echo (int) $ordemEtapa ?>
                </button>
            <?php } ?>
        </div>
    </div>

    <?php if ($jaConsolidada) { ?>
    <div style="display:flex; gap:14px; padding:14px 20px; background:#f8f9fa; border-bottom:1px solid #eee; font-size:13px; flex-wrap:wrap;">
        <div style="flex:1; min-width:160px;">
            <div style="color:#777; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Total de inscritos</div>
            <div style="font-size:22px; font-weight:600; color:#333;"><?php echo $qtdTotal ?></div>
        </div>
        <div style="flex:1; min-width:160px;">
            <div style="color:#777; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Deferidos</div>
            <div style="font-size:22px; font-weight:600; color:#2d6a4f;"><?php echo $qtdDef ?></div>
        </div>
        <div style="flex:1; min-width:160px;">
            <div style="color:#777; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Indeferidos</div>
            <div style="font-size:22px; font-weight:600; color:#a94442;"><?php echo $qtdInd ?></div>
        </div>
        <div style="flex:2; min-width:240px;">
            <div style="color:#777; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Ultima consolidacao</div>
            <div style="font-size:13px; color:#333; margin-top:4px;">
                <strong><?php echo htmlspecialchars((string) $ultima->getDataConsolidacao()) ?></strong><br>
                <span style="color:#777;">por</span>
                <strong><?php echo htmlspecialchars((string) $ultima->getIdConsolidacao()) ?></strong>
                <span style="color:#777;">(IP <?php echo htmlspecialchars((string) $ultima->getIpConsolidacao()) ?>)</span>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div style="padding:30px 20px; text-align:center; background:#fffbe6; border-bottom:1px solid #faecc4;">
        <i class="fa fa-info-circle" style="font-size:28px; color:#8a6d3b;"></i>
        <div style="margin-top:8px; color:#555;">
            Esta etapa <strong>ainda nao foi consolidada</strong>. Clique em
            <em>Consolidar Etapa <?php echo (int) $ordemEtapa ?></em> para preencher os campos finais
            (pontos, status e classificacao) com base no resultado pos-recurso.
        </div>
    </div>
    <?php } ?>

    <?php if ($jaConsolidada) { ?>
    <div style="padding:8px 20px; border-bottom:1px solid #eee; display:flex; gap:10px; align-items:center;">
        <label style="font-weight:600; font-size:13px; margin:0;">Mostrar:</label>
        <select id="filtroResultado" class="form-control" style="display:inline-block; width:auto; font-size:13px;">
            <option value="todos">Todos</option>
            <option value="deferidos">Apenas Deferidos</option>
            <option value="indeferidos">Apenas Indeferidos</option>
        </select>
        <small style="color:#999;">Filtro local (nao recarrega a pagina).</small>
    </div>
    <div style="overflow-x:auto;">
        <table class="tbl-fin" id="tabelaResultadoFinal" style="width:100%;">
            <thead>
                <tr>
                    <th style="width:60px; text-align:center;">Class.</th>
                    <th style="width:60px; text-align:center;">Seq</th>
                    <th style="width:110px; text-align:center;">Id Publico</th>
                    <th>Nome Candidato</th>
                    <th style="width:110px; text-align:right;">Pontos</th>
                    <th style="width:140px; text-align:center;">Situacao</th>
                </tr>
            </thead>
            <tbody>
                <?php $seq = 0; foreach ($listaConsolidada as $r) {
                    $seq++;
                    $statusFinal = $r->getClassificacaoStatusFinal();
                    $isDef = ($statusFinal === 'H');
                    $classifFinal = $r->getClassificacaoFinal();
                    $pontos = $r->getPontosTotalFinal();
                    $classGroup = $isDef ? 'def' : 'ind';
                ?>
                <tr data-grupo="<?php echo $classGroup ?>">
                    <td style="text-align:center; font-weight:600; <?php echo $isDef ? 'color:#1565c0;' : 'color:#bbb;' ?>">
                        <?php echo $isDef && $classifFinal !== '' && $classifFinal !== null ? (int)$classifFinal : '&mdash;' ?>
                    </td>
                    <td style="text-align:center; color:#888;"><?php echo $seq ?></td>
                    <td style="text-align:center;"><?php echo htmlspecialchars((string) $r->getIdPublico()) ?></td>
                    <td><?php echo htmlspecialchars((string) $r->getNomeInscrito()) ?></td>
                    <td style="text-align:right;">
                        <?php echo $isDef && $pontos !== null && $pontos !== ''
                            ? number_format((float) $pontos, 2, ',', '.')
                            : '&mdash;' ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($isDef) { ?>
                            <span style="display:inline-block; padding:3px 10px; border-radius:12px; background:#e6f4ea; color:#2d6a4f; font-size:11px; font-weight:600;">DEFERIDO</span>
                        <?php } elseif ($statusFinal === 'I') { ?>
                            <span style="display:inline-block; padding:3px 10px; border-radius:12px; background:#fde8e8; color:#a94442; font-size:11px; font-weight:600;">INDEFERIDO</span>
                        <?php } else { ?>
                            <span style="display:inline-block; padding:3px 10px; border-radius:12px; background:#f1f1f1; color:#555; font-size:11px;">SEM STATUS</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($listaConsolidada)) { ?>
                <tr><td colspan="6" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">
                    Sem registros consolidados.
                </td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>

<!-- ============================================================
     Modal: confirmacao da consolidacao
     ============================================================ -->
<div class="modal fade" id="modalConsolidar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" id="modalConsolidarHeader" style="background:#1565c0; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color:#fff; text-shadow:none; opacity:0.85;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-flag-checkered"></i> <span id="modalConsolidarTitulo">Consolidar Etapa</span></h4>
            </div>
            <div class="modal-body" id="modalConsolidarBody">
                <p>Aguardando verificacao de pre-requisitos...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarConsolidar" disabled>
                    <i class="fa fa-check"></i> Confirmar e Consolidar
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    var ordemEtapa = <?php echo (int) $ordemEtapa ?>;

    function abrirModalConsolidar() {
        var $body = $('#modalConsolidarBody');
        $body.html('<p><i class="fa fa-spinner fa-spin"></i> Verificando recursos pendentes...</p>');
        $('#btnConfirmarConsolidar').prop('disabled', true);
        $('#modalConsolidar').modal('show');

        $.ajax({
            type: 'POST',
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarResultadoFinalFase',
                'm': 'validarRecursosPendentes',
                'ordemEtapa': ordemEtapa,
                '_submit_check': '1'
            },
            dataType: 'json',
            success: function(data) {
                if (!data || data.statusMessage !== '1') {
                    $body.html('<div class="alert alert-danger">' + ((data && data.message) || 'Erro na verificacao.') + '</div>');
                    return;
                }
                if (data.totalPendentes > 0) {
                    var html = '<div class="alert alert-danger">';
                    html += '<strong><i class="fa fa-ban"></i> Consolidacao bloqueada.</strong> ';
                    html += 'Existem <strong>' + data.totalPendentes + ' recurso(s)</strong> sem decisao do avaliador. ';
                    html += 'Conclua a analise dos recursos abaixo antes de consolidar:';
                    html += '</div>';
                    html += '<div style="max-height:300px; overflow-y:auto; border:1px solid #eee; border-radius:6px;">';
                    html += '<table class="table table-condensed" style="margin:0;"><thead><tr><th>Id Publico</th><th>Nome</th></tr></thead><tbody>';
                    data.pendentes.forEach(function(p) {
                        html += '<tr><td>' + escapeHtml(p.id_publico) + '</td><td>' + escapeHtml(p.nome) + '</td></tr>';
                    });
                    html += '</tbody></table></div>';
                    $body.html(html);
                    $('#btnConfirmarConsolidar').prop('disabled', true);
                } else {
                    var html = '<div class="alert alert-info" style="margin-bottom:14px;">';
                    html += '<i class="fa fa-info-circle"></i> ';
                    html += 'Nenhum recurso pendente. A consolidacao vai <strong>preencher / sobrescrever</strong> os campos ';
                    html += '<code>pontos_total_final</code>, <code>classificacao_status_final</code> e ';
                    html += '<code>classificacao_final</code> de todos os inscritos da etapa <strong>' + ordemEtapa + '</strong>.';
                    html += '</div>';
                    html += '<p>Os campos <em>pontos_total_confirmado</em>, <em>pontos_total_apos_recurso</em>, ';
                    html += '<em>classificacao_status</em> e <em>classificacao_status_apos_recurso</em> <strong>nao serao alterados</strong> ';
                    html += '(historia preservada).</p>';
                    html += '<p>A operacao eh <strong>idempotente</strong>: pode ser re-executada se for necessario corrigir.</p>';
                    html += '<p>Esta acao sera registrada no log de auditoria com seu usuario, IP e data/hora.</p>';
                    $body.html(html);
                    $('#btnConfirmarConsolidar').prop('disabled', false);
                }
            },
            error: function() {
                $body.html('<div class="alert alert-danger">Erro de comunicacao com o servidor.</div>');
            }
        });
    }

    function executarConsolidacao() {
        var $body = $('#modalConsolidarBody');
        $('#btnConfirmarConsolidar').prop('disabled', true);
        $body.append('<div id="msgExecucao" style="margin-top:10px;"><i class="fa fa-spinner fa-spin"></i> Consolidando...</div>');

        $.ajax({
            type: 'POST',
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarResultadoFinalFase',
                'm': 'consolidar',
                'ordemEtapa': ordemEtapa,
                '_submit_check': '1'
            },
            dataType: 'json',
            success: function(data) {
                if (!data || data.statusMessage !== '1') {
                    $('#msgExecucao').html('<div class="alert alert-danger" style="margin:0;">' + ((data && data.message) || 'Erro ao consolidar.') + '</div>');
                    return;
                }
                $('#msgExecucao').html('<div class="alert alert-success" style="margin:0;">' + escapeHtml(data.message) + '</div>');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            },
            error: function() {
                $('#msgExecucao').html('<div class="alert alert-danger" style="margin:0;">Erro de comunicacao com o servidor.</div>');
            }
        });
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
            return ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'})[c];
        });
    }

    $(function() {
        $('#btnConsolidar').on('click', abrirModalConsolidar);
        $('#btnConfirmarConsolidar').on('click', executarConsolidacao);

        // Filtro local: mostra/esconde linhas pelo data-grupo
        $('#filtroResultado').on('change', function() {
            var v = $(this).val();
            $('#tabelaResultadoFinal tbody tr').each(function() {
                var g = $(this).data('grupo');
                if (v === 'todos' || (v === 'deferidos' && g === 'def') || (v === 'indeferidos' && g === 'ind')) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $('#btnGerarEditalFinal').on('click', function() {
            // O edital final eh a Fase 3 na config do EditalPublicacao,
            // que le os campos *_final da etapa de origem.
            var url = '<?php echo WB_URL_COMMAND ?>'
                    + '&c=GerenciarEditalPublicacao'
                    + '&m=gerarEditalDOCX'
                    + '&ordemEtapa=3'
                    + '&filtro=todos'
                    + '&_submit_check=1';
            window.location.href = url;
        });
    });
})();
</script>
