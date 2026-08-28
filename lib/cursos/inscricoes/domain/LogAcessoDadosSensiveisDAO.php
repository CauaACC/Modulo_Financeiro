<?php

class LogAcessoDadosSensiveisDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function insert(LogAcessoDadosSensiveis $obj, $debug = false) {
        try {
            $sql = sprintf("INSERT INTO acad_log_acesso_dados_sensiveis (cod_evento, tipo_dado, cod_referencia, acao, cod_usuario, data_acesso, ip, http_user_agent, detalhes)
                VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                $obj->getCodEvento(),
                addslashes($obj->getTipoDado()),
                addslashes($obj->getCodReferencia()),
                addslashes($obj->getAcao()),
                addslashes($obj->getCodUsuario()),
                $obj->getDataAcesso(),
                addslashes($obj->getIp()),
                addslashes($obj->getHttpUserAgent()),
                addslashes($obj->getDetalhes())
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            // Log de auditoria nao deve interromper a operacao principal
            return false;
        }
    }

    public function carregarLogsPorReferencia($codEvento, $tipoDado, $codReferencia, $debug = false) {
        try {
            $sql = sprintf("SELECT l.*, u.nome AS nome_usuario
                FROM acad_log_acesso_dados_sensiveis l
                LEFT JOIN sis_usuario u ON u.cod_usuario = l.cod_usuario
                WHERE l.cod_evento = '%s' AND l.tipo_dado = '%s' AND l.cod_referencia = '%s'
                ORDER BY l.data_acesso DESC",
                $codEvento, addslashes($tipoDado), addslashes($codReferencia));

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
