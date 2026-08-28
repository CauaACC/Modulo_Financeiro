<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$objInscrito = new Inscrito();
$objInscrito->setEMail($fws_['e_mail']);
$objInscrito->setCpf(formatCpfInsert($fws_['cpf']));
$objInscrito->setCodPerfil($fws_['cod_perfil']);
$objInscrito->setCodOficina($fws_['cod_oficina']);
$urlVoltar = "index.php?content=view/inscricao/inscricao_01.php";
if($_SESSION['_cod_inscrito']!=''){
    $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
    $objInscrito->getInscritoById($debug);
    $objInscrito->setReEMail($objInscrito->getEMail());
    $readOnly = 'readonly';
    $disabled  = 'disabled';
    $urlVoltar = "index.php?content=view/inscricao/inscricao_congresso_ok.php";
}
?>
<!--<FORM NAME="form" METHOD="POST" ACTION="view/inscricao/inscricao_congresso_grava.php"  onsubmit="return criticar();">-->
<form name="form" method="POST" onsubmit="return criticar();"
  action="<?php echo WB_URL_COMMAND?>">
    <input type="hidden" name="c" value="GerenciarInscricao">
    <input type="hidden" name="m" value="salvarInscricao">
    <INPUT TYPE='hidden' VALUE='Inserir' NAME='acao'>
    
<!--<input type="hidden" name="content" value="view/inscricao/inscricao_congresso_confirmar.php">-->
<h3>Área do Inscrito</h3>  
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Processo de Inscrição - Informe Dados Pessoais</h4></div>
        <div class="panel-body">
            <?php
            if($fws_['msg']){
                FWA_Mostrar_Mensagem($fws_['msg']);
            }
            ?> 
            <?php if ($objInscrito->getPossuiBoletoGerado()){?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"></div>
            <?php }else {?>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    <?php
                    //if ($_SESSION['REMOTE_ADDR']=='189.90.70.56'){
                        $objInscrito->carregarOjbPerfil($debug);
                        if($objInscrito->getCodPerfil() == ''){
                            //$url = WB_URL_VIEW . '?content=view/inscricao/inscricao_congresso_perfil_seleciona.php';
                            $msg = 'É necessário selecinar um perfil para inscrição';
                            $_SESSION['MSG']=$msg;
                            $url = WB_URL_VIEW . '?content=view/inscricao/inscricao_01.php&codRetorno=efetuarLogin&e_mail='.$fws_['e_mail'];
                            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
                            exit();
                        }
                        echo "<h4 class='corTitulo'>Categoria: ".$objInscrito->getObjPerfil()->getTitulo()."</h4>";
                        echo $objInscrito->getObjPerfil()->getInvestimento();
                        echo "<input type='hidden' name='cod_perfil' value='".$objInscrito->getObjPerfil()->getCodPerfil()."'>";
                        echo "<hr>";
                    //}
                    ?>
                </div>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    <?php
                        $objInscrito->carregarOjbOficina($debug);
                        if($objInscrito->getObjOficina()->getCodOficina()!=''){
                            echo "<h4 class='corTitulo'>Atividade: ".$objInscrito->getObjOficina()->getTitulo()."</h4>";
                            echo $objInscrito->getObjOficina()->getInvestimento();
                            echo "<input type='hidden' name='cod_oficina' value='".$objInscrito->getObjOficina()->getCodOficina()."'>";
                            echo "<hr>";
                        }else{
                            echo "<h4 class='corTitulo'>Oficina: não optou por atividade</h4>";
                        }
                    ?>
                </div>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    <h4 class='corTitulo'>Acrescentar valor do almoço junto ao boleto da inscrição?</h4>
                    <table class='tb-valor'>
                        <tr><td width="100%" height="10" colspan=2>Valor unitário R$ 49,00 (inclui um refrigerante ou água mineral por pessoa/refeição).</td></tr>
                        <tr><td width="100%" height="10">

                        &nbsp;&nbsp;<input type="checkbox" <?php echo  verifItemChecked('Terca',   json_decode($objInscrito->getInfoAlmoco())) ?> id="infoDiaSemana" name="info_almoco[]" value="Terca"> Terça-feira – 15/08/2017
                        &nbsp;&nbsp;<input type="checkbox" <?php echo  verifItemChecked('Quarta',  json_decode($objInscrito->getInfoAlmoco())) ?> id="infoDiaSemana" name="info_almoco[]" value="Quarta"> Quarta-feira – 16/08/2017
                        &nbsp;&nbsp;<input type="checkbox" <?php echo  verifItemChecked('Quinta',  json_decode($objInscrito->getInfoAlmoco())) ?> id="infoDiaSemana" name="info_almoco[]" value="Quinta"> Quinta-feira – 17/08/2017
                        <br>&nbsp;
                            </td><td></td></tr>
                     </table>
                </div>
            <?php } ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <!--<button class='btn btn-xs btn-primary' type="submit">Avançar</button>-->
                <button class='btn btn-xs btn-primary' type="submit">Salvar Inscrição</button>
                <button class='btn btn-xs btn-primary' type="reset">Limpar</button>
                <a href="<?php echo $urlVoltar?>"
                   class="btn btn-xs btn-primary" role="button">Voltar</a>
            </div>
            
            <div class="form-group col-sm-7" style="padding-left: 0px;">
                <label for="nome_inscrito">Nome para Certificado:</label>
                <input type="text" id="nome_inscrito" name="nome_inscrito" value="<?php echo  $objInscrito->getNomeInscrito() ?>"
                       class="form-control" placeholder="Nome para Certificado" required autofocus
                        SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
            </div>
            <div class="form-group col-sm-5" style="padding-left: 0px;">
                <label for="nome_apelido">Nome como quero ser chamado:</label>
                <input type="text" id="nome_apelido" name="nome_apelido" value="<?php echo  $objInscrito->getNomeApelido() ?>"
                       class="form-control" placeholder="Nome com quero ser chamado" 
                        SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
            </div>
                
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <input type='checkbox' id='estrangeiro' <?php echo $disabled?> name='estrangeiro' value='ON'/>&nbsp;Estrangeiro sem CPF?<br/>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="cpf" id="textoCpf"        class="show">Cpf:</label>
                <label for="cpf" id="textoPassaporte" class="hidden">Cpf/Passaporte:</label>
                <input type="text" id="cpf" <?php echo $readOnly?> name="cpf" value="<?php echo  $objInscrito->getCpf() ?>"
                       class="form-control" placeholder="Cpf" required autofocus
                        SIZE='60' maxlength='11' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="e_mail">E-mail:</label>
                <input type="email" id="e_mail" <?php echo $readOnly?> name="e_mail" value="<?php echo  $objInscrito->getEMail() ?>"
                       class="form-control" placeholder="Informe e-mail" required autofocus
                        SIZE='60' maxlength='255' onBlur='trimEmail(this);'>
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="re_e_mail">Confirme seu E-mail:</label>
                <input type="email" id="re_e_mail" <?php echo $readOnly?> name="re_e_mail" value="<?php echo  $objInscrito->getReEMail() ?>"
                       class="form-control" placeholder="Confirme seu e-mail" required autofocus
                        SIZE='60' maxlength='255' onBlur='trimEmail(this);'>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="text" id="data_nascimento" name="data_nascimento" value="<?php echo  $objInscrito->getDataNascimento() ?>"
                       class="form-control" placeholder="Data de Nascimento" required autofocus
                        SIZE='60' maxlength='10' onBlur='trim(this);'>
            </div>
            
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="endereco">Rua:</label>
                <input type="text" id="endereco" name="endereco" value="<?php echo  $objInscrito->getEndereco() ?>"
                       class="form-control" placeholder="" required autofocus
                        SIZE='60' maxlength='255' onBlur='trim(this);'>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="numero">Número:</label>
                <input type="text" id="numero" name="numero" value="<?php echo  $objInscrito->getNumero() ?>"
                       class="form-control" placeholder="Número" required autofocus
                        SIZE='60' maxlength='255' onBlur='trim(this);'>
            </div>
            <div class="form-group col-sm-4" style="padding-left: 0px;">
                <label for="complemento">Complemento:</label>
                <input type="text" id="complemento" name="complemento" value="<?php echo  $objInscrito->getComplemento() ?>" 
                       class="form-control" 
                        SIZE='60' maxlength='255' onBlur='trim(this);'>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="fone_contato">Fone Contato:</label>
                <input type="tel" id="fone_contato" name="fone_contato" value="<?php echo  $objInscrito->getFoneContato() ?>"
                       class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);'>
            </div>
                
            <div class="form-group col-sm-1" style="padding-left: 0px;">
                <label for="cidade">Estado:</label>
                <select class='form-control' name='estado' id='estado' placeholder="Estado" required autofocus>
                    <option class='form_campos' value='-1' selected>Selecione um Estado</option>
                    <?php
                    $fws_['estado'] = $objInscrito->getEstado();
                    CarregarOptionsAC::estados($fws_, $debug);
                    ?>
                </select>
            </div>
            <div class="form-group col-sm-5" style="padding-left: 0px;">
                <label for="cidade">Cidade:<?php echo $fws_['cidade']?> </label>
<!--                    <input type="text" id="cidade" name="cidade" value="<?php echo  $objInscrito->getcidade() ?>"
                       class="form-control" placeholder="Cidade" required autofocus
                        SIZE='60' maxlength='255' onBlur='trim(this);'>-->
                
                <select class='form-control' name='cidade' id='cidade' placeholder="Cidade" required autofocus>
                    <option class='form_campos' value='-1' selected>Selecione um Cidade</option>
                    <?php
                    $fws_['cidade'] = $objInscrito->getCidade();
                    CarregarOptionsAC::cidades($fws_, $debug);
                    ?>
                </select>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="cep">Cep:</label>
                <input type="text" id="cep" name="cep" value="<?php echo  $objInscrito->getCep() ?>" 
                       class="form-control" placeholder="Cep" required autofocus
                        SIZE='60' maxlength='9' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="bairro">Bairro:</label>
                <input type="text" id="bairro" name="bairro" value="<?php echo  $objInscrito->getBairro() ?>"
                       class="form-control" placeholder="Bairro" required autofocus
                        SIZE='60' maxlength='255' onBlur='trim(this);'>
            </div>
                
            <div class="form-group col-sm-12" style="padding-left: 0px;"></div>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <input type='checkbox' <?php echo  verifItemChecked('1', $objInscrito->getInstitucional()) ?> id='institucional' name='institucional' value='1'/>
                &nbsp;Deseja emitir Boleto/Recibo em nome de sua Instituição/Empresa?<br/>
            </div>
                
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <div class="form-recibo form-group col-sm-7" style="padding-left: 0px;">
                    <label for="nome_recibo">Nome da instituição:</label>
                    <input type="text" id="nome_recibo" name="nome_recibo" value="<?php echo  $objInscrito->getNomeRecibo() ?>"
                           class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="form-recibo form-group col-sm-3" style="padding-left: 0px;">
                    <label for="cpf_recibo">CPF/CNPJ:</label>
                    <input type="text" id="cpf_recibo" name="cpf_recibo" value="<?php echo  $objInscrito->getCpfRecibo() ?>"
                           class="form-control" SIZE='60' maxlength='18' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                </div>
            </div>
            <div class="form-group col-sm-12" style="padding-left: 0px;">

                <div class="form-recibo form-group col-sm-6" style="padding-left: 0px;">
                    <label for="endereco_recibo">Rua:</label>
                    <input type="text" id="endereco_recibo" name="endereco_recibo" value="<?php echo  $objInscrito->getEnderecoRecibo() ?>"
                           class="form-control" placeholder="" required autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="form-recibo form-group col-sm-1" style="padding-left: 0px;">
                    <label for="numero_recibo">Número:</label>
                    <input type="text" id="numero_recibo" name="numero_recibo" value="<?php echo  $objInscrito->getNumeroRecibo() ?>"
                           class="form-control" placeholder="Número" required autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="form-recibo form-group col-sm-3" style="padding-left: 0px;">
                    <label for="complement_reciboo">Complemento:</label>
                    <input type="text" id="complemento_recibo" name="complemento_recibo" value="<?php echo  $objInscrito->getComplementoRecibo() ?>" 
                           class="form-control" 
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
            </div>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <div class="form-recibo form-group col-sm-1" style="padding-left: 0px;">
                    <label for="estado_recibo">Estado:</label>
                    <select class='form-control' name='estado_recibo' id='estado_recibo' placeholder="Estado" required autofocus>
                        <option class='form_campos' value='-1' selected>Selecione um Estado</option>
                        <?php
                        $fws_['estado_recibo'] = $objInscrito->getEstadoRecibo();
                        CarregarOptionsAC::estados($fws_, $debug);
                        ?>
                    </select>
                </div>
                <div class="form-recibo form-group col-sm-5" style="padding-left: 0px;">
                    <label for="cidade_recibo">Cidade:<?php echo $fws_['cidade_recibo']?> </label>               
                    <select class='form-control' name='cidade_recibo' id='cidade_recibo' placeholder="Cidade" required autofocus>
                        <option class='form_campos' value='-1' selected>Selecione um Cidade</option>
                        <?php
                        $fws_['cidade_recibo'] = $objInscrito->getCidadeRecibo();
                        CarregarOptionsAC::cidades($fws_, $debug);
                        ?>
                    </select>
                </div>
                <div class="form-recibo form-group col-sm-2" style="padding-left: 0px;">
                    <label for="cep_recibo">Cep:</label>
                    <input type="text" id="cep_recibo" name="cep_recibo" value="<?php echo  $objInscrito->getCepRecibo() ?>" 
                           class="form-control" placeholder="Cep" required autofocus
                            SIZE='60' maxlength='9' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                </div>
                <div class="form-recibo form-group col-sm-2" style="padding-left: 0px;">
                    <label for="bairro_recibo">Bairro:</label>
                    <input type="text" id="bairro_recibo" name="bairro_recibo" value="<?php echo  $objInscrito->getBairroRecibo() ?>"
                           class="form-control" placeholder="Bairro" required autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
            </div>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    <!--<button class='btn btn-xs btn-primary' type="submit">Avançar</button>-->
                    <button class='btn btn-xs btn-primary' type="submit">Salvar Inscrição</button>
                    <button class='btn btn-xs btn-primary' type="reset">Limpar</button>
                    <a href="<?php echo $urlVoltar?>"
                       class="btn btn-xs btn-primary" role="button">Voltar</a>
                </div>
                <br><br><br><br><br><br>&nbsp;
            </div>
        </div>
    </div>
</div>
</form>
<script language="JavaScript1.2" src="resources/js/default.js"></script>
<script language="JavaScript" type="text/javascript">
$(document).ready(function () {
//    $("table.tablesorter").tablesorter({widgets: ['zebra']})
//    .tablesorterPager({container: $("#pager")}); 
   
    $("#estado").change(function () {
        carregarCidades(this.value);
    });
    $("#estado_recibo").change(function () {
        carregarCidadesRecibo(this.value);
    });
//    $('#data_nascimento').datepicker();

    $('#estrangeiro').change(function () {
        if (this.checked) {
            loading($('#textoCpf'),'hidden')
            loading($('#textoPassaporte'),'show')
            $('#cpf').attr("placeholder", "Cpf/Passaporte");
        } else {
            loading($('#textoCpf'),'show')
            loading($('#textoPassaporte'),'hidden')
            $('#cpf').attr("placeholder", "Cpf");
        }
    });
    $('#institucional').change(function () {
        if (this.checked) {
            $('#nome_recibo').removeAttr("disabled");
            $('#cpf_recibo').removeAttr("disabled");
            loading($('.form-recibo'),'show')
        } else {
            $('#nome_recibo').attr("disabled", "true");
            $('#cpf_recibo').attr("disabled", "true");
            loading($('.form-recibo'),'hidden')
        }
    });

    <?php if($objInscrito->getInstitucional() == '1') {?>
        $('#nome_recibo').removeAttr("disabled");
        $('#cpf_recibo').removeAttr("disabled");
        loading($('.form-recibo'),'show')
    <?php }else {?>
        $('#nome_recibo').attr("disabled", "true");
        $('#cpf_recibo').attr("disabled", "true");
        loading($('.form-recibo'),'hidden')
    <?php }?>    
});
function mostrarInfoRecibo(){
    $('#institucional').change(function () {
        if (this.checked) {
            $('#nome_recibo').attr("disabled", "true");
            $('#cpf_recibo').attr("disabled", "true");
        } else {
            $('#nome_recibo').attr("disabled", "false");
            $('#cpf_recibo').attr("disabled", "false");
        }
    });
}
function carregarCidades(cod, selecionado)
{
   $.ajax({
       type: "POST",
       url: '<?php echo  WB_URL_COMMAND ?>',
       data: ({'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1'}),
       dataType: "html",
       async: false,
       cache: false
       , success: function (data) {
           $("#cidade").html(data);
       }
       , error: function () {
           alert("Nao foi possivel obter informações:\n" + req.statusText);
       }
   });
}
function carregarCidadesRecibo(cod, selecionado)
{
   $.ajax({
       type: "POST",
       url: '<?php echo  WB_URL_COMMAND ?>',
       data: ({'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1'}),
       dataType: "html",
       async: false,
       cache: false
       , success: function (data) {
           $("#cidade_recibo").html(data);
       }
       , error: function () {
           alert("Nao foi possivel obter informações:\n" + req.statusText);
       }
   });
}

function criticar() {
  if (document.form.nome_inscrito.value == ""){
      alert ("O campo Nome do Certificado não está preenchido!");
      document.form.nome_inscrito.focus();
      return false ;
  }
  // testa se o cpf foi preenchido e se está no formato correto
  if(document.form.estrangeiro.value=='ON') {
      
  }else{
    if (!isCpf(document.form.cpf.value)) {
      alert ("O CPF não foi preenchido ou não possui um formato valido.");
      document.form.cpf.focus();
      return false;
    }
  }
  // testa se o email foi preenchido e se está no formato correto
  if (!isEmail(document.form.e_mail.value)) {
    alert ("O email deve ser preenchido no formato correto (usuário@dominio).");
    document.form.e_mail.focus();
    return false;
  }
  // testa se o email foi preenchido e se está no formato correto
  if (!isEmail(document.form.re_e_mail.value)) {
    alert ("O email deve ser preenchido no formato correto (usuário@dominio).");
    document.form.re_e_mail.focus();
    return false;
  }
  if($("#re_e_mail").val() != $("#e_mail").val()){
    alert ("O email de confirmação diferente do e-mail informado.");
    $("#re_e_mail").focus();
    return false;  
  }
  if (document.form.data_nascimento.value == ""){
      alert ("O campo Data de Nascimento não está preenchido!");
      document.form.data_nascimento.focus();
      return false ;
  }

//  if (getCheckedRadioButton(document.form.sexo) == -1){
//    alert ("O campo Sexo deve ser preenchido.");
//    document.form.sexo[0].focus();
//    return false;
//  }

  if (document.form.endereco.value == ""){
      alert ("O campo Rua não está preenchido!");
      document.form.endereco.focus();
      return false ;
  }
  if (document.form.numero.value == ""){
      alert ("O campo Número não está preenchido!");
      document.form.numero.focus();
      return false ;
  }
  if (document.form.bairro.value == ""){
      alert ("O campo Bairro não está preenchido!");
      document.form.bairro.focus();
      return false ;
  }
  if (document.form.cidade.value== ""){
      alert ("O campo Cidade não está preenchido!");
      document.form.cidade.focus();
      return false ;
  }
  if (document.form.cep.value== ""){
      alert ("O campo Cep não está preenchido!");
      document.form.cep.focus();
      return false ;
  }
  if (document.form.estado.value== "-1"){
      alert ("O campo Estado não está preenchido!");
      document.form.estado.focus();
      return false ;
  }
  if (document.form.fone_contato.value== ""){
      alert ("O campo Telefone não está preenchido!");
      document.form.fone_contato.focus();
      return false ;
  }
}
//-->

</script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
   <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>