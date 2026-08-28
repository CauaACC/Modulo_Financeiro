<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/atuacoes-convidados-seguranca.php");

echo "<hr size='1' width='100%'>";

#Definindo comando SQL
      $var_sql = "
                 SELECT cod_atuacao_convidado, titulo, ordem
                 FROM tab_opcoes_atuacoes_convidados
                 WHERE
                 (cod_evento  = ".COD_EVENTO.")
                 ORDER BY ordem, titulo
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
    &nbsp;Selecione uma Atuação do Convidado no campo abaixo:
    <form action='index.php' method='get'>
      <input type='hidden' name='content' value='view/admin/atuacoes-convidados-cadastro-ok.php'>
      &nbsp;<select name='COD_ATUACAO_CONVIDADO'>
               <option value='-1'>Selecione Uma Atuação do Convidado</option>
    ";

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    While (!$RS->EOF) {

      $COD_ATUACAO_CONVIDADO = $RS->fields[0];
      $TITULO_OPCAO= $RS->fields[1];
      $ORDEM       = $RS->fields[2];
      
      #Abreviar titulos com nº de caracteres maiores q 50
      $TITULO_OPCAO=substr($TITULO_OPCAO,0,50);
      If(strlen($TITULO_OPCAO)==50){
        $TITULO_OPCAO=$TITULO_OPCAO."...";
      }

      echo "<option value='{$COD_ATUACAO_CONVIDADO}'>{$ORDEM}-{$TITULO_OPCAO}</option>";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }
    echo "
      </select>
    <input type='submit' value='Buscar'>
    ";
  }Else{

    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhuma Atuação do Convidado cadastrada!<br><br>";

  }
?>
</form>