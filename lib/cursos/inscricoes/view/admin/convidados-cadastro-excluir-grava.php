<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/convidados-seguranca.php");

  $COD_CONVIDADO = $_GET['COD_CONVIDADO'];
  $acao          = $_GET['acao'];

  #echo $acao."-<BR>";
#-------------------------------------------
  #echo $Var_Sql."<br>".$Var_Campos."<br><br><br>";


   If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados

     $MSG     = "Operação-Não-Executada,-Erro-de-Conexão";
     $_SESSION['MSG'] = $MSG;
     $CONTENT = "view/admin/convidados-cadastro-ok.php";
     $URL = "index.php?content=".$CONTENT."&COD_CONVIDADO=".$COD_CONVIDADO;

  }Else{

     If ($acao=="Excluir") {
       $var_sql = "
                    INSERT INTO tab_convidados_excluidos
                    SELECT *
                    FROM tab_convidados
                    WHERE (cod_convidado = ".$COD_CONVIDADO.")and(cod_evento = ".COD_EVENTO.")
                  ";
       #echo "<br>".$var_sql."<br>";

       $RS = $Conexao_db->Execute($var_sql);

       $var_sql = "
                    SELECT cod_convidado
                    FROM tab_convidados_excluidos
                    WHERE (cod_convidado = ".$COD_CONVIDADO.")and(cod_evento = ".COD_EVENTO.")
                  ";

       #echo "<br>".$var_sql."<br>";

       $RS_select = $Conexao_db->Execute($var_sql);

       If (!$RS_select->EOF) {
         $cod_convidado = $RS_select->fields[0];

         $var_sql = "DELETE FROM tab_convidados WHERE (cod_convidado = ".$COD_CONVIDADO.")and(cod_evento = ".COD_EVENTO.")";
         $RS      = $Conexao_db->Execute($var_sql);

         $MSG     = "Operação-Executada";
         $_SESSION['MSG'] = $MSG;
         $CONTENT = "view/admin/convidados-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT."&COD_CONVIDADO=".$COD_CONVIDADO;


         #echo "<br>".$var_sql."<br>";

       }Else{
         $MSG     = "Operação-Não-Executada,-Ocorreu-um-Erro_ao-Excluir";
         $_SESSION['MSG'] = $MSG;
         $CONTENT = "view/admin/convidados-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT."&COD_CONVIDADO=".$COD_CONVIDADO;
       }
     }Else{
       $MSG     = "Operação-Não-Executada,-Acao-Diferente-Excluir";
       $_SESSION['MSG'] = $MSG;
       $CONTENT = "view/admin/convidados-cadastro-ok.php";
       $URL = "index.php?content=".$CONTENT."&COD_CONVIDADO=".$COD_CONVIDADO;
     }
  }

FWA_Mostrar_Mensagem($MSG);

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=".$URL."\"></HTML>";
?>

