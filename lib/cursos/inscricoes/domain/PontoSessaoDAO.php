<?php

/**
 * Folha de ponto do Curso EAD: historico de blocos de presenca na area (login/heartbeat)
 * + consultas que cruzam com os eventos de presenca de conteudo e envios de tarefa.
 */
class PontoSessaoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Heartbeat: estende o bloco atual do aluno (se o ultimo ping foi ha pouco)
     * ou abre um bloco novo (se houve intervalo grande = saiu e voltou).
     * $gapSeg = segundos de tolerancia sem ping p/ considerar o mesmo bloco.
     */
    public function heartbeat($codEvento, $codInscrito, $ip, $ua, $gapSeg = 150, $debug = false) {
        try {
            $codEvento = (int) $codEvento;
            $codInscrito = (int) $codInscrito;
            if (!$codEvento || !$codInscrito) return false;
            $ipSql = $ip ? "'" . addslashes(substr($ip, 0, 45)) . "'" : 'NULL';
            $uaSql = $ua ? "'" . addslashes(substr($ua, 0, 255)) . "'" : 'NULL';
            $this->conex->debug = $debug;

            $sql = sprintf("SELECT cod_ponto_sessao, TIMESTAMPDIFF(SECOND, fim, NOW()) AS gap
                FROM acad_ponto_sessao
                WHERE cod_evento=%d AND cod_inscrito=%d
                ORDER BY fim DESC LIMIT 1", $codEvento, $codInscrito);
            $rs = $this->conex->Execute($sql);

            // Estende so se o ultimo bloco terminou ha POUCO (gap entre 0 e gapSeg).
            // gap < 0 = fim no futuro (dado inconsistente) -> abre bloco novo, nunca duracao negativa.
            $gap = ($rs && !$rs->EOF) ? (int) $rs->fields['gap'] : null;
            if ($gap !== null && $gap >= 0 && $gap <= (int) $gapSeg) {
                $cod = (int) $rs->fields['cod_ponto_sessao'];
                $this->conex->Execute(sprintf(
                    "UPDATE acad_ponto_sessao SET fim=NOW(), segundos=TIMESTAMPDIFF(SECOND, inicio, NOW())
                     WHERE cod_ponto_sessao=%d", $cod));
            } else {
                $this->conex->Execute(sprintf(
                    "INSERT INTO acad_ponto_sessao (cod_evento, cod_inscrito, inicio, fim, segundos, ip, user_agent)
                     VALUES (%d, %d, NOW(), NOW(), 0, %s, %s)",
                    $codEvento, $codInscrito, $ipSql, $uaSql));
            }
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Alunos que tem registro de ponto no evento (para o seletor do relatorio). */
    public function alunosComPonto($codEvento, $debug = false) {
        $out = array();
        try {
            $sql = sprintf("SELECT s.cod_inscrito, i.nome_inscrito, i.e_mail,
                    COUNT(DISTINCT DATE(s.inicio)) AS dias,
                    SUM(s.segundos) AS total_seg,
                    MAX(s.fim) AS ultima
                FROM acad_ponto_sessao s
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = s.cod_inscrito
                WHERE s.cod_evento=%d
                GROUP BY s.cod_inscrito, i.nome_inscrito, i.e_mail
                ORDER BY i.nome_inscrito ASC", (int) $codEvento);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) foreach ($rs as $r) $out[] = $r;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); }
        return $out;
    }

    /** Blocos de presenca na area (login) do aluno, em ordem cronologica. */
    public function sessoesDoAluno($codEvento, $codInscrito, $debug = false) {
        $out = array();
        try {
            $sql = sprintf("SELECT cod_ponto_sessao, DATE(inicio) AS dia, inicio, fim, segundos, ip
                FROM acad_ponto_sessao
                WHERE cod_evento=%d AND cod_inscrito=%d
                ORDER BY inicio ASC", (int) $codEvento, (int) $codInscrito);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) foreach ($rs as $r) $out[] = $r;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); }
        return $out;
    }

    /**
     * COMPROMISSO (regra derivada, sem duplicar): oficinas ATIVADAS do aluno,
     * com carga_horaria / horario / periodo ja cadastrados em tab_opcoes_oficinas.
     * O agregado (soma carga, min/max horario/data) e' feito no controller.
     */
    public function oficinasDoAluno($codEvento, $codInscrito, $debug = false) {
        $out = array();
        try {
            $sql = sprintf("SELECT o.cod_oficina, o.titulo, o.carga_horaria, o.horario_ini, o.horario_fim, o.data_ini, o.data_fim
                FROM tab_lig_inscritos_oficinas l
                JOIN tab_opcoes_oficinas o ON o.cod_oficina = l.cod_oficina
                WHERE l.cod_evento=%d AND l.cod_inscrito=%d AND l.status='ATIVADA'",
                (int) $codEvento, (int) $codInscrito);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) foreach ($rs as $r) $out[] = $r;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); }
        return $out;
    }

    /** Dias uteis (tab_calendario_base) num periodo, excluindo fim de semana e feriados. */
    public function diasUteis($dataIni, $dataFim, $debug = false) {
        try {
            if (!$dataIni || !$dataFim) return 0;
            $sql = sprintf("SELECT COUNT(*) AS n FROM tab_calendario_base
                WHERE dia_calendario BETWEEN '%s' AND '%s' AND tipo_dia='Util' AND eh_feriado=0",
                addslashes(substr($dataIni, 0, 10)), addslashes(substr($dataFim, 0, 10)));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return ($rs && !$rs->EOF) ? (int) $rs->fields['n'] : 0;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); return 0; }
    }

    /**
     * O que o aluno fez (timeline): sessoes de conteudo (dos eventos de presenca)
     * + envios de tarefa. Ordenado por inicio.
     */
    public function atividadesDoAluno($codEvento, $codInscrito, $debug = false) {
        $out = array();
        try {
            $sql = sprintf("SELECT * FROM (
                    SELECT p.cod_curso_item AS cod, ci.titulo AS titulo, ci.tipo AS tipo_item, 'CONTEUDO' AS natureza,
                           MIN(p.data_hora) AS inicio, MAX(p.data_hora) AS fim,
                           TIMESTAMPDIFF(SECOND, MIN(p.data_hora), MAX(p.data_hora)) AS segundos
                    FROM acad_curso_item_presenca p
                    JOIN acad_curso_item ci ON ci.cod_curso_item = p.cod_curso_item
                    WHERE ci.cod_evento=%d AND p.cod_inscrito=%d
                    GROUP BY p.cod_curso_item, ci.titulo, ci.tipo, p.cod_sessao
                    UNION ALL
                    SELECT t.cod_curso_item, ci.titulo, ci.tipo, 'TAREFA' AS natureza,
                           t.dat_envio AS inicio, t.dat_envio AS fim, 0 AS segundos
                    FROM acad_curso_item_tarefa t
                    JOIN acad_curso_item ci ON ci.cod_curso_item = t.cod_curso_item
                    WHERE ci.cod_evento=%d AND t.cod_inscrito=%d
                ) x
                ORDER BY inicio ASC",
                (int) $codEvento, (int) $codInscrito, (int) $codEvento, (int) $codInscrito);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) foreach ($rs as $r) $out[] = $r;
        } catch (ADODB_Exception $e) { $this->enviarMensagem($e); }
        return $out;
    }
}
