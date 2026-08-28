<?php

class InteressadoDAO extends DAO
{

    public function __construct()
    {
        $this->tableName = "tab_interessado";
        parent::__construct();
    }

    public function getById(Interessado $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and cod_interessado= \'%s\' '
                , $obj->getCodEvento(), $obj->getCodInteressado());

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

    public function getByIdExiste(Interessado $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_interessado FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and cod_interessado= \'%s\' '
                , $obj->getCodEvento(), $obj->getCodInteressado());

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

    public function carregarListaInteressadosByIdEvento(Interessado $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT tma.*, top.titulo AS titulo_perfil 
                 FROM tab_interessado AS tma 
                 LEFT JOIN tab_opcoes_perfis AS top ON tma.cod_perfil = top.cod_perfil 
                 WHERE tma.cod_evento = %d order by tma.cod_interessado DESC',
                $obj->getCodEvento()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaInteressados($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaInteressadosForAreaInscrito(Interessado $obj, $debug = false) 
    {
        try {
            $sqlLido = '';
            if($obj->getCodInscrito()){
                $sqlLido = " , (select aviso_lido from tab_lig_inscritos_avisos where cod_inscrito=".$obj->getCodInscrito()." and cod_interessado=tma.cod_interessado) as aviso_lido ";
            }

            $sql = sprintf( 
                'SELECT tma.*, top.titulo AS titulo_perfil 
                 '.$sqlLido.'
                 FROM tab_interessado AS tma 
                 LEFT JOIN tab_opcoes_perfis AS top ON tma.cod_perfil = top.cod_perfil 
                 WHERE tma.cod_evento = %d AND (tma.cod_perfil = %d OR tma.cod_perfil IS NULL OR tma.cod_perfil=0)
                 ORDER BY tma.cod_interessado DESC',
                $obj->getCodEvento(), 
                $obj->getCodPerfil()
            );
    
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
    
            if ((!$rs) || ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaInteressados($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarTotalInteressadosForAreaInscrito(Interessado $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                "SELECT COUNT(*) AS total
                FROM tab_interessado AS tma
                WHERE tma.cod_evento = %d
                AND tma.status = 'ATIVADA'
                AND (tma.data_publicacao_ini IS NULL OR tma.data_publicacao_ini <= NOW())
                AND (tma.data_publicacao_fim IS NULL OR tma.data_publicacao_fim >= NOW())
                AND (tma.cod_perfil = %d OR tma.cod_perfil IS NULL OR tma.cod_perfil = 0)",
                $obj->getCodEvento(),
                $obj->getCodPerfil()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) || ($rs->EOF)) {
                return 0;
            } else {
                return (int) $rs->fields['total'];
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    public function insert(Interessado $obj, $debug = 1)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM $this->tableName WHERE "
                . " cod_evento=" . $obj->getCodEvento() . " and cod_interessado=0 ";

            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodInteressado($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
public function insertLigacaoInscritoAviso(Interessado $obj, $debug = false)
    {
        try {
            $sql = "INSERT INTO tab_lig_inscritos_avisos (cod_evento, cod_inscrito, cod_interessado, aviso_lido)
            VALUES (".$obj->getCodEvento().",".$obj->getCodInscrito().", ".$obj->getCodInteressado().",'1')";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;

        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(Interessado $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);
            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_evento = " . $obj->getCodEvento()
                . " and cod_interessado = " . $obj->getCodInteressado();
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

    public function delete(Interessado $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' AND cod_interessado= \'%s\' ', $obj->getCodEvento(), $obj->getCodInteressado());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return $rs !== false;
        } catch (ADODB_Exception $e) {
            echo 'Erro: ' . $e->getMessage();
            $this->enviarMensagem($e);
            return false;
        }
    }
}