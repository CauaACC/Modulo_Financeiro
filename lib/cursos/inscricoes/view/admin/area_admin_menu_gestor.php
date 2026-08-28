<?php
/**
 * Submenu do modulo GESTOR -- telas que NAO pertencem a um evento.
 * Visivel so para ADM_GERAL (verificacao GERAL, com cod_evento='TODOS').
 */
$pg = isset($fwsGet_['content']) ? $fwsGet_['content'] : '';

if (!FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
    return;
}

$_abaGestorCertificados = (strpos($pg, 'GerenciarCertificadosGestor') !== false);
$_abaGestorEquipe       = (strpos($pg, 'GerenciarEquipePadraoGestor') !== false);

// Default: certificados
if (!$_abaGestorCertificados && !$_abaGestorEquipe) {
    $_abaGestorCertificados = true;
}
?>
<div class="row">
<div class="col-sm-12 submenu-area">
    <style>
        .gestor-tab { display:inline-block; padding:7px 16px; font-size:13px; color:#888; text-decoration:none; border-bottom:3px solid transparent; cursor:pointer; margin-right:4px; }
        .gestor-tab:hover { color:#555; text-decoration:none; }
        .gestor-tab.gestor-tab-ativo { color:#333; font-weight:bold; border-bottom:3px solid #333; }
        .gestor-tab:visited { color:#888; }
        .gestor-tab.gestor-tab-ativo:visited { color:#333; }
    </style>
    <div style="border-bottom:1px solid #ddd; margin-bottom:8px; padding-bottom:0;">
        <a class="gestor-tab <?= $_abaGestorCertificados ? 'gestor-tab-ativo' : '' ?>" href="<?= WB_URL_VIEW ?>?content=view/admin/GerenciarCertificadosGestor.php">
            <i class="fa fa-certificate"></i> Certificados
        </a>
        <a class="gestor-tab <?= $_abaGestorEquipe ? 'gestor-tab-ativo' : '' ?>" href="<?= WB_URL_VIEW ?>?content=view/admin/GerenciarEquipePadraoGestor.php">
            <i class="fa fa-users"></i> Equipe padr&atilde;o
        </a>
    </div>
</div>
</div>
