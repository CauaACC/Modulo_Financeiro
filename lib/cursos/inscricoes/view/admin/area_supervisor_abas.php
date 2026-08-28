<?php
/**
 * Abas compartilhadas entre GerenciarSupervisores.php e GerenciarVinculoSupervisor.php.
 * Detecta a página atual via $pg e marca a aba ativa.
 */
$_abaSupervisores = (strpos($pg, 'GerenciarSupervisores') !== false && strpos($pg, 'GerenciarVinculoSupervisor') === false);
$_abaVinculos     = (strpos($pg, 'GerenciarVinculoSupervisor') !== false);
?>
<ul class="nav nav-tabs" style="margin-bottom:15px;">
    <li role="presentation" class="<?= $_abaSupervisores ? 'active' : '' ?>">
        <a href="index.php?content=view/admin/GerenciarSupervisores.php"><i class="fa fa-users"></i> Cadastro de Supervisores</a>
    </li>
    <li role="presentation" class="<?= $_abaVinculos ? 'active' : '' ?>">
        <a href="index.php?content=view/admin/GerenciarVinculoSupervisor.php"><i class="fa fa-sitemap"></i> V&iacute;nculos (PES e Col&eacute;gios)</a>
    </li>
</ul>
