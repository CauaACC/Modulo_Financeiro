<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
?>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
?>

<style>
.rp-presenca-filtro { margin-bottom: 15px; }
.rp-presenca-resumo { display:flex; gap:12px; margin-bottom:15px; flex-wrap:wrap; }
.rp-presenca-resumo .rp-card {
    flex:1; min-width:120px; padding:12px; border-radius:8px; text-align:center;
    font-weight:600; font-size:20px;
}
.rp-presenca-resumo .rp-card small { display:block; font-size:12px; font-weight:400; margin-top:2px; }
.rp-card-total { background:#E3F2FD; color:#1565C0; }
.rp-card-presente { background:#E8F5E9; color:#2E7D32; }
.rp-card-falta { background:#FFEBEE; color:#C62828; }
.rp-card-pendente { background:#FFF3E0; color:#E65100; }

.rp-presenca-tabela { width:100%; border-collapse:collapse; font-size:13px; }
.rp-presenca-tabela th { background:#f5f5f5; padding:8px 10px; text-align:left; border-bottom:2px solid #ddd; font-size:12px; }
.rp-presenca-tabela td { padding:8px 10px; border-bottom:1px solid #eee; }
.rp-presenca-tabela tr:hover { background:#f9f9f9; }
.rp-presenca-tabela .badge-presente { background:#4CAF50; color:#fff; padding:3px 8px; border-radius:10px; font-size:11px; }
.rp-presenca-tabela .badge-falta { background:#e53935; color:#fff; padding:3px 8px; border-radius:10px; font-size:11px; }
.rp-presenca-tabela .badge-pendente { background:#FF9800; color:#fff; padding:3px 8px; border-radius:10px; font-size:11px; }
.rp-presenca-tabela .badge-entrada { background:#2196F3; color:#fff; padding:3px 8px; border-radius:10px; font-size:11px; }
.rp-presenca-tabela .badge-completo { background:#4CAF50; color:#fff; padding:3px 8px; border-radius:10px; font-size:11px; }

.rp-grupo-colegio { background:#E3F2FD; font-weight:600; }
.rp-grupo-colegio td { padding:6px 10px !important; color:#1565C0; font-size:13px; }

.rp-filtro-status { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px; }
.rp-filtro-status button {
    padding:5px 12px; border-radius:16px; font-size:12px; font-weight:500;
    cursor:pointer; border:1px solid #ddd; background:#fff; transition:background 0.15s;
}
.rp-filtro-status button.ativo { background:#2196F3; color:#fff; border-color:#2196F3; }
</style>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><i class="fa fa-calendar-check-o"></i> Presen&ccedil;a do Dia</h4>
        </div>
        <div class="panel-body">

            <div class="rp-presenca-filtro">
                <div class="row">
                    <div class="col-sm-3">
                        <label for="rpDataPresenca"><b>Data:</b></label>
                        <input type="date" id="rpDataPresenca" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-sm-4">
                        <label for="rpFiltroColegio"><b>Col&eacute;gio:</b></label>
                        <select id="rpFiltroColegio" class="form-control">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-sm-3" style="padding-top:24px;">
                        <button type="button" class="btn btn-primary" id="btnCarregarPresenca">
                            <i class="fa fa-search"></i> Consultar
                        </button>
                    </div>
                </div>
            </div>

            <div id="rpPresencaResumo" style="display:none;">
                <div class="rp-presenca-resumo">
                    <div class="rp-card rp-card-total"><span id="rpTotal">0</span><small>Total</small></div>
                    <div class="rp-card rp-card-presente"><span id="rpPresentes">0</span><small>Presentes</small></div>
                    <div class="rp-card rp-card-falta"><span id="rpFaltas">0</span><small>Faltas</small></div>
                    <div class="rp-card rp-card-pendente"><span id="rpPendentes">0</span><small>Sem registro</small></div>
                </div>

                <div class="rp-filtro-status">
                    <button type="button" class="rp-filtro-btn ativo" data-filtro="todos">Todos</button>
                    <button type="button" class="rp-filtro-btn" data-filtro="PRESENTE">Presentes</button>
                    <button type="button" class="rp-filtro-btn" data-filtro="FALTA">Faltas</button>
                    <button type="button" class="rp-filtro-btn" data-filtro="PENDENTE">Sem registro</button>
                </div>

                <div class="table-responsive">
                    <table class="rp-presenca-tabela">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Turma</th>
                                <th>Turno</th>
                                <th>PES</th>
                                <th>Presen&ccedil;a</th>
                                <th>Registro</th>
                            </tr>
                        </thead>
                        <tbody id="rpPresencaBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="rpPresencaVazio" style="display:none; text-align:center; padding:20px; color:#999;">
                <i class="fa fa-info-circle"></i> Nenhum aluno encontrado para esta data.
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#btnCarregarPresenca').click(function() {
        carregarPresencaDia();
    });

    // Filtro por colegio
    $('#rpFiltroColegio').on('change', function() {
        aplicarFiltros();
    });

    // Filtro por status
    $(document).on('click', '.rp-filtro-btn', function() {
        $('.rp-filtro-btn').removeClass('ativo');
        $(this).addClass('ativo');
        aplicarFiltros();
    });

    // Carregar automaticamente
    carregarPresencaDia();
});

function carregarPresencaDia() {
    var data = $('#rpDataPresenca').val();
    if (!data) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarRegistroPedagogico',
            'm': 'carregarPresencaDia',
            'data': data,
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        success: function(resp) {
            if (!resp.statusMessage) {
                $('#rpPresencaResumo').hide();
                $('#rpPresencaVazio').show().html('<i class="fa fa-exclamation-triangle"></i> ' + (resp.message || 'Erro ao carregar.'));
                return;
            }

            if (resp.totalAlunos === 0) {
                $('#rpPresencaResumo').hide();
                $('#rpPresencaVazio').show();
                return;
            }

            $('#rpPresencaVazio').hide();
            $('#rpTotal').text(resp.totalAlunos);
            $('#rpPresentes').text(resp.presentes);
            $('#rpFaltas').text(resp.faltas);
            $('#rpPendentes').text(resp.semRegistro);
            $('#rpPresencaResumo').show();

            // Montar tabela agrupada por colegio
            var html = '';
            var colegioAtual = null;

            for (var i = 0; i < resp.registros.length; i++) {
                var r = resp.registros[i];
                var colegio = r.nomeColegio || 'Sem col\u00e9gio';

                if (colegio !== colegioAtual) {
                    colegioAtual = colegio;
                    html += '<tr class="rp-grupo-colegio"><td colspan="6"><i class="fa fa-building"></i> ' + colegio + '</td></tr>';
                }

                var badgePresenca = '';
                var statusFiltro = '';
                if (r.presenca === 'PRESENTE') {
                    badgePresenca = '<span class="badge-presente">Presente</span>';
                    statusFiltro = 'PRESENTE';
                } else if (r.presenca === 'FALTA' || r.presenca === 'FALTA_JUSTIFICADA') {
                    badgePresenca = '<span class="badge-falta">Faltou</span>';
                    statusFiltro = 'FALTA';
                } else {
                    badgePresenca = '<span class="badge-pendente">Sem registro</span>';
                    statusFiltro = 'PENDENTE';
                }

                var badgeRegistro = '';
                if (r.presenca === 'PRESENTE') {
                    if (r.condicaoInicio && r.condicaoFinal) {
                        badgeRegistro = '<span class="badge-completo">Completo</span>';
                    } else if (r.condicaoInicio) {
                        badgeRegistro = '<span class="badge-entrada">Entrada OK</span>';
                    } else {
                        badgeRegistro = '<span class="badge-pendente">Pendente</span>';
                    }
                } else if (r.presenca === 'FALTA' || r.presenca === 'FALTA_JUSTIFICADA') {
                    badgeRegistro = '-';
                } else {
                    badgeRegistro = '-';
                }

                html += '<tr data-status="' + statusFiltro + '" data-colegio="' + colegio + '">';
                html += '<td>' + r.codigoExibicao + '</td>';
                html += '<td>' + (r.serieTurma || '-') + '</td>';
                html += '<td>' + (r.turno || '-') + '</td>';
                html += '<td>' + (r.nomeAgente || '-') + '</td>';
                html += '<td>' + badgePresenca + '</td>';
                html += '<td>' + badgeRegistro + '</td>';
                html += '</tr>';
            }

            $('#rpPresencaBody').html(html);

            // Popular select de colegios
            var colegios = [];
            $('#rpPresencaBody tr.rp-grupo-colegio').each(function() {
                var nome = $(this).text().trim();
                if (colegios.indexOf(nome) === -1) colegios.push(nome);
            });
            var selColegio = $('#rpFiltroColegio');
            var valAtual = selColegio.val();
            selColegio.find('option:not(:first)').remove();
            for (var c = 0; c < colegios.length; c++) {
                selColegio.append('<option value="' + colegios[c] + '">' + colegios[c] + '</option>');
            }
            selColegio.val(valAtual);

            // Resetar filtro status
            $('.rp-filtro-btn').removeClass('ativo');
            $('.rp-filtro-btn[data-filtro="todos"]').addClass('ativo');
        }
    });
}

function aplicarFiltros() {
    var filtroStatus = $('.rp-filtro-btn.ativo').data('filtro') || 'todos';
    var filtroColegio = $('#rpFiltroColegio').val() || '';

    $('#rpPresencaBody tr').each(function() {
        if ($(this).hasClass('rp-grupo-colegio')) {
            $(this).show();
            return;
        }
        var status = $(this).data('status') || '';
        var colegio = $(this).data('colegio') || '';

        var okStatus = (filtroStatus === 'todos' || status === filtroStatus);
        var okColegio = (!filtroColegio || colegio === filtroColegio);

        $(this).toggle(okStatus && okColegio);
    });

    // Ocultar grupos sem filhos visiveis
    $('#rpPresencaBody tr.rp-grupo-colegio').each(function() {
        var nextRows = $(this).nextUntil('.rp-grupo-colegio');
        var temVisivel = nextRows.filter(':visible').length > 0;
        $(this).toggle(temVisivel);
    });

    // Atualizar contadores baseado nos visiveis
    var presentes = 0, faltas = 0, pendentes = 0;
    $('#rpPresencaBody tr:not(.rp-grupo-colegio)').each(function() {
        if (!$(this).is(':visible') && filtroColegio) {
            // Nao conta se filtrado por colegio e esta oculto
        } else if (filtroColegio && !$(this).is(':visible')) {
            // skip
        } else {
            var colegio = $(this).data('colegio') || '';
            var okColegio = (!filtroColegio || colegio === filtroColegio);
            if (okColegio) {
                var s = $(this).data('status');
                if (s === 'PRESENTE') presentes++;
                else if (s === 'FALTA') faltas++;
                else pendentes++;
            }
        }
    });
    if (filtroColegio) {
        var total = presentes + faltas + pendentes;
        $('#rpTotal').text(total);
        $('#rpPresentes').text(presentes);
        $('#rpFaltas').text(faltas);
        $('#rpPendentes').text(pendentes);
    }
}
</script>
