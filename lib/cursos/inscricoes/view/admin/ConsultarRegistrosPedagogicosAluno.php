<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarVinculoAgenteAC();
$codAlunoParam = isset($fwsGet_['codAluno']) ? $fwsGet_['codAluno'] : '';
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
.cr-info-aluno { background: #f5f8ff; border: 1px solid #d0daf0; border-radius: 4px; padding: 10px 14px; margin-bottom: 15px; }
.cr-info-aluno .cr-info-item { display: inline-block; margin-right: 20px; font-size: 13px; }
.cr-info-aluno .cr-info-label { color: #888; }
.cr-info-aluno .cr-info-valor { font-weight: bold; color: #333; }
.cr-registro-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 4px; padding: 12px 16px; margin-bottom: 10px; }
.cr-registro-card .cr-data { font-weight: bold; color: #337ab7; margin-bottom: 8px; font-size: 14px; }
.cr-registro-card .cr-campo { display: inline-block; margin-right: 18px; margin-bottom: 6px; font-size: 13px; }
.cr-registro-card .cr-campo-label { color: #888; }
.cr-registro-card .cr-campo-valor { color: #333; }
.cr-registro-card .cr-obs { font-size: 13px; color: #555; font-style: italic; margin-top: 6px; padding-top: 6px; border-top: 1px solid #f0f0f0; }
.cr-sem-registros { color: #999; font-style: italic; padding: 20px 0; text-align: center; }
.cr-loading { text-align: center; padding: 15px; color: #999; }
.cr-total { font-size: 13px; color: #666; margin-bottom: 10px; }
.cr-situacoes-lista { margin-top: 4px; }
.cr-situacoes-lista .cr-sit-badge { display: inline-block; padding: 2px 8px; margin: 2px 4px 2px 0; background: #f0f0f0; border-radius: 3px; font-size: 12px; color: #555; }
.cr-graficos { margin-bottom: 20px; padding: 15px; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 4px; }
.cr-grafico-box { display: inline-block; vertical-align: top; width: 49%; min-width: 300px; padding: 10px; }
.cr-grafico-titulo { font-size: 14px; font-weight: 600; color: #555; margin-bottom: 8px; }
.cr-sem-graficos { color: #999; font-style: italic; padding: 30px 0; text-align: center; }
.cr-filtros { background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px; padding: 12px 15px; margin-bottom: 15px; }
.cr-filtros label { font-size: 12px; color: #666; margin-bottom: 2px; }
.cr-filtro-count { font-size: 12px; color: #888; margin-top: 4px; }
</style>

<script type="text/javascript">
var crLabels = {
    'adequada': 'Adequada',
    'necessitou_apoio': 'Necessitou apoio inicial',
    'dificuldade_inicio': 'Dificuldade para iniciar',
    'espontanea': 'Espont\u00e2nea',
    'com_apoio': 'Com apoio',
    'resistente': 'Resistiu / Recusou',
    'autonomo': 'Aut\u00f4nomo',
    'pontual': 'Apoio pontual',
    'frequente': 'Apoio frequente',
    'intensivo': 'Apoio intensivo',
    'melhorou_parcialmente': 'Melhorou parcialmente',
    'permaneceu_dificuldade': 'Permaneceu com dificuldade',
    'melhorou': 'Melhorou',
    'manteve': 'Manteve',
    'piorou': 'Piorou',
    'agitacao': 'Agita\u00e7\u00e3o',
    'choro': 'Choro',
    'recusa': 'Recusa de atividade',
    'agressividade': 'Agressividade',
    'isolamento': 'Isolamento',
    'nenhuma': 'Nenhuma situa\u00e7\u00e3o relevante'
};

var crScores = {
    condicaoInicio: { 'adequada': 3, 'necessitou_apoio': 2, 'dificuldade_inicio': 1 },
    participacao:   { 'espontanea': 3, 'com_apoio': 2, 'resistente': 1 },
    grauApoio:      { 'autonomo': 3, 'pontual': 2.3, 'frequente': 1.6, 'intensivo': 1 },
    condicaoFinal:  { 'adequada': 3, 'melhorou_parcialmente': 2, 'permaneceu_dificuldade': 1 },
    evolucaoPeriodo:{ 'melhorou': 3, 'manteve': 2, 'piorou': 1 }
};

var crChartEvolucao = null;
var crChartSituacoes = null;
var crRegistrosCache = null;

// Dados globais para filtragem
var crTodosAlunos = [];
var crVinculos = []; // {codAgente, codAluno}

function crLabel(val) {
    return crLabels[val] || val || '-';
}

function crFormatDate(dateStr) {
    if (!dateStr) return '-';
    var parts = dateStr.split('-');
    if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
    return dateStr;
}

function crEscapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function crTitleCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
}

$(document).ready(function() {
    // Carregar dados em paralelo
    crCarregarDadosFiltros();

    // Eventos dos filtros
    $('#crFiltroAgente, #crFiltroColegio, #crFiltroTurno').on('change', function() {
        crAplicarFiltros();
    });

    $('#crBtnLimparFiltros').on('click', function() {
        $('#crFiltroAgente').val('');
        $('#crFiltroColegio').val('');
        $('#crFiltroTurno').val('');
        crAplicarFiltros();
    });

    // Select aluno
    $('#crSelectAluno').on('change', function() {
        var codAluno = $(this).val();
        if (codAluno) {
            crMostrarInfoAluno(codAluno);
            crCarregarRegistros(codAluno);
            $('#crAbas').show();
            $('#crLinkLaudos').attr('href', 'index.php?content=view/admin/GerenciarLaudosAluno.php&codAluno=' + codAluno);
            $('#crLinkAvaliacao').attr('href', 'index.php?content=view/admin/GerenciarAvaliacaoInicial.php&codAluno=' + codAluno);
            $('#crLinksAluno').show();
        } else {
            $('#crInfoAluno').hide();
            $('#crAbas').hide();
            $('#crLinksAluno').hide();
        }
    });

    // Filtros de data dos registros
    $('#crFiltroDataDe, #crFiltroDataAte').on('change', function() {
        crAplicarFiltroData();
    });

    $('#crFiltroMesAno').on('change', function() {
        var val = $(this).val();
        if (val) {
            // val = "YYYY-MM"
            var parts = val.split('-');
            var ano = parseInt(parts[0]);
            var mes = parseInt(parts[1]);
            var ultimoDia = new Date(ano, mes, 0).getDate();
            $('#crFiltroDataDe').val(val + '-01');
            $('#crFiltroDataAte').val(val + '-' + (ultimoDia < 10 ? '0' : '') + ultimoDia);
        } else {
            $('#crFiltroDataDe').val('');
            $('#crFiltroDataAte').val('');
        }
        crAplicarFiltroData();
    });

    $('#crBtnLimparFiltroData').on('click', function() {
        $('#crFiltroDataDe').val('');
        $('#crFiltroDataAte').val('');
        $('#crFiltroMesAno').val('');
        crAplicarFiltroData();
    });

    // Ao trocar para aba de graficos, renderizar com registros filtrados
    $('a[href="#tabGraficos"]').on('shown.bs.tab shown', function() {
        var filtrados = crFiltrarRegistrosPorData();
        if (filtrados && filtrados.length >= 2) {
            crGerarGraficos(filtrados);
        }
    });
});

function crCarregarDadosFiltros() {
    // Carregar alunos, agentes, colegios e vinculos em paralelo
    var dfdAlunos = $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarAluno', 'm': 'carregarListaAlunos', '_submit_check': '1'},
        dataType: "json",
        cache: false
    });

    var dfdAgentes = $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoAgente', 'm': 'carregarListaAgentes', '_submit_check': '1'},
        dataType: "json",
        cache: false
    });

    var dfdColegios = $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarColegio', 'm': 'carregarListaColegios', '_submit_check': '1'},
        dataType: "json",
        cache: false
    });

    var dfdVinculos = $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoAgente', 'm': 'carregarMapaAgenteAluno', '_submit_check': '1'},
        dataType: "json",
        cache: false
    });

    $.when(dfdAlunos, dfdAgentes, dfdColegios, dfdVinculos).done(function(rAlunos, rAgentes, rColegios, rVinculos) {
        var dataAlunos = rAlunos[0];
        var dataAgentes = rAgentes[0];
        var dataColegios = rColegios[0];
        var dataVinculos = rVinculos[0];

        // Guardar alunos
        if (dataAlunos.alunos) {
            crTodosAlunos = dataAlunos.alunos;
        }

        // Guardar vinculos
        if (dataVinculos.vinculos) {
            crVinculos = dataVinculos.vinculos;
        }

        // Popular select de agentes
        var selAgente = $('#crFiltroAgente');
        if (dataAgentes.agentes) {
            $.each(dataAgentes.agentes, function(i, ag) {
                var label = crTitleCase(ag.nome);
                if (ag.totalAlunos !== undefined) label += ' (' + ag.totalAlunos + ' alunos)';
                selAgente.append('<option value="' + ag.codInscrito + '">' + crEscapeHtml(label) + '</option>');
            });
        }

        // Popular select de colegios
        var selColegio = $('#crFiltroColegio');
        if (dataColegios.colegios) {
            $.each(dataColegios.colegios, function(i, col) {
                selColegio.append('<option value="' + col.codColegio + '">' + crEscapeHtml(col.nome) + '</option>');
            });
        }

        // Aplicar filtros iniciais (mostra todos)
        crAplicarFiltros();

        // Se veio com codAluno na URL, selecionar
        var paramAluno = '<?php echo addslashes($codAlunoParam) ?>';
        if (paramAluno) {
            $('#crSelectAluno').val(paramAluno).trigger('change');
        }
    });
}

function crAplicarFiltros() {
    var filtroAgente = $('#crFiltroAgente').val();
    var filtroColegio = $('#crFiltroColegio').val();
    var filtroTurno = $('#crFiltroTurno').val();

    // Montar set de alunos do agente selecionado
    var alunosDoAgente = null;
    if (filtroAgente) {
        alunosDoAgente = {};
        $.each(crVinculos, function(i, v) {
            if (v.codAgente == filtroAgente) {
                alunosDoAgente[v.codAluno] = true;
            }
        });
    }

    // Filtrar
    var alunosFiltrados = [];
    $.each(crTodosAlunos, function(i, al) {
        if (alunosDoAgente && !alunosDoAgente[al.codAluno]) return true;
        if (filtroColegio && al.codColegio != filtroColegio) return true;
        if (filtroTurno && al.turno != filtroTurno) return true;
        alunosFiltrados.push(al);
    });

    // Repopular select de alunos
    var select = $('#crSelectAluno');
    var valorAnterior = select.val();
    select.find('option:not(:first)').remove();

    $.each(alunosFiltrados, function(i, al) {
        var label = al.codigoExibicao;
        if (al.iniciais) label += ' - ' + al.iniciais;
        if (al.nomeColegio) label += ' (' + al.nomeColegio + ')';
        label += ' [' + (al.serieTurma || al.nivel) + ' / ' + al.turno + ']';
        select.append('<option value="' + al.codAluno + '" data-colegio="' + crEscapeHtml(al.nomeColegio) + '" data-nivel="' + crEscapeHtml(al.nivel) + '" data-turno="' + crEscapeHtml(al.turno) + '" data-codigo="' + crEscapeHtml(al.codigoExibicao) + '" data-nome="' + crEscapeHtml(al.iniciais || '') + '" data-serie="' + crEscapeHtml(al.serieTurma || '') + '">' + crEscapeHtml(label) + '</option>');
    });

    // Manter selecao se ainda existir
    if (valorAnterior && select.find('option[value="' + valorAnterior + '"]').length) {
        select.val(valorAnterior);
    } else {
        select.val('');
        $('#crInfoAluno').hide();
        $('#crAbas').hide();
    }

    // Atualizar contador
    $('#crFiltroCount').text(alunosFiltrados.length + ' aluno(s) encontrado(s)');
}

function crMostrarInfoAluno(codAluno) {
    var opt = $('#crSelectAluno option:selected');
    var html = '<span class="cr-info-item"><span class="cr-info-label">C&oacute;digo:</span> <span class="cr-info-valor">' + crEscapeHtml(opt.data('codigo')) + '</span></span>';
    if (opt.data('nome')) {
        html += '<span class="cr-info-item"><span class="cr-info-label">Nome:</span> <span class="cr-info-valor">' + crEscapeHtml(opt.data('nome')) + '</span></span>';
    }
    html += '<span class="cr-info-item"><span class="cr-info-label">Col&eacute;gio:</span> <span class="cr-info-valor">' + crEscapeHtml(opt.data('colegio') || 'N/A') + '</span></span>';
    html += '<span class="cr-info-item"><span class="cr-info-label">S&eacute;rie:</span> <span class="cr-info-valor">' + crEscapeHtml(opt.data('serie') || opt.data('nivel')) + '</span></span>';
    html += '<span class="cr-info-item"><span class="cr-info-label">Turno:</span> <span class="cr-info-valor">' + crEscapeHtml(opt.data('turno')) + '</span></span>';
    $('#crInfoAluno').html(html).show();
}

function crCarregarRegistros(codAluno) {
    crRegistrosCache = null;
    // Limpar filtros de data ao trocar de aluno
    $('#crFiltroDataDe').val('');
    $('#crFiltroDataAte').val('');
    $('#crFiltroMesAno').val('');
    $('#crListaRegistros').html('<div class="cr-loading"><i class="fa fa-spinner fa-spin"></i> Carregando registros...</div>');
    $('#crGraficosContent').html('<div class="cr-sem-graficos"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoAgente', 'm': 'carregarRegistrosPedagogicosAluno', 'codAluno': codAluno, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            if (!data.statusMessage || !data.registros || data.registros.length === 0) {
                crRegistrosCache = [];
                $('#crListaRegistros').html('<div class="cr-sem-registros"><i class="fa fa-info-circle"></i> Nenhum registro pedag&oacute;gico encontrado para este aluno.</div>');
                crAtualizarAbaGraficos([]);
                return;
            }

            crRegistrosCache = data.registros;

            // Popular o select de mes/ano com os meses disponiveis
            crPopularMesesDisponiveis();

            // Renderizar todos (sem filtro de data)
            crRenderizarRegistros(crRegistrosCache);
            crAtualizarAbaGraficos(crRegistrosCache);
        },
        error: function() {
            $('#crListaRegistros').html('<div class="text-danger">Erro ao carregar registros.</div>');
            crRegistrosCache = [];
            crAtualizarAbaGraficos([]);
        }
    });
}

function crPopularMesesDisponiveis() {
    var meses = {};
    var nomeMeses = ['', 'Janeiro', 'Fevereiro', 'Mar\u00e7o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $.each(crRegistrosCache, function(i, reg) {
        if (reg.dataRegistro) {
            var ym = reg.dataRegistro.substring(0, 7); // YYYY-MM
            if (!meses[ym]) {
                var parts = ym.split('-');
                meses[ym] = nomeMeses[parseInt(parts[1])] + '/' + parts[0];
            }
        }
    });

    var sel = $('#crFiltroMesAno');
    sel.find('option:not(:first)').remove();
    // Ordenar decrescente
    var keys = Object.keys(meses).sort().reverse();
    $.each(keys, function(i, ym) {
        sel.append('<option value="' + ym + '">' + meses[ym] + '</option>');
    });
}

function crFiltrarRegistrosPorData() {
    if (!crRegistrosCache) return [];
    var dataDe = $('#crFiltroDataDe').val();
    var dataAte = $('#crFiltroDataAte').val();

    if (!dataDe && !dataAte) return crRegistrosCache;

    var filtrados = [];
    $.each(crRegistrosCache, function(i, reg) {
        var d = reg.dataRegistro || '';
        if (dataDe && d < dataDe) return true;
        if (dataAte && d > dataAte) return true;
        filtrados.push(reg);
    });
    return filtrados;
}

function crAplicarFiltroData() {
    var filtrados = crFiltrarRegistrosPorData();
    crRenderizarRegistros(filtrados);
    crAtualizarAbaGraficos(filtrados);
}

function crRenderizarRegistros(registros) {
    var container = $('#crListaRegistros');
    container.empty();

    if (!registros || registros.length === 0) {
        var totalGeral = crRegistrosCache ? crRegistrosCache.length : 0;
        if (totalGeral > 0) {
            container.html('<div class="cr-sem-registros"><i class="fa fa-filter"></i> Nenhum registro encontrado no per&iacute;odo selecionado. <br><small>Total geral: ' + totalGeral + ' registro(s)</small></div>');
        } else {
            container.html('<div class="cr-sem-registros"><i class="fa fa-info-circle"></i> Nenhum registro pedag&oacute;gico encontrado para este aluno.</div>');
        }
        return;
    }

    var totalGeral = crRegistrosCache ? crRegistrosCache.length : registros.length;
    var textoTotal = '<strong>' + registros.length + '</strong> registro(s)';
    if (registros.length < totalGeral) {
        textoTotal += ' de <strong>' + totalGeral + '</strong> no total (filtro aplicado)';
    }
    container.append('<div class="cr-total">' + textoTotal + '</div>');

    $.each(registros, function(i, reg) {
        var situacoesHtml = '-';
        if (reg.situacoes) {
            try {
                var arr = JSON.parse(reg.situacoes);
                if (arr.length > 0) {
                    situacoesHtml = '<div class="cr-situacoes-lista">';
                    $.each(arr, function(j, v) {
                        situacoesHtml += '<span class="cr-sit-badge">' + crLabel(v) + '</span>';
                    });
                    situacoesHtml += '</div>';
                } else {
                    situacoesHtml = 'Nenhuma';
                }
            } catch(e) {
                situacoesHtml = crEscapeHtml(reg.situacoes);
            }
        }

        var html = '<div class="cr-registro-card">';
        html += '<div class="cr-data"><i class="fa fa-calendar"></i> ' + crFormatDate(reg.dataRegistro) + '</div>';
        html += '<div>';
        html += '<span class="cr-campo"><span class="cr-campo-label">Condi&ccedil;&atilde;o Inicial:</span> <span class="cr-campo-valor">' + crLabel(reg.condicaoInicio) + '</span></span>';
        html += '<span class="cr-campo"><span class="cr-campo-label">Participa&ccedil;&atilde;o:</span> <span class="cr-campo-valor">' + crLabel(reg.participacao) + '</span></span>';
        html += '<span class="cr-campo"><span class="cr-campo-label">Grau de Apoio:</span> <span class="cr-campo-valor">' + crLabel(reg.grauApoio) + '</span></span>';
        html += '</div>';
        html += '<div>';
        html += '<span class="cr-campo"><span class="cr-campo-label">Condi&ccedil;&atilde;o Final:</span> <span class="cr-campo-valor">' + crLabel(reg.condicaoFinal) + '</span></span>';
        html += '<span class="cr-campo"><span class="cr-campo-label">Evolu&ccedil;&atilde;o:</span> <span class="cr-campo-valor">' + crLabel(reg.evolucaoPeriodo) + '</span></span>';
        html += '</div>';
        html += '<div><span class="cr-campo"><span class="cr-campo-label">Situa&ccedil;&otilde;es Observadas:</span></span>' + situacoesHtml + '</div>';
        if (reg.interesses) {
            html += '<div class="cr-obs"><i class="fa fa-star-o"></i> <strong>Interesses:</strong> ' + crEscapeHtml(reg.interesses) + '</div>';
        }
        if (reg.brincadeirasFavoritas) {
            html += '<div class="cr-obs"><i class="fa fa-gamepad"></i> <strong>Brincadeiras:</strong> ' + crEscapeHtml(reg.brincadeirasFavoritas) + '</div>';
        }
        if (reg.comportamentoAlimentar) {
            html += '<div class="cr-obs"><i class="fa fa-cutlery"></i> <strong>Alimenta&ccedil;&atilde;o:</strong> ' + crEscapeHtml(reg.comportamentoAlimentar) + '</div>';
        }
        if (reg.observacao) {
            html += '<div class="cr-obs"><i class="fa fa-comment-o"></i> ' + crEscapeHtml(reg.observacao) + '</div>';
        }
        html += '</div>';

        container.append(html);
    });
}

function crAtualizarAbaGraficos(registros) {
    if (!registros || registros.length < 2) {
        $('#crGraficosContent').html('<div class="cr-sem-graficos"><i class="fa fa-info-circle"></i> S&atilde;o necess&aacute;rios pelo menos 2 registros para gerar os gr&aacute;ficos.</div>');
        return;
    }
    if ($('#tabGraficos').hasClass('active')) {
        crGerarGraficos(registros);
    } else {
        $('#crGraficosContent').html(
            '<div class="cr-graficos">' +
                '<div class="cr-grafico-box">' +
                    '<div class="cr-grafico-titulo"><i class="fa fa-line-chart"></i> Evolu&ccedil;&atilde;o ao Longo do Tempo</div>' +
                    '<canvas id="crGraficoEvolucao" height="220"></canvas>' +
                '</div>' +
                '<div class="cr-grafico-box" id="crGraficoSituacoesBox">' +
                    '<div class="cr-grafico-titulo"><i class="fa fa-bar-chart"></i> Situa&ccedil;&otilde;es Observadas</div>' +
                    '<canvas id="crGraficoSituacoes" height="220"></canvas>' +
                '</div>' +
            '</div>'
        );
    }
}

function crGerarGraficos(registros) {
    if ($('#crGraficoEvolucao').length === 0) {
        $('#crGraficosContent').html(
            '<div class="cr-graficos">' +
                '<div class="cr-grafico-box">' +
                    '<div class="cr-grafico-titulo"><i class="fa fa-line-chart"></i> Evolu&ccedil;&atilde;o ao Longo do Tempo</div>' +
                    '<canvas id="crGraficoEvolucao" height="220"></canvas>' +
                '</div>' +
                '<div class="cr-grafico-box" id="crGraficoSituacoesBox">' +
                    '<div class="cr-grafico-titulo"><i class="fa fa-bar-chart"></i> Situa&ccedil;&otilde;es Observadas</div>' +
                    '<canvas id="crGraficoSituacoes" height="220"></canvas>' +
                '</div>' +
            '</div>'
        );
    }

    var sorted = registros.slice().sort(function(a, b) {
        return (a.dataRegistro || '').localeCompare(b.dataRegistro || '');
    });

    var datas = [];
    var seriesCondicaoInicio = [];
    var seriesParticipacao = [];
    var seriesGrauApoio = [];
    var seriesCondicaoFinal = [];
    var seriesEvolucao = [];
    var sitContagem = {};

    $.each(sorted, function(i, reg) {
        datas.push(crFormatDate(reg.dataRegistro));
        seriesCondicaoInicio.push(crScores.condicaoInicio[reg.condicaoInicio] || null);
        seriesParticipacao.push(crScores.participacao[reg.participacao] || null);
        seriesGrauApoio.push(crScores.grauApoio[reg.grauApoio] || null);
        seriesCondicaoFinal.push(crScores.condicaoFinal[reg.condicaoFinal] || null);
        seriesEvolucao.push(crScores.evolucaoPeriodo[reg.evolucaoPeriodo] || null);

        if (reg.situacoes) {
            try {
                var arr = JSON.parse(reg.situacoes);
                $.each(arr, function(j, v) {
                    if (v && v !== 'nenhuma') {
                        sitContagem[v] = (sitContagem[v] || 0) + 1;
                    }
                });
            } catch(e) {}
        }
    });

    if (crChartEvolucao) { crChartEvolucao.destroy(); crChartEvolucao = null; }
    if (crChartSituacoes) { crChartSituacoes.destroy(); crChartSituacoes = null; }

    var ctxEv = document.getElementById('crGraficoEvolucao').getContext('2d');
    crChartEvolucao = new Chart(ctxEv, {
        type: 'line',
        data: {
            labels: datas,
            datasets: [
                { label: 'Condi\u00e7\u00e3o Inicial', data: seriesCondicaoInicio, borderColor: '#2196F3', backgroundColor: 'rgba(33,150,243,0.1)', tension: 0.3, fill: false, pointRadius: 4 },
                { label: 'Participa\u00e7\u00e3o', data: seriesParticipacao, borderColor: '#4CAF50', backgroundColor: 'rgba(76,175,80,0.1)', tension: 0.3, fill: false, pointRadius: 4 },
                { label: 'Grau de Apoio', data: seriesGrauApoio, borderColor: '#FF9800', backgroundColor: 'rgba(255,152,0,0.1)', tension: 0.3, fill: false, pointRadius: 4 },
                { label: 'Condi\u00e7\u00e3o Final', data: seriesCondicaoFinal, borderColor: '#9C27B0', backgroundColor: 'rgba(156,39,176,0.1)', tension: 0.3, fill: false, pointRadius: 4 },
                { label: 'Evolu\u00e7\u00e3o', data: seriesEvolucao, borderColor: '#f44336', backgroundColor: 'rgba(244,67,54,0.1)', tension: 0.3, fill: false, pointRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var v = ctx.parsed.y;
                            var desc = '';
                            if (v >= 2.8) desc = ' (Bom)';
                            else if (v >= 1.8) desc = ' (Regular)';
                            else desc = ' (Aten\u00e7\u00e3o)';
                            return ctx.dataset.label + ': ' + v.toFixed(1) + desc;
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0.5, max: 3.5,
                    ticks: {
                        stepSize: 1,
                        callback: function(val) {
                            if (val === 1) return 'Aten\u00e7\u00e3o';
                            if (val === 2) return 'Regular';
                            if (val === 3) return 'Bom';
                            return '';
                        }
                    }
                }
            }
        }
    });

    var sitKeys = Object.keys(sitContagem);
    if (sitKeys.length > 0) {
        sitKeys.sort(function(a, b) { return sitContagem[b] - sitContagem[a]; });
        var sitLabelsArr = sitKeys.map(function(k) { return crLabel(k); });
        var sitValuesArr = sitKeys.map(function(k) { return sitContagem[k]; });
        var sitColors = ['#f44336','#FF9800','#FFC107','#2196F3','#9C27B0','#607D8B'];

        var ctxSit = document.getElementById('crGraficoSituacoes').getContext('2d');
        crChartSituacoes = new Chart(ctxSit, {
            type: 'bar',
            data: {
                labels: sitLabelsArr,
                datasets: [{
                    label: 'Ocorr\u00eancias',
                    data: sitValuesArr,
                    backgroundColor: sitKeys.map(function(k, i) { return sitColors[i % sitColors.length]; }),
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
        $('#crGraficoSituacoesBox').show();
    } else {
        $('#crGraficoSituacoesBox').hide();
    }
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
        <h4><i class="fa fa-clipboard"></i> Registros Di&aacute;rios</h4>
    </div>
    <div class="panel-body">

        <!-- Filtros -->
        <div class="cr-filtros">
            <div class="row">
                <div class="col-sm-4">
                    <label for="crFiltroAgente"><i class="fa fa-user-circle-o"></i> Agente:</label>
                    <select id="crFiltroAgente" class="form-control input-sm">
                        <option value="">Todos os agentes</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label for="crFiltroColegio"><i class="fa fa-institution"></i> Col&eacute;gio:</label>
                    <select id="crFiltroColegio" class="form-control input-sm">
                        <option value="">Todos os col&eacute;gios</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label for="crFiltroTurno"><i class="fa fa-clock-o"></i> Turno:</label>
                    <select id="crFiltroTurno" class="form-control input-sm">
                        <option value="">Todos</option>
                        <option value="Matutino">Matutino</option>
                        <option value="Vespertino">Vespertino</option>
                        <option value="Integral">Integral</option>
                    </select>
                </div>
                <div class="col-sm-3" style="padding-top: 18px;">
                    <button id="crBtnLimparFiltros" class="btn btn-default btn-sm"><i class="fa fa-eraser"></i> Limpar filtros</button>
                    <span id="crFiltroCount" class="cr-filtro-count" style="margin-left:8px;"></span>
                </div>
            </div>
        </div>

        <!-- Select de aluno -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-sm-6">
                <label for="crSelectAluno">Selecionar Aluno:</label>
                <select id="crSelectAluno" class="form-control">
                    <option value="">Selecione um aluno...</option>
                </select>
            </div>
        </div>

        <div id="crInfoAluno" class="cr-info-aluno" style="display:none;"></div>

        <!-- Abas -->
        <div id="crAbas" style="display:none;">
            <ul class="nav nav-tabs" role="tablist" style="margin-bottom:15px;">
                <li role="presentation" class="active"><a href="#tabRegistros" data-toggle="tab" role="tab"><i class="fa fa-list"></i> Registros</a></li>
                <li role="presentation"><a href="#tabGraficos" data-toggle="tab" role="tab"><i class="fa fa-line-chart"></i> Gr&aacute;ficos</a></li>
            </ul>

            <!-- Filtro de periodo -->
            <div class="cr-filtros" style="margin-bottom:15px; padding:10px 15px;">
                <div class="row">
                    <div class="col-sm-3">
                        <label for="crFiltroMesAno" style="font-size:12px;"><i class="fa fa-calendar"></i> M&ecirc;s/Ano:</label>
                        <select id="crFiltroMesAno" class="form-control input-sm">
                            <option value="">Todos os meses</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label for="crFiltroDataDe" style="font-size:12px;">Data de:</label>
                        <input type="date" id="crFiltroDataDe" class="form-control input-sm">
                    </div>
                    <div class="col-sm-3">
                        <label for="crFiltroDataAte" style="font-size:12px;">Data at&eacute;:</label>
                        <input type="date" id="crFiltroDataAte" class="form-control input-sm">
                    </div>
                    <div class="col-sm-3" style="padding-top:18px;">
                        <button id="crBtnLimparFiltroData" class="btn btn-default btn-sm"><i class="fa fa-eraser"></i> Limpar per&iacute;odo</button>
                    </div>
                </div>
            </div>

            <div class="tab-content">

                <!-- TAB 1: Registros -->
                <div role="tabpanel" class="tab-pane active" id="tabRegistros">
                    <div id="crListaRegistros"></div>
                </div>

                <!-- TAB 2: Graficos -->
                <div role="tabpanel" class="tab-pane" id="tabGraficos">
                    <div id="crGraficosContent">
                        <div class="cr-sem-graficos"><i class="fa fa-info-circle"></i> Selecione um aluno para visualizar os gr&aacute;ficos.</div>
                    </div>
                </div>

            </div>
        </div>

        <p class="text-muted" style="font-size:13px; margin-top:15px;">
            <i class="fa fa-info-circle"></i> Use os filtros acima para localizar o aluno desejado. Selecione-o para visualizar seus registros pedag&oacute;gicos.
        </p>

    </div>
</div>
