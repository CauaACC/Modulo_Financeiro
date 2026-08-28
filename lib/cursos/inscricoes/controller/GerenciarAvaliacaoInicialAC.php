<?php

class GerenciarAvaliacaoInicialAC {

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)) {
            throw new Exception("Acesso restrito a coordenadores e gerentes.");
        }
    }

    public function carregarObjAvaliacao($fws_, $debug = false) {
        $obj = new AvaliacaoInicial();
        if (isset($fws_['codAvaliacao']) && $fws_['codAvaliacao']) {
            $obj->setCodAvaliacao($fws_['codAvaliacao']);
            $obj->getAvaliacaoInicialById($debug);
        }
        return $obj;
    }

    public function salvarAvaliacao($fws_, $debug = false) {
        $ok = false;
        $obj = null;
        $ex = null;
        try {
            if (isset($fws_['novoCodAvaliacao']) && $fws_['novoCodAvaliacao'] == 0) {
                $fws_['codAvaliacao'] = NULL;
            }

            // Converter arrays de habilidades e comportamentos para JSON
            if (isset($fws_['habilidades']) && is_array($fws_['habilidades'])) {
                $fws_['habilidades'] = json_encode($fws_['habilidades'], JSON_UNESCAPED_UNICODE);
            }
            if (isset($fws_['comportamentosDificeis']) && is_array($fws_['comportamentosDificeis'])) {
                $fws_['comportamentosDificeis'] = json_encode($fws_['comportamentosDificeis'], JSON_UNESCAPED_UNICODE);
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

    public function editarAvaliacao($fws_, $debug = false) {
        $obj = $this->carregarObjAvaliacao($fws_, $debug);

        $habilidades = $obj->getHabilidades();
        if ($habilidades && is_string($habilidades)) {
            $habilidades = json_decode($habilidades, true);
        }
        $comportamentos = $obj->getComportamentosDificeis();
        if ($comportamentos && is_string($comportamentos)) {
            $comportamentos = json_decode($comportamentos, true);
        }

        echo json_encode(array(
            'statusMessage'          => $obj->getCodAvaliacao() ? true : false,
            'codAvaliacao'           => $obj->getCodAvaliacao(),
            'codAluno'               => $obj->getCodAluno(),
            'codAgente'              => $obj->getCodAgente(),
            'tipoAvaliacao'          => $obj->getTipoAvaliacao(),
            'dataAvaliacao'          => $obj->getDataAvaliacao(),
            'nivelSuporte'           => $obj->getNivelSuporte(),
            'fazTerapia'             => $obj->getFazTerapia(),
            'terapiaLocal'           => $obj->getTerapiaLocal(),
            'terapiaTipos'           => $obj->getTerapiaTipos(),
            'demandasEscolares'      => $obj->getDemandasEscolares(),
            'habilidades'            => $habilidades,
            'comportamentosDificeis' => $comportamentos,
            'comportamentosOutros'   => $obj->getComportamentosOutros(),
            'dataInclusao'           => $obj->getDataInclusao()
        ));
        exit();
    }

    public function excluirAvaliacao($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        try {
            $obj = new AvaliacaoInicial();
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

    public function carregarListaProgramacoesParaSelect($fws_, $debug = false) {
        try {
            $obj = new ProgramacaoAvaliacao();
            $lista = $obj->getListaProgramacoes($debug);
            $result = array();
            $hoje = date('Y-m-d');
            foreach ($lista as $objP) {
                $situacao = 'Aguardando';
                if ($hoje >= $objP->getDataLiberacao() && $hoje <= $objP->getDataFechamento()) {
                    $situacao = 'Aberta';
                } elseif ($hoje > $objP->getDataFechamento()) {
                    $situacao = 'Encerrada';
                }
                $result[] = array(
                    'codProgramacao' => $objP->getCodProgramacao(),
                    'descricao'      => $objP->getDescricao(),
                    'tipoAvaliacao'  => $objP->getTipoAvaliacao(),
                    'dataLiberacao'  => $objP->getDataLiberacao(),
                    'dataFechamento' => $objP->getDataFechamento(),
                    'situacao'       => $situacao
                );
            }
            echo json_encode(array('statusMessage' => true, 'programacoes' => $result));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'programacoes' => array()));
        }
    }

    public function carregarTabelaAlunosPorProgramacao($fws_, $debug = false) {
        try {
            $codProgramacao = isset($fws_['codProgramacao']) ? $fws_['codProgramacao'] : '';
            if (!$codProgramacao) {
                throw new Exception("Programacao nao informada.");
            }

            $objProg = new ProgramacaoAvaliacao();
            $objProg->setCodProgramacao($codProgramacao);
            $objProg->getProgramacaoAvaliacaoById($debug);

            $obj = new AvaliacaoInicial();
            $alunos = $obj->getAlunosComStatusPorProgramacao(
                $objProg->getTipoAvaliacao(),
                $objProg->getDataLiberacao(),
                $objProg->getDataFechamento(),
                $debug
            );

            $totalAlunos = count($alunos);
            $totalPreenchidos = 0;
            foreach ($alunos as $row) {
                if (!empty($row['cod_avaliacao'])) $totalPreenchidos++;
                $dataAval = !empty($row['data_avaliacao']) ? date('d/m/Y', strtotime($row['data_avaliacao'])) : '';
                $dataPreench = !empty($row['data_preenchimento']) ? date('d/m/Y H:i', strtotime($row['data_preenchimento'])) : '';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['codigo_exibicao']) ?></td>
                    <td><?php echo htmlspecialchars($row['iniciais']) ?></td>
                    <td><?php echo htmlspecialchars($row['serie_turma']) ?></td>
                    <td><?php echo htmlspecialchars($row['turno']) ?></td>
                    <td><?php echo htmlspecialchars($row['nome_colegio']) ?></td>
                    <td class="text-center">
                        <?php if (!empty($row['cod_avaliacao'])): ?>
                            <span class="label label-success"><i class="fa fa-check"></i> Preenchida</span>
                            <br><small class="text-muted"><?php echo $dataAval ?></small>
                        <?php else: ?>
                            <span class="label label-warning"><i class="fa fa-clock-o"></i> Pendente</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if (!empty($row['cod_avaliacao'])): ?>
                            <button class="btn btn-xs btn-info ver-avaliacao-prog" data-cod-avaliacao="<?php echo $row['cod_avaliacao'] ?>" title="Visualizar">
                                <i class="fa fa-eye"></i>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-xs btn-success nova-avaliacao-prog" data-cod-aluno="<?php echo $row['cod_aluno'] ?>" title="Nova Avalia&ccedil;&atilde;o">
                                <i class="fa fa-plus"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="7">
                    Total de alunos: <?php echo $totalAlunos ?> |
                    Preenchidas: <?php echo $totalPreenchidos ?> |
                    Pendentes: <?php echo ($totalAlunos - $totalPreenchidos) ?>
                </td>
            </tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='7'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Nenhum aluno encontrado.";
            }
            echo "</td></tr>";
        }
    }

    public function carregarTabelaAvaliacoes($fws_, $debug = false) {
        try {
            $obj = new AvaliacaoInicial();
            $obj->setCodAluno($fws_['codAluno']);

            $lista = $obj->getListaAvaliacoesByCodAluno($debug);
            $ii = 0;
            foreach ($lista as $objA) {
                $ii++;
                $tipo = htmlspecialchars($objA->getTipoAvaliacao());
                $nivelSuporte = htmlspecialchars($objA->getNivelSuporte());
                $fazTerapia = $objA->getFazTerapia() == 'Sim' ? 'Sim' : 'Nao';
                $dataAval = $objA->getDataAvaliacao() ? date('d/m/Y', strtotime($objA->getDataAvaliacao())) : '-';
                ?>
                <tr id="<?php echo $objA->getCodAvaliacao() ?>">
                    <td><?php echo $tipo ?></td>
                    <td><?php echo $dataAval ?></td>
                    <td><?php echo $nivelSuporte ?></td>
                    <td><?php echo $fazTerapia ?></td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($objA->getDemandasEscolares(), 0, 80, '...')) ?></td>
                    <td class="text-nowrap">
                        <button class="btn btn-xs btn-acao-editar editar-avaliacao" title="Editar">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-acao-excluir excluir-avaliacao" title="Excluir">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr><td colspan="6">Total de avalia&ccedil;&otilde;es cadastradas: <?php echo $ii ?>.</td></tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='5'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Nenhuma avaliacao cadastrada.";
            }
            echo "</td></tr>";
        }
    }

}
