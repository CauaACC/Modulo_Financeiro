<?php
class GerenciarRegistroPedagogicoAC {

    function __construct() {

    }

    public function carregarRegistroPedagogico($fws_, $debug = false) {
        $obj = new RegistroPedagogico();
        $obj->setCodRegistro($fws_['codRegistro']);
        $obj->getRegistroPedagogicoById($debug);
        return $obj;
    }

    public function editarRegistroPedagogico($fws_, $debug = false) {
        $ok = false;
        $obj = $this->carregarRegistroPedagogico($fws_,$debug);
        $ok =  $obj->getStatus()? true : false;
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $obj->getMessage(),
            'codRegistro'   => $obj->getCodRegistro(),
            'codAluno'    => $obj->getCodAluno(),
            'codAgente'     => $obj->getCodAgente(),
            'dataRegistro'  => $obj->getDataRegistro(),
            'condicaoInicio'=> $obj->getCondicaoInicio(),
            'participacao'  => $obj->getParticipacao(),
            'grauApoio'     => $obj->getGrauApoio(),
            'situacoes'             => $obj->getSituacoes(),
            'condicaoFinal'         => $obj->getCondicaoFinal(),
            'evolucaoPeriodo'       => $obj->getEvolucaoPeriodo(),
            'interesses'            => $obj->getInteresses(),
            'brincadeirasFavoritas'  => $obj->getBrincadeirasFavoritas(),
            'comportamentoAlimentar' => $obj->getComportamentoAlimentar(),
            'observacao'            => $obj->getObservacao(),
            'presenca'              => $obj->getPresenca()
        ));
        exit();
    }

    public function marcarPresenca($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        $obj = null;
        try {
            $presenca = isset($fws_['presenca']) ? $fws_['presenca'] : '';
            if (!in_array($presenca, array('PRESENTE', 'FALTA', 'FALTA_JUSTIFICADA'))) {
                throw new Exception("Valor de presenca invalido.");
            }

            $obj = new RegistroPedagogico();
            $obj->setCodAluno($fws_['codAluno']);
            $obj->setCodAgente($fws_['codAgente']);
            $obj->setDataRegistro($fws_['dataRegistro']);

            // Verificar se ja existe registro para este aluno nesta data
            $codExistente = $obj->getRegistroByAlunoData($debug);
            if ($codExistente) {
                $obj->setCodRegistro($codExistente);
                $obj->getRegistroPedagogicoById($debug);
            }

            $obj->setPresenca($presenca);
            $obj->salvarPresenca($debug);
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
            'codRegistro'   => $obj ? $obj->getCodRegistro() : ''
        ));
    }

    public function salvarRegistroPedagogico($fws_, $debug = false) {
        try {
            $rascunho = isset($fws_['rascunho']) && $fws_['rascunho'] == '1';
            if ($fws_['novoCodRegistro'] == 0) {
                $fws_['codRegistro'] = NULL;
            }
            // dd($fws_);
            $isForm = true;
            $obj = $this->carregarRegistroPedagogico($fws_);
            $obj->setAll($fws_, $isForm);
            $obj->salvar($debug, $rascunho);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $e->getMessage(),
            'codRegistro'   => $obj->getCodRegistro()
        ));
    }

    public function excluirRegistroPedagogico($fws_, $debug = false) {
        try {
            $obj = new RegistroPedagogico();
            $obj->setCodRegistro($fws_['codRegistro']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $e->getMessage(),
            'codRegistro'   => $fws_['codRegistro']
        ));
    }

    public function carregarEstatisticasGerais($fws_, $debug = false) {
        try {
            $obj = new RegistroPedagogico();
            $rows = $obj->getEstatisticasGerais($debug);

            $registros = array();
            foreach ($rows as $row) {
                $registros[] = array(
                    'codAluno'        => $row['cod_aluno'],
                    'codAgente'       => $row['cod_agente'],
                    'codigoExibicao'  => $row['codigo_exibicao'],
                    'nomeAgente'      => $row['nome_agente'],
                    'nomeColegio'     => $row['nome_colegio'],
                    'dataRegistro'    => $row['data_registro'],
                    'condicaoInicio'  => $row['condicao_inicio'],
                    'participacao'    => $row['participacao'],
                    'grauApoio'       => $row['grau_apoio'],
                    'situacoes'       => $row['situacoes'],
                    'condicaoFinal'   => $row['condicao_final'],
                    'evolucaoPeriodo' => $row['evolucao_periodo']
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

    public function carregarTabelaRegistroPedagogicos($fws_, $debug = false) {
        try {
            $obj = new RegistroPedagogico();
            $obj->setCodAluno($fws_['codAluno']);

            $lista = $obj->getListaRegistroPedagogicosByCodAluno($debug);
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo $objL->getCodRegistro() ?>" data-presenca="<?php echo htmlspecialchars($objL->getPresenca()) ?>">
                    <td>
                        <img class="editar"
                             src="<?php echo WB_PATH ?>/resources/image/alterar.gif" alt="Editar Registro Pedagogico: <?php echo $objL->getCodRegistro() ?>" border="0">
                        <img class="excluir"
                             src="<?php echo WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Registro Pedagogico: <?php echo $objL->getCodRegistro() ?>" border="0">
                    </td>
                    <?php
                    echo "<td>" . formatDateView($objL->getDataRegistro()) . "</td>";
                    echo "<td>{$objL->getCondicaoInicio()}</td>";
                    echo "<td>{$objL->getParticipacao()}</td>";
                    echo "<td>{$objL->getGrauApoio()}</td>";
                    echo "<td>{$objL->getCondicaoFinal()}</td>";
                    echo "<td>{$objL->getEvolucaoPeriodo()}</td>";
                    echo "<td>" . mb_strimwidth($objL->getObservacao(), 0, 50, '...') . "</td>";
                echo "</tr>";
            }
            ?>
                <br>Total de Registros Pedagogicos cadastrados: <?php echo  $ii ?>.
            <?php
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=7>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }

    public function carregarPresencaDia($fws_, $debug = false) {
        ob_clean();
        try {
            $data = isset($fws_['data']) ? $fws_['data'] : date('Y-m-d');

            // Verificar permissao
            $cod_usuario = $_SESSION['_cod_usuario'];
            $isAdmin = FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
                    || FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false);
            $isSupervisor = FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_SUPERVISOR, 5, false, false);

            if (!$isAdmin && !$isSupervisor) {
                throw new Exception("Acesso restrito.");
            }

            $codSupervisor = null;
            if (!$isAdmin && $isSupervisor) {
                $codSupervisor = $cod_usuario;
            }

            $obj = new RegistroPedagogico();
            $rows = $obj->getPresencaDia($data, $codSupervisor, $debug);

            $totalAlunos = count($rows);
            $presentes = 0;
            $faltas = 0;
            $semRegistro = 0;
            $registros = array();

            foreach ($rows as $row) {
                $presenca = isset($row['presenca']) ? $row['presenca'] : '';
                if ($presenca === 'PRESENTE') $presentes++;
                elseif ($presenca === 'FALTA' || $presenca === 'FALTA_JUSTIFICADA') $faltas++;
                else $semRegistro++;

                $registros[] = array(
                    'codAluno'       => $row['cod_aluno'],
                    'codigoExibicao' => $row['codigo_exibicao'],
                    'serieTurma'     => $row['serie_turma'],
                    'turno'          => $row['turno'],
                    'nomeColegio'    => $row['nome_colegio'],
                    'codAgente'      => $row['cod_agente'],
                    'nomeAgente'     => $row['nome_agente'] ? mb_convert_case($row['nome_agente'], MB_CASE_TITLE, 'UTF-8') : '',
                    'presenca'       => $presenca,
                    'condicaoInicio' => isset($row['condicao_inicio']) ? $row['condicao_inicio'] : '',
                    'condicaoFinal'  => isset($row['condicao_final']) ? $row['condicao_final'] : ''
                );
            }

            echo json_encode(array(
                'statusMessage'  => true,
                'data'           => $data,
                'totalAlunos'    => $totalAlunos,
                'presentes'      => $presentes,
                'faltas'         => $faltas,
                'semRegistro'    => $semRegistro,
                'registros'      => $registros
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }
}
