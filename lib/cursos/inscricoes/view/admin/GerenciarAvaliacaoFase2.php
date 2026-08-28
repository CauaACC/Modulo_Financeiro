<?php
/**
 * GerenciarAvaliacaoFase2.php
 *
 * Tela da avaliacao da 2a Fase (Anexo II / pontuacao da Comissao Avaliadora).
 *
 * Sem codInscrito na URL: mostra listagem dos habilitados Fase 1 final
 *   com filtros (Pendentes / Avaliados / Todos).
 * Com codInscrito na URL: mostra form de avaliacao (Anexo II + classif).
 *
 * Reaproveita logica do GerenciarAvaliacaoInscritoAC (Fase 1):
 *   - formularioAvaliacaoProRede2() — Anexo II
 *   - carregarDocumentos() — lista de docs anexados pelo inscrito
 */

// SEGURANCA: deve rodar SEMPRE, antes de qualquer logica/renderizacao.
// O include faz FWA_Autenticacao e redireciona pro login se nao houver sessao valida.
require_once(dirname(__FILE__) . "/../../view/admin/consulta-avaliacao-inscrito-seguranca.php");

$objSRV       = new GerenciarAvaliacaoFase2AC();
$objSRVFase1  = new GerenciarAvaliacaoInscritoAC(); // helpers reutilizados (form anexo II + docs)
$fws_['codInscrito'] = $fwsGet_['codInscrito'] ?? '';
$temCodInscrito = !empty($fws_['codInscrito']);

// Verifica se a Fase 2 esta habilitada (linha em tab_avaliacao_etapa
// com ordem_etapa=2 AND ativa=1 AND status='ATIVADA').
$codEtapaFase2Ativa = $objSRV->getCodEtapaFase2Ativa();
$fase2Indisponivel  = empty($codEtapaFase2Ativa);

// Detecta se a Fase 2 esta CONCLUIDA (gestor consultando read-only).
$etapaConcluida = false;
if ($fase2Indisponivel) {
    $objEtapaCheck = new Etapa();
    $codEtapaConcluida = (int)$objEtapaCheck->getEtapaByOrdem(2, false);
    if ($codEtapaConcluida > 0) {
        $objEtapaCheck->setCodEtapa($codEtapaConcluida);
        $objEtapaCheck->getEtapaById();
        if ($objEtapaCheck->isConcluida()) {
            $etapaConcluida = true;
            $codEtapaFase2Ativa = $codEtapaConcluida; // permite carregar pra consulta
            $fase2Indisponivel = false;
        }
    }
}

// Janelas configuradas na etapa (analise inicial + analise de recurso).
// Datas vazias = sem restricao (modo legado, tudo aberto).
$foraDaJanelaAnalise  = false;
$dentroJanelaRecurso  = false;
$janelaAnaliseIni     = '';
$janelaAnaliseFim     = '';
$janelaRecursoIni     = '';
$janelaRecursoFim     = '';
if (!$fase2Indisponivel) {
    $objEtapaJanela = new Etapa();
    $objEtapaJanela->setCodEtapa($codEtapaFase2Ativa);
    $objEtapaJanela->getEtapaById();
    $janelaAnaliseIni = trim((string)$objEtapaJanela->getAnaliseDatIni());
    $janelaAnaliseFim = trim((string)$objEtapaJanela->getAnaliseDatFim());
    $janelaRecursoIni = trim((string)$objEtapaJanela->getAnaliseRecursoDatIni());
    $janelaRecursoFim = trim((string)$objEtapaJanela->getAnaliseRecursoDatFim());
    $agoraTs = time();
    if ($janelaAnaliseIni !== '' && $janelaAnaliseFim !== '') {
        $iniTs = strtotime($janelaAnaliseIni);
        $fimTs = strtotime($janelaAnaliseFim);
        if ($iniTs && $fimTs && ($agoraTs < $iniTs || $agoraTs > $fimTs)) {
            $foraDaJanelaAnalise = true;
        }
    }
    if ($janelaRecursoIni !== '' && $janelaRecursoFim !== '') {
        $iniTs = strtotime($janelaRecursoIni);
        $fimTs = strtotime($janelaRecursoFim);
        if ($iniTs && $fimTs && $agoraTs >= $iniTs && $agoraTs <= $fimTs) {
            $dentroJanelaRecurso = true;
        }
    }
}

// Modo de operacao:
//   AVALIACAO -> editar avaliacao inicial (Anexo II + parecer + status)
//   RECURSO   -> editar so resposta ao recurso (recursoParecer + *_apos_recurso)
//   CONSULTA  -> tudo read-only (etapa Concluida ou fora de ambas janelas)
$modoAtual = 'AVALIACAO';
if ($etapaConcluida) {
    $modoAtual = 'CONSULTA';
} elseif ($dentroJanelaRecurso) {
    $modoAtual = 'RECURSO';
} elseif ($foraDaJanelaAnalise) {
    $modoAtual = 'CONSULTA';
}
$bloqueioEdicao = ($modoAtual === 'CONSULTA');

if (!$fase2Indisponivel && $temCodInscrito) {
    $objAI = $objSRV->carregarAvaliacaoFase2ByIdInscrito($fws_, $debug);

    // Configura flags pro form Anexo II saber o que mostrar.
    $objEvConf->setTrabAvaliacaoComplementar(0);
    $objEvConf->setMostrarRecursos(0);
    $objEvConf->setHabilitarClassificacaoStatus(0);
    $objEvConf->setHabilitarParecerAvaliador(1);
    $objEvConf->setHabilitarPontosAposRecurso(0);
    $objEvConf->setHabilitarClassificacaoAposRecurso(0);
    $objEvConf->setHabilitarPontos1Avaliacao(0);
    $objEvConf->setFaseAvaliacao('AVALIACAO');

    // Marca SELECIONADO no historico (reserva nao-exclusiva) e busca outros
    // avaliadores ativos pra exibir warning informativo. So se etapa NAO concluida.
    $outrosAvaliadores = [];
    if (!$etapaConcluida) {
        $objHistAC = new HistoricoAvaliacaoInscritoAC();
        $fws_['codEtapa'] = $codEtapaFase2Ativa;
        $objHistAC->iniciarAvaliacao($fws_, $debug);
        $outrosAvaliadores = $objHistAC->listarOutrosAvaliadoresAtivos($fws_, $debug);
    }
}
?>

<script type="text/javascript">
var etapaConcluida = <?php echo !empty($etapaConcluida) ? 'true' : 'false'; ?>;
var modoAtualFase2 = <?php echo json_encode($modoAtual ?? 'AVALIACAO'); ?>;

/**
 * Padrao do projeto: marca .form-group ancestral com .has-error e anexa
 * <span class="msg-campo help-block"> com a mensagem. Reaproveita o mesmo
 * padrao do modal de Etapa (GerenciarEtapa.php).
 */
function marcarCampoErro(seletor, mensagem) {
    var $el = $(seletor);
    var $grp = $el.closest('.form-group');
    $grp.addClass('has-error');
    $grp.find('.msg-campo').remove();
    $grp.append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">' + mensagem + '</span>');
    $el.off('.campoErro').on('input.campoErro change.campoErro', function() {
        $(this).closest('.form-group').removeClass('has-error').find('.msg-campo').remove();
    });
    try { $el.focus(); } catch (e) {}
}
function limparErrosCampos() {
    $('#formAvaliacaoFase2 .form-group.has-error').removeClass('has-error');
    $('#formAvaliacaoFase2 .msg-campo').remove();
    $('#formAvaliacaoFase2 :input').off('.campoErro');
    $('#mensagem').html('');
}

/**
 * Aplica disable/readonly conforme modoAtualFase2:
 *   AVALIACAO -> campos da avaliacao inicial editaveis; recurso desativado
 *   RECURSO   -> recurso editavel; avaliacao inicial desativada
 *   CONSULTA  -> tudo desativado
 * Campos sao agrupados por classe CSS (.grp-avaliacao-inicial / .grp-avaliacao-recurso)
 * — evita usar <fieldset> que quebra layout de col-sm-X do Bootstrap.
 */
function aplicarModoSomenteLeituraFase2() {
    var modo = (typeof modoAtualFase2 !== 'undefined') ? modoAtualFase2 : 'AVALIACAO';
    if (modo === 'AVALIACAO') {
        $('#formAvaliacaoFase2 .grp-avaliacao-recurso :input').prop('disabled', true);
    } else if (modo === 'RECURSO') {
        $('#formAvaliacaoFase2 .grp-avaliacao-inicial :input').prop('disabled', true);
    } else { // CONSULTA
        $('#formAvaliacaoFase2 :input').not('button[onclick^="window.location"], a').prop('disabled', true);
        $('#btnSalvarFase2').hide();
    }
}

$(document).ready(function() {
    aplicarModoSomenteLeituraFase2();
    $('#filtroAvaliacao').on('change', function() {
        carregarTabelaFase2();
    });

    // Gera edital DOCX respeitando o filtro ativo. Redireciona pra rota do
    // CommandAC com dispara o download (window.location preserva sessao + cookies).
    $('#btnGerarEditalFase2').on('click', function() {
        var filtro = $('#filtroAvaliacao').val() || 'todos';
        var url = '<?php echo WB_URL_COMMAND ?>'
                + '&c=GerenciarEditalPublicacao'
                + '&m=gerarEditalDOCX'
                + '&ordemEtapa=2'
                + '&filtro=' + encodeURIComponent(filtro)
                + '&_submit_check=1';
        window.location.href = url;
    });

    $('#btnSalvarFase2').click(function() {
        salvarAvaliacaoFase2();
    });

    // Espelha a pontuacao total do Anexo II para o card destacado e
    // pro hidden input que vai pro banco. O Anexo II atualiza o
    // span #total_comissao via calcLinha; aqui apenas ouvimos esse elemento.
    sincronizarPontuacao();
    var totalEl = document.getElementById('total_comissao');
    if (totalEl) {
        var obs = new MutationObserver(sincronizarPontuacao);
        obs.observe(totalEl, { childList: true, characterData: true, subtree: true });
        // Backup: tambem atualiza ao digitar em qualquer input do anexo
        $(document).on('input change', 'input[name^="qtd["]', sincronizarPontuacao);
    }
});

function sincronizarPontuacao() {
    var totalEl = document.getElementById('total_comissao');
    if (!totalEl) return;
    var valor = (totalEl.innerText || totalEl.textContent || '0').trim();
    // Espelha o total calculado pelo Anexo II no hidden input que vai pro banco.
    var hidden = document.getElementById('pontosTotalConfirmado');
    if (hidden) hidden.value = valor.replace(',', '.');
}

function carregarTabelaFase2() {
    var filtro = $('#filtroAvaliacao').val() || 'todos';
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarAvaliacaoFase2',
        'm': 'carregarTabelaFase2',
        'filtroAvaliacao': filtro,
        '_submit_check': '1'
    }, function(html) {
        $('#tabelaFase2 tbody').html(html);
    });
}

function acessarAvaliacaoFase2(codInscrito) {
    window.location.href = 'index.php?content=view/admin/GerenciarAvaliacaoFase2.php&codInscrito=' + codInscrito;
}

function voltarParaListaFase2() {
    window.location.href = 'index.php?content=view/admin/GerenciarAvaliacaoFase2.php';
}

function liberarReservaAvaliacao() {
    var codInscrito = $('#codInscrito').val();
    var codEtapa    = <?php echo (int)($codEtapaFase2Ativa ?? 0) ?>;
    if (!codInscrito || !codEtapa) {
        $('#mensagem').html('<div class="alert alert-warning">Inscrito ou etapa inv&aacute;lidos.</div>');
        return;
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'HistoricoAvaliacaoInscrito',
            'm': 'liberarReserva',
            'codInscrito': codInscrito,
            'codEtapa': codEtapa,
            '_submit_check': '1'
        },
        dataType: 'json',
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage == '1') {
                voltarParaListaFase2();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function salvarAvaliacaoFase2() {
    limparErrosCampos();
    if ($('#codInscrito').val() == '') {
        $('#mensagem').html('<div class="alert alert-warning">Cidad&atilde;o n&atilde;o carregado.</div>');
        return false;
    }
    if (modoAtualFase2 === 'RECURSO') {
        // Modo recurso: valida os 3 campos do bloco recurso (status + parecer + pontos).
        // Marca TODOS os campos faltantes ao mesmo tempo (sem early return).
        var faltando = [];
        if ($('#classificacaoStatusAposRecurso').val() == '') {
            marcarCampoErro('#classificacaoStatusAposRecurso', 'Informe o status da classifica&ccedil;&atilde;o ap&oacute;s o recurso.');
            faltando.push('status');
        }
        if ($.trim($('#recursoParecer').val()) === '') {
            marcarCampoErro('#recursoParecer', 'Informe o parecer do avaliador para o recurso.');
            faltando.push('parecer');
        }
        var pAR = $.trim($('#pontosTotalAposRecurso').val());
        if (pAR === '' || isNaN(parseFloat(pAR))) {
            marcarCampoErro('#pontosTotalAposRecurso', 'Informe a pontua&ccedil;&atilde;o ap&oacute;s o recurso (n&uacute;mero).');
            faltando.push('pontos');
        }
        if (faltando.length > 0) {
            // Foco no primeiro pendente (marcarCampoErro ja move foco, mas
            // como chamamos varias vezes, o foco final pode ser o ultimo —
            // forca pra primeiro pra UX coerente).
            var primeiroSel = {
                status: '#classificacaoStatusAposRecurso',
                parecer: '#recursoParecer',
                pontos: '#pontosTotalAposRecurso'
            }[faltando[0]];
            try { $(primeiroSel).focus(); } catch (e) {}
            return false;
        }
    } else {
        // Modo avaliacao inicial
        if ($('#classificacaoStatus').val() == '') {
            marcarCampoErro('#classificacaoStatus', 'Informe o status da classifica&ccedil;&atilde;o (Habilitado / N&atilde;o Habilitado).');
            return false;
        }
    }

    $('#formAvaliacaoFase2').ajaxSubmit({
        target: '#mensagem',
        dataType: 'json',
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage == '1') {
                $('#mensagem').html('<div class="alert alert-success" style="margin-bottom:0;">'
                    + 'Avalia&ccedil;&atilde;o salva com sucesso. '
                    + '<a href="index.php?content=view/admin/GerenciarAvaliacaoFase2.php" class="btn btn-sm btn-default" style="margin-left:10px;">&larr; Voltar &agrave; lista</a>'
                    + '</div>');
            } else {
                $('#mensagem').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
            }
        },
        complete: function() { stopLoading(); }
    });
}
</script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_avaliacao.php");

if ($fase2Indisponivel) {
    ?>
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Avalia&ccedil;&atilde;o 2&ordf; Fase &mdash; Anexo II (Pontua&ccedil;&atilde;o)</h4>
            </div>
            <div class="panel-body">
                <div class="alert alert-warning" style="margin-bottom:0;">
                    <strong>Etapa 2 ainda n&atilde;o habilitada.</strong><br>
                    Para habilitar, entre em
                    <a href="index.php?content=view/admin/GerenciarEtapa.php">Etapas de Avalia&ccedil;&atilde;o</a>
                    e marque a etapa de ordem 2 como <strong>ativa</strong>.
                </div>
            </div>
        </div>
    </div>
    <?php
    return;
}
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<?php if ($temCodInscrito) { ?>
<!-- ============================================================
     MODO FORM — Avaliando 1 inscrito
     ============================================================ -->
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Avalia&ccedil;&atilde;o 2&ordf; Fase &mdash; Anexo II (Pontua&ccedil;&atilde;o)</h4>
        </div>
        <div class="panel-body">

            <div style="margin-bottom: 15px;">
                <a href="index.php?content=view/admin/GerenciarAvaliacaoFase2.php" class="btn btn-default modalBtnCancelar">
                    &larr; Voltar &agrave; Lista
                </a>
                <?php if (!$etapaConcluida) { ?>
                <button type="button" class="btn btn-default" onclick="liberarReservaAvaliacao()" title="Libera sua reserva — outro avaliador poderá pegar este inscrito">
                    <i class="fa fa-unlock"></i> Liberar reserva
                </button>
                <?php } ?>
            </div>

            <?php if ($etapaConcluida) { ?>
            <div class="alert alert-warning" role="alert" style="margin-bottom: 15px;">
                <i class="fa fa-lock"></i>
                <strong>Modo somente leitura.</strong>
                Esta etapa de avalia&ccedil;&atilde;o est&aacute; <strong>Conclu&iacute;da</strong>. N&atilde;o &eacute; permitido alterar avalia&ccedil;&otilde;es de fases encerradas.
            </div>
            <?php } else if (!empty($outrosAvaliadores)) { ?>
            <div class="alert alert-info alert-dismissible" role="alert" style="margin-bottom: 15px;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <i class="fa fa-info-circle"></i>
                <strong>Aten&ccedil;&atilde;o:</strong>
                <?php foreach ($outrosAvaliadores as $i => $av) {
                    $sep = $i > 0 ? ($i === count($outrosAvaliadores)-1 ? ' e ' : ', ') : '';
                    echo $sep . '<strong>' . htmlspecialchars($av['nome']) . '</strong>';
                } ?>
                tamb&eacute;m
                <?php echo count($outrosAvaliadores) === 1 ? 'est&aacute;' : 'est&atilde;o' ?>
                avaliando este inscrito.
            </div>
            <?php } ?>

            <form id="formAvaliacaoFase2" name="formAvaliacaoFase2" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <input type="hidden" value="1" name="_submit_check">
                <input type="hidden" value="GerenciarAvaliacaoFase2" name="c">
                <input type="hidden" value="salvarAvaliacaoFase2" name="m">
                <input type="hidden" name="codAvaliacao" id="codAvaliacao" value="<?php echo $objAI->getCodAvaliacao() ?>">

                <div class="form-group col-sm-3" style="padding-left: 0;">
                    <label>C&oacute;digo Inscrito:</label>
                    <input type="text" id="codInscrito" name="codInscrito" value="<?php echo $objAI->getCodInscrito() ?>" class="form-control" readonly>
                </div>
                <div class="form-group col-sm-9" style="padding-left: 0;">
                    <label>Nome:</label>
                    <input type="text" id="nomeInscrito" name="nomeInscrito" value="<?php echo $objAI->getNomeInscrito() ?>" class="form-control" readonly>
                </div>

                <input type="hidden" name="pontosTotalConfirmado" id="pontosTotalConfirmado" value="<?php echo $objAI->getPontosTotalConfirmado() ?>">

                <div class="form-group col-sm-12 grp-avaliacao-inicial" style="padding-left: 0;">
                    <label>Documentos do Candidato:</label><br>
                    <?php $objSRVFase1->carregarDocumentos($fws_, $objEv, $objAI, $debug); ?>
                </div>

                <div class="form-group col-sm-12 grp-avaliacao-inicial" style="padding-left: 0;">
                    <h4>Formul&aacute;rio Anexo II:</h4>
                    <?php echo GerenciarAvaliacaoInscritoAC::formularioAvaliacaoProRede2($fws_, $objEv, $objAI, $debug); ?>
                </div>

                <div class="form-group col-sm-12 grp-avaliacao-inicial" style="padding-left: 0;">
                    <label>Justificativa do Avaliador:</label>
                    <span style="font-size: 10px;">(opcional)</span>
                    <div style="margin:4px 0 6px; padding:6px 10px; background:#fff3cd; border-left:3px solid #f0b400; border-radius:3px; color:#856404; font-size:12px;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Aten&ccedil;&atilde;o:</strong> esta justificativa ser&aacute; <strong>exibida para o candidato avaliado</strong> ap&oacute;s a divulga&ccedil;&atilde;o do resultado.
                        Redija com clareza, sem dados pessoais de terceiros e usando linguagem profissional.
                    </div>
                    <textarea name="parecerAvaliador" id="parecerAvaliador" class="form-control" rows="4" maxlength="1000"><?php echo $objAI->getParecerAvaliador() ?></textarea>
                </div>

                <div class="form-group col-sm-6 grp-avaliacao-inicial" style="padding-left: 0;">
                    <label>Classifica&ccedil;&atilde;o Status:</label>
                    <select id="classificacaoStatus" name="classificacaoStatus" class="form-control" required>
                        <option value="">Selecione</option>
                        <option value="H" <?php echo $objAI->getClassificacaoStatus() == 'H' ? 'selected' : '' ?>>Habilitado</option>
                        <option value="I" <?php echo $objAI->getClassificacaoStatus() == 'I' ? 'selected' : '' ?>>N&atilde;o Habilitado</option>
                    </select>
                </div>

                <?php
                // ===== Bloco de Avaliacao do Recurso =====
                // So aparece quando o inscrito submeteu recurso (recurso_texto != '').
                // Habilitado pra edicao apenas em modo RECURSO. Nos demais modos, fica
                // visivel mas read-only (gestor consulta o que ja foi avaliado).
                $textoRecursoInscrito = (string) $objAI->getRecursoTexto();
                if ($textoRecursoInscrito !== '') { ?>
                <hr style="margin:18px 0 12px; border-top:2px solid #1565c0;">
                <div class="form-group col-sm-12" style="padding-left:0;">
                    <h4 style="margin-top:0; color:#1565c0;">
                        <i class="fa fa-gavel"></i> Avalia&ccedil;&atilde;o do Recurso
                        <?php if ($modoAtual === 'RECURSO') { ?>
                            <span style="font-size:12px; background:#1565c0; color:#fff; padding:2px 8px; border-radius:10px; vertical-align:middle; margin-left:6px;">edi&ccedil;&atilde;o aberta</span>
                        <?php } else if ($modoAtual === 'CONSULTA') { ?>
                            <span style="font-size:12px; background:#888; color:#fff; padding:2px 8px; border-radius:10px; vertical-align:middle; margin-left:6px;">consulta</span>
                        <?php } else { ?>
                            <span style="font-size:12px; background:#bbb; color:#fff; padding:2px 8px; border-radius:10px; vertical-align:middle; margin-left:6px;">fora do per&iacute;odo de an&aacute;lise de recurso</span>
                        <?php } ?>
                    </h4>
                </div>

                <div class="form-group col-sm-12" style="padding-left:0;">
                    <label>Texto do Recurso enviado pelo inscrito:</label>
                    <textarea class="form-control" rows="6" readonly style="background:#f8f9fa;"><?php echo htmlspecialchars($textoRecursoInscrito) ?></textarea>
                </div>

                <div class="form-group col-sm-12 grp-avaliacao-recurso" style="padding-left:0;">
                    <label>Parecer do Avaliador para o Recurso:</label>
                    <span style="font-size: 10px;">(m&aacute;ximo 2000 caracteres)</span>
                    <div style="margin:4px 0 6px; padding:6px 10px; background:#fff3cd; border-left:3px solid #f0b400; border-radius:3px; color:#856404; font-size:12px;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Aten&ccedil;&atilde;o:</strong> este parecer ser&aacute; <strong>exibido para o candidato avaliado</strong> ap&oacute;s a divulga&ccedil;&atilde;o do resultado.
                        Redija com clareza, sem dados pessoais de terceiros e usando linguagem profissional.
                    </div>
                    <textarea name="recursoParecer" id="recursoParecer" class="form-control" rows="5" maxlength="2000"><?php echo htmlspecialchars((string)$objAI->getRecursoParecer()) ?></textarea>
                </div>

                <div class="form-group col-sm-3 grp-avaliacao-recurso" style="padding-left:0;">
                    <label>Pontos Ap&oacute;s Recurso:</label>
                    <input type="number" step="0.01" min="0" name="pontosTotalAposRecurso" id="pontosTotalAposRecurso" class="form-control"
                           value="<?php echo $objAI->getPontosTotalAposRecurso() ?>">
                </div>

                <div class="form-group col-sm-6 grp-avaliacao-recurso" style="padding-left:0;">
                    <label>Classifica&ccedil;&atilde;o Status Ap&oacute;s Recurso:</label>
                    <select id="classificacaoStatusAposRecurso" name="classificacaoStatusAposRecurso" class="form-control">
                        <option value="">Selecione</option>
                        <option value="H" <?php echo $objAI->getClassificacaoStatusAposRecurso() == 'H' ? 'selected' : '' ?>>Habilitado</option>
                        <option value="I" <?php echo $objAI->getClassificacaoStatusAposRecurso() == 'I' ? 'selected' : '' ?>>N&atilde;o Habilitado</option>
                    </select>
                </div>
                <?php } else if ($modoAtual === 'RECURSO') { ?>
                <div class="alert alert-info" style="margin-top:18px;">
                    <i class="fa fa-info-circle"></i> Este inscrito <strong>n&atilde;o enviou recurso</strong>. Nada a analisar.
                </div>
                <?php } ?>

                <div class="form-group col-sm-12" style="padding-left: 0;">
                    <div id="mensagem"></div>
                    <?php if ($modoAtual === 'CONSULTA') { ?>
                    <div class="alert alert-warning" style="margin-bottom:10px;">
                        <i class="fa fa-clock-o"></i>
                        <strong>Fora do per&iacute;odo de an&aacute;lise.</strong>
                        <?php if ($etapaConcluida) { ?>
                            A etapa est&aacute; com situa&ccedil;&atilde;o <strong>Conclu&iacute;da</strong>.
                        <?php } else { ?>
                            Janela de avalia&ccedil;&atilde;o:
                            <strong><?php echo htmlspecialchars(formatDateView($janelaAnaliseIni)); ?></strong>
                            a <strong><?php echo htmlspecialchars(formatDateView($janelaAnaliseFim)); ?></strong>
                            <?php if ($janelaRecursoIni && $janelaRecursoFim) { ?>
                                &middot; Janela de an&aacute;lise de recurso:
                                <strong><?php echo htmlspecialchars(formatDateView($janelaRecursoIni)); ?></strong>
                                a <strong><?php echo htmlspecialchars(formatDateView($janelaRecursoFim)); ?></strong>.
                            <?php } ?>
                        <?php } ?>
                        Edi&ccedil;&atilde;o bloqueada &mdash; somente consulta.
                    </div>
                    <?php } else if ($modoAtual === 'RECURSO') { ?>
                    <div class="alert alert-info" style="margin-bottom:10px;">
                        <i class="fa fa-gavel"></i>
                        <strong>Modo An&aacute;lise de Recurso.</strong>
                        Janela:
                        <strong><?php echo htmlspecialchars(formatDateView($janelaRecursoIni)); ?></strong>
                        a <strong><?php echo htmlspecialchars(formatDateView($janelaRecursoFim)); ?></strong>.
                        Avalia&ccedil;&atilde;o inicial congelada &mdash; s&oacute; campos do recurso s&atilde;o edit&aacute;veis.
                    </div>
                    <?php } ?>
                    <?php if ($modoAtual === 'AVALIACAO') { ?>
                    <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarFase2">
                        Salvar Avalia&ccedil;&atilde;o 2&ordf; Fase
                    </button>
                    <?php } else if ($modoAtual === 'RECURSO' && $textoRecursoInscrito !== '') { ?>
                    <button type="button" class="btn btn-success modalBtnSalvar" id="btnSalvarFase2">
                        Salvar An&aacute;lise do Recurso
                    </button>
                    <?php } ?>
                    <a href="index.php?content=view/admin/GerenciarAvaliacaoFase2.php" class="btn btn-default modalBtnCancelar" role="button">
                        <?php echo $modoAtual === 'CONSULTA' ? 'Voltar' : 'Cancelar'; ?>
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
<?php } else { ?>
<!-- ============================================================
     MODO LISTAGEM — Sem codInscrito
     ============================================================ -->
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px; margin-bottom:30px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Avalia&ccedil;&atilde;o 2&ordf; Fase &mdash; Anexo II (Pontua&ccedil;&atilde;o)</h4>
        <div>
            <label for="filtroAvaliacao" style="font-weight: bold; margin-right: 6px; font-size: 13px;">Filtrar:</label>
            <?php $filtroSel = htmlspecialchars($fws_['filtroAvaliacao'] ?? 'todos'); ?>
            <select id="filtroAvaliacao" class="form-control" style="display: inline-block; width: auto; font-size: 13px;">
                <option value="pendente"        <?= $filtroSel == 'pendente'        ? 'selected' : '' ?>>Pendentes de avalia&ccedil;&atilde;o</option>
                <option value="avaliado"        <?= $filtroSel == 'avaliado'        ? 'selected' : '' ?>>J&aacute; avaliados</option>
                <option value="habilitados"     <?= $filtroSel == 'habilitados'     ? 'selected' : '' ?>>Habilitados (Fase 2)</option>
                <option value="nao_habilitados" <?= $filtroSel == 'nao_habilitados' ? 'selected' : '' ?>>N&atilde;o Habilitados (Fase 2)</option>
                <option value="com_recurso"     <?= $filtroSel == 'com_recurso'     ? 'selected' : '' ?>>Com recurso (Fase 2)</option>
                <option value="recurso_pendente" <?= $filtroSel == 'recurso_pendente' ? 'selected' : '' ?>>Recursos pendentes de an&aacute;lise</option>
                <option value="recurso_avaliado" <?= $filtroSel == 'recurso_avaliado' ? 'selected' : '' ?>>Recursos j&aacute; analisados</option>
                <option value="com_docs_ilegiveis" <?= $filtroSel == 'com_docs_ilegiveis' ? 'selected' : '' ?>>Com documentos ileg&iacute;veis</option>
                <option value="todos"           <?= $filtroSel == 'todos'           ? 'selected' : '' ?>>Todos os habilitados Fase 1</option>
            </select>
            <button type="button" id="btnGerarEditalFase2" class="btn btn-default" style="font-size:12px; margin-left:8px;" title="Gera o edital de publica&ccedil;&atilde;o em DOCX respeitando o filtro selecionado">
                <i class="fa fa-file-word-o"></i> Gerar Edital DOCX
            </button>
        </div>
    </div>
    <div>
        <table class="tbl-fin" id="tabelaFase2">
            <thead>
                <tr>
                    <?php $objSRV->carregarCabecalhoTabelaFase2($fws_, $debug); ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!isset($fws_['filtroAvaliacao'])) { $fws_['filtroAvaliacao'] = 'todos'; }
                $objSRV->carregarTabelaFase2($fws_, $debug);
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>

<form name="getdoc" method="post" action="<?php echo WB_URL_COMMAND ?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirDocumentoTela">
    <input type="hidden" value="" name="cod">
    <input type="hidden" value="" name="codEvento">
</form>
<form name="getdocunificado" method="post" action="<?php echo WB_URL_COMMAND ?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirDocumentoUnificadoTela">
    <input type="hidden" value="" name="cod">
    <input type="hidden" value="<?php echo $objEv->getCodEvento() ?>" name="codEvento">
</form>

<script type="text/javascript">
function visualizarDocUnificado(arquivoGerado){
    if (!arquivoGerado) { return; }
    document.getdocunificado.cod.value = arquivoGerado;
    document.getdocunificado.submit();
}
function visualizarDoc(){
    if ($('#arquivoGerado').val() == '') { return; }
    document.getdoc.cod.value = $('#arquivoGerado').val();
    document.getdoc.submit();
}
</script>
