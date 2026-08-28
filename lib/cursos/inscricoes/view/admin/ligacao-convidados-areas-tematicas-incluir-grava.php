<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/login-seguranca.php");
#-------------------------------------------
$FWS_ = processPostArray($FWS_Debug);
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

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
  $MSG .="Erro-de-Conexao";
  $FWS_Execucao = false;
}Else{
  $consql  = "SELECT cod_convidado, cod_area_tematica FROM tab_lig_convidados_areas_tematicas WHERE (cod_area_tematica=".$FWS_['cod_area_tematica'].")and(cod_convidado=".$FWS_['cod_convidado'].")and(cod_evento=".$FWS_['cod_evento'].")";
  If ($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) { //Consulta convidado possui login
    If($FWS_Debug==1){ echo "<br>".$RS->fields[0]."<br>"; }
    If($RS->fields[0]!=""){//Se convidado ja possui login
      $FWS_['cod_convidado']=$RS->fields[0];
      $MSG .="--Convidado-já-possui-ligação.";    
      $FWS_Execucao = false;
    }Else{//Se não convidado ja possui login
      
          $consql  = "Insert Into tab_lig_convidados_areas_tematicas(cod_area_tematica, cod_convidado, cod_evento) Values (".$FWS_['cod_area_tematica'].",".$FWS_['cod_convidado'].", ".$FWS_['cod_evento'].")";

          If (FWA_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) {//Se login inserido
            $MSG .="--OK-inserindo-ligação.";
            $FWS_Execucao = true;

            $consql  = "SELECT cod_convidado FROM tab_lig_convidados_areas_tematicas WHERE (cod_area_tematica=".$FWS_['cod_area_tematica'].")and(cod_convidado=".$FWS_['cod_convidado'].")and(cod_evento=".$FWS_['cod_evento'].")";
            If ($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) { //Se confirma inserçao
              $MSG .="--OK-Consuta-Confirmando.";
              If($FWS_Debug==1){ echo "<br>".$RS->fields[0]."<br>"; }
              If($RS->fields[0]!=""){
                $FWS_['cod_convidado']=$RS->fields[0];
                $MSG .="--OK-Criaçao-Confirmada.";
                $FWA_Execucao = true;
              }Else{
                $MSG .="--ERRO-Criaçao-Confirmada.";
                $FWA_Execucao = false;
              }
            }Else{//Se não confirma inserçao
              $MSG .="--ERRO-Consuta-Confirmando.";
              $FWA_Execucao = false;
            }//Fim Se não confirma inserçao
          }Else{//Se não login inserido
            $MSG .="--ERRO-inserindo-login.";
            $FWA_Execucao = false;
          }//Fim Se não login inserido
          
    }//Fim Se convidado ja possui login
  }Else{//Se não Consulta convidado-possui-ligação
    $MSG .="--ERRO-Consulta-convidado-possui-ligação.";
    $FWA_Execucao = false;
  }
}
#echo $MSG;
$_SESSION['MSG']=$MSG;
If(@$FWA_Execucao==1){
  $FWS_URL = WB_URL_VIEW."?content=view/admin/consulta-avaliacao-trabalhos-listagem.php&COD_CONVIDADO=".$FWS_['cod_convidado']."#".$FWS_['cod_area_tematica'];
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
}Else{
  $FWS_URL = WB_URL_VIEW."?content=view/admin/consulta-avaliacao-trabalhos-listagem.php&COD_CONVIDADO=".$FWS_['cod_convidado']."#".$FWS_['cod_area_tematica'];
  echo "<br>Mensagens:<BR>";
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
?>

