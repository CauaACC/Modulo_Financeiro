<?php

class GerenciarAlunoAC {

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

    public function carregarObjAluno($fws_, $debug = false) {
        $obj = new AlunoPublico();
        if (isset($fws_['codAluno']) && $fws_['codAluno']) {
            $obj->setCodAluno($fws_['codAluno']);
            $obj->getAlunoPublicoById($debug);
        }
        return $obj;
    }

    public function salvarAluno($fws_, $debug = false) {
        $ok = false;
        $obj = null;
        $ex = null;
        try {
            if (isset($fws_['novoCodAluno']) && $fws_['novoCodAluno'] == 0) {
                $fws_['codAluno'] = NULL;
            }
            $obj = $this->carregarObjAluno($fws_);
            $obj->setAll($fws_, true);
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
            'codAluno'    => $obj ? $obj->getCodAluno() : ''
        ));
    }

    public function editarAluno($fws_, $debug = false) {
        $obj = $this->carregarObjAluno($fws_, $debug);
        echo json_encode(array(
            'statusMessage'     => $obj->getCodAluno() ? true : false,
            'codAluno'          => $obj->getCodAluno(),
            'codigoExibicao'    => $obj->getCodigoExibicao(),
            'nomeCompleto'      => $obj->getNomeCompleto(),
            'dataNascimento'    => $obj->getDataNascimento(),
            'codColegio'        => $obj->getCodColegio(),
            'nivel'             => $obj->getNivel(),
            'serieTurma'        => $obj->getSerieTurma(),
            'turno'             => $obj->getTurno(),
            'professoraRegente' => $obj->getProfessoraRegente()
        ));
        exit();
    }

    public function excluirAluno($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        try {
            $obj = new AlunoPublico();
            $obj->setCodAluno($fws_['codAluno']);
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
            'codAluno'    => $fws_['codAluno']
        ));
    }

    public function carregarListaAlunos($fws_, $debug = false) {
        try {
            $obj = new AlunoPublico();
            if ($this->isSupervisor()) {
                $lista = $obj->getListaAlunoPublicosBySupervisor($_SESSION['_cod_usuario'], $debug);
            } else {
                $lista = $obj->getListaAlunoPublicosByIdEvento($debug);
            }

            $alunos = array();
            foreach ($lista as $objL) {
                $alunos[] = array(
                    'codAluno'          => $objL->getCodAluno(),
                    'codigoExibicao'    => $objL->getCodigoExibicao(),
                    'iniciais'          => $objL->getIniciais(),
                    'nomeCompleto'      => $objL->getNomeCompleto(),
                    'codColegio'        => $objL->getCodColegio(),
                    'nomeColegio'       => $objL->getNomeColegio() ? $objL->getNomeColegio() : '',
                    'nivel'             => $objL->getNivel(),
                    'serieTurma'        => $objL->getSerieTurma(),
                    'turno'             => $objL->getTurno(),
                    'professoraRegente' => $objL->getProfessoraRegente()
                );
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

    public function registrarAcessoNome($fws_, $debug = false) {
        $codAluno = isset($fws_['codAluno']) ? $fws_['codAluno'] : '';
        if (!$codAluno) {
            echo json_encode(array('statusMessage' => false, 'message' => 'Código do aluno não informado.'));
            exit();
        }
        LogAcessoDadosSensiveis::registrar('NOME_ALUNO', $codAluno, 'REVELAR_NOME');
        $obj = new AlunoPublico();
        $obj->setCodAluno($codAluno);
        $obj->getAlunoPublicoById($debug);
        $nome = $obj->getNomeCompleto() ? $obj->getNomeCompleto() : '';
        echo json_encode(array('statusMessage' => true, 'nomeCompleto' => $nome));
        exit();
    }

    public function carregarTabelaAlunos($fws_, $debug = false) {
        try {
            $obj = new AlunoPublico();
            if ($this->isSupervisor()) {
                $lista = $obj->getListaAlunoPublicosBySupervisor($_SESSION['_cod_usuario'], $debug);
            } else {
                $lista = $obj->getListaAlunoPublicosByIdEvento($debug);
            }
            $isAdmin = $this->isAdminPedagogico();
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <?php
                    $totalReg = intval($objL->getTotalRegistros());
                    $totalDoc = intval($objL->getTotalDocumentos());
                    $nomeAgente = $objL->getNomeAgente() ? mb_convert_case($objL->getNomeAgente(), MB_CASE_TITLE, 'UTF-8') : null;
                ?>
                <tr id="<?php echo $objL->getCodAluno() ?>">
                    <td><?php echo htmlspecialchars($objL->getCodigoExibicao()) ?></td>
                    <td style="min-width:180px;">
                        <button class="btn btn-xs btn-default btn-revelar-nome" title="Revelar/ocultar nome" onclick="toggleNome(this)" style="margin-right:5px;">
                            <i class="fa fa-eye"></i>
                        </button>
                        <span class="nome-iniciais"><?php echo htmlspecialchars($objL->getIniciais()) ?></span>
                        <span class="nome-completo" style="display:none;"></span>
                    </td>
                    <td><?php echo htmlspecialchars($objL->getNomeColegio() ? $objL->getNomeColegio() : '') ?></td>
                    <td><?php echo htmlspecialchars($objL->getSerieTurma()) ?></td>
                    <td><?php echo htmlspecialchars($objL->getTurno()) ?></td>
                    <td>
                        <?php echo $nomeAgente ? htmlspecialchars($nomeAgente) : '<span class="text-muted">-</span>' ?>
                        <?php if ($isAdmin && $objL->getCodAgentePes() && $objL->getCpfAgente()) { ?>
                        <br><a class="btn btn-xs btn-info" title="Logar como PES" target="_blank"
                               href="index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo urlencode($objL->getCpfAgente()) ?>&cod_inscrito=<?php echo $objL->getCodAgentePes() ?>&cod_evento=<?php echo COD_EVENTO ?>">
                            <i class="fa fa-sign-in"></i> Logar
                        </a>
                        <?php } ?>
                    </td>
                    <td class="text-center"><?php echo $totalReg > 0 ? '<span class="badge" style="background:#2196F3;">' . $totalReg . '</span>' : '<span class="text-muted">0</span>' ?></td>
                    <td class="text-center"><?php echo $totalDoc > 0 ? '<span class="badge" style="background:#4CAF50;">' . $totalDoc . '</span>' : '<span class="text-muted">0</span>' ?></td>
                    <td class="text-nowrap">
                        <button class="btn btn-xs btn-acao-editar editar" title="Editar">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-acao-excluir excluir" title="Excluir">
                            <i class="fa fa-trash"></i>
                        </button>
                        <a class="btn btn-xs btn-acao-info" title="Registros Comportamentais"
                           href="index.php?content=view/admin/ConsultarRegistrosPedagogicosAluno.php&codAluno=<?php echo $objL->getCodAluno() ?>">
                            <i class="fa fa-clipboard"> Registros</i>
                        </a>
                        <?php if ($isAdmin) { ?>
                        <a class="btn btn-xs btn-acao-info" title="Documentos do Aluno"
                           href="index.php?content=view/admin/GerenciarLaudosAluno.php&codAluno=<?php echo $objL->getCodAluno() ?>">
                            <i class="fa fa-file-text-o"> Documentos</i>
                        </a>
                        <a class="btn btn-xs btn-acao-info" title="Avalia&ccedil;&atilde;o de Repert&oacute;rio"
                           href="index.php?content=view/admin/GerenciarAvaliacaoInicial.php&codAluno=<?php echo $objL->getCodAluno() ?>">
                            <i class="fa fa-bar-chart"> Avalia&ccedil;&atilde;o</i>
                        </a>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr><td colspan="9">Total de alunos cadastrados: <?php echo $ii ?>.</td></tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='9'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }

}
