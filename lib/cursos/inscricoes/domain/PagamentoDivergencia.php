<?php
/**
 * Compara o que esta registrado no sistema (pagamento / pedido) com o que o
 * PagSeguro diz da mesma transacao, e devolve as inconsistencias em texto.
 *
 * A regra vive aqui, num lugar so, porque a mesma comparacao e' usada em tres
 * telas: a consulta de transacoes (GerenciarConsultaPagSeguro), a aba Pedidos e
 * a aba Pagamentos do cadastro do inscrito.
 */
class PagamentoDivergencia {

    /** Cache das transacoes ja consultadas nesta requisicao (transacao_id => dados|false) */
    private static $cacheTransacao = array();

    /**
     * Dados da transacao no PagSeguro. Uma consulta por transacao distinta:
     * a mesma transacao costuma aparecer no pedido e no pagamento da mesma tela.
     */
    public static function getTransacao($transacaoId) {
        $transacaoId = trim((string) $transacaoId);
        if ($transacaoId === '') {
            return false;
        }
        if (!array_key_exists($transacaoId, self::$cacheTransacao)) {
            try {
                $objDAO = new PagamentoDAO();
                self::$cacheTransacao[$transacaoId] = $objDAO->getByCodTransacaoPagSeguro($transacaoId);
            } catch (Exception $e) {
                self::$cacheTransacao[$transacaoId] = false;
            }
        }
        return self::$cacheTransacao[$transacaoId];
    }

    /**
     * Classifica o status do PagSeguro em: pago | negativo | pendente.
     * ATENCAO: 'aguardando'/'analise' sao testados ANTES de 'paga', senao
     * "aguardando pagamento" cai no teste de 'paga' e passa por transacao paga.
     */
    public static function classificarStatus($status) {
        // strtolower/strpos (nao mb_*): mexem so no ASCII e nao dependem da
        // extensao mbstring. Por isso "analise" e procurada por 'lise', que
        // casa com ou sem o acento em "analise"/"análise".
        $s = strtolower(trim((string) $status));
        if ($s === '') {
            return '';
        }
        if (strpos($s, 'aguardando') !== false || strpos($s, 'lise') !== false) {
            return 'pendente';
        }
        if (strpos($s, 'cancel') !== false || strpos($s, 'devolv') !== false
            || strpos($s, 'estorn') !== false || strpos($s, 'disputa') !== false) {
            return 'negativo';
        }
        if (strpos($s, 'paga') !== false || strpos($s, 'aprovad') !== false || strpos($s, 'dispon') !== false) {
            return 'pago';
        }
        return 'pendente';
    }

    /** Aceita tanto o formato do banco (290.00) quanto o brasileiro (1.290,00). */
    public static function paraNumero($valor) {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }
        $txt = preg_replace('/[^\d,.-]/', '', (string) $valor);
        if (strpos($txt, ',') !== false) {
            $txt = str_replace('.', '', $txt);   // brasileiro: ponto e' milhar
            $txt = str_replace(',', '.', $txt);
        }
        return is_numeric($txt) ? (float) $txt : null;
    }

    /** Só a parte aaaa-mm-dd, para comparar datas sem depender da hora. */
    private static function soData($valor) {
        $txt = trim((string) $valor);
        if ($txt === '' || strpos($txt, '0000') === 0) {
            return '';
        }
        return substr($txt, 0, 10);
    }

    /**
     * Divergencias entre um PAGAMENTO do sistema e a transacao do PagSeguro.
     *
     * @param string $transacaoId  transacao_id do pagamento
     * @param string $statusPag    status em tab_pagamentos (ATIVADA, DESATIVADA...)
     * @param mixed  $valorPag     valor do pagamento
     * @param string $dataPag      data_pagamento
     * @return array lista de mensagens (vazia = tudo certo)
     */
    public static function doPagamento($transacaoId, $statusPag, $valorPag = null, $dataPag = '') {
        $ps = self::getTransacao($transacaoId);
        if (!$ps) {
            return array();   // transacao nao importada ainda: nao ha o que comparar
        }
        return self::compararPagamento($ps, $statusPag, $valorPag, $dataPag);
    }

    /**
     * Nucleo da comparacao com a transacao ja em maos (status, valor_bruto,
     * data_transacao). Existe separado para quem ja carregou a transacao junto
     * do pagamento — a listagem da consulta traz os dois lados na mesma query e
     * nao pode disparar uma consulta por linha.
     */
    public static function compararPagamento($ps, $statusPag, $valorPag = null, $dataPag = '') {
        if (empty($ps)) {
            return array();
        }

        $avisos    = array();
        $classePS  = self::classificarStatus(isset($ps['status']) ? $ps['status'] : '');
        $statusPS  = trim((string) (isset($ps['status']) ? $ps['status'] : '')) ?: 'sem status';
        $statusPag = strtoupper(trim((string) $statusPag));

        // 1) Situacao do pagamento x situacao da transacao
        if ($classePS === 'negativo' && $statusPag === 'ATIVADA') {
            $avisos[] = 'Transação ' . strtolower($statusPS)
                      . ' no PagSeguro, mas o pagamento está ATIVO no sistema';
        } elseif ($classePS === 'pendente' && $statusPag === 'ATIVADA') {
            $avisos[] = 'Pagamento ATIVO no sistema sem confirmação do PagSeguro ('
                      . strtolower($statusPS) . ')';
        } elseif ($classePS === 'pago' && $statusPag !== '' && $statusPag !== 'ATIVADA') {
            $avisos[] = 'Transação paga no PagSeguro, mas o pagamento está ' . $statusPag . ' no sistema';
        }

        // 2) Valor da transacao x valor do pagamento
        $valorPS  = self::paraNumero(isset($ps['valor_bruto']) ? $ps['valor_bruto'] : null);
        $valorSis = self::paraNumero($valorPag);
        if ($valorPS !== null && $valorSis !== null && abs($valorPS - $valorSis) > 0.01) {
            $avisos[] = 'Valor diverge: PagSeguro ' . number_format($valorPS, 2, ',', '.')
                      . ' x sistema ' . number_format($valorSis, 2, ',', '.');
        }

        // 3) Pagamento registrado antes da transacao existir
        $dtPag = self::soData($dataPag);
        $dtPS  = self::soData(isset($ps['data_transacao']) ? $ps['data_transacao'] : '');
        if ($dtPag !== '' && $dtPS !== '' && $dtPag < $dtPS) {
            $avisos[] = 'Data de pagamento anterior à data da transação';
        }

        // 4) Pagamento ativo sem data de pagamento
        if ($statusPag === 'ATIVADA' && $dtPag === '') {
            $avisos[] = 'Pagamento ATIVO sem data de pagamento registrada';
        }

        return $avisos;
    }

    /**
     * Divergencias entre um PEDIDO e a transacao do PagSeguro. No pedido a
     * comparacao e' mais grossa: interessa se ele esta quitado ou nao.
     *
     * @param string $transacaoId  transacao_id do pedido
     * @param bool   $pedidoPago   pedido esta quitado no sistema
     * @param mixed  $valorPedido  valor total do pedido
     * @return array lista de mensagens (vazia = tudo certo)
     */
    public static function doPedido($transacaoId, $pedidoPago, $valorPedido = null) {
        $ps = self::getTransacao($transacaoId);
        if (!$ps) {
            return array();
        }
        return self::compararPedido($ps, $pedidoPago, $valorPedido);
    }

    /** Nucleo da comparacao do pedido, com a transacao ja carregada. */
    public static function compararPedido($ps, $pedidoPago, $valorPedido = null) {
        if (empty($ps)) {
            return array();
        }

        $avisos   = array();
        $classePS = self::classificarStatus(isset($ps['status']) ? $ps['status'] : '');
        $statusPS = trim((string) (isset($ps['status']) ? $ps['status'] : '')) ?: 'sem status';

        if ($pedidoPago && $classePS === 'negativo') {
            $avisos[] = 'Pedido quitado, mas a transação está ' . strtolower($statusPS) . ' no PagSeguro';
        } elseif ($pedidoPago && $classePS === 'pendente') {
            $avisos[] = 'Pedido quitado sem confirmação do PagSeguro (' . strtolower($statusPS) . ')';
        } elseif (!$pedidoPago && $classePS === 'pago') {
            $avisos[] = 'PagSeguro registra a transação como ' . strtolower($statusPS)
                      . ', mas o pedido continua em aberto';
        }

        $valorPS  = self::paraNumero(isset($ps['valor_bruto']) ? $ps['valor_bruto'] : null);
        $valorSis = self::paraNumero($valorPedido);
        if ($valorPS !== null && $valorSis !== null && abs($valorPS - $valorSis) > 0.01) {
            $avisos[] = 'Valor diverge: PagSeguro ' . number_format($valorPS, 2, ',', '.')
                      . ' x pedido ' . number_format($valorSis, 2, ',', '.');
        }

        return $avisos;
    }

    /** Bloco de aviso padrao das telas. Devolve '' quando nao ha divergencia. */
    public static function alertaHtml($avisos, $compacto = false) {
        if (empty($avisos)) {
            return '';
        }
        $itens = '';
        foreach ($avisos as $aviso) {
            $itens .= '<div><i class="fa fa-exclamation-triangle"></i> '
                    . htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8') . '</div>';
        }
        $estilo = 'background:#f2dede; border:1px solid #ebccd1; color:#a94442;'
                . ' border-radius:4px; font-weight:bold;'
                . ($compacto ? ' padding:4px 8px; margin:4px 0; font-size:11px;'
                             : ' padding:8px 12px; margin:8px 0; font-size:12px;');
        return '<div class="aviso-divergencia-pagseguro" style="' . $estilo . '">' . $itens . '</div>';
    }
}
