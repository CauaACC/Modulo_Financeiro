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

$objSRV = new GerenciarProdutoAC();
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
    .aviso-incompleto { background:#fdecea; border:1px solid #f5c6cb; color:#8b1a12; padding:8px 12px; border-radius:6px; font-size:12px; margin-bottom:12px; }
    .msg-ok { background:#e8f5e9; border:1px solid #a5d6a7; color:#1b5e20; padding:8px 12px; border-radius:6px; font-size:12px; margin-bottom:12px; }
    .box-saldo { background:#f5f6f8; border-radius:6px; padding:10px 14px; font-size:13px; margin-bottom:12px; }
    .box-saldo b { font-size:18px; }
</style>
<script type="text/javascript">
    var _codAlternar = null;
    var _codEstoque  = null;

    $(document).ready(function() {
        carregarTabelaProdutos();
        carregarOptionsGestor('');
        $('#btnSalvarProduto').click(function() { salvarProduto(); });
        $('#btnBuscar').click(function() { carregarTabelaProdutos(); });
        $('#busca').keypress(function(e) { if (e.which == 13) { carregarTabelaProdutos(); } });
        $('#soIncompletos').change(function() { carregarTabelaProdutos(); });
        $('#btnConfirmarMovimento').click(function() { confirmarMovimento(); });

        // Motivo so e obrigatorio no ajuste — a tela avisa antes do servidor.
        $('#movTipo').change(function() {
            var ajuste = $(this).val() === 'AJUSTE';
            $('#avisoAjuste').toggle(ajuste);
            $('#movMotivoLabel').html(ajuste ? 'Motivo *' : 'Motivo');
        });

        // Ordenacao pelo cabecalho: handler delegado, mexe so no tbody.
        $('#itens thead').on('click', 'th', function() {
            ordenarTabela($('#itens'), $(this).index());
        });
    });

    function bindEventosTabela() {
        $('.editar').off('click').on('click', function(e) {
            e.preventDefault();
            editarProduto($(this).parents("tr").attr("id"));
        });
        $('.estoque').off('click').on('click', function(e) {
            e.preventDefault();
            abrirEstoque($(this).parents("tr").attr("id"));
        });
        $('.alternar').off('click').on('click', function(e) {
            e.preventDefault();
            _codAlternar = $(this).parents("tr").attr("id");
            $('#modalAlternar').modal('show');
        });
    }

    function valorOrdenavel(texto) {
        var t = $.trim(texto);
        // Numero pt-BR ("1.234,500") vira numero de verdade para ordenar.
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

    function carregarTabelaProdutos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {
                'c': 'GerenciarProduto', 'm': 'carregarTabelaProdutos',
                'busca': $('#busca').val(),
                'soIncompletos': $('#soIncompletos').is(':checked') ? 1 : '',
                '_submit_check': '1'
            },
            function(html) {
                $('#itens').find('tbody.inputs').remove();
                $('#itens').append('<tbody class="inputs">' + html + '</tbody>');
                bindEventosTabela();
            }
        );
    }

    function carregarOptionsGestor(codGestor) {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarProduto', 'm': 'carregarOptionsGestor', 'codGestor': codGestor, '_submit_check': '1'},
            function(html) { $('#codGestor').html(html); }
        );
    }

    function abrirModalNovo() {
        limparCampos();
        carregarOptionsGestor('');
        $('#avisoIncompleto').hide();
        $('#modalProdutoLabel').html('Novo produto');
        $('#modalProduto').modal('show');
    }

    // Esta tela NAO tem os div#erro / div#sucesso que o processJson do padrao
    // antigo escreve — conferido ao vivo. Se a mensagem nao for renderizada
    // aqui, o Salvar falha EM SILENCIO: o servidor recusa, o modal continua
    // aberto e o usuario acha que gravou.
    function mostrarMensagem(seletor, data, msgPadrao) {
        var texto = data && data.message ? data.message : msgPadrao;
        var classe = (data && data.statusMessage) ? 'msg-ok' : 'aviso-incompleto';
        $(seletor).html('<div class="' + classe + '">' + texto + '</div>');
    }

    function salvarProduto() {
        $('#formProduto').ajaxSubmit({
            dataType: 'json', async: false, cache: false,
            beforeSend: function() { startLoading(); },
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                if (data.statusMessage) {
                    carregarTabelaProdutos();
                    $('#modalProduto').modal('hide');
                    limparCampos();
                } else {
                    mostrarMensagem('#modalProdutoMsg', data, 'Nao foi possivel salvar o produto.');
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function editarProduto(codProduto) {
        if (!codProduto) return;
        $.ajax({
            type: "POST", url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codProduto': codProduto, 'c': 'GerenciarProduto', 'm': 'editarProduto', '_submit_check': '1'},
            dataType: "json", async: false, cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                $('#codProduto').val(data.codProduto);
                $('#sku').val(data.sku);
                $('#descricao').val(data.descricao);
                $('#ncm').val(data.ncm);
                $('#unidade').val(data.unidade);
                $('#origem').val(data.origem);
                $('#cest').val(data.cest);
                $('#csosn').val(data.csosn);
                $('#valorVenda').val(data.valorVenda);
                $('#estoqueMinimo').val(data.estoqueMinimo);
                carregarOptionsGestor(data.codGestor);
                $('#avisoIncompleto').toggle(data.flgIncompleto == 1);
                $('#modalProdutoLabel').html('Editar produto');
                $('#modalProduto').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarAlternar() {
        if (!_codAlternar) return;
        $.ajax({
            type: "POST", url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codProduto': _codAlternar, 'c': 'GerenciarProduto', 'm': 'alternarStatusProduto', '_submit_check': '1'},
            dataType: "json", async: false, cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                if (data.statusMessage) {
                    carregarTabelaProdutos();
                    $('#modalAlternar').modal('hide');
                    _codAlternar = null;
                } else {
                    // Mesma razao do salvarProduto: sem isto o erro some.
                    mostrarMensagem('#modalAlternarMsg', data, 'Nao foi possivel alterar o status.');
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    // ---------- estoque ----------

    function abrirEstoque(codProduto) {
        if (!codProduto) return;
        _codEstoque = codProduto;
        $('#movQuantidade').val('');
        $('#movDocumento').val('');
        $('#movMotivo').val('');
        $('#movTipo').val('ENTRADA').trigger('change');
        $('#modalEstoqueMsg').html('');

        var $linha = $('#itens tr#' + codProduto);
        $('#estoqueProdutoNome').html($linha.children('td').eq(1).text() + ' — ' + $linha.children('td').eq(2).text());
        $('#estoqueSaldoAtual').html($linha.children('td').eq(5).text());

        carregarExtrato();
        $('#modalEstoque').modal('show');
    }

    function carregarExtrato() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'codProduto': _codEstoque, 'c': 'GerenciarProduto', 'm': 'carregarExtratoEstoque', '_submit_check': '1'},
            function(html) {
                $('#extrato').find('tbody.inputs').remove();
                $('#extrato').append('<tbody class="inputs">' + html + '</tbody>');
            }
        );
    }

    function confirmarMovimento() {
        if (!_codEstoque) return;
        $.ajax({
            type: "POST", url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codProduto': _codEstoque,
                'tipo': $('#movTipo').val(),
                'quantidade': $('#movQuantidade').val(),
                'documento': $('#movDocumento').val(),
                'motivo': $('#movMotivo').val(),
                'c': 'GerenciarProduto', 'm': 'movimentarEstoque', '_submit_check': '1'
            },
            dataType: "json", async: false, cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                var cor = data.statusMessage ? '#1b5e20' : '#b71c1c';
                var html = '<div style="color:' + cor + '; font-weight:600;">' + data.message + '</div>';
                if (data.aviso) {
                    html += '<div class="aviso-incompleto" style="margin-top:8px;">' + data.aviso + '</div>';
                }
                $('#modalEstoqueMsg').html(html);
                if (data.statusMessage) {
                    $('#estoqueSaldoAtual').html(data.saldo);
                    $('#movQuantidade').val('');
                    $('#movMotivo').val('');
                    carregarExtrato();
                    carregarTabelaProdutos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function limparCampos() {
        $('#formProduto')[0].reset();
        $('#codProduto').val('');
        $('#getMessage').html('');
        $('#modalProdutoMsg').html('');
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
    <h3>Produtos</h3>
    <p style="color:#666; font-size:13px;">
        Catálogo de mercadorias do gestor, com estoque. O produto também <b>nasce sozinho</b> na
        primeira venda importada — entra marcado como <b>incompleto</b> e só falta você completar
        o dado fiscal. Produto incompleto não emite nota.
    </p>

    <div class="barra-filtros">
        <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalNovo();">
            <i class="fa fa-plus"></i> Novo produto
        </button>
        <input type="text" id="busca" class="form-control input-sm" style="width:260px;"
               placeholder="Buscar por SKU ou descrição">
        <button type="button" class="btn btn-default btn-sm" id="btnBuscar">
            <i class="fa fa-search"></i> Buscar
        </button>
        <label style="font-weight:normal; margin:0;">
            <input type="checkbox" id="soIncompletos"> só incompletos
        </label>
    </div>

    <table class="tbl-fin" id="itens">
        <thead>
            <tr>
                <th class="col-codigo">Ações</th>
                <th class="col-codigo">SKU</th>
                <th>Descrição</th>
                <th class="col-codigo">NCM</th>
                <th class="col-codigo">Unid.</th>
                <th class="col-num">Estoque</th>
                <th>Gestor</th>
                <th class="col-badge">Situação</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal de cadastro -->
<div class="modal fade" id="modalProduto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formProduto" name="formProduto" method="post"
                  action="<?php echo WB_URL_COMMAND ?>" enctype="multipart/form-data">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modalProdutoLabel">Novo produto</h4>
                </div>

                <div class="modal-body">
                    <div id="modalProdutoMsg"></div>

                    <div id="avisoIncompleto" class="aviso-incompleto" style="display:none;">
                        <b>Produto incompleto.</b> Falta dado fiscal (NCM, unidade ou origem) —
                        enquanto faltar, ele <b>não entra em nota fiscal</b>. O conteúdo desses
                        campos é decisão do contador.
                    </div>

                    <div class="sec-titulo">Identificação</div>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Gestor *</label>
                            <select name="codGestor" id="codGestor" class="form-control"></select>
                        </div>
                        <div class="col-md-3">
                            <label>SKU *</label>
                            <input type="text" name="sku" id="sku" class="form-control" maxlength="60">
                            <span class="ajuda-campo">Código da <b>variação</b> (110v e 220v são SKUs diferentes).</span>
                        </div>
                        <div class="col-md-5">
                            <label>Descrição *</label>
                            <input type="text" name="descricao" id="descricao" class="form-control" maxlength="255">
                            <span class="ajuda-campo">É o que sai na nota — inclua a variação. Ex: "HANDLE MASSAGER RS-H019 110v".</span>
                        </div>
                    </div>

                    <div class="sec-titulo">Dados fiscais</div>
                    <div class="row">
                        <div class="col-md-3">
                            <label>NCM</label>
                            <input type="text" name="ncm" id="ncm" class="form-control" maxlength="10">
                            <span class="ajuda-campo">8 dígitos. Sem NCM a nota não sai.</span>
                        </div>
                        <div class="col-md-2">
                            <label>Unidade</label>
                            <input type="text" name="unidade" id="unidade" class="form-control" maxlength="6">
                            <span class="ajuda-campo">Ex: UND.</span>
                        </div>
                        <div class="col-md-4">
                            <label>Origem da mercadoria</label>
                            <select name="origem" id="origem" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="0">0 - Nacional</option>
                                <option value="1">1 - Estrangeira, importação direta</option>
                                <option value="2">2 - Estrangeira, adquirida no mercado interno</option>
                                <option value="3">3 - Nacional, conteúdo de importação &gt; 40%</option>
                                <option value="4">4 - Nacional, produção conforme processos básicos</option>
                                <option value="5">5 - Nacional, conteúdo de importação &lt;= 40%</option>
                                <option value="6">6 - Estrangeira, importação direta, sem similar nacional</option>
                                <option value="7">7 - Estrangeira, mercado interno, sem similar nacional</option>
                                <option value="8">8 - Nacional, conteúdo de importação &gt; 70%</option>
                            </select>
                            <span class="ajuda-campo"><b>Varia por produto</b> — não é padrão do emissor.</span>
                        </div>
                        <div class="col-md-3">
                            <label>CEST</label>
                            <input type="text" name="cest" id="cest" class="form-control" maxlength="10">
                            <span class="ajuda-campo">Só quando houver substituição tributária.</span>
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-3">
                            <label>CSOSN (exceção)</label>
                            <input type="text" name="csosn" id="csosn" class="form-control" maxlength="4">
                            <span class="ajuda-campo">Em branco = usa o padrão do emissor.</span>
                        </div>
                        <div class="col-md-3">
                            <label>Valor de venda</label>
                            <input type="text" name="valorVenda" id="valorVenda" class="form-control" maxlength="15">
                            <span class="ajuda-campo">Referência. A nota usa o valor do <b>pedido</b>.</span>
                        </div>
                        <div class="col-md-3">
                            <label>Estoque mínimo</label>
                            <input type="text" name="estoqueMinimo" id="estoqueMinimo" class="form-control" maxlength="15">
                            <span class="ajuda-campo">Em branco = não acompanha.</span>
                        </div>
                        <div class="col-md-3" style="padding-top:22px;">
                            <span class="ajuda-campo">
                                O saldo de estoque não se digita aqui — muda só por movimento,
                                no botão <i class="fa fa-cubes"></i> da lista.
                            </span>
                        </div>
                    </div>

                    <?php // _submit_check precisa vir DEPOIS dos campos: PadraoVO::setAll() da break nele. ?>
                    <input type="hidden" name="_submit_check" value="1">
                    <input type="hidden" name="c" value="GerenciarProduto">
                    <input type="hidden" name="m" value="salvarProduto">
                    <input type="hidden" name="codProduto" id="codProduto">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarProduto">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de estoque -->
<div class="modal fade" id="modalEstoque" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Estoque — <span id="estoqueProdutoNome"></span></h4>
            </div>
            <div class="modal-body">
                <div id="modalEstoqueMsg"></div>

                <div class="box-saldo">
                    Saldo atual: <b id="estoqueSaldoAtual">0</b>
                </div>

                <div class="sec-titulo">Novo movimento</div>
                <div class="row">
                    <div class="col-md-3">
                        <label>Tipo *</label>
                        <select id="movTipo" class="form-control">
                            <option value="ENTRADA">Entrada (compra/importação)</option>
                            <option value="SAIDA">Saída</option>
                            <option value="DEVOLUCAO">Devolução</option>
                            <option value="AJUSTE">Ajuste de inventário</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Quantidade *</label>
                        <input type="text" id="movQuantidade" class="form-control" maxlength="15">
                    </div>
                    <div class="col-md-3">
                        <label>Documento</label>
                        <input type="text" id="movDocumento" class="form-control" maxlength="60">
                        <span class="ajuda-campo">Nota de compra, DI, romaneio.</span>
                    </div>
                    <div class="col-md-4">
                        <label id="movMotivoLabel">Motivo</label>
                        <input type="text" id="movMotivo" class="form-control" maxlength="255">
                    </div>
                </div>

                <div id="avisoAjuste" class="aviso-incompleto" style="display:none; margin-top:10px;">
                    No <b>ajuste</b> o sinal vem da quantidade: <b>-5</b> tira cinco, <b>5</b> acrescenta cinco.
                    O motivo é obrigatório — ajuste sem motivo é buraco no controle.
                </div>

                <div style="margin-top:12px;">
                    <button type="button" class="btn btn-primary btn-sm" id="btnConfirmarMovimento">
                        Registrar movimento
                    </button>
                    <span class="ajuda-campo" style="display:inline; margin-left:10px;">
                        Movimento não se apaga — corrige-se com outro movimento.
                    </span>
                </div>

                <div class="sec-titulo">Últimos movimentos</div>
                <table class="tbl-fin" id="extrato">
                    <thead>
                        <tr>
                            <th class="col-codigo">Quando</th>
                            <th class="col-codigo">Tipo</th>
                            <th class="col-num">Qtd.</th>
                            <th class="col-num">Saldo após</th>
                            <th class="col-codigo">Origem</th>
                            <th>Motivo</th>
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

<!-- Modal de ativar/inativar -->
<div class="modal fade" id="modalAlternar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirmar</h4>
            </div>
            <div class="modal-body">
                <div id="modalAlternarMsg"></div>
                <p>Alternar o status deste produto?</p>
                <p style="font-size:12px; color:#777;">
                    Produto inativo não entra em novas vendas. O estoque e o histórico continuam intactos.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarAlternar();">Confirmar</button>
            </div>
        </div>
    </div>
</div>
