<?php
/**
 * Gatilho unico do cron do INESCO. Roda direto no servidor via PHP CLI
 * (crontab/cPanel), fora do Apache/PHP-FPM — nunca via HTTP, nunca via
 * router.php/api_token. Fica em lib/cursos/cron/, ao lado de lib/cursos/api/
 * (mesma ideia de "camada de entrada fina", uma para HTTP e outra para CLI) —
 * a logica de verdade mora nos Controllers normais em
 * lib/cursos/inscricoes/controller/, este arquivo so faz o bootstrap e chama.
 *
 * Bootstrap: sobe o config.php normal com rota=admin e sem sessao
 * administrativa — cai no caminho ja existente de COD_EVENTO=0/
 * config-opctec.php (o mesmo usado por um acesso a /admin sem evento
 * selecionado), o que da autoload de classes + conexao com o banco
 * principal sem prender o processo a um evento especifico.
 *
 * Novas tarefas agendadas = novo metodo em algum Controller + uma linha nova
 * aqui embaixo; nao precisa de nova entrada de cron no servidor.
 *
 * Uso: php executar-tarefas.php
 * Local (teste): CRON_SERVER_NAME=eventos.test php executar-tarefas.php
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Acesso negado.');
}

$_SERVER['SERVER_NAME']     = getenv('CRON_SERVER_NAME') ?: 'opctec.net.br';
$_SERVER['HTTP_HOST']       = $_SERVER['SERVER_NAME'];
$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
$_SERVER['REQUEST_URI']     = '/admin/index.php';
$_SERVER['SCRIPT_NAME']     = '/admin/index.php';
$_SERVER['HTTP_USER_AGENT'] = 'cron-cli';
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../..');
}
$_GET['prefixo'] = 'opctec';
$_GET['rota']    = 'admin';
$_SESSION = array();

require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/cursos/inscricoes/include/config.php';

$lockFile   = sys_get_temp_dir() . '/inesco_cron_executar_tarefas.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    executarLog('Execucao anterior ainda em andamento — pulando esta chamada.', 'CronExecutarTarefas', true);
    exit(0);
}

$cron = new GerenciarCronAC();

// 1) decide o que precisa de aviso e enfileira (nao envia nada ainda)
try {
    executarLog('INICIO executarAvisoVencimento', 'CronExecutarTarefas', true);
    $cron->executarAvisoVencimento(false);
    executarLog('FIM executarAvisoVencimento', 'CronExecutarTarefas', true);
} catch (Exception $e) {
    executarLog('ERRO executarAvisoVencimento: ' . $e->getMessage(), 'CronExecutarTarefas', true);
}

// 2) envia de verdade tudo que estiver PENDENTE em tab_email_fila — inclui
// o que acabou de ser enfileirado acima e o que enviarConfirmacaoPagamento()
// (chamado no fluxo web, fora deste script) enfileirou desde a ultima execucao
try {
    executarLog('INICIO processarFilaEmail', 'CronExecutarTarefas', true);
    $cron->processarFilaEmail(false);
    executarLog('FIM processarFilaEmail', 'CronExecutarTarefas', true);
} catch (Exception $e) {
    executarLog('ERRO processarFilaEmail: ' . $e->getMessage(), 'CronExecutarTarefas', true);
}

flock($lockHandle, LOCK_UN);
fclose($lockHandle);
