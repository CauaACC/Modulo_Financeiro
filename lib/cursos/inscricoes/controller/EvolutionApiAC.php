<?php
declare(strict_types=1);

/**
 * EvolutionApi — PHP puro (cURL)
 * Requisitos: PHP 8+ com cURL habilitado.
 *
 * ENV (opcional):
 *   EVO_BASE_URL, EVO_APIKEY, EVO_INSTANCE, EVO_TIMEOUT, EVO_INSECURE, EVO_VERBOSE, EVO_LOG_FILE
 */
class EvolutionApiAC
{
    private string $baseUrl;
    private string $apikey;
    private string $instance;
    private int    $timeout;
    private bool   $insecure;
    private bool   $verbose;
    private ?string $logFile;

    public function __construct(array $cfg = [])
    {
        // $this->baseUrl  = self::fixBaseUrl($cfg['baseUrl']  ?? getenv('EVO_BASE_URL')  ?: 'https://inscritec-evolution.wz5mth.easypanel.host');
        // $this->apikey   = (string)($cfg['apikey']   ?? getenv('EVO_APIKEY')   ?: '429683C4C977415CAAFCCE10F7D57E11');
        // $this->instance = (string)($cfg['instance'] ?? getenv('EVO_INSTANCE') ?: 'whatsopctec');
        // $this->timeout  = (int)   ($cfg['timeout']  ?? getenv('EVO_TIMEOUT')  ?: 20);
        // $this->insecure = self::toBool($cfg['insecure'] ?? getenv('EVO_INSECURE') ?? false);
        // $this->verbose  = self::toBool($cfg['verbose']  ?? getenv('EVO_VERBOSE')  ?? false);
        // $this->logFile  = $cfg['logFile'] ?? (getenv('EVO_LOG_FILE') ?: null);
    }


    private function log(string $msg): void
    {
        if ($this->verbose) echo '['.date('Y-m-d H:i:s')."] $msg\n";
        if ($this->logFile) @file_put_contents($this->logFile, '['.date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
    }

    function evolutionSendText($numero, $mensagem) {
        $url = 'https://inscritec-evolution.wz5mth.easypanel.host/message/sendText/whatsopctec';
        $apiKey = '429683C4C977415CAAFCCE10F7D57E11';

        $payload = [
            'number' => preg_replace('/\D/', '', $numero),
            'text'   => $mensagem
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'apikey: ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        // dd($response);
        return $response;
    }

}
