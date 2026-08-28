<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/ligacao-convidados-areas-tematicas-seguranca.php");
#-------------------------------------------
$FWS_ = processGetArray($FWS_Debug);
#-------------------------------------------
#$acao     = "INSERIR";
$FWS_Data_Ini  = "200604010001"; #aaaammddhhii
$FWS_Data_Fim  = "200606232359"; #aaaammddhhii
$FWS_Data_Hoje = Date('YmdHi');

$FWS_Debug=false;
$MSG = "";

@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
@$FWS_['status']          = "ATIVADO";
@$FWS_['session_id']      = session_id();
@$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

#-----------------------------------

#-------------------------------------------
If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados
   $MSG    .= "Operação-Não-Executada,-Erro-de-Conexão";
   $_SESSION['MSG']=$MSG;
   $CONTENT = "view/admin/ligacao-convidados-areas-tematicas-listagem.php";
   $URL     = "index.php?content=".$CONTENT;
   $FWA_Execucao = false;
}Else{

   If ($FWS_['acao']=="Excluir") {
       $var_sql = "DELETE FROM tab_lig_convidados_areas_tematicas WHERE (cod_convidado = ".$FWS_['cod_convidado'].")and(cod_area_tematica = ".$FWS_['cod_area_tematica'].")and(cod_evento = ".$FWS_['cod_evento'].")";
       
       If(!FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){ //Consulta login existe   
         $MSG    .= "ERRO-Ligação-não-deletada";
         $_SESSION['MSG']=$MSG;
         $CONTENT = "view/admin/ligacao-convidados-areas-tematicas-listagem.php";
         $URL     = "index.php?content=".$CONTENT;
         $FWA_Execucao = false;
       }Else{   
         $MSG    .= "OK-Ligação-deletada";
         $_SESSION['MSG']=$MSG;
         $CONTENT = "view/admin/ligacao-convidados-areas-tematicas-listagem.php";
         $URL     = "index.php?content=".$CONTENT;
         $FWA_Execucao = true;
       }
   }Else{
     $MSG     = "Operação-Não-Executada,-Acao-Diferente-Excluir";
     $_SESSION['MSG']=$MSG;
     $CONTENT = "view/admin/ligacao-convidados-areas-tematicas-listagem.php";
     $URL = "index.php?content=".$CONTENT;
     $FWA_Execucao = false;
   }
}

#echo $MSG;
If(@$FWA_Execucao==1){
  $FWS_URL = WB_URL_VIEW."?content=view/admin/consulta-avaliacao-trabalhos-listagem.php&COD_USUARIO=".$FWS_['cod_usuario']."#".$FWS_['cod_area_tematica'];
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
}Else{
  $FWS_URL = WB_URL_VIEW."?content=view/admin/consulta-avaliacao-trabalhos-listagem.php&COD_USUARIO=".$FWS_['cod_usuario']."#".$FWS_['cod_area_tematica'];
  echo "<br>Mensagens:<br>";
}

#--------------------------------------------------------------
$MSG_Imprimir = $MSG;

$loc   = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
#--------------------------------------------------------------
If($FWS_Debug==1){
  echo $MSG_Imprimir;
  echo "<br><a href='{$FWS_URL}'>{$FWS_URL}</a><br>";
}Else{
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$FWS_URL."\">";
}

