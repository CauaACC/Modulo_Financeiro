<?php

/**
 * DAO do dashboard do evento (filtrado por COD_EVENTO).
 *
 * Reune queries reaproveitadas dos arquivos legados:
 * - consulta-dados-congresso-listagem.php (2888 linhas)
 * - consulta-dados-congresso-graficos-trabalhos.php (534 linhas)
 * - consulta-dados-congresso-graficos.php (117 linhas)
 *
 * Todas filtram por cod_evento. Somente leitura.
 */
class DashboardAvaliacaoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    // ============================================================
    // INSCRITOS
    // ============================================================

    public function getInscritosPorDia($codEvento, $dias = 30, $debug = false) {
        $codEvento = (int) $codEvento;
        $dias = (int) $dias;
        $sql = "SELECT DATE(data_inscricao) AS dia, COUNT(*) AS total
                FROM tab_inscritos
                WHERE cod_evento = {$codEvento}
                  AND status = 'ATIVADA'
                  AND origem_cadastro = 'INSCRICAO'
                  AND data_inscricao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)
                GROUP BY DATE(data_inscricao)
                ORDER BY DATE(data_inscricao)";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getInscritosPorEstado($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT estado, COUNT(*) AS total
                FROM tab_inscritos
                WHERE cod_evento = {$codEvento}
                  AND status = 'ATIVADA'
                  AND origem_cadastro = 'INSCRICAO'
                  AND estado IS NOT NULL AND estado <> ''
                GROUP BY estado
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getInscritosPorCidadePR($codEvento, $limit = 20, $debug = false) {
        $codEvento = (int) $codEvento;
        $limit = (int) $limit;
        $sql = "SELECT cidade, COUNT(*) AS total
                FROM tab_inscritos
                WHERE cod_evento = {$codEvento}
                  AND status = 'ATIVADA'
                  AND origem_cadastro = 'INSCRICAO'
                  AND estado = 'PR'
                  AND cidade IS NOT NULL AND cidade <> ''
                GROUP BY cidade
                ORDER BY total DESC
                LIMIT {$limit}";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getInscritosPorPerfil($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT p.titulo, COUNT(*) AS total
                FROM tab_inscritos i
                LEFT JOIN tab_opcoes_perfis p
                  ON p.cod_perfil = i.cod_perfil AND p.cod_evento = i.cod_evento
                WHERE i.cod_evento = {$codEvento}
                  AND i.status = 'ATIVADA'
                  AND i.origem_cadastro = 'INSCRICAO'
                GROUP BY p.cod_perfil, p.titulo
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getInscritosPorInstituicao($codEvento, $limit = 15, $debug = false) {
        $codEvento = (int) $codEvento;
        $limit = (int) $limit;
        $sql = "SELECT instituicao, COUNT(*) AS total
                FROM tab_inscritos
                WHERE cod_evento = {$codEvento}
                  AND status = 'ATIVADA'
                  AND origem_cadastro = 'INSCRICAO'
                  AND instituicao IS NOT NULL AND instituicao <> ''
                GROUP BY instituicao
                ORDER BY total DESC
                LIMIT {$limit}";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getLoginsPorDia($codEvento, $dias = 30, $sucesso = 1, $debug = false) {
        $codEvento = (int) $codEvento;
        $dias = (int) $dias;
        $filtroSucesso = '';
        if ($sucesso === 1 || $sucesso === 0) {
            $filtroSucesso = " AND sucesso = " . ((int) $sucesso);
        }
        $sql = "SELECT DATE(data_inclusao) AS dia, COUNT(*) AS total
                FROM sis_log_acesso
                WHERE cod_evento = {$codEvento}
                  AND data_inclusao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)
                  {$filtroSucesso}
                GROUP BY DATE(data_inclusao)
                ORDER BY DATE(data_inclusao)";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getTotaisInscritos($codEvento, $dias = 30, $debug = false) {
        $codEvento = (int) $codEvento;
        $dias = (int) $dias;
        $sql = "SELECT
                    (SELECT COUNT(*) FROM tab_inscritos
                     WHERE cod_evento={$codEvento} AND status='ATIVADA' AND origem_cadastro='INSCRICAO') AS total_inscritos,
                    (SELECT COUNT(*) FROM tab_inscritos
                     WHERE cod_evento={$codEvento} AND status='ATIVADA' AND origem_cadastro='INSCRICAO'
                       AND data_inscricao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)) AS inscritos_periodo,
                    (SELECT COUNT(*) FROM sis_log_acesso
                     WHERE cod_evento={$codEvento} AND sucesso=1
                       AND data_inclusao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)) AS logins_sucesso,
                    (SELECT COUNT(*) FROM sis_log_acesso
                     WHERE cod_evento={$codEvento} AND sucesso=0
                       AND data_inclusao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)) AS logins_falha";
        $rs = $this->conex->Execute($sql);
        if ($rs && !$rs->EOF) {
            return [
                'total_inscritos'   => (int) $rs->fields['total_inscritos'],
                'inscritos_periodo' => (int) $rs->fields['inscritos_periodo'],
                'logins_sucesso'    => (int) $rs->fields['logins_sucesso'],
                'logins_falha'      => (int) $rs->fields['logins_falha'],
            ];
        }
        return ['total_inscritos' => 0, 'inscritos_periodo' => 0, 'logins_sucesso' => 0, 'logins_falha' => 0];
    }

    // ============================================================
    // TRABALHOS
    // ============================================================

    public function getTrabalhosPorDia($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        // Sem filtro de dias — mostra todo o período do evento (igual ao dashboard antigo).
        $sql = "SELECT DATE(data_inclusao) AS dia, COUNT(*) AS total
                FROM tab_trabalhos
                WHERE cod_evento = {$codEvento}
                  AND status = 'ATIVADA'
                  AND IFNULL(tipo_documento,'') <> 'COMPROVANTE_PERFIL'
                GROUP BY DATE(data_inclusao)
                ORDER BY DATE(data_inclusao)";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getTrabalhosPorAreaTematicaPorStatus($codEvento, $avaliacao, $debug = false) {
        $codEvento = (int) $codEvento;
        $avaliacao = addslashes($avaliacao);
        $sql = "SELECT toa.titulo, COUNT(tt.cod_trabalho) AS total
                FROM tab_trabalhos tt
                INNER JOIN tab_trabalhos_avaliacao ta ON ta.cod_trabalho = tt.cod_trabalho
                LEFT JOIN tab_opcoes_areas_tematicas toa ON toa.cod_area_tematica = tt.cod_area_tematica
                WHERE tt.cod_evento = {$codEvento}
                  AND tt.status = 'ATIVADA'
                  AND ta.status = 'ATIVADA'
                  AND ta.avaliacao = '{$avaliacao}'
                GROUP BY tt.cod_area_tematica, toa.titulo
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getTrabalhosPorEstado($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT ti.estado, COUNT(tt.cod_trabalho) AS total
                FROM tab_trabalhos tt
                INNER JOIN tab_inscritos ti ON ti.cod_inscrito = tt.cod_inscrito
                WHERE tt.cod_evento = {$codEvento}
                  AND tt.status = 'ATIVADA'
                  AND ti.status = 'ATIVADA'
                  AND ti.estado IS NOT NULL AND ti.estado <> ''
                GROUP BY ti.estado
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getTrabalhosAprovadosPorAreaFormato($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT toa.titulo, tt.formato_texto, COUNT(tt.cod_trabalho) AS total
                FROM tab_trabalhos tt
                INNER JOIN tab_trabalhos_avaliacao ta ON ta.cod_trabalho = tt.cod_trabalho
                LEFT JOIN tab_opcoes_areas_tematicas toa ON toa.cod_area_tematica = tt.cod_area_tematica
                WHERE tt.cod_evento = {$codEvento}
                  AND tt.status = 'ATIVADA'
                  AND ta.status = 'ATIVADA'
                  AND ta.avaliacao = 'APROVADO'
                  AND tt.formato_texto IS NOT NULL AND tt.formato_texto <> ''
                GROUP BY tt.cod_area_tematica, toa.titulo, tt.formato_texto
                ORDER BY toa.titulo, tt.formato_texto";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getTrabalhosPorAreaTematica($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT toa.titulo, COUNT(tt.cod_trabalho) AS total
                FROM tab_trabalhos tt
                LEFT JOIN tab_opcoes_areas_tematicas toa ON toa.cod_area_tematica = tt.cod_area_tematica
                WHERE tt.cod_evento = {$codEvento}
                  AND tt.status = 'ATIVADA'
                GROUP BY tt.cod_area_tematica, toa.titulo
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getTrabalhosPorFormato($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT formato_texto, COUNT(*) AS total
                FROM tab_trabalhos
                WHERE cod_evento = {$codEvento}
                  AND status = 'ATIVADA'
                  AND formato_texto IS NOT NULL AND formato_texto <> ''
                GROUP BY formato_texto
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getTrabalhosPorInstituicao($codEvento, $limit = 15, $debug = false) {
        $codEvento = (int) $codEvento;
        $limit = (int) $limit;
        $sql = "SELECT ti.instituicao, COUNT(tt.cod_trabalho) AS total
                FROM tab_trabalhos tt
                INNER JOIN tab_inscritos ti ON ti.cod_inscrito = tt.cod_inscrito
                WHERE tt.cod_evento = {$codEvento}
                  AND tt.status = 'ATIVADA'
                  AND ti.status = 'ATIVADA'
                  AND ti.instituicao IS NOT NULL AND ti.instituicao <> ''
                GROUP BY ti.instituicao
                ORDER BY total DESC
                LIMIT {$limit}";
        return $this->executarEBuscar($sql, $debug);
    }

    public function getTotaisTrabalhos($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT
                    (SELECT COUNT(*) FROM tab_trabalhos
                     WHERE cod_evento={$codEvento} AND status='ATIVADA'
                       AND IFNULL(tipo_documento,'') <> 'COMPROVANTE_PERFIL') AS total_trabalhos,
                    (SELECT COUNT(*) FROM tab_trabalhos tt
                     INNER JOIN tab_trabalhos_avaliacao ta ON ta.cod_trabalho=tt.cod_trabalho
                     WHERE tt.cod_evento={$codEvento} AND tt.status='ATIVADA'
                       AND ta.avaliacao='APROVADO' AND ta.status='ATIVADA') AS trabalhos_aprovados,
                    (SELECT COUNT(*) FROM tab_trabalhos tt
                     INNER JOIN tab_trabalhos_avaliacao ta ON ta.cod_trabalho=tt.cod_trabalho
                     WHERE tt.cod_evento={$codEvento} AND tt.status='ATIVADA'
                       AND ta.avaliacao='REPROVADO' AND ta.status='ATIVADA') AS trabalhos_reprovados,
                    (SELECT COUNT(*) FROM tab_trabalhos
                     WHERE cod_evento={$codEvento} AND status='ATIVADA'
                       AND flg_presenca IS NOT NULL) AS trabalhos_com_presenca";
        $rs = $this->conex->Execute($sql);
        if ($rs && !$rs->EOF) {
            return [
                'total_trabalhos'       => (int) $rs->fields['total_trabalhos'],
                'trabalhos_aprovados'   => (int) $rs->fields['trabalhos_aprovados'],
                'trabalhos_reprovados'  => (int) $rs->fields['trabalhos_reprovados'],
                'trabalhos_com_presenca'=> (int) $rs->fields['trabalhos_com_presenca'],
            ];
        }
        return ['total_trabalhos' => 0, 'trabalhos_aprovados' => 0, 'trabalhos_reprovados' => 0, 'trabalhos_com_presenca' => 0];
    }

    // ============================================================
    // AVALIACOES
    // ============================================================

    /**
     * Trabalhos agrupados por situacao da ultima avaliacao.
     * Retorna registros como: [{avaliacao: 'APROVADO', total: 10}, ...]
     */
    public function getTrabalhosPorStatusAvaliacao($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT ta.avaliacao, COUNT(ta.avaliacao) AS total
                FROM tab_historico_avaliacao ta
                INNER JOIN vw_ultima_avaliacao vua ON vua.cod_avaliacao = ta.cod_avaliacao
                INNER JOIN tab_trabalhos tt ON ta.cod_trabalho = tt.cod_trabalho AND tt.status = 'ATIVADA'
                INNER JOIN tab_inscritos ti ON ti.cod_inscrito = ta.cod_inscrito AND ti.status = 'ATIVADA'
                WHERE ta.status = 'ATIVADA'
                  AND ta.cod_evento = {$codEvento}
                GROUP BY ta.avaliacao
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Trabalhos ativos que nao tem nenhuma avaliacao registrada.
     * Util para ver quantos estao "sem avaliador".
     */
    public function getTotalTrabalhosSemAvaliador($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT COUNT(*) AS total
                FROM tab_trabalhos tt
                WHERE tt.cod_evento = {$codEvento}
                  AND tt.status = 'ATIVADA'
                  AND NOT EXISTS (
                      SELECT 1 FROM tab_historico_avaliacao ta
                      WHERE ta.cod_trabalho = tt.cod_trabalho
                        AND ta.status = 'ATIVADA'
                  )";
        $rs = $this->conex->Execute($sql);
        return ($rs && !$rs->EOF) ? (int) $rs->fields['total'] : 0;
    }

    /**
     * Top N avaliadores por quantidade de avaliacoes feitas (ultima por trabalho).
     * Junta com tab_inscritos/sis_usuarios para tentar pegar um nome.
     */
    public function getTopAvaliadores($codEvento, $limit = 10, $debug = false) {
        $codEvento = (int) $codEvento;
        $limit = (int) $limit;
        $sql = "SELECT
                    COALESCE(NULLIF(TRIM(ti.nome_inscrito), ''), CONCAT('Avaliador #', ta.cod_avaliador)) AS avaliador,
                    COUNT(DISTINCT ta.cod_trabalho) AS total
                FROM tab_historico_avaliacao ta
                INNER JOIN vw_ultima_avaliacao vua ON vua.cod_avaliacao = ta.cod_avaliacao
                LEFT JOIN tab_inscritos ti ON ti.cod_inscrito = ta.cod_avaliador AND ti.cod_evento = ta.cod_evento
                WHERE ta.status = 'ATIVADA'
                  AND ta.cod_evento = {$codEvento}
                  AND ta.cod_avaliador IS NOT NULL
                  AND ta.cod_avaliador <> 0
                GROUP BY ta.cod_avaliador, ti.nome_inscrito
                ORDER BY total DESC
                LIMIT {$limit}";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Distribuicao de inscritos por status apos recurso.
     * NULL = sem recurso avaliado ainda (ou sem recurso apresentado).
     */
    public function getInscritosPorStatusRecurso($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT
                    CASE
                        WHEN taia.classificacao_status_apos_recurso IS NULL THEN 'Sem recurso / Pendente'
                        WHEN taia.classificacao_status_apos_recurso = 'H' THEN 'Recurso Habilitado'
                        WHEN taia.classificacao_status_apos_recurso = 'I' THEN 'Recurso Inabilitado'
                        ELSE CONCAT('Outro: ', taia.classificacao_status_apos_recurso)
                    END AS situacao,
                    COUNT(*) AS total
                FROM tab_inscritos ti
                INNER JOIN tab_trabalhos_avaliacao_inscricao taia
                   ON taia.cod_inscrito = ti.cod_inscrito AND taia.status = 'ATIVADA'
                WHERE ti.cod_evento = {$codEvento}
                  AND ti.status = 'ATIVADA'
                  AND ti.origem_cadastro = 'INSCRICAO'
                GROUP BY situacao
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Distribuicao de inscritos por status final (apos recurso).
     * Reflete o resultado definitivo da avaliacao.
     */
    public function getInscritosPorStatusFinal($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT
                    CASE
                        WHEN taia.classificacao_status_final IS NULL THEN 'Pendente / Sem decisao final'
                        WHEN taia.classificacao_status_final = 'H' THEN 'Habilitado (Final)'
                        WHEN taia.classificacao_status_final = 'I' THEN 'Inabilitado (Final)'
                        ELSE CONCAT('Outro: ', taia.classificacao_status_final)
                    END AS situacao,
                    COUNT(*) AS total
                FROM tab_inscritos ti
                INNER JOIN tab_trabalhos_avaliacao_inscricao taia
                   ON taia.cod_inscrito = ti.cod_inscrito AND taia.status = 'ATIVADA'
                WHERE ti.cod_evento = {$codEvento}
                  AND ti.status = 'ATIVADA'
                  AND ti.origem_cadastro = 'INSCRICAO'
                GROUP BY situacao
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Totais de avaliacao de inscritos: total geral, com recurso apresentado, com decisao final.
     */
    public function getTotaisAvaliacaoInscrito($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT
                    (SELECT COUNT(*) FROM tab_inscritos
                     WHERE cod_evento={$codEvento} AND status='ATIVADA' AND origem_cadastro='INSCRICAO') AS total_inscritos,
                    (SELECT COUNT(*) FROM tab_trabalhos_avaliacao_inscricao taia
                     INNER JOIN tab_inscritos ti ON ti.cod_inscrito=taia.cod_inscrito
                     WHERE ti.cod_evento={$codEvento} AND ti.status='ATIVADA'
                       AND taia.status='ATIVADA'
                       AND taia.classificacao_status IS NOT NULL) AS avaliados,
                    (SELECT COUNT(*) FROM tab_trabalhos_avaliacao_inscricao taia
                     INNER JOIN tab_inscritos ti ON ti.cod_inscrito=taia.cod_inscrito
                     WHERE ti.cod_evento={$codEvento} AND ti.status='ATIVADA'
                       AND taia.status='ATIVADA'
                       AND taia.classificacao_status_apos_recurso IS NOT NULL) AS com_decisao_recurso,
                    (SELECT COUNT(*) FROM tab_trabalhos_avaliacao_inscricao taia
                     INNER JOIN tab_inscritos ti ON ti.cod_inscrito=taia.cod_inscrito
                     WHERE ti.cod_evento={$codEvento} AND ti.status='ATIVADA'
                       AND taia.status='ATIVADA'
                       AND taia.classificacao_status_final IS NOT NULL) AS com_decisao_final";
        $rs = $this->conex->Execute($sql);
        if ($rs && !$rs->EOF) {
            return [
                'total_inscritos'     => (int) $rs->fields['total_inscritos'],
                'avaliados'           => (int) $rs->fields['avaliados'],
                'com_decisao_recurso' => (int) $rs->fields['com_decisao_recurso'],
                'com_decisao_final'   => (int) $rs->fields['com_decisao_final'],
            ];
        }
        return ['total_inscritos'=>0, 'avaliados'=>0, 'com_decisao_recurso'=>0, 'com_decisao_final'=>0];
    }

    /**
     * Distribuicao de inscritos por situacao na avaliacao (habilitado, inabilitado, nao avaliado).
     * Usa tab_trabalhos_avaliacao_inscricao com status ATIVADA.
     */
    public function getInscritosPorStatusAvaliacao($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT
                    CASE
                        WHEN taia.classificacao_status IS NULL THEN 'Nao Avaliado'
                        WHEN taia.classificacao_status = 'H' THEN 'Habilitado'
                        WHEN taia.classificacao_status = 'I' THEN 'Inabilitado'
                        ELSE CONCAT('Status: ', taia.classificacao_status)
                    END AS situacao,
                    COUNT(*) AS total
                FROM tab_inscritos ti
                LEFT JOIN tab_trabalhos_avaliacao_inscricao taia
                  ON taia.cod_inscrito = ti.cod_inscrito AND taia.status = 'ATIVADA'
                WHERE ti.cod_evento = {$codEvento}
                  AND ti.status = 'ATIVADA'
                  AND ti.origem_cadastro = 'INSCRICAO'
                GROUP BY situacao
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    // ============================================================
    // PROGRESSO POR FASE (avaliacao de inscritos por etapa)
    // ============================================================

    /**
     * Retorna metricas agregadas por etapa de avaliacao do evento.
     * Para cada linha de tab_avaliacao_etapa traz:
     *   - cod_etapa, ordem_etapa, nome_etapa, situacao
     *   - total_avaliacoes  (rows existentes em tab_trabalhos_avaliacao_inscricao)
     *   - total_recursos    (rows com recurso_texto preenchido)
     *   - recursos_avaliados (recursos com classificacao_status_apos_recurso preenchido)
     *   - avaliados_humano  (rows com cod_avaliador setado)
     *   - habilitados / nao_habilitados (com base em classificacao_status_final, ou status original)
     *   - avaliacao_inicial_decidida, habilitados_inicial, nao_habilitados_inicial (sub-etapa 1)
     *   - habilitados_pos_recurso, nao_habilitados_pos_recurso (sub-etapa 2 — usa coalesce com status original)
     *   - total_publicado, habilitados_final, nao_habilitados_final (sub-etapa 3 — só conta status_final preenchido)
     */
    public function getProgressoFases($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        // elegiveis: pra ordem 1 = total inscritos ATIVADAs do evento (origem INSCRICAO).
        //            pra ordem > 1 = Habilitados (status efetivo = 'H') da fase de ordem N-1.
        // em_andamento_historico: avaliadores que "pegaram" o inscrito (SELECIONADO ativo)
        //            sem decisao posterior do mesmo avaliador no historico.
        $sql = "SELECT
                    tae.cod_etapa,
                    tae.ordem_etapa,
                    tae.nome_etapa,
                    tae.situacao,
                    COUNT(tia.cod_avaliacao) AS total_avaliacoes,
                    SUM(CASE WHEN tia.recurso_texto IS NOT NULL AND tia.recurso_texto <> '' THEN 1 ELSE 0 END) AS total_recursos,
                    SUM(CASE WHEN tia.classificacao_status_apos_recurso IN ('H','I') THEN 1 ELSE 0 END) AS recursos_avaliados,
                    SUM(CASE WHEN tia.cod_avaliador IS NOT NULL AND tia.cod_avaliador <> 0 THEN 1 ELSE 0 END) AS avaliados_humano,
                    SUM(CASE WHEN COALESCE(NULLIF(tia.classificacao_status_final,''), NULLIF(tia.classificacao_status_apos_recurso,''), tia.classificacao_status) = 'H' THEN 1 ELSE 0 END) AS habilitados,
                    SUM(CASE WHEN COALESCE(NULLIF(tia.classificacao_status_final,''), NULLIF(tia.classificacao_status_apos_recurso,''), tia.classificacao_status) = 'I' THEN 1 ELSE 0 END) AS nao_habilitados,
                    SUM(CASE WHEN tia.classificacao_status IN ('H','I') THEN 1 ELSE 0 END) AS avaliacao_inicial_decidida,
                    SUM(CASE WHEN tia.classificacao_status = 'H' THEN 1 ELSE 0 END) AS habilitados_inicial,
                    SUM(CASE WHEN tia.classificacao_status = 'I' THEN 1 ELSE 0 END) AS nao_habilitados_inicial,
                    SUM(CASE WHEN COALESCE(NULLIF(tia.classificacao_status_apos_recurso,''), tia.classificacao_status) = 'H' THEN 1 ELSE 0 END) AS habilitados_pos_recurso,
                    SUM(CASE WHEN COALESCE(NULLIF(tia.classificacao_status_apos_recurso,''), tia.classificacao_status) = 'I' THEN 1 ELSE 0 END) AS nao_habilitados_pos_recurso,
                    SUM(CASE WHEN tia.classificacao_status_final IN ('H','I') THEN 1 ELSE 0 END) AS total_publicado,
                    SUM(CASE WHEN tia.classificacao_status_final = 'H' THEN 1 ELSE 0 END) AS habilitados_final,
                    SUM(CASE WHEN tia.classificacao_status_final = 'I' THEN 1 ELSE 0 END) AS nao_habilitados_final,
                    (CASE WHEN tae.ordem_etapa = 1 THEN
                        (SELECT COUNT(*) FROM tab_inscritos ti_e
                          WHERE ti_e.cod_evento = tae.cod_evento
                            AND ti_e.status = 'ATIVADA'
                            AND ti_e.origem_cadastro = 'INSCRICAO')
                     ELSE
                        (SELECT COUNT(DISTINCT tia_prev.cod_inscrito)
                           FROM tab_trabalhos_avaliacao_inscricao tia_prev
                           JOIN tab_avaliacao_etapa tae_prev
                                ON tae_prev.cod_etapa = tia_prev.cod_etapa
                               AND tae_prev.cod_evento = tia_prev.cod_evento
                          WHERE tia_prev.cod_evento = tae.cod_evento
                            AND tia_prev.status = 'ATIVADA'
                            AND tae_prev.ordem_etapa = tae.ordem_etapa - 1
                            AND COALESCE(NULLIF(tia_prev.classificacao_status_final,''),
                                         NULLIF(tia_prev.classificacao_status_apos_recurso,''),
                                         tia_prev.classificacao_status) = 'H')
                    END) AS elegiveis,
                    (SELECT COUNT(DISTINCT thai.cod_inscrito)
                       FROM tab_trabalhos_historico_avaliacao_inscricao thai
                      WHERE thai.cod_evento = tae.cod_evento
                        AND thai.cod_etapa  = tae.cod_etapa
                        AND thai.status     = 'ATIVADA'
                        AND thai.avaliacao  = 'SELECIONADO'
                        AND NOT EXISTS (
                            SELECT 1 FROM tab_trabalhos_historico_avaliacao_inscricao thai2
                             WHERE thai2.cod_evento    = thai.cod_evento
                               AND thai2.cod_inscrito  = thai.cod_inscrito
                               AND thai2.cod_etapa     = thai.cod_etapa
                               AND thai2.cod_avaliador = thai.cod_avaliador
                               AND thai2.avaliacao IN ('HOMOLOGADO','NAO HOMOLOGADO')
                               AND thai2.status        = 'ATIVADA'
                        )
                    ) AS em_andamento_historico
                FROM tab_avaliacao_etapa tae
                LEFT JOIN tab_trabalhos_avaliacao_inscricao tia
                    ON tia.cod_etapa = tae.cod_etapa
                   AND tia.cod_evento = tae.cod_evento
                   AND tia.status = 'ATIVADA'
                WHERE tae.cod_evento = {$codEvento}
                  AND tae.status = 'ATIVADA'
                GROUP BY tae.cod_etapa, tae.ordem_etapa, tae.nome_etapa, tae.situacao
                ORDER BY tae.ordem_etapa";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Avaliadores em andamento por etapa ATIVA (situacao=1).
     * Le da tab_trabalhos_historico_avaliacao_inscricao:
     *   - pegou_sem_decisao: SELECIONADO ativo (sem decisao posterior do mesmo avaliador)
     *   - avaliadas: HOMOLOGADO ou NAO HOMOLOGADO registrados
     *   - total_atribuidas: distinct inscritos que o avaliador pegou ou decidiu
     */
    public function getAvaliadoresEmAndamento($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT
                    tae.cod_etapa,
                    tae.ordem_etapa,
                    tae.nome_etapa,
                    su.login,
                    COALESCE(NULLIF(TRIM(tc.nome_convidado),''),
                             NULLIF(TRIM(su.nome_usuario),''),
                             su.login,
                             CONCAT('Avaliador #', thai.cod_avaliador)) AS avaliador,
                    COUNT(DISTINCT CASE
                        WHEN thai.avaliacao IN ('HOMOLOGADO','NAO HOMOLOGADO') THEN thai.cod_inscrito
                    END) AS avaliadas,
                    COUNT(DISTINCT CASE
                        WHEN thai.avaliacao = 'SELECIONADO'
                         AND NOT EXISTS (
                            SELECT 1 FROM tab_trabalhos_historico_avaliacao_inscricao thai2
                             WHERE thai2.cod_evento    = thai.cod_evento
                               AND thai2.cod_inscrito  = thai.cod_inscrito
                               AND thai2.cod_etapa     = thai.cod_etapa
                               AND thai2.cod_avaliador = thai.cod_avaliador
                               AND thai2.avaliacao IN ('HOMOLOGADO','NAO HOMOLOGADO')
                               AND thai2.status = 'ATIVADA'
                         ) THEN thai.cod_inscrito
                    END) AS pegou_sem_decisao,
                    COUNT(DISTINCT thai.cod_inscrito) AS total_atribuidas
                FROM tab_trabalhos_historico_avaliacao_inscricao thai
                INNER JOIN tab_avaliacao_etapa tae
                    ON tae.cod_etapa  = thai.cod_etapa
                   AND tae.cod_evento = thai.cod_evento
                   AND tae.situacao   = 1
                INNER JOIN sis_usuarios   su ON su.cod_usuario = thai.cod_avaliador
                LEFT JOIN tab_convidados  tc ON tc.cod_usuario = su.cod_usuario
                WHERE thai.cod_evento = {$codEvento}
                  AND thai.status     = 'ATIVADA'
                  AND thai.cod_avaliador IS NOT NULL
                  AND thai.cod_avaliador <> 0
                GROUP BY tae.cod_etapa, tae.ordem_etapa, tae.nome_etapa,
                         thai.cod_avaliador, su.login, tc.nome_convidado, su.nome_usuario
                ORDER BY tae.ordem_etapa, pegou_sem_decisao DESC, avaliadas DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Top avaliadores de inscritos: conta cod_avaliador em
     * tab_trabalhos_avaliacao_inscricao do evento atual.
     */
    public function getTopAvaliadoresInscritos($codEvento, $limit = 10, $debug = false) {
        $codEvento = (int) $codEvento;
        $limit = (int) $limit;
        $sql = "SELECT
                    COALESCE(NULLIF(TRIM(tc.nome_convidado),''),
                             NULLIF(TRIM(su.nome_usuario),''),
                             su.login,
                             CONCAT('Avaliador #', tia.cod_avaliador)) AS avaliador,
                    su.login,
                    COUNT(*) AS total
                FROM tab_trabalhos_avaliacao_inscricao tia
                INNER JOIN sis_usuarios su ON su.cod_usuario = tia.cod_avaliador
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE tia.cod_evento = {$codEvento}
                  AND tia.status = 'ATIVADA'
                  AND tia.cod_avaliador IS NOT NULL
                  AND tia.cod_avaliador <> 0
                GROUP BY tia.cod_avaliador, su.login, tc.nome_convidado, su.nome_usuario
                ORDER BY total DESC
                LIMIT {$limit}";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Estatisticas de pontuacao da Fase 2 do evento.
     * Retorna min, max, media, mediana e contagem dos valores
     * de pontos_total_confirmado preenchidos na etapa de ordem 2.
     */
    public function getEstatisticasPontuacaoFase2($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT
                    COUNT(*) AS total,
                    MIN(CAST(tia.pontos_total_confirmado AS DECIMAL(10,2))) AS minimo,
                    MAX(CAST(tia.pontos_total_confirmado AS DECIMAL(10,2))) AS maximo,
                    AVG(CAST(tia.pontos_total_confirmado AS DECIMAL(10,2))) AS media
                FROM tab_trabalhos_avaliacao_inscricao tia
                INNER JOIN tab_avaliacao_etapa tae
                    ON tae.cod_etapa = tia.cod_etapa
                   AND tae.cod_evento = tia.cod_evento
                   AND tae.ordem_etapa = 2
                WHERE tia.cod_evento = {$codEvento}
                  AND tia.status = 'ATIVADA'
                  AND tia.pontos_total_confirmado IS NOT NULL
                  AND tia.pontos_total_confirmado <> ''
                  AND tia.pontos_total_confirmado <> '0'";
        $rs = $this->conex->Execute($sql);
        if (!$rs || $rs->EOF) {
            return ['total' => 0, 'minimo' => 0, 'maximo' => 0, 'media' => 0, 'mediana' => 0];
        }
        $linha = ['total' => (int) $rs->fields['total'],
                  'minimo' => (float) $rs->fields['minimo'],
                  'maximo' => (float) $rs->fields['maximo'],
                  'media'  => round((float) $rs->fields['media'], 2),
                  'mediana' => 0];
        if ($linha['total'] === 0) return $linha;

        // Mediana via PHP (mais portável que CTE/percentile)
        $sqlValores = "SELECT CAST(tia.pontos_total_confirmado AS DECIMAL(10,2)) AS p
                       FROM tab_trabalhos_avaliacao_inscricao tia
                       INNER JOIN tab_avaliacao_etapa tae
                           ON tae.cod_etapa = tia.cod_etapa
                          AND tae.cod_evento = tia.cod_evento
                          AND tae.ordem_etapa = 2
                       WHERE tia.cod_evento = {$codEvento}
                         AND tia.status = 'ATIVADA'
                         AND tia.pontos_total_confirmado IS NOT NULL
                         AND tia.pontos_total_confirmado <> ''
                         AND tia.pontos_total_confirmado <> '0'
                       ORDER BY p";
        $rsV = $this->conex->Execute($sqlValores);
        $valores = [];
        if ($rsV) {
            while (!$rsV->EOF) { $valores[] = (float) $rsV->fields['p']; $rsV->MoveNext(); }
        }
        $n = count($valores);
        if ($n > 0) {
            $meio = (int) floor(($n - 1) / 2);
            $linha['mediana'] = ($n % 2 === 1)
                ? $valores[$meio]
                : round(($valores[$meio] + $valores[$meio + 1]) / 2, 2);
        }
        return $linha;
    }

    /**
     * Histograma de pontuacao da Fase 2 em buckets de 20 pontos
     * (0-19,99 / 20-39,99 / 40-59,99 / 60-79,99 / 80-99,99 / 100+).
     */
    public function getHistogramaPontuacaoFase2($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT
                    CASE
                        WHEN CAST(tia.pontos_total_confirmado AS DECIMAL(10,2)) <  20 THEN '00-19'
                        WHEN CAST(tia.pontos_total_confirmado AS DECIMAL(10,2)) <  40 THEN '20-39'
                        WHEN CAST(tia.pontos_total_confirmado AS DECIMAL(10,2)) <  60 THEN '40-59'
                        WHEN CAST(tia.pontos_total_confirmado AS DECIMAL(10,2)) <  80 THEN '60-79'
                        WHEN CAST(tia.pontos_total_confirmado AS DECIMAL(10,2)) < 100 THEN '80-99'
                        ELSE '100+'
                    END AS faixa,
                    COUNT(*) AS total
                FROM tab_trabalhos_avaliacao_inscricao tia
                INNER JOIN tab_avaliacao_etapa tae
                    ON tae.cod_etapa = tia.cod_etapa
                   AND tae.cod_evento = tia.cod_evento
                   AND tae.ordem_etapa = 2
                WHERE tia.cod_evento = {$codEvento}
                  AND tia.status = 'ATIVADA'
                  AND tia.pontos_total_confirmado IS NOT NULL
                  AND tia.pontos_total_confirmado <> ''
                  AND tia.pontos_total_confirmado <> '0'
                GROUP BY faixa
                ORDER BY faixa";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Avaliacoes (humanas) por dia nos ultimos N dias.
     * Conta linhas em tab_trabalhos_avaliacao_inscricao com cod_avaliador
     * preenchido, agrupadas pela data_alteracao (quando avaliador salvou).
     */
    public function getAvaliacoesInscritoPorDia($codEvento, $dias = 30, $debug = false) {
        $codEvento = (int) $codEvento;
        $dias = max(1, (int) $dias);
        $sql = "SELECT DATE(tia.data_alteracao) AS dia, COUNT(*) AS total
                FROM tab_trabalhos_avaliacao_inscricao tia
                WHERE tia.cod_evento = {$codEvento}
                  AND tia.status = 'ATIVADA'
                  AND tia.cod_avaliador IS NOT NULL
                  AND tia.cod_avaliador <> 0
                  AND tia.data_alteracao IS NOT NULL
                  AND DATE(tia.data_alteracao) >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)
                GROUP BY dia
                ORDER BY dia";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Avaliador x Fase x Momento (inicial / pos-recurso / final).
     * Retorna 1 linha por (cod_avaliador, cod_etapa) com a quantidade de
     * avaliacoes em cada momento. View pivota para tabela com colunas
     * "F1 Inicial / F1 Recurso / F1 Final / F2 Inicial / ...".
     */
    public function getAvaliadoresPorFaseMomento($codEvento, $limit = 20, $debug = false) {
        $codEvento = (int) $codEvento;
        $limit = (int) $limit;
        $sqlTop = "SELECT cod_avaliador
                   FROM tab_trabalhos_avaliacao_inscricao
                   WHERE cod_evento = {$codEvento}
                     AND status = 'ATIVADA'
                     AND cod_avaliador IS NOT NULL AND cod_avaliador <> 0
                   GROUP BY cod_avaliador
                   ORDER BY COUNT(*) DESC
                   LIMIT {$limit}";
        $rsTop = $this->conex->Execute($sqlTop);
        $codigos = [];
        if ($rsTop) { while (!$rsTop->EOF) { $codigos[] = (int) $rsTop->fields['cod_avaliador']; $rsTop->MoveNext(); } }
        if (empty($codigos)) return [];
        $listaCods = implode(',', $codigos);
        $sql = "SELECT
                    tae.cod_etapa,
                    tae.ordem_etapa,
                    tae.nome_etapa,
                    tia.cod_avaliador,
                    su.login,
                    COALESCE(NULLIF(TRIM(tc.nome_convidado),''),
                             NULLIF(TRIM(su.nome_usuario),''),
                             su.login,
                             CONCAT('Avaliador #', tia.cod_avaliador)) AS avaliador,
                    SUM(CASE WHEN tia.classificacao_status IN ('H','I') THEN 1 ELSE 0 END) AS aval_inicial,
                    SUM(CASE WHEN tia.classificacao_status_apos_recurso IN ('H','I') THEN 1 ELSE 0 END) AS aval_recurso,
                    SUM(CASE WHEN tia.classificacao_status_final IN ('H','I') THEN 1 ELSE 0 END) AS aval_final,
                    COUNT(*) AS total_atribuidas
                FROM tab_trabalhos_avaliacao_inscricao tia
                INNER JOIN tab_avaliacao_etapa tae
                    ON tae.cod_etapa = tia.cod_etapa
                   AND tae.cod_evento = tia.cod_evento
                   AND tae.status = 'ATIVADA'
                INNER JOIN sis_usuarios su ON su.cod_usuario = tia.cod_avaliador
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE tia.cod_evento = {$codEvento}
                  AND tia.status = 'ATIVADA'
                  AND tia.cod_avaliador IN ({$listaCods})
                GROUP BY tae.cod_etapa, tae.ordem_etapa, tae.nome_etapa,
                         tia.cod_avaliador, su.login, tc.nome_convidado, su.nome_usuario
                ORDER BY tae.ordem_etapa, tia.cod_avaliador";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Evolucao por dia dos top N avaliadores (ultimos N dias).
     * Retorna [dia, cod_avaliador, avaliador, total]. View vira em series
     * para um grafico de linhas (uma serie por avaliador).
     */
    public function getEvolucaoAvaliadoresPorDia($codEvento, $dias = 30, $limit = 10, $debug = false) {
        $codEvento = (int) $codEvento;
        $dias = max(1, (int) $dias);
        $limit = (int) $limit;
        $sqlTop = "SELECT cod_avaliador
                   FROM tab_trabalhos_avaliacao_inscricao
                   WHERE cod_evento = {$codEvento}
                     AND status = 'ATIVADA'
                     AND cod_avaliador IS NOT NULL AND cod_avaliador <> 0
                     AND data_alteracao IS NOT NULL
                     AND DATE(data_alteracao) >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)
                   GROUP BY cod_avaliador
                   ORDER BY COUNT(*) DESC
                   LIMIT {$limit}";
        $rsTop = $this->conex->Execute($sqlTop);
        $codigos = [];
        if ($rsTop) { while (!$rsTop->EOF) { $codigos[] = (int) $rsTop->fields['cod_avaliador']; $rsTop->MoveNext(); } }
        if (empty($codigos)) return [];
        $listaCods = implode(',', $codigos);
        $sql = "SELECT
                    DATE(tia.data_alteracao) AS dia,
                    tia.cod_avaliador,
                    COALESCE(NULLIF(TRIM(tc.nome_convidado),''),
                             NULLIF(TRIM(su.nome_usuario),''),
                             su.login,
                             CONCAT('#', tia.cod_avaliador)) AS avaliador,
                    COUNT(*) AS total
                FROM tab_trabalhos_avaliacao_inscricao tia
                INNER JOIN sis_usuarios su ON su.cod_usuario = tia.cod_avaliador
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE tia.cod_evento = {$codEvento}
                  AND tia.status = 'ATIVADA'
                  AND tia.cod_avaliador IN ({$listaCods})
                  AND tia.data_alteracao IS NOT NULL
                  AND DATE(tia.data_alteracao) >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)
                GROUP BY dia, tia.cod_avaliador, su.login, tc.nome_convidado, su.nome_usuario
                ORDER BY dia, tia.cod_avaliador";
        return $this->executarEBuscar($sql, $debug);
    }

    // ============================================================
    // ATIVIDADE RECENTE (ultimos envios)
    // ============================================================

    /**
     * Ultimas N inscricoes ativadas do evento, com data/hora.
     */
    public function getUltimasInscricoes($codEvento, $limit = 10, $debug = false) {
        $codEvento = (int) $codEvento;
        $limit = (int) $limit;
        $sql = "SELECT ti.cod_inscrito,
                       ti.nome_inscrito,
                       ti.data_inscricao,
                       p.titulo AS perfil
                FROM tab_inscritos ti
                LEFT JOIN tab_opcoes_perfis p
                  ON p.cod_perfil = ti.cod_perfil AND p.cod_evento = ti.cod_evento
                WHERE ti.cod_evento = {$codEvento}
                  AND ti.status = 'ATIVADA'
                  AND ti.origem_cadastro = 'INSCRICAO'
                ORDER BY ti.data_inscricao DESC
                LIMIT {$limit}";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Ultimos N trabalhos submetidos (ativos), com autor e data/hora.
     */
    public function getUltimosTrabalhos($codEvento, $limit = 10, $debug = false) {
        $codEvento = (int) $codEvento;
        $limit = (int) $limit;
        $sql = "SELECT tt.cod_trabalho,
                       tt.cod_inscrito,
                       tt.titulo_trabalho,
                       tt.data_inclusao,
                       ti.nome_inscrito
                FROM tab_trabalhos tt
                LEFT JOIN tab_inscritos ti
                  ON ti.cod_inscrito = tt.cod_inscrito AND ti.cod_evento = tt.cod_evento
                WHERE tt.cod_evento = {$codEvento}
                  AND tt.status = 'ATIVADA'
                ORDER BY tt.data_inclusao DESC
                LIMIT {$limit}";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Ultimos N arquivos enviados (apresentacao ou avulso) do evento.
     */
    public function getUltimosArquivos($codEvento, $limit = 10, $debug = false) {
        $codEvento = (int) $codEvento;
        $limit = (int) $limit;
        $sql = "SELECT tipo, data_inclusao, cod_inscrito, nome_inscrito, titulo_trabalho
                FROM (
                    SELECT 'Apresentacao' AS tipo,
                           ap.data_inclusao,
                           ap.cod_inscrito,
                           ti.nome_inscrito,
                           tt.titulo_trabalho
                    FROM tab_arquivo_apresentacao ap
                    LEFT JOIN tab_inscritos ti
                      ON ti.cod_inscrito = ap.cod_inscrito AND ti.cod_evento = ap.cod_evento
                    LEFT JOIN tab_trabalhos tt
                      ON tt.cod_trabalho = ap.cod_trabalho AND tt.cod_evento = ap.cod_evento
                    WHERE ap.cod_evento = {$codEvento}
                      AND ap.status = 'ATIVADA'
                    UNION ALL
                    SELECT 'Avulso' AS tipo,
                           av.data_inclusao,
                           av.cod_inscrito,
                           ti.nome_inscrito,
                           NULL AS titulo_trabalho
                    FROM tab_arquivo_avulso av
                    LEFT JOIN tab_inscritos ti
                      ON ti.cod_inscrito = av.cod_inscrito AND ti.cod_evento = av.cod_evento
                    WHERE av.cod_evento = {$codEvento}
                      AND av.status = 'ATIVADA'
                ) AS envios
                ORDER BY data_inclusao DESC
                LIMIT {$limit}";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Timestamp mais recente entre inscricao, trabalho e arquivo (apresentacao/avulso).
     * Retorna string 'YYYY-MM-DD HH:ii:ss' ou null.
     */
    public function getUltimaAtividade($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT MAX(dt) AS ultima FROM (
                    SELECT MAX(data_inscricao) AS dt FROM tab_inscritos
                     WHERE cod_evento={$codEvento} AND status='ATIVADA' AND origem_cadastro='INSCRICAO'
                    UNION ALL
                    SELECT MAX(data_inclusao) AS dt FROM tab_trabalhos
                     WHERE cod_evento={$codEvento} AND status='ATIVADA'
                    UNION ALL
                    SELECT MAX(data_inclusao) AS dt FROM tab_arquivo_apresentacao
                     WHERE cod_evento={$codEvento} AND status='ATIVADA'
                    UNION ALL
                    SELECT MAX(data_inclusao) AS dt FROM tab_arquivo_avulso
                     WHERE cod_evento={$codEvento} AND status='ATIVADA'
                ) AS fontes";
        $rs = $this->conex->Execute($sql);
        if ($rs && !$rs->EOF) {
            return $rs->fields['ultima'] ?: null;
        }
        return null;
    }

    // ============================================================
    // HELPER
    // ============================================================

    private function executarEBuscar($sql, $debug = false) {
        try {
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $lista = [];
            if ($rs && !$rs->EOF) {
                while (!$rs->EOF) {
                    $lista[] = $rs->fields;
                    $rs->MoveNext();
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return [];
        }
    }
}
