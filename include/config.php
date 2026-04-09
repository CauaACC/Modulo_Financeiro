<?php
/**
 * Configuração simplificada do módulo financeiro standalone.
 * Baseado no config.php do sistema principal, com dependências mínimas.
 */

if (!session_id()) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set('America/Sao_Paulo');

// === CONFIGURAÇÃO DO EVENTO ===
if (!defined('COD_EVENTO')) {
    define('COD_EVENTO', '115');
}

// === CONTROLE DE ERROS ===
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set("display_errors", "Off");
$debug = false;

// === SANITIZAÇÃO DE GET/POST ===
$fws_ = array();
$fwsGet_ = array();

foreach ($_GET as $keyG => $valG) {
    $valG = (!is_array($valG) ? addslashes($valG) : "");
    $keyG = (!is_array($keyG) ? addslashes($keyG) : "");
    $fwsGet_[$keyG] = $valG;
}

if (!isset($_GET['content'])) $_GET['content'] = null;
if (!isset($_SESSION['_administrativo'])) $_SESSION['_administrativo'] = null;
if (!isset($fws_['c'])) $fws_['c'] = null;
if (!isset($fws_['m'])) $fws_['m'] = null;
if (!isset($fws_['msg'])) $fws_['msg'] = null;
if (!isset($_SESSION['_logadoComo'])) $_SESSION['_logadoComo'] = null;

foreach ($_POST as $key => $val) {
    $val = (!is_array($val) ? addslashes($val) : "");
    if (strpos($key, '_')) {
        $key = strtolower($key);
    }
    $fws_[$key] = $val;
    if ($fws_[$key] == 'undefined') {
        $fws_[$key] = "";
    }
    if (is_array($_POST[$key])) {
        $fws_[$key] = $_POST[$key];
        for ($ii = 0; $ii < count($_POST[$key]); $ii++) {
            $valArray = addslashes($_POST[$key][$ii]);
            $fws_[$key][$ii] = $valArray;
        }
    }
}

if (@$fws_['msg'] == "" && isset($_SESSION['MSG']) && $_SESSION['MSG'] != '') {
    $fws_['msg'] = addslashes($_SESSION['MSG']);
}

// === FUNÇÕES UTILITÁRIAS ===
require_once(dirname(__FILE__) . "/functions/functions.php");

// === AUTOLOADER ===
define('WB_ROOT', dirname(__FILE__) . '/../');

$path = WB_ROOT . 'controller';
$path .= PATH_SEPARATOR . WB_ROOT . 'domain';
$path .= PATH_SEPARATOR . WB_ROOT . 'domain/util';

set_include_path(get_include_path() . PATH_SEPARATOR . $path);

spl_autoload_register(function ($class_name) {
    $fileClass = $class_name . ".php";
    $dirs = explode(PATH_SEPARATOR, get_include_path());
    foreach ($dirs as $dir) {
        $file = $dir . DIRECTORY_SEPARATOR . $fileClass;
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// === CONSTANTES DE URL ===
$PROTOCOLO = "https";
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == 'eventos.test' || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0) {
    $PROTOCOLO = "http";
}

// Ajuste WB_PATH conforme o caminho do seu servidor web
define('WB_PATH', '/sistema');
define('WB_URL', $PROTOCOLO . '://' . $_SERVER['HTTP_HOST']);
define('WB_URL_ACTION', WB_URL . WB_PATH . '/index-action.php');
define('WB_URL_COMMAND', WB_URL_ACTION . '?content=controller/CommandAC.php');
define('WB_URL_VIEW', WB_URL . WB_PATH . '/index.php');
define('WB_URL_SITE', WB_URL . WB_PATH);
define('WB_URL_IMG', WB_URL . WB_PATH . '/resources/image');
define('IP', $_SERVER['REMOTE_ADDR']);

// === CONTEÚDO ROTEAMENTO ===
if (!isset($_POST['content'])) $_POST['content'] = null;

if ($_GET['content'] == "") {
    if ($_POST['content'] == "") {
        $content = "default.php";
    } else {
        $content = $_POST['content'];
    }
} else {
    $content = $_GET['content'];
}

// Proteção de roteamento
if ($content !== "default.php") {
    $diretoriosPermitidos = array('view/', 'controller/CommandAC.php');
    $permitido = false;
    foreach ($diretoriosPermitidos as $dir) {
        if (strpos($content, $dir) === 0) {
            $permitido = true;
            break;
        }
    }
    if (!$permitido
        || strpos($content, '..') !== false
        || preg_match('/^(\/|[a-zA-Z]:)/', $content)
        || strpos($content, "\0") !== false
        || strtolower(pathinfo($content, PATHINFO_EXTENSION)) !== 'php'
    ) {
        $content = "default.php";
    }
}
