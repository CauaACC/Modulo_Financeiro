<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/convidados-cadastro.js"></SCRIPT>

<?php
#require_once(dirname(__FILE__)."/../../include/config.php");
$FWA_Option_Dia    = FWA_Option_Dia();
$FWA_Option_Mes    = FWA_Option_Mes();
$FWA_Option_Ano    = FWA_Option_Ano(3);
$FWA_Option_Hora   = FWA_Option_Hora();
$FWA_Option_Minuto = FWA_Option_Minuto();


#$STATUS="DESATIVADO";
#echo $status."<br>";
if($_GET['COD_CONVIDADO']==''){
    $status = 'ATIVADA';
}

If(isset($status)){
  $OPTION_Status = "<option selected value='".$status."'>".$status."</option>";
}Else{
  $OPTION_Status = "<option selected value='-1'>Alterar Status</option>";
}

Switch($sexo){
  Case "FEMININO":
    $FWS_Sexo = "
                &nbsp;&nbsp; <input type='radio' value='FEMININO'  name='sexo'  checked> Feminino
                &nbsp;&nbsp; <input type='radio' value='MASCULINO' name='sexo' > Masculino
                ";
    break;
  Case "MASCULINO":
    $FWS_Sexo = "
                &nbsp;&nbsp; <input type='radio' value='FEMININO'  name='sexo'> Feminino
                &nbsp;&nbsp; <input type='radio' value='MASCULINO' name='sexo'  checked> Masculino
                ";
      break;
  Default;
    $FWS_Sexo = "
                &nbsp;&nbsp; <input type='radio' value='FEMININO'  name='sexo'> Feminino
                &nbsp;&nbsp; <input type='radio' value='MASCULINO' name='sexo'> Masculino
                ";
}


Switch($desconto){
  Case "SIM":
    $FWS_Desconto = "
                &nbsp;&nbsp; <input type='radio' value='SIM' name='desconto' checked> Sim
                &nbsp;&nbsp; <input type='radio' value='NÃO' name='desconto' > Não
                ";
    break;
  Case "NÃO":
    $FWS_Desconto = "
                &nbsp;&nbsp; <input type='radio' value='SIM' name='desconto' > Sim
                &nbsp;&nbsp; <input type='radio' value='NÃO' name='desconto'  checked> Não
                ";
    break;
  Default;
    $FWS_Desconto = "
                &nbsp;&nbsp; <input type='radio' value='SIM' name='desconto' > Sim
                &nbsp;&nbsp; <input type='radio' value='NÃO' name='desconto' > Não
                ";
    break;
}


If($estado!=""){
  $FWS_Estado = "<option class='form_campos' value='".$estado."' selected>".$estado."</option>";
}Else{
  $FWS_Estado = "<option class='form_campos' value='-1' selected>Selecione um Estado</option>";
}

#echo $cod_atuacao_convidado."<br>";
#echo $nome_atuacao_convidado."<br>";

If($cod_atuacao_convidado!=""){
  $OPTIONS_Atuacao = "<option class='form_campos' value='".cod_atuacao_convidado."' selected>".$nome_atuacao_convidado."--</option>";
}Else{
  $OPTIONS_Atuacao = "<option class='form_campos' value='-1' selected>Selecione uma atuação</option>";
}
echo <<<corpo
        <TR>
            <TD>Login do Convidado:</TD>
            <TD>
                {$objUsuario->getLogin()}
                <INPUT CLASS='form_campos' TYPE='hidden' NAME='cod_usuario' value='{$objUsuario->getCodUsuario()}'>
                <br>&nbsp;
            </TD>
        </TR>  
corpo;

echo <<<corpo
      <input type='hidden' name='COD_EVENTO' value='{$COD_EVENTO}'>
      <TABLE WIDTH='100%' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
        <TR><TD>Status:</TD>
            <TD>
              <select name='status'>
                {$OPTION_Status}
                <option value='ATIVADA '>ATIVADA </option>
                <option value='DESATIVA'>DESATIVA</option>
              </select>
            </TD>
        </TR>             
        <TR><TD COLSPAN='2'>
corpo;
        
        require_once(dirname(__FILE__)."/../../view/admin/convidados-cadastro-select-atuacao.php");
        
echo <<<corpo
        </TD></TR>
        <TR>
          <TD>Nome :</TD>
          <TD><INPUT CLASS='form_campos' TYPE='text' NAME='nome_convidado' value='{$nome_convidado}' SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'></TD>
        </TR>
        <TR>
          <TD>CPF:</TD>
          <TD><INPUT CLASS='form_campos' TYPE='text' NAME='cpf' value='{$cpf}' SIZE='20' maxlength='11' onkeypress='javascript: return isNum(window.event.keyCode);'> (ex: 12345678901)</TD>
        </TR>
        <TR>
          <TD>RG:</TD>
          <TD><INPUT CLASS='form_campos' TYPE='text' NAME='rg' value='{$rg}' SIZE='20' maxlength='15' onkeypress='javascript: return isNum(window.event.keyCode);'> (ex: 12345678901)</TD>
        </TR>
        <TR>
          <TD>E-Mail:</TD>
          <TD><INPUT CLASS='form_campos' TYPE='text' NAME='e_mail' value='{$objUsuario->getLogin()}' SIZE='60' maxlength='255' onBlur='trimEmail(this);' readonly></TD>
        </TR>
        <TR>
          <TD COLSPAN='2'>
              Data de Nascimento :
              &nbsp;&nbsp;
            <INPUT CLASS='form_campos' TYPE='text' NAME='data_nascimento' value='{$data_nascimento}' SIZE='20' maxlength='10'  onKeyUp='dateMask(this.value, this);' onkeypress='javascript: return isNum(window.event.keyCode);'><!-- onkeypress='return isDateFormat(window.event.keyCode);'>-->
            (ex: dd/mm/aaaa)
          </TD>
        </TR>

        <TR>
          <TD COLSPAN='2'>
            Sexo:
            {$FWS_Sexo}
          </TD>
        </TR>
        <TR>
          <TD>Rua:</TD>
          <TD><INPUT CLASS='form_campos' TYPE='text' NAME='endereco' value='{$endereco}' SIZE='60' maxlength='255' onBlur='trim(this);'></td>
         </tr>
         <TR>
          <TD>Número:</TD>
          <TD><INPUT CLASS='form_campos' TYPE='text' NAME='numero' value='{$numero}' SIZE='20' maxlength='10' onkeypress='javascript: return isNum(window.event.keyCode);'></TD>
        </TR>
        <TR>
          <TD COLSPAN='2'>
            Complemento:
            &nbsp;&nbsp;
            <INPUT CLASS='form_campos' TYPE='text' NAME='complemento' value='{$complemento}' SIZE='50' maxlength='255' value='' onBlur='trim(this);'>
          </TD>
        </TR>
        <TR>
          <TD colspan='2'>
              <TABLE WIDTH='100%' BORDER='0'>
              <TR>
              <TD>Bairro:</TD>
              <TD><INPUT CLASS='form_campos' TYPE='text' NAME='bairro' value='{$bairro}' SIZE='20' maxlength='255' onBlur='trim(this);'></TD>
              <TD>&nbsp;&nbsp;&nbsp;
              Cep:
              &nbsp;
              <INPUT CLASS='form_campos' TYPE='text' NAME='cep' value='{$cep}' SIZE='20' maxlength='8' onBlur='trim(this);' onkeypress='javascript: return isNum(window.event.keyCode);'>
              (ex:12345678)
              </TD>
              </TABLE>
          </TD>
        </TR>
        <TR>
          <TD>Cidade:</TD>
          <TD>
              <INPUT CLASS='form_campos' TYPE='text' NAME='cidade' value='{$cidade}'  maxlength='255' SIZE='20' onBlur='trim(this);'>
              &nbsp;&nbsp;
              Estado:
              &nbsp;&nbsp;
              <select class='form_campos' name='estado'>

                {$FWS_Estado}
                <option class='form_campos' value='AC'>AC</option>
                <option class='form_campos' value='AL'>AL</option>
                <option class='form_campos' value='AP'>AP</option>
                <option class='form_campos' value='AM'>AM</option>
                <option class='form_campos' value='BA'>BA</option>
                <option class='form_campos' value='CE'>CE</option>
                <option class='form_campos' value='DF'>DF</option>
                <option class='form_campos' value='ES'>ES</option>
                <option class='form_campos' value='GO'>GO</option>
                <option class='form_campos' value='MA'>MA</option>
                <option class='form_campos' value='MT'>MT</option>
                <option class='form_campos' value='MS'>MS</option>
                <option class='form_campos' value='MG'>MG</option>
                <option class='form_campos' value='PA'>PA</option>
                <option class='form_campos' value='PB'>PB</option>
                <option class='form_campos' value='PR'>PR</option>
                <option class='form_campos' value='PE'>PE</option>
                <option class='form_campos' value='PI'>PI</option>
                <option class='form_campos' value='RJ'>RJ</option>
                <option class='form_campos' value='RN'>RN</option>
                <option class='form_campos' value='RS'>RS</option>
                <option class='form_campos' value='RO'>RO</option>
                <option class='form_campos' value='RR'>RR</option>
                <option class='form_campos' value='SC'>SC</option>
                <option class='form_campos' value='SP'>SP</option>
                <option class='form_campos' value='SE'>SE</option>
                <option class='form_campos' value='TO'>TO</option>

              </select>
          </TD>
        </TR>
        <TR>
          <TD COLSPAN='2'>
              Telefone Res :
              &nbsp;
              <INPUT CLASS='form_campos' TYPE='text' NAME='fone_residencial' value='{$fone_residencial}' SIZE='20' maxlength='15' value='' onBlur='trim(this);' onkeypress='javascript: return isTelefone(window.event.keyCode);'>
              <FONT FACE='verdana' SIZE=1>ex:(43) 3324-2340
          </TD>
        </TR>
        <TR>
          <TD COLSPAN='2'>
            Telefone Com:
            &nbsp;
            <INPUT CLASS='form_campos' TYPE='text' NAME='fone_comercial' value='{$fone_comercial}' SIZE='20' maxlength='15' value='' onBlur='trim(this);' onkeypress='javascript: return isTelefone(window.event.keyCode);'>
            <FONT FACE='verdana' SIZE=1>ex:(43) 3324-2340
          </TD>
        </TR>
        <TR>
          <TD COLSPAN='2'>
            Telefone Cel:
            &nbsp;&nbsp;
            <INPUT CLASS='form_campos' TYPE='text' NAME='fone_celular' value='{$fone_celular}' SIZE='20' maxlength='15' value='' onBlur='trim(this);' onkeypress='javascript: return isTelefone(window.event.keyCode);'>
            <FONT FACE='verdana' SIZE=1>ex:(43) 9999-9999
          </TD>
        </TR>
        <TR>
          <TD COLSPAN='2'>
              Profissão/Atuação:
              &nbsp;
              <INPUT CLASS='form_campos' TYPE='text' NAME='profissao_atuacao' value='{$profissao_atuacao}' SIZE='48' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
          </TD>
        </TR>
        <TR>
          <TD COLSPAN='2'>
            Instituição/Entidade:
            &nbsp;
            <INPUT CLASS='form_campos' TYPE='text' NAME='instituicao' value='{$instituicao}' SIZE='56' maxlength='255'  onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
          </TD>
        </TR>
        <TR><TD COLSPAN='2'>&nbsp;</TD></TR>
       </TABLE>
       <br>&nbsp;
corpo;

?>

