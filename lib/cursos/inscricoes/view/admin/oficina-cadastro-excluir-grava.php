<?php
require_once(dirname(__FILE__)."/../../include/config.php");
  $COD_OFICINA = $_GET['COD_OFICINA'];
  $acao        = $_GET['acao'];
  $debug = false;
  $acao  = 'Excluir';
  #echo $acao."-<BR>";
#-------------------------------------------
   #echo $Var_Sql."<br>".$Var_Campos."<br><br><br>";

   If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados

     $MSG     = "Operação-Não-Executada,-Erro-de-Conexão";
     $_SESSION['MSG']=$MSG;
     $CONTENT = "view/admin/oficina-cadastro-ok.php";
     $URL = "index.php?content=".$CONTENT."&COD_OFICINA=".$COD_OFICINA;

  }Else{
      $Conexao_db->debug=$debug;
     If ($acao=="Excluir") {
       $var_sql = "
                    INSERT INTO tab_opcoes_oficinas_excluidos
                    SELECT *
                    FROM tab_opcoes_oficinas
                    WHERE (cod_oficina = ".$COD_OFICINA.")
                  ";
       #echo $var_sql."- <BR>";

       $RS = $Conexao_db->Execute($var_sql);

       $var_sql = "
                    SELECT cod_oficina
                    FROM tab_opcoes_oficinas_excluidos
                    WHERE (cod_oficina = ".$COD_OFICINA.")
                  ";

       #echo $var_sql."- <BR>";

       $RS_select = $Conexao_db->Execute($var_sql);

       If (!$RS_select->EOF) {
         $cod_oficina = $RS_select->fields[0];

         $var_sql = "DELETE FROM tab_opcoes_oficinas WHERE (cod_oficina = ".$COD_OFICINA.")";
         $RS      = $Conexao_db->Execute($var_sql);

         $MSG     = "Operação-Executada";
         $_SESSION['MSG']=$MSG;
         $CONTENT = "view/admin/oficina-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT;


         #echo $var_sql."- <BR>";

       }Else{
         $MSG     = "Operação-Não-Executada,-Ocorreu-um-Erro_ao-Excluir";
         $_SESSION['MSG']=$MSG;
         $CONTENT = "view/admin/oficina-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT."&COD_OFICINA=".$COD_OFICINA;
       }
     }Else{
       $MSG     = "Operação-Não-Executada,-Acao-Diferente-Excluir";
       $_SESSION['MSG']=$MSG;
       $CONTENT = "view/admin/oficina-cadastro-ok.php";
       $URL = "index.php?content=".$CONTENT."&COD_OFICINA=".$COD_OFICINA;
     }
  }

FWA_Mostrar_Mensagem($MSG);
$_SESSION['MSG']=$MSG;
$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\"></HTML>";
