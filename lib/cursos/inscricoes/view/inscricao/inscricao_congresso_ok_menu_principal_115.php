<?php require_once 'inscricao_congresso_ok_menu_principal_style.php'; ?>

<?php
/**
 * Regras de exibição das seções:
 *
 * INSCRITO                        → Documentos (edital)
 * CONVOCADO / Classificado 1-150  → Documentos + Docs Contratação
 * PARTICIPANDO (perfil 614)       → Documentos + Registro Pedagógico
 * DESISTENTE                      → Documentos (somente leitura)
 * Exceção 40920                   → Docs Contratação + Registro Pedagógico
 */
$objMsgAviso = new MsgAviso();
$objMsgAviso->setCodEvento($objEv->getCodEvento());
$objMsgAviso->setCodInscrito($objInscrito->getCodInscrito());
$objMsgAviso->setCodPerfil($objInscrito->getCodPerfil());

$statusProcesso = $objInscrito->getStatusProcesso();
$codPerfil = $objInscrito->getCodPerfil();
$codInscrito = $objInscrito->getCodInscrito();

// Buscar classificação final do inscrito para regra de exibição
$classificacaoFinal = 0;
try {
    $objAvalInscMenu = new AvaliacaoInscrito();
    $objAvalInscMenu->setCodEvento($objInscrito->getCodEvento());
    $objAvalInscMenu->setCodInscrito($codInscrito);
    $objAvalInscMenu->carregarListaAvaliacaoInscritoByIdInscritoProRedeFase2();
    $listaAval = $objAvalInscMenu->getListaAvaliacaoInscrito();
    if ($listaAval) {
        foreach ($listaAval as $objAvalItem) {
            $classificacaoFinal = intval($objAvalItem->getClassificacaoFinal());
            break;
        }
    }
} catch (Exception $e) {
    // Sem avaliação disponível
}

$isClassificado = ($classificacaoFinal >= 1 && $classificacaoFinal <= 150);
$isExcecao = ($codInscrito == '40920');

// Docs contratação: classificado até 150º, ou CONVOCADO, ou exceção individual
$exibirDocsContratacao = ($isClassificado || $statusProcesso == 'CONVOCADO' || $isExcecao)
                           && $statusProcesso != 'DESISTENTE';

// Registro pedagógico: usa regra centralizada em VinculoAgenteAluno
$exibirRegistroPedagogico = VinculoAgenteAluno::isAgentePedagogico($codInscrito, $statusProcesso, $codPerfil);

// Orientacoes recebidas do supervisor (so exibe se houver pelo menos 1 registro)
$totalOrientacoes = 0;
$orientacoesNaoLidas = 0;
try {
    $objReg = new RegistroOrientacao();
    $totalOrientacoes = count($objReg->getOrientacoesRecebidas($codInscrito));
    if ($totalOrientacoes > 0) {
        $orientacoesNaoLidas = $objReg->contarOrientacoesNaoLidas($codInscrito);
    }
} catch (Exception $e) {
    // silencioso: se der erro, simplesmente nao exibe
}
$exibirOrientacoes = $totalOrientacoes > 0;
?>

<div id="tela-inicial-container">
    <div id="home" class="section home">
        <div class="">
            <div class="menu-option" onclick="showSection('avisos')">
                <i class="glyphicon glyphicon-bell"></i>
                <div class="menu-text">
                    <span>Avisos <?php echo '(' . $objMsgAviso->getTotalMsgAvisosForAreaInscrito() . ')'; ?></span>
                    <p>Consulte os avisos e novidades importantes para o seu processo.</p>
                </div>
            </div>
            <div class="menu-option" onclick="showSection('inscricao')">
                <i class="glyphicon glyphicon-pencil"></i>
                <div class="menu-text">
                    <span>Documentos</span>
                    <p>Consulte documentos, editais e recursos do projeto.</p>
                </div>
            </div>
            <?php if ($objEvConf->getHabilitarModelosContrato()) { ?>
            <div class="menu-option" onclick="showSection('contratosModelos')">
                <i class="glyphicon glyphicon-file"></i>
                <div class="menu-text">
                    <span>Contratos e Documentos</span>
                    <p>Baixe seus contratos preenchidos com seus dados cadastrais.</p>
                </div>
            </div>
            <?php } ?>
            <?php if ($exibirDocsContratacao) { ?>
            <div class="menu-option" onclick="showSection('docsContratacao')">
                <i class="glyphicon glyphicon-folder-open"></i>
                <div class="menu-text">
                    <span>Documentos para Contratação</span>
                    <p>Envie os documentos obrigatórios para o processo de contratação.</p>
                </div>
            </div>
            <?php } ?>
<?php if ($exibirRegistroPedagogico) { ?>
            <div class="menu-option" onclick="showSection('registroPedagogico')">
                <i class="glyphicon glyphicon-book"></i>
                <div class="menu-text">
                    <span>Monitoramento Comportamental</span>
                    <p>Registre e acompanhe o comportamento dos alunos.</p>
                </div>
            </div>
            <?php } ?>
            <?php if ($exibirOrientacoes) { ?>
            <div class="menu-option" onclick="carregarOrientacoesSupervisor(); showSection('orientacoesSupervisor')">
                <i class="glyphicon glyphicon-comment"></i>
                <div class="menu-text">
                    <span>Orienta&ccedil;&otilde;es do Supervisor <span id="badgeNaoLidasMenu" style="<?php echo $orientacoesNaoLidas > 0 ? '' : 'display:none;' ?> color:#f0ad4e;"><?php echo $orientacoesNaoLidas > 0 ? '(' . $orientacoesNaoLidas . ' nova' . ($orientacoesNaoLidas > 1 ? 's' : '') . ')' : '' ?></span></span>
                    <p>Consulte as orienta&ccedil;&otilde;es recebidas dos seus supervisores.</p>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- Seção de Avisos -->
    <div id="avisos" class="section">
        <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Avisos
         <?php echo '(' . $objMsgAviso->getTotalMsgAvisosForAreaInscrito() . ')'; ?></h2>
        <?php require_once('inscricao_congresso_avisos.php'); ?>
        <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
    </div>

    <!-- Seção de Documentos (editais, recursos, avaliação) -->
    <div id="inscricao" class="section">
        <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Documentos</h2>
        <?php require_once 'inscricao_trabalhos_consulta_trabalhos_submetidos_edital_recurso_prorede2-fase-2.php'; ?>
        <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
    </div>

    <!-- Seção de Contratos e Documentos (modelos preenchidos) -->
    <?php if ($objEvConf->getHabilitarModelosContrato()) { ?>
    <div id="contratosModelos" class="section">
        <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Contratos e Documentos</h2>
        <?php require_once('inscricao_congresso_contratos_modelos.php'); ?>
        <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
    </div>
    <?php } ?>

    <!-- Seção de Documentos para Contratação -->
    <?php if ($exibirDocsContratacao) { ?>
    <div id="docsContratacao" class="section">
        <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Documentos para Contratação</h2>
        <?php require_once('inscricao_documentos_complementares_contratacao.php'); ?>
        <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
    </div>
    <?php } ?>

<!-- Seção de Registro Comportamental -->
    <?php if ($exibirRegistroPedagogico) { ?>
    <div id="registroPedagogico" class="section">
        <!-- <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Monitoramento Comportamental</h2> -->
        <h2>Monitoramento Comportamental</h2>
        <?php require_once(dirname(__FILE__).'/../../view/admin/GerenciarRegistroPedagogico.php'); ?>
        <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
    </div>
    <?php } ?>

    <!-- Seção de Orientações do Supervisor -->
    <?php if ($exibirOrientacoes) { ?>
    <div id="orientacoesSupervisor" class="section">
        <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Orienta&ccedil;&otilde;es do Supervisor</h2>
        <?php require_once('inscricao_orientacoes_supervisor.php'); ?>
        <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
    </div>
    <script>
    // Atualiza o contador do card ao marcar orientacoes como lidas
    function atualizarContadorOrientacoes() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarOrientacaoInscrito', 'm': 'contarNaoLidas', '_submit_check': '1'
        }, function(resp) {
            if (!resp || !resp.statusMessage) return;
            var n = parseInt(resp.naoLidas, 10) || 0;
            if (n > 0) {
                $('#badgeNaoLidasMenu').text('(' + n + ' nova' + (n > 1 ? 's' : '') + ')').show();
            } else {
                $('#badgeNaoLidasMenu').hide();
            }
        }, 'json');
    }
    </script>
    <?php } ?>
</div>
