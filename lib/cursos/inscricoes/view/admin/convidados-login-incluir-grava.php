<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/convidados-seguranca.php");
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
    $FWS_Executa_SQL = false;
  }Else{
    $FWS_Executa_SQL = true;
  }
  #echo $RS->fields[0];
  return $RS;
}

#-----------------------------------

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
  $MSG .="Erro-de-Conexao";
  $FWS_Execucao = false;
}Else{
  $consql  = "SELECT login, senha FROM sis_usuarios WHERE (cod_convidado=".$FWS_['cod_convidado'].")and(cod_evento=".$FWS_['cod_evento'].")";
  If ($RS=FWS_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) { //Consulta convidado possui login
    If($FWS_Debug==1){ echo "<br>".$RS->fields[0]."<br>"; }
    If($RS->fields[0]!=""){//Se convidado ja possui login
      $MSG .="--Convidado-já-possui-login:-".$RS->fields[0]." - senha: ".$RS->fields[1].".--";    
      $FWS_Execucao = false;
    }Else{//Se não convidado ja possui login
      $consql  = "SELECT max(cod_usuario) as cod FROM sis_usuarios WHERE (login='".$FWS_['login']."')and(cod_evento=".$FWS_['cod_evento'].")";

      If ($RS=FWS_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) { //Consulta login existe
        If($FWS_Debug==1){ echo "<br>".$RS->fields[0]."<br>"; }
        If($RS->fields[0]!=""){//Se login exite
          $MSG .="--O-login:-".$FWS_['login']."-já-existe.--";    
          $FWS_Execucao = false;
        }Else{//Se não login exite
          $consql  = "Insert Into sis_usuarios(login, senha, nome_usuario, cod_convidado, cod_evento) Values ('".$FWS_['login']."','".$FWS_['senha']."','".$FWS_['nome_usuario']."', ".$FWS_['cod_convidado'].", ".$FWS_['cod_evento'].")";

          If (FWS_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) {//Se login inserido
            $MSG .="--OK-inserindo-login.--";
            $FWS_Execucao = true;

            $consql  = "SELECT cod_usuario FROM sis_usuarios WHERE (login='".$FWS_['login']."')and(cod_evento=".$FWS_['cod_evento'].")";
            If ($RS=FWS_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) { //Se confirma inserçao
              $MSG .="--OK-Consuta-Confirmando.--";
              If($FWS_Debug==1){ echo "<br>".$RS->fields[0]."<br>"; }
              If($RS->fields[0]!=""){
                $MSG .="--OK-Criaçao-Confirmada.--";
                $FWA_Execucao = true;
              }Else{
                $MSG .="--ERRO-Criaçao-Confirmada.--";
                $FWA_Execucao = false;
              }
            }Else{//Se não confirma inserçao
              $MSG .="--ERRO-Consuta-Confirmando.--";
              $FWA_Execucao = false;
            }//Fim Se não confirma inserçao
          }Else{//Se não login inserido
            $MSG .="--ERRO-inserindo-login.--";
            $FWA_Execucao = false;
          }//Fim Se não login inserido
        }//Fim Se não login exite
      }Else{//Se não Consulta login existe
          $MSG .="--ERRO-Consulta-login-existe.--";
          $FWA_Execucao = false;
      }//Fim Se não Consulta login existe
    }//Fim Se convidado ja possui login
  }Else{//Se não Consulta convidado-possui-login
    $MSG .="--ERRO-Consulta-convidado-possui-login.--";
    $FWA_Execucao = false;
  }
}
#echo $MSG;
$_SESSION['MSG'] = $MSG;
If(@$FWA_Execucao==1){
  $FWS_URL = "index.php?content=view/admin/convidados-cadastro-ok.php&COD_CONVIDADO=".$FWS_['cod_convidado'];
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
}Else{
  $FWS_URL = "index.php?content=view/admin/convidados-cadastro-ok.php&COD_CONVIDADO=".$FWS_['cod_convidado'];
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

