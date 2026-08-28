<?php

/**
 * Carga das vendas da Shopee a partir da planilha de pedidos.
 *
 * O objetivo do modulo inteiro e ACABAR COM A REDIGITACAO: a planilha ja traz
 * cliente, endereco, CPF, item e valores. Aqui isso vira pedido + itens + dados
 * do canal, com o minimo de decisao humana — e o que sobrar de duvida aparece
 * no semaforo da tela em vez de ser adivinhado.
 *
 * IDEMPOTENTE: a chave e canal + ID do pedido na Shopee. Reimportar a mesma
 * planilha atualiza a venda existente, nunca duplica.
 */
class ShopeeImportadorPlanilha {

    const CANAL = 'SHOPEE';

    /**
     * Cabecalhos que interessam. A planilha REPETE nomes ("Cidade" aparece
     * duas vezes, "Desconto do vendedor" tambem), com a primeira ocorrencia
     * vazia — por isso a resolucao escolhe a coluna que TEM dado, e nao a
     * primeira que casa pelo nome.
     */
    private static $mapa = array(
        'idExterno'      => 'ID do pedido',
        'statusExterno'  => 'Status do pedido',
        'dataPedido'     => 'Data de criacao do pedido',
        'dataPagamento'  => 'Hora do pagamento do pedido',
        'dataEnvio'      => 'Data prevista de envio',
        'rastreio'       => 'Numero de rastreamento',
        'opcaoEnvio'     => 'Opcao de envio',
        'skuPrincipal'   => 'N de referencia do SKU principal',
        'produto'        => 'Nome do Produto',
        'sku'            => 'Numero de referencia SKU',
        'variacao'       => 'Nome da variacao',
        'precoAcordado'  => 'Preco acordado',
        'quantidade'     => 'Quantidade',
        'subtotal'       => 'Subtotal do produto',
        'descontoVend'   => 'Desconto do vendedor',
        'valorPago'      => 'Valor Total',
        'freteComprador' => 'Taxa de envio pagas pelo comprador',
        'freteEstimado'  => 'Valor estimado do frete',
        'taxaTransacao'  => 'Taxa de transacao',
        'taxaComissao'   => 'Taxa de comissao liquida',
        'taxaServico'    => 'Taxa de servico liquida',
        'totalGlobal'    => 'Total global',
        'nome'           => 'Nome do destinatario',
        'telefone'       => 'Telefone',
        'cpf'            => 'CPF do Comprador',
        'endereco'       => 'Endereco de entrega',
        'bairro'         => 'Bairro',
        'cidade'         => 'Cidade',
        'uf'             => 'UF',
        'cep'            => 'CEP'
    );

    private $codEvento;
    private $codGestor;
    private $resumo;

    public function __construct($codEvento, $codGestor) {
        $this->codEvento = (int) $codEvento;
        $this->codGestor = (int) $codGestor;
        $this->resumo = array(
            'linhas' => 0, 'pedidosNovos' => 0, 'pedidosAtualizados' => 0,
            'itens' => 0, 'clientesNovos' => 0, 'produtosNovos' => 0,
            'avisos' => array(), 'erros' => array()
        );
    }

    public function getResumo() {
        return $this->resumo;
    }

    // ------------------------------------------------------------------
    // Leitura
    // ------------------------------------------------------------------

    /**
     * Le a planilha (xlsx/xls/csv). Mesmo caminho ja usado nas cargas de
     * ensalamento e presenca — PhpSpreadsheet fica num vendor isolado.
     */
    private function lerGrade($caminhoArquivo) {
        $extensao = strtolower(pathinfo($caminhoArquivo, PATHINFO_EXTENSION));

        if ($extensao == 'csv' || $extensao == 'txt') {
            $conteudo = file($caminhoArquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (empty($conteudo)) {
                return array();
            }
            $separador = (substr_count($conteudo[0], ',') > substr_count($conteudo[0], ';')) ? ',' : ';';
            $linhas = array();
            foreach ($conteudo as $l) {
                $linhas[] = array_map('trim', str_getcsv(trim($l), $separador, '"'));
            }
            return $linhas;
        }

        $autoload = dirname(__FILE__) . '/../lib/spreadsheet/vendor/autoload.php';
        if (!file_exists($autoload)) {
            throw new Exception("Biblioteca PhpSpreadsheet nao encontrada no servidor.");
        }
        require_once($autoload);

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(
            \PhpOffice\PhpSpreadsheet\IOFactory::identify($caminhoArquivo)
        );
        $grade = $reader->load($caminhoArquivo)->getActiveSheet()->toArray(null, true, true, false);

        $linhas = array();
        foreach ($grade as $linhaArr) {
            $temAlgo = false;
            foreach ($linhaArr as $v) {
                if (trim((string) $v) !== '') { $temAlgo = true; break; }
            }
            if ($temAlgo) {
                $linhas[] = array_map(function ($v) { return trim((string) $v); }, $linhaArr);
            }
        }
        return $linhas;
    }

    /**
     * Resolve campo -> indice da coluna.
     *
     * Casa pelo cabecalho sem acento e sem simbolo, o que sobrevive a variacao
     * de acentuacao entre exportacoes. Quando o mesmo nome aparece em mais de
     * uma coluna, vence a que tem dado nas linhas — foi o caso de "Cidade",
     * duplicada com a primeira ocorrencia vazia.
     */
    private function resolverColunas($grade) {
        $cabecalho = $grade[0];
        $indices = array();

        foreach (self::$mapa as $campo => $titulo) {
            $alvo = $this->chaveCabecalho($titulo);
            $candidatos = array();
            foreach ($cabecalho as $i => $nome) {
                if ($this->chaveCabecalho($nome) === $alvo) {
                    $candidatos[] = $i;
                }
            }
            if (empty($candidatos)) {
                $indices[$campo] = null;
                continue;
            }
            if (count($candidatos) == 1) {
                $indices[$campo] = $candidatos[0];
                continue;
            }

            $escolhido = $candidatos[0];
            foreach ($candidatos as $i) {
                $temDado = false;
                for ($l = 1; $l < count($grade); $l++) {
                    if (isset($grade[$l][$i]) && trim((string) $grade[$l][$i]) !== '') {
                        $temDado = true;
                        break;
                    }
                }
                if ($temDado) {
                    $escolhido = $i;
                    break;
                }
            }
            $indices[$campo] = $escolhido;
        }

        return $indices;
    }

    private function chaveCabecalho($texto) {
        $texto = NormalizadorEndereco::semAcento((string) $texto);
        return preg_replace('/[^A-Z0-9]/', '', $texto);
    }

    private function valor($linha, $indices, $campo) {
        if (!isset($indices[$campo]) || $indices[$campo] === null) {
            return '';
        }
        $i = $indices[$campo];
        return isset($linha[$i]) ? trim((string) $linha[$i]) : '';
    }

    private function numero($linha, $indices, $campo) {
        $v = $this->valor($linha, $indices, $campo);
        if ($v === '') {
            return 0;
        }
        // A planilha usa ponto como decimal ("135.90"). Se vier virgula,
        // ela e o decimal e o ponto e milhar.
        if (strpos($v, ',') !== false) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }
        return (float) preg_replace('/[^0-9.\-]/', '', $v);
    }

    private function dataHora($linha, $indices, $campo) {
        $v = $this->valor($linha, $indices, $campo);
        if ($v === '') {
            return null;
        }
        $ts = strtotime($v);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    // ------------------------------------------------------------------
    // Importacao
    // ------------------------------------------------------------------

    public function importar($caminhoArquivo, $debug = false) {
        $grade = $this->lerGrade($caminhoArquivo);
        if (count($grade) < 2) {
            throw new Exception("A planilha nao tem linhas de dados.");
        }

        $indices = $this->resolverColunas($grade);
        if ($indices['idExterno'] === null) {
            throw new Exception("Nao encontrei a coluna 'ID do pedido' na planilha. "
                . "Confirme que e o relatorio de Pedidos da Shopee.");
        }

        // A planilha e UMA LINHA POR ITEM: pedido com varios itens repete o
        // ID. Sem agrupar, cada item viraria um pedido separado.
        $porPedido = array();
        for ($l = 1; $l < count($grade); $l++) {
            $linha = $grade[$l];
            $id = $this->valor($linha, $indices, 'idExterno');
            if ($id === '') {
                continue;
            }
            if (!isset($porPedido[$id])) {
                $porPedido[$id] = array();
            }
            $porPedido[$id][] = $linha;
            $this->resumo['linhas']++;
        }

        foreach ($porPedido as $idExterno => $linhas) {
            try {
                $this->importarPedido($idExterno, $linhas, $indices, $debug);
            } catch (Exception $ex) {
                $this->resumo['erros'][] = "Pedido " . $idExterno . ": " . $ex->getMessage();
            }
        }

        return $this->resumo;
    }

    private function importarPedido($idExterno, $linhas, $indices, $debug) {
        $primeira = $linhas[0];

        $codInscrito = $this->resolverCliente($primeira, $indices, $idExterno, $debug);

        // Base do item da nota = SUBTOTAL da mercadoria. Frete e taxas ficam
        // em fin_pedidos_marketplace e nao entram na nota: sao do canal, nao
        // da mercadoria.
        $valorPedido = 0;
        foreach ($linhas as $linha) {
            $valorPedido += $this->numero($linha, $indices, 'subtotal');
        }

        $objMkt = new PedidoMarketplace();
        $existe = $objMkt->getByIdExterno(self::CANAL, $idExterno, $debug);

        $descricao = $this->valor($primeira, $indices, 'produto');
        if (count($linhas) > 1) {
            $descricao .= ' (+' . (count($linhas) - 1) . ' itens)';
        }

        if ($existe) {
            $pedidoId = $objMkt->getPedidoId();
            $this->atualizarPedido($pedidoId, $codInscrito, $valorPedido, $descricao, $debug);
            $this->resumo['pedidosAtualizados']++;
        } else {
            $pedidoId = $this->criarPedido($codInscrito, $valorPedido, $descricao, $idExterno, $debug);
            $objMkt->setPedidoId($pedidoId);
            $this->resumo['pedidosNovos']++;
        }

        // Itens: a planilha e a fonte da verdade, entao reimportar SUBSTITUI.
        $objItemDAO = new PedidoItemDAO();
        $objItemDAO->excluirPorPedido($pedidoId, $debug);

        $ordem = 1;
        foreach ($linhas as $linha) {
            $this->gravarItem($pedidoId, $linha, $indices, $ordem++, $debug);
        }

        // Dados do canal
        $objMkt->setCanal(self::CANAL);
        $objMkt->setIdExterno($idExterno);
        $objMkt->setStatusExterno($this->valor($primeira, $indices, 'statusExterno'));
        $objMkt->setValorPagoComprador($this->numero($primeira, $indices, 'valorPago'));
        $objMkt->setValorLiquidoVendedor($this->numero($primeira, $indices, 'totalGlobal'));
        $objMkt->setValorFreteComprador($this->numero($primeira, $indices, 'freteComprador'));
        $objMkt->setValorFreteEstimado($this->numero($primeira, $indices, 'freteEstimado'));
        $objMkt->setTaxaComissao($this->numero($primeira, $indices, 'taxaComissao'));
        $objMkt->setTaxaServico($this->numero($primeira, $indices, 'taxaServico'));
        $objMkt->setTaxaTransacao($this->numero($primeira, $indices, 'taxaTransacao'));
        $objMkt->setDescontoVendedor($this->numero($primeira, $indices, 'descontoVend'));
        $objMkt->setCodigoRastreio($this->valor($primeira, $indices, 'rastreio'));
        $objMkt->setOpcaoEnvio($this->valor($primeira, $indices, 'opcaoEnvio'));
        $objMkt->setDataPedido($this->dataHora($primeira, $indices, 'dataPedido'));
        $objMkt->setDataPagamento($this->dataHora($primeira, $indices, 'dataPagamento'));
        $objMkt->setDataPrevistaEnvio($this->dataHora($primeira, $indices, 'dataEnvio'));
        $objMkt->gravar($debug);
    }

    /**
     * Cliente por CPF dentro do canal. Comprador que volta nao recadastra —
     * e uma das pontas do "acabar com a redigitacao".
     */
    private function resolverCliente($linha, $indices, $idExterno, $debug) {
        $cpf = preg_replace('/[^0-9]/', '', $this->valor($linha, $indices, 'cpf'));
        $nome = $this->valor($linha, $indices, 'nome');

        // Busca e insercao pelo caminho que o sistema ja tem: InscritoDAO
        // (getByCpfExiste = cod_evento + cpf + ATIVADA) e insert(), que cuida
        // de auditoria, id_publico, qr_code e do cod_inscrito (auto_increment).
        if ($cpf !== '') {
            $busca = new Inscrito();
            $busca->setCodEvento($this->codEvento);
            $busca->setCpf($cpf);
            if ($busca->verificarCpfEstaInscrito($debug)) {
                return (int) $busca->getCodInscrito();
            }
        } else {
            $this->resumo['avisos'][] = "Pedido " . $idExterno . ": venda sem CPF — "
                . "cliente cadastrado sem documento, a nota vai exigir o CPF depois.";
        }

        $cidade = $this->valor($linha, $indices, 'cidade');
        $ufNome = $this->valor($linha, $indices, 'uf');
        $uf = NormalizadorEndereco::resolverUf($ufNome);
        if ($uf === '' && $ufNome !== '') {
            $this->resumo['avisos'][] = "Pedido " . $idExterno . ": UF '" . $ufNome . "' nao reconhecida.";
        }

        $end = NormalizadorEndereco::quebrarLogradouro(
            $this->valor($linha, $indices, 'endereco'), $cidade, $ufNome);
        if ($end['incerto']) {
            $this->resumo['avisos'][] = "Pedido " . $idExterno . ": endereco sem numero identificado — conferir antes de emitir.";
        }

        $novo = new Inscrito();
        $novo->setCodEvento($this->codEvento);
        $novo->setCodPerfil(0);            // canal de venda nao tem perfil de inscricao
        $novo->setNomeInscrito($nome);
        $novo->setCpf($cpf);
        // `endereco` e varchar(50) na base: truncar aqui evita "Data too long"
        // derrubar a carga inteira por causa de uma linha.
        $novo->setEndereco(substr((string) $end['logradouro'], 0, 50));
        $novo->setNumero(substr((string) $end['numero'], 0, 10));
        $novo->setComplemento($end['complemento']);
        $novo->setBairro($this->valor($linha, $indices, 'bairro'));
        $novo->setCidade($cidade);
        $novo->setUf($uf);
        $novo->setEstado($uf);
        $novo->setCep(NormalizadorEndereco::limparCep($this->valor($linha, $indices, 'cep')));

        // salvarCarga() sinaliza sucesso por OKException (padrao do projeto) e
        // cuida de auditoria, id_publico, qr_code e do cod_inscrito, que e
        // auto_increment na tabela.
        try {
            $novo->salvarCarga($debug);
        } catch (OKException $ex) {
            // sucesso
        }

        if (!$novo->getCodInscrito()) {
            throw new Exception("Nao foi possivel cadastrar o cliente.");
        }
        $this->resumo['clientesNovos']++;
        return (int) $novo->getCodInscrito();
    }

    /**
     * SKU da variacao. Quando a planilha nao manda SKU nenhum — acontece —,
     * deriva uma chave de nome + variacao para a venda nao ficar de fora.
     * O produto nasce incompleto e o gestor troca pelo SKU real depois.
     */
    private function resolverSku($linha, $indices) {
        $sku = $this->valor($linha, $indices, 'sku');
        if ($sku !== '') {
            return $sku;
        }
        $sku = $this->valor($linha, $indices, 'skuPrincipal');
        if ($sku !== '') {
            return $sku;
        }

        $base = $this->valor($linha, $indices, 'produto') . ' ' . $this->valor($linha, $indices, 'variacao');
        $base = preg_replace('/[^A-Z0-9]/', '', NormalizadorEndereco::semAcento($base));
        if ($base === '') {
            return '';
        }
        return 'AUTO-' . substr($base, 0, 40);
    }

    private function gravarItem($pedidoId, $linha, $indices, $ordem, $debug) {
        $sku = $this->resolverSku($linha, $indices);

        // Descricao ja com a variacao: e ela que sai na nota (foi o que os
        // XMLs reais mostraram — "...RS-H019 110v" e um item, nao um atributo).
        $descricao = trim($this->valor($linha, $indices, 'produto') . ' '
                        . $this->valor($linha, $indices, 'variacao'));

        $codProduto = null;
        if ($sku !== '') {
            $produto = Produto::criarOuObterPorSku($this->codGestor, $sku, $descricao, $debug);
            if ($produto && $produto->getCodProduto()) {
                $codProduto = $produto->getCodProduto();
                if ((int) $produto->getFlgIncompleto() === 1) {
                    $this->resumo['produtosNovos']++;
                }
            }
        }

        $quantidade = $this->numero($linha, $indices, 'quantidade');
        if ($quantidade <= 0) {
            $quantidade = 1;
        }

        $item = new PedidoItem();
        $item->setPedidoId($pedidoId);
        $item->setCodProduto($codProduto);
        $item->setSku($sku);
        $item->setDescricao($descricao);
        $item->setQuantidade($quantidade);
        $item->setValorUnitario($this->numero($linha, $indices, 'precoAcordado'));
        $item->setValorDesconto($this->numero($linha, $indices, 'descontoVend'));
        $item->setValorTotal($this->numero($linha, $indices, 'subtotal'));
        $item->setOrdem($ordem);
        $item->gravar($debug);

        $this->resumo['itens']++;
    }

    // ------------------------------------------------------------------
    // pedidos (tabela core, sem prefixo e sem DAO proprio no modulo)
    // ------------------------------------------------------------------

    private function criarPedido($codInscrito, $valorTotal, $descricao, $idExterno, $debug) {
        $dao = new PedidoMarketplaceDAO();
        $conex = $dao->getConexao();
        $conex->debug = $debug;

        $sql = sprintf("INSERT INTO pedidos
            (created_at, updated_at, evento_id, inscrito_id, referencia, valor_total,
             descricao, status, tipo, versao)
            VALUES (NOW(), NOW(), %d, %d, '%s', %s, '%s', 'ATIVADA', 'VENDA', 1)",
            $this->codEvento, (int) $codInscrito,
            addslashes((string) $idExterno),
            number_format((float) $valorTotal, 2, '.', ''),
            addslashes(substr((string) $descricao, 0, 255)));

        $conex->Execute($sql);
        return (int) $conex->Insert_ID();
    }

    private function atualizarPedido($pedidoId, $codInscrito, $valorTotal, $descricao, $debug) {
        $dao = new PedidoMarketplaceDAO();
        $conex = $dao->getConexao();
        $conex->debug = $debug;

        $sql = sprintf("UPDATE pedidos SET updated_at=NOW(), inscrito_id=%d,
                valor_total=%s, descricao='%s' WHERE id=%d",
            (int) $codInscrito,
            number_format((float) $valorTotal, 2, '.', ''),
            addslashes(substr((string) $descricao, 0, 255)),
            (int) $pedidoId);

        $conex->Execute($sql);
        return true;
    }

}
