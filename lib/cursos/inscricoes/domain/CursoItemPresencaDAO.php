<?php

class CursoItemPresencaDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function insert(CursoItemPresenca $obj, $debug = false) {
        try {
            $sql = sprintf("INSERT INTO acad_curso_item_presenca
                (cod_curso_item, cod_inscrito, cod_sessao, tipo, data_hora, ip, user_agent, tempo_acumulado)
                VALUES (%d, %d, '%s', '%s', '%s', '%s', '%s', %d)",
                (int) $obj->getCodCursoItem(),
                (int) $obj->getCodInscrito(),
                addslashes((string) $obj->getCodSessao()),
                addslashes((string) $obj->getTipo()),
                addslashes((string) $obj->getDataHora()),
                addslashes((string) $obj->getIp()),
                addslashes((string) $obj->getUserAgent()),
                (int) $obj->getTempoAcumulado()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function relatorioPorItem($codCursoItem, $debug = false) {
        try {
            $sql = sprintf("SELECT p.cod_inscrito, i.nome_inscrito, i.e_mail,
                    SUM(p.tipo='INICIO') AS sessoes,
                    SUM(p.tipo='CONFIRMACAO_OK') AS conf_ok,
                    SUM(p.tipo='CONFIRMACAO_EXPIRADA') AS conf_exp,
                    SUM(p.tipo='FORA_HORARIO') AS fora_hor,
                    MAX(p.tempo_acumulado) AS tempo_segundos,
                    MIN(p.data_hora) AS primeira_visita,
                    MAX(p.data_hora) AS ultima_visita
                FROM acad_curso_item_presenca p
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = p.cod_inscrito
                WHERE p.cod_curso_item = %d
                GROUP BY p.cod_inscrito, i.nome_inscrito, i.e_mail
                ORDER BY i.nome_inscrito ASC", (int) $codCursoItem);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $r) { $linhas[] = $r; }
            }
            return $linhas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function relatorioPorInscrito($codCursoItem, $codInscrito, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_curso_item_presenca
                WHERE cod_curso_item = %d AND cod_inscrito = %d
                ORDER BY data_hora ASC", (int) $codCursoItem, (int) $codInscrito);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $r) { $linhas[] = $r; }
            }
            return $linhas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Lista de alunos com presença em qualquer item do evento, com agregados:
     * total tempo efetivo, total conclusões, qtd cursos, última atividade.
     */
    public function painelAlunos($codEvento, $codCursoFiltro = null, $debug = false) {
        try {
            $whereCurso = $codCursoFiltro ? "AND i.cod_curso = " . (int) $codCursoFiltro : "";
            $sql = "SELECT
                  ins.cod_inscrito,
                  ins.nome_inscrito,
                  ins.e_mail,
                  COUNT(DISTINCT i.cod_curso) AS qtd_cursos,
                  MAX(p.tempo_acumulado) AS tempo_efetivo_seg,
                  MAX(p.data_hora) AS ultima_atividade,
                  (SELECT COUNT(*) FROM acad_curso_item_conclusao c
                     JOIN acad_curso_item ci ON ci.cod_curso_item = c.cod_curso_item
                     WHERE c.cod_inscrito = ins.cod_inscrito AND ci.cod_evento = " . (int) $codEvento . "
                       $whereCurso
                  ) AS qtd_concluidos
                FROM acad_curso_item_presenca p
                JOIN acad_curso_item i ON i.cod_curso_item = p.cod_curso_item
                JOIN tab_inscritos ins ON ins.cod_inscrito = p.cod_inscrito
                WHERE i.cod_evento = " . (int) $codEvento . " $whereCurso
                GROUP BY ins.cod_inscrito, ins.nome_inscrito, ins.e_mail
                ORDER BY ultima_atividade DESC";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) foreach ($rs as $r) $linhas[] = $r;
            return $linhas;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); return array(); }
    }

    public function progressoAlunoPorCurso($codInscrito, $codEvento, $debug = false) {
        try {
            $sql = "SELECT c.cod_curso, c.titulo,
                COUNT(DISTINCT CASE WHEN i.tipo != 'CAPITULO' THEN i.cod_curso_item END) AS total_folhas,
                COUNT(DISTINCT cl.cod_conclusao) AS folhas_concluidas,
                MAX(p.tempo_acumulado) AS tempo_efetivo_seg,
                MAX(p.data_hora) AS ultima_atividade
              FROM acad_curso c
              LEFT JOIN acad_curso_item i ON i.cod_curso = c.cod_curso AND i.status='ATIVADA'
              LEFT JOIN acad_curso_item_conclusao cl ON cl.cod_curso_item = i.cod_curso_item AND cl.cod_inscrito = " . (int) $codInscrito . "
              LEFT JOIN acad_curso_item_presenca p ON p.cod_curso_item = i.cod_curso_item AND p.cod_inscrito = " . (int) $codInscrito . "
              WHERE c.cod_evento = " . (int) $codEvento . " AND c.status='ATIVADA'
              GROUP BY c.cod_curso, c.titulo
              ORDER BY c.ordem, c.cod_curso";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) foreach ($rs as $r) $linhas[] = $r;
            return $linhas;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); return array(); }
    }

    public function detalheAlunoPorItem($codInscrito, $codEvento, $debug = false) {
        try {
            $sql = "SELECT i.cod_curso_item, i.cod_curso, i.titulo, i.tipo, c.titulo AS curso_titulo,
                MAX(CASE WHEN cl.cod_conclusao IS NOT NULL THEN 1 ELSE 0 END) AS concluido,
                MAX(p.tempo_acumulado) AS tempo_seg,
                MAX(p.data_hora) AS ultima,
                MIN(p.data_hora) AS primeira
              FROM acad_curso_item i
              LEFT JOIN acad_curso c ON c.cod_curso = i.cod_curso
              LEFT JOIN acad_curso_item_conclusao cl ON cl.cod_curso_item = i.cod_curso_item AND cl.cod_inscrito = " . (int) $codInscrito . "
              LEFT JOIN acad_curso_item_presenca p ON p.cod_curso_item = i.cod_curso_item AND p.cod_inscrito = " . (int) $codInscrito . "
              WHERE i.cod_evento = " . (int) $codEvento . " AND i.status='ATIVADA' AND i.tipo != 'CAPITULO'
              GROUP BY i.cod_curso_item, i.cod_curso, i.titulo, i.tipo, c.titulo
              ORDER BY i.cod_curso, i.ordem, i.cod_curso_item";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) foreach ($rs as $r) $linhas[] = $r;
            return $linhas;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); return array(); }
    }

    public function timelineEventos($codInscrito, $codCursoItem, $codSessao = null, $debug = false) {
        try {
            $where = "cod_inscrito=" . (int) $codInscrito . " AND cod_curso_item=" . (int) $codCursoItem;
            if ($codSessao) $where .= " AND cod_sessao='" . addslashes($codSessao) . "'";
            $sql = "SELECT cod_presenca, cod_sessao, tipo, data_hora, tempo_acumulado, ip
                FROM acad_curso_item_presenca
                WHERE $where
                ORDER BY data_hora ASC, cod_presenca ASC";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) foreach ($rs as $r) $linhas[] = $r;
            return $linhas;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); return array(); }
    }

    public function folhaPontoPorAluno($codEvento, $codInscrito = null, $debug = false) {
        try {
            $whereInsc = $codInscrito ? "AND p.cod_inscrito = " . (int) $codInscrito : "";
            $sql = "SELECT
                  p.cod_inscrito, ins.nome_inscrito, ins.e_mail,
                  DATE(p.data_hora) AS dia,
                  COUNT(DISTINCT p.cod_sessao) AS qtd_sessoes,
                  SUM(s.tempo_max_sessao) AS tempo_efetivo_seg,
                  MIN(p.data_hora) AS primeira_entrada,
                  MAX(p.data_hora) AS ultima_saida
                FROM acad_curso_item_presenca p
                JOIN acad_curso_item i ON i.cod_curso_item = p.cod_curso_item
                JOIN tab_inscritos ins ON ins.cod_inscrito = p.cod_inscrito
                JOIN (
                    SELECT cod_inscrito, cod_sessao, MAX(tempo_acumulado) AS tempo_max_sessao
                    FROM acad_curso_item_presenca
                    WHERE tipo IN ('PING','CONFIRMACAO_OK','RETOMADA','FIM')
                    GROUP BY cod_inscrito, cod_sessao
                ) s ON s.cod_inscrito = p.cod_inscrito AND s.cod_sessao = p.cod_sessao
                WHERE i.cod_evento = " . (int) $codEvento . " $whereInsc
                GROUP BY p.cod_inscrito, ins.nome_inscrito, ins.e_mail, DATE(p.data_hora)
                ORDER BY ins.nome_inscrito, dia DESC";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) foreach ($rs as $r) $linhas[] = $r;
            return $linhas;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); return array(); }
    }

    public function relatorioGeralPorEvento($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT h.cod_curso_item, h.titulo, h.tipo,
                    COUNT(DISTINCT p.cod_inscrito) AS qtd_inscritos,
                    SUM(p.tipo='CONFIRMACAO_OK') AS conf_ok,
                    SUM(p.tipo='CONFIRMACAO_EXPIRADA') AS conf_exp,
                    SUM(p.tipo='FORA_HORARIO') AS fora_hor
                FROM acad_curso_item h
                LEFT JOIN acad_curso_item_presenca p ON p.cod_curso_item = h.cod_curso_item
                WHERE h.cod_evento = %d
                GROUP BY h.cod_curso_item, h.titulo, h.tipo
                ORDER BY h.ordem ASC, h.cod_curso_item ASC", (int) $codEvento);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $r) { $linhas[] = $r; }
            }
            return $linhas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }
}
