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
?>
<script type="text/javascript" src="resources/js/default.js"></script>
<style>
    .cfg-card { background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px; }
    .cfg-card-head { display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-bottom:1px solid #eee; }
    .cfg-card-head h4 { margin:0; font-size:15px; font-weight:600; color:#333; }
    .cfg-body { padding:18px 20px; }
    .cfg-situacao { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
    .cfg-pill { border:1px solid #e8e8e8; border-radius:10px; padding:10px 14px; min-width:150px; }
    .cfg-pill .rot { font-size:11px; text-transform:uppercase; color:#999; letter-spacing:0.3px; }
    .cfg-pill .val { font-size:20px; font-weight:700; color:#333; }
    .cfg-pill.vazio .val { color:#e74c3c; }
    .cfg-opcao { border:1px solid #e8e8e8; border-radius:10px; padding:16px; margin-bottom:14px; }
    .cfg-opcao h5 { margin:0 0 4px; font-size:14px; font-weight:600; color:#333; }
    .cfg-opcao p.ajuda { color:#777; font-size:12px; margin:0 0 12px; }
    .cfg-blocos { border:1px solid #e8e8e8; border-radius:10px; padding:12px 16px; margin-bottom:14px; background:#fcfcfd; }
    .cfg-blocos strong { font-size:12px; text-transform:uppercase; color:#7a879a; letter-spacing:0.3px; margin-right:14px; }
    .cfg-blocos label { font-weight:400; font-size:13px; margin-right:18px; color:#444; }
    .cfg-blocos .obs { color:#999; font-size:12px; }
    .cfg-origem { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:6px; }
    .cfg-origem label { margin:0; font-size:12px; text-transform:uppercase; color:#7a879a; letter-spacing:0.3px; }
    .cfg-origem select { flex:1; min-width:320px; max-width:640px; }
    .ajuda-origem { color:#777; font-size:12px; margin:0 0 14px; }
    .cfg-atalhos { border:1px solid #d8e3ef; background:#f6f9fc; border-radius:10px; padding:12px 16px; margin-bottom:14px; }
    .cfg-atalhos .rot { font-size:11px; text-transform:uppercase; color:#7a879a; letter-spacing:0.3px; margin-bottom:8px; }
    .cfg-atalhos .btn { margin-right:6px; margin-bottom:4px; }
    .cfg-previa { border:1px solid #cfe3d4; background:#f4fbf6; border-radius:10px; padding:14px 16px; margin-bottom:14px; }
    .cfg-previa h6 { margin:0 0 10px; font-size:13px; font-weight:700; color:#2b6b45; }
    .cfg-previa .grupo { margin-bottom:10px; }
    .cfg-previa .grupo .rot { font-size:11px; text-transform:uppercase; color:#7a8b80; letter-spacing:0.3px; margin-bottom:3px; }
    .cfg-previa .grupo ul { margin:0; padding-left:18px; }
    .cfg-previa .grupo li { font-size:12.5px; color:#333; line-height:1.6; white-space:pre; }
    .cfg-aviso { background:#fdf6e3; border:1px solid #f0e0b0; color:#8a6d3b; border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
    .tbl-fin { width:100%; border-collapse:collapse; font-size:13px; table-layout:auto; }
    .tbl-fin tbody td, .tbl-fin thead th { overflow:visible; text-overflow:clip; }
    .tbl-fin tbody td { padding:6px 10px; border-bottom:1px solid #f0f0f0; }
</style>
<script type="text/javascript">
    var _origens = [];
    var _previaDados = null;

    $(document).ready(function() {
        carregarSituacao();
        $('#btnAplicar').click(function() {
            // modelo padrao e copia sao a mesma acao para quem usa: muda so a origem
            var origem = $('#codEventoOrigem').val();
            executar(origem === 'PADRAO' ? 'aplicarModeloPadrao' : 'copiarDeEvento');
        });
        $('#codEventoOrigem').change(function() { verPrevia($(this).val()); });
        // marcar/desmarcar reflete na hora: o item some ou volta na lista,
        // deixando um retrato do que sera criado de fato
        $('.cfg-blocos input').change(function() { renderPrevia(); });
    });

    function carregarSituacao() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarCatalogo', 'm': 'listarOrigens', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                if (!data.statusMessage) { processJson(data); return; }
                var a = data.dados.atual;
                _origens = data.dados.origens || [];

                if (a.contas > 0 || a.centro > 0 || a.rateios > 0 || a.fontes > 0) {
                    $('#atalhosBox').show();
                }
                $('#pillContas').html(pill('Plano de contas', a.contas));
                $('#pillCentro').html(pill('Centro de custo', a.centro));
                $('#pillFontes').html(pill('Fontes de recurso', a.fontes));
                $('#pillRateios').html(pill('Regras de rateio', a.rateios));

                var sel = $('#codEventoOrigem');
                sel.empty();
                sel.append('<option value="PADRAO">Modelo padr&atilde;o &mdash; cat&aacute;logo base (receitas, 17 rubricas de despesa, taxas)</option>');
                $.each(_origens, function(i, o) {
                    sel.append('<option value="' + o.cod_evento + '">'
                        + 'Copiar do evento ' + o.cod_evento + ' - ' + o.nome
                        + '  (' + o.contas + ' contas, ' + o.rateios + ' rateios, ' + o.fontes + ' fontes)'
                        + '</option>');
                });
                verPrevia(sel.val());
            },
            complete: function() { stopLoading(); }
        });
    }

    function pill(rotulo, valor) {
        var classe = valor > 0 ? 'cfg-pill' : 'cfg-pill vazio';
        return '<div class="' + classe + '"><div class="rot">' + rotulo + '</div><div class="val">' + valor + '</div></div>';
    }

    // Mostra, item a item, o que a opcao escolhida vai criar -- antes de gravar.
    function verPrevia(codOrigem) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarCatalogo', 'm': 'detalharOrigem', '_submit_check': '1',
                   'codEventoOrigem': (codOrigem && codOrigem !== 'PADRAO') ? codOrigem : ''},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                if (!data.statusMessage) { processJson(data); return; }
                _previaDados = data.dados;
                renderPrevia();
            },
            complete: function() { stopLoading(); }
        });
    }

    // Ordem da hierarquia real: o centro de custo e o projeto (container), o
    // plano de contas vive nele, e as regras dependem das contas.
    function renderPrevia() {
        if (!_previaDados) { return; }
        var d = _previaDados;
        var html = '<h6>' + d.titulo + '</h6>';
        html += grupo('1. Centro de custo (o projeto)', d.centro,        $('#blocoCentro').is(':checked'));
        html += grupo('2. Plano de contas',             d.contas,        $('#blocoContas').is(':checked'));
        html += grupo('3. Perfil de cadastro',          d.perfil,        $('#blocoContas').is(':checked'));
        html += grupo('4. Fontes de recurso',           d.fontes,        $('#blocoFontes').is(':checked'));
        html += grupo('5. Taxas do meio de pagamento',  d.rateios,       $('#blocoRateios').is(':checked'));
        html += grupo('6. Taxas institucionais',        d.institucional, $('#blocoInstitucional').is(':checked'));
        if (html === '<h6>' + d.titulo + '</h6>') {
            html += '<div style="color:#8a6d3b; font-size:13px;">Nada marcado &mdash; nada ser&aacute; criado.</div>';
        }
        $('#previaBox').html(html).show();
    }

    // Item desmarcado SOME da lista, em vez de ficar em cinza: o que esta na
    // tela e exatamente o que sera criado.
    function grupo(rotulo, itens, marcado) {
        if (!itens || !itens.length || !marcado) { return ''; }
        var html = '<div class="grupo"><div class="rot">' + rotulo + '</div><ul>';
        $.each(itens, function(i, it) { html += '<li>' + it + '</li>'; });
        return html + '</ul></div>';
    }

    function executar(metodo) {
        var dados = {
            'c': 'GerenciarCatalogo',
            'm': metodo,
            '_submit_check': '1',
            'codEventoOrigem': $('#codEventoOrigem').val() === 'PADRAO' ? '' : $('#codEventoOrigem').val(),
            'blocoContas':  $('#blocoContas').is(':checked')  ? 1 : '',
            'blocoCentro':  $('#blocoCentro').is(':checked')  ? 1 : '',
            'blocoFontes':  $('#blocoFontes').is(':checked')  ? 1 : '',
            'blocoRateios': $('#blocoRateios').is(':checked') ? 1 : '',
            'blocoInstitucional': $('#blocoInstitucional').is(':checked') ? 1 : ''

        };
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: dados,
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarSituacao();
                    $('#previaBox').hide();
                    $('#atalhosBox').show();
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

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div class="cfg-card">
    <div class="cfg-card-head">
        <h4>Configuração inicial da financeiro</h4>
    </div>
    <div class="cfg-body">

        <p style="color:#666; font-size:13px; margin-bottom:14px;">
            Popula de uma vez o plano de contas, o centro de custo, as fontes de recurso e as regras de rateio
            deste evento. O que j&aacute; existe &eacute; preservado &mdash; s&oacute; se acrescenta o que falta.
            Depois de criado, tudo vira cadastro comum e pode ser editado normalmente.
        </p>

        <div class="cfg-situacao">
            <span id="pillContas"></span>
            <span id="pillCentro"></span>
            <span id="pillFontes"></span>
            <span id="pillRateios"></span>
        </div>

        <!-- Sempre visivel, acima do quadro: marcar/desmarcar reflete na hora
             na previa logo abaixo. A ordem segue a hierarquia real. -->
        <div class="cfg-blocos">
            <strong>Trazer:</strong>
            <label><input type="checkbox" id="blocoCentro" checked> 1. Centro de custo</label>
            <label><input type="checkbox" id="blocoContas" checked> 2. Plano de contas <span class="obs">(+ perfil de Fornecedor)</span></label>
            <label><input type="checkbox" id="blocoFontes" checked> 3. Fontes de recurso</label>
            <label><input type="checkbox" id="blocoRateios" checked> 4. Taxas do meio de pagamento</label>
            <label><input type="checkbox" id="blocoInstitucional"> 5. Taxas institucionais <span class="obs">(HUTEC / dire&ccedil;&atilde;o)</span></label>
        </div>

        <!-- Uma origem so: o modelo padrao e apenas mais uma opcao da lista -->
        <div class="cfg-origem">
            <label for="codEventoOrigem">De onde trazer:</label>
            <select id="codEventoOrigem" class="form-control"></select>
            <button type="button" class="btn btn-primary" id="btnAplicar">Criar neste evento</button>
        </div>
        <p class="ajuda-origem">
            O centro de custo &eacute; criado com o nome <strong>deste</strong> evento &mdash; informe depois o
            c&oacute;digo do projeto na funda&ccedil;&atilde;o. O que j&aacute; existe aqui &eacute; preservado.
        </p>

        <!-- Retrato do que sera criado: reflete na hora o que esta marcado acima -->
        <div id="previaBox" class="cfg-previa" style="display:none;"></div>

        <div id="atalhosBox" class="cfg-atalhos" style="display:none;">
            <div class="rot">Confira e ajuste o que foi criado:</div>
            <a class="btn btn-default btn-sm" href="index.php?content=view/admin/GerenciarPlanoContas.php">Plano de Contas</a>
            <a class="btn btn-default btn-sm" href="index.php?content=view/admin/GerenciarCentroCusto.php">Centro de Custo</a>
            <a class="btn btn-default btn-sm" href="index.php?content=view/admin/GerenciarRateios.php">Rateios</a>
            <a class="btn btn-default btn-sm" href="index.php?content=view/admin/GerenciarFontesRecurso.php">Fontes de Recurso</a>
            <a class="btn btn-primary btn-sm" href="index.php?content=view/admin/GerenciarLancamentos.php">Lan&ccedil;amentos</a>
        </div>


    </div>
    <div style="height:10px;"></div>
</div>
