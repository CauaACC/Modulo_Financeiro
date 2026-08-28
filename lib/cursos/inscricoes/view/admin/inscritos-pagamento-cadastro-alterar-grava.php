<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/inscritos-seguranca.php");

#-------------------------------------------
$debug = false;
#-------------------------------------------
@$fws_['data_alteracao'] = date('Y-m-d H:i:s');
@$fws_['resp_alteracao'] = $_SESSION['_cod_usuario'];
#@$fws_['status']         = "ATIVADA";
@$fws_['session_id_alteracao'] = $_COOKIE["PHPSESSID"];
@$fws_['http_user_agent_alteracao'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente_alteracao'] = $_SERVER["REMOTE_ADDR"];

#echo $acao."-<BR>";
#-------------------------------------------

if (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
    $MSG = "Erro-de-Conexao";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_inscrito=" . $cod_inscrito;
} else {

    $var_sql = "
    INSERT INTO tab_pagamentos_atualizados
    SELECT *
    FROM tab_pagamentos
    WHERE (cod_pagamento=" . $fws_['cod_pagamento'] . ")and(cod_evento =" . COD_EVENTO . ")
    ";

    if ($RS = FWA_Executa_SQL($debug, $Conexao_db, $var_sql)) {
        $rowsBackup = $Conexao_db->Affected_Rows();
        if ($rowsBackup == 0) {
            $fws_Execucao = false;
            $MSG = "Erro--Registro-de-pagamento-não-encontrado-no-evento-atual-(cod_evento=" . COD_EVENTO . ")--Verifique-se-você-está-no-evento-correto.";
            $_SESSION['MSG'] = $MSG;
            $CONTENT = "view/admin/inscritos-cadastro-ok.php";
            $URL = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_pagamento=" . $fws_['cod_pagamento'] . "&cod_inscrito=" . $fws_['cod_inscrito'];
        } else {
            $fws_Execucao = true;
        }
    } else {
        $fws_Execucao = false;
        $MSG = "Erro-de-Execucao-ERRO-SQL-Backup-Pagamento";
        $_SESSION['MSG'] = $MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_pagamento=" . $fws_['cod_pagamento'] . "&cod_inscrito=" . $fws_['cod_inscrito'];
    }

    if ($fws_Execucao == true) {
        $sqlPagamento = "";
        if($fws_['pedido_id'] != ''){ 
            $sqlPagamento .= " pedido_id = ".$fws_['pedido_id']. ", ";
        }
        if($fws_['transacao_id'] != ''){ 
            $sqlPagamento .= " transacao_id='" . $fws_['transacao_id'] . "', ";
        }
        if($fws_['data_emissao_recibo'] != ''){ 
            $sqlPagamento .= " data_emissao_recibo  ='" . addslashesopctec($fws_['data_emissao_recibo']) . "', ";
        }
        $var_sql = "
        UPDATE tab_pagamentos SET
        valor           ='" . formatValueToDotSeparator($fws_['valor']) . "',
        forma_pagamento ='" . $fws_['forma_pagamento'] . "',
        observacao      ='" . $fws_['observacao'] . "',
        observacao_recibo ='" . $fws_['observacao_recibo'] . "',
        numero_empenho  ='" . addslashesopctec($fws_['numero_empenho']) . "',
        data_pagamento ='" . addslashesopctec($fws_['data_pagamento']) . "',
        status          ='" . $fws_['status'] . "',
        ".$sqlPagamento."
        session_id_alteracao      ='" . $fws_['session_id_alteracao'] . "',
        http_user_agent_alteracao ='" . $fws_['http_user_agent_alteracao'] . "',
        ip_cliente_alteracao      ='" . $fws_['ip_cliente_alteracao'] . "',
        resp_alteracao  ='" . $fws_['resp_alteracao'] . "',
        data_alteracao  ='" . $fws_['data_alteracao'] . "'        
        WHERE (cod_pagamento=" . $fws_['cod_pagamento'] . ")and(cod_evento =" . COD_EVENTO . ")
        ";

        if ($RS = FWA_Executa_SQL($debug, $Conexao_db, $var_sql)) {
            $fws_Execucao = true;
            $MSG = "Operação-Executada-com-Sucesso";
            $_SESSION['MSG'] = $MSG;
            $CONTENT = "view/admin/inscritos-cadastro-ok.php";
            $URL = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_pagamento=" . $fws_['cod_pagamento'] . "&cod_inscrito=" . $fws_['cod_inscrito'];

            if ($_SERVER['REMOTE_ADDR'] == '200.203.142.2555') {
                if ($status == 'CANCELADO' OR $status == 'DEVOLVIDO' OR $status == 'ESTORNADO') {
//                  DESATIVADA CANCELADO DEVOLVIDO ESTORNADO        
                    $objPagamento = new Pagamento();
                    $objPagamento->setCodEvento(COD_EVENTO);
                    $objPagamento->setCodPagamento($fws_['cod_pagamento']);
                    $objPagamento->getPagamentoById();
                    $objInscrito = $objPagamento->getInscrito();

                    $to = $objInscrito->getEMail();
                    $to = 'zakacc@gmail.com';
                    $subject = "" . ABREV_EVENTO . " - Pagamento " . $status . " ";
                    $message = ""
                            . "\r\n\r\nOla " . $objInscrito->getNomeApelido()
                            . "\r\n\r\nO pagamento de sua inscrição no " . ABREV_EVENTO . " foi " . $status . "."
                            . "\r\nObservação:"
                            . "\r\n" . $fws_['observacao']
                            . "\r\n url do sistema: " . EMAIL_CONTATO_LINK_LOGIN
                            . "\r\n\r\n" . ABREV_EVENTO . " - " . NOME_EVENTO
                            . "\r\nDúvidas: " . EMAIL_CONTATO
                            . "\r\nData: " . Date('d/m/Y H:i')
                            . "";
                    GerenciarEmailAC::enviarEmail($to, $subject, $message);
                    echo $message;
                    exit();
                }
            }
        } else {
            $fws_Execucao = false;
            $MSG = "Erro-de-Execucao-ERRO-SQL-Update-Pagamento";
            $_SESSION['MSG'] = $MSG;
            $CONTENT = "view/admin/inscritos-cadastro-ok.php";
            $URL = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_pagamento=" . $fws_['cod_pagamento'] . "&cod_inscrito=" . $fws_['cod_inscrito'];
        }
    }
}
#--------------------------------------------------------------
$MSG_Imprimir = $MSG;

$loc = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc, $subst, $MSG_Imprimir);

$loc = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc, $subst, $MSG_Imprimir);
#--------------------------------------------------------------
if ($debug == true) {
    echo $MSG_Imprimir;
    echo "<br><a href='{$URL}'>{$URL}</a><br>";
} else {
    echo "<br>Aguarde um momento - " . date("d/m/Y - H:i:s");
    echo "<br>Gravando seus dados no sistema<br>";
    echo "<br>Este procedimento demora poucos segundos.<br>";
    echo "<br><a href='{$URL}'>{$URL}</a><br>";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\">";
}
