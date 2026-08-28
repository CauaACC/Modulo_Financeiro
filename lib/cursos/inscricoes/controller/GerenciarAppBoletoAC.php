<?php

class GerenciarAppBoletoAC
{
    public function __construct()
    {
    }

    public function sincronizarConfigEvento($fws_, $debug = false)
    {
        header('Content-Type: application/json; charset=utf-8');

        // Token de autenticação — definir APPBOLETO_API_TOKEN no config.php
        if (!defined('APPBOLETO_API_TOKEN') || APPBOLETO_API_TOKEN === '') {
            echo json_encode(['statusMessage' => false, 'message' => 'Token da API não configurado. Defina a constante APPBOLETO_API_TOKEN no config.php.']);
            return;
        }

        $cod_evento  = trim($fws_['cod_evento'] ?? '');
        $evento_type = trim($fws_['evento_type'] ?? '');
        $escape_url  = trim($fws_['escape_url'] ?? '');

        if ($cod_evento === '' || $evento_type === '') {
            echo json_encode(['statusMessage' => false, 'message' => 'Campos cod_evento e evento_type são obrigatórios.']);
            return;
        }

        $tipos_validos = ['AREMAC', 'PSTC', 'HUTEC', 'COMUEL', 'INESCO'];
        if (!in_array($evento_type, $tipos_validos)) {
            echo json_encode(['statusMessage' => false, 'message' => 'evento_type inválido. Valores aceitos: ' . implode(', ', $tipos_validos)]);
            return;
        }

        // URL da API do AppBoleto
        if ($_SERVER['HTTP_HOST'] === 'eventos.test') {
            $apiUrl = 'http://appboleto.test/api/config-evento';
        } else {
            $apiUrl = 'https://pg.opctec.net/api/config-evento';
        }

        $payload = json_encode([
            'cod_evento'  => (int) $cod_evento,
            'evento_type' => $evento_type,
            'escape_url'  => $escape_url !== '' ? $escape_url : null,
        ]);

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . APPBOLETO_API_TOKEN,
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo json_encode(['statusMessage' => false, 'message' => 'Erro de conexão com o gateway: ' . $curlError]);
            return;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            echo json_encode([
                'statusMessage' => true,
                'message' => 'Configuração sincronizada com sucesso!',
                'data' => $data,
            ]);
        } else if ($httpCode === 401) {
            echo json_encode(['statusMessage' => false, 'message' => 'Token de autenticação inválido (401). Verifique o APPBOLETO_API_TOKEN.']);
        } else {
            $data = json_decode($response, true);
            $msg = $data['message'] ?? 'Erro desconhecido';
            echo json_encode(['statusMessage' => false, 'message' => "Erro do gateway ($httpCode): $msg"]);
        }
    }
}
