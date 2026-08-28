<?php

class CursoTarefaMensagemDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function insert(CursoTarefaMensagem $obj, $debug = false) {
        try {
            $codAutorInscrito = $obj->getCodAutorInscrito() ? (int) $obj->getCodAutorInscrito() : 'NULL';
            $codAutorUsuario  = $obj->getCodAutorUsuario() ? (int) $obj->getCodAutorUsuario() : 'NULL';
            $sql = sprintf("INSERT INTO acad_curso_tarefa_mensagem
                (cod_evento, cod_curso_item, cod_inscrito, autor_tipo, cod_autor_inscrito, cod_autor_usuario, texto, status, dat_incl, ip)
                VALUES (%d, %d, %d, '%s', %s, %s, '%s', '%s', '%s', '%s')",
                (int) $obj->getCodEvento(),
                (int) $obj->getCodCursoItem(),
                (int) $obj->getCodInscrito(),
                addslashes((string) $obj->getAutorTipo()),
                $codAutorInscrito,
                $codAutorUsuario,
                addslashes((string) $obj->getTexto()),
                addslashes((string) $obj->getStatus()),
                addslashes((string) $obj->getDatIncl()),
                addslashes((string) $obj->getIp())
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) $obj->setCodMensagem($this->conex->Insert_ID());
            return $rs ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Thread de um aluno numa tarefa, com o nome do autor resolvido (aluno ou professor). */
    public function carregarThread($codCursoItem, $codInscrito, $debug = false) {
        try {
            $sql = sprintf("SELECT m.*, i.nome_inscrito AS autor_aluno, u.nome_usuario AS autor_prof
                FROM acad_curso_tarefa_mensagem m
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = m.cod_autor_inscrito
                LEFT JOIN sis_usuarios u ON u.cod_usuario = m.cod_autor_usuario
                WHERE m.cod_curso_item=%d AND m.cod_inscrito=%d AND m.status='ATIVADO'
                ORDER BY m.dat_incl ASC, m.cod_mensagem ASC",
                (int) $codCursoItem, (int) $codInscrito);
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

    /**
     * Conta mensagens não lidas.
     * $paraQuem='ALUNO'    -> msgs do PROFESSOR ainda não lidas pelo aluno (lida_aluno IS NULL).
     * $paraQuem='PROFESSOR'-> msgs do ALUNO ainda não lidas pelo professor (lida_professor IS NULL).
     */
    public function contarNaoLidas($codCursoItem, $codInscrito, $paraQuem, $debug = false) {
        try {
            if ($paraQuem === 'ALUNO') { $autor = 'PROFESSOR'; $col = 'lida_aluno'; }
            else                       { $autor = 'ALUNO';     $col = 'lida_professor'; }
            $sql = sprintf("SELECT COUNT(*) AS q FROM acad_curso_tarefa_mensagem
                WHERE cod_curso_item=%d AND cod_inscrito=%d AND status='ATIVADO'
                AND autor_tipo='%s' AND %s IS NULL",
                (int) $codCursoItem, (int) $codInscrito, $autor, $col);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return ($rs && !$rs->EOF) ? (int) $rs->fields['q'] : 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    /** Marca como lidas as mensagens do outro lado (mesma regra de contarNaoLidas). */
    public function marcarLidas($codCursoItem, $codInscrito, $paraQuem, $debug = false) {
        try {
            if ($paraQuem === 'ALUNO') { $autor = 'PROFESSOR'; $col = 'lida_aluno'; }
            else                       { $autor = 'ALUNO';     $col = 'lida_professor'; }
            $sql = sprintf("UPDATE acad_curso_tarefa_mensagem SET %s=NOW()
                WHERE cod_curso_item=%d AND cod_inscrito=%d AND status='ATIVADO'
                AND autor_tipo='%s' AND %s IS NULL",
                $col, (int) $codCursoItem, (int) $codInscrito, $autor, $col);
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql) ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Mapa cod_curso_item => qtd de mensagens do PROFESSOR ainda nao lidas por este aluno (badge "N novas"). */
    public function naoLidasPorAluno($codEvento, $codInscrito, $debug = false) {
        $map = array();
        try {
            $sql = sprintf("SELECT cod_curso_item, COUNT(*) AS q
                FROM acad_curso_tarefa_mensagem
                WHERE cod_evento=%d AND cod_inscrito=%d AND status='ATIVADO'
                AND autor_tipo='PROFESSOR' AND lida_aluno IS NULL
                GROUP BY cod_curso_item",
                (int) $codEvento, (int) $codInscrito);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) foreach ($rs as $r) $map[(int) $r['cod_curso_item']] = (int) $r['q'];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $map;
    }

    /** Caixa do professor: por tarefa, os alunos que têm thread + total e não lidas pelo professor. */
    public function alunosComThread($codCursoItem, $debug = false) {
        try {
            $sql = sprintf("SELECT m.cod_inscrito, i.nome_inscrito,
                    COUNT(*) AS total,
                    SUM(CASE WHEN m.autor_tipo='ALUNO' AND m.lida_professor IS NULL THEN 1 ELSE 0 END) AS nao_lidas,
                    MAX(m.dat_incl) AS ultima
                FROM acad_curso_tarefa_mensagem m
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = m.cod_inscrito
                WHERE m.cod_curso_item=%d AND m.status='ATIVADO'
                GROUP BY m.cod_inscrito, i.nome_inscrito
                ORDER BY ultima DESC",
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
