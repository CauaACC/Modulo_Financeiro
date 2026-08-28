<?php

class GerenciarNotaFiscalSolicitacaoAC {

    private $extensoesPermitidas = array('pdf');
    private $tamanhoMaximo = 10485760; // 10MB
    private $mimePermitidos = array('application/pdf');

    function __construct() {
        // Sem bloqueio global: cada metodo valida o que precisa.
        // Metodos do inscrito exigem _cod_inscrito; metodos do gestor exigem SIS_CONTROLE_FINANCEIRO.
    }

    private function getDiretorioNF() {
        $dir = WB_PATH_LOG_NOTA_FISCAL;
        verificarECriarPasta($dir);
        return $dir;
    }

    private function assegurarInscritoLogado() {
        if (empty($_SESSION['_cod_inscrito'])) {
            throw new Exception("Sessao do inscrito encerrada. Faca login novamente.");
        }
    }

    private function assegurarPermissaoGestor($codEvento) {
        $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
        if (!FWA_Autenticacao($codUsuario, enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false, $codEvento)) {
            throw new Exception("Acesso restrito a usuarios com permissao de Controle Financeiro.");
        }
    }

    private function carregarConfigEvento() {
        $objEvConf = new EventoConfiguracao();
        $objEvConf->setCodEvento(COD_EVENTO);
        $objEvConf->getEventoConfiguracaoById();
        return $objEvConf;
    }

    private function assegurarFeatureHabilitada($objEvConf) {
        if ($objEvConf->getSolicitacaoNfHabilitada() != 1) {
            throw new Exception("Solicitacao de Nota Fiscal nao esta habilitada para este evento.");
        }
    }

    private function carregarPagamentoDoInscrito($codPagamento, $codInscrito) {
        $objPag = new Pagamento();
        $objPag->setCodEvento(COD_EVENTO);
        $objPag->setCodPagamento($codPagamento);
        if (!$objPag->getPagamentoById()) {
            throw new Exception("Pagamento nao encontrado.");
        }
        if ($objPag->getCodInscrito() != $codInscrito) {
            throw new Exception("Pagamento nao pertence ao inscrito.");
        }
        return $objPag;
    }

    public function solicitar($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        try {
            $this->assegurarInscritoLogado();
            $objEvConf = $this->carregarConfigEvento();
            $this->assegurarFeatureHabilitada($objEvConf);

            $codInscrito = $_SESSION['_cod_inscrito'];
            $codPagamento = isset($fws_['cod_pagamento']) ? $fws_['cod_pagamento'] : '';
            $this->carregarPagamentoDoInscrito($codPagamento, $codInscrito);

            $obj = new NotaFiscalSolicitacao();
            $obj->setCodEvento(COD_EVENTO);
            $obj->setCodPagamento($codPagamento);
            $obj->setCodInscrito($codInscrito);
            $obj->setTomadorCpfCnpj(isset($fws_['tomador_cpf_cnpj']) ? $fws_['tomador_cpf_cnpj'] : '');
            $obj->setTomadorNome(isset($fws_['tomador_nome']) ? $fws_['tomador_nome'] : '');
            $obj->setTomadorEndereco(isset($fws_['tomador_endereco']) ? $fws_['tomador_endereco'] : '');
            $obj->setTomadorCep(isset($fws_['tomador_cep']) ? $fws_['tomador_cep'] : '');
            $obj->setTomadorCidade(isset($fws_['tomador_cidade']) ? $fws_['tomador_cidade'] : '');
            $obj->setTomadorUf(isset($fws_['tomador_uf']) ? $fws_['tomador_uf'] : '');
            $obj->setTomadorEmail(isset($fws_['tomador_email']) ? $fws_['tomador_email'] : '');
            $obj->setObservacaoInscrito(isset($fws_['observacao_inscrito']) ? $fws_['observacao_inscrito'] : '');

            $obj->solicitar($debug);
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $obj->getCodSolicitacao(),
                    'SOLICITAR',
                    'cod_pagamento=' . $obj->getCodPagamento()
                );
                $this->enviarEmailAoGestor($obj, $objEvConf);
            } catch (Exception $ignore) {
                // nao interromper: fluxo principal ok
            }
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : ''
        ));
    }

    public function anexar($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        $obj = null;
        try {
            $this->assegurarPermissaoGestor(COD_EVENTO);
            $codSolicitacao = isset($fws_['cod_solicitacao']) ? $fws_['cod_solicitacao'] : '';

            $obj = new NotaFiscalSolicitacao();
            $obj->setCodEvento(COD_EVENTO);
            $obj->setCodSolicitacao($codSolicitacao);
            if (!$obj->getSolicitacaoById($debug)) {
                throw new Exception("Solicitacao nao encontrada.");
            }
            if ($obj->getStatus() != 'ATIVADA') {
                throw new Exception("Solicitacao nao esta ativa.");
            }
            if ($obj->getArquivoNf()) {
                throw new Exception("Ja existe arquivo anexado. Use a opcao Substituir.");
            }

            $nomeArquivo = $this->processarUpload($obj);
            $obj->setArquivoNf($nomeArquivo);
            $obj->anexar($debug);
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $obj->getCodSolicitacao(),
                    'ANEXAR',
                    'arquivo=' . $obj->getArquivoNf()
                );
                $this->enviarEmailAoInscrito($obj);
            } catch (Exception $ignore) {
            }
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : ''
        ));
    }

    public function substituir($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        $objNova = null;
        try {
            $this->assegurarPermissaoGestor(COD_EVENTO);
            $codSolicitacao = isset($fws_['cod_solicitacao']) ? $fws_['cod_solicitacao'] : '';
            $motivo = isset($fws_['motivo_desativacao']) ? trim($fws_['motivo_desativacao']) : '';

            $objAtual = new NotaFiscalSolicitacao();
            $objAtual->setCodEvento(COD_EVENTO);
            $objAtual->setCodSolicitacao($codSolicitacao);
            if (!$objAtual->getSolicitacaoById($debug)) {
                throw new Exception("Solicitacao nao encontrada.");
            }
            if ($objAtual->getStatus() != 'ATIVADA') {
                throw new Exception("Solicitacao nao esta ativa.");
            }
            $objAtual->setMotivoDesativacao($motivo);

            $objNova = new NotaFiscalSolicitacao();
            $nomeArquivo = $this->processarUpload($objNova);
            $objNova->setArquivoNf($nomeArquivo);

            $objAtual->substituir($objNova, $debug);
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $objAtual->getCodSolicitacao(),
                    'SUBSTITUIR',
                    'motivo=' . $objAtual->getMotivoDesativacao()
                );
                $this->enviarEmailAoInscrito($objNova);
            } catch (Exception $ignore) {
            }
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : ''
        ));
    }

    public function baixar($fws_, $debug = false) {
        try {
            $codSolicitacao = isset($fws_['cod_solicitacao']) ? $fws_['cod_solicitacao']
                            : (isset($GLOBALS['fwsGet_']['cod_solicitacao']) ? $GLOBALS['fwsGet_']['cod_solicitacao'] : '');

            $obj = new NotaFiscalSolicitacao();
            $obj->setCodEvento(COD_EVENTO);
            $obj->setCodSolicitacao($codSolicitacao);
            if (!$obj->getSolicitacaoById($debug)) {
                throw new Exception("Solicitacao nao encontrada.");
            }
            if (!$obj->getArquivoNf()) {
                throw new Exception("Nota Fiscal ainda nao foi anexada.");
            }

            // Autorizacao: inscrito (dono do pagamento) ou gestor financeiro
            $temAcesso = false;
            if (!empty($_SESSION['_cod_inscrito']) && $_SESSION['_cod_inscrito'] == $obj->getCodInscrito()) {
                $temAcesso = true;
            } elseif (!empty($_SESSION['_cod_usuario'])
                && FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false, $obj->getCodEvento())) {
                $temAcesso = true;
            }
            if (!$temAcesso) {
                throw new Exception("Acesso nao autorizado.");
            }

            $caminho = $this->getDiretorioNF() . $obj->getArquivoNf();
            if (!file_exists($caminho)) {
                throw new Exception("Arquivo da Nota Fiscal nao encontrado no servidor.");
            }

            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $obj->getCodSolicitacao(),
                    'DOWNLOAD',
                    'cod_pagamento=' . $obj->getCodPagamento()
                );
            } catch (Exception $ignore) {}

            $nomeDownload = 'nota_fiscal_' . $obj->getCodPagamento() . '_' . $obj->getCodSolicitacao() . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $nomeDownload . '"');
            header('Content-Length: ' . filesize($caminho));
            readfile($caminho);
            exit;
        } catch (Exception $e) {
            echo "Erro: " . htmlspecialchars($e->getMessage());
            exit;
        }
    }

    public function carregarHistorico($fws_, $debug = false) {
        $ok = false;
        $lista = array();
        $ex = null;
        try {
            $this->assegurarPermissaoGestor(COD_EVENTO);
            $codPagamento = isset($fws_['cod_pagamento']) ? $fws_['cod_pagamento'] : '';
            if (!$codPagamento) {
                throw new Exception("Codigo do pagamento nao informado.");
            }
            $obj = new NotaFiscalSolicitacao();
            $obj->setCodEvento(COD_EVENTO);
            $obj->setCodPagamento($codPagamento);
            $historico = $obj->carregarHistoricoByPagamento($debug);

            foreach ($historico as $item) {
                $lista[] = array(
                    'codSolicitacao'     => $item->getCodSolicitacao(),
                    'status'             => $item->getStatus(),
                    'dataSolicitacao'    => $item->getDataInclusao(),
                    'dataAnexo'          => $item->getDataAnexo(),
                    'dataFim'            => $item->getDataFim(),
                    'temArquivo'         => $item->getArquivoNf() ? 1 : 0,
                    'motivoDesativacao'  => $item->getMotivoDesativacao(),
                    'tomadorNome'        => $item->getTomadorNome(),
                    'tomadorCpfCnpj'     => $item->getTomadorCpfCnpj()
                );
            }
            $ok = true;
        } catch (Exception $e) {
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : '',
            'historico'     => $lista
        ));
    }

    public function abrirSolicitacao($fws_, $debug = false) {
        try {
            $codSolic = isset($fws_['cod_solicitacao']) ? $fws_['cod_solicitacao']
                      : (isset($GLOBALS['fwsGet_']['cod_solicitacao']) ? $GLOBALS['fwsGet_']['cod_solicitacao']
                      : (isset($_GET['cod_solicitacao']) ? $_GET['cod_solicitacao'] : ''));
            if (!$codSolic) {
                throw new Exception("Parametro cod_solicitacao nao informado.");
            }

            // Se nao logado: salva URL de retorno em cookie e redireciona pro login
            $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
            if (empty($codUsuario)) {
                $urlRetorno = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                            . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                setcookie('opctec_retorno_pos_login', $urlRetorno, time() + 3600, '/');
                $loginUrl = WB_URL_VIEW . '?content=view/admin/login.php';
                header('Location: ' . $loginUrl);
                exit;
            }

            $obj = new NotaFiscalSolicitacao();
            $obj->setCodEvento(COD_EVENTO);
            $obj->setCodSolicitacao($codSolic);
            if (!$obj->getSolicitacaoById($debug)) {
                throw new Exception("Solicitacao de Nota Fiscal nao encontrada.");
            }

            // Exige permissao de Controle Financeiro no evento da solicitacao
            if (!FWA_Autenticacao($codUsuario, enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false, $obj->getCodEvento())) {
                throw new Exception("Acesso restrito a usuarios com permissao de Controle Financeiro.");
            }

            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $obj->getCodSolicitacao(),
                    'ABRIR_VIA_EMAIL',
                    'cod_pagamento=' . $obj->getCodPagamento()
                );
            } catch (Exception $ignore) {}

            $destino = WB_URL_VIEW
                     . '?content=view/admin/inscritos-cadastro-ok.php'
                     . '&cod_evento=' . $obj->getCodEvento()
                     . '&cod_inscrito=' . $obj->getCodInscrito()
                     . '#pagamentos';
            header('Location: ' . $destino);
            exit;
        } catch (Exception $e) {
            echo "<div style='font-family:sans-serif;padding:20px;'>"
               . "<h3>Nao foi possivel abrir a solicitacao</h3>"
               . "<p>" . htmlspecialchars($e->getMessage()) . "</p>"
               . "<p><a href='" . WB_URL_VIEW . "'>Voltar ao sistema</a></p>"
               . "</div>";
            exit;
        }
    }

    private function processarUpload(NotaFiscalSolicitacao $obj) {
        if (!isset($_FILES['arquivoNf']) || $_FILES['arquivoNf']['error'] == UPLOAD_ERR_NO_FILE) {
            throw new Exception("Nenhum arquivo foi enviado.");
        }
        $file = $_FILES['arquivoNf'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo (codigo: " . $file['error'] . ").");
        }
        if ($file['size'] > $this->tamanhoMaximo) {
            throw new Exception("Arquivo excede o tamanho maximo permitido (10MB).");
        }
        $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $this->extensoesPermitidas)) {
            throw new Exception("Extensao nao permitida. Apenas PDF.");
        }
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($file['tmp_name']);
            if ($mime && !in_array($mime, $this->mimePermitidos)) {
                throw new Exception("Tipo de arquivo invalido. Apenas PDF.");
            }
        }

        $nomeArquivo = 'nf_' . date('YmdHis') . '_' . uniqid() . '.pdf';
        $destino = $this->getDiretorioNF() . $nomeArquivo;
        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            throw new Exception("Falha ao gravar o arquivo no servidor.");
        }

        return $nomeArquivo;
    }

    private function enviarEmailAoGestor(NotaFiscalSolicitacao $obj, $objEvConf) {
        $emailGestor = trim($objEvConf->getEmailGestorNf());
        if (!$emailGestor) {
            return;
        }
        $urlAdmin = WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/api/nota-fiscal/abrir?cod_solicitacao=' . $obj->getCodSolicitacao();
        $assunto = '' . ABREV_EVENTO . ': Nova solicitacao de Nota Fiscal';
        $corpo  = "Foi registrada uma nova solicitacao de Nota Fiscal.<br><br>";
        $corpo .= "Acesse o sistema para visualizar os dados fiscais e anexar a NF:<br>";
        $corpo .= "<a href='" . $urlAdmin . "'>" . $urlAdmin . "</a><br><br>";
        $corpo .= "Este e-mail nao contem dados pessoais ou fiscais por motivo de seguranca.";
        GerenciarEmailAC::enviarEmail($emailGestor, $assunto, $corpo, false, true);
    }

    /**
     * Publico porque a emissao automatica (GerenciarNotaFiscalAC) avisa o
     * inscrito pelo mesmo caminho — o texto do aviso e um so.
     */
    public function enviarEmailAoInscrito(NotaFiscalSolicitacao $obj) {
        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($obj->getCodInscrito());
        $objInscrito->setCodEvento($obj->getCodEvento());
        $objInscrito->getInscritoById();
        $emailInscrito = $objInscrito->getEMail();
        if (!$emailInscrito) {
            return;
        }

        // Fora de producao este aviso nao pode chegar ao inscrito de verdade.
        // Ele dispara SOZINHO quando uma emissao atende a solicitacao, entao e
        // facil esquecer que ele existe e sair mandando e-mail em teste.
        $destino = GerenciarEmailAC::destinoParaAmbienteDeTeste($emailInscrito);
        if ($destino === null) {
            return;
        }
        $emailInscrito = $destino['email'];

        $urlPainel = WB_URL_VIEW . '?content=view/inscricao/inscricao_congresso_ok.php';
        // Mesmo caso do assunto da nota: sem ABREV_EVENTO o assunto comecava
        // com ": ", que chega assim para quem recebe.
        $abrev = defined('ABREV_EVENTO') ? trim((string) ABREV_EVENTO) : '';
        $assunto = ($abrev != '' ? $abrev . ': ' : '') . 'Sua Nota Fiscal esta disponivel';
        $corpo  = "Ola " . $objInscrito->getNomeInscrito() . ",<br><br>";
        $corpo .= "A Nota Fiscal referente ao seu pagamento ja esta disponivel.<br>";
        $corpo .= "Acesse sua area de inscrito para baixar:<br>";
        $corpo .= "<a href='" . $urlPainel . "'>" . $urlPainel . "</a>";
        GerenciarEmailAC::enviarEmail($emailInscrito, $assunto, $corpo, false, true);
    }

}
