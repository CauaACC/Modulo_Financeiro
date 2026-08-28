<?php

class SistemaDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(Sistema $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM sis_sistemas WHERE cod_sistema = '%s'",
                intval($obj->getCodSistema()));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {}
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(Sistema $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT 1 FROM sis_sistemas WHERE cod_sistema = '%s'",
                intval($obj->getCodSistema()));

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

    public function getProximoCodSistema($debug = false) {
        try {
            $sql = "SELECT COALESCE(MAX(cod_sistema), 0) + 1 AS prox FROM sis_sistemas WHERE cod_sistema < 999";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return intval($rs->fields['prox']);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Sistema $obj, $debug = false) {
        try {
            $sql = "SELECT * FROM sis_sistemas WHERE 1=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("sis_sistemas");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(Sistema $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM sis_sistemas WHERE cod_sistema = '%s'",
                intval($obj->getCodSistema()));
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("sis_sistemas");
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

    public function delete(Sistema $obj, $debug = false) {
        try {
            $sql = sprintf("DELETE FROM sis_sistemas WHERE cod_sistema = '%s'",
                intval($obj->getCodSistema()));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function contarVinculosDireitos($codSistema, $debug = false) {
        try {
            $sql = sprintf("SELECT COUNT(*) as total FROM sis_direitos_grupos_sistemas WHERE cod_sistema = '%s'",
                intval($codSistema));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return intval($rs->fields['total']);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return -1;
        }
    }

    public function getListaSistemas($debug = false) {
        try {
            $sql = "SELECT ss.cod_sistema, ss.nome_sistema, ss.enum_sistema,
                    (SELECT COUNT(*) FROM sis_direitos_grupos_sistemas dgs
                     WHERE dgs.cod_sistema = ss.cod_sistema) as total_vinculos
                    FROM sis_sistemas ss
                    ORDER BY ss.cod_sistema DESC, ss.nome_sistema";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            $lista = array();
            while (!$rs->EOF) {
                $lista[] = $rs->fields;
                $rs->MoveNext();
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getSelectSistemas($debug = false) {
        try {
            $sql = "SELECT DISTINCT cod_sistema, nome_sistema FROM sis_sistemas ORDER BY nome_sistema";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            $lista = array();
            while (!$rs->EOF) {
                $lista[] = $rs->fields;
                $rs->MoveNext();
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
