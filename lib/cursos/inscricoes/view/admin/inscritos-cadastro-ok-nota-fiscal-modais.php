<?php
// Modais globais de Anexar e Substituir NF - admin
// Esperado do escopo pai: $objEvConf
// Incluir UMA vez por pagina, apos o loop de pagamentos
if (!isset($objEvConf) || $objEvConf->getSolicitacaoNfHabilitada() != 1) {
    return;
}
?>
<div id="sucessoNfAdmin" class="hidden"></div>
<div id="erroNfAdmin" class="hidden"></div>

<!-- Modal Anexar NF -->
<div class="modal fade" id="modalAnexarNf" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <form id="formAnexarNf" enctype="multipart/form-data">
            <input type="hidden" name="cod_solicitacao" id="anexarCodSolicitacao" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Anexar Nota Fiscal</h5>
                </div>
                <div class="modal-body">
                    <div id="modalAnexarNfMsg"></div>
                    <label>Selecione o PDF da Nota Fiscal <span style="color:red">*</span></label>
                    <input type="file" name="arquivoNf" accept="application/pdf" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Anexar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Substituir NF -->
<div class="modal fade" id="modalSubstituirNf" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <form id="formSubstituirNf" enctype="multipart/form-data">
            <input type="hidden" name="cod_solicitacao" id="substituirCodSolicitacao" value="">
            <div class="modal-content">
                <div class="modal-header" style="background:#f0ad4e;color:#fff;">
                    <h5 class="modal-title">Substituir Nota Fiscal</h5>
                </div>
                <div class="modal-body">
                    <div id="modalSubstituirNfMsg"></div>
                    <div class="alert alert-warning">
                        A NF atual ser&aacute; mantida no hist&oacute;rico e substitu&iacute;da pela nova. Informe o motivo.
                    </div>
                    <label>Motivo da substitui&ccedil;&atilde;o <span style="color:red">*</span></label>
                    <textarea name="motivo_desativacao" class="form-control" rows="3" required maxlength="500"></textarea>
                    <br>
                    <label>Novo PDF da Nota Fiscal <span style="color:red">*</span></label>
                    <input type="file" name="arquivoNf" accept="application/pdf" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Confirmar Substitui&ccedil;&atilde;o</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    var urlAnexar     = '<?php echo WB_URL . "/" . WB_GRUPO . "/" . WB_PROJETO ?>/api/nota-fiscal/anexar';
    var urlSubstituir = '<?php echo WB_URL . "/" . WB_GRUPO . "/" . WB_PROJETO ?>/api/nota-fiscal/substituir';

    function mostrarModalCompat(id){
        var el = document.getElementById(id);
        if (!el) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }
        if (typeof $ !== 'undefined' && $.fn && typeof $.fn.modal === 'function') {
            $('#' + id).modal('show');
            return;
        }
        // Fallback manual: simula Bootstrap modal sem depender de JS de biblioteca
        // Fallback: confia no CSS do BS3/BS5 que ja esta carregado.
        // Apenas seta display e classes que acionam a visualizacao.
        el.style.display = 'block';
        el.classList.add('show','in'); // BS5: show, BS3: in
        el.removeAttribute('aria-hidden');
        el.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
        if (!document.querySelector('.modal-backdrop[data-for="' + id + '"]')) {
            var bd = document.createElement('div');
            bd.className = 'modal-backdrop fade show in';
            bd.setAttribute('data-for', id);
            document.body.appendChild(bd);
        }
    }
    function ocultarModalCompat(id){
        var el = document.getElementById(id);
        if (!el) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var inst = bootstrap.Modal.getInstance(el);
            if (inst) { inst.hide(); return; }
        }
        if (typeof $ !== 'undefined' && $.fn && typeof $.fn.modal === 'function') {
            $('#' + id).modal('hide');
            return;
        }
        el.style.display = 'none';
        el.classList.remove('show','in');
        el.setAttribute('aria-hidden', 'true');
        el.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
        var bd = document.querySelector('.modal-backdrop[data-for="' + id + '"]');
        if (bd) bd.remove();
    }

    // Liga os botoes "Cancelar"/X para usar o fallback quando BS/jQuery.modal nao existem
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"]', function(){
        var modal = $(this).closest('.modal');
        if (modal.length) ocultarModalCompat(modal.attr('id'));
    });

    $(document).on('click', '.js-btn-anexar-nf', function(){
        $('#modalAnexarNfMsg').html('');
        $('#formAnexarNf')[0].reset();
        $('#anexarCodSolicitacao').val($(this).data('cod-solicitacao'));
        mostrarModalCompat('modalAnexarNf');
    });
    $(document).on('click', '.js-btn-substituir-nf', function(){
        $('#modalSubstituirNfMsg').html('');
        $('#formSubstituirNf')[0].reset();
        $('#substituirCodSolicitacao').val($(this).data('cod-solicitacao'));
        mostrarModalCompat('modalSubstituirNf');
    });
    $(document).on('click', '.js-btn-hist-nf', function(e){
        e.preventDefault();
        $($(this).data('target')).slideToggle();
    });

    function enviarFormNf(form, url, modalId, msgBoxId, msgSucessoPadrao){
        $(msgBoxId).html('');
        var fd = new FormData(form);
        $.ajax({
            url: url,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data){
                if (data && data.statusMessage) {
                    ocultarModalCompat(modalId);
                    $('#sucessoNfAdmin').removeClass('hidden').html(data.message || msgSucessoPadrao);
                    setTimeout(function(){ location.reload(); }, 1200);
                } else {
                    var msg = (data && data.message) ? data.message : 'Erro.';
                    $(msgBoxId).html('<div class="alert alert-danger">' + msg + '</div>');
                }
            },
            error: function(xhr){
                var msg = 'Erro de comunica&ccedil;&atilde;o.';
                if (xhr && xhr.responseText) {
                    msg += ' (HTTP ' + xhr.status + ')';
                }
                $(msgBoxId).html('<div class="alert alert-danger">' + msg + '</div>');
            }
        });
    }

    $('#formAnexarNf').submit(function(e){
        e.preventDefault();
        enviarFormNf(this, urlAnexar, 'modalAnexarNf', '#modalAnexarNfMsg', 'NF anexada.');
    });
    $('#formSubstituirNf').submit(function(e){
        e.preventDefault();
        enviarFormNf(this, urlSubstituir, 'modalSubstituirNf', '#modalSubstituirNfMsg', 'NF substituida.');
    });
})();
</script>
