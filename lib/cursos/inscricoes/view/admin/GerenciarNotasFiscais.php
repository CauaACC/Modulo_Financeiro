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

$objSRV = new GerenciarNotasFiscaisAC();

// Periodo inicial = mes corrente. Abrir a tela e despejar a base inteira nao
// ajuda ninguem; quem quer o historico limpa as datas ou usa a busca.
$_nfIni = date('Y-m-01');
$_nfFim = date('Y-m-t');
?>
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
    .tbl-fin .badge-tipo .dot-blue  { background:#2d6cdf; }
    .tbl-fin .badge-tipo .dot-red   { background:#c0392b; }
    .tbl-fin .badge-tipo .dot-gray  { background:#95a5a6; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:3px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; text-decoration:none; }
    .cx-nf { background:#fff; border:1px solid #e5e5e5; border-radius:8px; padding:18px; margin-bottom:26px; }
    .cx-nf h3 { margin-top:0; font-size:18px; }
    .barra-filtros { display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; margin-bottom:14px; }
    .barra-filtros .campo { display:flex; flex-direction:column; }
    .barra-filtros .campo label { font-size:11px; color:#888; text-transform:uppercase; margin-bottom:2px; font-weight:600; }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        atualizarConsultaNotas();

        $('#btnFiltrarNf').click(function() { atualizarConsultaNotas(); });
        $('#nfBusca').keypress(function(e) { if (e.which == 13) { atualizarConsultaNotas(); } });
        $('#nfNumero').keypress(function(e) { if (e.which == 13) { atualizarConsultaNotas(); } });
        $('#nfStatus, #nfTipo, #nfAmbiente, #nfSemEnvio').change(function() { atualizarConsultaNotas(); });

        $('#btnLimparNf').click(function() {
            $('#nfIni').val(''); $('#nfFim').val('');
            $('#nfStatus').val(''); $('#nfTipo').val(''); $('#nfAmbiente').val('');
            $('#nfNumero').val(''); $('#nfBusca').val('');
            $('#nfSemEnvio').prop('checked', false);
            atualizarConsultaNotas();
        });

        // Ordenacao pelo cabecalho: handler delegado, mexe so no tbody
        $('#tblNotas thead').on('click', 'th', function() {
            ordenarTabelaNf($('#tblNotas'), $(this).index());
        });

        $('#tblNotas').on('click', '.nf-detalhe', function(e) {
            e.preventDefault();
            abrirDetalheNota($(this).closest('tr').data('id'));
        });
    });

    function filtrosNota() {
        return {
            'dataIni':      $('#nfIni').val(),
            'dataFim':      $('#nfFim').val(),
            'status':       $('#nfStatus').val(),
            'tipo':         $('#nfTipo').val(),
            'ambiente':     $('#nfAmbiente').val(),
            'numero':       $('#nfNumero').val(),
            'busca':        $('#nfBusca').val(),
            'semEnvio':     $('#nfSemEnvio').is(':checked') ? 1 : '',
            '_submit_check': '1'
        };
    }

    // Cards e lista sempre juntos: se um recarregar sem o outro, o total deixa
    // de bater com o que esta na tela.
    function atualizarConsultaNotas() {
        carregarResumoNotas();
        carregarTabelaNotas();
    }

    function carregarResumoNotas() {
        var dados = filtrosNota();
        dados.c = 'GerenciarNotasFiscais';
        dados.m = 'carregarResumoNotas';
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados, function(html) {
            $('#nfResumo').html(html);
        });
    }

    function carregarTabelaNotas() {
        var dados = filtrosNota();
        dados.c = 'GerenciarNotasFiscais';
        dados.m = 'carregarTabelaNotas';
        $('#tblNotas').find('tbody.inputs').remove();
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados, function(html) {
            $('#tblNotas').append('<tbody class="inputs">' + html + '</tbody>');
        });
    }

    function abrirDetalheNota(codNota) {
        if (!codNota) { return; }
        $('#nfDetalheCorpo').html('<div style="text-align:center; padding:20px;"><i class="fa fa-spinner fa-spin"></i></div>');
        $('#modalDetalheNota').modal('show');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'codNotaFiscal': codNota, 'c': 'GerenciarNotasFiscais', 'm': 'carregarDetalheNota', '_submit_check': '1'},
            function(html) { $('#nfDetalheCorpo').html(html); }
        );
    }

    // Numero em pt-BR ("1.234,56") e data dd/mm/aaaa nao ordenam como texto.
    function valorOrdenavelNf(texto) {
        var t = $.trim(texto);
        var data = t.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
        if (data) { return data[3] + data[2] + data[1]; }
        var num = t.replace(/^R\$\s*/, '');
        if (/^-?[\d.]+,\d+$/.test(num)) {
            return parseFloat(num.replace(/\./g, '').replace(',', '.'));
        }
        if (/^-?\d+$/.test(num)) { return parseFloat(num); }
        return t.toLowerCase();
    }

    function ordenarTabelaNf($tabela, coluna) {
        var $tbody = $tabela.find('tbody.inputs');
        var asc = $tabela.data('ordem-col') === coluna ? !$tabela.data('ordem-asc') : true;
        $tabela.data('ordem-col', coluna).data('ordem-asc', asc);

        var linhas = $tbody.find('tr').get();
        if (linhas.length < 2) { return; }
        linhas.sort(function(a, b) {
            var $a = $(a).children('td').eq(coluna);
            var $b = $(b).children('td').eq(coluna);
            // data-ord manda quando existe (data e valor ja vem prontos do PHP)
            var va = $a.attr('data-ord') !== undefined ? parseFloat($a.attr('data-ord')) || $a.attr('data-ord') : valorOrdenavelNf($a.text());
            var vb = $b.attr('data-ord') !== undefined ? parseFloat($b.attr('data-ord')) || $b.attr('data-ord') : valorOrdenavelNf($b.text());
            if (va < vb) { return asc ? -1 : 1; }
            if (va > vb) { return asc ? 1 : -1; }
            return 0;
        });
        $.each(linhas, function(i, linha) { $tbody.append(linha); });
    }
</script>

<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");
?>

<div class="cx-nf">
    <h3>Notas Fiscais</h3>
    <p style="color:#666; font-size:13px;">
        Notas emitidas pelo sistema — NFS-e (servi&ccedil;o, por pedido) e NF-e (venda de mercadoria).
        Come&ccedil;a no m&ecirc;s corrente; limpe as datas para buscar em todo o hist&oacute;rico.
    </p>

    <div class="barra-filtros">
        <div class="campo">
            <label for="nfIni">De</label>
            <input type="date" id="nfIni" class="form-control input-sm" value="<?php echo $_nfIni ?>">
        </div>
        <div class="campo">
            <label for="nfFim">At&eacute;</label>
            <input type="date" id="nfFim" class="form-control input-sm" value="<?php echo $_nfFim ?>">
        </div>
        <div class="campo">
            <label for="nfStatus">Situa&ccedil;&atilde;o</label>
            <select id="nfStatus" class="form-control input-sm">
                <option value="">Todas</option>
                <option value="AUTORIZADA">Autorizadas</option>
                <option value="PROCESSANDO">Processando</option>
                <option value="ERRO">Rejeitadas</option>
                <option value="CANCELADA">Canceladas</option>
            </select>
        </div>
        <div class="campo">
            <label for="nfTipo">Tipo</label>
            <select id="nfTipo" class="form-control input-sm">
                <option value="">Todos</option>
                <option value="NFSE">NFS-e</option>
                <option value="NFE">NF-e</option>
            </select>
        </div>
        <div class="campo">
            <label for="nfAmbiente">Ambiente</label>
            <select id="nfAmbiente" class="form-control input-sm">
                <option value="">Todos</option>
                <option value="PRODUCAO">Produ&ccedil;&atilde;o</option>
                <option value="HOMOLOGACAO">Homologa&ccedil;&atilde;o</option>
            </select>
        </div>
        <div class="campo">
            <label for="nfNumero">N&uacute;mero</label>
            <input type="text" id="nfNumero" class="form-control input-sm" style="width:110px;" placeholder="n&ordm; da nota">
        </div>
        <div class="campo">
            <label for="nfBusca">Tomador / pedido</label>
            <input type="text" id="nfBusca" class="form-control input-sm" style="width:250px;"
                   placeholder="nome, CPF/CNPJ, pedido ou chave">
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="btnFiltrarNf">
            <i class="fa fa-search"></i> Filtrar
        </button>
        <button type="button" class="btn btn-default btn-sm" id="btnLimparNf">Limpar</button>
    </div>

    <div class="barra-filtros" style="margin-top:-4px;">
        <label style="font-weight:normal; margin:0; font-size:12px;">
            <input type="checkbox" id="nfSemEnvio"> s&oacute; autorizadas <b>n&atilde;o enviadas</b> por e-mail
        </label>
    </div>

    <div id="nfResumo"></div>

    <table class="tbl-fin" id="tblNotas">
        <thead>
            <tr>
                <th class="col-codigo" style="width:110px;">A&ccedil;&otilde;es</th>
                <th class="col-codigo">N&ordm;</th>
                <th class="col-badge">Tipo</th>
                <th class="col-codigo">Emiss&atilde;o</th>
                <th>Tomador</th>
                <th class="col-num" style="text-align:right;">Valor</th>
                <th class="col-badge">Situa&ccedil;&atilde;o</th>
                <th class="col-codigo">Origem</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal Detalhe da Nota -->
<div class="modal fade" id="modalDetalheNota" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Detalhes da nota</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="nfDetalheCorpo"></div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
