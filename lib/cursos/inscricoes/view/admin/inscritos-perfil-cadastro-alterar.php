<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-busca.php");

#--------------------------------------------------------------------
$FWS_Debug = false;
@$cod_inscrito = $_GET['cod_inscrito'];
@$cod_perfil  = $_GET['cod_perfil'];

$MSG           = $_SESSION['MSG'];

$MSG_Imprimir = $MSG;
$loc   = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

If($MSG_Imprimir){
  echo "<hr size='1' width='100%'>";
  echo $MSG_Imprimir;
  #echo "<hr size='1' width='100%'>";
}

#--------------------------------------------------------------------
If(isset($cod_perfil)){

  #echo $cod_perfil."<br>";

  $Conexao_db = FWA_Conexao("OpcTec");

  $var_Sql = "
               SELECT
                 ti.cod_inscrito
                ,ti.nome_inscrito
                ,tli.cod_perfil
                ,too.titulo
                FROM tab_inscritos ti, tab_lig_inscritos_perfis tli, tab_opcoes_perfis too
                WHERE
                (tli.cod_perfil  = ".$cod_perfil.")AND
                (tli.cod_evento   = ".COD_EVENTO.")AND
                (tli.cod_inscrito = ".$cod_inscrito.")AND
                (tli.cod_inscrito = ti.cod_inscrito)AND
                (tli.cod_perfil  = too.cod_perfil )
               ";

  If($RS_Of=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_Sql)){
    If (!$RS_Of->EOF) {
      While (!$RS_Of->EOF) {
      
        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $cod_inscrito   = $RS_Of->fields[0];
        $nome_inscrito  = $RS_Of->fields[1];
        $cod_perfil    = $RS_Of->fields[2];
        $titulo_perfil = $RS_Of->fields[3];
        
        #Definindo tabela com resultado

        echo "
        <span style class='texto'> <B> Ficha de Alteração de Perfil</B><br>
        <span style class='textop'> <B> * Campos Obrigatórios</B></span>

        <table border='0' width='565'>
        <tr><td colspan='2' align='left'>
        
        <SCRIPT LANGUAGE=\"JavaScript1.2\" SRC=\"scripts/inscritos-perfil-cadastro.js\"></SCRIPT>

        <FORM method='POST' name='form' action='view/admin/inscritos-perfil-cadastro-alterar-grava.php' onsubmit=\"return criticar()\">
          <input type='hidden' name='COD_EVENTO'    value='{$COD_EVENTO}'>
          <input type='submit' value='Alterar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
          <br>&nbsp;
        </td></tr>
        </table>
        <fieldset width='570'><legend> Associar Inscrito a um Perfil</legend>
        ";

        require_once("inscritos-perfil-cadastro-campos.php");

        echo "
        </fieldset>
        <table border='0' width='565'>
        <tr><td colspan='2' align='left'>
              <br><br>
              <input type='submit' value='Alterar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
        </td></tr>
        </table>
        </FORM>
        ";

        #Movendo para o proximo registro
        $RS_Of->MoveNext();
      }


    }Else{
      echo "Codigo não encontrado.";
    }
  }Else{
    echo "Problema no SQL.";
  }
}Else{
  echo "É Necessário Codigo do Perfil.";
}

?>
