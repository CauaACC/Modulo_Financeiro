<div class="row">
<div class="col-sm-12 submenu-area">

<?php /* Modulo comercial: so em evento classificado como Loja (aba Projeto > Tipo de projeto) */ ?>
<?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)
       && $objEvConf->getTipoProjeto() == 'LOJA') { ?>

       <a class="submenu-btn <?= strpos($pg, 'GerenciarVendas') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarVendas.php">Vendas</a>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarProdutos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarProdutos.php">Produtos</a>

<?php } ?>
<?php /* Emissores de NF saiu daqui: virou Financeiro > Fiscal (configuracao fiscal da empresa, so ADM_GERAL) */ ?>

</div>
</div>
