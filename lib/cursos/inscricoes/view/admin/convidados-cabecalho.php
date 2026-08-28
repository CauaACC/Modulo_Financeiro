<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/convidados-seguranca.php");
if(!$_GET['COD_CONVIDADO']){
    $_GET['COD_CONVIDADO'] = $_GET['cod_convidado'];
}
?>
<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
//require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");
//require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_beneficios.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_usuarios.php");
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Gerenciar Convidados</h4></div>
    <div class="panel-body">
