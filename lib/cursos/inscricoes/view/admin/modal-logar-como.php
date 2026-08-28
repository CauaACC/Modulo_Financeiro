<?php
/**
 * Modal + Form + JS para "Logar Como" outro usuario.
 *
 * Variaveis obrigatorias (definir ANTES do include):
 *   $logarComoController  - nome do controller (ex: 'GerenciarSupervisor')
 *   $logarComoMethod      - nome do metodo     (ex: 'logarComoSupervisor')
 */
?>
<script type="text/javascript">
var _logarComoLogin = '';
function confirmarLogarComo(login) {
    _logarComoLogin = login;
    $('#logarComoNomeUsuario').text(login);
    // Mover modal e form para o body para evitar ficar atras do backdrop
    if (!$('#modalLogarComo').data('moved')) {
        $('#modalLogarComo').appendTo('body');
        $('#formLogarComo').appendTo('body');
        $('#modalLogarComo').data('moved', true);
    }
    $('#modalLogarComo').modal('show');
}
function executarLogarComo() {
    if (!_logarComoLogin) return;
    $('#modalLogarComo').modal('hide');
    $('#formLogarComo input[name=login]').val(_logarComoLogin);
    $('#formLogarComo').submit();
}
</script>

<!-- Modal: Confirmar Logar Como -->
<div class="modal fade" id="modalLogarComo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-user-secret"></i> Logar Como</h4>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja logar como:</p>
                <p><strong id="logarComoNomeUsuario"></strong></p>
                <p class="text-muted" style="font-size:12px;">Esta a&ccedil;&atilde;o ser&aacute; registrada no log de auditoria. A sess&atilde;o expira em 30 minutos.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="executarLogarComo()"><img src="resources/image/icone_preview.gif" border="0" style="vertical-align:middle;"> Confirmar</button>
            </div>
        </div>
    </div>
</div>
<form id="formLogarComo" method="POST" action="<?php echo WB_URL_COMMAND ?>" style="display:none;">
    <input type="hidden" name="c" value="<?php echo htmlspecialchars($logarComoController) ?>">
    <input type="hidden" name="m" value="<?php echo htmlspecialchars($logarComoMethod) ?>">
    <input type="hidden" name="login" value="">
    <input type="hidden" name="_submit_check" value="1">
</form>
