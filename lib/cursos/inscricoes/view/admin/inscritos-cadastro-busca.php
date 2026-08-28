<?php
require_once(dirname(__FILE__)."/../../include/config.php");
echo "<hr size='1' width='100%'>";

#Definindo comando SQL
      $var_sql = "
                 SELECT cod_inscrito, nome_inscrito
                 FROM tab_inscritos
                 WHERE
                 (cod_evento  = ".COD_EVENTO.")
                 ORDER BY nome_inscrito
                ";

  #echo $var_sql;

  #Fim da definição do comando SQL

  $Conexao_db = FWA_Conexao("OpcTec");

  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);

  #Verificando se houve retorno de algum registro
  If (!$RS->EOF) {
    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

    echo "<span style class=\"textop\">
    &nbsp;Selecione um Inscrito no campo abaixo:
    <form action='index.php' method='get'>
      <input type='hidden' name='content' value='view/admin/inscritos-cadastro-ok.php'>
      &nbsp;<select name='cod_inscrito'>
               <option value='-1'>Selecione Um Inscrito</option>
    ";

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    While (!$RS->EOF) {

      $COD_INSCRITO_B  = $RS->fields[0];
      $NOME_INSCRITO = $RS->fields[1];
            
      #Abreviar nome_inscritos com nº de caracteres maiores q 50
      $NOME_INSCRITO=substr($NOME_INSCRITO,0,50);
      If(strlen($NOME_INSCRITO)==50){
        $NOME_INSCRITO=$NOME_INSCRITO."...";
      }

      echo "<option value='".$COD_INSCRITO_B."'>".FWA_Zero_Esquerda($COD_INSCRITO_B,4)."-".$NOME_INSCRITO."</option>";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }
    echo "
      </select>
    <input type='submit' value='Buscar'>
    ";
  }Else{

    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhum Inscrito cadastrado!<br><br>";

  }
?>

</form>
