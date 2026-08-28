<div class="row">
<div class="col-sm-12 submenu-area">

<?php
// Dois niveis: quem so acompanha (professor) ve o Relatorio; montar conteudo e'
// do coordenador. O menu apenas esconde -- o gate de verdade esta na view
// (curso-ead-seguranca.php) e no construtor dos controllers.
$_eadMonta     = FWA_temPermissao(FWA_permissoesCoordenarEAD());
$_eadAcompanha = FWA_temPermissao(FWA_permissoesAcompanharEAD());
if ($_eadAcompanha) { ?>
    <?php if ($_eadMonta) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarCurso.php') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarCurso.php">Cursos</a>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarCursoItem') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarCursoItem.php">Itens do Curso</a>
<?php /* Importar do Classroom: feature ainda nao deployada (importador bloqueado). Descomentar quando ImportarCursoClassroom.* forem publicados.
       <a class="submenu-btn <?= strpos($pg, 'ImportarCursoClassroom') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/ImportarCursoClassroom.php">Importar do Classroom</a>
*/ ?>
    <?php } ?>
       <a class="submenu-btn <?= strpos($pg, 'RelatorioCursoItem') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/RelatorioCursoItem.php">Relat&oacute;rio</a>
<?php } ?>

</div>
</div>
