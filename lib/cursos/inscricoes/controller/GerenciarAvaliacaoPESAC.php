<?php

class GerenciarAvaliacaoPESAC {

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_SUPERVISOR, 5, false, false)) {
            throw new Exception("Acesso restrito a supervisores, coordenadores e gerentes.");
        }
    }

    private function isAdminPedagogico() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        return FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            || FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false);
    }

    private function isSupervisor() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        return FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_SUPERVISOR, 5, false, false);
    }

    private function getCodGrupoSupervisor($debug = false) {
        $obj = new AvaliacaoPES();
        return $obj->getCodGrupoSupervisor($debug);
    }

    public function carregarObjAvaliacao($fws_, $debug = false) {
        $obj = new AvaliacaoPES();
        if (isset($fws_['codAvaliacao']) && $fws_['codAvaliacao']) {
            $obj->setCodAvaliacao($fws_['codAvaliacao']);
            $obj->getAvaliacaoPESById($debug);
        }
        return $obj;
    }

    public function carregarListaSupervisoresParaSelect($fws_, $debug = false) {
        ob_clean();
        try {
            $obj = new AvaliacaoPES();
            $codGrupoSupervisor = $obj->getCodGrupoSupervisor($debug);
            if (!$codGrupoSupervisor) {
                echo json_encode(array('statusMessage' => '', 'supervisores' => array()));
                exit();
            }

            $supervisores = $obj->getListaSupervisores($codGrupoSupervisor, $debug);

            echo json_encode(array('statusMessage' => '1', 'supervisores' => $supervisores));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => '', 'supervisores' => array()));
        }
        exit();
    }

    public function carregarTabelaPES($fws_, $debug = false) {
        try {
            $codSupervisor = isset($fws_['codSupervisor']) ? intval($fws_['codSupervisor']) : 0;

            // Se nao e admin, forcar o supervisor logado
            if (!$this->isAdminPedagogico()) {
                $codSupervisor = intval($_SESSION['_cod_usuario']);
            }

            if (!$codSupervisor) {
                throw new Exception("Supervisor nao informado.");
            }

            $obj = new AvaliacaoPES();
            $agentes = $obj->getAgentesComStatusPorSupervisor($codSupervisor, $debug);

            $totalAgentes = count($agentes);
            $totalPreenchidos = 0;
            foreach ($agentes as $row) {
                if (!empty($row['cod_avaliacao'])) $totalPreenchidos++;
                $dataAval = !empty($row['data_avaliacao']) ? date('d/m/Y', strtotime($row['data_avaliacao'])) : '';
                $nome = htmlspecialchars(mb_convert_case($row['nome_inscrito'], MB_CASE_TITLE, 'UTF-8'));
                ?>
                <tr>
                    <td><?php echo $nome ?></td>
                    <td class="text-center"><?php echo intval($row['total_alunos']) ?></td>
                    <td class="text-center">
                        <?php if (!empty($row['cod_avaliacao'])): ?>
                            <span class="label label-success"><i class="fa fa-check"></i> Avaliado</span>
                            <br><small class="text-muted"><?php echo $dataAval ?></small>
                        <?php else: ?>
                            <span class="label label-warning"><i class="fa fa-clock-o"></i> Pendente</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if (!empty($row['cod_avaliacao'])): ?>
                            <button class="btn btn-xs btn-info ver-avaliacao-pes" data-cod-avaliacao="<?php echo $row['cod_avaliacao'] ?>" title="Visualizar/Editar">
                                <i class="fa fa-pencil"></i> Editar
                            </button>
                            <button class="btn btn-xs btn-danger excluir-avaliacao-pes" data-cod-avaliacao="<?php echo $row['cod_avaliacao'] ?>" title="Excluir">
                                <i class="fa fa-trash"></i>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-xs btn-success nova-avaliacao-pes" data-cod-agente="<?php echo $row['cod_agente'] ?>" data-nome="<?php echo $nome ?>" title="Avaliar">
                                <i class="fa fa-plus"></i> Avaliar
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="4">
                    Total de PES: <?php echo $totalAgentes ?> |
                    Avaliados: <?php echo $totalPreenchidos ?> |
                    Pendentes: <?php echo ($totalAgentes - $totalPreenchidos) ?>
                </td>
            </tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Nenhum PES vinculado.";
            }
            echo "</td></tr>";
        }
    }

    public function salvarAvaliacaoPES($fws_, $debug = false) {
        $ok = false;
        $obj = null;
        $ex = null;
        try {
            if (isset($fws_['novoCodAvaliacao']) && $fws_['novoCodAvaliacao'] == 0) {
                $fws_['codAvaliacao'] = NULL;
            }

            // Se nao e admin, forcar o supervisor logado
            if (!$this->isAdminPedagogico()) {
                $fws_['codSupervisor'] = $_SESSION['_cod_usuario'];
            }

            $obj = $this->carregarObjAvaliacao($fws_);
            $obj->setAll($fws_, true);
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : '',
            'codAvaliacao'  => $obj ? $obj->getCodAvaliacao() : ''
        ));
    }

    public function editarAvaliacaoPES($fws_, $debug = false) {
        $obj = $this->carregarObjAvaliacao($fws_, $debug);

        echo json_encode(array(
            'statusMessage'  => $obj->getCodAvaliacao() ? true : false,
            'codAvaliacao'   => $obj->getCodAvaliacao(),
            'codAgente'      => $obj->getCodAgente(),
            'codSupervisor'  => $obj->getCodSupervisor(),
            'dataAvaliacao'  => $obj->getDataAvaliacao(),
            'segueOrientacoes'        => $obj->getSegueOrientacoes(),
            'aberturaFeedback'        => $obj->getAberturaFeedback(),
            'comunicaDificuldades'    => $obj->getComunicaDificuldades(),
            'posturaProfissional'     => $obj->getPosturaProfissional(),
            'relacionamentoEquipe'    => $obj->getRelacionamentoEquipe(),
            'desempenhoAdequado'      => $obj->getDesempenhoAdequado(),
            'necessitaAcompanhamento' => $obj->getNecessitaAcompanhamento(),
            'observacoes'    => $obj->getObservacoes(),
            'dataInclusao'   => $obj->getDataInclusao()
        ));
        exit();
    }

    public function carregarEstatisticasPES($fws_, $debug = false) {
        ob_clean();
        try {
            $obj = new AvaliacaoPES();
            $rows = $obj->getEstatisticasGerais($debug);

            $totalAvaliacoes = count($rows);
            $agentesSet = array();
            $supervisoresSet = array();
            $respostasGerais = array('Sim' => 0, 'Nao' => 0, 'Parcialmente' => 0, 'NaoObservado' => 0);
            $colunas = array(
                'segue_orientacoes', 'abertura_feedback', 'comunica_dificuldades',
                'postura_profissional', 'relacionamento_equipe',
                'desempenho_adequado', 'necessita_acompanhamento'
            );

            $respostasPorPergunta = array();
            $porSupervisor = array();
            $ultimaPorAgente = array();

            foreach ($colunas as $col) {
                $respostasPorPergunta[$col] = array('Sim' => 0, 'Nao' => 0, 'Parcialmente' => 0, 'NaoObservado' => 0);
            }

            foreach ($rows as $row) {
                $agentesSet[$row['cod_agente']] = $row['nome_agente'];
                $codSup = $row['cod_supervisor'];
                $nomeSup = $row['nome_supervisor'] ?: 'Desconhecido';
                $supervisoresSet[$codSup] = $nomeSup;

                if (!isset($porSupervisor[$codSup])) {
                    $porSupervisor[$codSup] = array('nome' => $nomeSup, 'total' => 0);
                }
                $porSupervisor[$codSup]['total']++;

                // Guardar apenas a mais recente por agente (query ja vem ORDER BY DESC)
                if (!isset($ultimaPorAgente[$row['cod_agente']])) {
                    $ultimaPorAgente[$row['cod_agente']] = $row;
                }

                // Ler respostas das colunas
                foreach ($colunas as $col) {
                    $valor = isset($row[$col]) ? $row[$col] : '';
                    if (isset($respostasGerais[$valor])) {
                        $respostasGerais[$valor]++;
                        $respostasPorPergunta[$col][$valor]++;
                    }
                }
            }

            // Alertas: PES cuja ultima avaliacao indica necessidade de acompanhamento
            $alertas = array();
            foreach ($ultimaPorAgente as $codAg => $row) {
                if (isset($row['necessita_acompanhamento']) && $row['necessita_acompanhamento'] === 'Sim') {
                    $alertas[] = array(
                        'codAgente'      => $row['cod_agente'],
                        'nomeAgente'     => mb_convert_case($row['nome_agente'], MB_CASE_TITLE, 'UTF-8'),
                        'nomeSupervisor' => mb_convert_case($row['nome_supervisor'] ?: '', MB_CASE_TITLE, 'UTF-8'),
                        'dataAvaliacao'  => $row['data_avaliacao']
                    );
                }
            }

            $supArray = array_values($porSupervisor);
            foreach ($supArray as &$s) {
                $s['nome'] = mb_convert_case($s['nome'], MB_CASE_TITLE, 'UTF-8');
            }

            echo json_encode(array(
                'statusMessage'        => true,
                'totalAvaliacoes'      => $totalAvaliacoes,
                'totalAgentes'         => count($agentesSet),
                'totalSupervisores'    => count($supervisoresSet),
                'respostasGerais'      => $respostasGerais,
                'respostasPorPergunta' => $respostasPorPergunta,
                'porSupervisor'        => $supArray,
                'alertas'              => $alertas
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    public function carregarPainelComparativo($fws_, $debug = false) {
        ob_clean();
        try {
            $obj = new AvaliacaoPES();
            $rows = $obj->getPainelComparativo($debug);

            $colunas = array(
                'segue_orientacoes', 'abertura_feedback', 'comunica_dificuldades',
                'postura_profissional', 'relacionamento_equipe',
                'desempenho_adequado', 'necessita_acompanhamento'
            );

            // Agrupar por colegio > agente, pegar a mais recente por agente/colegio
            $porColegio = array();
            $ultimaPorAgenteColegio = array();

            foreach ($rows as $row) {
                $nomeColegio = $row['nome_colegio'] ?: 'Sem colegio';
                $codAgente = $row['cod_agente'];
                $chave = $codAgente . '_' . $nomeColegio;

                if (!isset($ultimaPorAgenteColegio[$chave])) {
                    $ultimaPorAgenteColegio[$chave] = $row;
                    $ultimaPorAgenteColegio[$chave]['_contagem'] = 1;
                    $ultimaPorAgenteColegio[$chave]['_somas'] = array();
                    foreach ($colunas as $col) {
                        $val = isset($row[$col]) ? $row[$col] : '';
                        $num = ($val === 'Sim') ? 3 : (($val === 'Parcialmente') ? 2 : (($val === 'Nao') ? 1 : null));
                        $ultimaPorAgenteColegio[$chave]['_somas'][$col] = $num !== null ? array($num) : array();
                    }
                } else {
                    $ultimaPorAgenteColegio[$chave]['_contagem']++;
                    foreach ($colunas as $col) {
                        $val = isset($row[$col]) ? $row[$col] : '';
                        $num = ($val === 'Sim') ? 3 : (($val === 'Parcialmente') ? 2 : (($val === 'Nao') ? 1 : null));
                        if ($num !== null) {
                            $ultimaPorAgenteColegio[$chave]['_somas'][$col][] = $num;
                        }
                    }
                }
            }

            // Montar resultado agrupado
            foreach ($ultimaPorAgenteColegio as $chave => $row) {
                $nomeColegio = $row['nome_colegio'] ?: 'Sem colegio';
                if (!isset($porColegio[$nomeColegio])) {
                    $porColegio[$nomeColegio] = array();
                }

                $medias = array();
                foreach ($colunas as $col) {
                    $valores = $row['_somas'][$col];
                    $medias[$col] = count($valores) > 0 ? round(array_sum($valores) / count($valores), 1) : 0;
                }

                $porColegio[$nomeColegio][] = array(
                    'codAgente'        => $row['cod_agente'],
                    'nomeAgente'       => mb_convert_case($row['nome_agente'], MB_CASE_TITLE, 'UTF-8'),
                    'nomeSupervisor'   => mb_convert_case($row['nome_supervisor'] ?: '', MB_CASE_TITLE, 'UTF-8'),
                    'totalAvaliacoes'  => $row['_contagem'],
                    'mediaSegue'       => $medias['segue_orientacoes'],
                    'mediaAbertura'    => $medias['abertura_feedback'],
                    'mediaComunica'    => $medias['comunica_dificuldades'],
                    'mediaPostura'     => $medias['postura_profissional'],
                    'mediaRelacionamento' => $medias['relacionamento_equipe'],
                    'mediaDesempenho'  => $medias['desempenho_adequado'],
                    'necessitaAcompanhamento' => $row['necessita_acompanhamento']
                );
            }

            echo json_encode(array(
                'statusMessage' => true,
                'porColegio' => $porColegio
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    public function excluirAvaliacaoPES($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        try {
            $obj = new AvaliacaoPES();
            $obj->setCodAvaliacao($fws_['codAvaliacao']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : '',
            'codAvaliacao'  => $fws_['codAvaliacao']
        ));
    }

}
