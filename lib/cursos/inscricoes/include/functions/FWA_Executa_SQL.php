<?php
Function FWA_Executa_SQL($var_Debug,$var_Conexao,$var_sql){

//  If($var_Debug==1){ echo "<br>".$var_sql."<br>"; };
  $var_Conexao->debug = $var_Debug;
  If (!$RS=$var_Conexao->Execute($var_sql)) { //Executando o Insert
    $FWA_Executa_SQL = false;
  }Else{
    $FWA_Executa_SQL = $RS;
  }
  return $FWA_Executa_SQL;
}


//Function FWA_Executa_SQL($var_Debug, $var_Conexao, $var_sql) {
//
//    try {
//        $var_Conexao->debug=$var_Debug;
//        $RS = $var_Conexao->Execute($var_sql);
//        if (!$RS->EOF) {
//            return $RS;
//        }      
//    } catch (ADODB_Exception $ex) {
//        echo $ex->getTrace();
//    }
//    return false;
//    
//}

