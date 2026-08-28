<h3>Área do Inscrito</h3>  
    <div class="panel-group">
        <div class="panel panel-default">
        <div class="panel-body">
                <?php
                if($fws_['msg'] and ($_GET['codRetorno']=='efetuarLogin')){
                    FWA_Mostrar_Login($fws_['msg']);
                }
                ?>
                <form class="form-signin" name="form" method="POST" onsubmit="return criticar();"
                  action="<?php echo WB_URL_COMMAND?>">
                    <input type="hidden" name="c" value="GerenciarLogin">
                    <input type="hidden" name="m" value="recuperarSenha">
                    
                <input type="text" name="e_mail" id="e_mail" value="<?php echo $_GET['e_mail']?>"
                       size='20' 
                       class="form_campos form-control" placeholder="Informe o e-mail utilizado na inscrição" required autofocus>

                <br>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Recuperar sua senha</button>
            </form>
        </div>
    </div>
</div>
      

<script language="JavaScript1.2" src="resources/js/default.js"></script>
<script LANGUAGE="JAVASCRIPT">
//------------------------------------------------------------------------

    function criticar() {

        // testa se o email foi preenchido e se está no formato correto
        if (!isEmail(document.form.e_mail.value)) {
            alert("O email deve ser preenchido no formato correto (usuário@dominio).");
            document.form.e_mail.focus();
            return false;
        }
    }
// -->

</SCRIPT>