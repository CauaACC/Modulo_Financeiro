<?php
require_once(dirname(__FILE__) . "/../../include/config.php");

// Modo preview de admin: visualizar o conteudo do curso sem ser inscrito, ignorando os
// filtros de divulgacao/oficina. Gate: admin logado com ADM_GERAL. So pra visualizacao.
//
// O preview vive SO nesta tela, de proposito: as escritas do aluno (presenca,
// conclusao, tarefa) exigem _cod_inscrito, que o preview nao tem, e comentar
// exige papel do EAD -- ou seja, os endpoints ja recusam sem precisar conhecer
// um "modo preview". Levar esse estado para os controllers criaria um modo
// paralelo em 6 rotas para resolver o que a permissao ja resolve.
$previewAdmin = false;
if (!empty($_GET['preview']) && !empty($_SESSION['_cod_usuario'])
    && FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
    $previewAdmin = true;
}
$previewCodCurso = isset($_GET['cod_curso']) ? (int) $_GET['cod_curso'] : 0;

if (!$previewAdmin && empty($_SESSION['_cod_inscrito'])) {
    $URL = WB_URL_VIEW . "?content=view/inscricao/inscricao_login.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}
$codInscrito = !empty($_SESSION['_cod_inscrito']) ? (int) $_SESSION['_cod_inscrito'] : 0;
$debug = false;

// Carregar dados do inscrito pra aplicar regras de divulgação (pulado no preview de admin)
$objInscrito = new Inscrito();
$objInscrito->setCodInscrito($codInscrito);
$inscricaoPaga = false;
if (!$previewAdmin) {
    try { $objInscrito->getInscritoById($debug); } catch (Exception $e) {}
    $inscricaoPaga = Curso::calcularInscricaoPaga($objInscrito);
}

// Carregar cursos do evento
$objCurso = new Curso();
$cursosBruto = array();
try {
    $objCurso->carregarLista(true, $debug);
    $cursosBruto = $objCurso->getListaCurso() ?: array();
} catch (Exception $e) { $cursosBruto = array(); }

// Oficinas em que o inscrito esta matriculado (ATIVADA) — pra restringir curso por oficina
$daoOfi = new OficinaDAO();
$oficinasInscrito = array();
$cursosComOficina = array();
if (!$previewAdmin) {
    foreach ($daoOfi->getCodOficinasByInscrito(COD_EVENTO, $codInscrito, $debug) as $co) {
        $oficinasInscrito[$co] = true;
    }
    // Mapa curso => oficinas de uma vez so (antes era uma consulta por curso
    // dentro do laco abaixo).
    $cursosComOficina = $daoOfi->getCursosComOficina(COD_EVENTO, $debug);
}

// Filtrar cursos pelas regras de divulgação. A regra mora em
// Curso::inscritoPodeVer -- a mesma que os endpoints AJAX usam, para que tela e
// servidor nunca discordem sobre o que este inscrito alcanca.
$cursos = array();
foreach ($cursosBruto as $cu) {
    // Preview de admin: sem filtros; se veio cod_curso, mostra so aquele curso
    if ($previewAdmin) {
        if ($previewCodCurso && (int) $cu->getCodCurso() !== $previewCodCurso) continue;
        $cursos[] = $cu;
        continue;
    }

    if (!$cu->inscritoPodeVer($objInscrito, $inscricaoPaga, $oficinasInscrito, $cursosComOficina)) continue;

    $cursos[] = $cu;
}

// Carregar todos os itens da árvore do evento
$objCI = new CursoItem();
$linhasItens = $objCI->carregarArvore(null, $debug);

// Carregar conclusões do inscrito no evento
$objConcl = new CursoItemConclusao();
$conclusoes = $objConcl->carregarConclusoesDoInscrito($codInscrito, COD_EVENTO, $debug);
$concluidos = array();
foreach ($conclusoes as $c) { $concluidos[(int) $c['cod_curso_item']] = true; }

// Mensagens privadas nao lidas pelo aluno, por item de TAREFA (badge "N novas")
$msgNaoLidas = array();
if (!empty($codInscrito)) {
    $daoMsgTarefa = new CursoTarefaMensagemDAO();
    $msgNaoLidas = $daoMsgTarefa->naoLidasPorAluno(COD_EVENTO, $codInscrito, $debug);
}

// Indexar itens por curso e por pai pra render recursive.
// $itensAtivos guarda quem esta no ar: um pre-requisito que foi inativado (ou
// excluido) nao pode continuar trancando o item dependente -- a conclusao dele
// nunca vai existir, e o aluno ficaria travado para sempre.
$porCurso = array();
$itensAtivos = array();
foreach ($linhasItens as $l) {
    if ($l['status'] !== 'ATIVADA') continue;
    $cc = (int) $l['cod_curso'];
    $cp = (int) $l['cod_pai'];
    $porCurso[$cc][$cp][] = $l;
    $itensAtivos[(int) $l['cod_curso_item']] = true;
}

// Helper: contar folhas e folhas concluidas dentro de um nó
function hv_contar_folhas($nodes, $cod_pai, &$total, &$feitas, $concluidos) {
    if (!isset($nodes[$cod_pai])) return;
    foreach ($nodes[$cod_pai] as $n) {
        $cod = (int) $n['cod_curso_item'];
        if ($n['tipo'] === 'CAPITULO') {
            hv_contar_folhas($nodes, $cod, $total, $feitas, $concluidos);
        } else {
            $total++;
            if (isset($concluidos[$cod])) $feitas++;
        }
    }
}

function hv_render_arvore($nodes, $cod_pai, $nivel, $concluidos, $codInscrito, $msgNaoLidas = array(), $itensAtivos = array()) {
    if (!isset($nodes[$cod_pai])) return;
    foreach ($nodes[$cod_pai] as $n) {
        $cod = (int) $n['cod_curso_item'];
        $tipo = $n['tipo'];
        $titulo = htmlspecialchars($n['titulo']);
        $isCap = ($tipo === 'CAPITULO');
        $jaConcl = isset($concluidos[$cod]);

        // So trava se o pre-requisito ainda estiver no ar (item inativado ou
        // excluido nao pode prender ninguem) e ainda nao tiver sido concluido.
        $bloqueadoPre = false;
        $codPre = !empty($n['cod_pre_requisito']) ? (int) $n['cod_pre_requisito'] : 0;
        if ($codPre && isset($itensAtivos[$codPre]) && !isset($concluidos[$codPre])) {
            $bloqueadoPre = true;
        }

        $dentroJanela = true;
        $hoje = date('Y-m-d');
        $horaAtual = date('H:i:s');
        if (!empty($n['dt_liberacao']) && $n['dt_liberacao'] > $hoje) $dentroJanela = false;
        if (!empty($n['hora_inicio']) && $horaAtual < $n['hora_inicio']) $dentroJanela = false;
        if (!empty($n['hora_fim']) && $horaAtual > $n['hora_fim']) $dentroJanela = false;

        $cls = 'item-liberado';
        if ($bloqueadoPre || !$dentroJanela) $cls = 'item-bloqueado';
        if ($jaConcl) $cls = 'item-concluido';
        if ($isCap) $cls = 'item-capitulo';

        $padding = 14 + ($nivel * 22);
        $iconeTipo = '🎬';
        if ($tipo === 'PDF')        $iconeTipo = '📄';
        if ($tipo === 'MEET')       $iconeTipo = '🎥';
        if ($tipo === 'SLIDES')     $iconeTipo = '📊';
        if ($tipo === 'TEXTO')      $iconeTipo = '📝';
        if ($tipo === 'TAREFA')     $iconeTipo = '✅';
        if ($tipo === 'LINK')       $iconeTipo = '🔗';
        if ($tipo === 'FORMULARIO') $iconeTipo = '📋';
        if ($isCap)                 $iconeTipo = '📁';

        echo '<div class="hv-item ' . $cls . '" style="padding-left:' . $padding . 'px;" data-cod="' . $cod . '">';
        if ($isCap) {
            echo '<h4 class="hv-cap-titulo">' . $iconeTipo . ' ' . $titulo . '</h4>';
            if (!empty($n['descricao'])) echo '<div class="hv-desc">' . nl2br(htmlspecialchars($n['descricao'])) . '</div>';
        } else {
            echo '<div class="hv-folha-row">';
            echo '<div class="hv-folha-info">';
            echo '<div class="hv-folha-titulo">' . $iconeTipo . ' ' . $titulo;
            if ($jaConcl) echo ' <span class="hv-badge-ok"><i class="fa fa-check"></i> concluído</span>';
            if ($bloqueadoPre) echo ' <span class="hv-badge-lock"><i class="fa fa-lock"></i> requer pré-requisito</span>';
            if (!$dentroJanela && !$bloqueadoPre) echo ' <span class="hv-badge-time"><i class="fa fa-clock-o"></i> fora do horário</span>';
            echo '</div>';
            if (!empty($n['descricao'])) echo '<div class="hv-desc">' . nl2br(htmlspecialchars($n['descricao'])) . '</div>';
            echo '</div>';

            echo '<div class="hv-folha-acoes">';
            if (!$bloqueadoPre && $dentroJanela) {
                if ($tipo === 'VIDEO' || $tipo === 'SLIDES' || $tipo === 'MEET' || $tipo === 'FORMULARIO') {
                    $intMin = (int) $n['intervalo_confirmacao_min'];
                    $intMax = (int) $n['intervalo_confirmacao_max'];
                    $tout   = (int) $n['timeout_confirmacao_seg'];
                    $detran = (int) $n['controle_presenca'];
                    $obj = new CursoItem();
                    $obj->setUrl($n['url']);
                    $embed = $obj->getUrlEmbed();
                    $tipoEmbed = $obj->detectarTipoEmbed();
                    echo '<button type="button" class="hv-btn hv-btn-primary"'
                       . ' onclick="hvAbrirPlayer(' . $cod . ','
                       . '\'' . htmlspecialchars(addslashes($embed), ENT_QUOTES) . '\','
                       . '\'' . htmlspecialchars(addslashes($titulo), ENT_QUOTES) . '\','
                       . $intMin . ',' . $intMax . ',' . $tout . ','
                       . '\'' . htmlspecialchars(addslashes($tipoEmbed)) . '\','
                       . $detran . ')">'
                       . '<i class="fa fa-play"></i> ' . ($jaConcl ? 'Rever' : 'Iniciar')
                       . '</button>';
                } elseif ($tipo === 'PDF' || $tipo === 'LINK') {
                    echo '<a class="hv-btn hv-btn-primary" href="' . htmlspecialchars($n['url']) . '" target="_blank"'
                       . ' onclick="hvRegistrarAcessoExterno(' . $cod . ')">'
                       . '<i class="fa fa-external-link"></i> Abrir</a>';
                } elseif ($tipo === 'TAREFA') {
                    echo '<button type="button" class="hv-btn hv-btn-primary" onclick="hvAbrirTarefa(' . $cod . ', \'' . htmlspecialchars(addslashes($titulo), ENT_QUOTES) . '\')">'
                       . '<i class="fa fa-upload"></i> ' . ($jaConcl ? 'Reenviar' : 'Enviar tarefa') . '</button>';
                } elseif ($tipo === 'TEXTO') {
                    echo '<button type="button" class="hv-btn hv-btn-primary" onclick="hvMarcarConcluido(' . $cod . ')">'
                       . '<i class="fa fa-check"></i> Marcar como lida</button>';
                }
                if (!$jaConcl && in_array($tipo, array('VIDEO','SLIDES','MEET','TAREFA','PDF'))) {
                    echo ' <button type="button" class="hv-btn hv-btn-secondary" title="Marcar manualmente como concluído"'
                       . ' onclick="hvMarcarConcluido(' . $cod . ')"><i class="fa fa-check"></i></button>';
                }
            }
            if ((int) $n['permite_comentarios'] === 1) {
                echo ' <button type="button" class="hv-btn hv-btn-ghost" onclick="hvAbrirComentarios(' . $cod . ', \'' . htmlspecialchars(addslashes($titulo), ENT_QUOTES) . '\')">'
                   . '<i class="fa fa-comment-o"></i></button>';
            }
            if ($tipo === 'TAREFA') {
                $nl = isset($msgNaoLidas[$cod]) ? (int) $msgNaoLidas[$cod] : 0;
                $badgeMsg = $nl > 0 ? ' <span class="hv-msg-badge">' . $nl . '</span>' : '';
                echo ' <button type="button" class="hv-btn hv-btn-secondary" onclick="hvAbrirMensagens(' . $cod . ', \'' . htmlspecialchars(addslashes($titulo), ENT_QUOTES) . '\')">'
                   . '<i class="fa fa-comments-o"></i> Mensagens' . $badgeMsg . '</button>';
            }
            echo '</div></div>';
        }

        hv_render_arvore($nodes, $cod, $nivel + 1, $concluidos, $codInscrito, $msgNaoLidas, $itensAtivos);
        echo '</div>';
    }
}
?>
<style>
    .hv-container { max-width: 980px; margin: 0 auto; padding: 10px 0 60px; }
    .hv-curso-card { background: #fff; border: 1px solid #e8e8e8; border-radius: 12px; margin-bottom: 18px; overflow: hidden; }
    .hv-curso-cab { padding: 16px 22px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; gap: 14px; }
    .hv-curso-cab h3 { margin: 0; font-size: 17px; font-weight: 700; color: #1f2937; }
    .hv-curso-cab .hv-curso-meta { font-size: 12px; color: #6c757d; }
    .hv-progress-wrap { width: 240px; }
    .hv-progress { background: #e9ecef; border-radius: 4px; height: 8px; overflow: hidden; }
    .hv-progress-bar { height: 100%; background: linear-gradient(90deg, #2d6a4f, #4caf50); transition: width .3s; }
    .hv-progress-txt { font-size: 12px; color: #4b5563; margin-top: 4px; text-align: right; }
    .hv-itens { padding: 8px 14px; }

    .hv-item { padding: 10px 14px; margin: 6px 0; border-radius: 8px; transition: background .15s; border-left: 4px solid transparent; }
    .hv-item.item-capitulo { background: transparent; border-left-color: #6c757d; padding-left: 14px !important; margin-top: 14px; margin-bottom: 4px; }
    .hv-item.item-capitulo .hv-cap-titulo { margin: 0; font-size: 14px; color: #1f2937; font-weight: 600; }
    .hv-item.item-liberado { background: #f5fff5; border-left-color: #28a745; }
    .hv-item.item-concluido { background: #eaf6ec; border-left-color: #1f7a1f; }
    .hv-item.item-bloqueado { background: #f8f9fa; border-left-color: #adb5bd; opacity: .8; }

    .hv-folha-row { display: flex; gap: 12px; align-items: center; }
    .hv-folha-info { flex: 1; }
    .hv-folha-titulo { font-weight: 600; color: #1f2937; font-size: 14px; }
    .hv-desc { color: #4b5563; font-size: 12px; margin-top: 4px; }
    .hv-folha-acoes { display: flex; gap: 6px; align-items: center; flex-shrink: 0; }

    .hv-badge-ok    { font-size: 11px; padding: 2px 8px; border-radius: 10px; background: #d4edda; color: #155724; margin-left: 6px; font-weight: 500; }
    .hv-badge-lock  { font-size: 11px; padding: 2px 8px; border-radius: 10px; background: #e2e3e5; color: #383d41; margin-left: 6px; font-weight: 500; }
    .hv-badge-time  { font-size: 11px; padding: 2px 8px; border-radius: 10px; background: #fff3cd; color: #856404; margin-left: 6px; font-weight: 500; }

    .hv-btn { display: inline-block; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; cursor: pointer; border: none; }
    .hv-btn-primary    { background: #2d6a4f; color: #fff !important; }
    .hv-btn-primary:hover { background: #245a42; }
    .hv-btn-secondary  { background: #fff; color: #2d6a4f !important; border: 1px solid #2d6a4f; }
    .hv-btn-secondary:hover { background: #f0fdf4; }
    .hv-btn-ghost      { background: transparent; color: #6c757d !important; border: 1px solid #e0e0e0; }
    .hv-btn-ghost:hover { background: #f5f5f5; }
    .hv-btn-disabled   { background: #e9ecef; color: #6c757d !important; cursor: not-allowed; }

    #hvPlayerModal { display: none; position: fixed; z-index: 99999; top: 60px; left: 60px; width: 720px; height: 445px; min-width: 320px; min-height: 220px; background: #000; border-radius: 8px; box-shadow: 0 12px 48px rgba(0,0,0,0.55); overflow: hidden; flex-direction: column; }
    #hvPlayerModal.show { display: flex; }
    #hvPlayerModal.hv-max { top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; border-radius: 0; }
    #hvPlayerBar { flex: 0 0 auto; height: 38px; display: flex; align-items: center; justify-content: space-between; padding: 0 6px 0 12px; background: #1f2937; color: #fff; cursor: move; user-select: none; }
    #hvPlayerInfo { flex: 1; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 10px; }
    .hv-player-actions { flex: 0 0 auto; display: flex; gap: 4px; }
    .hv-player-actions button { background: transparent; color: #fff; border: none; width: 30px; height: 28px; border-radius: 5px; cursor: pointer; font-size: 14px; }
    .hv-player-actions button:hover { background: rgba(255,255,255,0.15); }
    #hvPlayerClose { width: auto !important; padding: 0 12px; background: #dc3545 !important; color: #fff; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    #hvPlayerClose:hover { background: #b52b3a !important; }
    #hvPlayerBody { position: relative; flex: 1 1 auto; background: #000; }
    #hvPlayerIframe { position: absolute; inset: 0; width: 100%; height: 100%; border: none; display: block; }
    #hvPlayerShield { position: absolute; inset: 0; display: none; z-index: 2; }
    #hvPlayerResize { position: absolute; right: 0; bottom: 0; width: 20px; height: 20px; cursor: nwse-resize; z-index: 3; background: linear-gradient(135deg, transparent 45%, rgba(255,255,255,0.55) 45%, rgba(255,255,255,0.55) 55%, transparent 55%, transparent 68%, rgba(255,255,255,0.55) 68%, rgba(255,255,255,0.55) 78%, transparent 78%); }
    #hvPlayerModal.hv-max #hvPlayerResize { display: none; }

    #hvConfirmModal { display: none; position: fixed; z-index: 100000; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.92); }
    #hvConfirmModal.show { display: block; }
    #hvConfirmBox { background: #fff; padding: 36px 40px; border-radius: 14px; text-align: center; width: 420px; max-width: calc(100vw - 40px); box-shadow: 0 10px 40px rgba(0,0,0,0.5); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); transition: top .25s ease, left .25s ease; }
    #hvConfirmBox h3 { margin: 0 0 12px; color: #1f2937; font-size: 22px; }
    #hvConfirmBox p { color: #4b5563; font-size: 14px; margin-bottom: 20px; }
    #hvConfirmTimer { font-size: 48px; font-weight: 700; color: #dc3545; margin: 14px 0 22px; }
    #hvConfirmBtn { background: #2d6a4f; color: #fff; border: none; padding: 12px 32px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }

    #hvCommentsModal { display: none; position: fixed; z-index: 99000; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
    #hvCommentsModal.show { display: flex; }
    #hvCommentsBox { background: #fff; width: 92%; max-width: 600px; max-height: 84vh; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; }
    #hvCommentsHeader { padding: 14px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #2d6a4f; color: #fff; }
    #hvCommentsHeader h4 { margin: 0; font-size: 15px; }
    #hvCommentsHeader button { background: transparent; color: #fff; border: none; font-size: 18px; cursor: pointer; }
    #hvCommentsList { padding: 14px 20px; flex: 1; overflow-y: auto; }
    .hv-comment { padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
    .hv-comment:last-child { border-bottom: none; }
    .hv-comment .autor { font-weight: 600; color: #1f2937; font-size: 13px; }
    .hv-comment .data  { color: #6c757d; font-size: 11px; margin-left: 8px; }
    .hv-comment .texto { color: #4b5563; font-size: 13px; margin-top: 4px; white-space: pre-wrap; }
    #hvCommentsForm { padding: 12px 20px; border-top: 1px solid #eee; display: flex; gap: 8px; }
    #hvCommentsTexto { flex: 1; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 8px; resize: vertical; min-height: 36px; font-size: 13px; }
    #hvCommentsBtn { background: #2d6a4f; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; }

    .hv-aviso { display: none; position: fixed; top: 70px; left: 50%; transform: translateX(-50%); background: #d1ecf1; color: #0c5460; padding: 14px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 100001; font-size: 14px; }
    .hv-aviso.erro    { background: #f8d7da; color: #721c24; }
    .hv-aviso.sucesso { background: #d4edda; color: #155724; }
</style>

<style>
    /* ===== Barra de preferências do curso (layout / tema / fonte) — só nesta tela ===== */
    .hv-prefs { max-width: 980px; margin: 6px auto 14px; display: flex; flex-wrap: wrap; gap: 10px 16px; align-items: center; justify-content: flex-end; background: var(--hv-panel,#fff); border: 1px solid var(--hv-border,#e8e8e8); border-radius: 10px; padding: 8px 12px; }
    .hv-prefs .grp { display: flex; align-items: center; gap: 7px; }
    .hv-prefs .lbl { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: var(--hv-muted,#6c757d); font-weight: 700; }
    .hv-seg { display: inline-flex; background: var(--hv-soft,#f1f3f6); border: 1px solid var(--hv-border,#e0e0e0); border-radius: 8px; padding: 2px; }
    .hv-seg button { border: 0; background: transparent; color: var(--hv-muted,#6c757d); font: inherit; font-size: 12px; font-weight: 600; padding: 5px 11px; border-radius: 6px; cursor: pointer; }
    .hv-seg button[aria-pressed="true"] { background: #2d6a4f; color: #fff; }
    .hv-seg button:focus-visible { outline: 2px solid #2d6a4f; outline-offset: 2px; }

    /* ===== Layout LATERAL (menu à esquerda + conteúdo no centro) ===== */
    .hv-card-body { display: block; }
    .hv-main { display: none; }
    .hv-drawer-toggle { display: none; }
    .hv-container[data-layout="lateral"] .hv-card-body { display: grid; grid-template-columns: 320px 1fr; align-items: start; }
    .hv-container[data-layout="lateral"] .hv-itens { border-right: 1px solid var(--hv-border,#eee); max-height: 70vh; overflow: auto; }
    .hv-container[data-layout="lateral"] .hv-main { display: block; padding: 16px 18px; min-height: 320px; }
    /* Na coluna estreita, empilha titulo + acoes pra nao espremer o texto */
    .hv-container[data-layout="lateral"] .hv-folha-row { flex-direction: column; align-items: stretch; gap: 8px; }
    .hv-container[data-layout="lateral"] .hv-folha-acoes { justify-content: flex-start; flex-wrap: wrap; }
    .hv-main-empty { color: var(--hv-muted,#6c757d); font-size: 13px; padding: 40px 10px; text-align: center; }

    /* ===== Layout CLASSROOM (cards por capítulo, estilo mural) ===== */
    .hv-container[data-layout="classroom"] .hv-itens { padding: 8px 16px 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; align-items: start; }
    .hv-container[data-layout="classroom"] .hv-item { padding-left: 14px !important; }
    .hv-container[data-layout="classroom"] .item-capitulo { grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; background: transparent; border-left: 0; margin: 12px 0 4px; }
    .hv-container[data-layout="classroom"] .item-capitulo > .hv-cap-titulo,
    .hv-container[data-layout="classroom"] .item-capitulo > .hv-desc { grid-column: 1 / -1; }
    .hv-container[data-layout="classroom"] .hv-item:not(.item-capitulo) { background: var(--hv-panel,#fff); border: 1px solid var(--hv-border,#e8e8e8); border-radius: 12px; padding: 14px !important; margin: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
    .hv-container[data-layout="classroom"] .hv-item.item-liberado { border-top: 3px solid #28a745; }
    .hv-container[data-layout="classroom"] .hv-item.item-concluido { border-top: 3px solid #1f7a1f; }
    .hv-container[data-layout="classroom"] .hv-item.item-bloqueado { border-top: 3px solid #adb5bd; }
    .hv-container[data-layout="classroom"] .hv-folha-row { flex-direction: column; align-items: stretch; gap: 10px; min-height: 92px; }
    .hv-container[data-layout="classroom"] .hv-folha-acoes { flex-wrap: wrap; margin-top: auto; }

    /* ===== CATÁLOGO (lista de cursos) + botão voltar ===== */
    .hv-cat-topo { display: flex; align-items: center; justify-content: space-between; margin: 4px 2px 14px; }
    .hv-cat-titulo { margin: 0; font-size: 18px; font-weight: 700; color: var(--hv-ink,#1f2937); }
    .hv-catalogo { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
    .hv-cat-card { display: flex; flex-direction: column; background: var(--hv-panel,#fff); border: 1px solid var(--hv-border,#e8e8e8); border-radius: 14px; overflow: hidden; text-decoration: none; color: inherit; transition: box-shadow .15s, transform .15s; }
    .hv-cat-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.10); transform: translateY(-2px); }
    .hv-cat-capa { height: 92px; background: linear-gradient(135deg, #2d6a4f, #40916c); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 32px; }
    .hv-cat-corpo { padding: 14px 16px; flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .hv-cat-nome { margin: 0; font-size: 15px; font-weight: 700; color: var(--hv-ink,#1f2937); }
    .hv-cat-desc { margin: 0; font-size: 12.5px; color: var(--hv-muted,#6c757d); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .hv-cat-meta { font-size: 12px; color: var(--hv-muted,#6c757d); }
    .hv-cat-prog { margin-top: auto; }
    .hv-cat-progtxt { font-size: 11px; color: var(--hv-muted,#6c757d); margin-top: 4px; }
    .hv-cat-rodape { padding: 0 16px 14px; }
    .hv-cat-rodape .hv-btn { display: block; text-align: center; }
    .hv-voltar { display: inline-flex; align-items: center; gap: 7px; margin: 2px 2px 14px; color: #2d6a4f; text-decoration: none; font-size: 13px; font-weight: 600; }
    .hv-voltar:hover { text-decoration: underline; }
    .hv-container[data-theme="dark"] .hv-cat-card { background: #141a22; border-color: #28323f; }
    .hv-container[data-theme="dark"] .hv-cat-nome,
    .hv-container[data-theme="dark"] .hv-cat-titulo { color: #e8eef5; }
    .hv-container[data-theme="dark"] .hv-cat-desc,
    .hv-container[data-theme="dark"] .hv-cat-meta,
    .hv-container[data-theme="dark"] .hv-cat-progtxt { color: #9aa7b6; }
    .hv-container[data-theme="dark"] .hv-voltar { color: #7fc6a3; }

    /* Player "docado" no painel central (modo lateral) */
    #hvPlayerModal.hv-docked { position: static !important; width: 100% !important; height: auto !important; top: auto !important; left: auto !important; right: auto; bottom: auto; box-shadow: none; border-radius: 10px; display: flex; }
    #hvPlayerModal.hv-docked #hvPlayerBody { aspect-ratio: 16 / 9; flex: 0 0 auto; }
    #hvPlayerModal.hv-docked #hvPlayerBar { cursor: default; }
    #hvPlayerModal.hv-docked #hvPlayerResize { display: none; }
    /* Maximizar tem prioridade sobre o docado: volta a tela cheia e o vídeo preenche */
    #hvPlayerModal.hv-max { position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; border-radius: 0 !important; display: flex !important; flex-direction: column !important; }
    #hvPlayerModal.hv-max #hvPlayerBody { aspect-ratio: auto !important; flex: 1 1 auto !important; min-height: 0 !important; }

    /* ===== Tema ESCURO (só nesta tela) — camada de override ===== */
    .hv-container[data-theme="dark"] { --hv-panel:#161c25; --hv-border:#28323f; --hv-muted:#9aa7b6; --hv-soft:#1c2632; }
    .hv-container[data-theme="dark"] .hv-prefs { background: #161c25; border-color: #28323f; }
    .hv-container[data-theme="dark"] .hv-curso-card { background: #141a22; border-color: #28323f; }
    .hv-container[data-theme="dark"] .hv-curso-cab { border-bottom-color: #28323f; }
    .hv-container[data-theme="dark"] .hv-curso-cab h3 { color: #e8eef5; }
    .hv-container[data-theme="dark"] .hv-cap-titulo { color: #dbe4ee; }
    .hv-container[data-theme="dark"] .hv-folha-titulo { color: #e2e9f1; }
    .hv-container[data-theme="dark"] .hv-desc,
    .hv-container[data-theme="dark"] .hv-progress-txt { color: #9aa7b6; }
    .hv-container[data-theme="dark"] .hv-item.item-liberado { background: #13251c; border-left-color: #2f9e63; }
    .hv-container[data-theme="dark"] .hv-item.item-concluido { background: #122a1e; }
    .hv-container[data-theme="dark"] .hv-item.item-bloqueado { background: #1a222c; }
    .hv-container[data-theme="dark"] .hv-itens { border-right-color: #28323f; }
    .hv-container[data-theme="dark"] .hv-main-empty { color: #8fa0b2; }
    .hv-container[data-theme="dark"] .hv-progress { background: #28323f; }
    .hv-container[data-theme="dark"] .hv-btn-secondary { background: #141a22; color: #7fc6a3 !important; border-color: #2f6a4f; }
    .hv-container[data-theme="dark"] .hv-btn-ghost { background: transparent; color: #9aa7b6 !important; border-color: #28323f; }

    /* Escuro cobrindo o SHELL da página (header / conteúdo / rodapé) — vale só nesta tela de assistir */
    html[data-theme="dark"] body { background: #0d1117 !important; }
    html[data-theme="dark"] #container.miolo,
    html[data-theme="dark"] .miolo { background: #0d1117 !important; color: #e7ecf3; }
    html[data-theme="dark"] .cabecalho { background: #111722 !important; border-color: #28323f !important; box-shadow: none !important; }
    html[data-theme="dark"] .cabecalho a,
    html[data-theme="dark"] .cabecalho .navbar-text,
    html[data-theme="dark"] .cabecalho .navbar-brand,
    html[data-theme="dark"] .cabecalho li > a { color: #dfe6ef !important; }
    html[data-theme="dark"] .cabecalho a:hover { color: #ffffff !important; }
    html[data-theme="dark"] .cabecalho .dropdown-menu { background: #1a2230 !important; border-color: #28323f !important; }
    html[data-theme="dark"] .cabecalho .dropdown-menu > li > a { color: #dfe6ef !important; }
    html[data-theme="dark"] .cabecalho .dropdown-menu > li > a:hover { background: #232d3c !important; }
    html[data-theme="dark"] .rodape.footer { background: #0b0f14 !important; }
    html[data-theme="dark"] .rodape.footer .footer-box,
    html[data-theme="dark"] .rodape.footer .footer-box.copyright { background: transparent !important; }

    /* ===== Escala de FONTE (só nesta tela) ===== */
    .hv-container[data-fs="2"] .hv-folha-titulo, .hv-container[data-fs="2"] .hv-cap-titulo { font-size: 16px; }
    .hv-container[data-fs="2"] .hv-desc { font-size: 13px; }
    .hv-container[data-fs="2"] .hv-btn { font-size: 13px; }
    .hv-container[data-fs="2"] .hv-curso-cab h3 { font-size: 19px; }
    .hv-container[data-fs="3"] .hv-folha-titulo, .hv-container[data-fs="3"] .hv-cap-titulo { font-size: 18px; }
    .hv-container[data-fs="3"] .hv-desc { font-size: 14px; }
    .hv-container[data-fs="3"] .hv-btn { font-size: 14px; }
    .hv-container[data-fs="3"] .hv-curso-cab h3 { font-size: 21px; }

    /* ===== Mobile: lateral vira 1 coluna com "gaveta" das aulas ===== */
    @media (max-width: 720px) {
        .hv-container[data-layout="lateral"] .hv-card-body { grid-template-columns: 1fr; }
        .hv-container[data-layout="lateral"] .hv-itens { max-height: none; border-right: 0; border-bottom: 1px solid var(--hv-border,#eee); }
        .hv-container[data-layout="lateral"] .hv-drawer-toggle { display: inline-flex; align-items: center; gap: 7px; margin: 4px 0 10px; background: #2d6a4f; color: #fff; border: 0; border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .hv-container[data-layout="lateral"].hv-drawer-closed .hv-itens { display: none; }
    }

    /* ===== Painel de mensagens privadas aluno<->professor ===== */
    .hv-msg-badge { display: inline-block; min-width: 16px; padding: 0 5px; height: 16px; line-height: 16px; text-align: center; font-size: 10px; font-weight: 700; background: #e8842a; color: #fff; border-radius: 9px; margin-left: 3px; }
    .hv-msg { display: flex; flex-direction: column; gap: 12px; }
    .hv-msg-head { display: flex; flex-direction: column; gap: 2px; }
    .hv-msg-head .t { font-weight: 700; font-size: 14px; color: var(--hv-ink,#1f2937); }
    .hv-msg-head .s { font-size: 12px; color: var(--hv-muted,#6c757d); }
    .hv-msg-thread { display: flex; flex-direction: column; gap: 8px; max-height: 420px; overflow-y: auto; padding: 4px; }
    .hv-msg-loading, .hv-msg-empty { color: var(--hv-muted,#6c757d); font-size: 13px; text-align: center; padding: 24px 8px; }
    .hv-bub { max-width: 82%; padding: 9px 12px; border-radius: 12px; font-size: 13px; line-height: 1.4; white-space: pre-wrap; word-break: break-word; }
    .hv-bub-who { font-size: 11px; font-weight: 700; margin-bottom: 3px; opacity: .8; }
    .hv-bub-when { font-size: 10px; margin-top: 5px; opacity: .7; }
    .hv-bub.them { align-self: flex-start; background: #f1f3f6; color: #1f2937; border-bottom-left-radius: 4px; }
    .hv-bub.me { align-self: flex-end; background: #2d6a4f; color: #fff; border-bottom-right-radius: 4px; }
    .hv-msg-composer { display: flex; gap: 8px; }
    .hv-msg-composer input { flex: 1; padding: 9px 12px; border: 1px solid var(--hv-border,#e0e0e0); border-radius: 8px; font-size: 13px; background: var(--hv-panel,#fff); color: var(--hv-ink,#1f2937); }
    .hv-container[data-theme="dark"] .hv-bub.them { background: #1c2632; color: #e2e9f1; }
    .hv-container[data-theme="dark"] .hv-msg-head .t { color: #e8eef5; }

    #hvMsgModal { display: none; position: fixed; z-index: 99200; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
    #hvMsgModal.show { display: flex; }
    #hvMsgBox { background: #fff; width: 92%; max-width: 560px; max-height: 84vh; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; }
    #hvMsgHeader { padding: 14px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #2d6a4f; color: #fff; }
    #hvMsgHeader h4 { margin: 0; font-size: 15px; }
    #hvMsgHeader button { background: transparent; color: #fff; border: none; font-size: 18px; cursor: pointer; }
    #hvMsgModalBody { padding: 16px 20px; overflow-y: auto; }
</style>

<?php if ($previewAdmin) { ?>
<div style="max-width:980px; margin:12px auto 0; padding:10px 16px; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:8px; font-size:13px;">
    <i class="fa fa-eye"></i> <strong>Modo pré-visualização (admin)</strong> — conteúdo exibido sem os filtros de inscrito. Progresso, conclusão, comentários e envio de tarefa ficam desabilitados.
</div>
<?php } ?>
<div class="hv-container" id="hvRoot" data-layout="classroom" data-theme="light" data-fs="1">
    <?php
    // Curso selecionado (GET cod_curso) => modo DETALHE; senao => CATALOGO
    $codCursoSel = isset($_GET['cod_curso']) ? (int) $_GET['cod_curso'] : 0;
    $cursoDetalhe = null;
    if ($codCursoSel > 0) {
        foreach ($cursos as $cuSel) {
            if ((int) $cuSel->getCodCurso() === $codCursoSel) { $cursoDetalhe = $cuSel; break; }
        }
    }
    $urlBaseCurso = 'index.php?content=view/inscricao/inscricao_congresso_curso.php' . ($previewAdmin ? '&preview=1' : '');
    ?>
    <div class="hv-prefs" id="hvPrefs">
        <?php if ($cursoDetalhe !== null) { ?>
        <div class="grp"><span class="lbl">Layout</span>
            <div class="hv-seg">
                <button type="button" data-set="layout" data-val="lateral" aria-pressed="true">Lateral</button>
                <button type="button" data-set="layout" data-val="classroom" aria-pressed="false">Classroom</button>
                <button type="button" data-set="layout" data-val="atual" aria-pressed="false">Clássico</button>
            </div>
        </div>
        <?php } ?>
        <div class="grp"><span class="lbl">Tema</span>
            <div class="hv-seg">
                <button type="button" data-set="theme" data-val="light" aria-pressed="true">Claro</button>
                <button type="button" data-set="theme" data-val="dark" aria-pressed="false">Escuro</button>
            </div>
        </div>
        <div class="grp"><span class="lbl">Fonte</span>
            <div class="hv-seg">
                <button type="button" data-set="fs" data-val="1" aria-pressed="true">A</button>
                <button type="button" data-set="fs" data-val="2" aria-pressed="false">A+</button>
                <button type="button" data-set="fs" data-val="3" aria-pressed="false">A++</button>
            </div>
        </div>
    </div>
    <?php if (empty($cursos)) { ?>
        <div class="hv-curso-card" style="padding:30px; text-align:center; color:#6c757d;">
            Nenhum curso publicado ainda.
        </div>

    <?php } elseif ($cursoDetalhe === null) {
        // ===================== CATÁLOGO =====================
        ?>
        <div class="hv-cat-topo">
            <h2 class="hv-cat-titulo">Meus cursos</h2>
            <!-- espaço reservado para ações futuras (adicionar / comprar curso) -->
        </div>
        <div class="hv-catalogo">
            <?php foreach ($cursos as $cu) {
                $codCurso = (int) $cu->getCodCurso();
                $totalFolhas = 0; $folhasFeitas = 0;
                $nodes = isset($porCurso[$codCurso]) ? $porCurso[$codCurso] : array();
                hv_contar_folhas($nodes, 0, $totalFolhas, $folhasFeitas, $concluidos);
                $pct = $totalFolhas > 0 ? round(100 * $folhasFeitas / $totalFolhas) : 0;
                $hi = $cu->getHoraInicio() ? substr($cu->getHoraInicio(), 0, 5) : '';
                $hf = $cu->getHoraFim() ? substr($cu->getHoraFim(), 0, 5) : '';
                $janela = ($hi || $hf) ? "Disponível das $hi às $hf" : 'Disponível a qualquer horário';
                $desc = trim((string) $cu->getDescricao());
                ?>
                <a class="hv-cat-card" href="<?php echo htmlspecialchars($urlBaseCurso . '&cod_curso=' . $codCurso); ?>">
                    <div class="hv-cat-capa"><i class="fa fa-graduation-cap"></i></div>
                    <div class="hv-cat-corpo">
                        <h3 class="hv-cat-nome"><?php echo htmlspecialchars($cu->getTitulo()); ?></h3>
                        <?php if ($desc !== '') { ?><p class="hv-cat-desc"><?php echo htmlspecialchars($desc); ?></p><?php } ?>
                        <div class="hv-cat-meta"><i class="fa fa-clock-o"></i> <?php echo htmlspecialchars($janela); ?></div>
                        <div class="hv-cat-prog">
                            <div class="hv-progress"><div class="hv-progress-bar" style="width:<?php echo $pct; ?>%;"></div></div>
                            <div class="hv-cat-progtxt"><?php echo $pct; ?>% &middot; <?php echo $folhasFeitas; ?>/<?php echo $totalFolhas; ?> aulas</div>
                        </div>
                    </div>
                    <div class="hv-cat-rodape">
                        <span class="hv-btn hv-btn-primary"><?php echo $pct > 0 ? 'Continuar' : 'Entrar'; ?> <i class="fa fa-arrow-right"></i></span>
                    </div>
                </a>
            <?php } ?>
        </div>

    <?php } else {
        // ===================== DETALHE DE UM CURSO =====================
        $cu = $cursoDetalhe;
        $codCurso = (int) $cu->getCodCurso();
        $totalFolhas = 0; $folhasFeitas = 0;
        $nodes = isset($porCurso[$codCurso]) ? $porCurso[$codCurso] : array();
        hv_contar_folhas($nodes, 0, $totalFolhas, $folhasFeitas, $concluidos);
        $pct = $totalFolhas > 0 ? round(100 * $folhasFeitas / $totalFolhas) : 0;
        $hi = $cu->getHoraInicio() ? substr($cu->getHoraInicio(), 0, 5) : '';
        $hf = $cu->getHoraFim() ? substr($cu->getHoraFim(), 0, 5) : '';
        $janela = ($hi || $hf) ? "Disponível das $hi às $hf" : 'Disponível em qualquer horário';
        ?>
        <a class="hv-voltar" href="<?php echo htmlspecialchars($urlBaseCurso); ?>"><i class="fa fa-arrow-left"></i> Voltar aos cursos</a>
        <div class="hv-curso-card">
            <div class="hv-curso-cab">
                <div>
                    <h3><?php echo htmlspecialchars($cu->getTitulo()); ?></h3>
                    <div class="hv-curso-meta"><?php echo htmlspecialchars($janela); ?></div>
                </div>
                <div class="hv-progress-wrap">
                    <div class="hv-progress"><div class="hv-progress-bar" style="width:<?php echo $pct; ?>%;"></div></div>
                    <div class="hv-progress-txt">Progresso: <?php echo $pct; ?>% &nbsp;·&nbsp; <?php echo $folhasFeitas; ?> / <?php echo $totalFolhas; ?> aulas</div>
                </div>
            </div>
            <button type="button" class="hv-drawer-toggle" aria-label="Mostrar ou ocultar as aulas"><i class="fa fa-bars"></i> Aulas do curso</button>
            <div class="hv-card-body">
                <div class="hv-itens">
                    <?php
                    if (!empty($nodes[0])) {
                        hv_render_arvore($nodes, 0, 0, $concluidos, $codInscrito, $msgNaoLidas, $itensAtivos);
                    } else {
                        echo '<div style="padding:20px; text-align:center; color:#6c757d;">Sem itens cadastrados neste curso.</div>';
                    }
                    ?>
                </div>
                <aside class="hv-main"><div class="hv-main-empty">Selecione uma aula à esquerda para assistir aqui.</div></aside>
            </div>
        </div>
    <?php } ?>
</div>

<div id="hvPlayerModal">
    <div id="hvPlayerBar">
        <span id="hvPlayerInfo"></span>
        <div class="hv-player-actions">
            <button id="hvPlayerMax" type="button" onclick="hvPlayerMaximizar()" title="Maximizar / restaurar"><i class="fa fa-expand"></i></button>
            <button id="hvPlayerClose" type="button" onclick="hvFecharPlayer()" title="Fechar (Esc)"><i class="fa fa-times"></i> Fechar</button>
        </div>
    </div>
    <div id="hvPlayerBody">
        <iframe id="hvPlayerIframe" src="" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
        <div id="hvPlayerShield"></div>
    </div>
    <div id="hvPlayerResize"></div>
</div>

<div id="hvMsgModal">
    <div id="hvMsgBox">
        <div id="hvMsgHeader">
            <h4 id="hvMsgModalTitulo">Mensagens com o professor</h4>
            <button type="button" onclick="hvFecharMsgModal()">&times;</button>
        </div>
        <div id="hvMsgModalBody"></div>
    </div>
</div>

<div id="hvConfirmModal">
    <div id="hvConfirmBox">
        <h3>Você está aí?</h3>
        <p>Para validar sua presença, clique no botão abaixo dentro do tempo.</p>
        <div id="hvConfirmTimer">--</div>
        <button id="hvConfirmBtn" onclick="hvResponderConfirmacao()">Sim, estou assistindo</button>
    </div>
</div>

<!-- Upload de tarefa -->
<div id="hvTarefaModal" style="display:none; position:fixed; z-index:99500; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; width:92%; max-width:520px; border-radius:12px; overflow:hidden;">
        <div style="background:#2d6a4f; color:#fff; padding:14px 20px; display:flex; justify-content:space-between; align-items:center;">
            <h4 style="margin:0; font-size:15px;" id="hvTarefaTitulo">Enviar tarefa</h4>
            <button onclick="hvFecharTarefa()" style="background:transparent; color:#fff; border:none; font-size:18px; cursor:pointer;"><i class="fa fa-times"></i></button>
        </div>
        <div style="padding:20px;">
            <div id="hvTarefaExistente" style="display:none; padding:10px; background:#f0fdf4; border-radius:6px; margin-bottom:14px; font-size:13px; color:#1f7a1f;"></div>
            <form id="hvTarefaForm" enctype="multipart/form-data" onsubmit="event.preventDefault(); hvEnviarTarefa();">
                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Arquivo (PDF, DOC, imagem, etc.):</label>
                    <input type="file" id="hvTarefaArquivo" name="arquivo" required style="width:100%;">
                </div>
                <progress id="hvTarefaProgresso" value="0" max="100" style="width:100%; display:none;"></progress>
                <div id="hvTarefaMsg" style="margin:10px 0; min-height:20px; font-size:13px;"></div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" class="hv-btn hv-btn-ghost" onclick="hvFecharTarefa()">Cancelar</button>
                    <button type="submit" class="hv-btn hv-btn-primary"><i class="fa fa-upload"></i> Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="hvCommentsModal">
    <div id="hvCommentsBox">
        <div id="hvCommentsHeader">
            <h4 id="hvCommentsTitulo">Comentários</h4>
            <button onclick="hvFecharComentarios()"><i class="fa fa-times"></i></button>
        </div>
        <div id="hvCommentsList"><em>Carregando...</em></div>
        <div id="hvCommentsForm">
            <textarea id="hvCommentsTexto" placeholder="Escreva um comentário..."></textarea>
            <button id="hvCommentsBtn" onclick="hvEnviarComentario()">Enviar</button>
        </div>
    </div>
</div>

<div id="hvAviso" class="hv-aviso"></div>

<script>
(function() {
    var URL_AJAX = '<?php echo WB_URL_COMMAND ?>';
    var HV_PREVIEW = <?php echo $previewAdmin ? 'true' : 'false'; ?>;
    var hv = {
        codCursoItem: null, codSessao: null, tipoEmbed: null,
        intervaloMin: 5, intervaloMax: 5, timeoutSeg: 60,
        controlePresenca: 1,
        pingTimer: null, confirmTimer: null, timerCountdown: null,
        countdown: 0, tempoAcumulado: 0, ativa: false,
        codItemComentando: null
    };
    window._hv = hv;

    function uuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function aviso(msg, tipo) {
        var $a = $('#hvAviso');
        $a.removeClass('erro sucesso');
        if (tipo) $a.addClass(tipo);
        $a.text(msg).fadeIn(200);
        clearTimeout(hv._avisoT);
        hv._avisoT = setTimeout(function() { $a.fadeOut(300); }, 3500);
    }

    function registrar(tipo, cb) {
        if (HV_PREVIEW) { if (cb) cb({}); return; }
        $.post(URL_AJAX, {
            c: 'GerenciarCursoItemPresenca', m: 'registrarEvento', _submit_check: '1',
            codCursoItem: hv.codCursoItem, codSessao: hv.codSessao,
            tipoEvento: tipo, tempoAcumulado: hv.tempoAcumulado
        }, function(data) {
            if (data && data.tipoFinal === 'FORA_HORARIO') {
                aviso('Fora do horário permitido. Sessão encerrada.', 'erro');
                hvFecharPlayer();
            }
            if (data && data.concluiuAgora) {
                aviso('Item concluído! Recarregando...', 'sucesso');
                setTimeout(function() { location.reload(); }, 1500);
            }
            if (cb) cb(data);
        }, 'json').fail(function() {});
    }

    function pausarPlayer() {
        var iframe = document.getElementById('hvPlayerIframe');
        if (!iframe || !iframe.contentWindow) return;
        try {
            if (hv.tipoEmbed === 'vimeo') iframe.contentWindow.postMessage('{"method":"pause"}', '*');
            else if (hv.tipoEmbed === 'youtube') iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
        } catch (e) {}
    }
    function retomarPlayer() {
        var iframe = document.getElementById('hvPlayerIframe');
        if (!iframe || !iframe.contentWindow) return;
        try {
            if (hv.tipoEmbed === 'vimeo') iframe.contentWindow.postMessage('{"method":"play"}', '*');
            else if (hv.tipoEmbed === 'youtube') iframe.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
        } catch (e) {}
    }

    function posicionarConfirmacaoAleatoria() {
        var $box = $('#hvConfirmBox');
        $box.css({ top: '-9999px', left: '-9999px', transform: 'none' });
        var bw = $box.outerWidth(), bh = $box.outerHeight();
        var vw = window.innerWidth, vh = window.innerHeight;
        var margem = 20;
        var maxLeft = Math.max(margem, vw - bw - margem);
        var maxTop  = Math.max(margem, vh - bh - margem);
        var left = Math.floor(margem + Math.random() * (maxLeft - margem));
        var top  = Math.floor(margem + Math.random() * (maxTop  - margem));
        $box.css({ top: top + 'px', left: left + 'px', transform: 'none' });
    }

    function agendarProximaConfirmacao() {
        clearTimeout(hv.confirmTimer);
        var min = Math.max(1, hv.intervaloMin);
        var max = Math.max(min, hv.intervaloMax);
        var minutos = (min === max) ? min : (min + Math.random() * (max - min));
        hv.confirmTimer = setTimeout(mostrarConfirmacao, Math.round(minutos * 60 * 1000));
    }

    function mostrarConfirmacao() {
        if (!hv.ativa) return;
        pausarPlayer();
        $('#hvConfirmModal').addClass('show');
        posicionarConfirmacaoAleatoria();
        hv.countdown = hv.timeoutSeg;
        $('#hvConfirmTimer').text(hv.countdown);
        clearInterval(hv.timerCountdown);
        hv.timerCountdown = setInterval(function() {
            hv.countdown--;
            $('#hvConfirmTimer').text(hv.countdown);
            if (hv.countdown <= 0) {
                clearInterval(hv.timerCountdown);
                $('#hvConfirmModal').removeClass('show');
                registrar('CONFIRMACAO_EXPIRADA');
                registrar('PAUSA');
                aviso('Confirmação expirada. Vídeo pausado.', 'erro');
            }
        }, 1000);
    }

    window.hvResponderConfirmacao = function() {
        clearInterval(hv.timerCountdown);
        $('#hvConfirmModal').removeClass('show');
        registrar('CONFIRMACAO_OK', function() {
            retomarPlayer();
            agendarProximaConfirmacao();
        });
    };

    window.hvAbrirPlayer = function(codCI, urlEmbed, titulo, intMin, intMax, timeoutSeg, tipoEmbed, controlePresenca) {
        if (!urlEmbed) { aviso('URL não configurada para este item.', 'erro'); return; }
        hv.codCursoItem = codCI;
        hv.codSessao = uuid();
        hv.tipoEmbed = tipoEmbed;
        hv.intervaloMin = intMin || 5;
        hv.intervaloMax = intMax || hv.intervaloMin;
        hv.timeoutSeg = timeoutSeg || 60;
        hv.controlePresenca = controlePresenca || 0;
        hv.tempoAcumulado = 0;
        hv.ativa = true;
        $('#hvPlayerInfo').text(titulo);
        $('#hvPlayerIframe').attr('src', urlEmbed);
        if (hvLateralAtivo() && hvDockPlayer(codCI)) {
            /* player docado no painel central (layout lateral) */
        } else {
            hvUndockPlayer();
            hvPosicionarPlayerPadrao();
        }
        $('#hvPlayerModal').addClass('show');
        registrar('INICIO');

        clearInterval(hv.pingTimer);
        if (hv.controlePresenca == 1) {
            hv.pingTimer = setInterval(function() {
                if (!hv.ativa) return;
                hv.tempoAcumulado += 30;
                registrar('PING');
            }, 30000);
            agendarProximaConfirmacao();
        } else {
            hv.pingTimer = setInterval(function() {
                if (hv.ativa) hv.tempoAcumulado += 30;
            }, 30000);
        }
    };

    window.hvFecharPlayer = function() {
        if (hv.ativa) registrar('FIM');
        hv.ativa = false;
        clearInterval(hv.pingTimer);
        clearTimeout(hv.confirmTimer);
        clearInterval(hv.timerCountdown);
        $('#hvPlayerModal').removeClass('show hv-max');
        $('#hvConfirmModal').removeClass('show');
        $('#hvPlayerIframe').attr('src', '');
        var icMax = document.querySelector('#hvPlayerMax i'); if (icMax) icMax.className = 'fa fa-expand';
        hvUndockPlayer();
    };

    // ---- Player: janela flutuante (mover / redimensionar / maximizar) ----
    function hvPosicionarPlayerPadrao() {
        var pm = document.getElementById('hvPlayerModal');
        if (!pm) return;
        pm.classList.remove('hv-max');
        var ic = document.querySelector('#hvPlayerMax i');
        if (ic) ic.className = 'fa fa-expand';
        var dw = Math.min(720, window.innerWidth - 40);
        var dh = Math.min(Math.round(dw * 9 / 16) + 38, window.innerHeight - 40);
        pm.style.width = dw + 'px';
        pm.style.height = dh + 'px';
        pm.style.left = Math.max(0, Math.round((window.innerWidth - dw) / 2)) + 'px';
        pm.style.top = Math.max(0, Math.round((window.innerHeight - dh) / 2) - 20) + 'px';
        pm.style.right = 'auto';
        pm.style.bottom = 'auto';
    }

    window.hvPlayerMaximizar = function() {
        var pm = document.getElementById('hvPlayerModal');
        var ic = document.querySelector('#hvPlayerMax i');
        pm.classList.toggle('hv-max');
        if (pm.classList.contains('hv-max')) {
            if (ic) ic.className = 'fa fa-compress';
        } else {
            if (ic) ic.className = 'fa fa-expand';
            hvPosicionarPlayerPadrao();
        }
    };

    (function initPlayerJanela() {
        var pm = document.getElementById('hvPlayerModal');
        var bar = document.getElementById('hvPlayerBar');
        var rez = document.getElementById('hvPlayerResize');
        var shield = document.getElementById('hvPlayerShield');
        if (!pm || !bar || !rez) return;
        var st = null;
        function shieldOn(on) { if (shield) shield.style.display = on ? 'block' : 'none'; }
        bar.addEventListener('mousedown', function(e) {
            if (e.target.closest('button')) return;
            if (pm.classList.contains('hv-max')) return;
            var r = pm.getBoundingClientRect();
            st = { mode: 'move', dx: e.clientX - r.left, dy: e.clientY - r.top };
            shieldOn(true);
            e.preventDefault();
        });
        rez.addEventListener('mousedown', function(e) {
            if (pm.classList.contains('hv-max')) return;
            var r = pm.getBoundingClientRect();
            st = { mode: 'resize', sx: e.clientX, sy: e.clientY, sw: r.width, sh: r.height };
            shieldOn(true);
            e.preventDefault();
        });
        document.addEventListener('mousemove', function(e) {
            if (!st) return;
            if (st.mode === 'move') {
                var x = Math.max(0, Math.min(e.clientX - st.dx, window.innerWidth - 90));
                var y = Math.max(0, Math.min(e.clientY - st.dy, window.innerHeight - 44));
                pm.style.left = x + 'px';
                pm.style.top = y + 'px';
            } else {
                var w = Math.max(320, st.sw + (e.clientX - st.sx));
                var h = Math.max(220, st.sh + (e.clientY - st.sy));
                w = Math.min(w, window.innerWidth - pm.offsetLeft - 4);
                h = Math.min(h, window.innerHeight - pm.offsetTop - 4);
                pm.style.width = w + 'px';
                pm.style.height = h + 'px';
            }
        });
        document.addEventListener('mouseup', function() {
            if (st) { st = null; shieldOn(false); }
        });
    })();

    window.hvRegistrarAcessoExterno = function(codCI) {
        hv.codCursoItem = codCI;
        hv.codSessao = uuid();
        hv.tempoAcumulado = 0;
        registrar('INICIO');
    };

    window.hvAbrirTarefa = function(codCI, titulo) {
        hv.codTarefaItem = codCI;
        $('#hvTarefaTitulo').text('Enviar tarefa — ' + titulo);
        $('#hvTarefaArquivo').val('');
        $('#hvTarefaMsg').html('');
        $('#hvTarefaProgresso').hide().val(0);
        $('#hvTarefaExistente').hide().html('');
        $('#hvTarefaModal').css('display', 'flex');
        // Carregar tarefa anterior se houver
        $.post(URL_AJAX, {
            c: 'GerenciarCursoItemTarefa', m: 'carregarMinhaTarefa',
            codCursoItem: codCI, _submit_check: '1'
        }, function(data) {
            if (data && data.statusMessage && data.tarefa) {
                $('#hvTarefaExistente')
                    .html('<i class="fa fa-check-circle"></i> Última versão enviada: <strong>' + data.tarefa.versao + '</strong> em ' + data.tarefa.dat_envio + '<br><small>Arquivo: ' + data.tarefa.arquivo_original + '</small>')
                    .show();
            }
        }, 'json');
    };
    window.hvFecharTarefa = function() {
        $('#hvTarefaModal').hide();
        hv.codTarefaItem = null;
    };
    window.hvEnviarTarefa = function() {
        if (HV_PREVIEW) { $('#hvTarefaMsg').html('<span style="color:#c0392b;">Indisponível no modo pré-visualização.</span>'); return; }
        var fd = new FormData();
        var f = document.getElementById('hvTarefaArquivo').files[0];
        if (!f) { $('#hvTarefaMsg').html('<span style="color:#c0392b;">Selecione um arquivo.</span>'); return; }
        fd.append('arquivo', f);
        fd.append('c', 'GerenciarCursoItemTarefa');
        fd.append('m', 'salvarTarefa');
        fd.append('codCursoItem', hv.codTarefaItem);
        fd.append('_submit_check', '1');
        $.ajax({
            url: URL_AJAX,
            type: 'POST', dataType: 'json',
            data: fd, processData: false, contentType: false,
            xhr: function() {
                var x = $.ajaxSettings.xhr();
                if (x.upload) x.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) $('#hvTarefaProgresso').show().val(Math.round(e.loaded / e.total * 100));
                }, false);
                return x;
            },
            success: function(data) {
                if (data && data.statusMessage) {
                    $('#hvTarefaMsg').html('<span style="color:#1f7a1f;">' + data.message + ' Recarregando...</span>');
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    $('#hvTarefaMsg').html('<span style="color:#c0392b;">' + (data && data.message ? data.message : 'Erro no envio.') + '</span>');
                }
            },
            error: function() { $('#hvTarefaMsg').html('<span style="color:#c0392b;">Erro de rede.</span>'); }
        });
    };

    window.hvMarcarConcluido = function(codCI) {
        if (HV_PREVIEW) { aviso('Indisponível no modo pré-visualização.', 'erro'); return; }
        $.post(URL_AJAX, {
            c: 'GerenciarCursoItemPresenca', m: 'marcarConcluidoManual',
            codCursoItem: codCI, _submit_check: '1'
        }, function(data) {
            if (data && data.statusMessage) {
                aviso('Item marcado como concluído!', 'sucesso');
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                aviso(data && data.message ? data.message : 'Erro ao marcar.', 'erro');
            }
        }, 'json');
    };

    window.hvAbrirComentarios = function(codCI, titulo) {
        hv.codItemComentando = codCI;
        $('#hvCommentsTitulo').text('Comentários — ' + titulo);
        $('#hvCommentsList').html('<em>Carregando...</em>');
        $('#hvCommentsTexto').val('');
        $('#hvCommentsModal').addClass('show');
        $.post(URL_AJAX, {
            c: 'GerenciarCursoItemComentario', m: 'listar',
            codCursoItem: codCI, _submit_check: '1'
        }, function(data) {
            if (!data || !data.comentarios || data.comentarios.length === 0) {
                $('#hvCommentsList').html('<div style="color:#999; text-align:center; padding:20px;">Sem comentários ainda. Seja o primeiro!</div>');
                return;
            }
            var html = '';
            $.each(data.comentarios, function(i, c) {
                var autor = c.autor_inscrito || c.autor_admin || 'Anônimo';
                if (c.cod_usuario) autor = autor + ' (admin)';
                html += '<div class="hv-comment">';
                html += '<span class="autor">' + $('<div>').text(autor).html() + '</span>';
                html += '<span class="data">' + (c.dat_incl || '') + '</span>';
                html += '<div class="texto">' + $('<div>').text(c.texto || '').html() + '</div>';
                html += '</div>';
            });
            $('#hvCommentsList').html(html);
        }, 'json');
    };
    window.hvFecharComentarios = function() {
        $('#hvCommentsModal').removeClass('show');
        hv.codItemComentando = null;
    };
    window.hvEnviarComentario = function() {
        if (HV_PREVIEW) { aviso('Indisponível no modo pré-visualização.', 'erro'); return; }
        var t = $('#hvCommentsTexto').val().trim();
        if (!t) return;
        $.post(URL_AJAX, {
            c: 'GerenciarCursoItemComentario', m: 'comentar',
            codCursoItem: hv.codItemComentando, texto: t, _submit_check: '1'
        }, function(data) {
            if (data && data.statusMessage) {
                $('#hvCommentsTexto').val('');
                hvAbrirComentarios(hv.codItemComentando, $('#hvCommentsTitulo').text().replace('Comentários — ', ''));
            } else {
                aviso(data && data.message ? data.message : 'Erro ao enviar.', 'erro');
            }
        }, 'json');
    };

    window.addEventListener('beforeunload', function() {
        if (hv.ativa && navigator.sendBeacon) {
            navigator.sendBeacon(URL_AJAX,
                new URLSearchParams({
                    c: 'GerenciarCursoItemPresenca', m: 'registrarEvento', _submit_check: '1',
                    codCursoItem: hv.codCursoItem, codSessao: hv.codSessao,
                    tipoEvento: 'FIM', tempoAcumulado: hv.tempoAcumulado
                }).toString());
        }
    });

    // ===== Player docado no painel central (layout lateral) =====
    var hvDockedMain = null;
    function hvLateralAtivo() {
        var r = document.getElementById('hvRoot');
        return !!(r && r.getAttribute('data-layout') === 'lateral');
    }
    function hvRestorePlaceholder(main) {
        if (main) main.innerHTML = '<div class="hv-main-empty">Selecione uma aula à esquerda para assistir aqui.</div>';
    }
    window.hvDockPlayer = function(codCI) {
        var pm = document.getElementById('hvPlayerModal');
        var item = document.querySelector('.hv-item[data-cod="' + codCI + '"]');
        var card = item ? item.closest('.hv-curso-card') : null;
        var main = card ? card.querySelector('.hv-main') : null;
        if (!main) return false;
        if (hvDockedMain && hvDockedMain !== main) hvRestorePlaceholder(hvDockedMain);
        main.innerHTML = '';
        main.appendChild(pm);
        pm.classList.add('hv-docked');
        hvDockedMain = main;
        return true;
    };
    window.hvUndockPlayer = function() {
        var pm = document.getElementById('hvPlayerModal');
        pm.classList.remove('hv-docked');
        if (pm.parentNode !== document.body) document.body.appendChild(pm);
        if (hvDockedMain) { hvRestorePlaceholder(hvDockedMain); hvDockedMain = null; }
    };

    // ===== Mensagens privadas aluno<->professor (na TAREFA) =====
    function hvEsc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function(c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function hvMsgPanelHTML(titulo) {
        return '<div class="hv-msg">'
            + '<div class="hv-msg-head"><span class="t"><i class="fa fa-comments-o"></i> Mensagens com o professor</span><span class="s">' + hvEsc(titulo) + '</span></div>'
            + '<div class="hv-msg-thread"><div class="hv-msg-loading">Carregando…</div></div>'
            + '<div class="hv-msg-composer"><input type="text" class="hv-msg-input" placeholder="Escreva uma mensagem para o professor…" maxlength="2000"><button type="button" class="hv-btn hv-btn-primary" onclick="hvEnviarMensagemTarefa()"><i class="fa fa-paper-plane"></i> Enviar</button></div>'
            + '</div>';
    }
    function hvZerarBadgeMsg(cod) {
        var item = document.querySelector('.hv-item[data-cod="' + cod + '"]');
        if (item) { var b = item.querySelector('.hv-msg-badge'); if (b) b.parentNode.removeChild(b); }
    }
    function hvCarregarThreadMsg() {
        var box = hv.msgThreadEl;
        if (!box) return;
        $.post(URL_AJAX, { c: 'GerenciarCursoTarefaMensagem', m: 'listarThread', codCursoItem: hv.codTarefaMsg, _submit_check: '1' }, function(data) {
            if (!hv.msgThreadEl) return;
            var b = hv.msgThreadEl;
            if (!data || !data.statusMessage) { b.innerHTML = '<div class="hv-msg-empty">' + hvEsc((data && data.message) || 'Erro ao carregar.') + '</div>'; return; }
            var msgs = data.mensagens || [];
            if (!msgs.length) { b.innerHTML = '<div class="hv-msg-empty">Nenhuma mensagem ainda. Escreva a primeira abaixo.</div>'; return; }
            b.innerHTML = msgs.map(function(m) {
                var eu = (m.autor_tipo === 'ALUNO');
                var nome = eu ? 'Você' : ('Prof. ' + (m.autor_prof || ''));
                return '<div class="hv-bub ' + (eu ? 'me' : 'them') + '"><div class="hv-bub-who">' + hvEsc(nome) + '</div>' + hvEsc(m.texto) + '<div class="hv-bub-when">' + hvEsc(m.dat_incl || '') + '</div></div>';
            }).join('');
            b.scrollTop = b.scrollHeight;
        }, 'json');
    }
    window.hvAbrirMensagens = function(cod, titulo) {
        hv.codTarefaMsg = cod;
        var lateral = hvLateralAtivo();
        var item = document.querySelector('.hv-item[data-cod="' + cod + '"]');
        var card = item ? item.closest('.hv-curso-card') : null;
        var main = card ? card.querySelector('.hv-main') : null;
        var target;
        if (lateral && main) {
            if (hv.ativa) hvFecharPlayer(); else hvUndockPlayer();
            main.innerHTML = hvMsgPanelHTML(titulo);
            target = main;
        } else {
            document.getElementById('hvMsgModalTitulo').textContent = 'Mensagens — ' + titulo;
            document.getElementById('hvMsgModalBody').innerHTML = hvMsgPanelHTML(titulo);
            $('#hvMsgModal').addClass('show');
            target = document.getElementById('hvMsgModalBody');
        }
        hv.msgThreadEl = target.querySelector('.hv-msg-thread');
        hv.msgInputEl = target.querySelector('.hv-msg-input');
        hvCarregarThreadMsg();
        hvZerarBadgeMsg(cod);
    };
    window.hvEnviarMensagemTarefa = function() {
        if (HV_PREVIEW) { aviso('Indisponível no modo pré-visualização.', 'erro'); return; }
        var inp = hv.msgInputEl;
        if (!inp) return;
        var txt = (inp.value || '').trim();
        if (!txt) return;
        $.post(URL_AJAX, { c: 'GerenciarCursoTarefaMensagem', m: 'enviar', codCursoItem: hv.codTarefaMsg, texto: txt, _submit_check: '1' }, function(data) {
            if (data && data.statusMessage) { inp.value = ''; hvCarregarThreadMsg(); }
            else aviso((data && data.message) || 'Erro ao enviar.', 'erro');
        }, 'json');
    };
    window.hvFecharMsgModal = function() { $('#hvMsgModal').removeClass('show'); };

    // Esc = saida facil: fecha o player de video ou o modal de mensagens
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            if ($('#hvPlayerModal').hasClass('show')) { hvFecharPlayer(); }
            else if ($('#hvMsgModal').hasClass('show')) { hvFecharMsgModal(); }
        }
    });

    // ===== Preferências (layout / tema / fonte) — localStorage, só nesta tela =====
    (function() {
        var root = document.getElementById('hvRoot');
        if (!root) return;
        function gp(k, d) { try { var v = localStorage.getItem(k); return v === null ? d : v; } catch (e) { return d; } }
        function sp(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }
        root.setAttribute('data-layout', gp('hvLayout', 'classroom'));
        root.setAttribute('data-theme', gp('hvTheme', 'light'));
        root.setAttribute('data-fs', gp('hvFs', '1'));
        document.documentElement.setAttribute('data-theme', root.getAttribute('data-theme'));
        function sync() {
            root.querySelectorAll('#hvPrefs [data-set]').forEach(function(b) {
                var k = b.getAttribute('data-set'), v = b.getAttribute('data-val');
                b.setAttribute('aria-pressed', String(root.getAttribute('data-' + k) === v));
            });
        }
        sync();
        var prefs = document.getElementById('hvPrefs');
        if (prefs) prefs.addEventListener('click', function(e) {
            var b = e.target.closest('[data-set]');
            if (!b) return;
            var k = b.getAttribute('data-set'), v = b.getAttribute('data-val');
            root.setAttribute('data-' + k, v);
            sp('hv' + k.charAt(0).toUpperCase() + k.slice(1), v);
            sync();
            if (k === 'theme') document.documentElement.setAttribute('data-theme', v);
            if (k === 'layout' && v !== 'lateral') hvUndockPlayer();
        });
        root.querySelectorAll('.hv-drawer-toggle').forEach(function(t) {
            t.addEventListener('click', function() { root.classList.toggle('hv-drawer-closed'); });
        });
    })();

    // ===== Folha de ponto: heartbeat enquanto a area do curso esta aberta/visivel =====
    (function() {
        function bater() {
            if (document.hidden) return;
            try { $.post(URL_AJAX, { c: 'GerenciarCursoPonto', m: 'registrar', _submit_check: '1' }); } catch (e) {}
        }
        bater();
        setInterval(bater, 60000);
        document.addEventListener('visibilitychange', function() { if (!document.hidden) bater(); });
    })();

})();
</script>
