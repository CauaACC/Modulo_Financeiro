<?php
$pg = isset($fwsGet_['content']) ? $fwsGet_['content'] : '';
?>
<div class="row">
<div class="col-sm-12 submenu-area">
<?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_VOUCHER, 5, $cod_msg, false)) { ?>
    <a class="submenu-btn <?= strpos($pg, 'apresentacao') !== false ? 'submenu-btn-ativo' : '' ?>"
       href="index.php?content=view/coordenador/apresentacao.php">Apresenta&ccedil;&atilde;o</a>
    <a class="submenu-btn <?= strpos($pg, 'GerenciarProjetos') !== false ? 'submenu-btn-ativo' : '' ?>"
       href="index.php?content=view/coordenador/GerenciarProjetos.php">Projetos</a>
<?php } ?>
</div>
</div>
