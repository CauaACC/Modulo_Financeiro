<?php

class GerenciarRegistroAtividadePedagogicoAC {

    private $codInscritoResolvido = null;
    private $extensoesPermitidas = array('pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx');
    private $tamanhoMaximo = 10485760; // 10MB

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!$cod_usuario) {
            throw new Exception("Usuário não está logado.");
        }
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)) {
            throw new Exception("Acesso restrito a coordenadores e gerentes.");
        }
        $this->resolverCodInscrito($cod_usuario);
    }

    private function resolverCodInscrito($codUsuario) {
        try {
            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();
            $sql = sprintf(
                "SELECT cod_inscrito FROM sis_ligacao_usuarios_eventos WHERE cod_usuario = '%s' AND cod_evento = '%s' AND status = 'ATIVADA' AND cod_inscrito IS NOT NULL LIMIT 1",
                addslashes($codUsuario), COD_EVENTO
            );
            $rs = $conex->Execute($sql);
            if ($rs && !$rs->EOF && $rs->fields['cod_inscrito']) {
                $this->codInscritoResolvido = $rs->fields['cod_inscrito'];
                $_SESSION['_cod_inscrito'] = $this->codInscritoResolvido;
            } else {
                throw new Exception("Seu usuário não possui uma inscrição associada neste evento. Solicite ao administrador.");
            }
        } catch (ADODB_Exception $e) {
            executarLog("GerenciarRegistroAtividadePedagogicoAC::resolverCodInscrito - " . $e->getMessage(), "erro-pedagogico", true);
            if ($_SERVER['HTTP_HOST'] == 'eventos.test') {
                throw new Exception($e->getMessage());
            }
            throw new Exception("Erro ao verificar inscrição. Contate o administrador.");
        }
    }

    public function getCodInscritoResolvido() {
        return $this->codInscritoResolvido;
    }

    private function getDiretorioArquivos() {
        $dir = WB_PATH_LOG . 'registro_atividades/';
        verificarECriarPasta($dir);
        return $dir;
    }

    private function processarUploads($codRegAtividade) {
        if (!isset($_FILES['arquivosRegistro']) || !is_array($_FILES['arquivosRegistro']['name'])) {
            return;
        }

        $daoArquivo = new RegistroAtividadeArquivoDAO();
        $totalExistentes = $daoArquivo->contarPorRegistro($codRegAtividade, COD_EVENTO);
        $totalNovos = 0;

        foreach ($_FILES['arquivosRegistro']['error'] as $err) {
            if ($err !== UPLOAD_ERR_NO_FILE) $totalNovos++;
        }

        if ($totalExistentes + $totalNovos > 5) {
            throw new Exception("Limite de 5 arquivos por registro. Ja existem {$totalExistentes} anexado(s).");
        }

        $dir = $this->getDiretorioArquivos();

        for ($i = 0; $i < count($_FILES['arquivosRegistro']['name']); $i++) {
            if ($_FILES['arquivosRegistro']['error'][$i] == UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($_FILES['arquivosRegistro']['error'][$i] !== UPLOAD_ERR_OK) {
                throw new Exception("Erro no upload do arquivo (codigo: " . $_FILES['arquivosRegistro']['error'][$i] . ").");
            }
            if ($_FILES['arquivosRegistro']['size'][$i] > $this->tamanhoMaximo) {
                throw new Exception("Arquivo '" . $_FILES['arquivosRegistro']['name'][$i] . "' excede o tamanho maximo permitido (10MB).");
            }

            $extensao = strtolower(pathinfo($_FILES['arquivosRegistro']['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($extensao, $this->extensoesPermitidas)) {
                throw new Exception("Extensao nao permitida: " . $_FILES['arquivosRegistro']['name'][$i] . ". Formatos aceitos: " . implode(', ', $this->extensoesPermitidas));
            }

            $nomeOriginal = $_FILES['arquivosRegistro']['name'][$i];
            $nomeArquivo = 'reg_ativ_' . $this->codInscritoResolvido . '_' . date('YmdHis') . '_' . uniqid() . '.' . $extensao . '.enc';
            $destino = $dir . $nomeArquivo;

            CriptografiaHelper::criptografarArquivo($_FILES['arquivosRegistro']['tmp_name'][$i], $destino);

            $daoArquivo->inserir($codRegAtividade, COD_EVENTO, $nomeOriginal, $nomeArquivo);
        }
    }

    private function excluirArquivoFisicoPorPath($arquivoPath) {
        if ($arquivoPath) {
            $caminhoArquivo = $this->getDiretorioArquivos() . $arquivoPath;
            if (file_exists($caminhoArquivo)) {
                unlink($caminhoArquivo);
            }
        }
    }

    public function carregarRegistroAtividade($fws_, $debug = false) {
        $obj = new RegistroAtividade();
        $obj->setCodRegAtividade($fws_['codRegAtividade']);
        $obj->getRegistroAtividadeById($debug);
        return $obj;
    }

    public function editarRegistroAtividade($fws_, $debug = false) {
        $ok = false;
        $obj = $this->carregarRegistroAtividade($fws_, $debug);
        $ok = $obj->getStatus() ? true : false;
        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => $obj->getMessage(),
            'codRegAtividade' => $obj->getCodRegAtividade(),
            'dia' => $obj->getDia(),
            'diaSemana' => $obj->getDiaSemana(),
            'descricao' => $obj->getDescricao(),
            'justificativa' => $obj->getJustificativa(),
            'horasTrabalhadas' => $obj->getHorasTrabalhadas()
        ));
        exit();
    }

    public function salvarRegistroAtividade($fws_, $debug = false) {
        try {
            if ($fws_['novoCodRegAtividade'] == 0) {
                $fws_['codRegAtividade'] = NULL;
            }
            $obj = $this->carregarRegistroAtividade($fws_);
            $obj->setAll($fws_, true);
            $obj->setCodInscrito($this->codInscritoResolvido);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        // Processar uploads de arquivo (evento 115) - apos salvar para ter o cod_reg_atividade
        if ($ok && COD_EVENTO == 115 && $obj && $obj->getCodRegAtividade()) {
            try {
                $this->processarUploads($obj->getCodRegAtividade());
            } catch (Exception $exUpload) {
                $ok = true; // registro salvo com sucesso, mas avisa sobre o arquivo
                $e = new OkException($e->getMessage() . ' Porem: ' . $exUpload->getMessage());
            }
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => $e ? $e->getMessage() : '',
            'codRegAtividade' => $obj ? $obj->getCodRegAtividade() : ''
        ));
        exit();
    }

    public function downloadArquivoRegistro($fws_, $debug = false) {
        try {
            $daoArquivo = new RegistroAtividadeArquivoDAO();
            $arquivo = $daoArquivo->buscarPorCodigo($fws_['codArquivo'], COD_EVENTO);

            if (!$arquivo) {
                throw new Exception("Arquivo nao encontrado.");
            }

            $caminhoArquivo = $this->getDiretorioArquivos() . $arquivo['arquivo_path'];
            if (!file_exists($caminhoArquivo)) {
                throw new Exception("Arquivo nao encontrado no servidor.");
            }

            $nomeOriginal = $arquivo['arquivo_nome'] ? $arquivo['arquivo_nome'] : $arquivo['arquivo_path'];

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $nomeOriginal . '"');

            // Arquivos .enc sao criptografados, demais sao legados sem criptografia
            if (substr($arquivo['arquivo_path'], -4) === '.enc') {
                $conteudo = CriptografiaHelper::descriptografarArquivo($caminhoArquivo);
                header('Content-Length: ' . strlen($conteudo));
                echo $conteudo;
            } else {
                header('Content-Length: ' . filesize($caminhoArquivo));
                readfile($caminhoArquivo);
            }
            exit();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    public function excluirArquivoRegistro($fws_, $debug = false) {
        try {
            $daoArquivo = new RegistroAtividadeArquivoDAO();
            $arquivo = $daoArquivo->buscarPorCodigo($fws_['codArquivo'], COD_EVENTO);

            if (!$arquivo) {
                throw new Exception("Arquivo nao encontrado.");
            }

            $this->excluirArquivoFisicoPorPath($arquivo['arquivo_path']);
            $daoArquivo->excluir($fws_['codArquivo'], COD_EVENTO);

            echo json_encode(array('statusMessage' => true, 'message' => 'Arquivo removido.'));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function carregarArquivosRegistro($fws_, $debug = false) {
        $daoArquivo = new RegistroAtividadeArquivoDAO();
        $arquivos = $daoArquivo->listarPorRegistro($fws_['codRegAtividade'], COD_EVENTO);
        $html = '';
        foreach ($arquivos as $arq) {
            $nome = htmlspecialchars(mb_strimwidth($arq['arquivo_nome'], 0, 30, '...'));
            $nomeCompleto = htmlspecialchars($arq['arquivo_nome']);
            $html .= '<div class="arquivo-item" data-cod="' . $arq['cod_arquivo'] . '" style="display:flex; align-items:center; gap:8px; padding:4px 8px; background:#f5f5f5; border-radius:4px; margin-bottom:4px; font-size:12px;">';
            $html .= '<a href="' . WB_URL_COMMAND . '&c=GerenciarRegistroAtividadePedagogico&m=downloadArquivoRegistro&codArquivo=' . $arq['cod_arquivo'] . '&_submit_check=1" target="_blank" title="' . $nomeCompleto . '"><i class="fa fa-paperclip"></i> ' . $nome . '</a>';
            $html .= ' <button type="button" class="btn btn-xs btn-danger btn-remover-arquivo" onclick="excluirArquivoIndividual(' . $arq['cod_arquivo'] . ', this)" title="Remover"><i class="fa fa-times"></i></button>';
            $html .= '</div>';
        }
        echo $html;
        exit();
    }

    public function excluirRegistroAtividade($fws_, $debug = false) {
        try {
            $obj = new RegistroAtividade();
            $obj->setCodRegAtividade($fws_['codRegAtividade']);

            // Excluir arquivos fisicos (evento 115)
            if (COD_EVENTO == 115) {
                $daoArquivo = new RegistroAtividadeArquivoDAO();
                $arquivos = $daoArquivo->excluirPorRegistro($fws_['codRegAtividade'], COD_EVENTO);
                foreach ($arquivos as $arq) {
                    $this->excluirArquivoFisicoPorPath($arq['arquivo_path']);
                }
            }

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => $e ? $e->getMessage() : '',
            'codRegAtividade' => $fws_['codRegAtividade']
        ));
        exit();
    }

    public function carregarTabelaRegistroAtividades($fws_, $debug = false) {
        try {
            $obj = new RegistroAtividade();
            $obj->setCodInscrito($this->codInscritoResolvido);
            $lista = $obj->getListaRegistroAtividadesByIdCodInscrito($debug);
            $colspan = COD_EVENTO == 115 ? 7 : 6;
            $contagemArquivos = array();
            if (COD_EVENTO == 115) {
                $daoArquivo = new RegistroAtividadeArquivoDAO();
                $contagemArquivos = $daoArquivo->contarPorInscrito($this->codInscritoResolvido, COD_EVENTO);
            }
            $mesAtual = 0;
            $totalHorasPorMes = 0;
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                $parts = explode("-", $objL->getDia());
                $mes = $parts[1];
                $mesNome = getMonthName($mes);
                if ($mesAtual != $mes) {
                    if ($mesAtual != 0) {
                        $horas = floor($totalHorasPorMes / 60);
                        $minutos = $totalHorasPorMes % 60;
                        echo "<tr><td colspan='{$colspan}'><strong>Total: {$horas}h {$minutos}min</strong></td></tr>";
                    }
                    $mesAtual = $mes;
                    $totalHorasPorMes = 0;
                    ?>
                    <tr><td colspan="<?php echo $colspan ?>"><hr><h4>Relat&oacute;rio M&ecirc;s: <?php echo $mesNome ?></h4></td></tr>
                    <tr style="font-weight:bold; background-color:#ccc;">
                        <th width="30px"></th>
                        <th>Dia</th>
                        <th>Dia da semana</th>
                        <th>Horas</th>
                        <th>Descri&ccedil;&atilde;o</th>
                        <?php if (COD_EVENTO == 115) { ?><th>Arquivo</th><?php } ?>
                        <th>Valida&ccedil;&atilde;o</th>
                    </tr>
                    <?php
                }
                $resultado = GerenciarRegistroAtividadeUteisAC::tratarDadosAvaliacao($objL);
                $liberarBtnEditar = $resultado['liberarBtnEditar'];
                $colorText = $resultado['colorText'];
                ?>
                <tr id="<?php echo $objL->getCodRegAtividade() ?>">
                    <td>
                    <?php if ($liberarBtnEditar) { ?>
                        <span class="btn-acao editar" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    <?php } else { ?>
                        <span class="text-muted">Avaliado</span>
                    <?php } ?>
                    </td>
                    <td><?php echo formatDateView($objL->getDia()) ?></td>
                    <td><?php echo $objL->getDiaSemana() ?></td>
                    <td><?php echo converterMinutosParaHoras($objL->getHorasTrabalhadas()) ?></td>
                    <td><?php echo $objL->getDescricao() ?></td>
                    <?php if (COD_EVENTO == 115) {
                        $qtdArq = isset($contagemArquivos[$objL->getCodRegAtividade()]) ? $contagemArquivos[$objL->getCodRegAtividade()] : 0;
                    ?>
                    <td class="text-center">
                        <?php if ($qtdArq > 0) { ?>
                            <span title="<?php echo $qtdArq ?> arquivo(s) anexado(s)"><i class="fa fa-paperclip"></i> <?php echo $qtdArq ?></span>
                        <?php } else { ?>
                            <span class="text-muted">-</span>
                        <?php } ?>
                    </td>
                    <?php } ?>
                    <td style="color:<?php echo $colorText ?>;"><?php echo $objL->getAvaliacaoStatus() ?: 'Aguardando' ?></td>
                </tr>
                <?php
                $totalHorasPorMes += (float)$objL->getHorasTrabalhadas();
            }
            if ($mesAtual != 0) {
                $horas = floor($totalHorasPorMes / 60);
                $minutos = $totalHorasPorMes % 60;
                echo "<tr><td colspan='{$colspan}'><strong>Total: {$horas}h {$minutos}min</strong></td></tr>";
            }
            echo "<tr class='tbl-fin-total'><td colspan='{$colspan}' style='text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;'>Total de registros: {$ii}</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='{$colspan}'>";
            echo $e->getMessage() ?: "Nenhum registro encontrado.";
            echo "</td></tr>";
        }
    }
}
