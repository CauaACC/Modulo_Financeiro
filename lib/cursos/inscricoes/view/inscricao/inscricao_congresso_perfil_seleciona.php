<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$fws_['e_mail'] = $fws_['e_mail']==''? $_GET['e_mail'] : $fws_['e_mail'];
$fws_['cpf']    = $fws_['cpf']   ==''? $_GET['cpf']    : $fws_['cpf'];
$debug = false;

require_once 'inscricao_congresso_tit_area_inscrito.php';
?>
<div class="panel-group">
    <div class="panel panel-default">       
        <div class="panel-body">
            <?php
            GerenciarProcessoInscricaoAC::passo_01($fws_, $debug);
            ?>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
            <br>
        </div>
    </div>
</div>