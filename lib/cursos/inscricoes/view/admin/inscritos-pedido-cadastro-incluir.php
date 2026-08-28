<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-busca.php");

#--------------------------------------------
@$cod_inscrito = $_GET['cod_inscrito'];
$objI = new Inscrito();
$objI->setCodInscrito($cod_inscrito);
$objI->getInscritoByIdSemEvento();
if($objI->getCodEvento()!=''){
//    $objI->get();
    $cod_evento = $objI->getCodEvento(); 
}else{
    $cod_evento = COD_EVENTO; 
}
$objEv = new Evento();
$objEv->setCodEvento($cod_evento);
$objEv->getEventoById();

$objI->carregarOjbPerfil($debug);
$objPerfil = $objI->getObjPerfil();
$condicoes['data_hoje'] = Date('Y-m-d H:i:s');
try {
    $objPerfil->carregarTaxaAtiva($condicoes, $debug);
    $objTaxa= $objPerfil->getObjTaxa();
    $valorTaxa = $objTaxa->getTxUnicaAVistaValor();
} catch (Exception $exc) {
    echo "<font color=red>".$exc->getMessage()."</font><br>";
    $valorTaxa = "";
}


  #Definindo comando SQL
  $var_sql = "
             SELECT cod_inscrito, nome_inscrito
             FROM tab_inscritos
             WHERE
             (cod_evento  = ".$cod_evento.")and
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
<span style class='texto'> <B> Ficha de Cadastro de Pedido</B></span><br>
<span style class='textop'> <B> * Campos Obrigatórios</B></span>
<span style class='texto'>
          <a href='<?php echo WB_URL_VIEW ?>?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=<?php echo $cod_inscrito ?>'><button type='button' style='margin-left:15px;'>Voltar</button></a> 
<FORM method="POST" name="form" action="view/admin/inscritos-pedido-cadastro-incluir-grava.php" onsubmit="return criticar()">

<fieldset width=570><legend> Dados do Pedido </legend>

<?php
include_once("inscritos-pedido-cadastro-campos.php");
?>

</fieldset>

<table border=0 width=565>
<tr><td colspan=2 align=center>
      <input type='submit' value='Cadastrar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
</td></tr>
</table>

</FORM>
</div></div></div></div></div>
