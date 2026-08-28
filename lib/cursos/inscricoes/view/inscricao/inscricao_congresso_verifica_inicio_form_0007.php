<?php
session_start();
$_SESSION['_logado'] = false;
$_SESSION['_cod_inscrito'] = false;
$_SESSION['_logadoComo'] = false;
$_SESSION['_inscrito'] = false;
$_SESSION['_administrativo'] = false;
$_SESSION = array();
unset($_SESSION);
session_destroy();

if ($fws_['msg'] and ( $_GET['codRetorno'] == 'verificarEmailInscrito' or $_GET['codRetorno'] == '')) {
    FWA_Mostrar_Login($fws_['msg']);
}
?>

<?php
//if(COD_EVENTO==5){
//    echo "<center>";
//    echo "<h3>PLENÁRIA ESTADUAL DO CRESS-PR</h3>";
//    echo "<h4>Apresentação do Plano de Metas e Planejamento Orçamentário 2021</h3>";
//    echo "</center>";
//}
if($OP_PROCESSO_INSCRICAO){
?>
<div class="row">
    <div class="col-sm-12 text-right">
        <form class="form-signin" name="form" method="POST" onsubmit="return criticar();"
              action="<?php echo  WB_URL_COMMAND ?>">
            <input type="hidden" name="c" value="GerenciarLogin">
            <input type="hidden" name="m" value="verificarInscrito">

            <input type="cpf" name="cpf" id="cpf" value="<?php echo @$_GET['cpf'] ?>" onBlur="trim(this);" 
                   size='20' class="form_campos form-control" placeholder="Informe seu cpf para iniciar" required autofocus>

            <input type="e_mail" id="email" name="e_mail" onBlur="trim(this);" class="form-form_campos form-control hidden" placeholder="Informe seu E-mail" required autofocus>
            <?php if ($OP_ENTRANGEIRO_SEM_CPF) { ?>
            <br>
            <input type='checkbox' id='estrangeiro' name='estrangeiro' value='ON'/>&nbsp;Estrangeiro sem CPF?<br/>
            <?php } ?>
            <!--
            
                <label for="nome_inscrito" class="sr-only">Nome</label>
                <input type="text" id="nome_inscrito" nome="nome_inscrito" class="form-control" placeholder="Nome" required autofocus>
        
                <label for="rg" class="sr-only">RG</label>
                <input type="text" id="rg" nome="rg" class="form-control" placeholder="RG" required autofocus>
            -->
            <br>
            <input type="hidden" value="Inserir" name='acao'>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Iniciar Inscrição</button>
        </form> 
    </div>
</div>
<?php
}else if($OP_PROCESSO_INSCRICAO_SUSPENSO){
//    echo "<center><h4><br>"
//    . "<a href=\"http://congressosaudepublica.org.br/2020/2020/04/29/organizadores-e-promotores-informam-sobre-ajustes-no-5o-congresso-data-esta-mantida-mas-o-evento-sera-virtual\""
//            . "style='color:red;text-decoration:none;'>Novas inscrições suspensas"
//            . "<br> Até dia 30 de maio de 2020</a>"
//    . "</h4> </center>";
}else{
    if($FWS_Data_Hoje < $FWS_Data_Inscricao_Inicio){
        if($msg_tela_previsao_inicio){
            echo "<center><h4>".$msg_tela_previsao_inicio."</h4></center>";
        }
    }
    if($FWS_Data_Inscricao_Fim < $FWS_Data_Hoje){
        echo "<center><h4>Inscrições encerradas no dia ".formatDateView($FWS_Data_Inscricao_Fim).".</h4> </center>";
    }
//    echo "<center><h4>Para assistir ao evento <br>é necessário acessar sua área de inscrito.</h4> </center>";
}
?>    

<script language="JavaScript1.2" src="resources/js/default.js"></script>
<script type='text/javascript' language='javascript'>
//------------------------------------------------------------------------
$(document).ready(function () {
    $('#email').removeAttr('required');
    $('#estrangeiro').change(function () {
        if (this.checked) {
            $('#cpf').removeAttr('required');
            $('#cpf').addClass('hidden');
            
            $('#email').removeClass('hidden');
            $('#email').attr("required", "true");
            
            $('input[name="email"]').val('').focus();
        } else {
            $('#cpf').removeClass('hidden');
            $('#cpf').attr("required", "true");
            
            $('#email').removeAttr('required');
            $('#email').addClass('hidden');
            
            $('input[name="cpf"]').val('').focus();
        }
    });
});            
function criticar() {
    // testa se o email foi preenchido e se está no formato correto
    if (!isEmail(document.form.e_mail.value)) {
        alert("O email deve ser preenchido no formato correto (usuário@dominio).");
        document.form.e_mail.focus();
        return false;
    }
}
   
// -->
</script>