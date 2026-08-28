<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
$debug=false;  

  $COD_INSCRITO = $_GET['cod_inscrito'];
  $CPF          = $_GET['cpf'];
  $acao         = $_GET['acao'];

  $SESSION_ID     = $_SESSION['_login'];
  $DATA_INSCRICAO = Date('YmdHi');
  $DATA_ALTERACAO = Date('YmdHi');
  
  #echo $acao."-<BR>";
#-------------------------------------------
   If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados

     $MSG     = "Operação-Não-Executada,-Erro-de-Conexão";
     $_SESSION['MSG'] = $MSG;
     $CONTENT = "view/admin/inscritos-cadastro-ok.php";
     $URL = "index.php?content=".$CONTENT."&cod_inscrito=".$COD_INSCRITO;

  }Else{

     If ($acao=="Ativar") {
       $var_sql = "
                    INSERT INTO tab_inscritos_excluidos
                    SELECT *
                    FROM tab_inscritos
                    WHERE (cod_inscrito = ".$COD_INSCRITO.")and
                          (cod_evento   = ".COD_EVENTO.")
                  ";
       #echo $var_sql."- <BR>";

       $RS = $Conexao_db->Execute($var_sql);

       $var_sql = "
                    SELECT cod_inscrito
                    FROM tab_inscritos_excluidos
                    WHERE (cod_inscrito = ".$COD_INSCRITO.")and
                          (cod_evento   = ".COD_EVENTO.")
                  ";

       #echo $var_sql."- <BR>";

       $RS_select = $Conexao_db->Execute($var_sql);

       If (!$RS_select->EOF) {
         $COD_INSCRITO = $RS_select->fields[0];

         $var_sql = "UPDATE tab_inscritos SET 
                            status           ='ATIVADA'
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
         $MSG     = "Operação-Não-Executada,-Ocorreu-um-Erro_ao-".$acao;
         $_SESSION['MSG'] = $MSG;
         $CONTENT = "view/admin/inscritos-cadastro-ok.php";
         $URL = "index.php?content=".$CONTENT."&cod_inscrito=".$COD_INSCRITO;
       }
     }Else{
       $MSG     = "Operação-Não-Executada,-Acao-Diferente-Ativar";
       $_SESSION['MSG'] = $MSG;
       $CONTENT = "view/admin/inscritos-cadastro-ok.php";
       $URL = "index.php?content=".$CONTENT."&cod_inscrito=".$COD_INSCRITO."&cpf=".$CPF;
     }
  }

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
#--------------------------------------------------------------

FWA_Mostrar_Mensagem($MSG);
#--------------------------------------------------------------
If($debug==true){
  echo $MSG_Imprimir;
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
}Else{
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
  echo "<br>Este procedimento pode demorar algun tempo caso a lista de trabalhos a serem avaliados ainda seja grande<BR>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}
