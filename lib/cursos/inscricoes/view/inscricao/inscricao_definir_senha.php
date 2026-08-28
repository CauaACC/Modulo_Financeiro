<?php
$token = isset($_GET['token']) ? trim(addslashesopctec($_GET['token'])) : '';
if ($token === '') {
    $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
    exit();
}
?>
<h3>Área do Inscrito</h3>
<script type="text/javascript" src="resources/js/default.js"></script>
<script type="text/javascript">
(function esperarJq() {
    if (typeof jQuery === 'undefined') { return setTimeout(esperarJq, 50); }
    var $ = jQuery;

    $(function() {
        // Valida o token ao abrir a pagina (o formulario comeca oculto).
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarLogin',
                'm': 'validarTokenSenhaInscrito',
                'token': '<?php echo htmlspecialchars($token, ENT_QUOTES) ?>',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data.statusMessage) {
                    if (data.email) {
                        $('#emailDoToken').text(data.email);
                        $('#linhaEmail').show();
                    }
                    // Vale mais dizer o tempo que resta do que "expira em 1 hora":
                    // quem abre o link 50 minutos depois precisa saber que e agora.
                    if (data.minutosRestantes > 0) {
                        var m = data.minutosRestantes;
                        $('#prazoToken').text(m === 1 ? 'Este link expira em 1 minuto.'
                                                      : 'Este link expira em ' + m + ' minutos.');
                        $('#prazoToken').show();
                    }
                    $('#formDefinir').show();
                    $('#infoSenha').show();
                } else {
                    $('#msgDefinir').html(
                        '<div class="alert alert-danger" style="margin-bottom:0;">' +
                            '<i class="fa fa-exclamation-circle"></i> ' +
                            'Este link &eacute; <strong>inv&aacute;lido ou j&aacute; expirou</strong>.' +
                            '<br><small>Os links expiram em 1 hora e s&oacute; podem ser usados uma vez.</small>' +
                        '</div>' +
                        '<div style="margin-top:18px; text-align:center;">' +
                            '<a href="<?php echo WB_URL_VIEW ?>?content=view/inscricao/inscricao_esqueceu_credenciais.php" class="btn btn-primary" style="margin-right:8px;">Solicitar novo link</a>' +
                            '<a href="<?php echo WB_URL_VIEW ?>?content=view/inscricao/inscricao_01.php" class="btn btn-default">Voltar ao login</a>' +
                        '</div>'
                    );
                }
                $('#carregandoToken').hide();
            },
            error: function() {
                $('#carregandoToken').hide();
                $('#msgDefinir').html('<div class="alert alert-danger">Erro ao validar o link. Tente novamente.</div>');
            }
        });
    });

    // Mostrar senha: troca o type dos dois campos de uma vez. Em campo mascarado
    // sem conferencia visual o erro de digitacao so aparece no "nao conferem".
    $(function() {
        $(document).on('change', '#verSenha', function() {
            var tipo = this.checked ? 'text' : 'password';
            $('#senhaNova, #senhaNovaRepetir').attr('type', tipo);
        });
    });

    window.definirSenhaInscrito = function() {
        var senhaNova = $('#senhaNova').val();
        var senhaNovaRepetir = $('#senhaNovaRepetir').val();
        $('#msgDefinir').html('');

        if (!senhaNova || !senhaNovaRepetir) {
            $('#msgDefinir').html('<div class="alert alert-danger">Preencha os dois campos.</div>');
            return;
        }
        if (senhaNova !== senhaNovaRepetir) {
            $('#msgDefinir').html('<div class="alert alert-danger">As senhas n&atilde;o conferem.</div>');
            return;
        }
        if (senhaNova.length < 6) {
            $('#msgDefinir').html('<div class="alert alert-danger">A senha deve ter pelo menos 6 caracteres.</div>');
            return;
        }

        $('#btnDefinir').prop('disabled', true).html('Processando...');

        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarLogin',
                'm': 'definirSenhaInscritoPorToken',
                'token': '<?php echo htmlspecialchars($token, ENT_QUOTES) ?>',
                'senhaNova': senhaNova,
                'senhaNovaRepetir': senhaNovaRepetir,
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data.statusMessage) {
                    $('#msgDefinir').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + data.message + '</div>');
                    $('#formDefinir').hide();
                    setTimeout(function() {
                        window.location.href = '<?php echo WB_URL_VIEW ?>?content=view/inscricao/inscricao_01.php';
                    }, 3000);
                } else {
                    $('#msgDefinir').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + data.message + '</div>');
                    $('#btnDefinir').prop('disabled', false).html('Salvar senha');
                }
            },
            error: function() {
                $('#msgDefinir').html('<div class="alert alert-danger">Erro ao processar. Tente novamente.</div>');
                $('#btnDefinir').prop('disabled', false).html('Salvar senha');
            }
        });
    };
})();
</script>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Criar / redefinir senha</h4></div>
        <div class="panel-body">

            <div id="carregandoToken" class="text-center" style="padding:30px;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p class="text-muted" style="margin-top:10px;">Validando o link...</p>
            </div>

            <div id="infoSenha" class="alert alert-info" style="display:none;">
                <div id="linhaEmail" style="display:none; margin-bottom:8px;">
                    <i class="fa fa-user"></i> Definindo a senha de
                    <strong id="emailDoToken"></strong>
                </div>
                Defina sua senha abaixo. M&iacute;nimo de 6 caracteres. Ela vale para todos os
                eventos em que voc&ecirc; est&aacute; inscrito com este e-mail.
                <div id="prazoToken" class="text-muted" style="display:none; margin-top:8px; font-size:12px;"></div>
            </div>

            <div id="msgDefinir"></div>

            <div id="formDefinir" class="row" style="display:none;">
                <div class="col-sm-6 col-sm-offset-3">
                    <!-- autocomplete="new-password" nos dois: sem isso o gerenciador do
                         navegador preenchia "Confirmar senha" com a senha salva e deixava
                         a de cima vazia, e a pessoa levava "as senhas nao conferem". -->
                    <div class="form-group">
                        <label for="senhaNova">Nova senha:</label>
                        <input type="password" id="senhaNova" class="form-control"
                               autocomplete="new-password" placeholder="M&iacute;nimo 6 caracteres">
                    </div>
                    <div class="form-group">
                        <label for="senhaNovaRepetir">Confirmar senha:</label>
                        <input type="password" id="senhaNovaRepetir" class="form-control"
                               autocomplete="new-password" placeholder="Repita a senha">
                    </div>
                    <div class="checkbox" style="margin-top:-4px; margin-bottom:14px;">
                        <label style="font-weight:normal; font-size:13px;">
                            <input type="checkbox" id="verSenha"> Mostrar senha
                        </label>
                    </div>
                    <button id="btnDefinir" class="btn btn-primary btn-block" onclick="definirSenhaInscrito()">Salvar senha</button>
                    <div style="text-align:center; margin-top:10px;">
                        <a href="index.php?content=view/inscricao/inscricao_01.php" style="font-size:13px;">Voltar ao login</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
