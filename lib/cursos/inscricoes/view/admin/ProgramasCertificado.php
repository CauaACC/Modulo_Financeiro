<?php
// Gate de seguranca da view (mesmo padrao do Painel/Artes).
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new PainelCertificadosAC();
$debug = isset($debug) ? $debug : false;

$programas = $objSRV->programasVerso($debug);
?>
<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_configuracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_certificados.php");

/* Tres grupos, um por aba: o programa DO EVENTO (codOficina 0), as atividades
   que ja tem programa gerado, e as que ainda nao tem — estas viram o seletor
   de "adicionar". O programa do evento aparece mesmo sem PDF gerado, porque e
   a partir do card dele que se abre o editor. */
$prGeral  = array();
$prAtvCom = array();
$prAtvSem = array();
foreach ($programas as $pr) {
    if ($pr['codOficina'] == 0)    { $prGeral[]  = $pr; }
    elseif (!empty($pr['gerado'])) { $prAtvCom[] = $pr; }
    else                           { $prAtvSem[] = $pr; }
}
?>

<style>
    /* Abas no mesmo padrao da tela de Divulgacao (.ed-abas / .ed-card): a faixa
       fica ACIMA do card e a aba ativa encosta nele, que perde o arredondamento
       do canto superior esquerdo para os dois virarem uma peca so. */
    .cert-abas { display:flex; gap:4px; border-bottom:2px solid #e8e8e8; margin-top:10px; }
    .cert-aba { background:#f4f6f7; border:1px solid #e8e8e8; border-bottom:none; border-radius:8px 8px 0 0;
                padding:9px 22px; font-size:13px; color:#666; cursor:pointer; margin-bottom:-2px; }
    .cert-aba.ativa { background:#fff; color:#222; font-weight:600; border-bottom:2px solid #fff; }
    .cert-card { background:#fff; border:1px solid #e8e8e8; border-radius:0 12px 12px 12px;
                 overflow:hidden; margin-bottom:26px; }
    .cert-pane { display:none; }
    .cert-pane.ativa { display:block; }

    .pc-card { display:flex; gap:16px; align-items:center; border:1px solid #e3ebf3; border-radius:8px; padding:12px 14px; margin-bottom:12px; }
    .pc-card .pc-prev { flex:0 0 90px; height:70px; border:1px dashed #ccd; border-radius:6px; display:flex; align-items:center; justify-content:center; background:#fafcff; }
    .pc-card .pc-prev .pc-pdf { color:#b02a1c; font-size:12px; font-weight:600; text-align:center; }
    .pc-card .pc-info { flex:1 1 auto; }
    .pc-card .pc-info .pc-rot { font-weight:600; color:#333; }
    .pc-card .pc-info .pc-det { font-size:12px; color:#999; }
    .pc-card .pc-info .pc-nome { font-size:12px; color:#888; margin-top:2px; }
    .pc-card .pc-acoes { flex:0 0 auto; text-align:right; white-space:nowrap; }
</style>

<div class="cert-abas">
    <div class="cert-aba ativa" data-aba="evento"><i class="fa fa-certificate"></i> Do evento</div>
    <div class="cert-aba" data-aba="atividade"><i class="fa fa-sitemap"></i> Por atividade<?php
        echo !empty($prAtvCom) ? ' (' . count($prAtvCom) . ')' : ''; ?></div>
</div>

<div class="cert-card">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Programas do Certificado (verso)</h4>
        <small class="text-muted">Programa do evento e, se houver, por atividade</small>
    </div>

    <div style="padding:16px 20px;">
        <?php
        /* O card e o mesmo nas duas abas; so muda a lista que o alimenta. */
        if (!function_exists('pr_card_programa')) {
            function pr_card_programa($pr) {
                $cod = (int) $pr['codOficina'];
                $gerado = !empty($pr['gerado']);
                ?>
                <div class="pc-card">
                    <div class="pc-prev">
                        <?php if ($gerado): ?>
                            <span class="pc-pdf"><i class="fa fa-file-pdf-o fa-2x"></i><br>PDF</span>
                        <?php else: ?>
                            <span style="color:#bbb; font-size:12px;">sem PDF</span>
                        <?php endif; ?>
                    </div>
                    <div class="pc-info">
                        <div class="pc-rot"><?= rotuloAtividade($pr['codOficina'], $pr['escopo']) ?></div>
                        <div class="pc-det"><?= htmlspecialchars($pr['detalhe']) ?></div>
                        <div>
                            <?php if ($gerado): ?>
                                <span style="display:inline-block; padding:1px 8px; border-radius:10px; background:#e6f4ea; color:#1e7e34; font-size:12px; font-weight:600;">&#10003; pronto</span>
                            <?php else: ?>
                                <span style="display:inline-block; padding:1px 8px; border-radius:10px; background:#fdf4e6; color:#b07a1c; font-size:12px; font-weight:600;">n&atilde;o gerado</span>
                            <?php endif; ?>
                        </div>
                        <div class="pc-nome"><?= htmlspecialchars($pr['arquivo']) ?></div>
                    </div>
                    <div class="pc-acoes">
                        <a class="btn btn-xs btn-primary" href="index.php?content=view/admin/GerenciarVersoPrograma.php&codOficina=<?= $cod ?>" title="Abrir o editor com este escopo"><i class="fa fa-magic"></i> Gerar</a>
                        <button type="button" class="btn btn-xs btn-default pr-pronto" data-oficina="<?= $cod ?>"><i class="fa fa-upload"></i> Enviar pronto</button>
                        <?php if ($gerado): ?>
                            <button type="button" class="btn btn-xs btn-default pr-ver" data-oficina="<?= $cod ?>"><i class="fa fa-eye"></i> Visualizar</button>
                            <button type="button" class="btn btn-xs btn-danger pr-excluir" data-oficina="<?= $cod ?>" data-escopo="<?= htmlspecialchars($pr['escopo'], ENT_QUOTES) ?>" title="Excluir o PDF do programa"><i class="fa fa-trash"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
        }
        ?>

        <div class="cert-pane ativa" data-pane="evento">
            <small class="text-muted" style="display:block; margin-bottom:10px;">
                Este programa sai no verso de <b>todos</b> os certificados do evento. Pode ser
                <b>gerado</b> aqui no editor ou <b>enviado pronto</b> em PDF.
            </small>
            <?php foreach ($prGeral as $pr) { pr_card_programa($pr); } ?>
            <?php if (empty($prGeral)): ?>
                <p class="text-muted" style="margin:0;">Nenhum programa de evento dispon&iacute;vel.</p>
            <?php endif; ?>
        </div><!-- /aba Do evento -->

        <div class="cert-pane" data-pane="atividade">
            <small class="text-muted" style="display:block; margin-bottom:10px;">
                O programa de uma atividade sai s&oacute; no verso dos certificados <b>daquela
                atividade</b> e substitui o programa do evento.
            </small>
            <?php foreach ($prAtvCom as $pr) { pr_card_programa($pr); } ?>
            <?php if (empty($prAtvCom)): ?>
                <p class="text-muted" style="margin:0 0 8px;">Nenhuma atividade com programa pr&oacute;prio ainda.</p>
            <?php endif; ?>

        <?php if (!empty($prAtvSem)): ?>
        <div style="border:1px dashed #cdd7e3; background:#f8fafc; border-radius:8px; padding:12px 14px; margin-top:14px;">
            <div style="font-weight:600; color:#2c5f8d; margin-bottom:8px;">
                <i class="fa fa-plus-circle"></i> Adicionar programa para uma atividade
            </div>
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <select id="prNovaOficina" class="form-control input-sm" style="width:auto; max-width:380px; display:inline-block;">
                    <option value="">Selecione a atividade&hellip;</option>
                    <?php foreach ($prAtvSem as $pr): ?>
                        <option value="<?= (int) $pr['codOficina'] ?>"><?= htmlspecialchars(rotuloAtividade($pr['codOficina'], $pr['escopo'], false)) ?></option>
                    <?php endforeach; ?>
                </select>
                <a id="prNovaGerar" class="btn btn-sm btn-primary disabled" href="#"><i class="fa fa-magic"></i> Gerar no editor</a>
                <button type="button" id="prNovaPronto" class="btn btn-sm btn-default" disabled><i class="fa fa-upload"></i> Enviar PDF pronto</button>
            </div>
            <small class="text-muted" style="display:block; margin-top:6px;">Escolha a atividade e depois <b>gere</b> o programa no editor ou <b>envie um PDF</b> j&aacute; pronto.</small>
        </div>
        <?php endif; ?>
        </div><!-- /aba Por atividade -->

        <input type="file" id="prArquivo" accept=".pdf" style="display:none;">
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {
    var prOficinaAlvo = 0;

    /* Abas. Enviar/excluir programa termina em location.reload(); sem guardar a
       aba, quem mexeu no programa de uma atividade voltaria para a do evento. */
    var ABA_MEM = 'certAbaProgramas';
    function mostrarAba(nome) {
        if (!$('.cert-aba[data-aba="' + nome + '"]').length) { nome = 'evento'; }
        $('.cert-aba').removeClass('ativa').filter('[data-aba="' + nome + '"]').addClass('ativa');
        $('.cert-pane').removeClass('ativa').filter('[data-pane="' + nome + '"]').addClass('ativa');
        try { localStorage.setItem(ABA_MEM, nome); } catch (e) {}
    }
    $('.cert-aba').on('click', function () { mostrarAba($(this).data('aba')); });
    try {
        var guardada = localStorage.getItem(ABA_MEM);
        if (guardada) { mostrarAba(guardada); }
    } catch (e) {}

    // "Visualizar": abre o PDF do verso daquele escopo (geral ou atividade).
    $('.pr-ver').on('click', function () {
        var cod = $(this).data('oficina');
        var $f = $('<form>', {method:'post', action:'<?= WB_URL_COMMAND ?>&t=' + Math.random(), target:'_blank'});
        $.each({'c':'PainelCertificados','m':'verVerso','_submit_check':'1','codOficina':cod}, function (k, v) {
            $f.append($('<input>', {type:'hidden', name:k, value:v}));
        });
        $f.appendTo('body').submit().remove();
    });

    // Excluir o PDF do programa. O texto do editor NAO e apagado -- da para
    // gerar de novo; some so o PDF que o certificado anexa no verso.
    $('.pr-excluir').on('click', function () {
        var cod = $(this).data('oficina'), escopo = $(this).data('escopo');
        if (!confirm('Excluir o PDF do programa de "' + escopo + '"?\n\n'
                   + 'O texto continua salvo no editor e pode ser gerado de novo.')) { return; }
        startLoading();
        $.post('<?= WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c':'PainelCertificados', 'm':'excluirPrograma', '_submit_check':'1', 'codOficina':cod},
            function (r) {
                processJson && processJson(r);
                if (r && r.statusMessage) { setTimeout(function () { location.reload(); }, 700); }
            }, 'json'
        ).always(function () { stopLoading(); });
    });

    // "Enviar pronto" (linha existente): dispara o seletor de PDF.
    $('.pr-pronto').on('click', function () {
        prOficinaAlvo = $(this).data('oficina');
        $('#prArquivo').val('').trigger('click');
    });

    // Seletor "gerar ou adicionar para uma atividade" (so atividades sem programa).
    $('#prNovaOficina').on('change', function () {
        var cod = $(this).val();
        var temSel = cod !== '' && cod !== null;
        $('#prNovaPronto').prop('disabled', !temSel);
        $('#prNovaGerar').toggleClass('disabled', !temSel);
        $('#prNovaGerar').attr('href', temSel
            ? 'index.php?content=view/admin/GerenciarVersoPrograma.php&codOficina=' + cod : '#');
    });
    $('#prNovaGerar').on('click', function (e) { if ($(this).hasClass('disabled')) e.preventDefault(); });
    $('#prNovaPronto').on('click', function () {
        var cod = $('#prNovaOficina').val();
        if (!cod) { return; }
        prOficinaAlvo = cod;
        $('#prArquivo').val('').trigger('click');
    });

    // Upload do PDF pronto -> salvarProgramaPronto (geral ou atividade).
    $('#prArquivo').on('change', function () {
        var input = this;
        if (!input.files || !input.files.length) { return; }
        if (!input.files[0].name.toLowerCase().endsWith('.pdf')) {
            loading('div#erro', 'show'); $('div#erro').html('O programa pronto deve ser um PDF.');
            return;
        }
        var fd = new FormData();
        fd.append('c', 'PainelCertificados');
        fd.append('m', 'salvarProgramaPronto');
        fd.append('_submit_check', '1');
        fd.append('codOficina', prOficinaAlvo);
        fd.append('arquivo', input.files[0]);

        startLoading();
        $.ajax({
            type:'POST', url:'<?= WB_URL_COMMAND ?>&t=' + Math.random(),
            data:fd, processData:false, contentType:false, dataType:'json', cache:false,
            success: function (data) {
                processJson(data);
                if (data.statusMessage) { setTimeout(function () { location.reload(); }, 600); }
            },
            error: function () { loading('div#erro', 'show'); $('div#erro').html('Erro ao enviar o programa. Veja o console (F12).'); },
            complete: function () { stopLoading(); }
        });
    });
});
</script>
