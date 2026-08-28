<?php
/**
 * Aba 2a Fase — Pro-REDE 003/2026, evento 129.
 *
 * Mostra o cronograma da 2a fase + (quando liberado) o resultado
 * individual da avaliacao de documentos do inscrito (DEFERIDO/INDEFERIDO
 * + nota + classificacao + recurso).
 *
 * Datas oficiais do edital 003/2026:
 *   - Analise de documentos:    08/05/2026 a 15/05/2026
 *   - Divulgacao preliminar:    18/05/2026
 *   - Recurso:                  19/05/2026 08:00 a 20/05/2026 18:00
 *   - Analise de recursos:      21/05/2026 a 25/05/2026
 *   - Classificacao final:      26/05/2026
 *
 * Para atualizar quando virar config no banco, ver
 * memory/project_config_etapas_avaliacao.md.
 */

// =====================================================================
// FLAG DE DIVULGACAO DO RESULTADO INDIVIDUAL (FASE 2)
// =====================================================================
// Lida de tab_avaliacao_etapa.resultado_divulgado da etapa ordem=2.
// Pra liberar/bloquear, basta editar a etapa pela tela Outros > Etapas
// (checkbox "Resultado divulgado") — sem mexer em codigo nem FTP.
$DIVULGAR_RESULTADO_FASE2 = false;
$objEtapaDivulgacao = new Etapa();
if ($objEtapaDivulgacao->getEtapaByOrdem(2, false)) {
    $DIVULGAR_RESULTADO_FASE2 = ((int) $objEtapaDivulgacao->getResultadoDivulgado() === 1);
}

// Aviso obrigatorio: sempre conferir divulgacao oficial no site.
$avisoOficialFase2 = '<div style="margin-top:10px;padding:8px 12px;background:#fff3cd;border:1px solid #ffeeba;border-radius:4px;color:#856404;font-size:13px;line-height:1.4;">'
    . '<strong>⚠️ Atenção:</strong> este resultado tem caráter informativo. '
    . 'Confira sempre a divulgação oficial no site '
    . '<a href="https://hutec.org.br/curso/edital-de-processo-seletivo-no-003-2026/" target="_blank" style="color:#856404;font-weight:bold;text-decoration:underline;">'
    . 'hutec.org.br &mdash; Edital 003/2026</a>.'
    . '</div>';

// Monta HTML do bloco de resultado pra injetar dentro do container
// verde-claro da aba (mesmo visual do cronograma). Vazio quando nao
// divulgado ou inscrito sem avaliacao.
$blocoResultadoF2 = '';
if ($DIVULGAR_RESULTADO_FASE2) {
    try {
        $objAvalFase2 = new AvaliacaoInscrito();
        $objAvalFase2->setCodInscrito($objInscrito->getCodInscrito());
        if ($objAvalFase2->carregarAvaliacaoFase2ByIdInscrito()) {
            ob_start();
            $statusF2  = $objAvalFase2->getClassificacaoStatus();
            $pontosF2  = (float) ($objAvalFase2->getPontosTotalConfirmado() ?: 0);
            $pontosTxt = number_format($pontosF2, 2, ',', '.');

            // Prazo de recurso da Fase 2 vem de tab_avaliacao_etapa (ordem_etapa=2).
            // Flags de divulgacao — cada bloco eh liberado quando A flag boolean
            // estiver marcada OU a data de divulgacao ja tiver passado.
            // Isso da 2 formas pro admin liberar: marcar manualmente ou
            // confiar no cronograma cadastrado.
            $prazoIni = ''; $prazoFim = '';
            $divulgacaoRecursoDat = '';
            $divulgacaoFinalDat = '';
            $resultadoRecursoDivulgado = false;
            $resultadoFinalDivulgado   = false;
            $objEtapaF2 = new Etapa();
            if ($objEtapaF2->getEtapaByOrdem(2, false)) {
                $prazoIni = $objEtapaF2->getRecursoDatIni();
                $prazoFim = $objEtapaF2->getRecursoDatFim();
                $divulgacaoRecursoDat = $objEtapaF2->getDivulgacaoRecursoDat();
                $divulgacaoFinalDat   = $objEtapaF2->getDivulgacaoFinalDat();
                $flagRecurso = ((int)$objEtapaF2->getResultadoRecursoDivulgado() === 1);
                $flagFinal   = ((int)$objEtapaF2->getResultadoFinalDivulgado()   === 1);
                $dataRecursoPassou = ($divulgacaoRecursoDat && time() >= strtotime($divulgacaoRecursoDat));
                $dataFinalPassou   = ($divulgacaoFinalDat   && time() >= strtotime($divulgacaoFinalDat));
                $resultadoRecursoDivulgado = ($flagRecurso || $dataRecursoPassou);
                $resultadoFinalDivulgado   = ($flagFinal   || $dataFinalPassou);
            }
            $agora       = date('YmdHi');
            $iniInt      = $prazoIni ? formatDateHoraTrans($prazoIni, 'YYYY-MM-DD HH:II:SS', 'YYYYMMDDHHII') : '';
            $fimInt      = $prazoFim ? formatDateHoraTrans($prazoFim, 'YYYY-MM-DD HH:II:SS', 'YYYYMMDDHHII') : '';
            $dentroPrazo = ($iniInt && $fimInt && $agora >= $iniInt && $agora <= $fimInt);
            $antesPrazo  = ($iniInt && $agora < $iniInt);
            $depoisPrazo = ($fimInt && $agora > $fimInt);

            $recursoTexto = (string) $objAvalFase2->getRecursoTexto();
            $jaEnviou     = ($recursoTexto !== '');
            $temParecer   = ($objAvalFase2->getRecursoParecer() != '' || $objAvalFase2->getClassificacaoStatusAposRecurso() != '');

            // Cor + label conforme status (linha INTEIRA do resultado colorida)
            $corResultado = ($statusF2 == 'H') ? '#2e7d32' : '#c62828';
            $icone        = ($statusF2 == 'H') ? '✅' : '❌';
            $rotulo       = ($statusF2 == 'H') ? 'DEFERIDO' : 'INDEFERIDO';

            // Bloco do resultado (linha inteira colorida) — substitui o aviso
            // "Etapa atual" quando o resultado esta divulgado. Mesmo padding/visual.
            echo '<div style="margin:10px 0;padding:12px 14px;background:#fff;border:1px solid #d3e8e2;border-radius:4px;font-size:15px;line-height:1.6;">';
            echo '<div style="color:' . $corResultado . ';font-weight:700;">';
            echo $icone . ' Resultado da Avaliação de Documentos (2ª Fase): ' . $rotulo . '.';
            if ($pontosF2 > 0 || $statusF2 == 'H') {
                echo '<br>Pontuação obtida: ' . $pontosTxt;
            }
            echo '</div>';

            // Parecer da Comissao Avaliadora (justifica pontuacao/status).
            // Colapsado por default — clicar em "Ver parecer" pra abrir.
            $parecerAvaliador = (string) $objAvalFase2->getParecerAvaliador();
            $codAvalAtual     = (int) $objAvalFase2->getCodAvaliacao();
            if ($parecerAvaliador !== '') {
                echo '<div style="margin-top:10px;">';
                echo '<button type="button" class="btn btn-default btn-xs" onclick="toggleBloco(\'parecer-aval-' . $codAvalAtual . '\')">Ver parecer da Comissão</button>';
                echo '</div>';
                echo '<div id="parecer-aval-' . $codAvalAtual . '" style="display:none;margin-top:8px;padding:10px 12px;background:#f8f9fa;border-left:3px solid #ccc;border-radius:3px;color:#333;font-size:14px;line-height:1.5;">';
                echo '<strong>Parecer da Comissão Avaliadora:</strong><br>';
                echo nl2br(htmlspecialchars($parecerAvaliador));
                echo '</div>';
            }

            // Espaco antes do bloco de recurso
            echo '<div style="margin-top:18px;">';

            if ($prazoIni && $prazoFim) {
                echo '<div><strong>Prazo para recurso:</strong> de '
                    . formatDateView($prazoIni) . ' até ' . formatDateView($prazoFim) . '.</div>';
            }

            if ($temParecer && $resultadoRecursoDivulgado) {
                // Comissao ja respondeu o recurso E resultado da analise foi divulgado.
                // Status + pontuacao ficam visiveis; parecer e texto do recurso ficam
                // colapsados (botoes "Ver parecer" / "Ver meu recurso") pra nao poluir
                // a aba com textos longos.
                $statusFinal = $objAvalFase2->getClassificacaoStatusAposRecurso();
                $pontosFinal = (float) ($objAvalFase2->getPontosTotalAposRecurso() ?: 0);
                $corStatus   = ($statusFinal === 'H') ? '#3c763d' : '#a94442';
                $labelStatus = ($statusFinal === 'H') ? 'DEFERIDO' : 'INDEFERIDO';
                $cod         = (int) $objAvalFase2->getCodAvaliacao();
                echo '<div style="margin-top:6px;"><strong>Resposta da Comissão:</strong></div>';
                echo '<div style="margin-top:6px;"><strong>Status após recurso:</strong> <span style="color:' . $corStatus . ';font-weight:700;">' . htmlspecialchars($labelStatus) . '</span></div>';
                if ($pontosFinal > 0) {
                    echo '<div><strong>Pontuação após recurso:</strong> ' . number_format($pontosFinal, 2, ',', '.') . '</div>';
                }
                // Botoes pra abrir parecer e texto do recurso
                $parecerRecurso = (string) $objAvalFase2->getRecursoParecer();
                $temParecerTxt  = (trim($parecerRecurso) !== '');
                $temRecursoTxt  = ($jaEnviou && trim($recursoTexto) !== '');
                if ($temParecerTxt || $temRecursoTxt) {
                    echo '<div style="margin-top:10px;">';
                    if ($temParecerTxt) {
                        echo '<button type="button" class="btn btn-default btn-xs" onclick="toggleBloco(\'parecer-rec-' . $cod . '\')" style="margin-right:6px;">Ver parecer</button>';
                    }
                    if ($temRecursoTxt) {
                        echo '<button type="button" class="btn btn-default btn-xs" onclick="toggleBloco(\'meu-recurso-' . $cod . '\')">Ver meu recurso</button>';
                    }
                    echo '</div>';
                    if ($temParecerTxt) {
                        echo '<div id="parecer-rec-' . $cod . '" style="display:none;margin-top:8px;padding:10px 12px;background:#f8f9fa;border-left:3px solid #ccc;border-radius:3px;color:#333;font-size:14px;line-height:1.5;">';
                        echo '<strong>Parecer da Comissão sobre o recurso:</strong><br>';
                        echo nl2br(htmlspecialchars($parecerRecurso));
                        echo '</div>';
                    }
                    if ($temRecursoTxt) {
                        echo '<div id="meu-recurso-' . $cod . '" style="display:none;margin-top:8px;padding:10px 12px;background:#f8f9fa;border-left:3px solid #4A90E2;border-radius:3px;color:#333;font-size:14px;line-height:1.5;">';
                        echo '<strong>Seu recurso enviado:</strong><br>';
                        echo nl2br(htmlspecialchars($recursoTexto));
                        echo '</div>';
                    }
                }
            } elseif ($jaEnviou) {
                // Ja enviou recurso. So mostra status/parecer da Comissao depois
                // que resultado_final_divulgado=1 (gate tratado acima).
                echo '<div style="color:#2e7d32;font-weight:700;">Recurso enviado.</div>';
                if ($divulgacaoRecursoDat) {
                    echo '<div style="margin-top:6px;font-size:13px;color:#555;">'
                       . 'Divulgação do resultado da análise do recurso prevista para <strong>'
                       . formatDateView($divulgacaoRecursoDat) . '</strong>.</div>';
                }
                if ($dentroPrazo) {
                    echo '<div style="margin-top:10px;"><a href="index.php?content=view/inscricao/inscricao_recurso_fase2_129.php" class="btn btn-default" style="color:#555;border:1px solid #ccc;background:#fff;">Editar Recurso</a>'
                       . ' <span style="font-size:13px;color:#888;">(você pode editar até ' . formatDateView($prazoFim) . ')</span></div>';
                }
            } elseif ($dentroPrazo) {
                echo '<div style="margin-top:10px;"><a href="index.php?content=view/inscricao/inscricao_recurso_fase2_129.php" class="btn btn-success" style="color:#fff;">Solicitar Recurso</a></div>';
            } elseif ($antesPrazo) {
                echo '<div>O prazo de recurso ainda não foi aberto.</div>';
            } elseif ($depoisPrazo) {
                echo '<div>O prazo de recurso foi encerrado.</div>';
            }

            echo '</div>'; // fecha div de espaçamento (margin-top:18px)
            echo '</div>'; // fecha wrapper do bloco do resultado da avaliacao

            // Helper de collapse pros botoes "Ver parecer" / "Ver meu recurso"
            // (idempotente: so define se ainda nao existe na pagina)
            echo '<script>'
               . 'if (typeof window.toggleBloco !== "function") {'
               . '  window.toggleBloco = function(id) {'
               . '    var d = document.getElementById(id);'
               . '    if (!d) return;'
               . '    d.style.display = (d.style.display === "none" || !d.style.display) ? "block" : "none";'
               . '  };'
               . '}'
               . '</script>';

            $blocoResultadoF2 = ob_get_clean();
        }
    } catch (Exception $e) {
        if (ob_get_level() > 0) { ob_end_clean(); }
        $blocoResultadoF2 = '';
    }
}

$hojeF2 = strtotime(date('Y-m-d'));
$etapasF2 = array(
    array(
        'titulo'   => 'Análise dos documentos pela Comissão Avaliadora',
        'ini'      => strtotime('2026-05-08'),
        'fim'      => strtotime('2026-05-15'),
        'texto'    => '08/05/2026 a 15/05/2026',
    ),
    array(
        'titulo'   => 'Divulgação da Classificação Preliminar',
        'ini'      => strtotime('2026-05-18'),
        'fim'      => strtotime('2026-05-18'),
        'texto'    => '18/05/2026 (após as 22h)',
    ),
    array(
        'titulo'   => 'Período para interposição de Recurso',
        'ini'      => strtotime('2026-05-19'),
        'fim'      => strtotime('2026-05-20'),
        'texto'    => '19/05/2026 às 08h00 até 20/05/2026 às 18h00',
    ),
    array(
        'titulo'   => 'Análise dos Recursos pela Comissão Avaliadora',
        'ini'      => strtotime('2026-05-21'),
        'fim'      => strtotime('2026-05-25'),
        'texto'    => '21/05/2026 a 25/05/2026',
    ),
    array(
        'titulo'   => 'Divulgação da Classificação Final',
        'ini'      => strtotime('2026-05-26'),
        'fim'      => strtotime('2026-05-26'),
        'texto'    => '26/05/2026',
    ),
);
$etapaAtualIdx = -1;
foreach ($etapasF2 as $idx => $e) {
    if ($hojeF2 >= $e['ini'] && $hojeF2 <= $e['fim']) {
        $etapaAtualIdx = $idx;
        break;
    }
}
?>
<div style="padding:12px 14px; background:#f6fbfa; border:1px solid #d3e8e2; border-radius:6px;">
    <h4 style="color:#2D937E; margin-top:0; font-weight:bold;">2ª Fase — Avaliação de Documentos</h4>

    <?php if (!$DIVULGAR_RESULTADO_FASE2) { ?>
    <div style="margin:10px 0; padding:12px 14px; background:#fff3cd; border-left:4px solid #f0b400; border-radius:4px; color:#856404; font-size:14px; line-height:1.5;">
        <strong>📢 Divulgação da Classificação Preliminar:</strong> prevista para <strong>após as 22h de 18/05/2026</strong>.
        Acompanhe esta página e o site
        <a href="https://hutec.org.br/curso/edital-de-processo-seletivo-no-003-2026/" target="_blank" style="color:#856404; font-weight:bold; text-decoration:underline;">hutec.org.br</a>.
    </div>
    <?php } ?>

    <?php if (!$DIVULGAR_RESULTADO_FASE2 && $etapaAtualIdx >= 0) { ?>
        <div style="margin:10px 0; padding:10px 12px; background:#fff8e1; border-left:4px solid #f0b400; border-radius:4px;">
            <strong>Etapa atual:</strong> <?php echo htmlspecialchars($etapasF2[$etapaAtualIdx]['titulo']); ?>
            <br>
            <span style="font-size:13px; color:#555;"><?php echo htmlspecialchars($etapasF2[$etapaAtualIdx]['texto']); ?></span>
        </div>
    <?php } ?>

    <?php echo $blocoResultadoF2; ?>

    <?php if (!$DIVULGAR_RESULTADO_FASE2) { ?>
    <p style="margin:10px 0; color:#333;">
        A Comissão Avaliadora analisa os documentos enviados conforme o Anexo II do Edital.
        Os resultados serão divulgados na sua área do inscrito e no site oficial nas datas abaixo.
        <strong>Esta aba é apenas informativa</strong> — o resultado individual não está disponível até a divulgação.
    </p>

    <h5 style="margin-top:18px; color:#2D937E; font-weight:bold;">Cronograma da 2ª Fase</h5>
    <table class="table table-bordered" style="background:#fff; margin-bottom:10px;">
        <thead style="background:#eaf5f1;">
            <tr>
                <th style="width:60%;">Etapa</th>
                <th>Período</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($etapasF2 as $idx => $e) {
                $destaque = ($idx === $etapaAtualIdx);
                ?>
                <tr<?php echo $destaque ? ' style="background:#fff8e1;"' : ''; ?>>
                    <td>
                        <?php if ($destaque) { ?>
                            <strong><?php echo htmlspecialchars($e['titulo']); ?></strong>
                            <span style="color:#8a6d3b; font-size:11px; margin-left:6px;">(em andamento)</span>
                        <?php } else { ?>
                            <?php echo htmlspecialchars($e['titulo']); ?>
                        <?php } ?>
                    </td>
                    <td><?php echo htmlspecialchars($e['texto']); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <p style="margin-top:12px; font-size:13px; color:#666;">
        Cronograma completo disponível em
        <a href="https://hutec.org.br/curso/edital-de-processo-seletivo-no-003-2026/#cronograma" target="_blank" style="color:#2D937E; font-weight:bold;">
            hutec.org.br &mdash; Edital 003/2026
        </a>.
    </p>
    <?php } // fim if (!$DIVULGAR_RESULTADO_FASE2) — esconde cronograma/etc quando divulgado ?>
</div>
