<?php

/**
 * Cliente HTTP da API Focus NFe.
 *
 * Toda a conversa com a Focus passa por aqui — trocar de provedor no futuro
 * mexe nesta classe e em mais nada.
 *
 * Autenticacao: HTTP Basic com o token como usuario e senha em branco.
 * Ambiente e token vem do env.php (fora do webroot):
 *   WB_FOCUS_NFE_AMBIENTE, WB_FOCUS_NFE_TOKEN_HOMOLOGACAO, WB_FOCUS_NFE_TOKEN_PRODUCAO
 *
 * NFS-e nacional usa /v2/nfsen (Londrina aderiu ao padrao nacional em 01/01/2026).
 * O /v2/nfse antigo e o padrao municipal, que nao se aplica aqui.
 */
class FocusNFeClient {

    const URL_HOMOLOGACAO = 'https://homologacao.focusnfe.com.br';
    const URL_PRODUCAO    = 'https://api.focusnfe.com.br';

    const RECURSO_NFSE_NACIONAL = '/v2/nfsen';
    const RECURSO_NFE           = '/v2/nfe';

    private $ambiente;
    private $token;
    private $timeout = 60;

    public function __construct($ambiente = null) {
        $this->ambiente = $ambiente ? $ambiente : $this->getAmbienteConfigurado();
        $this->token = $this->getTokenConfigurado($this->ambiente);

        if ($this->token == '') {
            throw new Exception("Token da Focus NFe nao configurado para o ambiente '"
                . $this->ambiente . "'. Configure no env.php.");
        }
    }

    public function getAmbiente() {
        return $this->ambiente;
    }

    public function ehProducao() {
        return $this->ambiente == 'producao';
    }

    /**
     * Ambiente configurado SEM precisar de token. Quem so quer saber em que
     * ambiente a nota sairia — a previa, por exemplo — nao deveria falhar por
     * falta de credencial: previa nao conversa com a API.
     */
    public static function ambienteConfigurado() {
        $amb = defined('WB_FOCUS_NFE_AMBIENTE') ? WB_FOCUS_NFE_AMBIENTE : 'homologacao';
        return ($amb == 'producao') ? 'producao' : 'homologacao';
    }

    private function getAmbienteConfigurado() {
        return self::ambienteConfigurado();
    }

    private function getTokenConfigurado($ambiente) {
        if ($ambiente == 'producao') {
            return defined('WB_FOCUS_NFE_TOKEN_PRODUCAO') ? WB_FOCUS_NFE_TOKEN_PRODUCAO : '';
        }
        return defined('WB_FOCUS_NFE_TOKEN_HOMOLOGACAO') ? WB_FOCUS_NFE_TOKEN_HOMOLOGACAO : '';
    }

    private function getUrlBase() {
        return $this->ehProducao() ? self::URL_PRODUCAO : self::URL_HOMOLOGACAO;
    }

    /**
     * Envia a nota. A referencia e a chave de idempotencia: reenviar a mesma ref
     * nao gera nota duplicada, a Focus devolve a que ja existe.
     */
    public function emitirNfse($referencia, array $payload) {
        $url = self::RECURSO_NFSE_NACIONAL . '?ref=' . urlencode($referencia);
        return $this->requisitar('POST', $url, $payload);
    }

    public function consultarNfse($referencia, $completa = false) {
        $url = self::RECURSO_NFSE_NACIONAL . '/' . urlencode($referencia);
        if ($completa) {
            $url .= '?completa=1';
        }
        return $this->requisitar('GET', $url);
    }

    public function cancelarNfse($referencia, $justificativa) {
        $url = self::RECURSO_NFSE_NACIONAL . '/' . urlencode($referencia);
        return $this->requisitar('DELETE', $url, array('justificativa' => $justificativa));
    }

    // ------------------------------------------------------------------
    // NF-e de mercadoria (modelo 55) — outro recurso, MESMO token e mesmo
    // certificado. A conversa e identica a da NFS-e: ref idempotente, ciclo
    // processando -> autorizado, DELETE para cancelar.
    // ------------------------------------------------------------------

    public function emitirNfe($referencia, array $payload) {
        $url = self::RECURSO_NFE . '?ref=' . urlencode($referencia);
        return $this->requisitar('POST', $url, $payload);
    }

    public function consultarNfe($referencia, $completa = false) {
        $url = self::RECURSO_NFE . '/' . urlencode($referencia);
        if ($completa) {
            $url .= '?completa=1';
        }
        return $this->requisitar('GET', $url);
    }

    public function cancelarNfe($referencia, $justificativa) {
        $url = self::RECURSO_NFE . '/' . urlencode($referencia);
        return $this->requisitar('DELETE', $url, array('justificativa' => $justificativa));
    }

    /**
     * Baixa PDF/XML da nota. A Focus devolve caminhos relativos ao dominio da API,
     * entao aceitamos tanto caminho quanto URL completa.
     */
    public function baixarArquivo($caminhoOuUrl) {
        if ($caminhoOuUrl == '') {
            throw new Exception("Caminho do arquivo nao informado.");
        }

        // O PDF (DANFSE) vem como URL completa de um bucket S3 da Focus; o XML
        // vem como caminho relativo da API. Sao duas origens diferentes:
        //   - caminho relativo  -> monta a URL da API e MANDA o token
        //   - URL completa fora do dominio da API -> S3, que RECUSA (HTTP 400)
        //     se receber um Authorization que nao e dele. O link ja da acesso.
        $url = $caminhoOuUrl;
        $usarAutenticacao = true;

        if (stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0) {
            $hostArquivo = parse_url($url, PHP_URL_HOST);
            $hostApi     = parse_url($this->getUrlBase(), PHP_URL_HOST);
            $usarAutenticacao = ($hostArquivo === $hostApi);
        } else {
            $url = $this->getUrlBase() . '/' . ltrim($caminhoOuUrl, '/');
        }

        $ch = curl_init();
        $opcoes = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => $this->timeout,
        );
        if ($usarAutenticacao) {
            $opcoes[CURLOPT_USERPWD]  = $this->token . ':';
            $opcoes[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        }
        curl_setopt_array($ch, $opcoes);

        $conteudo = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erroCurl = curl_error($ch);
        curl_close($ch);

        if ($conteudo === false || $erroCurl != '') {
            throw new Exception("Falha ao baixar arquivo da nota: " . $erroCurl);
        }
        if ($http >= 400) {
            throw new Exception("Falha ao baixar arquivo da nota (HTTP " . $http . ").");
        }

        return $conteudo;
    }

    /**
     * @return array {http, corpo (array), corpo_bruto (string), erro (string)}
     *         Nunca lanca por erro da API — quem chama decide o que fazer com o
     *         status. So lanca quando a requisicao nem sai (rede/cURL).
     */
    private function requisitar($metodo, $caminho, array $payload = null) {
        $url = $this->getUrlBase() . $caminho;

        $ch = curl_init();
        $opcoes = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_USERPWD        => $this->token . ':',
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
            throw new Exception("Nao foi possivel comunicar com a Focus NFe: " . $erroCurl);
        }

        $corpo = json_decode($corpoBruto, true);
        if (!is_array($corpo)) {
            $corpo = array();
        }

        return array(
            'http'        => (int) $http,
            'corpo'       => $corpo,
            'corpo_bruto' => $corpoBruto,
            'erro'        => $erroCurl,
        );
    }

    /**
     * Consulta auxiliar de municipios. Usada para resolver o codigo IBGE do
     * tomador, que o cadastro de inscrito nao guarda. A busca por nome e
     * "contem" — quem chama precisa conferir a correspondencia exata.
     */
    public function consultarMunicipios($uf, $nome = '') {
        $url = '/v2/municipios?uf=' . urlencode($uf);
        if ($nome != '') {
            $url .= '&nome=' . urlencode($nome);
        }
        return $this->requisitar('GET', $url);
    }

    /**
     * Dados do municipio pelo codigo IBGE: diz se a NFS-e esta habilitada,
     * qual o provedor (Nacional x municipal) e se exige certificado.
     */
    public function consultarMunicipioPorCodigo($codigoIbge) {
        return $this->requisitar('GET', '/v2/municipios/' . urlencode($codigoIbge));
    }

    /**
     * Teste de conectividade e credencial, sem emitir nada: consulta uma
     * referencia que nao existe. 404 = token valido; 401/403 = token invalido.
     */
    public function testarConexao() {
        $resposta = $this->consultarNfse('TESTE-CONEXAO-' . date('YmdHis'));
        $http = $resposta['http'];

        if ($http == 401 || $http == 403) {
            return array(
                'ok' => false,
                'mensagem' => 'Token recusado pela Focus NFe (HTTP ' . $http . ') no ambiente ' . $this->ambiente . '.'
            );
        }
        if ($http == 404) {
            return array(
                'ok' => true,
                'mensagem' => 'Conexao OK com a Focus NFe (ambiente ' . $this->ambiente . ').'
            );
        }
        return array(
            'ok' => false,
            'mensagem' => 'Resposta inesperada da Focus NFe (HTTP ' . $http . '): ' . $resposta['corpo_bruto']
        );
    }

}
