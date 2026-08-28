<?php
// Auto-block de IPs que abusam do roteador.
// Se o mesmo IP gera mais de PROTECAO_IP_LIMITE_HITS bloqueios no log
// "roteamento-bloqueado" dentro de PROTECAO_IP_JANELA_SEG segundos,
// entra em blacklist por PROTECAO_IP_BLOQUEIO_SEG segundos.

// Configuracao externa fora do webroot (mesmo padrao do env.php).
// Permite ajustar limites sem mexer no codigo nem fazer deploy.
@include_once($_SERVER['DOCUMENT_ROOT'] . '/../logn/eventos/cache/cfg/protecao_ip.php');

if (!defined('PROTECAO_IP_LIMITE_HITS')) define('PROTECAO_IP_LIMITE_HITS', 50);
if (!defined('PROTECAO_IP_JANELA_SEG')) define('PROTECAO_IP_JANELA_SEG', 600);
if (!defined('PROTECAO_IP_BLOQUEIO_SEG')) define('PROTECAO_IP_BLOQUEIO_SEG', 3600);

function protecaoIPBloqueado_dir() {
    $dir = WB_ROOT . '../logn/eventos/geral/';
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

function protecaoIPBloqueado_isWhitelist($ip) {
    $whitelist = array('189.90.70.56', '177.100.49.198');
    return in_array($ip, $whitelist, true);
}

function protecaoIPBloqueado_isDesativado() {
    return file_exists(protecaoIPBloqueado_dir() . 'protecao_ip_desativada.flag');
}

function protecaoIPBloqueado_verificar($ip) {
    if (protecaoIPBloqueado_isDesativado()) {
        return false;
    }
    if (protecaoIPBloqueado_isWhitelist($ip)) {
        return false;
    }
    $arq = protecaoIPBloqueado_dir() . 'ip_bloqueado.txt';
    if (!file_exists($arq)) {
        return false;
    }
    $linhas = @file($arq, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$linhas) {
        return false;
    }
    $agora = time();
    foreach ($linhas as $l) {
        $partes = explode('|', $l);
        if (count($partes) < 2) continue;
        if ($partes[0] === $ip && (int)$partes[1] > $agora) {
            return true;
        }
    }
    return false;
}

function protecaoIPBloqueado_registrar($ip) {
    if (protecaoIPBloqueado_isDesativado()) {
        return false;
    }
    if (protecaoIPBloqueado_isWhitelist($ip)) {
        return false;
    }
    $dir = protecaoIPBloqueado_dir();
    $arqHits = $dir . 'ip_hits.txt';
    $arqBlk = $dir . 'ip_bloqueado.txt';
    $agora = time();
    $corte = $agora - PROTECAO_IP_JANELA_SEG;

    @file_put_contents($arqHits, $ip . '|' . $agora . "\n", FILE_APPEND | LOCK_EX);

    $contagem = 0;
    $valido = array();
    if (file_exists($arqHits)) {
        $linhas = @file($arqHits, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($linhas) {
            foreach ($linhas as $l) {
                $partes = explode('|', $l);
                if (count($partes) < 2) continue;
                if ((int)$partes[1] >= $corte) {
                    $valido[] = $l;
                    if ($partes[0] === $ip) $contagem++;
                }
            }
            if (count($linhas) > count($valido) + 100) {
                @file_put_contents($arqHits, implode("\n", $valido) . "\n", LOCK_EX);
            }
        }
    }

    if ($contagem < PROTECAO_IP_LIMITE_HITS) {
        return false;
    }

    $expira = $agora + PROTECAO_IP_BLOQUEIO_SEG;
    $blk = array();
    if (file_exists($arqBlk)) {
        $linhas = @file($arqBlk, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($linhas) {
            foreach ($linhas as $l) {
                $partes = explode('|', $l);
                if (count($partes) < 2) continue;
                if ((int)$partes[1] <= $agora) continue;
                if ($partes[0] === $ip) continue;
                $blk[] = $l;
            }
        }
    }
    $blk[] = $ip . '|' . $expira . '|' . Date('Y-m-d H:i:s');
    @file_put_contents($arqBlk, implode("\n", $blk) . "\n", LOCK_EX);
    return true;
}
