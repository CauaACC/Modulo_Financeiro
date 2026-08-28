
<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/ligacao-usuarios-grupos.js"></SCRIPT>
<FORM method="POST" name="form" action="view/admin/ligacao-usuarios-grupos-incluir-grava.php" onsubmit="return criticar()">

<?php
   $Conexao_db = FWA_Conexao("OpcTec");
   
   #Definindo comando SQL
       $var_sql = "
                  SELECT u.cod_usuario, u.login
                  FROM sis_usuarios u
                  inner join sis_ligacao_usuarios_eventos lue ON lue.cod_usuario=u.cod_usuario
                  WHERE
                  (lue.cod_evento  = ".COD_EVENTO.")
                  ORDER BY login
                 ";
 
   #echo $var_sql;
  
   #Executando a consulta no banco SQL
   $RS = $Conexao_db->Execute($var_sql);
 
   #Verificando se houve retorno de algum registro
   If (!$RS->EOF) {
     #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";
 
     echo "
     Selecione Login:<br>
       <select name='COD_USUARIO'>
       <option value='-1'>Selecione um Login</option>";
 
     #Efetuando loop para o visualização dos registros retornados pela consulta SQL
     While (!$RS->EOF) {
 
       $COD_USUARIO = $RS->fields[0];
       $LOGIN= $RS->fields[1];
       
       #Abreviar nome_grupos com nº de caracteres maiores q 50
       $LOGIN=substr($LOGIN,0,50);
       If(strlen($LOGIN)==50){
         $LOGIN=$LOGIN."...";
       }
 
       echo "<option value='{$COD_USUARIO}' ".  verifItemSelected($COD_USUARIO, $fws_['cod_usuario']).">{$LOGIN}</option>";
 
       #Movendo para o proximo registro
       $RS->MoveNext();
     }
     echo "</select> &nbsp;&nbsp;&nbsp;";
     
     echo "
     <a href='index.php?content=view/admin/login-cadastro-incluir.php'>
          Cadastrar usuário</a>
     ";
     
   }Else{
     echo "<br>Não há Login cadastrado!<br>";
   }
   
#------------------------------------------------------
   #Definindo comando SQL
//       $var_sql = "
//                  SELECT cod_grupo, nome_grupo
//                  FROM sis_grupos
//                  WHERE
//                  (cod_evento  = ".COD_EVENTO.")
//                  ORDER BY nome_grupo
//                 ";
//        Alterado pois todos os grupos devem estar disponíveis a todos os eventos
       $var_sql = "
                  SELECT cod_grupo, nome_grupo
                  FROM sis_grupos
                  WHERE cod_grupo <> 999
                  ORDER BY nome_grupo
                 ";
 
   #echo $var_sql;
  
   #Executando a consulta no banco SQL
   $RS = $Conexao_db->Execute($var_sql);
 
   #Verificando se houve retorno de algum registro
   If (!$RS->EOF) {
     #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";
 
     echo "<br><br>Para Associa-lo há um GRUPO:<br>
       <select name='COD_GRUPO' id='COD_GRUPO' v-model=\"codGrupo\">
       <option value='-1'>Selecione uma Grupo</option>";
 
     #Efetuando loop para o visualização dos registros retornados pela consulta SQL
     While (!$RS->EOF) {
 
       $COD_GRUPO = $RS->fields[0];
       $NOME_GRUPO= $RS->fields[1];
       
       #Abreviar nome_grupos com nº de caracteres maiores q 50
       $NOME_GRUPO=substr($NOME_GRUPO,0,50);
       If(strlen($NOME_GRUPO)==50){
         $NOME_GRUPO=$NOME_GRUPO."...";
       }
 
       echo "<option value='{$COD_GRUPO}' ".verifItemSelected($COD_GRUPO, $fws_['cod_grupo']).">{$NOME_GRUPO}</option>";
 
       #Movendo para o proximo registro
       $RS->MoveNext();
     }
     echo "
       </select>
     ";
   }Else{
     echo "<br>Não há Grupo cadastrada!<br>";
   }
echo "<br><br>
<input type='hidden' name='cod_evento' value='".COD_EVENTO."'>
<input type='checkbox' name='enviar_aviso' value='ON'/> Enviar comunicado por e-mail?
<input type='submit' value='Confirmando Associação'>
</form>";

?>
