<?php
require_once(dirname(__FILE__) . "/include/config.php");
?>
<!DOCTYPE html>
<html lang="pt-Br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Módulo Financeiro</title>

    <!-- Bootstrap 3 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <!-- TableSorter -->
    <script type="text/javascript" src="resources/js/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="resources/js/jquery.form.js"></script>

    <!-- JS Utilitários -->
    <script type="text/javascript" src="resources/js/default.js"></script>

    <style>
        body { padding-top: 60px; }
        .cabecalho { background-color: #2c3e50; }
        .cabecalho .navbar-brand { color: #fff; }
        .submenu-area a { margin-right: 5px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top cabecalho">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="<?php echo WB_URL_VIEW ?>">Módulo Financeiro</a>
            </div>
            <?php if (isset($_SESSION['_logado']) && $_SESSION['_logado'] == 1) { ?>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="<?php echo WB_URL_COMMAND ?>&c=GerenciarLogin&m=efetuarLogout">
                    <i class="fa fa-sign-out"></i> Sair
                </a></li>
            </ul>
            <?php } ?>
        </div>
    </nav>

    <!-- Conteúdo -->
    <div class="container" id="container">
        <?php
        if (FWA_Arquivo_Existe($content)) {
            require_once($content);
        } else {
            // Página padrão: redireciona para login ou dashboard
            if (isset($_SESSION['_logado']) && $_SESSION['_logado'] == 1) {
                require_once('view/admin/GerenciarLancamentosContabeis.php');
            } else {
                require_once('view/admin/login.php');
            }
        }
        ?>
    </div>

    <!-- Rodapé -->
    <div class="footer" style="text-align: center; padding: 20px; margin-top: 40px; color: #999;">
        <small>Módulo Financeiro Standalone</small>
    </div>
</body>
</html>
