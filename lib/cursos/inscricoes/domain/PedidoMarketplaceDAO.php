<?php

class PedidoMarketplaceDAO extends DAO {

    public function __construct() {
        $this->tableName = "fin_pedidos_marketplace";
        parent::__construct();
    }

    public function getByPedido(PedidoMarketplace $obj, $pedidoId, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . " WHERE pedido_id=%d LIMIT 1",
                (int) $pedidoId);
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

    /**
     * Chave da IDEMPOTENCIA da carga: canal + id do pedido na plataforma.
     * Reimportar a mesma planilha tem que reencontrar o pedido, nao criar outro.
     */
    public function getByIdExterno(PedidoMarketplace $obj, $canal, $idExterno, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . "
                WHERE canal='%s' AND id_externo='%s' LIMIT 1",
                addslashes((string) $canal), addslashes((string) $idExterno));
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

    /**
     * Vendas do canal com o que a tela precisa: pedido, cliente e situacao da
     * nota. O LEFT JOIN em fin_nota_fiscal e por origem/origem_id, mesmo
     * padrao que a NFS-e ja usa.
     */
    public function listarVendas($codEvento, $filtros = array(), $debug = false) {
        try {
            $where = array(sprintf("p.evento_id = %d", (int) $codEvento));

            if (isset($filtros['busca']) && trim((string) $filtros['busca']) != '') {
                $busca = addslashes(trim((string) $filtros['busca']));
                $where[] = "(m.id_externo LIKE '%" . $busca . "%'"
                         . " OR i.nome_inscrito LIKE '%" . $busca . "%'"
                         . " OR i.cpf LIKE '%" . $busca . "%')";
            }
            if (!empty($filtros['semNota'])) {
                $where[] = "nf.cod_nota_fiscal IS NULL";
            }

            $sql = "SELECT p.id AS pedido_id, p.valor_total, p.status AS status_pedido,
                           p.descricao, p.referencia,
                           m.*,
                           i.nome_inscrito, i.cpf, i.cidade, i.uf, i.cod_inscrito,
                           nf.cod_nota_fiscal, nf.numero AS numero_nota, nf.status AS status_nota,
                           (SELECT COUNT(*) FROM fin_pedidos_itens it
                             WHERE it.pedido_id = p.id AND it.status='ATIVADA') AS qtd_itens,
                           (SELECT COUNT(*) FROM fin_pedidos_itens it2
                             LEFT JOIN fin_produtos pr ON pr.cod_produto = it2.cod_produto
                            WHERE it2.pedido_id = p.id AND it2.status='ATIVADA'
                              AND (it2.cod_produto IS NULL OR pr.flg_incompleto = 1)) AS qtd_itens_pendentes
                    FROM " . $this->tableName . " m
                    INNER JOIN pedidos p ON p.id = m.pedido_id
                    LEFT JOIN tab_inscritos i
                           ON i.cod_inscrito = p.inscrito_id AND i.cod_evento = p.evento_id
                    LEFT JOIN fin_nota_fiscal nf
                           ON nf.origem = 'PEDIDO' AND nf.origem_id = p.id
                          AND nf.status <> 'CANCELADA'
                    WHERE " . implode(" AND ", $where) . "
                    ORDER BY m.data_pedido DESC, m.cod_pedido_marketplace DESC";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function insert(PedidoMarketplace $obj, $debug = false) {
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
            // O GetInsertSQL do ADOdb escreve NULL explicito para o que o
            // objeto nao setou — o DEFAULT da coluna nao chega a valer. Como
            // flg_xml_enviado e NOT NULL, o insert morria com 1048.
            if ($obj->getFlgXmlEnviado() === null || $obj->getFlgXmlEnviado() === '') {
                $obj->setFlgXmlEnviado(0);
            }

            $rs = $this->conex->Execute("SELECT * FROM " . $this->tableName . " WHERE cod_pedido_marketplace=0");
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodPedidoMarketplace($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(PedidoMarketplace $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setSessionIdAlteracao(isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '');
            $obj->setIpAlteracao(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgentAlteracao(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

            $rs = $this->conex->Execute(sprintf(
                "SELECT * FROM " . $this->tableName . " WHERE cod_pedido_marketplace=%d",
                (int) $obj->getCodPedidoMarketplace()));
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
