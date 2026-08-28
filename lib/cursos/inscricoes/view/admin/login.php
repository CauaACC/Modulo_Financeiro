<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if ($fwsGet_['acao']=='logarComo'){
    $fws_['login'] = addslashesopctec($_GET['login'])!=''? addslashesopctec($_GET['login']) : ''; 
}else{
    If ($_SESSION['_logado']) {
        $url = "". WB_URL_VIEW."?content=view/admin/default.php";
        @header("Location: $url");
        echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        exit();
    }
}
?>
<h4>Área Administrativa</h4>
<?php
if ($COD_EVENTO!='' and $COD_EVENTO!='COD_EVENTO'){
    echo "<h4>".$objEv->getTitulo()."</h4>";
}
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <form  class="form-signin" action="view/admin/login_acao.php" method="POST" onsubmit="return criticar();">

                <?php
                @$MSG = $_SESSION['MSG'];
                If (isset($MSG)) {
                    FWA_Mostrar_Mensagem($MSG);
                }
                ?>

                <input type='text' id='login' name='login'
                value='<?php echo $fws_['login']?>' size='20' maxlength='100' 
                class="form_campos form-control" placeholder="Informe seu Login" required autofocus>

                <label for="senha" class="sr-only">Senha</label>
                <input type="password" id="senha" name="senha" 
                class="form-control" placeholder="Senha" required autofocus>

                <br>
                <?php if(defined('GOOGLE_KEY')){ ?>
                <div class="g-recaptcha" data-sitekey="<?php echo GOOGLE_KEY?>"></div>
                <?php } ?>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Fazer Login</button>
                <div style="text-align:center; margin-top:10px;">
                    <a href="index.php?content=view/admin/login-esqueceu-senha.php" style="font-size:13px;">Esqueceu sua senha?</a>
                </div>
                <br><br><br><br><br><br><br><br><br>
            </form>
</div></div></div>
<?php if(defined('GOOGLE_KEY')){ ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>
<script LANGUAGE="JAVASCRIPT">
//------------------------------------------------------------------------
function criticar() {
    <?php if(defined('GOOGLE_KEY')){ ?>
     if( $('#g-recaptcha-response').val() == ''){
        alert('Clique na opção: Não sou um robô!');
        return false;
    }
    <?php } ?>
}
// -->
</SCRIPT>

