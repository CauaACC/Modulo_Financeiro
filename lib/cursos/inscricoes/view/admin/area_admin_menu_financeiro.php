<?php
// Determinar qual aba está ativa baseado na página atual
$_abaFinContab = (strpos($pg, 'GerenciarLancamentos') !== false
    || strpos($pg, 'GerenciarPlanoContas') !== false
    || strpos($pg, 'GerenciarRateios') !== false
    || strpos($pg, 'GerenciarCentroCusto') !== false
    || strpos($pg, 'GerenciarFontesRecurso') !== false
    || strpos($pg, 'GerenciarRelatoriosFinanceiros') !== false);

// Configuracao: o que se ajusta UMA vez e quase nao se volta. Estava
// espalhado — a inicial da contabilidade no meio da operacao contabil, o
// emissor no meio das notas —, competindo com o que se usa todo dia.
$_abaFinConfig = (strpos($pg, 'ConfigurarContabilidade') !== false
    || strpos($pg, 'GerenciarNotaFiscalEmissor') !== false);

$_abaFinInteg = (strpos($pg, 'GerenciarPagamentoUpload') !== false
    || strpos($pg, 'GerenciarItaushoplineUpload') !== false
    || strpos($pg, 'GerenciarRetornoHutec') !== false
    || strpos($pg, 'GerenciarConsultaPagSeguro') !== false
    || strpos($pg, 'GerenciarBoletoAvulso') !== false);

$_abaFinFiscal = (strpos($pg, 'GerenciarNotasFiscais') !== false);

// Se nenhuma aba detectada, default = integrações (mais usado)
if (!$_abaFinContab && !$_abaFinInteg && !$_abaFinFiscal && !$_abaFinConfig) $_abaFinInteg = true;
if ($_abaFinFiscal || $_abaFinConfig) $_abaFinInteg = false;

// Cadastro do emissor e configuracao fiscal da empresa (CNPJ, certificado,
// serie): fica no Financeiro por ser assunto fiscal, mas so ADM_GERAL abre.
$_nfEmissorLiberado = (bool) @FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false);
?>
<div class="row">
<div class="col-sm-12 submenu-area">

<?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)) { ?>

       <style>
              .fin-tab { display:inline-block; padding:7px 16px; font-size:13px; color:#888; text-decoration:none; border-bottom:3px solid transparent; cursor:pointer; margin-right:4px; }
              .fin-tab:hover { color:#555; text-decoration:none; }
              .fin-tab.fin-tab-ativo { color:#333; font-weight:bold; border-bottom:3px solid #333; }
              .fin-tab:visited { color:#888; }
              .fin-tab.fin-tab-ativo:visited { color:#333; }
       </style>
       <div style="border-bottom:1px solid #ddd; margin-bottom:8px; padding-bottom:0;">
              <a class="fin-tab <?= $_abaFinContab ? 'fin-tab-ativo' : '' ?>" data-toggle="tab" href="#tabFinContabilidade">
                     <i class="fa fa-calculator"></i> Contabilidade
                     <span style="font-size:10px; background:#f0ad4e; color:#fff; padding:1px 6px; border-radius:3px; margin-left:4px;">Beta</span>
              </a>
              <a class="fin-tab <?= $_abaFinInteg ? 'fin-tab-ativo' : '' ?>" data-toggle="tab" href="#tabFinIntegracoes">
                     <i class="fa fa-exchange"></i> Integra&ccedil;&otilde;es
              </a>
              <a class="fin-tab <?= $_abaFinFiscal ? 'fin-tab-ativo' : '' ?>" data-toggle="tab" href="#tabFinFiscal">
                     <i class="fa fa-file-text-o"></i> Fiscal
              </a>
              <a class="fin-tab <?= $_abaFinConfig ? 'fin-tab-ativo' : '' ?>" data-toggle="tab" href="#tabFinConfig">
                     <i class="fa fa-cog"></i> Configura&ccedil;&atilde;o
              </a>
       </div>

       <div class="tab-content">
              <div id="tabFinIntegracoes" class="tab-pane <?= $_abaFinInteg ? 'active' : '' ?>">
                     <?php
                     $gwMenu = $objEvConf->getGatewayPagamento();
                     $linkCarga = in_array($gwMenu, ['ITAU-SHOPLINE', 'ITAU-BOLECODE', 'ITAUSHOPLINE']) ? 'GerenciarItaushoplineUpload.php' : 'GerenciarPagamentoUpload.php';
                     ?>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarPagamentoUpload') !== false || strpos($pg, 'GerenciarItaushoplineUpload') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/<?= $linkCarga ?>">Carga Financeiro</a>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarRetornoHutecUpload') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarRetornoHutecUpload.php">Carga Retorno Hutec</a>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarConsultaPagSeguro') !== false ? 'submenu-btn-ativo' : '' ?>" href="<?= WB_URL_VIEW ?>?content=view/admin/GerenciarConsultaPagSeguro.php">Consulta PagSeguro</a>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarBoletoAvulso') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarBoletoAvulso.php">Boleto Avulso</a>
              </div>

              <div id="tabFinContabilidade" class="tab-pane <?= $_abaFinContab ? 'active' : '' ?>">
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarRelatoriosFinanceiros') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarRelatoriosFinanceiros.php"><i class="fa fa-bar-chart"></i> Relat&oacute;rios</a>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarLancamentos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarLancamentosContabeis.php">Lan&ccedil;amentos</a>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarPlanoContas') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarPlanoContas.php">Plano de Contas</a>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarRateios') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarRateios.php">Rateios</a>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarCentroCusto') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarCentroCusto.php">Centro de Custo</a>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarFontesRecurso') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarFontesRecurso.php">Fontes de Recurso</a>
              </div>

              <div id="tabFinFiscal" class="tab-pane <?= $_abaFinFiscal ? 'active' : '' ?>">
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarNotasFiscais') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarNotasFiscais.php">Notas Fiscais</a>
              </div>

              <div id="tabFinConfig" class="tab-pane <?= $_abaFinConfig ? 'active' : '' ?>">
                     <a class="submenu-btn <?= strpos($pg, 'ConfigurarContabilidade') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/ConfigurarContabilidade.php">Contabilidade</a>
                     <?php if ($_nfEmissorLiberado) { ?>
                     <a class="submenu-btn <?= strpos($pg, 'GerenciarNotaFiscalEmissor') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarNotaFiscalEmissor.php">Emissores de NF</a>
                     <?php } ?>
              </div>
       </div>

       <script>
       $(document).ready(function() {
              $('.fin-tab').on('click', function(e) {
                     e.preventDefault();
                     $('.fin-tab').removeClass('fin-tab-ativo');
                     $(this).addClass('fin-tab-ativo');
                     var target = $(this).attr('href');
                     $('.tab-content .tab-pane').removeClass('active');
                     $(target).addClass('active');
              });
       });
       </script>

<?php } else if (FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_CARGA_TRANSPARENCIA, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarCargaTransparencia') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarCargaTransparencia.php">Carga Transpar&ecirc;ncia</a>
       <a class="submenu-btn <?= strpos($pg, 'localizar_pagamentos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/transparencia/localizar_pagamentos.php">Transpar&ecirc;ncia</a>
<?php } ?>


</div>
</div>
