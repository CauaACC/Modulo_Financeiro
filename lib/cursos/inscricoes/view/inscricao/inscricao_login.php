<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
<h3>Área do Inscrito</h3>  
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Identifique-se Continuar:</h4>
        </div>
        <div class="panel-body">
            <form class="form-signin" name="form" method="POST" action="index.php?content=view/inscricao/inscricao_login_acao.php"  onsubmit='return criticar();'> 
                <?php
                if($fws_['msg']){
                    FWA_Mostrar_Mensagem($fws_['msg']);
                }
                ?>
                <input type="text" name="cpf" id="cpf" value="<?php echo addslashesopctec($_GET['cpf'])?>"
                       size='20' maxlength='11' onkeypress='javascript: return isNum(window.event.keyCode);'
                       class="form_campos form-control" placeholder="Informe seu CPF" required autofocus>

                <input type="text" name="cod_inscrito" 
                       size="20" maxlength="11" onkeypress="javascript: return isNum(window.event.keyCode);"
                       class="form_campos form-control" placeholder="Inform o número de sua inscrição" required autofocus>

                <br>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Fazer login</button>

                <br><a href='index.php?content=view/inscricao/inscricao_esqueceu_credenciais.php'>Esqueceu suas credenciais?</a>
                <br><a href='index.php?content=view/inscricao/inscricao_congresso_verifica_inicio.php'>Iniciar inscrição?</a>
                <br><INPUT TYPE='hidden' VALUE='Inserir' NAME='acao'>
            </form>
        </div>
    </div>
</div>

<SCRIPT LANGUAGE="JAVASCRIPT"> 
//<!--
    function criticar() {
        // testa se o cpf foi preenchido e se está no formato correto
        if (!isCpf(document.form.cpf.value)) {
            alert("O CPF não foi preenchido ou não possui um formato valido.");
            document.form.cpf.focus();
            return false;
        }
        if (document.form.cod_inscrito.value == "") {
            alert("O Código de Inscrito não foi preenchido.");
            document.form.cod_inscrito.focus();
            return false;
        }
    }
// -->
</SCRIPT>