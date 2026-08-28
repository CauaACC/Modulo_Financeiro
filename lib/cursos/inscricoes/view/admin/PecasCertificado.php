<?php
// Gate de seguranca da view (mesmo padrao do Painel/Verso).
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new PainelCertificadosAC();
$debug = isset($debug) ? $debug : false;

$cfgCert = $objSRV->configCertificado($debug);
$pecas = $objSRV->pecasGerais($debug);
$atividadesAss = $objSRV->atividadesCertificado($debug);
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

// Assinaturas POR ATIVIDADE: separa as que ja tem assinatura das que ainda nao
// tem. A contagem alimenta o rotulo da aba, por isso e calculada antes dela.
$atvComAss = array();
$atvSemAss = array();
foreach ($atividadesAss as $a) {
    if (!empty($a['temAssinatura'])) { $atvComAss[] = $a; }
    else { $atvSemAss[] = $a; }
}
// Evento que nao emite certificado nao tem o que separar por escopo: cai no
// aviso, sem abas.
$temAbas = !empty($cfgCert['emite']);
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
    .cert-card.sem-abas { border-radius:12px; margin-top:10px; }
    .cert-pane { display:none; }
    .cert-pane.ativa { display:block; }

    .pc-card { display:flex; gap:16px; align-items:center; border:1px solid #e3ebf3; border-radius:8px; padding:12px 14px; margin-bottom:12px; }
    .pc-card .pc-prev { flex:0 0 120px; height:80px; border:1px dashed #ccd; border-radius:6px; display:flex; align-items:center; justify-content:center; background:#fafcff; overflow:hidden; }
    .pc-card .pc-prev img { max-width:100%; max-height:100%; }
    .pc-card .pc-prev .pc-pdf { color:#b02a1c; font-size:12px; font-weight:600; text-align:center; }
    .pc-card .pc-prev .pc-vazio { color:#bbb; font-size:12px; }
    .pc-card .pc-info { flex:1 1 auto; }
    .pc-card .pc-info .pc-rot { font-weight:600; color:#333; }
    .pc-card .pc-info .pc-nome { font-size:12px; color:#888; margin-top:2px; }
    .pc-card .pc-acoes { flex:0 0 auto; text-align:right; }
    .pc-card .pc-acoes .input-group { margin-top:6px; }
</style>

<?php if ($temAbas): ?>
<div class="cert-abas">
    <div class="cert-aba ativa" data-aba="evento"><i class="fa fa-certificate"></i> Do evento</div>
    <div class="cert-aba" data-aba="atividade"><i class="fa fa-sitemap"></i> Por atividade<?php
        echo !empty($atvComAss) ? ' (' . count($atvComAss) . ')' : ''; ?></div>
</div>
<?php endif; ?>

<div class="cert-card<?php echo $temAbas ? '' : ' sem-abas'; ?>">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Assinaturas do Certificado</h4>
        <small class="text-muted">Cada assinatura usa o <b>padr&atilde;o do gestor</b> &mdash; personalize s&oacute; se precisar</small>
    </div>

    <div style="padding:16px 20px;">

        <?php if (!$temAbas): ?>
            <div style="border:1px solid #f0c9c2; background:#fdf3f1; border-radius:8px; padding:14px 16px; color:#8a3b2e;">
                <b>Este evento n&atilde;o emite certificado.</b>
                Ligue em <a href="index.php?content=view/admin/ConfiguracaoCertificado.php">Regras</a> para configurar as artes.
            </div>
        <?php else: ?>

        <div class="cert-pane ativa" data-pane="evento">

        <div style="font-size:12px; color:#888; margin-bottom:12px;">
            A <b>Configura&ccedil;&atilde;o</b> deste evento pede
            <b><?php echo $cfgCert['dupla'] ? 'duas assinaturas' : 'uma assinatura'; ?></b>.
            <a href="index.php?content=view/admin/ConfiguracaoCertificado.php">alterar em Regras</a>
        </div>

        <?php foreach ($pecas as $i => $p): ?>
            <div class="pc-card">
                <div class="pc-prev" id="pcPrev<?= $i ?>">
                    <?php if ($p['ok'] && $p['ehPdf']): ?>
                        <span class="pc-pdf"><i class="fa fa-file-pdf-o fa-2x"></i><br>PDF</span>
                    <?php elseif ($p['ok']): ?>
                        <img src="<?= WB_URL_COMMAND ?>&c=PainelCertificados&m=verPeca&_submit_check=1&slot=<?= urlencode($p['slotUpload']) ?>&t=<?= substr(md5($p['nome']), 0, 6) ?>" alt="preview">
                    <?php else: ?>
                        <span class="pc-vazio">sem arte</span>
                    <?php endif; ?>
                </div>
                <div class="pc-info">
                    <div class="pc-rot"><?= htmlspecialchars($p['rotulo']) ?></div>
                    <div>
                        <?php if ($p['ok']): ?>
                            <span style="display:inline-block; padding:1px 8px; border-radius:10px; background:#e6f4ea; color:#1e7e34; font-size:12px; font-weight:600;">&#10003; personalizada neste evento</span>
                        <?php elseif ($p['temPadrao']): ?>
                            <span style="display:inline-block; padding:1px 8px; border-radius:10px; background:#eef3f8; color:#3d6b95; font-size:12px; font-weight:600;">padr&atilde;o do gestor</span>
                        <?php else: ?>
                            <span style="display:inline-block; padding:1px 8px; border-radius:10px; background:#f4f4f4; color:#888; font-size:12px; font-weight:600;">sem fundo (verso branco)</span>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($p['usada'])): ?>
                        <div style="margin-top:3px; font-size:11px; color:#a06000;">
                            <i class="fa fa-info-circle"></i> N&atilde;o sai no certificado: o evento est&aacute; com
                            <b>uma assinatura</b>. Ligue a assinatura dupla em <i>Regras</i>.
                        </div>
                    <?php endif; ?>
                    <div class="pc-nome"><?= $p['nome'] !== '' ? htmlspecialchars($p['nome']) : '&mdash;' ?></div>
                </div>
                <div class="pc-acoes">
                    <?php if ($p['ok']): ?>
                        <button type="button" class="btn btn-xs btn-default pc-ver" data-slot="<?= htmlspecialchars($p['slotUpload'], ENT_QUOTES) ?>"><i class="fa fa-eye"></i> Visualizar</button>
                        <button type="button" class="btn btn-xs btn-danger pc-excluir" data-slot="<?= htmlspecialchars($p['slotUsado'], ENT_QUOTES) ?>" data-rotulo="<?= htmlspecialchars($p['rotulo'], ENT_QUOTES) ?>" title="Excluir esta assinatura"><i class="fa fa-trash"></i></button>
                    <?php endif; ?>
                    <div class="input-group input-group-sm" style="min-width:280px;">
                        <input type="file" class="form-control pc-file" data-idx="<?= $i ?>" data-slot="<?= htmlspecialchars($p['slotUpload'], ENT_QUOTES) ?>" accept="<?= htmlspecialchars($p['aceita'], ENT_QUOTES) ?>">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default pc-enviar" data-idx="<?= $i ?>"><i class="fa fa-upload"></i> Enviar</button>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <small class="text-muted" style="display:block; margin-top:6px;">
            Estas assinaturas ficam no m&oacute;dulo M&iacute;dia &mdash; aqui &eacute; s&oacute; um atalho focado no certificado.
        </small>

        </div><!-- /aba Do evento -->

        <div class="cert-pane" data-pane="atividade">
        <?php if (!empty($atividadesAss)): ?>
        <small class="text-muted" style="display:block; margin-bottom:10px;">
            Aqui &eacute; a assinatura do <b>coordenador de cada atividade</b>, que vale s&oacute; nos
            certificados daquela atividade. A assinatura que sai em todos os certificados do evento
            fica na aba <b>Do evento</b>.
        </small>

        <?php if (!empty($atvComAss)): ?>
        <?php foreach ($atvComAss as $a): ?>
            <div class="pc-card">
                <div class="pc-prev">
                    <img src="view/certificados/modelos/img/ass-coordenador.php?id=<?= (int) $a['codOficina'] ?>" alt="assinatura">
                </div>
                <div class="pc-info">
                    <div class="pc-rot"><?= rotuloAtividade($a['codOficina'], $a['titulo']) ?></div>
                    <div>
                        <span style="display:inline-block; padding:1px 8px; border-radius:10px; background:#e6f4ea; color:#1e7e34; font-size:12px; font-weight:600;">&#10003; assinatura enviada</span>
                    </div>
                    <div class="pc-nome">coordenador-<?= numberPad((int) $a['codOficina'], 7) ?>.jpg</div>
                </div>
                <div class="pc-acoes">
                    <div class="input-group input-group-sm" style="min-width:280px;">
                        <input type="file" class="form-control asf-file" data-oficina="<?= (int) $a['codOficina'] ?>" accept="image/*">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default asf-enviar"><i class="fa fa-upload"></i> Trocar</button>
                            <button type="button" class="btn btn-danger asf-excluir" data-oficina="<?= (int) $a['codOficina'] ?>" title="Excluir esta assinatura"><i class="fa fa-trash"></i></button>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if (empty($atvComAss)): ?>
        <p class="text-muted" style="margin:0 0 8px;">Nenhuma atividade com assinatura pr&oacute;pria ainda.</p>
        <?php endif; ?>

        <?php if (!empty($atvSemAss)): ?>
        <div style="border:1px dashed #cdd7e3; background:#f8fafc; border-radius:8px; padding:12px 14px; margin-top:12px;">
            <div style="font-weight:600; color:#2c5f8d; margin-bottom:8px;">
                <i class="fa fa-plus-circle"></i> Adicionar assinatura para uma atividade
            </div>
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <select id="assNovaOficina" class="form-control input-sm" style="width:auto; max-width:380px; display:inline-block;">
                    <option value="">Selecione a atividade&hellip;</option>
                    <?php foreach ($atvSemAss as $a): ?>
                        <option value="<?= (int) $a['codOficina'] ?>"><?= htmlspecialchars(rotuloAtividade($a['codOficina'], $a['titulo'], false)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="assNovaEnviar" class="btn btn-sm btn-primary" disabled><i class="fa fa-upload"></i> Enviar imagem</button>
            </div>
            <small class="text-muted" style="display:block; margin-top:6px;">Imagem JPG ou PNG da assinatura.</small>
        </div>
        <?php endif; ?>

        <!-- input escondido para upload direto da assinatura da atividade -->
        <input type="file" id="assArquivo" accept="image/*" style="display:none;">
        <?php else: ?>
        <p class="text-muted" style="margin:0;">
            Este evento n&atilde;o tem atividades que emitam certificado, ent&atilde;o n&atilde;o h&aacute;
            assinatura por atividade para configurar.
        </p>
        <?php endif; ?>
        </div><!-- /aba Por atividade -->

        <?php endif; // fim do "certificado emite" ?>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {

    /* Abas. Cada upload aqui termina em location.reload(); sem guardar a aba
       escolhida, quem acabou de trocar a assinatura de uma atividade voltaria
       para a aba do evento e acharia que nada foi salvo. */
    var ABA_MEM = 'certAbaAssinaturas';
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

    // Ver a peca numa nova aba (rota verPeca por slot).
    $('.pc-ver').on('click', function () {
        var slot = $(this).data('slot');
        var $f = $('<form>', {method:'post', action:'<?= WB_URL_COMMAND ?>&t=' + Math.random(), target:'_blank'});
        $.each({'c':'PainelCertificados','m':'verPeca','_submit_check':'1','slot':slot}, function (k, v) {
            $f.append($('<input>', {type:'hidden', name:k, value:v}));
        });
        $f.appendTo('body').submit().remove();
    });

    // Upload por slot, reusando GerenciarMidiaUpload (mesmo do gerador).
    // Fundo do verso: PDF -> slot PDF; imagem -> slot imagem (extensao decide).
    $('.pc-enviar').on('click', function () {
        var idx = $(this).data('idx');
        var $file = $('.pc-file[data-idx="' + idx + '"]');
        var input = $file.get(0);
        if (!input.files || !input.files.length) {
            loading('div#erro', 'show');
            $('div#erro').html('Selecione um arquivo primeiro.');
            return;
        }
        var nome = input.files[0].name.toLowerCase();
        var slot = $file.data('slot');
        if (slot === 'CERTIFICADO-VERSO-FUNDO' && nome.endsWith('.pdf')) {
            slot = 'CERTIFICADO-VERSO-FUNDO-PDF';
        }

        var fd = new FormData();
        fd.append('c', 'GerenciarMidiaUpload');
        fd.append('m', 'salvarMidiaUpload');
        fd.append('_submit_check', '1');
        fd.append('novoCodArquivo', '0');
        fd.append('codArquivo', '');
        fd.append('localMidia', slot);
        fd.append('codPerfil', '');
        fd.append('codOficina', '');
        fd.append('arquivo', input.files[0]);

        startLoading();
        $.ajax({
            type:'POST', url:'<?= WB_URL_COMMAND ?>&t=' + Math.random(),
            data:fd, processData:false, contentType:false, dataType:'json', cache:false,
            success: function (data) {
                processJson(data);
                if (data.statusMessage) { setTimeout(function () { location.reload(); }, 600); }
            },
            error: function () { loading('div#erro', 'show'); $('div#erro').html('Erro ao enviar. Veja o console (F12).'); },
            complete: function () { stopLoading(); }
        });
    });

    // ---------- Assinatura por atividade: upload direto (igual ao programa) ----------
    function enviarAssinatura(cod, file) {
        if (!cod || !file) { return; }
        var fd = new FormData();
        fd.append('c', 'PainelCertificados');
        fd.append('m', 'salvarAssinaturaPronta');
        fd.append('_submit_check', '1');
        fd.append('codOficina', cod);
        fd.append('arquivo', file);
        startLoading();
        $.ajax({
            type:'POST', url:'<?= WB_URL_COMMAND ?>&t=' + Math.random(),
            data:fd, processData:false, contentType:false, dataType:'json', cache:false,
            success: function (data) {
                processJson(data);
                if (data.statusMessage) { setTimeout(function () { location.reload(); }, 600); }
            },
            error: function () { loading('div#erro', 'show'); $('div#erro').html('Erro ao enviar a assinatura. Veja o console (F12).'); },
            complete: function () { stopLoading(); }
        });
    }

    // ---------- Exclusoes ----------
    function excluir(dados, confirmacao, sucesso) {
        if (!confirm(confirmacao)) { return; }
        startLoading();
        $.post('<?= WB_URL_COMMAND ?>&t=' + Math.random(),
            $.extend({'c':'PainelCertificados', '_submit_check':'1'}, dados),
            function (r) {
                processJson && processJson(r);
                if (r && r.statusMessage) { setTimeout(function () { location.reload(); }, 700); }
            }, 'json'
        ).always(function () { stopLoading(); });
    }

    // Assinatura do evento (presidente / coordenador geral).
    $('.pc-excluir').on('click', function () {
        var slot = $(this).data('slot'), rot = $(this).data('rotulo');
        excluir({'m':'excluirPeca', 'slot':slot},
                'Excluir "' + rot + '"?\n\nO certificado volta a usar o padrao do gestor.');
    });

    // Assinatura de uma atividade.
    $('.asf-excluir').on('click', function () {
        excluir({'m':'excluirAssinaturaAtividade', 'codOficina':$(this).data('oficina')},
                'Excluir a assinatura desta atividade?');
    });

    // Card existente: input inline (asf-file) + botao Trocar (asf-enviar).
    $('.asf-enviar').on('click', function () {
        var $file = $(this).closest('.pc-acoes').find('.asf-file');
        var input = $file.get(0);
        if (!input.files || !input.files.length) {
            loading('div#erro', 'show'); $('div#erro').html('Selecione a imagem da assinatura primeiro.');
            return;
        }
        enviarAssinatura($file.data('oficina'), input.files[0]);
    });

    // Seletor "adicionar": escolhe a atividade e dispara o file picker escondido.
    $('#assNovaOficina').on('change', function () {
        $('#assNovaEnviar').prop('disabled', !$(this).val());
    });
    $('#assNovaEnviar').on('click', function () {
        if (!$('#assNovaOficina').val()) { return; }
        $('#assArquivo').val('').trigger('click');
    });
    $('#assArquivo').on('change', function () {
        if (!this.files || !this.files.length) { return; }
        enviarAssinatura($('#assNovaOficina').val(), this.files[0]);
    });
});
</script>
