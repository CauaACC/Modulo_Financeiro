<?php

/**
 * GerenciarAvaliacaoFase2AC
 *
 * Controller dedicado da avaliacao da 2a Fase (Anexo II / pontuacao).
 *
 * Diferencas para GerenciarAvaliacaoInscritoAC (1a Fase recurso):
 *   - Trabalha sobre tab_trabalhos_avaliacao_inscricao filtrando por
 *     ordem_etapa=2 (cod_etapa dinamico, lido de tab_avaliacao_etapa
 *     onde ordem_etapa=2 AND situacao=1 [Em andamento]).
 *   - A linha de avaliacao Fase 2 nao existe ate o avaliador salvar
 *     pela primeira vez (INSERT). Edicoes posteriores fazem UPDATE.
 *   - Lista os inscritos elegiveis (Habilitados na Fase 1 final) com
 *     LEFT JOIN na Fase 2, sinalizando "pendente" quando ainda nao avaliado.
 *
 * Reaproveita do controller da Fase 1:
 *   - GerenciarAvaliacaoInscritoAC::formularioAvaliacaoProRede2() — Anexo II
 *   - GerenciarAvaliacaoInscritoAC::carregarDocumentos() — lista docs do inscrito
 *   - GerenciarAvaliacaoInscritoAC::buildAvaliacaoPesJsonForDb() — JSON da pontuacao
 */
class GerenciarAvaliacaoFase2AC
{

    /**
     * Garante checagem de auth ao instanciar o controller. O seguranca.php faz
     * FWA_Autenticacao e da exit() se nao houver permissao — protegendo todos
     * os metodos publicos contra invocacao via AJAX sem sessao valida.
     */
    public function __construct()
    {
        require_once(dirname(__FILE__) . "/../view/admin/consulta-avaliacao-inscrito-seguranca.php");
    }

    /**
     * Retorna o cod_etapa da Fase 2 ativa do evento atual, ou null se nao habilitada.
     */
    public function getCodEtapaFase2Ativa($debug = false)
    {
        $objEtapa = new Etapa();
        return $objEtapa->getEtapaByOrdem(2, true, $debug);
    }

    /**
     * Detecta o modo de operacao da Fase 2 baseado nas janelas configuradas
     * na etapa de ordem 2 e na data/hora atual.
     *
     * Retorna:
     *   'AVALIACAO' -> dentro de analise_dat_ini/fim (ou datas vazias = modo legado)
     *   'RECURSO'   -> dentro de analise_recurso_dat_ini/fim
     *   'CONSULTA'  -> fora de ambas as janelas OU etapa Concluida
     *
     * RECURSO tem prioridade quando as duas janelas se sobrepoem (cronologicamente
     * vem depois). Usado tanto pela view (decisao de UI) quanto pelo controller
     * (validacao server-side em salvarAvaliacaoFase2).
     */
    public function detectarModoFase2($debug = false)
    {
        $objEtapa = new Etapa();
        $codEtapa = (int)$objEtapa->getEtapaByOrdem(2, false, $debug);
        if ($codEtapa <= 0) {
            return 'CONSULTA';
        }
        $objEtapa->setCodEtapa($codEtapa);
        $objEtapa->getEtapaById();
        if ($objEtapa->isConcluida()) {
            return 'CONSULTA';
        }
        $agora = time();
        $iniR = strtotime(trim((string)$objEtapa->getAnaliseRecursoDatIni()));
        $fimR = strtotime(trim((string)$objEtapa->getAnaliseRecursoDatFim()));
        if ($iniR && $fimR && $agora >= $iniR && $agora <= $fimR) {
            return 'RECURSO';
        }
        $iniA = strtotime(trim((string)$objEtapa->getAnaliseDatIni()));
        $fimA = strtotime(trim((string)$objEtapa->getAnaliseDatFim()));
        // Datas vazias = modo legado, sem janela = aberto pra avaliacao
        if (!$iniA || !$fimA) {
            return 'AVALIACAO';
        }
        if ($agora >= $iniA && $agora <= $fimA) {
            return 'AVALIACAO';
        }
        return 'CONSULTA';
    }

    /**
     * Carrega a avaliacao Fase 2 do inscrito (se ja existe). Caso contrario,
     * devolve um AvaliacaoInscrito "vazio" mas com nome/dados do inscrito setados
     * (pra view conseguir exibir cabecalho mesmo na primeira avaliacao).
     */
    public function carregarAvaliacaoFase2ByIdInscrito($fws_, $debug = false)
    {
        $obj = new AvaliacaoInscrito();
        if (empty($fws_['codInscrito'])) {
            return $obj;
        }
        $obj->setCodInscrito($fws_['codInscrito']);
        // Usa metodo dedicado que filtra ordem_etapa=2 com LIMIT 1 (evita
        // duplicar/sobrescrever quando inscrito tem linha tambem na Fase 1).
        $obj->carregarAvaliacaoFase2ByIdInscrito($debug);
        // Nome/dados do inscrito nao vem desse SELECT — busca em tab_inscritos.
        if (empty($obj->getNomeInscrito())) {
            $objIns = new Inscrito();
            $objIns->setCodInscrito($fws_['codInscrito']);
            try {
                $objIns->getInscritoById($debug);
                $obj->setNomeInscrito($objIns->getNomeInscrito());
                if (!$obj->getCodInscrito()) {
                    $obj->setCodInscrito($fws_['codInscrito']);
                }
            } catch (Exception $e) { /* segue sem nome */ }
        }
        return $obj;
    }

    /**
     * Salva a avaliacao Fase 2 (INSERT se nao existe, UPDATE se ja existe).
     */
    public function salvarAvaliacaoFase2($fws_, $debug = false)
    {
        $ok = false;
        $e = null;

        try {
            // 1. Detectar modo ativo. Bloqueia salvar em modo CONSULTA.
            $modo = $this->detectarModoFase2($debug);
            if ($modo === 'CONSULTA') {
                $objEtapaCheck = new Etapa();
                if ($objEtapaCheck->getEtapaByOrdem(2, false, $debug)) {
                    $rot = $objEtapaCheck->getRotuloSituacao();
                    throw new Exception("Edicao bloqueada: a Etapa 2 esta com situacao '{$rot}' ou fora das janelas configuradas (analise_dat_ini/fim, analise_recurso_dat_ini/fim).");
                }
                throw new Exception("Etapa 2 nao esta cadastrada para este evento. Cadastre em Outros > Etapas antes de salvar.");
            }

            // Resolve cod_etapa (pode estar em situacao=1 OU em Em andamento com janela de recurso aberta)
            $objEtapa = new Etapa();
            $codEtapaFase2 = (int)$objEtapa->getEtapaByOrdem(2, false, $debug);

            // 2. Carregar avaliacao existente.
            $objExistente = $this->carregarAvaliacaoFase2ByIdInscrito($fws_);
            if ($objExistente->getCodAvaliacao() != '') {
                $fws_['codAvaliacao'] = $objExistente->getCodAvaliacao();
            } else {
                $fws_['codAvaliacao'] = null;
            }

            // 3. Montar a entidade.
            // Setamos os campos do form manualmente porque PadraoVO::setAll()
            // interrompe a iteracao ao encontrar a chave "_submit_check" — e
            // ela vem como primeiro campo do POST, o que zeraria o objeto
            // inteiro num INSERT (primeira avaliacao do inscrito).
            $obj = new AvaliacaoInscrito();
            if (!empty($fws_['codAvaliacao'])) {
                $obj->setCodAvaliacao($fws_['codAvaliacao']);
                $obj->getAvaliacaoInscritoById($debug);
            }
            $obj->setCodInscrito($fws_['codInscrito'] ?? '');
            $obj->setNomeInscrito($fws_['nomeInscrito'] ?? '');
            $obj->setCodEtapa($codEtapaFase2);

            if ($modo === 'RECURSO') {
                // Em modo RECURSO so persiste campos do recurso.
                // Avaliacao inicial (Anexo II, pontos, parecer, status) fica intocada.
                $codAvalInicial = $obj->getCodAvaliacao();
                if (empty($codAvalInicial)) {
                    throw new Exception("Nao existe avaliacao inicial pra este inscrito. Recurso so pode ser analisado apos avaliacao inicial salva.");
                }
                if (empty(trim((string)$obj->getRecursoTexto()))) {
                    throw new Exception("Inscrito nao enviou recurso. Nada a analisar.");
                }
                // Os 3 campos sao obrigatorios em modo recurso
                $rpStatus  = trim((string)($fws_['classificacaoStatusAposRecurso'] ?? ''));
                $rpParecer = trim((string)($fws_['recursoParecer'] ?? ''));
                $rpPontos  = trim((string)($fws_['pontosTotalAposRecurso'] ?? ''));
                if ($rpStatus !== 'H' && $rpStatus !== 'I') {
                    throw new Exception("Informe o status da classificacao apos o recurso (Habilitado / Nao Habilitado).");
                }
                if ($rpParecer === '') {
                    throw new Exception("Informe o parecer do avaliador para o recurso.");
                }
                if ($rpPontos === '' || !is_numeric($rpPontos)) {
                    throw new Exception("Informe a pontuacao apos o recurso (numerica).");
                }
                $obj->setRecursoParecer($rpParecer);
                $obj->setPontosTotalAposRecurso($rpPontos);
                $obj->setClassificacaoStatusAposRecurso($rpStatus);
                // recurso_status: marca como AVALIADO. (status do inscrito era AGUARDANDO)
                $obj->setRecursoStatus('AVALIADO');
            } else {
                // Modo AVALIACAO (avaliacao inicial)
                $obj->setPontosTotalConfirmado($fws_['pontosTotalConfirmado'] ?? null);
                $obj->setParecerAvaliador($fws_['parecerAvaliador'] ?? '');
                $obj->setClassificacaoStatus($fws_['classificacaoStatus'] ?? '');
                // JSON da pontuacao (Anexo II)
                $objSrvFase1 = new GerenciarAvaliacaoInscritoAC();
                $obj->setAvaliacaoJson($objSrvFase1->buildAvaliacaoPesJsonForDb($fws_));
            }

            // 4. Persistir.
            $obj->salvar($debug);

            // 5. Registrar decisao no historico. Em modo recurso usa o status APOS recurso.
            $statusParaHistorico = ($modo === 'RECURSO')
                ? $obj->getClassificacaoStatusAposRecurso()
                : $obj->getClassificacaoStatus();
            $objHistAC = new HistoricoAvaliacaoInscritoAC();
            $objHistAC->registrarDecisao(
                $obj->getCodInscrito(),
                $codEtapaFase2,
                $statusParaHistorico,
                $debug
            );

            $ok = true;
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $ex) {
            $ok = false;
            $e = $ex;
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '0',
            'message'       => $e ? $e->getMessage() : 'Avaliacao Fase 2 salva com sucesso.',
            'codAvaliacao'  => isset($obj) && $obj ? $obj->getCodAvaliacao() : '',
            'codInscrito'   => $fws_['codInscrito'] ?? '',
        ));
    }

    /**
     * AJAX — marca/desmarca o documento como legivel/ilegivel. Independente do
     * salvar da avaliacao (inline, por documento).
     * Espera: codTrabalho, legivel (1/0/''), documentoLegivelObs.
     */
    public function marcarDocumentoLegivel($fws_, $debug = false)
    {
        $ok = false;
        $msg = '';
        try {
            // Mesma regra da avaliacao: so permite marcar enquanto a Fase 2 estiver ativa
            // (ordem_etapa=2 AND situacao=1 [Em andamento]). Fora do periodo, recusa.
            $codEtapaFase2 = $this->getCodEtapaFase2Ativa($debug);
            if (empty($codEtapaFase2)) {
                throw new Exception('Etapa 2 não está aberta para marcação. O período pode ter encerrado ou ainda não começou.');
            }

            $codTrabalho = isset($fws_['codTrabalho']) ? (int)$fws_['codTrabalho'] : 0;
            if ($codTrabalho <= 0) {
                throw new Exception('Documento não informado.');
            }
            $legivel = isset($fws_['legivel']) ? $fws_['legivel'] : '';
            if ($legivel !== '' && $legivel !== '0' && $legivel !== '1' && $legivel !== 0 && $legivel !== 1) {
                throw new Exception('Valor inválido para legibilidade.');
            }
            $obs = isset($fws_['documentoLegivelObs']) ? trim((string)$fws_['documentoLegivelObs']) : '';

            $obj = new Trabalho();
            $obj->setCodTrabalho($codTrabalho);
            $obj->setCodEvento(COD_EVENTO);
            $obj->setDocumentoLegivel($legivel);
            $obj->setDocumentoLegivelObs($obs);
            $obj->marcarLegibilidade($debug);
            $ok = true;
            $msg = 'Legibilidade atualizada.';
        } catch (Exception $ex) {
            $ok = false;
            $msg = $ex->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '0',
            'message'       => $msg,
            'codTrabalho'   => isset($codTrabalho) ? $codTrabalho : '',
        ));
    }

    /**
     * AJAX para popular o form ao clicar em "Avaliar/Editar avaliação"
     * na listagem.
     */
    public function editarAvaliacaoFase2ByIdInscrito($fws_, $debug = false)
    {
        $obj = $this->carregarAvaliacaoFase2ByIdInscrito($fws_, $debug);

        $json = array(
            'statusMessage'              => $obj->getCodInscrito() ? '1' : '0',
            'codAvaliacao'               => $obj->getCodAvaliacao(),
            'codInscrito'                => $obj->getCodInscrito(),
            'nomeInscrito'               => $obj->getNomeInscrito(),
            'pontosTotalConfirmado'      => $obj->getPontosTotalConfirmado(),
            'classificacaoStatus'        => $obj->getClassificacaoStatus(),
            'parecerAvaliador'           => tratarJson($obj->getParecerAvaliador()),
            'avaliacaoJson'              => $obj->getAvaliacaoJson(),
        );
        echo json_encode($json);
        exit();
    }

    /**
     * Renderiza o cabecalho da tabela da listagem Fase 2.
     * Mantem o mesmo padrao da listagem da Fase 1 (cabecalho da
     * GerenciarAvaliacaoInscritoAC::carregarCabecalhoTrabelaAvaliacaoInscrito).
     */
    public function carregarCabecalhoTabelaFase2($fws_, $debug = false)
    {
        ?>
        <th width="40"  style="white-space:normal; overflow:visible; text-overflow:clip; text-align:center;">#</th>
        <th width="150"  style="white-space:nowrap; overflow:visible; text-overflow:clip;">A&ccedil;&otilde;es</th>
        <th width="110" data-col="2" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">C&oacute;digo Inscrito <span class="sort-arrow">&#8597;</span></th>
        <th             data-col="3" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Nome <span class="sort-arrow">&#8597;</span></th>
        <th width="100" data-col="4" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Pontos Total Confirmados <span class="sort-arrow">&#8597;</span></th>
        <th width="110" data-col="5" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Classif. Status <span class="sort-arrow">&#8597;</span></th>
        <th width="100" data-col="6" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer; text-align:center;">Docs Ileg&iacute;veis <span class="sort-arrow">&#8597;</span></th>
        <th width="90" data-col="7" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Solicitou Recurso <span class="sort-arrow">&#8597;</span></th>
        <th width="90" data-col="8" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Pontos Ap&oacute;s Recurso <span class="sort-arrow">&#8597;</span></th>
        <th width="130" data-col="9" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Classif. Status Ap&oacute;s Recurso <span class="sort-arrow">&#8597;</span></th>
        <?php
    }

    /**
     * Renderiza o tbody da listagem da Fase 2 (180 habilitados, com avaliacao
     * pendente ou ja realizada).
     */
    public function carregarTabelaFase2($fws_, $debug = false)
    {
        $filtro = isset($fws_['filtroAvaliacao']) ? $fws_['filtroAvaliacao'] : 'todos';
        $condicoes = array('filtro' => $filtro);

        // Modo atual da etapa: AVALIACAO | RECURSO | CONSULTA
        $modoListagem = $this->detectarModoFase2($debug);
        $modoConsulta = ($modoListagem === 'CONSULTA');

        $obj = new AvaliacaoInscrito();
        $lista = $obj->carregarListaInscritosElegiveisFase2($condicoes, $debug);

        if (empty($lista)) {
            echo '<tr><td colspan="10" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum inscrito elegivel encontrado para o filtro selecionado.</td></tr>';
            return;
        }

        $totalLinhas = 0;
        $contadorLinha = 0;
        foreach ($lista as $objL) {
            $contadorLinha++;
            $totalLinhas++;
            $temAvaliacao = !empty($objL->getCodAvaliacaoFase2());
            $statusF2     = $objL->getClassifStatusFase2();
            $pontosF2     = $objL->getPontosFase2();
            $pontosInfF2  = $objL->getPontosInformadosFase2();
            $recursoF2    = trim((string)$objL->getRecursoTextoFase2());
            $pontosAposF2 = $objL->getPontosAposRecursoFase2();
            $classifAposF2= $objL->getClassifAposRecursoFase2();
            $parecerF2    = trim((string)$objL->getParecerAvaliadorFase2());
            $codAvalF2    = (int)$objL->getCodAvaliacaoFase2();

            // Badge de status
            if (!$temAvaliacao) {
                $badge = '<span style="display:inline-block; padding:3px 10px; border-radius:12px; background:#fff4e5; color:#8a6d3b; font-size:11px; font-weight:600;">Pendente</span>';
                $btnTexto = 'Avaliar';
            } else if ($statusF2 === 'H') {
                $badge = '<span style="display:inline-block; padding:3px 10px; border-radius:12px; background:#e6f4ea; color:#2d6a4f; font-size:11px; font-weight:600;">Habilitado</span>';
                $btnTexto = 'Reavaliar';
            } else if ($statusF2 === 'I') {
                $badge = '<span style="display:inline-block; padding:3px 10px; border-radius:12px; background:#fde8e8; color:#a94442; font-size:11px; font-weight:600;">N&atilde;o Habilitado</span>';
                $btnTexto = 'Reavaliar';
            } else {
                $badge = '<span style="display:inline-block; padding:3px 10px; border-radius:12px; background:#f1f1f1; color:#555; font-size:11px;">Sem status</span>';
                $btnTexto = 'Reavaliar';
            }
            $btnClasse = 'btn-success';
            if ($modoListagem === 'CONSULTA') {
                // Fora de qualquer janela: tudo so consulta
                $btnTexto  = 'Consultar';
                $btnClasse = 'btn-default';
            } else if ($modoListagem === 'RECURSO') {
                // Janela de analise de recurso aberta
                if ($recursoF2 === '') {
                    // Inscrito nao enviou recurso -> nada pra avaliar; consulta
                    $btnTexto  = 'Consultar';
                    $btnClasse = 'btn-default';
                } else if (trim((string)$objL->getClassifAposRecursoFase2()) === '') {
                    // Recurso enviado mas ainda nao avaliado -> botao destaque azul
                    $btnTexto  = 'Avaliar Recurso';
                    $btnClasse = 'btn-success';
                } else {
                    // Recurso ja avaliado
                    $btnTexto  = 'Reavaliar Recurso';
                    $btnClasse = 'btn-success';
                }
            }
            // modo AVALIACAO: mantem Avaliar/Reavaliar verde definido acima
            ?>
            <?php
            // Status Fase 1 (vem como uma das colunas adicionais).
            $statusF1 = $objL->getStatusFase1Final();
            if ($statusF1 === 'H') {
                $badgeF1 = '<span style="display:inline-block; padding:2px 8px; border-radius:10px; background:#e6f4ea; color:#2d6a4f; font-size:11px; font-weight:600;">Habilitado</span>';
            } else if ($statusF1 === 'I') {
                $badgeF1 = '<span style="display:inline-block; padding:2px 8px; border-radius:10px; background:#fde8e8; color:#a94442; font-size:11px; font-weight:600;">N&atilde;o Habil.</span>';
            } else {
                $badgeF1 = '<span style="display:inline-block; padding:2px 8px; border-radius:10px; background:#f1f1f1; color:#888; font-size:11px;">&mdash;</span>';
            }
            ?>
            <tr id="<?php echo $objL->getCodInscrito() ?>">
                <td style="text-align:center; color:#888; font-size:12px;"><?php echo $contadorLinha ?></td>
                <td>
                    <button type="button" class="btn btn-sm <?php echo $btnClasse ?> btn-acessar-avaliacao-fase2" onclick="acessarAvaliacaoFase2(<?php echo $objL->getCodInscrito() ?>)">
                        <?php echo $btnTexto ?>
                    </button>
                </td>
                <td>
                    <?php
                    if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_LOGAR_COMO, 5, false, false)) {
                        $cpf = method_exists($objL, 'getCpf') ? $objL->getCpf() : '';
                        ?>
                        <a title='Logar Como' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $cpf ?>&cod_inscrito=<?php echo $objL->getCodInscrito() ?>&cod_evento=<?php echo COD_EVENTO ?>'>
                            <img src='<?php echo WB_URL_IMG ?>/icone_preview.gif' border='0'>
                        </a>
                    <?php } ?>
                    <?php echo $objL->getCodInscrito() ?>
                </td>
                <td>
                    <?php echo textoMaiusculoMinusculo($objL->getNomeInscrito()) ?>
                    <?php if ($parecerF2 !== '') { ?>
                        <br>
                        <button class="btn btn-xs btn-info" type="button" onclick="toggleParecer(<?php echo $codAvalF2 ?>)">
                            Mostrar Parecer
                        </button>
                        <div id="parecer-<?php echo $codAvalF2 ?>" style="display:none;">
                            <h5 style="margin-top:6px;">Parecer do Avaliador:</h5>
                            <?php echo nl2br(htmlspecialchars($parecerF2)) ?>
                        </div>
                    <?php } ?>
                </td>
                <td style="text-align:center;"><?php echo $pontosF2 !== null && $pontosF2 !== '' ? number_format((float)$pontosF2, 2, ',', '.') : '&mdash;' ?></td>
                <td><?php echo $badge ?></td>
                <td style="text-align:center;">
                    <?php
                    $qtdIleg = (int) $objL->getQtdDocsIlegiveis();
                    $qtdTot  = (int) $objL->getQtdDocsTotal();
                    if ($qtdTot == 0) {
                        echo '<span style="color:#bbb;">&mdash;</span>';
                    } else if ($qtdIleg > 0) {
                        echo '<span style="display:inline-block; padding:2px 8px; border-radius:10px; background:#fde8e8; color:#a94442; font-size:11px; font-weight:600;" title="' . $qtdIleg . ' de ' . $qtdTot . ' documento(s) marcado(s) como ilegivel">' . $qtdIleg . ' / ' . $qtdTot . '</span>';
                    } else {
                        echo '<span style="display:inline-block; padding:2px 8px; border-radius:10px; background:#e6f4ea; color:#2d6a4f; font-size:11px; font-weight:600;" title="Nenhum documento marcado como ilegivel">0 / ' . $qtdTot . '</span>';
                    }
                    ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($recursoF2 !== '') { ?>
                        <span style="display:inline-block; padding:2px 8px; border-radius:10px; background:#fff4e5; color:#8a6d3b; font-size:11px; font-weight:600;">Sim</span>
                    <?php } else { ?>
                        <span style="color:#bbb;">&mdash;</span>
                    <?php } ?>
                </td>
                <td style="text-align:center;"><?php echo $pontosAposF2 !== null && $pontosAposF2 !== '' ? number_format((float)$pontosAposF2, 2, ',', '.') : '&mdash;' ?></td>
                <td style="text-align:center;">
                    <?php
                    if ($classifAposF2 === 'H') {
                        echo '<span style="display:inline-block; padding:2px 8px; border-radius:10px; background:#e6f4ea; color:#2d6a4f; font-size:11px; font-weight:600;">Habilitado</span>';
                    } else if ($classifAposF2 === 'I') {
                        echo '<span style="display:inline-block; padding:2px 8px; border-radius:10px; background:#fde8e8; color:#a94442; font-size:11px; font-weight:600;">N&atilde;o Habil.</span>';
                    } else {
                        echo '<span style="color:#bbb;">INDEFINIDO</span>';
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <tr class="no-sort">
            <td colspan="10" style="text-align:right; color:#999; font-size:12px; padding:8px 12px; border-top:1px solid #eee;">
                Total: <strong><?php echo $totalLinhas ?></strong> registro<?php echo $totalLinhas === 1 ? '' : 's' ?>
            </td>
        </tr>
        <script>
        // Definido aqui caso ainda nao esteja carregado pela CarregarBotoesAC.
        if (typeof toggleParecer !== 'function') {
            window.toggleParecer = function(codAvaliacao) {
                var d = document.getElementById('parecer-' + codAvaliacao);
                if (!d) return;
                d.style.display = (d.style.display === 'none') ? 'block' : 'none';
            };
        }
        </script>
        <?php
    }
}
