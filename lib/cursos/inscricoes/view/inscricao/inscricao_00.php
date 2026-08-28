<?php
$iphone  = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$ipad    = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
$palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
$berry   = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
$ipod    = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
$symbian = strpos($_SERVER['HTTP_USER_AGENT'],"Symbian");

if ($iphone || $ipad || $ipod == true) {
    //echo "Olá, sou um iphone";
} else 
if ($android == true) {
//    header('Location: https://play.google.com/store/apps/details?id=dev.inesco.congresso');
//    exit();
    //echo "Olá, sou um android";
} else {
   //echo "Olá, eu sou um computador";
}
?>
<style>
    .fa-4x{
        color: #1cb097;
    }
    .txtIcons {
        color: #1cb097;
    }
    
</style>
        
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="col-12 text-center" style="padding: 20px;">
                <?php if ($NAO_MOSTRAR) { ?>
                <div class="row col-12" style="padding: 20px;">
                    <div class="text-center">
                        <a href="http://congressosaudepublica.org.br/2020/inscricoes/">
                            <i class="fa fa-user fa-4x"></i> <br>2ª Etapa de Inscrições
                            <br>Período de 1º de Junho
                            <br> a 7 de julho de 2020
                        </a>
                    </div>
                </div>               
                <div class="row col-12" style="padding: 20px;">
                    <div class="col-xs-6 col-sm-6 text-center">
                        <a href="?content=view/inscricao/inscricao_01.php">
                            <i class="fa fa-user fa-4x"></i> <br><span class="txtIcons">Inscrição como Ouvinte<br>até 12 de julho de 2020</span>
                        </a>
                    </div>
                    <div class="col-xs-6 col-sm-6 text-center">
                        <a href="https://plataformafrezarineventos.com.br/p/ao-vivo">
                            <i class="fa fa-video-camera fa-4x"></i> <br><span class="txtIcons">Plataforma Digital (ao vivo)</span>
                        </a>
                    </div>
                    <div class="col-xs-6 col-sm-6  text-center">
                        <a href="?content=view/inscricao/inscricao_01.php">
                        <i class="fa fa-id-card fa-4x"></i> <br><span class="txtIcons">Já estou inscrito</span>
                        </a>
                    </div>
                </div>
                <?php } ?>
                <div class="row col-12" style="padding: 20px;">
                    <div class="text-center">
                        <a href="?content=view/inscricao/inscricao_01.php">
                        <i class="fa fa-id-card fa-4x"></i> <br><span class="txtIcons">Já estou inscrito</span>
                        </a>
                    </div>
                </div>   
                <div class="row col-12" style="padding: 20px;">
                    <div class="col-xs-6 col-sm-6  text-center">
                        <a href="http://congressosaudepublica.org.br/2020/conferencia/">
                            <i class="fa fa-newspaper-o fa-4x"></i> <br><span class="txtIcons">Conferências </span>
                        </a>
                    </div>
                    <div class="col-xs-6 col-sm-6  text-center">
                        <a href="http://congressosaudepublica.org.br/2020/minicursos/">
                        <i class="fa fa-newspaper-o fa-4x"></i> <br><span class="txtIcons">Minicursos</span>
                        </a>
                    </div>
                </div>
                <div class="row col-12" style="padding: 20px;">
                    <div class="col-xs-6 col-sm-6  text-center">
                        <a href="http://congressosaudepublica.org.br/2020/mesas-redondas/">
                            <i class="fa fa-newspaper-o fa-4x"></i> <br><span class="txtIcons">Mesas Redondas </span>
                        </a>
                    </div>
                    <div class="col-xs-6 col-sm-6  text-center">
                        <a href="http://congressosaudepublica.org.br/2020/paineis-interativos/">
                        <i class="fa fa-newspaper-o fa-4x"></i> <br><span class="txtIcons">Painéis Interativos</span>
                        </a>
                    </div>
                </div>
                <div class="row col-12" style="padding: 20px;">
                    <div class="col-xs-6 col-sm-6  text-center">
                        <a href="http://congressosaudepublica.org.br/2020">
                            <i class="fa fa-calendar fa-4x"></i> <br><span class="txtIcons">Programação Geral</span>
                        </a>
                    </div>
                    <div class="col-xs-6 col-sm-6  text-center">
                        <a href="http://congressosaudepublica.org.br/2020/noticias/">
                        <i class="fa fa-newspaper-o fa-4x"></i> <br><span class="txtIcons">Notícias</span>
                        </a>
                    </div>
                </div>
<!--                <div class="row col-12" style="padding: 20px;">
                    <div class="col-xs-6 col-sm-6  text-center">
                        <i class="fa fa-cutlery fa-4x"></i> <br><span class="txtIcons">Alimentação</span>
                    </div>
                    <div class="col-xs-6 col-sm-6  text-center">
                        <i class="fa fa-bed fa-4x"></i> <br><span class="txtIcons">Hospedagens</span>
                    </div>
                </div>-->
                <div class="row col-12" style="padding: 20px;">
                    <div class="text-center">
                        <a href="http://congressosaudepublica.org.br/2020/2020/04/29/organizadores-e-promotores-informam-sobre-ajustes-no-5o-congresso-data-esta-mantida-mas-o-evento-sera-virtual/">
                           Saiba mais sobre o novo formato
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
