<?php
Function FWA_Option_Hora(){
  $Hora_Ini = 00;
  $Hora_Fim = 23;
  For($ii=$Hora_Ini;$ii<=$Hora_Fim;$ii++){
    $FWA_Hora = FWA_Zero_Esquerda($ii,2);
    @$FWA_Option_Hora.= "<option value='".$FWA_Hora."'>".$FWA_Hora."</option>\n";
  }
  return $FWA_Option_Hora;
}
?>