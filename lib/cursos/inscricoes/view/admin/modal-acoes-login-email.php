<?php
/**
 * Modal reutilizavel de "acoes de login" por e-mail: gerar/definir senha,
 * enviar por e-mail, logar como e abrir inscricao. So depende de WB_URL_COMMAND
 * e jQuery/Bootstrap (ja carregados nas telas admin). Chame abrirModalLoginEmail(email)
 * de qualquer onclick pra abrir.
 *
 * Usado em: DashboardUsuariosGeral.php (lista de tentativas por e-mail) e
 * GerenciarTrocaSenhaInscrito.php (historico de trocas de senha).
 * Endpoints AJAX ficam todos em GerenciarTrocaSenhaInscritoAC.php.
 */
?>
<div class="modal fade" id="modalLoginEmail" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-key"></i> Login de Inscrito &mdash; <span id="mleEmailTitulo"></span></h4>
            </div>
            <div class="modal-body">
                <div id="mleInfo"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>
                <div id="mleSenha" style="margin-top:10px;"></div>
                <div id="mleBotoesAcesso" style="margin-top:10px;"></div>
            </div>
        </div>
    </div>
</div>
<script>
var mlePodeManual = <?php echo ((int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0) === 668) ? 'true' : 'false'; ?>;
var mleEmailAtual = '';
// tokens CSRF de uso unico -- vem do backend a cada passo, nao sao gerados aqui.
// Sem isso, definirSenhaAdmin/enviarSenhaEmail (mudam senha e mandam e-mail)
// ficariam forjaveis via CSRF classico usando a sessao logada do admin.
var mleCsrfDefinir = '';
var mleCsrfEnviar = '';

function abrirModalLoginEmail(email) {
    mleEmailAtual = email;
    mleCsrfDefinir = '';
    mleCsrfEnviar = '';
    $('#mleEmailTitulo').text(email);
    $('#mleInfo').html('<i class="fa fa-spinner fa-spin"></i> Carregando...');
    $('#mleSenha').html('');
    $('#mleBotoesAcesso').html('<i class="fa fa-spinner fa-spin"></i> Carregando...');
    $('#modalLoginEmail').modal('show');
    mleBuscarInfo(email);
    mleCarregarBotoes(email);
}

function mleBuscarInfo(email) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarTrocaSenhaInscrito', 'm': 'buscarInscritoSenha', 'email': email, '_submit_check': '1'
    }, function (resp) {
        var d; try { d = JSON.parse(resp); } catch (e) { d = null; }
        if (!d || !d.ok) { $('#mleInfo').html('<span style="color:#c0392b;">' + (d && d.msg ? d.msg : 'Erro na busca.') + '</span>'); return; }
        mleCsrfDefinir = d.csrf || '';
        var ult4Seguro = $('<span>').text(d.ult4 || '&mdash;').html();
        var aviso = d.tem_cpf ? '' : '<div style="color:#c0392b; font-size:12px; margin-top:4px;">Sem CPF cadastrado &mdash; o inscrito n&atilde;o consegue logar (o login pede os 4 d&iacute;gitos do CPF).</div>';
        var jaTem = d.com_senha_propria > 0 ? '<span style="color:#2d6a4f;"> &middot; j&aacute; tem senha pr&oacute;pria</span>' : '';
        var manual = mlePodeManual
            ? ('<span style="color:#999; font-size:12px;">ou</span>' +
               '<input type="text" id="mleSenhaManual" placeholder="digite uma senha (min. 6)" class="form-control input-sm" style="width:210px; display:inline-block;" onkeydown="if(event.key===\'Enter\'){mleDefinir($(\'#mleSenhaManual\').val());return false;}">' +
               '<button class="btn btn-sm" onclick="mleDefinir($(\'#mleSenhaManual\').val())" style="background:#2d6a4f; color:#fff; border:none; border-radius:6px; padding:6px 16px; font-size:12px;"><i class="fa fa-key"></i> Definir esta</button>')
            : '';
        $('#mleInfo').html(
            '<div style="background:#f7f7f7; border:1px solid #eee; border-radius:8px; padding:10px 14px;">' +
              '<b>' + $('<span>').text(d.nome).html() + '</b>' + jaTem + '<br>' +
              '4 &uacute;ltimos do CPF: <b>' + ult4Seguro + '</b> &nbsp;|&nbsp; inscri&ccedil;&otilde;es ativas: <b>' + d.ativas + '</b>' +
              aviso +
              '<div style="margin-top:10px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">' +
                '<button class="btn btn-sm" onclick="mleDefinir()" style="background:#c0392b; color:#fff; border:none; border-radius:6px; padding:6px 16px; font-size:12px;"><i class="fa fa-random"></i> Gerar aleat&oacute;ria</button>' +
                manual +
              '</div>' +
            '</div>'
        );
    });
}

function mleDefinir(senhaManual) {
    senhaManual = senhaManual ? $.trim(senhaManual) : '';
    if (senhaManual && senhaManual.length < 6) { alert('A senha deve ter ao menos 6 caracteres.'); return; }
    if (!mleCsrfDefinir) { alert('Sessão do formulário expirada. Feche e reabra o modal.'); return; }
    var oque = senhaManual ? 'Definir a senha informada' : 'Gerar uma nova senha';
    if (!confirm(oque + ' para ' + mleEmailAtual + '?\nSubstitui a senha atual em TODAS as inscrições dessa pessoa.')) return;
    $('#mleSenha').html('<i class="fa fa-spinner fa-spin"></i> Gravando...');
    var dados = { 'c': 'GerenciarTrocaSenhaInscrito', 'm': 'definirSenhaAdmin', 'email': mleEmailAtual, 'csrf': mleCsrfDefinir, '_submit_check': '1' };
    if (senhaManual) dados.senha = senhaManual;
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados, function (resp) {
        var d; try { d = JSON.parse(resp); } catch (e) { d = null; }
        if (!d || !d.ok) { $('#mleSenha').html('<span style="color:#c0392b;">' + (d && d.msg ? d.msg : 'Erro ao gerar.') + '</span>'); return; }
        mleCsrfEnviar = d.csrfEnviar || '';
        var ult4Seguro = $('<span>').text(d.ult4 || '&mdash;').html();
        $('#mleSenha').html(
            '<div style="background:#eafaf1; border:1px solid #b7e4c7; border-radius:8px; padding:12px 14px;">' +
              '<div style="font-size:12px; color:#555;">Passe estes dados para a pessoa (ela troca depois):</div>' +
              '<div style="margin-top:6px; font-size:14px;">E-mail: <b>' + $('<span>').text(d.email).html() + '</b></div>' +
              '<div style="font-size:14px;">Senha: <b style="font-size:17px; letter-spacing:1px; color:#1b4332;">' + $('<span>').text(d.senha).html() + '</b></div>' +
              '<div style="font-size:14px;">4 &uacute;ltimos do CPF: <b>' + ult4Seguro + '</b></div>' +
              '<div style="margin-top:6px; font-size:11px; color:#888;">Gravada em ' + d.inscricoes + ' inscri&ccedil;&atilde;o(&otilde;es) &mdash; vale em todos os eventos dessa pessoa.</div>' +
              '<div style="margin-top:10px;">' +
                '<button class="btn btn-sm" onclick="mleEnviarEmail()" style="background:#2980b9; color:#fff; border:none; border-radius:6px; padding:6px 16px; font-size:12px;"><i class="fa fa-envelope"></i> Enviar por e-mail ao inscrito</button>' +
                '<span id="mleEnvioMsg" style="margin-left:10px; font-size:12px;"></span>' +
              '</div>' +
            '</div>'
        );
    });
}

function mleEnviarEmail() {
    if (!mleCsrfEnviar) return;
    $('#mleEnvioMsg').html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarTrocaSenhaInscrito', 'm': 'enviarSenhaEmail', 'csrf': mleCsrfEnviar, '_submit_check': '1'
    }, function (resp) {
        var d; try { d = JSON.parse(resp); } catch (e) { d = null; }
        mleCsrfEnviar = ''; // one-shot, mesmo se der erro
        if (d && d.ok) $('#mleEnvioMsg').html('<span style="color:#2d6a4f;"><i class="fa fa-check"></i> Enviado para ' + $('<span>').text(d.para).html() + '</span>');
        else $('#mleEnvioMsg').html('<span style="color:#c0392b;">' + (d && d.msg ? d.msg : 'Erro ao enviar') + '</span>');
    });
}

function mleCarregarBotoes(email) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarTrocaSenhaInscrito', 'm': 'renderBotoesAcessoPorEmail', 'email': email, '_submit_check': '1'
    }, function (html) {
        $('#mleBotoesAcesso').html(html);
    });
}
</script>
