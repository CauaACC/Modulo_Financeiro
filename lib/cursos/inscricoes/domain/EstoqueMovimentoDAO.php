<?php

class EstoqueMovimentoDAO extends DAO {

    public function __construct() {
        $this->tableName = "fin_estoque_movimentos";
        parent::__construct();
    }

    /**
     * Extrato do produto, do mais recente para o mais antigo.
     */
    public function listarPorProduto($codProduto, $limite = 100, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . "
                WHERE cod_produto=%d
                ORDER BY cod_movimento DESC
                LIMIT %d",
                (int) $codProduto, (int) $limite);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $item = new EstoqueMovimento();
                    $item->setAll($row);
                    $lista[] = $item;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Movimentos gerados por um pedido — usado no cancelamento, para saber o
     * que devolver sem depender do que a tela informou.
     */
    public function listarPorPedido($pedidoId, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . "
                WHERE origem='PEDIDO' AND origem_id='%s'
                ORDER BY cod_movimento",
                addslashes((string) $pedidoId));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $item = new EstoqueMovimento();
                    $item->setAll($row);
                    $lista[] = $item;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function insert(EstoqueMovimento $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setSessionIdInclusao(isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '');
            $obj->setIpInclusao(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgentInclusao(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
            $obj->setVersao(1);
            if ($obj->getStatus() == '') {
                $obj->setStatus('ATIVADA');
            }

            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_movimento=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodMovimento($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    // Movimento nao tem update nem delete de proposito: correcao e movimento
    // novo. Ver EstoqueMovimento.

}
