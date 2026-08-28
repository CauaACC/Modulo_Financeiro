<?php

class GerenciarAtendimentoAC
{
    function __construct() {
        if (!defined('FWA_API_EXEC')) { FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL); }
    }

    private $apiToken = '8fcd9a1736b8f4a5c9e2437dfc61298eb19df8e7e46d32ac6456c3fc921a7b4f';

    private function validarToken()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $header);
        if ($token !== $this->apiToken) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token invalido']);
            exit;
        }
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->sanitizeEncoding($data), JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function sanitizeEncoding($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeEncoding'], $data);
        }
        if (is_string($data) && !mb_check_encoding($data, 'UTF-8')) {
            return mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
        }
        return $data;
    }

    /**
     * Verifica se o certificado esta liberado para uma inscricao
     */
    private function verificarCertificado($objInscrito, $objEvento)
    {
        $resultado = [
            'liberado'   => false,
            'motivo'     => '',
        ];

        if (!$objEvento) {
            $resultado['motivo'] = 'Evento nao encontrado';
            return $resultado;
        }

        // 1. Verificar se evento tem emissao de certificado habilitada
        try {
            $objEvConf = EventoConfiguracao::getInstancePopulado($objInscrito->getCodEvento());
        } catch (Exception $e) {
            $resultado['motivo'] = 'Configuracao do evento nao encontrada';
            return $resultado;
        }

        $emitirCertificado = $objEvConf->getEmitirCertificado();
        if (!$emitirCertificado) {
            $resultado['motivo'] = 'Evento nao emite certificado';
            return $resultado;
        }

        // 2. Verificar data de liberacao
        $dataLiberar = $objEvConf->getDataLiberarCertificado();
        if ($dataLiberar) {
            $dataLiberarFormatada = formatDateTimeSemSeparador($dataLiberar);
            $dataHoje = date('YmdHi');
            if ($dataLiberarFormatada > $dataHoje) {
                $resultado['motivo'] = 'Certificados ainda nao liberados';
                $resultado['data_liberacao'] = $dataLiberar;
                return $resultado;
            }
        }

        // 3. Verificar pagamento/isencao
        $pago = $objInscrito->getCodVoucher()
             || $objInscrito->getCodIsento()
             || $objInscrito->getTotalPagamentos()
             || $objInscrito->getTotalPerfisIsentos();

        if (!$pago) {
            $resultado['motivo'] = 'Sem registro de pagamento ou isencao';
            return $resultado;
        }

        // 4. Verificar presenca (exceto eventos especificos)
        if (!$objInscrito->getFlgPresencaInscrito()) {
            if (!in_array($objInscrito->getCodEvento(), [2, 3, 61])) {
                $resultado['motivo'] = 'Presenca nao confirmada';
                return $resultado;
            }
        }

        // Certificado liberado
        $resultado['liberado'] = true;
        $resultado['motivo'] = 'Certificado disponivel';
        return $resultado;
    }

    /**
     * POST /atendimento/inscrito
     * Busca inscricoes por CPF usando as classes existentes do sistema
     */
    public function buscarInscritoPorCpf($fws_, $debug = false)
    {
        // echo "Debug: " . ($debug ? "ON" : "OFF") . "\n";
        if (empty($fws_['cpf'])) {
            $this->jsonResponse(['erro' => 'CPF é obrigatório', 'inscritos' => []]);
            return;
        }
        $cpfLimpo = formatCpfInsert($fws_['cpf']);
        $this->validarToken();
        
        // Usar a classe Inscrito existente com formatCpfInsert
        $objInscrito = new Inscrito();
        $condicoes['cpf']              = $cpfLimpo;
        $condicoes['mostrarInscritos'] = 1;
        $condicoes['perspectiva']      = 'default';
        $condicoes['todos_eventos']    = true;
            
        $objInscrito->carregarListaInscritos($condicoes, $debug);
        $lista = $objInscrito->getListaInscritos();

        $resultado = [];
        if ($lista) {
            foreach ($lista as $insc) {
                if ($insc->getStatus() !== 'ATIVADA') {
                    continue;
                }
                $nomeEvento = '';
                $pagamentoAtivo = false;
                $motivoInativo = '';
                try {
                    $insc->carregarObjEvento();
                    $objEvento = $insc->getObjEvento();
                    if ($objEvento) {
                        $nomeEvento = $objEvento->getTitulo();

                        // Avaliar se pagamento esta ativo:
                        //   1) EventoConfiguracao.recebe_pagamento == 1
                        //   2) hoje dentro de [data_inscricao_ini, data_inscricao_fim]
                        $recebePagamento = false;
                        try {
                            $objEvConf = EventoConfiguracao::getInstancePopulado($insc->getCodEvento());
                            $recebePagamento = ($objEvConf && $objEvConf->getRecebePagamento() == 1);
                        } catch (Exception $e) {}

                        if (!$recebePagamento) {
                            $motivoInativo = 'evento_nao_recebe_pagamento';
                        } else {
                            $datIni = $objEvento->getDataInscricaoIni();
                            $datFim = $objEvento->getDataInscricaoFim();
                            $hoje = date('Y-m-d');
                            $ini = $datIni ? substr($datIni, 0, 10) : '';
                            $fim = $datFim ? substr($datFim, 0, 10) : '';
                            if ($ini && $hoje < $ini) {
                                $motivoInativo = 'periodo_nao_iniciado';
                            } elseif ($fim && $hoje > $fim) {
                                $motivoInativo = 'periodo_encerrado';
                            } else {
                                $pagamentoAtivo = true;
                            }
                        }
                    }
                } catch (Exception $e) {}

                // // So incluir inscricoes cujo evento recebe pagamento
                // if (!$recebePagamento) {
                //     continue;
                // }

                $urlPrefixo = $insc->getUrlPrefixo();
                $qrCode = $insc->getQrCode();
                $linkPagamento = '';
                if ($urlPrefixo && $qrCode) {
                    $linkPagamento = 'https://' . $_SERVER["SERVER_NAME"] . '/' . $urlPrefixo . '/inscricoes/' . $qrCode;
                }

                // Verificar situacao do certificado
                $certificado = $this->verificarCertificado($insc, $objEvento);

                // Verificar pagamento (mesma logica do verificarCertificado)
                $pago = (bool)(
                       $insc->getCodVoucher()
                    || $insc->getCodIsento()
                    || $insc->getTotalPagamentos()
                    || $insc->getTotalPerfisIsentos()
                );

                $resultado[] = [
                    'cod_inscrito'     => $insc->getCodInscrito(),
                    'cod_inscricao'    => $insc->getCodInscrito(),
                    'nome'             => $insc->getNomeInscrito(),
                    'cpf'              => $insc->getCpf(),
                    'email'            => $insc->getEMail(),
                    'fone'             => $insc->getFoneContato(),
                    'cod_evento'       => $insc->getCodEvento(),
                    'nome_evento'      => $nomeEvento,
                    'status'           => $insc->getStatus(),
                    'status_processo'  => $insc->getStatusProcesso(),
                    'dat_inscricao'    => $insc->getDatIncl(),
                    'link_pagamento'   => $linkPagamento,
                    'pago'             => $pago,
                    'pagamento_ativo'  => $pagamentoAtivo,
                    'motivo_inativo'   => $motivoInativo,
                    'certificado'      => $certificado,
                ];
            }

            // Limitar aos ultimos 3
            // $resultado = array_slice($resultado, -3);
        }

        $this->jsonResponse([
            'total' => count($resultado),
            'inscritos' => $resultado,
        ]);
    }

    /**
     * GET /inscrito/abrir?cod_evento=..&cod_inscrito=..
     * Link enviado ao FINANCEIRO DO GESTOR no aviso de cancelamento de
     * inscricao paga. Abre a ficha do inscrito no admin (aba Pagamentos).
     * Clonado de GerenciarNotaFiscalSolicitacaoAC::abrirSolicitacao:
     * sem sessao grava cookie de retorno e manda pro login; exige
     * SIS_CONTROLE_FINANCEIRO no evento; audita o acesso.
     * NAO usa validarToken(): a autorizacao aqui e a sessao do admin.
     */
    public function abrirInscricao($fws_, $debug = false)
    {
        try {
            $codInscrito = isset($fws_['cod_inscrito']) ? intval($fws_['cod_inscrito'])
                         : (isset($_GET['cod_inscrito']) ? intval($_GET['cod_inscrito']) : 0);
            $codEvento   = isset($fws_['cod_evento']) ? intval($fws_['cod_evento'])
                         : (isset($_GET['cod_evento']) ? intval($_GET['cod_evento']) : 0);
            if (!$codInscrito or !$codEvento) {
                throw new Exception("Parametros cod_evento/cod_inscrito nao informados.");
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

            if (!FWA_Autenticacao($codUsuario, enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false, $codEvento)) {
                throw new Exception("Acesso restrito a usuarios com permissao de Controle Financeiro.");
            }

            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($codInscrito);
            $objInscrito->getInscritoById();
            if (!$objInscrito->getCodInscrito()) {
                throw new Exception("Inscricao nao encontrada.");
            }

            try {
                LogAcessoDadosSensiveis::registrar(
                    'INSCRITO',
                    $codInscrito,
                    'ABRIR_VIA_EMAIL',
                    'origem=notificacao_financeiro;cod_evento=' . $codEvento
                );
            } catch (Exception $ignore) {}

            $destino = WB_URL_VIEW
                     . '?content=view/admin/inscritos-cadastro-ok.php'
                     . '&cod_evento=' . $codEvento
                     . '&cod_inscrito=' . $codInscrito
                     . '#pagamentos';
            header('Location: ' . $destino);
            exit;
        } catch (Exception $e) {
            echo "<div style='font-family:sans-serif;padding:20px;'>"
               . "<h3>Nao foi possivel abrir a inscricao</h3>"
               . "<p>" . htmlspecialchars($e->getMessage()) . "</p>"
               . "<p><a href='" . WB_URL_VIEW . "'>Voltar ao sistema</a></p>"
               . "</div>";
            exit;
        }
    }

    /**
     * POST /atendimento/cancelar
     * Cancela (DESATIVA) uma inscricao e envia e-mail de confirmacao ao inscrito.
     * Body: cod_inscricao
     *
     * Passa pela PoliticaCancelamento (classe separada):
     *  - evento encerrado           -> {ok:false, motivo:'EVENTO_ENCERRADO'} (orientar secretaria)
     *  - com impedimento            -> NAO cancela e NAO envia e-mail; devolve
     *    {ok:false, motivo:'ORIENTAR_FINANCEIRO', email_financeiro} para o BOT
     *    orientar o inscrito a escrever ele mesmo ao financeiro do gestor
     *  - liberado (ex.: dentro dos 7 dias de arrependimento) -> desativa; se
     *    paga, o financeiro recebe o PEDIDO DE ESTORNO por e-mail (a execucao
     *    e humana, pelo botao Estornar do admin)
     */
    public function cancelarInscricao($fws_, $debug = false)
    {
        $this->validarToken();

        if (empty($fws_['cod_inscricao'])) {
            $this->jsonResponse(['ok' => false, 'erro' => 'cod_inscricao obrigatorio']);
            return;
        }
        $codInscricao = intval($fws_['cod_inscricao']);

        try {
            $obj = new Inscrito();
            $obj->setCodInscrito($codInscricao);
            $obj->getInscritoById();

            if (!$obj->getCodInscrito()) {
                $this->jsonResponse(['ok' => false, 'erro' => 'Inscricao nao encontrada']);
                return;
            }

            if ($obj->getStatus() !== 'ATIVADA') {
                $this->jsonResponse([
                    'ok'    => false,
                    'erro'  => 'Inscricao nao esta ativa (status atual: ' . $obj->getStatus() . ')',
                ]);
                return;
            }

            // ---- Politica de cancelamento (classe separada) -------------
            $avaliacao = PoliticaCancelamento::avaliar($obj);

            if ($avaliacao['evento_encerrado']) {
                $this->jsonResponse([
                    'ok'           => false,
                    'motivo'       => 'EVENTO_ENCERRADO',
                    'impedimentos' => $avaliacao['impedimentos'],
                ]);
                return;
            }

            if (!$avaliacao['pode_cancelar_direto']) {
                // NAO cancela e NAO envia e-mail: o proprio inscrito e
                // orientado a escrever ao financeiro do gestor (decisao do
                // usuario — o sistema nao assume a responsabilidade do pedido).
                $emailFinanceiro = (new GestorDAO())->getEmailFinanceiroByEvento($obj->getCodEvento());
                $this->jsonResponse([
                    'ok'               => false,
                    'motivo'           => $emailFinanceiro ? 'ORIENTAR_FINANCEIRO' : 'ORIENTAR_SECRETARIA',
                    'impedimentos'     => $avaliacao['impedimentos'],
                    'email_financeiro' => $emailFinanceiro ? $emailFinanceiro : '',
                    'pago'             => $avaliacao['pago'],
                ]);
                return;
            }

            $pago = $avaliacao['pago'];

            // Arquivar registro antes de alterar (mesma logica do alterarStatusInscricao)
            $obj->arquivarParaExcluidos();

            $obj->setStatus('DESATIVADA');
            // Inscrito::salvar() lanca OKException QUANDO DA CERTO (padrao do
            // dominio). Sem este catch o sucesso caia no catch (Exception) la
            // embaixo: a inscricao era cancelada, mas a API respondia ok:false,
            // sem e-mail ao inscrito e sem aviso ao financeiro. Bug corrigido
            // em 2026-08-20 — mesmo padrao de ListarInscritosAC.
            try {
                $obj->salvar();
            } catch (OKException $okExc) {
                // sucesso: segue o fluxo
            }

            // Enviar e-mail de confirmacao ao inscrito
            $emailEnviado = false;
            $emailDestino = $obj->getEMail();
            if ($emailDestino) {
                $nomeEvento = '';
                try {
                    $obj->carregarObjEvento();
                    $objEvento = $obj->getObjEvento();
                    if ($objEvento) {
                        $nomeEvento = $objEvento->getTitulo();
                    }
                } catch (Exception $e) {}

                $msgSubject = 'Confirmacao de cancelamento de inscricao';
                $msgBody  = 'Ola ' . $obj->getNomeInscrito() . ',<br><br>';
                $msgBody .= 'Confirmamos o cancelamento da sua inscricao';
                if ($nomeEvento) {
                    $msgBody .= ' no evento <b>' . $nomeEvento . '</b>';
                }
                $msgBody .= '.<br><br>';
                $msgBody .= 'Codigo da inscricao: ' . $codInscricao . '<br>';
                $msgBody .= 'Data do cancelamento: ' . date('d/m/Y H:i') . '<br><br>';
                $msgBody .= 'Caso nao tenha sido voce que solicitou este cancelamento, '
                          . 'entre em contato com a secretaria.<br><br>';
                $msgBody .= 'Esta e uma mensagem automatica, favor nao responder.<br>';

                $emailEnviado = (bool)GerenciarEmailAC::enviarEmail(
                    $emailDestino, $msgSubject, $msgBody, false, true
                );
            }

            // Inscricao paga (dentro do arrependimento): o sistema desativou,
            // entao manda ao financeiro o PEDIDO DE ESTORNO — a devolucao e
            // executada por humano, pelo botao Estornar do admin.
            //
            // No admin o aviso e opt-in (quem cancela responde "deseja
            // notificar?"). Aqui nao ha operador a quem perguntar e o sistema
            // agiu sozinho, entao o padrao e NOTIFICAR — senao ninguem fica
            // sabendo que ha dinheiro a devolver. O chamador pode desligar
            // mandando notificar_financeiro=0.
            $notificarFinanceiro = !isset($fws_['notificar_financeiro'])
                                || (!empty($fws_['notificar_financeiro'])
                                    && $fws_['notificar_financeiro'] !== '0');
            $financeiroNotificado = false;
            if ($pago && $notificarFinanceiro) {
                $financeiroNotificado = GerenciarNotificacaoFinanceiroAC::notificarCancelamentoPago(
                    $obj->getCodEvento(),
                    $codInscricao,
                    'api-whatsapp',
                    array(
                        'acao'   => 'DESATIVADA - PROVIDENCIAR ESTORNO',
                        'valor'  => $avaliacao['valor_pago'],
                        'formas' => $avaliacao['formas'],
                    )
                );
            }

            $this->jsonResponse([
                'ok'                    => true,
                'cod_inscricao'         => $codInscricao,
                'novo_status'           => 'DESATIVADA',
                'email_destino'         => $emailDestino,
                'email_enviado'         => $emailEnviado,
                'pago'                  => (bool)$pago,
                'financeiro_notificado' => (bool)$financeiroNotificado,
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'ok'   => false,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
