<?php
Function FWA_Checked($Value_int,$Value_externo){
   If($Value_int==$Value_externo){
     $FWA_Checked = "Checked";
   }Else{
     $FWA_Checked = "";
   }
   return $FWA_Checked;
   #echo $Value_int."-".$Value_externo."-".$FWA_Checked."-".$_SESSION[$FWS_Name]."<br>";
}
?>