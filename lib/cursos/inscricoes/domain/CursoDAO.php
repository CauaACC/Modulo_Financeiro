<?php

class CursoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(Curso $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_curso
                WHERE cod_evento=%d AND cod_curso=%d",
                (int) $obj->getCodEvento(), (int) $obj->getCodCurso());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return false;
            foreach ($rs as $rows) { $obj->setAll($rows); }
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarLista(Curso $obj, $somenteAtivos = false, $debug = false) {
        try {
            $where = "cod_evento=" . (int) $obj->getCodEvento();
            if ($somenteAtivos) $where .= " AND status='ATIVADA'";
            $sql = "SELECT * FROM acad_curso WHERE $where ORDER BY ordem ASC, cod_curso ASC";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $return = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $novo = new Curso();
                    $novo->setAll($rows);
                    $return[] = $novo;
                }
            }
            $obj->setListaCurso($return);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Curso $obj, $debug = false) {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            if (!$obj->getStatus()) $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM acad_curso WHERE cod_evento=" . (int) $obj->getCodEvento() . " AND cod_curso=0";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_curso");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodCurso($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(Curso $obj, $debug = false) {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao((int) $obj->getVersao() + 1);
            if (!$obj->getStatus()) $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM acad_curso WHERE cod_evento=" . (int) $obj->getCodEvento() . " AND cod_curso=" . (int) $obj->getCodCurso();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_curso");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);
            if (!$updateSQL) return true;
            $this->conex->debug = $debug;
            return $this->conex->Execute($updateSQL);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(Curso $obj, $debug = false) {
        try {
            $sql = sprintf("DELETE FROM acad_curso WHERE cod_evento=%d AND cod_curso=%d",
                (int) $obj->getCodEvento(), (int) $obj->getCodCurso());
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Conta itens de todos os cursos do evento, agrupados por tipo.
     * Retorna mapa: cod_curso => array('total'=>int, 'tipos'=>array(TIPO=>int)).
     */
    public function contarItensPorCurso($codEvento, $debug = false) {
        $mapa = array();
        try {
            $sql = "SELECT cod_curso, tipo, COUNT(*) AS qtd
                    FROM acad_curso_item
                    WHERE cod_evento=" . (int) $codEvento . "
                    GROUP BY cod_curso, tipo";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $r) {
                    $cc = (int) $r['cod_curso'];
                    if (!isset($mapa[$cc])) $mapa[$cc] = array('total' => 0, 'tipos' => array());
                    $q = (int) $r['qtd'];
                    $mapa[$cc]['tipos'][$r['tipo']] = $q;
                    $mapa[$cc]['total'] += $q;
                }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $mapa;
    }

    public function possuiItens(Curso $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT 1 FROM acad_curso_item WHERE cod_curso=%d LIMIT 1", (int) $obj->getCodCurso());
            $rs = $this->conex->Execute($sql);
            return $rs && !$rs->EOF;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return true; // fail-safe: não permite excluir se erro
        }
    }
}
