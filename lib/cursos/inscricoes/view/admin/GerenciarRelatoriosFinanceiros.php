<?php
require_once(dirname(__FILE__)."/../../include/config.php");

// Verificar permissao de financeiro
@$cod_msg = false;
if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "N&atilde;o h&aacute; usu&aacute;rio logado, ou usu&aacute;rio logado n&atilde;o tem permiss&atilde;o de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

$objSRV = new GerenciarRelatoriosFinanceirosAC();
// aba Duplicidades: controller proprio, para nao concorrer com o de relatorios
$objDup = new GerenciarDuplicidadesAC();
$resumo = $objSRV->carregarResumoFinanceiro($fws_);
$porTipo = $objSRV->carregarLancamentosPorTipo($fws_);
$porPlano = $objSRV->carregarLancamentosPorPlanoContas($fws_);
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
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
    .tbl-fin .badge-tipo .dot-orange { background:#f39c12; }
    .tbl-fin .badge-tipo .dot-red { background:#e74c3c; }
    .tbl-fin .badge-tipo .dot-purple { background:#8e44ad; }

    .balancete-pai td { font-weight: 600; color: #333; background: #f8f9fa; }
    .balancete-filho td { color: #666; }
    .balancete-spacer td { height:16px; padding:0; border:none !important; background:#fff; }
    .balancete-spacer:hover td { background:#fff; }
    .tree-connector { color: #bbb; margin-right: 4px; font-size: 13px; }
    .tree-indent { display: inline-block; width: 16px; }

    .rel-cards { display:flex; gap:15px; flex-wrap:wrap; margin-bottom:20px; }
    .rel-card { flex:1; min-width:180px; background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:20px; }
    .rel-card-label { font-size:11px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.5px; margin-bottom:6px; }
    .rel-card-valor { font-size:24px; font-weight:700; color:#333; }
    .rel-card-valor.positivo { color:#27ae60; }
    .rel-card-valor.negativo { color:#e74c3c; }
    .rel-card-sub { font-size:12px; color:#999; margin-top:4px; }

    .rel-section { background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-bottom:15px; }
    .rel-section-header { display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee; }
    .rel-section-header h4 { margin:0; font-size:15px; font-weight:600; color:#333; }

    .rel-filtros { background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:15px 20px; margin-bottom:15px; display:flex; align-items:flex-end; gap:15px; flex-wrap:wrap; }
    .rel-filtros .form-group { margin-bottom:0; }
    .rel-filtros label { font-size:11px; text-transform:uppercase; color:#999; font-weight:600; display:block; margin-bottom:4px; }
    .rel-filtros .form-control { font-size:13px; height:34px; }

    .rel-tab { display:inline-block; padding:8px 16px; font-size:13px; color:#888; text-decoration:none; border-bottom:3px solid transparent; cursor:pointer; margin-right:4px; }
    .rel-tab:hover { color:#555; text-decoration:none; }
    .rel-tab.rel-tab-ativo { color:#333; font-weight:bold; border-bottom:3px solid #333; }

    .btn-excel { background:#1d6f42; border-color:#1d6f42; color:#fff; }
    .btn-excel:hover, .btn-excel:focus { background:#17593a; border-color:#17593a; color:#fff; }

    @media print {
        .no-print { display:none !important; }
        .rel-card { border:1px solid #ccc; }
        .rel-section { border:1px solid #ccc; page-break-inside:avoid; }
        body { font-size:11px; }
    }
</style>
<script type="text/javascript">
    var _sortCol = -1;
    var _sortAsc = true;

    $(document).ready(function() {
        $('#codPlanoContasRazao').change(function() { carregarTabelaRazao(); });
        // Tabs de relatorio
        $('.rel-tab').on('click', function(e) {
            e.preventDefault();
            $('.rel-tab').removeClass('rel-tab-ativo');
            $(this).addClass('rel-tab-ativo');
            var target = $(this).attr('href');
            $('.rel-tab-content').removeClass('active').hide();
            $(target).addClass('active').show();
        });

        // Drill-down: clicar na linha do balancete OU do resumo por plano de
        // contas abre o Razao daquela conta. Tudo dentro do relatorio -- os
        // filtros do topo continuam valendo.
        $(document).on('click', '#tblBalancete tr[data-conta], #tblPorPlano tr[data-conta]', function() {
            var conta = $(this).data('conta');
            if (!conta) return;
            irParaRazao(conta);
        });

        function irParaRazao(conta) {
            $('#codPlanoContasRazao').val(conta);
            $('.rel-tab[href="#tabRazao"]').trigger('click');
            carregarTabelaRazao();
        }

        // Sort por coluna
        $(document).on('click', '.tbl-fin thead th[data-col]', function() {
            var table = $(this).closest('table');
            var col = parseInt($(this).data('col'));
            if (_sortCol === col) {
                _sortAsc = !_sortAsc;
            } else {
                _sortCol = col;
                _sortAsc = true;
            }
            sortTable(table, col, _sortAsc);
            table.find('thead th').css('font-weight', '600');
            table.find('thead th .sort-arrow').html('&#8593;&#8595;').css('color', '#ccc');
            $(this).css('font-weight', '700');
            $(this).find('.sort-arrow').html(_sortAsc ? '&#8593;' : '&#8595;').css('color', '#333');
        });
    });

    // Data dd/mm/aaaa vira aaaammdd para ordenar como data, e nao como texto
    // (senao 01/12 vem antes de 02/01).
    function valorOrdenavel(txt) {
        var d = txt.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
        if (d) { return { num: parseInt(d[3] + d[2] + d[1], 10) }; }
        var n = parseFloat(txt.replace(/[R$\s.]/g, '').replace(',', '.'));
        if (!isNaN(n) && /\d/.test(txt)) { return { num: n }; }
        return { txt: txt };
    }

    function compararCelulas(aVal, bVal, asc) {
        var a = valorOrdenavel(aVal);
        var b = valorOrdenavel(bVal);
        if (a.num !== undefined && b.num !== undefined) {
            return asc ? a.num - b.num : b.num - a.num;
        }
        return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    }

    function sortTable(table, colIndex, asc) {
        var tbody = table.find('tbody.inputs').first();
        var rows = tbody.find('tr').not('tfoot tr').not('.balancete-spacer').toArray();
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(colIndex).text().trim().toLowerCase();
            var bVal = $(b).find('td').eq(colIndex).text().trim().toLowerCase();
            return compararCelulas(aVal, bVal, asc);
        });
        $.each(rows, function(i, row) { tbody.append(row); });
    }

    function filtrarRelatorio() {
        // Recarregar resumo
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {
                'c': 'GerenciarRelatoriosFinanceiros',
                'm': 'carregarResumoFinanceiroAjax',
                'dataInicio': $('#dataInicio').val(),
                'dataFim': $('#dataFim').val(),
                '_submit_check': '1'
            },
            function(data) {
                var d = JSON.parse(data);
                var r = d.resumo;
                $('#cardCreditos').text('R$ ' + formatarValor(r.total_creditos));
                $('#cardDebitos').text('R$ ' + formatarValor(r.total_debitos));
                var saldo = parseFloat(r.saldo);
                $('#cardSaldo').text('R$ ' + formatarValor(r.saldo));
                $('#cardSaldo').removeClass('positivo negativo').addClass(saldo >= 0 ? 'positivo' : 'negativo');
                $('#cardQtd').text(r.qtd_lancamentos);
                $('#cardConciliados').text(r.qtd_conciliados + ' conciliado' + (r.qtd_conciliados != 1 ? 's' : '') + ' / ' + r.qtd_nao_conciliados + ' pendente' + (r.qtd_nao_conciliados != 1 ? 's' : ''));

                // Atualizar tabela por tipo
                var htmlTipo = '';
                if (d.porTipo.length > 0) {
                    for (var i = 0; i < d.porTipo.length; i++) {
                        var t = d.porTipo[i];
                        var dotT = 'dot-blue';
                        if (t.tipo_lancamento == 'VENDA') dotT = 'dot-green';
                        if (t.tipo_lancamento == 'DESPESA_PAGA') dotT = 'dot-red';
                        if (t.tipo_lancamento == 'PAGAMENTO_FORNECEDOR') dotT = 'dot-orange';
                        var flgT = t.flg_lancamento == 'C' ? 'CR&Eacute;DITO' : 'D&Eacute;BITO';
                        var dotF = t.flg_lancamento == 'C' ? 'dot-green' : 'dot-red';
                        htmlTipo += '<tr><td><span class="badge-tipo"><span class="dot ' + dotT + '"></span>' + t.tipo_lancamento + '</span></td>';
                        htmlTipo += '<td><span class="badge-tipo"><span class="dot ' + dotF + '"></span>' + flgT + '</span></td>';
                        htmlTipo += '<td style="text-align:center;">' + t.qtd + '</td>';
                        htmlTipo += '<td style="text-align:right; font-weight:600;">R$ ' + formatarValor(t.total_valor) + '</td></tr>';
                    }
                } else {
                    htmlTipo = '<tr><td colspan="4">Nenhum lan&ccedil;amento encontrado.</td></tr>';
                }
                $('#tblPorTipo tbody.inputs').html(htmlTipo);

                // Atualizar tabela por plano
                var htmlPlano = '';
                if (d.porPlano.length > 0) {
                    for (var i = 0; i < d.porPlano.length; i++) {
                        var p = d.porPlano[i];
                        var dotP = 'dot-blue';
                        if (p.tipo_conta == 'RECEITA') dotP = 'dot-green';
                        if (p.tipo_conta == 'DESPESA') dotP = 'dot-red';
                        if (p.tipo_conta == 'PASSIVO') dotP = 'dot-orange';
                        if (p.tipo_conta == 'PATRIMONIO_LIQUIDO') dotP = 'dot-purple';
                        htmlPlano += '<tr data-conta="' + p.cod_plano_contas + '" style="cursor:pointer;"'
                                   + ' title="Ver o extrato desta conta no Razão">'
                                   + '<td>' + p.codigo_financeiro + '</td>';
                        htmlPlano += '<td class="col-nome">' + p.nome_plano_contas + '</td>';
                        htmlPlano += '<td><span class="badge-tipo"><span class="dot ' + dotP + '"></span>' + p.tipo_conta + '</span></td>';
                        htmlPlano += '<td style="text-align:center;">' + p.qtd + '</td>';
                        htmlPlano += '<td style="text-align:right; font-weight:600;">R$ ' + formatarValor(p.total_valor) + '</td></tr>';
                    }
                } else {
                    htmlPlano = '<tr><td colspan="5">Nenhum lan&ccedil;amento encontrado.</td></tr>';
                }
                $('#tblPorPlano tbody.inputs').html(htmlPlano);
            }
        );

        // Recarregar tabela de lancamentos
        carregarTabelaLancamentos();

        // Recarregar balancete
        carregarTabelaBalancete();

        // Recarregar posicao de caixa
        carregarTabelaPosicaoCaixa();

        // Recarregar razao (so faz sentido com conta escolhida)
        if ($('#codPlanoContasRazao').val()) {
            carregarTabelaRazao();
        }
    }

    function carregarTabelaLancamentos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {
                'c': 'GerenciarRelatoriosFinanceiros',
                'm': 'carregarTabelaLancamentos',
                'dataInicio': $('#dataInicio').val(),
                'dataFim': $('#dataFim').val(),
                'filtroTipo': $('#filtroTipo').val(),
                'filtroConciliado': $('#filtroConciliado').val(),
                '_submit_check': '1'
            },
            function(html) {
                $("#tblLancamentos tbody.inputs tr").remove();
                $("#tblLancamentos tbody.inputs").append(html);
            }
        );
    }

    // Download: vai por GET, porque a resposta e um arquivo
    function exportarBalancete() {
        var url = '<?php echo WB_URL_COMMAND ?>'
                + '&c=GerenciarRelatoriosFinanceiros&m=exportarBalancete&_submit_check=1'
                + '&dataInicio=' + encodeURIComponent($('#dataInicio').val() || '')
                + '&dataFim=' + encodeURIComponent($('#dataFim').val() || '');
        window.location = url;
    }

    // Export generico das demais abas (Resumo, Razao, Caixa, Lancamentos, Plano, Rateios, Centro, Fontes)
    function exportarAba(aba) {
        if (aba === 'razao' && !$('#codPlanoContasRazao').val()) {
            alert('Escolha uma conta na Razão antes de exportar.');
            return;
        }
        var url = '<?php echo WB_URL_COMMAND ?>'
                + '&c=GerenciarRelatoriosFinanceiros&m=exportarAba&_submit_check=1'
                + '&aba=' + encodeURIComponent(aba)
                + '&dataInicio=' + encodeURIComponent($('#dataInicio').val() || '')
                + '&dataFim=' + encodeURIComponent($('#dataFim').val() || '')
                + '&filtroTipo=' + encodeURIComponent($('#filtroTipo').val() || '')
                + '&filtroConciliado=' + encodeURIComponent($('#filtroConciliado').val() || '')
                + '&codPlanoContasRazao=' + encodeURIComponent($('#codPlanoContasRazao').val() || '');
        window.location = url;
    }

    function carregarTabelaPosicaoCaixa() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {
                'c': 'GerenciarRelatoriosFinanceiros',
                'm': 'carregarTabelaPosicaoCaixa',
                'dataInicio': $('#dataInicio').val(),
                'dataFim': $('#dataFim').val(),
                '_submit_check': '1'
            },
            function(html) {
                $("#tblCaixa tbody.inputs").html(html);
            }
        );
    }

    function carregarTabelaRazao() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {
                'c': 'GerenciarRelatoriosFinanceiros',
                'm': 'carregarTabelaRazao',
                'codPlanoContasRazao': $('#codPlanoContasRazao').val(),
                'dataInicio': $('#dataInicio').val(),
                'dataFim': $('#dataFim').val(),
                '_submit_check': '1'
            },
            function(html) {
                var tbl = $("#tblRazao");
                tbl.find("tbody.inputs").first().html(html);
            }
        );
    }

    function carregarTabelaBalancete() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {
                'c': 'GerenciarRelatoriosFinanceiros',
                'm': 'carregarTabelaBalancete',
                'dataInicio': $('#dataInicio').val(),
                'dataFim': $('#dataFim').val(),
                '_submit_check': '1'
            },
            function(html) {
                var tbl = $("#tblBalancete");
                tbl.find("tbody.inputs tr").remove();
                tbl.find("tfoot").remove();
                tbl.find("tbody.inputs").first().append(html);
            }
        );
    }

    function limparFiltros() {
        $('#dataInicio').val('');
        $('#dataFim').val('');
        $('#filtroTipo').val('');
        $('#filtroConciliado').val('');
        filtrarRelatorio();
    }

    function formatarValor(valor) {
        var v = parseFloat(valor);
        if (isNaN(v)) return '0,00';
        return v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function imprimirRelatorio() {
        window.print();
    }
</script>
<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");
?>

<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden" style="display:none; position:fixed; top:10px; left:50%; transform:translateX(-50%); z-index:9999; min-width:350px; max-width:600px; box-shadow:0 4px 12px rgba(0,0,0,0.25); background:#dff0d8; border:1px solid #d6e9c6; color:#3c763d; padding:15px; border-radius:4px;"></div>
<div id="erro" class="hidden" style="display:none; position:fixed; top:10px; left:50%; transform:translateX(-50%); z-index:9999; min-width:350px; max-width:600px; box-shadow:0 4px 12px rgba(0,0,0,0.25); background:#f2dede; border:1px solid #ebccd1; color:#a94442; padding:15px; border-radius:4px;"></div>

<!-- Filtros -->
<div class="rel-filtros no-print" style="margin-top:10px;">
    <div class="form-group">
        <label for="dataInicio">Data In&iacute;cio</label>
        <input type="date" id="dataInicio" name="dataInicio" class="form-control" style="width:160px;">
    </div>
    <div class="form-group">
        <label for="dataFim">Data Fim</label>
        <input type="date" id="dataFim" name="dataFim" class="form-control" style="width:160px;">
    </div>
    <div class="form-group">
        <label for="filtroTipo">Tipo Lan&ccedil;amento</label>
        <select id="filtroTipo" name="filtroTipo" class="form-control" style="width:200px;">
            <option value="">Todos</option>
            <option value="VENDA">Venda</option>
            <option value="PAGAMENTO_FORNECEDOR">Pagamento de Fornecedor</option>
            <option value="DESPESA_PAGA">Despesa Paga</option>
        </select>
    </div>
    <div class="form-group">
        <label for="filtroConciliado">Conciliado</label>
        <select id="filtroConciliado" name="filtroConciliado" class="form-control" style="width:120px;">
            <option value="">Todos</option>
            <option value="1">SIM</option>
            <option value="0">N&Atilde;O</option>
        </select>
    </div>
    <div class="form-group">
        <button type="button" class="btn btn-sm btn-primary" onclick="filtrarRelatorio()" style="margin-bottom:0;">
            <i class="fa fa-filter"></i> Filtrar
        </button>
        <button type="button" class="btn btn-sm btn-default" onclick="limparFiltros()" style="margin-bottom:0;">
            <i class="fa fa-eraser"></i> Limpar
        </button>
        <button type="button" class="btn btn-sm btn-default" onclick="imprimirRelatorio()" style="margin-bottom:0;">
            <i class="fa fa-print"></i> Imprimir
        </button>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="rel-cards">
    <div class="rel-card">
        <div class="rel-card-label"><i class="fa fa-arrow-up" style="color:#27ae60; margin-right:4px;"></i>Total Cr&eacute;ditos</div>
        <div class="rel-card-valor positivo" id="cardCreditos">R$ <?php echo number_format($resumo['total_creditos'], 2, ',', '.'); ?></div>
        <div class="rel-card-sub">Vendas</div>
    </div>
    <div class="rel-card">
        <div class="rel-card-label"><i class="fa fa-arrow-down" style="color:#e74c3c; margin-right:4px;"></i>Total D&eacute;bitos</div>
        <div class="rel-card-valor negativo" id="cardDebitos">R$ <?php echo number_format($resumo['total_debitos'], 2, ',', '.'); ?></div>
        <div class="rel-card-sub">Pagamentos e Despesas</div>
    </div>
    <div class="rel-card">
        <div class="rel-card-label"><i class="fa fa-balance-scale" style="color:#2980b9; margin-right:4px;"></i>Saldo</div>
        <div class="rel-card-valor <?php echo ($resumo['saldo'] >= 0) ? 'positivo' : 'negativo'; ?>" id="cardSaldo">R$ <?php echo number_format($resumo['saldo'], 2, ',', '.'); ?></div>
        <div class="rel-card-sub">Cr&eacute;ditos - D&eacute;bitos</div>
    </div>
    <div class="rel-card">
        <div class="rel-card-label"><i class="fa fa-list" style="color:#8e44ad; margin-right:4px;"></i>Lan&ccedil;amentos</div>
        <div class="rel-card-valor" id="cardQtd"><?php echo $resumo['qtd_lancamentos']; ?></div>
        <div class="rel-card-sub" id="cardConciliados"><?php echo $resumo['qtd_conciliados']; ?> conciliado<?php echo $resumo['qtd_conciliados'] != 1 ? 's' : ''; ?> / <?php echo $resumo['qtd_nao_conciliados']; ?> pendente<?php echo $resumo['qtd_nao_conciliados'] != 1 ? 's' : ''; ?></div>
    </div>
</div>

<!-- Tabs de Relatorio -->
<div style="border-bottom:1px solid #ddd; margin-bottom:0; padding-bottom:0;" class="no-print">
    <a class="rel-tab rel-tab-ativo" href="#tabResumo"><i class="fa fa-pie-chart"></i> Resumo</a>
    <a class="rel-tab" href="#tabBalancete"><i class="fa fa-balance-scale"></i> Balancete</a>
    <a class="rel-tab" href="#tabRazao"><i class="fa fa-list-ul"></i> Raz&atilde;o</a>
    <a class="rel-tab" href="#tabCaixa"><i class="fa fa-university"></i> Posi&ccedil;&atilde;o de caixa</a>
    <a class="rel-tab" href="#tabLancamentos"><i class="fa fa-list-alt"></i> Lan&ccedil;amentos</a>
    <a class="rel-tab" href="#tabPlanoContas"><i class="fa fa-sitemap"></i> Plano de Contas</a>
    <a class="rel-tab" href="#tabRateios"><i class="fa fa-percent"></i> Rateios</a>
    <a class="rel-tab" href="#tabCentroCusto"><i class="fa fa-building"></i> Centro de Custo</a>
    <a class="rel-tab" href="#tabFontesRecurso"><i class="fa fa-money"></i> Fontes de Recurso</a>
    <a class="rel-tab" href="#tabDuplicidades"><i class="fa fa-clone"></i> Duplicidades</a>
</div>

<!-- Tab Resumo -->
<div id="tabResumo" class="rel-tab-content active" style="margin-top:15px;">
    <!-- Por Tipo de Lancamento -->
    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-bar-chart" style="margin-right:8px; opacity:0.6;"></i>Lan&ccedil;amentos por Tipo</h4>
            <button type="button" class="btn btn-sm btn-excel" onclick="exportarAba('resumo')" title="Baixa o resumo (por tipo e por plano de contas) em Excel">
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblPorTipo">
                <thead>
                    <tr>
                        <th data-col="0">Tipo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Natureza <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2" style="text-align:center;">Quantidade <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3" style="text-align:right;">Valor Total <span class="sort-arrow">&#8593;&#8595;</span></th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php
                    if (!empty($porTipo)) {
                        foreach ($porTipo as $t) {
                            $dotT = 'dot-blue';
                            if ($t['tipo_lancamento'] == 'VENDA') $dotT = 'dot-green';
                            if ($t['tipo_lancamento'] == 'DESPESA_PAGA') $dotT = 'dot-red';
                            if ($t['tipo_lancamento'] == 'PAGAMENTO_FORNECEDOR') $dotT = 'dot-orange';
                            $flgT = ($t['flg_lancamento'] == 'C') ? 'CR&Eacute;DITO' : 'D&Eacute;BITO';
                            $dotF = ($t['flg_lancamento'] == 'C') ? 'dot-green' : 'dot-red';
                            ?>
                            <tr>
                                <td><span class="badge-tipo"><span class="dot <?php echo $dotT; ?>"></span><?php echo $t['tipo_lancamento']; ?></span></td>
                                <td><span class="badge-tipo"><span class="dot <?php echo $dotF; ?>"></span><?php echo $flgT; ?></span></td>
                                <td style="text-align:center;"><?php echo $t['qtd']; ?></td>
                                <td style="text-align:right; font-weight:600;">R$ <?php echo number_format($t['total_valor'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="4">Nenhum lan&ccedil;amento encontrado.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Por Plano de Contas -->
    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-sitemap" style="margin-right:8px; opacity:0.6;"></i>Lan&ccedil;amentos por Plano de Contas</h4>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblPorPlano">
                <thead>
                    <tr>
                        <th data-col="0">C&oacute;digo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Conta <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2">Tipo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3" style="text-align:center;">Quantidade <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="4" style="text-align:right;">Valor Total <span class="sort-arrow">&#8593;&#8595;</span></th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php
                    if (!empty($porPlano)) {
                        foreach ($porPlano as $p) {
                            $dotP = 'dot-blue';
                            if ($p['tipo_conta'] == 'RECEITA') $dotP = 'dot-green';
                            if ($p['tipo_conta'] == 'DESPESA') $dotP = 'dot-red';
                            if ($p['tipo_conta'] == 'PASSIVO') $dotP = 'dot-orange';
                            if ($p['tipo_conta'] == 'PATRIMONIO_LIQUIDO') $dotP = 'dot-purple';
                            ?>
                            <tr data-conta="<?php echo $p['cod_plano_contas']; ?>"
                                style="cursor:pointer;"
                                title="Ver o extrato desta conta no Razão">
                                <td><?php echo $p['codigo_financeiro']; ?></td>
                                <td class="col-nome"><?php echo $p['nome_plano_contas']; ?></td>
                                <td><span class="badge-tipo"><span class="dot <?php echo $dotP; ?>"></span><?php echo $p['tipo_conta']; ?></span></td>
                                <td style="text-align:center;"><?php echo $p['qtd']; ?></td>
                                <td style="text-align:right; font-weight:600;">R$ <?php echo number_format($p['total_valor'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="5">Nenhum lan&ccedil;amento encontrado.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Balancete -->
<div id="tabBalancete" class="rel-tab-content" style="display:none; margin-top:15px;">
    <div class="rel-section">
        <div class="rel-section-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h4><i class="fa fa-balance-scale" style="margin-right:8px; opacity:0.6;"></i>Balancete de Verifica&ccedil;&atilde;o</h4>
            <button type="button" class="btn btn-sm btn-excel" onclick="exportarBalancete()" title="Baixa o balancete completo em Excel, no formato do arquivo da fundação (respeita o período dos filtros)">
                <i class="fa fa-file-excel-o"></i> Baixar balancete (Excel)
            </button>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblBalancete">
                <thead>
                    <tr>
                        <th data-col="0">C&oacute;digo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Conta <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2">Tipo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3" style="text-align:right;">D&eacute;bito (R$) <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="4" style="text-align:right;">Cr&eacute;dito (R$) <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="5" style="text-align:right;">Saldo (R$) <span class="sort-arrow">&#8593;&#8595;</span></th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php
                    $objSRV->carregarTabelaBalancete($fws_, $debug);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Razao: o extrato de uma conta (o balancete mostra o saldo; aqui se ve
     o que formou aquele saldo, com o saldo correndo linha a linha) -->
<!-- Posicao de caixa: saldo inicial + entradas - saidas = saldo atual, por
     conta. Bate com o extrato do banco. -->
<div id="tabCaixa" class="rel-tab-content" style="display:none; margin-top:15px;">
    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-university" style="margin-right:8px; opacity:0.6;"></i>Posi&ccedil;&atilde;o de caixa</h4>
            <button type="button" class="btn btn-sm btn-excel" onclick="exportarAba('caixa')" title="Baixa a posição de caixa em Excel">
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblCaixa">
                <thead>
                    <tr>
                        <th data-col="0">Conta <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1" class="col-data">Saldo desde <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2" class="col-num">Saldo inicial <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3" class="col-num">Entradas <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="4" class="col-num">Sa&iacute;das <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="5" class="col-num">Saldo atual <span class="sort-arrow">&#8593;&#8595;</span></th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php $objSRV->carregarTabelaPosicaoCaixa($fws_, $debug); ?>
                </tbody>
            </table>
        </div>
        <p style="padding:10px 14px; margin:0; color:#888; font-size:12px;">
            O saldo inicial de cada conta &eacute; informado em Fontes de Recurso. N&atilde;o entra no balancete
            de resultado &mdash; ele mostra receitas e despesas do per&iacute;odo; este quadro mostra quanto h&aacute; em caixa.
        </p>
    </div>
</div>

<div id="tabRazao" class="rel-tab-content" style="display:none; margin-top:15px;">
    <div class="rel-section">
        <div class="rel-section-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <h4><i class="fa fa-list-ul" style="margin-right:8px; opacity:0.6;"></i>Raz&atilde;o &mdash; extrato da conta</h4>
            <div style="display:flex; align-items:center; gap:8px;">
                <label for="codPlanoContasRazao" style="margin:0; font-size:12px; color:#666; font-weight:600;">CONTA:</label>
                <select id="codPlanoContasRazao" class="form-control" style="min-width:320px;">
                    <option value="">Selecione a conta...</option>
                    <?php CarregarOptionsAC::planoContasPai($fws_) ?>
                </select>
                <button type="button" class="btn btn-sm btn-excel" onclick="exportarAba('razao')" title="Baixa o extrato da conta selecionada em Excel">
                    <i class="fa fa-file-excel-o"></i> Exportar Excel
                </button>
            </div>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblRazao">
                <thead>
                    <tr>
                        <th data-col="0" class="col-data">Data <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Favorecido <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2">Hist&oacute;rico <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3" class="col-codigo">Documento <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="4" class="col-num">D&eacute;bito (R$) <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="5" class="col-num">Cr&eacute;dito (R$) <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="6" class="col-num">Saldo (R$) <span class="sort-arrow">&#8593;&#8595;</span></th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <tr><td colspan="7" style="padding:14px; color:#888;">Escolha uma conta para ver o extrato.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Lancamentos -->
<div id="tabLancamentos" class="rel-tab-content" style="display:none; margin-top:15px;">
    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-list-alt" style="margin-right:8px; opacity:0.6;"></i>Lan&ccedil;amentos Cont&aacute;beis</h4>
            <button type="button" class="btn btn-sm btn-excel" onclick="exportarAba('lancamentos')" title="Baixa os lançamentos (respeita os filtros) em Excel">
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblLancamentos">
                <thead>
                    <tr>
                        <th data-col="0">Dt. Compet&ecirc;ncia <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Dt. Financeira <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2">Tipo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3">Natureza <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="4" style="text-align:right;">Valor <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="5">Plano de Contas <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="6">Origem <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="7">Hist&oacute;rico</th>
                        <th data-col="8">Conciliado <span class="sort-arrow">&#8593;&#8595;</span></th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php
                    $objSRV->carregarTabelaLancamentos($fws_, $debug);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Plano de Contas -->
<div id="tabPlanoContas" class="rel-tab-content" style="display:none; margin-top:15px;">
    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-sitemap" style="margin-right:8px; opacity:0.6;"></i>Plano de Contas Cadastrado</h4>
            <button type="button" class="btn btn-sm btn-excel" onclick="exportarAba('planocontas')" title="Baixa o plano de contas em Excel">
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblPlanoContas">
                <thead>
                    <tr>
                        <th data-col="0">C&oacute;digo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Nome <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2">Tipo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3">Natureza Saldo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="4">Aceita Lan&ccedil;amento <span class="sort-arrow">&#8593;&#8595;</span></th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php
                    $objSRV->carregarTabelaPlanoContas($fws_, $debug);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Rateios -->
<div id="tabRateios" class="rel-tab-content" style="display:none; margin-top:15px;">
    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-percent" style="margin-right:8px; opacity:0.6;"></i>Rateios Cadastrados</h4>
            <button type="button" class="btn btn-sm btn-excel" onclick="exportarAba('rateios')" title="Baixa os rateios em Excel">
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblRateios">
                <thead>
                    <tr>
                        <th data-col="0">C&oacute;digo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Nome <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2" style="text-align:right;">Percentual <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3">Descri&ccedil;&atilde;o</th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php
                    $objSRV->carregarTabelaRateios($fws_, $debug);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Centro de Custo -->
<div id="tabCentroCusto" class="rel-tab-content" style="display:none; margin-top:15px;">
    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-building" style="margin-right:8px; opacity:0.6;"></i>Centros de Custo Cadastrados</h4>
            <button type="button" class="btn btn-sm btn-excel" onclick="exportarAba('centrocusto')" title="Baixa os centros de custo em Excel">
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblCentroCusto">
                <thead>
                    <tr>
                        <th data-col="0">C&oacute;digo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Nome <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2">Descri&ccedil;&atilde;o</th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php
                    $objSRV->carregarTabelaCentroCusto($fws_, $debug);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Fontes de Recurso -->
<div id="tabFontesRecurso" class="rel-tab-content" style="display:none; margin-top:15px;">
    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-money" style="margin-right:8px; opacity:0.6;"></i>Fontes de Recurso Cadastradas</h4>
            <button type="button" class="btn btn-sm btn-excel" onclick="exportarAba('fontesrecurso')" title="Baixa as fontes de recurso em Excel">
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <div style="padding:0;">
            <table class="tbl-fin" id="tblFontesRecurso">
                <thead>
                    <tr>
                        <th data-col="0">Nome <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Descri&ccedil;&atilde;o</th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php
                    $objSRV->carregarTabelaFontesRecurso($fws_, $debug);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Duplicidades: pagamento repetido do mesmo compromisso. Parcelamento
     NAO cai aqui -- cada parcela tem pedido proprio. Controller em arquivo
     separado (GerenciarDuplicidadesAC) de proposito. -->
<div id="tabDuplicidades" class="rel-tab-content" style="display:none; margin-top:15px;">

    <div style="background:#fdf6e3; border:1px solid #f0e0b0; color:#8a6d3b; border-radius:10px; padding:12px 16px; margin-bottom:15px; font-size:12px;">
        <i class="fa fa-info-circle" style="margin-right:6px;"></i>
        Esta aba <strong>aponta</strong>, n&atilde;o conclui. Mensalidade e parcelamento n&atilde;o aparecem aqui
        (cada parcela tem pedido pr&oacute;prio); o que aparece &eacute; a mesma cobran&ccedil;a paga mais de uma vez.
        Casos antigos de carga/importa&ccedil;&atilde;o podem cair no filtro &mdash; a decis&atilde;o de devolver &eacute; humana.
    </div>

    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-exclamation-triangle" style="margin-right:8px; opacity:0.6;"></i>1. Mesmo pedido pago mais de uma vez</h4>
            <span style="font-size:12px; color:#999;">crit&eacute;rio definitivo</span>
        </div>
        <div style="padding:0; overflow-x:auto;">
            <table class="tbl-fin" id="tblDupPedido">
                <thead>
                    <tr>
                        <th data-col="0" class="col-codigo">Pedido <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1">Inscrito <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2" class="col-num">Pagtos <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3" class="col-num">Total pago <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="4" class="col-codigo">C&oacute;digos <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="5" class="col-data">Primeiro <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="6" class="col-data">&Uacute;ltimo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="7" class="col-badge">No gateway <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th class="col-badge">Ver</th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php $objDup->carregarTabelaMesmoPedido($fws_, $debug); ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-search" style="margin-right:8px; opacity:0.6;"></i>2. Mesmo inscrito e mesmo valor em at&eacute; 7 dias</h4>
            <span style="font-size:12px; color:#999;">pega o que n&atilde;o tem pedido (boleto Ita&uacute;, PIX manual, carga)</span>
        </div>
        <div style="padding:0; overflow-x:auto;">
            <table class="tbl-fin" id="tblDupValor">
                <thead>
                    <tr>
                        <th data-col="0">Inscrito <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1" class="col-num">Valor <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2" class="col-num">Pares <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3" class="col-codigo">Pagamentos <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="4" class="col-data">Primeiro <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="5" class="col-data">&Uacute;ltimo <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th class="col-badge">Ver</th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php $objDup->carregarTabelaMesmoValor($fws_, $debug); ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="rel-section">
        <div class="rel-section-header">
            <h4><i class="fa fa-life-ring" style="margin-right:8px; opacity:0.6;"></i>3. Pagou mais que o esperado</h4>
            <span style="font-size:12px; color:#999;">rede de seguran&ccedil;a &mdash; esperado = soma dos pedidos ativos</span>
        </div>
        <div style="padding:0; overflow-x:auto;">
            <table class="tbl-fin" id="tblDupMaior">
                <thead>
                    <tr>
                        <th data-col="0">Inscrito <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="1" class="col-num">Pagtos <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="2" class="col-num">Esperado <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="3" class="col-num">Total pago <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th data-col="4" class="col-num">Diferen&ccedil;a <span class="sort-arrow">&#8593;&#8595;</span></th>
                        <th class="col-badge">Ver</th>
                    </tr>
                </thead>
                <tbody class="inputs">
                    <?php $objDup->carregarTabelaPagouMaior($fws_, $debug); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="height:150px;"></div>
