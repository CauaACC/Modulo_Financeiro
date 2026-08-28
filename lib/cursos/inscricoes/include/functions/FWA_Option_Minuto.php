<?php
Function FWA_Option_Minuto(){
  $Minuto_Ini = 00;
  $Minuto_Fim = 59;
  For($ii=$Minuto_Ini;$ii<=$Minuto_Fim;$ii++){
    $FWA_Minuto = FWA_Zero_Esquerda($ii,2);
    @$FWA_Option_Minuto.= "<option value='".$FWA_Minuto."'>".$FWA_Minuto."</option>\n";
  }
  return $FWA_Option_Minuto;
}
?>