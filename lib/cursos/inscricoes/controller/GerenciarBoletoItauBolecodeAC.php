<?php

/**
 * Controller para integração com Itaú BoleCode (boleto híbrido PIX + boleto bancário)
 *
 * Usa a API de BoleCode do Itaú para registrar boletos com QR Code PIX.
 * Certificados em: logn/eventos/cache/cert/
 *
 * Fluxo:
 * 1. Inscrito clica em "Pagar" → itauBolecodeForm.php
 * 2. Form verifica se já existe boleto registrado (tab_boletos)
 * 3. Se não existe, chama a API do Itaú para registrar
 * 4. Salva retorno no banco
 * 5. Exibe tela híbrida: QR Code PIX + botão PDF
 */
class GerenciarBoletoItauBolecodeAC {

    // URLs da API Itaú
    const TOKEN_URL    = 'https://sts.itau.com.br/api/oauth/token';
    const BOLECODE_URL = 'https://secure.api.itau/pix_recebimentos_conciliacoes/v2/boletos_pix';

    // Caminho dos certificados (relativo à raiz do site)
    const CERT_FILE = '../logn/eventos/cache/cert/Certificado_Itau.crt';
    const KEY_FILE  = '../logn/eventos/cache/cert/Certificado_Eventos_Chave.key';

    /**
     * Requisição cURL genérica para a API do Itaú
     */
    private function curlRequest($method, $url, $headers = [], $body = null, $isJsonBody = true) {
        $ch = curl_init($url);

        $defaultHeaders = [];
        if ($isJsonBody) {
            $defaultHeaders[] = 'Accept: application/json';
            $defaultHeaders[] = 'Content-Type: application/json';
        }
        $headers = array_merge($defaultHeaders, $headers);

        $certPath = WB_ROOT . self::CERT_FILE;
        $keyPath  = WB_ROOT . self::KEY_FILE;

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSLCERT        => $certPath,
            CURLOPT_SSLKEY         => $keyPath,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
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
            throw new Exception("Erro cURL Itaú: " . $err);
        }

        return [$info, $response];
    }

    /**
     * Obtém access_token via OAuth2 client_credentials
     */
    public function obterAccessToken($clientId, $clientSecret) {
        $postFields = http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ]);

        list($info, $response) = $this->curlRequest(
            'POST',
            self::TOKEN_URL,
            [
                'Content-Type: application/x-www-form-urlencoded',
                'x-itau-flowID: 1',
                'x-itau-correlationID: 2',
            ],
            $postFields,
            false
        );

        if ($info['http_code'] >= 400) {
            throw new Exception("Erro ao obter token Itaú (HTTP {$info['http_code']}): " . $response);
        }

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            throw new Exception('access_token não retornado pelo Itaú: ' . $response);
        }

        return $data['access_token'];
    }

    /**
     * Monta o payload do BoleCode conforme padrão da API Itaú
     */
    public function montarPayload($fws_, $objEvConf, $etapa = 'efetivacao') {
        $valor17 = str_pad(str_replace(['.', ','], '', $fws_['valorBoleto']), 17, '0', STR_PAD_LEFT);

        // Determinar tipo pessoa
        $cpfLimpo = preg_replace('/\D/', '', $fws_['cpfCnpj']);
        if (strlen($cpfLimpo) <= 11) {
            $tipoPessoa = [
                "codigo_tipo_pessoa"            => "F",
                "numero_cadastro_pessoa_fisica"  => $cpfLimpo,
            ];
        } else {
            $tipoPessoa = [
                "codigo_tipo_pessoa"                => "J",
                "numero_cadastro_nacional_pessoa_juridica" => $cpfLimpo,
            ];
        }

        return [
            "etapa_processo_boleto" => $etapa,

            "beneficiario" => [
                "id_beneficiario" => $objEvConf->getItauBolecodeIdBeneficiario(),
            ],

            "dado_boleto" => [
                "tipo_boleto"        => "a vista",
                "texto_seu_numero"   => $fws_['numeroDocumento'],
                "codigo_carteira"    => $objEvConf->getItauBolecodeCodigoCarteira(),
                "valor_total_titulo" => $valor17,
                "codigo_especie"     => "01",
                "data_emissao"       => date('Y-m-d'),
                "valor_abatimento"   => "00000000000000000",

                "pagador" => [
                    "pessoa" => [
                        "nome_pessoa"   => mb_substr($fws_['sacado'], 0, 40),
                        "nome_fantasia" => mb_substr($fws_['sacado'], 0, 40),
                        "tipo_pessoa"   => $tipoPessoa,
                    ],
                    "endereco" => [
                        "nome_logradouro" => mb_substr($fws_['endereco'], 0, 45),
                        "nome_bairro"     => mb_substr($fws_['bairro'], 0, 15),
                        "nome_cidade"     => mb_substr($fws_['cidade'], 0, 20),
                        "sigla_UF"        => $fws_['estado'],
                        "numero_CEP"      => preg_replace('/\D/', '', $fws_['cep']),
                    ],
                ],

                "dados_individuais_boleto" => [
                    [
                        "numero_nosso_numero"    => substr(str_pad($fws_['numeroDocumento'], 8, '0', STR_PAD_LEFT), -8),
                        "data_vencimento"        => $this->converterDataParaISO($fws_['dataVencimento']),
                        "texto_uso_beneficiario" => $fws_['numeroDocumento'],
                        "valor_titulo"           => $valor17,
                        "data_limite_pagamento"  => $this->converterDataParaISO($fws_['dataVencimento']),
                    ],
                ],

                "lista_mensagem_cobranca" => [
                    [
                        "mensagem" => mb_substr($fws_['instrucoes1'] ?? 'Pagamento de inscricao', 0, 78),
                    ],
                ],
            ],

            "dados_qrcode" => [
                "chave" => $objEvConf->getItauBolecodeChavePix(),
            ],
        ];
    }

    /**
     * Envia o BoleCode para a API do Itaú
     */
    public function enviarBolecode($payload, $accessToken, $clientId) {
        $correlationId = isset($payload['dado_boleto']['texto_seu_numero'])
            ? $payload['dado_boleto']['texto_seu_numero']
            : uniqid();

        list($info, $response) = $this->curlRequest(
            'POST',
            self::BOLECODE_URL,
            [
                "Authorization: Bearer {$accessToken}",
                "x-itau-apikey: {$clientId}",
                "x-itau-correlationID: {$correlationId}",
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
            throw new Exception('Resposta inválida do Itaú BoleCode: ' . $response);
        }

        return $data;
    }

    /**
     * Registra boleto no Itaú e retorna os dados do retorno
     */
    public function registrarBoleto($fws_, $objEvConf, $debug = false, $etapa = 'efetivacao') {
        $clientId     = $objEvConf->getItauBolecodeClientId();
        $clientSecret = $objEvConf->getItauBolecodeClientSecret();

        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception('Credenciais Itaú BoleCode não configuradas para este evento.');
        }

        // 1. Obter token
        $token = $this->obterAccessToken($clientId, $clientSecret);

        // 2. Montar payload
        $payload = $this->montarPayload($fws_, $objEvConf, $etapa);

        // 3. Enviar para API
        $retorno = $this->enviarBolecode($payload, $token, $clientId);

        return $retorno;
    }

    /**
     * Renderiza o botão/tela de pagamento híbrida (QR Code PIX + Boleto PDF)
     */
    public function btnBoleto($fws_, $debug = false) {
        // Este método é chamado pelo GerenciarBoletoAC quando o gateway é ITAU-BOLECODE
        // Porém, o fluxo principal é via itauBolecodeForm.php (include direto na view)
        // Aqui renderiza um botão simples que redireciona para o form
        ?>
        <form action="<?php echo WB_URL_COMMAND?>" method="post" name="pagamento" target="_blank">
            <input type="hidden" name="c" value="GerenciarBoleto">
            <input type="hidden" name="m" value="htmlBoleto">
            <input type="hidden" name="nomeBanco" value="ITAU-BOLECODE">
            <input type="hidden" name="cod_evento" value="<?php echo COD_EVENTO?>">
            <input type="hidden" name="cod_inscrito" value="<?php echo $fws_['cod_inscrito']?>">
            <input type="hidden" name="cod_boleto" value="<?php echo $fws_['cod_boleto']?>">
            <input type="hidden" name="valor_cobrado" value="<?php echo $fws_['valor_cobrado']?>">
            <button class="btn btn-xs btn-primary" type="submit">Pagar</button>
        </form>
        <?php
    }

    /**
     * Verifica se o boleto/PIX foi pago consultando a API do Itau
     * Retorna JSON com status
     */
    public function verificarPagamento($fws_, $debug = false) {
        header('Content-Type: application/json; charset=utf-8');
        try {
            // Carregar boleto
            $objBoleto = new Boleto();
            $objBoleto->setCodEvento(COD_EVENTO);
            $objBoleto->setCodBoleto($fws_['cod_boleto']);
            $objBoleto->getBoletoById();

            $retornoApi = json_decode($objBoleto->getRetornoApiItau(), true);
            if (empty($retornoApi)) {
                throw new Exception('Boleto sem retorno da API.');
            }

            $apiData = $retornoApi['data'] ?? $retornoApi;
            $txid = $apiData['dados_qrcode']['txid'] ?? '';
            if (empty($txid)) {
                throw new Exception('txid nao encontrado.');
            }

            // Config do evento
            $objEv = new Evento();
            $objEv->setCodEvento(COD_EVENTO);
            $objEv->getEventoById();
            $objEvConf = $objEv->getEventoConfiguracao();
            $clientId = $objEvConf->getItauBolecodeClientId();
            $clientSecret = $objEvConf->getItauBolecodeClientSecret();

            // Obter token
            $token = $this->obterAccessToken($clientId, $clientSecret);

            // Consultar /cash_management/v2/boletos/{id}
            $idBoleto = $apiData['dado_boleto']['dados_individuais_boleto'][0]['id_boleto_individual']
                ?? $txid;
            list($info, $response) = $this->curlRequest(
                'GET',
                'https://secure.api.cloud.itau.com.br/cash_management/v2/boletos/' . $idBoleto,
                [
                    "Authorization: Bearer {$token}",
                    "x-itau-apikey: " . $clientId,
                    "x-itau-correlationID: " . uniqid(),
                    "x-itau-flowID: 1",
                ],
                null,
                false
            );

            $cobData = json_decode($response, true);
            // Campo situacao: REGISTRADO, LIQUIDADO, PIX_LIQUIDADO, BAIXADO, etc.
            $situacao = $cobData['situacao'] ?? $cobData['status'] ?? 'DESCONHECIDO';
            $pago = in_array($situacao, ['LIQUIDADO', 'PIX_LIQUIDADO']);

            $resultado = [
                'statusMessage' => true,
                'status_pagamento' => $situacao,
                'pago' => $pago,
                'http_code' => $info['http_code'],
            ];

            // Se pago, gravar em tab_pagamentos
            if ($pago) {
                $pedidoId = $objBoleto->getPedidoId();

                // Verificar se ja existe pagamento para este pedido
                $objPagExistente = new Pagamento();
                $objPagExistente->setCodEvento(COD_EVENTO);
                $objPagExistente->setPedidoId($pedidoId);
                $jaTemPagamento = false;
                try {
                    $objPagExistente->getPagamentoByPedidoId();
                    if ($objPagExistente->getCodPagamento() != '') {
                        $jaTemPagamento = true;
                    }
                } catch (Exception $e) {
                    $jaTemPagamento = false;
                }

                $valorPago = $cobData['valor_pago'] ?? $cobData['valor_titulo'] ?? $objBoleto->getValorBoleto();
                $dataPagamento = $cobData['data_pagamento'] ?? $cobData['data_credito'] ?? date('Y-m-d');
                $formaPgto = ($situacao === 'PIX_LIQUIDADO') ? 'PIX-BOLECODE' : 'BOLETO-BOLECODE';

                $resultado['valor_pago'] = $valorPago;
                $resultado['data_pagamento'] = $dataPagamento;

                if (!$jaTemPagamento) {
                    $objPagamento = new Pagamento();
                    $objPagamento->setCodEvento(COD_EVENTO);
                    $objPagamento->setPedidoId($pedidoId);
                    $objPagamento->setCodInscrito($objBoleto->getCodInscrito());
                    $objPagamento->setValor($valorPago);
                    $objPagamento->setDataPagamento($dataPagamento);
                    $objPagamento->setFormaPagamento($formaPgto);
                    $objPagamento->setTransacaoId($txid);
                    $objPagamento->setStatus('ATIVADA');
                    try {
                        $objPagamento->salvarCargaPagamento();
                    } catch (OKException $ok) {
                        // salvo
                    }
                    $resultado['pagamento_registrado'] = true;
                } else {
                    $resultado['pagamento_registrado'] = false;
                    $resultado['mensagem'] = 'Pagamento ja registrado anteriormente.';
                }

                $resultado['valor_pago'] = $pixInfo['valor'];
                $resultado['data_pagamento'] = $pixInfo['horario'];
                $resultado['endToEndId'] = $pixInfo['endToEndId'] ?? '';
            }

            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

        } catch (OKException $ok) {
            echo json_encode(['statusMessage' => true, 'mensagem' => $ok->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            executarLog("BoleCode verificarPagamento - " . $e->getMessage(), "erro-bolecode", true);
            echo json_encode(['statusMessage' => false, 'mensagem' => 'Erro ao verificar pagamento. Tente novamente mais tarde.'], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    /**
     * AJAX: Registra boleto (se necessario) e retorna dados do QR Code
     * Chamado quando o modal abre
     */
    public function registrarOuCarregar($fws_, $debug = false) {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $objEv = new Evento();
            $objEv->setCodEvento(COD_EVENTO);
            $objEv->getEventoById();
            $objEvConf = $objEv->getEventoConfiguracao();

            $pedidoId = $fws_['pedido_id'];
            $codInscrito = $fws_['cod_inscrito'];

            // Verificar se ja existe boleto para este pedido
            $objBoleto = new Boleto();
            $objBoleto->setCodEvento(COD_EVENTO);
            $objBoleto->setPedidoId($pedidoId);
            $boletoExiste = false;
            try {
                if ($objBoleto->getBoletoByPedidoId($debug)) {
                    $boletoExiste = true;
                }
            } catch (Exception $e) {}

            // Se nao existe, criar
            if (!$boletoExiste) {
                $objBoleto = new Boleto();
                $objBoleto->setCodEvento(COD_EVENTO);
                $objBoleto->setCodInscrito($codInscrito);
                // Número documento: pedido(8) + sequencial(2) — ex: 0001636701
                $sequencia = $objBoleto->getNextSequenciaBoleto();
                $numeroDocumento = str_pad($pedidoId, 8, '0', STR_PAD_LEFT) . str_pad($sequencia, 2, '0', STR_PAD_LEFT);
                $objBoleto->setNumeroDocumento($numeroDocumento);
                $objBoleto->setSacado($fws_['sacado']);
                $objBoleto->setCpfCnpj($fws_['cpfCnpj']);
                $objBoleto->setTipoDocumento(strlen($fws_['cpfCnpj']) <= 11 ? '1' : '2');
                $objBoleto->setEndereco1($fws_['endereco'] . ', ' . $fws_['bairro']);
                $objBoleto->setEndereco2($fws_['cidade'] . '-' . $fws_['estado'] . ' CEP: ' . $fws_['cep']);
                $objBoleto->setCep($fws_['cep']);
                $objBoleto->setCidade($fws_['cidade']);
                $objBoleto->setUf($fws_['estado']);
                $objBoleto->setBairro($fws_['bairro']);
                $objBoleto->setValorBoleto($fws_['valorBoleto']);
                $objBoleto->setValorDocumento($fws_['valorBoleto']);
                $objBoleto->setDataVencimento($fws_['dataVencimento']);
                $objBoleto->setDataDocumento(date('d/m/Y'));
                $objBoleto->setDataEmissao(date('d/m/Y'));
                $objBoleto->setDataProcessamento(date('d/m/Y'));
                $objBoleto->setInstrucoes1($fws_['instrucoes1']);
                $objBoleto->setDemonstrativo1($fws_['demonstrativo1']);
                $objBoleto->setPedidoId($pedidoId);
                try { $objBoleto->salvar(); } catch (OKException $ok) {}
            }

            // Se nao tem retorno da API, registrar
            $retornoApi = json_decode($objBoleto->getRetornoApiItau(), true);
            if (empty($retornoApi)) {
                $fws_['numeroDocumento'] = $objBoleto->getNumeroDocumento();
                $retornoApi = $this->registrarBoleto($fws_, $objEvConf, $debug, 'efetivacao');

                if (!empty($retornoApi)) {
                    $objBoleto->setRetornoApiItau(json_encode($retornoApi, JSON_UNESCAPED_UNICODE));
                    $objBoleto->setFlgRegistrado(1);
                    try { $objBoleto->salvar(); } catch (OKException $ok) {}
                }
            }

            // Extrair dados
            $apiData = $retornoApi['data'] ?? $retornoApi ?? [];
            $dadosInd = $apiData['dado_boleto']['dados_individuais_boleto'][0] ?? [];
            $dadosQr = $apiData['dados_qrcode'] ?? [];

            // Verificar se deve exibir PIX
            $exibirPix = $objEvConf->getItauBolecodeExibirPix() == 1;

            echo json_encode([
                'statusMessage' => true,
                'cod_boleto' => $objBoleto->getCodBoleto(),
                'linha_digitavel' => $dadosInd['numero_linha_digitavel'] ?? '',
                'qrcode_texto' => $exibirPix ? ($dadosQr['emv'] ?? '') : '',
                'qrcode_base64' => $exibirPix ? ($dadosQr['base64'] ?? '') : '',
                'exibir_pix' => $exibirPix,
            ], JSON_UNESCAPED_UNICODE);

        } catch (OKException $ok) {
            echo json_encode(['statusMessage' => true, 'mensagem' => $ok->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            $msgErro = $e->getMessage();

            // Tratar erros da API com mensagem amigavel para o inscrito
            $contatoRodape = 'Entre em contato pelo WhatsApp dispon&iacute;vel no rodap&eacute; da p&aacute;gina.';

            if (stripos($msgErro, 'calendario.expiracao') !== false || stripos($msgErro, 'data hora de expiracao') !== false) {
                $msgAmigavel = 'O boleto est&aacute; vencido e n&atilde;o pode mais ser utilizado.<br><br>'
                    . 'Para solicitar a emiss&atilde;o de um novo boleto, ' . $contatoRodape;
            } else {
                $msgAmigavel = 'N&atilde;o foi poss&iacute;vel gerar o boleto no momento. Tente novamente mais tarde.<br><br>'
                    . 'Se o problema persistir, ' . $contatoRodape;
            }

            // Log + email de aviso (mesmo destino dos erros do DAO)
            $detalhes = "Inscrito: " . ($fws_['cod_inscrito'] ?? '') . " | Pedido: " . ($fws_['pedido_id'] ?? '') . " | Erro: " . $msgErro;
            executarLog("BoleCode registrarOuCarregar - " . $detalhes, "erro-bolecode", true);
            if ($_SERVER['HTTP_HOST'] != 'eventos.test') {
                $subject = "[" . ABREV_EVENTO . "] Erro BoleCode - Pedido " . ($fws_['pedido_id'] ?? '');
                new enviarErro("eventos@opctec.com.br", $subject, $detalhes);
            }

            echo json_encode(['statusMessage' => false, 'mensagem' => $msgAmigavel], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    /**
     * Gera HTML do boleto no layout padrao bancario (biblioteca BoletoPhp)
     */
    public function pdfBoleto($fws_, $debug = false) {
        try {
            $objBoleto = new Boleto();
            $objBoleto->setCodEvento(COD_EVENTO);

            if (!empty($fws_['cod_boleto'])) {
                $objBoleto->setCodBoleto($fws_['cod_boleto']);
                $objBoleto->getBoletoById();
            } else if (!empty($fws_['cod_inscrito'])) {
                $objBoleto->setCodInscrito($fws_['cod_inscrito']);
                $objBoleto->carregarUltimoBoletoByIdInscrito();
            }

            $retornoApi = json_decode($objBoleto->getRetornoApiItau(), true);
            $apiData = $retornoApi['data'] ?? $retornoApi;
            $dadosBoleto = $apiData['dado_boleto'] ?? [];
            $dadosIndividuais = $dadosBoleto['dados_individuais_boleto'][0] ?? [];

            // Extrair dados da config do evento para agencia/conta
            $objEv = new Evento();
            $objEv->setCodEvento(COD_EVENTO);
            $objEv->getEventoById();
            $objEvConf = $objEv->getEventoConfiguracao();
            $idBeneficiario = $objEvConf->getItauBolecodeIdBeneficiario();
            // id_beneficiario = agencia(4) + 00 + conta(5) + dv(1)
            $agencia = substr($idBeneficiario, 0, 4);
            $conta = substr($idBeneficiario, 6, 5);
            $contaDv = substr($idBeneficiario, 11, 1);

            // Montar array $dadosboleto no formato da biblioteca BoletoPhp
            $dadosboleto = [];
            $dadosboleto["nosso_numero"] = $dadosIndividuais['numero_nosso_numero'] ?? $objBoleto->getNumeroDocumento();
            $dadosboleto["numero_documento"] = $objBoleto->getNumeroDocumento();
            $dadosboleto["data_vencimento"] = $objBoleto->getDataVencimento();
            $dadosboleto["data_documento"] = $objBoleto->getDataDocumento();
            $dadosboleto["data_processamento"] = $objBoleto->getDataProcessamento();
            $dadosboleto["valor_boleto"] = number_format((float)$objBoleto->getValorBoleto(), 2, ',', '');
            $dadosboleto["valor_documento"] = $dadosboleto["valor_boleto"];

            $dadosboleto["sacado"] = $objBoleto->getSacado() . " / " . formatCpfCnpjView($objBoleto->getCpfCnpj());
            $dadosboleto["endereco1"] = $objBoleto->getEndereco1();
            $dadosboleto["endereco2"] = $objBoleto->getEndereco2();

            $dadosboleto["demonstrativo1"] = $objBoleto->getDemonstrativo1();
            $dadosboleto["demonstrativo2"] = $objBoleto->getDemonstrativo2();
            $dadosboleto["demonstrativo3"] = $objBoleto->getDemonstrativo3();
            $dadosboleto["instrucoes1"] = $objBoleto->getInstrucoes1();
            $dadosboleto["instrucoes2"] = $objBoleto->getInstrucoes2();
            $dadosboleto["instrucoes3"] = $objBoleto->getInstrucoes3();
            $dadosboleto["instrucoes4"] = "";

            $dadosboleto["quantidade"] = "";
            $dadosboleto["valor_unitario"] = "";
            $dadosboleto["aceite"] = "";
            $dadosboleto["especie"] = "R$";
            $dadosboleto["especie_doc"] = "";

            $dadosboleto["agencia"] = $agencia;
            $dadosboleto["conta"] = $conta;
            $dadosboleto["conta_dv"] = $contaDv;
            $dadosboleto["carteira"] = $objEvConf->getItauBolecodeCodigoCarteira();

            $beneficiario = $apiData['beneficiario'] ?? [];
            $dadosboleto["identificacao"] = ABREV_EVENTO;
            $dadosboleto["cpf_cnpj"] = $beneficiario['tipo_pessoa']['numero_cadastro_nacional_pessoa_juridica'] ?? '';
            $dadosboleto["endereco"] = ($beneficiario['endereco']['nome_logradouro'] ?? '') . ' ' . ($beneficiario['endereco']['nome_bairro'] ?? '');
            $dadosboleto["cidade_uf"] = ($beneficiario['endereco']['nome_cidade'] ?? '') . ' / ' . ($beneficiario['endereco']['sigla_UF'] ?? '');
            $dadosboleto["cedente"] = $beneficiario['nome_cobranca'] ?? '';

            header('Content-Type: text/html; charset=UTF-8');
            echo '<meta charset="UTF-8">';

            // Definir WB_URL_IMG_BOLETO para o layout (mesmo padrao do Sisprime)
            if (!defined('WB_URL_IMG_BOLETO')) {
                $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                define('WB_URL_IMG_BOLETO', $protocolo . '://' . $_SERVER['HTTP_HOST'] . '/' . WB_GRUPO . '/' . WB_PROJETO . '/inscricoes/lib/boleto/imagens');
            }

            include("lib/boleto/include/funcoes_itau.php");

            // Sobrescrever com valores da API (mais confiaveis que o recalculo da biblioteca)
            $linhaApi = $dadosIndividuais['numero_linha_digitavel'] ?? '';
            $barrasApi = $dadosIndividuais['codigo_barras'] ?? '';
            if ($linhaApi) {
                $dadosboleto["linha_digitavel"] = monta_linha_digitavel($barrasApi);
                // Forcar a linha digitavel da API
                $dadosboleto["linha_digitavel"] =
                    substr($linhaApi,0,5) . '.' . substr($linhaApi,5,5) . ' ' .
                    substr($linhaApi,10,5) . '.' . substr($linhaApi,15,6) . ' ' .
                    substr($linhaApi,21,5) . '.' . substr($linhaApi,26,6) . ' ' .
                    substr($linhaApi,32,1) . ' ' .
                    substr($linhaApi,33);
            }
            if ($barrasApi) {
                $dadosboleto["codigo_barras"] = $barrasApi;
            }

            // Logo do gestor no boleto
            $dadosboleto['logo_empresa'] = WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/inscricoes/resources/image/logo-60X235pxN-' . strtoupper(WB_GRUPO) . '.png';

            ob_start();
            include("lib/boleto/include/layout_itau.php");
            $htmlBoleto = ob_get_clean();
            // Corrigir paths das imagens
            $htmlBoleto = str_replace('src=imagens/', 'src=' . WB_URL_IMG_BOLETO . '/', $htmlBoleto);
            $htmlBoleto = str_replace('src="imagens/', 'src="' . WB_URL_IMG_BOLETO . '/', $htmlBoleto);

            echo $htmlBoleto;
            echo '<div style="height:200px;"></div>';

        } catch (OKException $ok) {
            // ignorar
        } catch (Exception $e) {
            echo '<div style="padding:20px;"><h3 style="color:red;">Erro ao gerar boleto</h3><p>' . htmlspecialchars($e->getMessage()) . '</p></div>';
        }
    }

    /**
     * Renderiza a tela de pagamento com QR Code e dados do boleto
     */
    public function htmlBoleto($fws_, $debug = false) {
        try {
            // Carrega boleto do banco
            $objBoleto = new Boleto();
            $objBoleto->setCodEvento(COD_EVENTO);

            if (!empty($fws_['cod_boleto'])) {
                $objBoleto->setCodBoleto($fws_['cod_boleto']);
                $objBoleto->getBoletoById();
            } else if (!empty($fws_['cod_inscrito'])) {
                $objBoleto->setCodInscrito($fws_['cod_inscrito']);
                $objBoleto->carregarUltimoBoletoByIdInscrito();
            } else {
                throw new Exception('cod_boleto ou cod_inscrito nao informado.');
            }

            if (!$objBoleto->getCodBoleto()) {
                throw new Exception('Boleto nao encontrado no banco. cod_boleto=' . $fws_['cod_boleto'] . ' cod_inscrito=' . $fws_['cod_inscrito']);
            }

            // Recupera dados do retorno da API
            $retornoApiJson = $objBoleto->getRetornoApiItau();
            $retornoApi = json_decode($retornoApiJson, true);

            if (empty($retornoApi)) {
                throw new Exception('Retorno da API Itau nao encontrado para este boleto (cod_boleto=' . $objBoleto->getCodBoleto() . '). JSON salvo: ' . mb_substr($retornoApiJson, 0, 200));
            }

            $this->renderizarTelaPagamento($objBoleto, $retornoApi, $debug);

        } catch (OKException $ok) {
            // ignorar OKException do getById
        } catch (Exception $e) {
            echo '<div style="padding:20px; font-family:Arial;"><h3 style="color:red;">Erro ao carregar boleto</h3><p>' . htmlspecialchars($e->getMessage()) . '</p></div>';
        }
    }

    /**
     * Renderiza a tela híbrida de pagamento
     */
    public function renderizarTelaPagamento($objBoleto, $retornoApi, $debug = false) {
        $valor = $objBoleto->getValorBoleto();
        $vencimento = $objBoleto->getDataVencimento(); // ja vem em dd/mm/yyyy
        $sacado = $objBoleto->getSacado();

        // Extrair dados do retorno da API
        $qrCodeBase64 = '';
        $qrCodeTexto = '';
        $linhaDigitavel = '';
        $codigoBarras = '';

        if (is_array($retornoApi)) {
            // Retorno da API vem dentro de 'data'
            $apiData = $retornoApi['data'] ?? $retornoApi;
            $dadosBoleto = $apiData['dado_boleto'] ?? [];
            $dadosIndividuais = $dadosBoleto['dados_individuais_boleto'][0] ?? [];
            $dadosQrcode = $apiData['dados_qrcode'] ?? [];

            $linhaDigitavel = $dadosIndividuais['numero_linha_digitavel'] ?? '';
            $codigoBarras = $dadosIndividuais['codigo_barras'] ?? '';
            $qrCodeTexto = $dadosQrcode['emv'] ?? '';
            $qrCodeBase64 = $dadosQrcode['base64'] ?? '';
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Pagamento - BoleCode Itaú</title>
            <link rel="stylesheet" href="<?php echo defined('WB_URL_CSS') ? WB_URL_CSS . 'bootstrap.min.css' : 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' ?>">
            <style>
                body { background: #f5f5f5; font-family: Arial, sans-serif; }
                .pagamento-container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
                .pagamento-header { background: #003399; color: #fff; padding: 20px; text-align: center; }
                .pagamento-header h3 { margin: 0; font-size: 20px; }
                .pagamento-body { padding: 25px; }
                .info-pagamento { background: #f8f9fa; border-radius: 6px; padding: 15px; margin-bottom: 20px; }
                .info-pagamento .label-info { color: #666; font-size: 13px; }
                .info-pagamento .valor-info { font-size: 16px; font-weight: bold; color: #333; }
                .qrcode-area { text-align: center; padding: 20px; border: 2px dashed #003399; border-radius: 8px; margin-bottom: 20px; }
                .qrcode-area img { max-width: 250px; }
                .pix-copia-cola { background: #e8f0fe; padding: 12px; border-radius: 6px; margin-bottom: 20px; word-break: break-all; font-size: 12px; cursor: pointer; position: relative; }
                .pix-copia-cola:hover { background: #d0e0fd; }
                .pix-copia-cola .copiar-label { color: #003399; font-weight: bold; font-size: 13px; display: block; margin-bottom: 5px; }
                .linha-digitavel { background: #fff3cd; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-family: monospace; font-size: 14px; letter-spacing: 1px; cursor: pointer; }
                .linha-digitavel:hover { background: #ffeaa0; }
                .separador { text-align: center; color: #999; margin: 20px 0; position: relative; }
                .separador::before, .separador::after { content: ''; position: absolute; top: 50%; width: 40%; height: 1px; background: #ddd; }
                .separador::before { left: 0; }
                .separador::after { right: 0; }
                .btn-pagar { width: 100%; padding: 12px; font-size: 16px; border-radius: 6px; margin-bottom: 10px; }
                .btn-pix { background: #00b894; border-color: #00b894; color: #fff; }
                .btn-boleto-pdf { background: #003399; border-color: #003399; color: #fff; }
                .copiado-msg { display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: #28a745; color: #fff; padding: 10px 25px; border-radius: 6px; z-index: 9999; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="copiado-msg" id="msgCopiado">Copiado!</div>

            <div class="pagamento-container">
                <div class="pagamento-header">
                    <h3>Pagamento via PIX ou Boleto</h3>
                </div>
                <div class="pagamento-body">
                    <!-- Informações do pagamento -->
                    <div class="info-pagamento">
                        <div class="row">
                            <div class="col-xs-6 col-sm-6">
                                <span class="label-info">Valor:</span><br>
                                <span class="valor-info">R$ <?php echo number_format((float)$valor, 2, ',', '.') ?></span>
                            </div>
                            <div class="col-xs-6 col-sm-6">
                                <span class="label-info">Vencimento:</span><br>
                                <span class="valor-info"><?php echo $vencimento ?></span>
                            </div>
                        </div>
                        <div style="margin-top: 8px;">
                            <span class="label-info">Pagador:</span><br>
                            <span style="font-size: 14px;"><?php echo htmlspecialchars($sacado) ?></span>
                        </div>
                    </div>

                    <?php if ($qrCodeBase64 || $qrCodeTexto) { ?>
                    <!-- QR Code PIX -->
                    <div class="qrcode-area">
                        <p style="color: #003399; font-weight: bold; margin-bottom: 10px;">Pague instantaneamente via PIX</p>
                        <?php if ($qrCodeBase64) { ?>
                            <img src="data:image/png;base64,<?php echo $qrCodeBase64 ?>" alt="QR Code PIX">
                        <?php } elseif ($qrCodeTexto) { ?>
                            <!-- Se não vier imagem, gerar QR via JS -->
                            <div id="qrcode-pix"></div>
                        <?php } ?>
                    </div>

                    <?php if ($qrCodeTexto) { ?>
                    <!-- PIX Copia e Cola -->
                    <div class="pix-copia-cola" onclick="copiarTexto('<?php echo htmlspecialchars($qrCodeTexto, ENT_QUOTES) ?>')">
                        <span class="copiar-label">PIX Copia e Cola (clique para copiar):</span>
                        <?php echo htmlspecialchars($qrCodeTexto) ?>
                    </div>
                    <?php } ?>
                    <?php } ?>

                    <?php if ($linhaDigitavel) { ?>
                    <!-- Linha digitável -->
                    <div class="linha-digitavel" onclick="copiarTexto('<?php echo htmlspecialchars($linhaDigitavel, ENT_QUOTES) ?>')" title="Clique para copiar">
                        <small style="color: #666; display: block; margin-bottom: 5px;">Linha digitável (clique para copiar):</small>
                        <?php echo htmlspecialchars($linhaDigitavel) ?>
                    </div>
                    <?php } ?>

                    <div class="separador">ou</div>

                    <!-- Botão gerar PDF do boleto -->
                    <form action="<?php echo WB_URL_COMMAND ?>" method="post" target="_blank">
                        <input type="hidden" name="c" value="GerenciarBoleto">
                        <input type="hidden" name="m" value="pdfBoleto">
                        <input type="hidden" name="nomeBanco" value="ITAU-BOLECODE">
                        <input type="hidden" name="cod_evento" value="<?php echo COD_EVENTO ?>">
                        <input type="hidden" name="cod_inscrito" value="<?php echo $objBoleto->getCodInscrito() ?>">
                        <input type="hidden" name="cod_boleto" value="<?php echo $objBoleto->getCodBoleto() ?>">
                        <button type="submit" class="btn btn-boleto-pdf btn-pagar">
                            Gerar Boleto em PDF
                        </button>
                    </form>
                </div>
            </div>

            <script>
            function copiarTexto(texto) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(texto);
                } else {
                    var el = document.createElement('textarea');
                    el.value = texto;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                }
                var msg = document.getElementById('msgCopiado');
                msg.style.display = 'block';
                setTimeout(function() { msg.style.display = 'none'; }, 2000);
            }
            </script>
        </body>
        </html>
        <?php
    }

    /**
     * Converte data DD/MM/YYYY para YYYY-MM-DD
     */
    private function converterDataParaISO($data) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return $data; // Já está em ISO
        }
        $partes = explode('/', $data);
        if (count($partes) == 3) {
            return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
        }
        return $data;
    }
}
