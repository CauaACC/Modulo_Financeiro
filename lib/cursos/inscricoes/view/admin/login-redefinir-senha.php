<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$token = isset($fwsGet_['token']) ? trim($fwsGet_['token']) : '';
if (empty($token)) {
    $url = WB_URL_VIEW . "?content=view/admin/login.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
    exit();
}
?>
<script type="text/javascript" src="resources/js/default.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Validar token ao carregar a pagina (formulario comeca oculto)
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarLogin',
                'm': 'validarTokenRecuperacao',
                'token': '<?php echo htmlspecialchars($token, ENT_QUOTES) ?>',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data.statusMessage) {
                    $('#formRedefinir').show();
                    $('#infoSenha').show();
                } else {
                    $('#msgRedefinir').html(
                        '<div class="alert alert-danger" style="margin-bottom:0;">' +
                            '<i class="fa fa-exclamation-circle"></i> ' +
                            'Este link de redefini&ccedil;&atilde;o de senha &eacute; <strong>inv&aacute;lido ou j&aacute; expirou</strong>.' +
                            '<br><small>Os links de recupera&ccedil;&atilde;o expiram em 1 hora e s&oacute; podem ser usados uma vez.</small>' +
                        '</div>' +
                        '<div style="margin-top:18px; text-align:center;">' +
                            '<a href="<?php echo WB_URL_VIEW ?>?content=view/admin/login-esqueceu-senha.php" class="btn btn-primary" style="margin-right:8px;">' +
                                '<i class="fa fa-envelope"></i> Solicitar novo link' +
                            '</a>' +
                            '<a href="<?php echo WB_URL_VIEW ?>?content=view/admin/login.php" class="btn btn-default">' +
                                '<i class="fa fa-sign-in"></i> Voltar ao login' +
                            '</a>' +
                        '</div>'
                    );
                }
                $('#carregandoToken').hide();
            },
            error: function() {
                $('#carregandoToken').hide();
                $('#msgRedefinir').html(
                    '<div class="alert alert-danger" style="margin-bottom:0;">' +
                        '<i class="fa fa-exclamation-circle"></i> Erro ao validar o link. Tente novamente.' +
                    '</div>' +
                    '<div style="margin-top:18px; text-align:center;">' +
                        '<a href="<?php echo WB_URL_VIEW ?>?content=view/admin/login-esqueceu-senha.php" class="btn btn-primary" style="margin-right:8px;">' +
                            '<i class="fa fa-envelope"></i> Solicitar novo link' +
                        '</a>' +
                        '<a href="<?php echo WB_URL_VIEW ?>?content=view/admin/login.php" class="btn btn-default">' +
                            '<i class="fa fa-sign-in"></i> Voltar ao login' +
                        '</a>' +
                    '</div>'
                );
            }
        });
    });

    function redefinirSenha() {
        var senhaNova = $('#senhaNova').val();
        var senhaNovaRepetir = $('#senhaNovaRepetir').val();
        $('#msgRedefinir').html('');

        if (!senhaNova || !senhaNovaRepetir) {
            $('#msgRedefinir').html('<div class="alert alert-danger">Preencha todos os campos.</div>');
            return;
        }
        if (senhaNova !== senhaNovaRepetir) {
            $('#msgRedefinir').html('<div class="alert alert-danger">As senhas n&atilde;o conferem.</div>');
            return;
        }
        if (senhaNova.length < 8) {
            $('#msgRedefinir').html('<div class="alert alert-danger">A nova senha deve ter pelo menos 8 caracteres.</div>');
            return;
        }
        <?php if (defined('GOOGLE_KEY')) { ?>
        if ($('#g-recaptcha-response').val() == '') {
            $('#msgRedefinir').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Marque a op&ccedil;&atilde;o "N&atilde;o sou um rob&ocirc;".</div>');
            return;
        }
        <?php } ?>

        $('#btnRedefinir').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processando...');

        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarLogin',
                'm': 'redefinirSenhaPorToken',
                'token': '<?php echo htmlspecialchars($token, ENT_QUOTES) ?>',
                'senhaNova': senhaNova,
                'senhaNovaRepetir': senhaNovaRepetir,
                'g-recaptcha-response': $('#g-recaptcha-response').val() || '',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data.statusMessage) {
                    $('#msgRedefinir').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + data.message + '</div>');
                    $('#formRedefinir').hide();
                    setTimeout(function() {
                        window.location.href = '<?php echo WB_URL_VIEW ?>?content=view/admin/login.php';
                    }, 3000);
                } else {
                    $('#msgRedefinir').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + data.message + '</div>');
                    $('#btnRedefinir').prop('disabled', false).html('<i class="fa fa-key"></i> Redefinir Senha');
                }
            },
            error: function() {
                $('#msgRedefinir').html('<div class="alert alert-danger">Erro ao processar. Tente novamente.</div>');
                $('#btnRedefinir').prop('disabled', false).html('<i class="fa fa-key"></i> Redefinir Senha');
            }
        });
    }
</script>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><i class="fa fa-key"></i> Redefinir Senha</h4>
        </div>
        <div class="panel-body">

            <div id="carregandoToken" class="text-center" style="padding:30px;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p class="text-muted" style="margin-top:10px;">Validando link de redefini&ccedil;&atilde;o...</p>
            </div>

            <div id="infoSenha" class="alert alert-info" style="display:none;">
                <i class="fa fa-info-circle"></i>
                Defina sua nova senha abaixo. Ela deve ter pelo menos 8 caracteres, contendo letras, n&uacute;meros e caracteres especiais.
            </div>

            <div id="msgRedefinir"></div>

            <div id="formRedefinir" class="row" style="display:none;">
                <div class="col-sm-6 col-sm-offset-3">
                    <div class="form-group">
                        <label for="senhaNova">Nova Senha:</label>
                        <input type="password" id="senhaNova" class="form-control" placeholder="M&iacute;nimo 8 caracteres">
                    </div>
                    <div class="form-group">
                        <label for="senhaNovaRepetir">Confirmar Nova Senha:</label>
                        <input type="password" id="senhaNovaRepetir" class="form-control" placeholder="Repita a nova senha">
                    </div>
                    <?php if (defined('GOOGLE_KEY')) { ?>
                    <div class="g-recaptcha" data-sitekey="<?php echo GOOGLE_KEY ?>" style="margin:10px 0;"></div>
                    <?php } ?>
                    <button id="btnRedefinir" class="btn btn-primary btn-block" onclick="redefinirSenha()">
                        <i class="fa fa-key"></i> Redefinir Senha
                    </button>
                    <div style="text-align:center; margin-top:10px;">
                        <a href="index.php?content=view/admin/login.php" style="font-size:13px;">
                            <i class="fa fa-arrow-left"></i> Voltar ao login
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php if (defined('GOOGLE_KEY')) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>
