<?php
/**
 * Funções utilitárias mínimas para o módulo financeiro.
 */

require_once(dirname(__FILE__) . "/FWA_Autenticacao.php");

function tratarJson($texto) {
    $texto = str_replace(array("\r\n", "\n", "\r", '"', "'"), array(' ', ' ', ' ', '\"', "\'"), $texto);
    return $texto;
}

function addslashesopctec($texto) {
    return addslashes($texto);
}

function verifItemSelected($item, $arrayList, $debug = false) {
    if ($item == $arrayList) {
        return " selected='selected' ";
    }
    return "";
}

function executarLog($text, $fnName = 'log', $executar = false) {
    if (!$executar) return;
    $logDir = defined('WB_ROOT') ? WB_ROOT . '../logn/' : '/tmp/';
    if (!is_dir($logDir)) return;
    $logFile = $logDir . $fnName . '-' . date('Y-m-d') . '.log';
    @file_put_contents($logFile, date('H:i:s') . " - " . $text . "\n", FILE_APPEND);
}

function FWA_Arquivo_Existe($arquivo, $redirect = 0) {
    return file_exists($arquivo);
}
