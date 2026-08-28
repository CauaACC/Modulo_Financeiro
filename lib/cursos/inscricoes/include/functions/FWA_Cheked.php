<?php
 Function FWA_Checked($Value_int,$Value_externo){
   If($Value_int==$Value_externo){
     $FWA_Checked = "checked";
   }Else{
     $FWA_Checked = "";
   }
   return $FWA_Checked;
   echo $FWA_Checked;
 }
?>
