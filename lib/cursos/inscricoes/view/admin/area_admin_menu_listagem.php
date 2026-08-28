<div class="row">
<div class="col-sm-12 submenu-area" style="margin-top:6px;margin-left:12px;">

<a href="<?php echo WB_URL_VIEW?>?content=view/admin/evento-cadastro-ok.php" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-list"></span> Listar Eventos/Projetos</a>

<a class='button btn btn-primary btn-xs' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php'
       >Listar Inscritos</a>

<!--<a href="<?php echo WB_URL_VIEW?>?content=view/inscricao/inscricao_01.php#inscricao" target="_blank" rel="noopener noreferrer" onclick="window.open(this.href,'_blank','webkitPrivateBrowsing')">-->
<a href="<?php echo WB_URL_VIEW?>?content=view/inscricao/inscricao_01.php#inscricao" class="button btn btn-primary btn-xs" target="_blank" rel="noopener noreferrer" onclick="return confirm('Se continuar, você será deslogado. Deseja continuar?');">
    <span class="glyphicon glyphicon-eye-open"></span> Iniciar Inscrição</a>

<a href="<?php echo WB_URL_VIEW?>?content=view/admin/evento-cadastro-alterar.php&cod_evento=<?php echo $cod_evento ?>" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-eye-open"></span> Editar Evento</a>

<a href="<?php echo WB_URL_VIEW?>?content=view/admin/evento-configurar-alterar.php&cod_evento=<?php echo $cod_evento ?>" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-eye-open"></span> Configurar Evento</a>

<a href="<?php echo WB_URL_VIEW?>?content=view/admin/perfil-cadastro-ok.php" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-eye-open"></span> Grupos de Participantes</a>

<a href="<?php echo WB_URL_VIEW?>?content=view/admin/oficina-cadastro-ok.php" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-eye-open"></span> Listar de Atividades</a>
<?php if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)) { ?>
    <?php if($objEvConf->getTrabEixoTematico()) { ?>
    <a href="<?php echo WB_URL_VIEW?>?content=view/admin/area-tematica-cadastro-ok.php" class="button btn btn-primary btn-xs">
        <span class="glyphicon glyphicon-eye-open"></span> Listar Eixos</a>
    <?php } ?>
<?php } ?>
</div></div>
