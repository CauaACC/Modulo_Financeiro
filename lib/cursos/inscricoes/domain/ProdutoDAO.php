<?php

class ProdutoDAO extends DAO {

    public function __construct() {
        $this->tableName = "fin_produtos";
        parent::__construct();
    }

    public function getById(Produto $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . " WHERE cod_produto=%d",
                (int) $obj->getCodProduto());

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
     * SKU e unico POR GESTOR — dois clientes podem usar o mesmo codigo para
     * produtos diferentes.
     */
    public function getBySku(Produto $obj, $codGestor, $sku, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . "
                WHERE cod_gestor=%d AND sku='%s' LIMIT 1",
                (int) $codGestor, addslashes(trim((string) $sku)));

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

    public function existeSkuDuplicado(Produto $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_produto FROM " . $this->tableName . "
                WHERE cod_gestor=%d AND sku='%s' AND cod_produto <> %d LIMIT 1",
                (int) $obj->getCodGestor(),
                addslashes((string) $obj->getSku()),
                (int) $obj->getCodProduto());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return !((!$rs) or ($rs->EOF));
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * @param array|null $codsGestor null = todos (so ADM_GERAL); array = so esses.
     * @param array $filtros busca (sku/descricao), incompletos, status.
     */
    public function listar($codsGestor = null, $filtros = array(), $debug = false) {
        try {
            $where = array();

            if (is_array($codsGestor)) {
                if (empty($codsGestor)) {
                    return array(); // sem gestor vinculado: nada a mostrar
                }
                $ids = array();
                foreach ($codsGestor as $g) {
                    $ids[] = (int) $g;
                }
                $where[] = "p.cod_gestor IN (" . implode(',', $ids) . ")";
            }

            if (!empty($filtros['codGestor'])) {
                $where[] = sprintf("p.cod_gestor = %d", (int) $filtros['codGestor']);
            }

            if (isset($filtros['busca']) && trim((string) $filtros['busca']) != '') {
                $busca = addslashes(trim((string) $filtros['busca']));
                $where[] = "(p.sku LIKE '%" . $busca . "%' OR p.descricao LIKE '%" . $busca . "%')";
            }

            if (!empty($filtros['soIncompletos'])) {
                $where[] = "p.flg_incompleto = 1";
            }

            if (!empty($filtros['status'])) {
                $where[] = sprintf("p.status = '%s'", addslashes((string) $filtros['status']));
            }

            $sql = "SELECT p.*, g.nome_gestor
                    FROM " . $this->tableName . " p
                    LEFT JOIN sis_gestor g ON g.cod_gestor = p.cod_gestor"
                 . (empty($where) ? "" : " WHERE " . implode(" AND ", $where))
                 . " ORDER BY p.flg_incompleto DESC, p.descricao, p.sku";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $item = new Produto();
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
     * Aplica o delta no saldo de forma RELATIVA (nao "saldo = valor lido + x").
     *
     * E isso que mantem o numero certo com dois movimentos simultaneos: ler o
     * saldo no PHP e regravar perderia um dos movimentos. Aqui o proprio banco
     * soma, entao a ordem entre eles nao importa.
     *
     * @param float $delta positivo entra, negativo sai
     * @return float saldo depois do movimento, ou null se falhou
     */
    public function aplicarDeltaEstoque($codProduto, $delta, $debug = false) {
        try {
            $this->conex->debug = $debug;

            $sql = sprintf("UPDATE " . $this->tableName . "
                SET estoque_atual = IFNULL(estoque_atual,0) + %s
                WHERE cod_produto = %d",
                $this->numeroSql($delta), (int) $codProduto);
            $this->conex->Execute($sql);

            $rs = $this->conex->Execute(sprintf(
                "SELECT estoque_atual FROM " . $this->tableName . " WHERE cod_produto=%d",
                (int) $codProduto));
            if ((!$rs) or ($rs->EOF)) {
                return null;
            }
            foreach ($rs as $row) {
                return (float) $row['estoque_atual'];
            }
            return null;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return null;
        }
    }

    /**
     * Numero para dentro do SQL sem aspas (addslashes nao protege numerico).
     */
    private function numeroSql($valor) {
        return number_format((float) $valor, 3, '.', '');
    }

    public function insert(Produto $obj, $debug = false) {
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
            // Saldo so muda por movimento de estoque, nunca pelo cadastro.
            $obj->setEstoqueAtual(0);

            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_produto=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodProduto($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(Produto $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setSessionIdAlteracao(isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '');
            $obj->setIpAlteracao(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgentAlteracao(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

            $sql = sprintf("SELECT * FROM " . $this->tableName . " WHERE cod_produto=%d",
                (int) $obj->getCodProduto());
            $rs = $this->conex->Execute($sql);

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
