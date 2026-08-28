<?php

class GerenciarModeloContratoAC
{
    function __construct()
    {
        $this->codEvento = COD_EVENTO;
    }

    /** Quantas versoes geradas guardar por inscrito/modelo. */
    const MAX_VERSOES_GUARDADAS = 15;

    private function getDiretorioModelos() {
        $dir = WB_PATH_LOG_CONTRATO . 'modelos/';
        verificarECriarPasta($dir);
        return $dir;
    }

    private function getDiretorioGerados() {
        $dir = WB_PATH_LOG_CONTRATO . 'gerados/';
        verificarECriarPasta($dir);
        return $dir;
    }

    /**
     * Guarda uma copia do que o inscrito baixou e mantem so as ultimas versoes.
     *
     * Serve para auditoria: saber qual texto a pessoa recebeu e quando. O contrato
     * continua sendo gerado na hora a cada download — isto e historico, nao cache.
     */
    private function guardarVersaoGerada($caminhoArquivo, $codModelo, $codInscrito, $extensao)
    {
        try {
            $dir = $this->getDiretorioGerados();
            $prefixo = $codModelo . '_' . $codInscrito . '_';
            $destino = $dir . $prefixo . date('Ymd_His') . $extensao;

            if (!@copy($caminhoArquivo, $destino)) {
                return false;
            }

            // rotaciona: o nome carrega a data, entao ordem alfabetica = ordem cronologica
            $existentes = glob($dir . $prefixo . '*');
            if ($existentes === false) {
                return true;
            }
            sort($existentes);
            $excedente = count($existentes) - self::MAX_VERSOES_GUARDADAS;
            for ($i = 0; $i < $excedente; $i++) {
                @unlink($existentes[$i]);
            }
            return true;
        } catch (Exception $e) {
            // guardar historico nunca pode impedir o download
            error_log('Contrato: falha ao guardar versao gerada. ' . $e->getMessage());
            return false;
        }
    }

    public function carregarObjModeloContrato($fws_, $debug = false)
    {
        $obj = new ModeloContrato();
        if ($fws_['codModeloContrato']) {
            $obj->setCodModeloContrato($fws_['codModeloContrato']);
            $obj->carregarModeloContratoById($debug);
        }
        return $obj;
    }

    public function salvarModeloContrato($fws_, $debug = false)
    {
        $ok = false;
        $ex = null;

        try {
            FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_CONFIGURACAO);

            $obj = $this->carregarObjModeloContrato($fws_);
            $obj->setNome($fws_['nome']);
            $obj->setDescricao($fws_['descricao']);
            $obj->setOrdem($fws_['ordem'] ?: 0);
            $obj->setFormatoSaida($fws_['formatoSaida'] ?: 'DOCX');

            // Upload do arquivo DOCX
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0) {
                $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
                if ($extensao != 'docx') {
                    throw new Exception("Apenas arquivos DOCX são permitidos.");
                }

                $dir = $this->getDiretorioModelos();
                $nomeArquivo = 'modelo-contrato-' . COD_EVENTO . '-' . time() . '.docx';

                gravandoArquivo($_FILES, $dir, $nomeArquivo, $debug);

                $obj->setArquivoModelo($nomeArquivo);
                $obj->setArquivoUploadNome($_FILES['arquivo']['name']);

                // Auto-detectar placeholders do DOCX
                $placeholders = ContratoPlaceholderHelper::detectarPlaceholders($dir . $nomeArquivo);
                $camposObrigatorios = ContratoPlaceholderHelper::getCamposObrigatoriosFromPlaceholders($placeholders);
                $obj->setCamposObrigatorios(json_encode($camposObrigatorios));
            } elseif (!$obj->getCodModeloContrato()) {
                throw new Exception("Arquivo DOCX é obrigatório.");
            }

            $obj->salvar();
            $ok = true;
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => $ex ? $ex->getMessage() : '',
            'codModeloContrato' => $obj ? $obj->getCodModeloContrato() : ''
        ));
        exit();
    }

    public function editarModeloContrato($fws_, $debug = false)
    {
        $obj = $this->carregarObjModeloContrato($fws_, $debug);
        echo json_encode(array(
            'statusMessage' => true,
            'codModeloContrato' => $obj->getCodModeloContrato(),
            'nome' => $obj->getNome(),
            'descricao' => $obj->getDescricao(),
            'arquivoUploadNome' => $obj->getArquivoUploadNome(),
            'camposObrigatorios' => $obj->getCamposObrigatorios(),
            'ordem' => $obj->getOrdem(),
            'formatoSaida' => $obj->getFormatoSaida() ?: 'DOCX'
        ));
        exit();
    }

    public function excluirModeloContrato($fws_, $debug = false)
    {
        $ok = false;
        $ex = null;

        try {
            FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_CONFIGURACAO);

            $obj = $this->carregarObjModeloContrato($fws_);
            $obj->excluir();
            $ok = true;
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => $ex ? $ex->getMessage() : ''
        ));
        exit();
    }

    public function carregarTabelaModeloContrato($fws_, $debug = false)
    {
        $obj = new ModeloContrato();
        $lista = array();
        try {
            $obj->carregarListaModeloContratoByEvento($debug);
            $lista = $obj->getListaModeloContrato();
        } catch (Exception $e) {
        }

        $html = '';
        if ($lista) {
            foreach ($lista as $item) {
                $campos = $item->getCamposObrigatorios() ? json_decode($item->getCamposObrigatorios(), true) : array();
                $mapa = ContratoPlaceholderHelper::getMapaPlaceholders();
                $labelsPlaceholders = array();
                foreach ($campos as $c) {
                    if (isset($mapa[$c])) {
                        $labelsPlaceholders[] = $mapa[$c][1];
                    }
                }
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($item->getNome()) . '</td>';
                // target _blank: o download nao tira o admin da tela de modelos, e se o
                // arquivo faltar no servidor o erro aparece na aba nova, nao no lugar da lista
                $html .= '<td><a href="' . WB_URL_COMMAND . '&c=GerenciarModeloContrato&m=downloadModelo&codModeloContrato=' . $item->getCodModeloContrato() . '&_submit_check=1" target="_blank" rel="noopener" title="Baixar template (abre em nova aba)" style="color:#2d6a4f; text-decoration:none;"><i class="fa fa-download" style="margin-right:4px;"></i>' . htmlspecialchars($item->getArquivoUploadNome()) . '</a></td>';
                $html .= '<td>' . implode(', ', $labelsPlaceholders) . '</td>';
                $html .= '<td>' . $item->getOrdem() . '</td>';
                $html .= '<td>';
                $html .= '<div style="display:flex; align-items:center; gap:4px; flex-wrap:nowrap;">';
                $html .= '<span class="btn-acao btn-editar-modelo" data-cod="' . $item->getCodModeloContrato() . '" title="Editar"><i class="fa fa-pencil"></i></span>';
                $html .= '<span class="btn-acao btn-acao-excluir btn-excluir-modelo" data-cod="' . $item->getCodModeloContrato() . '" data-nome="' . htmlspecialchars($item->getNome()) . '" title="Excluir"><i class="fa fa-trash"></i></span>';
                $html .= '</div>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        } else {
            $html = '<tr><td colspan="5" class="text-center">Nenhum modelo de contrato cadastrado.</td></tr>';
        }

        echo $html;
    }

    /**
     * Download do template DOCX original (para edição pelo admin)
     */
    public function downloadModelo($fws_, $debug = false)
    {
        try {
            FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_CONFIGURACAO);

            $codModeloContrato = $fws_['codModeloContrato'] ?: addslashesopctec($_GET['codModeloContrato']);
            if (!$codModeloContrato) {
                throw new Exception("Modelo não informado.");
            }

            $objModelo = new ModeloContrato();
            $objModelo->setCodModeloContrato($codModeloContrato);
            if (!$objModelo->carregarModeloContratoById()) {
                throw new Exception("Modelo não encontrado.");
            }

            $dir = $this->getDiretorioModelos();
            $caminhoArquivo = $dir . $objModelo->getArquivoModelo();

            if (!file_exists($caminhoArquivo)) {
                throw new Exception("Arquivo não encontrado no servidor.");
            }

            $nomeDownload = $objModelo->getArquivoUploadNome() ?: $objModelo->getArquivoModelo();

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $nomeDownload . '"');
            header('Content-Length: ' . filesize($caminhoArquivo));
            header('Cache-Control: no-cache, no-store, must-revalidate');

            readfile($caminhoArquivo);
            exit();

        } catch (Exception $e) {
            echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
            exit();
        }
    }

    /**
     * Gera o DOCX preenchido com dados do inscrito para download
     */
    public function gerarContratoInscrito($fws_, $debug = false)
    {
        try {
            if (!$_SESSION['_cod_inscrito']) {
                throw new Exception("Acesso negado.");
            }

            $codModeloContrato = $fws_['codModeloContrato'] ?: $fwsGet_['codModeloContrato'];
            if (!$codModeloContrato) {
                // Tentar pegar do GET
                $codModeloContrato = isset($_GET['codModeloContrato']) ? $_GET['codModeloContrato'] : '';
            }

            if (!$codModeloContrato) {
                throw new Exception("Modelo de contrato não informado.");
            }

            // Carregar modelo
            $objModelo = new ModeloContrato();
            $objModelo->setCodModeloContrato($codModeloContrato);
            if (!$objModelo->carregarModeloContratoById()) {
                throw new Exception("Modelo de contrato não encontrado.");
            }

            // Carregar inscrito
            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
            $objInscrito->getInscritoById();

            // Verificar campos obrigatórios
            $camposObrigatorios = $objModelo->getCamposObrigatorios() ? json_decode($objModelo->getCamposObrigatorios(), true) : array();
            $faltantes = ContratoPlaceholderHelper::getCamposFaltantes($objInscrito, $camposObrigatorios);
            if (!empty($faltantes)) {
                throw new Exception("Dados incompletos. Campos faltantes: " . implode(', ', $faltantes));
            }

            // Gerar contrato
            $dir = $this->getDiretorioModelos();
            $caminhoTemplate = $dir . $objModelo->getArquivoModelo();

            if (!file_exists($caminhoTemplate)) {
                throw new Exception("Arquivo de template não encontrado no servidor.");
            }

            $formatoSaida = strtoupper($objModelo->getFormatoSaida() ?: 'DOCX');

            $caminhoTempDocx = sys_get_temp_dir() . '/' . 'contrato_' . $objInscrito->getCodInscrito() . '_' . time() . '.docx';

            ContratoPlaceholderHelper::preencherTemplate($caminhoTemplate, $caminhoTempDocx, $objInscrito);

            // O DOCX ja esta pronto e correto neste ponto. Se o modelo pede PDF, a
            // conversao e feita pelo Gotenberg, que preserva o layout do Word. Como
            // depende de servico externo, caindo ele entregamos o DOCX em vez de
            // devolver erro para o inscrito.
            // (o modo antigo, PHPWord+mPDF, esta comentado em ContratoPlaceholderHelper)
            $formatoEntregue = 'DOCX';
            $caminhoTemp = $caminhoTempDocx;

            if ($formatoSaida == 'PDF') {
                try {
                    $caminhoTempPdf = str_replace('.docx', '.pdf', $caminhoTempDocx);
                    ContratoPlaceholderHelper::converterParaPDF($caminhoTempDocx, $caminhoTempPdf);

                    unlink($caminhoTempDocx);
                    $caminhoTemp = $caminhoTempPdf;
                    $formatoEntregue = 'PDF';
                } catch (Exception $eConversao) {
                    error_log('Contrato ' . $objModelo->getCodModeloContrato()
                        . ': conversao para PDF falhou, entregando DOCX. '
                        . $eConversao->getMessage());
                }
            }

            $extensao = ($formatoEntregue == 'PDF') ? '.pdf' : '.docx';

            $nomeDownload = COD_EVENTO . '_' . $objInscrito->getCodInscrito() . '_'
                . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $objModelo->getNome())
                . '_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $objInscrito->getNomeInscrito())
                . $extensao;

            $this->guardarVersaoGerada($caminhoTemp, $objModelo->getCodModeloContrato(),
                                       $objInscrito->getCodInscrito(), $extensao);

            if ($formatoEntregue == 'PDF') {
                header('Content-Type: application/pdf');
            } else {
                header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            }

            header('Content-Disposition: attachment; filename="' . $nomeDownload . '"');
            header('Content-Length: ' . filesize($caminhoTemp));
            header('Cache-Control: no-cache, no-store, must-revalidate');

            readfile($caminhoTemp);
            unlink($caminhoTemp);
            exit();

        } catch (Exception $e) {
            echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
            exit();
        }
    }
}
?>