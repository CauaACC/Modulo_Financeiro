
<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/ligacao-convidados-areas-tematicas.js"></SCRIPT>
<FORM method="POST" name="form" action="view/admin/ligacao-convidados-areas-tematicas-incluir-grava.php" onsubmit="return criticar()">

<?php
   $Conexao_db = FWA_Conexao("OpcTec");

   #Definindo comando SQL
       $var_sql = "
                  SELECT c.cod_convidado, c.nome_convidado
                  FROM tab_convidados c
                    inner join sis_ligacao_usuarios_eventos lue ON lue.cod_usuario=c.cod_usuario and lue.status='ATIVADA'
                  WHERE
                  lue.cod_evento=".COD_EVENTO."
                  ORDER BY c.nome_convidado
                 ";

   #echo $var_sql;

   #Executando a consulta no banco SQL
   $RS = $Conexao_db->Execute($var_sql);

   #Verificando se houve retorno de algum registro
   If (!$RS->EOF) {
     #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

     echo "
     Selecione Convidado:<br>
       <select name='COD_CONVIDADO'>
       <option value='-1'>Selecione um Convidado</option>";

     #Efetuando loop para o visualização dos registros retornados pela consulta SQL
     While (!$RS->EOF) {

       $COD_CONVIDADO = $RS->fields[0];
       $NOME_CONVIDADO= $RS->fields[1];
       
       #Abreviar titulos com nº de caracteres maiores q 50
       $NOME_CONVIDADO=substr($NOME_CONVIDADO,0,50);
       If(strlen($NOME_CONVIDADO)==50){
         $NOME_CONVIDADO=$NOME_CONVIDADO."...";
       }
       
       echo "<option value='{$COD_CONVIDADO}' ".  verifItemSelected($COD_CONVIDADO, $fws_['cod_convidado']).">{$NOME_CONVIDADO}</option>";

       #Movendo para o proximo registro
       $RS->MoveNext();
     }
     echo "
       </select>
     ";
   }Else{
     echo "<br>Não há Convidado cadastrado!<br>";
   }

   echo "&nbsp;&nbsp;&nbsp;<a href='index.php?content=view/admin/login-cadastro-incluir.php'>Cadastrar login não listado</a>";
#------------------------------------------------------
   #Definindo comando SQL
       $var_sql = "
                  SELECT cod_area_tematica, ordem, titulo
                  FROM tab_opcoes_areas_tematicas
                  WHERE
                  (cod_evento  = ".COD_EVENTO.")
                  ORDER BY ordem
                 ";

   #echo $var_sql;

   #Executando a consulta no banco SQL
   $RS = $Conexao_db->Execute($var_sql);

   #Verificando se houve retorno de algum registro
   If (!$RS->EOF) {
     #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

     echo "<br><br>Para Associa-lo há Área-Tematica: Ordem - Titulo Area Tematica<br>
       <select name='cod_area_tematica'>
       <option value='-1'>Selecione uma Área-Tematica</option>";

     #Efetuando loop para o visualização dos registros retornados pela consulta SQL
     While (!$RS->EOF) {

       $cod_area_tematica = $RS->fields[0];
       $ORDEM             = $RS->fields[1];
       $NOME_AREA_TEMATICA= $RS->fields[2];

       #Abreviar titulos com nº de caracteres maiores q 50
       $NOME_AREA_TEMATICA=substr($NOME_AREA_TEMATICA,0,50);
       If(strlen($NOME_AREA_TEMATICA)==50){
         $NOME_AREA_TEMATICA=$NOME_AREA_TEMATICA."...";
       }

       $cod_area_tematica_Z = FWA_Zero_Esquerda($cod_area_tematica,2);
       $ORDEM_Z             = FWA_Zero_Esquerda($ORDEM,2);

       $selected = "";
       if($_GET['cod_area_tematica'] == $cod_area_tematica){
           $selected = "SELECTED";
       }
       
       echo "<option {$selected} value='{$cod_area_tematica}'>{$ORDEM_Z} - {$NOME_AREA_TEMATICA}</option>";

       #Movendo para o proximo registro
       $RS->MoveNext();
     }
     echo "
       </select>
     ";
   }Else{
     echo "<br>Não há Área-Temática cadastrada!<br>";
   }
echo "<br><br>
<input type='hidden' name='cod_evento' value='".COD_EVENTO."'>
<input type='submit' value='Confirmando Associação'>
</form>";


