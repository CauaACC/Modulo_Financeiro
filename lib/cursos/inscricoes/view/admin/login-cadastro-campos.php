<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/login-cadastro.js"></SCRIPT>

<?php
#require_once(dirname(__FILE__)."/../../include/config.php");
$FWA_Option_Dia    = FWA_Option_Dia();
$FWA_Option_Mes    = FWA_Option_Mes();
$FWA_Option_Ano    = FWA_Option_Ano(3);
$FWA_Option_Hora   = FWA_Option_Hora();
$FWA_Option_Minuto = FWA_Option_Minuto();


#$STATUS="DESATIVADO";
#echo $status."<br>";

If(isset($status)){
  $OPTION_Status = "<option selected value='".$status."'>".$status."</option>";
}Else{
  $OPTION_Status = "<option selected value='-1'>Alterar Status</option>";
}

#echo $cod_atuacao_convidado."<br>";
#echo $nome_atuacao_convidado."<br>";

If($cod_convidado!=""){
  $OPTIONS_Atuacao = "<option class='form_campos' value='".$cod_convidado."' selected>".$nome_convidado."--</option>";
}Else{
  $OPTIONS_Atuacao = "<option class='form_campos' value='-1' selected>Selecione um Convidado</option>";
}

echo <<<corpo
      <input type='hidden' name='COD_USUARIO' value='{$COD_USUARIO}'>
      <input type='hidden' name='COD_EVENTO'  value='{$COD_EVENTO}'>
      
      <TABLE WIDTH='400' BORDER='0' CELLSPACING='2' CELLPADDING='4'>
corpo;
        
//        require_once(dirname(__FILE__)."/../../view/admin/login-cadastro-select-convidados.php");
        
echo <<<corpo
        </TD></TR>
        <TR>
          <TD>&nbsp;&nbsp;Nome:</TD>
          <TD><INPUT CLASS='form_campos' TYPE='text' NAME='nome' value='{$nome}' SIZE='60' maxlength='255'></TD>
        </TR>
        <TR>
          <TD>&nbsp;&nbsp;Login:</TD>
          <TD><INPUT CLASS='form_campos' TYPE='text' NAME='login' value='{$login}' SIZE='35' maxlength='255'></TD>
        </TR>
        <TR>
          <TD>&nbsp;&nbsp;Senha:</TD>
          <TD><INPUT CLASS='form_campos' TYPE='password' NAME='senha' value='{$senha}' SIZE='35' maxlength='255'> </TD>
        </TR>
        <TR>
          <TD colspan="2">&nbsp;&nbsp;<input type="checkbox" name="enviar_aviso" value="ON"/> Enviar comunicado por e-mail?</TD>
        </TR>
        
        <TR><TD COLSPAN='2'>&nbsp;</TD></TR>
       </TABLE>
corpo;

?>

