<?php
$pg = isset($fwsGet_['content']) ? $fwsGet_['content'] : '';
$_isAdminTransp = (isset($_SESSION['_cod_usuario']) && $_SESSION['_cod_usuario'] != ''
    && (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)
        || FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_CARGA_TRANSPARENCIA, 5, false, false)));

$_isConsultaPublica = (strpos($pg, 'localizar_projetos') !== false || strpos($pg, 'localizar_pagamentos') !== false);
?>
<style>
    .fin-tab { display:inline-block; padding:7px 16px; font-size:13px; color:#888; text-decoration:none; border-bottom:3px solid transparent; cursor:pointer; margin-right:4px; }
    .fin-tab:hover { color:#555; text-decoration:none; }
    .fin-tab.fin-tab-ativo { color:#333; font-weight:bold; border-bottom:3px solid #333; }
    .fin-tab:visited { color:#888; }
    .fin-tab.fin-tab-ativo:visited { color:#333; }
</style>

<!-- Topo -->
<div class="page-header">
    <div class="page-context">&Aacute;rea P&uacute;blica</div>
</div>

<!-- 1o nivel: modulo -->
<div class="row">
<div class="col-sm-12 submenu-area">
    <div class="modulo-nav">
        <div class="modulo-links">
            <a href="index.php?content=view/transparencia/localizar_projetos.php" class="modulo modulo-ativo">Transpar&ecirc;ncia</a>
        </div>
    </div>
</div>
</div>

<!-- 2o nivel: fin-tab -->
<div style="border-bottom:1px solid #ddd; margin-bottom:8px; padding-bottom:0;">
    <a class="fin-tab <?= $_isConsultaPublica ? 'fin-tab-ativo' : '' ?>" href="index.php?content=view/transparencia/localizar_projetos.php">
        <i class="fa fa-search"></i> Consulta P&uacute;blica
    </a>
    <?php if ($_isAdminTransp) { ?>
    <a class="fin-tab" href="index.php?content=view/admin/GerenciarCargaTransparenciaProjetos.php" style="margin-left:20px;">
        <i class="fa fa-lock"></i> &Aacute;rea Administrativa
    </a>
    <?php } ?>
</div>

<!-- 3o nivel: submenu-btn (so aparece quando esta em Consulta Publica) -->
<?php if ($_isConsultaPublica) { ?>
<div class="row">
<div class="col-sm-12 submenu-area">
    <a class="submenu-btn <?= strpos($pg, 'localizar_projetos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/transparencia/localizar_projetos.php">Consulta Projetos</a>
    <a class="submenu-btn <?= strpos($pg, 'localizar_pagamentos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/transparencia/localizar_pagamentos.php">Consulta Pagamentos</a>
</div>
</div>
<?php } ?>
