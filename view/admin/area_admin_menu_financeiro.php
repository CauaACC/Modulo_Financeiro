<?php
// Menu do módulo financeiro
$pg = $_GET['content'] ?? '';
?>
<div class="row">
    <div class="col-sm-12 submenu-area" style="margin-bottom: 15px;">
        <a class="btn btn-sm <?php echo strpos($pg, 'GerenciarLancamentosContabeis') !== false ? 'btn-primary' : 'btn-default' ?>"
           href="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarLancamentosContabeis.php">Lançamentos</a>

        <a class="btn btn-sm <?php echo strpos($pg, 'GerenciarPlanoContas') !== false ? 'btn-primary' : 'btn-default' ?>"
           href="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarPlanoContas.php">Plano de Contas</a>

        <a class="btn btn-sm <?php echo strpos($pg, 'GerenciarRateios') !== false ? 'btn-primary' : 'btn-default' ?>"
           href="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarRateios.php">Rateios</a>

        <a class="btn btn-sm <?php echo strpos($pg, 'GerenciarCentroCusto') !== false ? 'btn-primary' : 'btn-default' ?>"
           href="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarCentroCusto.php">Centro de Custo</a>

        <a class="btn btn-sm <?php echo strpos($pg, 'GerenciarFontesRecurso') !== false ? 'btn-primary' : 'btn-default' ?>"
           href="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarFontesRecurso.php">Fontes de Recurso</a>
    </div>
</div>
