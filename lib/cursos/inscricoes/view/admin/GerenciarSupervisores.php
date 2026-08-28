<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
if (!$_isAdminPedagogico) {
    require_once(dirname(__FILE__)."/acesso-negado.php");
    exit;
}
$objSRV = new GerenciarSupervisorAC();
?>
<style>.campo-invalido { border-color: #d9534f !important; box-shadow: 0 0 4px rgba(217,83,79,0.4) !important; }</style>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    carregarTabelaSupervisores();
    $(document).on('change keyup', '#modalNovo input', function() {
        $(this).removeClass('campo-invalido');
    });
});

function carregarTabelaSupervisores() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarSupervisor', 'm': 'carregarTabelaSupervisores', '_submit_check': '1'},
        function(html) {
            $("table#tabelaSupervisores tbody.inputs tr").remove();
            $("table#tabelaSupervisores tbody.inputs").append(html);
            bindAcoes();
        }
    );
}

function bindAcoes() {
    $('.btn-resetar-senha').off('click').on('click', function() {
        var codUsuario = $(this).parents("tr").data("cod-usuario");
        var nome = $(this).parents("tr").find("td:first").text();
        _resetarCodUsuario = codUsuario;
        $('#resetarNomeSupervisor').text(nome);
        $('#modalResetarSenha').modal('show');
    });
    $('.btn-editar-supervisor').off('click').on('click', function() {
        var tr = $(this).parents("tr");
        _editarCodUsuario = tr.data("cod-usuario");
        $('#editarNome').val(tr.data("nome"));
        $('#editarCpf').val(tr.data("cpf"));
        $('#editarCelular').val(tr.data("celular"));
        $('#modalEditarMsg').html('');
        $('#modalEditar').modal('show');
    });
    $('.btn-historico-acessos').off('click').on('click', function() {
        var codUsuario = $(this).parents("tr").data("cod-usuario");
        var nome = $(this).parents("tr").data("nome");
        abrirHistoricoAcessos(codUsuario, nome);
    });
    $('.btn-remover-supervisor').off('click').on('click', function() {
        var codUsuario = $(this).parents("tr").data("cod-usuario");
        var nome = $(this).parents("tr").find("td:first").text();
        _removerCodUsuario = codUsuario;
        $('#removerNomeSupervisor').text(nome);
        $('#modalRemoverSupervisor').modal('show');
    });
}

function abrirModalNovo() {
    $('#novoNome').val('');
    $('#novoLogin').val('');
    $('#novoCpf').val('');
    $('#novoCelular').val('');
    $('#modalNovoMsg').html('');
    $('#modalNovo').modal('show');
}

function cadastrarSupervisor() {
    var nome  = $.trim($('#novoNome').val());
    var login = $.trim($('#novoLogin').val());

    var cpf     = $.trim($('#novoCpf').val());
    var celular = $.trim($('#novoCelular').val());

    // Limpar marcacao anterior
    $('#modalNovo .campo-invalido').removeClass('campo-invalido');
    var camposFaltando = [];
    if (!nome)  { camposFaltando.push('Nome'); $('#novoNome').addClass('campo-invalido'); }
    if (!login) { camposFaltando.push('Login'); $('#novoLogin').addClass('campo-invalido'); }
    if (!cpf)   { camposFaltando.push('CPF'); $('#novoCpf').addClass('campo-invalido'); }
    if (camposFaltando.length > 0) {
        $('#modalNovoMsg').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-exclamation-triangle"></i> Preencha: ' + camposFaltando.join(', ') + '</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarSupervisor', 'm': 'cadastrarSupervisor', 'nome': nome, 'login': login, 'cpf': $.trim($('#novoCpf').val()), 'celular': $.trim($('#novoCelular').val()), '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                var msgExtra = '';
                if (data.codUsuario && (data.login || login)) {
                    var _login = data.login || login;
                    var _codUsuario = data.codUsuario;
                    msgExtra = ' <button class="btn btn-xs btn-info" onclick="enviarEmailSupervisor(\'' + _login + '\',' + _codUsuario + ')"><i class="fa fa-envelope"></i> Enviar login por e-mail</button>';
                }
                $('#novoNome').val('');
                $('#novoLogin').val('');
                $('#novoCpf').val('');
                $('#novoCelular').val('');
                $('#modalNovoMsg').html('<div class="alert alert-success"><i class="fa fa-check"></i> ' + data.message + msgExtra + '</div>');
                carregarTabelaSupervisores();
            } else {
                $('#modalNovoMsg').html('<div class="alert alert-danger"><i class="fa fa-times"></i> ' + (data.message || 'Erro ao cadastrar.') + '</div>');
            }
        },
        error: function() {
            $('#modalNovoMsg').html('<div class="alert alert-danger">Erro de comunica&ccedil;&atilde;o.</div>');
        },
        complete: function() { stopLoading(); }
    });
}

var _resetarCodUsuario = null;

function confirmarResetarSenha() {
    if (!_resetarCodUsuario) return;
    $('#modalResetarSenha').modal('hide');
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarSupervisor', 'm': 'resetarSenha', 'codUsuario': _resetarCodUsuario, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                $('#mensagem').html('<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-check"></i> ' + data.message + '</div>');
            } else {
                $('#mensagem').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + (data.message || 'Erro.') + '</div>');
            }
        },
        complete: function() { stopLoading(); _resetarCodUsuario = null; }
    });
}

function enviarEmailSupervisor(login, codUsuario) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarSupervisor', 'm': 'enviarEmailBoasVindas', 'login': login, 'codUsuario': codUsuario, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                $('#modalNovoMsg').html('<div class="alert alert-success"><i class="fa fa-check"></i> ' + data.message + '</div>');
            } else {
                $('#modalNovoMsg').html('<div class="alert alert-danger"><i class="fa fa-times"></i> ' + (data.message || 'Erro ao enviar.') + '</div>');
            }
        },
        complete: function() { stopLoading(); }
    });
}

var _editarCodUsuario = null;

function salvarEdicaoSupervisor() {
    if (!_editarCodUsuario) return;
    var nome    = $.trim($('#editarNome').val());
    var cpf     = $.trim($('#editarCpf').val());
    var celular = $.trim($('#editarCelular').val());

    if (!nome) {
        $('#modalEditarMsg').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-exclamation-triangle"></i> Nome &eacute; obrigat&oacute;rio.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarSupervisor', 'm': 'editarSupervisor', 'codUsuario': _editarCodUsuario, 'nome': nome, 'cpf': cpf, 'celular': celular, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                $('#modalEditar').modal('hide');
                $('#mensagem').html('<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-check"></i> ' + data.message + '</div>');
                carregarTabelaSupervisores();
            } else {
                $('#modalEditarMsg').html('<div class="alert alert-danger"><i class="fa fa-times"></i> ' + (data.message || 'Erro ao salvar.') + '</div>');
            }
        },
        error: function() {
            $('#modalEditarMsg').html('<div class="alert alert-danger">Erro de comunica&ccedil;&atilde;o.</div>');
        },
        complete: function() { stopLoading(); }
    });
}

var _removerCodUsuario = null;

function confirmarRemoverSupervisor() {
    if (!_removerCodUsuario) return;
    $('#modalRemoverSupervisor').modal('hide');
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarSupervisor', 'm': 'removerSupervisor', 'codUsuario': _removerCodUsuario, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaSupervisores();
            }
        },
        complete: function() { stopLoading(); _removerCodUsuario = null; }
    });
}

function mascaraCelular(input) {
    var v = input.value.replace(/\D/g, '');
    if (v.length > 11) v = v.substring(0, 11);
    if (v.length > 6) {
        input.value = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7);
    } else if (v.length > 2) {
        input.value = '(' + v.substring(0,2) + ') ' + v.substring(2);
    } else if (v.length > 0) {
        input.value = '(' + v;
    }
}

function mascaraCpf(input) {
    var v = input.value.replace(/\D/g, '');
    if (v.length > 11) v = v.substring(0, 11);
    if (v.length > 9) {
        input.value = v.substring(0,3) + '.' + v.substring(3,6) + '.' + v.substring(6,9) + '-' + v.substring(9);
    } else if (v.length > 6) {
        input.value = v.substring(0,3) + '.' + v.substring(3,6) + '.' + v.substring(6);
    } else if (v.length > 3) {
        input.value = v.substring(0,3) + '.' + v.substring(3);
    }
}

function abrirHistoricoAcessos(codUsuario, nome) {
    $('#historicoNomeSupervisor').text(nome);
    $('#historicoCorpo').html('<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando...</td></tr>');
    $('#modalHistoricoAcessos').modal('show');

    var motivos = {
        'login_ok': '<span class="label label-success">Login OK</span>',
        'senha_invalida': '<span class="label label-danger">Senha inv\u00e1lida</span>',
        'usuario_nao_encontrado': '<span class="label label-danger">N\u00e3o encontrado</span>',
        'conta_bloqueada': '<span class="label label-warning">Bloqueada</span>',
        'troca_senha': '<span class="label label-info">Troca de senha</span>',
        'token_recuperacao_gerado': '<span class="label label-info">Token gerado</span>',
        'senha_redefinida_por_token': '<span class="label label-info">Senha redefinida</span>',
        'login_como_administrativo': '<span class="label label-warning">Logado como</span>'
    };

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarSupervisor', 'm': 'carregarHistoricoAcessos', 'codUsuario': codUsuario, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            if (data.statusMessage && data.registros) {
                if (data.registros.length === 0) {
                    $('#historicoCorpo').html('<tr><td colspan="4" class="text-center text-muted">Nenhum registro de acesso encontrado.</td></tr>');
                    return;
                }
                var html = '';
                for (var i = 0; i < data.registros.length; i++) {
                    var r = data.registros[i];
                    var dt = r.data_acesso ? r.data_acesso.replace(/(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2}):\d{2}/, '$3/$2/$1 $4') : '-';
                    var labelMotivo = motivos[r.motivo] || '<span class="label label-default">' + (r.motivo || '-') + '</span>';
                    html += '<tr>'
                        + '<td>' + dt + '</td>'
                        + '<td class="text-center">' + labelMotivo + '</td>'
                        + '<td>' + (r.ip || '-') + '</td>'
                        + '<td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + (r.http_user_agent || '') + '">' + (r.http_user_agent || '-') + '</td>'
                        + '</tr>';
                }
                $('#historicoCorpo').html(html);
            } else {
                $('#historicoCorpo').html('<tr><td colspan="4" class="text-center text-danger">' + (data.message || 'Erro ao carregar.') + '</td></tr>');
            }
        },
        error: function() {
            $('#historicoCorpo').html('<tr><td colspan="4" class="text-center text-danger">Erro de comunica\u00e7\u00e3o.</td></tr>');
        }
    });
}

function copiarSenha(elementId) {
    var texto = document.getElementById(elementId).innerText;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(texto);
    } else {
        var temp = document.createElement('textarea');
        temp.value = texto;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
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
        <h4><i class="fa fa-users"></i> Supervisores</h4>
    </div>
    <div class="panel-body">

        <?php require_once(dirname(__FILE__)."/area_supervisor_abas.php"); ?>

        <div style="margin-bottom:12px;">
            <button class="btn btn-success btn-xs" onclick="abrirModalNovo()">
                <i class="fa fa-plus"></i> Novo Supervisor
            </button>
        </div>

        <div id="carregando" class="shown hidden">
            <div class="imagem">
                <div class="frase">Carregando...</div>
                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
            </div>
        </div>
        <div id="mensagem"></div>

        <div class="table-responsive">
            <table id="tabelaSupervisores" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Login</th>
                        <th style="text-align:center;">Alunos Cadastrados</th>
                        <th style="text-align:center;">&Uacute;ltimo Acesso</th>
                        <th>A&ccedil;&otilde;es</th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php $objSRV->carregarTabelaSupervisores($fws_, $debug); ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal Novo Supervisor -->
<div class="modal fade" id="modalNovo" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Novo Supervisor</h4>
            </div>
            <div class="modal-body">
                <div id="modalNovoMsg"></div>

                <div class="form-group">
                    <label for="novoNome">Nome completo: <span class="text-danger">*</span></label>
                    <input type="text" id="novoNome" class="form-control" maxlength="200" placeholder="Nome do supervisor">
                </div>
                <div class="form-group">
                    <label for="novoLogin">Login (e-mail): <span class="text-danger">*</span></label>
                    <input type="email" id="novoLogin" class="form-control" maxlength="200" placeholder="email@exemplo.com">
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="novoCpf">CPF:</label>
                            <input type="text" id="novoCpf" class="form-control" maxlength="14" placeholder="000.000.000-00" oninput="mascaraCpf(this)">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="novoCelular">Celular:</label>
                            <input type="text" id="novoCelular" class="form-control" maxlength="15" placeholder="(00) 00000-0000" oninput="mascaraCelular(this)">
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="cadastrarSupervisor()"><i class="fa fa-plus"></i> Cadastrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Reset de Senha -->
<div class="modal fade" id="modalResetarSenha" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#f0ad4e; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-key"></i> Resetar Senha</h4>
            </div>
            <div class="modal-body">
                <p>Deseja resetar a senha do supervisor <strong id="resetarNomeSupervisor"></strong>?</p>
                <p class="text-warning"><small>Um link de redefini&ccedil;&atilde;o de senha ser&aacute; enviado por e-mail.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarResetarSenha()"><i class="fa fa-key"></i> Resetar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Histórico de Acessos -->
<div class="modal fade" id="modalHistoricoAcessos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-history"></i> Hist&oacute;rico de Acessos &mdash; <span id="historicoNomeSupervisor"></span></h4>
            </div>
            <div class="modal-body" style="max-height:400px; overflow-y:auto;">
                <table class="table table-bordered table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th style="text-align:center;">Resultado</th>
                            <th>IP</th>
                            <th>Navegador</th>
                        </tr>
                    </thead>
                    <tbody id="historicoCorpo">
                    </tbody>
                </table>
                <p class="text-muted" style="font-size:11px;"><i class="fa fa-info-circle"></i> Exibindo os &uacute;ltimos 50 registros.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Supervisor -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-pencil"></i> Editar Supervisor</h4>
            </div>
            <div class="modal-body">
                <div id="modalEditarMsg"></div>

                <div class="form-group">
                    <label for="editarNome">Nome completo: <span class="text-danger">*</span></label>
                    <input type="text" id="editarNome" class="form-control" maxlength="200">
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="editarCpf">CPF:</label>
                            <input type="text" id="editarCpf" class="form-control" maxlength="14" oninput="mascaraCpf(this)">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="editarCelular">Celular:</label>
                            <input type="text" id="editarCelular" class="form-control" maxlength="15" oninput="mascaraCelular(this)">
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEdicaoSupervisor()"><i class="fa fa-save"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Remoção -->
<div class="modal fade" id="modalRemoverSupervisor" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Remover Supervisor</h4>
            </div>
            <div class="modal-body">
                <p>Deseja remover <strong id="removerNomeSupervisor"></strong> do grupo de supervisores?</p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> O login n&atilde;o ser&aacute; exclu&iacute;do, apenas removido do grupo.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarRemoverSupervisor()"><i class="fa fa-trash"></i> Remover</button>
            </div>
        </div>
    </div>
</div>

<?php
$logarComoController = 'GerenciarSupervisor';
$logarComoMethod = 'logarComoSupervisor';
require_once(dirname(__FILE__) . '/modal-logar-como.php');
?>
