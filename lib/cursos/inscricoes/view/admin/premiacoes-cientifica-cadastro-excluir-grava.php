<?php
require_once(dirname(__FILE__)."/../../include/config.php");

  $COD_PREMIACAO_CIENTIFICA = $_GET['COD_PREMIACAO_CIENTIFICA'];
  $acao        = $_GET['acao'];

  #echo $acao."-<BR>";
#-------------------------------------------
  #echo $Var_Sql."<br>".$Var_Campos."<br><br><br>";


   If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados

     $MSG     = "Operação-Não-Executada,-Erro-de-Conexão";
     $_SESSION['MSG']=$MSG;
     $CONTENT = "view/admin/premiacoes-cientifica-cadastro-ok.php";
     $URL = "index.php?content=".$CONTENT."&COD_PREMIACAO_CIENTIFICA=".$COD_PREMIACAO_CIENTIFICA;

  }Else{

     If ($acao=="Excluir") {
       $var_sql = "
                    INSERT INTO tab_opcoes_premiacoes_cientifica_excluidos
                    SELECT *
                    FROM tab_opcoes_premiacoes_cientifica
                    WHERE (cod_premiacao_cientifica = ".$COD_PREMIACAO_CIENTIFICA.")
                  ";
       #echo $var_sql."- <BR>";

       $RS = $Conexao_db->Execute($var_sql);

       $var_sql = "
                    SELECT cod_premiacao_cientifica
                    FROM tab_opcoes_premiacoes_cientifica_excluidos
                    WHERE (cod_premiacao_cientifica = ".$COD_PREMIACAO_CIENTIFICA.")
                  ";

       #echo $var_sql."- <BR>";

       $RS_select = $Conexao_db->Execute($var_sql);

       If (!$RS_select->EOF) {
         $cod_premiacao_cientifica = $RS_select->fields[0];

         $var_sql = "DELETE FROM tab_opcoes_premiacoes_cientifica WHERE (cod_premiacao_cientifica = ".$COD_PREMIACAO_CIENTIFICA.")";
         $RS      = $Conexao_db->Execute($var_sql);

         $MSG     = "Operação-Executada";
         $_SESSION['MSG']=$MSG;
         $CONTENT = "view/admin/premiacoes-cientifica-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT."&COD_PREMIACAO_CIENTIFICA=".$COD_PREMIACAO_CIENTIFICA;


         #echo $var_sql."- <BR>";

       }Else{
         $MSG     = "Operação-Não-Executada,-Ocorreu-um-Erro_ao-Excluir";
         $_SESSION['MSG']=$MSG;
         $CONTENT = "view/admin/premiacoes-cientifica-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT."&COD_PREMIACAO_CIENTIFICA=".$COD_PREMIACAO_CIENTIFICA;
       }
     }Else{
       $MSG     = "Operação-Não-Executada,-Acao-Diferente-Excluir";
       $_SESSION['MSG']=$MSG;
       $CONTENT = "view/admin/premiacoes-cientifica-cadastro-ok.php";
       $URL = "index.php?content=".$CONTENT."&COD_PREMIACAO_CIENTIFICA=".$COD_PREMIACAO_CIENTIFICA;
     }
  }

FWA_Mostrar_Mensagem($MSG);

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=".$URL."\"></HTML>";
