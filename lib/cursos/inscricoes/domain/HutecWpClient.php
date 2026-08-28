<?php

/**
 * Cliente HTTP da API REST do WordPress da HUTEC (hutec.org.br).
 *
 * Toda a conversa com o WordPress passa por aqui — publicar a divulgacao do
 * evento (post type "curso", campos ACF do grupo "CURSOS NEW") e enviar a arte.
 *
 * Autenticacao: HTTP Basic com Application Password do WordPress.
 * Credenciais POR EVENTO (tab_evento_divulgacao.wp_url/wp_user/wp_app_pass) —
 * cada gestor pode ter o seu WordPress. Quando o evento nao configura nada,
 * cai no fallback do env.php (caso HUTEC):
 *   WB_HUTEC_WP_URL (opcional, default https://hutec.org.br)
 *   WB_HUTEC_WP_USER, WB_HUTEC_WP_APP_PASS
 *
 * Regra da casa: todo post nasce como draft; quem publica e o admin no WP-admin.
 * Atencao ao atualizar: o ACF REST exige o bloco "acf" COMPLETO (todos os
 * campos obrigatorios do grupo) — enviar so o campo alterado da HTTP 400.
 */
class HutecWpClient
{

    const URL_PADRAO = 'https://hutec.org.br';

    /** Categoria "eventos_abertos" do site da HUTEC. */
    const CATEGORIA_EVENTOS_ABERTOS = 25;

    private $urlBase;
    private $usuario;
    private $appPass;
    private $timeout = 60;

    public function __construct($url = '', $usuario = '', $appPass = '')
    {
        // O fallback do env.php (credenciais da HUTEC) so vale quando o evento
        // NAO tem URL propria. Com URL propria, usuario e senha tem que vir do
        // evento — senao a senha da HUTEC seria enviada ao site de outro gestor.
        if ($url == '') {
            $url = defined('WB_HUTEC_WP_URL') && WB_HUTEC_WP_URL != '' ? WB_HUTEC_WP_URL : self::URL_PADRAO;
            if ($usuario == '') {
                $usuario = defined('WB_HUTEC_WP_USER') ? WB_HUTEC_WP_USER : '';
            }
            if ($appPass == '') {
                $appPass = defined('WB_HUTEC_WP_APP_PASS') ? WB_HUTEC_WP_APP_PASS : '';
            }
        }
        $this->urlBase = rtrim($url, '/');
        $this->usuario = $usuario;
        $this->appPass = $appPass;

        if ($this->usuario == '' || $this->appPass == '') {
            throw new Exception("Credenciais do WordPress nao configuradas para " . $this->urlBase . ". "
                . "Informe usuario e senha de aplicativo na secao 'WordPress de destino' da tela "
                . "(ou, para o site da HUTEC, defina WB_HUTEC_WP_USER/WB_HUTEC_WP_APP_PASS no env.php).");
        }
    }

    public function getUrlBase()
    {
        return $this->urlBase;
    }

    /**
     * Cria o post do tipo "curso" como RASCUNHO. Retorna a resposta bruta
     * {http, corpo, ...} — o corpo traz id e link.
     */
    public function criarCurso(array $payload)
    {
        $payload['status'] = 'draft';
        return $this->requisitar('POST', '/wp-json/wp/v2/curso', $payload);
    }

    /** Atualiza um post "curso" existente (mantem o status atual do post). */
    public function atualizarCurso($postId, array $payload)
    {
        return $this->requisitar('POST', '/wp-json/wp/v2/curso/' . (int) $postId, $payload);
    }

    public function consultarCurso($postId)
    {
        return $this->requisitar('GET', '/wp-json/wp/v2/curso/' . (int) $postId . '?context=edit');
    }

    /**
     * Testa a credencial sem publicar nada: /users/me exige autenticacao.
     * 200 = credencial valida; 401/403 = usuario ou senha recusados.
     */
    public function testarCredencial()
    {
        return $this->requisitar('GET', '/wp-json/wp/v2/users/me?context=edit');
    }

    /**
     * Sobe um arquivo para a biblioteca de midia. A arte fica fora do webroot
     * no InscriTec (logn/), sem URL publica — por isso enviamos os BYTES.
     * Retorna {http, corpo, ...}; corpo['id'] e o id da midia.
     */
    public function enviarMidia($caminhoArquivo, $nomeArquivo)
    {
        if (!is_file($caminhoArquivo)) {
            throw new Exception("Arquivo da arte nao encontrado no servidor: " . basename($caminhoArquivo));
        }
        $conteudo = file_get_contents($caminhoArquivo);
        if ($conteudo === false) {
            throw new Exception("Nao foi possivel ler o arquivo da arte.");
        }

        $mime = 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeDetectado = finfo_file($finfo, $caminhoArquivo);
            finfo_close($finfo);
            if ($mimeDetectado != '') {
                $mime = $mimeDetectado;
            }
        }

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $this->urlBase . '/wp-json/wp/v2/media',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_USERPWD        => $this->usuario . ':' . $this->appPass,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $conteudo,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: ' . $mime,
                'Content-Disposition: attachment; filename="' . basename($nomeArquivo) . '"',
                'Accept: application/json',
            ),
        ));

        $corpoBruto = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erroCurl = curl_error($ch);
        curl_close($ch);

        if ($corpoBruto === false) {
            throw new Exception("Nao foi possivel comunicar com o WordPress da HUTEC: " . $erroCurl);
        }

        $corpo = json_decode($corpoBruto, true);
        if (!is_array($corpo)) {
            $corpo = array();
        }
        return array('http' => (int) $http, 'corpo' => $corpo, 'corpo_bruto' => $corpoBruto, 'erro' => $erroCurl);
    }

    /**
     * Traduz uma resposta de erro da API em mensagem legivel; retorna '' se ok.
     */
    public function descreverErro(array $resposta)
    {
        if ($resposta['http'] >= 200 && $resposta['http'] < 300) {
            return '';
        }
        $msg = isset($resposta['corpo']['message']) ? $resposta['corpo']['message'] : '';
        if ($msg == '' && isset($resposta['corpo']['data']['params']['acf'])) {
            $msg = $resposta['corpo']['data']['params']['acf'];
        }
        if ($msg == '') {
            $msg = substr($resposta['corpo_bruto'], 0, 300);
        }
        return "WordPress da HUTEC respondeu HTTP " . $resposta['http'] . ": " . $msg;
    }

    /**
     * @return array {http, corpo (array), corpo_bruto (string), erro (string)}
     *         Nunca lanca por erro da API — quem chama decide o que fazer com o
     *         status. So lanca quando a requisicao nem sai (rede/cURL).
     */
    private function requisitar($metodo, $caminho, array $payload = null)
    {
        $ch = curl_init();
        $opcoes = array(
            CURLOPT_URL            => $this->urlBase . $caminho,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_USERPWD        => $this->usuario . ':' . $this->appPass,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_CUSTOMREQUEST  => $metodo,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Accept: application/json',
            ),
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
            throw new Exception("Nao foi possivel comunicar com o WordPress da HUTEC: " . $erroCurl);
        }

        $corpo = json_decode($corpoBruto, true);
        if (!is_array($corpo)) {
            $corpo = array();
        }

        return array('http' => (int) $http, 'corpo' => $corpo, 'corpo_bruto' => $corpoBruto, 'erro' => $erroCurl);
    }
}
