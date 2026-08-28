<div class="row">
<div class="col-sm-12 submenu-area">
       <a class="submenu-btn <?= (strpos($pg, 'evento-cadastro-ok') !== false || strpos($pg, 'evento-cadastro-listar') !== false) ? 'submenu-btn-ativo' : '' ?>" href="<?= WB_URL_VIEW ?>?content=view/admin/evento-cadastro-ok.php">Listar Projetos/Eventos</a>
       <a class="submenu-btn <?= strpos($pg, 'evento-cadastro-incluir') !== false ? 'submenu-btn-ativo' : '' ?>" href="<?= WB_URL_VIEW ?>?content=view/admin/evento-cadastro-incluir.php">Incluir Projeto/Evento</a>
       <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarConsultaPagSeguro') !== false ? 'submenu-btn-ativo' : '' ?>" href="<?= WB_URL_VIEW ?>?content=view/admin/GerenciarConsultaPagSeguro.php">Consulta PagSeguro</a>
       <?php } ?>
</div>
</div>
