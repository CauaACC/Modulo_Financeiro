<?php

class Router {
    private $fws_;
    private $rotas = [];

    public function __construct($fws) {
        $this->fws_ = $fws;
    }

    public function get($uri, $handler) {
        $this->rotas['GET']['/' . trim($uri, '/')] = $handler;
    }

    public function post($uri, $handler) {
        $this->rotas['POST']['/' . trim($uri, '/')] = $handler;
    }

    public function executar() {
        if (!defined('FWA_API_EXEC')) {
            define('FWA_API_EXEC', true);
        }
        require_once(dirname(__FILE__) . '/../config.php');
        $this->fws_ = $fws_;

        $method = $_SERVER['REQUEST_METHOD'];
        $uriCompleta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = '/' . trim(explode('/api/', $uriCompleta, 2)[1] ?? '', '/');

        $tokenValido = defined('WB_API_TOKEN') && WB_API_TOKEN !== ''
            && isset($_POST['api_token']) && hash_equals(WB_API_TOKEN, $_POST['api_token']);

        // Apenas rotas registradas explicitamente em lib/cursos/api/router.php sao aceitas.
        if (isset($this->rotas[$method][$uri])) {
            // Rota registrada + token valido = contexto de servico: a chamada vem de um
            // script (Python em recursosExtras, Chatwoot), nao de um gestor logado. Sem isso
            // os controllers morrem em FWA_exigirPermissao, que exige $_SESSION.
            // Vale SO para as rotas declaradas uma a uma no router de cima -- o fallback
            // generico c/m abaixo NAO define esta constante e continua exigindo sessao.
            if ($tokenValido && !defined('FWA_API_SERVICO')) {
                define('FWA_API_SERVICO', true);
            }
            $handler = $this->rotas[$method][$uri];
            return $this->executarHandler($handler);
        }

        // Fallbacks genericos (c/m, /c/X/m/Y) -- SOMENTE quando api_token valido for enviado.
        // Preservados para scripts externos ja em uso (Python em recursosExtras) enquanto
        // eles nao forem migrados para rotas registradas. Sem token, acesso e negado (404 + log + email).

        if ($tokenValido) {
            // Fallback 1: querystring ?c=Classe&m=metodo
            if (!empty($this->fws_['c']) && !empty($this->fws_['m'])) {
                return $this->executarController($this->fws_['c'], $this->fws_['m']);
            }
            // Fallback 2: path REST-like /c/Classe/m/Metodo
            $partes = explode('/', trim($uri, '/'));
            if (count($partes) >= 4 && $partes[0] === 'c' && $partes[2] === 'm') {
                return $this->executarController($partes[1], $partes[3]);
            }
        }

        /*
         * Fallback 3 (view-by-path) permanece DESATIVADO — nao ha caso de uso legitimo
         * e expunha arquivos sem passar pelo index.php.
         *
         * $viewPath = 'view/' . implode('/', $partes);
         * $fullPath = dirname(__FILE__) . '/../../' . $viewPath;
         * if (file_exists($fullPath)) {
         *     require_once($fullPath);
         *     return;
         * }
         */

        $this->registrarBloqueio($method, $uri, $uriCompleta);

        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Rota nao encontrada']);
    }

    /**
     * Log de rotas bloqueadas: grava em arquivo diario + envia e-mail de alerta.
     * Falha silenciosamente se log/email nao puderem ser escritos (nunca interrompe a resposta).
     */
    private function registrarBloqueio($method, $uri, $uriCompleta) {
        try {
            $ip        = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
            $ua        = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $referer   = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $params    = http_build_query(array_merge($_GET, $_POST));
            $linha     = sprintf('[%s] %s %s (full=%s) ip=%s ua=%s ref=%s params=%s',
                date('Y-m-d H:i:s'), $method, $uri, $uriCompleta, $ip,
                substr($ua, 0, 200), $referer, substr($params, 0, 500));

            if (function_exists('executarLog')) {
                executarLog($linha, 'api_rota_bloqueada', true);
            }

            if (class_exists('enviarErro')) {
                $subject = '[' . (defined('ABREV_EVENTO') ? ABREV_EVENTO : 'API') . '] Rota bloqueada em /api/';
                $body    = "Tentativa de acesso a rota nao registrada:\n\n" . $linha;
                @new enviarErro('zakacc@gmail.com', $subject, $body);
            }
        } catch (Exception $e) {
            // nao interromper resposta por falha em log/email
        }
    }

    private function executarHandler($handler) {
        if (is_callable($handler)) {
            return $handler();
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$classe, $metodo] = explode('@', $handler);
            return $this->executarController($classe, $metodo);
        }

        throw new Exception("Handler inválido.");
    }

    private function executarController($classeBase, $metodo) {
        $classe = $classeBase . 'AC';
        $path = dirname(__FILE__) . '/../inscricoes/controller/' . $classe . '.php';

        if (!class_exists($classe)) {
            if (file_exists($path)) require_once($path);
        }

        if (!class_exists($classe)) {
            throw new Exception("Classe '$classe' não encontrada.");
        }

        $obj = new $classe;
        if (!method_exists($obj, $metodo)) {
            throw new Exception("Método '$metodo' não encontrado em $classe.");
        }

        return $obj->$metodo($this->fws_);
    }
}
