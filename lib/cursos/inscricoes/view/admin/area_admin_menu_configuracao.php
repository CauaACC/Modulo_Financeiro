<div class="row">
<div class="col-sm-12 submenu-area">

       <a class="submenu-btn <?= strpos($pg, 'evento-cadastro-alterar') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/evento-cadastro-alterar.php&cod_evento=<?php echo COD_EVENTO ?>">Editar Evento</a>
       <a class="submenu-btn <?= strpos($pg, 'evento-configurar') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/evento-configurar-alterar.php&cod_evento=<?php echo COD_EVENTO ?>">Configurar Evento</a>
       <?php if ((int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0) === 668) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarEventoDivulgacao') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarEventoDivulgacao.php">Divulga&ccedil;&atilde;o do Evento</a>
       <?php } ?>
       <a class="submenu-btn <?= strpos($pg, 'perfil-cadastro') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/perfil-cadastro-ok.php">Grupos de Participantes</a>
       <a class="submenu-btn <?= strpos($pg, 'oficina-cadastro') !== false || strpos($pg, 'GerenciarGerarSessoes') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/oficina-cadastro-ok.php">Atividades</a>
       <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false) && $objEvConf->getRegistroDiario() == 1) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarLiberacaoRegistro') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarLiberacaoRegistro.php">Libera&ccedil;&atilde;o de Registro</a>
       <?php } ?>
       <?php if($objEvConf->getTrabEixoTematico()) { ?>
       <a class="submenu-btn <?= strpos($pg, 'area-tematica-cadastro') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/area-tematica-cadastro-ok.php">Eixos Tem&aacute;ticos</a>
       <a class="submenu-btn <?= strpos($pg, 'ligacao-convidados-areas-tematicas') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/ligacao-convidados-areas-tematicas-listagem.php">Associar Eixo Tem&aacute;tico</a>
       <?php } ?>
       <?php if($objEvConf->getHabilitarModelosContrato()) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarModeloContrato') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarModeloContrato.php">Modelos de Contrato</a>
       <?php } ?>
       <a class="submenu-btn <?= (strpos($pg, 'PainelCertificados') !== false || strpos($pg, 'ConfiguracaoCertificado') !== false || strpos($pg, 'PecasCertificado') !== false || strpos($pg, 'ProgramasCertificado') !== false || strpos($pg, 'CertificadoPorAtividade') !== false || strpos($pg, 'GerenciarVersoPrograma') !== false || strpos($pg, 'GerenciarInscritoPerfil') !== false) ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/ConfiguracaoCertificado.php">Certificados</a>

</div>
</div>
