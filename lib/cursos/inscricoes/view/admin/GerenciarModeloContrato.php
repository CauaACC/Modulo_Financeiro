<?php
require_once(dirname(__FILE__) . "/../../view/admin/evento-seguranca.php");
$objSRV = new GerenciarModeloContratoAC();
?>
<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_configuracao.php");
?>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Modelos de Contrato</h4>
        <button type="button" id="btnNovoModelo" class="btn btn-sm abriModal">
            <i class="fa fa-plus"></i> Adicionar Modelo
        </button>
    </div>

    <div style="padding:12px 18px;">
        <div class="alert alert-info" style="font-size:12px; margin-bottom:10px;">
            <b>Placeholders dispon&iacute;veis para o template DOCX:</b><br>
            <code>${NOME}</code> <code>${CPF}</code> <code>${RG}</code> <code>${NASCIMENTO}</code>
            <code>${ESTADO_CIVIL}</code> <code>${PROFISSAO}</code> <code>${ENDERECO}</code>
            <code>${NUMERO}</code> <code>${COMPLEMENTO}</code> <code>${BAIRRO}</code>
            <code>${CIDADE}</code> <code>${ESTADO}</code> <code>${CEP}</code>
            <code>${DDD}</code> <code>${TELEFONE}</code> <code>${EMAIL}</code>
            <code>${NOME_MAE}</code> <code>${NOME_PAI}</code> <code>${INSTITUICAO}</code>
            <code>${CARGO_ATUAL}</code> <code>${GRAU_ESCOLARIDADE}</code>
            <code>${CIDADE_UF}</code> <code>${TELEFONE_COMPLETO}</code>
            <code>${DATA_LOCAL}</code> <code>${DATA_ATUAL}</code>
        </div>
    </div>

    <div>
        <table class="tbl-fin" id="itensModelo">
            <thead>
            <tr>
                <th data-col="0" style="cursor:pointer;">Nome <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="1" style="cursor:pointer;">Arquivo <span class="sort-arrow">&uarr;&darr;</span></th>
                <th>Campos Obrigat&oacute;rios</th>
                <th data-col="3" style="cursor:pointer; width:60px;">Ordem <span class="sort-arrow">&uarr;&darr;</span></th>
                <th style="width:90px;">A&ccedil;&otilde;es</th>
            </tr>
            </thead>
            <tbody>
            <?php $objSRV->carregarTabelaModeloContrato($fws_, $debug); ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Modelo Contrato -->
<div class="modal fade" id="modalModeloContrato" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalModeloContratoTitulo"><i class="fa fa-file-word-o"></i> Novo Modelo de Contrato</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formModeloContrato" name="formModeloContrato" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post" enctype="multipart/form-data" novalidate>
                    <div class="form-group mb-3">
                        <label for="nome">Nome do modelo: <span class="text-danger">*</span></label>
                        <input type="text" name="nome" id="nomeModelo" class="form-control" placeholder="Ex: Contrato Educacional" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="descricao">Descri&ccedil;&atilde;o (vis&iacute;vel para o inscrito):</label>
                        <textarea name="descricao" id="descricaoModelo" class="form-control" rows="3" placeholder="Descri&ccedil;&atilde;o opcional"></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="arquivo">Arquivo DOCX (template com placeholders): <span class="text-danger" id="lblArquivoObrigatorio">*</span></label>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" onclick="$('#arquivoModelo').click();" style="height:34px;"><i class="fa fa-folder-open"></i> Procurar</button>
                            </span>
                            <input type="text" class="form-control" id="nomeArquivoModelo" readonly placeholder="Nenhum arquivo selecionado" style="height:34px;">
                        </div>
                        <input type="file" id="arquivoModelo" name="arquivo" accept=".docx" style="display:none;" onchange="$('#nomeArquivoModelo').val(this.files.length ? this.files[0].name : '');">
                        <small id="arquivoAtual" class="text-muted" style="display:none;">Arquivo atual: <span id="arquivoAtualNome"></span></small>
                    </div>

                    <div class="form-group mb-3">
                        <label for="formatoSaida">Formato de download para o inscrito:</label>
                        <select name="formatoSaida" id="formatoSaida" class="form-control" style="width:200px;">
                            <option value="DOCX">DOCX (Word)</option>
                            <option value="PDF">PDF</option>
                        </select>
                        <small class="text-muted">
                            O PDF mant&eacute;m cabe&ccedil;alho, rodap&eacute;, tabelas e
                            pagina&ccedil;&atilde;o iguais ao documento do Word.
                        </small>
                    </div>

                    <div class="form-group mb-3">
                        <label for="ordem">Ordem de exibi&ccedil;&atilde;o:</label>
                        <input type="number" name="ordem" id="ordemModelo" class="form-control" value="0" min="0" style="width:100px;">
                    </div>

                    <div id="carregando" class="shown hidden">
                        <div class="arquivo">
                            <div class="frase">Carregando...</div>
                            <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border:0;">
                        </div>
                    </div>
                    <div id="mensagem"></div>

                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarModeloContrato" name="c">
                    <input type="hidden" value="salvarModeloContrato" name="m">
                    <input type="hidden" name="codModeloContrato" id="codModeloContrato" value="">
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btnSalvarModelo" class="btn btn-primary modalBtnSalvar">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão -->
<div class="modal fade" id="modalConfirmarExclusaoModelo" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir o modelo <b id="nomeExcluir"></b>?</p>
                <p class="text-danger"><small>Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarExclusaoModelo" class="btn btn-danger modalBtnExcluir">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {

    // Novo Modelo
    $('#btnNovoModelo').on('click', function () {
        limparCamposModelo();
        $('#modalModeloContratoTitulo').html('<i class="fa fa-file-word-o"></i> Novo Modelo de Contrato');
        $('#lblArquivoObrigatorio').show();
        $('#arquivoAtual').hide();
        $('#modalModeloContrato').modal('show');
    });

    // Salvar
    $('#btnSalvarModelo').on('click', function () {
        salvarModeloContrato();
    });

    // Editar (delegado)
    $('#itensModelo').on('click', '.btn-editar-modelo', function () {
        var cod = $(this).data('cod');
        editarModeloContrato(cod);
    });

    // Excluir (delegado)
    var codExcluir = null;
    $('#itensModelo').on('click', '.btn-excluir-modelo', function () {
        codExcluir = $(this).data('cod');
        $('#nomeExcluir').text($(this).data('nome'));
        $('#modalConfirmarExclusaoModelo').modal('show');
    });

    $('#btnConfirmarExclusaoModelo').on('click', function () {
        $('#modalConfirmarExclusaoModelo').modal('hide');
        if (codExcluir) {
            excluirModeloContrato(codExcluir);
            codExcluir = null;
        }
    });
});

function carregarTabelaModeloContrato() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarModeloContrato', 'm': 'carregarTabelaModeloContrato', '_submit_check': '1'},
        function (html) {
            $("#itensModelo tbody").html(html);
        }
    );
}

function salvarModeloContrato() {
    var valido = true;
    $('#modalModeloContrato .form-group').removeClass('has-error');
    $('#modalModeloContrato .msg-campo').remove();

    if (!$('#nomeModelo').val().trim()) {
        $('#nomeModelo').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    if (!$('#codModeloContrato').val() && $('#arquivoModelo')[0].files.length === 0) {
        $('#arquivoModelo').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Selecione um arquivo DOCX</span>');
        valido = false;
    }
    if (!valido) return;

    var formData = new FormData();
    formData.append('c', 'GerenciarModeloContrato');
    formData.append('m', 'salvarModeloContrato');
    formData.append('_submit_check', '1');
    formData.append('codModeloContrato', $('#codModeloContrato').val());
    formData.append('nome', $('#nomeModelo').val());
    formData.append('descricao', $('#descricaoModelo').val());
    formData.append('ordem', $('#ordemModelo').val());
    formData.append('formatoSaida', $('#formatoSaida').val());
    if ($('#arquivoModelo')[0].files.length > 0) {
        formData.append('arquivo', $('#arquivoModelo')[0].files[0]);
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaModeloContrato();
                limparCamposModelo();
                $('#modalModeloContrato').modal('hide');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao salvar:', status, error, xhr.responseText);
            if (xhr.responseText && (xhr.responseText.indexOf('login.php') !== -1 || xhr.responseText.indexOf('permiss') !== -1)) {
                alert('Sua sess\u00e3o expirou. Fa\u00e7a login novamente.');
                window.location.href = '<?php echo WB_URL_VIEW ?>?content=view/admin/login.php';
                return;
            }
            loading('div#erro', 'show');
            $('div#erro').html('Erro ao salvar. Verifique o console (F12).');
        },
        complete: function() { stopLoading(); }
    });
}

function editarModeloContrato(codModeloContrato) {
    if (!codModeloContrato) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codModeloContrato': codModeloContrato,
            'c': 'GerenciarModeloContrato',
            'm': 'editarModeloContrato',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function () { startLoading(); },
        success: function (data) {
            $('#nomeModelo').val(data.nome);
            $('#descricaoModelo').val(data.descricao);
            $('#ordemModelo').val(data.ordem);
            $('#formatoSaida').val(data.formatoSaida || 'DOCX');
            $('#codModeloContrato').val(data.codModeloContrato);
            if (data.arquivoUploadNome) {
                $('#arquivoAtualNome').text(data.arquivoUploadNome);
                $('#arquivoAtual').show();
                $('#lblArquivoObrigatorio').hide();
            }
            $('#modalModeloContratoTitulo').html('<i class="fa fa-file-word-o"></i> Editar Modelo de Contrato');
            $('#modalModeloContrato').modal('show');
        },
        complete: function () { stopLoading(); },
        error: function (xhr, status, error) {
            console.error('Erro:', error);
        }
    });
}

function excluirModeloContrato(codModeloContrato) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codModeloContrato': codModeloContrato,
            'c': 'GerenciarModeloContrato',
            'm': 'excluirModeloContrato',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function () { startLoading(); },
        success: function (data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaModeloContrato();
            }
        },
        complete: function () { stopLoading(); }
    });
}

function limparCamposModelo() {
    $('#nomeModelo').val('');
    $('#descricaoModelo').val('');
    $('#ordemModelo').val(0);
    $('#formatoSaida').val('DOCX');
    $('#codModeloContrato').val('');
    $('#arquivoModelo').val('');
    $('#nomeArquivoModelo').val('');
    $('#arquivoAtual').hide();
    $('#lblArquivoObrigatorio').show();
    $('#formModeloContrato')[0].reset();
    $('#modalModeloContrato .form-group').removeClass('has-error');
    $('#modalModeloContrato .msg-campo').remove();
}
</script>