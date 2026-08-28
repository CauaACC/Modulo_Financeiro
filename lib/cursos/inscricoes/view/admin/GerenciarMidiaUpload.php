<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarMidiaUploadAC();
?>
<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_outros.php");
?>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar M&iacute;dia de Upload</h4>
        <button type="button" id="btnNovoUpload" class="btn btn-sm abriModal">
            <i class="fa fa-plus"></i> Novo Upload
        </button>
    </div>
    <div style="padding:12px 18px;">
        <h5 style="font-size:14px; font-weight:600; color:#555; margin:0;">Lista de M&iacute;dias</h5>
    </div>
    <div>
        <table class="tbl-fin" id="itens">
            <thead>
            <tr>
                <th style="width:90px;">A&ccedil;&otilde;es</th>
                <th data-col="1" style="cursor:pointer;">Local da m&iacute;dia <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="2" style="cursor:pointer;">Arquivo Enviado <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="3" style="cursor:pointer;">Perfil <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="4" style="cursor:pointer;">Atividade <span class="sort-arrow">&uarr;&darr;</span></th>
            </tr>
            </thead>
            <tbody>
            <?php $objSRV->carregarTabelaMidiaUpload($fws_, $debug); ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Mídia Upload -->
<div class="modal fade" id="modalMidiaUpload" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalMidiaUploadTitulo"><i class="fa fa-upload"></i>Novo Upload</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formMidiaUpload" name="formMidiaUpload" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post" enctype="multipart/form-data" novalidate>
                    <div class="form-group mb-3">
                        <label for="localMidia">Local da m&iacute;dia:</label>
                        <select name="localMidia" id="localMidia" class="form-control">
                            <option value="">Selecione o local da m&iacute;dia</option>
                            <?php CarregarOptionsAC::localMidia($fws_, $debug); ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="arquivo">Escolha o arquivo:</label>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" onclick="$('#arquivo').click();" style="height:34px;"><i class="fa fa-folder-open"></i> Procurar</button>
                            </span>
                            <input type="text" class="form-control" id="nomeArquivo" readonly placeholder="Nenhum arquivo selecionado" style="height:34px;">
                        </div>
                        <input type="file" id="arquivo" name="arquivo" style="display:none;" onchange="$('#nomeArquivo').val(this.files.length ? this.files[0].name : '');">
                    </div>

                    <div class="form-group mb-3">
                        <label for="codPerfil">Perfil:</label>
                        <select name="codPerfil" id="codPerfil" class="form-control">
                            <option value="">Selecione o Perfil</option>
                            <?php
                            try {
                                CarregarOptionsAC::perfisCadAdmin($fws_, $debug);
                            } catch (Exception $exc) {
                                echo $exc->getTraceAsString();
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="codOficina">Atividade <?php echo $tipoAtividade ?>:</label>
                        <select name="codOficina" id="codOficina" class="form-control">
                            <option value="">Selecione a Atividade</option>
                            <?php CarregarOptionsAC::oficinas($fws_, $debug); ?>
                        </select>
                    </div>

                    <div id="carregando" class="shown hidden">
                        <div class="arquivo">
                            <div class="frase">Carregando...</div>
                            <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border:0;">
                        </div>
                    </div>
                    <div id="mensagem"></div>

                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarMidiaUpload" name="c">
                    <input type="hidden" value="salvarMidiaUpload" name="m">
                    <input type="hidden" name="novoCodArquivo" id="novoCodArquivo" value="0">
                    <input type="hidden" name="codArquivo" id="codArquivo" value="">
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btnSalvar" class="btn btn-primary modalBtnSalvar">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação de Exclusão -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclus&atilde;o</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir esta m&iacute;dia?</p>
                <p class="text-danger"><small>Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarExclusao" class="btn btn-danger modalBtnExcluir">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {

    // Botão Novo Upload
    $('#btnNovoUpload').on('click', function () {
        limparCampos();
        $('#modalMidiaUploadTitulo').text('Novo Upload');
        $('#modalMidiaUpload').modal('show');
    });

    // Botão Salvar no modal
    $('#btnSalvar').on('click', function () {
        salvarMidiaUpload();
    });

    // Delegated: Editar
    $('#itens').on('click', '.editar', function () {
        var id = $(this).parents("tr").attr("id");
        editarMidiaUpload(id);
    });

    // Delegated: Baixar (clica no nome do arquivo; submit POST para o browser tratar como anexo)
    $('#itens').on('click', '.baixar', function (e) {
        e.preventDefault();
        var id = $(this).parents("tr").attr("id");
        if (!id) return;
        baixarMidiaUpload(id);
    });

    // Delegated: Excluir
    var codExcluir = null;
    $('#itens').on('click', '.excluir', function () {
        codExcluir = $(this).parents("tr").attr("id");
        $('#modalConfirmarExclusao').modal('show');
    });

    $('#btnConfirmarExclusao').on('click', function () {
        $('#modalConfirmarExclusao').modal('hide');
        if (codExcluir) {
            excluirMidiaUpload(codExcluir);
            codExcluir = null;
        }
    });
});

function carregarTabelaMidiaUpload() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarMidiaUpload', 'm': 'carregarTabelaMidiaUpload', '_submit_check': '1'},
        function (html) {
            $("#itens tbody").html(html);
        }
    );
}

function salvarMidiaUpload() {
    var valido = true;
    $('#modalMidiaUpload .form-group').removeClass('has-error');
    $('#modalMidiaUpload .msg-campo').remove();

    if (!$('#localMidia').val()) {
        $('#localMidia').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    if ($('#novoCodArquivo').val() == '0' && $('#arquivo')[0].files.length === 0) {
        $('#arquivo').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Selecione um arquivo</span>');
        valido = false;
    }
    if (!valido) return;

    var formData = new FormData();
    formData.append('c', 'GerenciarMidiaUpload');
    formData.append('m', 'salvarMidiaUpload');
    formData.append('_submit_check', '1');
    formData.append('codArquivo', $('#codArquivo').val());
    formData.append('novoCodArquivo', $('#novoCodArquivo').val());
    formData.append('localMidia', $('#localMidia').val());
    formData.append('codPerfil', $('#codPerfil').val());
    formData.append('codOficina', $('#codOficina').val());
    if ($('#arquivo')[0].files.length > 0) {
        formData.append('arquivo', $('#arquivo')[0].files[0]);
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaMidiaUpload();
                limparCampos();
                $('#modalMidiaUpload').modal('hide');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao salvar:', status, error, xhr.responseText);
            loading('div#erro', 'show');
            $('div#erro').html('Erro ao salvar. Verifique o console (F12).');
        },
        complete: function() { stopLoading(); }
    });
}

function baixarMidiaUpload(codArquivo) {
    // Cria um form POST temporario e submete: o servidor responde com o binario
    // (Content-Disposition: attachment), entao a pagina atual nao e substituida.
    var $form = $('<form>', {
        method: 'post',
        action: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random()
    });
    $form.append($('<input>', {type: 'hidden', name: 'c', value: 'GerenciarMidiaUpload'}));
    $form.append($('<input>', {type: 'hidden', name: 'm', value: 'downloadMidiaUpload'}));
    $form.append($('<input>', {type: 'hidden', name: '_submit_check', value: '1'}));
    $form.append($('<input>', {type: 'hidden', name: 'codArquivo', value: codArquivo}));
    $form.appendTo('body').submit().remove();
}

function editarMidiaUpload(codArquivo) {
    if (!codArquivo) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codArquivo': codArquivo,
            'c': 'GerenciarMidiaUpload',
            'm': 'editarMidiaUpload',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function () {
            startLoading();
        },
        success: function (data) {
            $('#localMidia').val(data.tipoArquivo);
            $('#codPerfil').val(data.codPerfil);
            $('#codOficina').val(data.codOficina);
            $('#novoCodArquivo').val(1);
            $('#codArquivo').val(data.codArquivo);
            $('#modalMidiaUploadTitulo').text('Editar Upload');
            $('#modalMidiaUpload').modal('show');
        },
        complete: function () {
            stopLoading();
        },
        error: function (xhr, status, error) {
            console.error('Erro na solicitação AJAX:', error);
        }
    });
}

function excluirMidiaUpload(codArquivo) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codArquivo': codArquivo,
            'c': 'GerenciarMidiaUpload',
            'm': 'excluirMidiaUpload',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function () {
            startLoading();
        },
        success: function (data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaMidiaUpload();
            }
        },
        complete: function () {
            stopLoading();
        }
    });
}

function limparCampos() {
    $('#localMidia').val('');
    $('#codPerfil').val('');
    $('#codOficina').val('');
    $('#novoCodArquivo').val(0);
    $('#codArquivo').val('');
    $('#arquivo').val('');
    $('#nomeArquivo').val('');
    $('#formMidiaUpload')[0].reset();
    $('#modalMidiaUpload .form-group').removeClass('has-error');
    $('#modalMidiaUpload .msg-campo').remove();
}
</script>
