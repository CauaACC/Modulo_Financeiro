<?php
Function FWA_Option_Ano($NumOption=false){
  if(!$NumOption)
      $NumOption= 5;
  $Ano_Ini = Date('Y')-1;
  $Ano_Fim = $Ano_Ini+$NumOption;
  For($ii=$Ano_Ini;$ii<=$Ano_Fim;$ii++){
    @$FWA_Option_Ano.= "<option value='".$ii."'>".$ii."</option>\n";
  }
  return $FWA_Option_Ano;
}
?>