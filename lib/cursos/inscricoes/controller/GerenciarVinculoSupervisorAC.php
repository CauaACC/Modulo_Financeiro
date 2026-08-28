<?php

class GerenciarVinculoSupervisorAC {

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)) {
            throw new Exception("Acesso restrito a coordenadores e gerentes.");
        }
    }

    private function getCodGrupoSupervisor($debug = false) {
        ConexaoDB::getInstance();
        $conex = ConexaoDB::getConexaoDB();

        $sql = sprintf("SELECT dgs.cod_grupo FROM sis_direitos_grupos_sistemas dgs
            WHERE dgs.cod_sistema = %s AND dgs.cod_evento = %s LIMIT 1",
            enumSistema::SIS_PEDAGOGICO_SUPERVISOR, COD_EVENTO);

        $conex->debug = $debug;
        $rs = $conex->Execute($sql);

        if ($rs && !$rs->EOF) {
            return intval($rs->fields['cod_grupo']);
        }
        return false;
    }

    public function carregarListaSupervisores($fws_, $debug = false) {
        ob_clean();
        try {
            $codGrupoSupervisor = $this->getCodGrupoSupervisor($debug);
            if (!$codGrupoSupervisor) {
                echo json_encode(array('statusMessage' => '', 'message' => 'Grupo de supervisor n&atilde;o configurado.', 'supervisores' => array()));
                exit();
            }

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            $sql = sprintf("SELECT su.cod_usuario, su.login,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) as nome,
                    (SELECT COUNT(*) FROM acad_lig_supervisor_agente vsa
                     WHERE vsa.cod_supervisor = su.cod_usuario AND vsa.cod_evento = %d AND vsa.status = 'ATIVADA') as total_agentes,
                    (SELECT COUNT(*) FROM acad_lig_supervisor_colegio vsc
                     WHERE vsc.cod_supervisor = su.cod_usuario AND vsc.cod_evento = %d AND vsc.status = 'ATIVADA') as total_colegios
                FROM sis_usuarios su
                INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario AND lue.cod_evento = %d
                INNER JOIN sis_ligacao_usuarios_grupos lug ON su.cod_usuario = lug.cod_usuario AND lug.cod_evento = %d AND lug.cod_grupo = %d
                LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                WHERE su.status = 'ATIVADA'
                ORDER BY nome ASC",
                COD_EVENTO, COD_EVENTO, COD_EVENTO, COD_EVENTO, $codGrupoSupervisor);

            $conex->debug = $debug;
            $rs = $conex->Execute($sql);

            $supervisores = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $supervisores[] = array(
                        'codUsuario'    => intval($row['cod_usuario']),
                        'login'         => $row['login'],
                        'nome'          => $row['nome'],
                        'totalAgentes'  => intval($row['total_agentes']),
                        'totalColegios' => intval($row['total_colegios'])
                    );
                }
            }

            echo json_encode(array(
                'statusMessage' => '1',
                'supervisores'  => $supervisores
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message'       => $e->getMessage(),
                'supervisores'  => array()
            ));
        }
        exit();
    }

    public function carregarAgentesVinculados($fws_, $debug = false) {
        try {
            $codSupervisor = intval($fws_['codSupervisor']);
            if (!$codSupervisor) {
                throw new Exception("Supervisor n&atilde;o informado.");
            }

            $obj = new VinculoSupervisorAgente();
            $obj->setCodSupervisor($codSupervisor);
            $lista = $obj->getListaVinculosBySupervisor($debug);

            if (empty($lista)) {
                echo "<tr><td colspan='5' class='text-center text-muted'>Nenhum agente (PES) vinculado.</td></tr>";
                return;
            }

            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr data-cod-vinculo="<?php echo $objL->getCodVinculo() ?>" data-cod-agente="<?php echo $objL->getCodAgente() ?>">
                    <td><?php echo htmlspecialchars(mb_convert_case($objL->getNomeInscrito(), MB_CASE_TITLE, 'UTF-8')) ?></td>
                    <td><?php echo htmlspecialchars($objL->getCpf()) ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($objL->getStatusProcesso()) ?></td>
                    <td class="text-center"><?php echo intval($objL->getTotalAlunos()) ?></td>
                    <td class="text-center">
                        <button class="btn btn-xs btn-acao-excluir btn-desassociar-pes" title="Desvincular PES">
                            <i class="fa fa-unlink"></i> Desvincular
                        </button>
                    </td>
                </tr>
                <?php
            }
            echo "<tr><td colspan='5'>Total de agentes vinculados: $ii</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='5' class='text-center text-muted'>" . ($e->getMessage() ? $e->getMessage() : 'Nenhum agente vinculado.') . "</td></tr>";
        }
    }

    public function carregarAgentesDisponiveis($fws_, $debug = false) {
        ob_clean();
        try {
            $codSupervisor = intval($fws_['codSupervisor']);
            if (!$codSupervisor) {
                throw new Exception("Supervisor n&atilde;o informado.");
            }

            $agentes = VinculoAgenteAluno::listarAgentesPedagogicosComAlunos($debug);

            // Excluir os que ja estao vinculados a este supervisor
            $vinculados = VinculoSupervisorAgente::listarCodAgentesDeSupervisor($codSupervisor, $debug);

            $disponiveis = array();
            foreach ($agentes as $ag) {
                if (!in_array($ag['codInscrito'], $vinculados)) {
                    $disponiveis[] = $ag;
                }
            }

            echo json_encode(array(
                'statusMessage' => '1',
                'agentes'       => $disponiveis
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message'       => $e->getMessage(),
                'agentes'       => array()
            ));
        }
        exit();
    }

    public function associarAgente($fws_, $debug = false) {
        ob_clean();
        $ok = false;
        $ex = null;
        try {
            $obj = new VinculoSupervisorAgente();
            $obj->setCodSupervisor(intval($fws_['codSupervisor']));
            $obj->setCodAgente(intval($fws_['codAgente']));
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $ex ? $ex->getMessage() : ''
        ));
        exit();
    }

    public function desassociarAgente($fws_, $debug = false) {
        ob_clean();
        $ok = false;
        $ex = null;
        try {
            $obj = new VinculoSupervisorAgente();
            $obj->setCodVinculo(intval($fws_['codVinculo']));
            $obj->excluir($debug);
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $ex ? $ex->getMessage() : ''
        ));
        exit();
    }

    // ---- Colégios ----

    public function carregarColegiosVinculados($fws_, $debug = false) {
        try {
            $codSupervisor = intval($fws_['codSupervisor']);
            if (!$codSupervisor) {
                throw new Exception("Supervisor n&atilde;o informado.");
            }

            $obj = new VinculoSupervisorColegio();
            $obj->setCodSupervisor($codSupervisor);
            $lista = $obj->getListaVinculosBySupervisor($debug);

            if (empty($lista)) {
                echo "<tr><td colspan='4' class='text-center text-muted'>Nenhum col&eacute;gio vinculado.</td></tr>";
                return;
            }

            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr data-cod-vinculo="<?php echo $objL->getCodVinculo() ?>" data-cod-colegio="<?php echo $objL->getCodColegio() ?>">
                    <td><?php echo htmlspecialchars($objL->getNomeColegio()) ?></td>
                    <td class="text-center"><?php echo intval($objL->getTotalAlunos()) ?></td>
                    <td class="text-center">
                        <button class="btn btn-xs btn-acao-excluir btn-desassociar-colegio" title="Desvincular col&eacute;gio">
                            <i class="fa fa-unlink"></i> Desvincular
                        </button>
                    </td>
                </tr>
                <?php
            }
            echo "<tr><td colspan='4'>Total de col&eacute;gios vinculados: $ii</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='4' class='text-center text-muted'>" . ($e->getMessage() ? $e->getMessage() : 'Nenhum col&eacute;gio vinculado.') . "</td></tr>";
        }
    }

    public function carregarColegiosDisponiveis($fws_, $debug = false) {
        ob_clean();
        try {
            $codSupervisor = intval($fws_['codSupervisor']);
            if (!$codSupervisor) {
                throw new Exception("Supervisor n&atilde;o informado.");
            }

            $objColegio = new Colegio();
            $objColegio->getListaColegiosByIdEvento($debug);
            $todos = $objColegio->getListaColegios();

            $vinculados = VinculoSupervisorColegio::listarCodColegiosDeSupervisor($codSupervisor, $debug);

            $contagens = VinculoSupervisorColegio::listarContagensPorColegio($debug);

            $disponiveis = array();
            if ($todos) {
                foreach ($todos as $c) {
                    if (!in_array($c->getCodColegio(), $vinculados)) {
                        $cod = $c->getCodColegio();
                        $disponiveis[] = array(
                            'codColegio'  => $cod,
                            'nome'        => $c->getNome(),
                            'totalAlunos' => isset($contagens[$cod]) ? $contagens[$cod]['alunos'] : 0,
                            'totalPes'    => isset($contagens[$cod]) ? $contagens[$cod]['pes'] : 0
                        );
                    }
                }
            }

            echo json_encode(array(
                'statusMessage' => '1',
                'colegios'      => $disponiveis
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message'       => $e->getMessage(),
                'colegios'      => array()
            ));
        }
        exit();
    }

    public function associarColegio($fws_, $debug = false) {
        ob_clean();
        $ok = false;
        $ex = null;
        try {
            $obj = new VinculoSupervisorColegio();
            $obj->setCodSupervisor(intval($fws_['codSupervisor']));
            $obj->setCodColegio($fws_['codColegio']);
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $ex ? $ex->getMessage() : ''
        ));
        exit();
    }

    public function desassociarColegio($fws_, $debug = false) {
        ob_clean();
        $ok = false;
        $ex = null;
        try {
            $obj = new VinculoSupervisorColegio();
            $obj->setCodVinculo(intval($fws_['codVinculo']));
            $obj->excluir($debug);
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $ex ? $ex->getMessage() : ''
        ));
        exit();
    }

}
