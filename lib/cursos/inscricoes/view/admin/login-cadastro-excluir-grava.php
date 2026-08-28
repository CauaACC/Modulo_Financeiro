<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/login-seguranca.php");

$FWS_Debug = false;
$MSG = "";

//COD_EVENTO  = $_GET['COD_EVENTO'];
$COD_USUARIO = $_GET['COD_USUARIO'];
$acao        = $_GET['acao'];
#-------------------------------------------
If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados
   $MSG    .= "Operação-Não-Executada,-Erro-de-Conexão";
   $CONTENT = "view/admin/login-cadastro-ok.php";
   $URL     = "index.php?content=".$CONTENT."&COD_USUARIO=".$COD_USUARIO;
}Else{

   If ($acao=="Excluir") {
       $var_sql = "DELETE FROM sis_usuarios WHERE (cod_usuario = ".$COD_USUARIO.")and(cod_evento = ".COD_EVENTO.")";
       
       If(!FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){ //Consulta login existe   
         $MSG    .= "--ERRO-Login-não-deletado";
         $CONTENT = "view/admin/login-cadastro-ok.php";
         $URL     = "index.php?content=".$CONTENT."&COD_USUARIO=".$COD_USUARIO;
       }Else{   
         $MSG    .= "--OK-Login-deletado";
         $var_sql = "DELETE FROM sis_ligacao_usuarios_grupos WHERE (cod_usuario = ".$COD_USUARIO.")and(cod_evento = ".COD_EVENTO.")";
         If(!FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){ //Consulta login existe   
           $MSG    .= "--ERRO-Assossiacao-aos-grupos-não-deletadas";
           $CONTENT = "view/admin/login-cadastro-ok.php";
           $URL     = "index.php?content=".$CONTENT."&COD_USUARIO=".$COD_USUARIO;
         }Else{
           $MSG     .= "--OK-Assossiacao-aos-grupos-deletadas";
           $CONTENT = "view/admin/login-cadastro-ok.php";
           $URL     = "index.php?content=".$CONTENT."&COD_USUARIO=".$COD_USUARIO;
         }
       }
   }Else{
     $MSG     = "Operação-Não-Executada,-Acao-Diferente-Excluir";
     $CONTENT = "view/admin/login-cadastro-ok.php";
     $URL = "index.php?content=".$CONTENT."&COD_USUARIO=".$COD_USUARIO;
   }
}
$_SESSION['MSG']=$MSG;
FWA_Mostrar_Mensagem($MSG);

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\"></HTML>";
