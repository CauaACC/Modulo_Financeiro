<?php
$pg = isset($fwsGet_['content']) ? $fwsGet_['content'] : '';

if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)
    && !FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_CARGA_TRANSPARENCIA, 5, false, false)) {
    return;
}

$_abaTranspProjetos    = (strpos($pg, 'TransparenciaProjetos') !== false);
$_abaTranspPagamentos  = (strpos($pg, 'TransparenciaPagamentos') !== false);
$_abaTranspPainel      = (strpos($pg, 'DashboardTransparencia') !== false);
$_abaTranspConsulta    = (strpos($pg, 'localizar_pagamentos') !== false);

// Default: projetos
if (!$_abaTranspProjetos && !$_abaTranspPagamentos && !$_abaTranspPainel && !$_abaTranspConsulta) {
    $_abaTranspProjetos = true;
}
?>
<div class="row">
<div class="col-sm-12 submenu-area">
    <style>
        .fin-tab { display:inline-block; padding:7px 16px; font-size:13px; color:#888; text-decoration:none; border-bottom:3px solid transparent; cursor:pointer; margin-right:4px; }
        .fin-tab:hover { color:#555; text-decoration:none; }
        .fin-tab.fin-tab-ativo { color:#333; font-weight:bold; border-bottom:3px solid #333; }
        .fin-tab:visited { color:#888; }
        .fin-tab.fin-tab-ativo:visited { color:#333; }
    </style>
    <div style="border-bottom:1px solid #ddd; margin-bottom:8px; padding-bottom:0;">
        <a class="fin-tab <?= $_abaTranspProjetos ? 'fin-tab-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarCargaTransparenciaProjetos.php">
            <i class="fa fa-folder-open"></i> Carga Projetos
        </a>
        <a class="fin-tab <?= $_abaTranspPagamentos ? 'fin-tab-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarCargaTransparenciaPagamentos.php">
            <i class="fa fa-money"></i> Carga Pagamentos
        </a>
        <a class="fin-tab <?= $_abaTranspPainel ? 'fin-tab-ativo' : '' ?>" href="index.php?content=view/admin/DashboardTransparencia.php">
            <i class="fa fa-dashboard"></i> Painel
        </a>
        <a class="fin-tab <?= $_abaTranspConsulta ? 'fin-tab-ativo' : '' ?>" href="index.php?content=view/transparencia/localizar_pagamentos.php">
            <i class="fa fa-search"></i> Consulta P&uacute;blica
        </a>
    </div>
</div>
</div>
