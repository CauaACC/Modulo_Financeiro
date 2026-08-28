<?php

/**
 * Contador de uso do menu administrativo.
 * Chamado por sendBeacon quando se clica num item do menu — nunca bloqueia
 * a navegacao e nunca devolve dado nenhum ao cliente.
 */
class GerenciarMenuAcessoAC {

    public function registrar($fws_) {
        header('Content-Type: application/json; charset=UTF-8');

        // Gate 1: so conta clique de admin logado. Sem sessao, ignora em silencio.
        if (empty($_SESSION['_logado']) || empty($_SESSION['_administrativo']) || empty($_SESSION['_cod_usuario'])) {
            echo json_encode(array('statusMessage' => false));
            return;
        }

        // Gate 2: a chamada tem que ter nascido de uma pagina do proprio site.
        // Sem isso, outro site poderia fazer o navegador do admin logado inflar
        // os contadores dele (CSRF). Falhar aqui so deixa de contar o clique.
        $host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $origem = '';
        if (!empty($_SERVER['HTTP_ORIGIN']))       { $origem = $_SERVER['HTTP_ORIGIN']; }
        elseif (!empty($_SERVER['HTTP_REFERER']))  { $origem = $_SERVER['HTTP_REFERER']; }
        $hostOrigem = $origem ? parse_url($origem, PHP_URL_HOST) : '';
        if (!$hostOrigem || strcasecmp($hostOrigem, preg_replace('/:\d+$/', '', $host)) !== 0) {
            echo json_encode(array('statusMessage' => false));
            return;
        }

        // $fws_ vem do POST ja com addslashes (config.php); GET fica fora dele
        $chave = '';
        if (!empty($fws_['k']))      { $chave = $fws_['k']; }
        elseif (!empty($_POST['k'])) { $chave = $_POST['k']; }
        elseif (!empty($_GET['k']))  { $chave = $_GET['k']; }
        $chave = trim(stripslashes($chave));

        try {
            $obj = new MenuAcesso();
            $obj->setCodUsuario($_SESSION['_cod_usuario']);
            $obj->setCodEvento(COD_EVENTO);
            $obj->setDesView($chave);
            $obj->registrar();
        } catch (Throwable $e) {
            // Throwable, nao Exception: se as classes ainda nao estiverem no
            // servidor, o "new MenuAcesso()" lanca Error e derrubaria a chamada.
        }

        echo json_encode(array('statusMessage' => true));
    }
}
