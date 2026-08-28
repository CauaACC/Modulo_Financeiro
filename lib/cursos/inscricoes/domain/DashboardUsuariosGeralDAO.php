<?php

/**
 * DAO do dashboard geral de usuarios (cross-event).
 *
 * Todas as queries agregam dados de multiplos eventos (sem filtro cod_evento),
 * limitadas por periodo em dias. Somente leitura.
 */
class DashboardUsuariosGeralDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Inscritos por dia (cross-event) nos ultimos N dias.
     * @return array [ ['dia' => 'YYYY-MM-DD', 'total' => n], ... ]
     */
    public function getInscritosPorDia($dias = 30, $debug = false) {
        $dias = (int) $dias;
        $sql = "SELECT DATE(data_inscricao) AS dia, COUNT(*) AS total
                FROM tab_inscritos
                WHERE status = 'ATIVADA'
                  AND origem_cadastro = 'INSCRICAO'
                  AND data_inscricao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)
                GROUP BY DATE(data_inscricao)
                ORDER BY DATE(data_inscricao)";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Logins por dia (cross-event) nos ultimos N dias.
     * @param int $sucesso 1 para sucessos, 0 para falhas, null para todos
     */
    public function getLoginsPorDia($dias = 30, $sucesso = 1, $debug = false) {
        $dias = (int) $dias;
        $filtroSucesso = '';
        if ($sucesso === 1 || $sucesso === 0) {
            $filtroSucesso = " AND sucesso = " . ((int) $sucesso);
        }
        $sql = "SELECT DATE(data_inclusao) AS dia, COUNT(*) AS total
                FROM sis_log_acesso
                WHERE data_inclusao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)
                  {$filtroSucesso}
                GROUP BY DATE(data_inclusao)
                ORDER BY DATE(data_inclusao)";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Por dia: total de tentativas, com sucesso e sem sucesso -- para o grafico de
     * LINHAS sobrepostas. Uma query so, dias ja alinhados (evita casar 3 arrays).
     */
    public function getTentativasSucessoFalhaPorDia($dias = 30, $debug = false) {
        $dias = (int) $dias;
        $sql = "SELECT DATE(data_inclusao) AS dia,
                       COUNT(*) AS total,
                       SUM(sucesso = 1) AS sucesso,
                       SUM(sucesso = 0 OR sucesso IS NULL) AS falha
                FROM sis_log_acesso
                WHERE data_inclusao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)
                GROUP BY DATE(data_inclusao)
                ORDER BY DATE(data_inclusao)";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Tentativas de login agrupadas POR LOGIN (e-mail): total, sucesso e falha
     * no periodo. Uma linha por e-mail, ordenado por quem mais tentou -- serve
     * pra achar rapido quem esta com problema (muita falha) ou confirmar quem
     * ja conseguiu entrar.
     * @param string $filtroEmail LIKE parcial no login; vazio = todos
     * @param string $de 'AAAA-MM-DD' -- se informado (com ou sem $ate), substitui $dias
     * @param string $ate 'AAAA-MM-DD' -- vazio com $de preenchido = ate hoje
     */
    public function getTentativasPorLogin($dias = 30, $filtroEmail = '', $de = '', $ate = '', $debug = false) {
        $dias = (int) $dias;
        $filtroEmail = trim((string) $filtroEmail);
        $de  = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $de)  ? $de  : '';
        $ate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $ate) ? $ate : '';

        if ($de !== '' || $ate !== '') {
            $where = "WHERE DATE(data_inclusao) >= '" . ($de !== '' ? addslashes($de) : '1970-01-01') . "'"
                   . " AND DATE(data_inclusao) <= '" . ($ate !== '' ? addslashes($ate) : date('Y-m-d')) . "'";
        } else {
            $where = "WHERE data_inclusao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)";
        }
        if ($filtroEmail !== '') {
            $where .= " AND login LIKE '%" . addslashes($filtroEmail) . "%'";
        }
        $sql = "SELECT login,
                       COUNT(*) AS total,
                       SUM(sucesso = 1) AS sucesso,
                       SUM(sucesso = 0 OR sucesso IS NULL) AS falha,
                       MAX(data_inclusao) AS ultima_tentativa
                FROM sis_log_acesso
                {$where}
                GROUP BY login
                ORDER BY total DESC
                LIMIT 500";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Inscritos por evento nos ultimos N dias (novo insight solicitado).
     * Junta com tab_eventos para trazer titulo.
     */
    public function getInscritosPorEventoUltimosDias($dias = 7, $debug = false) {
        $dias = (int) $dias;
        $sql = "SELECT i.cod_evento, e.titulo, COUNT(*) AS total
                FROM tab_inscritos i
                LEFT JOIN tab_eventos e ON e.cod_evento = i.cod_evento
                WHERE i.status = 'ATIVADA'
                  AND i.origem_cadastro = 'INSCRICAO'
                  AND i.data_inscricao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)
                GROUP BY i.cod_evento, e.titulo
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Inscritos por estado (cross-event, todos os tempos).
     */
    public function getInscritosPorEstado($debug = false) {
        $sql = "SELECT estado, COUNT(*) AS total
                FROM tab_inscritos
                WHERE status = 'ATIVADA'
                  AND origem_cadastro = 'INSCRICAO'
                  AND estado IS NOT NULL
                  AND estado <> ''
                GROUP BY estado
                ORDER BY total DESC";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Inscritos por cidade (apenas PR, cross-event).
     */
    public function getInscritosPorCidadePR($debug = false) {
        $sql = "SELECT cidade, COUNT(*) AS total
                FROM tab_inscritos
                WHERE status = 'ATIVADA'
                  AND origem_cadastro = 'INSCRICAO'
                  AND estado = 'PR'
                  AND cidade IS NOT NULL
                  AND cidade <> ''
                GROUP BY cidade
                ORDER BY total DESC
                LIMIT 20";
        return $this->executarEBuscar($sql, $debug);
    }

    /**
     * Totais (cards): totais dos ultimos N dias cross-event.
     */
    public function getTotais($dias = 7, $debug = false) {
        $dias = (int) $dias;
        $sql = "SELECT
                    (SELECT COUNT(*) FROM tab_inscritos
                     WHERE status='ATIVADA' AND origem_cadastro='INSCRICAO'
                       AND data_inscricao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)) AS inscritos_periodo,
                    (SELECT COUNT(*) FROM sis_log_acesso
                     WHERE sucesso=1
                       AND data_inclusao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)) AS logins_sucesso,
                    (SELECT COUNT(*) FROM sis_log_acesso
                     WHERE sucesso=0
                       AND data_inclusao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)) AS logins_falha,
                    (SELECT COUNT(DISTINCT cod_evento) FROM tab_inscritos
                     WHERE status='ATIVADA' AND origem_cadastro='INSCRICAO'
                       AND data_inscricao >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)) AS eventos_ativos";
        $rs = $this->conex->Execute($sql);
        if ($rs && !$rs->EOF) {
            return [
                'inscritos_periodo' => (int) $rs->fields['inscritos_periodo'],
                'logins_sucesso'    => (int) $rs->fields['logins_sucesso'],
                'logins_falha'      => (int) $rs->fields['logins_falha'],
                'eventos_ativos'    => (int) $rs->fields['eventos_ativos'],
            ];
        }
        return ['inscritos_periodo' => 0, 'logins_sucesso' => 0, 'logins_falha' => 0, 'eventos_ativos' => 0];
    }

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
