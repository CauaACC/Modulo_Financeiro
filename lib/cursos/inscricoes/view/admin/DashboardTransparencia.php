<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)
    && !FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_CARGA_TRANSPARENCIA, 5, false, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/acesso-negado.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}
?>
<script type="text/javascript" src="resources/js/default.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<div class="page-header">
    <div class="page-context">&Aacute;rea Administrativa</div>
</div>
<?php
require_once(dirname(__FILE__)."/area_admin_menu_administracao.php");
?>

<style>
.db-cards { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.db-card-metric { flex: 1; min-width: 140px; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px; text-align: center; }
.db-card-metric .db-metric-valor { font-size: 28px; font-weight: 700; }
.db-card-metric .db-metric-label { font-size: 12px; color: #888; margin-top: 4px; }
.db-card-metric .db-metric-sub { font-size: 11px; color: #aaa; margin-top: 2px; }
.db-graficos-row { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; }
.db-grafico-panel { flex: 1; min-width: 280px; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; }
.db-grafico-panel h5 { font-size: 14px; font-weight: 600; color: #555; margin: 0 0 10px 0; }
.db-loading { text-align: center; padding: 40px; color: #999; }
.db-sem-dados { text-align: center; padding: 40px; color: #999; font-style: italic; }
</style>

<!-- Divs flutuantes de sucesso/erro -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<!-- Filtros de periodo -->
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px; padding:15px 18px;">
    <div class="row">
        <div class="col-sm-2">
            <label class="label-filtro">Ano:</label>
            <select class="form-control input-sm" id="filtroAno">
                <?php
                $anoAtual = date('Y');
                for ($a = $anoAtual; $a >= $anoAtual - 5; $a--) {
                    $sel = ($a == $anoAtual) ? 'selected' : '';
                    echo "<option value='$a' $sel>$a</option>";
                }
                ?>
                <option value="">Todos</option>
            </select>
        </div>
        <div class="col-sm-2">
            <label class="label-filtro">M&ecirc;s:</label>
            <select class="form-control input-sm" id="filtroMes">
                <option value="">Todos</option>
                <option value="01">Janeiro</option>
                <option value="02">Fevereiro</option>
                <option value="03">Mar&ccedil;o</option>
                <option value="04">Abril</option>
                <option value="05">Maio</option>
                <option value="06">Junho</option>
                <option value="07">Julho</option>
                <option value="08">Agosto</option>
                <option value="09">Setembro</option>
                <option value="10">Outubro</option>
                <option value="11">Novembro</option>
                <option value="12">Dezembro</option>
            </select>
        </div>
        <div class="col-sm-3" style="padding-top:25px;">
            <button class="btn btn-sm btn-primary" type="button" onclick="carregarDashboard();"><i class="fa fa-search"></i> Filtrar</button>
        </div>
    </div>
</div>

<!-- Cards de metricas -->
<div id="dbCarregando" class="db-loading" style="display:none;"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Carregando painel...</div>

<div id="dbConteudo" style="display:none;">

    <div class="db-cards">
        <div class="db-card-metric" style="border-left:4px solid #3498db;">
            <div class="db-metric-valor" id="metricTotalProjetos">-</div>
            <div class="db-metric-label">Total Projetos</div>
            <div class="db-metric-sub"><span id="metricProjetosPublicados">-</span> publicados / <span id="metricProjetosRascunho">-</span> rascunho</div>
        </div>
        <div class="db-card-metric" style="border-left:4px solid #27ae60;">
            <div class="db-metric-valor" id="metricTotalPagamentos">-</div>
            <div class="db-metric-label">Total Pagamentos</div>
            <div class="db-metric-sub"><span id="metricPagamentosPublicados">-</span> publicados / <span id="metricPagamentosRascunho">-</span> rascunho</div>
        </div>
        <div class="db-card-metric" style="border-left:4px solid #f39c12;">
            <div class="db-metric-valor" id="metricValorBruto">-</div>
            <div class="db-metric-label">Valor Bruto (Publicados)</div>
        </div>
        <div class="db-card-metric" style="border-left:4px solid #e74c3c;">
            <div class="db-metric-valor" id="metricValorLiquido">-</div>
            <div class="db-metric-label">Valor L&iacute;quido (Publicados)</div>
        </div>
    </div>

    <!-- Graficos -->
    <div class="db-graficos-row">
        <div class="db-grafico-panel">
            <h5><i class="fa fa-pie-chart"></i> Status Projetos</h5>
            <div style="position:relative; height:250px;">
                <canvas id="chartProjetos"></canvas>
            </div>
        </div>
        <div class="db-grafico-panel">
            <h5><i class="fa fa-pie-chart"></i> Status Pagamentos</h5>
            <div style="position:relative; height:250px;">
                <canvas id="chartPagamentos"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Coordenadores -->
    <div class="db-graficos-row">
        <div class="db-grafico-panel" style="flex:2;">
            <h5><i class="fa fa-users"></i> Projetos por Coordenador (Top 10)</h5>
            <div style="position:relative; height:250px;">
                <canvas id="chartCoordenadores"></canvas>
            </div>
        </div>
    </div>

    <!-- Ultimas Cargas -->
    <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
        <div style="padding:12px 18px; border-bottom:1px solid #eee;">
            <h4 style="margin:0; font-size:16px;"><i class="fa fa-clock-o"></i> &Uacute;ltimas Cargas</h4>
        </div>
        <div style="padding:12px 18px;">
            <div id="tabelaUltimasCargas">
                <div class="db-loading"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>
            </div>
        </div>
    </div>

</div><!-- /dbConteudo -->


<script type="text/javascript">
var dbCharts = {};

$(document).ready(function() {
    carregarDashboard();
    $('#filtroAno, #filtroMes').change(function() {
        carregarDashboard();
    });
});

function formatarMoeda(valor) {
    return 'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function carregarDashboard() {
    $('#dbConteudo').hide();
    $('#dbCarregando').show();
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarRegistrosTransparencia',
            'm': 'carregarDadosDashboard',
            'ano': $('#filtroAno').val(),
            'mes': $('#filtroMes').val(),
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        success: function(data) {
            $('#dbCarregando').hide();
            if (!data.statusMessage) {
                $('#dbCarregando').html('<div class="db-sem-dados">' + (data.message || 'Erro ao carregar dados.') + '</div>').show();
                return;
            }
            $('#dbConteudo').show();
            preencherMetricas(data);
            montarGraficoPizza('chartProjetos', data.projetos, ['#27ae60', '#f39c12']);
            montarGraficoPizza('chartPagamentos', data.pagamentos, ['#27ae60', '#f39c12']);
            montarGraficoCoordenadores(data.topCoordenadores);
            montarTabelaCargas(data.ultimasCargas);
        },
        error: function() {
            $('#dbCarregando').html('<div class="db-sem-dados">Erro ao carregar painel.</div>');
        }
    });
}

function preencherMetricas(data) {
    var totalProj = (data.projetos.PUBLICADO || 0) + (data.projetos.RASCUNHO || 0);
    var totalPag = (data.pagamentos.PUBLICADO || 0) + (data.pagamentos.RASCUNHO || 0);

    $('#metricTotalProjetos').text(totalProj);
    $('#metricProjetosPublicados').text(data.projetos.PUBLICADO || 0);
    $('#metricProjetosRascunho').text(data.projetos.RASCUNHO || 0);

    $('#metricTotalPagamentos').text(totalPag);
    $('#metricPagamentosPublicados').text(data.pagamentos.PUBLICADO || 0);
    $('#metricPagamentosRascunho').text(data.pagamentos.RASCUNHO || 0);

    $('#metricValorBruto').text(formatarMoeda(data.financeiro.total_bruto || 0));
    $('#metricValorLiquido').text(formatarMoeda(data.financeiro.total_liquido || 0));
}

function montarGraficoPizza(canvasId, dados, cores) {
    var ctx = document.getElementById(canvasId).getContext('2d');
    if (dbCharts[canvasId]) dbCharts[canvasId].destroy();

    dbCharts[canvasId] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Publicado', 'Rascunho'],
            datasets: [{
                data: [dados.PUBLICADO || 0, dados.RASCUNHO || 0],
                backgroundColor: cores,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } }
            }
        }
    });
}

function montarGraficoCoordenadores(lista) {
    if (!lista || lista.length === 0) {
        $('#chartCoordenadores').closest('.db-grafico-panel').html('<h5><i class="fa fa-users"></i> Projetos por Coordenador</h5><div class="db-sem-dados">Sem dados.</div>');
        return;
    }
    var labels = [];
    var valores = [];
    for (var i = 0; i < lista.length; i++) {
        var nome = lista[i].coordenador || 'N/I';
        labels.push(nome.length > 25 ? nome.substring(0, 25) + '...' : nome);
        valores.push(parseInt(lista[i].total) || 0);
    }

    var ctx = document.getElementById('chartCoordenadores').getContext('2d');
    if (dbCharts['chartCoordenadores']) dbCharts['chartCoordenadores'].destroy();

    dbCharts['chartCoordenadores'] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Projetos',
                data: valores,
                backgroundColor: '#3498db',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

function montarTabelaCargas(lista) {
    if (!lista || lista.length === 0) {
        $('#tabelaUltimasCargas').html('<div class="db-sem-dados">Nenhuma carga realizada.</div>');
        return;
    }
    var html = '<table class="tbl-fin"><thead><tr>';
    html += '<th>Arquivo</th><th>Tipo</th><th>Situa&ccedil;&atilde;o</th><th>Data</th>';
    html += '</tr></thead><tbody>';

    for (var i = 0; i < lista.length; i++) {
        var c = lista[i];
        var tipo = (c.tipo_arquivo || '').replace('CARGA_TRANSPARENCIA_', '');
        var status = c.status_carga || 'AGUARDANDO';
        var badge = '';
        if (status === 'CARREGADO') badge = '<span class="badge-tipo"><span class="dot dot-green"></span>Carregado</span>';
        else if (status === 'PARCIALMENTE_CARREGADO') badge = '<span class="badge-tipo"><span class="dot dot-orange"></span>Parcial</span>';
        else badge = '<span class="badge-tipo"><span class="dot dot-red"></span>Aguardando</span>';

        html += '<tr>';
        html += '<td>' + (c.arquivo_upload || '') + '</td>';
        html += '<td>' + tipo + '</td>';
        html += '<td>' + badge + '</td>';
        html += '<td style="font-size:12px; color:#888;">' + (c.data_inclusao || '') + '</td>';
        html += '</tr>';
    }
    html += '</tbody></table>';
    $('#tabelaUltimasCargas').html(html);
}
</script>
<div style="height:300px;"></div>
