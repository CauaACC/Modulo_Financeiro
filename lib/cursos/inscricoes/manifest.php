<?php
/**
 * manifest.php — Gera manifest.json dinamicamente para PWA por evento
 *
 * Convenção de ícones:
 *   resources/public/{COD_EVENTO_FORMATADO}/pwa-icon-192.png
 *   resources/public/{COD_EVENTO_FORMATADO}/pwa-icon-512.png
 */
require_once(dirname(__FILE__) . "/include/config.php");

header('Content-Type: application/manifest+json');

$codEventoDir = str_pad(COD_EVENTO, 5, '0', STR_PAD_LEFT);
$basePath = 'resources/public/' . $codEventoDir . '/';

$icons = array();
foreach (array(192, 512) as $size) {
    $iconFile = $basePath . 'pwa-icon-' . $size . '.png';
    if (file_exists($iconFile)) {
        $icons[] = array(
            'src' => $iconFile,
            'sizes' => $size . 'x' . $size,
            'type' => 'image/png'
        );
    }
}

$manifest = array(
    'name' => TITLE_APP,
    'short_name' => ABREV_EVENTO,
    'description' => TITLE_APP . ' - ' . ANO_EVENTO,
    'start_url' => 'index.php',
    'display' => 'standalone',
    'background_color' => '#ffffff',
    'theme_color' => '#5b9a6e',
    'orientation' => 'portrait-primary',
    'icons' => $icons
);

echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
