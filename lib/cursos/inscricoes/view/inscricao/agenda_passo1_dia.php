<?php
require_once(dirname(__FILE__) . "/../../include/config.php");

// Só faz sentido com fluxo AGENDA configurado
if ($OP_TIPO_FLUXO_INSCRICAO != 'AGENDA') {
    $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
    exit();
}

// "Novo agendamento" vindo do modal "Minhas agendas": limpa sessão prévia de escolhas
if (!empty($_GET['novo'])) {
    unset($_SESSION['_agenda']);
}

require_once 'inscricao_congresso_tit_area_inscrito.php';

$debug = false;
$oficinas = GerenciarAgendaAC::carregarOficinasDoEvento(COD_EVENTO, $debug);
$perfis   = GerenciarAgendaAC::carregarPerfisDoEvento(COD_EVENTO, $debug);
list($valorMinimo, $valorMaximo) = GerenciarAgendaAC::calcularFaixaValores($perfis);

// Pré-seleção a partir da sessão (usuário voltou de um passo posterior).
$codOficinaSelecionado = isset($_SESSION['_agenda']['cod_oficina']) ? (int) $_SESSION['_agenda']['cod_oficina'] : 0;

// Step 2 só é clicável se já foi visitado (sessão tem cod_oficina).
$jaVisitouPasso2 = $codOficinaSelecionado > 0;

// Agrupar oficinas por mês (YYYY-MM) — muitos eventos têm 100+ dias, filtrar por mês ajuda
$mesesDisponiveis = array(); // chave YYYY-MM -> ['label' => 'MAI/2026', 'temDisponivel' => bool]
$oficinaParaMes = array();   // cod_oficina -> 'YYYY-MM'
$nomeMeses = array('','JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ');
foreach ($oficinas as $of) {
    $d = $of->getDataInicio() ?: $of->getDataIni();
    if (!isDateStringValid($d)) { continue; }
    $ts = strtotime($d);
    $chave = date('Y-m', $ts);
    $oficinaParaMes[(int) $of->getCodOficina()] = $chave;
    if (!isset($mesesDisponiveis[$chave])) {
        $mesesDisponiveis[$chave] = array(
            'label' => $nomeMeses[(int) date('n', $ts)] . '/' . date('Y', $ts),
            'temDisponivel' => false,
        );
    }
    // Oficinas com data_fim_inscricao vencida já foram filtradas no controller (mesma regra do legado).
    // Aqui só checamos se há vaga real disponível pra mostrar o badge "temDisponivel".
    $temVaga = ((int) $of->getNumeroVagas() > 0) && ((int) $of->getVagasDisponiveis() > 0);
    if ($temVaga) {
        $mesesDisponiveis[$chave]['temDisponivel'] = true;
    }
}
ksort($mesesDisponiveis);

// Mês default: primeiro com dia disponível; se não houver, primeiro mês existente
$mesAtivo = '';
foreach ($mesesDisponiveis as $chave => $info) {
    if ($info['temDisponivel']) { $mesAtivo = $chave; break; }
}
if ($mesAtivo === '' && count($mesesDisponiveis) > 0) {
    $mesAtivo = array_key_first($mesesDisponiveis);
}
// Se sessão já tem cod_oficina, abre no mês dela
if ($codOficinaSelecionado && isset($oficinaParaMes[$codOficinaSelecionado])) {
    $mesAtivo = $oficinaParaMes[$codOficinaSelecionado];
}
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
    .agenda-progress a.step-link { text-decoration: none; cursor: pointer; transition: opacity 0.15s; }
    .agenda-progress a.step-link:hover { opacity: 0.7; }
    .agenda-progress a.step-link .num { background: #2D937E !important; }
    .agenda-progress a.step-link .label-step { color: #2D937E; }

    .agenda-titulo { text-align: center; color: #333; margin: 5px 0 8px; font-size: 22px; }
    .agenda-subtitulo { text-align: center; color: #666; font-size: 14px; margin-bottom: 22px; line-height: 1.5; }
    .agenda-valor-destaque { color: #2D937E; font-weight: bold; }

    .agenda-meses { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin: 0 0 18px; }
    .agenda-meses button { background: #fff; color: #555; border: 1px solid #ccc; padding: 7px 14px; border-radius: 20px; font-size: 13px; cursor: pointer; transition: all 0.15s; font-weight: 500; }
    .agenda-meses button:hover { border-color: #2D937E; color: #2D937E; }
    .agenda-meses button.ativo { background: #2D937E; border-color: #2D937E; color: #fff; }
    .agenda-meses button.sem-vaga { opacity: 0.5; }
    .agenda-card { display: flex; align-items: center; gap: 10px; padding: 8px 12px; margin-bottom: 6px; border: 1px solid #e0e0e0; border-left: 3px solid #2D937E; border-radius: 5px; background: #fff; cursor: pointer; transition: box-shadow 0.2s, border-color 0.2s; }
    .agenda-card.mes-oculto { display: none; }
    .agenda-card:hover { box-shadow: 0 2px 8px rgba(45,147,126,0.15); }
    .agenda-card.selecionado { background: #f6fbfa; box-shadow: 0 2px 8px rgba(45,147,126,0.25); }
    .agenda-card.indisponivel { opacity: 0.55; cursor: not-allowed; }
    .agenda-card input[type=radio] { transform: scale(1.05); cursor: pointer; }
    .agenda-card.indisponivel input[type=radio] { cursor: not-allowed; }

    .agenda-data { min-width: 52px; text-align: center; border-right: 1px solid #eee; padding-right: 10px; }
    .agenda-data .dia { font-size: 20px; font-weight: bold; color: #2D937E; line-height: 1; }
    .agenda-data .mes { font-size: 10px; text-transform: uppercase; color: #666; letter-spacing: 0.5px; margin-top: 1px; }
    .agenda-data .ano { font-size: 10px; color: #999; }
    .agenda-info { flex: 1; }
    .agenda-info .titulo { font-size: 14px; font-weight: bold; color: #333; margin: 0 0 2px; }
    .agenda-info .meta { font-size: 12px; color: #666; line-height: 1.4; }
    .agenda-info .meta span { margin-right: 10px; }
    .agenda-info .aviso { font-size: 12px; color: #c0392b; margin-top: 2px; font-weight: bold; }

    .agenda-vazia { text-align: center; padding: 30px; color: #888; background: #fafafa; border-radius: 6px; }

    .agenda-botoes { display: flex; justify-content: space-between; align-items: center; padding: 18px 0 5px; margin-top: 18px; border-top: 1px solid #eee; }
    .agenda-botoes .btn-agenda-link { color: #666; text-decoration: none; font-size: 14px; padding: 9px 12px; }
    .agenda-botoes .btn-agenda-link:hover { color: #2D937E; text-decoration: underline; }
    .agenda-botoes .btn-agenda-primary { background: #2D937E; color: #fff; border: none; padding: 11px 32px; border-radius: 4px; font-size: 15px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
    .agenda-botoes .btn-agenda-primary:hover { background: #247a68; }
    .agenda-botoes .btn-agenda-primary:disabled { background: #ccc; cursor: not-allowed; }

    @media (max-width: 600px) {
        .agenda-card { flex-direction: row; }
        .agenda-progress .label-step { font-size: 11px; }
    }
</style>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="agenda-wrap">
                <div class="agenda-progress">
                    <div class="step active">
                        <span class="num">1</span>
                        <span class="label-step">Dia da Visita</span>
                    </div>
                    <?php if ($jaVisitouPasso2) { ?>
                    <a class="step inactive step-link" href="index.php?content=view/inscricao/agenda_passo2_grupo.php">
                        <span class="num">2</span>
                        <span class="label-step">Tamanho do Grupo</span>
                    </a>
                    <?php } else { ?>
                    <div class="step inactive">
                        <span class="num">2</span>
                        <span class="label-step">Tamanho do Grupo</span>
                    </div>
                    <?php } ?>
                    <div class="step inactive">
                        <span class="num">3</span>
                        <span class="label-step">Dados Pessoais</span>
                    </div>
                </div>

                <h3 class="agenda-titulo">Escolha o dia da visita</h3>
                <?php if ($valorMinimo !== null) { ?>
                <div class="agenda-subtitulo">
                    Visitação para grupos &mdash; investimento a partir de
                    <span class="agenda-valor-destaque">R$ <?php echo number_format($valorMinimo, 2, ',', '.'); ?></span>
                    <?php if ($valorMaximo !== null && $valorMaximo != $valorMinimo) { ?>
                    (até R$ <?php echo number_format($valorMaximo, 2, ',', '.'); ?>)
                    <?php } ?>
                    por grupo. O tamanho do grupo é escolhido na próxima etapa.
                </div>
                <?php } ?>

                <?php if (count($mesesDisponiveis) > 1) { ?>
                <div class="agenda-meses" id="agendaMeses">
                    <?php foreach ($mesesDisponiveis as $chave => $info) {
                        $classes = ($chave === $mesAtivo ? 'ativo' : '') . ($info['temDisponivel'] ? '' : ' sem-vaga');
                        ?>
                        <button type="button" class="<?php echo trim($classes); ?>" data-mes="<?php echo $chave; ?>"><?php echo $info['label']; ?></button>
                    <?php } ?>
                </div>
                <?php } ?>

                <form method="POST" name="form" action="index.php?content=view/inscricao/agenda_passo2_grupo.php" id="formAgenda1">
                    <?php // Anti-bot: honeypot (campo invisível) + timestamp de abertura do form ?>
                    <input type="text" name="hp_website" value="" tabindex="-1" autocomplete="off"
                           style="position:absolute;left:-9999px;opacity:0;height:0;width:0;" aria-hidden="true">
                    <input type="hidden" name="hp_t" value="<?php echo time(); ?>">
                    <?php if (count($oficinas) == 0) { ?>
                        <div class="agenda-vazia">Não há dias disponíveis para visitação no momento.</div>
                    <?php } else {
                        foreach ($oficinas as $of) {
                            $codOf = (int) $of->getCodOficina();
                            $totalVagas = (int) $of->getNumeroVagas();
                            $vagasRest = (int) $of->getVagasDisponiveis();
                            // Mesmas mensagens do fluxo legado (oficina_seleciona.php linhas 307-314):
                            // - numero_vagas <= 0: "Nenhuma vaga foi ofertada"
                            // - vagas_disponiveis <= 0 (mas tem oferta): "Todas as vagas preenchidas"
                            $semOferta = ($totalVagas <= 0);
                            $semVaga = (!$semOferta && $vagasRest <= 0);
                            $indisponivel = $semOferta || $semVaga;
                            $cardClass = 'agenda-card' . ($indisponivel ? ' indisponivel' : '');
                            // No banco do projeto a coluna que vale é data_inicio (data_ini é legado/vazio)
                            $dataIni = $of->getDataInicio() ?: $of->getDataIni();
                            $dia = ''; $mes = ''; $ano = '';
                            if (isDateStringValid($dataIni)) {
                                $ts = strtotime($dataIni);
                                $dia = date('d', $ts);
                                // Mês em pt-BR (strftime depende de locale; usamos o array já montado no topo)
                                $mes = $nomeMeses[(int) date('n', $ts)];
                                $ano = date('Y', $ts);
                            }
                            $horario = trim($of->getHorarioIni() . ($of->getHorarioFim() ? ' às ' . $of->getHorarioFim() : ''));
                            // Sem pré-seleção ao recarregar: o radio sempre começa vazio
                            $mesDoCard = isset($oficinaParaMes[$codOf]) ? $oficinaParaMes[$codOf] : '';
                            if ($mesDoCard !== '' && $mesDoCard !== $mesAtivo) { $cardClass .= ' mes-oculto'; }
                            ?>
                            <label class="<?php echo $cardClass; ?>" data-mes="<?php echo $mesDoCard; ?>">
                                <input type="radio" name="cod_oficina[]" value="<?php echo $codOf; ?>" <?php echo $indisponivel ? 'disabled' : 'required'; ?>>
                                <div class="agenda-data">
                                    <div class="dia"><?php echo $dia; ?></div>
                                    <div class="mes"><?php echo $mes; ?></div>
                                    <div class="ano"><?php echo $ano; ?></div>
                                </div>
                                <div class="agenda-info">
                                    <div class="titulo"><?php echo htmlspecialchars($of->getTitulo()); ?></div>
                                    <div class="meta">
                                        <?php if ($horario) { ?><span>&#128340; <?php echo $horario; ?></span><?php } ?>
                                        <?php if ($of->getLocal()) { ?><span>&#128205; <?php echo htmlspecialchars($of->getLocal()); ?></span><?php } ?>
                                    </div>
                                    <?php if ($indisponivel) { ?><div class="aviso">Não disponível</div><?php } ?>
                                </div>
                            </label>
                            <?php
                        }
                    } ?>

                    <div class="agenda-botoes">
                        <a href="index.php?content=view/inscricao/inscricao_01.php" class="btn-agenda-link">&larr; Voltar</a>
                        <button type="submit" class="btn-agenda-primary" id="btnAvancar" disabled>Avançar &rarr;</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação: ao clicar num dia, pergunta antes de avançar -->
<div class="modal fade" id="agendaModal" data-backdrop="static" data-bs-backdrop="static" data-keyboard="false" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#f6fbfa;border-bottom:1px solid #d3e8e2;">
                <h5 class="modal-title" style="color:#2D937E;font-weight:bold;">Confirmar dia da visita</h5>
            </div>
            <div class="modal-body" style="text-align:center;">
                <p style="margin-bottom:8px;color:#666;">Você escolheu visitar o museu em:</p>
                <div id="agendaModalInfo" style="font-size:18px;color:#2D937E;font-weight:bold;padding:10px 0;"></div>
                <input type="hidden" id="agendaModalCod" value="">
                <p style="margin-top:14px;color:#555;">Deseja avançar para escolher o tamanho do grupo?</p>
            </div>
            <div class="modal-footer" style="justify-content:space-between;">
                <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAgendaConfirmar" style="background:#2D937E;border-color:#2D937E;">Avançar &rarr;</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Click no radio (o label já redireciona o click do card pro radio interno) —
    // deixa o navegador fazer a marcação natural, e a gente só destaca o card + abre modal.
    $('#formAgenda1').on('click', 'input[type=radio][name="cod_oficina[]"]', function(){
        var $radio = $(this);
        var $card = $radio.closest('.agenda-card');
        if ($card.hasClass('indisponivel')) { return; }
        $('.agenda-card').removeClass('selecionado');
        $card.addClass('selecionado');
        $('#btnAvancar').prop('disabled', false);
        var codOf = $radio.val();
        var titulo = $card.find('.agenda-info .titulo').text().trim();
        var horarioMeta = $card.find('.agenda-info .meta').text().trim();
        // Título já costuma trazer data+horário; só fallback se vier vazio
        var info = titulo;
        if (!info) {
            var dia = $card.find('.agenda-data .dia').text();
            var mes = $card.find('.agenda-data .mes').text();
            var ano = $card.find('.agenda-data .ano').text();
            info = dia + '/' + mes + '/' + ano;
        }
        if (horarioMeta) { info += '<br><small style="font-weight:normal;color:#666;">' + horarioMeta + '</small>'; }
        $('#agendaModalCod').val(codOf);
        $('#agendaModalInfo').html(info);
        var $m = $('#agendaModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            (bootstrap.Modal.getOrCreateInstance($m[0])).show();
        } else {
            $m.modal('show');
        }
    });

    // Confirmar no modal: submete o form (radio já está marcado pelo click anterior)
    $('#btnAgendaConfirmar').on('click', function(){
        $('#formAgenda1').submit();
    });

    // Filtro por mês
    $('#agendaMeses').on('click', 'button', function(){
        var mes = $(this).data('mes');
        $('#agendaMeses button').removeClass('ativo');
        $(this).addClass('ativo');
        $('.agenda-card').each(function(){
            if ($(this).data('mes') === mes) {
                $(this).removeClass('mes-oculto');
            } else {
                $(this).addClass('mes-oculto');
            }
        });
    });
});
</script>
