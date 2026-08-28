<?php

class GerenciarLaudoAlunoAC {

    private $extensoesPermitidas = array('pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx');
    private $tamanhoMaximo = 10485760; // 10MB

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)) {
            throw new Exception("Acesso restrito a coordenadores e gerentes.");
        }
    }

    private function getDiretorioLaudos() {
        $dir = WB_PATH_LOG . 'laudos/';
        verificarECriarPasta($dir);
        return $dir;
    }

    private function verificarAutoria($obj) {
        $codUsuarioLogado = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
        if ($obj->getIdInclusao() && $obj->getIdInclusao() != $codUsuarioLogado) {
            throw new Exception("Somente o autor do documento pode alterar ou excluir este registro.");
        }
    }

    public function carregarObjLaudo($fws_, $debug = false) {
        $obj = new LaudoAluno();
        if (isset($fws_['codLaudo']) && $fws_['codLaudo']) {
            $obj->setCodLaudo($fws_['codLaudo']);
            $obj->getLaudoAlunoById($debug);
        }
        return $obj;
    }

    private function processarUpload($obj) {
        if (!isset($_FILES['arquivoLaudo']) || $_FILES['arquivoLaudo']['error'] == UPLOAD_ERR_NO_FILE) {
            return;
        }

        $file = $_FILES['arquivoLaudo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo (codigo: " . $file['error'] . ").");
        }

        if ($file['size'] > $this->tamanhoMaximo) {
            throw new Exception("Arquivo excede o tamanho maximo permitido (10MB).");
        }

        $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $this->extensoesPermitidas)) {
            throw new Exception("Extensao nao permitida. Formatos aceitos: " . implode(', ', $this->extensoesPermitidas));
        }

        $nomeOriginal = $file['name'];
        $nomeArquivo = 'laudo_' . $obj->getCodAluno() . '_' . date('YmdHis') . '_' . uniqid() . '.' . $extensao . '.enc';
        $destino = $this->getDiretorioLaudos() . $nomeArquivo;

        // Salvar temporariamente para depois criptografar
        $tmpDestino = $file['tmp_name'];
        CriptografiaHelper::criptografarArquivo($tmpDestino, $destino);

        $obj->setArquivoNome($nomeOriginal);
        $obj->setArquivoPath($nomeArquivo);
    }

    public function salvarLaudo($fws_, $debug = false) {
        $ok = false;
        $obj = null;
        $ex = null;
        try {
            if (isset($fws_['novoCodLaudo']) && $fws_['novoCodLaudo'] == 0) {
                $fws_['codLaudo'] = NULL;
            }
            $isNovo = !isset($fws_['novoCodLaudo']) || $fws_['novoCodLaudo'] == 0;
            $obj = $this->carregarObjLaudo($fws_);

            // Verificar autoria na edição
            if (!$isNovo && $obj->getCodLaudo()) {
                $this->verificarAutoria($obj);
            }

            $obj->setAll($fws_, true);
            $this->processarUpload($obj);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
            $ex = $e;
            LogAcessoLaudo::registrar($obj->getCodLaudo(), $obj->getCodAluno(), $isNovo ? 'INSERIR' : 'ALTERAR');
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : '',
            'codLaudo'      => $obj ? $obj->getCodLaudo() : ''
        ));
    }

    public function editarLaudo($fws_, $debug = false) {
        $obj = $this->carregarObjLaudo($fws_, $debug);
        if ($obj->getCodLaudo()) {
            LogAcessoLaudo::registrar($obj->getCodLaudo(), $obj->getCodAluno(), 'LER');
        }
        $codUsuarioLogado = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
        $ehAutor = ($obj->getIdInclusao() == $codUsuarioLogado);
        echo json_encode(array(
            'statusMessage'  => $obj->getCodLaudo() ? true : false,
            'codLaudo'       => $obj->getCodLaudo(),
            'codAluno'       => $obj->getCodAluno(),
            'dataLaudo'      => $obj->getDataLaudo(),
            'finalidade'     => $obj->getFinalidade(),
            'descricao'      => $obj->getDescricao(),
            'conclusao'      => $obj->getConclusao(),
            'recomendacao'   => $obj->getRecomendacao(),
            'arquivoNome'    => $obj->getArquivoNome(),
            'arquivoPath'    => $obj->getArquivoPath(),
            'ehAutor'        => $ehAutor
        ));
        exit();
    }

    public function excluirLaudo($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        try {
            $justificativa = isset($fws_['justificativa']) ? trim($fws_['justificativa']) : '';
            if ($justificativa == '') {
                throw new Exception("Informe a justificativa para exclusao do documento.");
            }

            $obj = new LaudoAluno();
            $obj->setCodLaudo($fws_['codLaudo']);
            $obj->getLaudoAlunoById();

            // Verificar autoria
            $this->verificarAutoria($obj);

            // Excluir arquivo físico se existir
            if ($obj->getArquivoPath()) {
                $caminhoArquivo = $this->getDiretorioLaudos() . $obj->getArquivoPath();
                if (file_exists($caminhoArquivo)) {
                    unlink($caminhoArquivo);
                }
            }

            $codAluno = $obj->getCodAluno();
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
            LogAcessoLaudo::registrar($fws_['codLaudo'], $codAluno, 'EXCLUIR', $justificativa);
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : '',
            'codLaudo'      => $fws_['codLaudo']
        ));
    }

    public function downloadArquivoLaudo($fws_, $debug = false) {
        try {
            $obj = new LaudoAluno();
            $obj->setCodLaudo($fws_['codLaudo']);
            $obj->getLaudoAlunoById();

            if (!$obj->getArquivoPath()) {
                throw new Exception("Documento nao possui arquivo anexado.");
            }

            $caminhoArquivo = $this->getDiretorioLaudos() . $obj->getArquivoPath();
            if (!file_exists($caminhoArquivo)) {
                throw new Exception("Arquivo nao encontrado no servidor.");
            }

            LogAcessoLaudo::registrar($obj->getCodLaudo(), $obj->getCodAluno(), 'DOWNLOAD');

            $nomeOriginal = $obj->getArquivoNome() ? $obj->getArquivoNome() : $obj->getArquivoPath();

            // Descriptografar o arquivo antes de enviar
            $conteudo = CriptografiaHelper::descriptografarArquivo($caminhoArquivo);

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $nomeOriginal . '"');
            header('Content-Length: ' . strlen($conteudo));
            echo $conteudo;
            exit();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    public function carregarHistoricoAcesso($fws_, $debug = false) {
        try {
            $obj = new LogAcessoLaudo();
            $logs = $obj->getLogsPorLaudo($fws_['codLaudo'], $debug);

            $acoes = array(
                'INSERIR' => 'Inseriu',
                'ALTERAR' => 'Alterou',
                'LER' => 'Leu/Abriu',
                'EXCLUIR' => 'Excluiu',
                'DOWNLOAD' => 'Baixou arquivo'
            );

            if (empty($logs)) {
                echo '<tr><td colspan="4">Nenhum registro de acesso.</td></tr>';
                return;
            }

            foreach ($logs as $log) {
                $acao = isset($acoes[$log['acao']]) ? $acoes[$log['acao']] : $log['acao'];
                if ($log['justificativa']) {
                    $acao .= '<br><small class="text-muted"><i class="fa fa-comment-o"></i> ' . htmlspecialchars($log['justificativa']) . '</small>';
                }
                ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i:s', strtotime($log['data_acesso'])) ?></td>
                    <td><?php echo htmlspecialchars($log['nome_usuario'] ? $log['nome_usuario'] : $log['cod_usuario']) ?></td>
                    <td><?php echo $acao ?></td>
                    <td><?php echo htmlspecialchars($log['ip']) ?></td>
                </tr>
                <?php
            }
        } catch (Exception $e) {
            echo '<tr><td colspan="4">Erro ao carregar historico.</td></tr>';
        }
    }

    public function carregarTabelaLaudos($fws_, $debug = false) {
        try {
            $obj = new LaudoAluno();
            $obj->setCodAluno($fws_['codAluno']);

            $codUsuarioLogado = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
            $lista = $obj->getListaLaudosByCodAluno($debug);
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                $temArquivo = $objL->getArquivoPath() ? true : false;
                $ehAutor = ($objL->getIdInclusao() == $codUsuarioLogado);
                ?>
                <tr id="<?php echo $objL->getCodLaudo() ?>">
                    <td><?php echo formatDateView($objL->getDataLaudo()) ?></td>
                    <td><?php echo htmlspecialchars($objL->getFinalidade() ? $objL->getFinalidade() : '') ?></td>
                    <td class="text-center">
                        <?php if ($temArquivo) { ?>
                            <a class="btn btn-xs btn-acao-info download-laudo" title="Baixar <?php echo htmlspecialchars($objL->getArquivoNome()) ?>"
                               href="<?php echo WB_URL_COMMAND ?>&c=GerenciarLaudoAluno&m=downloadArquivoLaudo&codLaudo=<?php echo $objL->getCodLaudo() ?>&_submit_check=1"
                               target="_blank">
                                <i class="fa fa-paperclip"></i> <?php echo htmlspecialchars(mb_strimwidth($objL->getArquivoNome(), 0, 20, '...')) ?>
                            </a>
                        <?php } else { ?>
                            <span class="text-muted" style="font-size:12px;">-</span>
                        <?php } ?>
                    </td>
                    <td class="text-nowrap">
                        <?php if ($ehAutor) { ?>
                            <button class="btn btn-xs btn-acao-editar editar-laudo" title="Editar">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button class="btn btn-xs btn-acao-excluir excluir-laudo" title="Excluir">
                                <i class="fa fa-trash"></i>
                            </button>
                        <?php } ?>
                        <button class="btn btn-xs btn-acao-info visualizar-laudo" title="Visualizar">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="btn btn-xs btn-acao-info historico-laudo" title="Hist&oacute;rico de Acesso">
                            <i class="fa fa-history"></i>
                        </button>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr><td colspan="4">Total de documentos cadastrados: <?php echo $ii ?>.</td></tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Nenhum documento cadastrado.";
            }
            echo "</td></tr>";
        }
    }

}
