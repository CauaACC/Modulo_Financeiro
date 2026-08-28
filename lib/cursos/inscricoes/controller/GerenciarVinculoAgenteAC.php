<?php

class GerenciarVinculoAgenteAC {

    function __construct() {

    }

    private function isSupervisor() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        return FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_SUPERVISOR, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false);
    }

    private function isAdminPedagogico() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        return FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            || FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false);
    }

    private function getFiltroSupervisor() {
        if ($this->isSupervisor()) {
            $codUsuario = addslashes($_SESSION['_cod_usuario']);
            return sprintf("AND (a.id_inclusao = '%s'
                OR a.cod_aluno IN (
                    SELECT vaa2.cod_aluno FROM acad_lig_agente_aluno vaa2
                    INNER JOIN acad_lig_supervisor_agente vsa2 ON vsa2.cod_agente = vaa2.cod_agente AND vsa2.cod_evento = vaa2.cod_evento
                    WHERE vsa2.cod_evento = '%s' AND vsa2.cod_supervisor = '%s' AND vsa2.status = 'ATIVADA' AND vaa2.status = 'ATIVADA'
                )
                OR a.cod_colegio IN (
                    SELECT vsc2.cod_colegio FROM acad_lig_supervisor_colegio vsc2
                    WHERE vsc2.cod_evento = '%s' AND vsc2.cod_supervisor = '%s' AND vsc2.status = 'ATIVADA'
                )
            )", $codUsuario, COD_EVENTO, $codUsuario, COD_EVENTO, $codUsuario);
        }
        return '';
    }

    public function associarAluno($fws_, $debug = false) {
        $ok = false;
        $obj = null;
        $ex = null;
        try {
            $obj = new VinculoAgenteAluno();
            $obj->setCodAgente($fws_['codAgente']);
            $obj->setCodAluno($fws_['codAluno']);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : '',
            'codVinculo'    => $obj ? $obj->getCodVinculo() : ''
        ));
    }

    public function cadastrarEAssociarAluno($fws_, $debug = false) {
        $ok = false;
        $objAluno = null;
        $objVinculo = null;
        $ex = null;
        try {
            // 1. Verificar limite ANTES de cadastrar o aluno
            $objVerifica = new VinculoAgenteAluno();
            $objVerifica->setCodAgente($fws_['codAgente']);
            $objVerifica->validarLimitePreCadastro($fws_['turno'], $debug);

            // 2. Cadastrar o aluno
            $objAluno = new AlunoPublico();
            $objAluno->setAll($fws_, true);
            $objAluno->salvar($debug);
        } catch (OkException $e) {
            // Aluno salvo, agora associar
            try {
                $objVinculo = new VinculoAgenteAluno();
                $objVinculo->setCodAgente($fws_['codAgente']);
                $objVinculo->setCodAluno($objAluno->getCodAluno());
                $objVinculo->salvar($debug);
            } catch (OkException $e2) {
                $ok = true;
                $ex = $e2;
            } catch (Exception $e2) {
                $ok = false;
                $ex = $e2;
            }
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : '',
            'codAluno'    => $objAluno ? $objAluno->getCodAluno() : '',
            'codVinculo'    => $objVinculo ? $objVinculo->getCodVinculo() : ''
        ));
    }

    public function desassociarAluno($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        try {
            $obj = new VinculoAgenteAluno();
            $obj->setCodVinculo($fws_['codVinculo']);
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
            'codVinculo'    => $fws_['codVinculo']
        ));
    }

    public function carregarAlunosVinculados($fws_, $debug = false) {
        try {
            $obj = new VinculoAgenteAluno();
            $obj->setCodAgente($fws_['codAgente']);
            $lista = $obj->getListaVinculosByAgente($debug);
            $isAdmin = $this->isAdminPedagogico();
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo $objL->getCodVinculo() ?>" data-cod-aluno="<?php echo $objL->getCodAluno() ?>">
                    <td><?php echo htmlspecialchars($objL->getCodigoExibicao()) ?></td>
                    <td><?php echo htmlspecialchars($objL->getNomeColegio() ? $objL->getNomeColegio() : '') ?></td>
                    <td><?php echo htmlspecialchars($objL->getNivel()) ?></td>
                    <td><?php echo htmlspecialchars($objL->getTurno()) ?></td>
                    <td class="text-nowrap">
                        <button class="btn btn-xs btn-acao-editar btn-editar-vinculo" title="Editar aluno">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-acao-excluir btn-desassociar" title="Desassociar">
                            <i class="fa fa-unlink"> Desvincular</i>
                        </button>
                        <a class="btn btn-xs btn-acao-info btn-registros" title="Registros Pedag&oacute;gicos"
                           href="index.php?content=view/admin/ConsultarRegistrosPedagogicosAluno.php&codAluno=<?php echo $objL->getCodAluno() ?>">
                            <i class="fa fa-clipboard"> Registros</i>
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr><td colspan="5">Total de alunos vinculados: <?php echo $ii ?>.</td></tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='5'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Nenhum aluno vinculado.";
            }
            echo "</td></tr>";
        }
    }

    public function carregarListaAgentes($fws_, $debug = false) {
        try {
            $agentes = VinculoAgenteAluno::listarAgentesPedagogicosComAlunos($debug);

            echo json_encode(array(
                'statusMessage' => true,
                'agentes'       => $agentes
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage(),
                'agentes'       => array()
            ));
        }
    }

    public function filtrarAgentesPorColegio($fws_, $debug = false) {
        try {
            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            $codColegio = $fws_['codColegio'];

            $sql = sprintf("SELECT DISTINCT v.cod_agente
                FROM acad_lig_agente_aluno v
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = v.cod_aluno AND a.cod_evento = v.cod_evento
                WHERE v.cod_evento = '%s' AND v.status = 'ATIVADA'
                AND a.cod_colegio = '%s'",
                COD_EVENTO, addslashes($codColegio));

            $conex->debug = $debug;
            $rs = $conex->Execute($sql);

            $codAgentes = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $codAgentes[] = $row['cod_agente'];
                }
            }

            echo json_encode(array(
                'statusMessage' => true,
                'codAgentes'    => $codAgentes
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage(),
                'codAgentes'    => array()
            ));
        }
    }

    public function carregarRegistrosPedagogicosAluno($fws_, $debug = false) {
        try {
            $obj = new RegistroPedagogico();
            $obj->setCodAluno($fws_['codAluno']);
            $lista = $obj->getListaRegistroPedagogicosByCodAluno($debug);

            $registros = array();
            foreach ($lista as $objL) {
                $registros[] = array(
                    'codRegistro'    => $objL->getCodRegistro(),
                    'dataRegistro'   => $objL->getDataRegistro(),
                    'condicaoInicio' => $objL->getCondicaoInicio(),
                    'participacao'   => $objL->getParticipacao(),
                    'grauApoio'      => $objL->getGrauApoio(),
                    'situacoes'      => $objL->getSituacoes(),
                    'condicaoFinal'          => $objL->getCondicaoFinal(),
                    'evolucaoPeriodo'        => $objL->getEvolucaoPeriodo(),
                    'interesses'             => $objL->getInteresses(),
                    'brincadeirasFavoritas'   => $objL->getBrincadeirasFavoritas(),
                    'comportamentoAlimentar'  => $objL->getComportamentoAlimentar(),
                    'observacao'             => $objL->getObservacao()
                );
            }

            echo json_encode(array(
                'statusMessage' => true,
                'registros'     => $registros
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage(),
                'registros'     => array()
            ));
        }
    }

    public function carregarMapaAgenteAluno($fws_, $debug = false) {
        try {
            $obj = new VinculoAgenteAluno();
            $mapa = $obj->getMapaAgenteAluno($debug);
            echo json_encode(array('statusMessage' => true, 'vinculos' => $mapa));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'vinculos' => array()));
        }
    }

    public function carregarAlunosDisponiveis($fws_, $debug = false) {
        try {
            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            // Alunos do evento que NAO estao vinculados ao agente selecionado
            $sql = sprintf("SELECT a.cod_aluno, a.codigo_exibicao, a.cod_colegio, a.nivel, a.turno, c.nome AS nome_colegio
                FROM acad_aluno_contexto a
                LEFT JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                WHERE a.cod_evento = '%s' AND a.status = 'ATIVADA'
                AND a.cod_aluno NOT IN (
                    SELECT v.cod_aluno FROM acad_lig_agente_aluno v
                    WHERE v.cod_evento = '%s' AND v.cod_agente = '%s' AND v.status = 'ATIVADA'
                )
                %s
                ORDER BY a.codigo_exibicao ASC",
                COD_EVENTO, COD_EVENTO, $fws_['codAgente'],
                $this->getFiltroSupervisor());

            $rs = $conex->Execute($sql);

            $alunos = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $alunos[] = array(
                        'codAluno'     => $row['cod_aluno'],
                        'codigoExibicao' => $row['codigo_exibicao'],
                        'codColegio'     => $row['cod_colegio'],
                        'nomeColegio'    => $row['nome_colegio'] ? $row['nome_colegio'] : '',
                        'nivel'          => $row['nivel'],
                        'turno'          => $row['turno']
                    );
                }
            }

            echo json_encode(array(
                'statusMessage' => true,
                'alunos'        => $alunos
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage(),
                'alunos'        => array()
            ));
        }
    }

}
