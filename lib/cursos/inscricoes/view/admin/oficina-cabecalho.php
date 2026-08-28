<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Gerenciar Atividades</h4></div>
        <div class="panel-body">
            <?php require_once(dirname(__FILE__)."/../../view/admin/oficina-seguranca.php"); ?>
            