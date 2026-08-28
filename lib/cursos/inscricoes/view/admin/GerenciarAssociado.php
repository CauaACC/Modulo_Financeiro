<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarAssociadoAC();
?>
<script type="text/javascript">
$(document).ready(function() {

    carregarTabelaAssociados();

    // Delegated events
    $('#itens').on('click', '.editar', function() {
        editarAssociado($(this).closest('tr').attr('id'));
    });
    $('#itens').on('click', '.excluir', function() {
        excluirAssociado($(this).closest('tr').attr('id'));
    });

    $('#btnSalvarAssociado').click(function() {
        salvarAssociado();
    });
});

function abrirModalAssociado() {
    limparCampos();
    $('#modalAssociadoTitulo').text('Novo Associado');
    $('#modalAssociado').modal('show');
}

function carregarTabelaAssociados() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarAssociado', 'm': 'carregarTabelaAssociados', '_submit_check': '1'},
        function(html) {
            $('#itens tbody').html(html);
        }
    );
}

function salvarAssociado() {
    var nome = $.trim($('#nomeAssociado').val());
    var valido = true;
    $('#modalAssociado .form-group').removeClass('has-error');
    $('#modalAssociado .msg-campo').remove();

    if (!nome) {
        $('#nomeAssociado').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    var cpf = $.trim($('#cpf').val());
    if (!cpf) {
        $('#cpf').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    } else if (!validarCpfDigitos(cpf)) {
        $('#cpf').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">CPF inv&aacute;lido</span>');
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
    if (!valido) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarAssociado',
            'm': 'salvarAssociado',
            'codAssociado': $('#codAssociado').val(),
            'novoCodAssociado': $('#novoCodAssociado').val(),
            'cpf': $.trim($('#cpf').val()),
            'email': $.trim($('#email').val()),
            'nomeAssociado': nome,
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
                carregarTabelaAssociados();
                $('#modalAssociado').modal('hide');
            }
        },
        complete: function() { stopLoading(); }
    });
}

function editarAssociado(codAssociado) {
    if (!codAssociado) return;
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'codAssociado': codAssociado, 'c': 'GerenciarAssociado', 'm': 'editarAssociado', '_submit_check': '1'},
        dataType: "json",
        beforeSend: function() { startLoading(); },
        success: function(data) {
            $('#codAssociado').val(data.codAssociado);
            $('#nomeAssociado').val(data.nomeAssociado);
            $('#cpf').val(data.cpf);
            $('#email').val(data.email);
            $('#novoCodAssociado').val(1);
            $('#modalAssociadoTitulo').text('Editar Associado');
            $('#modalAssociado').modal('show');
        },
        complete: function() { stopLoading(); }
    });
}

function excluirAssociado(codAssociado) {
    $('#modalConfirmarExclusao').data('cod', codAssociado);
    $('#modalConfirmarExclusao').modal('show');
}

function confirmarExclusao() {
    var cod = $('#modalConfirmarExclusao').data('cod');
    $('#modalConfirmarExclusao').modal('hide');
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'codAssociado': cod, 'c': 'GerenciarAssociado', 'm': 'excluirAssociado', '_submit_check': '1'},
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaAssociados();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function limparCampos() {
    $('#codAssociado').val('');
    $('#nomeAssociado').val('');
    $('#cpf').val('');
    $('#email').val('');
    $('#novoCodAssociado').val(0);
    $('#modalAssociadoMsg').html('');
    $('#modalAssociado .form-group').removeClass('has-error');
    $('#modalAssociado .msg-campo').remove();
}
</script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_outros.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Associado</h4>
        <button class="btn btn-sm abriModal" type="button" onclick="abrirModalAssociado()">
            <i class="fa fa-plus"></i> Novo Associado
        </button>
    </div>
    <div style="padding:12px 18px;">
        <div id="carregando" class="shown hidden">
            <div class="imagem">
                <div class="frase">Carregando...</div>
                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
            </div>
        </div>
        <h5 style="font-size:14px; font-weight:600; color:#555; margin:0;">Lista de Associados</h5>
    </div>
    <div>
        <table class="tbl-fin" id="itens">
            <thead>
            <tr>
                <th style="width:90px;">A&ccedil;&otilde;es</th>
                <th data-col="1" style="cursor:pointer;">Nome <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="2" style="cursor:pointer;">CPF <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="3" style="cursor:pointer;">E-mail <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="4" style="cursor:pointer;">J&aacute; inscrito <span class="sort-arrow">&uarr;&darr;</span></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Associado -->
<div class="modal fade" id="modalAssociado" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title" id="modalAssociadoTitulo"><i class="fa fa-users"></i>Novo Associado</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="formAssociado" name="formAssociado" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post" novalidate>
      <div class="modal-body">
        <div id="modalAssociadoMsg"></div>
        <input type="hidden" value="1" name="_submit_check">
        <input type="hidden" value="GerenciarAssociado" name="c">
        <input type="hidden" value="salvarAssociado" name="m">
        <input type="hidden" name="novoCodAssociado" id="novoCodAssociado" value="0">
        <input type="hidden" name="codAssociado" id="codAssociado" value="">
        <div class="form-group">
            <label for="nomeAssociado">Nome Associado:</label>
            <input type="text" id="nomeAssociado" name="nomeAssociado" class="form-control" placeholder="Nome Associado">
        </div>
        <div class="row">
            <div class="form-group col-sm-5">
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" class="form-control" placeholder="CPF" maxlength="11"
                       onkeypress="return isNumInt(getKey(event));">
            </div>
            <div class="form-group col-sm-7">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" class="form-control" placeholder="Email">
            </div>
        </div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarAssociado">Salvar</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Confirmar Exclusao -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalhoExclusao">
        <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclus&atilde;o</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p>Deseja realmente excluir este associado?</p>
        <p style="color:#a94442; font-size:12px;"><i class="fa fa-exclamation-triangle"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</p>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
      </div>
    </div>
  </div>
</div>
