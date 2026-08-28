<?php

class PedidoItemVO extends PadraoVO {

    public function validatorGravar() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getPedidoId())) {
            $message .= "Pedido nao foi informado no item.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getDescricao())) {
            $message .= "Descricao do item nao foi informada.<br>";
            $return = false;
        }
        if ((float) $this->getQuantidade() <= 0) {
            $message .= "Quantidade do item deve ser maior que zero.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

}
