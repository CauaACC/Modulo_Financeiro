<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");

require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-localiza.php");


$MSG   = $_SESSION['MSG'];
$FWS_Debug = false;

$MSG_Imprimir = $MSG;
$loc   = "/--/";
$subst = "<br>";
if ($MSG_Imprimir != '') {
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
}

If($MSG_Imprimir){
  echo "<hr size='1' width='100%'>";
  echo $MSG_Imprimir;
  #echo "<hr size='1' width='100%'>";
}


@$cod_trabalho = $_GET['cod_trabalho'];
If(isset($cod_trabalho)){

  #echo $cod_trabalho."<br>";

  $Conexao_db = FWA_Conexao("OpcTec");

  $var_Sql = "SELECT
                tt.cod_inscrito
                FROM tab_trabalhos tt
                WHERE
                (tt.cod_trabalho = ".$cod_trabalho.")AND
                (tt.cod_evento   = ".COD_EVENTO.")
               ";

  If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_Sql)){
    If (!$RS->EOF) {
      While (!$RS->EOF) {
        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $cod_inscrito          = $RS->fields[0];
        $_SESSION['MSG'] = $MSG;
        $URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/index.php?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=".$cod_inscrito;
        echo "<br><a href='{$URL}'>Redirecionando... para pagina da inscrição.</a><br>";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
    }Else{
      $MSG = "--Código-do-Trabalho-não-foi-encontrado!--";
      $_SESSION['MSG']=$MSG;
      $_SESSION['MSG']=$MSG;
      $URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/index.php?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=".$cod_inscrito;
      echo "<br><a href='{$URL}'>Redirecionando... para pagina da inscrição.</a><br>";
    }
  }Else{
    $MSG = "--Código-do-Trabalho-não-foi-encontrado!--";
    $_SESSION['MSG']=$MSG;
    $URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/index.php?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=".$cod_inscrito;
    echo "<br><a href='{$URL}'>Redirecionando... para pagina da inscrição.</a><br>";
  }
}Else{
  $MSG = "--Código-do-Trabalho-não-foi-preenchido!--";
  $_SESSION['MSG']=$MSG;
  $URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/index.php?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=".$cod_inscrito;
  echo "<br><a href='{$URL}'>Redirecionando... para pagina da inscrição.</a><br>";
}
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
