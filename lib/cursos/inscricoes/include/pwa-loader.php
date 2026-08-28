<?php
/**
 * pwa-loader.php — Emite meta tags PWA se o evento tiver PWA habilitado
 *
 * Requisitos para o evento ter PWA:
 *   1. Coluna pwa_habilitado = 1 em tab_eventos_configuracao
 *   2. Ícones em resources/public/{COD_EVENTO}/pwa-icon-192.png e pwa-icon-512.png
 *
 * Incluir no <head> do index.php: require 'include/pwa-loader.php';
 */
$_pwaUsuarioLogado = !empty($_SESSION['_cod_inscrito']) || !empty($_SESSION['_cod_usuario']);
if (isset($objEvConf) && $objEvConf->getPwaHabilitado() == 1 && $_pwaUsuarioLogado) {
    $codEventoDir = str_pad(COD_EVENTO, 5, '0', STR_PAD_LEFT);
    $pwaBasePath = 'resources/public/' . $codEventoDir . '/';
    $iconPath = $pwaBasePath . 'pwa-icon-192.png';
    $manifestPath = $pwaBasePath . 'manifest.json';
?>
        <link rel="manifest" href="<?php echo $manifestPath; ?>">
        <meta name="theme-color" content="#5b9a6e">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="<?php echo ABREV_EVENTO; ?>">
        <?php if (file_exists($iconPath)) { ?>
        <link rel="apple-touch-icon" href="<?php echo $iconPath; ?>">
        <?php } ?>
<?php
}
