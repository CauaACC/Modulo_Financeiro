<div class="row">
<div class="col-sm-12 submenu-area">

       <a class="submenu-btn <?= strpos($pg, 'listagem-inscritos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php">Localizar Cadastrados</a>
       <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_LISTAR_TRABALHOS, 5, false, false) && $objEvConf->getTrabSubmissao() == 1) { ?>
       <!-- <a class="submenu-btn <?= strpos($pg, 'listagem-trabalhos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php">Listar <?php echo $objEvConf->getTrabAnexoTipo()?></a> -->
       <?php } ?>
       <a class="submenu-btn" href="<?= WB_URL_VIEW ?>#inscricao" target="_blank" rel="noopener noreferrer">Iniciar Inscri&ccedil;&atilde;o</a>
       <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_VOUCHER, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarInscritoApto') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarInscritoApto.php">Aptos a Participar</a>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarDesconto') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarDesconto.php">Descontos</a>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarVoucherEvento') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarVoucherEvento.php">Vouchers</a>
       <?php } ?>
       <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_INSCRITO, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarInscritoStatus') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarInscritoStatus.php">Hist. Situação no Processo</a>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarCargaPessoaUpload') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarCargaPessoaUpload.php">Carga de Inscritos</a>
       <?php } ?>
       <?php if ($objEvConf->getEmitirCertificado() == 1
                 && FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_CERTIFICADO, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarInscritoPerfil') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarInscritoPerfil.php">Gerar Certificados</a>
       <?php } ?>
       <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONSULTAR_PAINEL_BORDO, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'consulta-dados-congresso-listagem') !== false && strpos($pg, 'inscritos') === false && strpos($pg, 'trabalhos') === false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/consulta-dados-congresso-listagem.php">Painel de Bordo</a>
       <?php } ?>

</div>
</div>
