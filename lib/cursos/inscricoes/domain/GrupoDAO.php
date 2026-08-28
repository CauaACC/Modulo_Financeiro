<?php

class GrupoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(Grupo $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM sis_grupos WHERE cod_grupo = '%s'",
                intval($obj->getCodGrupo()));

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

    public function getByIdExiste(Grupo $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT 1 FROM sis_grupos WHERE cod_grupo = '%s'",
                intval($obj->getCodGrupo()));

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

    public function getProximoCodGrupo($debug = false) {
        try {
            $sql = "SELECT COALESCE(MAX(cod_grupo), 0) + 1 AS prox FROM sis_grupos WHERE cod_grupo < 999";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return intval($rs->fields['prox']);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Grupo $obj, $debug = false) {
        try {
            $sql = "SELECT * FROM sis_grupos WHERE 1=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("sis_grupos");
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

    public function update(Grupo $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM sis_grupos WHERE cod_grupo = '%s'",
                intval($obj->getCodGrupo()));
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("sis_grupos");
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

    public function delete(Grupo $obj, $debug = false) {
        try {
            $sql = sprintf("DELETE FROM sis_grupos WHERE cod_grupo = '%s'",
                intval($obj->getCodGrupo()));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function contarVinculosDireitos($codGrupo, $debug = false) {
        try {
            $sql = sprintf("SELECT COUNT(*) as total FROM sis_direitos_grupos_sistemas WHERE cod_grupo = '%s'",
                intval($codGrupo));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return intval($rs->fields['total']);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return -1;
        }
    }

    public function contarVinculosUsuarios($codGrupo, $debug = false) {
        try {
            $sql = sprintf("SELECT COUNT(*) as total FROM sis_ligacao_usuarios_grupos WHERE cod_grupo = '%s'",
                intval($codGrupo));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return intval($rs->fields['total']);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return -1;
        }
    }

    public function getListaGrupos($debug = false) {
        try {
            $sql = "SELECT sg.cod_grupo, sg.nome_grupo, sg.enum_grupo,
                    (SELECT COUNT(*) FROM sis_direitos_grupos_sistemas dgs
                     WHERE dgs.cod_grupo = sg.cod_grupo) as total_vinculos
                    FROM sis_grupos sg
                    ORDER BY sg.cod_grupo DESC, sg.nome_grupo";

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

    public function getSelectGrupos($debug = false) {
        try {
            $sql = "SELECT cod_grupo, nome_grupo FROM sis_grupos WHERE cod_grupo <> 999 ORDER BY nome_grupo";

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

    public function getGruposByEvento($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_grupo, nome_grupo, enum_grupo, ordem, categoria
                FROM sis_grupos
                WHERE cod_evento = '%s' AND cod_grupo <> 999
                ORDER BY nome_grupo", intval($codEvento));

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

    public function existeGrupoNoEvento($codGrupo, $codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT 1 FROM sis_grupos WHERE cod_grupo = '%s' AND cod_evento = '%s'",
                intval($codGrupo), intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return ($rs && !$rs->EOF);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function inserirGrupoNoEvento($codGrupo, $nomeGrupo, $codEvento, $enumGrupo, $ordem, $categoria, $debug = false) {
        try {
            $sql = sprintf("INSERT INTO sis_grupos (cod_grupo, nome_grupo, cod_evento, enum_grupo, ordem, categoria)
                VALUES ('%s', '%s', '%s', '%s', '%s', '%s')",
                intval($codGrupo), addslashes($nomeGrupo), intval($codEvento),
                addslashes($enumGrupo), intval($ordem), addslashes($categoria));

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
