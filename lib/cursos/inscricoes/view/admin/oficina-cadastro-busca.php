<?php
//require_once(dirname(__FILE__)."/../../include/config.php");
//
//echo "<hr size='1' width='100%'>";
//
//#Definindo comando SQL
//      $var_sql = "
//                 SELECT cod_oficina, titulo, ordem
//                 FROM tab_opcoes_oficinas
//                 WHERE
//                 (cod_evento  = ".COD_EVENTO.")
//                 ORDER BY cod_oficina desc, ordem, titulo
//                ";
//
//  #echo $var_sql;
//
//  #Fim da definição do comando SQL
//
//  $Conexao_db = FWA_Conexao("OpcTec");
//
//  #Executando a consulta no banco SQL
//  $RS = $Conexao_db->Execute($var_sql);
//
//  #Verificando se houve retorno de algum registro
//  If (!$RS->EOF) {
//    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";
//
//    echo "
//    &nbsp;Selecione uma Atividade no campo abaixo:
//    <form action='index.php' method='get'>
//      <input type='hidden' name='content' value='view/admin/oficina-cadastro-ok.php'>
//      &nbsp;<select name='COD_OFICINA'>
//               <option value='-1'></option>
//    ";
//
//    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
//    While (!$RS->EOF) {
//
//      $COD_OFICINA_L = $RS->fields[0];
//      $TITULO_OPCAO= $RS->fields[1];
//      $ORDEM       = $RS->fields[2];
//      
//      #Abreviar titulos com nº de caracteres maiores q 50
//      $TITULO_OPCAO=substr($TITULO_OPCAO,0,120);
//      If(strlen($TITULO_OPCAO)==120){
//        $TITULO_OPCAO=$TITULO_OPCAO."...";
//      }
//
//      echo "<option value='{$COD_OFICINA_L}'>{$ORDEM}-{$COD_OFICINA_L}-{$TITULO_OPCAO}</option>";
//
//      #Movendo para o proximo registro
//      $RS->MoveNext();
//    }
//    echo "
//      </select>
//    <input type='submit' value='Buscar'  class='button btn btn-primary btn-xs'>
//    ";
//  }Else{
//
//    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhuma atividade cadastrada!<br><br>";
//
//  }
//</form>
//require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");
