<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$debug=false;

$objLogin = new GerenciarLoginAC();
$objLogin->loginAdministrativo($fws_, $debug);
