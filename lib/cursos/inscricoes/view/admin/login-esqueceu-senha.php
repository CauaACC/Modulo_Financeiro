<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if ($_SESSION['_logado'] && $_SESSION['_administrativo']) {
    $url = WB_URL_VIEW . "?content=view/admin/default.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
    exit();
}
?>
<script type="text/javascript" src="resources/js/default.js"></script>
<script type="text/javascript">
    function solicitarRecuperacao() {
        var login = $('#loginRecuperacao').val();
        $('#msgRecuperacao').html('');

        if (!login) {
            $('#msgRecuperacao').html('<div class="alert alert-danger">Informe seu login.</div>');
            return;
        }
        <?php if (defined('GOOGLE_KEY')) { ?>
        if ($('#g-recaptcha-response').val() == '') {
            $('#msgRecuperacao').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Marque a op&ccedil;&atilde;o "N&atilde;o sou um rob&ocirc;".</div>');
            return;
        }
        <?php } ?>

        $('#btnRecuperar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarLogin',
                'm': 'solicitarRecuperacaoSenha',
                'login': login,
                'g-recaptcha-response': $('#g-recaptcha-response').val() || '',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                $('#msgRecuperacao').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + data.message + '</div>');
                $('#formRecuperacao').hide();
            },
            error: function() {
                $('#msgRecuperacao').html('<div class="alert alert-danger">Erro ao processar. Tente novamente.</div>');
                $('#btnRecuperar').prop('disabled', false).html('<i class="fa fa-envelope"></i> Enviar');
            }
        });
    }
</script>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><i class="fa fa-unlock-alt"></i> Recupera&ccedil;&atilde;o de Senha</h4>
        </div>
        <div class="panel-body">

            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                Informe seu login abaixo. Se houver uma conta associada, enviaremos um e-mail com instru&ccedil;&otilde;es para redefinir sua senha.
            </div>

            <div id="msgRecuperacao"></div>

            <div id="formRecuperacao" class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    <div class="form-group">
                        <label for="loginRecuperacao">Login:</label>
                        <input type="text" id="loginRecuperacao" class="form-control" placeholder="Informe seu login" autofocus>
                    </div>
                    <?php if (defined('GOOGLE_KEY')) { ?>
                    <div class="g-recaptcha" data-sitekey="<?php echo GOOGLE_KEY ?>" style="margin:10px 0;"></div>
                    <?php } ?>
                    <button id="btnRecuperar" class="btn btn-primary btn-block" onclick="solicitarRecuperacao()">
                        <i class="fa fa-envelope"></i> Enviar
                    </button>
                </div>
            </div>
            <?php if (defined('GOOGLE_KEY')) { ?>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <?php } ?>
            <div style="text-align:center; margin-top:10px;">
                <a href="index.php?content=view/admin/login.php" style="font-size:13px;">
                    <i class="fa fa-arrow-left"></i> Voltar ao login
                </a>
            </div>

        </div>
    </div>
</div>
