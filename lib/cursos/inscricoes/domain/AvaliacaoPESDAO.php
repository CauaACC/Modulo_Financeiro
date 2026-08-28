<?php

/**
 * Tabela: acad_avaliacao_pes
 *
 * CREATE TABLE acad_avaliacao_pes (
 *     cod_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
 *     cod_evento INT NOT NULL,
 *     cod_supervisor INT NOT NULL,
 *     cod_agente INT NOT NULL,
 *     data_avaliacao DATE NOT NULL,
 *     segue_orientacoes VARCHAR(20),
 *     abertura_feedback VARCHAR(20),
 *     comunica_dificuldades VARCHAR(20),
 *     postura_profissional VARCHAR(20),
 *     relacionamento_equipe VARCHAR(20),
 *     desempenho_adequado VARCHAR(20),
 *     necessita_acompanhamento VARCHAR(20),
 *     observacoes TEXT,
 *     status VARCHAR(20) DEFAULT 'ATIVADA',
 *     versao INT DEFAULT 1,
 *     data_inclusao DATETIME,
 *     id_inclusao INT,
 *     session_id_inclusao INT,
 *     ip_inclusao VARCHAR(50),
 *     http_user_agent_inclusao VARCHAR(500),
 *     data_alteracao DATETIME,
 *     id_alteracao INT,
 *     session_id_alteracao INT,
 *     ip_alteracao VARCHAR(50),
 *     http_user_agent_alteracao VARCHAR(500)
 * );
 */
class AvaliacaoPESDAO extends DAO {

    private $camposCriptografados = array('observacoes');

    public function __construct() {
        parent::__construct();
    }

    private function descriptografarCampos(AvaliacaoPES $obj) {
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $setter = 'set' . ucfirst($campo);
            $valor = $obj->$getter();
            if ($valor !== null && $valor !== '') {
                $obj->$setter(CriptografiaHelper::descriptografar($valor));
            }
        }
    }

    private function criptografarCampos(AvaliacaoPES $obj) {
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $setter = 'set' . ucfirst($campo);
            $valor = $obj->$getter();
            if ($valor !== null && $valor !== '') {
                $obj->$setter(CriptografiaHelper::criptografar($valor));
            }
        }
    }

    private function guardarValoresOriginais(AvaliacaoPES $obj) {
        $valores = array();
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $valores[$campo] = $obj->$getter();
        }
        return $valores;
    }

    private function restaurarCamposOriginais(AvaliacaoPES $obj, $valoresOriginais) {
        foreach ($valoresOriginais as $campo => $valor) {
            $setter = 'set' . ucfirst($campo);
            $obj->$setter($valor);
        }
    }

    public function getById(AvaliacaoPES $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM acad_avaliacao_pes WHERE cod_evento=\'%s\' AND cod_avaliacao=\'%s\'',
                $obj->getCodEvento(), $obj->getCodAvaliacao());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {}
                $obj->setAll($rows);
                $this->descriptografarCampos($obj);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(AvaliacaoPES $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT cod_avaliacao FROM acad_avaliacao_pes WHERE cod_evento=\'%s\' AND cod_avaliacao=\'%s\'',
                $obj->getCodEvento(), $obj->getCodAvaliacao());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaAvaliacoesByCodAgente(AvaliacaoPES $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_avaliacao_pes
                WHERE cod_evento='%s' AND cod_agente='%s' AND status='ATIVADA'
                ORDER BY data_avaliacao DESC",
                $obj->getCodEvento(), $obj->getCodAgente());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = array();
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $this->descriptografarCampos($obj);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliacoes($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarAgentesComStatusPorSupervisor($codEvento, $codSupervisor, $debug = false) {
        try {
            $sql = sprintf("SELECT v.cod_agente,
                    i.nome_inscrito, i.cpf, i.status_processo,
                    (SELECT COUNT(*) FROM acad_lig_agente_aluno aa
                     INNER JOIN acad_aluno_contexto ac ON ac.cod_aluno = aa.cod_aluno AND ac.cod_evento = aa.cod_evento AND ac.status = 'ATIVADA'
                     WHERE aa.cod_agente = v.cod_agente AND aa.cod_evento = v.cod_evento AND aa.status = 'ATIVADA') as total_alunos,
                    ap.cod_avaliacao, ap.data_avaliacao, ap.data_inclusao AS data_preenchimento
                FROM acad_lig_supervisor_agente v
                INNER JOIN tab_inscritos i ON i.cod_inscrito = v.cod_agente AND i.cod_evento = v.cod_evento
                LEFT JOIN acad_avaliacao_pes ap ON ap.cod_agente = v.cod_agente AND ap.cod_evento = v.cod_evento
                    AND ap.cod_supervisor = v.cod_supervisor AND ap.status='ATIVADA'
                    AND ap.data_avaliacao >= DATE_FORMAT(CURDATE(), '%%Y-%%m-01')
                WHERE v.cod_evento='%s' AND v.cod_supervisor='%s' AND v.status='ATIVADA'
                ORDER BY i.nome_inscrito ASC",
                $codEvento, $codSupervisor);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return array();
            }

            $return = array();
            foreach ($rs as $row) {
                $return[] = $row;
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function insert(AvaliacaoPES $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $valoresOriginais = $this->guardarValoresOriginais($obj);
            $this->criptografarCampos($obj);

            $sql = "SELECT * FROM acad_avaliacao_pes WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_avaliacao=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_avaliacao_pes");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodAvaliacao($this->conex->Insert_ID());

            $this->restaurarCamposOriginais($obj, $valoresOriginais);

            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(AvaliacaoPES $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setSessionIdAlteracao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            $valoresOriginais = $this->guardarValoresOriginais($obj);
            $this->criptografarCampos($obj);

            $sql = "SELECT * FROM acad_avaliacao_pes WHERE cod_evento = " . $obj->getCodEvento() . " AND cod_avaliacao = " . $obj->getCodAvaliacao();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_avaliacao_pes");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);

            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $this->restaurarCamposOriginais($obj, $valoresOriginais);

            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarEstatisticasGeraisPES($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT ap.cod_avaliacao, ap.cod_agente, ap.cod_supervisor,
                    ap.data_avaliacao,
                    ap.segue_orientacoes, ap.abertura_feedback, ap.comunica_dificuldades,
                    ap.postura_profissional, ap.relacionamento_equipe,
                    ap.desempenho_adequado, ap.necessita_acompanhamento,
                    iag.nome_inscrito AS nome_agente,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) AS nome_supervisor
                FROM acad_avaliacao_pes ap
                INNER JOIN tab_inscritos iag ON iag.cod_inscrito = ap.cod_agente AND iag.cod_evento = ap.cod_evento
                LEFT JOIN sis_usuarios su ON su.cod_usuario = ap.cod_supervisor
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = ap.cod_supervisor
                WHERE ap.cod_evento = '%s' AND ap.status = 'ATIVADA'
                ORDER BY ap.data_avaliacao DESC",
                $codEvento);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $registros = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $registros[] = $row;
                }
            }
            return $registros;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function getCodGrupoSupervisor($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT dgs.cod_grupo FROM sis_direitos_grupos_sistemas dgs
                WHERE dgs.cod_sistema = %s AND dgs.cod_evento = %s LIMIT 1",
                enumSistema::SIS_PEDAGOGICO_SUPERVISOR, $codEvento);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ($rs && !$rs->EOF) {
                return intval($rs->fields['cod_grupo']);
            }
            return false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaSupervisores($codEvento, $codGrupoSupervisor, $debug = false) {
        try {
            $sql = sprintf("SELECT su.cod_usuario,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) as nome
                FROM sis_usuarios su
                INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario AND lue.cod_evento = %d
                INNER JOIN sis_ligacao_usuarios_grupos lug ON su.cod_usuario = lug.cod_usuario AND lug.cod_evento = %d AND lug.cod_grupo = %d
                LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                WHERE su.status = 'ATIVADA'
                ORDER BY nome ASC",
                $codEvento, $codEvento, $codGrupoSupervisor);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $supervisores = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $supervisores[] = array(
                        'codUsuario' => intval($row['cod_usuario']),
                        'nome'       => $row['nome']
                    );
                }
            }
            return $supervisores;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function carregarPainelComparativo($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT ap.cod_agente, ap.cod_supervisor,
                    iag.nome_inscrito AS nome_agente,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) AS nome_supervisor,
                    c.nome AS nome_colegio, c.cod_colegio,
                    ap.segue_orientacoes, ap.abertura_feedback, ap.comunica_dificuldades,
                    ap.postura_profissional, ap.relacionamento_equipe,
                    ap.desempenho_adequado, ap.necessita_acompanhamento,
                    ap.data_avaliacao
                FROM acad_avaliacao_pes ap
                INNER JOIN tab_inscritos iag ON iag.cod_inscrito = ap.cod_agente AND iag.cod_evento = ap.cod_evento
                LEFT JOIN sis_usuarios su ON su.cod_usuario = ap.cod_supervisor
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = ap.cod_supervisor
                LEFT JOIN acad_lig_agente_aluno vaa ON vaa.cod_agente = ap.cod_agente AND vaa.cod_evento = ap.cod_evento AND vaa.status = 'ATIVADA'
                LEFT JOIN acad_aluno_contexto ac ON ac.cod_aluno = vaa.cod_aluno AND ac.cod_evento = vaa.cod_evento AND ac.status = 'ATIVADA'
                LEFT JOIN acad_colegio c ON c.cod_colegio = ac.cod_colegio AND c.cod_evento = ac.cod_evento
                WHERE ap.cod_evento = '%s' AND ap.status = 'ATIVADA'
                GROUP BY ap.cod_avaliacao
                ORDER BY c.nome ASC, iag.nome_inscrito ASC, ap.data_avaliacao DESC",
                $codEvento);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $return = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $return[] = $row;
                }
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function delete(AvaliacaoPES $obj, $debug = false) {
        try {
            $sql = sprintf('DELETE FROM acad_avaliacao_pes WHERE cod_evento=\'%s\' AND cod_avaliacao=\'%s\'',
                $obj->getCodEvento(), $obj->getCodAvaliacao());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}
