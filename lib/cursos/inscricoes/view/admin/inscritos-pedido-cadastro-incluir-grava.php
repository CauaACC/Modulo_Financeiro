<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$debug=false;
#-------------------------------------------
@$fws_['data_inclusao']  = date('Y-m-d H:i:s');
@$fws_['resp_inclusao']   =$_SESSION['_cod_usuario'];
#@$fws_['status']         = "ATIVADA";
@$fws_['session_id_inclusao']      = $_COOKIE["PHPSESSID"];
@$fws_['http_user_agent_inclusao'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente_inclusao']      = $_SERVER["REMOTE_ADDR"]; 

#echo $acao."-<BR>";
#-------------------------------------------
$objI = new Inscrito();
$objI->setCodInscrito($fws_['cod_inscrito']);
$objI->getInscritoByIdSemEvento();
if($objI->getCodEvento()!=''){
//    $objI->get();
    $cod_evento = $objI->getCodEvento(); 
}else{
    $cod_evento = COD_EVENTO; 
}
#-------------------------------------------
if($fws_['descricao']==''){
  $fws_['descricao'] = $fws_['descricao_completa'];
}
if($fws_['descricao_completa']==''){
  $fws_['descricao_completa'] = $fws_['descricao'];
}
$Var_Sql = ""
.""
.$cod_evento.","
.$fws_['cod_inscrito'].",'"
.formatValueToDotSeparator($fws_['valor_total'])."','"
.$fws_['tipo']."','"
.substr($fws_['descricao'], 0, 98)."','"
.$fws_['descricao_completa']."','"
.$fws_['dat_vencimento']."','"
.$fws_['max_qtd_parcela']."','"
.$fws_['referencia']."','"
.$fws_['status']."'"
."";
//-------------------------------------------

//I: ---------------------------------------
$Var_Campos = ""
."evento_id   ,"
."inscrito_id ,"
."valor_total  ,"
."tipo         ,"
."descricao    ,"
."descricao_completa    ,"
."dat_vencimento ,"
."max_qtd_parcela,"
."referencia,"
."status        ";

#-------------------------------------------
#echo $Var_Sql."<br>".$Var_Campos."<br>";

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG']=$MSG;
  $CONTENT = "view/admin/inscritos-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;

}Else{

  $var_sql= "
             Insert Into pedidos (".$Var_Campos.")
             Values (".$Var_Sql.")
            ";

//    echo $var_sql.'-';
//    exit();
  If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
      $MSG     = "Operação-Executada-com-Sucesso--Inscrito-incluído-com-sucesso";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
  }Else{
    $MSG     = "Erro-de-Execucao-ERRO-SQL-Insert";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
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
  echo "<br>Gravando seus dados no sistema<BR>";
  echo "<br>Este procedimento demora poucos segundos.<br>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}
