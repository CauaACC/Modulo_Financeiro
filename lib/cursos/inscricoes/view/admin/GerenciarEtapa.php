<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarEtapaAC();
?>
<script type="text/javascript">
$(document).ready(function() {

    carregarTabelaEtapas();

    // Delegated events
    $('#itens').on('click', '.editar', function() {
        editarEtapa($(this).closest('tr').attr('id'));
    });
    $('#itens').on('click', '.excluir', function() {
        excluirEtapa($(this).closest('tr').attr('id'));
    });
    $('#itens').on('click', '.btn-toggle-ativa', function(e) {
        e.stopPropagation();
        toggleAtivaEtapa($(this).closest('tr').attr('id'));
    });

    $('#btnSalvarEtapa').click(function() {
        salvarEtapa();
    });

    // Re-renderiza a timeline + valida pares quando qualquer data muda
    $('#modalEtapa').on('input change', 'input[type=datetime-local]', function() {
        atualizarTimelineEtapa();
        validarDatasEtapa();
    });
});

// === Helpers da timeline + validacao ===
function formatarDataTimeline(iso) {
    // 'YYYY-MM-DDTHH:MM' -> 'DD/MM' (ou 'DD/MM HH:MM' se hora != 00:00)
    if (!iso) return '';
    var p = String(iso).split('T');
    if (!p[0]) return '';
    var d = p[0].split('-');
    var h = p[1] || '';
    var data = d[2] + '/' + d[1];
    if (h && h !== '00:00' && h !== '00:00:00') {
        return data + ' ' + h.substring(0,5);
    }
    return data;
}
function formatarRangeTimeline(ini, fim) {
    var i = formatarDataTimeline(ini);
    var f = formatarDataTimeline(fim);
    if (i && f) {
        if (i === f) return i;
        return i + ' a ' + f;
    }
    return i || f || '';
}
function atualizarTimelineEtapa() {
    var steps = [
        {id:'tl-1', txt: formatarRangeTimeline($('#analiseDatIni').val(), $('#analiseDatFim').val())},
        {id:'tl-2', txt: formatarDataTimeline($('#divulgacaoPreliminarDat').val())},
        {id:'tl-3', txt: formatarRangeTimeline($('#recursoDatIni').val(), $('#recursoDatFim').val())},
        {id:'tl-4', txt: formatarRangeTimeline($('#analiseRecursoDatIni').val(), $('#analiseRecursoDatFim').val())},
        {id:'tl-5', txt: formatarDataTimeline($('#divulgacaoFinalDat').val())}
    ];
    steps.forEach(function(s) {
        var $el = $('#' + s.id);
        $el.find('.datas').text(s.txt || '—');
        if (s.txt) { $el.removeClass('empty'); } else { $el.addClass('empty'); }
    });
}
function validarDatasEtapa() {
    var pares = [
        {card:'card-1', ini:'analiseDatIni',         fim:'analiseDatFim',         nome:'An&aacute;lise'},
        {card:'card-3', ini:'recursoDatIni',         fim:'recursoDatFim',         nome:'Recurso'},
        {card:'card-4', ini:'analiseRecursoDatIni',  fim:'analiseRecursoDatFim',  nome:'An&aacute;lise de Recurso'}
    ];
    var ok = true;
    pares.forEach(function(p) {
        var $card = $('#' + p.card);
        $card.find('.etapa-msg-erro').remove();
        $card.removeClass('has-error');
        var i = $('#' + p.ini).val();
        var f = $('#' + p.fim).val();
        if (i && f && f < i) {
            $card.addClass('has-error');
            $card.append('<div class="etapa-msg-erro"><i class="fa fa-exclamation-triangle"></i> Data de fim &eacute; anterior &agrave; data de in&iacute;cio.</div>');
            ok = false;
        }
    });
    // Aviso (nao bloqueia) se sequencia entre cards fica fora de ordem
    var sequencia = [
        {nome:'An&aacute;lise fim',            val:$('#analiseDatFim').val()},
        {nome:'Divulga&ccedil;&atilde;o Preliminar', val:$('#divulgacaoPreliminarDat').val()},
        {nome:'Recurso in&iacute;cio',         val:$('#recursoDatIni').val()},
        {nome:'Recurso fim',                   val:$('#recursoDatFim').val()},
        {nome:'An&aacute;lise Recurso in&iacute;cio', val:$('#analiseRecursoDatIni').val()},
        {nome:'An&aacute;lise Recurso fim',    val:$('#analiseRecursoDatFim').val()},
        {nome:'Divulga&ccedil;&atilde;o Final', val:$('#divulgacaoFinalDat').val()}
    ];
    var ultimo = null, foraSeq = false;
    for (var k=0; k<sequencia.length; k++) {
        var v = sequencia[k].val;
        if (!v) continue;
        if (ultimo && v < ultimo) { foraSeq = true; break; }
        ultimo = v;
    }
    var $avisoSeq = $('#etapaAvisoSequencia');
    if (foraSeq) {
        if (!$avisoSeq.length) {
            $('#modalEtapaMsg').html('<div id="etapaAvisoSequencia" class="alert alert-warning" style="padding:6px 10px; margin-bottom:8px; font-size:12px;"><i class="fa fa-exclamation-triangle"></i> Sequ&ecirc;ncia das datas est&aacute; fora de ordem cronol&oacute;gica (apenas aviso, n&atilde;o impede salvar).</div>');
        }
    } else {
        $avisoSeq.remove();
    }
    return ok;
}

function abrirModalEtapa() {
    limparCampos();
    $('#modalEtapaTitulo').text('Nova Etapa');
    $('#modalEtapa').modal('show');
}

function carregarTabelaEtapas() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarEtapa', 'm': 'carregarTabelaEtapas', '_submit_check': '1'},
        function(html) {
            $('#itens tbody').html(html);
        }
    );
}

function salvarEtapa() {
    var nomeEtapa  = $.trim($('#nomeEtapa').val());
    var ordemEtapa = $.trim($('#ordemEtapa').val());
    var valido = true;
    $('#modalEtapa .form-group').removeClass('has-error');
    $('#modalEtapa .msg-campo').remove();

    if (!nomeEtapa) {
        $('#nomeEtapa').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    if (!ordemEtapa || isNaN(ordemEtapa) || parseInt(ordemEtapa) < 1) {
        $('#ordemEtapa').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Informe um n&uacute;mero v&aacute;lido (1, 2, 3...)</span>');
        valido = false;
    }
    // Validacao das datas dos sub-eventos (impede salvar se fim < ini)
    if (!validarDatasEtapa()) {
        valido = false;
    }
    if (!valido) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarEtapa',
            'm': 'salvarEtapa',
            'codEtapa': $('#codEtapa').val(),
            'nomeEtapa': nomeEtapa,
            'descricao': $.trim($('#descricao').val()),
            'ordemEtapa': ordemEtapa,
            'situacao': $('#situacao').val(),
            'analiseDatIni': $('#analiseDatIni').val(),
            'analiseDatFim': $('#analiseDatFim').val(),
            'divulgacaoPreliminarDat': $('#divulgacaoPreliminarDat').val(),
            'resultadoDivulgado': $('#resultadoDivulgado').is(':checked') ? '1' : '0',
            'recursoDatIni': $('#recursoDatIni').val(),
            'recursoDatFim': $('#recursoDatFim').val(),
            'analiseRecursoDatIni': $('#analiseRecursoDatIni').val(),
            'analiseRecursoDatFim': $('#analiseRecursoDatFim').val(),
            'divulgacaoRecursoDat': $('#divulgacaoRecursoDat').val(),
            'resultadoRecursoDivulgado': $('#resultadoRecursoDivulgado').is(':checked') ? '1' : '0',
            'divulgacaoFinalDat': $('#divulgacaoFinalDat').val(),
            'resultadoFinalDivulgado': $('#resultadoFinalDivulgado').is(':checked') ? '1' : '0',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                limparCampos();
                carregarTabelaEtapas();
                $('#modalEtapa').modal('hide');
            }
        },
        complete: function() { stopLoading(); }
    });
}

function editarEtapa(codEtapa) {
    if (!codEtapa) return;
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'codEtapa': codEtapa, 'c': 'GerenciarEtapa', 'm': 'editarEtapa', '_submit_check': '1'},
        dataType: "json",
        beforeSend: function() { startLoading(); },
        success: function(data) {
            $('#codEtapa').val(data.codEtapa);
            $('#nomeEtapa').val(data.nomeEtapa);
            $('#descricao').val(data.descricao);
            $('#ordemEtapa').val(data.ordemEtapa);
            $('#situacao').val(String(parseInt(data.situacao) || 0));
            // Converte 'YYYY-MM-DD HH:II:SS' do banco pra 'YYYY-MM-DDTHH:II' do input datetime-local
            var toLocal = function(v) { return v ? String(v).replace(' ', 'T').substring(0,16) : ''; };
            $('#analiseDatIni').val(toLocal(data.analiseDatIni));
            $('#analiseDatFim').val(toLocal(data.analiseDatFim));
            $('#divulgacaoPreliminarDat').val(toLocal(data.divulgacaoPreliminarDat));
            $('#resultadoDivulgado').prop('checked', String(data.resultadoDivulgado) === '1');
            $('#recursoDatIni').val(toLocal(data.recursoDatIni));
            $('#recursoDatFim').val(toLocal(data.recursoDatFim));
            $('#analiseRecursoDatIni').val(toLocal(data.analiseRecursoDatIni));
            $('#analiseRecursoDatFim').val(toLocal(data.analiseRecursoDatFim));
            $('#divulgacaoRecursoDat').val(toLocal(data.divulgacaoRecursoDat));
            $('#resultadoRecursoDivulgado').prop('checked', String(data.resultadoRecursoDivulgado) === '1');
            $('#divulgacaoFinalDat').val(toLocal(data.divulgacaoFinalDat));
            $('#resultadoFinalDivulgado').prop('checked', String(data.resultadoFinalDivulgado) === '1');
            $('#modalEtapaTitulo').text('Editar Etapa');
            atualizarTimelineEtapa();
            validarDatasEtapa();
            $('#modalEtapa').modal('show');
        },
        complete: function() { stopLoading(); }
    });
}

function excluirEtapa(codEtapa) {
    $('#modalConfirmarExclusao').data('cod', codEtapa);
    $('#modalConfirmarExclusao').modal('show');
}

function confirmarExclusao() {
    var codEtapa = $('#modalConfirmarExclusao').data('cod');
    $('#modalConfirmarExclusao').modal('hide');
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'codEtapa': codEtapa, 'c': 'GerenciarEtapa', 'm': 'excluirEtapa', '_submit_check': '1'},
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaEtapas();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function toggleAtivaEtapa(codEtapa) {
    if (!codEtapa) return;
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'codEtapa': codEtapa, 'c': 'GerenciarEtapa', 'm': 'toggleAtivaEtapa', '_submit_check': '1'},
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaEtapas();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function limparCampos() {
    $('#codEtapa').val('');
    $('#nomeEtapa').val('');
    $('#descricao').val('');
    $('#ordemEtapa').val('');
    $('#situacao').val('0');
    $('#analiseDatIni').val('');
    $('#analiseDatFim').val('');
    $('#divulgacaoPreliminarDat').val('');
    $('#resultadoDivulgado').prop('checked', false);
    $('#recursoDatIni').val('');
    $('#recursoDatFim').val('');
    $('#analiseRecursoDatIni').val('');
    $('#analiseRecursoDatFim').val('');
    $('#divulgacaoRecursoDat').val('');
    $('#resultadoRecursoDivulgado').prop('checked', false);
    $('#divulgacaoFinalDat').val('');
    $('#resultadoFinalDivulgado').prop('checked', false);
    $('#modalEtapaMsg').html('');
    $('.etapa-card').removeClass('has-error');
    $('.etapa-msg-erro').remove();
    atualizarTimelineEtapa();
}
</script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_avaliacao.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Configurar Etapas</h4>
        <button class="btn btn-sm abriModal" type="button" onclick="abrirModalEtapa()">
            <i class="fa fa-plus"></i> Nova Etapa
        </button>
    </div>
    <div style="padding:12px 18px;">
        <div id="carregando" class="shown hidden">
            <div class="imagem">
                <div class="frase">Carregando...</div>
                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
            </div>
        </div>
    </div>
    <div>
        <table class="tbl-fin" id="itens">
            <thead>
            <tr>
                <th style="width:130px;">A&ccedil;&otilde;es</th>
                <th style="width:60px;">Ordem</th>
                <th>Nome</th>
                <th>Descri&ccedil;&atilde;o</th>
                <th style="width:100px;">Ativa</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Estilos do modal de etapa -->
<style>
.etapa-config-geral {
    background: #f8f9fa;
    border: 1px solid #e8e8e8;
    border-radius: 4px;
    padding: 10px 12px 4px;
    margin-bottom: 12px;
}
.etapa-timeline {
    display: flex;
    align-items: stretch;
    background: #f6fbfa;
    border: 1px solid #d3e8e2;
    border-radius: 6px;
    padding: 8px 4px;
    margin-bottom: 12px;
}
.etapa-timeline-step {
    flex: 1;
    text-align: center;
    padding: 2px 4px;
    border-right: 1px dashed #d3e8e2;
}
.etapa-timeline-step:last-child { border-right: none; }
.etapa-timeline-step .num {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #2D937E;
    color: white;
    line-height: 20px;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 2px;
}
.etapa-timeline-step .titulo {
    display: block;
    color: #333;
    font-weight: 600;
    font-size: 10px;
    line-height: 1.2;
}
.etapa-timeline-step .datas {
    display: block;
    color: #555;
    font-size: 10px;
    margin-top: 1px;
}
.etapa-timeline-step.empty .num { background: #ccc; }
.etapa-timeline-step.empty .titulo,
.etapa-timeline-step.empty .datas { color: #aaa; font-weight: 400; }

.etapa-card {
    border: 1px solid #e0e0e0;
    border-left: 4px solid #2D937E;
    border-radius: 4px;
    padding: 8px 12px 4px;
    margin-bottom: 8px;
    background: #fff;
}
.etapa-card-titulo {
    font-weight: 600;
    color: #333;
    margin-bottom: 6px;
    font-size: 13px;
}
.etapa-num {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #2D937E;
    color: white;
    text-align: center;
    line-height: 20px;
    font-size: 11px;
    font-weight: 700;
    margin-right: 6px;
}
.etapa-msg-erro {
    color: #a94442;
    font-size: 11px;
    margin-top: 3px;
    font-weight: 600;
}
.etapa-card.has-error { border-left-color: #a94442; background: #fdf2f2; }
.etapa-card.has-error input { border-color: #a94442; }
.etapa-card .form-group { margin-bottom: 6px; }
.etapa-card .hint { font-size: 11px; color: #888; }
.etapa-card .checkbox-inline {
    display: inline-flex;
    align-items: center;
    margin: 24px 0 0;
    font-size: 13px;
}
.etapa-card .checkbox-inline input[type=checkbox] { margin-right: 6px; }
</style>

<!-- Modal Etapa -->
<div class="modal fade" id="modalEtapa" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title" id="modalEtapaTitulo"><i class="fa fa-list"></i>Nova Etapa</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="formEtapa" name="formEtapa" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post" novalidate>
      <div class="modal-body">
        <div id="modalEtapaMsg"></div>
        <input type="hidden" value="1" name="_submit_check">
        <input type="hidden" value="GerenciarEtapa" name="c">
        <input type="hidden" value="salvarEtapa" name="m">
        <input type="hidden" name="codEtapa" id="codEtapa" value="">

        <!-- ===== Configura&ccedil;&otilde;es Gerais ===== -->
        <div class="etapa-config-geral">
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="nomeEtapa">Nome da Etapa:</label>
                    <input type="text" id="nomeEtapa" name="nomeEtapa" class="form-control input-sm" placeholder="Ex.: Avalia&ccedil;&atilde;o do Curr&iacute;culo (Anexo II)" required>
                </div>
                <div class="form-group col-sm-2">
                    <label for="ordemEtapa">Ordem:</label>
                    <input type="number" id="ordemEtapa" name="ordemEtapa" class="form-control input-sm" placeholder="1" min="1" required>
                </div>
                <div class="form-group col-sm-4">
                    <label for="situacao">Situa&ccedil;&atilde;o:</label>
                    <select id="situacao" name="situacao" class="form-control input-sm">
                        <option value="0">Inativa</option>
                        <option value="1">Em andamento</option>
                        <option value="2">Conclu&iacute;da</option>
                        <option value="3">Aguardando in&iacute;cio</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:4px;">
                <label for="descricao">Descri&ccedil;&atilde;o:</label>
                <textarea id="descricao" name="descricao" class="form-control input-sm" placeholder="Descri&ccedil;&atilde;o curta da etapa" rows="2"></textarea>
            </div>
        </div>

        <!-- ===== Timeline visual ===== -->
        <h5 style="margin:6px 0 8px; font-weight:600; color:#2D937E;">
            <i class="fa fa-calendar-o"></i> Cronograma da Etapa
        </h5>
        <div class="etapa-timeline">
            <div class="etapa-timeline-step empty" id="tl-1">
                <span class="num">1</span>
                <span class="titulo">An&aacute;lise</span>
                <span class="datas">&mdash;</span>
            </div>
            <div class="etapa-timeline-step empty" id="tl-2">
                <span class="num">2</span>
                <span class="titulo">Div. Preliminar</span>
                <span class="datas">&mdash;</span>
            </div>
            <div class="etapa-timeline-step empty" id="tl-3">
                <span class="num">3</span>
                <span class="titulo">Recurso</span>
                <span class="datas">&mdash;</span>
            </div>
            <div class="etapa-timeline-step empty" id="tl-4">
                <span class="num">4</span>
                <span class="titulo">An. Recurso</span>
                <span class="datas">&mdash;</span>
            </div>
            <div class="etapa-timeline-step empty" id="tl-5">
                <span class="num">5</span>
                <span class="titulo">Div. Final</span>
                <span class="datas">&mdash;</span>
            </div>
        </div>

        <!-- 1. Análise -->
        <div class="etapa-card" id="card-1">
            <div class="etapa-card-titulo"><span class="etapa-num">1</span>An&aacute;lise / Avalia&ccedil;&atilde;o</div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="analiseDatIni">In&iacute;cio:</label>
                    <input type="datetime-local" id="analiseDatIni" name="analiseDatIni" class="form-control input-sm">
                </div>
                <div class="form-group col-sm-6">
                    <label for="analiseDatFim">Fim:</label>
                    <input type="datetime-local" id="analiseDatFim" name="analiseDatFim" class="form-control input-sm">
                </div>
            </div>
            <div class="hint"><i class="fa fa-info-circle"></i> Fora deste per&iacute;odo o bot&atilde;o &quot;Salvar Avalia&ccedil;&atilde;o&quot; fica oculto.</div>
        </div>

        <!-- 2. Divulgação Preliminar -->
        <div class="etapa-card" id="card-2">
            <div class="etapa-card-titulo"><span class="etapa-num">2</span>Divulga&ccedil;&atilde;o Preliminar</div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="divulgacaoPreliminarDat">Data:</label>
                    <input type="datetime-local" id="divulgacaoPreliminarDat" name="divulgacaoPreliminarDat" class="form-control input-sm">
                </div>
                <div class="form-group col-sm-6">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="resultadoDivulgado" name="resultadoDivulgado" value="1">
                        <strong>Resultado preliminar divulgado</strong>
                    </label>
                    <div class="hint">Libera DEFERIDO/INDEFERIDO + nota na &aacute;rea do inscrito.</div>
                </div>
            </div>
        </div>

        <!-- 3. Recurso -->
        <div class="etapa-card" id="card-3">
            <div class="etapa-card-titulo"><span class="etapa-num">3</span>Per&iacute;odo de Recurso</div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="recursoDatIni">In&iacute;cio:</label>
                    <input type="datetime-local" id="recursoDatIni" name="recursoDatIni" class="form-control input-sm">
                </div>
                <div class="form-group col-sm-6">
                    <label for="recursoDatFim">Fim:</label>
                    <input type="datetime-local" id="recursoDatFim" name="recursoDatFim" class="form-control input-sm">
                </div>
            </div>
        </div>

        <!-- 4. Análise de Recurso + Divulgacao do Resultado da Analise do Recurso -->
        <div class="etapa-card" id="card-4">
            <div class="etapa-card-titulo"><span class="etapa-num">4</span>An&aacute;lise de Recurso</div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="analiseRecursoDatIni">In&iacute;cio:</label>
                    <input type="datetime-local" id="analiseRecursoDatIni" name="analiseRecursoDatIni" class="form-control input-sm">
                </div>
                <div class="form-group col-sm-6">
                    <label for="analiseRecursoDatFim">Fim:</label>
                    <input type="datetime-local" id="analiseRecursoDatFim" name="analiseRecursoDatFim" class="form-control input-sm">
                </div>
            </div>
            <div class="row" style="margin-top:4px; padding-top:6px; border-top:1px dashed #e0e0e0;">
                <div class="form-group col-sm-6">
                    <label for="divulgacaoRecursoDat">Divulga&ccedil;&atilde;o do Resultado da An&aacute;lise:</label>
                    <input type="datetime-local" id="divulgacaoRecursoDat" name="divulgacaoRecursoDat" class="form-control input-sm">
                </div>
                <div class="form-group col-sm-6">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="resultadoRecursoDivulgado" name="resultadoRecursoDivulgado" value="1">
                        <strong>Resultado do recurso divulgado</strong>
                    </label>
                    <div class="hint">Libera parecer + status/pontos ap&oacute;s recurso na &aacute;rea do inscrito.</div>
                </div>
            </div>
        </div>

        <!-- 5. Divulgação Final -->
        <div class="etapa-card" id="card-5">
            <div class="etapa-card-titulo"><span class="etapa-num">5</span>Divulga&ccedil;&atilde;o Final</div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="divulgacaoFinalDat">Data:</label>
                    <input type="datetime-local" id="divulgacaoFinalDat" name="divulgacaoFinalDat" class="form-control input-sm">
                </div>
                <div class="form-group col-sm-6">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="resultadoFinalDivulgado" name="resultadoFinalDivulgado" value="1">
                        <strong>Resultado final divulgado</strong>
                    </label>
                    <div class="hint">Libera a classifica&ccedil;&atilde;o final (ap&oacute;s recursos).</div>
                </div>
            </div>
        </div>

      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarEtapa">Salvar</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Confirmar Exclusao -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalhoExclusao">
        <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclus&atilde;o</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p>Deseja realmente excluir esta etapa?</p>
        <p style="color:#a94442; font-size:12px;"><i class="fa fa-exclamation-triangle"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</p>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
      </div>
    </div>
  </div>
</div>
