<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");

// So COORDENADOR avalia. Gerente nao avalia suas proprias atividades (seria self-review).
if ($_papelPedagogico !== 'COORDENADOR') {
    echo '<div class="alert alert-warning" style="margin:20px;"><i class="fa fa-lock"></i> Apenas coordenadores podem avaliar registros de atividade.</div>';
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
    return;
}

try {
    $objSRV = new GerenciarRegistroAtividadePedagogicoAvaliacaoAC();
} catch (Exception $e) {
    echo '<div class="alert alert-danger" style="margin:20px;">' . $e->getMessage() . '</div>';
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
    return;
}
?>

<script type="text/javascript">
var lastEditedIdRAA;

$(document).ready(function() {
    carregarListaGerentesAjax();

    // Filtros pre-definidos: mes/ano atual + status aguardando
    var hoje = new Date();
    var mesAtual = ('0' + (hoje.getMonth() + 1)).slice(-2);
    var anoAtual = hoje.getFullYear().toString();
    $('#filtroMes').val(mesAtual);
    $('#filtroAno').val(anoAtual);
    $('#filtroStatus').val('AGUARDANDO');

    carregarTabela();

    $('#btnAtualizarLista').on('click', carregarTabela);
    $('#filtroGerente, #filtroStatus, #filtroMes, #filtroAno').on('change', carregarTabela);
    $('#btnExportarPlanilhaRAA').on('click', exportarPlanilhaRAA);

    // Bind explicito com delegacao (caso o modal seja re-renderizado)
    $(document).off('click.raaSalvar').on('click.raaSalvar', '#btnSalvarAvaliacaoRAA', function(e) {
        e.preventDefault();
        console.log('[RAA] click salvar');
        salvarAvaliacao();
    });

    // Parecer obrigatorio quando "Nao Validado"
    $('#aStatusRAA').on('change', function() {
        var valor = $(this).val();
        if (valor === 'Não Validado') {
            $('#aParecerRAA').prop('required', true);
            $('#labelParecerRAA').html('Parecer do Avaliador <span style="color:#a94442;">*obrigat&oacute;rio</span>:');
        } else {
            $('#aParecerRAA').prop('required', false);
            $('#labelParecerRAA').html('Parecer do Avaliador (opcional):');
        }
    });
});

function carregarListaGerentesAjax() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroAtividadePedagogicoAvaliacao', 'm': 'carregarListaGerentes', '_submit_check': '1'
    }, function(resp) {
        if (resp && resp.statusMessage && resp.lista) {
            var html = '<option value="">Todos os gerentes</option>';
            for (var i = 0; i < resp.lista.length; i++) {
                html += '<option value="' + resp.lista[i].cod_inscrito + '">' + escapeHtmlRAA(resp.lista[i].nome_inscrito) + '</option>';
            }
            $('#filtroGerente').html(html);
            $('#exportGerente').html(html);
        }
    }, 'json');
}

function exportarPlanilhaRAA() {
    var codInscrito = $('#exportGerente').val(); // vazio = todos
    var dataInicio = $('#exportDataInicio').val();
    var dataFim = $('#exportDataFim').val();
    if (!dataInicio || !dataFim) {
        if (typeof processJson === 'function') {
            processJson({ erro: 'Informe a data inicial e a data final.' });
        } else {
            alert('Informe a data inicial e a data final.');
        }
        return;
    }
    if (dataFim < dataInicio) {
        if (typeof processJson === 'function') {
            processJson({ erro: 'A data final nao pode ser anterior a data inicial.' });
        } else {
            alert('A data final nao pode ser anterior a data inicial.');
        }
        return;
    }
    var form = $('<form>', { method: 'POST', action: '<?php echo WB_URL_COMMAND ?>', target: '_blank' });
    form.append($('<input>', { type: 'hidden', name: 'c', value: 'GerenciarRegistroAtividadePedagogicoAvaliacao' }));
    form.append($('<input>', { type: 'hidden', name: 'm', value: 'exportarPlanilha' }));
    form.append($('<input>', { type: 'hidden', name: 'codInscritoFiltro', value: codInscrito || '' }));
    form.append($('<input>', { type: 'hidden', name: 'dataInicio', value: dataInicio }));
    form.append($('<input>', { type: 'hidden', name: 'dataFim', value: dataFim }));
    form.append($('<input>', { type: 'hidden', name: '_submit_check', value: '1' }));
    $('body').append(form);
    form.submit();
    form.remove();
}

function carregarTabela() {
    var params = {
        'c': 'GerenciarRegistroAtividadePedagogicoAvaliacao',
        'm': 'carregarTabelaRegistroAtividadesAvaliacao',
        '_submit_check': '1',
        'filtroGerente': $('#filtroGerente').val(),
        'filtroStatus': $('#filtroStatus').val(),
        'filtroMes': $('#filtroMes').val(),
        'filtroAno': $('#filtroAno').val()
    };
    $('#tabelaRAA tbody').html('<tr><td colspan="7" style="text-align:center; padding:20px; color:#888;"><i class="fa fa-spinner fa-spin"></i> Carregando...</td></tr>');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), params, function(html) {
        $('#tabelaRAA tbody').html(html);
        $('#tabelaRAA .avaliarAtividade').off('click').on('click', function() {
            var id = $(this).closest('tr').attr('id');
            lastEditedIdRAA = id;
            abrirModalAvaliacao(id);
        });
    });
}

function abrirModalAvaliacao(codRegAtividade) {
    if (!codRegAtividade) return;
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroAtividadePedagogicoAvaliacao',
        'm': 'editarRegistroAtividadeAvaliacao',
        'codRegAtividade': codRegAtividade,
        '_submit_check': '1'
    }, function(resp) {
        if (!resp || !resp.statusMessage) {
            processJson({ erro: 'N&atilde;o foi poss&iacute;vel carregar o registro.' });
            return;
        }
        $('#aCodRegAtividadeRAA').val(resp.codRegAtividade);
        $('#aNomeInscritoRAA').val(resp.nomeInscrito || '');
        $('#aDiaRAA').val(resp.dia || '');
        $('#aDiaSemanaRAA').val(resp.diaSemana || '');
        $('#aHorasRAA').val(resp.horasTrabalhadas || '');
        $('#aDescricaoRAA').val(resp.descricao || '');
        $('#aJustificativaRAA').val(resp.justificativa || '');
        $('#aParecerRAA').val(resp.parecerAvaliador || '');
        $('#aStatusRAA').val(resp.avaliacaoStatus || '').trigger('change');
        carregarArquivosAvaliacao(resp.codRegAtividade);
        $('#modalAvaliacaoRAA').modal('show');
    }, 'json');
}

function carregarArquivosAvaliacao(codRegAtividade) {
    $('#aArquivosRAA').html('<div style="font-size:12px; color:#888; font-style:italic;">Carregando...</div>');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroAtividadePedagogicoAvaliacao',
        'm': 'carregarArquivosRegistroAvaliacao',
        'codRegAtividade': codRegAtividade,
        '_submit_check': '1'
    }, function(html) {
        $('#aArquivosRAA').html(html || '<div style="font-size:12px; color:#888; font-style:italic;">Nenhum arquivo anexado.</div>');
    });
}

function salvarAvaliacao() {
    var codReg = $('#aCodRegAtividadeRAA').val();
    var status = $('#aStatusRAA').val();
    console.log('[RAA] salvando', { codReg: codReg, status: status });
    if (!codReg) {
        alert('Registro nao carregado. Feche e abra o modal novamente.');
        return;
    }
    if (!status) {
        alert('Selecione Validado ou Nao Validado.');
        return;
    }
    if (status === 'Não Validado' && !$('#aParecerRAA').val().trim()) {
        alert('Parecer e obrigatorio para Nao Validado.');
        return;
    }
    var dados = {
        'c': 'GerenciarRegistroAtividadePedagogicoAvaliacao',
        'm': 'salvarRegistroAtividadeAvaliacao',
        '_submit_check': '1',
        'codRegAtividade': codReg,
        'avaliacaoStatus': status,
        'parecerAvaliador': $('#aParecerRAA').val()
    };
    $.ajax({
        type: 'POST',
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: dados,
        dataType: 'json',
        cache: false,
        beforeSend: function() { if (typeof startLoading === 'function') startLoading(); },
        success: function(resp) {
            console.log('[RAA] resposta', resp);
            if (resp && resp.statusMessage) {
                if (typeof processJson === 'function') processJson(resp);
                $('#modalAvaliacaoRAA').modal('hide');
                carregarTabela();
            } else {
                alert('Erro ao salvar: ' + (resp && resp.message ? resp.message : 'sem resposta do servidor'));
            }
        },
        error: function(xhr, status, err) {
            console.error('[RAA] erro ajax', status, err, xhr.responseText);
            alert('Erro de comunicacao: ' + status + ' - ' + err + '\n\n' + (xhr.responseText ? xhr.responseText.substring(0, 300) : ''));
        },
        complete: function() { if (typeof stopLoading === 'function') stopLoading(); }
    });
}

function escapeHtmlRAA(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
</script>

<style>
#raa-container { background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:20px; margin-top:10px; }
#raa-filtros { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; padding:12px; background:#fafafa; border:1px solid #e0e0e0; border-radius:8px; margin-bottom:16px; }
#raa-filtros .filtro-item { display:flex; flex-direction:column; }
#raa-filtros label { font-size:12px; font-weight:600; color:#555; margin-bottom:4px; }
#raa-filtros select { min-width:140px; }
#tabelaRAA { width:100%; background:#fff; border-collapse:collapse; }
#tabelaRAA tr.ativ-row { border-bottom:1px solid #eee; }
#tabelaRAA tr.ativ-row td { padding:8px 10px; vertical-align:top; }
#tabelaRAA tr.status-aguardando td { background-color:#fffbe6; }
#tabelaRAA tr.status-validado td { background-color:#f0faf0; }
#tabelaRAA tr.status-nao-validado td { background-color:#fdf0f0; }
#tabelaRAA tr.ativ-row:hover td { background-color:#eef6fb; cursor:pointer; }
</style>

<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div id="raa-container">
    <div id="raa-filtros">
        <div class="filtro-item">
            <label for="filtroGerente">Gerente:</label>
            <select id="filtroGerente" class="form-control">
                <option value="">Todos os gerentes</option>
            </select>
        </div>
        <div class="filtro-item">
            <label for="filtroStatus">Status:</label>
            <select id="filtroStatus" class="form-control">
                <option value="">Todos</option>
                <option value="AGUARDANDO">Aguardando Valida&ccedil;&atilde;o</option>
                <option value="Validado">Validado</option>
                <option value="Não Validado">N&atilde;o Validado</option>
            </select>
        </div>
        <div class="filtro-item">
            <label for="filtroMes">M&ecirc;s:</label>
            <select id="filtroMes" class="form-control">
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
        <div class="filtro-item">
            <label for="filtroAno">Ano:</label>
            <select id="filtroAno" class="form-control">
                <option value="">Todos</option>
                <?php
                $anoAtual = intval(date('Y'));
                for ($a = $anoAtual + 1; $a >= $anoAtual - 3; $a--) {
                    echo "<option value=\"{$a}\">{$a}</option>";
                }
                ?>
            </select>
        </div>
        <div class="filtro-item">
            <label>&nbsp;</label>
            <button type="button" id="btnAtualizarLista" class="btn btn-primary btn-sm"><i class="fa fa-refresh"></i> Atualizar</button>
        </div>
    </div>

    <table id="tabelaRAA" class="tbl-fin">
        <tbody></tbody>
    </table>

    <div style="background:#f7f9fb; border:1px solid #e0e6ec; border-radius:8px; padding:12px 15px; margin-top:20px;">
        <div style="font-size:13px; font-weight:600; color:#444; margin-bottom:8px;">
            <i class="fa fa-file-excel-o"></i> Exportar relatorio em planilha (Excel)
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">
            <div>
                <label for="exportGerente" style="font-size:12px; color:#666; display:block; margin-bottom:3px;">Gerente</label>
                <select id="exportGerente" class="form-control" style="width:240px;">
                    <option value="">Todos os gerentes</option>
                </select>
            </div>
            <div>
                <label for="exportDataInicio" style="font-size:12px; color:#666; display:block; margin-bottom:3px;">Data inicial</label>
                <input type="date" id="exportDataInicio" class="form-control" style="width:160px;"
                    value="<?php echo date('Y-m-01') ?>">
            </div>
            <div>
                <label for="exportDataFim" style="font-size:12px; color:#666; display:block; margin-bottom:3px;">Data final</label>
                <input type="date" id="exportDataFim" class="form-control" style="width:160px;"
                    value="<?php echo date('Y-m-d') ?>">
            </div>
            <div>
                <button type="button" id="btnExportarPlanilhaRAA" class="btn abriModal" style="height:34px;">
                    <i class="fa fa-download"></i> Exportar planilha
                </button>
            </div>
        </div>
        <div style="font-size:11px; color:#888; margin-top:8px;">
            A planilha trara: data, dia da semana, gerente, horas, descricao, status da avaliacao e quem avaliou. Selecione um gerente especifico ou deixe como "Todos os gerentes".
        </div>
    </div>
</div>
<div style="height:60px;"></div>

<!-- Modal Avaliacao -->
<div class="modal fade" id="modalAvaliacaoRAA" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title"><i class="fa fa-check-square-o"></i> Avaliar Registro de Atividade</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="aCodRegAtividadeRAA" value="">
        <div class="form-group">
          <label>Colaborador:</label>
          <input type="text" id="aNomeInscritoRAA" class="form-control" readonly>
        </div>
        <div style="display:flex; gap:10px;">
          <div class="form-group" style="flex:1;">
            <label>Data:</label>
            <input type="text" id="aDiaRAA" class="form-control" readonly>
          </div>
          <div class="form-group" style="flex:1;">
            <label>Dia da semana:</label>
            <input type="text" id="aDiaSemanaRAA" class="form-control" readonly>
          </div>
          <div class="form-group" style="flex:1;">
            <label>Horas trabalhadas:</label>
            <input type="text" id="aHorasRAA" class="form-control" readonly>
          </div>
        </div>
        <div class="form-group">
          <label>Descri&ccedil;&atilde;o das atividades:</label>
          <textarea id="aDescricaoRAA" class="form-control" rows="4" readonly></textarea>
        </div>
        <div class="form-group">
          <label>Justificativa do colaborador:</label>
          <textarea id="aJustificativaRAA" class="form-control" rows="2" readonly></textarea>
        </div>
        <div class="form-group">
          <label>Anexos do colaborador:</label>
          <div id="aArquivosRAA"><div style="font-size:12px; color:#888; font-style:italic;">Carregando...</div></div>
        </div>
        <hr>
        <div class="form-group">
          <label for="aStatusRAA">Decis&atilde;o do Avaliador: <span style="color:#a94442;">*</span></label>
          <select id="aStatusRAA" class="form-control" required>
            <option value=""></option>
            <option value="Validado">Validado</option>
            <option value="Não Validado">N&atilde;o Validado</option>
          </select>
        </div>
        <div class="form-group">
          <label id="labelParecerRAA" for="aParecerRAA">Parecer do Avaliador (opcional):</label>
          <textarea id="aParecerRAA" class="form-control" rows="3" placeholder="Observa&ccedil;&otilde;es / justificativa da decis&atilde;o"></textarea>
        </div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarAvaliacaoRAA"><i class="fa fa-check"></i> Salvar Decis&atilde;o</button>
      </div>
    </div>
  </div>
</div>
