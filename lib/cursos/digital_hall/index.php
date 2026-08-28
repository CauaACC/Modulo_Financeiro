<?php
require_once(dirname(__FILE__)."/../inscricoes/include/config.php");
$url = "". WB_URL_VIEW."?content=view/inscricao/inscricao_congresso_hall_virtual.php";
//@header("Location: $url");
echo "<a href='".$url."'>redirecionando para o Hall Virtual, caso não redirecione clique aqui ...</a>";
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
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
        <link rel="icon" href="resources/image/favicon.ico">
        <title><?php echo defined('TITLE_APP') ? TITLE_APP : 'OPCTEC - Gerenciador de Eventos'; ?></title>
        <link href="resources/bootstrap-3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="resources/bootstrap-3.3.7/docs/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
        <!-- Custom styles for this template -->
        <link href="resources/bootstrap-3.3.7/docs/examples/theme/theme.css" rel="stylesheet">
        <link href="resources/bootstrap-3.3.7/docs/examples/signin/signin.css" rel="stylesheet">
        <link href="resources/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="resources/css/bootstrap-adapt<?php echo '-'.COD_EVENTO; ?>.css" rel="stylesheet">
        <link href="resources/css/mensagens.css" rel="stylesheet">
        
        <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
        <!--[if lt IE 9]><script src="resources/bootstrap-3.3.7/docs/assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
        <script src="resources/bootstrap-3.3.7/docs/assets/js/ie-emulation-modes-warning.js"></script>

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        
        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>-->
        <script src="resources/js/jquery/1.12.4/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src=".resources/bootstrap-3.3.7/docs/assets/js/vendor/jquery.min.js"><\/script>')</script>
        <script src="resources/bootstrap-3.3.7/dist/js/bootstrap.min.js"></script>
        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="resources/bootstrap-3.3.7/docs/assets/js/ie10-viewport-bug-workaround.js"></script>
        <script language="JavaScript1.2" src="resources/js/default.js"></script>
        
<!--        
        <link type="text/css" href="resources/js/jquery-ui-1.12.1.custom/jquery-ui.css" rel="stylesheet"/>
        <script type="text/javascript" src="resources/js/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
        <script type="text/javascript" src="resources/JS/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>-->
        
<!--    
        <link type="text/css" href="resources/js/jquery-ui-1.7.3.custom/css/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet"/>
        <script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-ui-1.7.3.custom.min.js"></script>
        <script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-1.3.2.min.js"></script>
-->
        <!--<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/ui.dialog.js"></script>-->
        <script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>        
        <!--<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>-->
        <!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js"></script>-->

        <script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>

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
            require 'miolo.php';
            ?>
<!--            </div>-->
        </div>
        <!-- /container -->
        <div class="container rodape footer">
            <?php
            require 'rodape.php';
            ?>
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
$_POST?  executarLogApp($_POST, 'index-i-post') : "";
$_INPUT = file_get_contents("php://input");
$_INPUT?  executarLogApp($_INPUT, 'index-i-input')   : "";
?>