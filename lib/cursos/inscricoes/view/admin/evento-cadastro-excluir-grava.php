<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/evento-seguranca.php");

  $cod_evento = $_GET['cod_evento'];
  $acao              = $_GET['acao'];

  #echo $acao."-<BR>";
#-------------------------------------------
  require_once("../include/functions/FWA_Functions.php");

  #echo $Var_Sql."<br>".$Var_Campos."<br><br><br>";


   If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados

     $MSG     = "Operação-Não-Executada,-Erro-de-Conexão";
     $_SESSION['MSG']=$MSG;
     $CONTENT = "view/admin/evento-cadastro-ok.php";
     $URL = "index.php?content=".$CONTENT."&cod_evento=".$cod_evento;

  }Else{

     If ($acao=="Excluir") {
       $var_sql = "
                    INSERT INTO tab_eventos_excluidos
                    SELECT *
                    FROM tab_eventos
                    WHERE (cod_evento = ".$cod_evento.")
                  ";
       #echo $var_sql."- <BR>";

       $RS = $Conexao_db->Execute($var_sql);

       $var_sql = "
                    SELECT cod_evento
                    FROM tab_eventos_excluidos
                    WHERE (cod_evento = ".$cod_evento.")
                  ";

       #echo $var_sql."- <BR>";

       $RS_select = $Conexao_db->Execute($var_sql);

       If (!$RS_select->EOF) {
         $cod_evento = $RS_select->fields[0];

         $var_sql = "DELETE FROM tab_eventos WHERE (cod_evento = ".$cod_evento.")";
         $RS      = $Conexao_db->Execute($var_sql);

         $MSG     = "Operação-Executada-com-Sucesso";
         $_SESSION['MSG']=$MSG;
         $CONTENT = "view/admin/evento-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT."&cod_evento=".$cod_evento;


         #echo $var_sql."- <BR>";

       }Else{
         $MSG     = "Operação-Não-Executada,-Erro-ao-tentar-Excluir";
         $_SESSION['MSG']=$MSG;
         $CONTENT = "view/admin/evento-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT."&cod_evento=".$cod_evento;
       }
     }Else{
       $MSG     = "Operação-Não-Executada,-Acao-não-Informada-Corretamente";
       $_SESSION['MSG']=$MSG;
       $CONTENT = "view/admin/evento-cadastro-ok.php";
       $URL = "index.php?content=".$CONTENT."&cod_evento=".$cod_evento;
     }
  }

FWA_Mostrar_Mensagem($MSG);

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\"></HTML>";


