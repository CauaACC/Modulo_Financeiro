<?php

class BloqueioAvaliacaoDAO extends DAO
{

    public function __construct()
    {
        $this->tableName = "tab_bloqueio_avaliacao";
        parent::__construct();
    }

    public function getById(BloqueioAvaliacao $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' AND cod_bloqueio=\'%s\'',
                $obj->getCodEvento(), $obj->getCodBloqueio());

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

    public function getByIdExiste(BloqueioAvaliacao $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_bloqueio FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' AND cod_bloqueio=\'%s\'',
                $obj->getCodEvento(), $obj->getCodBloqueio());

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

    public function listarByEvento(BloqueioAvaliacao $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT * FROM ' . $this->tableName . ' WHERE cod_evento = %d ORDER BY ano DESC, mes DESC',
                $obj->getCodEvento()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $registros = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $registros[$row['ano'] . '-' . str_pad($row['mes'], 2, '0', STR_PAD_LEFT)] = $row;
                }
            }
            return $registros;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function buscarPorMesAno($codEvento, $mes, $ano, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT * FROM ' . $this->tableName . ' WHERE cod_evento = %d AND mes = %d AND ano = %d',
                $codEvento, $mes, $ano
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                return $rows;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(BloqueioAvaliacao $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);

            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_bloqueio=0";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodBloqueio($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(BloqueioAvaliacao $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_evento = " . $obj->getCodEvento()
                . " AND cod_bloqueio = " . $obj->getCodBloqueio();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
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

    public function delete(BloqueioAvaliacao $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' AND cod_bloqueio=\'%s\'',
                $obj->getCodEvento(), $obj->getCodBloqueio());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return $rs !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
