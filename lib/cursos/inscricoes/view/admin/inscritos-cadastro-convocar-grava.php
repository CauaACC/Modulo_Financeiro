<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
  
  $COD_INSCRITO = $_GET['cod_inscrito'];
  $CPF          = $_GET['cpf'];
  $acao         = $_GET['acao'];

  $SESSION_ID     = $_SESSION['_login'];
  $DATA_INSCRICAO = Date('YmdHi');
  $DATA_ALTERACAO = Date('YmdHi');
  
  #echo $acao."-<BR>";
#-------------------------------------------
  #require_once("../include/functions/FWA_Functions.php");

  #echo $Var_Sql."<br>".$Var_Campos."<br><br><br>";


   If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados

     $MSG     = "Operação-Não-Executada,-Erro-de-Conexão";
     $_SESSION['MSG'] = $MSG;
     $CONTENT = "view/admin/erro.php";
     $URL = "index.php?content=".$CONTENT."&cod_inscrito=".$COD_INSCRITO;

  }Else{

     If ($acao=="Convocar") {

         $var_sql = "UPDATE tab_inscritos SET 
                            status_processo ='CONVOCADO'
                           ,data_alteracao   ='".$DATA_ALTERACAO."'
                           ,session_id       ='".$SESSION_ID."'
                           ,http_user_agent  ='".$_SERVER["HTTP_USER_AGENT"]."'
                           ,ip_cliente       ='".$_SERVER["REMOTE_ADDR"]."'                             
                     WHERE (cod_inscrito = ".$COD_INSCRITO.")and
                           (cod_evento   = ".COD_EVENTO.")";
                     
         $RS      = $Conexao_db->Execute($var_sql);

         $MSG     = "Operação-de-".$acao."-Executada-Codigo-Inscrito:".$COD_INSCRITO;
         $_SESSION['MSG'] = $MSG;
         $CONTENT = "view/admin/inscritos-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT."&cod_inscrito=".$COD_INSCRITO."&cpf=".$CPF;

         #echo $var_sql."- <BR>";

     }Else{
       $MSG     = "Operação-Não-Executada,-Acao-Diferente-Convocar";
       $_SESSION['MSG'] = $MSG;
       $CONTENT = "view/admin/inscritos-cadastro-ok.php";
       $URL = "index.php?content=".$CONTENT."&cod_inscrito=".$COD_INSCRITO."&cpf=".$CPF;
     }
  }

FWA_Mostrar_Mensagem($MSG);

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\"></HTML>";
