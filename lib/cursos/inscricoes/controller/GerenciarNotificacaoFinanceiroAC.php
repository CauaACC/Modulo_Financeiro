<?php

/**
 * Aviso ao FINANCEIRO DO GESTOR quando uma inscricao JA PAGA e cancelada,
 * desativada ou excluida — para providenciar estorno ou cobranca de multa.
 *
 * E-mail do financeiro: sis_gestor.email_financeiro (por gestor, resolvido
 * pelo evento — ver GestorDAO::getEmailFinanceiroByEvento). Sem e-mail
 * cadastrado, nada e enviado (retorno false, silencioso).
 *
 * Corpo SEM dado pessoal (mesmo criterio do e-mail da Nota Fiscal): o dado
 * fica atras do login, acessado pelo link /api/inscrito/abrir.
 *
 * Gatilhos:
 *  - ListarInscritosAC::alterarStatusInscricao (botao X do admin)
 *  - GerenciarAtendimentoAC::cancelarInscricao (API / bot WhatsApp)
 */
class GerenciarNotificacaoFinanceiroAC
{
    /**
     * @param int    $codEvento
     * @param int    $codInscrito
     * @param string $origem 'admin-listagem' | 'api-whatsapp' | outro rotulo curto
     * @param array  $dados  opcional: acao ('DESATIVADA','EXCLUÍDA',...), valor (float),
     *                       formas (string), cod_pagamento
     * @return bool true somente se o e-mail foi de fato enviado
     */
    public static function notificarCancelamentoPago($codEvento, $codInscrito, $origem, array $dados = array())
    {
        try {
            $codEvento   = intval($codEvento);
            $codInscrito = intval($codInscrito);

            $emailFinanceiro = (new GestorDAO())->getEmailFinanceiroByEvento($codEvento);
            if (!$emailFinanceiro) {
                return false;
            }

            // Valor/formas: se o gatilho nao trouxe, resume os pagamentos vivos.
            if (!isset($dados['valor'])) {
                $resumo = (new PagamentoDAO())->resumoPagamentosAtivos($codEvento, $codInscrito);
                if ($resumo['total'] == 0) {
                    return false; // nada pago de fato — sem aviso
                }
                $dados['valor']  = $resumo['valor'];
                $dados['formas'] = $resumo['formas'];
            }

            $acao   = isset($dados['acao']) ? $dados['acao'] : 'CANCELADA';
            $formas = isset($dados['formas']) ? trim((string)$dados['formas']) : '';
            $valor  = floatval($dados['valor']);

            $linkFicha = WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO
                       . '/api/inscrito/abrir?cod_evento=' . $codEvento
                       . '&cod_inscrito=' . $codInscrito;

            $assunto = ABREV_EVENTO . ': inscricao PAGA cancelada - verificar estorno/multa'
                     . ' (insc. ' . $codInscrito . ')';

            $corpo  = "Uma inscricao COM PAGAMENTO foi cancelada e precisa de providencias";
            $corpo .= " do financeiro (estorno ou cobranca de multa, conforme o caso).<br><br>";
            $corpo .= "<b>Evento:</b> " . ABREV_EVENTO . " (cod. " . $codEvento . ")<br>";
            $corpo .= "<b>Inscricao:</b> " . $codInscrito . "<br>";
            $corpo .= "<b>Acao registrada:</b> " . $acao . "<br>";
            $corpo .= "<b>Valor pago:</b> R$ " . number_format($valor, 2, ',', '.') . "<br>";
            if ($formas != '') {
                $corpo .= "<b>Forma(s):</b> " . $formas . "<br>";
            }
            if (!empty($dados['cod_pagamento'])) {
                $corpo .= "<b>Cod. pagamento:</b> " . intval($dados['cod_pagamento']) . "<br>";
            }
            $corpo .= "<b>Origem:</b> " . $origem . "<br>";
            $corpo .= "<b>Data/hora:</b> " . date('d/m/Y H:i') . "<br><br>";
            $corpo .= "Abrir a ficha do inscrito (aba Pagamentos):<br>";
            $corpo .= "<a href='" . $linkFicha . "'>" . $linkFicha . "</a><br><br>";
            $corpo .= "Este e-mail nao contem dados pessoais por motivo de seguranca.<br>";
            $corpo .= "Apos devolver o valor, registre o estorno pelo botao Estornar no card do pagamento.";

            // Fora de producao o aviso e desviado para WB_EMAIL_TESTE (nunca
            // escrever para o financeiro real em teste local).
            $destino = GerenciarEmailAC::destinoParaAmbienteDeTeste($emailFinanceiro);
            if ($destino === null) {
                return false;
            }
            if ($destino['desviado']) {
                $corpo = "[TESTE - destino real: " . $emailFinanceiro . "]<br><br>" . $corpo;
            }

            return (bool) GerenciarEmailAC::enviarEmail($destino['email'], $assunto, $corpo, false, true);
        } catch (Exception $e) {
            // Aviso ao financeiro nunca pode quebrar o cancelamento em si.
            error_log('[NOTIF_FINANCEIRO] Falha ao notificar: ' . $e->getMessage());
            return false;
        }
    }
}
