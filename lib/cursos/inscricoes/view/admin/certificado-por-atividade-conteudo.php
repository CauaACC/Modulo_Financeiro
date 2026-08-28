<?php
/**
 * Conteudo da aba "Por atividade" dos Textos do Certificado.
 *
 * Fica em parcial porque a mesma listagem e usada pela aba de Textos e pela
 * rota antiga (CertificadoPorAtividade.php, hoje um stub redirecionador).
 *
 * Espera: $atividades (de PainelCertificadosAC::atividadesCertificado()).
 * Quem inclui e responsavel por trazer o modal certificado-atividade-modal.php.
 */
$atvComTexto = array();
$atvSemTexto = array();
foreach ($atividades as $a) {
    // Topo = ja definido (Liberar Certificado = Sim, a mesma coluna da
    // listagem geral de atividades). "Adicionar" mostra TODAS as demais, sem
    // filtro -- mesmo padrao das abas "Por atividade" de Assinaturas/Programas.
    if (!empty($a['libera'])) { $atvComTexto[] = $a; }
    else { $atvSemTexto[] = $a; }
}
?>
<?php if (empty($atividades)): ?>
    <p class="text-muted">Este evento n&atilde;o possui atividades cadastradas.</p>
<?php else: ?>
<small class="text-muted" style="display:block; margin-bottom:12px;">
    Cada atividade pode ter <b>certificado pr&oacute;prio</b> com seus textos (t&iacute;tulo, qualifica&ccedil;&atilde;o, per&iacute;odo, data, carga hor&aacute;ria).
    A <b>assinatura</b> fica na aba Assinaturas e o <b>programa</b> na aba Programas.
</small>

<?php if (!empty($atvComTexto)): ?>
<div style="border:1px solid #eee; border-radius:8px; overflow:hidden;">
    <table class="table" style="margin:0;">
        <thead>
            <tr style="background:#fafafa;">
                <th style="padding:8px 14px;">Atividade</th>
                <th style="padding:8px 14px;">Carga hor&aacute;ria</th>
                <th style="padding:8px 14px;">T&iacute;tulo do certificado</th>
                <th style="padding:8px 14px; text-align:right;">A&ccedil;&otilde;es</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($atvComTexto as $a): ?>
            <tr>
                <td style="padding:8px 14px; font-weight:600;"><?= rotuloAtividade($a['codOficina'], $a['titulo']) ?></td>
                <td style="padding:8px 14px; color:#666;"><?= $a['cargaHoraria'] !== '' ? htmlspecialchars($a['cargaHoraria']) . 'h' : '&mdash;' ?></td>
                <td style="padding:8px 14px; color:#666; font-size:13px;"><?= $a['tituloCert'] !== '' ? htmlspecialchars($a['tituloCert']) : '<span style="color:#bbb;">(usa o t&iacute;tulo da atividade)</span>' ?></td>
                <td style="padding:8px 14px; text-align:right; white-space:nowrap;">
                    <button type="button" class="btn btn-xs btn-primary btn-edit-atv" data-oficina="<?= (int) $a['codOficina'] ?>" data-titulo="<?= htmlspecialchars($a['titulo'], ENT_QUOTES) ?>"><i class="fa fa-pencil"></i> Ajustar texto</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p class="text-muted" style="margin:0 0 8px;">Nenhuma atividade com texto de certificado ainda.</p>
<?php endif; ?>

<?php if (!empty($atvSemTexto)): ?>
<div style="border:1px dashed #cdd7e3; background:#f8fafc; border-radius:8px; padding:12px 14px; margin-top:12px;">
    <div style="font-weight:600; color:#2c5f8d; margin-bottom:8px;">
        <i class="fa fa-plus-circle"></i> Adicionar texto para uma atividade
    </div>
    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <select id="atvNovaOficina" class="form-control input-sm" style="width:auto; max-width:420px; display:inline-block;">
            <option value="">Selecione a atividade&hellip;</option>
            <?php foreach ($atvSemTexto as $a): ?>
                <option value="<?= (int) $a['codOficina'] ?>" data-titulo="<?= htmlspecialchars(rotuloAtividade($a['codOficina'], $a['titulo'], false), ENT_QUOTES) ?>"><?= htmlspecialchars(rotuloAtividade($a['codOficina'], $a['titulo'], false)) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" id="atvNovaAjustar" class="btn btn-sm btn-primary" disabled><i class="fa fa-pencil"></i> Ajustar texto</button>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<script type="text/javascript">
$(document).ready(function () {
    // Seletor "adicionar": escolhe a atividade e abre o mesmo modal.
    $('#atvNovaOficina').on('change', function () {
        $('#atvNovaAjustar').prop('disabled', !$(this).val());
    });
    $('#atvNovaAjustar').on('click', function () {
        var $sel = $('#atvNovaOficina');
        var cod = $sel.val();
        if (!cod) { return; }
        abrirModalAtividade(cod, $sel.find('option:selected').data('titulo'), true);
    });
});
</script>
