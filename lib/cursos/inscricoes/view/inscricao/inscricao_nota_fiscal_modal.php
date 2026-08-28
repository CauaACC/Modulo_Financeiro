<?php
// Modal de solicitacao de Nota Fiscal + JS de envio
// Incluir UMA vez por pagina, depois dos botoes
// Esperado do escopo pai: $objInscrito, $objEvConf
if (!isset($objEvConf) || $objEvConf->getSolicitacaoNfHabilitada() != 1) {
    return;
}
?>
<div class="modal fade" id="modalSolicitarNf" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <form id="formSolicitarNf">
            <input type="hidden" name="cod_pagamento" id="nfCodPagamento" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Solicitar Nota Fiscal</h5>
                </div>
                <div class="modal-body">
                    <div id="modalSolicitarNfMsg"></div>
                    <p>Confira/ajuste os dados do tomador da Nota Fiscal antes de enviar:</p>
                    <div class="form-group">
                        <label>CPF/CNPJ <span style="color:red">*</span></label>
                        <input type="text" name="tomador_cpf_cnpj" class="form-control" maxlength="20" required>
                    </div>
                    <div class="form-group">
                        <label>Nome/Raz&atilde;o Social <span style="color:red">*</span></label>
                        <input type="text" name="tomador_nome" class="form-control" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label>Endere&ccedil;o</label>
                        <input type="text" name="tomador_endereco" class="form-control" maxlength="500">
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>CEP</label>
                            <input type="text" name="tomador_cep" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-5 form-group">
                            <label>Cidade</label>
                            <input type="text" name="tomador_cidade" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>UF</label>
                            <input type="text" name="tomador_uf" class="form-control" maxlength="2">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="tomador_email" class="form-control" maxlength="255">
                    </div>
                    <div class="form-group">
                        <label>Observa&ccedil;&atilde;o (opcional)</label>
                        <textarea name="observacao_inscrito" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar Solicita&ccedil;&atilde;o</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
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
        el.style.display = 'block';
        el.classList.add('show','in');
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

    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"]', function(){
        var modal = $(this).closest('.modal');
        if (modal.length) ocultarModalCompat(modal.attr('id'));
    });

    var dadosTomadorInscrito = {
        cpf:       <?php echo json_encode($objInscrito->getCpfRecibo() ? $objInscrito->getCpfRecibo() : $objInscrito->getCpf()); ?>,
        nome:      <?php echo json_encode($objInscrito->getNomeRecibo() ? $objInscrito->getNomeRecibo() : $objInscrito->getNomeInscrito()); ?>,
        endereco:  <?php echo json_encode(($objInscrito->getEnderecoRecibo() ? $objInscrito->getEnderecoRecibo() : $objInscrito->getEndereco()) . ($objInscrito->getNumeroRecibo() ? ', ' . $objInscrito->getNumeroRecibo() : ($objInscrito->getNumero() ? ', ' . $objInscrito->getNumero() : ''))); ?>,
        cep:       <?php echo json_encode($objInscrito->getCepRecibo() ? $objInscrito->getCepRecibo() : $objInscrito->getCep()); ?>,
        cidade:    <?php echo json_encode($objInscrito->getCidadeRecibo() ? $objInscrito->getCidadeRecibo() : $objInscrito->getCidade()); ?>,
        uf:        <?php echo json_encode($objInscrito->getEstadoRecibo() ? $objInscrito->getEstadoRecibo() : $objInscrito->getEstado()); ?>,
        email:     <?php echo json_encode($objInscrito->getEMail()); ?>
    };

    $(document).on('click', '.js-btn-solicitar-nf', function(){
        var codPagamento = $(this).data('cod-pagamento');
        $('#modalSolicitarNfMsg').html('');
        $('#formSolicitarNf')[0].reset();
        $('#nfCodPagamento').val(codPagamento);
        $('input[name=tomador_cpf_cnpj]').val(dadosTomadorInscrito.cpf);
        $('input[name=tomador_nome]').val(dadosTomadorInscrito.nome);
        $('input[name=tomador_endereco]').val(dadosTomadorInscrito.endereco);
        $('input[name=tomador_cep]').val(dadosTomadorInscrito.cep);
        $('input[name=tomador_cidade]').val(dadosTomadorInscrito.cidade);
        $('input[name=tomador_uf]').val(dadosTomadorInscrito.uf);
        $('input[name=tomador_email]').val(dadosTomadorInscrito.email);
        mostrarModalCompat('modalSolicitarNf');
    });

    $('#formSolicitarNf').submit(function(e){
        e.preventDefault();
        $('#modalSolicitarNfMsg').html('');
        $.ajax({
            type: 'POST',
            url: '<?php echo WB_URL . "/" . WB_GRUPO . "/" . WB_PROJETO ?>/api/nota-fiscal/solicitar',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(data){
                if (data.statusMessage) {
                    ocultarModalCompat('modalSolicitarNf');
                    $('#sucesso').removeClass('hidden').html(data.message || 'Solicita&ccedil;&atilde;o registrada.');
                    setTimeout(function(){ location.reload(); }, 1200);
                } else {
                    $('#modalSolicitarNfMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro.') + '</div>');
                }
            },
            error: function(){
                $('#modalSolicitarNfMsg').html('<div class="alert alert-danger">Erro de comunica&ccedil;&atilde;o.</div>');
            }
        });
    });
})();
</script>
