<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$tituloPagina = 'Cadastrar Projeto/Evento';
$permSistema = enumSistema::SIS_INCLUIR_EVENTO;
$submenu = 'area_admin_menu_projetos.php';
require_once(dirname(__FILE__)."/../../view/admin/cabecalho-geral.php");
?>

<form method="POST" name="form" action="view/admin/evento-cadastro-incluir-grava.php" onsubmit="return criticar()">
<?php
$objEvAlt = new Evento();
include_once("evento-cadastro-campos.php");
?>

<div style="margin-top:12px;">
    <button type="submit" class="btn btn-primary btn-xs">Cadastrar</button>
    &nbsp;&nbsp;
    <button type="reset" class="btn btn-secondary btn-xs">Limpar</button>
</div>

</form>

</div></div></div>
