<?php
// Gate de seguranca da view (mesmo padrao do Painel/Regras).
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarConfiguracaoCertificadoAC();
$debug = isset($debug) ? $debug : false;

// Config atual (aba Padrao) e atividades (aba Por atividade).
$objEvConf  = $objSRV->carregar($debug);
$atividades = (new PainelCertificadosAC())->atividadesCertificado($debug);
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
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Textos do Certificado</h4>
        <small class="text-muted">O que est&aacute; escrito no certificado. As regras ficam na aba <b>Regras</b></small>
    </div>

    <div style="padding:16px 20px;">
        <?php
        // Helpers locais (mesmos da tela de Regras).
        function tc_sim($v) { return ((string) $v === '1') ? 'checked' : ''; }
        function tc_nao($v) { return ((string) $v === '1') ? '' : 'checked'; }
        function tc_val($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
        function tc_lin_radio($rot, $name, $val) {
            echo '<div class="cc-row"><div class="cc-lab">' . $rot . '</div><div class="cc-ctl">'
               . '<label class="opt"><input type="radio" name="' . $name . '" value="1" ' . tc_sim($val) . '> Sim</label>'
               . '<label class="opt"><input type="radio" name="' . $name . '" value="0" ' . tc_nao($val) . '> N&atilde;o</label>'
               . '</div></div>';
        }
        function tc_lin_texto($rot, $name, $val) {
            echo '<div class="cc-row"><div class="cc-lab">' . $rot . '</div><div class="cc-ctl">'
               . '<input type="text" id="' . $name . '" name="' . $name . '" class="form-control" maxlength="255" value="' . tc_val($val) . '">'
               . '</div></div>';
        }
        ?>
        <style>
            .cc-card { border:1px solid #e3ebf3; border-radius:8px; padding:6px 16px; margin-bottom:14px; }
            .cc-card.cinza { border-left:4px solid #999; }
            .cc-card.laranja { border-left:4px solid #d9a441; }
            .cc-tit { font-weight:600; color:#333; padding:8px 0 4px; }
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
            /* abas */
            .tc-abas { display:flex; gap:6px; border-bottom:1px solid #e8e8e8; margin-bottom:16px; }
            .tc-aba { padding:8px 18px; border:1px solid transparent; border-bottom:0; border-radius:8px 8px 0 0;
                      cursor:pointer; font-size:14px; color:#555; background:none; margin-bottom:-1px; }
            .tc-aba:hover { color:#2c5f8d; }
            .tc-aba.ativa { background:#fff; border-color:#e8e8e8; color:#2c5f8d; font-weight:600; }
            .tc-painel { display:none; }
            .tc-painel.ativo { display:block; }
        </style>

        <div class="tc-abas">
            <button type="button" class="tc-aba ativa" data-painel="padrao">Padr&atilde;o</button>
            <button type="button" class="tc-aba" data-painel="atividade">Por atividade</button>
        </div>

        <!-- ================= ABA: TEXTO PADRAO ================= -->
        <div id="painel-padrao" class="tc-painel ativo">
            <form id="formTextosCert" onsubmit="return false;">

                <div class="cc-card cinza">
                    <div class="cc-tit">Textos do certificado</div>
                    <?php
                    tc_lin_texto('Substituir &ldquo;na qualidade de&rdquo;', 'certificado_texto_qualidade', $objEvConf->getCertificadoTextoQualidade());
                    tc_lin_texto('Substituir &ldquo;CONFERIDO A&rdquo;', 'certificado_texto_inicial', $objEvConf->getCertificadoTextoInicial());
                    tc_lin_texto('Substituir &ldquo;CONFERIDO ao TRABALHO&rdquo;', 'certificado_texto_inicial_trabalho', $objEvConf->getCertificadoTextoInicialTrabalho());
                    tc_lin_texto('Texto &ldquo;No / Na&rdquo;', 'certificado_texto_no', $objEvConf->getCertificadoTextoNo());
                    tc_lin_texto('Texto &ldquo;Do / Da&rdquo;', 'certificado_texto_do', $objEvConf->getCertificadoTextoDo());
                    tc_lin_texto('Substituir &ldquo;Realizado&rdquo;', 'certificado_texto_reallizado', $objEvConf->getCertificadoTextoReallizado());
                    tc_lin_texto('Promovido por <span class="hint">(aparece se preenchido)</span>', 'certificado_promovido_por', $objEvConf->getCertificadoPromovidoPor());
                    ?>
                </div>

                <div class="cc-card laranja">
                    <div class="cc-tit">Congressista</div>
                    <?php
                    tc_lin_texto('Substituir &ldquo;MEMBRO EFETIVO&rdquo;', 'certificado_congressista_qualidade', $objEvConf->getCertificadoCongressistaQualidade());
                    tc_lin_texto('Substituir &ldquo;Carga Hor&aacute;ria: 15h&rdquo;', 'certificado_congressista_carga_horaria', $objEvConf->getCertificadoCongressistaCargaHoraria());
                    ?>
                </div>

                <div style="margin-top:6px;">
                    <button type="button" id="btnSalvarTextosCert" class="btn btn-primary">
                        <i class="fa fa-save"></i> Salvar textos
                    </button>
                </div>
            </form>
        </div>

        <!-- ================= ABA: POR ATIVIDADE ================= -->
        <div id="painel-atividade" class="tc-painel">
            <?php require(dirname(__FILE__) . "/certificado-por-atividade-conteudo.php"); ?>
        </div>
    </div>
</div>

<!-- Modal de edicao do certificado da atividade (mesmo do Painel) -->
<?php require_once(dirname(__FILE__) . "/../../view/admin/certificado-atividade-modal.php"); ?>

<script type="text/javascript">
$(document).ready(function () {
    // --- abas ---
    // O hash da URL guarda qual aba esta aberta: salvar uma atividade recarrega
    // a pagina (location.reload() no modal), e sem o hash o reload sempre volta
    // pra aba Padrao -- perdendo o lugar de quem estava editando "Por atividade".
    function abrirAba(nome, atualizarHash) {
        $('.tc-aba').removeClass('ativa').filter('[data-painel="' + nome + '"]').addClass('ativa');
        $('.tc-painel').removeClass('ativo');
        $('#painel-' + nome).addClass('ativo');
        if (atualizarHash !== false && history.replaceState) {
            var hash = (nome === 'atividade') ? '#por-atividade' : '';
            history.replaceState(null, '', window.location.pathname + window.location.search + hash);
        }
    }
    $('.tc-aba').on('click', function () { abrirAba($(this).data('painel')); });
    // Abre direto na aba de atividades: link externo (...#por-atividade) ou
    // reload apos salvar uma atividade (mesmo hash, gravado no clique acima).
    if (window.location.hash === '#por-atividade') { abrirAba('atividade', false); }

    // --- salvar so os textos padrao ---
    $('#btnSalvarTextosCert').on('click', function () {
        var dados = {
            'c': 'GerenciarConfiguracaoCertificado', 'm': 'gravar', '_submit_check': '1'
        };
        $('#formTextosCert').find('input, select, textarea').each(function () {
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
