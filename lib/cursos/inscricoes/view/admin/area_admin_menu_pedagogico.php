<div class="row">
<div class="col-sm-12 submenu-area">

<?php if (!empty($_papelPedagogico)) { ?>
       <div style="display: flex; gap: 0;">
       <div style="flex: 2; border-right: 1px solid #ccc; padding-right: 12px;">
              <?php if ($_isAdminPedagogico) { ?>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarColegios') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarColegios.php">Col&eacute;gios</a>
              <?php } ?>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarAlunos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarAlunos.php">Alunos</a>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarGestaoAgente') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarGestaoAgente.php">Gest&atilde;o por Agente</a>
              <a class="submenu-btn <?= strpos($pg, 'ConsultarRegistrosPedagogicos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/ConsultarRegistrosPedagogicosAluno.php">Registros Di&aacute;rios</a>
              <?php if ($_isAdminPedagogico) { ?>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarAvaliacaoInicial') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarAvaliacaoInicial.php">Avalia&ccedil;&atilde;o de Repert&oacute;rio</a>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarLaudosAluno') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarLaudosAluno.php">Documentos</a>
              <?php } ?>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarAvaliacaoPES.php') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarAvaliacaoPES.php">Avalia&ccedil;&atilde;o PES por Supervisor</a>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarAvaliacaoPESProfessor') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarAvaliacaoPESProfessor.php">Avalia&ccedil;&atilde;o PES por Professor</a>
              <a class="submenu-btn <?= strpos($pg, 'RelatorioPresencaPedagogico') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/RelatorioPresencaPedagogico.php">Presen&ccedil;a</a>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarRegistroOrientacao') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarRegistroOrientacao.php">Registro e Orienta&ccedil;&otilde;es</a>
              <?php if ($_isAdminPedagogico) { ?>
              <a class="submenu-btn <?= (strpos($pg, 'GerenciarRegistroAtividadePedagogico.php') !== false) ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarRegistroAtividadePedagogico.php">Registro de Atividade</a>
              <?php } ?>
              <?php if ($_papelPedagogico === 'COORDENADOR') { ?>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarRegistroAtividadePedagogicoAvaliacao') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarRegistroAtividadePedagogicoAvaliacao.php">Registro de Atividade Avalia&ccedil;&atilde;o</a>
              <?php } ?>
       </div>
       <div style="flex: 1; padding-left: 12px;">
              <?php if ($_isAdminPedagogico) { ?>
              <a class="submenu-btn <?= strpos($pg, 'DashboardPedagogico') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/DashboardPedagogico.php">Painel</a>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarProgramacaoAvaliacao') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarProgramacaoAvaliacao.php">Calend&aacute;rio de Avalia&ccedil;&atilde;o</a>
              <a class="submenu-btn <?= (strpos($pg, 'GerenciarSupervisores') !== false || strpos($pg, 'GerenciarVinculoSupervisor') !== false) ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarSupervisores.php">Supervisores</a>
              <?php } ?>
       </div>
       </div>

<?php } ?>

</div>
</div>

<?php require_once(dirname(__FILE__)."/aviso-lgpd.php"); ?>
