<?php
$moduloAtivo = '';
$pg = isset($fwsGet_['content']) ? $fwsGet_['content'] : '';

// Projetos
if (strpos($pg, 'evento-cadastro-ok') !== false
    || strpos($pg, 'evento-cadastro-listar') !== false
    || strpos($pg, 'evento-cadastro-incluir') !== false
    || strpos($pg, 'GerenciarConsultaPagSeguro') !== false
) {
    $moduloAtivo = 'projetos';
}

// Transparencia
if (strpos($pg, 'GerenciarCargaTransparencia') !== false
    || strpos($pg, 'GerenciarRegistrosTransparencia') !== false
    || strpos($pg, 'DashboardTransparencia') !== false
    || strpos($pg, 'localizar_pagamentos') !== false
) {
    $moduloAtivo = 'transparencia';
}

// Inscrições
if (strpos($pg, 'listagem-inscritos') !== false
    || strpos($pg, 'inscritos-cadastro') !== false
    || strpos($pg, 'consulta-dados-congresso-listagem') !== false
    || strpos($pg, 'consulta-dados-congresso-graficos') !== false
    || strpos($pg, 'GerenciarInscritoDependentes') !== false
    || strpos($pg, 'GerenciarInscritoStatus') !== false
    || strpos($pg, 'GerenciarCargaPessoaUpload') !== false
    || strpos($pg, 'GerenciarRegistroAtividade') !== false
    || strpos($pg, 'GerenciarDesconto') !== false
    || strpos($pg, 'GerenciarVoucherEvento') !== false
    || strpos($pg, 'GerenciarInscritoApto') !== false
) {
    $moduloAtivo = 'inscricoes';
}

// Avaliação
if (strpos($pg, 'AvaliacaoFases') !== false
    || strpos($pg, 'GerenciarAvaliacaoInscrito') !== false
    || strpos($pg, 'GerenciarAvaliacaoFase2') !== false
    || strpos($pg, 'consulta-avaliacao-trabalhos') !== false
    || strpos($pg, 'GerenciarTrabalhoAvaliador') !== false
    || strpos($pg, 'GerenciarEtapa') !== false
    || strpos($pg, 'DashboardAvaliacao') !== false
) {
    $moduloAtivo = 'avaliacao';
}

// Configuração
if (strpos($pg, 'evento-cadastro-alterar') !== false
    || strpos($pg, 'evento-configurar') !== false
    || strpos($pg, 'perfil-cadastro') !== false
    || strpos($pg, 'oficina-cadastro') !== false
    || strpos($pg, 'area-tematica-cadastro') !== false
    || strpos($pg, 'GerenciarInscritoPerfil') !== false
    || strpos($pg, 'ligacao-convidados-areas-tematicas') !== false
    || strpos($pg, 'GerenciarAreaTematica') !== false
    || strpos($pg, 'GerenciarMunicipio') !== false
    || strpos($pg, 'GerenciarArquivoAtividade') !== false
    || strpos($pg, 'GerenciarNovoProjeto') !== false
    || strpos($pg, 'GerenciarEventoDivulgacao') !== false
    || strpos($pg, 'GerenciarModeloContrato') !== false
    || strpos($pg, 'GerenciarLiberacaoRegistro') !== false
    || strpos($pg, 'PainelCertificados') !== false
    || strpos($pg, 'ConfiguracaoCertificado') !== false
    || strpos($pg, 'PecasCertificado') !== false
    || strpos($pg, 'ProgramasCertificado') !== false
    || strpos($pg, 'CertificadoPorAtividade') !== false
    || strpos($pg, 'GerenciarVersoPrograma') !== false
) {
    $moduloAtivo = 'configuracao';
}

// Cursos EAD
if (strpos($pg, 'GerenciarCurso') !== false
    || strpos($pg, 'RelatorioCursoItem') !== false
    || strpos($pg, 'ImportarCursoClassroom') !== false
) {
    $moduloAtivo = 'cursos';
}

// Financeiro
if (strpos($pg, 'GerenciarLancamentos') !== false
    || strpos($pg, 'GerenciarPlanoContas') !== false
    || strpos($pg, 'GerenciarRateios') !== false
    || strpos($pg, 'GerenciarCentroCusto') !== false
    || strpos($pg, 'GerenciarFontesRecurso') !== false
    || strpos($pg, 'GerenciarItaushoplineUpload') !== false
    || strpos($pg, 'GerenciarPagamentoUpload') !== false
    || strpos($pg, 'GerenciarIngresso') !== false
    || strpos($pg, 'GerenciarPrestacaoServico') !== false
    || strpos($pg, 'GerenciarIsento') !== false
    || strpos($pg, 'carregarBoletosPagos') !== false
    || strpos($pg, 'GerenciarRetornoHutec') !== false
    || strpos($pg, 'GerenciarBoletoAvulso') !== false
) {
    $moduloAtivo = 'financeiro';
}

// Acadêmico
if (strpos($pg, 'GerenciarAlunos') !== false
    || strpos($pg, 'GerenciarGestaoAgente') !== false
    || strpos($pg, 'GerenciarColegios') !== false
    || strpos($pg, 'ConsultarRegistrosPedagogicos') !== false
    || strpos($pg, 'DashboardPedagogico') !== false
) {
    $moduloAtivo = 'academico';
}

// Comunicados
if (strpos($pg, 'EmailTemplate') !== false
    || strpos($pg, 'MsgAviso') !== false
) {
    $moduloAtivo = 'comunicados';
}

// Usuários
if (strpos($pg, 'login-cadastro') !== false
    || strpos($pg, 'ligacao-usuarios') !== false
    || strpos($pg, 'GerenciarAcessos') !== false
    || strpos($pg, 'GerenciarModulos') !== false
    || strpos($pg, 'GerenciarGestaoAcessos') !== false
    || strpos($pg, 'convidados-cadastro') !== false
    || strpos($pg, 'GerenciarGruposSistemas') !== false
    || strpos($pg, 'GerenciarTrocaSenhaInscrito') !== false
) {
    $moduloAtivo = 'usuarios';
}

// Gestor (telas que NAO pertencem a um evento)
if (strpos($pg, 'GerenciarCertificadosGestor') !== false
) {
    $moduloAtivo = 'gestor';
}

// Outros
if (strpos($pg, 'GerenciarAssociado') !== false
    || strpos($pg, 'GerenciarInteressado') !== false
    || strpos($pg, 'GerenciarMidiaUpload') !== false
    || strpos($pg, 'GerenciarEtapa') !== false
    || strpos($pg, 'GerenciarFeriado') !== false
    || strpos($pg, 'GerenciarCalendarioBase') !== false
    || strpos($pg, 'GerenciarCertificadoEvento') !== false
    || strpos($pg, 'GerenciarProjeto') !== false
    || strpos($pg, 'GerenciarProposta') !== false
    || strpos($pg, 'GerenciarRegistroAtividadeAvaliacao') !== false
) {
    $moduloAtivo = 'outros';
}

// Vendas (modulo comercial: catalogo, vendas do canal e emissao de nota).
// Produtos e Emissores de NF sairam de "Outros" para ca — sao operacao do
// mesmo modulo, nao cadastros avulsos.
if (strpos($pg, 'GerenciarVendas') !== false
    || strpos($pg, 'GerenciarProdutos') !== false
    || strpos($pg, 'GerenciarNotaFiscalEmissor') !== false
) {
    $moduloAtivo = 'vendas';
}
?>
<div class="row">
<div class="col-sm-12 submenu-area">
  <div class="modulo-nav">
    <div class="modulo-links">
    <a href="<?= WB_URL_VIEW ?>?content=view/admin/evento-cadastro-listar.php"
       class="modulo <?= $moduloAtivo=='projetos' ? 'modulo-ativo' : '' ?>">Eventos/Projetos</a>
    <?php if ($moduloAtivo != 'projetos') { ?>
    <a href="index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php"
       class="modulo <?= $moduloAtivo=='inscricoes' ? 'modulo-ativo' : '' ?>">Inscri&ccedil;&otilde;es</a>
    <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_EVENTO, 5, false, false)) { ?>
    <a href="index.php?content=view/admin/evento-cadastro-alterar.php&cod_evento=<?= COD_EVENTO ?>"
       class="modulo <?= $moduloAtivo=='configuracao' ? 'modulo-ativo' : '' ?>">Configura&ccedil;&atilde;o</a>
    <?php } ?>
    <?php
    // Cursos EAD: aba propria, so aparece quando o evento usa o modulo (curso_habilitado=1)
    $_cursoHabMenu = 0;
    try { if (isset($objEvConf)) $_cursoHabMenu = (int) $objEvConf->getCursoHabilitado(); } catch (Exception $e) {}
    // Destino por papel: quem so acompanha (professor) cai no Relatorio -- a tela
    // de Cursos exige coordenador e responderia "sem permissao".
    $_eadMontaMenu = FWA_temPermissao(FWA_permissoesCoordenarEAD());
    if ($_cursoHabMenu == 1 && FWA_temPermissao(FWA_permissoesAcompanharEAD())) { ?>
    <a href="index.php?content=view/admin/<?= $_eadMontaMenu ? 'GerenciarCurso.php' : 'RelatorioCursoItem.php' ?>"
       class="modulo <?= $moduloAtivo=='cursos' ? 'modulo-ativo' : '' ?>">Cursos EAD</a>
    <?php } ?>
    <?php
    // Menu Avaliacao aparece se usuario tem permissao E o evento tem algum modulo
    // de avaliacao/submissao habilitado. Evento que so tem TrabSubmissao=1 (envio de documento
    // sem avaliar) ainda precisa do painel para ver estatisticas dos documentos enviados.
    $_eventoTemAvaliacao = ($objEvConf->getTrabAvaliacao() == 1)
                        || ($objEvConf->getTrabPremiacao() == 1)
                        || ($objEvConf->getTrabSubmissao() == 1)
                        || ($objEvConf->getRegistroAtividade() == 1);
    if ((FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)
        || FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false))
        && $_eventoTemAvaliacao) { ?>
    <a href="index.php?content=view/admin/AvaliacaoFases.php"
       class="modulo <?= $moduloAtivo=='avaliacao' ? 'modulo-ativo' : '' ?>">Avalia&ccedil;&atilde;o</a>
    <?php } ?>
    <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)) { ?>
    <a href="index.php?content=view/admin/GerenciarRelatoriosFinanceiros.php"
       class="modulo <?= $moduloAtivo=='financeiro' ? 'modulo-ativo' : '' ?>">Financeiro</a>
    <?php } ?>
    <?php /* Vendas so em evento classificado como Loja (aba Projeto > Tipo de projeto) */ ?>
    <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)
           && $objEvConf->getTipoProjeto() == 'LOJA') { ?>
    <a href="index.php?content=view/admin/GerenciarVendas.php"
       class="modulo <?= $moduloAtivo=='vendas' ? 'modulo-ativo' : '' ?>">Vendas</a>
    <?php } ?>
    <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)
           || FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_CARGA_TRANSPARENCIA, 5, false, false)) { ?>
    <a href="index.php?content=view/admin/GerenciarCargaTransparencia.php"
       class="modulo <?= $moduloAtivo=='transparencia' ? 'modulo-ativo' : '' ?>">Transpar&ecirc;ncia</a>
    <?php } ?>
    <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_PEDAGOGICO, 5, false, false)) { ?>
    <a href="index.php?content=view/admin/GerenciarAlunos.php"
       class="modulo <?= $moduloAtivo=='academico' ? 'modulo-ativo' : '' ?>">Acad&ecirc;mico</a>
    <?php } ?>
    <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)
        || FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_INSCRITO, 5, false, false)) { ?>
    <a href="index.php?content=view/admin/GerenciarEmailTemplate.php"
       class="modulo <?= $moduloAtivo=='comunicados' ? 'modulo-ativo' : '' ?>">Comunicados</a>
    <?php } ?>
    <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) { ?>
    <a href="index.php?content=view/admin/GerenciarAssociado.php"
       class="modulo <?= $moduloAtivo=='outros' ? 'modulo-ativo' : '' ?>">Outros</a>
    <?php } ?>
    <?php } ?>
    <?php // Gestor e Usuarios ficam FORA do if($moduloAtivo!='projetos'): sao telas que
          // nao dependem de evento carregado, logo aparecem em qualquer contexto. ?>
    <?php if (FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) { ?>
    <a href="<?= WB_URL_VIEW ?>?content=view/admin/GerenciarCertificadosGestor.php"
       class="modulo <?= $moduloAtivo=='gestor' ? 'modulo-ativo' : '' ?>">Gestor</a>
    <?php } ?>
    <?php if (FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) { ?>
    <a href="<?= WB_URL_VIEW ?>?content=view/admin/GerenciarGestaoAcessos.php"
       class="modulo <?= $moduloAtivo=='usuarios' ? 'modulo-ativo' : '' ?>">Usu&aacute;rios</a>
    <?php } ?>
    </div>
  </div>
</div>
<?php if ($moduloAtivo == 'transparencia') { require_once(dirname(__FILE__)."/area_admin_menu_transparencia.php"); } ?>
<?php if ($moduloAtivo == 'cursos') { require_once(dirname(__FILE__)."/area_admin_menu_cursos.php"); } ?>
</div>
