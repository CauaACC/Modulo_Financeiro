<?php

/**
 * Item do pedido. Snapshot do que foi vendido — o vinculo com fin_produtos
 * e opcional e serve para puxar o dado FISCAL (NCM, origem, unidade) na hora
 * de montar a nota; o que foi vendido e por quanto fica gravado aqui.
 */
class PedidoItem extends PedidoItemVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new PedidoItemDAO();
    }

    public function getObjDAO() {
        return $this->objDAO;
    }

    public function listarPorPedido($pedidoId, $debug = false) {
        return $this->objDAO->listarPorPedido($pedidoId, $debug);
    }

    public function gravar($debug = false) {
        $this->validatorGravar();

        // Total do item = o que vale na nota. Quando nao vier calculado da
        // origem, deriva de quantidade x unitario menos desconto do vendedor.
        if ($this->isNullOrEmpty($this->getValorTotal())) {
            $total = ((float) $this->getQuantidade() * (float) $this->getValorUnitario())
                   - (float) $this->getValorDesconto();
            $this->setValorTotal(round($total, 2));
        }

        if ($this->getCodItem()) {
            if (!$this->objDAO->update($this, $debug)) {
                throw new Exception("Item do pedido nao foi alterado.");
            }
            return true;
        }

        if (!$this->objDAO->insert($this, $debug)) {
            throw new Exception("Item do pedido nao foi gravado.");
        }
        return true;
    }

    /**
     * O produto do catalogo, quando o item ja casou com um SKU.
     * @return Produto|null
     */
    public function getProduto($debug = false) {
        if ($this->isNullOrEmpty($this->getCodProduto())) {
            return null;
        }
        $obj = new Produto();
        $obj->setCodProduto($this->getCodProduto());
        return $obj->getProdutoById($debug) ? $obj : null;
    }

    /**
     * Item so entra na nota se tiver produto casado E o produto estiver
     * completo — e o produto que carrega NCM/origem/unidade.
     */
    public function pendenciaParaNota($debug = false) {
        $produto = $this->getProduto($debug);
        if ($produto === null) {
            return "SKU '" . $this->getSku() . "' nao esta no catalogo de produtos.";
        }
        if (!$produto->estaCompleto()) {
            return "Produto '" . $produto->getSku() . "' esta incompleto (falta NCM, unidade ou origem).";
        }
        return '';
    }

}
