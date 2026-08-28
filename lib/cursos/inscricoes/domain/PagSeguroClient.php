<?php

/**
 * Cliente HTTP da API PagBank/PagSeguro (v4, api.pagseguro.com).
 *
 * Toda conversa do PHP com o PagBank passa por aqui (molde: FocusNFeClient).
 * Primeiro uso: ESTORNO de cobranca (devolver o dinheiro ao pagador) a partir
 * do botao Estornar do admin — POST /charges/{charge_id}/cancel.
 *
 * O charge_id da v4 e derivavel do que ja esta em banco:
 *   charge_id = 'CHAR_' . tab_pagamentos.transacao_id  (code da API v3)
 * (comprovado pela conciliacao em recursosExtras/pagSeguroBaixarTransacoesByAPI.py)
 *
 * Autenticacao: Bearer token da conta do gestor. Ambiente e token vem do
 * env.php (fora do webroot):
 *   WB_PAGSEGURO_AMBIENTE ('producao' | 'homologacao' | ausente = desativado)
 *   WB_PAGSEGURO_TOKEN            (producao)
 *   WB_PAGSEGURO_TOKEN_SANDBOX    (homologacao/sandbox)
 *
 * Regras do PagBank: janela de estorno de 90 dias; Pix devolve a conta de
 * origem; estornos parciais nao mudam o status da charge (o total muda para
 * CANCELED). Usamos sempre estorno TOTAL nesta versao.
 */
class PagSeguroClient {

    const URL_PRODUCAO    = 'https://api.pagseguro.com';
    const URL_HOMOLOGACAO = 'https://sandbox.api.pagseguro.com';

    private $ambiente;
    private $token;
    private $timeout = 30;

    public function __construct($ambiente = null) {
        $this->ambiente = $ambiente ? $ambiente : self::ambienteConfigurado();
        $this->token = self::tokenConfigurado($this->ambiente);
        if ($this->token == '') {
            throw new Exception("Token do PagBank nao configurado para o ambiente '"
                . $this->ambiente . "'. Configure WB_PAGSEGURO_* no env.php.");
        }
    }

    public static function ambienteConfigurado() {
        $amb = defined('WB_PAGSEGURO_AMBIENTE') ? WB_PAGSEGURO_AMBIENTE : '';
        return ($amb == 'producao') ? 'producao' : ($amb ? 'homologacao' : '');
    }

    public static function tokenConfigurado($ambiente = null) {
        $ambiente = $ambiente !== null ? $ambiente : self::ambienteConfigurado();
        if ($ambiente == 'producao') {
            return defined('WB_PAGSEGURO_TOKEN') ? trim(WB_PAGSEGURO_TOKEN) : '';
        }
        if ($ambiente == 'homologacao') {
            return defined('WB_PAGSEGURO_TOKEN_SANDBOX') ? trim(WB_PAGSEGURO_TOKEN_SANDBOX) : '';
        }
        return '';
    }

    /** Estorno via API esta habilitado neste ambiente? */
    public static function estornoHabilitado() {
        return self::tokenConfigurado() != '';
    }

    private function getUrlBase() {
        return $this->ambiente == 'producao' ? self::URL_PRODUCAO : self::URL_HOMOLOGACAO;
    }

    /**
     * Estorno TOTAL de uma cobranca.
     *
     * @param string $transacaoId  tab_pagamentos.transacao_id (UUID, sem CHAR_)
     * @param int    $valorCentavos valor total em centavos (ex.: R$100,00 = 10000)
     * @param string $chaveIdempotencia identificador estavel do estorno
     *        (ex.: 'estorno-{cod_evento}-{cod_pagamento}') — reenvio acidental
     *        com a mesma chave nao gera devolucao em dobro
     * @return array ['ok'=>bool, 'http'=>int, 'resposta'=>array, 'erro'=>string]
     */
    public function estornarCharge($transacaoId, $valorCentavos, $chaveIdempotencia) {
        $transacaoId = strtoupper(trim((string)$transacaoId));
        if ($transacaoId == '') {
            return array('ok' => false, 'http' => 0, 'resposta' => array(),
                         'erro' => 'transacao_id vazio — nada a estornar via API');
        }
        $chargeId = (strpos($transacaoId, 'CHAR_') === 0) ? $transacaoId : 'CHAR_' . $transacaoId;

        $resultado = $this->requisitar(
            'POST',
            '/charges/' . rawurlencode($chargeId) . '/cancel',
            array('amount' => array('value' => intval($valorCentavos))),
            $chaveIdempotencia
        );

        // 200/201 = estorno aceito
        $resultado['ok'] = ($resultado['http'] >= 200 && $resultado['http'] < 300);
        if (!$resultado['ok'] && $resultado['erro'] == '') {
            $msgApi = '';
            if (isset($resultado['resposta']['error_messages'][0]['description'])) {
                $msgApi = $resultado['resposta']['error_messages'][0]['description'];
            } elseif (isset($resultado['resposta']['message'])) {
                $msgApi = $resultado['resposta']['message'];
            }
            $resultado['erro'] = 'PagBank HTTP ' . $resultado['http']
                               . ($msgApi != '' ? ' - ' . $msgApi : '');
        }

        $this->registrarLog('estornarCharge', $chargeId, $valorCentavos, $resultado);
        return $resultado;
    }

    /**
     * @return array {http, resposta (array), corpo_bruto, erro}
     *         So preenche 'erro' quando a requisicao nem sai (rede/cURL).
     */
    private function requisitar($metodo, $caminho, array $payload = null, $chaveIdempotencia = '') {
        $url = $this->getUrlBase() . $caminho;

        $headers = array(
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json',
        );
        if ($chaveIdempotencia != '') {
            $headers[] = 'x-idempotency-key: ' . $chaveIdempotencia;
        }

        $ch = curl_init();
        $opcoes = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_CUSTOMREQUEST  => $metodo,
            CURLOPT_HTTPHEADER     => $headers,
        );
        if ($payload !== null) {
            $opcoes[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        curl_setopt_array($ch, $opcoes);

        $corpoBruto = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erroCurl = curl_error($ch);
        curl_close($ch);

        if ($corpoBruto === false) {
            return array('http' => 0, 'resposta' => array(), 'corpo_bruto' => '',
                         'erro' => 'Falha de comunicacao com o PagBank: ' . $erroCurl);
        }

        $resposta = json_decode($corpoBruto, true);
        return array(
            'http'       => intval($http),
            'resposta'   => is_array($resposta) ? $resposta : array(),
            'corpo_bruto'=> (string)$corpoBruto,
            'erro'       => '',
        );
    }

    /** Trilha em arquivo: cada tentativa de estorno com pedido e resposta. */
    private function registrarLog($operacao, $chargeId, $valorCentavos, array $resultado) {
        try {
            $dir = defined('WB_PATH_LOG') ? WB_PATH_LOG : (dirname(__FILE__) . '/../../../../logn');
            $arquivo = rtrim($dir, '/\\') . '/' . date('Ymd') . '-pagseguro_estorno.txt';
            $linha = date('Y-m-d H:i:s')
                   . ' | ' . $operacao
                   . ' | ambiente=' . $this->ambiente
                   . ' | charge=' . $chargeId
                   . ' | valor_centavos=' . intval($valorCentavos)
                   . ' | http=' . (isset($resultado['http']) ? $resultado['http'] : '')
                   . ' | ok=' . (!empty($resultado['ok']) ? '1' : '0')
                   . ' | erro=' . (isset($resultado['erro']) ? $resultado['erro'] : '')
                   . ' | resposta=' . (isset($resultado['corpo_bruto']) ? substr($resultado['corpo_bruto'], 0, 1000) : '')
                   . "\n";
            @file_put_contents($arquivo, $linha, FILE_APPEND);
        } catch (Exception $e) {
            // log nunca derruba a operacao
        }
    }
}
