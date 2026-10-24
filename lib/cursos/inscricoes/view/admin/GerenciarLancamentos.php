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

$objSRV = new GerenciarLancamentosAC();
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
    .tbl-fin .badge-tipo .dot-orange { background:#f39c12; }
    .tbl-fin .badge-tipo .dot-red { background:#e74c3c; }
    .tbl-fin .badge-tipo .dot-purple { background:#8e44ad; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; transition:all 0.2s; font-size:13px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; border-color:#ccc; }
    .btn-acao.btn-acao-excluir:hover { background:#fef2f2; color:#c0392b; border-color:#f5c6cb; }
    .tbl-fin tbody td:first-child, .tbl-fin thead th:first-child { white-space:nowrap; width:1%; }

    /* Listagem de lancamentos: sao muitas colunas, entao o texto so quebra
       onde ha espaco util (historico, favorecido, nome da conta). Sem isso o
       break-word parte "DESPESA_PAGA" letra a letra. */
    #itens tbody td { word-break:normal; overflow-wrap:normal; }
    #itens .col-conta { min-width:150px; max-width:230px; word-break:normal; overflow-wrap:break-word; }
    #itens .col-conta .conta-codigo { font-weight:700; }
    #itens .col-conta .conta-nome { display:block; font-size:11px; color:#888; font-weight:400; line-height:1.3; }
    #itens .col-hist { min-width:220px; word-break:normal; overflow-wrap:break-word; font-size:12px; color:#555; }
    #itens td.col-nome { min-width:140px; word-break:normal; overflow-wrap:break-word; }
    #itens .data-secundaria { display:block; font-size:11px; color:#999; font-weight:400; }
    #itens .badge-tipo { white-space:nowrap; }
</style>
<script type="text/javascript">
    var _codExcluir = null;
    var _sortCol = -1;
    var _sortAsc = true;

    $(document).ready(function() {
        bindEventosTabela();
        verificarSituacao();
        verificarFechamento();

        // Filtro vindo de link (aba Duplicidades): aplica ja na abertura
        lerFiltroDaUrl();
        if (_filtroLink.pedido || _filtroLink.inscrito || _filtroLink.pagamento) {
            carregarTabelaLancamentos();
        }

        // Filtros: select aplica na hora; na busca livre espera o Enter
        $('#fPlanoContas, #fTipo, #fOrigem, #fConciliado, #fDataInicio, #fDataFim').change(function() {
            carregarTabelaLancamentos();
        });
        $('#fBusca').keypress(function(e) {
            if (e.which === 13) {
                e.preventDefault();
                carregarTabelaLancamentos();
            }
        });

        $('#btnSalvar').click(function() {
            salvarLancamentos();
        });

        $('#tipoLancamento').change(function () {
            var tipoLancamento = $(this).val();
            esconderLimparCD();
            if (tipoLancamento === 'PAGAMENTO_FORNECEDOR' || tipoLancamento === 'DESPESA_PAGA') {
                $('#campoDebito').show();
                $('#campoCredito').hide();
                $('#debito').prop('required', true);
            }
            if (tipoLancamento === 'VENDA') {
                $('#campoCredito').show();
                $('#campoDebito').hide();
                $('#credito').prop('required', true);
            }
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

    function esconderLimparCD() {
        $('#campoDebito').hide();
        $('#campoCredito').hide();
        $('#debito').prop('required', false).val('');
        $('#credito').prop('required', false).val('');
    }

    // Data dd/mm/aaaa vira aaaammdd, e "R$ 1.234,56" vira numero -- senao
    // data e dinheiro ordenam como texto.
    function valorOrdenavel(txt) {
        var d = txt.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
        if (d) { return { num: parseInt(d[3] + d[2] + d[1], 10) }; }
        var n = parseFloat(txt.replace(/[R$\s.]/g, '').replace(',', '.'));
        if (!isNaN(n) && /\d/.test(txt)) { return { num: n }; }
        return { txt: txt };
    }

    function sortTable(colIndex, asc) {
        var tbody = $('#itens tbody.inputs').first();
        var rows = tbody.find('tr').not('tfoot tr').toArray();
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(colIndex).text().trim().toLowerCase();
            var bVal = $(b).find('td').eq(colIndex).text().trim().toLowerCase();
            var a2 = valorOrdenavel(aVal);
            var b2 = valorOrdenavel(bVal);
            if (a2.num !== undefined && b2.num !== undefined) {
                return asc ? a2.num - b2.num : b2.num - a2.num;
            }
            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        $.each(rows, function(i, row) { tbody.append(row); });
    }

    function bindEventosTabela() {
        $('.editar').off('click').on('click', function() {
            var id = $(this).parents("tr").attr("id");
            editarLancamentos(id);
        });
        $('.excluir').off('click').on('click', function() {
            _codExcluir = $(this).parents("tr").attr("id");
            $('#modalExclusao').modal('show');
        });
    }

    function abrirModalNovo() {
        limparCampos();
        $('#comprovante').val('');
        $('#comprovanteAtual').html('');
        $('#btnSalvarComoNovo').hide();
        $('#modalLancamentoLabel').html('<i class="fa fa-pencil-square-o" style="margin-right:8px; opacity:0.8;"></i>Novo Lançamento');
        $('#modalLancamento').modal('show');
    }

    // Devolução: um lançamento de despesa como outro qualquer, só que ja aberto
    // com o tipo e a rubrica de devolução escolhidos. Falta o usuário informar
    // favorecido, valor real devolvido e documento (o valor costuma ser
    // diferente do que foi pago).
    function abrirDevolucao() {
        limparCampos();
        $('#comprovante').val('');
        $('#comprovanteAtual').html('');
        $('#btnSalvarComoNovo').hide();
        // tipo = despesa paga; dispara o change para exibir o campo débito
        $('#tipoLancamento').val('DESPESA_PAGA').trigger('change');
        $('#origemLancamento').val('MANUAL');
        // acha a rubrica de devolução no select sem código fixo: código 0283
        // ou nome com "DEVOL"/"DEV. C" (dev. c/ inscrição)
        var achou = '';
        $('#debito option').each(function() {
            var txt = $(this).text().toUpperCase();
            if (txt.indexOf('0283') === 0 || txt.indexOf('DEVOL') >= 0 || txt.indexOf('DEV. C') >= 0 || txt.indexOf('DEV.E DEV') >= 0) {
                achou = $(this).val();
                return false;
            }
        });
        if (achou) {
            $('#debito').val(achou);
        }
        $('#historico').val('Devolução de inscrição');
        $('#modalLancamentoLabel').html('<i class="fa fa-rotate-left" style="margin-right:8px; opacity:0.8;"></i>Lançar Devolução');
        var aviso = achou ? '' :
            '<div style="color:#b9770e; font-size:12px; margin-bottom:8px;"><i class="fa fa-exclamation-triangle"></i> '
            + 'Rubrica de devolução não encontrada no plano de contas &mdash; escolha o débito manualmente.</div>';
        $('#modalLancamentoMsg').html(aviso);
        $('#modalLancamento').modal('show');
    }

    // Filtros vindos de link (aba Duplicidades manda pedido/inscrito/pagamento).
    // Ficam fora dos campos da barra porque sao recorte pontual, nao escolha do
    // usuario -- por isso aparecem como aviso, com um "x" para sair dele.
    var _filtroLink = {inscrito: '', pagamento: '', pedido: ''};

    function lerFiltroDaUrl() {
        var q = new URLSearchParams(window.location.search);
        _filtroLink.inscrito  = q.get('inscrito')  || '';
        _filtroLink.pagamento = q.get('pagamento') || '';
        _filtroLink.pedido    = q.get('pedido')    || '';
        mostrarAvisoFiltroLink();
    }

    function mostrarAvisoFiltroLink() {
        var partes = [];
        if (_filtroLink.pedido)    partes.push('pedido <strong>' + _filtroLink.pedido + '</strong>');
        if (_filtroLink.inscrito)  partes.push('inscrito <strong>' + _filtroLink.inscrito + '</strong>');
        if (_filtroLink.pagamento) partes.push('pagamento <strong>' + _filtroLink.pagamento + '</strong>');
        if (!partes.length) { $('#avisoFiltroLink').hide(); return; }
        $('#avisoFiltroLinkTexto').html('Mostrando apenas os lançamentos de ' + partes.join(' e ') + '.');
        $('#avisoFiltroLink').show();
    }

    function sairFiltroLink() {
        _filtroLink = {inscrito: '', pagamento: '', pedido: ''};
        mostrarAvisoFiltroLink();
        carregarTabelaLancamentos();
    }

    // Os filtros vao junto em toda recarga da tabela -- inclusive depois de
    // salvar/excluir, para o usuario nao perder o recorte em que estava.
    function filtrosAtuais() {
        return {
            'c': 'GerenciarLancamentos',
            'm': 'carregarTabelaLancamentos',
            '_submit_check': '1',
            'dataInicio': $('#fDataInicio').val() || '',
            'dataFim': $('#fDataFim').val() || '',
            'codPlanoContas': $('#fPlanoContas').val() || '',
            'tipoLancamento': $('#fTipo').val() || '',
            'origemLancamento': $('#fOrigem').val() || '',
            'conciliado': $('#fConciliado').val() || '',
            'busca': $('#fBusca').val() || '',
            'filtroInscrito': _filtroLink.inscrito,
            'filtroPagamento': _filtroLink.pagamento,
            'filtroPedido': _filtroLink.pedido
        };
    }

    function limparFiltros() {
        $('#fDataInicio, #fDataFim, #fBusca').val('');
        $('#fPlanoContas, #fTipo, #fOrigem, #fConciliado').val('');
        _filtroLink = {inscrito: '', pagamento: '', pedido: ''};
        mostrarAvisoFiltroLink();
        carregarTabelaLancamentos();
    }

    function carregarTabelaLancamentos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            filtrosAtuais(),
            function(html) {
                var $table = $("#itens");
                $table.find("tfoot, tbody").remove();
                $table.append('<tbody class="inputs">' + html + '</tbody>');
                bindEventosTabela();
            }
        );
    }

    function salvarLancamentos() {
        $('#formLancamentos').ajaxSubmit({
            target: '#modalLancamentoMsg',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            timeout: 4000,
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaLancamentos();
                    $('#modalLancamento').modal('hide');
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function editarLancamentos(codLancamentos) {
        if (!codLancamentos) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codLancamentos': codLancamentos, 'c': 'GerenciarLancamentos', 'm': 'editarLancamentos', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                esconderLimparCD();
                if (data.flgLancamento === 'C') {
                    $('#credito').val(data.codPlanoContas);
                    $('#campoCredito').show();
                    $('#campoDebito').hide();
                    $('#credito').prop('required', true);
                } else if (data.flgLancamento === 'D') {
                    $('#debito').val(data.codPlanoContas);
                    $('#campoDebito').show();
                    $('#campoCredito').hide();
                    $('#debito').prop('required', true);
                }
                $('#codLancamentos').val(data.codLancamentos);
                $('#historico').val(data.historico);
                $('#origemLancamento').val(data.origemLancamento);
                $('#valor').val(data.valor);
                $('#dataFinanceira').val(data.dataFinanceira);
                $('#dataCompetencia').val(data.dataCompetencia);
                // comprovante ja anexado: mostra link e deixa claro que so troca se reenviar
                $('#comprovante').val('');
                if (data.arquivoComprovante) {
                    $('#comprovanteAtual').html('<i class="fa fa-paperclip"></i> anexado: '
                        + '<a href="<?php echo WB_URL_COMMAND ?>&c=GerenciarLancamentos&m=baixarComprovante&codLancamentos='
                        + data.codLancamentos + '&_submit_check=1" target="_blank">ver</a>'
                        + ' <span style="color:#999;">(envie outro para substituir)</span>');
                } else {
                    $('#comprovanteAtual').html('');
                }
                $('#tipoLancamento').val(data.tipoLancamento);
                $('#conciliado').val(data.conciliado);
                // recarrega centro de custo, fonte, favorecido e documento --
                // sem isto voltavam em branco e eram apagados ao salvar
                $('#codCentroCusto').val(data.codCentroCusto || '');
                $('#codFontesRecurso').val(data.codFontesRecurso || '');
                $('#codInscrito').val(data.codInscrito || '');
                $('#favorecido').val(data.favorecido || '');
                $('#numDocumento').val(data.numDocumento || '');
                $('#modalLancamentoLabel').html('<i class="fa fa-pencil-square-o" style="margin-right:8px; opacity:0.8;"></i>Editar Lançamento');
                // "Salvar como novo" so faz sentido a partir de um existente
                $('#btnSalvarComoNovo').show();
                $('#modalLancamento').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    // Salvar como novo: aproveita o lancamento aberto como modelo e grava OUTRO,
    // sem tocar no original. So limpa o codigo -- o salvar ja insere quando ele
    // vem vazio. Util para repetir conta/centro/fonte/favorecido.
    // O comprovante NAO e copiado: o arquivo pertence ao lancamento original.
    function salvarComoNovo() {
        if (!$('#codLancamentos').val()) return;
        $('#codLancamentos').val('');
        $('#comprovanteAtual').html('');
        $('#modalLancamentoLabel').html('<i class="fa fa-files-o" style="margin-right:8px; opacity:0.8;"></i>Novo Lançamento (a partir de outro)');
        $('#btnSalvarComoNovo').hide();
        salvarLancamentos();
    }

    function confirmarExclusao() {
        if (!_codExcluir) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codLancamentos': _codExcluir, 'c': 'GerenciarLancamentos', 'm': 'excluirLancamentos', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) { carregarTabelaLancamentos(); }
                $('#modalExclusao').modal('hide');
                _codExcluir = null;
            },
            complete: function() { stopLoading(); }
        });
    }

    function limparCampos() {
        esconderLimparCD();
        $('#codLancamentos').val('');
        $('#tipoLancamento').val('');
        $('#valor').val('');
        $('#dataFinanceira').val('');
        $('#dataCompetencia').val('');
        $('#origemLancamento').val('');
        $('#historico').val('');
        $('#conciliado').val('');
        $('#codCentroCusto').val('');
        $('#codFontesRecurso').val('');
        $('#codInscrito').val('');
        $('#favorecido').val('');
        $('#numDocumento').val('');
        $('#getMessage').html('');
        $('#modalLancamentoMsg').html('');
    }

    // Confere se os lancamentos automaticos estao em dia com os pagamentos e
    // com as regras de rateio -- nenhum dos dois se aplica retroativamente.
    function verificarSituacao() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarMotorFinanceiro', 'm': 'situacaoMotor', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            success: function(data) {
                if (!data.statusMessage || !data.dados.temLancamentos) { return; }
                var d = data.dados, partes = [];
                if (d.pagamentosNovos > 0) {
                    partes.push('<strong>' + d.pagamentosNovos + '</strong> pagamento(s) ainda n&atilde;o lancado(s)');
                }
                if (d.regrasAlteradas) {
                    partes.push('as <strong>regras de rateio mudaram</strong> depois da &uacute;ltima gera&ccedil;&atilde;o');
                }
                if (!partes.length) { return; }
                var txt = 'O balancete est&aacute; desatualizado: ' + partes.join(' e ') + '.';
                if (d.conciliados > 0) {
                    txt += ' <span style="color:#a07d3b;">' + d.conciliados + ' lan&ccedil;amento(s) conciliado(s) n&atilde;o ser&atilde;o refeitos.</span>';
                }
                // regra alterada exige refazer; pagamento novo so precisa do incremental
                var modo = d.regrasAlteradas ? 'completo' : 'incremental';
                $('#avisoTexto').html(txt);
                $('#avisoAcao').text(d.regrasAlteradas ? 'Refazer tudo agora' : 'Gerar Lancamento agora')
                               .off('click').on('click', function() { abrirMotor(modo); });
                $('#avisoDesatualizado').show();
            }
        });
    }

    // ------------------------------------------------ fechamento de periodo
    var _dataFechamento = null;

    function verificarFechamento() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarLancamentos', 'm': 'situacaoFechamento', '_submit_check': '1'},
            function(data) {
                _dataFechamento = data.dataFechamento || null;
                if (_dataFechamento) {
                    var d = _dataFechamento.substr(8,2) + '/' + _dataFechamento.substr(5,2) + '/' + _dataFechamento.substr(0,4);
                    $('#btnFechamentoLabel').text('Fechado até ' + d);
                } else {
                    $('#btnFechamentoLabel').text('Fechar período');
                }
            }, 'json');
    }

    function abrirFechamento() {
        var msg = 'Data de fechamento (lançamentos ATÉ essa data ficam travados).\n'
                + 'Deixe em branco e confirme para REABRIR o período.';
        var atual = _dataFechamento || '';
        var d = prompt(msg, atual);
        if (d === null) { return; } // cancelou
        d = d.trim();
        // aceita dd/mm/aaaa ou aaaa-mm-dd
        if (d && d.indexOf('/') > 0) {
            var p = d.split('/');
            if (p.length === 3) { d = p[2] + '-' + p[1].padStart(2,'0') + '-' + p[0].padStart(2,'0'); }
        }
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarLancamentos', 'm': 'definirFechamento', 'dataFechamento': d, '_submit_check': '1'},
            function(data) {
                processJson(data);
                verificarFechamento();
                carregarTabelaLancamentos();
            }, 'json');
    }

    // ---------------------------------------------- geracao de lancamentos automatica
    function fmtMoeda(v) {
        return 'R$ ' + (parseFloat(v) || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    var _modoMotor = 'incremental';

    // Refazer e destrutivo: nao fica junto das acoes do dia a dia. Chega-se
    // nele por aqui, e ainda assim so executa apos marcar o aceite.
    function irParaRefazer() {
        $('#modalMotor').modal('hide');
        setTimeout(function() { abrirMotor('completo'); }, 400);
    }

    function abrirMotor(modo) {
        _modoMotor = modo || 'incremental';
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarMotorFinanceiro', 'm': 'previaLancamentos', '_submit_check': '1', 'modo': _modoMotor},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                if (!data.statusMessage) { processJson(data); return; }
                var r = data.resumo;
                var html = ''
                    + (_modoMotor === 'completo'
                        ? '<p style="margin-bottom:12px;">Todos os lan&ccedil;amentos autom&aacute;ticos ser&atilde;o <strong>apagados e refeitos</strong> com as regras atuais:</p>'
                        : '<p style="margin-bottom:12px;">Ser&atilde;o lan&ccedil;ados apenas os pagamentos <strong>ainda n&atilde;o lancados</strong> &mdash; o que j&aacute; est&aacute; gravado n&atilde;o &eacute; tocado:</p>')
                    + '<table class="tbl-fin" style="font-size:13px;"><tbody>'
                    + '<tr><td>Pagamentos a gerar lancamento</td><td style="text-align:right;font-weight:600;">' + r.pagamentos + '</td></tr>'
                    + '<tr><td>Receita bruta<br><span style="color:#999;font-size:11px;">' + (r.conta_receita || '') + '</span></td><td style="text-align:right;font-weight:600;">' + fmtMoeda(r.receita) + '</td></tr>'
                    + '<tr><td>Taxa do meio de pagamento<br><span style="color:#999;font-size:11px;">' + (r.conta_taxa || '') + '</span></td><td style="text-align:right;font-weight:600;">' + fmtMoeda(r.taxa) + '</td></tr>'
                    + (r.institucional > 0
                        ? '<tr><td>Taxas institucionais<br><span style="color:#999;font-size:11px;">HUTEC / dire&ccedil;&atilde;o, conforme cadastrado em Rateios</span></td><td style="text-align:right;font-weight:600;">' + fmtMoeda(r.institucional) + '</td></tr>'
                        : '')
                    + '<tr><td>Lan&ccedil;amentos que ser&atilde;o gerados</td><td style="text-align:right;font-weight:600;">' + r.lancamentos + '</td></tr>'
                    + '</tbody></table>';
                if (r.confirmados_pelo_retorno > 0) {
                    html += '<p style="margin-top:12px;color:#777;font-size:12px;"><i class="fa fa-info-circle"></i> '
                          + r.confirmados_pelo_retorno + ' pagamento(s) cancelado(s)/devolvido(s) entram como receita porque o retorno HUTEC confirma que o dinheiro entrou.</p>';
                }
                if (r.ignorados > 0) {
                    html += '<p style="margin-top:6px;color:#777;font-size:12px;"><i class="fa fa-info-circle"></i> '
                          + r.ignorados + ' pagamento(s) cancelado(s)/devolvido(s) ficam de fora &mdash; sem confirma&ccedil;&atilde;o de entrada no retorno.</p>';
                }
                if (r.sem_regra_taxa > 0) {
                    html += '<p style="margin-top:6px;color:#b9770e;font-size:12px;"><i class="fa fa-exclamation-triangle"></i> '
                          + r.sem_regra_taxa + ' pagamento(s) sem regra de taxa cadastrada em Rateios &mdash; ser&atilde;o lan&ccedil;ados sem dedu&ccedil;&atilde;o.</p>';
                }
                html += '<p style="margin-top:6px;color:#777;font-size:12px;">Devolu&ccedil;&otilde;es n&atilde;o s&atilde;o geradas automaticamente (o valor devolvido costuma ser diferente do pago) &mdash; lance manualmente na rubrica de devolu&ccedil;&atilde;o do evento.</p>';
                html += _modoMotor === 'completo'
                    ? '<p style="margin-top:12px;color:#777;font-size:12px;">Os lan&ccedil;amentos autom&aacute;ticos anteriores (n&atilde;o conciliados) ser&atilde;o substitu&iacute;dos. Lan&ccedil;amentos manuais e conciliados n&atilde;o s&atilde;o afetados.</p>'
                    : '<p style="margin-top:12px;color:#777;font-size:12px;">Nada do que j&aacute; foi lan&ccedil;ado ser&aacute; apagado. '
                      + '<a href="javascript:void(0)" onclick="irParaRefazer()" style="color:#a33;">Alterei uma regra e preciso refazer tudo</a></p>';
                if (_modoMotor === 'completo') {
                    html += '<div style="margin-top:14px; border:1px solid #f0c8c8; background:#fdf4f4; border-radius:8px; padding:12px 14px;">'
                          + '<div style="color:#a33; font-weight:600; font-size:13px; margin-bottom:6px;">'
                          + '<i class="fa fa-exclamation-triangle"></i> Isto apaga os lan&ccedil;amentos autom&aacute;ticos existentes e os cria de novo.</div>'
                          + '<div style="color:#7a5a5a; font-size:12px; margin-bottom:8px;">Os n&uacute;meros dos lan&ccedil;amentos mudam. S&oacute; fa&ccedil;a isso depois de alterar uma regra de rateio, quando o percentual novo precisa valer para os lan&ccedil;amentos antigos.</div>'
                          + '<label style="font-weight:400; font-size:13px; color:#a33;"><input type="checkbox" id="confirmaRefazer"> Entendi, pode apagar e refazer</label>'
                          + '</div>';
                }
                $('#resumoMotor').html(html);
                if (_modoMotor === 'completo') {
                    $('#btnConfirmarMotor').prop('disabled', true).text('Apagar e refazer');
                    $('#confirmaRefazer').change(function() {
                        $('#btnConfirmarMotor').prop('disabled', !$(this).is(':checked'));
                    });
                } else {
                    $('#btnConfirmarMotor').prop('disabled', false).text('Gerar lançamentos');
                }
                $('#modalMotor').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarMotor() {
        if (_modoMotor === 'completo' && !$('#confirmaRefazer').is(':checked')) {
            return;
        }
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarMotorFinanceiro', 'm': 'gerarLancamentosPagamentos', '_submit_check': '1', 'modo': _modoMotor},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                $('#modalMotor').modal('hide');
                if (data.statusMessage) {
                    carregarTabelaLancamentos();
                    $('#avisoDesatualizado').hide();
                    verificarSituacao();
        verificarFechamento();
                }
            },
            complete: function() { stopLoading(); }
        });
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

<!-- Avisa quando o balancete ficou para tras: pagamento novo ou regra alterada
     nao entram sozinhos nos lancamentos. -->
<div id="avisoDesatualizado" style="display:none; background:#fdf6e3; border:1px solid #f0e0b0; color:#8a6d3b; border-radius:10px; padding:12px 16px; margin-top:10px; font-size:13px;">
    <i class="fa fa-exclamation-triangle" style="margin-right:6px;"></i>
    <span id="avisoTexto"></span>
    <a href="javascript:void(0)" id="avisoAcao" style="margin-left:8px; font-weight:600;">Gerar Lancamento agora</a>
</div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Lançamentos</h4>
        <div>
            <button type="button" class="btn btn-sm" style="margin-right:6px;" onclick="abrirMotor('incremental')" title="Lança apenas os pagamentos ainda não lancados">
                <i class="fa fa-refresh"></i> Motor Financeiro
            </button>
            <button type="button" class="btn btn-sm" style="margin-right:6px;" onclick="abrirFechamento()" title="Trava lançamentos até uma data (período já prestado)">
                <i class="fa fa-lock"></i> <span id="btnFechamentoLabel">Fechar período</span>
            </button>
            <button type="button" class="btn btn-sm" style="margin-right:6px;" onclick="abrirDevolucao()" title="Lança uma devolução de inscrição já com o tipo e a rubrica preenchidos">
                <i class="fa fa-rotate-left"></i> Lançar devolução
            </button>
            <button type="button" class="btn btn-sm abriModal" onclick="abrirModalNovo()">
                <i class="fa fa-plus"></i> Novo Lançamento
            </button>
        </div>
    </div>
    <!-- Recorte vindo de link (ex.: "ver lançamentos" da aba Duplicidades) -->
    <div id="avisoFiltroLink" style="display:none; background:#eef5fd; border-bottom:1px solid #d6e4f5; color:#2c5d8f; padding:10px 20px; font-size:12px;">
        <i class="fa fa-filter" style="margin-right:6px;"></i>
        <span id="avisoFiltroLinkTexto"></span>
        <a href="javascript:void(0)" onclick="sairFiltroLink()" style="margin-left:10px; font-weight:600;">ver todos</a>
    </div>

    <!-- Filtros: a lista traz todos os lancamentos do evento (centenas depois de
         gerar lancamento), entao sem filtro nao ha como trabalhar. -->
    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; padding:12px 20px; border-bottom:1px solid #eee; background:#fafafa;">
        <div>
            <label for="fDataInicio" style="display:block; margin:0 0 2px; font-size:11px; color:#666; font-weight:600;">COMPETÊNCIA DE</label>
            <input type="date" id="fDataInicio" class="form-control" style="width:150px;">
        </div>
        <div>
            <label for="fDataFim" style="display:block; margin:0 0 2px; font-size:11px; color:#666; font-weight:600;">ATÉ</label>
            <input type="date" id="fDataFim" class="form-control" style="width:150px;">
        </div>
        <div>
            <label for="fPlanoContas" style="display:block; margin:0 0 2px; font-size:11px; color:#666; font-weight:600;">CONTA</label>
            <select id="fPlanoContas" class="form-control" style="width:230px;">
                <option value="">Todas</option>
                <?php CarregarOptionsAC::planoContasFiltro($fws_) ?>
            </select>
        </div>
        <div>
            <label for="fTipo" style="display:block; margin:0 0 2px; font-size:11px; color:#666; font-weight:600;">TIPO</label>
            <select id="fTipo" class="form-control" style="width:170px;">
                <option value="">Todos</option>
                <option value="VENDA">Venda</option>
                <option value="DESPESA_PAGA">Despesa Paga</option>
                <option value="PAGAMENTO_FORNECEDOR">Pagamento de Fornecedor</option>
            </select>
        </div>
        <div>
            <label for="fOrigem" style="display:block; margin:0 0 2px; font-size:11px; color:#666; font-weight:600;">ORIGEM</label>
            <select id="fOrigem" class="form-control" style="width:180px;">
                <option value="">Todas</option>
                <option value="MANUAL">Manual</option>
                <option value="PAGAMENTO_AUTOMATICO">Pagamento automático</option>
                <option value="FINANCEIRO">Financeiro</option>
                <option value="INTEGRACAO">Integração</option>
            </select>
        </div>
        <div>
            <label for="fConciliado" style="display:block; margin:0 0 2px; font-size:11px; color:#666; font-weight:600;">CONCILIADO</label>
            <select id="fConciliado" class="form-control" style="width:110px;">
                <option value="">Todos</option>
                <option value="SIM">SIM</option>
                <option value="NAO">NÃO</option>
            </select>
        </div>
        <div style="flex:1; min-width:200px;">
            <label for="fBusca" style="display:block; margin:0 0 2px; font-size:11px; color:#666; font-weight:600;">BUSCA (histórico, favorecido, documento — ou cód. do inscrito, pagamento ou pedido)</label>
            <input type="text" id="fBusca" class="form-control" placeholder="Digite e tecle Enter">
        </div>
        <div>
            <button type="button" class="btn btn-sm" onclick="carregarTabelaLancamentos()" title="Aplica os filtros">
                <i class="fa fa-search"></i> Filtrar
            </button>
            <button type="button" class="btn btn-sm" onclick="limparFiltros()" title="Limpa os filtros e mostra tudo">
                <i class="fa fa-eraser"></i> Limpar
            </button>
        </div>
    </div>
    <div style="padding:0; overflow-x:auto;">
        <table class="tbl-fin" id="itens">
            <thead>
                <tr>
                    <th style="width:80px;">Ações</th>
                    <th data-col="1" class="col-data">Data <span class="sort-arrow">↑↓</span></th>
                    <th data-col="2">Conta <span class="sort-arrow">↑↓</span></th>
                    <th data-col="3" class="col-badge">Tipo <span class="sort-arrow">↑↓</span></th>
                    <th data-col="4" class="col-num">Valor <span class="sort-arrow">↑↓</span></th>
                    <th data-col="5">Favorecido <span class="sort-arrow">↑↓</span></th>
                    <th data-col="6" class="col-codigo">Documento <span class="sort-arrow">↑↓</span></th>
                    <th data-col="7" class="col-badge">Origem <span class="sort-arrow">↑↓</span></th>
                    <th data-col="8">Histórico <span class="sort-arrow">↑↓</span></th>
                    <th data-col="9" class="col-badge">Conciliado <span class="sort-arrow">↑↓</span></th>
                </tr>
            </thead>
            <tbody class="inputs">
                <?php
                $objSRV->carregarTabelaLancamentos($fws_, $debug);
                ?>
            </tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:150px;"></div>

<!-- Modal Cadastro/Edição -->
<div class="modal fade" id="modalLancamento" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalLancamentoLabel"><i class="fa fa-pencil-square-o"></i>Novo Lançamento</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalLancamentoMsg"></div>
                <form id="formLancamentos" name="formLancamentos" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="dataCompetencia">Data de Competência:</label>
                                <input type="date" id="dataCompetencia" name="dataCompetencia" value="" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="dataFinanceira">Data Financeira:</label>
                                <input type="date" id="dataFinanceira" name="dataFinanceira" value="" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="tipoLancamento">Tipo:</label>
                                <select id="tipoLancamento" name="tipoLancamento" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="PAGAMENTO_FORNECEDOR">Pagamento de Fornecedor</option>
                                    <option value="VENDA">Venda</option>
                                    <option value="DESPESA_PAGA">Despesa Paga</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="valor">Valor:</label>
                                <input type="text" id="valor" name="valor" value="" class="form-control" placeholder="Valor" required>
                            </div>
                        </div>
                    </div>
                    <div id="campoDebito" style="display: none;">
                        <div class="form-group">
                            <label for="debito">Débito:</label>
                            <select id="debito" name="debito" class="form-control">
                                <option value="">Selecione...</option>
                                <?php CarregarOptionsAC::planoContasDebito($fws_) ?>
                            </select>
                        </div>
                    </div>
                    <div id="campoCredito" style="display: none;">
                        <div class="form-group">
                            <label for="credito">Crédito:</label>
                            <select id="credito" name="credito" class="form-control">
                                <option value="">Selecione...</option>
                                <?php CarregarOptionsAC::planoContasCredito($fws_) ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="codCentroCusto">Centro de Custo:</label>
                                <select id="codCentroCusto" name="codCentroCusto" class="form-control">
                                    <option value="">Selecione...</option>
                                    <?php CarregarOptionsAC::centroCusto($fws_) ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="codFontesRecurso">Fonte de Recurso:</label>
                                <select id="codFontesRecurso" name="codFontesRecurso" class="form-control">
                                    <option value="">Selecione...</option>
                                    <?php CarregarOptionsAC::fontesRecurso($fws_) ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Favorecido: quem recebeu (despesa) ou quem pagou (receita).
                         Sai do cadastro de inscritos, onde o ministrante ja esta com
                         seus dados bancarios; quem nao tem cadastro vai no texto. -->
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="codInscrito">Favorecido (cadastrado):</label>
                                <select id="codInscrito" name="codInscrito" class="form-control">
                                    <option value="">Selecione ou deixe em branco...</option>
                                    <?php CarregarOptionsAC::inscritoFavorecido($fws_) ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="favorecido">Favorecido (sem cadastro):</label>
                                <input type="text" id="favorecido" name="favorecido" value="" class="form-control"
                                       placeholder="Nome de quem recebeu, se n&atilde;o estiver na lista">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="numDocumento">N&ordm; do Documento:</label>
                                <input type="text" id="numDocumento" name="numDocumento" value="" class="form-control"
                                       placeholder="Nota fiscal ou recibo">
                            </div>
                            <!-- Comprovante: nota fiscal / RPA / recibo. Vai para a
                                 pasta padrao do evento (logn/eventos/<COD>/rpa/). -->
                            <div class="form-group">
                                <label for="comprovante">Comprovante (arquivo):</label>
                                <input type="file" id="comprovante" name="comprovante" class="form-control"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div id="comprovanteAtual" style="font-size:12px; margin-top:4px;"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="origemLancamento">Origem do Lançamento:</label>
                                <select id="origemLancamento" name="origemLancamento" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="MANUAL">Manual</option>
                                    <option value="FINANCEIRO">Financeiro</option>
                                    <option value="INTEGRACAO">Integração</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="conciliado">Conciliado:</label>
                                <select id="conciliado" name="conciliado" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="1">SIM</option>
                                    <option value="0">NÃO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="historico">Histórico:</label>
                        <textarea id="historico" name="historico" class="form-control" placeholder="Descrição" required></textarea>
                    </div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarLancamentos" name="c">
                    <input type="hidden" value="salvarLancamentos" name="m">
                    <input type="hidden" name="codLancamentos" id="codLancamentos" value=""/>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <!-- so aparece ao editar: grava OUTRO lancamento com estes dados,
                     preservando o original (o comprovante nao vai junto) -->
                <button type="button" class="btn btn-default" id="btnSalvarComoNovo" style="display:none;"
                        onclick="salvarComoNovo()"
                        title="Cria um novo lançamento com estes mesmos dados, sem alterar o atual. O comprovante anexado não é copiado.">
                    <i class="fa fa-files-o" style="margin-right:6px;"></i>Salvar como novo
                </button>
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
                <p>Deseja realmente excluir este Lançamento?</p>
                <p><strong>Esta ação é irreversível.</strong></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Motor Financeiro -->
<div class="modal fade" id="modalMotor" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-refresh" style="margin-right:8px; opacity:0.8;"></i>Motor Financeiro</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="resumoMotor"></div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarMotor" onclick="confirmarMotor()">Gerar lançamentos</button>
            </div>
        </div>
    </div>
</div>
