<?php

class IngressoDAO extends DAO
{

    public function __construct()
    {
        $this->tableName = "ingressos";
        parent::__construct();
    }

    public function getById(Ingresso $obj, $debug = false)
    {
        try {

            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' WHERE evento_id=\'%s\' '
                . 'and id = \'%s\' ', $obj->getEventoId(), $obj->getId());

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

    public function getByInscritoId(Ingresso $obj, $debug = false)
    {
        try {

            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' WHERE evento_id=\'%s\' '
                . ' and inscrito_id= \'%s\'', $obj->getEventoId(), $obj->getInscritoId());

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

    public function getByIdExiste(Ingresso $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT id FROM ' . $this->tableName . ' '
                . 'WHERE evento_id=\'%s\' and id= \'%s\' '
                , $obj->getEventoId(), $obj->getId());

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

    public function carregarListaIngressosByInscritoId(Ingresso $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE evento_id=\'%s\' and inscrito_id= \'%s\' '
                , $obj->getEventoId(), $obj->getInscritoId());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaIngressos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaIngressosByPedidoId(Ingresso $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE evento_id=\'%s\' and pedido_id= \'%s\' '
                , $obj->getEventoId(), $obj->getPedidoId());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaIngressos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
//    public function getAll(Ingresso $obj, $debug=false) {
//        try{
//            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE '
//            . ' evento_id='.$obj->getEventoId().' order by evento_id desc');
//
//            $this->conex->debug = $debug;
//            $rs = $this->conex->Execute($sql);
//
//            if((!$rs)or($rs->EOF)) {
//                return false;
//            }else{
//                foreach($rs as $rows){
//                    $obj->setAll($rows);
//                    $return[] = clone $obj;
//                }
//                $obj->setListaIngressosEventos($return);
//                return true;
//            }
//        } catch (ADODB_Exception $e) {
//            $this->enviarMensagem($e);
//            return false;
//        }
//    }
    public function insert(Ingresso $obj, $debug = 1)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            $obj->setQrCod(hash('sha256', $obj->getId()."-".$obj->getCodEvento()."-".$obj->getDatIncl()));

            $sql = "SELECT * FROM $this->tableName WHERE "
                . " evento_id=" . $obj->getEventoId() . " and id=0 ";

            $rs = $this->conex->Execute($sql);
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

    public function update(Ingresso $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);
            $sql = "SELECT * FROM " . $this->tableName . " WHERE evento_id = " . $obj->getEventoId()
                . " and id = " . $obj->getId();
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

    public function delete(Ingresso $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM ' . $this->tableName . ' WHERE evento_id=\'%s\' AND id= \'%s\' ', $obj->getEventoId(), $obj->getId());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return $rs !== false;  // Retorna true se a exclusão foi bem-sucedida, false caso contrário
        } catch (ADODB_Exception $e) {
            echo 'Erro: ' . $e->getMessage();
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getAll(Ingresso $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' WHERE evento_id=\'%s\'', $obj->getEventoId());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $lista = array();
                while (!$rs->EOF) {
                    $vo = new Ingresso();
                    $vo->setAll($rs->fields);
                    $lista[] = $vo;
                    $rs->MoveNext();
                }
                // Inverte a ordem dos resultados
                $lista = array_reverse($lista);
                return $lista;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByQrCode(Inscrito $obj, $debug=false) {
        try{
            $sql = "SELECT ti.* "
                . $this->sqlLeftJoinColuns
                . "FROM tab_inscritos ti "
                . $this->getSqlLeftJoin($obj->getCodEvento())
                . " WHERE ti.cod_evento=".$obj->getCodEvento()
                . " and ti.qr_code= '".$obj->getQrCode()."'";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
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
}