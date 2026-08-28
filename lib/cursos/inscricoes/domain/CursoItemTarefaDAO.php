<?php

class CursoItemTarefaDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getPorItemInscrito(CursoItemTarefa $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_curso_item_tarefa
                WHERE cod_curso_item=%d AND cod_inscrito=%d LIMIT 1",
                (int) $obj->getCodCursoItem(), (int) $obj->getCodInscrito());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return null;
            foreach ($rs as $r) return $r;
            return null;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return null;
        }
    }

    public function insert(CursoItemTarefa $obj, $debug = false) {
        try {
            $sql = sprintf("INSERT INTO acad_curso_item_tarefa
                (cod_curso_item, cod_inscrito, arquivo_original, arquivo_salvo, tamanho_bytes, versao, status, dat_envio, ip)
                VALUES (%d, %d, '%s', '%s', %d, %d, '%s', '%s', '%s')",
                (int) $obj->getCodCursoItem(),
                (int) $obj->getCodInscrito(),
                addslashes((string) $obj->getArquivoOriginal()),
                addslashes((string) $obj->getArquivoSalvo()),
                (int) $obj->getTamanhoBytes(),
                (int) $obj->getVersao(),
                addslashes((string) $obj->getStatus() ?: 'ENVIADO'),
                addslashes((string) $obj->getDatEnvio()),
                addslashes((string) $obj->getIp())
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) $obj->setCodTarefa($this->conex->Insert_ID());
            return $rs ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(CursoItemTarefa $obj, $debug = false) {
        try {
            $sql = sprintf("UPDATE acad_curso_item_tarefa SET
                arquivo_original='%s', arquivo_salvo='%s', tamanho_bytes=%d,
                versao=%d, dat_alter=NOW(), ip='%s'
                WHERE cod_tarefa=%d",
                addslashes((string) $obj->getArquivoOriginal()),
                addslashes((string) $obj->getArquivoSalvo()),
                (int) $obj->getTamanhoBytes(),
                (int) $obj->getVersao(),
                addslashes((string) $obj->getIp()),
                (int) $obj->getCodTarefa());
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql) ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
