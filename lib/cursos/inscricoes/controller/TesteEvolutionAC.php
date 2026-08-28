<?php

class TesteEvolutionAC {
    
    private $urlBase = "https://inscritec-evolution.wz5mth.easypanel.host";
    private $session = "whatsoptec";
    private $token   = "429683C4C977415CAAFCCE10F7D57E11"; // ✅ CORRIGIDO
    
    
    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    public function testarConectividade($debug = true) {
        echo "<h3>🔍 Teste de Conectividade Evolution API</h3>";
        
        // Teste 1: Resolução DNS
        $this->testarDNS();
        
        // Teste 2: Conectividade básica
        $this->testarConectividadeBasica();
        
        // Teste 3: Status da instância
        $this->testarStatusInstancia();
        
        // Teste 4: Teste de envio de mensagem
        return $this->testarEnvioMensagem($debug);
    }
    
    private function testarDNS() {
        echo "<br><b>1️⃣ Teste de resolução DNS:</b><br>";
        $hostname = 'inscritec-evolution.wz5mth.easypanel.host';
        $ip = gethostbyname($hostname);
        
        echo "Hostname: " . $hostname . "<br>";
        echo "IP resolvido: " . $ip . "<br>";
        
        if ($ip === $hostname) {
            echo "<span style='color: red;'>❌ Problema de resolução DNS</span><br>";
            return false;
        } else {
            echo "<span style='color: green;'>✅ DNS resolvido com sucesso</span><br>";
            return true;
        }
    }
    
    private function testarConectividadeBasica() {
        echo "<br><b>2️⃣ Teste de conectividade básica:</b><br>";
        
        $ch = curl_init($this->urlBase);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_NOBODY         => true,
            CURLOPT_FOLLOWLOCATION => true,
            // ✅ ADICIONE ESTES HEADERS PARA SIMULAR NAVEGADOR:
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_HTTPHEADER     => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: pt-BR,pt;q=0.9,en;q=0.8',
                'Accept-Encoding: gzip, deflate, br',
                'DNT: 1',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: none',
                'Cache-Control: max-age=0'
            ]
        ]);
        
        $result = curl_exec($ch);
        $http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);
        
        echo "URL: " . $this->urlBase . "<br>";
        echo "HTTP Code: " . $http . "<br>";
        echo "Tempo de conexão: " . round($info['connect_time'], 2) . "s<br>";
        echo "Tempo total: " . round($info['total_time'], 2) . "s<br>";
        
        if ($error) {
            echo "<span style='color: red;'>❌ Erro cURL: " . $error . "</span><br>";
            return false;
        } else if ($http >= 200 && $http < 400) {
            echo "<span style='color: green;'>✅ Servidor responde</span><br>";
            return true;
        } else {
            echo "<span style='color: orange;'>⚠️ Servidor responde mas com código " . $http . "</span><br>";
            return false;
        }
    }
    
    private function testarStatusInstancia() {
        echo "<br><b>3️⃣ Teste de status da instância:</b><br>";
        
        $checkUrl = $this->urlBase . "/instance/connectionState/" . $this->session;
        
        $ch = curl_init($checkUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . $this->token,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $result = curl_exec($ch);
        $http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);
        
        echo "URL: " . $checkUrl . "<br>";
        echo "HTTP Code: " . $http . "<br>";
        
        if ($error) {
            echo "<span style='color: red;'>❌ Erro: " . $error . "</span><br>";
            return false;
        }
        
        if ($http === 200) {
            echo "<span style='color: green;'>✅ Instância conectada</span><br>";
            echo "Response: " . $result . "<br>";
            return true;
        } else {
            echo "<span style='color: red;'>❌ Instância não conectada (HTTP " . $http . ")</span><br>";
            echo "Response: " . $result . "<br>";
            return false;
        }
    }
    
    public function testarEnvioMensagem($debug = true, $numeroTeste = '554399598746') {
        echo "<br><b>4️⃣ Teste de envio de mensagem:</b><br>";
        
        $mensagem = 'Teste de conectividade ' . date('d/m/Y H:i:s');
        
        // Normaliza número
        $foneTo = preg_replace('/\D+/', '', $numeroTeste);
        if (substr($foneTo, 0, 2) !== '55') $foneTo = '55'.$foneTo;
        
        // Primeiro tenta HTTPS
        $url = $this->urlBase . "/message/sendText/" . $this->session;
        $payload = [
            "number"      => $foneTo,
            "text"        => $mensagem,
            "linkPreview" => false
        ];
        
        $headers = [
            'Content-Type: application/json', 
            'apikey: ' . $this->token
        ];
        
        if ($debug) {
            echo "Número: " . $foneTo . "<br>";
            echo "URL HTTPS: " . $url . "<br>";
            echo "Payload: " . json_encode($payload) . "<br>";
        }
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $result = curl_exec($ch);
        $http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);
        
        echo "HTTP Code: " . $http . "<br>";
        echo "Tempo de resposta: " . round($info['total_time'], 2) . "s<br>";
        
        if ($error) {
            echo "<span style='color: red;'>❌ Erro cURL HTTPS: " . $error . "</span><br>";
            
            // Se HTTPS falhou, tenta HTTP
            echo "<br>🔄 Tentando com HTTP...<br>";
            $resultHTTP = $this->enviarMensagemByEvolHTTP($foneTo, $mensagem, true);
            
            if ($resultHTTP['success']) {
                return true;
            } else {
                return false;
            }
        }
        
        if ($http >= 200 && $http < 300) {
            echo "<span style='color: green;'>✅ Mensagem enviada com sucesso!</span><br>";
            echo "Response: " . $result . "<br>";
            return true;
        } else {
            echo "<span style='color: red;'>❌ Falha no envio (HTTP " . $http . ")</span><br>";
            echo "Response: " . $result . "<br>";
            return false;
        }
    }
    
    public function gerarRelatorioCompleto() {
        echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 10px 0;'>";
        $conectividade = $this->testarConectividade(true);
        echo "</div>";
        
        return $conectividade;
    }
    
    public function testarPortas() {
        echo "<br><b>🔍 Teste de portas:</b><br>";
        
        // Teste porta 80 (HTTP)
        $http_test = @fsockopen('147.93.33.133', 80, $errno, $errstr, 5);
        if ($http_test) {
            echo "✅ Porta 80 (HTTP): Aberta<br>";
            fclose($http_test);
        } else {
            echo "❌ Porta 80 (HTTP): Fechada/Filtrada<br>";
        }
        
        // Teste porta 443 (HTTPS)
        $https_test = @fsockopen('147.93.33.133', 443, $errno, $errstr, 5);
        if ($https_test) {
            echo "✅ Porta 443 (HTTPS): Aberta<br>";
            fclose($https_test);
        } else {
            echo "❌ Porta 443 (HTTPS): Fechada/Filtrada - Erro: $errstr<br>";
        }
    }
    
    public function testarFirewall() {
        echo "<br><b>🛡️ Teste de Firewall/Proxy:</b><br>";
        
        // Teste com diferentes User-Agents
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'curl/7.68.0',
            'PostmanRuntime/7.29.0'
        ];
        
        foreach ($userAgents as $ua) {
            echo "Testando com User-Agent: " . substr($ua, 0, 30) . "...<br>";
            
            $ch = curl_init('https://httpbin.org/get');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_USERAGENT      => $ua,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $result = curl_exec($ch);
            $http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($http === 200) {
                echo "✅ Conexão externa OK<br>";
                break;
            } else {
                echo "❌ Falha na conexão externa<br>";
            }
            curl_close($ch);
        }
    }
    
    public function enviarMensagemByEvolHTTP($foneTo, $mensagem, $debug = false) {
        echo "<br><b>🔄 Teste com HTTP (sem SSL):</b><br>";
        
        // Tenta HTTP primeiro (sem SSL)
        $urlBase = "http://inscritec-evolution.wz5mth.easypanel.host";
        $session = "whatsoptec";
        $token   = "429683C4C977415CAAFCCE10F7D57E11"; // ✅ CORRIGIDO

        // Normaliza número
        $foneTo = preg_replace('/\D+/', '', $foneTo);
        if (substr($foneTo, 0, 2) !== '55') $foneTo = '55'.$foneTo;

        $url = $urlBase . "/message/sendText/" . $session;
        $payload = [
            "number"      => $foneTo,
            "text"        => $mensagem,
            "linkPreview" => false
        ];

        $headers = [
            'Content-Type: application/json', 
            'apikey: ' . $token
        ];

        if ($debug) {
            echo "URL HTTP: " . $url . "<br>";
            echo "Payload: " . json_encode($payload) . "<br>";
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);

        $result = curl_exec($ch);
        $http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);

        if ($debug) {
            echo "HTTP Code: " . $http . "<br>";
            echo "Tempo de resposta: " . round($info['total_time'], 2) . "s<br>";
            echo "Response: " . $result . "<br>";
            if ($error) echo "Erro: " . $error . "<br>";
        }

        return [
            'http' => $http, 
            'result' => $result, 
            'error' => $error,
            'success' => ($http >= 200 && $http < 300)
        ];
    }
    
    public function testarProtocolos($foneTo, $mensagem) {
        echo "<br><b>🔄 Teste de diferentes protocolos:</b><br>";
        
        $protocolos = [
            'HTTPS' => 'https://inscritec-evolution.wz5mth.easypanel.host',
            'HTTP'  => 'http://inscritec-evolution.wz5mth.easypanel.host'
        ];
        
        foreach ($protocolos as $nome => $url) {
            echo "<br><b>Testando {$nome}:</b><br>";
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_NOBODY         => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $result = curl_exec($ch);
            $http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error  = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                echo "❌ {$nome}: " . $error . "<br>";
            } else {
                echo "✅ {$nome}: HTTP {$http}<br>";
                
                // Se funcionou, testa envio de mensagem
                if ($http > 0) {
                    if ($nome === 'HTTP') {
                        $resultado = $this->enviarMensagemByEvolHTTP($foneTo, $mensagem, true);
                        if ($resultado['success']) {
                            echo "🎉 <b>SUCESSO com HTTP!</b><br>";
                            return $resultado;
                        }
                    }
                }
            }
        }
        
        return false;
    }
    
    public function testarComIP() {
        echo "<br><b>🔍 Teste com IP direto:</b><br>";
        
        $ip = "147.93.33.133"; // IP que você descobriu
        $urlIP = "https://" . $ip;
        
        $ch = curl_init($urlIP);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                'Host: inscritec-evolution.wz5mth.easypanel.host', // Header Host obrigatório
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
        
        $result = curl_exec($ch);
        $http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);
        
        echo "IP direto: " . $http . " - " . ($error ?: 'OK') . "<br>";
        
        return $http > 0;
    }
    
    public function testarCloudflareBypass() {
        echo "<br><b>☁️ Teste bypass Cloudflare:</b><br>";
        
        $ch = curl_init($this->urlBase);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_COOKIEJAR      => '/tmp/cookies.txt',
            CURLOPT_COOKIEFILE     => '/tmp/cookies.txt',
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language: pt-BR,pt;q=0.9,en;q=0.8',
                'Accept-Encoding: gzip, deflate, br',
                'Sec-Ch-Ua: "Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
                'Sec-Ch-Ua-Mobile: ?0',
                'Sec-Ch-Ua-Platform: "Windows"',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: none',
                'Sec-Fetch-User: ?1',
                'Upgrade-Insecure-Requests: 1'
            ]
        ]);
        
        $result = curl_exec($ch);
        $http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);
        
        echo "Cloudflare bypass: " . $http . " - " . ($error ?: 'OK') . "<br>";
        if ($result) {
            echo "Response preview: " . substr($result, 0, 200) . "...<br>";
        }
        
        return $http === 200;
    }
    
    public function diagnosticarServidor() {
        echo "<br><b>🔧 Diagnóstico do Servidor:</b><br>";
        
        // Versão do cURL
        $curl_version = curl_version();
        echo "cURL Version: " . $curl_version['version'] . "<br>";
        echo "SSL Version: " . $curl_version['ssl_version'] . "<br>";
        echo "Protocols: " . implode(', ', $curl_version['protocols']) . "<br>";
        
        // Configurações PHP
        echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'ON' : 'OFF') . "<br>";
        echo "user_agent: " . ini_get('user_agent') . "<br>";
        echo "default_socket_timeout: " . ini_get('default_socket_timeout') . "s<br>";
        
        // Teste simples
        echo "<br><b>Teste file_get_contents:</b><br>";
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'timeout' => 10
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $result = @file_get_contents($this->urlBase, false, $context);
        if ($result !== false) {
            echo "✅ file_get_contents funcionou!<br>";
            echo "Response size: " . strlen($result) . " bytes<br>";
        } else {
            echo "❌ file_get_contents falhou<br>";
            $error = error_get_last();
            echo "Erro: " . $error['message'] . "<br>";
        }
    }
}