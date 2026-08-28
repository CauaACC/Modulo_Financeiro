<?php
Function FWA_Zero_Esquerda($VarStr,$NumLen){
  #$NumLen
  #$VarStr
  $VarLen  = strlen($VarStr);
  $NumZeros=$NumLen-$VarLen;
  If($NumZeros>0){
    For($ii=1;$ii<=$NumZeros;$ii++){
      $VarStr = "0".$VarStr;
    }
  }
  $FWA_Zero_Esquerda = $VarStr;
  return $FWA_Zero_Esquerda;
}
?>