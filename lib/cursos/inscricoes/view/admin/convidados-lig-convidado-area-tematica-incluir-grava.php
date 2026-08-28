<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#-------------------------------------------
$FWS_ = processGetArray($FWS_Debug);
#-------------------------------------------
#$acao     = "INSERIR";
$FWS_Data_Ini  = "200604010001"; #aaaammddhhii
$FWS_Data_Fim  = "200606232359"; #aaaammddhhii
$FWS_Data_Hoje = Date('YmdHi');

$FWS_Debug=true;

@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
@$FWS_['status']          = "ATIVADO";
@$FWS_['session_id']      = session_id();
@$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

Function FWS_Executa_SQL($var_Debug,$var_Conexao,$var_sql){

  If($var_Debug==1){ echo "<br>".$var_sql."<br>"; }
  
  If (!$RS=$var_Conexao->Execute($var_sql)) { //Executando o Insert
    echo $RS->fields[0];
    $FWS_Executa_SQL = false;
  }Else{
    $FWS_Executa_SQL = true;
  }
  return $FWS_Executa_SQL;
  return $RS;
}

session_start();
@$cod_usuario = $_SESSION['_cod_usuario'];
@$cod_sistema = 1;
@$cod_direito = 5;
@$cod_msg     = true;
If(@FWA_Autenticacao($cod_usuario,$cod_sistema,$cod_direito,$cod_msg)==false){ exit; }

#echo $Var_Sql."<br>".$Var_Campos."<br>";

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
  $MSG .="Erro-de-Conexao";
  $FWS_Execucao = false;
}Else{
  $consql  = "SELECT max(cod_usuario) as cod FROM sis_usuarios WHERE (login='".$FWS_['login']."')";

  If (FWS_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) { //Consulta cod do Login inserido
    If($RS->fields[0]!=""){
      $MSG .="--O-login:-".$FWS_['login']."-já-existe.--";    
      $FWS_Execucao = false;
    }Else{
      $consql  = "Insert Into sis_usuarios(login, senha, nome_usuario, cod_convidado) Values ('".$FWS_['login']."','".$FWS_['senha']."','".$FWS_['nome_usuario']."', ".$FWS_['cod_convidado'].")";

      If (FWS_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) {  //Insere Login
        $MSG .="--Login-inserido-com-sucesso.--";
        $FWS_Execucao = true;

        $consql  = "SELECT cod_usuario FROM sis_usuarios WHERE (login='".$FWS_['login']."')";

        If (FWS_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) {  //Consulta cod do Login inserido
          $MSG .="--Selecionado-cod-usuario-inserido.--";
          $FWA_Execucao = true;
        }Else{
          $MSG .="--ERRO-Não-Selecionado-cod-usuario-inserido.--";
          $FWA_Execucao = false;
        }

      }Else{
        $MSG .="--ERRO-Não-Login-inserido-com-sucesso.--";
        $FWA_Execucao = false;
      }
    }
  }Else{
    $MSG .="--ERRO-Não-Selecionado-cod-usuario-pesquisado.--";
    $FWA_Execucao = false;
  }
}
#echo $MSG;
$_SESSION['MSG'] = $MSG;
If(@$FWA_Execucao==1){
  $FWS_URL = WB_URL_VIEW."?content=view/admin/convidado-cadastro-ok.php&cod_convidado=".$FWS_['cod_convidado'];
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
}Else{
  $FWS_URL = WB_URL_VIEW."?content=view/admin/convidado-cadastro-ok.php";
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

