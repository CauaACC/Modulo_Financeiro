<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarItaushoplineUploadAC();
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<style>
    .tbl-fin { width:100%; border-collapse:collapse; font-size:13px; }
    .tbl-fin thead th { font-size:11px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.3px; padding:12px 10px; border-bottom:1px solid #e0e0e0; background:#f5f6f8; cursor:pointer; white-space:nowrap; user-select:none; }
    .tbl-fin thead th:first-child { cursor:default; }
    .tbl-fin thead th[data-col]:hover { color:#555; }
    .tbl-fin thead th[data-col]:hover .sort-arrow { color:#666 !important; }
    .tbl-fin thead th .sort-arrow { color:#ccc; margin-left:4px; font-size:10px; }
    .tbl-fin tbody tr { border-bottom:1px solid #f0f0f0; transition:background 0.15s; }
    .tbl-fin tbody tr:hover { background:#f9fafb; }
    .tbl-fin tbody td { padding:5px 10px; vertical-align:middle; color:#333; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; transition:all 0.2s; font-size:13px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; border-color:#ccc; }
    .btn-acao.btn-acao-excluir:hover { background:#fef2f2; color:#c0392b; border-color:#f5c6cb; }
    .tbl-fin tbody td:first-child { white-space:nowrap; }
    .btn-mini-acao { font-size:11px; padding:3px 10px; border-radius:20px; border:1px solid #ddd; background:#fff; color:#555; cursor:pointer; margin:2px 0; transition:all 0.2s; }
    .btn-mini-acao:hover { background:#2d6a4f; color:#fff; border-color:#2d6a4f; }
    .btn-mini-acao.btn-mini-carregar:hover { background:#e6a817; border-color:#e6a817; }
    .btn-mini-acao.btn-mini-associar:hover { background:#28a745; border-color:#28a745; }
</style>
<script type="text/javascript">
    var _codExcluir = null;
    var _sortCol = -1;
    var _sortAsc = true;

    $(document).ready(function() {
        bindEventosTabela();

        $('#btnSalvar').on('click', function() {
            salvarArquivo();
        });

        // Sort por coluna
        $(document).on('click', '.tbl-fin thead th[data-col]', function() {
            var col = parseInt($(this).data('col'));
            if (_sortCol === col) { _sortAsc = !_sortAsc; } else { _sortCol = col; _sortAsc = true; }
            sortTable(col, _sortAsc);
            $('.tbl-fin thead th').css('font-weight', '600');
            $('.tbl-fin thead th .sort-arrow').html('&uarr;&darr;').css('color', '#ccc');
            $(this).css('font-weight', '700');
            $(this).find('.sort-arrow').html(_sortAsc ? '&uarr;' : '&darr;').css('color', '#333');
        });
    });

    function sortTable(colIndex, asc) {
        var tbody = $('#itens tbody.inputs').first();
        var rows = tbody.find('tr').not('.tbl-fin-total').toArray();
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(colIndex).text().trim().toLowerCase();
            var bVal = $(b).find('td').eq(colIndex).text().trim().toLowerCase();
            var aNum = parseFloat(aVal); var bNum = parseFloat(bVal);
            if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        $.each(rows, function(i, row) { tbody.append(row); });
    }

    function bindEventosTabela() {
        $('#itens').off('click', '.excluir').on('click', '.excluir', function(e) {
            e.preventDefault();
            _codExcluir = $(this).closest('tr').attr('id');
            $('#modalExclusao').modal('show');
        });
        $('#itens').off('click', '.botaoCargaRetorno').on('click', '.botaoCargaRetorno', function(e) {
            e.preventDefault();
            var id = $(this).closest('tr').attr('id');
            carregarArquivoItaushoplines(id);
        });
        $('#itens').off('click', '.botaoAssociar').on('click', '.botaoAssociar', function(e) {
            e.preventDefault();
            var id = $(this).closest('tr').attr('id');
            associarInscritoPagamento(id);
        });
        $('#itens').off('click', '.mostrarLog').on('click', '.mostrarLog', function(e) {
            e.preventDefault();
            $(this).closest('td').find('.messageLog').toggle();
        });
    }

    function exibirResultadoCarga(data) {
        var tipo = data.statusMessage ? 'alert-success' : 'alert-danger';
        var html = '<div class="alert ' + tipo + ' alert-dismissible" style="margin-top:10px; max-height:300px; overflow-y:auto;">';
        html += '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
        html += data.message;
        html += '</div>';
        $('#resultadoCarga').html(html);
        $('html,body').animate({ scrollTop: $('#resultadoCarga').offset().top }, 'fast');
    }

    function carregarTabelaArquivos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarItaushoplineUpload',
            'm': 'carregarTabelaArquivos',
            '_submit_check': '1'
        }, function(html) {
            $('#itens tbody.inputs tr').remove();
            $('#itens tbody.inputs').append(html);
            bindEventosTabela();
        });
    }

    function salvarArquivo() {
        $('#formArquivo').ajaxSubmit({
            target: '#modalUploadMsg',
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            timeout: 10000,
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaArquivos();
                    $('#modalUpload').modal('hide');
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function carregarArquivoItaushoplines(codArquivo) {
        if (!codArquivo) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codEvento': '<?php echo COD_EVENTO ?>',
                'codArquivo': codArquivo,
                'c': 'GerenciarItaushoplineUpload',
                'm': 'carregarArquivoItaushoplines',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                exibirResultadoCarga(data);
                if (data.statusMessage) { carregarTabelaArquivos(); }
            },
            complete: function() { stopLoading(); }
        });
    }

    function associarInscritoPagamento(codArquivo) {
        if (!codArquivo) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codEvento': '<?php echo COD_EVENTO ?>',
                'codArquivo': codArquivo,
                'c': 'GerenciarItaushoplineUpload',
                'm': 'associarInscritoPagamento',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                exibirResultadoCarga(data);
                if (data.statusMessage) { carregarTabelaArquivos(); }
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarExclusao() {
        if (!_codExcluir) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codArquivo': _codExcluir,
                'c': 'GerenciarItaushoplineUpload',
                'm': 'excluirArquivo',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) { carregarTabelaArquivos(); }
                $('#modalExclusao').modal('hide');
                _codExcluir = null;
            },
            complete: function() { stopLoading(); }
        });
    }

    function abrirModalUpload() {
        limparCampos();
        $('#modalUpload').modal('show');
    }

    function limparCampos() {
        $('#codArquivo').val('');
        $('#arquivo').val('');
        $('#novoCodArquivo').val(0);
        $('#nomeArquivo').text('Nenhum arquivo selecionado');
        $('#modalUploadMsg').html('');
    }
</script>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");
?>

<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<!-- Resultado da carga -->
<div id="resultadoCarga"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Carga de Retorno Banc&aacute;rio</h4>
        <button type="button" class="btn btn-sm abriModal" onclick="abrirModalUpload()">
            <i class="fa fa-upload"></i> Enviar Arquivo
        </button>
    </div>
    <div style="padding:0;">
        <table class="tbl-fin" id="itens" style="width:100%; table-layout:auto;">
            <thead>
                <tr>
                    <th style="width:140px;">A&ccedil;&otilde;es</th>
                    <th data-col="1" style="width:100px;">Status <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="2">Origem / Arquivo <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="3">Arquivo Enviado <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="4" style="width:140px;">Data <span class="sort-arrow">&uarr;&darr;</span></th>
                </tr>
            </thead>
            <tbody class="inputs">
                <?php $objSRV->carregarTabelaArquivos($fws_, $debug); ?>
            </tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:150px;"></div>

<!-- Modal Upload -->
<div class="modal fade" id="modalUpload" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:550px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-upload"></i> Enviar Arquivo de Retorno</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalUploadMsg"></div>
                <form id="formArquivo" name="formArquivo" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group">
                        <label for="tipoArquivo">Origem:</label>
                        <select class="form-control" id="tipoArquivo" name="tipoArquivo">
                            <option value="ITAU-BOLECODE">ITAU-BOLECODE</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="codPerfil">Perfil:</label>
                        <select class="form-control" id="codPerfil" name="codPerfil">
                            <option value="">Selecione...</option>
                            <?php
                            $fws_['codEvento'] = $objEv->getCodEvento();
                            $fws_['semRestricao'] = true;
                            CarregarOptionsAC::perfis($fws_, $debug);
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="arquivo">Arquivo de retorno (.RET):</label>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <button type="button" onclick="$('#arquivo').click();" style="border-radius:8px; padding:8px 16px; font-size:13px; background:#f0f0f0; color:#555; border:1px solid #ddd; cursor:pointer; white-space:nowrap;"><i class="fa fa-folder-open" style="margin-right:6px;"></i>Procurar...</button>
                            <span id="nomeArquivo" style="font-size:13px; color:#888; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">Nenhum arquivo selecionado</span>
                            <input type="file" name="arquivo" id="arquivo" required accept=".ret,.RET,.txt,.TXT" style="display:none;" onchange="$('#nomeArquivo').text(this.files.length ? this.files[0].name : 'Nenhum arquivo selecionado');">
                        </div>
                    </div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarItaushoplineUpload" name="c">
                    <input type="hidden" value="salvarPagamentoUpload" name="m">
                    <input type="hidden" name="codEvento" id="codEvento" value="<?php echo COD_EVENTO ?>">
                    <input type="hidden" name="novoCodArquivo" id="novoCodArquivo" value="0">
                    <input type="hidden" name="codArquivo" id="codArquivo" value="">
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvar">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exclusao -->
<div class="modal fade" id="modalExclusao" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:450px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este arquivo de retorno?</p>
                <p><strong>Esta a&ccedil;&atilde;o &eacute; irrevers&iacute;vel.</strong></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>
