<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-seguranca.php");

echo "<hr size='1' width='100%'>";
echo "";
#Definindo comando SQL
      $var_sql = "
                 SELECT toa.cod_area_tematica, toa.titulo
                 FROM tab_opcoes_areas_tematicas toa
                 WHERE
                 (toa.cod_evento = ".COD_EVENTO.")
                 ORDER BY toa.ordem
                ";

  #echo $var_sql;

  #Fim da definição do comando SQL

  $Conexao_db = FWA_Conexao("OpcTec");

  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);

  #Verificando se houve retorno de algum registro
  If (!$RS->EOF) {
    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

    echo "
    &nbsp;Selecione uma Area Tematica no campo abaixo:
    <form action='index.php' method='post'>
      <input type='hidden' name='content' value='view/admin/trabalhos-cadastro-listagem.php'>
      &nbsp;<select name='cod_area_tematica'>
               <option value='-1'>Selecione Uma Area Tematica</option>
    ";

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    While (!$RS->EOF) {

      $cod_area_tematica = $RS->fields[0];
      $TITULO_OPCAO      = $RS->fields[1];

      #Abreviar titulos com nº de caracteres maiores q 50
      $TITULO_OPCAO=substr($TITULO_OPCAO,0,50);
      If(strlen($TITULO_OPCAO)==50){
        $TITULO_OPCAO=$TITULO_OPCAO."...";
      }

      echo "<option value='{$cod_area_tematica}'>{$cod_area_tematica}-{$TITULO_OPCAO}</option>";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }
    echo "
      </select>
    <input type='submit' value='Buscar'>
    ";
  }Else{

    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhuma AREA TEMATICA cadastrada!<br><br>";

  }
?>

</form>
<hr size='1' width='100%'>
