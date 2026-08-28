<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarVinculoAgenteAC();
$temPermissaoLogarComo = FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_LOGAR_COMO, 5, false, false);
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>

<style>
.agente-lista { border: 1px solid #ddd; border-radius: 4px; }
.agente-item { border-bottom: 1px solid #eee; }
.agente-item:last-child { border-bottom: none; }
.agente-header { display: flex; align-items: center; padding: 7px 12px; cursor: pointer; }
.agente-header:hover { background-color: #f5f5f5; }
.agente-icon { width: 16px; text-align: center; color: #aaa; font-size: 10px; transition: transform 0.2s; display: inline-block; }
.agente-icon.open { transform: rotate(90deg); }
.agente-nome { flex: 1; font-size: 13px; color: #333; padding-left: 6px; }
.agente-qtd { width: 80px; text-align: right; font-size: 12px; color: #888; padding-right: 10px; }
.agente-qtd-zero { color: #ccc; }
.agente-acoes { width: 220px; text-align: right; white-space: nowrap; }
.btn-logar-como { font-weight: bold; }
.agente-detalhe { background: #fafafa; }
.agente-detalhe table { font-size: 12px; }
.filtro-bar { margin-bottom: 15px; }
</style>

<script type="text/javascript">
var agentesData = [];
var alunosCarregados = {};
var debounceTimer = null;
var permiteLogarComo = <?php echo $temPermissaoLogarComo ? 'true' : 'false'; ?>;
var codEventoAtual = '<?php echo COD_EVENTO; ?>';
var urlLogarComo = 'index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo';

$(document).ready(function() {
    carregarListaColegiosVinculo();
    carregarAgentesCards();

    $('#filtroNome').on('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(filtrarAgentes, 300);
    });

    $('#filtroColegio').on('change', function() {
        filtrarAgentesPorColegio();
    });

    $('#filtroStatus').on('change', function() {
        aplicarFiltros();
    });

    $('#btnConfirmarAssociar').on('click', function() {
        associarAlunoExistente();
    });

    $('#btnCadastrarEAssociar').on('click', function() {
        cadastrarEAssociarAluno();
    });
});

function carregarListaColegiosVinculo() {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarColegio', 'm': 'carregarListaColegios', '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            var selects = $('#editColegio, #novoColegio, #filtroColegio');
            selects.find('option:not(:first)').remove();
            if (data.colegios) {
                $.each(data.colegios, function(i, col) {
                    selects.append('<option value="' + col.codColegio + '">' + col.nome + '</option>');
                });
            }
        }
    });
}

function carregarAgentesCards() {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoAgente', 'm': 'carregarListaAgentes', '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { $('#listaAgentes').html('<div class="text-center" style="padding:20px;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>'); },
        success: function(data) {
            if (data.statusMessage && data.agentes) {
                agentesData = data.agentes;
                renderizarCards(agentesData);
                aplicarFiltros(null);
            } else {
                $('#listaAgentes').html('<div class="alert alert-warning">Nenhum agente encontrado.</div>');
            }
        },
        error: function() {
            $('#listaAgentes').html('<div class="alert alert-danger">Erro ao carregar agentes.</div>');
        }
    });
}

function titleCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
}

function renderizarCards(agentes) {
    var container = $('#listaAgentes');
    container.empty();
    $('#contadorAgentes').text(agentes.length + ' agente(s)');

    if (agentes.length === 0) {
        container.html('<div class="alert alert-info">Nenhum agente encontrado com os filtros aplicados.</div>');
        return;
    }

    var html = '<div class="agente-lista">';
    $.each(agentes, function(i, ag) {
        var nomeFormatado = titleCase(ag.nome);
        var qtdTexto = ag.totalAlunos > 0 ? ag.totalAlunos + ' aluno(s)' : '0 alunos';
        var qtdClass = ag.totalAlunos > 0 ? 'agente-qtd' : 'agente-qtd agente-qtd-zero';

        var btnLogar = '';
        if (permiteLogarComo) {
            if (ag.cpf) {
                btnLogar = '<a class="btn btn-xs btn-info btn-logar-como" onclick="event.stopPropagation();" href="' + urlLogarComo + '&cpf=' + encodeURIComponent(ag.cpf) + '&cod_inscrito=' + ag.codInscrito + '&cod_evento=' + codEventoAtual + '" target="_blank" title="Logar como este agente">'
                    + '<i class="fa fa-sign-in"></i> Logar como'
                    + '</a> ';
            } else {
                btnLogar = '<button class="btn btn-xs btn-default btn-logar-como" onclick="event.stopPropagation();" disabled title="CPF n&atilde;o cadastrado para este agente">'
                    + '<i class="fa fa-sign-in"></i> Logar como'
                    + '</button> ';
            }
        }

        var isInativo = (ag.ativo === 0 || ag.ativo === '0');
        var estiloCard = isInativo ? ' style="opacity:0.6; border-left:3px solid #dc3545;"' : '';
        var badgeStatus = isInativo ? ' <span style="font-size:10px; color:#dc3545; font-weight:600;">(' + (ag.statusProcesso || 'INATIVO') + ')</span>' : '';

        html += '<div class="agente-item card-agente" data-cod-agente="' + ag.codInscrito + '" data-nome="' + escapeHtml(ag.nome).toLowerCase() + '" data-status="' + (ag.statusProcesso || '') + '" data-ativo="' + (ag.ativo !== undefined ? ag.ativo : 1) + '"' + estiloCard + '>'
            + '<div class="agente-header" onclick="toggleAgente(\'' + ag.codInscrito + '\')">'
            + '  <span class="agente-icon" id="chevron-' + ag.codInscrito + '"><i class="fa fa-angle-right"></i></span>'
            + '  <span class="agente-nome">' + escapeHtml(nomeFormatado) + badgeStatus + '</span>'
            + '  <span class="' + qtdClass + '">' + qtdTexto + '</span>'
            + '  <span class="agente-acoes">'
            + '    ' + btnLogar
            + '    <button class="btn btn-xs btn-acao-editar" onclick="event.stopPropagation(); abrirModalAssociar(\'' + ag.codInscrito + '\', \'' + escapeAttr(nomeFormatado) + '\')" title="Associar aluno">'
            + '      <i class="fa fa-plus"></i> Associar Aluno'
            + '    </button>'
            + '  </span>'
            + '</div>'
            + '<div class="panel-collapse collapse" id="collapse-' + ag.codInscrito + '">'
            + '  <div class="agente-detalhe">'
            + '    <div class="table-responsive">'
            + '      <table class="table table-bordered table-striped table-hover" style="margin-bottom:0;">'
            + '        <thead><tr><th>C&oacute;digo</th><th>Col&eacute;gio</th><th>N&iacute;vel</th><th>Turno</th><th>A&ccedil;&otilde;es</th></tr></thead>'
            + '        <tbody id="alunos-' + ag.codInscrito + '">'
            + '          <tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando...</td></tr>'
            + '        </tbody>'
            + '      </table>'
            + '    </div>'
            + '  </div>'
            + '</div>'
            + '</div>';
    });
    html += '</div>';
    container.html(html);
}

function toggleAgente(codAgente) {
    var collapseEl = $('#collapse-' + codAgente);
    var chevron = $('#chevron-' + codAgente);

    if (collapseEl.hasClass('in')) {
        collapseEl.collapse('hide');
        chevron.removeClass('open');
    } else {
        collapseEl.collapse('show');
        chevron.addClass('open');
        if (!alunosCarregados[codAgente]) {
            carregarAlunosDoAgente(codAgente);
        }
    }
}

function carregarAlunosDoAgente(codAgente) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarVinculoAgente', 'm': 'carregarAlunosVinculados', 'codAgente': codAgente, '_submit_check': '1'},
        function(html) {
            $('#alunos-' + codAgente).html(html);
            alunosCarregados[codAgente] = true;
            bindAcoesAlunos(codAgente);
        }
    );
}

function bindAcoesAlunos(codAgente) {
    var tbody = $('#alunos-' + codAgente);
    tbody.find('.btn-editar-vinculo').off('click').on('click', function() {
        var codAluno = $(this).parents("tr").data("cod-aluno");
        editarAlunoVinculo(codAluno, codAgente);
    });
    tbody.find('.btn-desassociar').off('click').on('click', function() {
        var codVinculo = $(this).parents("tr").attr("id");
        desassociarAlunoInline(codVinculo, codAgente);
    });
}

function aplicarFiltros(colegioAgentes) {
    var termo = $('#filtroNome').val().toLowerCase().trim();
    var status = $('#filtroStatus').val();
    var visiveis = 0;
    $('.card-agente').each(function() {
        var nome = $(this).data('nome');
        var st = $(this).data('status');
        var cod = $(this).data('cod-agente').toString();
        var matchNome = !termo || nome.indexOf(termo) !== -1;
        var atvData = $(this).data('ativo');
        var matchStatus;
        if (status === 'INATIVOS') {
            matchStatus = (atvData == 0);
        } else if (status === 'ATIVOS') {
            matchStatus = (atvData != 0);
        } else if (!status) {
            matchStatus = true;
        } else {
            matchStatus = (st === status);
        }
        var matchColegio = !colegioAgentes || colegioAgentes.indexOf(cod) !== -1;
        if (matchNome && matchStatus && matchColegio) {
            $(this).show();
            visiveis++;
        } else {
            $(this).hide();
        }
    });
    $('#contadorAgentes').text(visiveis + ' agente(s)');
}

function filtrarAgentes() {
    var codColegio = $('#filtroColegio').val();
    if (codColegio) {
        filtrarAgentesPorColegio();
    } else {
        aplicarFiltros(null);
    }
}

function filtrarAgentesPorColegio() {
    var codColegio = $('#filtroColegio').val();
    if (!codColegio) {
        aplicarFiltros(null);
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoAgente', 'm': 'filtrarAgentesPorColegio', 'codColegio': codColegio, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            if (data.statusMessage) {
                aplicarFiltros(data.codAgentes);
            }
        }
    });
}

function abrirModalAssociar(codAgente, nomeAgente) {
    $('#modalAssociarAgente').val(codAgente);
    $('#modalAssociarLabel').text('Associar Aluno - ' + nomeAgente);
    carregarAlunosDisponiveis(codAgente);
    limparCamposNovoAluno();
    $('#abaSelecionar').tab('show');
    $('#modalAssociar').modal('show');
}

function carregarAlunosDisponiveis(codAgente) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoAgente', 'm': 'carregarAlunosDisponiveis', 'codAgente': codAgente, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            var select = $('#selectAlunoDisponivel');
            select.find('option:not(:first)').remove();
            if (data.alunos) {
                $.each(data.alunos, function(i, al) {
                    var label = al.codigoExibicao;
                    if (al.nomeColegio) label += ' - ' + al.nomeColegio;
                    label += ' (' + al.nivel + ' / ' + al.turno + ')';
                    select.append('<option value="' + al.codAluno + '">' + label + '</option>');
                });
            }
        }
    });
}

function associarAlunoExistente() {
    var codAgente = $('#modalAssociarAgente').val();
    var codAluno = $('#selectAlunoDisponivel').val();
    if (!codAluno) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-exclamation-triangle"></i> Selecione um aluno.</div>');
        return;
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoAgente', 'm': 'associarAluno', 'codAgente': codAgente, 'codAluno': codAluno, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                processJson(data);
                $('#modalAssociar').modal('hide');
                atualizarCardAgente(codAgente);
            } else {
                $('#mensagem').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + data.message + '</div>');
            }
        },
        complete: function() { stopLoading(); }
    });
}

function cadastrarEAssociarAluno() {
    var codAgente = $('#modalAssociarAgente').val();
    var codigoExibicao = $('#novoCodigoExibicao').val();
    var codColegio = $('#novoColegio').val();
    var nivel = $('#novoNivel').val();
    var turno = $('#novoTurno').val();

    if (!nivel || !turno) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-exclamation-triangle"></i> Preencha os campos obrigat&oacute;rios: N&iacute;vel e Turno.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarVinculoAgente',
            'm': 'cadastrarEAssociarAluno',
            'codAgente': codAgente,
            'codigoExibicao': codigoExibicao,
            'codColegio': codColegio,
            'nivel': nivel,
            'turno': turno,
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                processJson(data);
                $('#modalAssociar').modal('hide');
                atualizarCardAgente(codAgente);
            } else {
                $('#mensagem').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + data.message + '</div>');
            }
        },
        complete: function() { stopLoading(); }
    });
}

function editarAlunoVinculo(codAluno, codAgente) {
    if (!codAluno) return;
    $('#editAgentContexto').val(codAgente);
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'codAluno': codAluno, 'c': 'GerenciarAluno', 'm': 'editarAluno', '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            $('#editCodAluno').val(data.codAluno);
            $('#editCodigoExibicao').val(data.codigoExibicao);
            $('#editColegio').val(data.codColegio);
            $('#editNivel').val(data.nivel);
            $('#editTurno').val(data.turno);
            $('#modalEditarAluno').modal('show');
        },
        complete: function() { stopLoading(); }
    });
}

function salvarEdicaoAluno() {
    var codAgente = $('#editAgentContexto').val();
    $('#formEditarAlunoVinculo').ajaxSubmit({
        target: '#mensagem',
        dataType: 'json',
        cache: false,
        beforeSend: function() { startLoading(); },
        timeout: 4000,
        error: function(ajax) { showResponse(ajax); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                $('#modalEditarAluno').modal('hide');
                if (codAgente) {
                    atualizarCardAgente(codAgente);
                }
            }
        },
        complete: function() { stopLoading(); }
    });
}

var _desassociarVinculo = null;
var _desassociarAgente = null;

function desassociarAlunoInline(codVinculo, codAgente) {
    _desassociarVinculo = codVinculo;
    _desassociarAgente = codAgente;
    $('#modalDesassociar').modal('show');
}

function confirmarDesassociar() {
    if (!_desassociarVinculo) return;
    $('#modalDesassociar').modal('hide');
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'codVinculo': _desassociarVinculo, 'c': 'GerenciarVinculoAgente', 'm': 'desassociarAluno', '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                atualizarCardAgente(_desassociarAgente);
            }
        },
        complete: function() {
            stopLoading();
            _desassociarVinculo = null;
            _desassociarAgente = null;
        }
    });
}

function atualizarCardAgente(codAgente) {
    alunosCarregados[codAgente] = false;
    carregarAlunosDoAgente(codAgente);

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarVinculoAgente', 'm': 'carregarAlunosVinculados', 'codAgente': codAgente, '_submit_check': '1'},
        function(html) {
            var temp = $('<tbody>').html(html);
            var totalRows = temp.find('tr').length;
            var totalAlunos = totalRows > 1 ? totalRows - 1 : 0;

            var card = $('.card-agente[data-cod-agente="' + codAgente + '"]');
            var qtdEl = card.find('.agente-qtd');
            if (totalAlunos > 0) {
                qtdEl.text(totalAlunos + ' aluno(s)').removeClass('agente-qtd-zero');
            } else {
                qtdEl.text('0 alunos').addClass('agente-qtd-zero');
            }

            for (var i = 0; i < agentesData.length; i++) {
                if (agentesData[i].codInscrito == codAgente) {
                    agentesData[i].totalAlunos = totalAlunos;
                    break;
                }
            }
        }
    );
}

function limparCamposNovoAluno() {
    $('#novoCodigoExibicao').val('');
    $('#novoColegio').val('');
    $('#novoNivel').val('');
    $('#novoTurno').val('');
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function escapeAttr(text) {
    return text.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}
</script>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
?>

<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4>Gest&atilde;o por Agente</h4>
    </div>
    <div class="panel-body">

        <!-- Barra de filtros -->
        <div class="row filtro-bar">
            <div class="col-sm-4">
                <label for="filtroNome">Buscar por nome:</label>
                <input type="text" id="filtroNome" class="form-control" placeholder="Digite o nome do agente...">
            </div>
            <div class="col-sm-3">
                <label for="filtroColegio">Filtrar por col&eacute;gio:</label>
                <select id="filtroColegio" class="form-control">
                    <option value="">Todos os col&eacute;gios</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label for="filtroStatus">Filtrar por status:</label>
                <select id="filtroStatus" class="form-control">
                    <option value="">Todos</option>
                    <option value="ATIVOS">Apenas ativos</option>
                    <option value="INATIVOS">Apenas inativos com alunos</option>
                </select>
            </div>
            <div class="col-sm-2" style="padding-top:25px;">
                <span id="contadorAgentes" class="text-muted">Carregando...</span>
            </div>
        </div>

        <div id="carregando" class="shown hidden">
            <div class="imagem">
                <div class="frase">Carregando...</div>
                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
            </div>
        </div>
        <div id="mensagem"></div>

        <!-- Container dos cards -->
        <div id="listaAgentes"></div>

    </div>
</div>

<!-- Modal Associar Aluno -->
<div class="modal fade" id="modalAssociar" tabindex="-1" role="dialog" aria-labelledby="modalAssociarLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalAssociarLabel">Associar Aluno</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalAssociarAgente" value="">
                <!-- Abas dentro do modal -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#tabSelecionar" id="abaSelecionar" aria-controls="tabSelecionar" role="tab" data-toggle="tab">Selecionar Existente</a>
                    </li>
                    <li role="presentation">
                        <a href="#tabCadastrar" aria-controls="tabCadastrar" role="tab" data-toggle="tab">Cadastrar Novo</a>
                    </li>
                </ul>
                <div class="tab-content" style="padding-top:15px;">
                    <!-- Aba: Selecionar Existente -->
                    <div role="tabpanel" class="tab-pane active" id="tabSelecionar">
                        <div class="form-group">
                            <label for="selectAlunoDisponivel">Aluno:</label>
                            <select id="selectAlunoDisponivel" class="form-control">
                                <option value="">Selecione um aluno...</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" id="btnConfirmarAssociar">
                            <i class="fa fa-link"></i> Associar
                        </button>
                    </div>
                    <!-- Aba: Cadastrar Novo -->
                    <div role="tabpanel" class="tab-pane" id="tabCadastrar">
                        <div class="form-group">
                            <label for="novoCodigoExibicao">C&oacute;digo de Exibi&ccedil;&atilde;o:</label>
                            <input type="text" id="novoCodigoExibicao" class="form-control" maxlength="50" placeholder="Gerado automaticamente (ex: ALU-001)">
                        </div>
                        <div class="form-group">
                            <label for="novoColegio">Col&eacute;gio:</label>
                            <select id="novoColegio" class="form-control">
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="novoNivel">N&iacute;vel: *</label>
                            <select id="novoNivel" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="Infantil">Infantil</option>
                                <option value="Fundamental I">Fundamental I</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="novoTurno">Turno: *</label>
                            <select id="novoTurno" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="Matutino">Matutino</option>
                                <option value="Vespertino">Vespertino</option>
                                <option value="Integral">Integral</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-success" id="btnCadastrarEAssociar">
                            <i class="fa fa-plus"></i> Cadastrar e Associar
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Desassociacao -->
<div class="modal fade" id="modalDesassociar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Desassocia&ccedil;&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja realmente desassociar este aluno do agente?</p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarDesassociar()"><i class="fa fa-unlink"></i> Desassociar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Aluno (do vinculo) -->
<div class="modal fade" id="modalEditarAluno" tabindex="-1" role="dialog" aria-labelledby="modalEditarAlunoLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formEditarAlunoVinculo" name="formEditarAlunoVinculo" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalEditarAlunoLabel">Editar Aluno</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editCodigoExibicao">C&oacute;digo de Exibi&ccedil;&atilde;o:</label>
                        <input type="text" id="editCodigoExibicao" name="codigoExibicao" class="form-control" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="editColegio">Col&eacute;gio:</label>
                        <select id="editColegio" name="codColegio" class="form-control">
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editNivel">N&iacute;vel:</label>
                        <select id="editNivel" name="nivel" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="Infantil">Infantil</option>
                            <option value="Fundamental I">Fundamental I</option>
                            <option value="Fundamental II">Fundamental II</option>
                            <option value="Medio">M&eacute;dio</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editTurno">Turno:</label>
                        <select id="editTurno" name="turno" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="Matutino">Matutino</option>
                            <option value="Vespertino">Vespertino</option>
                            <option value="Integral">Integral</option>
                        </select>
                    </div>

                    <input type="hidden" name="codAluno" id="editCodAluno" value="">
                    <input type="hidden" id="editAgentContexto" value="">
                    <input type="hidden" name="novoCodAluno" value="1">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarAluno" name="c">
                    <input type="hidden" value="salvarAluno" name="m">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarEdicaoAluno()">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
