<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$FWS_Debug = false;
$FWS_ = processPostArray($FWS_Debug);
#-------------------------------------------
@$FWS_['data_inclusao']  = date('Y-m-d H:i:s');
@$FWS_['resp_inclusao']   =$_SESSION['_cod_usuario'];
#@$FWS_['status']         = "ATIVADA";
@$FWS_['session_id_inclusao']      = $_COOKIE["PHPSESSID"];
@$FWS_['http_user_agent_inclusao'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente_inclusao']      = $_SERVER["REMOTE_ADDR"]; 

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

$Var_Sql = ""
.""
.$cod_evento.","
.$objI->getCodInscrito().",'"
.formatValueToDotSeparator($FWS_['valor'])."','"
.$FWS_['forma_pagamento']."','"
.$FWS_['observacao']."','"
.$FWS_['observacao_recibo']."','"
.addslashesopctec($FWS_['numero_empenho'])."','"
.addslashesopctec($FWS_['data_emissao_recibo'])."','"
.addslashesopctec($FWS_['data_pagamento'])."','"
.$FWS_['data_inclusao']."','"
.$FWS_['resp_inclusao']."','"
.$FWS_['status']."','"
.$FWS_['session_id_inclusao']."','"
.$FWS_['http_user_agent_inclusao']."','"
.$FWS_['ip_cliente_inclusao']."','"
.$FWS_['pedido_id']."','"
.$FWS_['transacao_id']."'"
."";
//-------------------------------------------

//I: ---------------------------------------
$Var_Campos = ""
."cod_evento      ,"
."cod_inscrito    ,"
."valor           ,"
."forma_pagamento ,"
."observacao      ,"
."observacao_recibo,"
."numero_empenho  ,"
."data_emissao_recibo  ,"
."data_pagamento  ,"
."data_inclusao   ,"
."resp_inclusao   ,"
."status          ,"
."session_id_inclusao      ,"
."http_user_agent_inclusao ,"
."ip_cliente_inclusao      ,"
."pedido_id      ,"
."transacao_id      ";

#-------------------------------------------
#echo $Var_Sql."<br>".$Var_Campos."<br>";

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG']=$MSG;
  $CONTENT = "view/admin/inscritos-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;

}Else{

  $var_sql= "
             Insert Into tab_pagamentos (".$Var_Campos.")
             Values (".$Var_Sql.")
            ";

  If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){

      // Lançamento contábil automático
      try {
          $codPagamentoInserido = $Conexao_db->Insert_ID();
          $config = EventoConfiguracao::getInstancePopulado($cod_evento);
          $codPlanoContasReceita = $config->getCodPlanoContasReceita();

          if (!empty($codPlanoContasReceita) && !empty($codPagamentoInserido)) {
              $lancamento = new LancamentosContabeis();
              $lancamento->setCodEvento($cod_evento);
              $lancamento->setCodPagamento($codPagamentoInserido);

              if (!$lancamento->existeLancamentoByCodPagamento()) {
                  $lancamento->setTipoLancamento('VENDA');
                  $lancamento->setValor(formatValueToDotSeparator($FWS_['valor']));
                  $lancamento->setOrigemLancamento('MANUAL');
                  $lancamento->setCredito($codPlanoContasReceita);
                  $lancamento->setCodPlanoContas($codPlanoContasReceita);
                  $lancamento->setFlgLancamento('C');
                  $lancamento->setConciliado(1);
                  $lancamento->setDataCompetencia(date('Y-m-d'));
                  $lancamento->setDataFinanceira(date('Y-m-d'));
                  $lancamento->setHistorico('Lançamento automático - Inscrito: ' . $objI->getCodInscrito()
                      . ' | Forma: ' . $FWS_['forma_pagamento']
                      . ' | Transação: ' . $FWS_['transacao_id']);
                  $lancamento->salvar();
              }
          }
      } catch (OKException $e) {
          // Lançamento criado com sucesso
      } catch (Exception $e) {
          // Falha no lançamento não bloqueia o pagamento
      }

      // Aviso automático de pagamento confirmado (não bloqueia o cadastro)
      try {
          $objPagamentoAviso = new Pagamento();
          $objPagamentoAviso->setCodEvento($cod_evento);
          $objPagamentoAviso->setCodPagamento($codPagamentoInserido);
          $objPagamentoAviso->setCodInscrito($objI->getCodInscrito());
          $objPagamentoAviso->setValor(formatValueToDotSeparator($FWS_['valor']));
          $objPagamentoAviso->setFormaPagamento($FWS_['forma_pagamento']);
          $objPagamentoAviso->setStatus($FWS_['status']);
          GerenciarEmailAC::enviarConfirmacaoPagamento($objPagamentoAviso, $FWS_Debug);
      } catch (Exception $e) {
          // Falha no aviso não bloqueia o cadastro
      }

      $MSG     = "Operação-Executada-com-Sucesso--Inscrito-incluído-com-sucesso";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$FWS_['cod_inscrito'];
  }Else{
    $MSG     = "Erro-de-Execucao-ERRO-SQL-Insert";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$FWS_['cod_inscrito'];
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
  echo "<br>Gravando seus dados no sistema<BR>";
  echo "<br>Este procedimento demora poucos segundos.<br>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}
