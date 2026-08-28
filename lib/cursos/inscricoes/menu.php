<div class="navbar-header">
    <div class="d-block d-md-flex flex-wrap align-items-center justify-content-center justify-content-lg-start h-100">
        <div>
            <?php
            $urlHomeMenu = $objEv->getUrlHomeMenu();
            if ($urlHomeMenu=='') {
                $urlHomeMenu = '#';
            }
            if($objEv->getUrlLogo()!=''){
                $urlLogo = $objEv->getUrlLogo();
            }else{
                if($organizacao->getUrlLogo()){
                    $urlLogo = $organizacao->getUrlLogo();
                }else{
                    $urlLogo = WB_URL_SITE;
                }
            }
            if(file_exists('../logn/eventos/105/media/logo-60X235pxN-105.png')){
                executarLog(WB_PATH_LOG_CERTIFICADOS.'../media/logo-60X235pxN-'.COD_EVENTO.'.png'. '-existe',"logoEvento",true);
            }else{ 
                executarLog(WB_PATH_LOG_CERTIFICADOS.'../media/logo-60X235pxN-'.COD_EVENTO.'.png'. '-não-existe',"logoEvento",true);
            }
            ?>
            <a class="d-flex align-items-center my-2 m-md-0 me-lg-auto text-white text-decoration-none m-auto"
               href="<?php echo $urlLogo; ?>" style="width: fit-content">
                <?php if(COD_EVENTO==0 AND file_exists('resources/image/logo-60X235pxN-'. strtoupper($organizacao->getSigla()).'.png')){ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/<?php echo '/logo-60X235pxN-'.strtoupper($organizacao->getSigla()).'.png'; ?>" class="img-responsive" style="height: 70px;">               
                <?php }else if(file_exists('resources/image/logo-60X235pxN-'.COD_EVENTO.'.png')){ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/<?php echo '/logo-60X235pxN-'.COD_EVENTO.'.png'; ?>" class="img-responsive" style="height: 70px;">
                <?php }else if(file_exists(WB_PATH_LOG_CERTIFICADOS.'../media/logo-60X235pxN-'.COD_EVENTO.'.png')){ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/logo-60X235pxN.php" class="img-responsive" style="height: 70px;">
                <?php }else if(file_exists('resources/image/logo-60X235pxN-'.$objEv->getGestor().'.png')){ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/<?php echo '/logo-60X235pxN-'.$objEv->getGestor().'.png'; ?>" class="img-responsive" style="height: 70px;">
                <?php }else if(file_exists('resources/image/logo-60X235pxN-'. strtoupper($organizacao->getSigla()).'.png')){ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/<?php echo '/logo-60X235pxN-'.strtoupper($organizacao->getSigla()).'.png'; ?>" class="img-responsive" style="height: 70px;">
                <?php }else{ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/logo-OPCTEC.png" class="img-responsive" style="height: 70px;" alt="OPCTEC">
                <?php }  ?>
            </a>
        </div>
        <button type="button" class="navbar-toggle collapsed d-flex" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
</div>
    
<!--<a class="tituloSite"      style="text-decoration: none;" href="<?php //echo URL_OFICIAL?>"><?php echo DESC_EVENTO ?></a>-->
<!--<a class="tituloSiteAbrev" style="text-decoration: none;" href="<?php //echo URL_OFICIAL?>"><?php echo ABREV_EVENTO ?></a>-->
    
<div id="navbar" class="navbar-collapse collapse bottom" style="margin-top: 10px;">
    <ul class="nav navbar-nav">
 <?php //If (@$_SESSION['_inscrito'] == true) { ?>
        <?php if ($OP_EVENTO_ONLINE_LIBERADO==true){ ?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/inscricao/inscricao_congresso_hall_virtual.php#inscricao">
                <i class='fa fa-film'></i> Salas e Sessões Virtuais</a></li>
        <?php } ?>      
        <?php
        // Fluxo AGENDA: quem chega precisa decidir entre "agendar visita" (nova
        // reserva, sem CPF inicial) e "acessar minhas agendas" (login). No
        // restante dos fluxos mantem o link unico "Area do Inscrito".
        if (isset($OP_TIPO_FLUXO_INSCRICAO) && $OP_TIPO_FLUXO_INSCRICAO == 'AGENDA'
            && @$_SESSION['_logado'] != '1'
            && @$_SESSION['_inscrito'] != '1') {
            ?>
            <li><a href="<?php echo WB_URL_VIEW?>?content=view/inscricao/agenda_passo1_dia.php&amp;novo=1">
                    <i class='fa fa-calendar'></i> Agendar visita</a></li>
            <li><a href="<?php echo WB_URL_VIEW?>?content=view/inscricao/inscricao_01.php&amp;login=1">
                    <i class='fa fa-user'></i> Acessar minhas agendas</a></li>
        <?php } else { ?>
            <li><a href="<?php echo WB_URL_VIEW?>?content=view/inscricao/inscricao_congresso_ok.php#inscricao">
                    <i class='fa fa-user'></i> Área do Inscrito</a></li>
        <?php } ?>
        
        <?php If (@$_SESSION['_inscrito'] == true) { ?>        
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/inscricao/inscricao_eventos.php">
                Meus Eventos/Cursos</a></li>                
        <?php } ?>        
        
        <?php If ($_SESSION['_administrativo']!=1) { ?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/default.php#adminSistema">
                Administração</a></li>
         <?php } ?>
        <!--<li><a href="<?php  echo URL_BOLETO?>">-->
                <!--<i class='fa fa-home'></i> Home</a></li>-->
    <!--</ul>-->
 <?php //}?>
 <?php If ($_SESSION['_administrativo']==1) { ?>
        <?php //if ($objEvConf->getTrabSubmissao()==1) { ?>
            <?php
            $liberaMenuAvaliacaoTrabalho = false;
            $liberaMenuAvaliacaoTrabalhoPremiacao = false;
            if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_TRABALHO, enumDireito::DIR_ADMINISTRAR, false, false)) {
                $liberaMenuAvaliacaoTrabalho = true;
            }
            if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_TRABALHO_PREMIADO, enumDireito::DIR_ADMINISTRAR, false, false)) {
                $liberaMenuAvaliacaoTrabalhoPremiacao = true;
            }
            if ($liberaMenuAvaliacaoTrabalho or $liberaMenuAvaliacaoTrabalhoPremiacao) {
            ?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    Avaliadores <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <?php
                    if ($liberaMenuAvaliacaoTrabalho) {
                        echo "<li><a href='".WB_URL_VIEW."?content=view/avaliacao/avaliacao-trabalhos-apresentacao.php#avalTrabalhos'>
                                Avaliação de Trabalhos</a></li>";

                    }
                    if ($liberaMenuAvaliacaoTrabalhoPremiacao) {
                        // echo "<li><a href='".WB_URL_VIEW."?content=view/avaliacao_premiacao/avaliacao-trabalhos-apresentacao.php#avalTrabalhos'>
                        //         Avaliação de Trabalhos - Premiação</a></li>"; 
                    }
                    ?>
                </ul>
            </li>
            <?php 
            }
        //}
        ?>
        <?php If (@$_SESSION['_administrativo'] == "" or @$_SESSION['_logado']==false or !isset($naoMostrar)) {  //?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/default.php#adminSistema">
                Administração</a></li>
        <?php }else if ($_SESSION['_administrativo']) {?>
            <li class="dropdown">
                <a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php#adminSistema"
                   class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    Administração. <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <!-- <?php //if ($objEvConf->getTrabSubmissao()==1) { ?>
                    <li><a href="#">Avaliação de Trabalhos</a></li>
                    <?php //} ?>
                    <li role="separator" class="divider"></li>
                    <li><a href="#">Financeiro</a></li>
                    <li><a href="#">Relatórios</a></li>
                    <li><a href="#">Cadastros</a></li>
                    
                    <li class="dropdown-header">Nav header</li>
                    <li><a href="#">Separated link</a></li>
                    <li><a href="#">One more separated link</a></li> -->
                    <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/default.php#adminSistema">
                    Menu Geral</a></li>
                </ul>
            </li>
        <?php }?>

    </ul>
    <?php }?>            
    <ul class="nav navbar-nav">
        <?php
//        if ((substr($content, 0, 10) == "view/admin/")or(substr($content, 0, 14) == "view/avaliacao/")){
            If (@$_SESSION['_logado'] == 1 and @$_SESSION['_administrativo']=='1') {
                ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <b>Olá <?php echo  $_SESSION['_login']?></b><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <?php if($_SESSION['_administrativo']=='1'){?>
                            <li><a href='<?php echo WB_URL_VIEW ?>?content=view/admin/minha-conta.php'><i class="fa fa-user"></i> Minha Conta</a></li>
                            <li class="divider"></li>
                            <li><a href='<?php echo WB_URL_ACTION ?>?content=view/admin/logout_acao.php'><b>Sair</b></a></li>
                        <?php }else{?>
                            <li><a href='<?php echo WB_URL_ACTION ?>?content=view/inscricao/inscricao_logout_acao.php'><b>Sair</b></a></li>
                        <?php }?>
                        <?php if(isset($_SESSION['_logadoComo']) and $_SESSION['_logadoComo'] != ''){?>
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
<?php if (isset($_SESSION['_logadoComo']) && $_SESSION['_logadoComo'] == 'administrativo') {
    $segRestantes = !empty($_SESSION['_logadoComoExpira']) ? max(0, $_SESSION['_logadoComoExpira'] - time()) : 0;
    $urlLogoutLogarComo = WB_URL_COMMAND . '&c=GerenciarLogin&m=logoutComoAdministrativo';

    // Se o tempo ja expirou ate aqui, redireciona imediatamente para logout (sem renderizar a barra)
    if ($segRestantes <= 0) {
        echo '<script>window.location.href=' . json_encode($urlLogoutLogarComo) . ';</script>';
        return;
    }
    $minRestantes = (int) ceil($segRestantes / 60);
?>
<div id="barraLogadoComo" style="position:fixed; top:0; left:0; right:0; z-index:99999; background:#d9534f; color:#fff; text-align:center; padding:6px 10px; font-size:13px; font-weight:bold; box-shadow:0 2px 6px rgba(0,0,0,0.3);">
    <i class="fa fa-exclamation-triangle"></i>
    Logado como: <u><?php echo htmlspecialchars($_SESSION['_login'])?></u>
    (<?php echo htmlspecialchars($_SESSION['_nome_usuario'])?>)
    &mdash; Expira em <span id="minLogadoComo"><?php echo $minRestantes?></span> min
    &nbsp;
    <a href="<?php echo $urlLogoutLogarComo ?>"
       style="color:#fff; background:#c9302c; padding:3px 12px; border-radius:3px; text-decoration:none; margin-left:10px;">
        <i class="fa fa-sign-out"></i> Voltar ao meu usu&aacute;rio
    </a>
</div>
<style>body { padding-top: 40px !important; }</style>
<script>
(function(){
    // Countdown client-side da barra "logar como": atualiza o contador
    // a cada 30s e redireciona pra logout quando chega a 0.
    var segRestantes = <?php echo (int)$segRestantes ?>;
    var urlLogout    = <?php echo json_encode($urlLogoutLogarComo) ?>;
    var spanMin = document.getElementById('minLogadoComo');

    function atualizar() {
        segRestantes -= 30;
        if (segRestantes <= 0) {
            window.location.href = urlLogout;
            return;
        }
        if (spanMin) {
            spanMin.innerText = Math.ceil(segRestantes / 60);
        }
    }
    // Atualiza a cada 30s (mais granular que o min visivel, garante redirect proximo do limite)
    setInterval(atualizar, 30000);
})();
</script>
<?php } ?>

<?php
// Modal "Logar Como" — só renderiza se logado com permissão SIS_LOGAR_COMO
if (!empty($_SESSION['_logado']) && $_SESSION['_logado'] == 1
    && !empty($_SESSION['_administrativo']) && $_SESSION['_administrativo'] == '1'
    && !empty($_SESSION['_cod_usuario'])
    && FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_LOGAR_COMO, enumDireito::DIR_ADMINISTRAR, false, false)
) {
?>
<!-- Modal e funcao para "logar como" admin (POST + confirmacao) -->
<div class="modal fade" id="modalLogarComo" tabindex="-1" role="dialog" style="z-index:99999; display:none;">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" style="color:#fff;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-user-secret"></i> Logar Como</h4>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja logar como o usu&aacute;rio:</p>
                <p><strong id="loginLogarComo"></strong></p>
                <p class="text-muted" style="font-size:12px;">Esta a&ccedil;&atilde;o ser&aacute; registrada no log de auditoria. A sess&atilde;o expira em 30 minutos.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="executarLogarComo()">
                    <i class="fa fa-sign-in"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
<form id="formLogarComo" method="POST" action="<?php echo WB_URL_COMMAND?>" style="display:none;">
    <input type="hidden" name="c" value="GerenciarLogin">
    <input type="hidden" name="m" value="loginComoAdministrativo">
    <input type="hidden" name="login" id="inputLoginLogarComo" value="">
    <input type="hidden" name="_submit_check" value="1">
</form>
<script>
function confirmarLogarComo(login) {
    document.getElementById('loginLogarComo').textContent = login;
    document.getElementById('inputLoginLogarComo').value = login;

    // Mover modal e form para body (evita ficar preso dentro de container com z-index)
    var modalEl = document.getElementById('modalLogarComo');
    var formEl = document.getElementById('formLogarComo');
    if (modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }
    if (formEl.parentNode !== document.body) {
        document.body.appendChild(formEl);
    }

    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else if (typeof $ !== 'undefined') {
            $('#modalLogarComo').modal('show');
        }
    } catch(e) {
        if (typeof $ !== 'undefined') {
            $('#modalLogarComo').modal('show');
        }
    }
}
function executarLogarComo() {
    document.getElementById('formLogarComo').submit();
}
</script>
<?php } // fim modalLogarComo ?>