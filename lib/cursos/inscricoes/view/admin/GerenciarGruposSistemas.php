<?php
require_once(dirname(__FILE__) . "/../../view/admin/seguranca-adm-geral.php");
$objSRV = new GerenciarGruposSistemasAC();
$objModulos = new GerenciarModulosAC();
?>
<link href="resources/css/select2.min.css" rel="stylesheet" />
<script src="resources/js/select2/select2.min.js"></script>
<script src="resources/js/select2/pt-BR.js"></script>
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // Inicializar tablesorter (coluna Ações sem ordenação)
    $("#tblGrupos").tablesorter({ headers: { 4: { sorter: false } } });
    $("#tblSistemas").tablesorter({ headers: { 3: { sorter: false } } });
    $("#tblModulos").tablesorter({ headers: { 5: { sorter: false } } });

    // Inicializar Select2 nos selects de evento
    if ($.fn.select2) {
        $('#selectEventoModulo').select2({
            placeholder: 'Digite para buscar um evento',
            allowClear: true,
            language: 'pt-BR',
            width: '100%'
        });
        $('#selectEventoOrigem').select2({
            placeholder: 'Digite para buscar o evento de origem',
            allowClear: true,
            language: 'pt-BR',
            width: '100%'
        });
    }

    carregarTabelaGrupos();
    carregarTabelaSistemas();

    // Fechar mensagem ao clicar
    $('#sucesso, #erro').click(function() {
        clearTimeout(_msgTimer);
        $(this).stop(true).fadeOut(200);
    });

    // Toggle da seção copiar módulos
    $('.section-toggle').click(function() {
        var target = $(this).data('target');
        $(target).slideToggle(300);
        $(this).find('i.fa').toggleClass('fa-angle-right fa-angle-down');
    });

    // Filtro ao digitar no campo de nome
    $('#nome_grupo').on('keyup', function() {
        filtrarTabela($(this).val(), '#tblGrupos tbody.gruposBody tr', 1);
    });
    $('#nome_sistema').on('keyup', function() {
        filtrarTabela($(this).val(), '#tblSistemas tbody.sistemasBody tr', 1);
    });
});

/* ========== MENSAGENS ========== */
function startLoading() { loading('div#carregando', 'show'); }
function stopLoading()  { loading('div#carregando', 'hidden'); }

/* ========== PREVIEW ENUM ========== */
function nomeParaConstante(nome) {
    // Mesma logica do PHP: remover acentos, maiusculo, substituir nao-alfanumericos por _
    return nome.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .toUpperCase()
        .replace(/[^A-Z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '')
        .replace(/_+/g, '_');
}

function atualizarEnumCampo(inputId, prefixo, enumInputId) {
    var nome = $.trim($('#' + inputId).val());
    var enumInput = $('#' + enumInputId);
    // So preenche automaticamente se o campo estiver vazio ou se o valor atual parece auto-gerado
    var valorAtual = enumInput.val();
    if (nome && (!valorAtual || enumInput.data('auto'))) {
        enumInput.val(prefixo + nomeParaConstante(nome));
        enumInput.data('auto', true);
    }
    if (!nome) {
        enumInput.val('');
        enumInput.data('auto', true);
    }
}
function marcarEnumManual(enumInputId) {
    $('#' + enumInputId).data('auto', false);
}

/* ========== FILTRO DE TABELA ========== */
function filtrarTabela(termo, seletor, coluna) {
    var filtro = termo.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    $(seletor).each(function() {
        var texto = $(this).find('td').eq(coluna).text().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        $(this).toggle(texto.indexOf(filtro) > -1);
    });
}

/* ========== GRUPOS ========== */
function carregarTabelaGrupos() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGruposSistemas',
        'm': 'carregarTabelaGrupos',
        '_submit_check': '1'
    }, function(html) {
        $("tbody.gruposBody").html(html);
        $("#tblGrupos").trigger("update");
    });
}

function editarGrupo(codGrupo, nomeGrupo, enumGrupo) {
    $('#cod_grupo').val(codGrupo);
    $('#nome_grupo').val(nomeGrupo);
    $('#enum_grupo').val(enumGrupo || '');
    $('#enum_grupo').data('auto', false);
    $('#btnCancelarGrupo').show();
    $('#nome_grupo').focus();
}

function limparFormGrupo() {
    $('#cod_grupo').val('');
    $('#nome_grupo').val('');
    $('#enum_grupo').val('');
    $('#enum_grupo').data('auto', true);
    $('#btnCancelarGrupo').hide();
    $('#tblGrupos tbody.gruposBody tr').show();
}

function salvarGrupo() {
    var nomeGrupo = $.trim($('#nome_grupo').val());
    if (!nomeGrupo) { alert('Informe o nome do grupo.'); return; }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGruposSistemas',
            'm': 'salvarGrupo',
            'cod_grupo': $('#cod_grupo').val(),
            'nome_grupo': nomeGrupo,
            'enum_grupo': $.trim($('#enum_grupo').val()),
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                limparFormGrupo();
                carregarTabelaGrupos();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function excluirGrupo(codGrupo, nomeGrupo) {
    if (!confirm('Deseja excluir o grupo "' + nomeGrupo + '"?')) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGruposSistemas',
            'm': 'excluirGrupo',
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
                carregarTabelaGrupos();
            }
        },
        complete: function() { stopLoading(); }
    });
}

/* ========== SISTEMAS ========== */
function carregarTabelaSistemas() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarGruposSistemas',
        'm': 'carregarTabelaSistemas',
        '_submit_check': '1'
    }, function(html) {
        $("tbody.sistemasBody").html(html);
        $("#tblSistemas").trigger("update");
    });
}

function editarSistema(codSistema, nomeSistema, enumSistema) {
    $('#cod_sistema').val(codSistema);
    $('#nome_sistema').val(nomeSistema);
    $('#enum_sistema').val(enumSistema || '');
    $('#enum_sistema').data('auto', false);
    $('#btnCancelarSistema').show();
    $('#nome_sistema').focus();
}

function limparFormSistema() {
    $('#cod_sistema').val('');
    $('#nome_sistema').val('');
    $('#enum_sistema').val('');
    $('#enum_sistema').data('auto', true);
    $('#btnCancelarSistema').hide();
    $('#tblSistemas tbody.sistemasBody tr').show();
}

function salvarSistema() {
    var nomeSistema = $.trim($('#nome_sistema').val());
    if (!nomeSistema) { alert('Informe o nome do sistema.'); return; }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGruposSistemas',
            'm': 'salvarSistema',
            'cod_sistema': $('#cod_sistema').val(),
            'nome_sistema': nomeSistema,
            'enum_sistema': $.trim($('#enum_sistema').val()),
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                limparFormSistema();
                carregarTabelaSistemas();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function excluirSistema(codSistema, nomeSistema) {
    if (!confirm('Deseja excluir o sistema "' + nomeSistema + '"?')) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGruposSistemas',
            'm': 'excluirSistema',
            'cod_sistema': codSistema,
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaSistemas();
            }
        },
        complete: function() { stopLoading(); }
    });
}

/* ========== MODULOS ========== */
function carregarTabelaModulos() {
    var codEvento = $('#selectEventoModulo').val();
    if (!codEvento) {
        $("tbody.modulosBody").html('<tr><td colspan="6">Selecione um evento.</td></tr>');
        return;
    }

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarModulos',
        'm': 'carregarTabelaModulos',
        'cod_evento': codEvento,
        '_submit_check': '1'
    }, function(html) {
        $("tbody.modulosBody").html(html);
        // Mover linha de totais para tfoot (não participa da ordenação)
        var $totalRow = $("tbody.modulosBody tr.totalRow").detach();
        $("#tblModulosFoot").html($totalRow);
        $("#tblModulos").trigger("update");
    });
}

function adicionarModulo() {
    var codEvento  = $('#selectEventoModulo').val();
    var codGrupo   = $('#selectGrupoModulo').val();
    var codSistema = $('#selectSistemaModulo').val();
    var codDireito = $('#selectDireitoModulo').val();

    if (!codEvento)  { alert('Selecione um evento.'); return; }
    if (!codGrupo)   { alert('Selecione um grupo.'); return; }
    if (!codSistema) { alert('Selecione um sistema/m\u00f3dulo.'); return; }
    if (!codDireito) { alert('Selecione um direito.'); return; }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'cod_evento': codEvento,
            'cod_grupo': codGrupo,
            'cod_sistema': codSistema,
            'cod_direito': codDireito,
            'c': 'GerenciarModulos',
            'm': 'adicionarModulo',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaModulos();
            }
        },
        error: function() {
            mostrarMensagem({statusMessage: '', message: 'Erro de comunica\u00e7\u00e3o com o servidor.'});
        },
        complete: function() { stopLoading(); }
    });
}

function excluirModulo(codGrupo, codSistema, codDireito) {
    if (!confirm('Deseja realmente excluir este m\u00f3dulo?')) return;

    var codEvento = $('#selectEventoModulo').val();

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'cod_evento': codEvento,
            'cod_grupo': codGrupo,
            'cod_sistema': codSistema,
            'cod_direito': codDireito,
            'c': 'GerenciarModulos',
            'm': 'excluirModulo',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaModulos();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function copiarModulosEvento() {
    var codEventoDestino = $('#selectEventoModulo').val();
    var codEventoOrigem  = $('#selectEventoOrigem').val();
    var copiarGrupos  = $('#chkCopiarGrupos').is(':checked') ? '1' : '0';
    var copiarModulos = $('#chkCopiarModulos').is(':checked') ? '1' : '0';

    if (!codEventoOrigem)  { alert('Selecione o evento de origem.'); return; }
    if (!codEventoDestino) { alert('Selecione o evento de destino (evento principal).'); return; }
    if (codEventoOrigem == codEventoDestino) {
        alert('Evento de origem e destino n\u00e3o podem ser iguais.');
        return;
    }
    if (copiarGrupos == '0' && copiarModulos == '0') {
        alert('Selecione ao menos uma op\u00e7\u00e3o para copiar.');
        return;
    }

    var itens = [];
    if (copiarGrupos == '1') itens.push('Grupos');
    if (copiarModulos == '1') itens.push('M\u00f3dulos');
    if (!confirm('Copiar ' + itens.join(' e ') + ' do evento de origem para o evento selecionado?')) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'cod_evento_origem': codEventoOrigem,
            'cod_evento_destino': codEventoDestino,
            'copiar_grupos': copiarGrupos,
            'copiar_modulos': copiarModulos,
            'c': 'GerenciarModulos',
            'm': 'copiarModulosEvento',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaModulos();
                if (copiarGrupos == '1') carregarTabelaGrupos();
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

if ($moduloIndisponivel) {
    require_once(dirname(__FILE__)."/modulo-indisponivel.php");
    return;
}
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 id="tituloPaginaUsuarios" style="margin:0; font-size:15px; font-weight:600; color:#333;">Grupos</h4>
    </div>
    <div style="padding:15px 20px;">

            <div id="carregando" class="shown hidden">
                <div class="imagem">
                    <div class="frase">Carregando...</div>
                    <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
                </div>
            </div>
            <div id="mensagem"></div>

            <!-- ABAS (controladas pelo submenu area_admin_menu_usuarios.php) -->
            <ul class="nav nav-tabs" role="tablist" style="display:none;">
                <li class="active">
                    <a data-toggle="tab" href="#tabGrupos"></a>
                </li>
                <li>
                    <a data-toggle="tab" href="#tabSistemas"></a>
                </li>
                <li>
                    <a data-toggle="tab" href="#tabModulos"></a>
                </li>
            </ul>

            <div class="tab-content" style="padding-top:15px;">

                <!-- ==================== TAB 1: Grupos ==================== -->
                <div id="tabGrupos" class="tab-pane fade in active">

                    <!-- Formulario inline -->
                    <div class="well well-sm" style="margin-bottom:15px;">
                        <b><i class="fa fa-plus"></i> Adicionar / Editar Grupo:</b>
                        <div class="row" style="margin-top:8px;">
                            <input type="hidden" id="cod_grupo" value="">
                            <div class="col-sm-4">
                                <label for="nome_grupo" style="font-weight:normal; font-size:11px; margin-bottom:2px;">Nome do grupo</label>
                                <input type="text" id="nome_grupo" class="form-control" placeholder="Ex: Coordenadores ProRede" maxlength="100" oninput="atualizarEnumCampo('nome_grupo','GR_','enum_grupo')">
                            </div>
                            <div class="col-sm-4">
                                <label for="enum_grupo" style="font-weight:normal; font-size:11px; margin-bottom:2px;">Identificador no c&oacute;digo <i class="fa fa-info-circle text-muted" title="Nome usado internamente pelo sistema para referenciar este grupo. Gerado automaticamente, mas pode ser ajustado."></i></label>
                                <input type="text" id="enum_grupo" class="form-control" placeholder="GR_NOME" maxlength="100" oninput="marcarEnumManual('enum_grupo')" style="font-family:monospace; font-size:12px;">
                            </div>
                            <div class="col-sm-3">
                                <label style="font-weight:normal; font-size:11px; margin-bottom:2px;">&nbsp;</label><br>
                                <button class="btn btn-sm" type="button" onclick="salvarGrupo()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;">
                                    <i class="fa fa-save"></i> Salvar
                                </button>
                                <button class="btn btn-sm" type="button" id="btnCancelarGrupo" onclick="limparFormGrupo()" style="display:none; border-radius:5px; padding:5px 14px; font-size:12px; background:#f0f0f0; color:#555; border:1px solid #ddd;">
                                    <i class="fa fa-times"></i> Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped tablesorter" id="tblGrupos">
                        <thead>
                            <tr>
                                <th width="60px">C&oacute;d</th>
                                <th>Nome do Grupo</th>
                                <th>Identificador</th>
                                <th width="100px" style="text-align:center;">A&ccedil;&otilde;es</th>
                            </tr>
                        </thead>
                        <tbody class="gruposBody">
                            <tr><td colspan="4">Carregando...</td></tr>
                        </tbody>
                    </table>

                </div>

                <!-- ==================== TAB 2: Sistemas ==================== -->
                <div id="tabSistemas" class="tab-pane fade">

                    <!-- Formulario inline -->
                    <div class="well well-sm" style="margin-bottom:15px;">
                        <b><i class="fa fa-plus"></i> Adicionar / Editar Sistema:</b>
                        <div class="row" style="margin-top:8px;">
                            <input type="hidden" id="cod_sistema" value="">
                            <div class="col-sm-4">
                                <label for="nome_sistema" style="font-weight:normal; font-size:11px; margin-bottom:2px;">Nome do sistema</label>
                                <input type="text" id="nome_sistema" class="form-control" placeholder="Ex: Pedagogico Supervisor" maxlength="100" oninput="atualizarEnumCampo('nome_sistema','SIS_','enum_sistema')">
                            </div>
                            <div class="col-sm-4">
                                <label for="enum_sistema" style="font-weight:normal; font-size:11px; margin-bottom:2px;">Identificador no c&oacute;digo <i class="fa fa-info-circle text-muted" title="Nome usado internamente pelo sistema para referenciar este m&oacute;dulo. Gerado automaticamente, mas pode ser ajustado."></i></label>
                                <input type="text" id="enum_sistema" class="form-control" placeholder="SIS_NOME" maxlength="100" oninput="marcarEnumManual('enum_sistema')" style="font-family:monospace; font-size:12px;">
                            </div>
                            <div class="col-sm-3">
                                <label style="font-weight:normal; font-size:11px; margin-bottom:2px;">&nbsp;</label><br>
                                <button class="btn btn-sm" type="button" onclick="salvarSistema()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;">
                                    <i class="fa fa-save"></i> Salvar
                                </button>
                                <button class="btn btn-sm" type="button" id="btnCancelarSistema" onclick="limparFormSistema()" style="display:none; border-radius:5px; padding:5px 14px; font-size:12px; background:#f0f0f0; color:#555; border:1px solid #ddd;">
                                    <i class="fa fa-times"></i> Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped tablesorter" id="tblSistemas">
                        <thead>
                            <tr>
                                <th width="60px">C&oacute;d</th>
                                <th>Nome do Sistema</th>
                                <th>Identificador</th>
                                <th width="100px" style="text-align:center;">A&ccedil;&otilde;es</th>
                            </tr>
                        </thead>
                        <tbody class="sistemasBody">
                            <tr><td colspan="3">Carregando...</td></tr>
                        </tbody>
                    </table>

                </div>

                <!-- ==================== TAB 3: Permissões ==================== -->
                <div id="tabModulos" class="tab-pane fade">

                    <!-- SELECT: Evento -->
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-sm-6">
                            <label for="selectEventoModulo">Evento:</label>
                            <select id="selectEventoModulo" name="cod_evento" class="form-control" onchange="carregarTabelaModulos()">
                                <option value=""></option>
                                <?php $objModulos->carregarSelectEventos($fws_, $debug); ?>
                            </select>
                        </div>
                    </div>

                    <!-- FORM: Adicionar Modulo -->
                    <div class="well well-sm" style="margin-bottom:15px;">
                        <b><i class="fa fa-plus"></i> Adicionar Permiss&atilde;o:</b>
                        <div class="row" style="margin-top:8px;">
                            <div class="col-sm-3">
                                <label for="selectGrupoModulo">Grupo:</label>
                                <select id="selectGrupoModulo" name="cod_grupo" class="form-control">
                                    <?php $objModulos->carregarSelectGrupos($fws_, $debug); ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label for="selectSistemaModulo">Sistema:</label>
                                <select id="selectSistemaModulo" name="cod_sistema" class="form-control">
                                    <?php $objModulos->carregarSelectSistemas($fws_, $debug); ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label for="selectDireitoModulo">Direito:</label>
                                <select id="selectDireitoModulo" name="cod_direito" class="form-control">
                                    <option value="">Selecione um direito</option>
                                    <option value="1">Leitura</option>
                                    <option value="2">Adicionar</option>
                                    <option value="3">Alterar</option>
                                    <option value="4">Excluir</option>
                                    <option value="5">Administrar</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label>&nbsp;</label><br>
                                <button class="btn btn-sm" type="button" onclick="adicionarModulo()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TABELA: Permissões do Evento -->
                    <table class="table table-striped table-bordered tablesorter" id="tblModulos">
                        <thead>
                            <tr>
                                <th width="50px">Cód</th>
                                <th>Grupo</th>
                                <th width="50px">Cód</th>
                                <th>Sistema</th>
                                <th>Direito</th>
                                <th width="80px" style="text-align:center;">A&ccedil;&otilde;es</th>
                            </tr>
                        </thead>
                        <tbody class="modulosBody">
                            <tr><td colspan="6">Selecione um evento.</td></tr>
                        </tbody>
                        <tfoot id="tblModulosFoot"></tfoot>
                    </table>

                    <!-- COPIAR DE OUTRO EVENTO -->
                    <div class="section-toggle" data-target="#secCopiar" style="cursor:pointer;padding:8px 0;font-weight:bold;color:#337ab7;">
                        <i class="fa fa-angle-right"></i> Copiar configura&ccedil;&atilde;o de outro evento
                    </div>
                    <div id="secCopiar" style="display:none;">
                        <div class="row" style="margin-top:10px;">
                            <div class="col-sm-5">
                                <label for="selectEventoOrigem">Evento de Origem:</label>
                                <select id="selectEventoOrigem" name="cod_evento_origem" class="form-control">
                                    <option value=""></option>
                                    <?php $objModulos->carregarSelectEventos($fws_, $debug); ?>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label>O que copiar:</label>
                                <div>
                                    <label class="checkbox-inline" style="font-weight:normal;">
                                        <input type="checkbox" id="chkCopiarGrupos" value="1" checked> Grupos
                                    </label>
                                    <label class="checkbox-inline" style="font-weight:normal;">
                                        <input type="checkbox" id="chkCopiarModulos" value="1" checked> M&oacute;dulos (direitos)
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label>&nbsp;</label><br>
                                <button class="btn btn-sm" type="button" onclick="copiarModulosEvento()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#f39c12; color:#fff; border:none;">
                                    <i class="fa fa-copy"></i> Copiar para evento selecionado
                                </button>
                            </div>
                        </div>
                        <p class="text-muted" style="margin-top:8px; font-size:12px;">
                            <i class="fa fa-info-circle"></i> S&oacute; acrescenta o que n&atilde;o existe no evento destino. Registros existentes s&atilde;o ignorados.
                        </p>
                    </div>

                </div>

            </div><!-- /tab-content -->

        </div>
    </div>
</div>

<style>
.well-sm {
    background-color: #f5f5f5;
    border: 1px solid #e3e3e3;
    border-radius: 3px;
    padding: 10px 15px;
}
.nav-tabs > li > a {
    font-weight: bold;
}
.nav-tabs > li > a > i {
    margin-right: 4px;
}
#sucesso,
#erro {
    position: fixed;
    top: 15px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    min-width: 450px;
    max-width: 700px;
    padding: 18px 30px;
    border-radius: 6px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    cursor: pointer;
}
#sucesso {
    background-color: #dff0d8;
    border: 1px solid #d6e9c6;
    color: #3c763d;
}
#erro {
    background-color: #f2dede;
    border: 1px solid #ebccd1;
    color: #a94442;
}
/* Tablesorter - indicadores de ordenacao */
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
.section-toggle:hover {
    text-decoration: underline;
}
</style>
