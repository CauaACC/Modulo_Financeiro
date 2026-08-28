<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");

#-------------------------------------------

$debug = false;

#-------------------------------------------
reset($_GET);
foreach ($_GET as $key => $val) {
    if ($val == "") {
        $val = null; // Substitui valores vazios por NULL
    }
    $val = addslashesopctec($val); // Aplica sua função customizada
    $fws_[strtolower($key)] = $val;
    // Debug opcional
    if (isset($debug) && $debug === true) {
        echo $key . " -> " . $fws_[$key] . "<br>";
    }
    // Você pode descomentar essa linha se precisar salvar na sessão
    // $_SESSION[$key] = $val;
}

#-------------------------------------------
@$fws_['cod_evento']      = COD_EVENTO;
@$fws_['data_alteracao']  = Date("YmdHi");
@$fws_['data_inclusao']   = Date("YmdHi");
#@$fws_['status']         = "ATIVADA";
@$fws_['session_id']      = $_SESSION['_cod_usuario'];
@$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

#echo $acao."-<BR>";
#-------------------------------------------

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG']=$MSG;
  $CONTENT = "view/admin/inscritos-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;

}Else{

  $var_sql = "
               INSERT INTO tab_trabalhos_atualizados
               SELECT *
               FROM tab_trabalhos
               WHERE (cod_trabalho=".$fws_['cod_trabalho'].")and(cod_evento =".$fws_['cod_evento'].")
             ";
             
  If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
    $fws_Execucao = true;
    $var_sql = "
                 INSERT INTO tab_trabalhos_avaliacao_atualizados
                 SELECT *
                 FROM tab_trabalhos_avaliacao
                 WHERE (cod_trabalho=".$fws_['cod_trabalho'].")and(cod_evento =".$fws_['cod_evento'].")
               ";

    If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
      $fws_Execucao = true;
    }Else{
      $fws_Execucao=false;
      $MSG     = "Erro-de-Execucao-ERRO-SQL-Backup-Avaliacao";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$fws_['cod_trabalho']."&cod_inscrito=".$fws_['cod_inscrito'];
    }      
  }Else{
    $fws_Execucao=false;
    $MSG     = "Erro-de-Execucao-ERRO-SQL-Backup-Trabalho";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$fws_['cod_trabalho']."&cod_inscrito=".$fws_['cod_inscrito'];
  }  
  
  If($fws_Execucao==true){
    $var_sql = "
              DELETE FROM tab_trabalhos 
              WHERE (cod_trabalho=".$fws_['cod_trabalho'].")and(cod_evento =".$fws_['cod_evento'].")
             ";

    If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
      $fws_Execucao=true;
      $var_sql = "
                DELETE FROM tab_trabalhos_avaliacao
                WHERE (cod_trabalho=".$fws_['cod_trabalho'].")and(cod_evento =".$fws_['cod_evento'].")
               ";

      If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
        $fws_Execucao=true;
        $MSG     = "Operação-Executada-com-Sucesso";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$fws_['cod_trabalho']."&cod_inscrito=".$fws_['cod_inscrito'];
      }Else{
        $fws_Execucao=false;
        $MSG     = "Erro-de-Execucao-ERRO-SQL-Delete-Avaliacao";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$fws_['cod_trabalho']."&cod_inscrito=".$fws_['cod_inscrito'];
      }
    }Else{
      $fws_Execucao=false;
      $MSG     = "Erro-de-Execucao-ERRO-SQL-Delete-Trabalho";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$fws_['cod_trabalho']."&cod_inscrito=".$fws_['cod_inscrito'];
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
If($debug==true){
  echo $MSG_Imprimir;
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
}Else{
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<br>";
  echo "<br>Este procedimento demora poucos segundos.<br>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}
?>
