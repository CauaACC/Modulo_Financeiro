<?php
Function FWA_JaVotou($cod_eleicao,$cod_eleitor){
  $var_sql_2 = "
                SELECT * 
                FROM votos_identificados 
                WHERE 
                  (cod_eleitor=".$cod_eleitor.") and 
                  (cod_eleicao=".$cod_eleicao.")
               ";
  
  #echo $var_sql_2."<br>";
  $Conexao_db = FWA_Conexao(FWA_Dominio());
  $RS=$Conexao_db->Execute($var_sql_2);
  If(!$RS->EOF){
    $FWA_JaVotou = true;
  }Else{
    $FWA_JaVotou = false;    
  }
return $FWA_JaVotou;
}
?>