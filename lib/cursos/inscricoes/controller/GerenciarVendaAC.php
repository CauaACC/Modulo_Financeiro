<?php

/**
 * Vendas do canal: carga da planilha e acompanhamento.
 *
 * O semaforo desta tela e o coracao do fluxo — ele diz, por venda, o que
 * impede a nota de sair. Sem isso o gestor so descobre o problema quando a
 * SEFAZ rejeita.
 */
class GerenciarVendaAC {

    function __construct() {
        FWA_exigirPermissao(array(enumSistema::SIS_CONTROLE_FINANCEIRO, enumSistema::SIS_GERENCIAR_ADM_GERAL));
        $this->exigirProjetoLoja();
    }

    /**
     * Modulo comercial so vale em evento classificado como Loja (aba Projeto).
     * Fica no construtor para cobrir todos os metodos: sem isso, bastaria
     * chamar o CommandAC direto para operar vendas num projeto que nao vende.
     */
    private function exigirProjetoLoja() {
        try {
            $conf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
            $ehLoja = $conf && $conf->getTipoProjeto() == 'LOJA';
        } catch (Exception $e) {
            $ehLoja = false;
        }
        if (!$ehLoja) {
            throw new Exception("Modulo de Vendas nao habilitado neste projeto. "
                . "Classifique o evento como 'Loja (venda de mercadoria)' em Configuracao do evento > Projeto.");
        }
    }


    /**
     * Gestor do evento atual — o mesmo criterio do resto do modulo fiscal:
     * o evento resolve o gestor por NOME.
     */
    private function getCodGestorDoEvento($debug = false) {
        $obj = new NotaFiscalEmissor();
        if ($obj->getEmissorAtivoByEvento(COD_EVENTO, $debug)) {
            return (int) $obj->getCodGestor();
        }
        return 0;
    }

    public function carregarTabelaVendas($fws_, $debug = false) {
        $obj = new PedidoMarketplace();
        $filtros = array(
            'busca'   => isset($fws_['busca']) ? $fws_['busca'] : '',
            'semNota' => !empty($fws_['semNota'])
        );
        $lista = $obj->getObjDAO()->listarVendas(COD_EVENTO, $filtros, $debug);

        if (empty($lista)) {
            echo "<tr><td colspan='8' style='text-align:center; padding:24px; color:#999;'>"
               . "Nenhuma venda neste canal. Use <b>Carregar planilha</b> para importar os pedidos da Shopee."
               . "</td></tr>";
            return;
        }

        foreach ($lista as $row) {
            $pendencias = array();

            if ((int) $row['qtd_itens'] == 0) {
                $pendencias[] = "sem itens";
            }
            if ((int) $row['qtd_itens_pendentes'] > 0) {
                $pendencias[] = (int) $row['qtd_itens_pendentes'] . " item(ns) sem produto completo";
            }
            if (trim((string) $row['cpf']) == '') {
                $pendencias[] = "cliente sem CPF";
            }
            if (trim((string) $row['uf']) == '') {
                $pendencias[] = "sem UF";
            }

            $temNota = !empty($row['cod_nota_fiscal']);

            echo "<tr id='" . (int) $row['pedido_id'] . "'>";
            echo "<td class='col-codigo'>"
               . "<a href='#' class='btn-acao ver' title='Ver itens'><i class='fa fa-list'></i></a>";

            // Botao de nota so aparece no que da para faturar: venda com
            // pendencia ou ja faturada nao oferece a acao, em vez de oferecer
            // e falhar depois do clique.
            if (!$temNota && empty($pendencias)) {
                echo "<a href='#' class='btn-acao nfe' title='Emitir NF-e'>"
                   . "<i class='fa fa-file-text-o'></i></a>";
            }
            echo "</td>";
            echo "<td class='col-codigo'>" . htmlspecialchars((string) $row['id_externo']) . "</td>";

            $quando = (string) $row['data_pedido'];
            echo "<td class='col-codigo'>" . ($quando != '' ? date('d/m/Y H:i', strtotime($quando)) : '') . "</td>";

            echo "<td class='col-nome'>" . htmlspecialchars((string) $row['nome_inscrito']);
            if (trim((string) $row['cidade']) != '') {
                echo "<br><span style='font-weight:normal; color:#888; font-size:11px;'>"
                   . htmlspecialchars((string) $row['cidade'] . '/' . $row['uf']) . "</span>";
            }
            echo "</td>";

            echo "<td class='col-num' style='text-align:right;'>"
               . "R$ " . number_format((float) $row['valor_total'], 2, ',', '.') . "</td>";
            echo "<td class='col-codigo'>" . (int) $row['qtd_itens'] . "</td>";

            if ($temNota) {
                echo "<td class='col-badge'><span class='badge-tipo'><span class='dot dot-green'></span>"
                   . "NF-e " . htmlspecialchars((string) $row['numero_nota']) . "</span></td>";
            } else {
                echo "<td class='col-badge'><span class='badge-tipo'><span class='dot dot-blue'></span>"
                   . "sem nota</span></td>";
            }

            if (empty($pendencias)) {
                echo "<td class='col-badge'><span class='badge-tipo'><span class='dot dot-green'></span>"
                   . "Pronta</span></td>";
            } else {
                echo "<td><span class='badge-tipo' style='background:#fdecea; color:#8b1a12;'>"
                   . "<span class='dot dot-red'></span>" . htmlspecialchars(implode(' · ', $pendencias))
                   . "</span></td>";
            }
            echo "</tr>";
        }
    }

    public function carregarItensVenda($fws_, $debug = false) {
        $pedidoId = isset($fws_['pedidoId']) ? (int) $fws_['pedidoId'] : 0;
        if (!$pedidoId) {
            echo "<tr><td colspan='6' style='text-align:center; color:#999; padding:20px;'>Venda nao encontrada.</td></tr>";
            return;
        }

        $obj = new PedidoItem();
        $itens = $obj->listarPorPedido($pedidoId, $debug);

        if (empty($itens)) {
            echo "<tr><td colspan='6' style='text-align:center; color:#999; padding:20px;'>"
               . "Esta venda nao tem itens.</td></tr>";
            return;
        }

        foreach ($itens as $item) {
            $pendencia = $item->pendenciaParaNota($debug);
            echo "<tr>";
            echo "<td class='col-codigo'>" . (int) $item->getOrdem() . "</td>";
            echo "<td class='col-codigo'>" . htmlspecialchars((string) $item->getSku()) . "</td>";
            echo "<td>" . htmlspecialchars((string) $item->getDescricao()) . "</td>";
            echo "<td class='col-num' style='text-align:right;'>"
               . number_format((float) $item->getQuantidade(), 3, ',', '.') . "</td>";
            echo "<td class='col-num' style='text-align:right;'>"
               . "R$ " . number_format((float) $item->getValorTotal(), 2, ',', '.') . "</td>";
            if ($pendencia == '') {
                echo "<td class='col-badge'><span class='badge-tipo'><span class='dot dot-green'></span>ok</span></td>";
            } else {
                echo "<td style='color:#8b1a12; font-size:12px;'>" . htmlspecialchars($pendencia) . "</td>";
            }
            echo "</tr>";
        }
    }

    // ==================================================================
    // NF-e de venda (modelo 55)
    //
    // Mesmo desenho da NFS-e que ja roda em producao: previa antes de
    // emitir, referencia idempotente, numero reservado no emissor e ciclo
    // emitir -> processando -> autorizada. O que muda e o payload.
    // ==================================================================

    private function responder($ok, $mensagem, $extra = array()) {
        $resposta = array('statusMessage' => $ok, 'message' => $mensagem);
        echo json_encode(array_merge($resposta, $extra), JSON_UNESCAPED_UNICODE);
    }

    private function carregarPedidoDaVenda($pedidoId, $codEvento) {
        if (!$pedidoId) {
            throw new Exception("Venda nao informada.");
        }
        $objPedido = new Pedido();
        $objPedido->setEventoId($codEvento);
        $objPedido->setId($pedidoId);
        $objPedido->getPedidoById();

        // Pedido::getPedidoById() nao devolve o resultado do DAO (sempre null),
        // entao a carga se confirma por um campo que so existe vindo do banco.
        if (!$objPedido->getInscritoId()) {
            throw new Exception("Venda nao encontrada neste canal.");
        }
        return $objPedido;
    }

    private function carregarEmissorNfe($codEvento) {
        $objEmissor = new NotaFiscalEmissor();
        if (!$objEmissor->getEmissorAtivoByEvento($codEvento)) {
            throw new Exception("Nenhum emissor de nota cadastrado para o gestor deste canal. "
                . "Cadastre em Vendas > Emissores de NF.");
        }
        if (!$objEmissor->emiteNfe()) {
            throw new Exception("O emissor deste gestor nao tem serie de NF-e configurada — "
                . "ele esta cadastrado apenas para nota de servico. "
                . "Preencha o bloco de NF-e em Vendas > Emissores de NF.");
        }
        $objEmissor->validatorAptoParaEmitirNfe();
        return $objEmissor;
    }

    /**
     * Itens da venda ja casados com o produto do catalogo. Item sem produto,
     * ou com produto incompleto, derruba a emissao ANTES de gastar numero de
     * nota — e o mesmo criterio do semaforo da listagem.
     */
    private function carregarItensParaNota($pedidoId, $debug = false) {
        $obj = new PedidoItem();
        $itens = $obj->listarPorPedido($pedidoId, $debug);

        if (empty($itens)) {
            throw new Exception("Esta venda nao tem itens — nao ha o que faturar.");
        }

        $lista = array();
        foreach ($itens as $item) {
            $pendencia = $item->pendenciaParaNota($debug);
            if ($pendencia != '') {
                throw new Exception($pendencia);
            }
            $produto = $item->getProduto($debug);
            $produto->validatorAptoParaNota();
            $lista[] = array('item' => $item, 'produto' => $produto);
        }
        return $lista;
    }

    /**
     * Dados do destinatario a partir do cadastro do cliente. Na venda de
     * marketplace nao ha solicitacao de nota nem dado de recibo: o comprador
     * e o destinatario, e foi a propria carga que criou o cadastro dele.
     */
    private function resolverDestinatario(NotaFiscal $objNota, Pedido $objPedido, $codEvento) {
        $codInscrito = $objPedido->getInscritoId();

        $objInscrito = new Inscrito();
        $objInscrito->setCodEvento($codEvento);
        $objInscrito->setCodInscrito($codInscrito);
        if (!$objInscrito->getInscritoById()) {
            throw new Exception("Cliente da venda nao encontrado.");
        }

        $cidade = $objInscrito->getCidade();
        $uf = $objInscrito->getUf() ? $objInscrito->getUf() : $objInscrito->getEstado();

        $objNota->setCodInscrito($codInscrito);
        $objNota->setTomadorCpfCnpj(preg_replace('/[^0-9]/', '', (string) $objInscrito->getCpf()));
        $objNota->setTomadorNome($objInscrito->getNomeInscrito());
        $objNota->setTomadorEmail($objInscrito->getEMail());
        $objNota->setTomadorTelefone($objInscrito->getFoneCelular());
        $objNota->setTomadorCep(preg_replace('/[^0-9]/', '', (string) $objInscrito->getCep()));
        $objNota->setTomadorLogradouro($objInscrito->getEndereco());
        $objNota->setTomadorNumero($objInscrito->getNumero());
        $objNota->setTomadorComplemento($objInscrito->getComplemento());
        $objNota->setTomadorBairro($objInscrito->getBairro());
        $objNota->setTomadorUf($uf);
        $objNota->setTomadorCidadeNome($cidade);

        // A NF-e identifica o municipio pelo NOME, mas guardamos tambem o IBGE:
        // e o que permite conferir depois se a cidade foi resolvida certo.
        $codigoMunicipio = MunicipioIbge::resolver($cidade, $uf);
        if ($codigoMunicipio != '') {
            $objNota->setTomadorCodigoMunicipio($codigoMunicipio);
        }

        return $objNota;
    }

    /**
     * Monta a nota a partir da venda — SEM reservar numero, SEM gravar e SEM
     * chamar a API. Usado pela previa E pela emissao: caminho unico para que a
     * previa nao mostre uma coisa e a nota saia outra.
     */
    private function montarNotaDaVenda(Pedido $objPedido, NotaFiscalEmissor $objEmissor,
                                       array $itens, $ambiente, $codEvento) {
        $objNota = new NotaFiscal();
        $objNota->setCodEmissor($objEmissor->getCodEmissor());
        $objNota->setCodEvento($codEvento);
        $objNota->setOrigem('PEDIDO');
        $objNota->setOrigemId($objPedido->getId());
        $objNota->setAmbiente($ambiente);
        $objNota->setTipo('NFE');
        $objNota->setStatus('PROCESSANDO');
        $objNota->setNaturezaOperacao($objEmissor->getNaturezaOperacao());

        $this->resolverDestinatario($objNota, $objPedido, $codEvento);

        // CFOP so se resolve depois de saber a UF do destinatario.
        $objNota->setCfop($objEmissor->getCfopParaUf($objNota->getTomadorUf()));

        // Total da nota = soma dos ITENS, nao o valor do pedido: o pedido pode
        // carregar frete do marketplace, que nao entra na nota.
        $total = 0;
        foreach ($itens as $par) {
            $total += (float) $par['item']->getValorTotal();
        }
        $total = round($total, 2);
        $objNota->setValorProdutos($total);
        $objNota->setValorTotalNfe($total);

        return $objNota;
    }

    /**
     * Dados do canal usados na nota. O identificador do vendedor na plataforma
     * e do CANAL, nao do emissor: o mesmo CNPJ pode vender em varias.
     */
    private function dadosDoCanal($codEvento) {
        $dados = array('cnpj_intermediador' => '', 'id_vendedor' => '');
        try {
            $objConf = EventoConfiguracao::getInstancePopulado($codEvento);
            if ($objConf) {
                $dados['id_vendedor'] = (string) $objConf->getCanalVendaIdentificador();
            }
        } catch (Exception $e) {
            // Canal sem configuracao nao impede a nota: so impede declarar
            // intermediador, e nesse caso o payload avisa com mensagem propria.
        }
        return $dados;
    }

    /**
     * Previa: monta a nota inteira e devolve o que sairia, INCLUSIVE o JSON
     * que vai para a API. Nao reserva numero, nao grava e nao emite.
     */
    public function previaNfe($fws_, $debug = false) {
        try {
            $codEvento = COD_EVENTO;
            $pedidoId = isset($fws_['pedidoId']) ? (int) $fws_['pedidoId'] : 0;

            $objPedido  = $this->carregarPedidoDaVenda($pedidoId, $codEvento);
            $objEmissor = $this->carregarEmissorNfe($codEvento);
            $itens      = $this->carregarItensParaNota($pedidoId, $debug);

            // Previa NAO instancia o cliente HTTP: ela nao fala com a API, e
            // exigir token faria a conferencia falhar em ambiente que ainda nao
            // tem credencial — justamente quando conferir e mais util.
            // Ambiente do EMISSOR (fallback no global). Alem do aviso de valor
            // fiscal, e ele que escolhe a sequencia do numero da NF-e mostrada
            // na previa — no ambiente errado, o numero exibido seria de outra
            // serie.
            $ambiente = trim((string) $objEmissor->getAmbiente());
            if ($ambiente === '') {
                $ambiente = FocusNFeClient::ambienteConfigurado();
            }

            $objNota = $this->montarNotaDaVenda($objPedido, $objEmissor, $itens, $ambiente, $codEvento);

            // Na previa o numero e o PREVISTO, nao reservado — so a emissao
            // consome a sequencia.
            $objNota->setSerieNfe($objEmissor->getSerieNfe());
            $objNota->setNumeroNfe($objEmissor->getProximoNumeroNfeDoAmbiente($ambiente));

            $payload = NotaFiscalPayloadNFe::montar($objEmissor, $objNota, $itens,
                                                    $this->dadosDoCanal($codEvento));

            $linhas = array();
            foreach ($payload['items'] as $linha) {
                $linhas[] = array(
                    'numero'    => $linha['numero_item'],
                    'codigo'    => $linha['codigo_produto'],
                    'descricao' => $linha['descricao'],
                    'ncm'       => $linha['codigo_ncm'],
                    'cfop'      => $linha['cfop'],
                    'origem'    => $linha['icms_origem'],
                    'csosn'     => isset($linha['icms_situacao_tributaria']) ? $linha['icms_situacao_tributaria'] : '',
                    'unidade'   => isset($linha['unidade_comercial']) ? $linha['unidade_comercial'] : '',
                    'quantidade' => number_format((float) $linha['quantidade_comercial'], 3, ',', '.'),
                    'valor'      => number_format((float) $linha['valor_bruto'], 2, ',', '.')
                );
            }

            $this->responder(true, '', array(
                'ambiente' => $ambiente,
                'emissor'  => array(
                    'razaoSocial'   => $objEmissor->getRazaoSocial(),
                    'cnpj'          => $objEmissor->getCnpj(),
                    'ie'            => $objEmissor->getInscricaoEstadual(),
                    'serie'         => $objEmissor->getSerieNfe(),
                    'proximoNumero' => $objEmissor->getProximoNumeroNfeDoAmbiente($ambiente),
                    'crt'           => $objEmissor->getCodigoRegimeTributario()
                ),
                'destinatario' => array(
                    'nome'      => $objNota->getTomadorNome(),
                    'documento' => $objNota->getTomadorCpfCnpj(),
                    'endereco'  => $this->enderecoDaPrevia($objNota),
                    'cidade'    => $objNota->getTomadorCidadeNome(),
                    'uf'        => $objNota->getTomadorUf(),
                    'cep'       => $objNota->getTomadorCep(),
                    'municipioIbge' => $objNota->getTomadorCodigoMunicipio()
                ),
                'operacao' => array(
                    'natureza' => $objNota->getNaturezaOperacao(),
                    'cfop'     => $objNota->getCfop(),
                    'total'    => number_format((float) $objNota->getValorTotalNfe(), 2, ',', '.')
                ),
                'itens'   => $linhas,
                'avisos'  => $this->avisosDaPrevia($objNota, $objEmissor, $payload),
                'payload' => $payload
            ));

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage());
        }
    }

    private function enderecoDaPrevia(NotaFiscal $objNota) {
        $partes = array();
        foreach (array($objNota->getTomadorLogradouro(), $objNota->getTomadorNumero(),
                       $objNota->getTomadorBairro()) as $parte) {
            $parte = trim((string) $parte);
            if ($parte !== '') {
                $partes[] = $parte;
            }
        }
        return implode(', ', $partes);
    }

    /**
     * O que a SEFAZ costuma rejeitar, visto ANTES do envio. Nao impede emitir —
     * quem decide e o gestor —, mas ninguem deveria emitir sem ver.
     */
    private function avisosDaPrevia(NotaFiscal $objNota, NotaFiscalEmissor $objEmissor, array $payload) {
        $avisos = array();

        $doc = preg_replace('/[^0-9]/', '', (string) $objNota->getTomadorCpfCnpj());
        if ($doc == '') {
            $avisos[] = "Destinatario sem CPF/CNPJ.";
        } elseif (strlen($doc) != 11 && strlen($doc) != 14) {
            $avisos[] = "CPF/CNPJ do destinatario tem tamanho invalido.";
        }
        if (trim((string) $objNota->getTomadorCidadeNome()) == '') {
            $avisos[] = "Destinatario sem cidade — a NF-e identifica o municipio pelo nome.";
        }
        if (trim((string) $objNota->getTomadorUf()) == '') {
            $avisos[] = "Destinatario sem UF: sem ela o CFOP nao distingue dentro e fora do estado.";
        }
        if (preg_replace('/[^0-9]/', '', (string) $objNota->getTomadorCep()) == '') {
            $avisos[] = "Destinatario sem CEP.";
        }
        if (trim((string) $objNota->getTomadorLogradouro()) == '') {
            $avisos[] = "Destinatario sem logradouro.";
        }
        if (trim((string) $objNota->getCfop()) == '') {
            $avisos[] = "CFOP nao resolvido: o emissor nao tem o CFOP cadastrado para este destino.";
        }
        if ((float) $objNota->getValorTotalNfe() <= 0) {
            $avisos[] = "Valor total da nota e zero.";
        }
        if (!isset($payload['formas_pagamento'])) {
            $avisos[] = "Emissor sem forma de pagamento (tPag) configurada — o grupo de pagamento "
                      . "e obrigatorio na NF-e.";
        }

        // Os dois pontos que o contador ainda precisa confirmar. Aparecem na
        // previa porque foram herdados do sistema antigo sem conferencia.
        if ((int) $objEmissor->getIndicadorIntermediador() === 0) {
            $avisos[] = "Operacao declarada SEM intermediador (indIntermed = 0). "
                      . "Venda por marketplace normalmente declara 1 — confirme com o contador.";
        }
        if (trim((string) $objEmissor->getTipoPagamento()) === '01') {
            $avisos[] = "Forma de pagamento declarada como dinheiro (tPag = 01) numa venda "
                      . "paga pela plataforma — confirme com o contador.";
        }

        return $avisos;
    }

    /**
     * Emissao de verdade. Reserva numero, grava a nota e chama a API.
     *
     * Exige confirmacao explicita vinda da previa: emitir e irreversivel do
     * lado da SEFAZ (desfazer e cancelamento, com prazo e justificativa), entao
     * nao pode acontecer por um clique perdido na listagem.
     */
    public function emitirNfe($fws_, $debug = false) {
        try {
            $codEvento = COD_EVENTO;
            $pedidoId = isset($fws_['pedidoId']) ? (int) $fws_['pedidoId'] : 0;

            if (empty($fws_['confirmar'])) {
                throw new Exception("Emissao nao confirmada. Abra a previa e confirme antes de emitir.");
            }

            $objPedido  = $this->carregarPedidoDaVenda($pedidoId, $codEvento);
            $objEmissor = $this->carregarEmissorNfe($codEvento);
            $itens      = $this->carregarItensParaNota($pedidoId, $debug);

            // Idempotencia do nosso lado: venda que ja tem nota valida nao emite
            // de novo. A ref idempotente da Focus e a segunda barreira.
            $objExistente = new NotaFiscal();
            $objExistente->setOrigem('PEDIDO');
            $objExistente->setOrigemId($objPedido->getId());
            if ($objExistente->getNotaVigenteByOrigem()) {
                throw new Exception("Esta venda ja possui nota fiscal ("
                    . $objExistente->getDescricaoStatus() . "). Cancele a atual antes de emitir outra.");
            }

            // Ambiente do EMISSOR: permite testar um gestor em homologacao
            // enquanto os demais seguem emitindo em producao. Vazio/NULL cai
            // no global do env.php (comportamento antigo).
            $client = new FocusNFeClient($objEmissor->getAmbiente());
            $ambiente = $client->getAmbiente();

            $objNota = $this->montarNotaDaVenda($objPedido, $objEmissor, $itens, $ambiente, $codEvento);
            $objNota->setReferencia(NotaFiscal::montarReferencia('NFE', $objPedido->getId()));

            // Numero reservado ANTES de montar o payload e gravado na nota.
            // Consumido mesmo em rejeicao: buraco na sequencia e aceitavel,
            // numero repetido na SEFAZ nao e.
            $numeroNfe = $objEmissor->reservarNumeroNfe($ambiente, $debug);
            $objNota->setSerieNfe($objEmissor->getSerieNfe());
            $objNota->setNumeroNfe($numeroNfe);

            $payload = NotaFiscalPayloadNFe::montar($objEmissor, $objNota, $itens,
                                                    $this->dadosDoCanal($codEvento));
            $objNota->setRequestJson(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // Grava antes de chamar a API: se a chamada cair no meio, o registro
            // existe e a consulta de status recupera o que aconteceu.
            $objNota->gravar($debug);

            $resposta = $client->emitirNfe($objNota->getReferencia(), $payload);
            $objNota->aplicarRespostaFocus($resposta);
            $objNota->atualizarRetorno($debug);

            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL', $objNota->getCodNotaFiscal(), 'EMITIR_NFE',
                    'venda=' . $objPedido->getId() . '; ref=' . $objNota->getReferencia());
            } catch (Exception $ignore) {}

            if ($objNota->getStatus() == 'ERRO') {
                $this->responder(false, "A nota foi rejeitada: " . $objNota->getMensagemErro(),
                    array('codNotaFiscal' => $objNota->getCodNotaFiscal(), 'status' => $objNota->getStatus()));
                return;
            }

            $msg = $objNota->getStatus() == 'AUTORIZADA'
                 ? "NF-e autorizada com sucesso."
                 : "NF-e enviada. A SEFAZ esta processando — consulte o status em alguns segundos.";

            $this->responder(true, $msg, array(
                'codNotaFiscal' => $objNota->getCodNotaFiscal(),
                'status'        => $objNota->getStatus(),
                'numero'        => $objNota->getNumero(),
                'chave'         => $objNota->getChaveNfe()
            ));

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage());
        }
    }

    /**
     * Carga da planilha. O arquivo NAO fica no servidor: e lido do temporario
     * do upload e descartado — a fonte da verdade passa a ser o banco.
     */
    public function importarPlanilha($fws_, $debug = false) {
        $ok = false;
        $mensagem = '';
        $resumo = null;

        try {
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] != UPLOAD_ERR_OK) {
                throw new Exception("Nenhum arquivo recebido. Selecione a planilha de pedidos da Shopee.");
            }

            $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
            if (!in_array($extensao, array('xlsx', 'xls', 'csv', 'txt'))) {
                throw new Exception("Formato nao suportado: ." . $extensao . ". Envie .xlsx, .xls ou .csv.");
            }

            $codGestor = $this->getCodGestorDoEvento($debug);
            if (!$codGestor) {
                throw new Exception("Este canal nao tem emissor de nota configurado — "
                    . "sem ele nao da para saber de qual gestor e o catalogo de produtos. "
                    . "Cadastre o emissor em Vendas > Emissores de NF.");
            }

            $importador = new ShopeeImportadorPlanilha(COD_EVENTO, $codGestor);
            $resumo = $importador->importar($_FILES['arquivo']['tmp_name'], $debug);

            $ok = true;
            $mensagem = sprintf(
                "Carga concluida: %d linha(s) lidas, %d venda(s) nova(s), %d atualizada(s), "
                . "%d item(ns), %d cliente(s) novo(s), %d produto(s) criado(s) como incompleto(s).",
                $resumo['linhas'], $resumo['pedidosNovos'], $resumo['pedidosAtualizados'],
                $resumo['itens'], $resumo['clientesNovos'], $resumo['produtosNovos']);
        } catch (Exception $ex) {
            $ok = false;
            $mensagem = $ex->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $mensagem,
            'avisos'        => $resumo ? $resumo['avisos'] : array(),
            'erros'         => $resumo ? $resumo['erros'] : array()
        ), JSON_UNESCAPED_UNICODE);
    }

}
