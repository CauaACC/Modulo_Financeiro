<?php

class PedidoItemDAO extends DAO {

    public function __construct() {
        $this->tableName = "fin_pedidos_itens";
        parent::__construct();
    }

    public function getById(PedidoItem $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . " WHERE cod_item=%d",
                (int) $obj->getCodItem());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            foreach ($rs as $rows) {}
            $obj->setAll($rows);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function listarPorPedido($pedidoId, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . "
                WHERE pedido_id=%d AND status='ATIVADA'
                ORDER BY IFNULL(ordem, cod_item), cod_item",
                (int) $pedidoId);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $item = new PedidoItem();
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
     * Apaga os itens de um pedido. Usado na REIMPORTACAO: a planilha e a fonte
     * da verdade do que foi vendido, entao reimportar substitui os itens em vez
     * de acumular duplicata.
     */
    public function excluirPorPedido($pedidoId, $debug = false) {
        try {
            $this->conex->debug = $debug;
            $this->conex->Execute(sprintf("DELETE FROM " . $this->tableName . " WHERE pedido_id=%d",
                (int) $pedidoId));
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Soma dos itens — e o valor_total do pedido. */
    public function somarPorPedido($pedidoId, $debug = false) {
        try {
            $sql = sprintf("SELECT IFNULL(SUM(valor_total),0) AS total FROM " . $this->tableName . "
                WHERE pedido_id=%d AND status='ATIVADA'", (int) $pedidoId);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return 0;
            }
            foreach ($rs as $row) {
                return (float) $row['total'];
            }
            return 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    public function insert(PedidoItem $obj, $debug = false) {
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

            $rs = $this->conex->Execute("SELECT * FROM " . $this->tableName . " WHERE cod_item=0");
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodItem($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(PedidoItem $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setSessionIdAlteracao(isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '');
            $obj->setIpAlteracao(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgentAlteracao(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

            $rs = $this->conex->Execute(sprintf("SELECT * FROM " . $this->tableName . " WHERE cod_item=%d",
                (int) $obj->getCodItem()));
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, true);

            $this->conex->debug = $debug;
            if ($updateSQL) {
                $this->conex->Execute($updateSQL);
            }
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}
