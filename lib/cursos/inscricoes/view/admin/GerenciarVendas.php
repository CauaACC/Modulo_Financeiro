<?php
// Gate da propria view: nao depender so do controller, senao a tela renderiza
// (e vaza estrutura/labels) para quem nao tem permissao.
try {
    FWA_exigirPermissao(array(enumSistema::SIS_CONTROLE_FINANCEIRO, enumSistema::SIS_GERENCIAR_ADM_GERAL));
} catch (Exception $e) {
    echo "<div class='alert alert-danger' style='margin:20px;'>"
       . "<b>Acesso nao permitido.</b> Esta tela e restrita a usuarios com permissao de Controle Financeiro."
       . "</div>";
    return;
}

// Modulo comercial so existe em evento classificado como Loja: sem isso a tela
// abre em qualquer projeto e sugere um fluxo que aquele evento nao tem.
try {
    $_evConfLoja = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
    if (!$_evConfLoja || $_evConfLoja->getTipoProjeto() != 'LOJA') {
        echo "<div class='alert alert-warning' style='margin:20px;'>"
           . "<b>Modulo nao habilitado neste projeto.</b> O modulo de Vendas atende projetos do tipo "
           . "<b>Loja (venda de mercadoria)</b>. Ajuste em Configuracao do evento &gt; aba Projeto &gt; Tipo de projeto."
           . "</div>";
        return;
    }
} catch (Exception $e) {
    echo "<div class='alert alert-warning' style='margin:20px;'>Modulo nao habilitado neste projeto.</div>";
    return;
}

$objSRV = new GerenciarVendaAC();
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<style>
    .tbl-fin { width:100%; border-collapse:collapse; font-size:13px; table-layout:auto; }
    .tbl-fin thead th { font-size:11px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.3px; padding:12px 10px; border-bottom:1px solid #e0e0e0; background:#f5f6f8; white-space:nowrap; cursor:pointer; }
    .tbl-fin tbody tr { border-bottom:1px solid #f0f0f0; }
    .tbl-fin tbody tr:hover { background:#f9fafb; }
    .tbl-fin tbody td { padding:6px 10px; vertical-align:middle; color:#333; white-space:normal; word-break:break-word; }
    .tbl-fin td.col-codigo, .tbl-fin th.col-codigo { white-space:nowrap; width:1%; }
    .tbl-fin td.col-num, .tbl-fin th.col-num { white-space:nowrap; width:1%; }
    .tbl-fin td.col-badge, .tbl-fin th.col-badge { white-space:nowrap; width:1%; }
    .tbl-fin tbody td.col-nome { font-weight:600; }
    .tbl-fin .badge-tipo { display:inline-flex; align-items:center; font-size:12px; font-weight:500; padding:3px 10px; border-radius:12px; background:#f0f1f3; }
    .tbl-fin .badge-tipo .dot { width:8px; height:8px; border-radius:50%; margin-right:6px; display:inline-block; }
    .tbl-fin .badge-tipo .dot-green { background:#27ae60; }
    .tbl-fin .badge-tipo .dot-blue { background:#95a5a6; }
    .tbl-fin .badge-tipo .dot-red { background:#c0392b; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; }
    .cx-nf { background:#fff; border:1px solid #e5e5e5; border-radius:8px; padding:18px; margin-bottom:26px; }
    .cx-nf h3 { margin-top:0; font-size:18px; }
    .ajuda-campo { font-size:11px; color:#888; display:block; margin-top:2px; }
    .sec-titulo { font-size:12px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.4px; margin:18px 0 8px; border-bottom:1px solid #eee; padding-bottom:4px; }
    .barra-filtros { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:14px; }
    .msg-erro { background:#fdecea; border:1px solid #f5c6cb; color:#8b1a12; padding:8px 12px; border-radius:6px; font-size:12px; margin-bottom:12px; }
    .msg-ok { background:#e8f5e9; border:1px solid #a5d6a7; color:#1b5e20; padding:8px 12px; border-radius:6px; font-size:12px; margin-bottom:12px; }
    .msg-aviso { background:#fff8e1; border:1px solid #ffe082; color:#7a5c00; padding:8px 12px; border-radius:6px; font-size:12px; margin-bottom:12px; }
    .msg-aviso ul { margin:6px 0 0 18px; padding:0; }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        carregarTabelaVendas();
        $('#btnBuscar').click(function() { carregarTabelaVendas(); });
        $('#busca').keypress(function(e) { if (e.which == 13) { carregarTabelaVendas(); } });
        $('#semNota').change(function() { carregarTabelaVendas(); });
        $('#btnImportar').click(function() { importarPlanilha(); });
        $('#btnEmitirNfe').click(function() { emitirNfe(); });

        $('#itens thead').on('click', 'th', function() {
            ordenarTabela($('#itens'), $(this).index());
        });
    });

    var pedidoEmFaturamento = null;

    function bindEventosTabela() {
        $('.ver').off('click').on('click', function(e) {
            e.preventDefault();
            abrirItens($(this).parents("tr").attr("id"));
        });
        $('.nfe').off('click').on('click', function(e) {
            e.preventDefault();
            abrirPreviaNfe($(this).parents("tr").attr("id"));
        });
    }

    function txt(valor) {
        if (valor === null || valor === undefined) { return ''; }
        return $('<div>').text(valor).html();
    }

    /**
     * Previa: mostra exatamente o que sairia na nota, sem reservar numero nem
     * emitir. E a unica porta para o botao de emitir.
     */
    function abrirPreviaNfe(pedidoId) {
        if (!pedidoId) { return; }
        pedidoEmFaturamento = pedidoId;

        $('#previaCorpo').html('<div class="msg-aviso">Montando a prévia da nota...</div>');
        $('#btnEmitirNfe').prop('disabled', true);
        $('#modalPrevia').modal('show');

        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'pedidoId': pedidoId, 'c': 'GerenciarVenda', 'm': 'previaNfe', '_submit_check': '1'},
            function(data) {
                if (!data.statusMessage) {
                    $('#previaCorpo').html('<div class="msg-erro">' + data.message + '</div>');
                    return;
                }
                $('#previaCorpo').html(montarHtmlPrevia(data));
                $('#btnEmitirNfe').prop('disabled', false);

                $('#btnVerJson').off('click').on('click', function() {
                    $('#previaJson').toggle();
                });
            },
            'json'
        ).fail(function() {
            $('#previaCorpo').html('<div class="msg-erro">Não foi possível montar a prévia.</div>');
        });
    }

    function montarHtmlPrevia(d) {
        var h = '';

        if (d.ambiente !== 'producao') {
            h += '<div class="msg-aviso"><b>Ambiente de HOMOLOGAÇÃO.</b> '
               + 'Nota emitida aqui não tem valor fiscal.</div>';
        }

        if (d.avisos && d.avisos.length) {
            h += '<div class="msg-aviso"><b>Confira antes de emitir:</b><ul>';
            for (var i = 0; i < d.avisos.length; i++) { h += '<li>' + txt(d.avisos[i]) + '</li>'; }
            h += '</ul></div>';
        }

        h += '<div class="sec-titulo">Emitente</div>';
        h += '<div style="font-size:13px;">' + txt(d.emissor.razaoSocial)
           + ' &middot; CNPJ ' + txt(d.emissor.cnpj)
           + ' &middot; IE ' + txt(d.emissor.ie)
           + '<br><span style="color:#888;">Série ' + txt(d.emissor.serie)
           + ' &middot; número previsto <b>' + txt(d.emissor.proximoNumero) + '</b>'
           + ' &middot; CRT ' + txt(d.emissor.crt) + '</span></div>';

        h += '<div class="sec-titulo">Destinatário</div>';
        h += '<div style="font-size:13px;">' + txt(d.destinatario.nome)
           + ' &middot; ' + txt(d.destinatario.documento)
           + '<br><span style="color:#888;">' + txt(d.destinatario.endereco)
           + ' &middot; ' + txt(d.destinatario.cidade) + '/' + txt(d.destinatario.uf)
           + ' &middot; CEP ' + txt(d.destinatario.cep) + '</span></div>';

        h += '<div class="sec-titulo">Operação</div>';
        h += '<div style="font-size:13px;">' + txt(d.operacao.natureza)
           + '<br><span style="color:#888;">CFOP ' + txt(d.operacao.cfop) + '</span></div>';

        h += '<div class="sec-titulo">Itens</div>';
        h += '<table class="tbl-fin"><thead><tr>'
           + '<th class="col-codigo">#</th><th class="col-codigo">Código</th><th>Descrição</th>'
           + '<th class="col-codigo">NCM</th><th class="col-codigo">CFOP</th>'
           + '<th class="col-codigo">Orig.</th><th class="col-codigo">CSOSN</th>'
           + '<th class="col-codigo">Un.</th><th class="col-num">Qtd.</th><th class="col-num">Valor</th>'
           + '</tr></thead><tbody>';
        for (var j = 0; j < d.itens.length; j++) {
            var it = d.itens[j];
            h += '<tr><td class="col-codigo">' + txt(it.numero) + '</td>'
               + '<td class="col-codigo">' + txt(it.codigo) + '</td>'
               + '<td>' + txt(it.descricao) + '</td>'
               + '<td class="col-codigo">' + txt(it.ncm) + '</td>'
               + '<td class="col-codigo">' + txt(it.cfop) + '</td>'
               + '<td class="col-codigo">' + txt(it.origem) + '</td>'
               + '<td class="col-codigo">' + txt(it.csosn) + '</td>'
               + '<td class="col-codigo">' + txt(it.unidade) + '</td>'
               + '<td class="col-num" style="text-align:right;">' + txt(it.quantidade) + '</td>'
               + '<td class="col-num" style="text-align:right;">R$ ' + txt(it.valor) + '</td></tr>';
        }
        h += '</tbody></table>';

        h += '<div style="text-align:right; font-size:15px; font-weight:600; margin-top:10px;">'
           + 'Total da nota: R$ ' + txt(d.operacao.total) + '</div>';

        // O JSON fica escondido mas disponivel: quando a SEFAZ rejeita, e a
        // primeira coisa que se quer ver, e sem isso vira adivinhacao.
        h += '<div style="margin-top:14px;">'
           + '<button type="button" class="btn btn-default btn-sm" id="btnVerJson">'
           + 'Ver JSON que será enviado</button></div>'
           + '<pre id="previaJson" style="display:none; margin-top:10px; max-height:300px; '
           + 'overflow:auto; font-size:11px;">'
           + txt(JSON.stringify(d.payload, null, 2)) + '</pre>';

        return h;
    }

    function emitirNfe() {
        if (!pedidoEmFaturamento) { return; }
        if (!confirm('Emitir a NF-e desta venda?\n\nA numeração é consumida mesmo se a SEFAZ rejeitar, '
                   + 'e desfazer uma nota autorizada exige cancelamento com justificativa.')) {
            return;
        }

        $('#btnEmitirNfe').prop('disabled', true);
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {
                'pedidoId': pedidoEmFaturamento, 'confirmar': '1',
                'c': 'GerenciarVenda', 'm': 'emitirNfe', '_submit_check': '1'
            },
            function(data) {
                var classe = data.statusMessage ? 'msg-ok' : 'msg-erro';
                $('#previaCorpo').prepend('<div class="' + classe + '">' + txt(data.message) + '</div>');
                if (data.statusMessage) {
                    carregarTabelaVendas();
                } else {
                    $('#btnEmitirNfe').prop('disabled', false);
                }
            },
            'json'
        ).fail(function() {
            $('#previaCorpo').prepend('<div class="msg-erro">Falha ao chamar a emissão.</div>');
            $('#btnEmitirNfe').prop('disabled', false);
        });
    }

    function valorOrdenavel(texto) {
        var t = $.trim(texto).replace('R$', '').trim();
        if (/^-?[\d.]+,\d+$/.test(t) || /^-?\d+$/.test(t)) {
            var n = parseFloat(t.replace(/\./g, '').replace(',', '.'));
            if (!isNaN(n)) { return n; }
        }
        return t.toLowerCase();
    }

    function ordenarTabela($tabela, coluna) {
        var $tbody = $tabela.find('tbody.inputs');
        var asc = $tabela.data('ordem-col') === coluna ? !$tabela.data('ordem-asc') : true;
        $tabela.data('ordem-col', coluna).data('ordem-asc', asc);

        var linhas = $tbody.find('tr').get();
        if (linhas.length < 2) { return; }
        linhas.sort(function(a, b) {
            var va = valorOrdenavel($(a).children('td').eq(coluna).text());
            var vb = valorOrdenavel($(b).children('td').eq(coluna).text());
            if (va < vb) { return asc ? -1 : 1; }
            if (va > vb) { return asc ? 1 : -1; }
            return 0;
        });
        $.each(linhas, function(i, linha) { $tbody.append(linha); });
    }

    function carregarTabelaVendas() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {
                'c': 'GerenciarVenda', 'm': 'carregarTabelaVendas',
                'busca': $('#busca').val(),
                'semNota': $('#semNota').is(':checked') ? 1 : '',
                '_submit_check': '1'
            },
            function(html) {
                $('#itens').find('tbody.inputs').remove();
                $('#itens').append('<tbody class="inputs">' + html + '</tbody>');
                bindEventosTabela();
            }
        );
    }

    function abrirItens(pedidoId) {
        if (!pedidoId) return;
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'pedidoId': pedidoId, 'c': 'GerenciarVenda', 'm': 'carregarItensVenda', '_submit_check': '1'},
            function(html) {
                $('#itensVenda').find('tbody.inputs').remove();
                $('#itensVenda').append('<tbody class="inputs">' + html + '</tbody>');
                $('#modalItens').modal('show');
            }
        );
    }

    function importarPlanilha() {
        if (!$('#arquivo').val()) {
            $('#modalCargaMsg').html('<div class="msg-erro">Selecione a planilha antes de carregar.</div>');
            return;
        }
        $('#formCarga').ajaxSubmit({
            dataType: 'json', cache: false,
            beforeSend: function() {
                startLoading();
                $('#modalCargaMsg').html('<div class="msg-aviso">Lendo a planilha...</div>');
            },
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                // Esta tela nao tem os div#erro/#sucesso do padrao antigo: a
                // mensagem tem que ser renderizada aqui, senao a carga termina
                // sem o usuario saber o que aconteceu.
                var html = '<div class="' + (data.statusMessage ? 'msg-ok' : 'msg-erro') + '">'
                         + data.message + '</div>';

                if (data.erros && data.erros.length) {
                    html += '<div class="msg-erro"><b>Vendas que nao entraram:</b><ul>';
                    for (var i = 0; i < data.erros.length; i++) { html += '<li>' + data.erros[i] + '</li>'; }
                    html += '</ul></div>';
                }
                if (data.avisos && data.avisos.length) {
                    html += '<div class="msg-aviso"><b>Conferir antes de emitir:</b><ul>';
                    for (var j = 0; j < data.avisos.length; j++) { html += '<li>' + data.avisos[j] + '</li>'; }
                    html += '</ul></div>';
                }
                $('#modalCargaMsg').html(html);

                if (data.statusMessage) { carregarTabelaVendas(); }
            },
            complete: function() { stopLoading(); }
        });
    }
</script>

<?php
// Cabecalho e menus da area administrativa: sem isto a tela abre "solta",
// fora do modulo, sem submenu. Cada view do admin inclui os seus.
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_vendas.php");
?>

<div class="cx-nf">
    <h3>Vendas</h3>
    <p style="color:#666; font-size:13px;">
        Vendas do canal, prontas para virar nota fiscal. A coluna <b>Situação</b> mostra o que
        ainda impede a emissão — produto sem dado fiscal, cliente sem CPF, endereço incompleto.
    </p>

    <div class="barra-filtros">
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCarga">
            <i class="fa fa-upload"></i> Carregar planilha
        </button>
        <input type="text" id="busca" class="form-control input-sm" style="width:280px;"
               placeholder="Buscar por pedido, cliente ou CPF">
        <button type="button" class="btn btn-default btn-sm" id="btnBuscar">
            <i class="fa fa-search"></i> Buscar
        </button>
        <label style="font-weight:normal; margin:0;">
            <input type="checkbox" id="semNota"> só sem nota
        </label>
    </div>

    <table class="tbl-fin" id="itens">
        <thead>
            <tr>
                <th class="col-codigo">Ações</th>
                <th class="col-codigo">Pedido</th>
                <th class="col-codigo">Data</th>
                <th>Cliente</th>
                <th class="col-num">Valor</th>
                <th class="col-codigo">Itens</th>
                <th class="col-badge">Nota</th>
                <th>Situação</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal de carga -->
<div class="modal fade" id="modalCarga" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formCarga" name="formCarga" method="post"
                  action="<?php echo WB_URL_COMMAND ?>" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Carregar planilha de pedidos</h4>
                </div>
                <div class="modal-body">
                    <div id="modalCargaMsg"></div>

                    <p style="font-size:13px; color:#666;">
                        Envie o relatório de <b>Pedidos</b> exportado da Shopee (.xlsx, .xls ou .csv).
                    </p>

                    <input type="file" name="arquivo" id="arquivo" class="form-control"
                           accept=".xlsx,.xls,.csv,.txt">

                    <div class="msg-aviso" style="margin-top:12px;">
                        <b>Como a carga se comporta:</b>
                        <ul>
                            <li>Reenviar a mesma planilha <b>não duplica</b> venda — o pedido é reconhecido pelo ID da Shopee.</li>
                            <li>Cliente é encontrado pelo <b>CPF</b>; quem já comprou não é recadastrado.</li>
                            <li>Produto novo é criado <b>incompleto</b> (sem NCM) e não emite nota até você completar.</li>
                            <li>Frete e taxas da Shopee <b>não entram na nota</b> — ficam guardados à parte.</li>
                        </ul>
                    </div>

                    <?php // _submit_check por ultimo: PadraoVO::setAll() da break nele. ?>
                    <input type="hidden" name="_submit_check" value="1">
                    <input type="hidden" name="c" value="GerenciarVenda">
                    <input type="hidden" name="m" value="importarPlanilha">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btnImportar">Carregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de previa da NF-e: unica porta para a emissao -->
<div class="modal fade" id="modalPrevia" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Prévia da NF-e de venda</h4>
            </div>
            <div class="modal-body">
                <div id="previaCorpo"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btnEmitirNfe" disabled>Emitir NF-e</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de itens -->
<div class="modal fade" id="modalItens" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Itens da venda</h4>
            </div>
            <div class="modal-body">
                <table class="tbl-fin" id="itensVenda">
                    <thead>
                        <tr>
                            <th class="col-codigo">#</th>
                            <th class="col-codigo">SKU</th>
                            <th>Descrição</th>
                            <th class="col-num">Qtd.</th>
                            <th class="col-num">Total</th>
                            <th>Situação p/ nota</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
