<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarEmailTemplateAC();
// Mesmo gate do controller: sem ADM_GERAL no evento a tela so mostra o aviso de acesso restrito.
$podeGerenciarTemplate = @FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false);
?>
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>
<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

        // Delegated events para a lista de templates
        $('#listaTemplates').on('click', '.btn-editar', function(event) {
            event.preventDefault();
            var id = $(this).closest('tr').data('id');
            mdtEditarTemplate(id);
        });
        $('#listaTemplates').on('click', '.btn-excluir', function(event) {
            event.preventDefault();
            _mdtExcluirId = $(this).closest('tr').data('id');
            $('#modalConfirmarExclusaoTemplate').modal('show');
        });
        $('#btnConfirmarExcluirTemplate').click(function() {
            $('#modalConfirmarExclusaoTemplate').modal('hide');
            excluirEmailTemplate(_mdtExcluirId);
        });
        $('#listaTemplates').on('click', '.toggle-corpo', function(e) {
            e.preventDefault();
            var $container = $(this).closest('tr');
            $container.find('.corpo-preview, .corpo-completo').toggle();
            $(this).text($(this).text() === 'ver mais' ? 'ver menos' : 'ver mais');
        });

        // Callback: ao salvar no modal, recarregar a listagem
        _mdtOnSalvo = function() {
            carregarTabelaEmailTemplate();
        };
    });

    function carregarTabelaEmailTemplate() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarEmailTemplate',
            'm': 'carregarTabelaEmailTemplate',
            '_submit_check': '1'
        }, function(html) {
            $('#listaTemplates').html(html);
        });
    }

    function abrirModalImportar() {
        $('#importarAssuntosList').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');
        $('#modalImportarTemplates').modal('show');
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarEmailTemplate',
                'm': 'carregarAssuntosImportacao',
                '_submit_check': '1'
            },
            dataType: "json",
            success: function(data) {
                if (data.statusMessage && data.templates.length > 0) {
                    var $container = $('<div>');
                    $container.append('<div class="checkbox" style="margin-bottom:5px; font-size:12px;"><label><input type="checkbox" id="chkTodosAssuntos"> <strong>Selecionar todos</strong></label></div><hr style="margin:5px 0;">');
                    for (var i = 0; i < data.templates.length; i++) {
                        var $chk = $('<input type="checkbox" class="chk-assunto">').val(data.templates[i].cod);
                        var $label = $('<label style="font-weight:normal;">').append($chk).append(' ' + data.templates[i].assunto);
                        $container.append($('<div class="checkbox" style="margin-bottom:2px; font-size:12px;">').append($label));
                    }
                    $('#importarAssuntosList').html($container);
                    $('#chkTodosAssuntos').change(function() {
                        $('.chk-assunto').prop('checked', $(this).is(':checked'));
                    });
                } else {
                    $('#importarAssuntosList').html('<div class="alert alert-info">' + (data.message || 'Não há templates disponíveis para importação.') + '</div>');
                }
            },
            error: function() {
                $('#importarAssuntosList').html('<div class="alert alert-danger">Erro ao carregar assuntos.</div>');
            }
        });
    }

    function importarTemplatesSelecionados() {
        var selecionados = [];
        $('.chk-assunto:checked').each(function() {
            selecionados.push($(this).val());
        });
        if (selecionados.length === 0) {
            processJson({statusMessage: false, message: 'Selecione ao menos um assunto para importar.'});
            return;
        }
        $('#btnImportarConfirmar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importando...');
        var postData = {
            'c': 'GerenciarEmailTemplate',
            'm': 'importarTemplatesPorIds',
            '_submit_check': '1'
        };
        for (var i = 0; i < selecionados.length; i++) {
            postData['ids[' + i + ']'] = selecionados[i];
        }
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: postData,
            dataType: "json",
            success: function(data) {
                if (data.statusMessage) {
                    $('#importarAssuntosList').html('<div class="alert alert-success">' + (data.message || 'Templates importados com sucesso!') + '</div>');
                    setTimeout(function() {
                        $('#modalImportarTemplates').modal('hide');
                        carregarTabelaEmailTemplate();
                    }, 1500);
                } else {
                    processJson(data);
                }
                $('#btnImportarConfirmar').prop('disabled', false).html('Importar Selecionados');
            },
            error: function() {
                processJson({statusMessage: false, message: 'Erro de comunica\u00e7\u00e3o.'});
                $('#btnImportarConfirmar').prop('disabled', false).html('Importar Selecionados');
            }
        });
    }

    var _mdtExcluirId = null;
    function excluirEmailTemplate(codEmailTemplate) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: {
                    'codEmailTemplate': codEmailTemplate,
                    'c': 'GerenciarEmailTemplate',
                    'm': 'excluirEmailTemplate',
                    '_submit_check': '1'
                },
                dataType: "json",
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaEmailTemplate();
                    }
                }
            });
    }
</script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_comunicados.php");
?>
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar E-mail Templates</h4>
        <?php if ($podeGerenciarTemplate) { ?>
        <div>
            <button class="btn btn-sm" type="button" onclick="abrirModalImportar()" style="border-radius:20px; padding:7px 18px; font-size:13px; background:#f0f0f0; color:#555; border:1px solid #ddd; margin-right:6px;">
                <i class="fa fa-download"></i> Importar
            </button>
            <button class="btn btn-sm abriModal" type="button" onclick="mdtNovoTemplate()">
                <i class="fa fa-plus"></i> Novo Template
            </button>
        </div>
        <?php } ?>
    </div>
    <div id="listaTemplates">
        <?php
        try {
            $objSRV->carregarTabelaEmailTemplate($fws_, $debug);
        } catch (Exception $ex) {
            ?>
            <div style="padding:25px; text-align:center; color:#a94442;">
                <i class="fa fa-lock" style="font-size:22px;"></i>
                <p style="margin:8px 0 0;"><?= htmlspecialchars($ex->getMessage(), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<?php if ($podeGerenciarTemplate) { ?>
<?php include(dirname(__FILE__) . "/modal-email-template.php"); ?>

<!-- Modal Confirmar Exclusao Template -->
<div class="modal fade" id="modalConfirmarExclusaoTemplate" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este template?</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" id="btnConfirmarExcluirTemplate"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Importar Templates de Outros Eventos -->
<div class="modal fade" id="modalImportarTemplates" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title"><i class="fa fa-download"></i> Importar Templates de Outros Eventos</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Selecione os templates que deseja importar. Apenas assuntos que ainda n&atilde;o existem neste evento s&atilde;o exibidos.</p>
        <div id="importarAssuntosList" style="max-height:400px; overflow-y:auto;"></div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
        <button type="button" class="btn modalBtnSalvar" id="btnImportarConfirmar" onclick="importarTemplatesSelecionados()"><i class="fa fa-download"></i> Importar Selecionados</button>
      </div>
    </div>
  </div>
</div>
<?php } ?>
