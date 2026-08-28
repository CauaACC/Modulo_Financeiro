<?php
Function FWA_Select_Area_Tematica($cod_area_tematica,$titulo_area_tematica,$cod_evento){
  $FWS_Debug = false;

  If(isset($cod_area_tematica)){
    $OPTION_Area_Tematica = "<option selected value='".$cod_area_tematica."'>".$titulo_area_tematica."</option>";
  }Else{
    $OPTION_Area_Tematica = "<option selected value='-1'>Selecione Area Temática</option>";
  }

  #Definindo comando SQL
      $var_sql = "
                 SELECT cod_area_tematica, ordem, titulo
                 FROM tab_opcoes_areas_tematicas
                 WHERE
                 (cod_evento  = ".COD_EVENTO.")
                 ORDER BY ordem
                ";

  #Fim da definição do comando SQL

  $Conexao_db = FWA_Conexao("OpcTec");

  #Executando a consulta no banco SQL
  If($RS_AT=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
    #Verificando se houve retorno de algum registro
    If (!$RS_AT->EOF) {
        $FWA_Select_Area_Tematica.="
        <select name='COD_AREA_TEMATICA'>
        {$OPTION_Area_Tematica}
        ";

      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS_AT->EOF) {

        $COD_AREA_TEMATICA = $RS_AT->fields[0];
        $ORDEM             = $RS_AT->fields[1];
        $NOME_AREA_TEMATICA= $RS_AT->fields[2];

        #Abreviar titulos com nº de caracteres maiores q 50
        $NOME_AREA_TEMATICA=substr($NOME_AREA_TEMATICA,0,50);
        If(strlen($NOME_AREA_TEMATICA)==50){
          $NOME_AREA_TEMATICA=$NOME_AREA_TEMATICA."...";
        }

        $COD_AREA_TEMATICA_Z = FWA_Zero_Esquerda($COD_AREA_TEMATICA,2);
        $ORDEM_Z             = FWA_Zero_Esquerda($ORDEM,2);

        $FWA_Select_Area_Tematica.="<option value='{$COD_AREA_TEMATICA}'>{$COD_AREA_TEMATICA_Z}-{$ORDEM_Z}-{$NOME_AREA_TEMATICA}</option>";

        #Movendo para o proximo registro
        $RS_AT->MoveNext();
      }
      $FWA_Select_Area_Tematica.="
        </select>
      ";
    }Else{
      $FWA_Select_Area_Tematica.="<br>Não há Área-Temática cadastrada!<br>";
    }
  }Else{
    $FWA_Select_Area_Tematica.="<br>Erro na Consulta<br>";
  }

return $FWA_Select_Area_Tematica;
}
?>
