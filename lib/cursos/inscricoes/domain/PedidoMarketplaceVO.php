<?php

class PedidoMarketplaceVO extends PadraoVO {

    public function validatorGravar() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getPedidoId())) {
            $message .= "Pedido nao foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getCanal())) {
            $message .= "Canal de venda nao foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getIdExterno())) {
            // Sem o id externo a carga perde a idempotencia: reimportar a mesma
            // planilha criaria pedidos repetidos.
            $message .= "Identificador do pedido no canal nao foi informado.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

}
