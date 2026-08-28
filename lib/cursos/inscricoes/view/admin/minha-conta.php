<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if (!@$_SESSION['_logado'] || !@$_SESSION['_administrativo']) {
    $url = WB_URL_VIEW . "?content=view/admin/login.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
    exit();
}
?>

<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><i class="fa fa-user"></i> Minha Conta</h4>
    </div>
    <div class="panel-body">

        <p style="font-size:16px; margin-bottom:20px;">
            Ol&aacute;, <strong><?php echo htmlspecialchars($_SESSION['_login']); ?></strong>
        </p>

        <div class="row">
            <div class="col-sm-4">
                <a href="<?php echo WB_URL_VIEW ?>?content=view/admin/login-forcar-troca-senha.php" style="text-decoration:none; color:inherit;">
                    <div class="panel panel-default" style="cursor:pointer;">
                        <div class="panel-body text-center">
                            <i class="fa fa-key fa-3x text-primary" style="margin-bottom:10px;"></i>
                            <h5><strong>Alterar Senha</strong></h5>
                            <p class="text-muted" style="font-size:12px;">Trocar sua senha de acesso</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-4">
                <a href="<?php echo WB_URL_VIEW ?>?content=view/admin/meus-dados.php" style="text-decoration:none; color:inherit;">
                    <div class="panel panel-default" style="cursor:pointer;">
                        <div class="panel-body text-center">
                            <i class="fa fa-id-card fa-3x text-primary" style="margin-bottom:10px;"></i>
                            <h5><strong>Meus Dados</strong></h5>
                            <p class="text-muted" style="font-size:12px;">Atualizar nome e celular</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

    </div>
</div>
