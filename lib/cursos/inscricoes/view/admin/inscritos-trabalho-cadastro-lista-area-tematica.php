<?php
Function FWA_Select_Inscritos(){
#------------------------------------------------------
   #Definindo comando SQL
       $var_sql = "
                  SELECT cod_area_tematica, ordem, titulo
                  FROM tab_opcoes_areas_tematicas
                  WHERE
                  (cod_evento  = ".COD_EVENTO.")
                  ORDER BY ordem
                 ";

   #Executando a consulta no banco SQL
 If($RS_AT=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
   #Verificando se houve retorno de algum registro
   If (!$RS_AT->EOF) {
       $FWA_Select_Inscritos.="
       <select name='cod_area_tematica'>
       {$OPTION_Area_Tematica}
       ";

     #Efetuando loop para o visualização dos registros retornados pela consulta SQL
     While (!$RS_AT->EOF) {

       $cod_area_tematica = $RS_AT->fields[0];
       $ORDEM             = $RS_AT->fields[1];
       $NOME_AREA_TEMATICA= $RS_AT->fields[2];

       #Abreviar titulos com nº de caracteres maiores q 50
       $NOME_AREA_TEMATICA=substr($NOME_AREA_TEMATICA,0,50);
       If(strlen($NOME_AREA_TEMATICA)==50){
         $NOME_AREA_TEMATICA=$NOME_AREA_TEMATICA."...";
       }

       $cod_area_tematica_Z = FWA_Zero_Esquerda($cod_area_tematica,2);
       $ORDEM_Z             = FWA_Zero_Esquerda($ORDEM,2);

       $FWA_Select_Inscritos.="<option value='{$cod_area_tematica}'>{$cod_area_tematica_Z}-{$ORDEM_Z}-{$NOME_AREA_TEMATICA}</option>";

       #Movendo para o proximo registro
       $RS_AT->MoveNext();
     }
     $FWA_Select_Inscritos.="
       </select>
     ";
   }Else{
     $FWA_Select_Inscritos.="<br>Não há Área-Temática cadastrada!<br>";
   }
 }Else{
   $FWA_Select_Inscritos.="<br>Erro na Consulta<br>";
 }

return $FWA_Select_Inscritos;

}
?>
