<?php
// require_once(dirname(__FILE__)."/../../include/config.php");
// require_once(dirname(__FILE__)."/../../view/admin/area-tematica-seguranca.php");

// #Definindo comando SQL
//       $var_sql = "
//                  SELECT cod_area_tematica, ordem, titulo
//                  FROM tab_opcoes_areas_tematicas
//                  WHERE
//                  (cod_evento  = ".COD_EVENTO.") and (status='ATIVADO')
//                  ORDER BY ordem
//                 ";

//   #echo $var_sql;

//   #Fim da definição do comando SQL

//   $Conexao_db = FWA_Conexao("OpcTec");

//   #Executando a consulta no banco SQL
//   $RS = $Conexao_db->Execute($var_sql);

//   #Verificando se houve retorno de algum registro
//   If (!$RS->EOF) {
//     #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

//     echo "
//     &nbsp;Selecione uma Area Tematica no campo abaixo:<br>
//     &nbsp;<span style='font-size:10px'>Posição/Nome da Eixo Temático</span>
//     <form action='index.php' method='get'>
//       <input type='hidden' name='content' value='view/admin/area-tematica-cadastro-ok.php'>
//       &nbsp;<select name='cod_area_tematica'>
//                <option value='-1'>Selecione Uma Area Tematica</option>
//     ";

//     #Efetuando loop para o visualização dos registros retornados pela consulta SQL
//     While (!$RS->EOF) {

//       $cod_area_tematica = $RS->fields[0];
//       $ORDEM             = $RS->fields[1];
//       $TITULO_OPCAO      = $RS->fields[2];

//       #Abreviar titulos com nº de caracteres maiores q 50
//       $TITULO_OPCAO=substr($TITULO_OPCAO,0,80);
//       If(strlen($TITULO_OPCAO)==80){
//         $TITULO_OPCAO=$TITULO_OPCAO."...";
//       }
//        $cod_area_tematica_Z = FWA_Zero_Esquerda($cod_area_tematica,2);
//        $ORDEM_Z             = FWA_Zero_Esquerda($ORDEM,2);

//        echo "<option value='{$cod_area_tematica}'>{$ORDEM_Z}-{$TITULO_OPCAO}</option>";

//       #Movendo para o proximo registro
//       $RS->MoveNext();
//     }
//     echo "
//       </select>
//     <input type='submit' value='Buscar'>
//     ";
//   }Else{

//     echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhuma AREA TEMATICA cadastrada!<br><br>";

//   }
// 
// </form>
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");