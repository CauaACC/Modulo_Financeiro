<?php

class CursoItemComentarioDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function insert(CursoItemComentario $obj, $debug = false) {
        try {
            $codPai = $obj->getCodPai() ? (int) $obj->getCodPai() : 'NULL';
            $codInscrito = $obj->getCodInscrito() ? (int) $obj->getCodInscrito() : 'NULL';
            $codUsuario = $obj->getCodUsuario() ? (int) $obj->getCodUsuario() : 'NULL';
            $sql = sprintf("INSERT INTO acad_curso_item_comentario
                (cod_curso_item, cod_pai, cod_inscrito, cod_usuario, texto, status, dat_incl)
                VALUES (%d, %s, %s, %s, '%s', '%s', '%s')",
                (int) $obj->getCodCursoItem(),
                $codPai, $codInscrito, $codUsuario,
                addslashes((string) $obj->getTexto()),
                addslashes((string) $obj->getStatus()),
                addslashes((string) $obj->getDatIncl())
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) $obj->setCodComentario($this->conex->Insert_ID());
            return $rs ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(CursoItemComentario $obj, $debug = false) {
        try {
            $sql = sprintf("UPDATE acad_curso_item_comentario
                SET texto='%s', dat_alter=NOW()
                WHERE cod_comentario=%d",
                addslashes((string) $obj->getTexto()),
                (int) $obj->getCodComentario()
            );
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql) ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function softDelete(CursoItemComentario $obj, $debug = false) {
        try {
            $sql = sprintf("UPDATE acad_curso_item_comentario
                SET status='REMOVIDO', dat_alter=NOW()
                WHERE cod_comentario=%d",
                (int) $obj->getCodComentario());
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql) ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getById($codComentario, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_comentario, cod_curso_item, cod_inscrito, cod_usuario, status
                FROM acad_curso_item_comentario WHERE cod_comentario=%d",
                (int) $codComentario);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) return $rs->fields;
            return null;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return null;
        }
    }

    public function carregarPorItem($codCursoItem, $debug = false) {
        try {
            $sql = sprintf("SELECT c.*, i.nome_inscrito AS autor_inscrito, u.nome_usuario AS autor_admin
                FROM acad_curso_item_comentario c
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = c.cod_inscrito
                LEFT JOIN sis_usuarios u ON u.cod_usuario = c.cod_usuario
                WHERE c.cod_curso_item=%d AND c.status='ATIVADO'
                ORDER BY c.cod_pai IS NULL DESC, c.cod_pai ASC, c.dat_incl ASC",
                (int) $codCursoItem);
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
}
