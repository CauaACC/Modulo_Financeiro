<?php
// Certifique-se de ter o arquivo JWT.php incluído no mesmo diretório ou ajustado o path.
// composer require firebase/php-jwt
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

class GerenciarBoletoOnlineAC
{

    private $chaveGeral;
    private $chaveConta;
    private $endpoint;
    private $boletoRepository;

    public function __construct($chaveGeral, $chaveConta, $endpoint)
    {
        $this->chaveGeral = $chaveGeral;
        $this->chaveConta = $chaveConta;
        $this->endpoint   = $endpoint;
        // Pseudo chamada para instanciar o repositório responsável pela persistência dos boletos
        $this->boletoRepository = new BoletoRepository();
    }

    // Gera o token JWT usando Firebase JWT
    private function gerarToken()
    {
        $payload = [
            'iat'  => time(),
            'exp'  => time() + 600,
            'iss'  => 'cobexpress',
            'hash' => $this->chaveConta
        ];
        return JWT::encode($payload, $this->chaveGeral, 'HS512');
    }

    // Registra um único boleto online enviando os dados para o webservice
    public function registrarBoleto(array $titulo)
    {
        $token = $this->gerarToken();
        $dados = [
            'token'  => $token,
            'titulo' => $titulo
        ];
        $json = json_encode($dados);

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception("Erro no cURL: " . curl_error($ch));
        }
        curl_close($ch);

        $retorno = json_decode($response, true);
        // Processa o retorno e atualiza o boleto no banco, se registrado com sucesso
        $this->processarRetorno($retorno, $titulo);
        return $retorno;
    }

    // Processa o retorno do webservice e simula a gravação dos dados no banco de dados
    private function processarRetorno(array $retorno, array $titulo)
    {
        if (isset($retorno['status_retorno']) && $retorno['status_retorno'] == '0') {
            echo "Boleto registrado com sucesso.\n";
            if (isset($retorno['dados_titulo'])) {
                $dadosTitulo = $retorno['dados_titulo'];
                // Atualiza os dados do boleto conforme o retorno
                $titulo['nosso_numero'] = $dadosTitulo['nosso_numero'] ?? null;
                // Se houver QR Code, provavelmente para boletos PIX
                $titulo['qr_code'] = $dadosTitulo['qr_code'] ?? null;
                // Outros dados, como linha digitável, podem ser processados aqui...
            }
            // Chama método do repositório para salvar/atualizar o boleto
            $this->boletoRepository->salvar($titulo);
        } else {
            $descricao = $retorno['descricao'] ?? 'Erro desconhecido';
            echo "Falha no registro do boleto: " . $descricao . "\n";
            // Opcional: atualiza o boleto com o erro ocorrido
            $titulo['erro_registro'] = $descricao;
            $this->boletoRepository->salvar($titulo);
        }
    }

    // Pseudo método para listar boletos pendentes para registro (por exemplo, do banco de dados)
    public function listarBoletosPendentes()
    {
        return $this->boletoRepository->listarPendentes();
    }

    // Processa em lote os boletos pendentes para registro
    public function processarRegistrosPendentes()
    {
        $boletosPendentes = $this->listarBoletosPendentes();
        foreach ($boletosPendentes as $boleto) {
            try {
                echo "Registrando boleto: " . $boleto['numero_documento'] . "\n";
                $this->registrarBoleto($boleto);
            } catch (Exception $e) {
                echo "Erro ao registrar boleto " . $boleto['numero_documento'] . ": " . $e->getMessage() . "\n";
                $boleto['erro_registro'] = $e->getMessage();
                $this->boletoRepository->salvar($boleto);
            }
        }
    }
}

// Pseudo classe para persistência dos boletos
class BoletoRepository
{
    // Pseudo método para salvar ou atualizar um boleto no banco de dados
    public function salvar(array $boleto)
    {
        // Aqui você implementaria a lógica real de persistência (PDO, ORM, etc.)
        echo "Salvando boleto no banco: " . $boleto['numero_documento'] . "\n";
    }

    // Pseudo método para listar boletos pendentes (não registrados)
    public function listarPendentes()
    {
        // Exemplo fictício de retorno: array de boletos pendentes
        return [
            [
                'ocorrencia_remessa' => '1',
                'nosso_numero'       => '11111111111',
                'numero_documento'   => 'DOC1111',
                'data_emissao'       => '2024-08-01',
                'data_vencimento'    => '2024-08-30',
                'valor_documento'    => '200.00',
                'especie_documento'  => '02',
                'tipo_protesto_negativacao' => '0',
                'percentual_juros'   => '1.00',
                'percentual_multa'   => '1.00',
                'codigo_pagador'     => '1',
                'tipo_inscricao_pagador' => '1',
                'inscricao_pagador'  => '00000000191',
                'nome_pagador'       => 'Cliente Exemplo',
                'logradouro_pagador' => 'Rua Exemplo',
                'numero_logradouro_pagador' => '123',
                'complemento_logradouro_pagador' => '',
                'cep_pagador'        => '00000000',
                'bairro_pagador'     => 'Bairro Exemplo',
                'municipio_pagador'  => 'Cidade Exemplo',
                'uf_pagador'         => 'SP',
                'endereco_eletronico_pagador' => 'cliente@exemplo.com',
                'linhas_mensagem'    => ['Mensagem de teste']
            ],
            // Outros boletos pendentes podem ser listados aqui
        ];
    }
}

// Exemplo de uso:
try {
    $chaveGeral = 'f096f110d687f999537b70c8d4d11ec8';
    $chaveConta = 'a523f5a7a3ba04bab7715bf8bb51402e';
    $endpoint   = 'https://homologa-ws.cobexpress.com.br/webservice/enviar-boleto';

    $registroController = new GerenciarBoletoOnlineAC($chaveGeral, $chaveConta, $endpoint);

    // Para processar em lote os boletos pendentes (pode ser acionado via tarefa em segundo plano)
    $registroController->processarRegistrosPendentes();

    // Ou, para registrar um único boleto:
    /*
        $titulo = [
            'ocorrencia_remessa' => '1',
            'nosso_numero'       => '12345678901',
            'numero_documento'   => '1234567890',
            'data_emissao'       => '2024-08-01',
            'data_vencimento'    => '2024-08-30',
            'valor_documento'    => '100.00',
            'especie_documento'  => '02',
            'tipo_protesto_negativacao' => '0',
            'percentual_juros'   => '1.00',
            'percentual_multa'   => '1.00',
            'codigo_pagador'     => '1',
            'tipo_inscricao_pagador' => '1',
            'inscricao_pagador'  => '00000000191',
            'nome_pagador'       => 'NOME DO PAGADOR',
            'logradouro_pagador' => 'ENDERECO DO PAGADOR',
            'numero_logradouro_pagador' => '1234567890',
            'complemento_logradouro_pagador' => 'COMPLEMENTO',
            'cep_pagador'        => '00001000',
            'bairro_pagador'     => 'BAIRRO',
            'municipio_pagador'  => 'MUNICIPIO',
            'uf_pagador'         => 'UF',
            'endereco_eletronico_pagador' => 'email@dominio.com.br',
            'linhas_mensagem'    => [
                'REFERENTE NF 1234/2024',
                'AGRADECEMOS PELA PREFERÊNCIA'
            ]
        ];
        $registroController->registrarBoleto($titulo);
        */
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
