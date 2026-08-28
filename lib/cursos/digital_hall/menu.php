<?php
if (@$_SESSION['_inscrito'] == false) { 
//$url = "/inesco/5congresso/inscricoes/index.php?content=view/inscricao/inscricao_congresso_ok.php";
//@header("Location: $url");
//echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
//exit();
}
?>
<div class="navbar-header">
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>
    <a href="<?php echo URL_BOLETO?>">
        <img src="<?php echo WB_URL_SITE; ?>/resources/image/<?php echo '/logo-60X235px-'.COD_EVENTO.'.png'; ?>" class="logoPrincipal" style="width:251px;"></a>
</div>
    
<!--<a class="tituloSite"      style="text-decoration: none;" href="<?php echo URL_OFICIAL?>"><?php echo DESC_EVENTO ?></a>-->
<!--<a class="tituloSiteAbrev" style="text-decoration: none;" href="<?php echo URL_OFICIAL?>"><?php echo ABREV_EVENTO ?></a>-->
    
<div id="navbar" class="navbar-collapse collapse bottom">
 <?php //If (@$_SESSION['_inscrito'] == false) { ?>
    <ul class="nav navbar-nav">
        <li><a href="<?php echo URL_OFICIAL?>">
                Home</a></li>
        <li><a href="/inesco/5congresso/inscricoes/index.php?content=view/inscricao/inscricao_congresso_ok.php">
                Área do Inscrito</a></li>
        <li><a href="<?php echo URL_HALL?>?content=view/anais/anais_pesquisa.php">
                6º Mostra Paranaense de Pesquisa em Saúde</a></li>
    </ul>
    <?php //}?>            
    <ul class="nav navbar-nav">
        
        <?php
//        if ((substr($content, 0, 10) == "view/admin/")or(substr($content, 0, 14) == "view/avaliacao/")){
            If (@$_SESSION['_logado'] == 1 and @$_SESSION['_administrativo']=='1') {
                ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <b>Olá <?php echo $_SESSION['_nome_usuario']?></b><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <?php if($_SESSION['_administrativo']=='1'){?>
                            <li><a href='<?php echo WB_URL_ACTION ?>?content=view/admin/logout_acao.php'><b>Sair</b></a></li>
                        <?php }else{?>
                            <li><a href='<?php echo WB_URL_ACTION ?>?content=view/inscricao/inscricao_logout_acao.php'><b>Sair</b></a></li>
                        <?php }?>
                        <?php if($_SESSION['_logadoComo'] != ''){?>
                            <li><a href='<?php echo WB_URL_COMMAND ?>&c=GerenciarLogin&m=logoutComoAdministrativo'><b>Sair de logado como</b></a></li>
                        <?php }?>
                    </ul>
                </li>
            <?php } Else {?>
<!--                <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/login.php">
                    <b>Login</b></a>
                </li>-->
            <?php }
//        }
        ?>
        </li>
    </ul>
</div><!--/.nav-collapse -->