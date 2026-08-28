<?php

class TipoReceitaDespesaDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(TipoReceitaDespesa $obj, $debug = false) {
        try {

            $sql = sprintf('SELECT * FROM adm_tipo_receita_despesa WHERE cod_tipo_receita_despesa= \'%s\'', $obj->getCodTipoReceitaDespesa());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    
                }
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getLastId($condicao = false, $debug = false) {
        try {
            $sql = "SELECT ifnull(MAX(cod_tipo_receita_despesa),0)+1 FROM adm_tipo_receita_despesa";

            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    
                }
                return $rows['0'];
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(TipoReceitaDespesa $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT 1 FROM adm_tipo_receita_despesa WHERE cod_tipo_receita_despesa= \'%s\'', $obj->getCodTipoReceitaDespesa());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getAll(TipoReceitaDespesa $obj, $debug = false) {
        try {
            if ($obj->getTipo()) {
                $sqlWhere = " where  tipo='" . $obj->getTipo() . "'";
            }

            $sql = sprintf('SELECT * FROM adm_tipo_receita_despesa '.$sqlWhere. ' order by nome_tipo_receita_despesa');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);


            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTipoReceitaDespesa($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaTipoReceitaDespesas(TipoReceitaDespesa $obj, $debug = false) {
        try {

            $sql = sprintf('SELECT * FROM adm_tipo_receita_despesa');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTipoReceitaDespesas($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(TipoReceitaDespesa $obj, $debug = false) {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);

            $sql = "SELECT * FROM adm_tipo_receita_despesa WHERE cod_tipo_receita_despesa=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("adm_tipo_receita_despesa");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodTipoReceitaDespesa($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(TipoReceitaDespesa $obj, $debug = false) {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            $sql = "SELECT * FROM adm_tipo_receita_despesa WHERE cod_tipo_receita_despesa = " . $obj->getCodTipoReceitaDespesa();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("adm_tipo_receita_despesa");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);

            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(TipoReceitaDespesa $obj, $debug = false) {
        try {
            $sql = sprintf('DELETE FROM adm_tipo_receita_despesa WHERE cod_tipo_receita_despesa= \'%s\'', $obj->getCodTipoReceitaDespesa());

            $this->conex->debug = $debug;

            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}

?>