<?php
require_once(dirname(__FILE__)."/../../include/config.php");

// Gate de pagina, como nas outras telas do menu Financeiro (GerenciarCentroCusto,
// GerenciarPlanoContas...). Sem ele, com a sessao morta o construtor do AC
// lancava Exception antes de qualquer saida e a tela ficava em branco, sem
// mensagem, em producao. Aceita as mesmas permissoes que o AC (ADM_GERAL ou
// CONTROLE_FINANCEIRO) para a pagina nao negar o que o controller permite.
@$cod_msg = false;
if (!FWA_temPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO])) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

$objSRV = new GerenciarFontesRecursoAC();
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<style>
    .tbl-fin { width:100%; border-collapse:collapse; font-size:13px; table-layout:auto; }
    .tbl-fin tbody td, .tbl-fin thead th { overflow:visible; text-overflow:clip; }
    .tbl-fin thead th { font-size:11px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.3px; padding:12px 10px; border-bottom:1px solid #e0e0e0; background:#f5f6f8; cursor:pointer; white-space:nowrap; user-select:none; }
    .tbl-fin thead th:first-child { cursor:default; }
    .tbl-fin thead th[data-col]:hover { color:#555; }
    .tbl-fin thead th[data-col]:hover .sort-arrow { color:#666 !important; }
    .tbl-fin thead th .sort-arrow { color:#ccc; margin-left:4px; font-size:10px; }
    .tbl-fin tbody tr { border-bottom:1px solid #f0f0f0; transition:background 0.15s; }
    .tbl-fin tbody tr:hover { background:#f9fafb; }
    .tbl-fin tbody td { padding:6px 10px; vertical-align:middle; color:#333; white-space:normal; word-break:break-word; }
    /* colunas curtas encolhem ate o conteudo; o texto fica com o resto */
    .tbl-fin td.col-codigo, .tbl-fin th.col-codigo { white-space:nowrap; width:1%; }
    .tbl-fin td.col-num, .tbl-fin th.col-num { white-space:nowrap; width:1%; text-align:right; }
    .tbl-fin th.col-num .sort-arrow { margin-left:3px; }
    .tbl-fin td.col-data, .tbl-fin th.col-data { white-space:nowrap; width:1%; }
    .tbl-fin td.col-badge, .tbl-fin th.col-badge { white-space:nowrap; width:1%; }
    .tbl-fin tbody td.col-nome { font-weight:600; }
    .tbl-fin .badge-tipo { display:inline-flex; align-items:center; font-size:12px; font-weight:500; padding:3px 10px; border-radius:12px; background:#f0f1f3; }
    .tbl-fin .badge-tipo .dot { width:8px; height:8px; border-radius:50%; margin-right:6px; display:inline-block; flex-shrink:0; }
    .tbl-fin .badge-tipo .dot-green { background:#27ae60; }
    .tbl-fin .badge-tipo .dot-blue { background:#2980b9; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; transition:all 0.2s; font-size:13px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; border-color:#ccc; }
    .btn-acao.btn-acao-excluir:hover { background:#fef2f2; color:#c0392b; border-color:#f5c6cb; }
    .tbl-fin tbody td:first-child, .tbl-fin thead th:first-child { white-space:nowrap; width:1%; }
</style>
<script type="text/javascript">
    var _codExcluir = null;
    var _sortCol = -1;
    var _sortAsc = true;

    $(document).ready(function() {
        bindEventosTabela();

        $('#btnSalvar').click(function() {
            salvarFontesRecurso();
        });

        $('.tbl-fin thead th[data-col]').on('click', function() {
            var col = parseInt($(this).data('col'));
            if (_sortCol === col) { _sortAsc = !_sortAsc; } else { _sortCol = col; _sortAsc = true; }
            sortTable(col, _sortAsc);
            $('.tbl-fin thead th').css('font-weight', '600');
            $('.tbl-fin thead th .sort-arrow').html('↑↓').css('color', '#ccc');
            $(this).css('font-weight', '700');
            $(this).find('.sort-arrow').html(_sortAsc ? '↑' : '↓').css('color', '#333');
        });
    });

    function sortTable(colIndex, asc) {
        var tbody = $('#itens tbody.inputs').first();
        var rows = tbody.find('tr').not('tfoot tr').toArray();
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
        $('.editar').off('click').on('click', function() { editarFontesRecurso($(this).parents("tr").attr("id")); });
        $('.excluir').off('click').on('click', function() { _codExcluir = $(this).parents("tr").attr("id"); $('#modalExclusao').modal('show'); });
    }

    function abrirModalNovo() {
        limparCampos();
        $('#modalFontesRecursoLabel').html('<i class="fa fa-database" style="margin-right:8px; opacity:0.8;"></i>Nova Fonte de Recurso');
        $('#modalFontesRecurso').modal('show');
    }

    function carregarTabelaFontesRecursos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarFontesRecurso', 'm': 'carregarTabelaFontesRecursos', 'ordenar': $('#ordenar').val(), '_submit_check': '1'},
            function(html) { $('#itens').find('tbody.inputs, tfoot').remove(); $('#itens').append('<tbody class="inputs">' + html + '</tbody>'); bindEventosTabela(); }
        );
    }

    function salvarFontesRecurso() {
        $('#formFontesRecurso').ajaxSubmit({
            target: '#modalFontesRecursoMsg', dataType: 'json', async: false, cache: false,
            beforeSend: function() { startLoading(); }, timeout: 4000,
            error: function(ajax) { showResponse(ajax); },
            success: function(data) { processJson(data); if (data.statusMessage) { carregarTabelaFontesRecursos(); $('#modalFontesRecurso').modal('hide'); limparCampos(); } },
            complete: function() { stopLoading(); }
        });
    }

    function editarFontesRecurso(codFontesRecurso) {
        if (!codFontesRecurso) return;
        $.ajax({
            type: "POST", url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codFontesRecurso': codFontesRecurso, 'c': 'GerenciarFontesRecurso', 'm': 'editarFontesRecurso', '_submit_check': '1'},
            dataType: "json", async: false, cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                $('#codFontesRecurso').val(data.codFontesRecurso);
                $('#nomeFontesRecurso').val(data.nomeFontesRecurso);
                $('#tipoFontesRecurso').val(data.tipoFontesRecurso);
                $('#descricao').val(data.descricao);
                $('#saldoInicial').val(data.saldoInicial);
                $('#dataSaldoInicial').val(data.dataSaldoInicial);
                $('#modalFontesRecursoLabel').html('<i class="fa fa-database" style="margin-right:8px; opacity:0.8;"></i>Editar Fonte de Recurso');
                $('#modalFontesRecurso').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarExclusao() {
        if (!_codExcluir) return;
        $.ajax({
            type: "POST", url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codFontesRecurso': _codExcluir, 'c': 'GerenciarFontesRecurso', 'm': 'excluirFontesRecurso', '_submit_check': '1'},
            dataType: "json", async: false, cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) { processJson(data); if (data.statusMessage) { carregarTabelaFontesRecursos(); } $('#modalExclusao').modal('hide'); _codExcluir = null; },
            complete: function() { stopLoading(); }
        });
    }

    function limparCampos() {
        $('#codFontesRecurso').val(''); $('#nomeFontesRecurso').val(''); $('#tipoFontesRecurso').val(''); $('#descricao').val(''); $('#saldoInicial').val(''); $('#dataSaldoInicial').val('');
        $('#getMessage').html(''); $('#modalFontesRecursoMsg').html('');
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

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Fontes de Recurso</h4>
        <button type="button" class="btn btn-sm abriModal" onclick="abrirModalNovo()">
            <i class="fa fa-plus"></i> Nova Fonte de Recurso
        </button>
    </div>
    <div style="padding:0;">
        <table class="tbl-fin" id="itens">
            <thead>
                <tr>
                    <th style="width:80px;">Ações</th>
                    <th data-col="1">Nome <span class="sort-arrow">↑↓</span></th>
                    <th data-col="2">Tipo <span class="sort-arrow">↑↓</span></th>
                    <th data-col="3">Descrição <span class="sort-arrow">↑↓</span></th>
                </tr>
            </thead>
            <tbody class="inputs">
                <?php
                $objSRV->carregarTabelaFontesRecursos($fws_, $debug);
                ?>
            </tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:150px;"></div>

<!-- Modal Cadastro/Edição -->
<div class="modal fade" id="modalFontesRecurso" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:550px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalFontesRecursoLabel"><i class="fa fa-database"></i>Nova Fonte de Recurso</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalFontesRecursoMsg"></div>
                <form id="formFontesRecurso" name="formFontesRecurso" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group">
                        <label for="nomeFontesRecurso">Nome da Fonte de Recurso:</label>
                        <input type="text" id="nomeFontesRecurso" name="nomeFontesRecurso" value="" class="form-control" placeholder="Nome da Fonte de Recurso" required>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição:</label>
                        <textarea id="descricao" name="descricao" class="form-control" placeholder="Descrição"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tipoFontesRecurso">Tipo:</label>
                        <select id="tipoFontesRecurso" name="tipoFontesRecurso" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="INTERNA">INTERNA</option>
                            <option value="EXTERNA">EXTERNA</option>
                        </select>
                    </div>
                    <!-- Saldo de abertura da conta: o que ela ja tinha antes da
                         geracao de lancamentos. Nao entra no resultado; alimenta o quadro
                         Posicao de caixa. -->
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="saldoInicial">Saldo inicial (R$):</label>
                                <input type="text" id="saldoInicial" name="saldoInicial" value="" class="form-control" placeholder="0,00">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="dataSaldoInicial">Data do saldo:</label>
                                <input type="date" id="dataSaldoInicial" name="dataSaldoInicial" value="" class="form-control">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarFontesRecurso" name="c">
                    <input type="hidden" value="salvarFontesRecurso" name="m">
                    <input type="hidden" name="codFontesRecurso" id="codFontesRecurso" value=""/>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvar">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exclusão -->
<div class="modal fade" id="modalExclusao" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:450px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclusão</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir esta Fonte de Recurso?</p>
                <p><strong>Esta ação é irreversível.</strong></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>
