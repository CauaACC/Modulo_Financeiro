<?php

class ProgramacaoAvaliacaoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(ProgramacaoAvaliacao $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM acad_programacao_avaliacao WHERE cod_evento=\'%s\' AND cod_programacao=\'%s\'',
                $obj->getCodEvento(), $obj->getCodProgramacao());

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

    public function getByIdExiste(ProgramacaoAvaliacao $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT cod_programacao FROM acad_programacao_avaliacao WHERE cod_evento=\'%s\' AND cod_programacao=\'%s\'',
                $obj->getCodEvento(), $obj->getCodProgramacao());

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

    public function carregarListaProgramacoes(ProgramacaoAvaliacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_programacao_avaliacao
                WHERE cod_evento='%s' AND status='ATIVADA'
                ORDER BY data_liberacao DESC",
                $obj->getCodEvento());

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
                $obj->setListaProgramacoes_($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(ProgramacaoAvaliacao $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM acad_programacao_avaliacao WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_programacao=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_programacao_avaliacao");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodProgramacao($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(ProgramacaoAvaliacao $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setSessionIdAlteracao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            $sql = "SELECT * FROM acad_programacao_avaliacao WHERE cod_evento = " . $obj->getCodEvento() . " AND cod_programacao = " . $obj->getCodProgramacao();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_programacao_avaliacao");
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

    public function delete(ProgramacaoAvaliacao $obj, $debug = false) {
        try {
            $sql = sprintf('DELETE FROM acad_programacao_avaliacao WHERE cod_evento=\'%s\' AND cod_programacao=\'%s\'',
                $obj->getCodEvento(), $obj->getCodProgramacao());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}
