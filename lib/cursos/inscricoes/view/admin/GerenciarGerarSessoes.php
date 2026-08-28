<?php
require_once(dirname(__FILE__)."/../../view/admin/oficina-cabecalho.php");
?>
<style>
.gs-section { background:#fff; border:1px solid #e0e0e0; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.gs-section-header { padding:8px 14px; background:#f5f6f8; border-bottom:1px solid #e0e0e0; font-weight:600; font-size:13px; color:#495057; }
.gs-section-body { padding:12px 14px; }
.gs-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:10px; }
.gs-field { flex:1; min-width:0; }
.gs-field label { display:block; font-size:12px; color:#666; margin-bottom:2px; font-weight:500; }
.gs-field input, .gs-field select, .gs-field textarea {
    width:100%; padding:6px 8px; border:1px solid #ccc; border-radius:4px;
    font-size:13px; color:#333; background:#fff; box-sizing:border-box;
}
.gs-field input:focus, .gs-field select:focus, .gs-field textarea:focus {
    border-color:#2D937E; outline:none; box-shadow:0 0 0 2px rgba(45,147,126,0.15);
}
.gs-dia-row { display:flex; align-items:center; gap:8px; padding:6px 0; border-bottom:1px solid #f0f0f0; }
.gs-dia-row:last-child { border-bottom:none; }
.gs-dia-nome { width:120px; font-weight:600; font-size:13px; color:#333; }
.gs-turno-group { display:flex; gap:16px; flex:1; flex-wrap:wrap; }
.gs-turno-item { display:flex; align-items:center; gap:4px; }
.gs-turno-item label { font-size:12px; color:#555; margin:0; cursor:pointer; white-space:nowrap; }
.gs-turno-item input[type="checkbox"] { accent-color:#2D937E; cursor:pointer; }
.gs-turno-item input[type="time"] { width:75px; padding:2px 4px; font-size:12px; border:1px solid #ccc; border-radius:3px; }
.gs-preview-table { width:100%; border-collapse:collapse; font-size:12px; margin-top:8px; }
.gs-preview-table th { background:#f5f6f8; padding:6px 8px; text-align:left; border:1px solid #e0e0e0; font-size:11px; color:#666; }
.gs-preview-table td { padding:5px 8px; border:1px solid #e8e8e8; }
.gs-preview-table tr:nth-child(even) td { background:#fafafa; }
.gs-btn { padding:8px 16px; border:none; border-radius:4px; font-size:13px; cursor:pointer; font-weight:500; }
.gs-btn-primary { background:#2D937E; color:#fff; }
.gs-btn-primary:hover { background:#247a68; }
.gs-btn-danger { background:#d9534f; color:#fff; }
.gs-btn-danger:hover { background:#c9302c; }
.gs-btn-default { background:#e0e0e0; color:#333; }
.gs-btn-default:hover { background:#d0d0d0; }
.gs-counter { display:inline-block; background:#2D937E; color:#fff; padding:2px 10px; border-radius:10px; font-size:12px; font-weight:600; }
</style>

        <h4>Gerador de Sess&otilde;es / Agenda</h4>
        <a href="index.php?content=view/admin/oficina-cadastro-ok.php" class="btn btn-default btn-xs" style="margin-bottom:10px;">&larr; Voltar para Atividades</a>

        <!-- PERÍODO -->
        <div class="gs-section">
            <div class="gs-section-header">Per&iacute;odo</div>
            <div class="gs-section-body">
                <div class="gs-row">
                    <div class="gs-field" style="flex:0 0 180px;">
                        <label>Data In&iacute;cio</label>
                        <input type="date" id="gsDataIni">
                    </div>
                    <div class="gs-field" style="flex:0 0 180px;">
                        <label>Data Fim</label>
                        <input type="date" id="gsDataFim">
                    </div>
                    <div class="gs-field" style="flex:0 0 100px;">
                        <label>Vagas/sess&atilde;o</label>
                        <input type="number" id="gsVagas" value="1" min="1">
                    </div>
                    <div class="gs-field" style="flex:0 0 140px;">
                        <label>Dias antec. inscr.</label>
                        <input type="number" id="gsDiasAntecedencia" value="5" min="0">
                    </div>
                    <div class="gs-field" style="flex:0 0 140px;">
                        <label>Tipo</label>
                        <select id="gsTipo">
                            <option value="Agenda">Agenda</option>
                            <option value="Minicurso">Minicurso</option>
                            <option value="Oficina">Oficina</option>
                            <option value="Palestra">Palestra</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- TURNOS POR DIA -->
        <div class="gs-section">
            <div class="gs-section-header">Turnos por Dia da Semana</div>
            <div class="gs-section-body">
                <?php
                $dias = [1=>'Segunda-feira', 2=>'Ter&ccedil;a-feira', 3=>'Quarta-feira', 4=>'Quinta-feira', 5=>'Sexta-feira', 6=>'S&aacute;bado', 0=>'Domingo'];
                foreach ($dias as $d => $nome) { ?>
                <div class="gs-dia-row">
                    <div class="gs-dia-nome"><?= $nome ?></div>
                    <div class="gs-turno-group">
                        <div class="gs-turno-item">
                            <input type="checkbox" id="dia_<?= $d ?>_manha">
                            <label for="dia_<?= $d ?>_manha">Manh&atilde;</label>
                            <input type="time" id="dia_<?= $d ?>_manha_ini" value="10:00">
                            <span style="color:#999;">-</span>
                            <input type="time" id="dia_<?= $d ?>_manha_fim" value="12:00">
                        </div>
                        <div class="gs-turno-item">
                            <input type="checkbox" id="dia_<?= $d ?>_tarde">
                            <label for="dia_<?= $d ?>_tarde">Tarde</label>
                            <input type="time" id="dia_<?= $d ?>_tarde_ini" value="14:00">
                            <span style="color:#999;">-</span>
                            <input type="time" id="dia_<?= $d ?>_tarde_fim" value="16:00">
                        </div>
                        <div class="gs-turno-item">
                            <input type="checkbox" id="dia_<?= $d ?>_noite">
                            <label for="dia_<?= $d ?>_noite">Noite</label>
                            <input type="time" id="dia_<?= $d ?>_noite_ini" value="19:00">
                            <span style="color:#999;">-</span>
                            <input type="time" id="dia_<?= $d ?>_noite_fim" value="21:00">
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- BLOQUEIOS -->
        <div class="gs-section">
            <div class="gs-section-header">Feriados / Bloqueios</div>
            <div class="gs-section-body">
                <div class="gs-field">
                    <label>Datas bloqueadas (uma por linha, formato DD/MM/AAAA ou AAAA-MM-DD)</label>
                    <textarea id="gsBloqueios" rows="4" placeholder="03/04/2026&#10;21/04/2026&#10;01/05/2026"></textarea>
                </div>
            </div>
        </div>

        <!-- AÇÕES -->
        <div style="display:flex; gap:8px; margin-bottom:14px; align-items:center; flex-wrap:wrap;">
            <button class="gs-btn gs-btn-primary" onclick="gerarPreview()">Gerar Preview</button>
            <button class="gs-btn gs-btn-primary" onclick="importarSessoes()" id="btnImportar" disabled>Importar Sess&otilde;es</button>
            <button class="gs-btn gs-btn-danger" onclick="excluirTodas()" style="margin-left:auto;">Excluir Todas as Sess&otilde;es Ativas</button>
        </div>

        <!-- PREVIEW -->
        <div class="gs-section" id="gsPreviewArea" style="display:none;">
            <div class="gs-section-header">
                Preview <span class="gs-counter" id="gsPreviewTotal">0</span>
            </div>
            <div class="gs-section-body" id="gsPreviewBody" style="max-height:500px; overflow-y:auto;">
            </div>
        </div>

    </div>
</div>
</div>

<!-- Modal confirmação desativar -->
<div class="modal fade" id="modalDesativar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                <h4 class="modal-title">Excluir Todas as Sess&otilde;es</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    Todas as sess&otilde;es <b>ativas</b> deste evento ser&atilde;o desativadas. Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita facilmente.
                </div>
                <p>Deseja continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExcluir()">Excluir Todas</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var sessoesGeradas = [];

function coletarDados() {
    var data = {
        'cod_evento': '<?= COD_EVENTO ?>',
        'data_ini': $('#gsDataIni').val(),
        'data_fim': $('#gsDataFim').val(),
        'vagas': $('#gsVagas').val(),
        'dias_antecedencia': $('#gsDiasAntecedencia').val(),
        'tipo': $('#gsTipo').val(),
        'bloqueios': $('#gsBloqueios').val()
    };
    // Turnos por dia
    for (var d = 0; d <= 6; d++) {
        ['manha','tarde','noite'].forEach(function(t) {
            data['dia_'+d+'_'+t] = $('#dia_'+d+'_'+t).is(':checked') ? '1' : '0';
            data['dia_'+d+'_'+t+'_ini'] = $('#dia_'+d+'_'+t+'_ini').val();
            data['dia_'+d+'_'+t+'_fim'] = $('#dia_'+d+'_'+t+'_fim').val();
        });
    }
    return data;
}

function gerarPreview() {
    var data = coletarDados();
    if (!data.data_ini || !data.data_fim) {
        alert('Informe o período.');
        return;
    }
    data['c'] = 'GerenciarGerarSessoes';
    data['m'] = 'gerarPreview';
    data['_submit_check'] = '1';

    $.ajax({
        type: 'POST',
        url: '<?= WB_URL_COMMAND ?>&t=' + Math.random(),
        data: data,
        dataType: 'json',
        success: function(resp) {
            if (resp.statusMessage == '1') {
                sessoesGeradas = resp.sessoes;
                renderPreview(resp.sessoes);
                $('#btnImportar').prop('disabled', false);
            } else {
                alert(resp.message);
            }
        },
        error: function() { alert('Erro ao gerar preview.'); }
    });
}

function renderPreview(sessoes) {
    var html = '<table class="gs-preview-table"><thead><tr>'
        + '<th>#</th><th>Data</th><th>Dia</th><th>Turno</th><th>Hor&aacute;rio</th><th>T&iacute;tulo</th>'
        + '</tr></thead><tbody>';
    for (var i = 0; i < sessoes.length; i++) {
        var s = sessoes[i];
        html += '<tr>'
            + '<td>' + (i+1) + '</td>'
            + '<td>' + s.data_formatada + '</td>'
            + '<td>' + s.dia_semana + '</td>'
            + '<td>' + s.turno + '</td>'
            + '<td>' + s.horario_ini + ' - ' + s.horario_fim + '</td>'
            + '<td>' + s.titulo + '</td>'
            + '</tr>';
    }
    html += '</tbody></table>';
    $('#gsPreviewBody').html(html);
    $('#gsPreviewTotal').text(sessoes.length);
    $('#gsPreviewArea').show();
}

function importarSessoes() {
    if (sessoesGeradas.length === 0) {
        alert('Gere o preview primeiro.');
        return;
    }
    if (!confirm('Importar ' + sessoesGeradas.length + ' sessões para o evento <?= COD_EVENTO ?>?')) {
        return;
    }

    var data = coletarDados();
    data['c'] = 'GerenciarGerarSessoes';
    data['m'] = 'importarSessoes';
    data['_submit_check'] = '1';

    $('#btnImportar').prop('disabled', true).text('Importando...');

    $.ajax({
        type: 'POST',
        url: '<?= WB_URL_COMMAND ?>&t=' + Math.random(),
        data: data,
        dataType: 'json',
        success: function(resp) {
            $('#btnImportar').prop('disabled', false).text('Importar Sessões');
            if (resp.statusMessage == '1') {
                alert(resp.message);
                sessoesGeradas = [];
                $('#gsPreviewArea').hide();
                $('#btnImportar').prop('disabled', true);
            } else {
                alert('Erro: ' + resp.message);
            }
        },
        error: function() {
            $('#btnImportar').prop('disabled', false).text('Importar Sessões');
            alert('Erro ao importar.');
        }
    });
}

function excluirTodas() {
    $('#modalDesativar').modal('show');
}

function confirmarExcluir() {
    $.ajax({
        type: 'POST',
        url: '<?= WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGerarSessoes',
            'm': 'excluirTodas',
            'cod_evento': '<?= COD_EVENTO ?>',
            '_submit_check': '1'
        },
        dataType: 'json',
        success: function(resp) {
            $('#modalDesativar').modal('hide');
            if (resp.statusMessage == '1') {
                alert(resp.message);
            } else {
                alert('Erro: ' + resp.message);
            }
        },
        error: function() {
            $('#modalDesativar').modal('hide');
            alert('Erro ao desativar.');
        }
    });
}
</script>
