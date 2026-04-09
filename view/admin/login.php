<?php
require_once(dirname(__FILE__) . "/../../include/config.php");

if (isset($_SESSION['_logado']) && $_SESSION['_logado'] == 1) {
    $URL = WB_URL_VIEW . "?content=view/admin/GerenciarLancamentosContabeis.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}
?>

<div class="container" style="max-width: 400px; margin-top: 80px;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Módulo Financeiro - Login</h3>
        </div>
        <div class="panel-body">
            <?php if (isset($_SESSION['MSG']) && $_SESSION['MSG'] != '') { ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['MSG']; $_SESSION['MSG'] = ''; ?>
                </div>
            <?php } ?>

            <form action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="form-group">
                    <label for="login">Login / E-mail:</label>
                    <input type="text" id="login" name="login" class="form-control" required autofocus>
                </div>
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" class="form-control" required>
                </div>
                <input type="hidden" name="c" value="GerenciarLogin">
                <input type="hidden" name="m" value="loginAdministrativo">
                <input type="hidden" name="_submit_check" value="1">
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>
        </div>
    </div>
</div>
