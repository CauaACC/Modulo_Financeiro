<?php

class CursoItemConclusaoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function insertOrIgnore(CursoItemConclusao $obj, $debug = false) {
        try {
            $sql = sprintf("INSERT IGNORE INTO acad_curso_item_conclusao
                (cod_curso_item, cod_inscrito, data_conclusao, origem, tempo_efetivo)
                VALUES (%d, %d, '%s', '%s', %d)",
                (int) $obj->getCodCursoItem(),
                (int) $obj->getCodInscrito(),
                addslashes((string) $obj->getDataConclusao()),
                addslashes((string) $obj->getOrigem()),
                (int) $obj->getTempoEfetivo()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function jaConcluiu($codCursoItem, $codInscrito, $debug = false) {
        try {
            $sql = sprintf("SELECT 1 FROM acad_curso_item_conclusao
                WHERE cod_curso_item=%d AND cod_inscrito=%d LIMIT 1",
                (int) $codCursoItem, (int) $codInscrito);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs && !$rs->EOF;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarConclusoesDoInscrito($codInscrito, $codEvento = null, $debug = false) {
        try {
            $sql = "SELECT c.cod_curso_item, c.data_conclusao, c.origem, c.tempo_efetivo
                FROM acad_curso_item_conclusao c
                JOIN acad_curso_item i ON i.cod_curso_item = c.cod_curso_item
                WHERE c.cod_inscrito = " . (int) $codInscrito;
            if ($codEvento) $sql .= " AND i.cod_evento = " . (int) $codEvento;
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) foreach ($rs as $r) $linhas[] = $r;
            return $linhas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function contarConclusoesPorItem($codCursoItem, $debug = false) {
        try {
            $sql = sprintf("SELECT COUNT(*) AS qtd FROM acad_curso_item_conclusao
                WHERE cod_curso_item=%d", (int) $codCursoItem);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return 0;
            foreach ($rs as $r) return (int) $r['qtd'];
            return 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }
}
