<?php

class LiberacaoRegistroDAO extends DAO
{

    public function __construct()
    {
        $this->tableName = "tab_liberacao_registro";
        parent::__construct();
    }

    public function getById(LiberacaoRegistro $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' AND cod_liberacao=\'%s\' '
                , $obj->getCodEvento(), $obj->getCodLiberacaoRegistro());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach($rs as $rows){
                }
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(LiberacaoRegistro $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_liberacao FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' AND cod_liberacao=\'%s\' '
                , $obj->getCodEvento(), $obj->getCodLiberacaoRegistro());

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

    public function listarByEvento(LiberacaoRegistro $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT tlr.*, ti.nome_inscrito AS nome_inscrito
                 FROM ' . $this->tableName . ' AS tlr
                 LEFT JOIN tab_inscritos AS ti ON tlr.cod_inscrito = ti.cod_inscrito AND tlr.cod_evento = ti.cod_evento
                 WHERE tlr.cod_evento = %d
                 ORDER BY tlr.cod_liberacao DESC',
                $obj->getCodEvento()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = array();
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaLiberacoes($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function buscarInscritos(LiberacaoRegistro $obj, $debug = false)
    {
        try {
            $termo = addslashes($obj->getTermoBusca());
            $sql = sprintf(
                "SELECT cod_inscrito, nome_inscrito
                 FROM tab_inscritos
                 WHERE cod_evento = %d
                 AND nome_inscrito LIKE '%%%s%%'
                 ORDER BY nome_inscrito
                 LIMIT 20",
                $obj->getCodEvento(),
                $termo
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return array();
            } else {
                $result = array();
                foreach ($rs as $rows) {
                    $result[] = array(
                        'id' => $rows['cod_inscrito'],
                        'text' => $rows['nome_inscrito'] . ' (#' . $rows['cod_inscrito'] . ')'
                    );
                }
                return $result;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function buscarLiberacaoAtiva(LiberacaoRegistro $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                "SELECT * FROM " . $this->tableName . "
                 WHERE cod_evento = %d
                 AND cod_inscrito = %d
                 AND data_inicio <= '%s'
                 AND data_fim >= '%s'
                 ORDER BY data_inicio ASC
                 LIMIT 1",
                $obj->getCodEvento(),
                $obj->getCodInscrito(),
                date('Y-m-d'),
                date('Y-m-d')
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach($rs as $rows){
                }
                return $rows;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(LiberacaoRegistro $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);

            $sql = "SELECT * FROM $this->tableName WHERE "
                . " cod_evento=" . $obj->getCodEvento() . " AND cod_liberacao=0 ";

            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodLiberacaoRegistro($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(LiberacaoRegistro $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_evento = " . $obj->getCodEvento()
                . " AND cod_liberacao = " . $obj->getCodLiberacaoRegistro();
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

    public function delete(LiberacaoRegistro $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' AND cod_liberacao=\'%s\' ',
                $obj->getCodEvento(), $obj->getCodLiberacaoRegistro());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return $rs !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
