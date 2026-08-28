<?php
require_once(dirname(__FILE__) . "/../../view/admin/seguranca-adm-geral.php");
$objSRV = new GerenciarTrocaSenhaInscritoAC();
?>
<script type="text/javascript">
$(document).ready(function() {
    carregarTabelaTrocaSenha();
    $('#tsFiltrar').click(carregarTabelaTrocaSenha);
    $('#tsIncluirPendentes').change(carregarTabelaTrocaSenha);

    // atalho vindo da lista de inscritos: ?lsEmail= ja abre o modal de acoes
    var lsInicial = <?php echo json_encode(isset($_GET['lsEmail']) ? (string) $_GET['lsEmail'] : ''); ?>;
    if (lsInicial) { $('#lsEmail').val(lsInicial); abrirModalLoginEmail(lsInicial); }

    // Ordenacao pelo cabecalho (handler delegado -- a tabela e remontada por AJAX).
    $('#tsResultado').on('click', 'th.ts-sort', function() {
        var th = $(this), tabela = th.closest('table'), col = parseInt(th.data('col'), 10);
        var asc = !(th.data('asc') === true);
        tabela.find('th.ts-sort').data('asc', null).find('.sort-arrow').html('&uarr;&darr;');
        th.data('asc', asc).find('.sort-arrow').html(asc ? '&uarr;' : '&darr;');

        var tbody = tabela.find('tbody');
        var linhas = tbody.find('tr').get();
        if (linhas.length && $(linhas[0]).find('td').length < 10) return; // linha "nenhum registro"

        function valor(tr) {
            var td = $(tr).find('td').eq(col);
            var ord = td.attr('data-ord');
            return (ord !== undefined && ord !== null) ? ord : td.text().trim();
        }
        function ehNum(s){ return /^-?\d{1,3}(\.\d{3})*(,\d+)?$/.test(s); }

        linhas.sort(function(a, b) {
            var va = valor(a), vb = valor(b);
            var ea = (va === '' || va === '—'), eb = (vb === '' || vb === '—');
            if (ea && eb) return 0;
            if (ea) return 1;   // vazio sempre por ultimo
            if (eb) return -1;
            var r;
            if (ehNum(va) && ehNum(vb)) {
                r = parseFloat(va.replace(/\./g,'').replace(',','.')) - parseFloat(vb.replace(/\./g,'').replace(',','.'));
            } else {
                r = va.localeCompare(vb, 'pt-BR');
            }
            return asc ? r : -r;
        });
        $.each(linhas, function(_, tr) { tbody.append(tr); });
        // renumera a coluna #
        tbody.find('tr').each(function(idx){ $(this).find('td').eq(0).text(idx+1); });
    });
});

function carregarTabelaTrocaSenha() {
    $('#tsResultado').html('<div class="text-center" style="padding:24px;"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarTrocaSenhaInscrito',
        'm': 'carregarTabelaTrocaSenha',
        'de': $('#tsDe').val(),
        'ate': $('#tsAte').val(),
        'email': $('#tsEmail').val(),
        'incluirPendentes': $('#tsIncluirPendentes').is(':checked') ? '1' : '0',
        '_submit_check': '1'
    }, function(html) {
        $('#tsResultado').html(html);
    });
}

// Card "definir senha" do topo: busca livre por e-mail (nao precisa estar na
// tabela abaixo) -- reusa o MESMO modal de acoes das duas telas.
function lsBuscar() {
    var email = $.trim($('#lsEmail').val());
    if (!email) { alert('Informe o e-mail.'); return; }
    abrirModalLoginEmail(email);
}
</script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_usuarios.php");
?>
<!-- Card: admin define a senha do inscrito -->
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">
            <i class="fa fa-key"></i> Login de Inscritos &mdash; definir senha
        </h4>
        <p style="margin:4px 0 0; color:#888; font-size:12px;">
            Gere uma senha para o inscrito entrar. Ele acessa com <b>e-mail + a senha gerada + os 4 &uacute;ltimos d&iacute;gitos do CPF</b>.
        </p>
        <?php $objSRV->resumoMigracao($debug); ?>
    </div>
    <div style="padding:14px 20px;">
        <label class="label-filtro">E-mail do inscrito:</label>
        <div style="display:flex; gap:8px; max-width:520px;">
            <input type="text" id="lsEmail" class="form-control input-sm" placeholder="email@exemplo.com" onkeydown="if(event.key==='Enter'){lsBuscar();return false;}">
            <button class="btn btn-sm" id="lsBuscar" onclick="lsBuscar()" style="border-radius:5px; padding:5px 16px; font-size:12px; background:#2980b9; color:#fff; border:none; white-space:nowrap;">
                <i class="fa fa-search"></i> Buscar
            </button>
        </div>
    </div>
</div>

<!-- Card: historico de trocas -->
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:12px;">
    <div style="padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">
            <i class="fa fa-history"></i> Hist&oacute;rico de trocas de senha
        </h4>
        <p style="margin:4px 0 0; color:#888; font-size:12px;">
            Quem pediu o link de recupera&ccedil;&atilde;o, quando trocou, &uacute;ltimo login e &uacute;ltima tentativa.
            N&atilde;o inclui senha gerada direto pelo admin (card acima) sem passar pelo link. N&atilde;o mostra a senha.
        </p>
    </div>
    <div style="padding:12px 18px;">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-sm-2">
                <label class="label-filtro">De:</label>
                <input type="date" id="tsDe" class="form-control input-sm">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">At&eacute;:</label>
                <input type="date" id="tsAte" class="form-control input-sm">
            </div>
            <div class="col-sm-3">
                <label class="label-filtro">E-mail:</label>
                <input type="text" id="tsEmail" class="form-control input-sm" placeholder="filtrar por e-mail">
            </div>
            <div class="col-sm-3" style="padding-top:22px;">
                <label style="font-weight:normal; font-size:13px;">
                    <input type="checkbox" id="tsIncluirPendentes"> Incluir pedidos n&atilde;o conclu&iacute;dos
                </label>
            </div>
            <div class="col-sm-2" style="padding-top:16px;">
                <button class="btn btn-sm" id="tsFiltrar" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;">
                    <i class="fa fa-search"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
    <div id="tsResultado" style="padding:0 6px 10px;">
        <?php $objSRV->carregarTabelaTrocaSenha($fws_, $debug); ?>
    </div>
</div>

<?php require_once(dirname(__FILE__) . "/modal-acoes-login-email.php"); ?>
