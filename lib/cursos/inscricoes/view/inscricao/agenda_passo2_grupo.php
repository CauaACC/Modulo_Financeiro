<?php
require_once(dirname(__FILE__) . "/../../include/config.php");

// Só faz sentido com fluxo AGENDA configurado
if ($OP_TIPO_FLUXO_INSCRICAO != 'AGENDA') {
    $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
    exit();
}

// Anti-bot: rejeita se honeypot preenchido OU submit muito rápido (< 2 segundos)
if (isset($fws_['hp_website']) && $fws_['hp_website'] !== '') {
    if (function_exists('executarLog')) {
        executarLog('AGENDA honeypot passo1: ip=' . ($_SERVER['REMOTE_ADDR'] ?? '') . ' hp=' . substr($fws_['hp_website'], 0, 50), 'antibot-agenda', true);
    }
    $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">"; exit();
}
if (isset($fws_['hp_t']) && (time() - (int) $fws_['hp_t']) < 2) {
    if (function_exists('executarLog')) {
        executarLog('AGENDA timecheck passo1: ip=' . ($_SERVER['REMOTE_ADDR'] ?? '') . ' delta=' . (time() - (int) $fws_['hp_t']) . 's', 'antibot-agenda', true);
    }
    $url = WB_URL_VIEW . "?content=view/inscricao/agenda_passo1_dia.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">"; exit();
}

// Recebe cod_oficina do passo 1 (POST) e persiste em sessão. Também aceita reentrada via sessão
// (usuário voltando de passo posterior pelo stepper, sem POST).
if (isset($fws_['cod_oficina']) && is_array($fws_['cod_oficina']) && count($fws_['cod_oficina']) > 0) {
    $_SESSION['_agenda']['cod_oficina'] = (int) $fws_['cod_oficina'][0];
}
$codOficinaEscolhida = isset($_SESSION['_agenda']['cod_oficina']) ? (int) $_SESSION['_agenda']['cod_oficina'] : 0;

// Guard: sem dia escolhido em sessão, volta para o passo 1.
if (!$codOficinaEscolhida) {
    $_SESSION['MSG'] = 'Escolha primeiro o dia da visita.';
    $url = WB_URL_VIEW . "?content=view/inscricao/agenda_passo1_dia.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
    exit();
}

// cod_perfil: se já foi escolhido antes (usuário voltando), pré-marca o radio
$codPerfilSelecionado = isset($_SESSION['_agenda']['cod_perfil']) ? (int) $_SESSION['_agenda']['cod_perfil'] : 0;

require_once 'inscricao_congresso_tit_area_inscrito.php';

$debug = false;
$objOficina = GerenciarAgendaAC::carregarOficinaPorCodigo($codOficinaEscolhida, $debug);
if (!$objOficina || !$objOficina->getCodOficina()) {
    $_SESSION['MSG'] = 'Dia escolhido não foi encontrado. Selecione novamente.';
    $url = WB_URL_VIEW . "?content=view/inscricao/agenda_passo1_dia.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
    exit();
}

$perfis = GerenciarAgendaAC::carregarPerfisDoEvento(COD_EVENTO, $debug);

// No banco do projeto a coluna que vale é data_inicio (data_ini é legado/vazio)
$dataIni = $objOficina->getDataInicio() ?: $objOficina->getDataIni();
$diaFmt = isDateStringValid($dataIni) ? formatDateView($dataIni) : '';
$horarioIni = $objOficina->getHorarioIni();
$horarioFim = $objOficina->getHorarioFim();
$horarioFmt = trim($horarioIni . ($horarioFim ? ' às ' . $horarioFim : ''));
?>
<style>
    .agenda-wrap { max-width: 720px; margin: 0 auto; padding: 0 15px; }
    .agenda-progress { display: flex; justify-content: center; margin: 10px 0 25px; }
    .agenda-progress .step { text-align: center; padding: 4px 16px; flex: 1; max-width: 200px; }
    .agenda-progress .num { display: inline-block; width: 34px; height: 34px; line-height: 34px; border-radius: 50%; color: #fff; font-weight: bold; font-size: 15px; margin-bottom: 4px; }
    .agenda-progress .step.active .num   { background: #2D937E; box-shadow: 0 0 0 4px rgba(45,147,126,0.15); }
    .agenda-progress .step.inactive .num { background: #ccc; }
    .agenda-progress .step.done .num     { background: #2D937E; }
    .agenda-progress .label-step { display: block; font-size: 12px; color: #888; }
    .agenda-progress .step.active .label-step { color: #2D937E; font-weight: bold; }
    .agenda-progress .step.done .label-step   { color: #2D937E; }
    .agenda-progress a.step-link { text-decoration: none; cursor: pointer; transition: opacity 0.15s; }
    .agenda-progress a.step-link:hover { opacity: 0.7; }

    .agenda-titulo { text-align: center; color: #333; margin: 5px 0 8px; font-size: 22px; }

    .agenda-dia-escolhido { background: #f6fbfa; border: 1px solid #d3e8e2; border-left: 4px solid #2D937E; padding: 12px 18px; margin: 0 0 22px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; gap: 14px; }
    .agenda-dia-escolhido .info { flex: 1; }
    .agenda-dia-escolhido .label { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
    .agenda-dia-escolhido .valor { font-size: 16px; color: #2D937E; font-weight: bold; margin-top: 2px; }
    .agenda-dia-escolhido .valor small { display: block; color: #666; font-weight: normal; font-size: 13px; margin-top: 2px; }
    .agenda-dia-escolhido .trocar { font-size: 13px; color: #666; text-decoration: underline; }
    .agenda-dia-escolhido .trocar:hover { color: #2D937E; }

    .agenda-grupo-card { display: flex; align-items: center; gap: 14px; padding: 16px 20px; margin-bottom: 10px; border: 1px solid #e0e0e0; border-left: 4px solid #2D937E; border-radius: 6px; background: #fff; cursor: pointer; transition: box-shadow 0.2s; }
    .agenda-grupo-card:hover { box-shadow: 0 2px 10px rgba(45,147,126,0.18); }
    .agenda-grupo-card.selecionado { background: #f6fbfa; box-shadow: 0 2px 10px rgba(45,147,126,0.25); }
    .agenda-grupo-card input[type=radio] { transform: scale(1.25); cursor: pointer; }
    .agenda-grupo-card .info { flex: 1; }
    .agenda-grupo-card .titulo { font-size: 16px; font-weight: bold; color: #333; margin: 0; }
    .agenda-grupo-card .desc { font-size: 13px; color: #666; line-height: 1.4; margin-top: 3px; }
    .agenda-grupo-card .valor { font-size: 20px; font-weight: bold; color: #2D937E; white-space: nowrap; }

    .agenda-vazia { text-align: center; padding: 30px; color: #888; background: #fafafa; border-radius: 6px; }

    .agenda-botoes { display: flex; justify-content: space-between; align-items: center; padding: 18px 0 5px; margin-top: 18px; border-top: 1px solid #eee; }
    .agenda-botoes .btn-agenda-link { color: #666; text-decoration: none; font-size: 14px; padding: 9px 12px; }
    .agenda-botoes .btn-agenda-link:hover { color: #2D937E; text-decoration: underline; }
    .agenda-botoes .btn-agenda-primary { background: #2D937E; color: #fff; border: none; padding: 11px 32px; border-radius: 4px; font-size: 15px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
    .agenda-botoes .btn-agenda-primary:hover { background: #247a68; }
    .agenda-botoes .btn-agenda-primary:disabled { background: #ccc; cursor: not-allowed; }
</style>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="agenda-wrap">
                <div class="agenda-progress">
                    <a class="step done step-link" href="index.php?content=view/inscricao/agenda_passo1_dia.php">
                        <span class="num">&#10003;</span>
                        <span class="label-step">Dia da Visita</span>
                    </a>
                    <div class="step active">
                        <span class="num">2</span>
                        <span class="label-step">Tamanho do Grupo</span>
                    </div>
                    <div class="step inactive">
                        <span class="num">3</span>
                        <span class="label-step">Dados Pessoais</span>
                    </div>
                </div>

                <div class="agenda-dia-escolhido">
                    <div class="info">
                        <div class="label">Dia escolhido</div>
                        <div class="valor">
                            <?php echo $diaFmt; ?><?php echo $horarioFmt ? ' &middot; ' . $horarioFmt : ''; ?>
                            <small><?php echo htmlspecialchars($objOficina->getTitulo()); ?><?php echo $objOficina->getLocal() ? ' &middot; ' . htmlspecialchars($objOficina->getLocal()) : ''; ?></small>
                        </div>
                    </div>
                    <a href="index.php?content=view/inscricao/agenda_passo1_dia.php" class="trocar">trocar dia</a>
                </div>

                <h3 class="agenda-titulo">Escolha o tamanho do grupo</h3>

                <form method="POST" name="form" action="index.php?content=view/inscricao/agenda_grupo_submit.php" id="formAgenda2">
                    <input type="hidden" name="cod_oficina[]" value="<?php echo $codOficinaEscolhida; ?>">
                    <?php // Anti-bot: honeypot + timestamp ?>
                    <input type="text" name="hp_website" value="" tabindex="-1" autocomplete="off"
                           style="position:absolute;left:-9999px;opacity:0;height:0;width:0;" aria-hidden="true">
                    <input type="hidden" name="hp_t" value="<?php echo time(); ?>">

                    <?php if (count($perfis) == 0) { ?>
                        <div class="agenda-vazia">Não há tamanhos de grupo configurados para este evento.</div>
                    <?php } else {
                        foreach ($perfis as $perfil) {
                            $codP = (int) $perfil->getCodPerfil();
                            $valor = floatval($perfil->getValorTaxa());
                            $valorFmt = $valor > 0 ? 'R$ ' . number_format($valor, 2, ',', '.') : 'Gratuito';
                            // Sem pré-seleção ao recarregar — radio começa vazio sempre
                            ?>
                            <label class="agenda-grupo-card">
                                <input type="radio" name="cod_perfil" value="<?php echo $codP; ?>" required>
                                <div class="info">
                                    <div class="titulo"><?php echo htmlspecialchars($perfil->getTitulo()); ?></div>
                                    <?php if ($perfil->getDescricao()) { ?><div class="desc"><?php echo $perfil->getDescricao(); ?></div><?php } ?>
                                </div>
                                <div class="valor"><?php echo $valorFmt; ?></div>
                            </label>
                            <?php
                        }
                    } ?>

                    <div class="agenda-botoes">
                        <a href="index.php?content=view/inscricao/agenda_passo1_dia.php" class="btn-agenda-link">&larr; Voltar</a>
                        <button type="submit" class="btn-agenda-primary" id="btnAvancar" disabled>Avançar &rarr;</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#formAgenda2').on('change', 'input[type=radio][name=cod_perfil]', function(){
        $('.agenda-grupo-card').removeClass('selecionado');
        $(this).closest('.agenda-grupo-card').addClass('selecionado');
        $('#btnAvancar').prop('disabled', false);
    });
});
</script>
