<?php

class EstoqueMovimentoVO extends PadraoVO {

    /** Tipos aceitos e o sinal que cada um aplica no saldo. */
    public static function tiposValidos() {
        return array(
            'ENTRADA'   => 1,   // compra, importacao, producao
            'SAIDA'     => -1,  // venda, perda, transferencia
            'DEVOLUCAO' => 1,   // cliente devolveu / venda cancelada
            'AJUSTE'    => 1,   // inventario: o sinal vem da quantidade informada
        );
    }

    public function validatorGravar() {
        $message = "";
        $return = true;

        if ($this->isNullOrEmpty($this->getCodProduto())) {
            $message .= "Produto nao foi informado.<br>";
            $return = false;
        }

        $tipo = strtoupper(trim((string) $this->getTipo()));
        if ($tipo == "") {
            $message .= "Tipo do movimento nao foi informado.<br>";
            $return = false;
        } elseif (!array_key_exists($tipo, self::tiposValidos())) {
            $message .= "Tipo de movimento invalido: " . $tipo . ".<br>";
            $return = false;
        }

        $qtd = (float) $this->getQuantidade();
        if ($qtd == 0) {
            $message .= "Quantidade nao pode ser zero.<br>";
            $return = false;
        } elseif ($tipo != 'AJUSTE' && $qtd < 0) {
            // Fora do ajuste o sinal vem do TIPO. Aceitar negativo aqui deixaria
            // uma "entrada de -10" existir, que ninguem consegue interpretar
            // depois no extrato.
            $message .= "Quantidade deve ser positiva — quem define entrada ou saida e o tipo.<br>";
            $return = false;
        }

        // Ajuste sem motivo e buraco no controle: some estoque e ninguem sabe
        // por que. Nos outros tipos o documento/origem ja explica.
        if ($tipo == 'AJUSTE' && $this->isNullOrEmpty($this->getMotivo())) {
            $message .= "Ajuste de estoque exige o motivo.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

}
