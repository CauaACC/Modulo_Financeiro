<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$debug = false;
$FWS_ = processPostArray($debug);
#-------------------------------------------
@$FWS_['data_alteracao']  = date('Y-m-d H:i:s');
@$FWS_['resp_alteracao']   =$_SESSION['_cod_usuario'];
#@$FWS_['status']         = "ATIVADA";
@$FWS_['session_id_alteracao']      = $_COOKIE["PHPSESSID"];
@$FWS_['http_user_agent_alteracao'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente_alteracao']      = $_SERVER["REMOTE_ADDR"]; 

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
If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG']=$MSG;
  $CONTENT = "view/admin/inscritos-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;

}Else{

  # Lista colunas de pedidos para INSERT explicito (pedidos_atualizados ganhou
  # colunas extras de auditoria e o antigo `INSERT...SELECT *` deixou de bater)
  $colunas = array();
  $rsCols  = FWA_Executa_SQL($debug,$Conexao_db,"SHOW COLUMNS FROM pedidos");
  if($rsCols){
      foreach($rsCols as $rowCol){
          $colunas[] = $rowCol['Field'];
      }
  }

  $cod_pedido_int = (int)$FWS_['cod_pedido'];
  $cod_evento_int = (int)$cod_evento;
  $usuario_acao   = (int)($_SESSION['_cod_usuario'] ?? 0);
  $ip_cliente     = addslashes($_SERVER['REMOTE_ADDR'] ?? '');

  if(empty($colunas)){
      $FWS_Execucao=false;
      $MSG     = "Erro-de-Execucao-ERRO-SQL-Listagem-Colunas-Pedidos";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pedido=".$FWS_['cod_pedido']."&cod_inscrito=".$FWS_['cod_inscrito'];
  }else{
      $listaCol = '`'.implode('`,`', $colunas).'`';
      $var_sql = "
                   INSERT INTO pedidos_atualizados ($listaCol, tipo_acao, motivo_acao, usuario_acao, ip_cliente_acao, data_acao)
                   SELECT $listaCol, 'ALTERACAO', NULL, $usuario_acao, '$ip_cliente', NOW()
                   FROM pedidos
                   WHERE (id=$cod_pedido_int) and (evento_id=$cod_evento_int)
                 ";

      If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
        $FWS_Execucao = true;
      }Else{
        $FWS_Execucao=false;
        $MSG     = "Erro-de-Execucao-ERRO-SQL-Backup-Pedido";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pedido=".$FWS_['cod_pedido']."&cod_inscrito=".$FWS_['cod_inscrito'];
      }
  }
  
  If($FWS_Execucao==true){
      $sqlVencimento = "";
      if ($FWS_['dat_vencimento']!=''){
          $sqlVencimento = " dat_vencimento ='".$FWS_['dat_vencimento']."', ";
      }
      if($FWS_['descricao']==''){
        $FWS_['descricao'] = $FWS_['descricao_completa'];
      }
      if($FWS_['descricao_completa']==''){
        $FWS_['descricao_completa'] = $FWS_['descricao'];
      }
    $var_sql = "
              UPDATE pedidos SET
                valor_total ='". formatValueToDotSeparator($FWS_['valor_total'])."',
                tipo        ='".$FWS_['tipo']."',
                descricao   ='".substr($FWS_['descricao'], 0, 98)."',
                descricao_completa   ='".$FWS_['descricao_completa']."',
                max_qtd_parcela='".$FWS_['max_qtd_parcela']."',
                ".$sqlVencimento."
                status      ='".$FWS_['status']."',
                referencia      ='".$FWS_['referencia']."'
              WHERE (id=".$FWS_['cod_pedido'].")and(evento_id =".$cod_evento.")
             ";

    If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
        $FWS_Execucao=true;
        $MSG     = "Operação-Executada-com-Sucesso";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pedido=".$FWS_['cod_pedido']."&cod_inscrito=".$FWS_['cod_inscrito'];
    }Else{
      $FWS_Execucao=false;
      $MSG     = "Erro-de-Execucao-ERRO-SQL-Update-Pedido";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pedido=".$FWS_['cod_pedido']."&cod_inscrito=".$FWS_['cod_inscrito'];
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
