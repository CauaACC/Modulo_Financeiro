<?php
require_once(dirname(__FILE__) . "/../../view/admin/seguranca-adm-geral.php");
exit();
$objSRV = new GerenciarAcessosAC();
?>
<script type="text/javascript">
$(document).ready(function() {

    $('#btnIncluirLogin').click(function() {
        salvarLogin();
    });

    $('#btnAssociarGrupo').click(function() {
        adicionarGrupo();
    });

    // Toggle da seção de grupos
    $('.section-toggle').click(function() {
        var target = $(this).data('target');
        $(target).slideToggle(300);
        $(this).find('i.fa').toggleClass('fa-angle-right fa-angle-down');
    });
});

function carregarTabelaUsuarios() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarAcessos',
        'm': 'carregarTabelaUsuarios',
        '_submit_check': '1'
    }, function(html) {
        $("tbody.usuarios").html(html);
    });
}

function carregarTabelaGrupos() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarAcessos',
        'm': 'carregarTabelaGrupos',
        '_submit_check': '1'
    }, function(html) {
        $("tbody.grupos").html(html);
    });
}

function carregarSelectUsuarios() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarAcessos',
        'm': 'carregarSelectUsuarios',
        '_submit_check': '1'
    }, function(html) {
        $("#selectUsuario").html(html);
    });
}

function salvarLogin() {
    var nome  = $.trim($('#inputNome').val());
    var login = $.trim($('#inputLogin').val());
    var senha = $.trim($('#inputSenha').val());

    if (!nome || !login || !senha) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Preencha Nome, Login/Email e Senha.</div>');
        return;
    }

    $('#formLogin').ajaxSubmit({
        target: '#mensagem',
        dataType: 'json',
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        timeout: 8000,
        error: function(ajax) { showResponse(ajax); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaUsuarios();
                carregarSelectUsuarios();
                limparCamposLogin();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function adicionarGrupo() {
    var codUsuario = $('#selectUsuario').val();
    var codGrupo   = $('#selectGrupo').val();

    if (!codUsuario) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um usu&aacute;rio.</div>');
        return;
    }
    if (!codGrupo) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione um grupo.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'cod_usuario': codUsuario,
            'cod_grupo': codGrupo,
            'c': 'GerenciarAcessos',
            'm': 'adicionarGrupo',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaUsuarios();
                carregarTabelaGrupos();
            }
        },
        complete: function() { stopLoading(); }
    });
}

var _removerGrupoAcessos = { codUsuario: null, codGrupo: null };

function removerGrupo(codUsuario, codGrupo) {
    _removerGrupoAcessos = { codUsuario: codUsuario, codGrupo: codGrupo };
    $('#modalRemoverGrupoAcessos').modal('show');
}

function confirmarRemoverGrupoAcessos() {
    if (!_removerGrupoAcessos.codUsuario) return;
    $('#modalRemoverGrupoAcessos').modal('hide');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'cod_usuario': _removerGrupoAcessos.codUsuario,
            'cod_grupo': _removerGrupoAcessos.codGrupo,
            'c': 'GerenciarAcessos',
            'm': 'removerGrupo',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaUsuarios();
                carregarTabelaGrupos();
            }
        },
        complete: function() { stopLoading(); _removerGrupoAcessos = {}; }
    });
}

var _excluirUsuarioAcessos = { codUsuario: null };

function excluirUsuario(codUsuario, login) {
    _excluirUsuarioAcessos = { codUsuario: codUsuario };
    $('#modalExcluirUsuarioMsg').html('Deseja remover <strong>' + $('<span>').text(login).html() + '</strong> deste evento?<br>Todos os grupos associados tamb&eacute;m ser&atilde;o removidos.');
    $('#modalExcluirUsuarioAcessos').modal('show');
}

function confirmarExcluirUsuarioAcessos() {
    if (!_excluirUsuarioAcessos.codUsuario) return;
    $('#modalExcluirUsuarioAcessos').modal('hide');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'cod_usuario': _excluirUsuarioAcessos.codUsuario,
            'c': 'GerenciarAcessos',
            'm': 'excluirUsuario',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaUsuarios();
                carregarTabelaGrupos();
                carregarSelectUsuarios();
            }
        },
        complete: function() { stopLoading(); _excluirUsuarioAcessos = {}; }
    });
}

var _enviarEmailCodConvidado = null;

function enviarEmail(codConvidado) {
    _enviarEmailCodConvidado = codConvidado;
    $('#modalEnviarEmailAcessos').modal('show');
}

function confirmarEnviarEmailAcessos() {
    if (!_enviarEmailCodConvidado) return;
    $('#modalEnviarEmailAcessos').modal('hide');
    var form = document.getElementById('formEnviarEmail');
    form.cod_convidado.value = _enviarEmailCodConvidado;
    form.submit();
    _enviarEmailCodConvidado = null;
}

function limparCamposLogin() {
    $('#inputNome').val('');
    $('#inputLogin').val('');
    $('#inputSenha').val('');
}
</script>

<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_usuarios.php");

// Carregar selects de usuários e grupos via PHP
$Conexao_db = FWA_Conexao("OpcTec");

// Select de usuários do evento
$sqlUsuarios = "
    SELECT u.cod_usuario, u.login
    FROM sis_usuarios u
    INNER JOIN sis_ligacao_usuarios_eventos lue ON lue.cod_usuario = u.cod_usuario
    WHERE lue.cod_evento = " . intval(COD_EVENTO) . "
    ORDER BY u.login
";
$rsUsuarios = $Conexao_db->Execute($sqlUsuarios);

// Select de grupos
$sqlGrupos = "
    SELECT cod_grupo, nome_grupo
    FROM sis_grupos
    WHERE cod_grupo <> 999
    ORDER BY nome_grupo
";
$rsGrupos = $Conexao_db->Execute($sqlGrupos);
?>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar Acessos</h4>
        </div>
        <div class="panel-body">

            <!-- FORM: Incluir Login -->
            <b>Incluir Login:</b>
            <form id="formLogin" name="formLogin" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="row" style="margin-bottom:10px;">
                    <div class="col-sm-4">
                        <label for="inputNome">Nome:</label>
                        <input type="text" id="inputNome" name="nome" class="form-control" placeholder="Nome completo">
                    </div>
                    <div class="col-sm-3">
                        <label for="inputLogin">Login/Email:</label>
                        <input type="text" id="inputLogin" name="login" class="form-control" placeholder="email@exemplo.com">
                    </div>
                    <div class="col-sm-3">
                        <label for="inputSenha">Senha:</label>
                        <input type="text" id="inputSenha" name="senha" class="form-control" placeholder="Senha">
                    </div>
                    <div class="col-sm-2">
                        <label>&nbsp;</label><br>
                        <button class="btn btn-primary btn-sm" type="button" id="btnIncluirLogin">
                            <i class="fa fa-plus"></i> Incluir
                        </button>
                    </div>
                </div>
                <input type="hidden" value="1" name="_submit_check">
                <input type="hidden" value="GerenciarAcessos" name="c">
                <input type="hidden" value="salvarLogin" name="m">
            </form>

            <div id="carregando" class="shown hidden">
                <div class="imagem">
                    <div class="frase">Carregando...</div>
                    <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
                </div>
            </div>
            <div id="mensagem"></div>
            <div id="sucesso" class="hidden"></div>
            <div id="erro" class="hidden"></div>

            <hr>

            <!-- FORM: Associar Grupo -->
            <b>Associar Grupo a Usuário:</b>
            <form id="formGrupo" name="formGrupo">
                <div class="row" style="margin-bottom:10px;">
                    <div class="col-sm-5">
                        <label for="selectUsuario">Usuário:</label>
                        <select id="selectUsuario" name="cod_usuario" class="form-control">
                            <option value="">Selecione um Login</option>
                            <?php
                            while (!$rsUsuarios->EOF) {
                                echo '<option value="' . $rsUsuarios->fields['cod_usuario'] . '">'
                                    . htmlspecialchars($rsUsuarios->fields['login'])
                                    . '</option>';
                                $rsUsuarios->MoveNext();
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-sm-5">
                        <label for="selectGrupo">Grupo:</label>
                        <select id="selectGrupo" name="cod_grupo" class="form-control">
                            <option value="">Selecione um Grupo</option>
                            <?php
                            while (!$rsGrupos->EOF) {
                                echo '<option value="' . $rsGrupos->fields['cod_grupo'] . '">'
                                    . htmlspecialchars($rsGrupos->fields['nome_grupo'])
                                    . '</option>';
                                $rsGrupos->MoveNext();
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label>&nbsp;</label><br>
                        <button class="btn btn-success btn-sm" type="button" id="btnAssociarGrupo">
                            <i class="fa fa-link"></i> Associar
                        </button>
                    </div>
                </div>
            </form>

            <hr>

            <!-- TABELA: Usuários do Evento -->
            <b>Usuários do Evento:</b>
            <table class="table table-striped table-bordered" id="tabelaUsuarios" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th width="120px">Ações</th>
                        <th>Login</th>
                        <th>Nome</th>
                        <th>Grupos</th>
                    </tr>
                </thead>
                <tbody class="usuarios">
                    <?php $objSRV->carregarTabelaUsuarios($fws_, $debug); ?>
                </tbody>
            </table>

            <hr>

            <!-- SEÇÃO COLAPSÁVEL: Grupos do Evento -->
            <div class="section-toggle" data-target="#secGrupos" style="cursor:pointer;padding:8px 0;font-weight:bold;color:#337ab7;">
                <i class="fa fa-angle-right"></i> Grupos e Módulos do Evento
            </div>
            <div id="secGrupos" style="display:none;">
                <table class="table table-bordered" id="tabelaGrupos" style="margin-top:10px;">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Módulos/Sistemas</th>
                            <th width="120px">Qtd Usuários</th>
                        </tr>
                    </thead>
                    <tbody class="grupos">
                        <?php $objSRV->carregarTabelaGrupos($fws_, $debug); ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Confirmar Remover Grupo -->
<div class="modal fade" id="modalRemoverGrupoAcessos" tabindex="-1" role="dialog">
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
                <button type="button" class="btn btn-danger" onclick="confirmarRemoverGrupoAcessos()"><i class="fa fa-trash"></i> Remover</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Excluir Usuario -->
<div class="modal fade" id="modalExcluirUsuarioAcessos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Remo&ccedil;&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p id="modalExcluirUsuarioMsg"></p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExcluirUsuarioAcessos()"><i class="fa fa-trash"></i> Remover</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Envio de Email -->
<div class="modal fade" id="modalEnviarEmailAcessos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-envelope"></i> Enviar Senha</h4>
            </div>
            <div class="modal-body">
                <p>Enviar senha por e-mail para este usu&aacute;rio?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" onclick="confirmarEnviarEmailAcessos()"><i class="fa fa-envelope"></i> Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Form oculto para envio de email (mesma abordagem do legado) -->
<form id="formEnviarEmail" name="formEnviarEmail" method="post" action="<?php echo WB_URL_COMMAND ?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarEmail">
    <input type="hidden" name="m" value="enviarEmailLoginConvidado">
    <input type="hidden" name="cod_convidado" value="">
</form>

<style>
#sucesso,
#erro {
    min-height: 40px;
    visibility: hidden;
}
#sucesso.shown,
#erro.shown {
    visibility: visible;
}
.section-toggle:hover {
    text-decoration: underline;
}
</style>
