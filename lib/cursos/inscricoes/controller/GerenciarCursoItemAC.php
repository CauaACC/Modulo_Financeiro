<?php

class GerenciarCursoItemAC {

    /**
     * Papeis que podem MONTAR o conteudo (criar/editar/excluir/inativar item).
     * Coordenador do EAD ou ADM_GERAL. A lista vive em FWA_permissoesCoordenarEAD()
     * (include/functions/FWA_CursoEadEscopo.php) -- uma fonte so para controllers,
     * views e menus.
     */
    public static function permissoesCoordenarEAD() {
        return FWA_permissoesCoordenarEAD();
    }

    /** Papeis que podem ACOMPANHAR (relatorios, presenca, folha de ponto). */
    public static function permissoesAcompanharEAD() {
        return FWA_permissoesAcompanharEAD();
    }

    /**
     * Piso da classe: quem acompanha entra. Os metodos de MONTAGEM chamam
     * exigirCoordenador() na primeira linha.
     *
     * ATENCAO ao acrescentar metodo aqui: ele nasce liberado para o professor.
     * Se for de montagem (grava, apaga ou revela opcoes de edicao), a primeira
     * linha tem de ser $this->exigirCoordenador().
     */
    function __construct() {
        FWA_exigirPermissao(self::permissoesAcompanharEAD());
    }

    private function exigirCoordenador() {
        FWA_exigirPermissao(self::permissoesCoordenarEAD());
    }

    public function carregarCursoItem($fws_, $debug = false) {
        $obj = new CursoItem();
        $obj->setCodCursoItem($fws_['codCursoItem']);
        $obj->getCursoItemById($debug);
        return $obj;
    }

    public function editarCursoItem($fws_, $debug = false) {
        $this->exigirCoordenador();
        $obj = $this->carregarCursoItem($fws_, $debug);
        echo json_encode(array(
            'statusMessage'           => true,
            'codCursoItem'            => $obj->getCodCursoItem(),
            'codCurso'                => $obj->getCodCurso(),
            'codPai'                  => $obj->getCodPai(),
            'codPreRequisito'         => $obj->getCodPreRequisito(),
            'tipo'                    => $obj->getTipo(),
            'titulo'                  => $obj->getTitulo(),
            'descricao'               => $obj->getDescricao(),
            'url'                     => $obj->getUrl(),
            'dtLiberacao'             => $obj->getDtLiberacao(),
            'horaInicio'              => $obj->getHoraInicio(),
            'horaFim'                 => $obj->getHoraFim(),
            'ordem'                   => $obj->getOrdem(),
            'controlePresenca'        => (int) $obj->getControlePresenca(),
            'permiteComentarios'      => (int) $obj->getPermiteComentarios(),
            'intervaloConfirmacaoMin' => $obj->getIntervaloConfirmacaoMin(),
            'intervaloConfirmacaoMax' => $obj->getIntervaloConfirmacaoMax(),
            'timeoutConfirmacaoSeg'   => $obj->getTimeoutConfirmacaoSeg(),
            'status'                  => $obj->getStatus(),
        ));
        exit();
    }

    public function salvarCursoItem($fws_, $debug = false) {
        $ok = false;
        $codCursoItem = '';
        $message = '';
        try {
            $this->exigirCoordenador();
            $obj = $this->carregarCursoItem($fws_, $debug);
            $isForm = true;
            $obj->setAll($fws_, $isForm);
            $obj->setCodEvento(COD_EVENTO);

            // Normalizar valores nulos
            if (empty($fws_['codPai'])) $obj->setCodPai(null);
            if (empty($fws_['codPreRequisito'])) $obj->setCodPreRequisito(null);
            if (empty($fws_['codCurso'])) {
                throw new Exception("Selecione o curso ao qual o item pertence.");
            }

            // Defaults por tipo se não vier do formulário
            if (!isset($fws_['controlePresenca'])) {
                $tipo = $obj->getTipo();
                $obj->setControlePresenca(in_array($tipo, array('VIDEO','MEET')) ? 1 : 0);
            }
            if (!isset($fws_['permiteComentarios'])) {
                $tipo = $obj->getTipo();
                $obj->setPermiteComentarios(in_array($tipo, array('LINK','FORMULARIO','CAPITULO')) ? 0 : 1);
            }

            if (!$obj->getStatus()) $obj->setStatus('ATIVADA');
            if (!$obj->getOrdem()) $obj->setOrdem(0);
            if (!$obj->getIntervaloConfirmacaoMin()) $obj->setIntervaloConfirmacaoMin(5);
            if (!$obj->getIntervaloConfirmacaoMax()) $obj->setIntervaloConfirmacaoMax((int) $obj->getIntervaloConfirmacaoMin());
            if (!$obj->getTimeoutConfirmacaoSeg()) $obj->setTimeoutConfirmacaoSeg(60);

            // Anti-ciclo: pai não pode ser ele mesmo nem descendente
            if ($obj->getCodCursoItem() && $obj->getCodPai()
                && (int) $obj->getCodPai() === (int) $obj->getCodCursoItem()) {
                throw new Exception("Um item não pode ser pai de si mesmo.");
            }

            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $codCursoItem = $obj->getCodCursoItem();
            $message = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => tratarJson($message),
            'codCursoItem'  => $codCursoItem,
        ));
    }

    public function excluirCursoItem($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $this->exigirCoordenador();
            $obj = new CursoItem();
            $obj->setCodCursoItem($fws_['codCursoItem']);
            $obj->excluir($debug);
        } catch (OKException $e) {
            $ok = true;
            $message = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => tratarJson($message),
            'codCursoItem'  => $fws_['codCursoItem'],
        ));
    }

    /**
     * Tira o item do ar preservando o historico. E' a saida para o item que nao
     * pode ser excluido -- e a acao principal depois que o curso comeca a rodar.
     *
     * Acao propria, e nao um salvarCursoItem com status: o salvar monta o objeto
     * a partir do formulario inteiro (setAll com isForm) e apagaria titulo, URL
     * e janela se recebesse so o codigo e o status.
     */
    public function inativarCursoItem($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $this->exigirCoordenador();
            $obj = new CursoItem();
            $obj->setCodCursoItem($fws_['codCursoItem']);
            $obj->inativar($debug);
        } catch (OKException $e) {
            $ok = true;
            $message = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => tratarJson($message),
            'codCursoItem'  => $fws_['codCursoItem'],
        ));
    }

    public function reativarCursoItem($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $this->exigirCoordenador();
            $obj = new CursoItem();
            $obj->setCodCursoItem($fws_['codCursoItem']);
            $obj->reativar($debug);
        } catch (OKException $e) {
            $ok = true;
            $message = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => tratarJson($message),
            'codCursoItem'  => $fws_['codCursoItem'],
        ));
    }

    public function carregarOpcoesCurso($fws_, $debug = false) {
        $this->exigirCoordenador();
        $c = new Curso();
        $c->carregarLista(false, $debug);
        $lista = $c->getListaCurso() ?: array();
        $out = array();
        foreach ($lista as $cu) {
            $out[] = array('cod' => (int) $cu->getCodCurso(), 'titulo' => $cu->getTitulo());
        }
        echo json_encode($out);
    }

    public function carregarOpcoesPai($fws_, $debug = false) {
        $this->exigirCoordenador();
        $codCurso = isset($fws_['codCurso']) ? (int) $fws_['codCurso'] : 0;
        $excluir = isset($fws_['excluirCodItem']) ? (int) $fws_['excluirCodItem'] : null;
        $obj = new CursoItem();
        echo json_encode($obj->carregarPossiveisPais($codCurso, $excluir, $debug));
    }

    public function carregarOpcoesPreRequisito($fws_, $debug = false) {
        $this->exigirCoordenador();
        $codCurso = isset($fws_['codCurso']) ? (int) $fws_['codCurso'] : 0;
        $excluir = isset($fws_['excluirCodItem']) ? (int) $fws_['excluirCodItem'] : null;
        $obj = new CursoItem();
        echo json_encode($obj->carregarPossiveisPreRequisitos($codCurso, $excluir, $debug));
    }

    public function carregarRelatorioGeral($fws_, $debug = false) {
        $obj = new CursoItemPresenca();
        try {
            $linhas = $obj->carregarRelatorioGeral(COD_EVENTO, $debug);
            if (!$linhas || count($linhas) == 0) {
                echo "<tr><td colspan='8' style='text-align:center; padding:30px; color:#999;'>Nenhum item cadastrado neste evento.</td></tr>";
                return;
            }
            foreach ($linhas as $r) {
                $codHV = (int) $r['cod_curso_item'];
                $tipo = htmlspecialchars($r['tipo']);
                $titulo = htmlspecialchars($r['titulo']);
                $qtd = (int) $r['qtd_inscritos'];
                $ok = (int) $r['conf_ok'];
                $exp = (int) $r['conf_exp'];
                $fora = (int) $r['fora_hor'];
                ?>
                <tr id="rel-<?php echo $codHV; ?>">
                    <td><span class="badge-tipo"><?php echo $tipo; ?></span></td>
                    <td class="col-nome"><?php echo $titulo; ?></td>
                    <td style="text-align:center;"><?php echo $qtd; ?></td>
                    <td style="text-align:center; color:#27ae60; font-weight:600;"><?php echo $ok; ?></td>
                    <td style="text-align:center; color:#e74c3c; font-weight:600;"><?php echo $exp; ?></td>
                    <td style="text-align:center; color:#f39c12; font-weight:600;"><?php echo $fora; ?></td>
                    <td style="text-align:center;">
                        <?php if ($qtd > 0) { ?>
                            <button type="button" class="btn btn-xs btn-default verDetalhes" data-cod="<?php echo $codHV; ?>" data-titulo="<?php echo $titulo; ?>"><i class="fa fa-users"></i> Inscritos</button>
                        <?php } else { ?>
                            <span style="color:#999;">—</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='8' style='text-align:center; padding:30px; color:#c0392b;'>" . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
    }

    public function carregarPainelAlunos($fws_, $debug = false) {
        $codCurso = isset($fws_['codCurso']) && $fws_['codCurso'] ? (int) $fws_['codCurso'] : null;
        $diasAtraso = isset($fws_['diasAtraso']) ? (int) $fws_['diasAtraso'] : 7;
        $obj = new CursoItemPresenca();
        $alunos = $obj->carregarPainelAlunos(COD_EVENTO, $codCurso, $debug);

        if (!$alunos) {
            echo "<tr><td colspan='7' style='text-align:center; padding:30px; color:#999;'>Nenhum aluno acessou cursos ainda.</td></tr>";
            return;
        }
        $hoje = time();
        foreach ($alunos as $a) {
            $tempoSeg = (int) $a['tempo_efetivo_seg'];
            $tempoFmt = sprintf('%02d:%02d:%02d', floor($tempoSeg / 3600), floor(($tempoSeg % 3600) / 60), $tempoSeg % 60);
            $ultima = $a['ultima_atividade'];
            $diasSem = $ultima ? floor(($hoje - strtotime($ultima)) / 86400) : 999;
            $atrasado = $diasSem > $diasAtraso;
            $alertaAtraso = $atrasado ? '<span title="Sem atividade há '.$diasSem.' dias" style="color:#dc3545; margin-right:6px;"><i class="fa fa-exclamation-triangle"></i></span>' : '';
            ?>
            <tr>
                <td><?php echo $alertaAtraso; ?><a href="javascript:void(0)" class="abrirDetalheAluno" data-cod="<?php echo (int) $a['cod_inscrito']; ?>" data-nome="<?php echo htmlspecialchars($a['nome_inscrito']); ?>"><?php echo htmlspecialchars($a['nome_inscrito']); ?></a><br><small style="color:#888;"><?php echo htmlspecialchars($a['e_mail']); ?></small></td>
                <td style="text-align:center;"><?php echo (int) $a['qtd_cursos']; ?></td>
                <td style="text-align:center;"><?php echo (int) $a['qtd_concluidos']; ?></td>
                <td style="text-align:center; font-family:monospace;"><?php echo $tempoFmt; ?></td>
                <td><small><?php echo htmlspecialchars($ultima ?: '—'); ?></small><?php if ($atrasado) echo ' <small style="color:#dc3545;">('.$diasSem.'d)</small>'; ?></td>
                <td style="text-align:center;"><button type="button" class="btn btn-xs btn-default abrirDetalheAluno" data-cod="<?php echo (int) $a['cod_inscrito']; ?>" data-nome="<?php echo htmlspecialchars($a['nome_inscrito']); ?>"><i class="fa fa-folder-open"></i> Detalhar</button></td>
            </tr>
            <?php
        }
    }

    public function carregarDetalheAluno($fws_, $debug = false) {
        $codInscrito = (int) $fws_['codInscrito'];
        $obj = new CursoItemPresenca();
        $progresso = $obj->carregarProgressoAlunoPorCurso($codInscrito, COD_EVENTO, $debug);
        $itens = $obj->carregarDetalheAlunoPorItem($codInscrito, COD_EVENTO, $debug);
        echo json_encode(array(
            'statusMessage' => true,
            'progresso' => $progresso,
            'itens' => $itens,
        ));
    }

    public function carregarTimeline($fws_, $debug = false) {
        $codInscrito = (int) $fws_['codInscrito'];
        $codCursoItem = (int) $fws_['codCursoItem'];
        $codSessao = !empty($fws_['codSessao']) ? (string) $fws_['codSessao'] : null;
        $obj = new CursoItemPresenca();
        $linhas = $obj->carregarTimelineEventos($codInscrito, $codCursoItem, $codSessao, $debug);
        echo json_encode(array('statusMessage' => true, 'eventos' => $linhas));
    }

    public function carregarFolhaPonto($fws_, $debug = false) {
        $codInscrito = isset($fws_['codInscrito']) && $fws_['codInscrito'] ? (int) $fws_['codInscrito'] : null;
        $obj = new CursoItemPresenca();
        $linhas = $obj->carregarFolhaPontoPorAluno(COD_EVENTO, $codInscrito, $debug);
        if (!$linhas) {
            echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#999;'>Sem registros de presença ainda.</td></tr>";
            return;
        }
        $totalGeralSeg = 0;
        $alunoAtual = null;
        foreach ($linhas as $r) {
            $tempoSeg = (int) $r['tempo_efetivo_seg'];
            $tempoFmt = sprintf('%02d:%02d:%02d', floor($tempoSeg / 3600), floor(($tempoSeg % 3600) / 60), $tempoSeg % 60);
            $totalGeralSeg += $tempoSeg;
            if ($alunoAtual !== $r['cod_inscrito']) {
                ?>
                <tr style="background:#eef2f5;">
                    <td colspan="6" style="font-weight:700; padding:10px;"><i class="fa fa-user"></i> <?php echo htmlspecialchars($r['nome_inscrito']); ?> &nbsp; <small style="color:#888;"><?php echo htmlspecialchars($r['e_mail']); ?></small></td>
                </tr>
                <?php
                $alunoAtual = $r['cod_inscrito'];
            }
            ?>
            <tr>
                <td></td>
                <td><?php echo htmlspecialchars($r['dia']); ?></td>
                <td style="text-align:center;"><?php echo (int) $r['qtd_sessoes']; ?></td>
                <td style="text-align:center; font-family:monospace; font-weight:600;"><?php echo $tempoFmt; ?></td>
                <td><small><?php echo htmlspecialchars($r['primeira_entrada']); ?></small></td>
                <td><small><?php echo htmlspecialchars($r['ultima_saida']); ?></small></td>
            </tr>
            <?php
        }
        $totGFmt = sprintf('%02d:%02d:%02d', floor($totalGeralSeg / 3600), floor(($totalGeralSeg % 3600) / 60), $totalGeralSeg % 60);
        ?>
        <tr style="background:#f8f9fa; font-weight:700;"><td colspan="3" style="text-align:right; padding:10px;">Total geral:</td><td style="text-align:center; font-family:monospace;"><?php echo $totGFmt; ?></td><td colspan="2"></td></tr>
        <?php
    }

    public function exportarFolhaPontoCsv($fws_, $debug = false) {
        $obj = new CursoItemPresenca();
        $linhas = $obj->carregarFolhaPontoPorAluno(COD_EVENTO, null, $debug);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=folha_ponto_curso_evento_' . COD_EVENTO . '.csv');
        $out = fopen('php://output', 'w');
        // BOM pra Excel reconhecer UTF-8
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array('cod_inscrito','nome','email','dia','qtd_sessoes','tempo_efetivo_seg','tempo_efetivo','primeira_entrada','ultima_saida'), ';');
        foreach ($linhas as $r) {
            $tempoSeg = (int) $r['tempo_efetivo_seg'];
            $tempoFmt = sprintf('%02d:%02d:%02d', floor($tempoSeg / 3600), floor(($tempoSeg % 3600) / 60), $tempoSeg % 60);
            fputcsv($out, array(
                $r['cod_inscrito'], $r['nome_inscrito'], $r['e_mail'],
                $r['dia'], $r['qtd_sessoes'], $tempoSeg, $tempoFmt,
                $r['primeira_entrada'], $r['ultima_saida']
            ), ';');
        }
        fclose($out);
        exit;
    }

    public function carregarRelatorioInscritosPorItem($fws_, $debug = false) {
        $codCursoItem = (int) $fws_['codCursoItem'];
        $obj = new CursoItemPresenca();
        $linhas = $obj->carregarRelatorioPorItem($codCursoItem, $debug);
        if (!$linhas || count($linhas) == 0) {
            echo "<tr><td colspan='8' style='text-align:center; padding:20px; color:#999;'>Nenhum inscrito assistiu este item ainda.</td></tr>";
            return;
        }
        foreach ($linhas as $r) {
            $nome = htmlspecialchars($r['nome_inscrito']);
            $email = htmlspecialchars($r['e_mail']);
            $sessoes = (int) $r['sessoes'];
            $ok = (int) $r['conf_ok'];
            $exp = (int) $r['conf_exp'];
            $fora = (int) $r['fora_hor'];
            $tempoSeg = (int) $r['tempo_segundos'];
            $tempoFmt = sprintf('%02d:%02d:%02d', floor($tempoSeg / 3600), floor(($tempoSeg % 3600) / 60), $tempoSeg % 60);
            $primeira = htmlspecialchars($r['primeira_visita']);
            $ultima = htmlspecialchars($r['ultima_visita']);
            ?>
            <tr>
                <td class="col-nome"><?php echo $nome; ?><br><small style="color:#888;"><?php echo $email; ?></small></td>
                <td style="text-align:center;"><?php echo $sessoes; ?></td>
                <td style="text-align:center; color:#27ae60; font-weight:600;"><?php echo $ok; ?></td>
                <td style="text-align:center; color:#e74c3c; font-weight:600;"><?php echo $exp; ?></td>
                <td style="text-align:center; color:#f39c12; font-weight:600;"><?php echo $fora; ?></td>
                <td style="text-align:center; font-family:monospace;"><?php echo $tempoFmt; ?></td>
                <td><small><?php echo $primeira; ?></small></td>
                <td><small><?php echo $ultima; ?></small></td>
            </tr>
            <?php
        }
    }

    /**
     * Renderiza árvore agrupada por curso, com indentação por nível.
     */
    public function carregarTabelaCursoItem($fws_, $debug = false) {
        $obj = new CursoItem();
        try {
            $linhas = $obj->carregarArvore(null, $debug);
            if (!$linhas || count($linhas) == 0) {
                echo "<tr><td colspan='8' style='text-align:center; padding:30px; color:#999;'>Nenhum item cadastrado ainda. Crie um <strong>Curso</strong> primeiro e depois adicione itens.</td></tr>";
                return;
            }

            // Indexar por cod_curso → cod_pai → array de itens
            $porCurso = array();
            foreach ($linhas as $l) {
                $codC = (int) $l['cod_curso'];
                $codP = (int) $l['cod_pai'];
                $porCurso[$codC][$codP][] = $l;
            }

            // Dependentes de TODOS os itens numa consulta so: a linha ja sai da
            // grade sabendo se pode ser excluida, em vez de o usuario descobrir
            // no erro depois do clique.
            $daoItem = new CursoItemDAO();
            $mapaDep = $daoItem->contarDependentesPorItem(COD_EVENTO, $debug);
            $podeMontar = FWA_temPermissao(self::permissoesCoordenarEAD());

            foreach ($porCurso as $codCurso => $porPai) {
                if ($codCurso > 0) {
                    // cabeçalho do curso
                    $tituloCurso = '';
                    foreach ($linhas as $l2) {
                        if ((int) $l2['cod_curso'] === $codCurso) { $tituloCurso = $l2['curso_titulo']; break; }
                    }
                    ?>
                    <tr style="background:#eef2f5;">
                        <td colspan="8" style="font-weight:700; color:#333; padding:10px 16px;">
                            <i class="fa fa-graduation-cap"></i> Curso: <?php echo htmlspecialchars($tituloCurso); ?>
                        </td>
                    </tr>
                    <?php
                } else {
                    ?>
                    <tr style="background:#fff8e1;">
                        <td colspan="8" style="font-weight:700; color:#666; padding:10px 16px;">
                            <i class="fa fa-folder-open-o"></i> Itens sem curso (legado)
                        </td>
                    </tr>
                    <?php
                }
                if (isset($porPai[0])) {
                    $this->renderRamo($porPai, $porPai[0], 0, $mapaDep, $podeMontar);
                }
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='8' style='text-align:center; padding:30px; color:#c0392b;'>" . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
    }

    private function renderRamo($porPai, $filhos, $nivel, $mapaDep = array(), $podeMontar = true) {
        foreach ($filhos as $it) {
            $tipo = $it['tipo'];
            $isCapitulo = ($tipo === 'CAPITULO');
            $dot = 'dot-blue';
            if ($tipo === 'TAREFA') $dot = 'dot-orange';
            if ($tipo === 'PDF') $dot = 'dot-red';
            if ($tipo === 'TEXTO') $dot = 'dot-purple';
            if ($tipo === 'MEET') $dot = 'dot-green';
            if ($isCapitulo) $dot = 'dot-blue';

            $statusDot = ($it['status'] === 'ATIVADA') ? 'dot-green' : 'dot-red';
            $statusTxt = ($it['status'] === 'ATIVADA') ? 'Ativo' : 'Inativo';

            $janelaTxt = '';
            if ($it['dt_liberacao']) $janelaTxt .= htmlspecialchars($it['dt_liberacao']) . ' ';
            if ($it['hora_inicio'] || $it['hora_fim']) {
                $hi = $it['hora_inicio'] ? substr($it['hora_inicio'], 0, 5) : '00:00';
                $hf = $it['hora_fim']    ? substr($it['hora_fim'],    0, 5) : '23:59';
                $janelaTxt .= $hi . ' - ' . $hf;
            }
            if (!$janelaTxt) $janelaTxt = '<span style="color:#999;">herda</span>';

            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $nivel);
            $icone = $isCapitulo ? '<i class="fa fa-folder-o" style="color:#3a5a7d;"></i>' : '<i class="fa fa-file-o" style="color:#888;"></i>';
            $tituloHtml = $indent . $icone . ' ' . htmlspecialchars($it['titulo']);
            $codItem  = (int) $it['cod_curso_item'];
            $dep      = isset($mapaDep[$codItem]) ? $mapaDep[$codItem] : CursoItemDAO::dependentesZerados();
            $travas   = CursoItem::descreverDependentes($dep);
            $bloqueio = implode(', ', $travas);
            $ativo    = ($it['status'] === 'ATIVADA');
            ?>
            <tr id="<?php echo $codItem; ?>"
                data-cod-curso-item="<?php echo $codItem; ?>"
                data-titulo="<?php echo htmlspecialchars($it['titulo']); ?>"
                data-status="<?php echo htmlspecialchars($it['status']); ?>"
                data-tem-dep="<?php echo $bloqueio ? 1 : 0; ?>"
                data-bloqueios="<?php echo htmlspecialchars($bloqueio); ?>">
                <td>
                    <?php if (!$podeMontar) { ?>
                        <span class="text-muted" title="Somente o coordenador do curso edita o conteúdo">—</span>
                    <?php } else { ?>
                        <span class="editar btn-acao" title="Editar"><i class="fa fa-pencil"></i></span>
                        <?php if ($bloqueio) { ?>
                            <span class="btn-acao text-muted" style="cursor:not-allowed; opacity:.5;"
                                  title="Não é possível excluir: <?php echo htmlspecialchars($bloqueio); ?>"><i class="fa fa-trash"></i></span>
                        <?php } else { ?>
                            <span class="excluir btn-acao btn-acao-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                        <?php } ?>
                        <?php if ($ativo) { ?>
                            <span class="inativar btn-acao" title="Inativar (tira do ar e preserva o histórico)"><i class="fa fa-power-off"></i></span>
                        <?php } else { ?>
                            <span class="reativar btn-acao" title="Reativar (volta a aparecer para os alunos)"><i class="fa fa-refresh"></i></span>
                        <?php } ?>
                    <?php } ?>
                </td>
                <td><?php echo (int) $it['ordem']; ?></td>
                <td><span class="badge-tipo"><span class="dot <?php echo $dot; ?>"></span><?php echo htmlspecialchars($tipo); ?></span></td>
                <td class="col-nome"><?php echo $tituloHtml; ?></td>
                <td><?php echo $janelaTxt; ?></td>
                <td>
                    <?php if ((int) $it['controle_presenca'] === 1) { ?>
                        <span title="DETRAN ativo"><i class="fa fa-eye" style="color:#27ae60;"></i></span>
                    <?php } else { ?>
                        <span style="color:#bbb;">—</span>
                    <?php } ?>
                    &nbsp;
                    <?php if ((int) $it['permite_comentarios'] === 1) { ?>
                        <span title="Comentários abertos"><i class="fa fa-comment-o" style="color:#2980b9;"></i></span>
                    <?php } else { ?>
                        <span style="color:#bbb;">—</span>
                    <?php } ?>
                </td>
                <td><span class="badge-tipo"><span class="dot <?php echo $statusDot; ?>"></span><?php echo $statusTxt; ?></span></td>
            </tr>
            <?php
            $codAtual = (int) $it['cod_curso_item'];
            if (isset($porPai[$codAtual])) {
                $this->renderRamo($porPai, $porPai[$codAtual], $nivel + 1, $mapaDep, $podeMontar);
            }
        }
    }
}
