<?php

/**
 * Produto do catalogo de um gestor (venda de mercadoria).
 *
 * O saldo de estoque mora aqui (estoque_atual) e o historico em
 * fin_estoque_movimentos — ver EstoqueMovimento.
 */
class Produto extends ProdutoVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new ProdutoDAO();
    }

    public function getObjDAO() {
        return $this->objDAO;
    }

    public function getProdutoById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function getProdutoBySku($codGestor, $sku, $debug = false) {
        return $this->objDAO->getBySku($this, $codGestor, $sku, $debug);
    }

    public function listarProdutos($codsGestor = null, $filtros = array(), $debug = false) {
        return $this->objDAO->listar($codsGestor, $filtros, $debug);
    }

    public function gravar($debug = false) {
        $this->validatorGravar();

        $this->setSku(trim((string) $this->getSku()));
        $this->setNcm(preg_replace('/[^0-9]/', '', (string) $this->getNcm()));

        // Incompleto e DERIVADO, nunca digitado: e o proprio conteudo do
        // cadastro que diz se da para emitir nota. Marcar na mao daria
        // divergencia entre o flag e a realidade.
        $this->setFlgIncompleto($this->estaCompleto() ? 0 : 1);

        if ($this->objDAO->existeSkuDuplicado($this, $debug)) {
            throw new Exception("Ja existe um produto com o SKU '" . $this->getSku()
                . "' neste gestor. Edite o cadastro existente.");
        }

        if ($this->getCodProduto()) {
            if (!$this->objDAO->update($this, $debug)) {
                throw new Exception("Produto nao foi alterado.");
            }
            throw new OKException("Produto alterado com sucesso.");
        }

        if (!$this->objDAO->insert($this, $debug)) {
            throw new Exception("Produto nao foi cadastrado.");
        }
        throw new OKException("Produto cadastrado com sucesso.");
    }

    /**
     * Cria o produto a partir do que o marketplace manda (SKU + nome), ja
     * marcado como incompleto. E o caminho que evita pre-cadastro de catalogo:
     * o gestor so completa o que faltar, uma vez por produto novo.
     *
     * Idempotente pelo SKU — reimportar a mesma venda nao duplica produto.
     *
     * @return Produto o produto existente ou o recem-criado
     */
    public static function criarOuObterPorSku($codGestor, $sku, $descricao, $debug = false) {
        $obj = new Produto();
        if ($obj->getProdutoBySku($codGestor, $sku, $debug)) {
            return $obj;
        }

        $novo = new Produto();
        $novo->setCodGestor($codGestor);
        $novo->setSku($sku);
        $novo->setDescricao($descricao);
        $novo->setStatus('ATIVADA');
        try {
            $novo->gravar($debug);
        } catch (OKException $ex) {
            // gravar() sinaliza sucesso por excecao (padrao do projeto).
        }
        return $novo;
    }

    /**
     * Completo = tem o que a NF-e exige do item. Nao inclui preco: o valor da
     * nota vem do PEDIDO, que e o que o comprador pagou.
     */
    public function estaCompleto() {
        return !$this->isNullOrEmpty($this->getNcm())
            && !$this->isNullOrEmpty($this->getUnidade())
            && !$this->isNullOrEmpty($this->getOrigem());
    }

    /**
     * Registra movimento e atualiza o saldo. Ver EstoqueMovimento::registrar
     * para o porque do saldo relativo.
     */
    public function movimentarEstoque($tipo, $quantidade, $opcoes = array(), $debug = false) {
        if (!$this->getCodProduto()) {
            throw new Exception("Produto nao identificado para movimentar estoque.");
        }
        return EstoqueMovimento::registrar($this->getCodProduto(), $tipo, $quantidade, $opcoes, $debug);
    }

    public function abaixoDoMinimo() {
        $minimo = $this->getEstoqueMinimo();
        if ($this->isNullOrEmpty($minimo)) {
            return false;
        }
        return (float) $this->getEstoqueAtual() <= (float) $minimo;
    }

    public function getDescricaoAmigavel() {
        return trim((string) $this->getSku()) . ' — ' . trim((string) $this->getDescricao());
    }

}
