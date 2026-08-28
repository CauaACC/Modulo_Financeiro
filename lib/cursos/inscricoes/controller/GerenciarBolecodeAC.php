<?php

class GerenciarBolecodeAC
{
    // Caminhos dos certificados (relativos à raiz do projeto)
    private $certPath;
    private $keyPath;
    private $clientId;
    private $clientSecret;

    public function __construct()
    {
        $logCacheCert = WB_ROOT . '../logn/eventos/cache/cert/';
        $this->certPath = $logCacheCert . 'Certificado_Itau.crt';
        $this->keyPath  = $logCacheCert . 'Certificado_Eventos_Chave.key';

        // Credenciais vem da configuracao do evento
        $objEv = new Evento();
        $objEv->setCodEvento(COD_EVENTO);
        $objEv->getEventoById();
        $objEvConf = $objEv->getEventoConfiguracao();
        $this->clientId     = $objEvConf->getItauBolecodeClientId();
        $this->clientSecret = $objEvConf->getItauBolecodeClientSecret();
    }

    /**
     * GET /api/bolecode/teste
     * Testa conexao com a API do Itau (obtem token)
     */
    public function testarConexao($fws_, $debug = false)
    {
        header('Content-Type: application/json; charset=utf-8');
        $resultado = [];

        try {
            // Verificar certificados
            $resultado['certificados'] = [
                'cert_existe' => file_exists($this->certPath),
                'cert_path'   => $this->certPath,
                'key_existe'  => file_exists($this->keyPath),
                'key_path'    => $this->keyPath,
            ];

            if (!file_exists($this->certPath) || !file_exists($this->keyPath)) {
                throw new Exception('Certificados nao encontrados. Verifique os caminhos.');
            }

            // Verificar credenciais
            $resultado['credenciais'] = [
                'client_id_configurado' => !empty($this->clientId),
                'client_secret_configurado' => !empty($this->clientSecret),
            ];

            if (empty($this->clientId) || empty($this->clientSecret)) {
                throw new Exception('Credenciais Itau BoleCode nao configuradas para o evento ' . COD_EVENTO);
            }

            // Verificar extensoes PHP necessarias
            $resultado['extensoes'] = [
                'curl'    => extension_loaded('curl'),
                'openssl' => extension_loaded('openssl'),
                'json'    => extension_loaded('json'),
            ];

            if (!extension_loaded('curl')) {
                throw new Exception('Extensao cURL nao esta habilitada.');
            }

            // Tentar obter token
            $token = $this->obterAccessToken();
            $resultado['token'] = [
                'sucesso' => true,
                'token_parcial' => substr($token, 0, 20) . '...',
            ];

            $resultado['status'] = 'OK';
            $resultado['mensagem'] = 'Conexao com API Itau funcionando.';

        } catch (Exception $e) {
            $resultado['status'] = 'ERRO';
            $resultado['mensagem'] = $e->getMessage();
        }

        echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * GET /api/bolecode/simular
     * Emite boleto de simulacao com dados de teste
     */
    public function simular($fws_, $debug = false)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $dadosBoleto = [
                'etapa'                 => 'simulacao',
                'id_beneficiario'       => $fws_['id_beneficiario'] ?: '411300148888',
                'tipo_boleto'           => 'a vista',
                'codigo_carteira'       => '109',
                'seu_numero'            => 'SIM' . date('His'),
                'nosso_numero'          => '00000' . rand(100, 999),
                'valor_total'           => '00000000000010000', // R$ 100,00
                'data_emissao'          => date('Y-m-d'),
                'data_vencimento'       => date('Y-m-d', strtotime('+30 days')),
                'data_limite_pagamento' => date('Y-m-d', strtotime('+30 days')),
                'nome_pagador'          => 'Teste Simulacao',
                'cpf_pagador'           => '01603004955',
                'logradouro'            => 'RUA TESTE 123',
                'bairro'                => 'Centro',
                'cidade'                => 'Londrina',
                'uf'                    => 'PR',
                'cep'                   => '86000000',
                'mensagem'              => 'Boleto de simulacao - teste API',
                'chave_pix'             => $fws_['chave_pix'] ?: '0f0f4eba-8bb5-4de8-9dc7-fa09a8bd0761',
            ];

            $token = $this->obterAccessToken();
            $payload = $this->montarPayloadBolecode($dadosBoleto);
            $retorno = $this->enviarBolecode($payload, $token);

            echo json_encode([
                'status' => 'OK',
                'mensagem' => 'Simulacao realizada com sucesso.',
                'dados_enviados' => $dadosBoleto,
                'retorno_itau' => $retorno,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'ERRO',
                'mensagem' => $e->getMessage(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    /**
     * POST /api/bolecode/emitir
     * Emite boleto BoleCode (simulacao ou efetivacao)
     */
    public function emitirBoleto($fws_, $debug = false)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $dadosBoleto = [
                'etapa'                 => $fws_['etapa'] ?: 'simulacao',
                'id_beneficiario'       => $fws_['id_beneficiario'],
                'tipo_boleto'           => $fws_['tipo_boleto'] ?: 'a vista',
                'codigo_carteira'       => $fws_['codigo_carteira'] ?: '109',
                'seu_numero'            => $fws_['seu_numero'],
                'nosso_numero'          => $fws_['nosso_numero'],
                'valor_total'           => $fws_['valor_total'],
                'data_emissao'          => $fws_['data_emissao'] ?: date('Y-m-d'),
                'data_vencimento'       => $fws_['data_vencimento'],
                'data_limite_pagamento' => $fws_['data_limite_pagamento'],
                'nome_pagador'          => $fws_['nome_pagador'],
                'cpf_pagador'           => $fws_['cpf_pagador'],
                'logradouro'            => $fws_['logradouro'],
                'bairro'                => $fws_['bairro'],
                'cidade'                => $fws_['cidade'],
                'uf'                    => $fws_['uf'],
                'cep'                   => $fws_['cep'],
                'mensagem'              => $fws_['mensagem'] ?: '',
                'chave_pix'             => $fws_['chave_pix'],
            ];

            $token = $this->obterAccessToken();
            $payload = $this->montarPayloadBolecode($dadosBoleto);
            $retorno = $this->enviarBolecode($payload, $token);

            echo json_encode([
                'status' => 'OK',
                'mensagem' => 'Boleto processado com sucesso.',
                'retorno' => $retorno,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'ERRO',
                'mensagem' => $e->getMessage(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    /**
     * GET /api/bolecode/consultar?cod_boleto=4422
     * Consulta status de pagamento do boleto (PIX via /cob/{txid})
     */
    public function consultarPagamento($fws_, $debug = false)
    {
        header('Content-Type: application/json; charset=utf-8');
        // Aceitar parametros via GET tambem
        $codBoleto = $fws_['cod_boleto'] ?: $_GET['cod_boleto'];
        $pedidoId = $fws_['pedido_id'] ?: $_GET['pedido_id'];
        $numDoc = $fws_['numero_documento'] ?: $_GET['numero_documento'];

        try {
            // Carregar boleto do banco
            $objBoleto = new Boleto();
            $objBoleto->setCodEvento(COD_EVENTO);
            if (!empty($codBoleto)) {
                $objBoleto->setCodBoleto($codBoleto);
                $objBoleto->getBoletoById();
            } else if (!empty($pedidoId)) {
                $objBoleto->setPedidoId($pedidoId);
                $objBoleto->getBoletoByPedidoId();
            } else if (!empty($numDoc)) {
                $objBoleto->setNumeroDocumento($numDoc);
                $objBoleto->getBoletoByNumeroDocumento();
            } else {
                throw new Exception('Informe cod_boleto, pedido_id ou numero_documento.');
            }
            if (!$objBoleto->getCodBoleto()) {
                throw new Exception('Boleto nao encontrado.');
            }

            $retornoApi = json_decode($objBoleto->getRetornoApiItau(), true);
            if (empty($retornoApi)) {
                throw new Exception('Boleto sem retorno da API registrado.');
            }

            $apiData = $retornoApi['data'] ?? $retornoApi;
            $txid = $apiData['dados_qrcode']['txid'] ?? '';

            if (empty($txid)) {
                throw new Exception('txid nao encontrado no retorno da API.');
            }

            // Consultar via Cash Management /v2/boletos/{id}
            $token = $this->obterAccessToken();
            $idBoleto = $apiData['dado_boleto']['dados_individuais_boleto'][0]['id_boleto_individual'] ?? $txid;
            $urlConsulta = 'https://secure.api.cloud.itau.com.br/cash_management/v2/boletos/' . $idBoleto;

            list($info, $response) = $this->curlRequest(
                'GET',
                $urlConsulta,
                [
                    "Authorization: Bearer {$token}",
                    "x-itau-apikey: " . $this->clientId,
                    "x-itau-correlationID: " . uniqid(),
                    "x-itau-flowID: 1",
                ],
                null,
                false
            );

            $cobData = json_decode($response, true);
            $situacao = $cobData['situacao'] ?? $cobData['status'] ?? 'DESCONHECIDO';

            $resultado = [
                'status' => 'OK',
                'cod_boleto' => $objBoleto->getCodBoleto(),
                'id_boleto' => $idBoleto,
                'http_code' => $info['http_code'],
                'situacao' => $situacao,
                'consulta' => $cobData,
            ];

            $resultado['status_pagamento'] = $situacao;
            $resultado['pago'] = in_array($situacao, ['LIQUIDADO', 'PIX_LIQUIDADO']);

            if ($statusCob === 'CONCLUIDA' && !empty($cobData['pix'])) {
                $resultado['pix_pagamento'] = $cobData['pix'][0];
            }

            echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (OKException $ok) {
            // ignorar
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'ERRO',
                'mensagem' => $e->getMessage(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    // ==========================================
    // Metodos privados
    // ==========================================

    private function obterAccessToken()
    {
        $postFields = http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        list($info, $response) = $this->curlRequest(
            'POST',
            'https://sts.itau.com.br/api/oauth/token',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'x-itau-flowID: 1',
                'x-itau-correlationID: 2',
            ],
            $postFields,
            false
        );

        if ($info['http_code'] >= 400) {
            throw new Exception("Erro ao obter token (HTTP {$info['http_code']}): " . $response);
        }

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            throw new Exception('access_token nao retornado: ' . $response);
        }

        return $data['access_token'];
    }

    private function montarPayloadBolecode(array $dados)
    {
        return [
            "etapa_processo_boleto" => $dados['etapa'],
            "beneficiario" => [
                "id_beneficiario" => $dados['id_beneficiario'],
            ],
            "dado_boleto" => [
                "tipo_boleto"         => $dados['tipo_boleto'],
                "texto_seu_numero"    => $dados['seu_numero'],
                "codigo_carteira"     => $dados['codigo_carteira'],
                "valor_total_titulo"  => $dados['valor_total'],
                "codigo_especie"      => "01",
                "data_emissao"        => $dados['data_emissao'],
                "valor_abatimento"    => "00000000000000000",
                "pagador" => [
                    "pessoa" => [
                        "nome_pessoa"   => $dados['nome_pagador'],
                        "nome_fantasia" => $dados['nome_pagador'],
                        "tipo_pessoa"   => [
                            "codigo_tipo_pessoa"              => "F",
                            "numero_cadastro_pessoa_fisica"   => $dados['cpf_pagador'],
                        ],
                    ],
                    "endereco" => [
                        "nome_logradouro" => $dados['logradouro'],
                        "nome_bairro"     => $dados['bairro'],
                        "nome_cidade"     => $dados['cidade'],
                        "sigla_UF"        => $dados['uf'],
                        "numero_CEP"      => $dados['cep'],
                    ],
                ],
                "dados_individuais_boleto" => [
                    [
                        "numero_nosso_numero"    => $dados['nosso_numero'],
                        "data_vencimento"        => $dados['data_vencimento'],
                        "texto_uso_beneficiario" => $dados['seu_numero'],
                        "valor_titulo"           => $dados['valor_total'],
                        "data_limite_pagamento"  => $dados['data_limite_pagamento'],
                    ],
                ],
                "lista_mensagem_cobranca" => [
                    [
                        "mensagem" => $dados['mensagem'] ?: "mensagem 1",
                    ],
                ],
            ],
            "dados_qrcode" => [
                "chave" => $dados['chave_pix'],
            ],
        ];
    }

    private function enviarBolecode(array $payload, string $accessToken)
    {
        $clientId = $this->clientId;

        list($info, $response) = $this->curlRequest(
            'POST',
            'https://secure.api.itau/pix_recebimentos_conciliacoes/v2/boletos_pix',
            [
                "Authorization: Bearer {$accessToken}",
                "x-itau-apikey: " . $clientId,
                "x-itau-correlationID: " . ($payload['dado_boleto']['texto_seu_numero'] ?? uniqid()),
                "x-itau-flowID: 1",
            ],
            $payload,
            true
        );

        if ($info['http_code'] >= 400) {
            throw new Exception("Erro ao registrar BoleCode (HTTP {$info['http_code']}): " . $response);
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new Exception('Resposta invalida ao criar BoleCode: ' . $response);
        }

        return $data;
    }

    private function curlRequest($method, $url, $headers = [], $body = null, $isJsonBody = true)
    {
        $ch = curl_init($url);

        $defaultHeaders = [];
        if ($isJsonBody) {
            $defaultHeaders[] = 'Accept: application/json';
            $defaultHeaders[] = 'Content-Type: application/json';
        }

        $headers = array_merge($defaultHeaders, $headers);

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSLCERT        => $this->certPath,
            CURLOPT_SSLKEY         => $this->keyPath,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        if ($body !== null) {
            if ($isJsonBody) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }

        $response = curl_exec($ch);
        $info     = curl_getinfo($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception("Erro cURL: " . $err);
        }

        return [$info, $response];
    }
}
