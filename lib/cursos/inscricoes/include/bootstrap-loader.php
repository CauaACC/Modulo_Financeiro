<?php
/**
 * Bootstrap & jQuery — Carregamento condicional
 *
 * Variável de controle: $USAR_BOOTSTRAP_5 (definida no index.php)
 *
 * true  = Bootstrap 5.3 + jQuery 3.7.1 + camada de compatibilidade
 * false = Bootstrap 3.3.7 + jQuery 1.12.4 (original)
 */

if ($USAR_BOOTSTRAP_5) { ?>
        <!-- ============== BOOTSTRAP 5 + jQuery 3 ============== -->
        <link href="resources/bootstrap-5.3/css/bootstrap.min.css" rel="stylesheet">
        <link href="resources/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="resources/css/bootstrap3-compat.css" rel="stylesheet">
        <?php  if(file_exists('resources/css/bootstrap-adapt-'.COD_EVENTO.'.css')){ ?>
            <link href="resources/css/bootstrap-adapt-<?php echo COD_EVENTO; ?>.css" rel="stylesheet">
        <?php }else{?>
            <link href="resources/css/bootstrap-adapt-padrao.css" rel="stylesheet">
        <?php }?>
        <!-- Adaptações do tema para BS5 (sobrescreve o adapt acima) -->
        <link href="resources/css/bootstrap-adapt-padrao-bs5.css" rel="stylesheet">
        <link href="resources/css/bootstrap-adapt-padrao-abas.css" rel="stylesheet">
        <link href="resources/css/mensagens.css" rel="stylesheet">

        <script src="resources/js/jquery/3.7.1/jquery.min.js"></script>
        <script src="resources/bootstrap-5.3/js/bootstrap.bundle.min.js"></script>
        <script src="resources/js/bootstrap3-compat.js"></script>
        <script src="resources/js/default.js?v=20260329b"></script>
        <script type="text/javascript" src="resources/js/jquery/jquery.form.min.js"></script>
        <script src="resources/js/acao-lote-mensalidade.js?v=20260819"></script>
        <!-- Aviso de sessao expirada: vale para todas as telas, admin e inscrito -->
        <script src="resources/js/sessao-expirada.js?v=20260810"></script>

<?php } else { ?>
        <!-- ============== BOOTSTRAP 3 + jQuery 1.12 (original) ============== -->
        <link href="resources/bootstrap-3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="resources/bootstrap-3.3.7/docs/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
        <link href="resources/bootstrap-3.3.7/docs/examples/theme/theme.css" rel="stylesheet">
        <link href="resources/bootstrap-3.3.7/docs/examples/signin/signin.css" rel="stylesheet">
        <link href="resources/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
        <?php  if(file_exists('resources/css/bootstrap-adapt-'.COD_EVENTO.'.css')){ ?>
            <link href="resources/css/bootstrap-adapt-<?php echo COD_EVENTO; ?>.css" rel="stylesheet">
        <?php }else{?>
            <link href="resources/css/bootstrap-adapt-padrao.css" rel="stylesheet">
        <?php }?>
        <link href="resources/css/bootstrap-adapt-padrao-abas.css" rel="stylesheet">
        <link href="resources/css/mensagens.css" rel="stylesheet">

        <!--[if lt IE 9]><script src="resources/bootstrap-3.3.7/docs/assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
        <script src="resources/bootstrap-3.3.7/docs/assets/js/ie-emulation-modes-warning.js"></script>
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script src="resources/js/jquery/1.12.4/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src=".resources/bootstrap-3.3.7/docs/assets/js/vendor/jquery.min.js"><\/script>')</script>
        <script src="resources/bootstrap-3.3.7/dist/js/bootstrap.min.js"></script>
        <script src="resources/bootstrap-3.3.7/docs/assets/js/ie10-viewport-bug-workaround.js"></script>
        <script language="JavaScript1.2" src="resources/js/default.js"></script>
        <script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
        <script src="resources/js/acao-lote-mensalidade.js?v=20260819"></script>
        <!-- Aviso de sessao expirada: vale para todas as telas, admin e inscrito -->
        <script src="resources/js/sessao-expirada.js?v=20260810"></script>
<?php } ?>
