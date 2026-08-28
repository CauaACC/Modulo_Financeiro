<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$FWS_Debug = false;
$FWS_ = processPostArray($FWS_Debug);
#-------------------------------------------
@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
@$FWS_['status']         = "ATIVADA";
@$FWS_['session_id']      = $_SESSION['_cod_usuario'];
@$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

#echo $acao."-<BR>";
#-------------------------------------------

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG']=$MSG;
  $CONTENT = "view/admin/inscritos-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;

}Else{

  $var_sql = "
               INSERT INTO tab_lig_inscritos_perfis_atualizados
               SELECT *
               FROM tab_lig_inscritos_perfis 
               WHERE (cod_inscrito=".$FWS_['cod_inscrito'].")and(cod_evento =".$FWS_['cod_evento'].")
             ";
             
  If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
    $FWS_Execucao = true;
  }Else{
    $FWS_Execucao=false;
    $MSG     = "Erro-de-Execucao-ERRO-SQL-Backup-Ligacao_Perfil";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$FWS_['cod_trabalho']."&cod_inscrito=".$FWS_['cod_inscrito'];
  }  
  
  If($FWS_Execucao==true){
    $var_sql = "
              UPDATE tab_lig_inscritos_perfis SET
                cod_inscrito     ='".$FWS_['cod_inscrito'   ]."',
                cod_perfil      ='".$FWS_['cod_perfil'    ]."',
                data_alteracao   ='".$FWS_['data_alteracao' ]."',
                status           ='".$FWS_['status'         ]."',
                session_id       ='".$FWS_['session_id'     ]."',
                http_user_agent  ='".$FWS_['http_user_agent']."',
                ip_cliente       ='".$FWS_['ip_cliente'     ]."'
              WHERE (cod_inscrito=".$FWS_['cod_inscrito'].")and(cod_evento =".$FWS_['cod_evento'].")
             ";

    If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
      $FWS_Execucao=true;
      $MSG     = "Operação-Executada-com-Sucesso";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_perfil=".$FWS_['cod_perfil']."&cod_inscrito=".$FWS_['cod_inscrito'];
    }Else{
      $FWS_Execucao=false;
      $MSG     = "Erro-de-Execucao-ERRO-SQL-Update-Trabalho";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$FWS_['cod_trabalho']."&cod_inscrito=".$FWS_['cod_inscrito'];
    }
  }

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
If($FWS_Debug==true){
  echo $MSG_Imprimir;
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
}Else{
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<br>";
  echo "<br>Este procedimento pode demorar alguns segundos.<br>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}

