<?php
/**
 * Aba "Resultado Final" — Pro-REDE 003/2026, evento 129.
 *
 * Card unico e destacado com o resultado consolidado do candidato apos
 * a 2a Fase + analise de recurso terminarem. Eh o estado FINAL no processo.
 *
 * Le os campos *_final de tab_trabalhos_avaliacao_inscricao (preenchidos
 * pela operacao admin "Consolidar Fase"). Gate de divulgacao: flag
 * resultado_final_divulgado=1 OU data divulgacao_final_dat ja passou.
 *
 * Antes da divulgacao, mostra apenas mensagem informativa com previsao.
 */

// Etapa 2 do evento + flags/datas de divulgacao
$divulgacaoFinalDat = '';
$flagFinal          = false;
$dataFinalPassou    = false;
$objEtapaF2 = new Etapa();
if ($objEtapaF2->getEtapaByOrdem(2, false)) {
    $divulgacaoFinalDat = $objEtapaF2->getDivulgacaoFinalDat();
    $flagFinal          = ((int) $objEtapaF2->getResultadoFinalDivulgado() === 1);
    $dataFinalPassou    = ($divulgacaoFinalDat && time() >= strtotime($divulgacaoFinalDat));
}
$resultadoFinalDivulgado = ($flagFinal || $dataFinalPassou);

// Carrega a avaliacao da Fase 2 deste inscrito
$objAvalFinal = new AvaliacaoInscrito();
$objAvalFinal->setCodInscrito($objInscrito->getCodInscrito());
$temAvaliacao = $objAvalFinal->carregarAvaliacaoFase2ByIdInscrito();

$statusFinalCons = $temAvaliacao ? trim((string) $objAvalFinal->getClassificacaoStatusFinal()) : '';
$pontosFinalCons = $temAvaliacao ? trim((string) $objAvalFinal->getPontosTotalFinal())         : '';
$classifFinal    = $temAvaliacao ? trim((string) $objAvalFinal->getClassificacaoFinal())       : '';
$temResultadoConsolidado = ($statusFinalCons !== '');
?>

<div style="padding:12px 14px; background:#f6fbfa; border:1px solid #d3e8e2; border-radius:6px;">
    <h4 style="color:#2D937E; margin-top:0; font-weight:bold;">Resultado Final</h4>

    <?php if (!$resultadoFinalDivulgado || !$temResultadoConsolidado) { ?>
        <div style="margin:10px 0; padding:12px 14px; background:#fff3cd; border-left:4px solid #f0b400; border-radius:4px; color:#856404; font-size:14px; line-height:1.5;">
            <strong>📢 Resultado Final ainda não divulgado.</strong>
            <?php if ($divulgacaoFinalDat) { ?>
                <br>Previsão de divulgação: <strong><?php echo formatDateView($divulgacaoFinalDat); ?></strong>.
            <?php } ?>
            <br>Acompanhe esta página e o site
            <a href="https://hutec.org.br/curso/edital-de-processo-seletivo-no-003-2026/" target="_blank" style="color:#856404; font-weight:bold; text-decoration:underline;">hutec.org.br &mdash; Edital 003/2026</a>.
        </div>
    <?php } else {
        $isDefFinal = ($statusFinalCons === 'H');
        $vagasConv  = EditalPublicacao::getNumeroVagasConvocados($objEv->getCodEvento());
        if ($isDefFinal) {
            $situacaoFinal = ($vagasConv > 0 && $classifFinal !== '' && (int)$classifFinal > 0 && (int)$classifFinal <= $vagasConv)
                ? 'Convocado'
                : 'Cadastro de Reserva';
            $corBox    = '#2d6a4f';
            $bgBox     = '#e6f4ea';
            $bordBox   = '#a3d3b9';
            $iconeBox  = '🏁';
            $rotuloBox = 'DEFERIDO';
        } else {
            $situacaoFinal = 'Inabilitado';
            $corBox    = '#a94442';
            $bgBox     = '#fde8e8';
            $bordBox   = '#e6b5b3';
            $iconeBox  = '🏁';
            $rotuloBox = 'INDEFERIDO';
        }
        ?>
        <div style="margin:10px 0;padding:18px 20px;background:<?php echo $bgBox; ?>;border:2px solid <?php echo $bordBox; ?>;border-radius:6px;">
            <div style="color:<?php echo $corBox; ?>;font-weight:700;font-size:18px;border-bottom:1px solid <?php echo $bordBox; ?>;padding-bottom:10px;margin-bottom:12px;">
                <?php echo $iconeBox; ?> Resultado Final da 2ª Fase: <?php echo $rotuloBox; ?>
            </div>
            <?php if ($isDefFinal) { ?>
                <?php if ($pontosFinalCons !== '') { ?>
                    <div style="margin-top:4px;color:#333;font-size:15px;"><strong>Pontuação final:</strong> <?php echo number_format((float) $pontosFinalCons, 2, ',', '.'); ?></div>
                <?php } ?>
                <?php if ($classifFinal !== '') { ?>
                    <div style="margin-top:4px;color:#333;font-size:15px;"><strong>Classificação:</strong> <?php echo (int) $classifFinal; ?>º</div>
                <?php } ?>
                <div style="margin-top:8px;color:<?php echo $corBox; ?>;font-weight:700;font-size:16px;"><strong>Situação:</strong> <?php echo htmlspecialchars($situacaoFinal); ?></div>
            <?php } ?>
            <div style="margin-top:14px;padding:8px 12px;background:#fff3cd;border:1px solid #ffeeba;border-radius:4px;color:#856404;font-size:13px;line-height:1.4;">
                <strong>⚠️ Atenção:</strong> este resultado tem caráter informativo.
                Confira sempre a divulgação oficial no site
                <a href="https://hutec.org.br/curso/edital-de-processo-seletivo-no-003-2026/" target="_blank" style="color:#856404;font-weight:bold;text-decoration:underline;">
                    hutec.org.br &mdash; Edital 003/2026
                </a>.
            </div>
        </div>
    <?php } ?>
</div>
