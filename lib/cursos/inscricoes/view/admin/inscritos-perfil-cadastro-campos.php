
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
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
  $OPTION_Status = "<option selected value='ATIVADA'>ATIVADA</option>";
}

$FWA_Select_Inscrito = FWA_Select_Inscrito($cod_inscrito,$nome_inscrito,COD_EVENTO);

$FWA_Select_Perfil  = FWA_Select_Perfil($cod_perfil,$titulo_perfil,COD_EVENTO);


echo <<<corpo
            
      <TABLE WIDTH='100%' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
        <TR>
          <TD VALIGN='top'>

            Nome do Inscrito:<br>
            {$cod_inscrito} - {$nome_inscrito}
            <input type='hidden' name='cod_inscrito' value='{$cod_inscrito}'>
            <br><br>
            Titulo das Perfils:<br>
            {$FWA_Select_Perfil}

          </TD>
        </TR>
       </TABLE>
       <br>&nbsp;
corpo;

?>

