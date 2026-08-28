<?php
//require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarMsgAvisoAC();
?>
<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

        // Delegated events para a lista de avisos
        $('#listaAvisos').on('click', '.btn-editar', function(event) {
            event.preventDefault();
            var id = $(this).closest('tr').data('id');
            mavEditarAviso(id);
        });
        $('#listaAvisos').on('click', '.btn-excluir', function(event) {
            event.preventDefault();
            var id = $(this).closest('tr').data('id');
            _mavExcluirId = id;
            $('#modalConfirmarExclusaoAviso').modal('show');
        });
        $('#btnConfirmarExcluirAviso').click(function() {
            $('#modalConfirmarExclusaoAviso').modal('hide');
            excluirMsgAvisos(_mavExcluirId);
        });
        $('#listaAvisos').on('click', '.toggle-corpo', function(e) {
            e.preventDefault();
            var $container = $(this).closest('tr');
            $container.find('.corpo-preview, .corpo-completo').toggle();
            $(this).text($(this).text() === 'ver mais' ? 'ver menos' : 'ver mais');
        });

        // Callback: ao salvar no modal, recarregar a listagem
        _mavOnSalvo = function() {
            carregarTabelaMsgAvisos();
        };
    });

    function carregarTabelaMsgAvisos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarMsgAviso',
            'm': 'carregarTabelaMsgAvisos',
            '_submit_check': '1'
        }, function(html) {
            $('#listaAvisos').html(html);
        });
    }

    var _mavExcluirId = null;
    function excluirMsgAvisos(codMsgAviso) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codMsgAviso': codMsgAviso,
                'c': 'GerenciarMsgAviso',
                'm': 'excluirMsgAvisos',
                '_submit_check': '1'
            },
            dataType: "json",
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaMsgAvisos();
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
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Avisos</h4>
        <button class="btn btn-sm abriModal" type="button" onclick="mavNovoAviso()">
            <i class="fa fa-plus"></i> Novo Aviso
        </button>
    </div>
    <div id="listaAvisos">
        <?php $objSRV->carregarTabelaMsgAvisos($fws_, $debug); ?>
    </div>
</div>

<!-- Modal Confirmar Exclusao Aviso -->
<div class="modal fade" id="modalConfirmarExclusaoAviso" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este aviso?</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" id="btnConfirmarExcluirAviso"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>

<?php include(dirname(__FILE__) . "/modal-msg-aviso.php"); ?>
