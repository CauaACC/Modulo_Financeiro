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

$objSRV = new GerenciarPlanoContasAC();
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
    .tbl-fin thead th.sort-asc .sort-arrow { color:#333; }
    .tbl-fin thead th.sort-desc .sort-arrow { color:#333; }
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
    .tbl-fin .badge-tipo .dot-orange { background:#f39c12; }
    .tbl-fin .badge-tipo .dot-red { background:#e74c3c; }
    .tbl-fin .badge-tipo .dot-purple { background:#8e44ad; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; transition:all 0.2s; font-size:13px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; border-color:#ccc; }
    .btn-acao.btn-acao-excluir:hover { background:#fef2f2; color:#c0392b; border-color:#f5c6cb; }
    .tbl-fin tbody td:first-child, .tbl-fin thead th:first-child { white-space:nowrap; width:1%; }

    /* Hierarquia */
    .conta-principal { background:#f8f9fb; }
    .conta-principal td.col-nome { font-weight:700; color:#222; }
    .conta-principal td.col-codigo { font-weight:700; }
    .sub-conta { background:#fff; }
    .sub-conta td.col-nome { font-weight:400; color:#555; }
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
            salvarPlanoContas();
        });

        $('#codPlanoContasPai').change(function () {
            var tipoPai = $('#codPlanoContasPai option:selected').data('tipo');
            if (!tipoPai) {
                $('#tipoConta').prop('disabled', false).val('').css('background', '');
                return;
            }
            $('#tipoConta').val(tipoPai);
            $('#tipoConta').prop('readonly', true).css('background', '#eee');
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
            // Atualizar setas e negrito
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
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return asc ? aNum - bNum : bNum - aNum;
            }
            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        $.each(rows, function(i, row) { tbody.append(row); });
    }

    function bindEventosTabela() {
        $('.editar').off('click').on('click', function() {
            var id = $(this).parents("tr").attr("id");
            editarPlanoContas(id);
        });
        $('.excluir').off('click').on('click', function() {
            _codExcluir = $(this).parents("tr").attr("id");
            $('#modalExclusao').modal('show');
        });
    }

    function abrirModalNovo() {
        limparCampos();
        $('#tipoConta').prop('disabled', false).prop('readonly', false).css('background', '');
        $('#modalPlanoContasLabel').html('<i class="fa fa-book" style="margin-right:8px; opacity:0.8;"></i>Novo Plano de Contas');
        $('#modalPlanoContas').modal('show');
    }

    function carregarTabelaPlanoContass() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarPlanoContas', 'm': 'carregarTabelaPlanoContass', 'ordenar': $('#ordenar').val(), '_submit_check': '1'},
            function(html) {
                $('#itens').find('tbody.inputs, tfoot').remove();
                $('#itens').append('<tbody class="inputs">' + html + '</tbody>');
                bindEventosTabela();
            }
        );
    }

    function salvarPlanoContas() {
        $('#formPlanoContas').ajaxSubmit({
            target: '#modalPlanoContasMsg',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            timeout: 4000,
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaPlanoContass();
                    $('#modalPlanoContas').modal('hide');
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function editarPlanoContas(codPlanoContas) {
        if (!codPlanoContas) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codPlanoContas': codPlanoContas, 'c': 'GerenciarPlanoContas', 'm': 'editarPlanoContas', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                $('#codPlanoContas').val(data.codPlanoContas);
                $('#codPlanoContasPai').val(data.codPlanoContasPai);
                $('#nomePlanoContas').val(data.nomePlanoContas);
                $('#tipoConta').val(data.tipoConta);
                $('#naturezaSaldo').val(data.naturezaSaldo);
                $('#codigoContabil').val(data.codigoContabil);
                $('#descricao').val(data.descricao);
                $('#aceitaLancamento').val(data.aceitaLancamento);
                var tipoPai = $('#codPlanoContasPai option:selected').data('tipo');
                if (tipoPai) {
                    $('#tipoConta').prop('readonly', true).css('background', '#eee');
                } else {
                    $('#tipoConta').prop('disabled', false).prop('readonly', false).css('background', '');
                }
                $('#modalPlanoContasLabel').html('<i class="fa fa-book" style="margin-right:8px; opacity:0.8;"></i>Editar Plano de Contas');
                $('#modalPlanoContas').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarExclusao() {
        if (!_codExcluir) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codPlanoContas': _codExcluir, 'c': 'GerenciarPlanoContas', 'm': 'excluirPlanoContas', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) { carregarTabelaPlanoContass(); }
                $('#modalExclusao').modal('hide');
                _codExcluir = null;
            },
            complete: function() { stopLoading(); }
        });
    }

    function limparCampos() {
        $('#codPlanoContas').val('');
        $('#codPlanoContasPai').val('');
        $('#nomePlanoContas').val('');
        $('#tipoConta').val('').prop('disabled', false).prop('readonly', false).css('background', '');
        $('#naturezaSaldo').val('');
        $('#codigoContabil').val('');
        $('#descricao').val('');
        $('#aceitaLancamento').val(1);
        $('#getMessage').html('');
        $('#modalPlanoContasMsg').html('');
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
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Plano de Contas</h4>
        <button type="button" class="btn btn-sm abriModal" onclick="abrirModalNovo()">
            <i class="fa fa-plus"></i> Novo Plano de Contas
        </button>
    </div>
    <div style="padding:0;">
        <table class="tbl-fin" id="itens">
            <thead>
                <tr>
                    <th style="width:80px;">Ações</th>
                    <th class="col-codigo" data-col="1">Código Contábil <span class="sort-arrow">↑↓</span></th>
                    <th data-col="2">Nome <span class="sort-arrow">↑↓</span></th>
                    <th data-col="3">Descrição</th>
                    <th data-col="4">Tipo de Conta <span class="sort-arrow">↑↓</span></th>
                    <th data-col="5">Natureza do Saldo <span class="sort-arrow">↑↓</span></th>
                    <th data-col="6">Aceita Lançamento</th>
                </tr>
            </thead>
            <tbody class="inputs">
                <?php
                $objSRV->carregarTabelaPlanoContass($fws_, $debug);
                ?>
            </tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:150px;"></div>

<!-- Modal Cadastro/Edição -->
<div class="modal fade" id="modalPlanoContas" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalPlanoContasLabel"><i class="fa fa-book"></i>Novo Plano de Contas</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalPlanoContasMsg"></div>
                <form id="formPlanoContas" name="formPlanoContas" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="codigoContabil">Código Contábil:</label>
                                <input type="text" id="codigoContabil" name="codigoContabil" value="" class="form-control" placeholder="Ex.: 0106">
                                <small class="text-muted">Deixe em branco para gerar automaticamente.</small>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="nomePlanoContas">Nome do Plano de Contas:</label>
                                <input type="text" id="nomePlanoContas" name="nomePlanoContas" value="" class="form-control" placeholder="Nome do Plano de Contas" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição:</label>
                        <textarea id="descricao" name="descricao" class="form-control" placeholder="Descrição"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="codPlanoContasPai">Plano de Contas Pai:</label>
                        <select id="codPlanoContasPai" name="codPlanoContasPai" class="form-control">
                            <option value="">Selecione Conta Pai se necessário</option>
                            <?php CarregarOptionsAC::planoContasPai($fws_) ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="tipoConta">Tipo da Conta:</label>
                                <select id="tipoConta" name="tipoConta" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="RECEITA">RECEITA</option>
                                    <option value="DESPESA">DESPESA</option>
                                    <option value="ATIVO">ATIVO</option>
                                    <option value="PASSIVO">PASSIVO</option>
                                    <option value="PATRIMONIO_LIQUIDO">PATRIMÔNIO LÍQUIDO</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="aceitaLancamento">Aceita Lançamento:</label>
                                <select id="aceitaLancamento" name="aceitaLancamento" class="form-control" required>
                                    <option value="1">SIM</option>
                                    <option value="0">NÃO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarPlanoContas" name="c">
                    <input type="hidden" value="salvarPlanoContas" name="m">
                    <input type="hidden" name="codPlanoContas" id="codPlanoContas" value=""/>
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
                <p>Deseja realmente excluir este Plano de Contas?</p>
                <p><strong>Esta ação é irreversível.</strong></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>
