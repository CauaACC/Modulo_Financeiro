<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if (!@$_SESSION['_logado'] || !@$_SESSION['_administrativo']) {
    $url = WB_URL_VIEW . "?content=view/admin/login.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
    exit();
}
?>
<script type="text/javascript" src="resources/js/default.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    carregarDados();
});

function carregarDados() {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: { 'c': 'GerenciarLogin', 'm': 'carregarDadosConta', '_submit_check': '1' },
        dataType: "json",
        cache: false,
        success: function(data) {
            if (data.statusMessage) {
                $('#inputNome').val(data.nome || '');
                $('#inputLogin').val(data.login || '');
                $('#inputCelular').val(data.celular || '');
            }
        }
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

function salvarDados() {
    var nome    = $('#inputNome').val().trim();
    var celular = $('#inputCelular').val().trim();
    $('#msgDados').html('');

    if (!nome) {
        $('#msgDados').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Nome &eacute; obrigat&oacute;rio.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarLogin',
            'm': 'atualizarDadosConta',
            'nome': nome,
            'celular': celular,
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { $('#btnSalvar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...'); },
        success: function(data) {
            if (data.statusMessage) {
                $('#msgDados').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + data.message + '</div>');
            } else {
                $('#msgDados').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + data.message + '</div>');
            }
        },
        error: function() {
            $('#msgDados').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Erro de comunica&ccedil;&atilde;o.</div>');
        },
        complete: function() { $('#btnSalvar').prop('disabled', false).html('<i class="fa fa-save"></i> Salvar'); }
    });
}

</script>

<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><i class="fa fa-id-card"></i> Meus Dados</h4>
    </div>
    <div class="panel-body">

        <p>
            <a href="<?php echo WB_URL_VIEW ?>?content=view/admin/minha-conta.php" style="text-decoration:none;">
                <i class="fa fa-arrow-left"></i> Voltar para Minha Conta
            </a>
        </p>

        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">

                <div id="msgDados"></div>

                <div class="form-group">
                    <label for="inputNome">Nome:</label>
                    <input type="text" id="inputNome" class="form-control" maxlength="200" placeholder="Seu nome completo">
                </div>
                <div class="form-group">
                    <label for="inputLogin">E-mail / Login:</label>
                    <input type="text" id="inputLogin" class="form-control" readonly style="background-color:#eee;">
                    <p class="text-muted" style="font-size:11px; margin-top:4px;">
                        <i class="fa fa-info-circle"></i> O e-mail &eacute; o login de acesso e n&atilde;o pode ser alterado por aqui.
                    </p>
                </div>
                <div class="form-group">
                    <label for="inputCelular">Celular:</label>
                    <input type="text" id="inputCelular" class="form-control" maxlength="15" placeholder="(00) 00000-0000" oninput="mascaraCelular(this)">
                </div>

                <button id="btnSalvar" class="btn btn-primary btn-block" type="button" onclick="salvarDados()">
                    <i class="fa fa-save"></i> Salvar
                </button>

            </div>
        </div>

    </div>
</div>
