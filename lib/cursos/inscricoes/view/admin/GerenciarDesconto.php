<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarDescontoAC();
?>
<script type="text/javascript">
    var _codExcluir = null;
    var _sortCol = -1;
    var _sortAsc = true;

    $(document).ready(function() {
        bindEventosTabela();

        $('#btnSalvar').click(function() {
            salvarDesconto();
        });

        $('#cpf').on('paste', function(e) {
            var pastedText = (e.originalEvent || e).clipboardData.getData('text');
            var cleanedText = pastedText.replace(/[\s.-]+/g, '').trim();
            $(this).val(cleanedText);
            e.preventDefault();
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
            var aNum = parseFloat(aVal.replace(',', '.'));
            var bNum = parseFloat(bVal.replace(',', '.'));
            if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        $.each(rows, function(i, row) { tbody.append(row); });
    }

    function bindEventosTabela() {
        $('#itens').off('click', '.editar').on('click', '.editar', function() {
            var id = $(this).closest('tr').attr('id');
            editarDesconto(id);
        });
        $('#itens').off('click', '.excluir').on('click', '.excluir', function() {
            _codExcluir = $(this).closest('tr').attr('id');
            $('#modalExclusao').modal('show');
        });
    }

    function abrirModalNovo() {
        limparCampos();
        $('#modalDescontoLabel').html('<i class="fa fa-percent" style="margin-right:8px; opacity:0.8;"></i>Novo Desconto');
        $('#novoCodDesconto').val(0);
        $('#modalDesconto').modal('show');
    }

    function carregarTabelaDescontos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarDesconto', 'm': 'carregarTabelaDescontos', '_submit_check': '1'},
            function(html) {
                $("#itens tbody.inputs tr").remove();
                $("#itens tbody.inputs").append(html);
                bindEventosTabela();
            }
        );
    }

    function salvarDesconto() {
        $('#formDesconto').ajaxSubmit({
            target: '#modalDescontoMsg',
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            error: function(xhr, status, error) {
                stopLoading();
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao salvar o desconto. Tente novamente ou entre em contato com o suporte.');
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaDescontos();
                    $('#modalDesconto').modal('hide');
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function editarDesconto(codDesconto) {
        if (!codDesconto) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codDesconto': codDesconto, 'c': 'GerenciarDesconto', 'm': 'editarDesconto', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                limparCampos();
                $('#codDesconto').val(data.codDesconto);
                $('#nomeDesconto').val(data.nomeDesconto);
                $('#cpf').val(data.cpf);
                $('#email').val(data.email);
                $('#valorDoado').val(data.valorDoado);
                $('#valorFixo').val(data.valorFixo);
                $('#valorDesconto').val(data.valorDesconto);
                $('#porcentagemDesconto').val(data.porcentagemDesconto);
                $('#novoCodDesconto').val(1);
                $('#modalDescontoLabel').html('<i class="fa fa-percent" style="margin-right:8px; opacity:0.8;"></i>Editar Desconto');
                $('#modalDesconto').modal('show');
            },
            error: function(xhr, status, error) {
                stopLoading();
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao carregar o desconto. Tente novamente ou entre em contato com o suporte.');
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarExclusao() {
        if (!_codExcluir) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codDesconto': _codExcluir, 'c': 'GerenciarDesconto', 'm': 'excluirDesconto', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) { carregarTabelaDescontos(); }
                $('#modalExclusao').modal('hide');
                _codExcluir = null;
            },
            error: function(xhr, status, error) {
                stopLoading();
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao excluir o desconto. Tente novamente ou entre em contato com o suporte.');
            },
            complete: function() { stopLoading(); }
        });
    }

    function limparCampos() {
        $('#codDesconto').val('');
        $('#nomeDesconto').val('');
        $('#cpf').val('');
        $('#email').val('');
        $('#valorDoado').val('');
        $('#valorFixo').val('');
        $('#valorDesconto').val('');
        $('#porcentagemDesconto').val('');
        $('#novoCodDesconto').val(0);
        $('#modalDescontoMsg').html('');
    }
</script>
<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_inscricoes.php");
?>

<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Descontos</h4>
        <button type="button" class="btn btn-sm abriModal" onclick="abrirModalNovo()">
            <i class="fa fa-plus"></i> Novo Desconto
        </button>
    </div>
    <div style="padding:0;">
        <table class="tbl-fin" id="itens" style="table-layout:fixed; width:100%;">
            <thead>
                <tr>
                    <th class="col-1">A&ccedil;&otilde;es</th>
                    <th data-col="1" class="col-4">Nome <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="2" class="col-1">CPF <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="3" class="col-1">Valor Doado <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="4" class="col-1">Valor Inscri&ccedil;&atilde;o <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="5" class="col-1">Valor Desconto <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="6" class="col-1">% Desconto <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="7" class="col-1">J&aacute; Inscrito <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="8" class="col-1">Pagamento <span class="sort-arrow">&uarr;&darr;</span></th>
                </tr>
            </thead>
            <tbody class="inputs">
                <?php
                $objSRV->carregarTabelaDescontos($fws_, $debug);
                ?>
            </tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:150px;"></div>

<!-- Modal Cadastro/Edicao -->
<div class="modal fade" id="modalDesconto" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:650px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalDescontoLabel"><i class="fa fa-percent" style="margin-right:8px; opacity:0.8;"></i>Novo Desconto</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalDescontoMsg"></div>
                <form id="formDesconto" name="formDesconto" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="row">
                        <div class="form-group col-sm-8">
                            <label for="nomeDesconto">Nome Benefici&aacute;rio:</label>
                            <input type="text" id="nomeDesconto" name="nomeInscrito" value=""
                                   class="form-control" placeholder="Nome Benefici&aacute;rio" required>
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="cpf">CPF:</label>
                            <input type="text" id="cpf" name="cpf" value="" maxlength="14"
                                   onkeypress="return isNumInt(getKey(event));"
                                   class="form-control" placeholder="CPF" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="text" id="email" name="email" value=""
                               class="form-control" placeholder="E-mail">
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-3">
                            <label for="valorDoado">Valor Doado:</label>
                            <input type="text" id="valorDoado" name="valorDoado" value=""
                                   class="form-control" placeholder="0,00">
                        </div>
                        <div class="form-group col-sm-3">
                            <label for="valorFixo">Valor Inscri&ccedil;&atilde;o:</label>
                            <input type="text" id="valorFixo" name="valorFixo" value=""
                                   class="form-control" placeholder="0,00">
                        </div>
                        <div class="form-group col-sm-3">
                            <label for="valorDesconto">Valor Desconto:</label>
                            <input type="text" id="valorDesconto" name="valorDesconto" value=""
                                   class="form-control" placeholder="0,00">
                        </div>
                        <div class="form-group col-sm-3">
                            <label for="porcentagemDesconto">% Desconto:</label>
                            <input type="text" id="porcentagemDesconto" name="porcentagemDesconto" value=""
                                   class="form-control" placeholder="0">
                        </div>
                    </div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarDesconto" name="c">
                    <input type="hidden" value="salvarDesconto" name="m">
                    <input type="hidden" name="novoCodDesconto" id="novoCodDesconto" value="0"/>
                    <input type="hidden" name="codDesconto" id="codDesconto" value=""/>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvar">Salvar</button>
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
                <p>Deseja realmente excluir este desconto?</p>
                <p><strong>Esta a&ccedil;&atilde;o &eacute; irrevers&iacute;vel.</strong></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>
