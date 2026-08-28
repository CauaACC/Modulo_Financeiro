<?php
// Gate de seguranca da view (mesmo padrao do Painel/Verso).
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarConfiguracaoCertificadoAC();
$debug = isset($debug) ? $debug : false;

// Carrega a config atual para o formulario reaproveitado popular os campos.
$objEvConf = $objSRV->carregar($debug);
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
?>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Regras do Certificado</h4>
        <small class="text-muted">Quem recebe, quando libera e o que exige. Os textos ficam na aba <b>Textos</b></small>
    </div>

    <div style="padding:16px 20px;">
        <?php
        // Helpers locais de formatacao (sim/nao e valor de texto).
        function cc_sim($v) { return ((string) $v === '1') ? 'checked' : ''; }
        function cc_nao($v) { return ((string) $v === '1') ? '' : 'checked'; }
        function cc_val($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
        ?>
        <style>
            .cc-card { border:1px solid #e3ebf3; border-radius:8px; padding:6px 16px; margin-bottom:14px; }
            .cc-card.azul    { border-left:4px solid #4a90d9; }
            .cc-card.verde   { border-left:4px solid #5cb85c; }
            .cc-card.laranja { border-left:4px solid #d9a441; }
            .cc-card.cinza   { border-left:4px solid #999; }
            .cc-tit { font-weight:600; color:#333; padding:8px 0 4px; }
            /* linha: label a esquerda, controle a direita, alinhados */
            .cc-row { display:flex; align-items:center; gap:16px; padding:7px 0; border-top:1px solid #f2f2f2; }
            .cc-row:first-of-type { border-top:0; }
            .cc-row > .cc-lab { flex:0 0 44%; max-width:44%; font-size:13px; color:#555; }
            .cc-row > .cc-lab .hint { color:#999; font-size:12px; }
            .cc-row > .cc-ctl { flex:1 1 auto; }
            .cc-row > .cc-ctl .form-control { width:100%; max-width:420px; }
            .cc-row .opt { font-weight:normal; margin-right:18px; cursor:pointer; white-space:nowrap; }
            @media (max-width:640px){
                .cc-row { flex-direction:column; align-items:flex-start; gap:4px; }
                .cc-row > .cc-lab { flex-basis:auto; max-width:100%; }
            }
        </style>

        <?php
        // Helpers de linha: radio Sim/Nao e input de texto, ja no layout 2 colunas.
        function cc_lin_radio($rot, $name, $val) {
            echo '<div class="cc-row"><div class="cc-lab">' . $rot . '</div><div class="cc-ctl">'
               . '<label class="opt"><input type="radio" name="' . $name . '" value="1" ' . cc_sim($val) . '> Sim</label>'
               . '<label class="opt"><input type="radio" name="' . $name . '" value="0" ' . cc_nao($val) . '> N&atilde;o</label>'
               . '</div></div>';
        }
        function cc_lin_texto($rot, $name, $val, $tipo = 'text') {
            echo '<div class="cc-row"><div class="cc-lab">' . $rot . '</div><div class="cc-ctl">'
               . '<input type="' . $tipo . '" id="' . $name . '" name="' . $name . '" class="form-control" maxlength="255" value="' . cc_val($val) . '">'
               . '</div></div>';
        }
        ?>

        <form id="formConfigCert" onsubmit="return false;">

            <div class="cc-card azul">
                <div class="cc-tit">Libera&ccedil;&atilde;o</div>
                <?php
                cc_lin_radio('Liberar certificado', 'emitir_certificado', $objEvConf->getEmitirCertificado());
                cc_lin_texto('Data de libera&ccedil;&atilde;o', 'certificado_dat_ini', $objEvConf->getCertificadoDatIni(), 'datetime-local');
                cc_lin_texto('Data de emiss&atilde;o', 'certificado_data_emissao', substr($objEvConf->getCertificadoDataEmissao(), 0, 10), 'date');
                cc_lin_radio('Exigir avalia&ccedil;&atilde;o do evento antes de emitir', 'liberar_avaliar_evento', $objEvConf->getLiberarAvaliarEvento());
                cc_lin_texto('URL do formul&aacute;rio de avalia&ccedil;&atilde;o', 'url_avaliar_evento', $objEvConf->getUrlAvaliarEvento());
                ?>
            </div>

            <div class="cc-card azul">
                <div class="cc-tit">Assinaturas</div>
                <?php
                cc_lin_radio('Selo na frente', 'certificado_selo', $objEvConf->getCertificadoSelo());
                cc_lin_radio('Duas assinaturas', 'certificado_assinatura_dupla', $objEvConf->getCertificadoAssinaturaDupla());
                ?>
                <div class="cc-row"><div class="cc-lab" style="flex-basis:100%; max-width:100%;">
                    <span class="hint">As artes das assinaturas ficam em
                    <a href="index.php?content=view/admin/PecasCertificado.php">Assinaturas</a>.</span>
                </div></div>
            </div>

            <div class="cc-card verde">
                <div class="cc-tit">Exibi&ccedil;&atilde;o</div>
                <?php
                // Liga/desliga o trecho; a REDACAO dele fica na aba Textos.
                cc_lin_radio('Exibir &ldquo;na qualidade de &hellip;&rdquo;', 'certificado_exibir_qualidade', $objEvConf->getCertificadoExibirQualidade());
                ?>
                <div class="cc-row"><div class="cc-lab" style="flex-basis:100%; max-width:100%;">
                    <span class="hint">O texto que substitui &ldquo;na qualidade de&rdquo; fica em
                    <a href="index.php?content=view/admin/TextosCertificado.php">Textos</a>.</span>
                </div></div>
            </div>

            <div class="cc-card laranja">
                <div class="cc-tit">Congressista &amp; Trabalhos</div>
                <?php
                cc_lin_radio('Liberar certificado de congressista/participante a todos', 'certificado_congressista', $objEvConf->getCertificadoCongressista());
                cc_lin_radio('Liberar publica&ccedil;&atilde;o p/ aprovados n&atilde;o apresentados', 'certificado_trabalho_nao_apresentado', $objEvConf->getCertificadoTrabalhoNaoApresentado());
                ?>
                <div class="cc-row"><div class="cc-lab" style="flex-basis:100%; max-width:100%;">
                    <span class="hint">Os textos que substituem &ldquo;MEMBRO EFETIVO&rdquo; e &ldquo;Carga Hor&aacute;ria: 15h&rdquo;
                    ficam em <a href="index.php?content=view/admin/TextosCertificado.php">Textos</a>.</span>
                </div></div>
            </div>

            <div style="margin-top:6px;">
                <button type="button" id="btnSalvarConfigCert" class="btn btn-primary">
                    <i class="fa fa-save"></i> Salvar regras
                </button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {
    $('#btnSalvarConfigCert').on('click', function () {
        // Coleta todos os campos do form (os names sao os das colunas).
        var dados = {
            'c': 'GerenciarConfiguracaoCertificado', 'm': 'gravar', '_submit_check': '1'
        };
        $('#formConfigCert').find('input, select, textarea').each(function () {
            var $el = $(this);
            var nome = $el.attr('name');
            if (!nome) { return; }
            if ($el.is(':radio')) {
                if ($el.is(':checked')) { dados[nome] = $el.val(); }
            } else {
                dados[nome] = $el.val();
            }
        });

        if (typeof startLoading === 'function') { startLoading(); }
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados,
            function (data) {
                if (typeof processJson === 'function') {
                    processJson(data);
                } else if (data && data.statusMessage) {
                    alert(data.message || 'Salvo.');
                } else {
                    alert((data && data.message) || 'Não foi possível salvar.');
                }
            }, 'json'
        ).always(function () {
            if (typeof stopLoading === 'function') { stopLoading(); }
        });
    });
});
</script>
