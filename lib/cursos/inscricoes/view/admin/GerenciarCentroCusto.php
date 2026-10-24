<?php
require_once(dirname(__FILE__)."/../../include/config.php");

// Verificar permissão de financeiro
@$cod_msg = false;
if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

$objSRV = new GerenciarCentroCustoAC();
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
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; transition:all 0.2s; font-size:13px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; border-color:#ccc; }
    .btn-acao.btn-acao-excluir:hover { background:#fef2f2; color:#c0392b; border-color:#f5c6cb; }
    .tbl-fin tbody td:first-child, .tbl-fin thead th:first-child { white-space:nowrap; width:1%; }

    /* Hierarquia */
    .centro-pai { background:#f8f9fb; }
    .centro-pai td.col-nome { font-weight:700; color:#222; }
    .centro-pai td.col-codigo { font-weight:700; }
    .centro-filho { background:#fff; }
    .centro-filho td.col-nome { font-weight:400; color:#555; }
    .tree-connector { display:inline-block; color:#bbb; margin-right:6px; font-family:monospace; font-size:13px; vertical-align:middle; }
    .tree-indent { display:inline-block; width:20px; }
</style>
<script type="text/javascript">
    var _codExcluir = null;
    var _sortCol = -1;
    var _sortAsc = true;

    $(document).ready(function() {
        bindEventosTabela();

        $('#btnSalvar').click(function() {
            salvarCentroCusto();
        });

        // Sort por coluna
        $('.tbl-fin thead th[data-col]').on('click', function() {
            var col = parseInt($(this).data('col'));
            if (_sortCol === col) {
                _sortAsc = !_sortAsc;
            } else {
                _sortCol = col;
                _sortAsc = true;
            }
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
            var aNum = parseFloat(aVal);
            var bNum = parseFloat(bVal);
            if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        $.each(rows, function(i, row) { tbody.append(row); });
    }

    function bindEventosTabela() {
        $('.editar').off('click').on('click', function() {
            var id = $(this).parents("tr").attr("id");
            editarCentroCusto(id);
        });
        $('.excluir').off('click').on('click', function() {
            _codExcluir = $(this).parents("tr").attr("id");
            $('#modalExclusao').modal('show');
        });
    }

    function abrirModalNovo() {
        limparCampos();
        $('#modalCentroCustoLabel').html('<i class="fa fa-sitemap" style="margin-right:8px; opacity:0.8;"></i>Novo Centro de Custo');
        $('#modalCentroCusto').modal('show');
    }

    function carregarTabelaCentroCustos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCentroCusto', 'm': 'carregarTabelaCentroCustos', 'ordenar': $('#ordenar').val(), '_submit_check': '1'},
            function(html) {
                $('#itens').find('tbody.inputs, tfoot').remove();
                $('#itens').append('<tbody class="inputs">' + html + '</tbody>');
                bindEventosTabela();
            }
        );
    }

    function salvarCentroCusto() {
        $('#formCentroCusto').ajaxSubmit({
            target: '#modalCentroCustoMsg',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            timeout: 4000,
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaCentroCustos();
                    $('#modalCentroCusto').modal('hide');
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function editarCentroCusto(codCentroCusto) {
        if (!codCentroCusto) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codCentroCusto': codCentroCusto, 'c': 'GerenciarCentroCusto', 'm': 'editarCentroCusto', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                $('#codCentroCusto').val(data.codCentroCusto);
                $('#nomeCentroCusto').val(data.nomeCentroCusto);
                $('#codCentroCustoPai').val(data.codCentroCustoPai);
                $('#descricao').val(data.descricao);
                $('#modalCentroCustoLabel').html('<i class="fa fa-sitemap" style="margin-right:8px; opacity:0.8;"></i>Editar Centro de Custo');
                $('#modalCentroCusto').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarExclusao() {
        if (!_codExcluir) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codCentroCusto': _codExcluir, 'c': 'GerenciarCentroCusto', 'm': 'excluirCentroCusto', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) { carregarTabelaCentroCustos(); }
                $('#modalExclusao').modal('hide');
                _codExcluir = null;
            },
            complete: function() { stopLoading(); }
        });
    }

    function limparCampos() {
        $('#codCentroCusto').val('');
        $('#nomeCentroCusto').val('');
        $('#codCentroCustoPai').val('');
        $('#descricao').val('');
        $('#getMessage').html('');
        $('#modalCentroCustoMsg').html('');
    }
</script>
<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");
?>

<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Centros de Custo</h4>
        <button type="button" class="btn btn-sm abriModal" onclick="abrirModalNovo()">
            <i class="fa fa-plus"></i> Novo Centro de Custo
        </button>
    </div>
    <div style="padding:0;">
        <table class="tbl-fin" id="itens">
            <thead>
                <tr>
                    <th style="width:80px;">Ações</th>
                    <th class="col-codigo" data-col="1">Código <span class="sort-arrow">↑↓</span></th>
                    <th data-col="2">Nome <span class="sort-arrow">↑↓</span></th>
                    <th data-col="3">Descrição <span class="sort-arrow">↑↓</span></th>
                </tr>
            </thead>
            <tbody class="inputs">
                <?php
                $objSRV->carregarTabelaCentroCustos($fws_, $debug);
                ?>
            </tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:150px;"></div>

<!-- Modal Cadastro/Edição -->
<div class="modal fade" id="modalCentroCusto" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:550px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalCentroCustoLabel"><i class="fa fa-sitemap"></i>Novo Centro de Custo</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalCentroCustoMsg"></div>
                <form id="formCentroCusto" name="formCentroCusto" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group">
                        <label for="nomeCentroCusto">Nome do Centro de Custo:</label>
                        <input type="text" id="nomeCentroCusto" name="nomeCentroCusto" value="" class="form-control" placeholder="Nome do Centro de Custo" required>
                    </div>
                    <div class="form-group">
                        <label for="codCentroCustoPai">Centro de Custo Pai:</label>
                        <select id="codCentroCustoPai" name="codCentroCustoPai" class="form-control">
                            <option value="">Selecione Centro de Custo Pai se necessário</option>
                            <?php CarregarOptionsAC::centroCustoPai($fws_) ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição:</label>
                        <textarea id="descricao" name="descricao" class="form-control" placeholder="Descrição"></textarea>
                    </div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarCentroCusto" name="c">
                    <input type="hidden" value="salvarCentroCusto" name="m">
                    <input type="hidden" name="codCentroCusto" id="codCentroCusto" value=""/>
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
                <p>Deseja realmente excluir este Centro de Custo?</p>
                <p><strong>Esta ação é irreversível.</strong></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>
