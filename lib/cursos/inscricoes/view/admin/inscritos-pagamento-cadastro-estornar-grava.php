<?php
/**
 * Registra o ESTORNO de um pagamento (botao "Estornar" do card na aba
 * Pagamentos da ficha do inscrito).
 *
 * O que faz: snapshot em tab_pagamentos_atualizados, status ESTORNADO e
 * contra-lancamento financeiro (debito) — ver Pagamento::estornar().
 * A devolucao do dinheiro em si e feita FORA do sistema (gateway/banco).
 *
 * Restrito a SIS_CONTROLE_FINANCEIRO: estorno e ato do financeiro (o aviso
 * de cancelamento chega a ele por e-mail; este botao e o registro).
 */
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/inscritos-seguranca.php");

FWA_exigirPermissao(array(
    enumSistema::SIS_CONTROLE_FINANCEIRO,
    enumSistema::SIS_GERENCIAR_ADM_GERAL,
));

$debug = false;

// Chamada direta por link (GET): os parametros vem por processGetArray(),
// como no inscritos-pagamento-cadastro-excluir-grava.php. O $fws_ montado
// pelo config.php nao carrega os GETs desta requisicao.
$FWS_ = processGetArray($debug);
$cod_pagamento = isset($FWS_['cod_pagamento']) ? intval($FWS_['cod_pagamento']) : 0;
$cod_inscrito  = isset($FWS_['cod_inscrito'])  ? intval($FWS_['cod_inscrito'])  : 0;

$CONTENT = "view/admin/inscritos-cadastro-ok.php";
$URL = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_inscrito=" . $cod_inscrito . "#pagamentos";

try {
    if (!$cod_pagamento or !$cod_inscrito) {
        throw new Exception("Parâmetros cod_pagamento/cod_inscrito não informados.");
    }

    // Mesmo gate do botao: sem credencial do PagBank no env.php a operacao nao
    // existe (nem por URL digitada na mao).
    if (!PagSeguroClient::estornoHabilitado()) {
        throw new Exception("Estorno indisponível: a integração com o PagBank não está"
            . " configurada neste ambiente (WB_PAGSEGURO_TOKEN no env.php).");
    }

    $objPagamento = new Pagamento();
    $objPagamento->setCodEvento(COD_EVENTO);
    $objPagamento->setCodPagamento($cod_pagamento);
    $objPagamento->estornar($debug);

    // estornar() sempre lanca excecao; chegar aqui e anomalia
    throw new Exception("Estorno não confirmado.");
} catch (OKException $exc) {
    $MSG = $exc->getMessage();

    // Confirmacao ao inscrito (fora de producao o e-mail e desviado)
    try {
        $objInscrito = $objPagamento->getInscritoByIdPagamento();
        $emailInscrito = $objInscrito ? trim((string)$objInscrito->getEMail()) : '';
        if ($emailInscrito != '') {
            $destino = GerenciarEmailAC::destinoParaAmbienteDeTeste($emailInscrito);
            if ($destino !== null) {
                $assunto = ABREV_EVENTO . ' - Estorno de pagamento';
                $corpo  = ($destino['desviado'] ? "[TESTE - destino real: " . $emailInscrito . "]<br><br>" : "");
                $corpo .= "Olá " . $objInscrito->getNomeApelido() . ",<br><br>";
                $corpo .= "O pagamento de R$ " . formatMoneyView($objPagamento->getValor())
                        . " (cód. " . $cod_pagamento . ") referente à sua inscrição no "
                        . ABREV_EVENTO . " foi ESTORNADO.<br><br>";
                $corpo .= "A devolução do valor segue o prazo do meio de pagamento utilizado.<br>";
                $corpo .= "Dúvidas: " . EMAIL_CONTATO . "<br>";
                $corpo .= "Data: " . date('d/m/Y H:i') . "<br>";
                GerenciarEmailAC::enviarEmail($destino['email'], $assunto, $corpo, false, true);
            }
        }
    } catch (Exception $ignore) {
        // e-mail de confirmacao nao bloqueia o estorno
    }
} catch (Exception $exc) {
    $MSG = "Erro: " . $exc->getMessage();
}

$_SESSION['MSG'] = $MSG;

echo "<br>Aguarde um momento - " . date("d/m/Y - H:i:s");
echo "<br>" . htmlspecialchars($MSG) . "<br>";
echo "<br><a href='{$URL}'>{$URL}</a><br>";
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
