<?php
//require_once(dirname(__FILE__)."/../../include/config.php");
//require_once(dirname(__FILE__)."/../../view/admin/taxa-seguranca.php");
//
//#Definindo comando SQL
//      $var_sql = "
//                 SELECT cod_taxa, ordem, titulo, cod_taxa
//                 FROM tab_opcoes_taxas
//                 WHERE
//                 (cod_evento  = ".COD_EVENTO.")
//                 ORDER BY cod_taxa,cod_oficina, ordem
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
//    &nbsp;Selecione uma Taxa no campo abaixo:<br>
//    &nbsp;<span style='font-size:10px'>Posição/Nome da Eixo Temático</span>
//    <form action='index.php' method='get'>
//      <input type='hidden' name='content' value='view/admin/taxa-cadastro-ok.php'>
//      &nbsp;<select name='cod_taxa'>
//               <option value='-1'>Selecione Uma Taxa</option>
//    ";
//
//    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
//    While (!$RS->EOF) {
//
//      $cod_taxa = $RS->fields[0];
//      $ORDEM             = $RS->fields[1];
//      $TITULO_OPCAO      = $RS->fields[2];
//      $cod_taxa      = $RS->fields[3];
//
//      #Abreviar titulos com nº de caracteres maiores q 50
//      $TITULO_OPCAO=substr($TITULO_OPCAO,0,50);
//      If(strlen($TITULO_OPCAO)==50){
//        $TITULO_OPCAO=$TITULO_OPCAO."...";
//      }
//       $cod_taxa_Z = FWA_Zero_Esquerda($cod_taxa,2);
//       $ORDEM_Z             = FWA_Zero_Esquerda($ORDEM,2);
//
//       echo "<option value='{$cod_taxa}'>Taxa: {$cod_taxa}-{$TITULO_OPCAO}</option>";
//
//      #Movendo para o proximo registro
//      $RS->MoveNext();
//    }
//    echo "
//      </select>
//    <input type='submit' value='Buscar'>
//    ";
//  }Else{
//
//    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhuma TAXA cadastrada!<br><br>";
//
//  }
//</form>
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");
$objEvAlt = new Evento();
$objEvAlt->setCodEvento($cod_evento);
$objEvAlt->getEventoById();
echo "<h4>Evento: ".$objEvAlt->getTitulo()."</h4>";
