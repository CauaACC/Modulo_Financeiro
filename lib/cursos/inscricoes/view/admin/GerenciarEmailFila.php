<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarEmailFilaAC();
?>
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>
<script type="text/javascript">
$(document).ready(function() {
    carregarTabelaEmailFila();

    $('#filtroStatus, #filtroCodEmailTemplate').change(function() {
        carregarTabelaEmailFila();
    });

    $('#listaEmailFila').on('click', '.btn-ver-corpo', function(e) {
        e.preventDefault();
        var id = $(this).closest('tr').data('id');
        verCorpoEmail(id);
    });

    $('#listaEmailFila').on('click', '.btn-cancelar-email', function(e) {
        e.preventDefault();
        _emailCancelarId = $(this).closest('tr').data('id');
        $('#modalConfirmarCancelarEmail').modal('show');
    });
    $('#btnConfirmarCancelarEmail').click(function() {
        $('#modalConfirmarCancelarEmail').modal('hide');
        cancelarEmail(_emailCancelarId);
    });
});

function carregarTabelaEmailFila() {
    $('#listaEmailFila').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarEmailFila',
        'm': 'carregarTabelaEmailFila',
        'filtroStatus': $('#filtroStatus').val(),
        'filtroCodEmailTemplate': $('#filtroCodEmailTemplate').val(),
        '_submit_check': '1'
    }, function(html) {
        $('#listaEmailFila').html(html);
    });
}

function verCorpoEmail(codEmailFila) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarEmailFila',
            'm': 'verCorpoEmail',
            'codEmailFila': codEmailFila,
            '_submit_check': '1'
        },
        dataType: "json",
        success: function(data) {
            if (data.statusMessage) {
                var statusLabel = '';
                if (data.status == 'ENVIADO') statusLabel = '<span class="label label-success">ENVIADO</span>';
                else if (data.status == 'ERRO') statusLabel = '<span class="label label-danger">ERRO</span>';
                else if (data.status == 'PENDENTE') statusLabel = '<span class="label label-info">PENDENTE</span>';
                else statusLabel = '<span class="label label-default">' + data.status + '</span>';

                var info = '<p><strong>Para:</strong> ' + (data.nome || '') + ' &lt;' + data.destinatario + '&gt;</p>';
                info += '<p><strong>Assunto:</strong> ' + data.assunto + '</p>';
                info += '<p><strong>Status:</strong> ' + statusLabel + '</p>';
                if (data.datProgramado) info += '<p><strong>Programado:</strong> ' + data.datProgramado + '</p>';
                if (data.datEnviado) info += '<p><strong>Enviado:</strong> ' + data.datEnviado + '</p>';
                if (data.erroMensagem) info += '<p class="text-danger"><strong>Erro:</strong> ' + data.erroMensagem + '</p>';
                info += '<hr>';

                $('#modalVerCorpoInfo').html(info);
                $('#modalVerCorpoConteudo').html(data.corpo);
                $('#modalVerCorpo').modal('show');
            } else {
                processJson({statusMessage: false, message: data.message || 'E-mail n\u00e3o encontrado.'});
            }
        }
    });
}

var _emailCancelarId = null;
function cancelarEmail(codEmailFila) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarEmailFila',
            'm': 'cancelarEmail',
            'codEmailFila': codEmailFila,
            '_submit_check': '1'
        },
        dataType: "json",
        success: function(data) {
            if (data.statusMessage) {
                carregarTabelaEmailFila();
            } else {
                processJson(data);
            }
        }
    });
}
</script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_comunicados.php");
?>
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Histórico de E-mails Enviados</h4>
    </div>
    <div style="padding:12px 18px;">
            <div class="row" style="margin-bottom:15px;">
                <div class="col-sm-3">
                    <label class="label-filtro">Status:</label>
                    <select id="filtroStatus" class="form-control input-sm">
                        <option value="">Todos</option>
                        <option value="PENDENTE">Pendente</option>
                        <option value="ENVIADO">Enviado</option>
                        <option value="ERRO">Erro</option>
                        <option value="CANCELADO">Cancelado</option>
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="label-filtro">Template:</label>
                    <select id="filtroCodEmailTemplate" class="form-control input-sm">
                        <?php $objSRV->carregarOptionsTemplates($fws_, $debug); ?>
                    </select>
                </div>
                <div class="col-sm-2" style="padding-top:22px;">
                    <button class="btn btn-sm" onclick="carregarTabelaEmailFila()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;">
                        <i class="fa fa-refresh"></i> Atualizar
                    </button>
                </div>
            </div>
            <h5 style="font-size:14px; font-weight:600; color:#555; margin:0;">Hist&oacute;rico de Envios</h5>
    </div>
    <div id="listaEmailFila">
        <?php $objSRV->carregarTabelaEmailFila($fws_, $debug); ?>
    </div>
</div>

<!-- Modal Confirmar Cancelamento Email -->
<div class="modal fade" id="modalConfirmarCancelarEmail" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Cancelar Envio</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja cancelar o envio deste e-mail?</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">N&atilde;o</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" id="btnConfirmarCancelarEmail"><i class="fa fa-times"></i> Cancelar Envio</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Corpo do Email -->
<div class="modal fade" id="modalVerCorpo" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title"><i class="fa fa-envelope-open"></i> Detalhes do E-mail</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div id="modalVerCorpoInfo"></div>
        <div id="modalVerCorpoConteudo" style="border:1px solid #ddd; padding:15px; background:#fafafa; max-height:400px; overflow-y:auto;"></div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
