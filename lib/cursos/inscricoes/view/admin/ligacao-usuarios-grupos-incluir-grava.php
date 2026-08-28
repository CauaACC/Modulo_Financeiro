<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/ligacao-usuarios-grupos-seguranca.php");
#-------------------------------------------
$FWS_Debug = false;
$FWS_ = processPostArray($FWS_Debug);
#-------------------------------------------
#$acao     = "INSERIR";
$fws_Data_Ini  = "200604010001"; #aaaammddhhii
$fws_Data_Fim  = "200606232359"; #aaaammddhhii
$fws_Data_Hoje = Date('YmdHi');

$debug=false;
$MSG = "";

@$fws_['data_alteracao']  = Date("YmdHi");
@$fws_['data_inclusao']   = Date("YmdHi");
@$fws_['status']          = "ATIVADO";
@$fws_['session_id']      = session_id();
@$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

#-----------------------------------

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
  $MSG .="Erro-de-Conexao";
  $fws_Execucao = false;
}Else{
  $consql  = "SELECT cod_usuario, cod_grupo FROM sis_ligacao_usuarios_grupos WHERE (cod_grupo=".$fws_['cod_grupo'].")and(cod_usuario=".$fws_['cod_usuario'].")and(cod_evento=".$fws_['cod_evento'].")";
  If ($RS=FWA_Executa_SQL($debug,$Conexao_db,$consql)) { //Consulta convidado possui login
    If($debug==1){ echo "<br>".$RS->fields[0]."<br>"; }
    If($RS->fields[0]!=""){//Se convidado ja possui login
      $fws_['cod_usuario']=$RS->fields[0];
      $MSG .="--Login-já-possui-ligação.";    
      $fws_Execucao = false;
    }Else{//Se não convidado ja possui login
      
          $consql  = "Insert Into sis_ligacao_usuarios_grupos(cod_grupo, cod_usuario, cod_evento) Values (".$fws_['cod_grupo'].",".$fws_['cod_usuario'].", ".$fws_['cod_evento'].")";

          If (FWA_Executa_SQL($debug,$Conexao_db,$consql)) {//Se login inserido
            $MSG .="--OK-inserindo-ligação.";
            $fws_Execucao = true;

            $consql  = "SELECT lig.cod_usuario, su.cod_convidado FROM sis_ligacao_usuarios_grupos lig "
                    . " INNER JOIN sis_usuarios su on su.cod_usuario=lig.cod_usuario"
                    . " WHERE (lig.cod_grupo=".$fws_['cod_grupo'].")and(lig.cod_usuario=".$fws_['cod_usuario'].")and(lig.cod_evento=".$fws_['cod_evento'].")";
            If ($RS = FWA_Executa_SQL($debug, $Conexao_db, $consql)) { //Se confirma inserçao
                $MSG .="--OK-Consuta-Confirmando.--";
                If ($debug == 1) {
                    echo "<br>" . $RS->fields[0] . "<br>";
                }
                If ($RS->fields[0] != "") {
                    $fws_['cod_usuario'] = $RS->fields[0];
                    $fws_['cod_convidado'] = $RS->fields[1];
                    /* Enviar email com a senha */
                    if ($fws_['cod_grupo'] ==4){
                        if ($fws_['enviar_aviso'] == 'ON') {
                            $objEmail = new GerenciarEmailAC();
                            $objEmail->enviarEmailLoginConvidadoAvaliador($fws_, $debug);
                        }
                    }
                    $MSG .="--OK-Criaçao-Confirmada.--";
                    $FWA_Execucao = true;
                } Else {
                    $MSG .="--ERRO-Criaçao-Confirmada.--";
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
  $fws_URL = WB_URL_VIEW."?content=view/admin/ligacao-usuarios-grupos-listagem.php&COD_USUARIO=".$fws_['cod_usuario']."&COD_GRUPO=".$fws_['cod_grupo'];
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
}Else{
  $fws_URL = WB_URL_VIEW."?content=view/admin/ligacao-usuarios-grupos-listagem.php&COD_USUARIO=".$fws_['cod_usuario']."&COD_GRUPO=".$fws_['cod_grupo'];
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
If($debug==1){
  echo $MSG_Imprimir;
  echo "<br><a href='{$fws_URL}'>{$fws_URL}</a><br>";
}Else{
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$fws_URL."\">";
}

