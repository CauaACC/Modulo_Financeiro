<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-busca.php");

#--------------------------------------------
@$cod_inscrito = $_GET['cod_inscrito'];

  #Definindo comando SQL
  $var_sql = "
             SELECT cod_inscrito, nome_inscrito
             FROM tab_inscritos
             WHERE
             (cod_evento  = ".COD_EVENTO.")and
             (cod_inscrito= ".$cod_inscrito.")
             ORDER BY nome_inscrito
            ";

  #Fim da definição do comando SQL

  $Conexao_db = FWA_Conexao("OpcTec");

  #Executando a consulta no banco SQL
  If($RS_Ins=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
    #Verificando se houve retorno de algum registro
    If (!$RS_Ins->EOF) {
      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS_Ins->EOF) {
        $cod_inscrito  = $RS_Ins->fields[0];
        $nome_inscrito = $RS_Ins->fields[1];
        $RS_Ins->MoveNext();
      }
    }
  }

?>
<hr size='1' width='100%'>
<span style class='texto'> <B> Ficha de Inscrição em Perfil</B></span><br>
<span style class='textop'><B> * Campos Obrigatórios</B></span>
<span style class='texto'>

<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/inscritos-perfil-cadastro.js"></SCRIPT>

<FORM method="POST" name="form" action="view/admin/inscritos-perfil-cadastro-incluir-grava.php" onsubmit="return criticar()">

<fieldset width=570><legend> Associar Inscrito a um  Perfils</legend>

<?php
include_once("inscritos-perfil-cadastro-campos.php");
?>

</fieldset>

<table border=0 width=565>
<tr><td colspan=2 align=center>
      <br><br>
      <input type='submit' value='Cadastrar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
</td></tr>
</table>

</FORM>

