<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/taxa-seguranca.php");

$debug = false;
$cod_taxa = $_GET['cod_taxa'];
$acao     = $_GET['acao'];

echo $debug? $acao . "<-<BR>" : "";
#-------------------------------------------
if (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados

  $MSG     = "Operação-Não-Executada,-Erro-de-Conexão";
  $_SESSION['MSG'] = $MSG;
  $CONTENT = "view/admin/perfil-cadastro-ok.php";
  $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $cod_taxa;
} else {

  if ($acao == "Excluir") {
    $var_sql = "
                    INSERT INTO tab_opcoes_taxas_excluidos
                    SELECT *
                    FROM tab_opcoes_taxas
                    WHERE (cod_taxa = " . $cod_taxa . ")
                  ";
    echo $debug? $var_sql."- <BR>" : "";
    $RS = $Conexao_db->Execute($var_sql);

    $var_sql = "
                    SELECT cod_taxa
                    FROM tab_opcoes_taxas_excluidos
                    WHERE (cod_taxa = " . $cod_taxa . ")
                  ";

    echo $debug? $var_sql."- <BR>" : "";
    $RS_select = $Conexao_db->Execute($var_sql);

    if (!$RS_select->EOF) {
      $cod_taxa = $RS_select->fields[0];

      $var_sql = "DELETE FROM tab_opcoes_taxas WHERE (cod_taxa = " . $cod_taxa . ")";
      $RS      = $Conexao_db->Execute($var_sql);

      $MSG     = "Operação-Executada-com-Sucesso";
      $_SESSION['MSG'] = $MSG;
      $CONTENT = "view/admin/perfil-cadastro-ok.php";
      $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $cod_taxa;


      #echo $var_sql."- <BR>";

    } else {
      $MSG     = "Operação-Não-Executada,-Erro-ao-tentar-Excluir";
      $_SESSION['MSG'] = $MSG;
      $CONTENT = "view/admin/perfil-cadastro-ok.php";
      $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $cod_taxa;
    }
  } else {
    $MSG     = "Operação-Não-Executada,-Acao-não-Informada-Corretamente";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/perfil-cadastro-ok.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_taxa=" . $cod_taxa;
  }
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
