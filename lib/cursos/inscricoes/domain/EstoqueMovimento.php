<?php

/**
 * Movimento de estoque — o "por que" do saldo que fica em fin_produtos.
 *
 * Movimento nao se edita nem se apaga: corrige-se com outro movimento. Extrato
 * que pode ser reescrito nao serve para explicar divergencia.
 */
class EstoqueMovimento extends EstoqueMovimentoVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new EstoqueMovimentoDAO();
    }

    public function getObjDAO() {
        return $this->objDAO;
    }

    public function listarPorProduto($codProduto, $limite = 100, $debug = false) {
        return $this->objDAO->listarPorProduto($codProduto, $limite, $debug);
    }

    /**
     * Registra o movimento e atualiza o saldo do produto.
     *
     * ESTOQUE NEGATIVO NAO BLOQUEIA — apenas avisa. O marketplace vende sem
     * consultar o nosso estoque, entao a venda ja aconteceu quando chega aqui:
     * recusar o movimento nao desfaz a venda, so impede de registra-la e deixa
     * o sistema mentindo. O saldo negativo e o sintoma correto de "vendeu mais
     * do que tinha cadastrado", e o gestor resolve com entrada ou ajuste.
     *
     * @param array $opcoes origem, origemId, documento, motivo
     * @return EstoqueMovimento o movimento gravado (getSaldoApos tem o saldo novo)
     */
    public static function registrar($codProduto, $tipo, $quantidade, $opcoes = array(), $debug = false) {
        $tipo = strtoupper(trim((string) $tipo));

        $obj = new EstoqueMovimento();
        $obj->setCodProduto($codProduto);
        $obj->setTipo($tipo);
        $obj->setQuantidade($quantidade);
        $obj->setOrigem(isset($opcoes['origem']) && $opcoes['origem'] != '' ? $opcoes['origem'] : 'MANUAL');
        $obj->setOrigemId(isset($opcoes['origemId']) ? $opcoes['origemId'] : null);
        $obj->setDocumento(isset($opcoes['documento']) ? $opcoes['documento'] : null);
        $obj->setMotivo(isset($opcoes['motivo']) ? $opcoes['motivo'] : null);
        $obj->validatorGravar();

        // O banco soma; o PHP so diz quanto. Ver ProdutoDAO::aplicarDeltaEstoque.
        $delta = $obj->getDeltaDoSaldo();
        $objProdutoDAO = new ProdutoDAO();
        $saldo = $objProdutoDAO->aplicarDeltaEstoque($codProduto, $delta, $debug);

        if ($saldo === null) {
            throw new Exception("Nao foi possivel atualizar o saldo do produto.");
        }

        $obj->setSaldoApos($saldo);
        $obj->setStatus('ATIVADA');

        if (!$obj->getObjDAO()->insert($obj, $debug)) {
            // O saldo ja mudou: desfaz para nao ficar saldo sem historico.
            $objProdutoDAO->aplicarDeltaEstoque($codProduto, -$delta, $debug);
            throw new Exception("Movimento de estoque nao foi registrado.");
        }

        return $obj;
    }

    /**
     * Quanto este movimento soma (ou subtrai) do saldo.
     *
     * No AJUSTE o sinal vem da propria quantidade — inventario tanto acrescenta
     * quanto tira. Nos demais, quem manda e o tipo.
     */
    public function getDeltaDoSaldo() {
        $tipo = strtoupper(trim((string) $this->getTipo()));
        $qtd = (float) $this->getQuantidade();

        if ($tipo == 'AJUSTE') {
            return $qtd;
        }

        $tipos = self::tiposValidos();
        $sinal = isset($tipos[$tipo]) ? $tipos[$tipo] : 1;
        return abs($qtd) * $sinal;
    }

    /**
     * Saida por venda. Atalho usado pelo pedido (Fase 3) — mantem a origem
     * padronizada para o estorno conseguir achar o movimento depois.
     */
    public static function baixarPorPedido($codProduto, $quantidade, $pedidoId, $debug = false) {
        return self::registrar($codProduto, 'SAIDA', $quantidade, array(
            'origem'   => 'PEDIDO',
            'origemId' => $pedidoId,
            'motivo'   => 'Venda do pedido ' . $pedidoId
        ), $debug);
    }

    /**
     * Devolucao do que saiu por um pedido (cancelamento). Nao apaga a saida:
     * entra como movimento novo, e o extrato mostra os dois.
     */
    public static function devolverPorPedido($codProduto, $quantidade, $pedidoId, $debug = false) {
        return self::registrar($codProduto, 'DEVOLUCAO', $quantidade, array(
            'origem'   => 'PEDIDO',
            'origemId' => $pedidoId,
            'motivo'   => 'Cancelamento do pedido ' . $pedidoId
        ), $debug);
    }

}
