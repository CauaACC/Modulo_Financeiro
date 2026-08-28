<?php

class HistoricoAvaliacaoInscritoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function insert(HistoricoAvaliacaoInscrito $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : null);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR'] ?? '');
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT'] ?? '');
            $obj->setSessionId(session_id());
            $obj->setVersao(1);
            if (is_null($obj->getStatus()) || $obj->getStatus() == '') {
                $obj->setStatus('ATIVADA');
            }

            $sql = "SELECT * FROM tab_trabalhos_historico_avaliacao_inscricao WHERE cod_avaliacao_historico = 0";
            $rs  = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("tab_trabalhos_historico_avaliacao_inscricao");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodAvaliacaoHistorico($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(HistoricoAvaliacaoInscrito $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : null);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR'] ?? '');
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT'] ?? '');
            $obj->setVersao((int)$obj->getVersao() + 1);

            $sql = "SELECT * FROM tab_trabalhos_historico_avaliacao_inscricao
                    WHERE cod_avaliacao_historico = " . (int)$obj->getCodAvaliacaoHistorico();
            $rs  = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("tab_trabalhos_historico_avaliacao_inscricao");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($updateSQL);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Marca SELECIONADO para (cod_evento, cod_inscrito, cod_etapa, cod_avaliador).
     * Idempotente: se ja existe, NAO insere de novo.
     * Retorna true se gravou agora, false se ja existia.
     */
    public function marcarSelecionado(HistoricoAvaliacaoInscrito $obj, $debug = false) {
        try {
            $codEv  = (int) $obj->getCodEvento();
            $codIns = (int) $obj->getCodInscrito();
            $codEt  = (int) $obj->getCodEtapa();
            $codAv  = (int) $obj->getCodAvaliador();

            $sqlExiste = "SELECT cod_avaliacao_historico
                            FROM tab_trabalhos_historico_avaliacao_inscricao
                           WHERE cod_evento    = {$codEv}
                             AND cod_inscrito  = {$codIns}
                             AND cod_etapa     = {$codEt}
                             AND cod_avaliador = {$codAv}
                             AND avaliacao     = '" . HistoricoAvaliacaoInscritoVO::ACAO_SELECIONADO . "'
                             AND status        = 'ATIVADA'
                           LIMIT 1";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sqlExiste);
            if ($rs && !$rs->EOF) {
                return false; // ja existia
            }

            $obj->setAvaliacao(HistoricoAvaliacaoInscritoVO::ACAO_SELECIONADO);
            $obj->setStatus('ATIVADA');
            $this->insert($obj, $debug);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Retorna LISTA de avaliadores com reserva SELECIONADO ativa
     * (data_inclusao dentro da janela de expiracao) para
     * (cod_evento, cod_inscrito, cod_etapa). Cada item: cod_avaliador,
     * nome (de tab_convidados/sis_usuarios), login, data_inclusao.
     * Lista vazia se nao houver.
     */
    public function getAvaliadoresSelecionados(HistoricoAvaliacaoInscrito $obj, $expiraHoras = 24, $debug = false) {
        try {
            $codEv  = (int) $obj->getCodEvento();
            $codIns = (int) $obj->getCodInscrito();
            $codEt  = (int) $obj->getCodEtapa();
            $expiraHoras = (int) $expiraHoras;

            $sql = "SELECT thai.cod_avaliador,
                           thai.data_inclusao,
                           su.login,
                           COALESCE(NULLIF(TRIM(tc.nome_convidado),''),
                                    NULLIF(TRIM(su.nome_usuario),''),
                                    su.login,
                                    CONCAT('Avaliador #', thai.cod_avaliador)) AS nome
                      FROM tab_trabalhos_historico_avaliacao_inscricao thai
                      LEFT JOIN sis_usuarios   su ON su.cod_usuario = thai.cod_avaliador
                      LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                     WHERE thai.cod_evento   = {$codEv}
                       AND thai.cod_inscrito = {$codIns}
                       AND thai.cod_etapa    = {$codEt}
                       AND thai.avaliacao    = '" . HistoricoAvaliacaoInscritoVO::ACAO_SELECIONADO . "'
                       AND thai.status       = 'ATIVADA'
                       AND STR_TO_DATE(thai.data_inclusao, '%Y-%m-%d %H:%i:%s')
                           >= DATE_SUB(NOW(), INTERVAL {$expiraHoras} HOUR)
                     ORDER BY thai.cod_avaliacao_historico DESC";
            return $this->executarEBuscar($sql, $debug);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return [];
        }
    }

    /**
     * Registra decisao final (HOMOLOGADO/NAO HOMOLOGADO) do avaliador.
     * Idempotente: se ja existe linha com a mesma decisao do mesmo avaliador,
     * nao insere de novo. Se existe decisao diferente, insere nova (mantem trilha).
     */
    public function registrarDecisao(HistoricoAvaliacaoInscrito $obj, $debug = false) {
        try {
            $codEv  = (int) $obj->getCodEvento();
            $codIns = (int) $obj->getCodInscrito();
            $codEt  = (int) $obj->getCodEtapa();
            $codAv  = (int) $obj->getCodAvaliador();
            $aval   = addslashes($obj->getAvaliacao());

            $sqlExiste = "SELECT cod_avaliacao_historico
                            FROM tab_trabalhos_historico_avaliacao_inscricao
                           WHERE cod_evento    = {$codEv}
                             AND cod_inscrito  = {$codIns}
                             AND cod_etapa     = {$codEt}
                             AND cod_avaliador = {$codAv}
                             AND avaliacao     = '{$aval}'
                             AND status        = 'ATIVADA'
                           LIMIT 1";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sqlExiste);
            if ($rs && !$rs->EOF) {
                return false; // ja existia decisao identica
            }
            $obj->setStatus('ATIVADA');
            $this->insert($obj, $debug);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Helper privado: executa SELECT e devolve array de rows.
     * (DAO base nao tem esse helper — replicado aqui pra usar nos SELECTs.)
     */
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

    /**
     * Libera (status='DESATIVADA') as reservas SELECIONADO ativas do
     * avaliador atual em (cod_evento, cod_inscrito, cod_etapa, cod_avaliador).
     * Nao afeta reservas de outros avaliadores.
     */
    public function liberarReserva(HistoricoAvaliacaoInscrito $obj, $debug = false) {
        try {
            $codEv  = (int) $obj->getCodEvento();
            $codIns = (int) $obj->getCodInscrito();
            $codEt  = (int) $obj->getCodEtapa();
            $codAv  = (int) $obj->getCodAvaliador();
            $agora  = date('Y-m-d H:i:s');

            $sql = "UPDATE tab_trabalhos_historico_avaliacao_inscricao
                       SET status = 'DESATIVADA',
                           data_alteracao = '{$agora}',
                           id_alteracao   = '{$codAv}'
                     WHERE cod_evento    = {$codEv}
                       AND cod_inscrito  = {$codIns}
                       AND cod_etapa     = {$codEt}
                       AND cod_avaliador = {$codAv}
                       AND avaliacao     = '" . HistoricoAvaliacaoInscritoVO::ACAO_SELECIONADO . "'
                       AND status        = 'ATIVADA'";
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
