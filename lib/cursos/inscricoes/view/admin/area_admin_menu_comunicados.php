<div class="row">
<div class="col-sm-12 submenu-area">

<?php /* Email Templates: o controller exige ADM_GERAL no evento -- o menu segue o mesmo gate */ ?>
<?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) { ?>

       <a class="submenu-btn <?= strpos($pg, 'EmailTemplate') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarEmailTemplate.php">Email Templates</a>

<?php } ?>
<?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_CONFIGURACAO, 5, false, false)) { ?>

       <a class="submenu-btn <?= strpos($pg, 'MsgAviso') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarMsgAviso.php">Avisos</a>

<?php } ?>
<?php if (FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) { ?>

       <a class="submenu-btn <?= strpos($pg, 'EmailFila') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarEmailFila.php">Histórico de E-mails</a>

<?php } ?>

</div>
</div>
