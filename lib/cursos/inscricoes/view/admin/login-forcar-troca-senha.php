<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if (!$_SESSION['_logado'] || !$_SESSION['_administrativo']) {
    $url = WB_URL_VIEW . "?content=view/admin/login.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
    exit();
}
$trocaObrigatoria = (isset($fwsGet_['motivo']) && $fwsGet_['motivo'] === 'obrigatoria');
?>
<script type="text/javascript" src="resources/js/default.js"></script>
<script type="text/javascript">
    function trocarSenhaObrigatoria() {
        var senhaAtual = $('#senhaAtual').val();
        var senhaNova = $('#senhaNova').val();
        var senhaNovaRepetir = $('#senhaNovaRepetir').val();
        $('#msgTroca').html('');

        if (!senhaAtual || !senhaNova || !senhaNovaRepetir) {
            $('#msgTroca').html('<div class="alert alert-danger">Preencha todos os campos.</div>');
            return;
        }
        if (senhaNova !== senhaNovaRepetir) {
            $('#msgTroca').html('<div class="alert alert-danger">As senhas novas n&atilde;o conferem.</div>');
            return;
        }
        if (senhaNova.length < 8) {
            $('#msgTroca').html('<div class="alert alert-danger">A nova senha deve ter pelo menos 8 caracteres.</div>');
            return;
        }

        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarLogin',
                'm': 'trocarSenhaSegura',
                'senhaAtual': senhaAtual,
                'senhaNova': senhaNova,
                'senhaNovaRepetir': senhaNovaRepetir,
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data.statusMessage) {
                    $('#msgTroca').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + data.message + ' Redirecionando...</div>');
                    setTimeout(function() {
                        window.location.href = '<?php echo WB_URL_VIEW ?>?content=view/admin/default.php';
                    }, 2000);
                } else {
                    $('#msgTroca').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + data.message + '</div>');
                }
            },
            error: function() {
                $('#msgTroca').html('<div class="alert alert-danger">Erro ao processar. Tente novamente.</div>');
            }
        });
    }
</script>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><i class="fa fa-lock"></i> <?php echo $trocaObrigatoria ? 'Troca de Senha Obrigat&oacute;ria' : 'Alterar Senha'; ?></h4>
        </div>
        <div class="panel-body">

            <?php if ($trocaObrigatoria): ?>
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                <strong>Aten&ccedil;&atilde;o:</strong> Por quest&otilde;es de seguran&ccedil;a, &eacute; necess&aacute;rio trocar sua senha antes de continuar.
            </div>
            <?php endif; ?>

            <div id="msgTroca"></div>

            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    <div class="form-group">
                        <label for="senhaAtual">Senha Atual:</label>
                        <input type="password" id="senhaAtual" class="form-control" placeholder="Informe sua senha atual">
                    </div>
                    <div class="form-group">
                        <label for="senhaNova">Nova Senha:</label>
                        <input type="password" id="senhaNova" class="form-control" placeholder="M&iacute;nimo 8 caracteres, letras, n&uacute;meros e especiais">
                    </div>
                    <div class="form-group">
                        <label for="senhaNovaRepetir">Confirmar Nova Senha:</label>
                        <input type="password" id="senhaNovaRepetir" class="form-control" placeholder="Repita a nova senha">
                    </div>
                    <button class="btn btn-primary btn-block" onclick="trocarSenhaObrigatoria()">
                        <i class="fa fa-key"></i> Alterar Senha
                    </button>
                    <p class="text-muted" style="font-size:12px; margin-top:10px;">
                        A senha deve ter pelo menos 8 caracteres, contendo letras, n&uacute;meros e caracteres especiais.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
