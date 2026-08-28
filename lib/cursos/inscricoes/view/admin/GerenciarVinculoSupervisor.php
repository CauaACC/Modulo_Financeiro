<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
if (!$_isAdminPedagogico) {
    require_once(dirname(__FILE__)."/acesso-negado.php");
    exit;
}
$objSRV = new GerenciarVinculoSupervisorAC();
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>

<style>
.panel-supervisor { margin-bottom: 0; border-radius: 0; border-top: none; }
.panel-supervisor:first-child { border-top: 1px solid #ddd; }
.panel-supervisor:last-child { border-radius: 0 0 4px 4px; }
.panel-supervisor .panel-heading { padding: 8px 15px; }
</style>

<script type="text/javascript">
var _supervisores = [];
var _codSupervisorAtivo = null;

$(document).ready(function() {
    carregarSupervisores();
});

function carregarSupervisores() {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoSupervisor', 'm': 'carregarListaSupervisores', '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { $('#listaSupervisores').html('<div class="text-center" style="padding:30px;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>'); },
        success: function(data) {
            if (data.statusMessage && data.supervisores) {
                _supervisores = data.supervisores;
                renderizarCards(data.supervisores);
            } else {
                $('#listaSupervisores').html('<div class="text-center text-muted" style="padding:30px;">Nenhum supervisor encontrado.</div>');
            }
        },
        error: function() {
            $('#listaSupervisores').html('<div class="text-center text-danger" style="padding:30px;">Erro ao carregar supervisores.</div>');
        }
    });
}

function renderizarCards(supervisores) {
    if (supervisores.length === 0) {
        $('#listaSupervisores').html('<div class="text-center text-muted" style="padding:30px;">Nenhum supervisor encontrado.</div>');
        return;
    }

    var html = '';
    for (var i = 0; i < supervisores.length; i++) {
        var s = supervisores[i];
        var nomeEsc = escapeHtml(s.nome).replace(/'/g, "\\'");
        html += '<div class="panel panel-default panel-supervisor" id="panelSup' + s.codUsuario + '">';
        html += '  <div class="panel-heading" style="cursor:pointer;" onclick="toggleSupervisor(' + s.codUsuario + ')">';
        html += '    <div class="row">';
        html += '      <div class="col-sm-4"><strong>' + escapeHtml(s.nome) + '</strong></div>';
        html += '      <div class="col-sm-3"><small class="text-muted">' + escapeHtml(s.login) + '</small></div>';
        var badgePesClass = s.totalAgentes > 0 ? 'label label-primary' : '';
        var badgeColClass = (s.totalColegios || 0) > 0 ? 'label label-info' : '';
        html += '      <div class="col-sm-1 text-center"><span class="' + badgePesClass + '" id="badgeAgentes' + s.codUsuario + '">' + s.totalAgentes + ' PES</span></div>';
        html += '      <div class="col-sm-1 text-center"><span class="' + badgeColClass + '" id="badgeColegios' + s.codUsuario + '">' + (s.totalColegios || 0) + ' Col.</span></div>';
        html += '      <div class="col-sm-3 text-right">';
        html += '        <button class="btn btn-xs btn-success" onclick="event.stopPropagation(); abrirModalAssociarPES(' + s.codUsuario + ', \'' + nomeEsc + '\')" title="Associar PES">';
        html += '          <i class="fa fa-plus"></i> PES';
        html += '        </button> ';
        html += '        <button class="btn btn-xs btn-info" onclick="event.stopPropagation(); abrirModalAssociarColegio(' + s.codUsuario + ', \'' + nomeEsc + '\')" title="Associar Col&eacute;gio">';
        html += '          <i class="fa fa-plus"></i> Col&eacute;gio';
        html += '        </button>';
        html += '      </div>';
        html += '    </div>';
        html += '  </div>';
        html += '  <div class="panel-body" id="bodySup' + s.codUsuario + '" style="display:none;">';

        // Abas
        html += '    <ul class="nav nav-tabs" role="tablist">';
        html += '      <li role="presentation" class="active"><a href="#tabPES' + s.codUsuario + '" data-toggle="tab" onclick="carregarAgentesDoSupervisor(' + s.codUsuario + ')"><i class="fa fa-users"></i> Agentes (PES)</a></li>';
        html += '      <li role="presentation"><a href="#tabColegios' + s.codUsuario + '" data-toggle="tab" onclick="carregarColegiosDoSupervisor(' + s.codUsuario + ')"><i class="fa fa-building"></i> Col&eacute;gios</a></li>';
        html += '    </ul>';
        html += '    <div class="tab-content" style="padding-top:10px;">';

        // Aba PES
        html += '      <div role="tabpanel" class="tab-pane active" id="tabPES' + s.codUsuario + '">';
        html += '        <div class="table-responsive">';
        html += '          <table class="table table-bordered table-striped table-hover table-condensed">';
        html += '            <thead><tr>';
        html += '              <th>Nome</th><th>CPF</th><th class="text-center">Status</th><th class="text-center">Alunos</th><th class="text-center">A&ccedil;&otilde;es</th>';
        html += '            </tr></thead>';
        html += '            <tbody id="tbodyAgentes' + s.codUsuario + '">';
        html += '              <tr><td colspan="5" class="text-center text-muted">Clique para expandir.</td></tr>';
        html += '            </tbody>';
        html += '          </table>';
        html += '        </div>';
        html += '      </div>';

        // Aba Colégios
        html += '      <div role="tabpanel" class="tab-pane" id="tabColegios' + s.codUsuario + '">';
        html += '        <div class="table-responsive">';
        html += '          <table class="table table-bordered table-striped table-hover table-condensed">';
        html += '            <thead><tr>';
        html += '              <th>Col&eacute;gio</th><th class="text-center">Alunos</th><th class="text-center">A&ccedil;&otilde;es</th>';
        html += '            </tr></thead>';
        html += '            <tbody id="tbodyColegios' + s.codUsuario + '">';
        html += '              <tr><td colspan="3" class="text-center text-muted">Clique na aba para carregar.</td></tr>';
        html += '            </tbody>';
        html += '          </table>';
        html += '        </div>';
        html += '      </div>';

        html += '    </div>'; // tab-content
        html += '  </div>'; // panel-body
        html += '</div>';
    }

    $('#listaSupervisores').html(html);
    bindDesassociar();
    $('#contadorSupervisores').text(supervisores.length + ' de ' + supervisores.length + ' supervisor(es)');
}

function toggleSupervisor(codSupervisor) {
    var body = $('#bodySup' + codSupervisor);
    if (body.is(':visible')) {
        body.slideUp(200);
    } else {
        body.slideDown(200);
        carregarAgentesDoSupervisor(codSupervisor);
    }
}

// ---- PES ----

function carregarAgentesDoSupervisor(codSupervisor) {
    var tbody = $('#tbodyAgentes' + codSupervisor);
    tbody.html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando...</td></tr>');

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarVinculoSupervisor', 'm': 'carregarAgentesVinculados', 'codSupervisor': codSupervisor, '_submit_check': '1'},
        function(html) {
            tbody.html(html);
            bindDesassociar();
        }
    );
}

function bindDesassociar() {
    $('.btn-desassociar-pes').off('click').on('click', function() {
        var tr = $(this).closest('tr');
        var codVinculo = tr.data('cod-vinculo');
        var nome = tr.find('td:first').text();
        _codSupervisorAtivo = tr.closest('.panel-supervisor').attr('id').replace('panelSup', '');

        $('#desassociarNomePES').text(nome);
        $('#desassociarCodVinculo').val(codVinculo);
        $('#modalDesassociarPES').modal('show');
    });

    $('.btn-desassociar-colegio').off('click').on('click', function() {
        var tr = $(this).closest('tr');
        var codVinculo = tr.data('cod-vinculo');
        var nome = tr.find('td:first').text();
        _codSupervisorAtivo = tr.closest('.panel-supervisor').attr('id').replace('panelSup', '');

        $('#desassociarNomeColegio').text(nome);
        $('#desassociarColegioVinculo').val(codVinculo);
        $('#modalDesassociarColegio').modal('show');
    });
}

function confirmarDesassociarPES() {
    var codVinculo = $('#desassociarCodVinculo').val();
    $('#modalDesassociarPES').modal('hide');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoSupervisor', 'm': 'desassociarAgente', 'codVinculo': codVinculo, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            processJson(data);
            if (data.statusMessage && _codSupervisorAtivo) {
                carregarAgentesDoSupervisor(_codSupervisorAtivo);
                atualizarBadge('badgeAgentes' + _codSupervisorAtivo, -1, ' PES');
            }
        }
    });
}

function abrirModalAssociarPES(codSupervisor, nomeSupervisor) {
    _codSupervisorAtivo = codSupervisor;
    $('#associarPESNomeSupervisor').text(nomeSupervisor);
    $('#associarPESCodSupervisor').val(codSupervisor);
    $('#selectAgente').html('<option value="">Carregando...</option>');
    $('#modalAssociarPESMsg').html('');
    $('#modalAssociarPES').modal('show');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoSupervisor', 'm': 'carregarAgentesDisponiveis', 'codSupervisor': codSupervisor, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            if (data.statusMessage && data.agentes) {
                var opts = '<option value="">-- Selecione um PES --</option>';
                for (var i = 0; i < data.agentes.length; i++) {
                    var ag = data.agentes[i];
                    opts += '<option value="' + ag.codInscrito + '">' + escapeHtml(titleCase(ag.nome)) + ' (' + ag.totalAlunos + ' alunos)</option>';
                }
                $('#selectAgente').html(opts);
            } else {
                $('#selectAgente').html('<option value="">Nenhum PES dispon&iacute;vel</option>');
            }
        },
        error: function() {
            $('#selectAgente').html('<option value="">Erro ao carregar</option>');
        }
    });
}

function associarPES() {
    var codSupervisor = $('#associarPESCodSupervisor').val();
    var codAgente = $('#selectAgente').val();
    $('#modalAssociarPESMsg').html('');

    if (!codAgente) {
        $('#modalAssociarPESMsg').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-exclamation-triangle"></i> Selecione um PES.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoSupervisor', 'm': 'associarAgente', 'codSupervisor': codSupervisor, 'codAgente': codAgente, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            if (data.statusMessage) {
                $('#modalAssociarPESMsg').html('<div class="alert alert-success"><i class="fa fa-check"></i> ' + data.message + '</div>');
                $('#selectAgente option[value="' + codAgente + '"]').remove();
                carregarAgentesDoSupervisor(codSupervisor);
                atualizarBadge('badgeAgentes' + codSupervisor, 1, ' PES');
            } else {
                $('#modalAssociarPESMsg').html('<div class="alert alert-danger"><i class="fa fa-times"></i> ' + (data.message || 'Erro ao associar.') + '</div>');
            }
        },
        error: function() {
            $('#modalAssociarPESMsg').html('<div class="alert alert-danger">Erro de comunica&ccedil;&atilde;o.</div>');
        }
    });
}

// ---- Colégios ----

function carregarColegiosDoSupervisor(codSupervisor) {
    var tbody = $('#tbodyColegios' + codSupervisor);
    tbody.html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando...</td></tr>');

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarVinculoSupervisor', 'm': 'carregarColegiosVinculados', 'codSupervisor': codSupervisor, '_submit_check': '1'},
        function(html) {
            tbody.html(html);
            bindDesassociar();
        }
    );
}

function abrirModalAssociarColegio(codSupervisor, nomeSupervisor) {
    _codSupervisorAtivo = codSupervisor;
    $('#associarColegioNomeSupervisor').text(nomeSupervisor);
    $('#associarColegioCodSupervisor').val(codSupervisor);
    $('#selectColegio').html('<option value="">Carregando...</option>');
    $('#modalAssociarColegioMsg').html('');
    $('#modalAssociarColegio').modal('show');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoSupervisor', 'm': 'carregarColegiosDisponiveis', 'codSupervisor': codSupervisor, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            if (data.statusMessage && data.colegios) {
                var opts = '<option value="">-- Selecione um col&eacute;gio --</option>';
                for (var i = 0; i < data.colegios.length; i++) {
                    var c = data.colegios[i];
                    opts += '<option value="' + c.codColegio + '">' + escapeHtml(c.nome) + ' (' + c.totalAlunos + ' alunos, ' + c.totalPes + ' PES)</option>';
                }
                $('#selectColegio').html(opts);
            } else {
                $('#selectColegio').html('<option value="">Nenhum col&eacute;gio dispon&iacute;vel</option>');
            }
        },
        error: function() {
            $('#selectColegio').html('<option value="">Erro ao carregar</option>');
        }
    });
}

function associarColegio() {
    var codSupervisor = $('#associarColegioCodSupervisor').val();
    var codColegio = $('#selectColegio').val();
    $('#modalAssociarColegioMsg').html('');

    if (!codColegio) {
        $('#modalAssociarColegioMsg').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-exclamation-triangle"></i> Selecione um col&eacute;gio.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoSupervisor', 'm': 'associarColegio', 'codSupervisor': codSupervisor, 'codColegio': codColegio, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            if (data.statusMessage) {
                $('#modalAssociarColegioMsg').html('<div class="alert alert-success"><i class="fa fa-check"></i> ' + data.message + '</div>');
                $('#selectColegio option[value="' + codColegio + '"]').remove();
                carregarColegiosDoSupervisor(codSupervisor);
                atualizarBadge('badgeColegios' + codSupervisor, 1, ' Col.');
            } else {
                $('#modalAssociarColegioMsg').html('<div class="alert alert-danger"><i class="fa fa-times"></i> ' + (data.message || 'Erro ao associar.') + '</div>');
            }
        },
        error: function() {
            $('#modalAssociarColegioMsg').html('<div class="alert alert-danger">Erro de comunica&ccedil;&atilde;o.</div>');
        }
    });
}

function confirmarDesassociarColegio() {
    var codVinculo = $('#desassociarColegioVinculo').val();
    $('#modalDesassociarColegio').modal('hide');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarVinculoSupervisor', 'm': 'desassociarColegio', 'codVinculo': codVinculo, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            processJson(data);
            if (data.statusMessage && _codSupervisorAtivo) {
                carregarColegiosDoSupervisor(_codSupervisorAtivo);
                atualizarBadge('badgeColegios' + _codSupervisorAtivo, -1, ' Col.');
            }
        }
    });
}

// ---- Utilitários ----

function atualizarBadge(badgeId, delta, sufixo) {
    var badge = $('#' + badgeId);
    var atual = parseInt(badge.text()) || 0;
    var novo = atual + delta;
    badge.text(novo + sufixo);
    var corClass = badgeId.indexOf('Agentes') !== -1 ? 'label label-primary' : 'label label-info';
    badge.attr('class', novo > 0 ? corClass : '');

    // Atualizar o array _supervisores para manter o filtro consistente
    var codSup = badgeId.replace('badgeAgentes', '').replace('badgeColegios', '');
    for (var i = 0; i < _supervisores.length; i++) {
        if (_supervisores[i].codUsuario == codSup) {
            if (badgeId.indexOf('Agentes') !== -1) {
                _supervisores[i].totalAgentes = novo;
            } else {
                _supervisores[i].totalColegios = novo;
            }
            break;
        }
    }
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function titleCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
}

function filtrarSupervisores() {
    var termo = $('#filtroNome').val().toLowerCase();
    var filtroVinculo = $('#filtroVinculo').val();
    var visiveis = 0;

    $('.panel-supervisor').each(function() {
        var nome = $(this).find('.panel-heading strong').text().toLowerCase();
        var matchNome = !termo || nome.indexOf(termo) !== -1;

        var matchVinculo = true;
        if (filtroVinculo) {
            var codSup = $(this).attr('id').replace('panelSup', '');
            var sup = null;
            for (var i = 0; i < _supervisores.length; i++) {
                if (_supervisores[i].codUsuario == codSup) { sup = _supervisores[i]; break; }
            }
            if (sup) {
                if (filtroVinculo === 'com_pes') matchVinculo = sup.totalAgentes > 0;
                else if (filtroVinculo === 'sem_pes') matchVinculo = sup.totalAgentes === 0;
                else if (filtroVinculo === 'com_colegio') matchVinculo = (sup.totalColegios || 0) > 0;
                else if (filtroVinculo === 'sem_colegio') matchVinculo = (sup.totalColegios || 0) === 0;
            }
        }

        if (matchNome && matchVinculo) {
            $(this).show();
            visiveis++;
        } else {
            $(this).hide();
        }
    });

    $('#contadorSupervisores').text(visiveis + ' de ' + _supervisores.length + ' supervisor(es)');
}
</script>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><i class="fa fa-users"></i> Supervisores</h4>
    </div>
    <div class="panel-body">

        <?php require_once(dirname(__FILE__)."/area_supervisor_abas.php"); ?>

        <!-- Barra de filtros -->
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-4">
                <label for="filtroNome">Buscar por nome:</label>
                <input type="text" id="filtroNome" class="form-control" placeholder="Digite o nome do supervisor..." oninput="filtrarSupervisores()">
            </div>
            <div class="col-sm-3">
                <label for="filtroVinculo">Filtrar por v&iacute;nculo:</label>
                <select id="filtroVinculo" class="form-control" onchange="filtrarSupervisores()">
                    <option value="">Todos</option>
                    <option value="com_pes">Com PES vinculado</option>
                    <option value="sem_pes">Sem PES vinculado</option>
                    <option value="com_colegio">Com col&eacute;gio vinculado</option>
                    <option value="sem_colegio">Sem col&eacute;gio vinculado</option>
                </select>
            </div>
            <div class="col-sm-2" style="padding-top:25px;">
                <span id="contadorSupervisores" class="text-muted"></span>
            </div>
        </div>

        <!-- Cabeçalho de colunas -->
        <div class="row" style="background:#f5f5f5; border:1px solid #ddd; border-radius:4px 4px 0 0; padding:8px 15px; font-weight:600; font-size:13px; color:#555; margin-bottom:0;">
            <div class="col-sm-4">Supervisor</div>
            <div class="col-sm-3">Login</div>
            <div class="col-sm-1 text-center">PES</div>
            <div class="col-sm-1 text-center">Col&eacute;gios</div>
            <div class="col-sm-3 text-right">A&ccedil;&otilde;es</div>
        </div>

        <div id="listaSupervisores"></div>

    </div>
</div>

<!-- Modal Associar PES -->
<div class="modal fade" id="modalAssociarPES" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus"></i> Associar PES ao Supervisor</h4>
            </div>
            <div class="modal-body">
                <p>Supervisor: <strong id="associarPESNomeSupervisor"></strong></p>
                <input type="hidden" id="associarPESCodSupervisor" value="">
                <div id="modalAssociarPESMsg"></div>
                <div class="form-group">
                    <label for="selectAgente">Selecione o PES (Agente):</label>
                    <select id="selectAgente" class="form-control">
                        <option value="">Carregando...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="associarPES()"><i class="fa fa-link"></i> Associar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Associar Colégio -->
<div class="modal fade" id="modalAssociarColegio" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus"></i> Associar Col&eacute;gio ao Supervisor</h4>
            </div>
            <div class="modal-body">
                <p>Supervisor: <strong id="associarColegioNomeSupervisor"></strong></p>
                <input type="hidden" id="associarColegioCodSupervisor" value="">
                <div id="modalAssociarColegioMsg"></div>
                <div class="form-group">
                    <label for="selectColegio">Selecione o Col&eacute;gio:</label>
                    <select id="selectColegio" class="form-control">
                        <option value="">Carregando...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="associarColegio()"><i class="fa fa-link"></i> Associar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Desassociar PES -->
<div class="modal fade" id="modalDesassociarPES" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-unlink"></i> Desvincular PES</h4>
            </div>
            <div class="modal-body">
                <p>Deseja desvincular <strong id="desassociarNomePES"></strong> deste supervisor?</p>
                <p class="text-muted"><small><i class="fa fa-info-circle"></i> O v&iacute;nculo ser&aacute; desativado (mantendo hist&oacute;rico).</small></p>
                <input type="hidden" id="desassociarCodVinculo" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarDesassociarPES()"><i class="fa fa-unlink"></i> Desvincular</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Desassociar Colégio -->
<div class="modal fade" id="modalDesassociarColegio" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-unlink"></i> Desvincular Col&eacute;gio</h4>
            </div>
            <div class="modal-body">
                <p>Deseja desvincular <strong id="desassociarNomeColegio"></strong> deste supervisor?</p>
                <p class="text-muted"><small><i class="fa fa-info-circle"></i> O v&iacute;nculo ser&aacute; desativado (mantendo hist&oacute;rico).</small></p>
                <input type="hidden" id="desassociarColegioVinculo" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarDesassociarColegio()"><i class="fa fa-unlink"></i> Desvincular</button>
            </div>
        </div>
    </div>
</div>
