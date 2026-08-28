<?php
// Dashboard de Avaliacao (filtrado por COD_EVENTO).
// Foco em avaliacao de trabalhos, avaliacao de inscritos e documentos.
// Convive ao lado do antigo Painel de Trabalhos (consulta-dados-congresso-graficos-trabalhos.php)
// e evolui incrementalmente.

require_once(dirname(__FILE__) . "/../../include/config.php");
// SEGURANCA: redireciona pro login se nao houver sessao com permissao adequada.
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$tituloPainel = "Painel de Avaliação:";
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-graficos-cabecalho.php");

$codEvento = COD_EVENTO;

// $objEvConf ja carregado pelo cabecalho/config
if (!isset($objEvConf) || !$objEvConf) {
    $objEvConf = new EventoConfiguracao();
    $objEvConf->setCodEvento($codEvento);
    $objEvConf->getEventoConfiguracaoById();
}

$abas = DashboardAvaliacaoAC::definirAbasAtivas($objEvConf);

$dadosTrabalhos = !empty($abas['trabalhos'])
    ? DashboardAvaliacaoAC::carregarDadosTrabalhos($codEvento)
    : null;

$dadosAvaliacoes = !empty($abas['avaliacoes'])
    ? DashboardAvaliacaoAC::carregarDadosAvaliacoes($codEvento)
    : null;

// Atividade recente (ultimas inscricoes, trabalhos e arquivos). So carrega se a aba Trabalhos estiver ativa.
$dadosAtividade = !empty($abas['trabalhos'])
    ? DashboardAvaliacaoAC::carregarAtividadeRecente($codEvento, 10)
    : null;

function fmtTempoDesde($dataHora) {
    if (empty($dataHora)) return 'sem atividade';
    $ts = strtotime($dataHora);
    if ($ts === false) return 'sem atividade';
    $diff = time() - $ts;
    if ($diff < 60)   return 'há ' . $diff . 's';
    if ($diff < 3600) return 'há ' . (int) floor($diff / 60) . ' min';
    if ($diff < 86400) {
        $h = (int) floor($diff / 3600);
        return 'há ' . $h . 'h' . ($h == 1 ? '' : '');
    }
    $d = (int) floor($diff / 86400);
    return 'há ' . $d . ' dia' . ($d == 1 ? '' : 's');
}

function fmtDataHoraCurta($dataHora) {
    if (empty($dataHora)) return '—';
    $ts = strtotime($dataHora);
    if ($ts === false) return htmlspecialchars($dataHora);
    return date('d/m H:i', $ts);
}

function urlListagemInscrito($codInscrito) {
    $codInscrito = (int) $codInscrito;
    if ($codInscrito <= 0) return '';
    return 'index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_inscrito=' . $codInscrito . '&listar=1';
}

// Helpers
function fmtGraficoDia($lista) {
    $linhas = '';
    foreach ($lista as $row) {
        $dia = isset($row['dia']) ? formatDateView($row['dia']) : '';
        $total = (int) ($row['total'] ?? 0);
        $linhas .= "['" . $dia . "'," . $total . "],";
    }
    return $linhas;
}
function fmtGraficoCategoria($lista, $colCat, $colTotal = 'total') {
    $linhas = '';
    foreach ($lista as $row) {
        $cat = isset($row[$colCat]) ? addslashes((string) $row[$colCat]) : '';
        if ($cat === '') continue;
        $total = (int) ($row[$colTotal] ?? 0);
        $linhas .= "['" . $cat . "'," . $total . "],";
    }
    return $linhas;
}
function fmtGraficoMatrizAreaFormato($lista) {
    // Agrupa por area e separa "Pesquisa Original" vs "Relato de Experiência"
    $porArea = [];
    $formatos = [];
    foreach ($lista as $row) {
        $area = addslashes((string) ($row['titulo'] ?? ''));
        $fmt = (string) ($row['formato_texto'] ?? '');
        $total = (int) ($row['total'] ?? 0);
        if ($area === '' || $fmt === '') continue;
        if (!isset($porArea[$area])) $porArea[$area] = [];
        $porArea[$area][$fmt] = $total;
        $formatos[$fmt] = true;
    }
    $listaFormatos = array_keys($formatos);
    if (empty($listaFormatos)) return ['linhas' => '', 'formatos' => []];
    $linhas = '';
    foreach ($porArea as $area => $valoresFormato) {
        $linhas .= "['" . $area . "'";
        foreach ($listaFormatos as $fmt) {
            $linhas .= ',' . ((int) ($valoresFormato[$fmt] ?? 0));
        }
        $linhas .= "],";
    }
    return ['linhas' => $linhas, 'formatos' => $listaFormatos];
}

$matrizAreaFormato = !empty($dadosTrabalhos)
    ? fmtGraficoMatrizAreaFormato($dadosTrabalhos['aprovadosAreaFormato'])
    : ['linhas' => '', 'formatos' => []];
?>

<p class="text-muted">Painel de avaliação do evento. Abas visíveis conforme configuração.</p>

<?php if (!empty($abas['trabalhos'])) { ?>
<div class="row" style="margin-bottom:15px;">
    <div class="col-sm-3">
        <div class="panel panel-default"><div class="panel-body text-center">
            <h4 style="color:#337ab7;"><?php echo $dadosTrabalhos['totais']['total_trabalhos']; ?></h4>
            <small>Total de trabalhos</small>
        </div></div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-default"><div class="panel-body text-center">
            <h4 style="color:#5cb85c;"><?php echo $dadosTrabalhos['totais']['trabalhos_aprovados']; ?></h4>
            <small>Aprovados</small>
        </div></div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-default"><div class="panel-body text-center">
            <h4 style="color:#d9534f;"><?php echo $dadosTrabalhos['totais']['trabalhos_reprovados']; ?></h4>
            <small>Reprovados</small>
        </div></div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-default"><div class="panel-body text-center">
            <h4 style="color:#f0ad4e;"><?php echo $dadosTrabalhos['totais']['trabalhos_com_presenca']; ?></h4>
            <small>Com presença registrada</small>
        </div></div>
    </div>
</div>
<?php } ?>

<ul class="nav nav-tabs" role="tablist">
    <?php
    $primeiraAba = true;
    if (!empty($abas['trabalhos'])) { ?>
    <li role="presentation" class="<?= $primeiraAba ? 'active' : '' ?>"><a href="#tab-trabalhos" role="tab" data-toggle="tab" data-bs-toggle="tab">Trabalhos</a></li>
    <?php $primeiraAba = false; } ?>
    <?php if (!empty($abas['avaliacoes'])) { ?>
    <li role="presentation" class="<?= $primeiraAba ? 'active' : '' ?>"><a href="#tab-avaliacoes" role="tab" data-toggle="tab" data-bs-toggle="tab">Avaliações</a></li>
    <?php $primeiraAba = false; } ?>
    <?php if (!empty($abas['documentos'])) { ?>
    <li role="presentation" class="<?= $primeiraAba ? 'active' : '' ?>"><a href="#tab-documentos" role="tab" data-toggle="tab" data-bs-toggle="tab">Documentos</a></li>
    <?php $primeiraAba = false; } ?>
</ul>

<?php if (empty($abas['trabalhos']) && empty($abas['avaliacoes']) && empty($abas['documentos'])) { ?>
<div class="alert alert-warning" style="margin-top:15px;">
    Nenhum módulo de avaliação ativo para este evento.
</div>
<?php } ?>

<div class="tab-content" style="padding-top:15px;">

    <!-- ABA TRABALHOS -->
    <?php
    $primeiraPane = true;
    if (!empty($abas['trabalhos'])) { ?>
    <div role="tabpanel" class="tab-pane <?= $primeiraPane ? 'active' : '' ?>" id="tab-trabalhos">

        <?php
        // Link para o Painel de Trabalhos antigo — ainda util para drill-down por situacao
        // (clicar em "Aprovados", "Reprovados", etc. abre listagem filtrada).
        // So aparece quando faz sentido: evento com submissao e avaliacao nao-por-candidato.
        if ($objEvConf->getTrabSubmissao() == 1 && $objEvConf->getTrabAvaliacaoPor() != 'CANDIDATO') { ?>
        <div style="margin-bottom:12px; text-align:right;">
            <a href="index.php?content=view/admin/consulta-dados-congresso-graficos-trabalhos.php"
               class="btn btn-default btn-xs"
               title="Visão antiga com tabelas detalhadas e drill-down por situação de avaliação">
                <i class="fa fa-list-alt"></i> Ver versão antiga (com drill-down)
            </a>
        </div>
        <?php } ?>

        <?php if (!empty($dadosAtividade)) { ?>
        <!-- Atividade recente: card "ultima atividade" + 3 listas (inscricoes, trabalhos, arquivos) -->
        <div class="row" style="margin-bottom:10px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center;">
                        <strong><i class="fa fa-clock-o"></i> Atividade recente</strong>
                        <span class="label label-<?= !empty($dadosAtividade['ultimaAtividade']) ? 'success' : 'default' ?>">
                            Última atividade <?= htmlspecialchars(fmtTempoDesde($dadosAtividade['ultimaAtividade'])) ?>
                            <?php if (!empty($dadosAtividade['ultimaAtividade'])) { ?>
                                <small style="opacity:0.85;">(<?= htmlspecialchars(fmtDataHoraCurta($dadosAtividade['ultimaAtividade'])) ?>)</small>
                            <?php } ?>
                        </span>
                    </div>
                    <div class="panel-body">
                        <div class="row">

                            <div class="col-sm-4">
                                <h5 style="margin-top:0;"><i class="fa fa-user-plus"></i> Últimas inscrições</h5>
                                <?php if (empty($dadosAtividade['ultimasInscricoes'])) { ?>
                                    <p class="text-muted"><small>Nenhuma inscrição ainda.</small></p>
                                <?php } else { ?>
                                    <ul class="list-unstyled" style="font-size:12px; margin-bottom:0;">
                                    <?php foreach ($dadosAtividade['ultimasInscricoes'] as $row) {
                                        $urlIns = urlListagemInscrito($row['cod_inscrito'] ?? 0);
                                        $nome = htmlspecialchars($row['nome_inscrito'] ?? '—');
                                    ?>
                                        <li style="padding:4px 0; border-bottom:1px solid #eee;">
                                            <?php if ($urlIns !== '') { ?>
                                                <a href="<?= $urlIns ?>" title="Abrir inscrito na listagem"><strong><?= $nome ?></strong></a>
                                            <?php } else { ?>
                                                <strong><?= $nome ?></strong>
                                            <?php } ?>
                                            <?php if (!empty($row['perfil'])) { ?>
                                                <span class="text-muted"> · <?= htmlspecialchars($row['perfil']) ?></span>
                                            <?php } ?>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars(fmtDataHoraCurta($row['data_inscricao'])) ?> · <?= htmlspecialchars(fmtTempoDesde($row['data_inscricao'])) ?></small>
                                        </li>
                                    <?php } ?>
                                    </ul>
                                <?php } ?>
                            </div>

                            <div class="col-sm-4">
                                <h5 style="margin-top:0;"><i class="fa fa-file-text-o"></i> Últimos trabalhos</h5>
                                <?php if (empty($dadosAtividade['ultimosTrabalhos'])) { ?>
                                    <p class="text-muted"><small>Nenhum trabalho submetido ainda.</small></p>
                                <?php } else { ?>
                                    <ul class="list-unstyled" style="font-size:12px; margin-bottom:0;">
                                    <?php foreach ($dadosAtividade['ultimosTrabalhos'] as $row) {
                                        $titulo = (string) ($row['titulo_trabalho'] ?? '');
                                        if (mb_strlen($titulo) > 80) $titulo = mb_substr($titulo, 0, 77) . '...';
                                        $urlIns = urlListagemInscrito($row['cod_inscrito'] ?? 0);
                                        $nomeAutor = htmlspecialchars($row['nome_inscrito'] ?? '—');
                                    ?>
                                        <li style="padding:4px 0; border-bottom:1px solid #eee;">
                                            <strong><?= htmlspecialchars($titulo !== '' ? $titulo : '(sem título)') ?></strong><br>
                                            <?php if ($urlIns !== '') { ?>
                                                <a href="<?= $urlIns ?>" title="Abrir inscrito na listagem" class="text-muted"><small><?= $nomeAutor ?></small></a>
                                            <?php } else { ?>
                                                <span class="text-muted"><small><?= $nomeAutor ?></small></span>
                                            <?php } ?>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars(fmtDataHoraCurta($row['data_inclusao'])) ?> · <?= htmlspecialchars(fmtTempoDesde($row['data_inclusao'])) ?></small>
                                        </li>
                                    <?php } ?>
                                    </ul>
                                <?php } ?>
                            </div>

                            <div class="col-sm-4">
                                <h5 style="margin-top:0;"><i class="fa fa-upload"></i> Últimos arquivos enviados</h5>
                                <?php if (empty($dadosAtividade['ultimosArquivos'])) { ?>
                                    <p class="text-muted"><small>Nenhum arquivo enviado ainda.</small></p>
                                <?php } else { ?>
                                    <ul class="list-unstyled" style="font-size:12px; margin-bottom:0;">
                                    <?php foreach ($dadosAtividade['ultimosArquivos'] as $row) {
                                        $titulo = (string) ($row['titulo_trabalho'] ?? '');
                                        if (mb_strlen($titulo) > 60) $titulo = mb_substr($titulo, 0, 57) . '...';
                                        $urlIns = urlListagemInscrito($row['cod_inscrito'] ?? 0);
                                        $nomeArq = htmlspecialchars($row['nome_inscrito'] ?? '—');
                                    ?>
                                        <li style="padding:4px 0; border-bottom:1px solid #eee;">
                                            <span class="label label-info"><?= htmlspecialchars($row['tipo'] ?? '—') ?></span>
                                            <?php if ($urlIns !== '') { ?>
                                                <a href="<?= $urlIns ?>" title="Abrir inscrito na listagem"><strong> <?= $nomeArq ?></strong></a>
                                            <?php } else { ?>
                                                <strong> <?= $nomeArq ?></strong>
                                            <?php } ?>
                                            <?php if ($titulo !== '') { ?><br><span class="text-muted"><small><?= htmlspecialchars($titulo) ?></small></span><?php } ?>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars(fmtDataHoraCurta($row['data_inclusao'])) ?> · <?= htmlspecialchars(fmtTempoDesde($row['data_inclusao'])) ?></small>
                                        </li>
                                    <?php } ?>
                                    </ul>
                                <?php } ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="row">
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTrabalhosPorDia" style="width:100%; height:280px;"></div>
                </div></div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTrabalhosPorFormato" style="width:100%; height:280px;"></div>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTrabalhosPorArea" style="width:100%; height:320px;"></div>
                </div></div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTrabalhosPorEstado" style="width:100%; height:320px;"></div>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTrabalhosAprovadosPorArea" style="width:100%; height:320px;"></div>
                </div></div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTrabalhosReprovadosPorArea" style="width:100%; height:320px;"></div>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTrabalhosPorInstituicao" style="width:100%; height:320px;"></div>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoAprovadosAreaFormato" style="width:100%; height:360px;"></div>
                </div></div>
            </div>
        </div>

    </div>
    <?php $primeiraPane = false; } ?>

    <!-- ABA AVALIACOES -->
    <?php if (!empty($abas['avaliacoes'])) { ?>
    <div role="tabpanel" class="tab-pane <?= $primeiraPane ? 'active' : '' ?>" id="tab-avaliacoes">

        <?php
        $semAvaliador  = (int) ($dadosAvaliacoes['trabalhosSemAvaliador'] ?? 0);
        $totaisInscAv  = $dadosAvaliacoes['totaisAvalInscrito'] ?? ['total_inscritos'=>0,'avaliados'=>0,'com_decisao_recurso'=>0,'com_decisao_final'=>0];
        $avaliacaoPor  = $objEvConf->getTrabAvaliacaoPor();
        ?>

        <?php if ($avaliacaoPor != 'CANDIDATO') { ?>
        <!-- Card para quando avaliacao e por arquivo/trabalho -->
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-4">
                <div class="panel panel-default"><div class="panel-body text-center">
                    <h4 style="color:#f0ad4e;"><?php echo $semAvaliador; ?></h4>
                    <small>Trabalhos sem avaliador atribuído</small>
                </div></div>
            </div>
        </div>
        <?php } ?>

        <?php if ($avaliacaoPor == 'CANDIDATO') { ?>
        <!-- Cards especificos de avaliacao de inscritos (edital/selecao) -->
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-3">
                <div class="panel panel-default"><div class="panel-body text-center">
                    <h4 style="color:#337ab7;"><?php echo $totaisInscAv['total_inscritos']; ?></h4>
                    <small>Total de inscritos</small>
                </div></div>
            </div>
            <div class="col-sm-3">
                <div class="panel panel-default"><div class="panel-body text-center">
                    <h4 style="color:#5cb85c;"><?php echo $totaisInscAv['avaliados']; ?></h4>
                    <small>Avaliados (1ª análise)</small>
                </div></div>
            </div>
            <div class="col-sm-3">
                <div class="panel panel-default"><div class="panel-body text-center">
                    <h4 style="color:#5bc0de;"><?php echo $totaisInscAv['com_decisao_recurso']; ?></h4>
                    <small>Com decisão pós-recurso</small>
                </div></div>
            </div>
            <div class="col-sm-3">
                <div class="panel panel-default"><div class="panel-body text-center">
                    <h4 style="color:#f0ad4e;"><?php echo $totaisInscAv['com_decisao_final']; ?></h4>
                    <small>Com decisão final</small>
                </div></div>
            </div>
        </div>

        <!-- ============ NOVOS BLOCOS: Recursos / Avaliados por momento / Avaliador x Fase x Momento / Evolucao ============ -->
        <?php
        $progressoFases = $dadosAvaliacoes['progressoFases'] ?? [];
        // Totalizadores agregados por momento (a partir do progressoFases)
        $totRecursos   = 0; $totRecAvaliados = 0; $totRecPendentes = 0;
        $totInicial    = 0; $totRecursoOK    = 0; $totFinal        = 0;
        foreach ($progressoFases as $f) {
            $tr  = (int)($f['total_recursos'] ?? 0);
            $tra = (int)($f['recursos_avaliados'] ?? 0);
            $totRecursos     += $tr;
            $totRecAvaliados += $tra;
            $totRecPendentes += max(0, $tr - $tra);
            $totInicial      += (int)($f['avaliacao_inicial_decidida'] ?? 0);
            $totRecursoOK    += (int)($f['recursos_avaliados'] ?? 0);
            $totFinal        += (int)($f['total_publicado'] ?? 0);
        }
        $pctRecAvalGeral = $totRecursos > 0 ? round(($totRecAvaliados / $totRecursos) * 100) : 0;
        ?>

        <?php if (!empty($progressoFases)) { ?>
        <!-- Pedidos de recurso em destaque (cards + barra geral) -->
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-3">
                <div class="panel panel-default"><div class="panel-body text-center">
                    <h4 style="color:#5bc0de;"><?php echo $totRecursos; ?></h4>
                    <small>Pedidos de recurso (todas as fases)</small>
                </div></div>
            </div>
            <div class="col-sm-3">
                <div class="panel panel-default"><div class="panel-body text-center">
                    <h4 style="color:#5cb85c;"><?php echo $totRecAvaliados; ?></h4>
                    <small>Recursos avaliados</small>
                </div></div>
            </div>
            <div class="col-sm-3">
                <div class="panel panel-default"><div class="panel-body text-center">
                    <h4 style="color:#f0ad4e;"><?php echo $totRecPendentes; ?></h4>
                    <small>Recursos pendentes</small>
                </div></div>
            </div>
            <div class="col-sm-3">
                <div class="panel panel-default"><div class="panel-body text-center">
                    <h4 style="color:#337ab7;"><?php echo $pctRecAvalGeral; ?>%</h4>
                    <small>% recursos analisados</small>
                </div></div>
            </div>
        </div>

        <!-- Recursos detalhado por Fase -->
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><i class="fa fa-gavel"></i> Pedidos de recurso por Fase</strong></div>
                    <div class="panel-body" style="padding:0;">
                        <table class="table table-striped" style="margin:0;">
                            <thead>
                                <tr>
                                    <th>Fase</th>
                                    <th style="width:130px; text-align:center;">Pedidos</th>
                                    <th style="width:130px; text-align:center;">Avaliados</th>
                                    <th style="width:130px; text-align:center;">Pendentes</th>
                                    <th style="width:130px; text-align:center;">Habilitados (H)</th>
                                    <th style="width:130px; text-align:center;">Inabilitados (I)</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($progressoFases as $f) {
                                $tr  = (int)($f['total_recursos'] ?? 0);
                                $tra = (int)($f['recursos_avaliados'] ?? 0);
                                $hr  = (int)($f['habilitados_pos_recurso'] ?? 0);
                                $ir  = (int)($f['nao_habilitados_pos_recurso'] ?? 0);
                                $delta = $tr - $tra;
                            ?>
                                <tr>
                                    <td><strong>Fase <?php echo (int)$f['ordem_etapa'] ?></strong> &mdash; <?php echo htmlspecialchars($f['nome_etapa']) ?></td>
                                    <td style="text-align:center;"><strong><?php echo $tr ?></strong></td>
                                    <td style="text-align:center; color:#2d6a4f;"><strong><?php echo $tra ?></strong></td>
                                    <td style="text-align:center;">
                                        <?php if ($delta > 0) { ?>
                                            <span style="background:#fff4e0; color:#a05a00; padding:2px 10px; border-radius:10px; font-size:12px; font-weight:600;"><?php echo $delta ?></span>
                                        <?php } else { ?>
                                            <span style="color:#bbb;">0</span>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align:center; color:#5cb85c;"><?php echo $hr ?></td>
                                    <td style="text-align:center; color:#d9534f;"><?php echo $ir ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avaliados por Fase x Momento (inicial / pos-recurso / final) -->
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong><i class="fa fa-flag-checkered"></i> Avaliados por Fase &times; Momento</strong>
                        <small style="color:#888; font-weight:normal; margin-left:8px;">
                            Total geral: <?php echo $totInicial ?> inicial &middot; <?php echo $totRecursoOK ?> p&oacute;s-recurso &middot; <?php echo $totFinal ?> final
                        </small>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        <table class="table table-striped" style="margin:0;">
                            <thead>
                                <tr>
                                    <th>Fase</th>
                                    <th style="width:120px; text-align:center;">Atribu&iacute;dos</th>
                                    <th style="width:120px; text-align:center;">Inicial</th>
                                    <th style="width:120px; text-align:center;">P&oacute;s-recurso</th>
                                    <th style="width:120px; text-align:center;">Final</th>
                                    <th style="width:120px; text-align:center;">H final</th>
                                    <th style="width:120px; text-align:center;">I final</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($progressoFases as $f) {
                                $totA   = (int)($f['total_avaliacoes'] ?? 0);
                                $eleg   = (int)($f['elegiveis'] ?? 0);
                                $atribuidos = $eleg > 0 ? $eleg : $totA; // elegiveis quando houver; senao cai pro legado
                                $denom  = $atribuidos > 0 ? $atribuidos : $totA; // base do percentual
                                $ini    = (int)($f['avaliacao_inicial_decidida'] ?? 0);
                                $rec    = (int)($f['recursos_avaliados'] ?? 0);
                                $fin    = (int)($f['total_publicado'] ?? 0);
                                $hf     = (int)($f['habilitados_final'] ?? 0);
                                $infl   = (int)($f['nao_habilitados_final'] ?? 0);
                                $pctIni = $denom > 0 ? round(($ini / $denom) * 100) : 0;
                                $pctFin = $denom > 0 ? round(($fin / $denom) * 100) : 0;
                            ?>
                                <tr>
                                    <td><strong>Fase <?php echo (int)$f['ordem_etapa'] ?></strong> &mdash; <?php echo htmlspecialchars($f['nome_etapa']) ?></td>
                                    <td style="text-align:center;" title="Inscritos eleg&iacute;veis para esta fase (Habilitados da fase anterior)"><?php echo $atribuidos ?></td>
                                    <td style="text-align:center;"><strong><?php echo $ini ?></strong> <small style="color:#888;">(<?php echo $pctIni ?>%)</small></td>
                                    <td style="text-align:center;"><?php echo $rec ?></td>
                                    <td style="text-align:center;"><strong><?php echo $fin ?></strong> <small style="color:#888;">(<?php echo $pctFin ?>%)</small></td>
                                    <td style="text-align:center; color:#5cb85c;"><?php echo $hf ?></td>
                                    <td style="text-align:center; color:#d9534f;"><?php echo $infl ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avaliador x Fase x Momento (Top 20) -->
        <?php
        $linhasAFM = $dadosAvaliacoes['avaliadoresFaseMomento'] ?? [];
        if (!empty($linhasAFM)) {
            // Pivota: $matriz[cod_avaliador]['nome'], ['fases'][cod_etapa] = ['ord','nome','ini','rec','fin','total']
            $matriz = [];
            $fasesUnicas = [];
            $totalGeral = [];
            foreach ($linhasAFM as $r) {
                $codAv = (int)$r['cod_avaliador'];
                $codEt = (int)$r['cod_etapa'];
                $ord   = (int)$r['ordem_etapa'];
                if (!isset($matriz[$codAv])) {
                    $matriz[$codAv] = ['nome' => $r['avaliador'], 'login' => $r['login'], 'fases' => []];
                }
                $matriz[$codAv]['fases'][$codEt] = [
                    'ordem' => $ord,
                    'nome'  => $r['nome_etapa'],
                    'ini'   => (int)$r['aval_inicial'],
                    'rec'   => (int)$r['aval_recurso'],
                    'fin'   => (int)$r['aval_final'],
                    'total' => (int)$r['total_atribuidas'],
                ];
                $fasesUnicas[$codEt] = ['ordem' => $ord, 'nome' => $r['nome_etapa']];
                $totalGeral[$codAv] = ($totalGeral[$codAv] ?? 0) + (int)$r['aval_inicial'] + (int)$r['aval_recurso'] + (int)$r['aval_final'];
            }
            uasort($fasesUnicas, function($a, $b) { return $a['ordem'] - $b['ordem']; });
            arsort($totalGeral);
        ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong><i class="fa fa-users"></i> Avalia&ccedil;&otilde;es por Avaliador &times; Fase &times; Momento</strong>
                        <small style="color:#888; font-weight:normal; margin-left:8px;">Top 20 por total geral</small>
                    </div>
                    <div class="panel-body" style="padding:0; overflow-x:auto;">
                        <table class="table table-striped" style="margin:0; min-width:760px;">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="vertical-align:middle;">#</th>
                                    <th rowspan="2" style="vertical-align:middle;">Avaliador</th>
                                    <th rowspan="2" style="vertical-align:middle;">Login</th>
                                    <?php foreach ($fasesUnicas as $codEt => $info) { ?>
                                        <th colspan="3" style="text-align:center; border-left:1px solid #ddd;">
                                            Fase <?php echo (int)$info['ordem'] ?>
                                            <div style="font-weight:normal; font-size:11px; color:#888;"><?php echo htmlspecialchars($info['nome']) ?></div>
                                        </th>
                                    <?php } ?>
                                    <th rowspan="2" style="vertical-align:middle; text-align:center; border-left:1px solid #ddd;">Total</th>
                                </tr>
                                <tr>
                                    <?php foreach ($fasesUnicas as $codEt => $info) { ?>
                                        <th style="text-align:center; font-size:11px; border-left:1px solid #ddd;">Inicial</th>
                                        <th style="text-align:center; font-size:11px;">Recurso</th>
                                        <th style="text-align:center; font-size:11px;">Final</th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $rk = 0; foreach ($totalGeral as $codAv => $tg) { $rk++;
                                $av = $matriz[$codAv];
                            ?>
                                <tr>
                                    <td style="text-align:center;"><?php echo $rk ?></td>
                                    <td><?php echo htmlspecialchars($av['nome']) ?></td>
                                    <td><span style="font-size:12px; color:#666;"><?php echo htmlspecialchars($av['login']) ?></span></td>
                                    <?php foreach ($fasesUnicas as $codEt => $info) {
                                        $cel = $av['fases'][$codEt] ?? null;
                                        $i = $cel ? $cel['ini'] : 0;
                                        $r = $cel ? $cel['rec'] : 0;
                                        $f = $cel ? $cel['fin'] : 0;
                                    ?>
                                        <td style="text-align:center; border-left:1px solid #f0f0f0;"><?php echo $i ?: '<span style="color:#ccc;">0</span>' ?></td>
                                        <td style="text-align:center;"><?php echo $r ?: '<span style="color:#ccc;">0</span>' ?></td>
                                        <td style="text-align:center;"><?php echo $f ?: '<span style="color:#ccc;">0</span>' ?></td>
                                    <?php } ?>
                                    <td style="text-align:center; border-left:1px solid #ddd;"><strong><?php echo $tg ?></strong></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <!-- Evolucao temporal dos Top 10 avaliadores -->
        <?php
        $evol = $dadosAvaliacoes['evolucaoAvaliadores'] ?? [];
        $linhasJs = '';
        $colNomes = [];
        if (!empty($evol)) {
            // Estrutura por avaliador -> dias (e dias unicos ordenados)
            $diasUnicos = [];
            $porAval = [];
            foreach ($evol as $r) {
                $dia = $r['dia'];
                $codAv = (int)$r['cod_avaliador'];
                $diasUnicos[$dia] = true;
                if (!isset($porAval[$codAv])) {
                    $porAval[$codAv] = ['nome' => $r['avaliador'], 'porDia' => []];
                }
                $porAval[$codAv]['porDia'][$dia] = (int)$r['total'];
            }
            ksort($diasUnicos);
            $diasOrd = array_keys($diasUnicos);

            // Monta JS: header [Dia, AvA, AvB, ...] e linhas
            $colNomes = ['Dia'];
            $codigosOrd = array_keys($porAval);
            foreach ($codigosOrd as $codAv) { $colNomes[] = $porAval[$codAv]['nome']; }
            foreach ($diasOrd as $dia) {
                $diaFmt = date('d/m', strtotime($dia));
                $linha = "['" . addslashes($diaFmt) . "'";
                foreach ($codigosOrd as $codAv) {
                    $linha .= ',' . (int)($porAval[$codAv]['porDia'][$dia] ?? 0);
                }
                $linha .= '],';
                $linhasJs .= $linha;
            }
        }
        if (!empty($evol)) {
        ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong><i class="fa fa-line-chart"></i> Evolu&ccedil;&atilde;o por avaliador &mdash; Top 10 (&uacute;ltimos 30 dias)</strong>
                    </div>
                    <div class="panel-body">
                        <div id="graficoEvolucaoAvaliadores" style="width:100%; height:380px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <?php } ?>
        <!-- ============ FIM DOS NOVOS BLOCOS ============ -->

        <!-- Progresso por Fase de Avaliacao -->
        <?php if (!empty($progressoFases)) { ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><i class="fa fa-tasks"></i> Progresso por Fase</strong></div>
                    <div class="panel-body" style="padding:14px 18px;">
                        <?php foreach ($progressoFases as $fase) {
                            $ordemEt   = (int)($fase['ordem_etapa'] ?? 0);
                            $totalAv   = (int)($fase['total_avaliacoes'] ?? 0);
                            $eleg      = (int)($fase['elegiveis'] ?? 0);
                            $emAndHist = (int)($fase['em_andamento_historico'] ?? 0);
                            $denom     = $eleg > 0 ? $eleg : $totalAv;
                            $totalRec  = (int)($fase['total_recursos'] ?? 0);
                            $recAval   = (int)($fase['recursos_avaliados'] ?? 0);
                            $habil     = (int)($fase['habilitados'] ?? 0);
                            $naoHabil  = (int)($fase['nao_habilitados'] ?? 0);
                            $avalHum   = (int)($fase['avaliados_humano'] ?? 0);
                            $ativa     = (int)($fase['situacao'] ?? 0) === 1;
                            $totalDec  = $habil + $naoHabil;
                            $pctTotal  = $denom > 0 ? round(($totalDec / $denom) * 100) : 0;
                            $pctRec    = $totalRec > 0 ? round(($recAval / $totalRec) * 100) : 0;

                            // Snapshots H/I em cada momento (acrescentados pra mostrar evolução)
                            $habIni     = (int)($fase['habilitados_inicial'] ?? 0);
                            $naoHabIni  = (int)($fase['nao_habilitados_inicial'] ?? 0);
                            $habRec     = (int)($fase['habilitados_pos_recurso'] ?? 0);
                            $naoHabRec  = (int)($fase['nao_habilitados_pos_recurso'] ?? 0);
                            $totalPub   = (int)($fase['total_publicado'] ?? 0);
                            $habFin     = (int)($fase['habilitados_final'] ?? 0);
                            $naoHabFin  = (int)($fase['nao_habilitados_final'] ?? 0);
                            $pctPub     = $totalAv > 0 ? round(($totalPub / $totalAv) * 100) : 0;
                            $deltaHRec  = $habRec - $habIni;
                            $deltaIRec  = $naoHabRec - $naoHabIni;
                        ?>
                        <div style="border-bottom:1px solid #eee; padding:12px 0;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <div>
                                    <strong style="font-size:14px;">Fase <?php echo $ordemEt ?> &mdash; <?php echo htmlspecialchars($fase['nome_etapa']) ?></strong>
                                    <?php if ($ativa) { ?>
                                        <span style="background:#e6f4ea; color:#2d6a4f; padding:2px 10px; border-radius:10px; font-size:11px; font-weight:600; margin-left:6px;">Em andamento</span>
                                    <?php } else { ?>
                                        <span style="background:#f1f1f1; color:#888; padding:2px 10px; border-radius:10px; font-size:11px; margin-left:6px;">Inativa</span>
                                    <?php } ?>
                                </div>
                                <div style="font-size:12px; color:#666;">
                                    <span style="color:#5cb85c;"><strong><?php echo $habil ?></strong> habilitados</span>
                                    &middot;
                                    <span style="color:#d9534f;"><strong><?php echo $naoHabil ?></strong> não habilitados</span>
                                </div>
                            </div>

                            <!-- Barra de progresso de classificacao -->
                            <div style="font-size:11px; color:#888; margin-bottom:2px;">
                                Classificação: <?php echo $totalDec ?>/<?php echo $denom ?> (<?php echo $pctTotal ?>%)
                                <?php if ($habIni + $naoHabIni > 0) { ?>
                                    &middot;
                                    <span style="color:#5cb85c;"><?php echo $habIni ?> H</span>
                                    /
                                    <span style="color:#d9534f;"><?php echo $naoHabIni ?> I</span>
                                <?php } ?>
                                <?php if ($emAndHist > 0) { ?>
                                    &middot;
                                    <span style="color:#8a6d3b; background:#fff4e5; padding:1px 8px; border-radius:8px; font-weight:600;" title="Avaliador pegou pra avaliar mas ainda nao salvou decisao">
                                        <?php echo $emAndHist ?> em andamento
                                    </span>
                                <?php } ?>
                            </div>
                            <div style="background:#eee; border-radius:6px; height:10px; overflow:hidden; margin-bottom:8px;">
                                <div style="background:#5cb85c; height:100%; width:<?php echo $pctTotal ?>%; transition:width 0.3s;"></div>
                            </div>

                            <div style="font-size:11px; color:#888; margin-top:8px; margin-bottom:2px;">
                                Recursos:
                                <strong><?php echo $totalRec ?></strong> pedidos
                                <?php if ($totalAv > 0) { ?>
                                    <span style="color:#999;">(de <?php echo $totalAv ?> inscritos)</span>
                                <?php } ?>
                                <?php if ($totalRec > 0) { ?>
                                    &middot;
                                    <?php echo $recAval ?>/<?php echo $totalRec ?> avaliados (<?php echo $pctRec ?>%)
                                    <?php if ($habRec + $naoHabRec > 0) { ?>
                                        &middot;
                                        <span style="color:#5cb85c;"><?php echo $habRec ?> H</span>
                                        /
                                        <span style="color:#d9534f;"><?php echo $naoHabRec ?> I</span>
                                        <?php if ($deltaHRec !== 0 || $deltaIRec !== 0) { ?>
                                            <span style="color:#999;" title="Variação em relação à 1ª avaliação">
                                                (<?php echo ($deltaHRec > 0 ? '+' : '') . $deltaHRec ?> H,
                                                <?php echo ($deltaIRec > 0 ? '+' : '') . $deltaIRec ?> I)
                                            </span>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span style="color:#bbb;">&middot; nenhum recurso solicitado ainda</span>
                                <?php } ?>
                            </div>
                            <div style="background:#eee; border-radius:6px; height:10px; overflow:hidden;">
                                <div style="background:#f0ad4e; height:100%; width:<?php echo $pctRec ?>%; transition:width 0.3s;"></div>
                            </div>

                            <?php if ($totalAv > 0) { ?>
                            <div style="font-size:11px; color:#888; margin-top:8px; margin-bottom:2px;">
                                Publicação final: <?php echo $totalPub ?>/<?php echo $totalAv ?> (<?php echo $pctPub ?>%)
                                <?php if ($habFin + $naoHabFin > 0) { ?>
                                    &middot;
                                    <span style="color:#5cb85c;"><?php echo $habFin ?> H</span>
                                    /
                                    <span style="color:#d9534f;"><?php echo $naoHabFin ?> I</span>
                                <?php } ?>
                            </div>
                            <div style="background:#eee; border-radius:6px; height:10px; overflow:hidden;">
                                <div style="background:#337ab7; height:100%; width:<?php echo $pctPub ?>%; transition:width 0.3s;"></div>
                            </div>
                            <?php } ?>

                            <?php if ($avalHum > 0) { ?>
                            <div style="font-size:11px; color:#999; margin-top:6px;">
                                <i class="fa fa-user"></i> <strong><?php echo $avalHum ?></strong> registros com avaliador humano atribuído
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <!-- Estatisticas de pontuacao Fase 2 -->
        <?php $estPF2 = $dadosAvaliacoes['estatisticasPontuacaoF2'] ?? null; ?>
        <?php if (!empty($estPF2) && (int)$estPF2['total'] > 0) { ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><i class="fa fa-line-chart"></i> Pontuação 2ª Fase &mdash; Estatísticas</strong></div>
                    <div class="panel-body" style="padding:14px 18px;">
                        <div class="row">
                            <div class="col-sm-2 text-center">
                                <h4 style="color:#337ab7; margin:0;"><?php echo (int)$estPF2['total'] ?></h4>
                                <small>Avaliados</small>
                            </div>
                            <div class="col-sm-2 text-center">
                                <h4 style="color:#5cb85c; margin:0;"><?php echo number_format($estPF2['media'], 2, ',', '.') ?></h4>
                                <small>Média</small>
                            </div>
                            <div class="col-sm-2 text-center">
                                <h4 style="color:#5bc0de; margin:0;"><?php echo number_format($estPF2['mediana'], 2, ',', '.') ?></h4>
                                <small>Mediana</small>
                            </div>
                            <div class="col-sm-2 text-center">
                                <h4 style="color:#888; margin:0;"><?php echo number_format($estPF2['minimo'], 2, ',', '.') ?></h4>
                                <small>Mínima</small>
                            </div>
                            <div class="col-sm-2 text-center">
                                <h4 style="color:#f0ad4e; margin:0;"><?php echo number_format($estPF2['maximo'], 2, ',', '.') ?></h4>
                                <small>Máxima</small>
                            </div>
                            <div class="col-sm-2 text-center">
                                <h4 style="color:#999; margin:0;"><?php echo number_format($estPF2['maximo'] - $estPF2['minimo'], 2, ',', '.') ?></h4>
                                <small>Amplitude</small>
                            </div>
                        </div>

                        <?php $histo = $dadosAvaliacoes['histogramaPontuacaoF2'] ?? []; ?>
                        <?php if (!empty($histo)) {
                            $maxFaixa = 0;
                            foreach ($histo as $h) { $maxFaixa = max($maxFaixa, (int)$h['total']); }
                            $faixasMap = [];
                            foreach ($histo as $h) { $faixasMap[$h['faixa']] = (int)$h['total']; }
                            $faixasFixas = ['00-19','20-39','40-59','60-79','80-99','100+'];
                        ?>
                        <hr style="margin:14px 0;">
                        <div style="font-size:12px; color:#666; margin-bottom:8px; font-weight:600;">Distribuição por faixa:</div>
                        <div style="display:flex; align-items:flex-end; justify-content:space-around; height:140px; gap:8px;">
                            <?php foreach ($faixasFixas as $faixa) {
                                $valor = isset($faixasMap[$faixa]) ? $faixasMap[$faixa] : 0;
                                $altura = $maxFaixa > 0 ? max(2, round(($valor / $maxFaixa) * 110)) : 2;
                            ?>
                            <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%;">
                                <div style="font-size:11px; font-weight:600; color:#337ab7; margin-bottom:3px;"><?php echo $valor ?></div>
                                <div style="background:linear-gradient(to top, #337ab7, #5bc0de); width:100%; max-width:50px; height:<?php echo $altura ?>px; border-radius:4px 4px 0 0;"></div>
                                <div style="font-size:11px; color:#888; margin-top:4px;"><?php echo htmlspecialchars($faixa) ?></div>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <!-- Linha do tempo: avaliacoes por dia (ultimos 30 dias) -->
        <?php $avalDia = $dadosAvaliacoes['avaliacoesPorDia'] ?? []; ?>
        <?php if (!empty($avalDia)) {
            $maxDia = 0;
            foreach ($avalDia as $d) { $maxDia = max($maxDia, (int)$d['total']); }
            $totalPeriodo = 0;
            foreach ($avalDia as $d) { $totalPeriodo += (int)$d['total']; }
        ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong><i class="fa fa-calendar"></i> Ritmo de Avaliações &mdash; Últimos 30 dias</strong>
                        <small style="color:#888; font-weight:normal; margin-left:8px;">
                            <?php echo $totalPeriodo ?> avaliações registradas no período
                        </small>
                    </div>
                    <div class="panel-body" style="padding:14px 18px;">
                        <div style="display:flex; align-items:flex-end; height:120px; gap:3px; border-bottom:1px solid #eee; padding-bottom:6px;">
                            <?php foreach ($avalDia as $d) {
                                $valor = (int)$d['total'];
                                $altura = $maxDia > 0 ? max(2, round(($valor / $maxDia) * 100)) : 2;
                                $diaFmt = $d['dia'] ? date('d/m', strtotime($d['dia'])) : '';
                            ?>
                            <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%;" title="<?php echo $diaFmt ?>: <?php echo $valor ?> avaliações">
                                <div style="font-size:10px; color:#5cb85c; margin-bottom:2px;"><?php echo $valor ?></div>
                                <div style="background:#5cb85c; width:100%; max-width:24px; height:<?php echo $altura ?>px; border-radius:3px 3px 0 0;"></div>
                            </div>
                            <?php } ?>
                        </div>
                        <div style="display:flex; gap:3px; padding-top:4px; font-size:10px; color:#888;">
                            <?php foreach ($avalDia as $d) {
                                $diaFmt = $d['dia'] ? date('d/m', strtotime($d['dia'])) : '';
                            ?>
                            <div style="flex:1; text-align:center;"><?php echo $diaFmt ?></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <!-- Em andamento por avaliador (so quando ha etapa ATIVA) -->
        <?php $emAndamento = $dadosAvaliacoes['avaliadoresEmAndamento'] ?? [];
        if (!empty($emAndamento)) {
            // Agrupa por etapa pra mostrar uma tabela por fase em andamento
            $porEtapa = [];
            foreach ($emAndamento as $row) {
                $codEt = (int)$row['cod_etapa'];
                if (!isset($porEtapa[$codEt])) {
                    $porEtapa[$codEt] = [
                        'ordem' => (int)$row['ordem_etapa'],
                        'nome'  => $row['nome_etapa'],
                        'rows'  => [],
                    ];
                }
                $porEtapa[$codEt]['rows'][] = $row;
            }
            foreach ($porEtapa as $grupo) {
                $totalPegou   = 0;
                $totalAval    = 0;
                $totalAtrib   = 0;
                foreach ($grupo['rows'] as $r) {
                    $totalPegou += (int)$r['pegou_sem_decisao'];
                    $totalAval  += (int)$r['avaliadas'];
                    $totalAtrib += (int)$r['total_atribuidas'];
                }
        ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong><i class="fa fa-hourglass-half"></i> Em andamento por avaliador &mdash; Fase <?php echo (int)$grupo['ordem'] ?>: <?php echo htmlspecialchars($grupo['nome']) ?></strong>
                        <small style="color:#888; font-weight:normal; margin-left:8px;">
                            <?php echo $totalPegou ?> pendentes &middot; <?php echo $totalAval ?> avaliadas &middot; <?php echo $totalAtrib ?> atribu&iacute;das
                        </small>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        <table class="table table-striped" style="margin:0;">
                            <thead>
                                <tr>
                                    <th>Avaliador</th>
                                    <th>Login</th>
                                    <th style="width:120px; text-align:center;">Pegou (pendente)</th>
                                    <th style="width:120px; text-align:center;">Avaliadas</th>
                                    <th style="width:120px; text-align:center;">Total atribu&iacute;das</th>
                                    <th style="width:140px; text-align:center;">Progresso</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($grupo['rows'] as $r) {
                                $pegou  = (int)$r['pegou_sem_decisao'];
                                $aval   = (int)$r['avaliadas'];
                                $totalA = (int)$r['total_atribuidas'];
                                $pct    = $totalA > 0 ? round(($aval / $totalA) * 100) : 0;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['avaliador'] ?? '') ?></td>
                                    <td><span style="font-size:12px; color:#666;"><?php echo htmlspecialchars($r['login'] ?? '') ?></span></td>
                                    <td style="text-align:center;">
                                        <?php if ($pegou > 0) { ?>
                                            <span style="background:#fff4e0; color:#a05a00; padding:2px 10px; border-radius:10px; font-size:12px; font-weight:600;"><?php echo $pegou ?></span>
                                        <?php } else { ?>
                                            <span style="color:#bbb;">0</span>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align:center;"><strong style="color:#2d6a4f;"><?php echo $aval ?></strong></td>
                                    <td style="text-align:center;"><?php echo $totalA ?></td>
                                    <td>
                                        <div style="background:#eee; border-radius:6px; height:8px; overflow:hidden;">
                                            <div style="background:#5cb85c; height:100%; width:<?php echo $pct ?>%; transition:width 0.3s;"></div>
                                        </div>
                                        <div style="font-size:10px; color:#888; text-align:center; margin-top:2px;"><?php echo $pct ?>%</div>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php } } ?>

        <!-- Top Avaliadores de Inscritos -->
        <?php $topAvalInsc = $dadosAvaliacoes['topAvaliadoresInscritos'] ?? []; ?>
        <?php if (!empty($topAvalInsc)) { ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><i class="fa fa-trophy"></i> Avaliadores de Inscritos &mdash; Atividade no Evento</strong></div>
                    <div class="panel-body" style="padding:0;">
                        <table class="table table-striped" style="margin:0;">
                            <thead>
                                <tr>
                                    <th style="width:40px; text-align:center;">#</th>
                                    <th>Avaliador</th>
                                    <th>Login</th>
                                    <th style="width:130px; text-align:center;">Avaliações</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $rank = 0; foreach ($topAvalInsc as $av) { $rank++; ?>
                                <tr>
                                    <td style="text-align:center;"><?php echo $rank ?></td>
                                    <td><?php echo htmlspecialchars($av['avaliador'] ?? '') ?></td>
                                    <td><span style="font-size:12px; color:#666;"><?php echo htmlspecialchars($av['login'] ?? '') ?></span></td>
                                    <td style="text-align:center;"><strong><?php echo (int)($av['total'] ?? 0) ?></strong></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <?php } ?>

        <?php if ($avaliacaoPor != 'CANDIDATO') { ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTrabalhosPorStatusAval" style="width:100%; height:320px;"></div>
                </div></div>
            </div>
        </div>
        <?php } ?>

        <?php if ($avaliacaoPor != 'CANDIDATO') { ?>
        <!-- Top avaliadores de trabalhos (legado) — escondido para avaliacao por candidato -->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTopAvaliadores" style="width:100%; height:360px;"></div>
                </div></div>
            </div>
        </div>
        <?php } ?>

    </div>
    <?php $primeiraPane = false; } ?>

    <!-- ABA DOCUMENTOS (Fase 2) -->
    <?php if (!empty($abas['documentos'])) { ?>
    <div role="tabpanel" class="tab-pane <?= $primeiraPane ? 'active' : '' ?>" id="tab-documentos">
        <div class="alert alert-info">
            <strong>Em breve.</strong> Arquivos enviados por tipo (apresentação, avulso, contrato, vídeo, boleto), por dia, % completude.
        </div>
    </div>
    <?php $primeiraPane = false; } ?>

</div>

<?php if (!empty($abas['trabalhos']) || !empty($abas['avaliacoes'])) { ?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load("current", {packages: ["corechart", "bar"]});
google.charts.setOnLoadCallback(function() {
    <?php if (!empty($abas['trabalhos'])) { ?>
    desenharColuna('graficoTrabalhosPorDia',           'Trabalhos enviados por dia',         'Evolução diária das submissões de trabalhos ativos. Útil para ver picos antes de prazos finais.', [<?php echo fmtGraficoDia($dadosTrabalhos['porDia']); ?>]);
    desenharColuna('graficoTrabalhosPorFormato',       'Trabalhos por Formato de Texto',     'Distribuição entre "Pesquisa Original", "Relato de Experiência" e outros formatos configurados.', [<?php echo fmtGraficoCategoria($dadosTrabalhos['porFormato'], 'formato_texto'); ?>]);
    desenharBarra ('graficoTrabalhosPorArea',          'Trabalhos por Área Temática',        'Total de trabalhos submetidos agrupados pela área temática escolhida na inscrição.', [<?php echo fmtGraficoCategoria($dadosTrabalhos['porAreaTematica'], 'titulo'); ?>]);
    desenharColuna('graficoTrabalhosPorEstado',        'Trabalhos enviados por Estado',      'UF do inscrito autor. Mostra o alcance geográfico da chamada.', [<?php echo fmtGraficoCategoria($dadosTrabalhos['porEstado'], 'estado'); ?>]);
    desenharBarra ('graficoTrabalhosAprovadosPorArea', 'Aprovados por Área Temática',        'Apenas trabalhos com avaliação APROVADO, agrupados por área. Mede taxa de aceite por área.', [<?php echo fmtGraficoCategoria($dadosTrabalhos['porAreaAprovados'], 'titulo'); ?>], '#5cb85c');
    desenharBarra ('graficoTrabalhosReprovadosPorArea','Reprovados por Área Temática',       'Trabalhos com avaliação REPROVADO, agrupados por área.', [<?php echo fmtGraficoCategoria($dadosTrabalhos['porAreaReprovados'], 'titulo'); ?>], '#d9534f');
    desenharBarra ('graficoTrabalhosPorInstituicao',   'Trabalhos por Instituição (top 15)', 'Instituições com mais trabalhos submetidos (via campo instituição do inscrito autor).', [<?php echo fmtGraficoCategoria($dadosTrabalhos['porInstituicao'], 'instituicao'); ?>]);

    <?php if (!empty($matrizAreaFormato['linhas'])) { ?>
    desenharMatriz('graficoAprovadosAreaFormato',
                   'Aprovados por Área Temática × Formato de Texto',
                   'Apenas trabalhos APROVADOS, cruzando área temática com formato do texto.',
                   <?php echo json_encode($matrizAreaFormato['formatos']); ?>,
                   [<?php echo $matrizAreaFormato['linhas']; ?>]);
    <?php } else { ?>
    vazioInformativo('graficoAprovadosAreaFormato',
                     'Aprovados por Área Temática × Formato de Texto',
                     'Apenas trabalhos APROVADOS, cruzando área temática com formato do texto.',
                     'fa-table');
    <?php } ?>
    <?php } ?>

    <?php if (!empty($abas['avaliacoes'])) { ?>
    <?php if ($objEvConf->getTrabAvaliacaoPor() != 'CANDIDATO') { ?>
    desenharPizza('graficoTrabalhosPorStatusAval',
                  'Trabalhos por Situação de Avaliação',
                  'Última situação de cada trabalho (aprovado, reprovado, devolvido, submetido, etc.).',
                  [<?php echo fmtGraficoCategoria($dadosAvaliacoes['trabalhosPorStatus'], 'avaliacao'); ?>]);
    <?php } ?>

    <?php if ($objEvConf->getTrabAvaliacaoPor() != 'CANDIDATO') { ?>
    desenharBarra ('graficoTopAvaliadores',
                   'Top 10 Avaliadores (por trabalhos avaliados)',
                   'Avaliadores com mais trabalhos atribuídos neste evento. Útil para ver carga de avaliação.',
                   [<?php echo fmtGraficoCategoria($dadosAvaliacoes['topAvaliadores'], 'avaliador'); ?>]);
    <?php } ?>

    <?php if ($objEvConf->getTrabAvaliacaoPor() == 'CANDIDATO' && !empty($linhasJs)) { ?>
    desenharLinhas('graficoEvolucaoAvaliadores',
                   'Evolução por avaliador (últimos 30 dias)',
                   'Quantidade de avaliações registradas por dia para cada um dos top 10 avaliadores.',
                   <?php echo json_encode($colNomes); ?>,
                   [<?php echo $linhasJs; ?>]);
    <?php } ?>
    <?php } ?>
});

function desenharLinhas(idDiv, titulo, descricao, cabecalho, linhas) {
    var el = document.getElementById(idDiv);
    if (!el) return;
    if (!linhas.length) { vazioInformativo(idDiv, titulo, descricao, 'fa-line-chart'); return; }
    var data = google.visualization.arrayToDataTable([cabecalho].concat(linhas));
    var options = {
        title: titulo,
        legend: {position: 'right'},
        chartArea: {left: 50, right: 200, top: 50, bottom: 50},
        hAxis: {title: 'Dia'},
        vAxis: {title: 'Avaliações', minValue: 0},
        pointSize: 4,
        curveType: 'function'
    };
    new google.visualization.LineChart(el).draw(data, options);
}

function vazioInformativo(idDiv, titulo, descricao, icone) {
    var el = document.getElementById(idDiv);
    if (!el) return;
    icone = icone || 'fa-bar-chart';
    el.innerHTML =
        '<div style="padding:30px 20px; text-align:center; color:#888;">' +
        '<i class="fa ' + icone + '" style="font-size:42px; color:#dbe4ea;"></i>' +
        '<h5 style="margin:10px 0 6px 0; color:#666;">' + titulo + '</h5>' +
        '<div style="font-size:12px; line-height:1.5; max-width:420px; margin:0 auto;">' + descricao + '</div>' +
        '<div style="margin-top:14px;"><span class="label label-default">Sem dados no período</span></div>' +
        '</div>';
}

function desenharColuna(idDiv, titulo, descricao, linhas, cor) {
    var el = document.getElementById(idDiv);
    if (!el) return;
    if (!linhas.length) { vazioInformativo(idDiv, titulo, descricao, 'fa-bar-chart'); return; }
    var data = google.visualization.arrayToDataTable([['Categoria', 'Total']].concat(linhas));
    var options = {title: titulo, legend: {position: 'none'}};
    if (cor) options.colors = [cor];
    new google.visualization.ColumnChart(el).draw(data, options);
}

function desenharBarra(idDiv, titulo, descricao, linhas, cor) {
    var el = document.getElementById(idDiv);
    if (!el) return;
    if (!linhas.length) { vazioInformativo(idDiv, titulo, descricao, 'fa-align-left'); return; }
    var data = google.visualization.arrayToDataTable([['Categoria', 'Total']].concat(linhas));
    var options = {title: titulo, legend: {position: 'none'}, chartArea: {left: 200}};
    if (cor) options.colors = [cor];
    new google.visualization.BarChart(el).draw(data, options);
}

function desenharMatriz(idDiv, titulo, descricao, formatos, linhas) {
    var el = document.getElementById(idDiv);
    if (!el) return;
    if (!linhas.length) { vazioInformativo(idDiv, titulo, descricao, 'fa-table'); return; }
    var header = ['Área'].concat(formatos);
    var data = google.visualization.arrayToDataTable([header].concat(linhas));
    var options = {title: titulo, chartArea: {left: 200}, bars: 'horizontal', isStacked: false};
    new google.visualization.BarChart(el).draw(data, options);
}

function desenharPizza(idDiv, titulo, descricao, linhas) {
    var el = document.getElementById(idDiv);
    if (!el) return;
    if (!linhas.length) { vazioInformativo(idDiv, titulo, descricao, 'fa-pie-chart'); return; }
    var data = google.visualization.arrayToDataTable([['Categoria', 'Total']].concat(linhas));
    var options = {title: titulo, pieHole: 0.4, legend: {position: 'right'}};
    new google.visualization.PieChart(el).draw(data, options);
}
</script>
<?php } ?>
<?php
// Fecha as 3 divs abertas por consulta-dados-congresso-graficos-cabecalho.php:
// panel-body, panel panel-default, panel-group
?>
        </div>
    </div>
</div>
