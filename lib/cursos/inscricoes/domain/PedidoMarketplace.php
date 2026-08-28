<?php

/**
 * Dados do canal externo, 1:1 com o pedido.
 *
 * Guarda o que e da plataforma (id, status, taxas, frete, datas) sem repetir
 * cliente nem valor do pedido. As TAXAS ficam aqui justamente para deixar
 * claro que nao entram na nota: sao despesa do vendedor.
 */
class PedidoMarketplace extends PedidoMarketplaceVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new PedidoMarketplaceDAO();
    }

    public function getObjDAO() {
        return $this->objDAO;
    }

    public function getByPedido($pedidoId, $debug = false) {
        return $this->objDAO->getByPedido($this, $pedidoId, $debug);
    }

    public function getByIdExterno($canal, $idExterno, $debug = false) {
        return $this->objDAO->getByIdExterno($this, $canal, $idExterno, $debug);
    }

    public function gravar($debug = false) {
        $this->validatorGravar();

        if ($this->getCodPedidoMarketplace()) {
            if (!$this->objDAO->update($this, $debug)) {
                throw new Exception("Dados do canal nao foram alterados.");
            }
            return true;
        }

        if (!$this->objDAO->insert($this, $debug)) {
            throw new Exception("Dados do canal nao foram gravados.");
        }
        return true;
    }

    /**
     * Soma do que a plataforma reteve. Nao vai para a nota — serve para
     * conferencia de repasse.
     */
    public function getTotalTaxas() {
        return (float) $this->getTaxaComissao()
             + (float) $this->getTaxaServico()
             + (float) $this->getTaxaTransacao();
    }

}
