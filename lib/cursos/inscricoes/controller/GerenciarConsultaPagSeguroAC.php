<?php

class GerenciarConsultaPagSeguroAC {

    function __construct() {
        // O gate ja separa sessao expirada de falta de permissao e o CommandAC
        // marca o caso na resposta. Existia aqui um teste de sessao DEPOIS desta
        // linha: inalcancavel, porque o gate lanca antes — era o que fazia a tela
        // responder "Nenhuma transacao encontrada" com a sessao morta.
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
    }

    public function consultar($fws_, $debug = false) {
        $transacaoId = trim($fws_['transacao_id']);

        if (!$transacaoId) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Código da transação não informado.']);
            exit();
        }

        try {
            $objDAO = new PagamentoDAO();
            $dados = $objDAO->getByCodTransacaoPagSeguro($transacaoId, $debug);

            if (!$dados) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhuma transação encontrada com o código informado.']);
                exit();
            }

            $camposPagSeguro = [
                'codigo_transacao'          => 'Código Transação',
                'data_transacao'            => 'Data Transação',
                'data_prevista_liberacao'   => 'Data Prevista Liberação',
                'nome_cliente'              => 'Nome Cliente',
                'email_cliente'             => 'E-mail Cliente',
                'nome_comprador'            => 'Nome Comprador',
                'email_comprador'           => 'E-mail Comprador',
                'forma_pagamento'           => 'Forma Pagamento',
                'bandeira'                  => 'Bandeira',
                'numero_cartao'             => 'Número Cartão',
                'parcela'                   => 'Parcelas',
                'valor_bruto'               => 'Valor Bruto',
                'valor_taxa'                => 'Valor Taxa',
                'valor_liquido'             => 'Valor Líquido',
                'status'                    => 'Status',
                'nsu'                       => 'NSU',
                'codigo_autorizacao'        => 'Cód. Autorização',
                'identificacao_maquininha'  => 'Identificação Maquininha',
                'codigo_venda'              => 'Cód. Venda',
                'codigo_referencia'         => 'Cód. Referência',
                'codigo_tx_id'              => 'Código TX ID',
            ];

            $campos = [];
            foreach ($camposPagSeguro as $coluna => $label) {
                $campos[] = [
                    'label' => $label,
                    'valor' => isset($dados[$coluna]) ? $dados[$coluna] : ''
                ];
            }

            // Buscar evento associado à maquininha
            $eventoMaquininhaInfo = null;
            $maquininha = isset($dados['identificacao_maquininha']) ? trim($dados['identificacao_maquininha']) : '';
            if ($maquininha) {
                $eventoMaquininha = $objDAO->getEventoByMaquininha($maquininha, $debug);
                if ($eventoMaquininha) {
                    $eventoMaquininhaInfo = $eventoMaquininha['cod_evento'] . ' - ' . $eventoMaquininha['titulo'];
                }
            }

            $resultado = [
                'sucesso' => true,
                'pagseguro' => $campos,
                'eventoMaquininha' => $eventoMaquininhaInfo,
                'pagamento' => null,
                'pedido' => null,
                'linkInscrito' => null
            ];

            // Buscar pagamento associado em tab_pagamentos (protegido para não derrubar o resultado do PagSeguro)
            try {
                $pagamento = $objDAO->getPagamentoByTransacaoIdRaw($transacaoId, $debug);
            } catch (Exception $e) {
                $pagamento = false;
            }

            if ($pagamento) {
                $camposPagamento = [
                    'cod_pagamento'    => 'Cód. Pagamento',
                    'cod_evento'       => 'Cód. Evento',
                    'cod_inscrito'     => 'Cód. Inscrito',
                    'pedido_id'        => 'Pedido ID',
                    'valor'            => 'Valor',
                    'forma_pagamento'  => 'Forma Pagamento',
                    'data_pagamento'   => 'Data Pagamento',
                    'status'           => 'Status',
                    'transacao_id'     => 'Transação ID',
                    'numero_documento' => 'Número Documento',
                    'data_inclusao'    => 'Data Inclusão',
                ];

                $listaPag = [];
                foreach ($camposPagamento as $coluna => $label) {
                    $listaPag[] = [
                        'label' => $label,
                        'valor' => isset($pagamento[$coluna]) ? $pagamento[$coluna] : ''
                    ];
                }
                $resultado['pagamento'] = $listaPag;

                // Link para o inscrito
                $codEvento = isset($pagamento['cod_evento']) ? $pagamento['cod_evento'] : '';
                $codInscrito = isset($pagamento['cod_inscrito']) ? $pagamento['cod_inscrito'] : '';
                if ($codEvento && $codInscrito) {
                    // Montar URL com o prefixo correto do evento
                    $urlPrefixo = $objDAO->getUrlPrefixoByEvento($codEvento, $debug);
                    if ($urlPrefixo) {
                        global $PROTOCOLO;
                        $baseUrl = $PROTOCOLO . '://' . FWA_Dominio() . '/' . $urlPrefixo . '/inscricoes/index.php';
                    } else {
                        $baseUrl = WB_URL_VIEW;
                    }
                    $resultado['linkInscrito'] = $baseUrl
                        . '?content=view/admin/inscritos-cadastro-ok.php'
                        . '&acao=Visualizar'
                        . '&cod_inscrito=' . urlencode($codInscrito)
                        . '&cod_evento=' . urlencode($codEvento);

                    // Buscar nome do inscrito
                    try {
                        $inscrito = $objDAO->getInscritoNomeByIds($codEvento, $codInscrito, $debug);
                        if ($inscrito) {
                            $resultado['nomeInscrito'] = $inscrito['nome_inscrito'];
                        }
                    } catch (Exception $e) {
                        // não impede o resultado
                    }
                }

                // Buscar pedido associado (protegido)
                try {
                    $pedidoId = isset($pagamento['pedido_id']) ? $pagamento['pedido_id'] : '';
                    if ($pedidoId && $codEvento) {
                        $pedido = $objDAO->getPedidoByIdRaw($codEvento, $pedidoId, $debug);
                        if ($pedido) {
                            $camposPedido = [
                                'id'             => 'Pedido ID',
                                'evento_id'      => 'Evento ID',
                                'inscrito_id'    => 'Inscrito ID',
                                'descricao'      => 'Descrição',
                                'referencia'     => 'Referência',
                                'valor'          => 'Valor',
                                'dat_vencimento' => 'Data Vencimento',
                                'status'         => 'Status',
                                'dat_incl'       => 'Data Inclusão',
                            ];

                            $listaPed = [];
                            foreach ($camposPedido as $coluna => $label) {
                                $listaPed[] = [
                                    'label' => $label,
                                    'valor' => isset($pedido[$coluna]) ? $pedido[$coluna] : ''
                                ];
                            }
                            $resultado['pedido'] = $listaPed;
                        }
                    }
                } catch (Exception $e) {
                    // pedido fica null, sem derrubar o resultado
                }
            }

            echo json_encode($resultado);

        } catch (Exception $ex) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao consultar: ' . $ex->getMessage()]);
        }
        exit();
    }

    public function buscarPorFiltros($fws_, $debug = false) {
        $nome       = isset($fws_['filtro_nome'])       ? trim($fws_['filtro_nome'])       : '';
        $email      = isset($fws_['filtro_email'])      ? trim($fws_['filtro_email'])      : '';
        $referencia = isset($fws_['filtro_referencia']) ? trim($fws_['filtro_referencia']) : '';
        $status     = isset($fws_['filtro_status'])     ? trim($fws_['filtro_status'])     : '';
        $dataIni    = isset($fws_['filtro_data_ini'])   ? trim($fws_['filtro_data_ini'])   : '';
        $dataFim    = isset($fws_['filtro_data_fim'])   ? trim($fws_['filtro_data_fim'])   : '';
        $maquininha = isset($fws_['filtro_maquininha']) ? trim($fws_['filtro_maquininha']) : '';
        $pagStatus  = isset($fws_['filtro_pag_status']) ? trim($fws_['filtro_pag_status']) : '';

        if (!$nome && !$email && !$referencia && !$status && !$maquininha && !$pagStatus) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Informe ao menos um filtro.']);
            exit();
        }

        // Status ou maquininha sem nome/email/cpf exige período
        if (($status || $maquininha || $pagStatus) && !$nome && !$email && !$referencia) {
            if (!$dataIni || !$dataFim) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Ao filtrar por status ou maquininha sem outros filtros, informe o período.']);
                exit();
            }
        }

        try {
            $objDAO = new PagamentoDAO();
            $registros = $objDAO->buscarTransacoesPorFiltros($nome, $email, '', $status, $dataIni, $dataFim, $maquininha, $pagStatus, $referencia, $debug);

            if (!$registros) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhuma transação encontrada com os filtros informados.']);
                exit();
            }

            // Divergencias entre o pagamento do sistema e a transacao do PagSeguro.
            // A query ja trouxe os dois lados, entao a comparacao roda aqui mesmo,
            // sem uma consulta por linha, com a mesma regra das outras telas.
            foreach ($registros as $i => $reg) {
                if (empty($reg['pag_cod_inscrito'])) {
                    $registros[$i]['divergencias'] = [];
                    continue;
                }
                $ps = [
                    'status'         => isset($reg['status']) ? $reg['status'] : '',
                    'valor_bruto'    => isset($reg['valor_bruto_num']) ? $reg['valor_bruto_num'] : null,
                    'data_transacao' => isset($reg['data_transacao_ord']) ? $reg['data_transacao_ord'] : '',
                ];
                $registros[$i]['divergencias'] = PagamentoDivergencia::compararPagamento(
                    $ps,
                    isset($reg['pag_status']) ? $reg['pag_status'] : '',
                    isset($reg['pag_valor']) ? $reg['pag_valor'] : null,
                    isset($reg['data_pagamento_ord']) ? $reg['data_pagamento_ord'] : ''
                );
            }

            $limite = 200;
            $limitado = count($registros) >= $limite;

            echo json_encode([
                'sucesso' => true,
                'totalRegistros' => count($registros),
                'limitado' => $limitado,
                'registros' => $registros
            ]);

        } catch (Exception $ex) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao consultar: ' . $ex->getMessage()]);
        }
        exit();
    }
}
