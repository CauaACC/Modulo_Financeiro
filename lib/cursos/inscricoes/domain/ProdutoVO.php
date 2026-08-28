<?php

class ProdutoVO extends PadraoVO {

    /**
     * O minimo para o produto EXISTIR. De proposito nao cobra dado fiscal:
     * produto criado pela importacao de vendas nasce so com SKU e nome (e o
     * que o marketplace manda) e precisa poder ser gravado assim, marcado
     * como incompleto. Exigir NCB/NCM aqui obrigaria o gestor a pre-cadastrar
     * catalogo — exatamente o que este modulo veio eliminar.
     */
    public function validatorGravar() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getCodGestor())) {
            $message .= "Gestor nao foi informado.<br>";
            $return = false;
        }

        if ($this->isNullOrEmpty($this->getSku())) {
            $message .= "SKU nao foi informado.<br>";
            $return = false;
        }

        if ($this->isNullOrEmpty($this->getDescricao())) {
            $message .= "Descricao do produto nao foi informada.<br>";
            $return = false;
        }

        $origem = $this->getOrigem();
        if (!$this->isNullOrEmpty($origem) && !in_array((int) $origem, array(0, 1, 2, 3, 4, 5, 6, 7, 8))) {
            $message .= "Origem da mercadoria invalida.<br>";
            $return = false;
        }

        $ncm = preg_replace('/[^0-9]/', '', (string) $this->getNcm());
        if ($ncm != "" && strlen($ncm) != 8) {
            $message .= "NCM deve ter 8 digitos.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    /**
     * O que a NF-e exige do ITEM. Separado do validatorGravar porque o produto
     * pode ficar incompleto no catalogo sem problema — o que nao pode e sair
     * numa nota assim.
     */
    public function validatorAptoParaNota() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getNcm())) {
            $message .= "Produto sem NCM.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getUnidade())) {
            $message .= "Produto sem unidade comercial.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getOrigem())) {
            $message .= "Produto sem origem da mercadoria.<br>";
            $return = false;
        }
        if ($this->getStatus() != 'ATIVADA') {
            $message .= "Produto esta inativo.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception("Produto '" . $this->getSku() . "' nao pode entrar na nota:<br>" . $message);
        }
        return true;
    }

}
