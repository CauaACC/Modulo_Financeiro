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



if ($fws_['msg'] and ( $_GET['codRetorno'] == 'verificarEmailInscrito' or $_GET['codRetorno'] == 'verificarCpfInscrito' or $_GET['codRetorno'] == '')) {
    FWA_Mostrar_Login($fws_['msg']);
}
?>

<?php
if(COD_EVENTO==5){
    echo "<center>";
    echo "<h3>PLENÁRIA ESTADUAL DO CRESS-PR</h3>";
    echo "<h4 class='msgTelas'>Apresentação do Plano de Metas e Planejamento Orçamentário 2021</h3>";
    echo "</center>";
}
//echo '->'.$OP_PROCESSO_INSCRICAO.'<->'.$OP_PROCESSO_INSCRICAO_SUSPENSO.'<-';
/**@todo inserir mensagem para o caso de suspenção, e mensagem de número de vagas */
if($OP_PROCESSO_INSCRICAO_SUSPENSO){
    $msg_tela_durante = $objEvConf->getMsgTelaDurante();
    if($msg_tela_durante){
        echo "<center><h4 style='margin-bottom:0px;'>".$msg_tela_durante."</h4></center>";
    }
}
if($OP_PROCESSO_INSCRICAO==true and $OP_PROCESSO_INSCRICAO_SUSPENSO==false and ($FWS_Data_HojeHM < $FWS_Data_Inscricao_Fim)){
    $msg_tela_durante = $objEvConf->getMsgTelaDurante();
    if($msg_tela_durante){
        echo "<center><h4 style='margin-bottom:0px;'>".$msg_tela_durante."</h4></center>";
    }
?>
<div class="row">
    <div class="col-sm-12 text-center">
        <form class="form-signin" name="form" method="POST" onsubmit="return criticar();"
              action="<?php echo  WB_URL_COMMAND ?>">
            <input type="hidden" name="c" value="GerenciarLogin">
            <input type="hidden" name="m" value="verificarInscrito">
            <?php
            $msg_tela_inicio = $objEvConf->getMsgBtnIniciarInscricaoTelaAcima();
            if ($msg_tela_inicio) {
                echo $msg_tela_inicio;
            } else {
                echo "Para iniciar inscrição informe CPF.";
            }
            ?>
            <BR>
            <input type="cpf" name="cpf" id="cpf" value="<?php echo @$_GET['cpf'] ?>" onBlur="trim(this);" 
                   size='20' maxlength="14" class="form_campos form-control somente-numeros" placeholder="Informe seu cpf" required autofocus>

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
            <?php
            $msg_botao_iniciar = $objEvConf->getMsgBtnIniciarInscricao();
            if ($msg_botao_iniciar) {
                echo '<button class="btn btn-lg btn-primary btn-block" type="submit">' . $msg_botao_iniciar . '</button>';
            } else {
                echo '<button class="btn btn-lg btn-primary btn-block" type="submit">Iniciar Inscrição</button>';
            }
            ?>
        </form> 
    </div>
</div>
<?php
}else if($OP_PROCESSO_INSCRICAO_SUSPENSO){
    echo "<center><h4 class='msgTelas'><br>"
            . "<span style='color:red;text-decoration:none;'>Novas inscrições estão suspensas</span>"
    . "</h4> </center>";
//    echo "<center><h4 class='msgTelas'><br>"
//    . "<a href=\"http://congressosaudepublica.org.br/2020/2020/04/29/organizadores-e-promotores-informam-sobre-ajustes-no-5o-congresso-data-esta-mantida-mas-o-evento-sera-virtual\""
//            . "style='color:red;text-decoration:none;'>Novas inscrições estão suspensas"
////            . "<br> Até dia 30 de maio de 2020</a>"
//    . "</h4> </center>";
}else{
    if($FWS_Data_HojeHM < $FWS_Data_Inscricao_Inicio){
        $msg_tela_previsao_inicio = $objEvConf->getMsgTelaAntes();
        if($msg_tela_previsao_inicio){
            echo "<center><h4 style='margin-bottom:0px;'>".$msg_tela_previsao_inicio."</h4></center>";
        }
    }
    if($FWS_Data_Inscricao_Fim < $FWS_Data_HojeHM){
        $msg_tela_depois_encerrado = $objEvConf->getMsgTelaDepois();
        if($msg_tela_depois_encerrado){
            echo "<center><h4 class='msgTelas'>".$msg_tela_depois_encerrado."</h4></center>";
        }else{
//            echo "<center><h4 class='msgTelas'>Data de hoje: ".$FWS_Data_HojeHM.'-'.$FWS_Data_Inscricao_Fim.".</h4> </center>";
            echo "<center><h4 class='msgTelas'>Inscrições encerradas em: ".formatDateHoraTrans($FWS_Data_Inscricao_Fim, 'YYYYMMDDHHIISS', 'DD/MM/YYYY HH:II').".</h4> </center>";
        }
    }
    //echo "<center><h4>Para assistir ao evento <br>é necessário acessar sua área de inscrito.</h4> </center>";
    if($OP_EMISSAO_CERTIFICADOS==true){
        echo "<center><h4 class='msgTelas'>Para emitir certificados <br>é necessário acessar sua área de inscrito.</h4> </center>";
    }else{
//        echo "<center><h4 class='msgTelas'>Para emitir documentos <br>é necessário acessar sua área de inscrito.</h4> </center>";
    }
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
    $('.somente-numeros').on('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });

});            
function validarCpfDigitos(cpf) {
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length !== 11) return false;
    if (/^(\d)\1{10}$/.test(cpf)) return false;
    var soma = 0;
    for (var i = 0; i < 9; i++) soma += parseInt(cpf.charAt(i)) * (10 - i);
    var resto = soma % 11;
    var dig1 = (resto < 2) ? 0 : 11 - resto;
    if (parseInt(cpf.charAt(9)) !== dig1) return false;
    soma = 0;
    for (var i = 0; i < 10; i++) soma += parseInt(cpf.charAt(i)) * (11 - i);
    resto = soma % 11;
    var dig2 = (resto < 2) ? 0 : 11 - resto;
    if (parseInt(cpf.charAt(10)) !== dig2) return false;
    return true;
}
function criticar() {
    var estrangeiro = document.getElementById('estrangeiro');
    if (estrangeiro && estrangeiro.checked) {
        return true;
    }
    var cpfValue = document.form.cpf.value.replace(/\D/g, '');
    if (!validarCpfDigitos(cpfValue)) {
        alert("O CPF informado não é válido.");
        document.form.cpf.focus();
        return false;
    }
    return true;
}
   
// -->
</script>
<style>
    .msgTelas {
        margin-bottom:0px;
    }
</style>
    