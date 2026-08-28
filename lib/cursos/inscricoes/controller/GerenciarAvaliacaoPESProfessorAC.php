<?php

class GerenciarAvaliacaoPESProfessorAC {

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
            $ciclo = isset($fws_['ciclo']) ? intval($fws_['ciclo']) : 1;

            if (!$this->isAdminPedagogico()) {
                $codSupervisor = intval($_SESSION['_cod_usuario']);
            }

            if (!$codSupervisor) {
                throw new Exception("Supervisor nao informado.");
            }

            $obj = new AvaliacaoPESProfessor();
            $agentes = $obj->getAgentesComAvaliacoesPorSupervisor($codSupervisor, $ciclo, $debug);

            foreach ($agentes as $row) {
                $nome = htmlspecialchars(mb_convert_case($row['nome_inscrito'], MB_CASE_TITLE, 'UTF-8'));
                $totalAval = intval($row['total_avaliacoes']);
                $professores = $row['professores_avaliaram'] ? htmlspecialchars($row['professores_avaliaram']) : '';
                ?>
                <tr data-cod-agente="<?php echo $row['cod_agente'] ?>" data-nome="<?php echo $nome ?>">
                    <td><?php echo $nome ?></td>
                    <td class="text-center">
                        <?php if ($totalAval > 0): ?>
                            <span class="label label-success"><?php echo $totalAval ?> avalia&ccedil;&atilde;o(s)</span>
                            <br><small class="text-muted"><?php echo $professores ?></small>
                        <?php else: ?>
                            <span class="label label-warning">Nenhuma</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-nowrap">
                        <button class="btn btn-xs btn-success btn-nova-aval-prof" data-cod-agente="<?php echo $row['cod_agente'] ?>" data-nome="<?php echo $nome ?>" title="Nova avalia&ccedil;&atilde;o">
                            <i class="fa fa-plus"></i> Avaliar
                        </button>
                        <?php if ($totalAval > 0): ?>
                            <button class="btn btn-xs btn-info btn-ver-avaliacoes" data-cod-agente="<?php echo $row['cod_agente'] ?>" data-nome="<?php echo $nome ?>" title="Ver avalia&ccedil;&otilde;es">
                                <i class="fa fa-list"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
            }

            if (empty($agentes)) {
                echo "<tr><td colspan='3'>Nenhum PES vinculado.</td></tr>";
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='3'>" . ($e->getMessage() ?: "Erro ao carregar.") . "</td></tr>";
        }
    }

    public function carregarAvaliacoesPorAgente($fws_, $debug = false) {
        ob_clean();
        try {
            $codAgente = isset($fws_['codAgente']) ? intval($fws_['codAgente']) : 0;
            $ciclo = isset($fws_['ciclo']) ? intval($fws_['ciclo']) : 1;

            if (!$codAgente) throw new Exception("PES nao informado.");

            $obj = new AvaliacaoPESProfessor();
            $avaliacoes = $obj->getAvaliacoesPorAgente($codAgente, $ciclo, $debug);

            echo json_encode(array(
                'statusMessage' => true,
                'avaliacoes' => $avaliacoes
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    public function editarAvaliacao($fws_, $debug = false) {
        ob_clean();
        try {
            $obj = new AvaliacaoPESProfessor();
            $obj->setCodAvaliacao($fws_['codAvaliacao']);
            $obj->getAvaliacaoById($debug);

            echo json_encode(array(
                'statusMessage'          => $obj->getCodAvaliacao() ? true : false,
                'codAvaliacao'           => $obj->getCodAvaliacao(),
                'codAgente'              => $obj->getCodAgente(),
                'codSupervisor'          => $obj->getCodSupervisor(),
                'nomeProfessor'          => $obj->getNomeProfessor(),
                'ciclo'                  => $obj->getCiclo(),
                'dataAvaliacao'          => $obj->getDataAvaliacao(),
                'integraAtividades'      => $obj->getIntegraAtividades(),
                'contribuiParticipacao'  => $obj->getContribuiParticipacao(),
                'favoreceAprendizagem'   => $obj->getFavoreceAprendizagem(),
                'reduzComportamentos'    => $obj->getReduzComportamentos(),
                'promoveAutonomia'       => $obj->getPromoveAutonomia(),
                'comunicacaoProfessor'   => $obj->getComunicacaoProfessor(),
                'segueOrientacoes'       => $obj->getSegueOrientacoes(),
                'contribuiAula'          => $obj->getContribuiAula(),
                'posturaProfissional'    => $obj->getPosturaProfissional(),
                'notaGeral'              => $obj->getNotaGeral(),
                'observacoes'            => $obj->getObservacoes()
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    public function salvarAvaliacao($fws_, $debug = false) {
        $ok = false;
        $obj = null;
        $ex = null;
        try {
            if (isset($fws_['novoCodAvaliacao']) && $fws_['novoCodAvaliacao'] == 0) {
                $fws_['codAvaliacao'] = NULL;
            }

            if (!$this->isAdminPedagogico()) {
                $fws_['codSupervisor'] = $_SESSION['_cod_usuario'];
            }

            $obj = new AvaliacaoPESProfessor();
            if (isset($fws_['codAvaliacao']) && $fws_['codAvaliacao']) {
                $obj->setCodAvaliacao($fws_['codAvaliacao']);
                $obj->getAvaliacaoById($debug);
            }
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

    public function excluirAvaliacao($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        try {
            $obj = new AvaliacaoPESProfessor();
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
            'message'       => $ex ? $ex->getMessage() : ''
        ));
    }

    public function carregarPainelComparativo($fws_, $debug = false) {
        ob_clean();
        try {
            $ciclo = isset($fws_['ciclo']) ? intval($fws_['ciclo']) : 1;

            $obj = new AvaliacaoPESProfessor();
            $dados = $obj->getPainelComparativo($ciclo, $debug);

            // Agrupar por colegio
            $porColegio = array();
            foreach ($dados as $row) {
                $nomeColegio = $row['nome_colegio'] ?: 'Sem col&eacute;gio';
                if (!isset($porColegio[$nomeColegio])) {
                    $porColegio[$nomeColegio] = array();
                }
                $porColegio[$nomeColegio][] = array(
                    'codAgente'      => $row['cod_agente'],
                    'nomeAgente'     => mb_convert_case($row['nome_agente'], MB_CASE_TITLE, 'UTF-8'),
                    'totalAvaliacoes'=> intval($row['total_avaliacoes']),
                    'mediaNota'      => round(floatval($row['media_nota']), 1),
                    'mediaIntegra'   => round(floatval($row['media_integra']), 1),
                    'mediaContribui' => round(floatval($row['media_contribui']), 1),
                    'mediaAprendizagem' => round(floatval($row['media_aprendizagem']), 1),
                    'mediaComportamentos' => round(floatval($row['media_comportamentos']), 1),
                    'mediaAutonomia' => round(floatval($row['media_autonomia']), 1),
                    'mediaComunicacao' => round(floatval($row['media_comunicacao']), 1),
                    'mediaOrientacoes' => round(floatval($row['media_orientacoes']), 1),
                    'mediaContribuiAula' => round(floatval($row['media_contribui_aula']), 1),
                    'mediaPostura'   => round(floatval($row['media_postura']), 1)
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

}
