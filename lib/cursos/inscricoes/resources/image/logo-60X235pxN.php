<?php
$dir = dirname(__FILE__);
$_GET['figura'] = true;
require_once($dir."/../../inscricoes/include/config.php");
$fws_['cod_oficina'] = addslashes($_GET['id']);
$debug = true;

if (!defined('COD_EVENTO')) {
    http_response_code(400);
    executarLog('COD_EVENTO-nao-definido-400',"logoEvento",$debug);
    exit('COD_EVENTO não definido.');
}

$filepath = WB_PATH_LOG_CERTIFICADOS."../media/";

// Lista de candidatos em ordem de prioridade: evento -> gestor -> sigla organização -> default OPCTEC
$candidatos = array();

// 1) Logo específica do evento (pasta segura /media/)
$candidatos[] = $filepath . "logo-60X235pxN-" . intval(COD_EVENTO) . ".png";

// 2) Logo do gestor (pasta segura /media/)
if (isset($objEv) && is_object($objEv) && method_exists($objEv, 'getGestor') && $objEv->getGestor() != '') {
    $candidatos[] = $filepath . "logo-60X235pxN-" . preg_replace('/[^A-Za-z0-9_\-]/', '', $objEv->getGestor()) . ".png";
}

// 3) Logo da sigla da organização (pasta segura /media/)
if (isset($organizacao) && is_object($organizacao) && method_exists($organizacao, 'getSigla') && $organizacao->getSigla() != '') {
    $candidatos[] = $filepath . "logo-60X235pxN-" . strtoupper(preg_replace('/[^A-Za-z0-9_\-]/', '', $organizacao->getSigla())) . ".png";
}

// 4) Fallback final: logo OPCTEC estática dentro de resources/image (sempre presente)
$candidatos[] = $dir . "/logo-OPCTEC.png";

$filePath = false;
$pastaMedia = realpath($filepath);
foreach ($candidatos as $candidato) {
    $resolvido = realpath($candidato);
    if (!$resolvido || !file_exists($resolvido)) {
        continue;
    }
    // Arquivos em /media/ devem ficar contidos na pasta esperada (defesa contra path traversal).
    // Já o fallback OPCTEC mora em resources/image, então é confiável por estar fixado em código.
    $dentroDeMedia = $pastaMedia && strpos($resolvido, $pastaMedia) === 0;
    $ehFallbackEstatico = ($resolvido === realpath($dir . "/logo-OPCTEC.png"));
    if ($dentroDeMedia || $ehFallbackEstatico) {
        $filePath = $resolvido;
        break;
    }
}

if (!$filePath) {
    http_response_code(404);
    executarLog($filepath . '-nao-encontrada-404-sem-fallback',"logoEvento",$debug);
    exit('Logo não encontrada.');
}

executarLog($filePath . '-servindo',"logoEvento",$debug);

// Descobre o tipo MIME real (segurança contra arquivos disfarçados)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Aceitar somente tipos de imagem
$allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
if (!in_array($mimeType, $allowed, true)) {
    http_response_code(403);
    executarLog($filePath . '-tipo-nao-permitido-403',"logoEvento",$debug);
    exit('Tipo de arquivo não permitido.');
}

// Define cabeçalhos e envia a imagem
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
