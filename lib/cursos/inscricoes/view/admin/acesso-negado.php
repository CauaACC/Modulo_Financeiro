<?php
require_once(dirname(__FILE__)."/../../include/config.php");
FWA_UsuarioLogado($_SESSION['_cod_usuario'], 'true');
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Acesso Negado</h4></div>
    <div class="panel-body">
        <div class="alert alert-warning" style="margin-top:10px;">
            <i class="fa fa-lock"></i>
            <strong>Acesso restrito.</strong> Voc&ecirc; n&atilde;o possui permiss&atilde;o para acessar este m&oacute;dulo.
        </div>
        <button onclick="history.go(-1)" class="btn btn-primary btn-xs">Voltar</button>
    </div>
</div>
</div>
