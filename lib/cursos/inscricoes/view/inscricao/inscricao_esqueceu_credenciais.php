<h3>Área do Inscrito</h3>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <div id="msgEsqueci"></div>

            <form class="form-signin" name="form" id="formEsqueci" onsubmit="return false;">
                <input type="hidden" id="csrfEsqueci" value="<?php echo GerenciarLoginAC::novoTokenCsrf() ?>">
                <label for="e_mail">Informe o e-mail utilizado na inscrição:</label>
                <input type="email" name="e_mail" id="e_mail" value="<?php echo addslashesopctec($_GET['e_mail']) ?>"
                    size='20' class="form_campos form-control" onBlur="trim(this);"
                    placeholder="E-mail da inscrição" required autofocus>

                <br>
                <font color="#555" style="font-size:13px;">
                    Enviaremos para o seu e-mail um <b>link para criar uma nova senha</b>.
                    Verifique a caixa de entrada e o spam antes de pedir novamente.
                </font>

                <br><br>
                <button class="btn btn-lg btn-primary btn-block" type="button" id="btnEsqueci"
                        onclick="solicitarLinkSenha()">Enviar link de senha</button>

                <div style="text-align:center; margin-top:10px;">
                    <a href="index.php?content=view/inscricao/inscricao_01.php" style="font-size:13px;">Voltar ao login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script language="JavaScript1.2" src="resources/js/default.js"></script>
<script type="text/javascript">
    function solicitarLinkSenha() {
        var email = $('#e_mail').val();
        $('#msgEsqueci').html('');
        if (!isEmail(email)) {
            $('#msgEsqueci').html('<div class="alert alert-danger">Informe um e-mail v&aacute;lido.</div>');
            $('#e_mail').focus();
            return;
        }
        $('#btnEsqueci').prop('disabled', true).html('Enviando...');
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarLogin',
                'm': 'solicitarLinkSenhaInscrito',
                'e_mail': email,
                'csrf': $('#csrfEsqueci').val(),
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                // Resposta sempre neutra (nao revela se o e-mail existe).
                $('#formEsqueci').hide();
                $('#msgEsqueci').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + data.message + '</div>');
            },
            error: function() {
                $('#btnEsqueci').prop('disabled', false).html('Enviar link de senha');
                $('#msgEsqueci').html('<div class="alert alert-danger">Erro ao processar. Tente novamente.</div>');
            }
        });
    }
</script>
