<?php
/**
 * Entry point para requisições AJAX (sem HTML shell).
 */
require_once(dirname(__FILE__) . "/include/config.php");

if (FWA_Arquivo_Existe($content)) {
    require_once($content);
}
