<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/re-avaliacao-trabalhos-seguranca.php");
echo "<hr size='1' width='100%'>";

#Definindo comando SQL
$var_sql = "
         SELECT toa.cod_area_tematica, toa.ordem, toa.titulo
         FROM tab_opcoes_areas_tematicas toa
         WHERE
         (".COD_EVENTO." = toa.cod_evento)
         ORDER BY toa.ordem
        ";

  //echo $var_sql;
  #Fim da definição do comando SQL
  $Conexao_db = FWA_Conexao("OpcTec");
  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);
  
  #Verificando se houve retorno de algum registro
  If (!$RS->EOF) {
    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

    echo "
    Selecione uma Area Tematica no campo abaixo.:<br><br>

    <form action='index.php?content=view/admin/re-avaliacao-trabalhos-listagem.php' method='post'>
        <select name='cod_area_tematica' class='form-control'>
            <option value='-1'>Selecione Uma Area Tematica</option>
    ";

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    While (!$RS->EOF) {

      $COD_AREA_TEMATICA = $RS->fields[0];
      $ORDEM             = $RS->fields[1];
      $TITULO_OPCAO      = $RS->fields[2];

      #Abreviar titulos com nº de caracteres maiores q 50
//      $TITULO_OPCAO=substr($TITULO_OPCAO,0,50);
//      If(strlen($TITULO_OPCAO)==50){
//        $TITULO_OPCAO=$TITULO_OPCAO."...";
//      }

      $COD_AREA_TEMATICA_Z = FWA_Zero_Esquerda($COD_AREA_TEMATICA,2);
      $ORDEM_Z             = FWA_Zero_Esquerda($ORDEM,2);

      if($RS->RecordCount()<=1){
          $fws_['cod_area_tematica'] = $COD_AREA_TEMATICA;
      }
      
      echo "<option ";
      echo verifItemSelected($fws_['cod_area_tematica'], $COD_AREA_TEMATICA);
      echo " value='{$COD_AREA_TEMATICA}'>{$TITULO_OPCAO}</option>";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }
    echo "</select>";

    If($FWS_Form_Detalhes==true){
      If(@$_POST['detalhes']==1){
        $checked_detalhes_1 = "checked";
      }Else{
        $checked_detalhes_0 = "checked";
      }
      echo "<!--<b>Alternar lista de trabalhos </b>:-->
            <br>    
            &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='1' ".$checked_detalhes_1."> Trabalhos Avaliados
            &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='0' ".$checked_detalhes_0."> Trabalhos a serem Avaliados
            ";    
    }
    echo "&nbsp;&nbsp;<input type='submit' value='Listar' class='btn btn-xs btn-primary'><br><br>";
    echo "</form>";
    
  }Else{

    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Nenhuma ÀREA TEMÁTICA cadastrada para o usuário logado!<br><br>";

  }
?>

</form>
<hr size='1' width='100%'>
