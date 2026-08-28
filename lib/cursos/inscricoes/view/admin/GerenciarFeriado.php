<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarFeriadoAC();
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<script type="text/javascript">
$(document).ready(function() {

    carregarAnosDisponiveisFeriado(function(){
        carregarTabelaFeriado();
    });

    $('#filtroAnoFeriado').on('change', function(){
        carregarTabelaFeriado();
    });

    $('#btnNovoFeriado').on('click', function() {
        limparCampos();
        $('#modalFeriadoLabel').text('Novo Feriado');
        $('#modalFeriado').modal('show');
    });

    // Se URL trouxe ?data=YYYY-MM-DD (vindo do Calendario Base), abre modal preenchido
    <?php $dataInicial = isset($fwsGet_['dia_calendario']) ? preg_replace('/[^0-9\-]/', '', $fwsGet_['dia_calendario']) : ''; ?>
    <?php if ($dataInicial) { ?>
    setTimeout(function(){
        limparCampos();
        $('#modalFeriadoLabel').text('Novo Feriado — <?php echo $dataInicial; ?>');
        $('#dia_calendario').val('<?php echo $dataInicial; ?>');
        $('#descricao').focus();
        $('#modalFeriado').modal('show');
    }, 200);
    <?php } ?>

    $('#btnSalvar').on('click', function() {
        salvarFeriado();
    });

    $('#btnSalvarComoNovo').on('click', function() {
        $('#updateId').val(0);
        $('#id').val('');
        salvarFeriado();
    });

    // Delegated events on #itens
    $('#itens').on('click', '.editar', function(e) {
        e.preventDefault();
        var id = $(this).closest('.cb-feriado-row').attr('id');
        editarFeriado(id);
    });

    $('#itens').on('click', '.excluir', function(e) {
        e.preventDefault();
        var id = $(this).closest('.cb-feriado-row').attr('id');
        excluirFeriado(id);
    });
});

function carregarTabelaFeriado() {
    var ano = $('#filtroAnoFeriado').val() || '';
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarFeriado',
        'm': 'carregarTabelaFeriado',
        'ano': ano,
        '_submit_check': '1'
    }, function(html) {
        $('#itens').html(html);
    });
}

function carregarAnosDisponiveisFeriado(callback) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarFeriado',
        'm': 'carregarAnosDisponiveisFeriado',
        '_submit_check': '1'
    }, function(data) {
        try {
            var resp = (typeof data === 'string') ? JSON.parse(data) : data;
            var anoAtual = (new Date()).getFullYear();
            var sel = $('#filtroAnoFeriado');
            sel.find('option:not([value=""])').remove();
            (resp.anos || []).forEach(function(ano){
                sel.append('<option value="' + ano + '">' + ano + '</option>');
            });
            if (resp.anos && resp.anos.indexOf(anoAtual) >= 0) {
                sel.val(anoAtual);
            }
        } catch (e) {}
        if (callback) callback();
    });
}

function salvarFeriado() {
    var valido = true;
    $('#modalFeriado .form-group').removeClass('has-error');
    $('#modalFeriado .msg-campo').remove();

    if (!$.trim($('#dia_calendario').val())) {
        $('#dia_calendario').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    if (!valido) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarFeriado',
            'm': 'salvarFeriado',
            'id': $('#id').val(),
            'updateId': $('#updateId').val(),
            'dia_calendario': $.trim($('#dia_calendario').val()),
            'tipo_dia': $('#tipo_dia').val(),
            'descricao': $.trim($('#descricao').val()),
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                limparCampos();
                carregarTabelaFeriado();
                $('#modalFeriado').modal('hide');
            }
        },
        complete: function() { stopLoading(); }
    });
}

function editarFeriado(cod) {
    if (!cod) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'id': cod,
            'c': 'GerenciarFeriado',
            'm': 'editarFeriado',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                $('#updateId').val(1);
                $('#id').val(data.id);
                $('#dia_calendario').val(data.dia_calendario);
                $('#tipo_dia').val(data.tipo_dia);
                $('#descricao').val(data.descricao);
                $('#modalFeriadoLabel').text('Editar Feriado');
                $('#btnSalvarComoNovo').show();
                $('#modalFeriado').modal('show');
            }
        },
        complete: function() { stopLoading(); },
        error: function(xhr, status, error) {
            console.error('Erro na solicita\u00e7\u00e3o:', error);
        }
    });
}

function excluirFeriado(cod) {
    $('#modalConfirmarExclusao').data('cod', cod);
    $('#modalConfirmarExclusao').modal('show');
}

function confirmarExclusao() {
    var cod = $('#modalConfirmarExclusao').data('cod');
    $('#modalConfirmarExclusao').modal('hide');

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'id': cod,
            'c': 'GerenciarFeriado',
            'm': 'excluirFeriado',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaFeriado();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function limparCampos() {
    $('#id').val('');
    $('#dia_calendario').val('');
    $('#tipo_dia').val('');
    $('#descricao').val('');
    $('#updateId').val(0);
    $('#btnSalvarComoNovo').hide();
    $('#modalFeriado .form-group').removeClass('has-error');
    $('#modalFeriado .msg-campo').remove();
}
</script>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_outros.php");
?>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px; margin-bottom:300px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Feriados</h4>
        <button type="button" id="btnNovoFeriado" class="btn btn-sm abriModal">
            <i class="fa fa-plus"></i> Novo Feriado
        </button>
    </div>
    <div style="padding:12px 18px; display:flex; align-items:center; gap:10px;">
        <label for="filtroAnoFeriado" style="margin:0; font-size:14px; font-weight:600; color:#555;">Ano:</label>
        <select id="filtroAnoFeriado" class="form-control" style="width:auto; display:inline-block;">
            <option value="">— Todos —</option>
        </select>
        <span style="font-size:12px;color:#999">Feriados marcados como <strong>GLOBAL</strong> vêm do Calendário Base (todos eventos).</span>
    </div>
    <div id="itens"></div>
</div>

<!-- Modal Feriado -->
<div class="modal fade" id="modalFeriado" tabindex="-1" role="dialog" aria-labelledby="modalFeriadoLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalFeriadoLabel"><i class="fa fa-calendar"></i>Novo Feriado</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formFeriado" name="formFeriado" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post" novalidate>
                    <div class="row">
                        <div class="form-group col-sm-5">
                            <label for="dia_calendario">Dia:</label>
                            <input type="date" id="dia_calendario" name="dia_calendario" value="" class="form-control">
                        </div>
                        <div class="form-group col-sm-7">
                            <label for="tipo_dia">Tipo do Dia:</label>
                            <select class="form-control" name="tipo_dia" id="tipo_dia">
                                <option value=""></option>
                                <option value="Feriado Nacional">Feriado Nacional</option>
                                <option value="Feriado Estadual">Feriado Estadual</option>
                                <option value="Feriado Municipal">Feriado Municipal</option>
                                <option value="Feriado Evento">Feriado Evento</option>
                                <option value="Recesso">Recesso</option>
                                <option value="Ponto Facultativo">Ponto Facultativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descri&ccedil;&atilde;o:</label>
                        <input type="text" id="descricao" name="descricao" value="" class="form-control">
                    </div>
                    <div id="mensagem"></div>
                    <div id="carregando" class="shown hidden">
                        <div class="imagem">
                            <div class="frase">Carregando...</div>
                            <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
                        </div>
                    </div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarFeriado" name="c">
                    <input type="hidden" value="salvarFeriado" name="m">
                    <input type="hidden" name="updateId" id="updateId" value="0">
                    <input type="hidden" name="id" id="id" value="">
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarComoNovo" style="display:none;"><i class="fa fa-copy"></i> Salvar como novo</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvar">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusao -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclus&atilde;o</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este feriado?</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>
