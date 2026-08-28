<?php

class FeriadoDAO extends DAO
{
    public function __construct()
    {
        $this->tableName = "tab_calendario_feriados_evento";
        parent::__construct();
    }

    public function getById(Feriado $obj, $debug = false)
    {
        try {
            $sql = 'SELECT * FROM ' . $this->tableName . ' WHERE evento_id=? AND id=?';
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql, array($obj->getEventoId(), $obj->getId()));
            if (!$rs || $rs->EOF) {
                return false;
            } else {
                foreach ($rs as $rows) { }
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(Feriado $obj, $debug = false)
    {
        try {
            $sql = 'SELECT id FROM ' . $this->tableName . ' WHERE evento_id=? AND id=?';
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql, array($obj->getEventoId(), $obj->getId()));
            return (!$rs || $rs->EOF) ? false : true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaFeriadoByIdEvento(Feriado $obj, $debug = false)
    {
        try {
            // Une feriados especificos do evento (editaveis) + feriados nacionais
            // do calendario base (apenas visualizacao, marcados com origem='GLOBAL').
            $sql = "
                SELECT id, evento_id, dia_calendario, descricao,
                       tipo_dia AS tipo_dia,
                       'EVENTO' AS origem
                  FROM tab_calendario_feriados_evento
                 WHERE evento_id = ?
                UNION ALL
                SELECT NULL AS id, NULL AS evento_id, dia_calendario, descricao,
                       tipo_dia,
                       'GLOBAL' AS origem
                  FROM tab_calendario_base
                 WHERE eh_feriado = 1
                ORDER BY dia_calendario
            ";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql, array($obj->getEventoId()));
            if (!$rs || $rs->EOF) {
                return false;
            } else {
                $return = [];
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaFeriados($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Feriado $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);

            $sql = "SELECT * FROM " . $this->tableName . " WHERE evento_id=? AND id=0";
            $rs = $this->conex->Execute($sql, array($obj->getEventoId()));
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setId($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(Feriado $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            $sql = "SELECT * FROM " . $this->tableName . " WHERE evento_id=? AND id=?";
            $rs = $this->conex->Execute($sql, array($obj->getEventoId(), $obj->getId()));
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);
            $sql = sprintf($updateSQL);

            // O GetUpdateSQL herda os "?" do WHERE do SELECT inicial; e necessario
            // passar os binds no Execute final, caso contrario MySQL recebe os "?"
            // literais e quebra com syntax error.
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql, array($obj->getEventoId(), $obj->getId()));
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(Feriado $obj, $debug = false)
    {
        try {
            $sql = 'DELETE FROM ' . $this->tableName . ' WHERE evento_id=? AND id=?';
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql, array($obj->getEventoId(), $obj->getId())) !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
