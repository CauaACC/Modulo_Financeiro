<?php

class LogAcessoLaudoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function insert(LogAcessoLaudo $obj, $debug = false) {
        try {
            $sql = sprintf("INSERT INTO acad_log_acesso_laudo (cod_evento, cod_laudo, cod_aluno, cod_usuario, acao, data_acesso, ip, http_user_agent, justificativa)
                VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                $obj->getCodEvento(),
                $obj->getCodLaudo(),
                $obj->getCodAluno(),
                $obj->getCodUsuario(),
                $obj->getAcao(),
                $obj->getDataAcesso(),
                addslashes($obj->getIp()),
                addslashes($obj->getHttpUserAgent()),
                addslashes($obj->getJustificativa())
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            // Log de auditoria nao deve interromper a operacao principal
            return false;
        }
    }

    public function carregarLogsPorLaudo($codEvento, $codLaudo, $debug = false) {
        try {
            $sql = sprintf("SELECT l.*, IFNULL(su.nome_usuario, tc.nome_convidado) AS nome_usuario
                FROM acad_log_acesso_laudo l
                LEFT JOIN sis_usuarios su ON su.cod_usuario = l.cod_usuario
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = l.cod_usuario
                WHERE l.cod_evento = '%s' AND l.cod_laudo = '%s'
                ORDER BY l.data_acesso DESC",
                $codEvento, $codLaudo);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $logs = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $logs[] = $row;
                }
            }
            return $logs;
        } catch (ADODB_Exception $e) {
            return array();
        }
    }

}
