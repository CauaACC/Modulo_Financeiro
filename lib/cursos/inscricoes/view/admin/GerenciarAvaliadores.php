<?php
/**
 * GerenciarAvaliadores.php
 *
 * Cadastro/listagem de avaliadores do processo seletivo (perfil
 * SIS_AVALIACAO_INSCRITO). Espelha GerenciarSupervisores (modulo
 * pedagogico) com escopo reduzido: cadastrar / listar / editar /
 * remover / resetar senha.
 *
 * Acesso: SIS_GERENCIAR_AVALIACAO_TRABALHOS (coordenador da avaliacao).
 */
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");

if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)) {
    require_once(dirname(__FILE__) . "/acesso-negado.php");
    exit;
}

$objSRV = new GerenciarAvaliadorAC();
?>
<style>.campo-invalido { border-color:#d9534f !important; box-shadow:0 0 4px rgba(217,83,79,0.4) !important; }</style>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    carregarTabelaAvaliadores();
    $(document).on('change keyup', '#modalNovo input', function() {
        $(this).removeClass('campo-invalido');
    });
});

function carregarTabelaAvaliadores() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarAvaliador', 'm': 'carregarTabelaAvaliadores', '_submit_check': '1'},
        function(html) {
            $("table#tabelaAvaliadores tbody.inputs tr").remove();
            $("table#tabelaAvaliadores tbody.inputs").append(html);
            bindAcoes();
        }
    );
}

function bindAcoes() {
    $('.btn-resetar-senha').off('click').on('click', function() {
        var codUsuario = $(this).parents("tr").data("cod-usuario");
        var nome = $(this).parents("tr").find("td:first").text();
        _resetarCodUsuario = codUsuario;
        $('#resetarNomeAvaliador').text(nome);
        $('#modalResetarSenha').modal('show');
    });
    $('.btn-editar-avaliador').off('click').on('click', function() {
        var tr = $(this).parents("tr");
        _editarCodUsuario = tr.data("cod-usuario");
        $('#editarNome').val(tr.data("nome"));
        $('#editarCpf').val(tr.data("cpf"));
        $('#editarCelular').val(tr.data("celular"));
        $('#modalEditarMsg').html('');
        $('#modalEditar').modal('show');
    });
    $('.btn-remover-avaliador').off('click').on('click', function() {
        var codUsuario = $(this).parents("tr").data("cod-usuario");
        var nome = $(this).parents("tr").find("td:first").text();
        _removerCodUsuario = codUsuario;
        $('#removerNomeAvaliador').text(nome);
        $('#modalRemoverAvaliador').modal('show');
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

function cadastrarAvaliador() {
    var nome    = $.trim($('#novoNome').val());
    var login   = $.trim($('#novoLogin').val());
    var cpf     = $.trim($('#novoCpf').val());
    var celular = $.trim($('#novoCelular').val());

    $('#modalNovo .campo-invalido').removeClass('campo-invalido');
    var errors = [];
    if (!nome)  { errors.push('Nome');  $('#novoNome').addClass('campo-invalido'); }
    if (!login) { errors.push('Login'); $('#novoLogin').addClass('campo-invalido'); }
    if (!cpf)   { errors.push('CPF');   $('#novoCpf').addClass('campo-invalido'); }
    if (errors.length) {
        $('#modalNovoMsg').html('<div class="alert alert-warning">Preencha: ' + errors.join(', ') + '.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarAvaliador', 'm': 'cadastrarAvaliador',
            'nome': nome, 'login': login, 'cpf': cpf, 'celular': celular,
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                $('#modalNovo').modal('hide');
                processJson(data);
                carregarTabelaAvaliadores();
            } else {
                $('#modalNovoMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro') + '</div>');
            }
        },
        complete: function() { stopLoading(); }
    });
}

var _editarCodUsuario = null;
function salvarEdicaoAvaliador() {
    var nome    = $.trim($('#editarNome').val());
    var cpf     = $.trim($('#editarCpf').val());
    var celular = $.trim($('#editarCelular').val());
    if (!nome) {
        $('#modalEditarMsg').html('<div class="alert alert-warning">Nome &eacute; obrigat&oacute;rio.</div>');
        return;
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarAvaliador', 'm': 'editarAvaliador',
            'codUsuario': _editarCodUsuario,
            'nome': nome, 'cpf': cpf, 'celular': celular,
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                $('#modalEditar').modal('hide');
                processJson(data);
                carregarTabelaAvaliadores();
            } else {
                $('#modalEditarMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro') + '</div>');
            }
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
        data: {'c': 'GerenciarAvaliador', 'm': 'resetarSenha', 'codUsuario': _resetarCodUsuario, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
        },
        complete: function() { stopLoading(); _resetarCodUsuario = null; }
    });
}

var _removerCodUsuario = null;
function confirmarRemoverAvaliador() {
    if (!_removerCodUsuario) return;
    $('#modalRemoverAvaliador').modal('hide');
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'c': 'GerenciarAvaliador', 'm': 'removerAvaliador', 'codUsuario': _removerCodUsuario, '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) carregarTabelaAvaliadores();
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
</script>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_avaliacao.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">
            <i class="fa fa-users"></i> Avaliadores
        </h4>
        <button class="btn btn-sm abriModal" type="button" onclick="abrirModalNovo()">
            <i class="fa fa-plus"></i> Novo Avaliador
        </button>
    </div>
    <div style="padding:12px 18px;">
        <div id="mensagem"></div>
        <p style="font-size:13px; color:#666; margin:0 0 10px;">
            Cadastre os avaliadores do processo seletivo. Cada avaliador receber&aacute; um login com
            permiss&atilde;o para avaliar inscritos (1&ordf; e 2&ordf; fase).
        </p>
    </div>
    <div>
        <table class="tbl-fin" id="tabelaAvaliadores">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Login</th>
                    <th style="text-align:center; width:110px;">Avalia&ccedil;&otilde;es</th>
                    <th style="text-align:center; width:130px;">&Uacute;ltimo Acesso</th>
                    <th style="width:160px;">A&ccedil;&otilde;es</th>
                </tr>
            </thead>
            <tbody class="inputs"></tbody>
        </table>
    </div>
</div>

<!-- Modal Novo Avaliador -->
<div class="modal fade" id="modalNovo" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-user-plus"></i> Novo Avaliador</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalNovoMsg"></div>
                <div class="form-group">
                    <label for="novoNome">Nome completo: <span class="text-danger">*</span></label>
                    <input type="text" id="novoNome" class="form-control" maxlength="200" placeholder="Nome do avaliador">
                </div>
                <div class="form-group">
                    <label for="novoLogin">Login (e-mail): <span class="text-danger">*</span></label>
                    <input type="email" id="novoLogin" class="form-control" maxlength="200" placeholder="email@exemplo.com">
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="novoCpf">CPF: <span class="text-danger">*</span></label>
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
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" onclick="cadastrarAvaliador()">
                    <i class="fa fa-plus"></i> Cadastrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Avaliador -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-pencil"></i> Editar Avaliador</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
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
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" onclick="salvarEdicaoAvaliador()">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Resetar Senha -->
<div class="modal fade" id="modalResetarSenha" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#f0ad4e; color:#fff;">
                <h4 class="modal-title"><i class="fa fa-key"></i> Resetar Senha</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja resetar a senha de <strong id="resetarNomeAvaliador"></strong>?</p>
                <p class="text-warning"><small>Um link de redefini&ccedil;&atilde;o ser&aacute; enviado por e-mail.</small></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarResetarSenha()">
                    <i class="fa fa-key"></i> Resetar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Remover Avaliador -->
<div class="modal fade" id="modalRemoverAvaliador" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Remover Avaliador</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Remover <strong id="removerNomeAvaliador"></strong> do grupo de avaliadores?</p>
                <p style="color:#a94442; font-size:12px;">
                    <i class="fa fa-info-circle"></i> O login do usu&aacute;rio n&atilde;o ser&aacute; deletado &mdash;
                    apenas o v&iacute;nculo com o grupo de avaliadores deste evento.
                </p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarRemoverAvaliador()">
                    <i class="fa fa-trash"></i> Remover
                </button>
            </div>
        </div>
    </div>
</div>
