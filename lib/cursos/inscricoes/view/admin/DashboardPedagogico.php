<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarRegistroPedagogicoAC();
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
.db-cards { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.db-card-metric { flex: 1; min-width: 140px; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px; text-align: center; }
.db-card-metric .db-metric-valor { font-size: 28px; font-weight: 700; }
.db-card-metric .db-metric-label { font-size: 12px; color: #888; margin-top: 4px; }
.db-graficos-row { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; }
.db-grafico-panel { flex: 1; min-width: 320px; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; }
.db-grafico-panel h5 { font-size: 14px; font-weight: 600; color: #555; margin: 0 0 10px 0; }
.db-tabela-atencao { margin-top: 20px; }
.db-tabela-atencao th { font-size: 13px; }
.db-tabela-atencao td { font-size: 13px; }
.db-badge-piorou { background: #f44336; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px; }
.db-badge-dificuldade { background: #FF9800; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px; }
.db-loading { text-align: center; padding: 40px; color: #999; }
.db-sem-dados { text-align: center; padding: 40px; color: #999; font-style: italic; }
</style>

<script type="text/javascript">
var dbCharts = {};

var dbLabels = {
    'adequada': 'Adequada',
    'necessitou_apoio': 'Necessitou apoio',
    'dificuldade_inicio': 'Dificuldade',
    'espontanea': 'Espont\u00e2nea',
    'com_apoio': 'Com apoio',
    'resistente': 'Resistiu/Recusou',
    'autonomo': 'Aut\u00f4nomo',
    'pontual': 'Apoio pontual',
    'frequente': 'Apoio frequente',
    'intensivo': 'Apoio intensivo',
    'melhorou_parcialmente': 'Melhorou parcial',
    'permaneceu_dificuldade': 'Com dificuldade',
    'melhorou': 'Melhorou',
    'manteve': 'Manteve',
    'piorou': 'Piorou',
    'agitacao': 'Agita\u00e7\u00e3o',
    'choro': 'Choro',
    'recusa': 'Recusa de atividade',
    'agressividade': 'Agressividade',
    'isolamento': 'Isolamento',
    'nenhuma': 'Nenhuma'
};

function dbLabel(val) { return dbLabels[val] || val || '-'; }

function dbTitleCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
}

function dbEscapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

var pesCarregado = false;
var profCarregado = false;
var presencaCarregado = false;

$(document).ready(function() {
    dbCarregarDados();

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr('href');
        if (target === '#tabPES' && !pesCarregado) {
            pesCarregado = true;
            pesCarregarDados();
        }
        if (target === '#tabProfessor' && !profCarregado) {
            profCarregado = true;
            profCarregarDados();
        }
        if (target === '#tabPresenca' && !presencaCarregado) {
            presencaCarregado = true;
            presencaCarregarDados();
        }
    });
});

function dbCarregarDados() {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarRegistroPedagogico', 'm': 'carregarEstatisticasGerais', '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { $('#dbConteudo').html('<div class="db-loading"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Carregando dados...</div>'); },
        success: function(data) {
            if (!data.statusMessage || !data.registros || data.registros.length === 0) {
                $('#dbConteudo').html('<div class="db-sem-dados"><i class="fa fa-info-circle fa-2x"></i><br>Nenhum registro pedag&oacute;gico encontrado.</div>');
                return;
            }
            dbProcessarDados(data.registros);
        },
        error: function() {
            $('#dbConteudo').html('<div class="text-danger text-center" style="padding:30px;">Erro ao carregar dados.</div>');
        }
    });
}

function dbProcessarDados(registros) {
    var total = registros.length;

    // Contagens
    var evolucao = {}, situacoes = {}, grauApoio = {}, participacao = {};
    var porAgente = {}, porAluno = {};
    var alertas = []; // alunos com piorou ou permaneceu_dificuldade recentemente

    $.each(registros, function(i, r) {
        // Evolucao
        evolucao[r.evolucaoPeriodo] = (evolucao[r.evolucaoPeriodo] || 0) + 1;

        // Grau apoio
        grauApoio[r.grauApoio] = (grauApoio[r.grauApoio] || 0) + 1;

        // Participacao
        participacao[r.participacao] = (participacao[r.participacao] || 0) + 1;

        // Situacoes
        if (r.situacoes) {
            try {
                var arr = JSON.parse(r.situacoes);
                $.each(arr, function(j, v) {
                    if (v && v !== 'nenhuma') {
                        situacoes[v] = (situacoes[v] || 0) + 1;
                    }
                });
            } catch(e) {}
        }

        // Por agente
        var nomeAg = r.nomeAgente || 'Desconhecido';
        if (!porAgente[r.codAgente]) {
            porAgente[r.codAgente] = { nome: nomeAg, total: 0 };
        }
        porAgente[r.codAgente].total++;

        // Por aluno (ultimo registro)
        if (!porAluno[r.codAluno]) {
            porAluno[r.codAluno] = r; // primeiro = mais recente (ORDER BY DESC)
        }
    });

    // Alertas: alunos cujo ultimo registro tem piorou ou permaneceu_dificuldade
    $.each(porAluno, function(cod, r) {
        if (r.evolucaoPeriodo === 'piorou' || r.condicaoFinal === 'permaneceu_dificuldade') {
            alertas.push(r);
        }
    });

    var totalAlunos = Object.keys(porAluno).length;
    var totalAgentes = Object.keys(porAgente).length;
    var pctMelhorou = evolucao['melhorou'] ? Math.round((evolucao['melhorou'] / total) * 100) : 0;

    // Renderizar
    var html = '';

    // Cards de metricas
    html += '<div class="db-cards">';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#2196F3;">' + total + '</div><div class="db-metric-label">Total de Registros</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#4CAF50;">' + totalAlunos + '</div><div class="db-metric-label">Alunos Acompanhados</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#FF9800;">' + totalAgentes + '</div><div class="db-metric-label">Agentes Ativos</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#4CAF50;">' + pctMelhorou + '%</div><div class="db-metric-label">Registros com Melhora</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#f44336;">' + alertas.length + '</div><div class="db-metric-label">Alunos em Aten&ccedil;&atilde;o</div></div>';
    html += '</div>';

    // Graficos
    html += '<div class="db-graficos-row">';
    html += '<div class="db-grafico-panel"><h5><i class="fa fa-pie-chart"></i> Evolu&ccedil;&atilde;o Geral</h5><canvas id="dbChartEvolucao" height="200"></canvas></div>';
    html += '<div class="db-grafico-panel"><h5><i class="fa fa-bar-chart"></i> Situa&ccedil;&otilde;es Mais Frequentes</h5><canvas id="dbChartSituacoes" height="200"></canvas></div>';
    html += '</div>';

    html += '<div class="db-graficos-row">';
    html += '<div class="db-grafico-panel"><h5><i class="fa fa-pie-chart"></i> Grau de Apoio</h5><canvas id="dbChartApoio" height="200"></canvas></div>';
    html += '<div class="db-grafico-panel"><h5><i class="fa fa-bar-chart"></i> Registros por Agente</h5><canvas id="dbChartAgentes" height="200"></canvas></div>';
    html += '</div>';

    // Tabela de alertas
    if (alertas.length > 0) {
        html += '<div class="db-tabela-atencao">';
        html += '<h5><i class="fa fa-exclamation-triangle" style="color:#f44336;"></i> Alunos que Precisam de Aten&ccedil;&atilde;o</h5>';
        html += '<p style="font-size:12px;color:#888;">Alunos cujo &uacute;ltimo registro indica piora ou dificuldade persistente.</p>';
        html += '<table class="table table-bordered table-striped table-hover"><thead><tr>';
        html += '<th>Aluno</th><th>Col&eacute;gio</th><th>Agente</th><th>&Uacute;ltimo Registro</th><th>Evolu&ccedil;&atilde;o</th><th>Condi&ccedil;&atilde;o Final</th><th></th>';
        html += '</tr></thead><tbody>';
        $.each(alertas, function(i, r) {
            var badgeEv = r.evolucaoPeriodo === 'piorou' ? '<span class="db-badge-piorou">Piorou</span>' : dbLabel(r.evolucaoPeriodo);
            var badgeCf = r.condicaoFinal === 'permaneceu_dificuldade' ? '<span class="db-badge-dificuldade">Com dificuldade</span>' : dbLabel(r.condicaoFinal);
            var dataFmt = r.dataRegistro ? r.dataRegistro.split('-').reverse().join('/') : '-';
            html += '<tr>';
            html += '<td>' + dbEscapeHtml(r.codigoExibicao || r.codAluno) + '</td>';
            html += '<td>' + dbEscapeHtml(r.nomeColegio || '') + '</td>';
            html += '<td>' + dbEscapeHtml(dbTitleCase(r.nomeAgente || '')) + '</td>';
            html += '<td>' + dataFmt + '</td>';
            html += '<td>' + badgeEv + '</td>';
            html += '<td>' + badgeCf + '</td>';
            html += '<td><a class="btn btn-xs btn-acao-info" href="index.php?content=view/admin/ConsultarRegistrosPedagogicosAluno.php&codAluno=' + r.codAluno + '" title="Ver registros"><i class="fa fa-clipboard"></i></a></td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
    }

    $('#dbConteudo').html(html);

    // Montar graficos
    dbMontarGraficoEvolucao(evolucao);
    dbMontarGraficoSituacoes(situacoes);
    dbMontarGraficoApoio(grauApoio);
    dbMontarGraficoAgentes(porAgente);
}

function dbMontarGraficoEvolucao(dados) {
    var ordem = ['melhorou', 'manteve', 'piorou'];
    var labels = ordem.map(function(k) { return dbLabel(k); });
    var values = ordem.map(function(k) { return dados[k] || 0; });
    var colors = ['#4CAF50', '#FFC107', '#f44336'];

    dbCharts.evolucao = new Chart(document.getElementById('dbChartEvolucao').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: values, backgroundColor: colors, borderWidth: 2 }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } }
            }
        }
    });
}

function dbMontarGraficoSituacoes(dados) {
    var keys = Object.keys(dados).sort(function(a, b) { return dados[b] - dados[a]; });
    if (keys.length === 0) {
        $('#dbChartSituacoes').parent().find('h5').after('<p style="color:#999;font-style:italic;text-align:center;">Nenhuma situa&ccedil;&atilde;o registrada.</p>');
        return;
    }
    var labels = keys.map(function(k) { return dbLabel(k); });
    var values = keys.map(function(k) { return dados[k]; });
    var colors = ['#f44336','#FF9800','#FFC107','#2196F3','#9C27B0','#607D8B'];

    dbCharts.situacoes = new Chart(document.getElementById('dbChartSituacoes').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'Ocorr\u00eancias', data: values, backgroundColor: keys.map(function(k, i) { return colors[i % colors.length]; }), borderRadius: 4 }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

function dbMontarGraficoApoio(dados) {
    var ordem = ['autonomo', 'pontual', 'frequente', 'intensivo'];
    var labels = ordem.map(function(k) { return dbLabel(k); });
    var values = ordem.map(function(k) { return dados[k] || 0; });
    var colors = ['#4CAF50', '#8BC34A', '#FF9800', '#f44336'];

    dbCharts.apoio = new Chart(document.getElementById('dbChartApoio').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: values, backgroundColor: colors, borderWidth: 2 }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } }
            }
        }
    });
}

function dbMontarGraficoAgentes(porAgente) {
    var keys = Object.keys(porAgente).sort(function(a, b) { return porAgente[b].total - porAgente[a].total; });
    var labels = keys.map(function(k) { return dbTitleCase(porAgente[k].nome); });
    var values = keys.map(function(k) { return porAgente[k].total; });

    dbCharts.agentes = new Chart(document.getElementById('dbChartAgentes').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'Registros', data: values, backgroundColor: '#2196F3', borderRadius: 4 }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

// ===================== AVALIACAO PES =====================

var pesColunas = [
    {campo: 'segue_orientacoes',        label: 'Segue orienta\u00e7\u00f5es do supervisor'},
    {campo: 'abertura_feedback',        label: 'Abertura para feedback'},
    {campo: 'comunica_dificuldades',    label: 'Comunica d\u00favidas antes de decidir'},
    {campo: 'postura_profissional',     label: 'Postura profissional adequada'},
    {campo: 'relacionamento_equipe',    label: 'Bom relacionamento com equipe'},
    {campo: 'desempenho_adequado',      label: 'Desempenho adequado para fun\u00e7\u00e3o'},
    {campo: 'necessita_acompanhamento', label: 'Precisa acompanhamento da coordena\u00e7\u00e3o'}
];

var pesLabelResp = {
    'Sim': 'Sim',
    'Nao': 'N\u00e3o',
    'Parcialmente': 'Parcialmente',
    'NaoObservado': 'N\u00e3o observado'
};

function pesCarregarDados() {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarAvaliacaoPES', 'm': 'carregarEstatisticasPES', '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { $('#pesConteudo').html('<div class="db-loading"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Carregando dados PES...</div>'); },
        success: function(data) {
            if (!data.statusMessage || data.totalAvaliacoes === 0) {
                $('#pesConteudo').html('<div class="db-sem-dados"><i class="fa fa-info-circle fa-2x"></i><br>Nenhuma avalia\u00e7\u00e3o PES encontrada.</div>');
                return;
            }
            pesRenderizar(data);
        },
        error: function() {
            $('#pesConteudo').html('<div class="text-danger text-center" style="padding:30px;">Erro ao carregar dados PES.</div>');
        }
    });
}

function pesRenderizar(data) {
    var html = '';

    // Cards (sempre visiveis acima das abas)
    var pctAtencao = data.totalAgentes > 0 ? Math.round((data.alertas.length / data.totalAgentes) * 100) : 0;
    html += '<div class="db-cards">';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#2196F3;">' + data.totalAvaliacoes + '</div><div class="db-metric-label">Total de Avalia\u00e7\u00f5es</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#4CAF50;">' + data.totalAgentes + '</div><div class="db-metric-label">PES Avaliados</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#FF9800;">' + data.totalSupervisores + '</div><div class="db-metric-label">Supervisores</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#f44336;">' + data.alertas.length + ' (' + pctAtencao + '%)</div><div class="db-metric-label">PES que Precisam Acompanhamento</div></div>';
    html += '</div>';

    // Abas
    html += '<ul class="nav nav-tabs" id="pesAbas" role="tablist">';
    html += '<li role="presentation" class="active"><a href="#pesTabResumo" data-toggle="tab"><i class="fa fa-pie-chart"></i> Resumo</a></li>';
    html += '<li role="presentation"><a href="#pesTabCriterios" data-toggle="tab"><i class="fa fa-bar-chart"></i> Por Crit\u00e9rio</a></li>';
    html += '<li role="presentation"><a href="#pesTabAtencao" data-toggle="tab"><i class="fa fa-exclamation-triangle"></i> Aten\u00e7\u00e3o (' + data.alertas.length + ')</a></li>';
    html += '</ul>';

    html += '<div class="tab-content" style="padding-top:15px;">';

    // Aba Resumo
    html += '<div role="tabpanel" class="tab-pane active" id="pesTabResumo">';
    html += '<div class="db-graficos-row">';
    html += '<div class="db-grafico-panel"><h5><i class="fa fa-pie-chart"></i> Distribui\u00e7\u00e3o Geral de Respostas</h5><canvas id="pesChartGeral" height="200"></canvas></div>';
    html += '<div class="db-grafico-panel"><h5><i class="fa fa-bar-chart"></i> Avalia\u00e7\u00f5es por Supervisor</h5><canvas id="pesChartSupervisores" height="200"></canvas></div>';
    html += '</div>';
    html += '</div>';

    // Aba Por Criterio
    html += '<div role="tabpanel" class="tab-pane" id="pesTabCriterios">';
    html += '<div class="db-graficos-row">';
    html += '<div class="db-grafico-panel" style="min-width:100%;"><h5><i class="fa fa-bar-chart"></i> Respostas por Crit\u00e9rio</h5><canvas id="pesChartPerguntas" height="280"></canvas></div>';
    html += '</div>';
    html += '</div>';

    // Aba Atencao
    html += '<div role="tabpanel" class="tab-pane" id="pesTabAtencao">';
    if (data.alertas.length > 0) {
        html += '<div class="db-tabela-atencao">';
        html += '<p style="font-size:12px;color:#888;">PES cuja \u00faltima avalia\u00e7\u00e3o indica necessidade de acompanhamento mais pr\u00f3ximo da coordena\u00e7\u00e3o (Pergunta 7 = Sim).</p>';
        html += '<table class="table table-bordered table-striped table-hover"><thead><tr>';
        html += '<th>PES (Agente)</th><th>Supervisor</th><th>Data da Avalia\u00e7\u00e3o</th>';
        html += '</tr></thead><tbody>';
        $.each(data.alertas, function(i, a) {
            var dataFmt = a.dataAvaliacao ? a.dataAvaliacao.split('-').reverse().join('/') : '-';
            html += '<tr>';
            html += '<td>' + dbEscapeHtml(a.nomeAgente) + '</td>';
            html += '<td>' + dbEscapeHtml(a.nomeSupervisor) + '</td>';
            html += '<td>' + dataFmt + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
    } else {
        html += '<div class="db-sem-dados"><i class="fa fa-check-circle fa-2x" style="color:#4CAF50;"></i><br>Nenhum PES precisa de acompanhamento no momento.</div>';
    }
    html += '</div>';

    html += '</div>'; // tab-content

    $('#pesConteudo').html(html);

    // Montar grafico da aba ativa
    pesMontarGraficoGeral(data.respostasGerais);
    pesMontarGraficoSupervisores(data.porSupervisor);

    // Redimensionar graficos ao trocar de aba
    $('#pesAbas a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr('href');
        if (target === '#pesTabCriterios' && !dbCharts.pesPerguntas) {
            pesMontarGraficoPerguntas(data.respostasPorPergunta);
        }
        // Resize de graficos visiveis
        $.each(dbCharts, function(key, chart) {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    });
}

function pesMontarGraficoGeral(dados) {
    var ordem = ['Sim', 'Parcialmente', 'Nao', 'NaoObservado'];
    var labels = ordem.map(function(k) { return pesLabelResp[k]; });
    var values = ordem.map(function(k) { return dados[k] || 0; });
    var colors = ['#4CAF50', '#FFC107', '#f44336', '#9E9E9E'];

    dbCharts.pesGeral = new Chart(document.getElementById('pesChartGeral').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: values, backgroundColor: colors, borderWidth: 2 }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } }
            }
        }
    });
}

function pesMontarGraficoSupervisores(porSupervisor) {
    var labels = porSupervisor.map(function(s) { return s.nome; });
    var values = porSupervisor.map(function(s) { return s.total; });

    dbCharts.pesSupervisores = new Chart(document.getElementById('pesChartSupervisores').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'Avalia\u00e7\u00f5es', data: values, backgroundColor: '#3F51B5', borderRadius: 4 }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

function pesMontarGraficoPerguntas(respostasPorPergunta) {
    var labels = pesColunas.map(function(c) { return c.label; });
    var ordem = ['Sim', 'Parcialmente', 'Nao', 'NaoObservado'];
    var cores = { 'Sim': '#4CAF50', 'Parcialmente': '#FFC107', 'Nao': '#f44336', 'NaoObservado': '#9E9E9E' };

    var datasets = ordem.map(function(resp) {
        return {
            label: pesLabelResp[resp],
            data: pesColunas.map(function(c) {
                return respostasPorPergunta[c.campo] ? (respostasPorPergunta[c.campo][resp] || 0) : 0;
            }),
            backgroundColor: cores[resp],
            borderRadius: 2
        };
    });

    dbCharts.pesPerguntas = new Chart(document.getElementById('pesChartPerguntas').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } }
            },
            scales: {
                x: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } },
                y: { stacked: true }
            }
        }
    });
}

// ===================== AVALIACAO PES POR PROFESSOR =====================

var profColunas = [
    {campo: 'integra_atividades',     label: 'Integra atividades em sala'},
    {campo: 'contribui_participacao', label: 'Contribui para participa\u00e7\u00e3o'},
    {campo: 'favorece_aprendizagem',  label: 'Favorece aprendizagem'},
    {campo: 'reduz_comportamentos',   label: 'Reduz comportamentos'},
    {campo: 'promove_autonomia',      label: 'Promove autonomia'},
    {campo: 'comunicacao_professor',  label: 'Comunica\u00e7\u00e3o com professor'},
    {campo: 'segue_orientacoes',      label: 'Segue orienta\u00e7\u00f5es'},
    {campo: 'contribui_aula',         label: 'Contribui para a aula'},
    {campo: 'postura_profissional',   label: 'Postura profissional'}
];

function profCarregarDados() {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarAvaliacaoPESProfessor', 'm': 'carregarPainelComparativo', 'ciclo': 1, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { $('#profConteudo').html('<div class="db-loading"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Carregando dados...</div>'); },
        success: function(data) {
            if (!data.statusMessage || !data.porColegio || Object.keys(data.porColegio).length === 0) {
                $('#profConteudo').html('<div class="db-sem-dados"><i class="fa fa-info-circle fa-2x"></i><br>Nenhuma avalia\u00e7\u00e3o por professor encontrada.</div>');
                return;
            }
            profRenderizar(data);
        },
        error: function() {
            $('#profConteudo').html('<div class="text-danger text-center" style="padding:30px;">Erro ao carregar dados.</div>');
        }
    });
}

function profRenderizar(data) {
    var totalAval = 0;
    var totalPES = 0;
    var totalColegios = Object.keys(data.porColegio).length;
    var somaNotas = 0;
    var countNotas = 0;
    var dadosGrafico = [];

    $.each(data.porColegio, function(nomeColegio, agentes) {
        totalPES += agentes.length;
        $.each(agentes, function(i, ag) {
            totalAval += ag.totalAvaliacoes;
            if (ag.mediaNota > 0) {
                somaNotas += ag.mediaNota * ag.totalAvaliacoes;
                countNotas += ag.totalAvaliacoes;
            }
            dadosGrafico.push({
                nome: ag.nomeAgente,
                colegio: nomeColegio,
                nota: ag.mediaNota
            });
        });
    });

    var mediaGeral = countNotas > 0 ? (somaNotas / countNotas).toFixed(1) : '-';
    var classeMedia = mediaGeral >= 7 ? 'color:#4CAF50;' : (mediaGeral >= 5 ? 'color:#FF9800;' : 'color:#f44336;');

    var html = '';

    // Cards
    html += '<div class="db-cards">';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#2196F3;">' + totalAval + '</div><div class="db-metric-label">Total de Avalia\u00e7\u00f5es</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#4CAF50;">' + totalPES + '</div><div class="db-metric-label">PES Avaliados</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#FF9800;">' + totalColegios + '</div><div class="db-metric-label">Unidades</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="' + classeMedia + '">' + mediaGeral + '</div><div class="db-metric-label">Nota M\u00e9dia Geral</div></div>';
    html += '</div>';

    // Seletor de ciclo
    html += '<div style="margin-bottom:15px;"><label>Ciclo: </label> ';
    html += '<select id="profCicloSelect" class="form-control" style="width:200px;display:inline-block;">';
    html += '<option value="1">1\u00ba Ciclo (In\u00edcio)</option>';
    html += '<option value="2">2\u00ba Ciclo (Meio do ano)</option>';
    html += '<option value="3">3\u00ba Ciclo (Final do ano)</option>';
    html += '</select></div>';

    // Grafico notas por PES
    html += '<div class="db-graficos-row">';
    html += '<div class="db-grafico-panel" style="min-width:100%;"><h5><i class="fa fa-bar-chart"></i> Nota M\u00e9dia por PES</h5><canvas id="profChartNotas" height="' + Math.max(200, dadosGrafico.length * 28) + '"></canvas></div>';
    html += '</div>';

    // Tabela por colegio
    $.each(data.porColegio, function(nomeColegio, agentes) {
        html += '<h5 style="background:#E3F2FD; padding:8px 12px; border-radius:4px; color:#1565C0; font-weight:600; margin:15px 0 8px 0;"><i class="fa fa-building"></i> ' + nomeColegio + '</h5>';
        html += '<table class="table table-bordered table-condensed" style="font-size:12px;"><thead><tr><th>PES</th><th class="text-center">Avalia\u00e7\u00f5es</th><th class="text-center">Nota M\u00e9dia</th></tr></thead><tbody>';
        $.each(agentes, function(i, ag) {
            var classe = ag.mediaNota >= 7 ? 'media-boa' : (ag.mediaNota >= 5 ? 'media-media' : 'media-baixa');
            html += '<tr><td>' + ag.nomeAgente + '</td><td class="text-center">' + ag.totalAvaliacoes + '</td><td class="text-center"><strong class="' + classe + '">' + ag.mediaNota + '</strong></td></tr>';
        });
        html += '</tbody></table>';
    });

    $('#profConteudo').html(html);

    // Grafico
    dadosGrafico.sort(function(a, b) { return b.nota - a.nota; });
    var labels = dadosGrafico.map(function(d) { return d.nome; });
    var values = dadosGrafico.map(function(d) { return d.nota; });
    var colors = values.map(function(v) { return v >= 7 ? '#4CAF50' : (v >= 5 ? '#FF9800' : '#f44336'); });

    dbCharts.profNotas = new Chart(document.getElementById('profChartNotas').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'Nota M\u00e9dia', data: values, backgroundColor: colors, borderRadius: 4 }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, max: 10, ticks: { stepSize: 1 } } }
        }
    });

    // Bind troca de ciclo
    $('#profCicloSelect').on('change', function() {
        profCarregado = false;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarAvaliacaoPESProfessor', 'm': 'carregarPainelComparativo', 'ciclo': $(this).val(), '_submit_check': '1'},
            dataType: "json",
            cache: false,
            success: function(resp) {
                if (resp.statusMessage && resp.porColegio && Object.keys(resp.porColegio).length > 0) {
                    if (dbCharts.profNotas) { dbCharts.profNotas.destroy(); dbCharts.profNotas = null; }
                    profRenderizar(resp);
                } else {
                    $('#profConteudo').html('<div class="db-sem-dados"><i class="fa fa-info-circle fa-2x"></i><br>Nenhuma avalia\u00e7\u00e3o encontrada para este ciclo.</div>');
                }
            }
        });
    });
}

// ===================== PRESENCA =====================

function presencaCarregarDados() {
    // Carregar ultimos 7 dias
    var hoje = new Date();
    var datas = [];
    for (var i = 6; i >= 0; i--) {
        var d = new Date(hoje);
        d.setDate(d.getDate() - i);
        if (d.getDay() !== 0 && d.getDay() !== 6) { // pular fim de semana
            datas.push(d.toISOString().split('T')[0]);
        }
    }

    $('#presencaConteudo').html('<div class="db-loading"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Carregando dados de presen\u00e7a...</div>');

    var resultados = {};
    var concluidos = 0;

    $.each(datas, function(i, data) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarRegistroPedagogico', 'm': 'carregarPresencaDia', 'data': data, '_submit_check': '1'},
            dataType: "json",
            cache: false,
            success: function(resp) {
                if (resp.statusMessage) {
                    resultados[data] = resp;
                }
            },
            complete: function() {
                concluidos++;
                if (concluidos === datas.length) {
                    presencaRenderizar(resultados, datas);
                }
            }
        });
    });
}

function presencaRenderizar(resultados, datas) {
    if (Object.keys(resultados).length === 0) {
        $('#presencaConteudo').html('<div class="db-sem-dados"><i class="fa fa-info-circle fa-2x"></i><br>Nenhum dado de presen\u00e7a encontrado.</div>');
        return;
    }

    var totalAlunos = 0, presentesGeral = 0, faltasGeral = 0, semRegistroGeral = 0;
    var labelsGrafico = [], valPresentes = [], valFaltas = [], valSemRegistro = [];
    var diasComDados = 0;

    $.each(datas, function(i, data) {
        var r = resultados[data];
        if (!r) return;
        diasComDados++;
        if (r.totalAlunos > totalAlunos) totalAlunos = r.totalAlunos; // pegar o maior (numero de alunos)
        presentesGeral += r.presentes;
        faltasGeral += r.faltas;
        semRegistroGeral += r.semRegistro;

        var dataFmt = data.split('-').reverse().join('/');
        labelsGrafico.push(dataFmt);
        valPresentes.push(r.presentes);
        valFaltas.push(r.faltas);
        valSemRegistro.push(r.semRegistro);
    });

    var totalComPresenca = presentesGeral + faltasGeral;
    var pctPresenca = totalComPresenca > 0 ? Math.round((presentesGeral / totalComPresenca) * 100) : 0;
    var classePresenca = pctPresenca >= 80 ? 'color:#4CAF50;' : (pctPresenca >= 60 ? 'color:#FF9800;' : 'color:#f44336;');

    var html = '';

    // Cards
    html += '<div class="db-cards">';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#2196F3;">' + totalAlunos + '</div><div class="db-metric-label">Total de Alunos</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#4CAF50;">' + presentesGeral + '</div><div class="db-metric-label">Presen\u00e7as (' + diasComDados + ' dias)</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#f44336;">' + faltasGeral + '</div><div class="db-metric-label">Faltas (' + diasComDados + ' dias)</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="color:#FF9800;">' + semRegistroGeral + '</div><div class="db-metric-label">Sem Registro</div></div>';
    html += '<div class="db-card-metric"><div class="db-metric-valor" style="' + classePresenca + '">' + pctPresenca + '%</div><div class="db-metric-label">Taxa de Presen\u00e7a</div></div>';
    html += '</div>';

    // Graficos
    html += '<div class="db-graficos-row">';
    html += '<div class="db-grafico-panel"><h5><i class="fa fa-line-chart"></i> Presen\u00e7a nos \u00daltimos Dias \u00dateis</h5><canvas id="presencaChartLinha" height="200"></canvas></div>';
    html += '<div class="db-grafico-panel"><h5><i class="fa fa-pie-chart"></i> Distribui\u00e7\u00e3o Geral</h5><canvas id="presencaChartPizza" height="200"></canvas></div>';
    html += '</div>';

    $('#presencaConteudo').html(html);

    // Grafico de linha
    dbCharts.presencaLinha = new Chart(document.getElementById('presencaChartLinha').getContext('2d'), {
        type: 'line',
        data: {
            labels: labelsGrafico,
            datasets: [
                { label: 'Presentes', data: valPresentes, borderColor: '#4CAF50', backgroundColor: 'rgba(76,175,80,0.1)', fill: true, tension: 0.3 },
                { label: 'Faltas', data: valFaltas, borderColor: '#f44336', backgroundColor: 'rgba(244,67,54,0.1)', fill: true, tension: 0.3 },
                { label: 'Sem registro', data: valSemRegistro, borderColor: '#FF9800', backgroundColor: 'rgba(255,152,0,0.1)', fill: true, tension: 0.3 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Grafico pizza
    dbCharts.presencaPizza = new Chart(document.getElementById('presencaChartPizza').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Presentes', 'Faltas', 'Sem registro'],
            datasets: [{ data: [presentesGeral, faltasGeral, semRegistroGeral], backgroundColor: ['#4CAF50', '#f44336', '#FF9800'], borderWidth: 2 }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } }
        }
    });
}
</script>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><i class="fa fa-bar-chart"></i> Painel de Relat&oacute;rios</h4>
    </div>
    <div class="panel-body">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#tabPedagogico" aria-controls="tabPedagogico" role="tab" data-toggle="tab">
                    <i class="fa fa-book"></i> Registros Pedag&oacute;gicos
                </a>
            </li>
            <li role="presentation">
                <a href="#tabPES" aria-controls="tabPES" role="tab" data-toggle="tab">
                    <i class="fa fa-clipboard"></i> Avalia&ccedil;&atilde;o por Supervisor
                </a>
            </li>
            <li role="presentation">
                <a href="#tabProfessor" aria-controls="tabProfessor" role="tab" data-toggle="tab">
                    <i class="fa fa-graduation-cap"></i> Avalia&ccedil;&atilde;o por Professor
                </a>
            </li>
            <li role="presentation">
                <a href="#tabPresenca" aria-controls="tabPresenca" role="tab" data-toggle="tab">
                    <i class="fa fa-calendar-check-o"></i> Presen&ccedil;a
                </a>
            </li>
        </ul>
        <div class="tab-content" style="padding-top: 20px;">
            <div role="tabpanel" class="tab-pane active" id="tabPedagogico">
                <div id="dbConteudo"></div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tabPES">
                <div id="pesConteudo"></div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tabProfessor">
                <div id="profConteudo"></div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tabPresenca">
                <div id="presencaConteudo"></div>
            </div>
        </div>
    </div>
</div>
