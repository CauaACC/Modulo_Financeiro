<?php
Function FWA_Option_Mes(){
  $Mes_Ini = 01;
  $Mes_Fim = 12;
  For($ii=$Mes_Ini;$ii<=$Mes_Fim;$ii++){
    $FWA_MES = FWA_Zero_Esquerda($ii,2);
    @$FWA_Option_Mes.= "<option value='".$FWA_MES."'>".$FWA_MES."</option>\n";
  }
  return $FWA_Option_Mes;
}
?>