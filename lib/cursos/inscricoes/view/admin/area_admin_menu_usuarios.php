<?php
// Determinar qual aba está ativa baseado na página atual
$_abaUsrGestao     = (strpos($pg, 'GerenciarGestaoAcessos')   !== false);
$_abaUsrGrupos     = (strpos($pg, 'GerenciarGruposSistemas')  !== false);
$_abaUsrDashboard  = (strpos($pg, 'DashboardUsuariosGeral')   !== false);
$_abaUsrTrocaSenha = (strpos($pg, 'GerenciarTrocaSenhaInscrito') !== false);

// Se nenhuma aba detectada, default = gestao
if (!$_abaUsrGestao && !$_abaUsrGrupos && !$_abaUsrDashboard && !$_abaUsrTrocaSenha) $_abaUsrGestao = true;

// Dashboard Geral e restrito ao super admin (cod_usuario 668)
$_mostraDashboardGeral = (isset($_SESSION['_cod_usuario']) && (int) $_SESSION['_cod_usuario'] === 668);

// Aba interna ativa via parâmetro GET
$_abaInterna = isset($fwsGet_aba) ? $fwsGet_aba : '';
?>
<div class="row">
<div class="col-sm-12 submenu-area">

<?php if (FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) { ?>

       <style>
              .usr-tab { display:inline-block; padding:7px 16px; font-size:13px; color:#888; text-decoration:none; border-bottom:3px solid transparent; cursor:pointer; margin-right:4px; }
              .usr-tab:hover { color:#555; text-decoration:none; }
              .usr-tab.usr-tab-ativo { color:#333; font-weight:bold; border-bottom:3px solid #333; }
              .usr-tab:visited { color:#888; }
              .usr-tab.usr-tab-ativo:visited { color:#333; }
       </style>
       <div style="border-bottom:1px solid #ddd; margin-bottom:8px; padding-bottom:0;">
              <a class="usr-tab <?= $_abaUsrGestao ? 'usr-tab-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarGestaoAcessos.php">
                     <i class="fa fa-lock"></i> Gest&atilde;o de Acessos
              </a>
              <a class="usr-tab <?= $_abaUsrGrupos ? 'usr-tab-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarGruposSistemas.php">
                     <i class="fa fa-sitemap"></i> Grupos e Sistemas
              </a>
              <a class="usr-tab <?= $_abaUsrTrocaSenha ? 'usr-tab-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarTrocaSenhaInscrito.php">
                     <i class="fa fa-key"></i> Login de Inscritos
              </a>
              <?php if ($_mostraDashboardGeral) { ?>
              <a class="usr-tab <?= $_abaUsrDashboard ? 'usr-tab-ativo' : '' ?>" href="index.php?content=view/admin/DashboardUsuariosGeral.php"
                 title="Dashboard cross-event (acesso restrito)">
                     <i class="fa fa-bar-chart"></i> Dashboard Geral
              </a>
              <?php } ?>
       </div>

       <?php if ($_abaUsrGestao) { ?>
       <div class="usr-submenu-btns">
              <a class="submenu-btn submenu-btn-ativo" href="#" data-aba-alvo="tabEventosUsuario" data-titulo="Eventos do Usu&aacute;rio"><i class="fa fa-user"></i> Eventos do Usu&aacute;rio</a>
              <a class="submenu-btn" href="#" data-aba-alvo="tabUsuariosEvento" data-titulo="Usu&aacute;rios do Evento"><i class="fa fa-users"></i> Usu&aacute;rios do Evento</a>
              <a class="submenu-btn" href="#" data-aba-alvo="tabEventosGestor" data-titulo="Eventos do Gestor"><i class="fa fa-building"></i> Eventos do Gestor</a>
              <a class="submenu-btn" href="#" data-aba-alvo="tabUsuariosGestor" data-titulo="Usu&aacute;rios do Gestor"><i class="fa fa-id-card"></i> Usu&aacute;rios do Gestor</a>
       </div>
       <?php } ?>

       <?php if ($_abaUsrGrupos) { ?>
       <div class="usr-submenu-btns">
              <a class="submenu-btn submenu-btn-ativo" href="#" data-aba-alvo="tabGrupos" data-titulo="Grupos"><i class="fa fa-object-group"></i> Grupos</a>
              <a class="submenu-btn" href="#" data-aba-alvo="tabSistemas" data-titulo="Sistemas"><i class="fa fa-cogs"></i> Sistemas</a>
              <a class="submenu-btn" href="#" data-aba-alvo="tabModulos" data-titulo="Permiss&otilde;es"><i class="fa fa-key"></i> Permiss&otilde;es</a>
       </div>
       <?php } ?>

       <script>
       $(document).ready(function() {
              // Botões do submenu ativam as abas internas da página
              $('.usr-submenu-btns .submenu-btn').on('click', function(e) {
                     e.preventDefault();
                     var alvo = $(this).data('aba-alvo');
                     if (!alvo) return;

                     // Atualizar botão ativo no submenu
                     $('.usr-submenu-btns .submenu-btn').removeClass('submenu-btn-ativo');
                     $(this).addClass('submenu-btn-ativo');

                     // Ativar a aba interna da página
                     $('.nav-tabs li').removeClass('active');
                     $('.tab-content .tab-pane').removeClass('active in show');
                     $('a[href="#' + alvo + '"]').closest('li').addClass('active');
                     $('#' + alvo).addClass('active in show');

                     // Atualizar título da página
                     var titulo = $(this).data('titulo');
                     if (titulo) {
                            $('#tituloPaginaUsuarios').text(titulo);
                     }

                     // Disparar evento de aba para triggers existentes
                     $('a[href="#' + alvo + '"]').trigger('shown.bs.tab');
              });

              // Ativar aba via parâmetro GET (?aba=tabXxx)
              var _abaParam = '<?= $_abaInterna ?>';
              if (_abaParam) {
                     $('.usr-submenu-btns .submenu-btn').each(function() {
                            if ($(this).data('aba-alvo') === _abaParam) {
                                   $(this).trigger('click');
                            }
                     });
              }
       });
       </script>

<?php } ?>

</div>
</div>
