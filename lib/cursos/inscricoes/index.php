<?php
require_once(dirname(__FILE__)."/../inscricoes/include/config.php");

/**
 * BOOTSTRAP 5 + jQuery 3 — Chave de ativação
 *
 * true  = Bootstrap 5.3 + jQuery 3.7.1 (novo)
 * false = Bootstrap 3.3.7 + jQuery 1.12.4 (original)
 *
 * Altere para false caso precise voltar ao comportamento antigo.
 */
$USAR_BOOTSTRAP_5 = false;

?>
<!DOCTYPE html>
<html lang="pt-Br" xml:lang="pt-Br" xmlns="https://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
        <META HTTP-EQUIV="Pragma" CONTENT="no-cache">

        <meta http-equiv="Cache-Control" content="no-store" />
        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <?php  if(file_exists('resources/image/favicon-'.COD_EVENTO.'.ico')){ ?>
            <link rel="icon" href="resources/image/favicon-<?php echo COD_EVENTO; ?>.ico">
        <?php }else{ ?>
            <link rel="icon" href="resources/image/favicon-<?php echo strtolower($objEv->getGestor()); ?>.ico">
        <?php } ?>
        <?php require 'include/pwa-loader.php'; ?>
        <title><?php echo TITLE_APP; ?></title>
        <?php require_once(dirname(__FILE__)."/include/bootstrap-loader.php"); ?>
    </head>
    <body id='principal-conteudo1'>
        <!-- Fixed navbar -->
        <nav class="navbar navbar-inverse navbar-fixed-top cabecalho">
            <div class="container">
              <?php require "menu.php";?>
            </div>
        </nav>
        <div class="container miolo" id="container">
            <!-- Main component for a primary marketing message or call to action -->
            <!--<div class="jumbotron">-->
            <?php
                If(FWA_Arquivo_Existe($content,0)){
                    require_once($content);
//                    require 'view/admin/default.php';
                }Else{
                    require 'miolo.php';
                }
            ?>
<!--            </div>-->
        </div>
        <!-- /container -->
        <div class="rodape footer">
            <div class="container">
            <?php
            require 'rodape.php';
            ?>
            </div>
        </div>

    </body>
</html>
<!--<script type="text/javascript">
var LHCChatOptions = {};
LHCChatOptions.opt = {widget_height:340,widget_width:300,popup_height:520,popup_width:500};
(function() {
var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
var referrer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';
var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';
po.src = '//zaqueu.atlantidaweb.com/livehelper/index.php/por/chat/getstatus/(click)/internal/(position)/bottom_right/(ma)/br/(top)/350/(units)/pixels/(leaveamessage)/true?r='+referrer+'&l='+location;
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
</script>-->
<!--<script type="text/javascript">
var LHCChatOptions = {};
LHCChatOptions.opt = {widget_height:340,widget_width:300,popup_height:520,popup_width:500};
(function() {
var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
var referrer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';
var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';
po.src = '//zaqueu.atlantidaweb.com/livehelper/index.php/por/chat/getstatus/(click)/internal/(position)/bottom_right/(ma)/br/(top)/350/(units)/pixels/(leaveamessage)/true/(disable_pro_active)/true/(noresponse)/true?r='+referrer+'&l='+location;
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
</script>-->
<?php
//$_GET?   executarLogApp($_GET,  'index-i-get')  : "";
//$_POST?  executarLogApp($_POST, 'index-i-post') : "";
//$_INPUT = file_get_contents("php://input");
//$_INPUT?  executarLogApp($_INPUT, 'index-i-input')   : "";
?>