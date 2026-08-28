<?php
Function FWA_Option_Dia(){
  $Dia_Ini = 01;
  $Dia_Fim = 31;
  For($ii=$Dia_Ini;$ii<=$Dia_Fim;$ii++){
    $FWA_Dia = FWA_Zero_Esquerda($ii,2);
    @$FWA_Option_Dia.= "<option value='".$FWA_Dia."'>".$FWA_Dia."</option>\n";
  }
  return $FWA_Option_Dia;
}
?>