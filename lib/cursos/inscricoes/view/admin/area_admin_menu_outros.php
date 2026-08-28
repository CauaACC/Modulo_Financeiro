<div class="row">
<div class="col-sm-12 submenu-area">

       <a class="submenu-btn <?= strpos($pg, 'GerenciarAssociado') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarAssociado.php">Associados</a>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarInteressado') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarInteressado.php">Inscritos Interessados</a>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarMidiaUpload') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarMidiaUpload.php">M&iacute;dia Upload</a>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarEtapa') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarEtapa.php">Etapas</a>
<?php /* Feriado e Calendario Base: coordenador ou admin geral (mesmo gate dos controllers). */
if (FWA_temPermissao(array(enumSistema::SIS_PEDAGOGICO, enumSistema::SIS_GERENCIAR_ADM_GERAL))) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarFeriado') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarFeriado.php">Feriados</a>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarCalendarioBase') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarCalendarioBase.php">Calend&aacute;rio Base</a>
<?php } ?>
<?php /* Proposta do evento: substitui a antiga tela "Projetos" (GerenciarProjeto),
         que gravava em tab_projetos — tabela que nao existe no banco. O coordenador
         do evento monta a propria proposta, dai a permissao mais aberta. */
if (FWA_temPermissao(array(enumSistema::SIS_GERENCIAR_EVENTO, enumSistema::SIS_GERENCIAR_ADM_GERAL))) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarProposta') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarProposta.php">Proposta do Evento</a>
<?php } ?>


</div>
</div>
