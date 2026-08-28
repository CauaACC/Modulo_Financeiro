<script language="JavaScript" type="text/javascript">
$(document).ready(function () {
    $('#btnBuscarGruposConvidado').click(function(){
        buscarGruposConvidado($(this).data('cod_convidado'))
        //$('#cod_convidado').val();
    });
});
function buscarGruposConvidado(cod_convidado){
    if(cod_convidado){
        $('#cod_convidado').val(cod_convidado);
        $('#buscarGruposConvidado').submit();
    }
}
</script>
<form action='index.php' method='get' name='buscarGruposConvidado' id='buscarGruposConvidado'>
    <input type='hidden' name='content' value='view/admin/convidados-cadastro-ok-listagem-grupos.php'>
    <input type='hidden' name='COD_CONVIDADO' id='cod_convidado' value=''>
</form>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");

echo "<hr size='1' width='100%'>";

#Definindo comando SQL
      $var_sql = "SELECT tc.cod_convidado, tc.nome_convidado
                 FROM tab_convidados tc
		 INNER JOIN sis_usuarios u ON u.cod_usuario=tc.cod_usuario 
		 INNER JOIN sis_ligacao_usuarios_eventos lue ON u.cod_usuario=lue.cod_usuario AND lue.cod_evento = ".COD_EVENTO." 
                 ORDER BY tc.nome_convidado";

  #echo $var_sql;

  #Fim da definição do comando SQL

  $Conexao_db = FWA_Conexao("OpcTec");

  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);

  #Verificando se houve retorno de algum registro
  If (!$RS->EOF) {
    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

    echo "<span style class=\"textop\">
    &nbsp;Selecione um Convidado no campo abaixo:
    <form action='index.php' method='get'>
      <input type='hidden' name='content' value='view/admin/convidados-cadastro-ok.php'>
      &nbsp;<select name='COD_CONVIDADO'>
               <option value='-1'>Selecione Um Convidado</option>
    ";

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    While (!$RS->EOF) {

      $COD_CONVIDADO  = $RS->fields[0];
      $NOME_CONVIDADO = $RS->fields[1];
            
      #Abreviar nome_convidados com nº de caracteres maiores q 50
      $NOME_CONVIDADO=substr($NOME_CONVIDADO,0,50);
      If(strlen($NOME_CONVIDADO)==50){
        $NOME_CONVIDADO=$NOME_CONVIDADO."...";
      }

      echo "<option value='{$COD_CONVIDADO}'>{$ORDEM}-{$NOME_CONVIDADO}</option>";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }
    echo "
      </select>
    <input type='submit' value='Buscar'>
    ";
  }Else{

    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhum Convidado cadastrado!<br><br>";

  }
?>

</form>
