<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/login-seguranca.php");
#-------------------------------------------
#$acao     = "INSERIR";
$fws_Data_Ini  = "200604010001"; #aaaammddhhii
$fws_Data_Fim  = "200606232359"; #aaaammddhhii
$fws_Data_Hoje = Date('YmdHi');

$fws_Debug=false;
$MSG = "";

@$fws_['data_alteracao']  = Date("YmdHi");
@$fws_['data_inclusao']   = Date("YmdHi");
@$fws_['status']          = "ATIVADO";
@$fws_['session_id']      = session_id();
@$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

#------------------------------------------------------

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG']=$MSG;
  $CONTENT = "view/admin/login-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&COD_USUARIO=".$fws_['cod_usuario'];
}Else{

  $consql  = "
              UPDATE sis_usuarios SET
                login = '".$fws_['login']."',
                senha = '".$fws_['senha']."'
              WHERE (cod_usuario=".$fws_['cod_usuario'].")
                 and(cod_evento   =".$fws_['cod_evento'].")
                 and(cod_convidado=".$fws_['cod_convidado'].")
             ";

  #echo $consql;

  If (!$Conexao_db->Execute($consql)) { //Executando o Insert
    $MSG     = "Erro-de-Execucao-ERRO-SQL";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/login-cadastro-ok.php";
    $URL = "index.php?content=".$CONTENT."&COD_USUARIO=".$fws_['cod_usuario'];
  }Else{
    $consql  = "SELECT cod_usuario,cod_convidado FROM sis_usuarios WHERE (login='".$fws_['login']."')and(cod_evento=".$fws_['cod_evento'].")";
    If ($RS = FWA_Executa_SQL($debug, $Conexao_db, $consql)) { //Se confirma inserçao
        $MSG .="--OK-Consuta-Confirmando.--";
        If ($debug == 1) {
            echo "<br>" . $RS->fields[0] . "<br>";
        }
        If ($RS->fields[0] != "") {
            $fws_['cod_usuario'] = $RS->fields[0];
            $fws_['cod_convidado'] = $RS->fields[1];
            /* Enviar email com a senha */
            if ($fws_['enviar_aviso'] == 'ON') {
                $objEmail = new GerenciarEmailAC();
                $objEmail->enviarEmailLoginConvidado($fws_, $debug);
            }

            $MSG .="--OK-Criaçao-Confirmada.--";
            $FWA_Execucao = true;
        } Else {
            $MSG .="--ERRO-Criaçao-Confirmada.--";
            $FWA_Execucao = false;
        }
    }
    $MSG     = "Operação-Executada-com-Sucesso";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/login-cadastro-ok.php";
    $URL = "index.php?content=".$CONTENT."&COD_USUARIO=".$fws_['cod_usuario'];
  }
}
$loc   = "/-/";
$subst = " ";
echo preg_replace($loc,$subst,$MSG)." ,aguarde um momento - ".date("d/m/Y - H:i:s");

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=".$URL."\"></HTML>";
