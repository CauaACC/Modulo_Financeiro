<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");

#-------------------------------------------

$FWS_Debug = false;
$FWS_ = processGetArray($FWS_Debug);
#-------------------------------------------
@$FWS_['cod_evento']      = COD_EVENTO;
@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
#@$FWS_['status']         = "ATIVADA";
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
               INSERT INTO tab_pagamentos_atualizados
               SELECT *
               FROM tab_pagamentos
               WHERE (cod_pagamento=".$FWS_['cod_pagamento'].")and(cod_evento =".$FWS_['cod_evento'].")
             ";
             
  If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
    $FWS_Execucao = true;
  }Else{
    $FWS_Execucao=false;
    $MSG     = "Erro-de-Execucao-ERRO-SQL-Backup-Trabalho";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pagamento=".$FWS_['cod_pagamento']."&cod_inscrito=".$FWS_['cod_inscrito'];
  }  
  
  If($FWS_Execucao==true){
    $var_sql = "
              DELETE FROM tab_pagamentos 
              WHERE (cod_pagamento=".$FWS_['cod_pagamento'].")and(cod_evento =".$FWS_['cod_evento'].")
             ";

    If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
      $FWS_Execucao=true;

        // Excluir lançamento contábil vinculado ao pagamento
        try {
            $lancamento = new Lancamentos();
            $lancamento->setCodEvento($FWS_['cod_evento']);
            $lancamento->setCodPagamento($FWS_['cod_pagamento']);
            $lancamento->excluirByCodPagamento();
        } catch (Exception $e) {
            // Falha na exclusão do lançamento não bloqueia a exclusão do pagamento
        }

        $MSG     = "Operação-Executada-com-Sucesso";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pagamento=".$FWS_['cod_pagamento']."&cod_inscrito=".$FWS_['cod_inscrito'];
    }Else{
      $FWS_Execucao=false;
      $MSG     = "Erro-de-Execucao-ERRO-SQL-Delete-Trabalho";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pagamento=".$FWS_['cod_pagamento']."&cod_inscrito=".$FWS_['cod_inscrito'];
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
  echo "<br>Este procedimento demora poucos segundos.<br>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}

