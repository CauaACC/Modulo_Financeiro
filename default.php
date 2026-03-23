<?php
// Redireciona para login ou tela principal
if (isset($_SESSION['_logado']) && $_SESSION['_logado'] == 1) {
    require_once('view/admin/GerenciarLancamentosContabeis.php');
} else {
    require_once('view/admin/login.php');
}
