<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");

try {
    $objSRV = new GerenciarRegistroOrientacaoAC();
    $isAdmin = $objSRV->isAdmin();
    $codSupervisorLogado = $objSRV->getCodSupervisor();
} catch (Exception $e) {
    echo '<div class="alert alert-danger" style="margin-top:15px;">' . $e->getMessage() . '</div>';
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
    return;
}

$labelsAtividadesDia = GerenciarRegistroOrientacaoAC::getLabelsAtividadesDia();
$labelsAtividadesPes = GerenciarRegistroOrientacaoAC::getLabelsAtividadesPes();
$labelsAtividadesCrianca = GerenciarRegistroOrientacaoAC::getLabelsAtividadesCrianca();
?>
<script type="text/javascript">

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
function formatarData(d) {
    if (!d) return '';
    var p = d.split('-');
    return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : d;
}
function formatarDataHora(dh) {
    if (!dh) return '';
    var partes = dh.split(' ');
    if (partes.length !== 2) return dh;
    return formatarData(partes[0]) + ' às ' + partes[1].substring(0, 5);
}
function renderStatusLeituraOrientacao(dataLeitura) {
    if (dataLeitura) {
        return '<div class="ro-leitura-ok"><i class="fa fa-check-circle"></i> Lida em ' + formatarDataHora(dataLeitura) + '</div>';
    }
    return '<div class="ro-leitura-pendente"><i class="fa fa-clock-o"></i> Ainda não lida pelo PES</div>';
}
function dataHojeIso() {
    var d = new Date();
    var mm = ('0' + (d.getMonth() + 1)).slice(-2);
    var dd = ('0' + d.getDate()).slice(-2);
    return d.getFullYear() + '-' + mm + '-' + dd;
}

var labelsAtividadesDia = <?php echo json_encode($labelsAtividadesDia) ?>;
var labelsAtividadesPes = <?php echo json_encode($labelsAtividadesPes) ?>;
var labelsAtividadesCrianca = <?php echo json_encode($labelsAtividadesCrianca) ?>;

var listaPes = [];
var listaCriancas = [];
var isAdmin = <?php echo $isAdmin ? 'true' : 'false' ?>;

$(document).ready(function() {
    <?php if (!$isAdmin) { ?>
    carregarListaPesAjax();
    carregarListaCriancasAjax();
    $('#campoData').val(dataHojeIso());
    // Entry-point: mostra historico primeiro; editor so abre quando clicar em "Novo" ou numa data
    carregarHistorico();

    $('#campoData').on('change', carregarDiaAtual);
    $('#btnPreencherDia').on('click', abrirModalDia);
    $('#btnEditarDia').on('click', abrirModalDia);
    $('#btnAdicionarPes').on('click', function() { abrirModalPes(null); });
    $('#btnAdicionarCrianca').on('click', function() { abrirModalCrianca(null); });
    $('#modalCriancaColegio').on('change', function() { aplicarFiltroColegioCrianca($(this).val(), ''); });
    $('#modalPesColegio').on('change', function() { aplicarFiltroColegioPes($(this).val(), ''); });
    $('#btnSalvarDia').on('click', salvarDia);
    $('#btnSalvarPes').on('click', salvarItemPes);
    $('#btnSalvarCrianca').on('click', salvarItemCrianca);

    $('#btnNovoRegistro').on('click', function() {
        $('#blocoHistorico').hide();
        $('#blocoEditor').show();
        $('#campoData').val(dataHojeIso());
        carregarDiaAtual();
    });
    $('#btnVoltarEditor').on('click', function() {
        $('#blocoEditor').hide();
        $('#blocoHistorico').show();
        carregarHistorico();
    });
    <?php } else { ?>
    carregarListaSupervisoresAjax();
    $('#filtroSupervisor').on('change', carregarHistorico);
    $('#btnExportarCsv').on('click', exportarCsv);
    <?php } ?>
});

function exportarCsv() {
    var codSupervisor = $('#filtroSupervisor').val(); // vazio = todos
    var dataInicio = $('#filtroDataInicio').val();
    var dataFim = $('#filtroDataFim').val();
    if (!dataInicio || !dataFim) {
        processJson({ erro: 'Informe a data inicial e a data final.' });
        return;
    }
    if (dataFim < dataInicio) {
        processJson({ erro: 'A data final nao pode ser anterior a data inicial.' });
        return;
    }
    var form = $('<form>', { method: 'POST', action: '<?php echo WB_URL_COMMAND ?>', target: '_blank' });
    form.append($('<input>', { type: 'hidden', name: 'c', value: 'GerenciarRegistroOrientacao' }));
    form.append($('<input>', { type: 'hidden', name: 'm', value: 'exportarPlanilha' }));
    form.append($('<input>', { type: 'hidden', name: 'codSupervisorFiltro', value: codSupervisor || '' }));
    form.append($('<input>', { type: 'hidden', name: 'dataInicio', value: dataInicio }));
    form.append($('<input>', { type: 'hidden', name: 'dataFim', value: dataFim }));
    form.append($('<input>', { type: 'hidden', name: '_submit_check', value: '1' }));
    $('body').append(form);
    form.submit();
    form.remove();
}

// ========== LISTAS ==========
function carregarListaPesAjax() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroOrientacao', 'm': 'carregarListaPes', '_submit_check': '1'
    }, function(data) {
        if (data.statusMessage) listaPes = data.lista;
    }, 'json');
}
function carregarListaCriancasAjax() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroOrientacao', 'm': 'carregarListaCriancas', '_submit_check': '1'
    }, function(data) {
        if (data.statusMessage) listaCriancas = data.lista;
    }, 'json');
}
function carregarListaSupervisoresAjax() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroOrientacao', 'm': 'carregarListaSupervisores', '_submit_check': '1'
    }, function(data) {
        if (data.statusMessage && data.lista) {
            for (var i = 0; i < data.lista.length; i++) {
                $('#filtroSupervisor').append('<option value="' + data.lista[i].cod_usuario + '">' + escapeHtml(data.lista[i].nome) + '</option>');
            }
        }
    }, 'json');
}

function montarOptionsPes(valorSelecionado, codColegioFiltro) {
    var html = '<option value="">Selecione o PES</option>';
    for (var i = 0; i < listaPes.length; i++) {
        var pes = listaPes[i];
        if (codColegioFiltro) {
            var cols = pes.cod_colegios || [];
            var match = false;
            for (var k = 0; k < cols.length; k++) {
                if (cols[k] == codColegioFiltro) { match = true; break; }
            }
            if (!match) continue;
        }
        var sel = (pes.cod_inscrito == valorSelecionado) ? ' selected' : '';
        html += '<option value="' + pes.cod_inscrito + '"' + sel + '>' + escapeHtml(pes.nome_inscrito) + '</option>';
    }
    return html;
}
function montarOptionsColegioPes(valorSelecionado) {
    // Deriva os colegios a partir dos cod_colegios em listaPes (alinhado a quem o supervisor cobre).
    var vistos = {};
    var colegios = [];
    for (var i = 0; i < listaPes.length; i++) {
        var cols = listaPes[i].cod_colegios || [];
        for (var k = 0; k < cols.length; k++) {
            var cod = cols[k];
            if (!cod || vistos[cod]) continue;
            vistos[cod] = true;
            var nome = null;
            for (var j = 0; j < listaCriancas.length; j++) {
                if (listaCriancas[j].cod_colegio == cod) { nome = listaCriancas[j].nome_colegio; break; }
            }
            colegios.push({ cod_colegio: cod, nome_colegio: nome || ('Colégio ' + cod) });
        }
    }
    colegios.sort(function(a, b) { return (a.nome_colegio || '').localeCompare(b.nome_colegio || ''); });
    var html = '<option value="">Selecione o colégio</option>';
    for (var m = 0; m < colegios.length; m++) {
        var sel = (colegios[m].cod_colegio == valorSelecionado) ? ' selected' : '';
        html += '<option value="' + colegios[m].cod_colegio + '"' + sel + '>' + escapeHtml(colegios[m].nome_colegio) + '</option>';
    }
    return { html: html, total: colegios.length };
}
function aplicarFiltroColegioPes(codColegio, codPesSelecionado) {
    if (codColegio) {
        $('#modalPesSelect').html(montarOptionsPes(codPesSelecionado || '', codColegio)).prop('disabled', false);
    } else {
        $('#modalPesSelect').html('<option value="">Selecione primeiro o colégio</option>').prop('disabled', true);
    }
}
function getCodColegioDoPes(codInscrito) {
    for (var i = 0; i < listaPes.length; i++) {
        if (listaPes[i].cod_inscrito == codInscrito) {
            var cols = listaPes[i].cod_colegios || [];
            return cols.length ? cols[0] : '';
        }
    }
    return '';
}
function montarOptionsColegio(valorSelecionado) {
    var vistos = {};
    var colegios = [];
    for (var i = 0; i < listaCriancas.length; i++) {
        var c = listaCriancas[i];
        if (!c.cod_colegio || vistos[c.cod_colegio]) continue;
        vistos[c.cod_colegio] = true;
        colegios.push({ cod_colegio: c.cod_colegio, nome_colegio: c.nome_colegio || ('Col\u00e9gio ' + c.cod_colegio) });
    }
    colegios.sort(function(a, b) { return (a.nome_colegio || '').localeCompare(b.nome_colegio || ''); });
    var html = '<option value="">Selecione o col\u00e9gio</option>';
    for (var k = 0; k < colegios.length; k++) {
        var sel = (colegios[k].cod_colegio == valorSelecionado) ? ' selected' : '';
        html += '<option value="' + colegios[k].cod_colegio + '"' + sel + '>' + escapeHtml(colegios[k].nome_colegio) + '</option>';
    }
    return { html: html, total: colegios.length };
}
function montarOptionsCrianca(valorSelecionado, codColegioFiltro) {
    var html = '<option value="">Selecione a crian\u00e7a</option>';
    for (var i = 0; i < listaCriancas.length; i++) {
        var c = listaCriancas[i];
        if (codColegioFiltro && c.cod_colegio != codColegioFiltro) continue;
        var partes = [];
        if (c.codigo_exibicao) partes.push(c.codigo_exibicao);
        if (c.iniciais) partes.push(c.iniciais);
        var label = partes.join(' - ');
        var turma = [];
        if (c.serie_turma) turma.push(c.serie_turma);
        if (c.turno) turma.push(c.turno);
        if (turma.length) label += ' [' + turma.join(' / ') + ']';
        if (!label) label = 'Aluno n\u00e3o identificado';
        var sel = (c.cod_aluno == valorSelecionado) ? ' selected' : '';
        html += '<option value="' + c.cod_aluno + '"' + sel + '>' + escapeHtml(label) + '</option>';
    }
    return html;
}
function getCodColegioDoAluno(codAluno) {
    for (var i = 0; i < listaCriancas.length; i++) {
        if (listaCriancas[i].cod_aluno == codAluno) return listaCriancas[i].cod_colegio;
    }
    return '';
}
function aplicarFiltroColegioCrianca(codColegio, codAlunoSelecionado) {
    if (codColegio) {
        $('#modalCriancaSelect').html(montarOptionsCrianca(codAlunoSelecionado || '', codColegio)).prop('disabled', false);
    } else {
        $('#modalCriancaSelect').html('<option value="">Selecione primeiro o col\u00e9gio</option>').prop('disabled', true);
    }
}

// ========== DIA ==========
function carregarDiaAtual() {
    var data = $('#campoData').val();
    if (!data) return;
    $('#listaItensPes, #listaItensCrianca').html('<div class="ro-loading">Carregando...</div>');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroOrientacao', 'm': 'carregarDadosDia', 'dataRegistro': data, '_submit_check': '1'
    }, function(data) {
        if (!data.statusMessage) {
            $('#resumoDia').html('<span style="color:#a94442;">' + escapeHtml(data.message || 'Erro ao carregar') + '</span>');
            return;
        }
        renderResumoDia(data.atividadesDia, data.descricaoDia);
        renderItensPes(data.itensPes || []);
        renderItensCrianca(data.itensCrianca || []);
    }, 'json');
}

function renderResumoDia(atividadesDia, descricaoDia) {
    var html = '';
    var temConteudo = false;
    if (atividadesDia) {
        var codigos = atividadesDia.split(',');
        var nomes = [];
        for (var i = 0; i < codigos.length; i++) {
            var cod = codigos[i].trim();
            if (labelsAtividadesDia[cod]) nomes.push(labelsAtividadesDia[cod]);
        }
        if (nomes.length) {
            html += '<div style="margin-bottom:6px;"><strong>Atividades:</strong> ' + nomes.join(', ') + '</div>';
            temConteudo = true;
        }
    }
    if (descricaoDia) {
        html += '<div><strong>Descri\u00e7\u00e3o:</strong> <span style="white-space:pre-line;">' + escapeHtml(descricaoDia) + '</span></div>';
        temConteudo = true;
    }
    if (temConteudo) {
        $('#resumoDia').html(html);
        $('#btnPreencherDia').hide();
        $('#btnEditarDia').show();
    } else {
        $('#resumoDia').html('<em style="color:#888;">Nenhuma atividade do dia preenchida ainda.</em>');
        $('#btnPreencherDia').show();
        $('#btnEditarDia').hide();
    }
    // Preenche o modal para quando for aberto
    $('#modalDiaChecks input[type=checkbox]').prop('checked', false);
    if (atividadesDia) {
        var codigos2 = atividadesDia.split(',');
        for (var j = 0; j < codigos2.length; j++) {
            $('#modalDiaChecks input[value="' + codigos2[j].trim() + '"]').prop('checked', true);
        }
    }
    $('#modalDiaDescricao').val(descricaoDia || '');
}

function abrirModalDia() {
    $('#modalDia').modal('show');
}

function salvarDia() {
    var data = $('#campoData').val();
    var atividades = [];
    $('#modalDiaChecks input:checked').each(function() { atividades.push($(this).val()); });
    var dados = {
        'c': 'GerenciarRegistroOrientacao', 'm': 'salvarDia', '_submit_check': '1',
        'dataRegistro': data,
        'atividades_dia[]': atividades,
        'descricaoDia': $('#modalDiaDescricao').val()
    };
    $.ajax({
        type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), data: dados, dataType: 'json', cache: false,
        beforeSend: function() { startLoading(); },
        success: function(resp) {
            processJson(resp);
            if (resp.statusMessage) {
                $('#modalDia').modal('hide');
                carregarDiaAtual();
            }
        },
        complete: function() { stopLoading(); }
    });
}

// ========== PES ==========
function renderItensPes(itens) {
    if (!itens.length) {
        $('#listaItensPes').html('<div class="ro-empty">Nenhuma supervis\u00e3o de PES registrada neste dia.</div>');
        return;
    }
    var html = '';
    for (var i = 0; i < itens.length; i++) {
        var p = itens[i];
        html += '<div class="ro-card" data-id="' + p.cod_registro_pes + '">';
        html += '<div class="ro-card-title"><i class="fa fa-user"></i> ' + escapeHtml(p.nome_pes || 'PES ' + p.cod_agente) + '</div>';
        if (p.atividades_pes) {
            var ap = p.atividades_pes.split(',');
            var nomes = [];
            for (var j = 0; j < ap.length; j++) { nomes.push(labelsAtividadesPes[ap[j].trim()] || ap[j].trim()); }
            html += '<div class="ro-card-meta"><em>Atividades:</em> ' + nomes.join(', ') + '</div>';
        }
        if (p.descricao_pes) html += '<div class="ro-card-text"><em>Descri\u00e7\u00e3o:</em> <span style="white-space:pre-line;">' + escapeHtml(p.descricao_pes) + '</span></div>';
        if (p.orientacoes_pes) html += '<div class="ro-card-text ro-card-orientacoes"><em><i class="fa fa-commenting"></i> Orienta\u00e7\u00f5es PES:</em> <span style="white-space:pre-line;">' + escapeHtml(p.orientacoes_pes) + '</span>' + renderStatusLeituraOrientacao(p.data_leitura_pes) + '</div>';
        html += '<div class="ro-card-actions">';
        html += '<button type="button" class="btn btn-sm btn-default ro-editar-pes" data-id="' + p.cod_registro_pes + '"><i class="fa fa-pencil"></i> Editar</button> ';
        html += '<button type="button" class="btn btn-sm btn-danger ro-excluir-pes" data-id="' + p.cod_registro_pes + '"><i class="fa fa-trash"></i> Excluir</button>';
        html += '</div></div>';
    }
    $('#listaItensPes').html(html);
    $('#listaItensPes .ro-editar-pes').on('click', function() { abrirModalPes($(this).data('id')); });
    $('#listaItensPes .ro-excluir-pes').on('click', function() {
        abrirModalExclusao('pes', $(this).data('id'));
    });
}

function abrirModalPes(codRegistroPes) {
    var colegiosPes = montarOptionsColegioPes('');
    $('#modalPesColegio').html(colegiosPes.html);
    aplicarFiltroColegioPes('', '');
    $('#modalPesChecks input[type=checkbox]').prop('checked', false);
    $('#modalPesDescricao').val('');
    $('#modalPesOrientacoes').val('');
    $('#modalPesCodItem').val('');
    $('#modalPesStatusLeitura').html('');

    // Se o supervisor cobre um unico colegio, pre-seleciona e esconde a linha
    if (colegiosPes.total === 1) {
        var unico = $('#modalPesColegio option:eq(1)').val();
        $('#modalPesColegio').val(unico);
        $('#modalPesLinhaColegio').hide();
        aplicarFiltroColegioPes(unico, '');
    } else {
        $('#modalPesLinhaColegio').show();
    }

    if (!codRegistroPes) {
        $('#modalPesTitulo').text('Nova Supervis\u00e3o de PES');
        $('#modalPes').modal('show');
        return;
    }
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroOrientacao', 'm': 'carregarItemPes', 'cod_registro_pes': codRegistroPes, '_submit_check': '1'
    }, function(resp) {
        if (!resp.statusMessage || !resp.item) {
            processJson({ erro: 'N\u00e3o foi poss\u00edvel carregar o item.' });
            return;
        }
        $('#modalPesTitulo').text('Editar Supervis\u00e3o de PES');
        $('#modalPesCodItem').val(resp.item.cod_registro_pes);
        var codColegioPes = getCodColegioDoPes(resp.item.cod_agente);
        $('#modalPesColegio').val(codColegioPes);
        aplicarFiltroColegioPes(codColegioPes, resp.item.cod_agente);
        if (resp.item.atividades_pes) {
            var codigos = resp.item.atividades_pes.split(',');
            for (var i = 0; i < codigos.length; i++) {
                $('#modalPesChecks input[value="' + codigos[i].trim() + '"]').prop('checked', true);
            }
        }
        $('#modalPesDescricao').val(resp.item.descricao_pes || '');
        $('#modalPesOrientacoes').val(resp.item.orientacoes_pes || '');
        if (resp.item.orientacoes_pes && resp.item.orientacoes_pes.trim() !== '') {
            $('#modalPesStatusLeitura').html(renderStatusLeituraOrientacao(resp.item.data_leitura_pes));
        } else {
            $('#modalPesStatusLeitura').html('');
        }
        $('#modalPes').modal('show');
    }, 'json');
}

function salvarItemPes() {
    var atividades = [];
    $('#modalPesChecks input:checked').each(function() { atividades.push($(this).val()); });
    var dados = {
        'c': 'GerenciarRegistroOrientacao', 'm': 'salvarItemPes', '_submit_check': '1',
        'dataRegistro': $('#campoData').val(),
        'cod_registro_pes': $('#modalPesCodItem').val(),
        'cod_agente': $('#modalPesSelect').val(),
        'atividades_pes[]': atividades,
        'descricao_pes': $('#modalPesDescricao').val(),
        'orientacoes_pes': $('#modalPesOrientacoes').val()
    };
    $.ajax({
        type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), data: dados, dataType: 'json', cache: false,
        beforeSend: function() { startLoading(); },
        success: function(resp) {
            processJson(resp);
            if (resp.statusMessage) {
                $('#modalPes').modal('hide');
                carregarDiaAtual();
            }
        },
        complete: function() { stopLoading(); }
    });
}

// ========== CRIANCA ==========
function renderItensCrianca(itens) {
    if (!itens.length) {
        $('#listaItensCrianca').html('<div class="ro-empty">Nenhuma interven\u00e7\u00e3o com crian\u00e7a registrada neste dia.</div>');
        return;
    }
    var html = '';
    for (var i = 0; i < itens.length; i++) {
        var c = itens[i];
        var identificacao = [];
        if (c.codigo_exibicao) identificacao.push(c.codigo_exibicao);
        if (c.iniciais) identificacao.push(c.iniciais);
        var titulo = identificacao.join(' - ');
        if (c.serie_turma || c.turno) {
            var t = [];
            if (c.serie_turma) t.push(c.serie_turma);
            if (c.turno) t.push(c.turno);
            titulo += ' [' + t.join(' / ') + ']';
        }
        if (c.nome_colegio) titulo += ' (' + c.nome_colegio + ')';
        if (!titulo) titulo = 'Aluno n\u00e3o identificado';

        html += '<div class="ro-card" data-id="' + c.cod_registro_crianca + '">';
        html += '<div class="ro-card-title"><i class="fa fa-child"></i> ' + escapeHtml(titulo) + '</div>';
        if (c.atividades_crianca) {
            var ac = c.atividades_crianca.split(',');
            var nomes = [];
            for (var j = 0; j < ac.length; j++) { nomes.push(labelsAtividadesCrianca[ac[j].trim()] || ac[j].trim()); }
            html += '<div class="ro-card-meta"><em>Atividades:</em> ' + nomes.join(', ') + '</div>';
        }
        if (c.descricao_crianca) html += '<div class="ro-card-text"><em>Descri\u00e7\u00e3o:</em> <span style="white-space:pre-line;">' + escapeHtml(c.descricao_crianca) + '</span></div>';
        if (c.orientacoes_crianca) html += '<div class="ro-card-text ro-card-orientacoes"><em><i class="fa fa-commenting"></i> Orienta\u00e7\u00f5es ao PES sobre esta crian\u00e7a:</em> <span style="white-space:pre-line;">' + escapeHtml(c.orientacoes_crianca) + '</span>' + renderStatusLeituraOrientacao(c.data_leitura_crianca) + '</div>';
        html += '<div class="ro-card-actions">';
        html += '<button type="button" class="btn btn-sm btn-default ro-editar-crianca" data-id="' + c.cod_registro_crianca + '"><i class="fa fa-pencil"></i> Editar</button> ';
        html += '<button type="button" class="btn btn-sm btn-danger ro-excluir-crianca" data-id="' + c.cod_registro_crianca + '"><i class="fa fa-trash"></i> Excluir</button>';
        html += '</div></div>';
    }
    $('#listaItensCrianca').html(html);
    $('#listaItensCrianca .ro-editar-crianca').on('click', function() { abrirModalCrianca($(this).data('id')); });
    $('#listaItensCrianca .ro-excluir-crianca').on('click', function() {
        abrirModalExclusao('crianca', $(this).data('id'));
    });
}

function abrirModalCrianca(codRegistroCrianca) {
    var colegios = montarOptionsColegio('');
    $('#modalCriancaColegio').html(colegios.html);
    aplicarFiltroColegioCrianca('', '');
    $('#modalCriancaChecks input[type=checkbox]').prop('checked', false);
    $('#modalCriancaDescricao').val('');
    $('#modalCriancaOrientacoes').val('');
    $('#modalCriancaStatusLeitura').html('');
    $('#modalCriancaCodItem').val('');

    // Se o supervisor atende um \u00fanico col\u00e9gio, pr\u00e9-seleciona e esconde a linha
    if (colegios.total === 1) {
        var unico = $('#modalCriancaColegio option:eq(1)').val();
        $('#modalCriancaColegio').val(unico);
        $('#modalCriancaLinhaColegio').hide();
        aplicarFiltroColegioCrianca(unico, '');
    } else {
        $('#modalCriancaLinhaColegio').show();
    }

    if (!codRegistroCrianca) {
        $('#modalCriancaTitulo').text('Nova Interven\u00e7\u00e3o com Crian\u00e7a');
        $('#modalCrianca').modal('show');
        return;
    }
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroOrientacao', 'm': 'carregarItemCrianca', 'cod_registro_crianca': codRegistroCrianca, '_submit_check': '1'
    }, function(resp) {
        if (!resp.statusMessage || !resp.item) {
            processJson({ erro: 'N\u00e3o foi poss\u00edvel carregar o item.' });
            return;
        }
        $('#modalCriancaTitulo').text('Editar Interven\u00e7\u00e3o com Crian\u00e7a');
        $('#modalCriancaCodItem').val(resp.item.cod_registro_crianca);
        var codColegioAluno = getCodColegioDoAluno(resp.item.cod_aluno);
        $('#modalCriancaColegio').val(codColegioAluno);
        aplicarFiltroColegioCrianca(codColegioAluno, resp.item.cod_aluno);
        if (resp.item.atividades_crianca) {
            var codigos = resp.item.atividades_crianca.split(',');
            for (var i = 0; i < codigos.length; i++) {
                $('#modalCriancaChecks input[value="' + codigos[i].trim() + '"]').prop('checked', true);
            }
        }
        $('#modalCriancaDescricao').val(resp.item.descricao_crianca || '');
        $('#modalCriancaOrientacoes').val(resp.item.orientacoes_crianca || '');
        if (resp.item.orientacoes_crianca && resp.item.orientacoes_crianca.trim() !== '') {
            $('#modalCriancaStatusLeitura').html(renderStatusLeituraOrientacao(resp.item.data_leitura_crianca));
        } else {
            $('#modalCriancaStatusLeitura').html('');
        }
        $('#modalCrianca').modal('show');
    }, 'json');
}

function salvarItemCrianca() {
    var atividades = [];
    $('#modalCriancaChecks input:checked').each(function() { atividades.push($(this).val()); });
    var dados = {
        'c': 'GerenciarRegistroOrientacao', 'm': 'salvarItemCrianca', '_submit_check': '1',
        'dataRegistro': $('#campoData').val(),
        'cod_registro_crianca': $('#modalCriancaCodItem').val(),
        'cod_aluno': $('#modalCriancaSelect').val(),
        'atividades_crianca[]': atividades,
        'descricao_crianca': $('#modalCriancaDescricao').val(),
        'orientacoes_crianca': $('#modalCriancaOrientacoes').val()
    };
    $.ajax({
        type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), data: dados, dataType: 'json', cache: false,
        beforeSend: function() { startLoading(); },
        success: function(resp) {
            processJson(resp);
            if (resp.statusMessage) {
                $('#modalCrianca').modal('hide');
                carregarDiaAtual();
            }
        },
        complete: function() { stopLoading(); }
    });
}

// ========== EXCLUSÃO ==========
var exclusaoAtual = { tipo: null, id: null };
function abrirModalExclusao(tipo, id) {
    exclusaoAtual = { tipo: tipo, id: id };
    var msg = '';
    if (tipo === 'pes') msg = 'Excluir esta supervis\u00e3o de PES?';
    else if (tipo === 'crianca') msg = 'Excluir esta interven\u00e7\u00e3o com crian\u00e7a?';
    else if (tipo === 'dia') msg = 'Excluir o registro deste dia? S\u00f3 \u00e9 poss\u00edvel se n\u00e3o houver supervis\u00f5es de PES ou interven\u00e7\u00f5es com crian\u00e7as vinculadas. Se existirem, exclua os itens primeiro ou solicite ao administrador.';
    $('#modalExclusaoMensagem').text(msg);
    $('#modalExclusao').modal('show');
}
function confirmarExclusao() {
    if (!exclusaoAtual.tipo || !exclusaoAtual.id) return;
    var metodo = '', campo = '';
    if (exclusaoAtual.tipo === 'pes') { metodo = 'excluirItemPes'; campo = 'cod_registro_pes'; }
    else if (exclusaoAtual.tipo === 'crianca') { metodo = 'excluirItemCrianca'; campo = 'cod_registro_crianca'; }
    else if (exclusaoAtual.tipo === 'dia') { metodo = 'excluirRegistro'; campo = 'codRegistro'; }
    var dados = {
        'c': 'GerenciarRegistroOrientacao', 'm': metodo, '_submit_check': '1'
    };
    dados[campo] = exclusaoAtual.id;
    $.ajax({
        type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), data: dados, dataType: 'json', cache: false,
        beforeSend: function() { startLoading(); },
        success: function(resp) {
            processJson(resp);
            if (resp.statusMessage) {
                $('#modalExclusao').modal('hide');
                if (exclusaoAtual.tipo === 'dia') {
                    carregarHistorico();
                } else {
                    carregarDiaAtual();
                }
            }
        },
        complete: function() { stopLoading(); exclusaoAtual = { tipo: null, id: null }; }
    });
}

// ========== HISTÓRICO ==========
function carregarHistorico() {
    var params = { 'c': 'GerenciarRegistroOrientacao', 'm': 'carregarTabela', '_submit_check': '1' };
    <?php if ($isAdmin) { ?>
    var codSup = $('#filtroSupervisor').val();
    if (codSup === '0') {
        $('#tabelaHistorico tbody').html('<tr><td colspan="5" style="padding:15px; color:#888; font-style:italic;">Op&ccedil;&atilde;o dispon&iacute;vel apenas para exporta&ccedil;&atilde;o em planilha. Use o bot&atilde;o "Exportar planilha" acima.</td></tr>');
        return;
    }
    params['codSupervisorFiltro'] = codSup;
    <?php } ?>
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), params, function(html) {
        $('#tabelaHistorico tbody').html(html);
        $('#tabelaHistorico .abrir-dia').off('click').on('click', function() {
            var tr = $(this).closest('tr');
            var dataIso = tr.data('data');
            if (!dataIso) return;
            $('#blocoHistorico').hide();
            $('#blocoEditor').show();
            $('#campoData').val(dataIso);
            carregarDiaAtual();
        });
        $('#tabelaHistorico .excluir').off('click').on('click', function() {
            var id = $(this).closest('tr').attr('id');
            abrirModalExclusao('dia', id);
        });
        $('#tabelaHistorico .visualizar').off('click').on('click', function() {
            var id = $(this).closest('tr').attr('id');
            visualizarRegistroAdmin(id);
        });
    });
}

function visualizarRegistroAdmin(codRegistro) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroOrientacao', 'm': 'visualizarRegistro', 'codRegistro': codRegistro, '_submit_check': '1'
    }, function(data) {
        if (!data.statusMessage) { processJson(data); return; }
        var html = '<h5><i class="fa fa-calendar"></i> Data: ' + formatarData(data.dataRegistro) + '</h5>';
        if (data.nomeSupervisor) {
            html += '<div style="margin-top:2px; color:#555;"><i class="fa fa-user-md"></i> <strong>Supervisor: ' + escapeHtml(data.nomeSupervisor) + '</strong></div>';
        }
        if (data.atividadesDia) {
            var ativs = data.atividadesDia.split(',');
            html += '<div style="margin-top:10px;"><strong>Atividades do dia:</strong><ul style="margin:5px 0;">';
            for (var i = 0; i < ativs.length; i++) {
                var cod = ativs[i].trim();
                html += '<li>' + (labelsAtividadesDia[cod] || cod) + '</li>';
            }
            html += '</ul></div>';
        }
        if (data.descricaoDia) {
            html += '<div style="margin-top:5px;"><strong>Descri\u00e7\u00e3o:</strong><br><span style="white-space:pre-line;">' + escapeHtml(data.descricaoDia) + '</span></div>';
        }
        if (data.itensPes && data.itensPes.length) {
            html += '<hr><h5><i class="fa fa-user"></i> Supervis\u00f5es de PES (' + data.itensPes.length + ')</h5>';
            for (var k = 0; k < data.itensPes.length; k++) {
                var pes = data.itensPes[k];
                html += '<div class="ro-card">';
                html += '<strong>PES: ' + escapeHtml(pes.nome_pes || pes.cod_agente) + '</strong>';
                if (pes.atividades_pes) {
                    var apA = pes.atividades_pes.split(','); var nomesA = [];
                    for (var m = 0; m < apA.length; m++) nomesA.push(labelsAtividadesPes[apA[m].trim()] || apA[m].trim());
                    html += '<div><strong>Atividades:</strong> ' + nomesA.join(', ') + '</div>';
                }
                if (pes.descricao_pes) html += '<div><strong>Descri\u00e7\u00e3o:</strong> <span style="white-space:pre-line;">' + escapeHtml(pes.descricao_pes) + '</span></div>';
                if (pes.orientacoes_pes) html += '<div class="ro-card-orientacoes"><div><strong>Orienta\u00e7\u00e3o vis\u00edvel ao PES:</strong> ' + renderStatusLeituraOrientacao(pes.data_leitura_pes) + '</div><div style="white-space:pre-line; margin-top:4px;">' + escapeHtml(pes.orientacoes_pes) + '</div></div>';
                html += '</div>';
            }
        }
        if (data.itensCrianca && data.itensCrianca.length) {
            html += '<hr><h5><i class="fa fa-child"></i> Interven\u00e7\u00f5es com crian\u00e7as (' + data.itensCrianca.length + ')</h5>';
            for (var n = 0; n < data.itensCrianca.length; n++) {
                var cr = data.itensCrianca[n];
                html += '<div class="ro-card">';
                var identAluno = cr.codigo_exibicao || '';
                if (cr.iniciais) identAluno += (identAluno ? ' - ' : '') + cr.iniciais;
                if (!identAluno) identAluno = 'Aluno não identificado';
                html += '<strong>Aluno: ' + escapeHtml(identAluno) + '</strong>';
                if (cr.atividades_crianca) {
                    var acA = cr.atividades_crianca.split(','); var nomesC = [];
                    for (var p = 0; p < acA.length; p++) nomesC.push(labelsAtividadesCrianca[acA[p].trim()] || acA[p].trim());
                    html += '<div><strong>Atividades:</strong> ' + nomesC.join(', ') + '</div>';
                }
                if (cr.descricao_crianca) html += '<div><strong>Descri\u00e7\u00e3o:</strong> <span style="white-space:pre-line;">' + escapeHtml(cr.descricao_crianca) + '</span></div>';
                if (cr.orientacoes_crianca) html += '<div class="ro-card-orientacoes"><div><strong>Orienta\u00e7\u00e3o vis\u00edvel ao PES:</strong> ' + renderStatusLeituraOrientacao(cr.data_leitura_crianca) + '</div><div style="white-space:pre-line; margin-top:4px;">' + escapeHtml(cr.orientacoes_crianca) + '</div></div>';
                html += '</div>';
            }
        }
        $('#modalVisualizarConteudo').html(html);
        $('#modalVisualizar').modal('show');
    }, 'json');
}
</script>

<style>
.ro-container { background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:15px 20px; margin-top:10px; }
.ro-secao { border:1px solid #e0e0e0; border-radius:8px; padding:12px; margin-bottom:15px; background:#fafafa; }
.ro-secao-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.ro-secao-titulo { font-size:14px; font-weight:600; color:#444; margin:0; }
.ro-card { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:10px 12px; margin-bottom:8px; }
.ro-card-title { font-weight:600; margin-bottom:6px; color:#333; }
.ro-card-meta, .ro-card-text { font-size:13px; margin-top:4px; color:#555; }
.ro-card-orientacoes { background:#fff3cd; padding:6px 10px; border-radius:4px; margin-top:6px; }
.ro-leitura-ok { margin-top:6px; padding:4px 8px; background:#dff0d8; color:#3c763d; border-radius:4px; font-size:12px; display:inline-block; }
.ro-leitura-pendente { margin-top:6px; padding:4px 8px; background:#f0f0f0; color:#777; border-radius:4px; font-size:12px; display:inline-block; font-style:italic; }
.ro-card-actions { margin-top:8px; }
.ro-empty { font-size:13px; color:#888; padding:10px; font-style:italic; }
.ro-loading { text-align:center; padding:10px; color:#999; font-size:13px; }
.ro-modo-switch { display:flex; align-items:center; gap:8px; margin-top:10px; flex-wrap:wrap; }
.ro-modo-switch-label { font-size:13px; font-weight:600; color:#666; margin-right:2px; }
.ro-modo-btn { border:1px solid #cfd6de; background:#fff; color:#555; border-radius:20px; padding:6px 16px; font-size:13px; cursor:pointer; transition:all 0.15s; }
.ro-modo-btn:hover { border-color:#7aa7c7; color:#2e5c8a; }
.ro-modo-btn.ro-modo-ativo { background:#2e7d46; border-color:#2e7d46; color:#fff; font-weight:600; }
</style>

<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<?php if (!$isAdmin) { ?>
<!-- === SUPERVISOR === -->

<!-- Editor do dia (oculto por padrao; abre ao clicar "Novo" ou numa linha do historico) -->
<div id="blocoEditor" class="ro-container" style="display:none;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div>
            <button type="button" id="btnVoltarEditor" class="btn btn-sm btn-default" style="margin-right:10px;">
                <i class="fa fa-arrow-left"></i> Voltar
            </button>
            <label for="campoData" style="margin-right:6px;">Data:</label>
            <input type="date" id="campoData" class="form-control" style="display:inline-block; width:auto;" value="">
        </div>
    </div>

    <!-- Atividades do dia -->
    <div class="ro-secao">
        <div class="ro-secao-header">
            <h5 class="ro-secao-titulo">Atividades do dia</h5>
            <div>
                <button type="button" id="btnPreencherDia" class="btn btn-sm btn-primary" style="display:none;">
                    <i class="fa fa-plus"></i> Preencher
                </button>
                <button type="button" id="btnEditarDia" class="btn btn-sm btn-default" style="display:none;">
                    <i class="fa fa-pencil"></i> Editar
                </button>
            </div>
        </div>
        <div id="resumoDia"></div>
    </div>

    <!-- Supervisões de PES -->
    <div class="ro-secao">
        <div class="ro-secao-header">
            <h5 class="ro-secao-titulo">Supervis&otilde;es de PES</h5>
            <button type="button" id="btnAdicionarPes" class="btn btn-sm btn-primary">
                <i class="fa fa-plus"></i> Adicionar
            </button>
        </div>
        <div id="listaItensPes"></div>
    </div>

    <!-- Intervenções com crianças -->
    <div class="ro-secao">
        <div class="ro-secao-header">
            <h5 class="ro-secao-titulo">Interven&ccedil;&otilde;es com crian&ccedil;as</h5>
            <button type="button" id="btnAdicionarCrianca" class="btn btn-sm btn-primary">
                <i class="fa fa-plus"></i> Adicionar
            </button>
        </div>
        <div id="listaItensCrianca"></div>
    </div>
</div>

<!-- Historico (entry-point do supervisor) -->
<div id="blocoHistorico" style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Hist&oacute;rico de Registros</h4>
        <button type="button" id="btnNovoRegistro" class="btn btn-sm abriModal">
            <i class="fa fa-plus"></i> Novo registro
        </button>
    </div>
    <div style="padding:0;">
        <table class="tbl-fin" id="tabelaHistorico">
            <thead>
                <tr>
                    <th style="width:90px;">A&ccedil;&otilde;es</th>
                    <th style="width:110px;">Data</th>
                    <th>Atividades do dia</th>
                    <th style="width:120px; text-align:center;">Supervis&atilde;o PES</th>
                    <th style="width:160px; text-align:center;">Interven&ccedil;&atilde;o Crian&ccedil;as</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:60px;"></div>
<?php } else { ?>
<!-- === ADMIN (coordenador/gerente) === -->

<!-- Barra "Ver por": alterna entre a visão atual (Supervisor) e as consolidadas (PES/Aluno) -->
<div class="ro-modo-switch">
    <span class="ro-modo-switch-label">Ver por:</span>
    <button type="button" id="btnModoSupervisor" class="ro-modo-btn ro-modo-ativo"><i class="fa fa-user-md"></i> Supervisor</button>
    <button type="button" id="btnModoPes" class="ro-modo-btn"><i class="fa fa-user"></i> PES</button>
    <button type="button" id="btnModoAluno" class="ro-modo-btn"><i class="fa fa-child"></i> Aluno</button>
</div>

<div id="modoSupervisor">
<div class="ro-container">
    <div class="form-group" style="margin-bottom:15px;">
        <label for="filtroSupervisor"><strong>Selecione o Supervisor:</strong></label>
        <select id="filtroSupervisor" class="form-control" style="max-width:400px;">
            <option value="">-- Selecione --</option>
            <option value="0">-- Todos os supervisores (apenas para exportar) --</option>
        </select>
    </div>

    <div style="background:#f7f9fb; border:1px solid #e0e6ec; border-radius:8px; padding:12px 15px; margin-bottom:15px;">
        <div style="font-size:13px; font-weight:600; color:#444; margin-bottom:8px;">
            <i class="fa fa-file-excel-o"></i> Exportar relatorio em planilha (Excel)
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">
            <div>
                <label for="filtroDataInicio" style="font-size:12px; color:#666; display:block; margin-bottom:3px;">Data inicial</label>
                <input type="date" id="filtroDataInicio" class="form-control" style="width:160px;"
                    value="<?php echo date('Y-m-01') ?>">
            </div>
            <div>
                <label for="filtroDataFim" style="font-size:12px; color:#666; display:block; margin-bottom:3px;">Data final</label>
                <input type="date" id="filtroDataFim" class="form-control" style="width:160px;"
                    value="<?php echo date('Y-m-d') ?>">
            </div>
            <div>
                <button type="button" id="btnExportarCsv" class="btn abriModal" style="height:34px;">
                    <i class="fa fa-download"></i> Exportar planilha
                </button>
            </div>
        </div>
        <div style="font-size:11px; color:#888; margin-top:8px;">
            Para exportar todos os supervisores de uma vez, selecione "Todos os supervisores" no filtro acima.
            A planilha trara: data, supervisor, atividades do dia e supervisoes de PES (com atividades, descricao e orientacoes). Dados de criancas nao sao exportados.
        </div>
    </div>

    <table class="tbl-fin" id="tabelaHistorico">
        <thead>
            <tr>
                <th style="width:90px;">A&ccedil;&otilde;es</th>
                <th style="width:110px;">Data</th>
                <th>Atividades do dia</th>
                <th style="width:120px; text-align:center;">Supervis&atilde;o PES</th>
                <th style="width:160px; text-align:center;">Interven&ccedil;&atilde;o Crian&ccedil;as</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
</div><!-- /#modoSupervisor -->

<?php require_once(dirname(__FILE__)."/GerenciarRegistroOrientacao_consulta.php"); ?>

<div style="height:60px;"></div>
<?php } ?>

<!-- === MODAIS (somente supervisor) === -->
<?php if (!$isAdmin) { ?>

<!-- Modal Dia -->
<div class="modal fade" id="modalDia" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title"><i class="fa fa-calendar"></i> Atividades do dia</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div id="modalDiaChecks">
          <?php foreach ($labelsAtividadesDia as $valor => $label) { ?>
          <label style="display:block; margin-bottom:6px; font-weight:normal;">
            <input type="checkbox" value="<?php echo $valor ?>"> <?php echo $label ?>
          </label>
          <?php } ?>
        </div>
        <div class="form-group" style="margin-top:10px;">
          <label for="modalDiaDescricao">Descri&ccedil;&atilde;o breve:</label>
          <textarea id="modalDiaDescricao" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarDia">Salvar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal PES -->
<div class="modal fade" id="modalPes" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title" id="modalPesTitulo">Supervis&atilde;o de PES</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modalPesCodItem" value="">
        <div class="form-group" id="modalPesLinhaColegio">
          <label>Colégio:</label>
          <select id="modalPesColegio" class="form-control"></select>
        </div>
        <div class="form-group">
          <label>PES:</label>
          <select id="modalPesSelect" class="form-control" disabled></select>
        </div>
        <div class="form-group">
          <label>Atividade realizada com o PES:</label>
          <div id="modalPesChecks">
            <?php foreach ($labelsAtividadesPes as $valor => $label) { ?>
            <label style="display:block; margin-bottom:4px; font-weight:normal;">
              <input type="checkbox" value="<?php echo $valor ?>"> <?php echo $label ?>
            </label>
            <?php } ?>
          </div>
        </div>
        <div class="form-group">
          <label for="modalPesDescricao">Descri&ccedil;&atilde;o das atividades:</label>
          <textarea id="modalPesDescricao" class="form-control" rows="2"></textarea>
        </div>
        <div class="form-group">
          <label for="modalPesOrientacoes">Orienta&ccedil;&otilde;es para o PES <small>(vis&iacute;vel ao PES)</small>:</label>
          <textarea id="modalPesOrientacoes" class="form-control" rows="2"></textarea>
          <div id="modalPesStatusLeitura" style="margin-top:6px;"></div>
        </div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarPes">Salvar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Criança -->
<div class="modal fade" id="modalCrianca" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title" id="modalCriancaTitulo">Interven&ccedil;&atilde;o com Crian&ccedil;a</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modalCriancaCodItem" value="">
        <div class="form-group" id="modalCriancaLinhaColegio">
          <label>Col&eacute;gio:</label>
          <select id="modalCriancaColegio" class="form-control"></select>
        </div>
        <div class="form-group">
          <label>Crian&ccedil;a:</label>
          <select id="modalCriancaSelect" class="form-control" disabled></select>
        </div>
        <div class="form-group">
          <label>Atividade realizada com a crian&ccedil;a:</label>
          <div id="modalCriancaChecks">
            <?php foreach ($labelsAtividadesCrianca as $valor => $label) { ?>
            <label style="display:block; margin-bottom:4px; font-weight:normal;">
              <input type="checkbox" value="<?php echo $valor ?>"> <?php echo $label ?>
            </label>
            <?php } ?>
          </div>
        </div>
        <div class="form-group">
          <label for="modalCriancaDescricao">Descri&ccedil;&atilde;o das atividades:</label>
          <textarea id="modalCriancaDescricao" class="form-control" rows="2"></textarea>
        </div>
        <div class="form-group">
          <label for="modalCriancaOrientacoes">Orienta&ccedil;&otilde;es ao PES sobre esta crian&ccedil;a <small>(vis&iacute;vel ao PES respons&aacute;vel)</small>:</label>
          <textarea id="modalCriancaOrientacoes" class="form-control" rows="2"></textarea>
          <div id="modalCriancaStatusLeitura" style="margin-top:6px;"></div>
        </div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarCrianca">Salvar</button>
      </div>
    </div>
  </div>
</div>

<?php } ?>

<!-- Modal Exclusão (genérico, usado por supervisor e admin) -->
<div class="modal fade" id="modalExclusao" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalhoExclusao">
        <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p id="modalExclusaoMensagem"></p>
        <p style="color:#a94442; font-size:12px;"><i class="fa fa-exclamation-triangle"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</p>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger modalBtnExcluir" id="btnConfirmarExclusao">Excluir</button>
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function() { $('#btnConfirmarExclusao').on('click', confirmarExclusao); });
</script>

<!-- Modal Visualizar (admin) -->
<?php if ($isAdmin) { ?>
<div class="modal fade" id="modalVisualizar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title"><i class="fa fa-eye"></i> Detalhes do Registro</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body" id="modalVisualizarConteudo" style="max-height:500px; overflow-y:auto;"></div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
<?php } ?>
