<?php
require_once(dirname(__FILE__) . "/../../view/admin/seguranca-adm-geral.php");
$objSRV = new GerenciarGestaoAcessosAC();
?>
<link href="resources/css/select2.min.css" rel="stylesheet" />
<script src="resources/js/select2/select2.min.js"></script>
<script src="resources/js/select2/pt-BR.js"></script>
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript">
$(document).ready(function() {

    // Inicializar tablesorter nas 4 tabelas
    $("#tblEventosGestor").tablesorter({ headers: { 3: { sorter: false } } });
    $("#tblUsuariosGestor").tablesorter({ headers: { 3: { sorter: false } } });
    $("#tblUsuariosEvento").tablesorter({ headers: { 2: { sorter: false }, 3: { sorter: false } } });
    $("#tblEventosUsuario").tablesorter({ headers: { 2: { sorter: false }, 3: { sorter: false } } });

    // Inicializar Select2 nos selects de busca (defensivo: se Select2 não carregar, funciona normal)
    if ($.fn.select2) {
        $('#selectUsuarioTab4').select2({
            placeholder: 'Digite para buscar um usu\u00e1rio',
            allowClear: true,
            language: {
                noResults: function() {
                    return $('<span>Nenhum resultado encontrado <button type="button" class="btn btn-success btn-xs" style="margin-left:8px;" onclick="abrirModalCadastrarLogin()"><i class="fa fa-user-plus"></i> Cadastrar</button></span>');
                },
                inputTooShort: function(args) { return 'Digite ' + (args.minimum - args.input.length) + ' ou mais caracteres'; },
                searching: function() { return 'Buscando\u2026'; }
            },
            width: '100%'
        });

        $('#selectEventoTab3').select2({
            placeholder: 'Digite para buscar um evento',
            allowClear: true,
            language: 'pt-BR',
            width: '100%'
        }).val('').trigger('change');

        $('#selectEventoTab4').select2({
            placeholder: 'Selecione um evento',
            allowClear: true,
            language: 'pt-BR',
            width: '100%'
        });
    }

    // Eventos de change (fora do if para funcionar com ou sem Select2)
    $('#selectUsuarioTab4').on('change', function() {
        carregarTabelaEventosUsuario();
        $('#divEnviarLogin').toggle(!!$(this).val());
    });

    $('#selectEventoTab3').on('change', function() {
        carregarTabelaUsuariosEvento();
    });

    // Recarregar tabela ao fechar modal de grupos
    $('#modalGrupos').on('hidden.bs.modal', function() {
        if ($('#tabEventosUsuario').hasClass('active') || $('#tabEventosUsuario').hasClass('show')) {
            carregarTabelaEventosUsuario();
        } else if ($('#tabUsuariosEvento').hasClass('active') || $('#tabUsuariosEvento').hasClass('show')) {
            carregarTabelaUsuariosEvento();
        }
    });

    // Ao trocar de aba via nav-tabs
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr('href');
        if (target === '#tabEventosGestor') {
            carregarTabelaEventosGestor();
        }
    });

    // Filtrar tabela Usuarios do Evento ao selecionar usuario ou grupo
    $('#selectUsuarioTab3').on('change', function() {
        filtrarTabelaUsuariosEvento();
    });
    $('#selectGrupoTab3').on('change', function() {
        filtrarTabelaUsuariosEvento();
    });
});

/* ========== Helper: mover linha de total para tfoot ========== */
function moverTotalParaTfoot(tblId) {
    var tbl = $(tblId);
    var totalRow = tbl.find('tbody tr.total-row');
    if (totalRow.length) {
        tbl.find('tfoot').html(totalRow);
        totalRow.remove();
    } else {
        tbl.find('tfoot').empty();
    }
    tbl.trigger("update");
}

/* ========== Tab 1: Eventos do Gestor ========== */
function carregarTabelaEventosGestor() {
    var gestor = $('#selectGestor').val();
    if (!gestor) {
        $("tbody.eventosGestor").html('<tr><td colspan="4">Selecione um gestor.</td></tr>');
        return;
    }

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGestaoAcessos',
        'm': 'carregarTabelaEventosGestor',
        'gestor': gestor,
        '_submit_check': '1'
    }, function(html) {
        $("tbody.eventosGestor").html(html);
        moverTotalParaTfoot("#tblEventosGestor");
    });
}

/* ========== Tab 2: Usuarios do Gestor ========== */
function carregarTabelaUsuariosGestor() {
    var gestor = $('#selectGestorTab2').val();
    if (!gestor) {
        $("tbody.usuariosGestor").html('<tr><td colspan="4">Selecione um gestor.</td></tr>');
        return;
    }

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGestaoAcessos',
        'm': 'carregarTabelaUsuariosGestor',
        'gestor': gestor,
        '_submit_check': '1'
    }, function(html) {
        $("tbody.usuariosGestor").html(html);
        moverTotalParaTfoot("#tblUsuariosGestor");
    });
}

/* ========== Importar Usuarios de outro Evento ========== */
function importarUsuariosEvento() {
    var codEventoOrigem = $('#selectEventoImportar').val();
    var codEventoDestino = $('#selectEventoTab3').val();

    if (!codEventoOrigem) {
        alert('Selecione o evento de origem.');
        return;
    }
    if (!codEventoDestino) {
        alert('Selecione o evento de destino (campo Evento acima).');
        return;
    }
    if (codEventoOrigem == codEventoDestino) {
        alert('Evento de origem e destino devem ser diferentes.');
        return;
    }
    if (!confirm('Importar todos os usu\u00e1rios e permiss\u00f5es do evento ' + codEventoOrigem + ' para o evento ' + codEventoDestino + '?')) {
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'importarUsuariosEvento',
            'cod_evento_origem': codEventoOrigem,
            'cod_evento_destino': codEventoDestino,
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaUsuariosEvento();
            }
        },
        complete: function() { stopLoading(); }
    });
}

/* ========== Tab 3: Usuarios do Evento ========== */
function carregarTabelaUsuariosEvento() {
    var codEvento = $('#selectEventoTab3').val();
    if (!codEvento) {
        $("tbody.usuariosEvento").html('<tr><td colspan="4">Selecione um evento.</td></tr>');
        return;
    }

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGestaoAcessos',
        'm': 'carregarTabelaUsuariosEvento',
        'cod_evento': codEvento,
        '_submit_check': '1'
    }, function(html) {
        $("tbody.usuariosEvento").html(html);
        moverTotalParaTfoot("#tblUsuariosEvento");
        // Reaplicar filtro se houver seleção nos selects
        filtrarTabelaUsuariosEvento();
    });

    // Recarregar select de usuarios do evento para o form de adicionar grupo
    carregarSelectUsuariosEvento(codEvento);
}

function carregarSelectUsuariosEvento(codEvento) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGestaoAcessos',
        'm': 'carregarSelectUsuariosEvento',
        'cod_evento': codEvento,
        '_submit_check': '1'
    }, function(html) {
        $('#selectUsuarioTab3').html(html);
    });
}

/* ========== Tab 4: Eventos do Usuario ========== */
function carregarTabelaEventosUsuario() {
    var codUsuario = $('#selectUsuarioTab4').val();
    if (!codUsuario) {
        $("tbody.eventosUsuario").html('<tr><td colspan="4">Selecione um usu\u00e1rio.</td></tr>');
        $('#selectEventoTab4').html('<option value="">Selecione um evento</option>');
        return;
    }

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGestaoAcessos',
        'm': 'carregarTabelaEventosUsuario',
        'cod_usuario': codUsuario,
        '_submit_check': '1'
    }, function(html) {
        $("tbody.eventosUsuario").html(html);
        moverTotalParaTfoot("#tblEventosUsuario");
    });

    // Recarregar select de eventos disponíveis
    carregarSelectEventosDisponiveisUsuario(codUsuario);
}

function carregarSelectEventosDisponiveisUsuario(codUsuario) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGestaoAcessos',
        'm': 'carregarSelectEventosDisponiveisUsuario',
        'cod_usuario': codUsuario,
        '_submit_check': '1'
    }, function(html) {
        $('#selectEventoTab4').html(html).trigger('change.select2');
    });
}

function adicionarUsuarioEvento() {
    var codUsuario = $('#selectUsuarioTab4').val();
    var codEvento  = $('#selectEventoTab4').val();

    if (!codUsuario) { $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um usu\u00e1rio.</div>'); return; }
    if (!codEvento)  { $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um evento.</div>'); return; }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'adicionarUsuarioEvento',
            'cod_usuario': codUsuario,
            'cod_evento': codEvento,
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaEventosUsuario();
            }
        },
        complete: function() { stopLoading(); }
    });
}

/* ========== Filtro na tabela Usuarios do Evento ========== */
function filtrarTabelaUsuariosEvento() {
    var loginFiltro = $('#selectUsuarioTab3 option:selected').text().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    var grupoFiltro = $('#selectGrupoTab3 option:selected').text().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    var temUsuario = $('#selectUsuarioTab3').val();
    var temGrupo   = $('#selectGrupoTab3').val();

    $('#tblUsuariosEvento tbody.usuariosEvento tr').not('.total-row').each(function() {
        var login  = $(this).find('td').eq(0).text().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        var grupos = $(this).find('td').eq(2).text().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '');

        var mostra = true;
        if (temUsuario) {
            mostra = mostra && (login.indexOf(loginFiltro) > -1);
        }
        if (temGrupo) {
            mostra = mostra && (grupos.indexOf(grupoFiltro) > -1);
        }
        $(this).toggle(mostra);
    });
}

/* ========== Navegacao entre abas ========== */
function irParaUsuariosEvento(codEvento) {
    $('a[href="#tabUsuariosEvento"]').tab('show');
    $('#selectEventoTab3').val(codEvento).trigger('change');
    $('html, body').animate({ scrollTop: 0 }, 200);
}

function irParaEventosUsuario(codUsuario) {
    $('a[href="#tabEventosUsuario"]').tab('show');
    $('#selectUsuarioTab4').val(codUsuario).trigger('change');
    $('html, body').animate({ scrollTop: 0 }, 200);
}

/* ========== CRUD: Adicionar Grupo ao Usuario ========== */
function adicionarGrupoUsuario() {
    var codEvento  = $('#selectEventoTab3').val();
    var codUsuario = $('#selectUsuarioTab3').val();
    var codGrupo   = $('#selectGrupoTab3').val();

    if (!codEvento) { $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um evento.</div>'); return; }
    if (!codUsuario) { $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um usu\u00e1rio.</div>'); return; }
    if (!codGrupo) { $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um grupo.</div>'); return; }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'adicionarGrupoUsuario',
            'cod_evento': codEvento,
            'cod_usuario': codUsuario,
            'cod_grupo': codGrupo,
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaUsuariosEvento();
            }
        },
        complete: function() { stopLoading(); }
    });
}

/* ========== CRUD: Remover Grupo do Usuario ========== */
var _removerGrupo = { codUsuario: null, codGrupo: null, codEvento: null, callback: null };

function removerGrupoUsuario(codUsuario, codGrupo) {
    _removerGrupo = { codUsuario: codUsuario, codGrupo: codGrupo, codEvento: $('#selectEventoTab3').val(), callback: 'carregarTabelaUsuariosEvento' };
    $('#modalRemoverGrupo').modal('show');
}

function confirmarRemoverGrupo() {
    if (!_removerGrupo.codUsuario) return;
    $('#modalRemoverGrupo').modal('hide');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'removerGrupoUsuario',
            'cod_evento': _removerGrupo.codEvento,
            'cod_usuario': _removerGrupo.codUsuario,
            'cod_grupo': _removerGrupo.codGrupo,
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                if (_removerGrupo.callback === 'carregarTabelaEventosUsuario') {
                    carregarTabelaEventosUsuario();
                } else {
                    carregarTabelaUsuariosEvento();
                }
            }
        },
        complete: function() { stopLoading(); _removerGrupo = {}; }
    });
}

/* ========== Tab 4: Remover Grupo do Usuario (inline) ========== */
function removerGrupoUsuarioTab4(codUsuario, codGrupo, codEvento) {
    _removerGrupo = { codUsuario: codUsuario, codGrupo: codGrupo, codEvento: codEvento, callback: 'carregarTabelaEventosUsuario' };
    $('#modalRemoverGrupo').modal('show');
}

/* ========== Tab 4: Modal de Grupos ========== */
function abrirModalGrupos(codUsuario, codEvento) {
    $('#modalGruposBody').html('<p>Carregando...</p>');
    $('#modalGrupos').modal('show');

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGestaoAcessos',
        'm': 'carregarCheckboxGruposEvento',
        'cod_usuario': codUsuario,
        'cod_evento': codEvento,
        '_submit_check': '1'
    }, function(html) {
        $('#modalGruposBody').html(html);
    });
}

function alternarGrupoEvento(codUsuario, codGrupo, codEvento, element) {
    var acao = $(element).is(':checked') ? 'adicionar' : 'remover';

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': acao === 'adicionar' ? 'adicionarGrupoUsuario' : 'removerGrupoUsuario',
            'cod_evento': codEvento,
            'cod_usuario': codUsuario,
            'cod_grupo': codGrupo,
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
        },
        complete: function() { stopLoading(); }
    });
}

/* ========== Copiar Direitos de Outro Evento ========== */
function abrirModalCopiarDireitos(codEventoDestino, tituloDestino) {
    $('#copiarDireitosEventoDestino').text(codEventoDestino + ' - ' + tituloDestino);
    $('#copiarDireitosEventoDestinoId').val(codEventoDestino);

    // Carregar select com eventos do usuário (como origem)
    var codUsuario = $('#selectUsuarioTab4').val();
    var selectOrigem = $('#selectEventoOrigem');
    selectOrigem.html('<option value="">Carregando...</option>');
    $('#modalCopiarDireitos').modal({backdrop: 'static', keyboard: false});

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGestaoAcessos',
        'm': 'carregarTabelaEventosUsuario',
        'cod_usuario': codUsuario,
        '_submit_check': '1'
    }, function(html) {
        // Extrair eventos da resposta HTML para montar select
        var options = '<option value="">Selecione o evento de origem</option>';
        $(html).filter('tr').each(function() {
            var link = $(this).find('a.link-nav');
            if (link.length) {
                var texto = link.text().trim();
                var onclick = link.attr('onclick') || '';
                var match = onclick.match(/irParaUsuariosEvento\((\d+)\)/);
                if (match) {
                    var codEv = match[1];
                    if (codEv != codEventoDestino) {
                        options += '<option value="' + codEv + '">' + texto + '</option>';
                    }
                }
            }
        });
        selectOrigem.html(options);
    });
}

function executarCopiarDireitos() {
    var codUsuario       = $('#selectUsuarioTab4').val();
    var codEventoOrigem  = $('#selectEventoOrigem').val();
    var codEventoDestino = $('#copiarDireitosEventoDestinoId').val();

    if (!codEventoOrigem) {
        $('#modalCopiarDireitosMsg').html('<div class="alert alert-warning">Selecione o evento de origem.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'copiarDireitosEvento',
            'cod_usuario': codUsuario,
            'cod_evento_origem': codEventoOrigem,
            'cod_evento_destino': codEventoDestino,
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                $('#modalCopiarDireitos').modal('hide');
                carregarTabelaEventosUsuario();
            } else {
                $('#modalCopiarDireitosMsg').html('<div class="alert alert-danger">' + data.message + '</div>');
            }
        },
        complete: function() { stopLoading(); }
    });
}

/* ========== Editar Usuário ========== */
function abrirModalEditarUsuario() {
    var codUsuario = $('#selectUsuarioTab4').val();
    if (!codUsuario) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um usu\u00e1rio.</div>');
        return;
    }

    $('#modalEditarUsuarioMsg').html('');
    $('#modalEditarUsuario').modal({backdrop: 'static', keyboard: false});

    // Carregar dados do usuário
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'carregarDadosUsuario',
            'cod_usuario': codUsuario,
            '_submit_check': '1'
        },
        dataType: "json",
        success: function(data) {
            if (data.sucesso) {
                $('#editUsuarioCod').val(data.cod_usuario);
                $('#editUsuarioNome').val(data.nome);
                $('#editUsuarioLogin').val(data.login);
                $('#editUsuarioStatus').val(data.status);
                if ($('#editUsuarioCelular').length) {
                    $('#editUsuarioCelular').val(data.celular || '');
                }
            } else {
                $('#modalEditarUsuarioMsg').html('<div class="alert alert-danger">' + data.mensagem + '</div>');
            }
        }
    });
}

function salvarEdicaoUsuario() {
    var codUsuario = $('#editUsuarioCod').val();
    var nome       = $('#editUsuarioNome').val().trim();
    var login      = $('#editUsuarioLogin').val().trim();
    var status     = $('#editUsuarioStatus').val();
    var celular    = $('#editUsuarioCelular').length ? $('#editUsuarioCelular').val().trim() : '';

    if (!login) {
        $('#modalEditarUsuarioMsg').html('<div class="alert alert-warning">Login \u00e9 obrigat\u00f3rio.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'salvarEdicaoUsuario',
            'cod_usuario': codUsuario,
            'nome': nome,
            'login': login,
            'status': status,
            'celular': celular,
            '_submit_check': '1'
        },
        dataType: "json",
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                $('#modalEditarUsuario').modal('hide');
                // Atualizar o texto do option no select
                var opt = $('#selectUsuarioTab4 option[value="' + codUsuario + '"]');
                opt.text(login + (nome ? ' - ' + nome : ''));
            } else {
                $('#modalEditarUsuarioMsg').html('<div class="alert alert-danger">' + data.message + '</div>');
            }
        },
        complete: function() { stopLoading(); }
    });
}

/* ========== Cadastrar Login ========== */
function abrirModalCadastrarLogin() {
    // Fechar dropdown do Select2 antes de abrir o modal
    if ($.fn.select2) {
        $('#selectUsuarioTab4').select2('close');
    }
    $('#inputNomeLogin').val('');
    $('#inputLogin').val('');
    $('#modalCadastrarLogin').modal('show');
}

function cadastrarLogin() {
    var nome  = $('#inputNomeLogin').val().trim();
    var login = $('#inputLogin').val().trim();

    if (!login) { $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Login \u00e9 obrigat\u00f3rio.</div>'); return; }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'cadastrarLogin',
            'nome': nome,
            'login': login,
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                $('#modalCadastrarLogin').modal('hide');
                recarregarSelectUsuarios(data.cod_usuario);
            }
            processJson(data);
        },
        error: function(xhr, status, error) {
            stopLoading();
            $('#mensagem').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Erro na requisi\u00e7\u00e3o: ' + error + '</div>');
        },
        complete: function() { stopLoading(); }
    });
}

function recarregarSelectUsuarios(codUsuarioSelecionar) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGestaoAcessos',
        'm': 'carregarSelectUsuarios',
        '_submit_check': '1'
    }, function(html) {
        if ($.fn.select2) {
            $('#selectUsuarioTab4').select2('destroy');
        }
        $('#selectUsuarioTab4').html(html);
        if ($.fn.select2) {
            $('#selectUsuarioTab4').select2({
                placeholder: 'Digite para buscar um usu\u00e1rio',
                allowClear: true,
                language: {
                    noResults: function() {
                        return $('<span>Nenhum resultado encontrado <button type="button" class="btn btn-success btn-xs" style="margin-left:8px;" onclick="abrirModalCadastrarLogin()"><i class="fa fa-user-plus"></i> Cadastrar</button></span>');
                    },
                    inputTooShort: function(args) { return 'Digite ' + (args.minimum - args.input.length) + ' ou mais caracteres'; },
                    searching: function() { return 'Buscando\u2026'; }
                },
                width: '100%'
            });
        }
        if (codUsuarioSelecionar) {
            $('#selectUsuarioTab4').val(codUsuarioSelecionar).trigger('change');
        }
    });
}

/* ========== CRUD: Remover Usuario do Evento ========== */
var _removerUsuarioEvento = { codUsuario: null, codEvento: null };

function removerUsuarioEvento(codUsuario, codEvento, label) {
    _removerUsuarioEvento = { codUsuario: codUsuario, codEvento: codEvento };
    var nomeUsuario = $('#selectUsuarioTab4 option:selected').text().trim();
    $('#modalRemoverUsuarioMsg').html(
        'Deseja desvincular o usu\u00e1rio <strong>' + $('<span>').text(nomeUsuario).html() + '</strong> do evento <strong>' + $('<span>').text(label).html() + '</strong>?'
        + '<br><br><span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Isso ir\u00e1 remover todos os grupos/permiss\u00f5es deste usu\u00e1rio neste evento. A a\u00e7\u00e3o n\u00e3o pode ser desfeita.</span>'
    );
    $('#modalRemoverUsuario').modal('show');
}

function confirmarRemoverUsuarioEvento() {
    if (!_removerUsuarioEvento.codUsuario) return;
    $('#modalRemoverUsuario').modal('hide');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'removerUsuarioEvento',
            'cod_evento': _removerUsuarioEvento.codEvento,
            'cod_usuario': _removerUsuarioEvento.codUsuario,
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                if ($('#tabUsuariosEvento').hasClass('active') || $('#tabUsuariosEvento').hasClass('show')) {
                    carregarTabelaUsuariosEvento();
                } else if ($('#tabEventosUsuario').hasClass('active') || $('#tabEventosUsuario').hasClass('show')) {
                    carregarTabelaEventosUsuario();
                }
            }
        },
        complete: function() { stopLoading(); _removerUsuarioEvento = {}; }
    });
}

/* ========== Enviar Login por E-mail ========== */
function enviarEmailLogin() {
    var codUsuario = $('#selectUsuarioTab4').val();
    var login = $('#selectUsuarioTab4 option:selected').data('login');
    if (!codUsuario || !login) { $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um usu\u00e1rio.</div>'); return; }

    _enviarEmailLogin = login;
    _enviarEmailCodUsuario = codUsuario;
    $('#modalEnviarEmailLabel').text('Enviar login para ' + login + '?');
    $('#modalEnviarEmail').modal('show');
    return;
}

var _enviarEmailLogin = null;
var _enviarEmailCodUsuario = null;

/* ========== Logar Como Usuario ========== */
function confirmarLogarComoUsuario() {
    var codUsuario = $('#selectUsuarioTab4').val();
    var login = $('#selectUsuarioTab4 option:selected').data('login');
    if (!codUsuario || !login) { $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um usu&aacute;rio.</div>'); return; }
    confirmarLogarComo(login);
}

function confirmarEnviarEmailLogin() {
    if (!_enviarEmailLogin) return;
    $('#modalEnviarEmail').modal('hide');
    var login = _enviarEmailLogin;
    var codUsuario = _enviarEmailCodUsuario;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos',
            'm': 'enviarEmailLogin',
            'login': login,
            'cod_usuario': codUsuario,
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
        },
        complete: function() { stopLoading(); }
    });
}

function abrirModalAssociarInscrito() {
    var codUsuario = $('#selectUsuarioTab4').val();
    if (!codUsuario) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um usu\u00e1rio primeiro.</div>');
        return;
    }
    // Pegar nome e login do select
    var textoOption = $('#selectUsuarioTab4 option:selected').text().trim();
    var login = $('#selectUsuarioTab4 option:selected').data('login') || '';
    // textoOption formato: "login (nome)" — extrair nome
    var nomeUsuario = '';
    var match = textoOption.match(/\((.+)\)$/);
    if (match) nomeUsuario = match[1];

    $('#modalAssocInscritoMsg').html('');
    var html = '';
    html += '<div class="form-group"><label>Nome:</label><input type="text" id="assocNome" class="form-control" value="' + nomeUsuario + '"></div>';
    html += '<div class="row"><div class="form-group col-sm-5"><label>CPF:</label><input type="text" id="assocCpf" class="form-control" maxlength="14"></div>';
    html += '<div class="form-group col-sm-7"><label>Email:</label><input type="text" id="assocEmail" class="form-control" value="' + login + '"></div></div>';
    html += '<div class="form-group"><label>Perfil:</label><select id="assocPerfil" class="form-control"><option value="">Selecione...</option>';
    html += $('#assocPerfilOptions').html();
    html += '</select></div>';
    html += '<div id="assocResultado" style="padding:10px 0;"><span style="color:#999; font-style:italic;">Clique em "Verificar" para buscar inscri\u00e7\u00e3o existente.</span></div>';
    html += '<hr style="margin:10px 0;">';
    html += '<div style="display:flex; gap:8px; flex-wrap:wrap;">';
    html += '<button type="button" class="btn btn-sm" id="btnAssocVerificar" onclick="confirmarAssociarInscrito(false)" style="background:#2980b9; color:#fff; border:none; border-radius:6px; padding:6px 16px;"><i class="fa fa-search"></i> Verificar</button>';
    html += '<button type="button" class="btn btn-sm" id="btnAssocExistente" onclick="executarAssociar(false)" disabled style="background:#27ae60; color:#fff; border:none; border-radius:6px; padding:6px 16px; opacity:0.5;"><i class="fa fa-link"></i> Associar existente</button>';
    html += '<button type="button" class="btn btn-sm" id="btnAssocCriar" onclick="executarAssociar(true)" style="background:#8e44ad; color:#fff; border:none; border-radius:6px; padding:6px 16px;"><i class="fa fa-plus"></i> Criar novo e associar</button>';
    html += '</div>';
    $('#modalAssocInscritoBody').html(html);
    $('#modalAssociarInscrito').modal('show');
}

function confirmarAssociarInscrito(criarNovo) {
    var codUsuario = $('#selectUsuarioTab4').val();
    var nome = $.trim($('#assocNome').val());
    var email = $.trim($('#assocEmail').val());
    var cpf = $.trim($('#assocCpf').val());
    var codPerfil = $('#assocPerfil').val();

    if (!nome || !email || !codPerfil) {
        $('#modalAssocInscritoMsg').html('<div class="alert alert-danger">Preencha nome, email e perfil.</div>');
        return;
    }
    if (cpf && !validarCpfDigitos(cpf)) {
        $('#modalAssocInscritoMsg').html('<div class="alert alert-danger">CPF inv\u00e1lido.</div>');
        return;
    }
    if (!validarEmail(email)) {
        $('#modalAssocInscritoMsg').html('<div class="alert alert-danger">Email inv\u00e1lido.</div>');
        return;
    }

    // Se nao for criar novo, primeiro verificar se existe inscrito
    if (!criarNovo) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarGestaoAcessos', 'm': 'buscarInscritoParaAssociar',
                'cod_usuario': codUsuario, 'cod_evento': '<?php echo COD_EVENTO ?>',
                'email': email, 'cpf': cpf,
                '_submit_check': '1'
            },
            dataType: "json",
            beforeSend: function() {
                startLoading();
                $('#assocResultado').html('<span style="color:#999;"><i class="fa fa-spinner fa-spin"></i> Buscando...</span>');
            },
            success: function(data) {
                if (data.jaAssociado) {
                    $('#assocResultado').html('<div class="alert alert-success" style="margin-bottom:0;"><i class="fa fa-check-circle"></i> <b>J\u00e1 associado</b> ao inscrito <b>' + data.nomeInscrito + '</b> (c\u00f3d: ' + data.codInscrito + ')</div>');
                    $('#btnAssocExistente').prop('disabled', true).css('opacity','0.5');
                    $('#btnAssocCriar').prop('disabled', true).css('opacity','0.5');
                } else if (data.inscritoEncontrado) {
                    $('#assocResultado').html(
                        '<div class="alert alert-info" style="margin-bottom:0;"><i class="fa fa-check"></i> <b>Inscrito encontrado:</b> ' + data.nomeInscrito + ' (' + data.emailInscrito + ') - Perfil: ' + (data.perfilInscrito || '-') + '<br><small>C\u00f3d: ' + data.codInscrito + '</small></div>' +
                        '<input type="hidden" id="assocCodInscrito" value="' + data.codInscrito + '">'
                    );
                    $('#btnAssocExistente').prop('disabled', false).css('opacity','1');
                    $('#btnAssocCriar').prop('disabled', false).css('opacity','1');
                } else {
                    $('#assocResultado').html('<div class="alert alert-warning" style="margin-bottom:0;"><i class="fa fa-exclamation-circle"></i> <b>Nenhum inscrito encontrado</b> com este email/CPF neste evento.</div>');
                    $('#btnAssocExistente').prop('disabled', true).css('opacity','0.5');
                    $('#btnAssocCriar').prop('disabled', false).css('opacity','1');
                }
            },
            complete: function() { stopLoading(); }
        });
    } else {
        executarAssociar(true);
    }
}

function executarAssociar(criarNovo) {
    var codUsuario = $('#selectUsuarioTab4').val();
    var dados = {
        'c': 'GerenciarGestaoAcessos', 'm': 'associarInscrito',
        'cod_usuario': codUsuario, 'cod_evento': '<?php echo COD_EVENTO ?>',
        'criar_novo': criarNovo ? '1' : '0',
        '_submit_check': '1'
    };
    if (criarNovo) {
        dados.nome = $.trim($('#assocNome').val());
        dados.email = $.trim($('#assocEmail').val());
        dados.cpf = $.trim($('#assocCpf').val());
        dados.cod_perfil = $('#assocPerfil').val();
    } else {
        dados.cod_inscrito = $('#assocCodInscrito').val();
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: dados,
        dataType: "json",
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                $('#modalAssociarInscrito').modal('hide');
            } else {
                $('#modalAssocInscritoMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro') + '</div>');
            }
        },
        complete: function() { stopLoading(); }
    });
}

function desassociarInscrito() {
    var codUsuario = $('#selectUsuarioTab4').val();
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGestaoAcessos', 'm': 'desassociarInscrito',
            'cod_usuario': codUsuario, 'cod_evento': '<?php echo COD_EVENTO ?>',
            '_submit_check': '1'
        },
        dataType: "json",
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                $('#modalAssociarInscrito').modal('hide');
            }
        },
        complete: function() { stopLoading(); }
    });
}
</script>

<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_usuarios.php");
?>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 id="tituloPaginaUsuarios" style="margin:0; font-size:15px; font-weight:600; color:#333;">Eventos do Usu&aacute;rio</h4>
        <button class="btn btn-sm abriModal" type="button" onclick="abrirModalCadastrarLogin()">
            <i class="fa fa-user-plus"></i> Cadastrar Login
        </button>
    </div>
    <div style="padding:15px 20px;">

            <div id="carregando" class="shown hidden">
                <div class="imagem">
                    <div class="frase">Carregando...</div>
                    <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
                </div>
            </div>
            <div id="mensagem"></div>
            <div id="sucesso" class="hidden"></div>
            <div id="erro" class="hidden"></div>

            <!-- ABAS (controladas pelo submenu area_admin_menu_usuarios.php) -->
            <ul class="nav nav-tabs" role="tablist" style="display:none;">
                <li class="active"><a data-toggle="tab" href="#tabEventosUsuario"></a></li>
                <li><a data-toggle="tab" href="#tabUsuariosEvento"></a></li>
                <li><a data-toggle="tab" href="#tabEventosGestor"></a></li>
                <li><a data-toggle="tab" href="#tabUsuariosGestor"></a></li>
            </ul>

            <div class="tab-content" style="padding-top:15px;">

                <!-- ==================== Eventos do Usuario ==================== -->
                <div id="tabEventosUsuario" class="tab-pane fade in active">

                    <div class="row" style="margin-bottom:15px;">
                        <div class="col-sm-6">
                            <label for="selectUsuarioTab4">Usu&aacute;rio:</label>
                            <select id="selectUsuarioTab4" class="form-control">
                                <?php $objSRV->carregarSelectUsuarios($fws_, $debug); ?>
                            </select>
                        </div>
                        <div class="col-sm-5" id="divEnviarLogin" style="display:none;padding-top:24px;">
                            <button class="btn btn-sm" type="button" onclick="enviarEmailLogin()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2980b9; color:#fff; border:none;">
                                <i class="fa fa-envelope"></i> Enviar login por e-mail
                            </button>
                            <button class="btn btn-sm" type="button" onclick="confirmarLogarComoUsuario()" title="Logar como este usu&aacute;rio" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#f39c12; color:#fff; border:none;">
                                <i class="fa fa-sign-in"></i> Logar como
                            </button>
                            <?php if (@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)): ?>
                            <button class="btn btn-sm" type="button" onclick="abrirModalEditarUsuario()" title="Editar dados do usu&aacute;rio" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#f0f0f0; color:#555; border:1px solid #ddd;">
                                <i class="fa fa-pencil"></i> Editar
                            </button>
                            <button class="btn btn-sm" type="button" onclick="abrirModalAssociarInscrito()" title="Associar inscri&ccedil;&atilde;o ao usu&aacute;rio" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#8e44ad; color:#fff; border:none;">
                                <i class="fa fa-link"></i> Associar Inscrito
                            </button>
                            <button class="btn btn-sm" type="button" onclick="abrirModalTrocarSenha()" title="Gera uma nova senha aleat&oacute;ria para este usu&aacute;rio" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#c0392b; color:#fff; border:none;">
                                <i class="fa fa-key"></i> Trocar senha
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Form: Adicionar usuario a um evento -->
                    <div class="well well-sm" style="margin-bottom:15px;">
                        <b><i class="fa fa-plus"></i> Vincular usu&aacute;rio a um novo evento:</b>
                        <div class="row" style="margin-top:8px;">
                            <div class="col-sm-6">
                                <select id="selectEventoTab4" class="form-control">
                                    <option value="">Selecione um evento (somente eventos onde o usu&aacute;rio ainda n&atilde;o est&aacute; vinculado)</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-sm" type="button" onclick="adicionarUsuarioEvento()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped tablesorter" id="tblEventosUsuario">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th width="100px">Gestor</th>
                                <th>Grupos</th>
                                <th width="50px" style="text-align:center;">A&ccedil;&otilde;es</th>
                            </tr>
                        </thead>
                        <tbody class="eventosUsuario">
                            <tr><td colspan="4">Selecione um usu&aacute;rio.</td></tr>
                        </tbody>
                        <tfoot></tfoot>
                    </table>

                </div>

                <!-- ==================== Usuarios do Evento ==================== -->
                <div id="tabUsuariosEvento" class="tab-pane fade">

                    <div class="row" style="margin-bottom:15px;">
                        <div class="col-sm-6">
                            <label for="selectEventoTab3">Evento:</label>
                            <select id="selectEventoTab3" class="form-control">
                                <?php $objSRV->carregarSelectEventos($fws_, $debug); ?>
                            </select>
                        </div>
                    </div>

                    <!-- Form: Adicionar grupo ao usuario -->
                    <div class="well well-sm" style="margin-bottom:15px;">
                        <b><i class="fa fa-plus"></i> Adicionar grupo ao usu&aacute;rio:</b>
                        <div class="row" style="margin-top:8px;">
                            <div class="col-sm-4">
                                <select id="selectUsuarioTab3" class="form-control">
                                    <option value="">Selecione um usu&aacute;rio</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select id="selectGrupoTab3" class="form-control">
                                    <?php $objSRV->carregarSelectGrupos($fws_, $debug); ?>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-sm" type="button" onclick="adicionarGrupoUsuario()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Botão Importar Usuários -->
                    <div class="well well-sm" style="margin-bottom:15px;">
                        <b><i class="fa fa-download"></i> Importar usu&aacute;rios de outro evento:</b>
                        <div class="row" style="margin-top:8px;">
                            <div class="col-sm-4">
                                <select id="selectEventoImportar" class="form-control">
                                    <option value="">Selecione o evento de origem</option>
                                    <?php $objSRV->carregarSelectEventos($fws_, $debug); ?>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-sm" type="button" onclick="importarUsuariosEvento()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;">
                                    <i class="fa fa-download"></i> Importar
                                </button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped tablesorter" id="tblUsuariosEvento">
                        <thead>
                            <tr>
                                <th>Login</th>
                                <th>Nome</th>
                                <th>Grupos</th>
                                <th width="50px" style="text-align:center;">A&ccedil;&otilde;es</th>
                            </tr>
                        </thead>
                        <tbody class="usuariosEvento">
                            <tr><td colspan="4">Selecione um evento.</td></tr>
                        </tbody>
                        <tfoot></tfoot>
                    </table>

                </div>

                <!-- ==================== Eventos do Gestor ==================== -->
                <div id="tabEventosGestor" class="tab-pane fade">

                    <div class="row" style="margin-bottom:15px;">
                        <div class="col-sm-4">
                            <label for="selectGestor">&Oacute;rg&atilde;o Gestor:</label>
                            <select id="selectGestor" class="form-control" onchange="carregarTabelaEventosGestor()">
                                <?php $objSRV->carregarSelectGestores($fws_, $debug); ?>
                            </select>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped tablesorter" id="tblEventosGestor">
                        <thead>
                            <tr>
                                <th width="60px">C&oacute;d</th>
                                <th>Evento</th>
                                <th width="80px" style="text-align:center;">Usu&aacute;rios</th>
                                <th>Grupos &raquo; M&oacute;dulos configurados</th>
                            </tr>
                        </thead>
                        <tbody class="eventosGestor">
                            <tr><td colspan="4">Selecione um gestor.</td></tr>
                        </tbody>
                        <tfoot></tfoot>
                    </table>

                </div>

                <!-- ==================== Usuarios do Gestor ==================== -->
                <div id="tabUsuariosGestor" class="tab-pane fade">

                    <div class="row" style="margin-bottom:15px;">
                        <div class="col-sm-4">
                            <label for="selectGestorTab2">&Oacute;rg&atilde;o Gestor:</label>
                            <select id="selectGestorTab2" class="form-control" onchange="carregarTabelaUsuariosGestor()">
                                <?php $objSRV->carregarSelectGestores($fws_, $debug); ?>
                            </select>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped tablesorter" id="tblUsuariosGestor">
                        <thead>
                            <tr>
                                <th>Login</th>
                                <th>Nome</th>
                                <th width="80px" style="text-align:center;">Eventos</th>
                                <th>Eventos vinculados</th>
                            </tr>
                        </thead>
                        <tbody class="usuariosGestor">
                            <tr><td colspan="4">Selecione um gestor.</td></tr>
                        </tbody>
                        <tfoot></tfoot>
                    </table>

                </div>

            </div><!-- /tab-content -->

        </div>
    </div>
</div>

<!-- Modal: Gerenciar Grupos -->
<div class="modal fade" id="modalGrupos" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-users"></i> Gerenciar Grupos</h4>
            </div>
            <div class="modal-body" id="modalGruposBody">
                <p>Carregando...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Remover Grupo -->
<div class="modal fade" id="modalRemoverGrupo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Remo&ccedil;&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja remover este grupo do usu&aacute;rio?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarRemoverGrupo()"><i class="fa fa-trash"></i> Remover</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Remover Usuario do Evento -->
<div class="modal fade" id="modalRemoverUsuario" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Remo&ccedil;&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p id="modalRemoverUsuarioMsg"></p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarRemoverUsuarioEvento()"><i class="fa fa-trash"></i> Remover</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Envio de Email -->
<div class="modal fade" id="modalEnviarEmail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-envelope"></i> Enviar Login</h4>
            </div>
            <div class="modal-body">
                <p id="modalEnviarEmailLabel"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" onclick="confirmarEnviarEmailLogin()"><i class="fa fa-envelope"></i> Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cadastrar Login -->
<div class="modal fade" id="modalCadastrarLogin" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-user-plus"></i> Cadastrar Login</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="inputNomeLogin">Nome:</label>
                    <input type="text" class="form-control" id="inputNomeLogin" maxlength="255">
                </div>
                <div class="form-group">
                    <label for="inputLogin">Login (e-mail):</label>
                    <input type="text" class="form-control" id="inputLogin" maxlength="255">
                </div>
                <p class="text-muted" style="font-size:12px; margin-top:8px;">
                    <i class="fa fa-info-circle"></i> A senha ser&aacute; gerada automaticamente. Ap&oacute;s o cadastro, envie o e-mail com o link para o usu&aacute;rio definir sua pr&oacute;pria senha.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="cadastrarLogin()">
                    <i class="fa fa-check"></i> Cadastrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Usuário -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-pencil"></i> Editar Usu&aacute;rio</h4>
            </div>
            <div class="modal-body">
                <div id="modalEditarUsuarioMsg"></div>
                <input type="hidden" id="editUsuarioCod">
                <div class="form-group">
                    <label for="editUsuarioNome">Nome:</label>
                    <input type="text" class="form-control" id="editUsuarioNome" maxlength="255">
                </div>
                <div class="form-group">
                    <label for="editUsuarioLogin">Login (e-mail):</label>
                    <input type="text" class="form-control" id="editUsuarioLogin" maxlength="255">
                </div>
                <div class="form-group">
                    <label for="editUsuarioStatus">Situa&ccedil;&atilde;o:</label>
                    <select id="editUsuarioStatus" class="form-control">
                        <option value="ATIVADO">Ativo</option>
                        <option value="INATIVO">Inativo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editUsuarioCelular">Telefone/Celular:</label>
                    <input type="text" class="form-control" id="editUsuarioCelular" maxlength="20" placeholder="(00) 00000-0000">
                </div>
                <p class="text-muted" style="font-size:12px;">
                    <i class="fa fa-info-circle"></i> Usu&aacute;rios inativos n&atilde;o conseguem efetuar login no sistema.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEdicaoUsuario()">
                    <i class="fa fa-check"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Copiar Direitos -->
<div class="modal fade" id="modalCopiarDireitos" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#31708f;color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-copy"></i> Copiar Direitos de Outro Evento</h4>
            </div>
            <div class="modal-body">
                <div id="modalCopiarDireitosMsg"></div>
                <div class="form-group">
                    <label>Evento destino:</label>
                    <p class="form-control-static" style="font-weight:bold;" id="copiarDireitosEventoDestino"></p>
                    <input type="hidden" id="copiarDireitosEventoDestinoId">
                </div>
                <div class="form-group">
                    <label for="selectEventoOrigem">Copiar direitos de:</label>
                    <select id="selectEventoOrigem" class="form-control">
                        <option value="">Selecione o evento de origem</option>
                    </select>
                </div>
                <p class="text-muted" style="font-size:12px;">
                    <i class="fa fa-info-circle"></i> Os grupos do evento de origem ser&atilde;o copiados para o evento destino. Grupos j&aacute; existentes no destino ser&atilde;o mantidos.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="executarCopiarDireitos()">
                    <i class="fa fa-copy"></i> Copiar
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$logarComoController = 'GerenciarGestaoAcessos';
$logarComoMethod = 'logarComoUsuario';
require_once(dirname(__FILE__) . '/modal-logar-como.php');
?>

<style>
.nav-tabs > li > a {
    font-weight: bold;
}
.nav-tabs > li > a > i {
    margin-right: 4px;
}
.link-nav {
    color: #337ab7;
    cursor: pointer;
    text-decoration: none;
}
.link-nav:hover {
    text-decoration: underline;
    color: #23527c;
}
.badge-link {
    background-color: #337ab7;
    color: #fff;
    padding: 4px 10px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    text-decoration: none;
    display: inline-block;
}
.badge-link:hover {
    background-color: #23527c;
    color: #fff;
    text-decoration: none;
}
.link-badge {
    margin: 1px 2px;
    display: inline-block;
    cursor: pointer;
}
.link-badge:hover {
    opacity: 0.8;
    text-decoration: none;
    color: #fff;
}
.badge-link-static {
    background-color: #5bc0de;
    color: #fff;
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 13px;
    display: inline-block;
}
.well-sm {
    background-color: #f5f5f5;
    border: 1px solid #e3e3e3;
    border-radius: 3px;
    padding: 10px 15px;
}
/* Select2 — ajuste para ficar consistente com Bootstrap 3 */
.select2-container--default .select2-selection--single {
    height: 34px;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 20px;
    padding-left: 0;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 32px;
}
#sucesso,
#erro {
    display: none;
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    min-width: 350px;
    max-width: 600px;
    padding: 12px 20px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    font-size: 14px;
    text-align: center;
}
#sucesso.show {
    display: block;
    background-color: #dff0d8;
    border: 1px solid #d6e9c6;
    color: #3c763d;
}
#erro.show {
    display: block;
    background-color: #f2dede;
    border: 1px solid #ebccd1;
    color: #a94442;
}
/* Tablesorter - indicadores de ordenacao compativeis com Bootstrap */
table.tablesorter thead th.header {
    cursor: pointer;
    padding-right: 20px;
    position: relative;
}
table.tablesorter thead th.header:after {
    content: "\f0dc";
    font-family: FontAwesome;
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    color: #bbb;
    font-size: 12px;
}
table.tablesorter thead th.headerSortUp:after {
    content: "\f0de";
    color: #337ab7;
}
table.tablesorter thead th.headerSortDown:after {
    content: "\f0dd";
    color: #337ab7;
}
table.tablesorter thead th.header:hover {
    background-color: #e8e8e8;
}
</style>

<!-- Options de perfil para o modal Associar Inscrito (oculto) -->
<select id="assocPerfilOptions" style="display:none;">
    <?php try { CarregarOptionsAC::perfisCadAdmin($fws_, $debug); } catch(Exception $e) {} ?>
</select>

<!-- Modal Associar Inscrito -->
<div class="modal fade" id="modalAssociarInscrito" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-link"></i> Associar Inscrito</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalAssocInscritoMsg"></div>
                <div id="modalAssocInscritoBody"><p>Carregando...</p></div>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Trocar Senha (admin geral) - confirmacao destrutiva -->
<div class="modal fade" id="modalTrocarSenha" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#c0392b; color:#fff;">
                <h4 class="modal-title"><i class="fa fa-key"></i> Trocar senha do usu&aacute;rio</h4>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:0.9;"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalTrocarSenhaMsg"></div>
                <p>Voc&ecirc; est&aacute; prestes a gerar uma <b>nova senha aleat&oacute;ria</b> para:</p>
                <p style="font-size:15px;"><b id="trocaSenhaLogin"></b></p>
                <div class="alert alert-warning" style="margin:10px 0;">
                    <i class="fa fa-exclamation-triangle"></i>
                    Esta a&ccedil;&atilde;o:
                    <ul style="margin:6px 0 0 18px;">
                        <li>invalida a sess&atilde;o ativa do usu&aacute;rio (ele ser&aacute; deslogado)</li>
                        <li>obriga o usu&aacute;rio a definir nova senha no pr&oacute;ximo login</li>
                        <li>a senha gerada ser&aacute; mostrada <b>uma &uacute;nica vez</b> &mdash; anote ou copie</li>
                    </ul>
                </div>
                <div id="trocaSenhaAdminGeralBox" style="display:none;" class="alert alert-danger">
                    <i class="fa fa-shield"></i>
                    <b>Aten&ccedil;&atilde;o:</b> este usu&aacute;rio tamb&eacute;m &eacute; <b>administrador geral</b>.
                    <label style="display:block; margin-top:8px; font-weight:normal;">
                        <input type="checkbox" id="trocaSenhaConfirmarAG"> Confirmo que desejo trocar a senha de outro administrador geral
                    </label>
                </div>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarTrocarSenha"><i class="fa fa-key"></i> Gerar nova senha</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Resultado: senha gerada (exibida 1 unica vez) -->
<div class="modal fade" id="modalSenhaGerada" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#27ae60; color:#fff;">
                <h4 class="modal-title"><i class="fa fa-check"></i> Senha gerada</h4>
            </div>
            <div class="modal-body">
                <p>Nova senha de <b id="senhaGeradaLogin"></b>:</p>
                <div style="display:flex; gap:8px; align-items:center; margin:10px 0;">
                    <input type="text" id="senhaGeradaValor" class="form-control" readonly style="font-family:Consolas,Monaco,monospace; font-size:18px; font-weight:bold; letter-spacing:1px; background:#f8f9fa;">
                    <button type="button" class="btn btn-default" id="btnCopiarSenha" title="Copiar senha"><i class="fa fa-copy"></i> Copiar</button>
                </div>
                <div class="alert alert-warning" style="margin:10px 0 0;">
                    <i class="fa fa-info-circle"></i>
                    Esta senha <b>n&atilde;o ser&aacute; exibida novamente</b>. Anote ou copie agora.<br>
                    No pr&oacute;ximo login o usu&aacute;rio ser&aacute; obrigado a redefini-la.
                </div>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function abrirModalTrocarSenha() {
    var codUsuario = $('#selectUsuarioTab4').val();
    var loginTxt   = $('#selectUsuarioTab4 option:selected').text();
    if (!codUsuario) {
        alert('Selecione um usuário primeiro.');
        return;
    }
    $('#modalTrocarSenhaMsg').html('');
    $('#trocaSenhaLogin').text(loginTxt);
    $('#trocaSenhaAdminGeralBox').hide();
    $('#trocaSenhaConfirmarAG').prop('checked', false);
    $('#btnConfirmarTrocarSenha').data('codUsuario', codUsuario);
    $('#modalTrocarSenha').modal('show');
}

$(document).on('click', '#btnConfirmarTrocarSenha', function() {
    var codUsuario = $(this).data('codUsuario');
    var confirmarAG = $('#trocaSenhaConfirmarAG').is(':checked') ? '1' : '0';
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Gerando...');

    $.ajax({
        type: 'POST',
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            c: 'GerenciarGestaoAcessos',
            m: 'trocarSenhaUsuario',
            codUsuario: codUsuario,
            confirmarAdminGeral: confirmarAG,
            _submit_check: '1'
        },
        dataType: 'json',
        cache: false,
        success: function(data) {
            $btn.prop('disabled', false).html('<i class="fa fa-key"></i> Gerar nova senha');
            if (data.statusMessage == '1') {
                $('#modalTrocarSenha').modal('hide');
                $('#senhaGeradaLogin').text(data.login || '');
                $('#senhaGeradaValor').val(data.senha || '');
                $('#modalSenhaGerada').modal('show');
            } else if (data.requerConfirmacao) {
                $('#trocaSenhaAdminGeralBox').show();
                $('#modalTrocarSenhaMsg').html(
                    '<div class="alert alert-warning"><i class="fa fa-info-circle"></i> ' +
                    (data.message || 'Confirma&ccedil;&atilde;o necess&aacute;ria.') + '</div>');
            } else {
                $('#modalTrocarSenhaMsg').html(
                    '<div class="alert alert-danger">' + (data.message || 'Erro.') + '</div>');
            }
        },
        error: function() {
            $btn.prop('disabled', false).html('<i class="fa fa-key"></i> Gerar nova senha');
            $('#modalTrocarSenhaMsg').html(
                '<div class="alert alert-danger">Erro de comunica&ccedil;&atilde;o.</div>');
        }
    });
});

$(document).on('click', '#btnCopiarSenha', function() {
    var input = document.getElementById('senhaGeradaValor');
    input.select();
    input.setSelectionRange(0, 99999);
    try {
        document.execCommand('copy');
        var $b = $(this).html('<i class="fa fa-check"></i> Copiado!');
        setTimeout(function() { $b.html('<i class="fa fa-copy"></i> Copiar'); }, 1500);
    } catch (e) {
        alert('Selecione e copie manualmente.');
    }
});
</script>
