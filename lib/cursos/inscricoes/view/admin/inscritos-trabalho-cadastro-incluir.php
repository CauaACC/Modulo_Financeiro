<?php
require_once(dirname(__FILE__) . "/../../include/config.php");

require_once(dirname(__FILE__) . "/../../view/admin/inscritos-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-busca.php");

#--------------------------------------------
@$cod_inscrito = $_GET['cod_inscrito'];

#Definindo comando SQL
$var_sql = "
             SELECT cod_inscrito, nome_inscrito
             FROM tab_inscritos
             WHERE
             (cod_evento  = " . COD_EVENTO . ")and
             (cod_inscrito= " . $cod_inscrito . ")
             ORDER BY nome_inscrito
            ";

#Fim da definição do comando SQL

$Conexao_db = FWA_Conexao("OpcTec");

#Executando a consulta no banco SQL
if ($RS_Ins = FWA_Executa_SQL($FWS_Debug, $Conexao_db, $var_sql)) {
  #Verificando se houve retorno de algum registro
  if (!$RS_Ins->EOF) {
    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    while (!$RS_Ins->EOF) {
      $cod_inscrito  = $RS_Ins->fields[0];
      $nome_inscrito = $RS_Ins->fields[1];
      $RS_Ins->MoveNext();
    }
  }
}
if ($cod_inscrito != '') {
  try {
    $objI = new Inscrito();
    $objI->setCodInscrito($cod_inscrito);
    $objI->getInscritoById();
    echo "<h4>".$objI->getCodInscrito()." - ".$objI->getNomeInscrito()."</h4>";
  } catch (Exception $e) {
    // Captura a exceção e executa o código aqui
    echo "Não foi possível carregar inscrito: " . $e->getMessage();
  }
}
?>
<hr size='1' width='100%'>
<span style class='texto'> <B> Ficha de Cadastro do Trabalho</B></span><br>
<span style class='textop'> <B> * Campos Obrigatórios</B></span>
<span style class='texto'>

  <SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/inscricao_trabalhos.js"></SCRIPT>

  <!--FORM method="POST" name="form" action="view/admin/inscritos-trabalho-cadastro-incluir-grava.php" onsubmit="return criticar()"-->
  <FORM method="POST" name="form" action="view/admin/inscritos-trabalho-cadastro-grava.php" onsubmit="return criticarTrabalho9CBMP();" enctype="multipart/form-data">
    <input type='hidden' name='COD_EVENTO' value='<?php echo $COD_EVENTO ?>'>
    <input type='hidden' name='cod_inscrito' value='<?php echo $cod_inscrito ?>'>
    <input type='hidden' name='trabalho_tipo' value='<?php echo $objEvConf->getTrabAnexoTipo() ?>'>
    <fieldset width=570>
      <legend> Dados do Trabalho </legend>

      <?php
      $objTrabalho = new Trabalho();
      $obj = $objTrabalho;
      include_once("inscritos-trabalho-cadastro-campos.php");
      ?>

    </fieldset>

    <table border=0 width=565>
      <tr>
        <td colspan=2 align=center>
          <br><br>
          <input type='submit' value='Cadastrar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
        </td>
      </tr>
    </table>

  </FORM>