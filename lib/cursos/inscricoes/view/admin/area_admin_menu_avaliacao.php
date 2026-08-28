<?php
// As entradas pras fases (Fase 1 recurso, Fase 2, etc) NAO ficam no submenu.
// O acesso eh via cards na tela AvaliacaoFases.php (botao "Avaliar" / "Consultar").
?>
<div class="row">
<div class="col-sm-12 submenu-area">

       <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)
           || FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'AvaliacaoFases') !== false || strpos($pg, 'GerenciarAvaliacaoInscrito') !== false || strpos($pg, 'GerenciarAvaliacaoFase2') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/AvaliacaoFases.php">Avalia&ccedil;&atilde;o</a>
       <?php } ?>
       <?php
       // Tela legada em fase de ajustes (gestao de distribuicao por area tematica).
       // Aparece quando a avaliacao do evento NAO e por candidato (logo, por arquivo/trabalho).
       // Restrita ao user 668 enquanto passa por revisao.
       if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)
           && $objEvConf->getTrabAvaliacao() == 1
           && $objEvConf->getTrabAvaliacaoPor() != 'CANDIDATO'
           && (int) $_SESSION['_cod_usuario'] === 668) { ?>
       <a class="submenu-btn <?= strpos($pg, 'consulta-avaliacao-trabalhos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/consulta-avaliacao-trabalhos-listagem.php">Avalia&ccedil;&atilde;o de Trabalhos</a>
       <?php } ?>
       <?php
       // Tela legada de avaliadores por trabalho — so faz sentido em eventos com
       // avaliacao de trabalho ativada (TrabAvaliacao=1 e TrabAvaliacaoPor != 'CANDIDATO').
       if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)
           && $objEvConf->getTrabAvaliacao() == 1
           && $objEvConf->getTrabAvaliacaoPor() != 'CANDIDATO') { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarTrabalhoAvaliador.php') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarTrabalhoAvaliador.php">Avaliadores (Trabalho)</a>
       <?php } ?>
       <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false) && $objEvConf->getTrabPremiacao() == 1) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarTrabalhoAvaliadorPremiacao') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarTrabalhoAvaliadorPremiacao.php">Premia&ccedil;&atilde;o</a>
       <?php } ?>
       <?php
       // "Painel de Trabalhos" antigo removido daqui — substituido pelo "Painel de Avaliação".
       // O link para a versao antiga fica dentro do Painel de Avaliação (com drill-down por situacao).
       if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONSULTAR_PAINEL_BORDO, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'DashboardAvaliacao') !== false || strpos($pg, 'consulta-dados-congresso-graficos-trabalhos') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/DashboardAvaliacao.php">Painel de Avaliação</a>
       <?php } ?>
       <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarEtapa') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarEtapa.php">Configurar Etapas</a>
       <?php } ?>
       <?php
       // Resultado Final: consolida pontos/status/classificacao finais da etapa
       // depois que avaliacao + recurso terminaram. Restrito a admin geral.
       if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarResultadoFinalFase') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarResultadoFinalFase.php&ordemEtapa=2">Resultado Final</a>
       <?php } ?>
       <?php
       // Cadastro de avaliadores com login (perfil SIS_AVALIACAO_INSCRITO).
       // Disponivel para quem gerencia avaliacao em eventos por candidato.
       // Posicionado por ultimo no submenu (a direita).
       if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)
           && $objEvConf->getTrabAvaliacaoPor() == 'CANDIDATO') { ?>
       <a class="submenu-btn <?= strpos($pg, 'GerenciarAvaliadores') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarAvaliadores.php">Avaliadores</a>
       <?php } ?>

</div>
</div>
