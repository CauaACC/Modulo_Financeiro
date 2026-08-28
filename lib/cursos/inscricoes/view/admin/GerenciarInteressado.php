<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarInteressadoAC();
?>
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_outros.php");
?>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Interessados</h4>
        <button type="button" class="btn btn-sm abriModal" id="btnNovoInteressado">
            <i class="fa fa-plus"></i> Novo Interessado
        </button>
    </div>
    <div style="padding:12px 18px;">
        <h5 style="font-size:14px; font-weight:600; color:#555; margin:0;">Lista de Interessados</h5>
    </div>
    <div>
        <table class="tbl-fin" id="itens">
            <thead>
            <tr>
                <th style="width:90px;">A&ccedil;&otilde;es</th>
                <th data-col="1" style="cursor:pointer;">Nome <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="2" style="cursor:pointer;">E-mail <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="3" style="cursor:pointer;">Telefone <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="4" style="cursor:pointer;">Status <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="5" style="cursor:pointer;">J&aacute; inscrito <span class="sort-arrow">&uarr;&darr;</span></th>
            </tr>
            </thead>
            <tbody>
                <?php $objSRV->carregarTabelaInteressados($fws_, $debug); ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Interessado -->
<div class="modal fade" id="modalInteressado" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalInteressadoTitulo"><i class="fa fa-user"></i>Novo Interessado</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formInteressado" name="formInteressado" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post" novalidate>
                    <div class="form-group">
                        <label for="nomeInteressado">Nome Interessado:</label>
                        <input type="text" id="nomeInteressado" name="nomeInteressado" value="" class="form-control" placeholder="Nome Interessado">
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-7">
                            <label for="email">Email:</label>
                            <input type="text" id="email" name="email" value="" class="form-control" placeholder="Email">
                        </div>
                        <div class="form-group col-sm-5">
                            <label for="telefone">Telefone:</label>
                            <input type="text" id="telefone" name="telefone" value="" class="form-control" placeholder="Telefone">
                        </div>
                    </div>
                    <div id="modalInteressadoMsg"></div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarInteressado" name="c">
                    <input type="hidden" value="salvarInteressados" name="m">
                    <input type="hidden" name="novoCodInteressado" id="novoCodInteressado" value="0">
                    <input type="hidden" name="codInteressado" id="codInteressado" value="">
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarInteressado">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmacao Exclusao -->
<div class="modal fade" id="modalExcluirInteressado" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclus&atilde;o</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este interessado?</p>
                <p class="text-danger" style="font-size:12px;"><i class="fa fa-exclamation-triangle"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" id="btnConfirmarExclusao">Excluir</button>
            </div>
        </div>
    </div>
</div>

<div id="carregando" class="shown hidden">
    <div class="imagem">
        <div class="frase">Carregando...</div>
        <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {

    // Novo Interessado
    $('#btnNovoInteressado').on('click', function() {
        limparCampos();
        $('#modalInteressadoTitulo').text('Novo Interessado');
        $('#modalInteressado').modal('show');
    });

    // Salvar
    $('#btnSalvarInteressado').on('click', function() {
        salvarInteressado();
    });

    // Editar (delegado)
    $('#itens').on('click', '.editar', function(e) {
        e.preventDefault();
        var id = $(this).closest('tr').attr('id');
        editarInteressado(id);
    });

    // Excluir (delegado)
    var codExcluir = null;
    $('#itens').on('click', '.excluir', function(e) {
        e.preventDefault();
        codExcluir = $(this).closest('tr').attr('id');
        $('#modalExcluirInteressado').modal('show');
    });

    $('#btnConfirmarExclusao').on('click', function() {
        if (codExcluir) {
            excluirInteressado(codExcluir);
            $('#modalExcluirInteressado').modal('hide');
        }
    });
});

function carregarTabelaInteressados() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarInteressado',
        'm': 'carregarTabelaInteressados',
        '_submit_check': '1'
    }, function(html) {
        $('#itens tbody').html(html);
    });
}

function salvarInteressado() {
    var nome = $.trim($('#nomeInteressado').val());
    var valido = true;
    $('#modalInteressado .form-group').removeClass('has-error');
    $('#modalInteressado .msg-campo').remove();

    if (!nome) {
        $('#nomeInteressado').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    var email = $.trim($('#email').val());
    if (!email) {
        $('#email').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    } else if (!validarEmail(email)) {
        $('#email').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Email inv&aacute;lido</span>');
        valido = false;
    }
    var telefone = $.trim($('#telefone').val());
    if (!telefone) {
        $('#telefone').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    if (!valido) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarInteressado',
            'm': 'salvarInteressados',
            'codInteressado': $('#codInteressado').val(),
            'updateInteressados': $('#novoCodInteressado').val(),
            'nomeInteressado': nome,
            'email': email,
            'telefone': telefone,
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                limparCampos();
                carregarTabelaInteressados();
                $('#modalInteressado').modal('hide');
            }
        },
        complete: function() { stopLoading(); }
    });
}

function editarInteressado(codInteressado) {
    if (!codInteressado) {
        return;
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codInteressado': codInteressado,
            'c': 'GerenciarInteressado',
            'm': 'editarInteressados',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() {
            startLoading();
        },
        success: function(data) {
            $('#codInteressado').val(data.codInteressado);
            $('#nomeInteressado').val(data.nomeInteressado);
            $('#email').val(data.email);
            $('#telefone').val(data.telefone);
            $('#novoCodInteressado').val(1);
            $('#modalInteressadoTitulo').text('Editar Interessado');
            $('#modalInteressado').modal('show');
        },
        complete: function() {
            stopLoading();
        },
        error: function(xhr, status, error) {
            console.error('Erro na solicitacao:', error);
        }
    });
}

function excluirInteressado(codInteressado) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codInteressado': codInteressado,
            'c': 'GerenciarInteressado',
            'm': 'excluirInteressados',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() {
            startLoading();
        },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaInteressados();
            }
        },
        complete: function() {
            stopLoading();
        }
    });
}

function limparCampos() {
    $('#codInteressado').val('');
    $('#nomeInteressado').val('');
    $('#email').val('');
    $('#telefone').val('');
    $('#novoCodInteressado').val(0);
    $('#modalInteressadoMsg').html('');
    $('#modalInteressado .form-group').removeClass('has-error');
    $('#modalInteressado .msg-campo').remove();
}
</script>
