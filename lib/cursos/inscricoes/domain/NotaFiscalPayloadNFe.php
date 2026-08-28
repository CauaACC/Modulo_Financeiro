<?php

/**
 * Traduz nosso emissor + nossa nota + os itens do pedido para o JSON do
 * endpoint /v2/nfe (NF-e modelo 55) da Focus NFe.
 *
 * Irmao do NotaFiscalPayloadNacional, que faz o mesmo para a NFS-e. Sao
 * classes separadas de proposito: os dois universos nao compartilham nem
 * nomes de campo nem regras — juntar viraria um emaranhado de "if e servico".
 *
 * PURA: nao consulta banco. Recebe tudo pronto para poder ser testada e para
 * que a previa e a emissao usem exatamente o mesmo caminho — se fossem
 * caminhos diferentes, a previa mostraria uma coisa e a nota sairia outra.
 *
 * Mapeamento com o XML da NF-e:
 *   natOp     -> natureza_operacao        indPres   -> presenca_comprador
 *   mod/serie -> serie (do emissor)       indFinal  -> consumidor_final
 *   CRT       -> regime_tributario_emitente
 *   indIEDest -> indicador_inscricao_estadual_destinatario
 *   NCM       -> codigo_ncm               CFOP      -> cfop
 *   orig      -> icms_origem              CSOSN     -> icms_situacao_tributaria
 *   modFrete  -> modalidade_frete         infCpl    -> informacoes_adicionais_contribuinte
 */
class NotaFiscalPayloadNFe {

    /**
     * @param array $itens lista de array('item' => PedidoItem, 'produto' => Produto)
     *        Produto e obrigatorio: e dele que vem NCM, origem e unidade. Item
     *        sem produto completo nao chega aqui — a validacao barra antes.
     * @param array $canal dados do canal de venda, quando houver intermediador:
     *        'cnpj_intermediador' e 'id_vendedor'. Vem do canal e nao do
     *        emissor porque o mesmo CNPJ pode vender em varias plataformas.
     */
    public static function montar(NotaFiscalEmissor $emissor, NotaFiscal $nota, array $itens,
                                  array $canal = array()) {

        if (empty($itens)) {
            throw new Exception("Nota de venda sem itens — nao ha o que faturar.");
        }

        $payload = array();

        // -------- Identificacao da operacao --------
        $payload['natureza_operacao'] = self::limitar($nota->getNaturezaOperacao(), 60);
        $payload['data_emissao'] = date('Y-m-d\TH:i:sP');

        // tpNF 1 = saida. Venda sempre sai; entrada (devolucao) e outro fluxo.
        $payload['tipo_documento'] = 1;
        $payload['finalidade_emissao'] = 1; // finNFe 1 = normal

        // idDest: 1 = mesma UF, 2 = outra UF. Deriva do destino, nao se cadastra.
        $payload['local_destino'] = self::localDestino($emissor->getUf(), $nota->getTomadorUf());

        $payload['consumidor_final']   = (int) $emissor->getIndicadorConsumidorFinal();
        $payload['presenca_comprador'] = (int) $emissor->getIndicadorPresenca();

        // Numeracao: quem numera somos nos, serie propria do emissor. Enquanto
        // o sistema antigo estiver vivo na serie dele, esta e outra — nunca dois
        // emissores na MESMA serie ao mesmo tempo.
        if (trim((string) $nota->getSerieNfe()) != '' && $nota->getNumeroNfe()) {
            $payload['serie']  = (string) $nota->getSerieNfe();
            $payload['numero'] = (string) $nota->getNumeroNfe();
        }

        // -------- Emitente --------
        // So o que IDENTIFICA e o que e fiscal. Endereco, razao social e
        // municipio a Focus tira do cadastro dela — que e o mesmo que assina
        // com o certificado. Mandar daqui abriria caminho para a nota sair com
        // um endereco e o certificado dizer outro.
        $payload['cnpj_emitente'] = self::digitos($emissor->getCnpj());

        // IE do emitente: a NF-e exige, a NFS-e nem conhece o campo. Emissor sem
        // IE nao passa pelo validador, mas o payload nao inventa valor.
        if (trim((string) $emissor->getInscricaoEstadual()) != '') {
            $payload['inscricao_estadual_emitente'] = self::digitos($emissor->getInscricaoEstadual());
        }
        $payload['regime_tributario_emitente'] = (int) $emissor->getCodigoRegimeTributario();

        // -------- Destinatario --------
        // "Tomador" no jargao da NFS-e, "destinatario" no da NF-e: mesmo papel,
        // por isso reusa os campos tomador_* da nota.
        $doc = self::digitos($nota->getTomadorCpfCnpj());
        if (strlen($doc) == 14) {
            $payload['cnpj_destinatario'] = $doc;
        } elseif ($doc != '') {
            $payload['cpf_destinatario'] = $doc;
        }
        $payload['nome_destinatario'] = self::limitar($nota->getTomadorNome(), 60);

        // indIEDest 9 = nao contribuinte. E o caso do consumidor final que
        // compra no marketplace; contribuinte exigiria a IE dele.
        $payload['indicador_inscricao_estadual_destinatario'] = (int) $emissor->getIndicadorIeDestinatario();

        $payload['logradouro_destinatario'] = self::limitar($nota->getTomadorLogradouro(), 60);
        $payload['numero_destinatario']     = self::numeroOuSemNumero($nota->getTomadorNumero());
        $payload['bairro_destinatario']     = self::limitar($nota->getTomadorBairro(), 60);
        $payload['municipio_destinatario']  = self::limitar($nota->getTomadorCidadeNome(), 60);
        $payload['uf_destinatario']         = strtoupper(trim((string) $nota->getTomadorUf()));
        $payload['cep_destinatario']        = self::digitos($nota->getTomadorCep());
        $payload['pais_destinatario']       = 'Brasil';

        if (trim((string) $nota->getTomadorComplemento()) != '') {
            $payload['complemento_destinatario'] = self::limitar($nota->getTomadorComplemento(), 60);
        }
        // Telefone da Shopee vem mascarado ("******54"): sem digito suficiente,
        // nao mandamos — campo opcional que so serviria para a SEFAZ rejeitar.
        $fone = self::digitos($nota->getTomadorTelefone());
        if (strlen($fone) >= 8) {
            $payload['telefone_destinatario'] = $fone;
        }
        if (trim((string) $nota->getTomadorEmail()) != '') {
            $payload['email_destinatario'] = trim($nota->getTomadorEmail());
        }

        // -------- Itens --------
        $cfop = $emissor->getCfopParaUf($nota->getTomadorUf());
        $payload['items'] = array();
        $totalProdutos = 0.0;
        $numero = 0;

        foreach ($itens as $par) {
            $numero++;
            $linha = self::montarItem($emissor, $par['item'], $par['produto'], $cfop, $numero);
            $payload['items'][] = $linha;
            $totalProdutos += $linha['valor_bruto'];
        }

        $totalProdutos = round($totalProdutos, 2);

        // -------- Totais --------
        // O frete pago pelo comprador ao marketplace NAO entra: quem presta o
        // transporte e a plataforma, nao o vendedor. As taxas (comissao,
        // servico, transacao) sao despesa do vendedor e tambem ficam de fora.
        // A nota e pelo que o comprador pagou de MERCADORIA.
        $payload['valor_produtos'] = $totalProdutos;
        $payload['valor_total']    = $totalProdutos;
        $payload['valor_frete']    = 0;
        $payload['valor_seguro']   = 0;
        $payload['valor_desconto'] = 0;
        $payload['valor_outras_despesas'] = 0;

        // modFrete 9 = sem ocorrencia de transporte.
        $payload['modalidade_frete'] = (int) $emissor->getModalidadeFrete();

        // -------- Blocos com nome de campo A CONFIRMAR --------
        // A documentacao publica resumida da Focus nao expoe o schema destes
        // tres blocos. Os nomes abaixo sao os do dicionario de campos deles,
        // mas so a primeira emissao em homologacao confirma. Ficam isolados
        // aqui para que um ajuste seja de uma linha, e nao uma caca no arquivo.
        self::aplicarInformacoesComplementares($payload, $emissor);
        self::aplicarPagamento($payload, $emissor, $totalProdutos);
        self::aplicarIntermediador($payload, $emissor, $canal);

        return $payload;
    }

    /**
     * O item e onde a nota de mercadoria se decide: NCM, origem e unidade vem
     * do CADASTRO do produto (decisao fiscal do contador), quantidade e valor
     * vem do PEDIDO (o que o comprador realmente pagou).
     */
    private static function montarItem(NotaFiscalEmissor $emissor, PedidoItem $item, Produto $produto,
                                       $cfop, $numeroItem) {
        $quantidade = round((float) $item->getQuantidade(), 4);
        $valorTotal = round((float) $item->getValorTotal(), 2);

        // Unitario derivado do total, e nao o contrario: o total e o que fecha
        // com o pedido e com o que a plataforma cobrou. Recalcular o total a
        // partir de um unitario arredondado produz centavos de diferenca.
        $unitario = ($quantidade > 0) ? round($valorTotal / $quantidade, 10) : 0;

        $linha = array(
            'numero_item'              => $numeroItem,
            'codigo_produto'           => self::limitar($produto->getSku(), 60),
            'descricao'                => self::limitar(self::descricaoDoItem($item, $produto), 120),
            'cfop'                     => (string) $cfop,
            'codigo_ncm'               => self::digitos($produto->getNcm()),
            'unidade_comercial'        => self::limitar($produto->getUnidade(), 6),
            'quantidade_comercial'     => $quantidade,
            'valor_unitario_comercial' => $unitario,
            'unidade_tributavel'        => self::limitar($produto->getUnidade(), 6),
            'quantidade_tributavel'     => $quantidade,
            'valor_unitario_tributavel' => $unitario,
            'valor_bruto'              => $valorTotal,
            'inclui_no_total'          => 1,
        );

        // Sem GTIN cadastrado, a NF-e exige literalmente "SEM GTIN" — deixar
        // vazio e rejeicao certa. Foi o que as notas do sistema antigo usavam.
        $linha['codigo_barras_comercial']  = 'SEM GTIN';
        $linha['codigo_barras_tributavel'] = 'SEM GTIN';

        // orig varia por PRODUTO (importacao direta x adquirido no mercado
        // interno), por isso nao e default do emissor.
        $linha['icms_origem'] = (int) $produto->getOrigem();

        // CSOSN do produto sobrescreve o do emissor: a exceção mora no item,
        // o padrao no emitente.
        $csosn = trim((string) $produto->getCsosn());
        if ($csosn === '') {
            $csosn = trim((string) $emissor->getCsosnPadrao());
        }
        $linha['icms_situacao_tributaria'] = $csosn;

        $cst = trim((string) $emissor->getCstPisCofins());
        if ($cst !== '') {
            $linha['pis_situacao_tributaria']    = $cst;
            $linha['cofins_situacao_tributaria'] = $cst;
        }

        // CEST so existe quando ha substituicao tributaria.
        if (trim((string) $produto->getCest()) != '') {
            $linha['cest'] = self::digitos($produto->getCest());
        }

        return $linha;
    }

    /**
     * A descricao que sai na nota e a do PEDIDO quando existe (ja vem com a
     * variacao: "...RS-H019 110v"), com o cadastro do produto como reserva.
     */
    private static function descricaoDoItem(PedidoItem $item, Produto $produto) {
        $descricao = trim((string) $item->getDescricao());
        if ($descricao !== '') {
            return $descricao;
        }
        return (string) $produto->getDescricao();
    }

    /**
     * infCpl — texto fixo do emitente (dizeres do Simples Nacional, etc).
     * NOME DO CAMPO A CONFIRMAR na primeira emissao em homologacao.
     */
    private static function aplicarInformacoesComplementares(array &$payload, NotaFiscalEmissor $emissor) {
        $texto = trim((string) $emissor->getInformacoesComplementaresNfe());
        if ($texto !== '') {
            $payload['informacoes_adicionais_contribuinte'] = self::limitar($texto, 2000);
        }
    }

    /**
     * Grupo de pagamento (tPag/vPag). Obrigatorio na NF-e desde a NT2016.002.
     * NOME DOS CAMPOS A CONFIRMAR na primeira emissao em homologacao.
     *
     * O valor declarado e o total da nota — nao o que o comprador pagou a
     * plataforma (que inclui frete) nem o liquido repassado ao vendedor.
     */
    private static function aplicarPagamento(array &$payload, NotaFiscalEmissor $emissor, $total) {
        $tipo = trim((string) $emissor->getTipoPagamento());
        if ($tipo === '') {
            return;
        }
        $payload['formas_pagamento'] = array(array(
            'forma_pagamento' => $tipo,
            'valor_pagamento' => round((float) $total, 2),
        ));
    }

    /**
     * indIntermed + grupo infIntermed. So vai quando o emissor declara que a
     * venda tem intermediador (marketplace) — e ai a SEFAZ exige o CNPJ da
     * plataforma e o identificador do vendedor nela.
     * NOME DOS CAMPOS A CONFIRMAR na primeira emissao em homologacao.
     */
    private static function aplicarIntermediador(array &$payload, NotaFiscalEmissor $emissor, array $canal) {
        $indicador = $emissor->getIndicadorIntermediador();
        if ($indicador === null || $indicador === '') {
            return;
        }

        $payload['indicador_intermediario'] = (int) $indicador;

        if ((int) $indicador !== 1) {
            return;
        }

        // Declarou que ha intermediador: sem CNPJ e identificador do vendedor
        // a SEFAZ rejeita. Preferimos falhar aqui, com mensagem clara, a
        // mandar um grupo pela metade e receber um codigo de rejeicao.
        $cnpj = self::digitos(isset($canal['cnpj_intermediador']) ? $canal['cnpj_intermediador'] : '');
        $idVendedor = trim((string) (isset($canal['id_vendedor']) ? $canal['id_vendedor'] : ''));

        if ($cnpj === '' || $idVendedor === '') {
            throw new Exception("O emissor esta configurado com intermediador (indIntermed = 1), "
                . "mas o canal de venda nao tem o CNPJ do marketplace ou o identificador do "
                . "vendedor na plataforma. Complete a configuracao do canal ou volte o "
                . "indicador do emissor para 0.");
        }

        $payload['cnpj_intermediario'] = $cnpj;
        $payload['id_vendedor_marketplace'] = self::limitar($idVendedor, 60);
    }

    /**
     * idDest: 1 = operacao interna (mesma UF), 2 = interestadual.
     * 3 (exterior) nao se aplica ao fluxo atual.
     */
    private static function localDestino($ufEmitente, $ufDestino) {
        $ufEmitente = strtoupper(trim((string) $ufEmitente));
        $ufDestino  = strtoupper(trim((string) $ufDestino));

        if ($ufEmitente !== '' && $ufDestino !== '' && $ufEmitente === $ufDestino) {
            return 1;
        }
        return 2;
    }

    /**
     * A NF-e nao aceita numero vazio no endereco; "S/N" e a convencao do
     * documento fiscal. Mesmo tratamento que a NFS-e ja faz.
     */
    private static function numeroOuSemNumero($numero) {
        $numero = trim((string) $numero);
        return ($numero !== '') ? self::limitar($numero, 60) : 'S/N';
    }

    private static function digitos($valor) {
        return preg_replace('/[^0-9]/', '', (string) $valor);
    }

    private static function limitar($texto, $tamanho) {
        $texto = trim((string) $texto);
        if (function_exists('mb_substr')) {
            return mb_substr($texto, 0, $tamanho, 'UTF-8');
        }
        return substr($texto, 0, $tamanho);
    }

}
