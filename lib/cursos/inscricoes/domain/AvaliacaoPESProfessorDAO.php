<?php

class AvaliacaoPESProfessorDAO extends DAO {

    private $camposCriptografados = array('observacoes');

    public function __construct() {
        parent::__construct();
    }

    private function descriptografarCampos(AvaliacaoPESProfessor $obj) {
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $setter = 'set' . ucfirst($campo);
            $valor = $obj->$getter();
            if ($valor !== null && $valor !== '') {
                $obj->$setter(CriptografiaHelper::descriptografar($valor));
            }
        }
    }

    private function criptografarCampos(AvaliacaoPESProfessor $obj) {
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $setter = 'set' . ucfirst($campo);
            $valor = $obj->$getter();
            if ($valor !== null && $valor !== '') {
                $obj->$setter(CriptografiaHelper::criptografar($valor));
            }
        }
    }

    private function guardarValoresOriginais(AvaliacaoPESProfessor $obj) {
        $valores = array();
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $valores[$campo] = $obj->$getter();
        }
        return $valores;
    }

    private function restaurarCamposOriginais(AvaliacaoPESProfessor $obj, $valoresOriginais) {
        foreach ($valoresOriginais as $campo => $valor) {
            $setter = 'set' . ucfirst($campo);
            $obj->$setter($valor);
        }
    }

    public function getById(AvaliacaoPESProfessor $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM acad_avaliacao_pes_professor WHERE cod_evento=\'%s\' AND cod_avaliacao=\'%s\'',
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

    public function getByIdExiste(AvaliacaoPESProfessor $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT cod_avaliacao FROM acad_avaliacao_pes_professor WHERE cod_evento=\'%s\' AND cod_avaliacao=\'%s\'',
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

    public function carregarAgentesComAvaliacoesPorSupervisor($codEvento, $codSupervisor, $ciclo, $debug = false) {
        try {
            $sql = sprintf("SELECT v.cod_agente,
                    i.nome_inscrito,
                    (SELECT COUNT(*) FROM acad_avaliacao_pes_professor ap
                     WHERE ap.cod_agente = v.cod_agente AND ap.cod_evento = v.cod_evento
                     AND ap.ciclo = %d AND ap.status='ATIVADA') as total_avaliacoes,
                    (SELECT GROUP_CONCAT(DISTINCT ap2.nome_professor SEPARATOR ', ')
                     FROM acad_avaliacao_pes_professor ap2
                     WHERE ap2.cod_agente = v.cod_agente AND ap2.cod_evento = v.cod_evento
                     AND ap2.ciclo = %d AND ap2.status='ATIVADA') as professores_avaliaram
                FROM acad_lig_supervisor_agente v
                INNER JOIN tab_inscritos i ON i.cod_inscrito = v.cod_agente AND i.cod_evento = v.cod_evento
                WHERE v.cod_evento='%s' AND v.cod_supervisor='%s' AND v.status='ATIVADA'
                ORDER BY i.nome_inscrito ASC",
                $ciclo, $ciclo, $codEvento, $codSupervisor);

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

    public function carregarAvaliacoesPorAgente($codEvento, $codAgente, $ciclo, $debug = false) {
        try {
            $sql = sprintf("SELECT ap.*, i.nome_inscrito AS nome_agente
                FROM acad_avaliacao_pes_professor ap
                INNER JOIN tab_inscritos i ON i.cod_inscrito = ap.cod_agente AND i.cod_evento = ap.cod_evento
                WHERE ap.cod_evento='%s' AND ap.cod_agente='%s' AND ap.ciclo=%d AND ap.status='ATIVADA'
                ORDER BY ap.nome_professor ASC",
                $codEvento, $codAgente, $ciclo);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $return = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $this->descriptografarCamposArray($row);
                    $return[] = $row;
                }
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    private function descriptografarCamposArray(&$row) {
        foreach ($this->camposCriptografados as $campo) {
            if (isset($row[$campo]) && $row[$campo] !== null && $row[$campo] !== '') {
                $row[$campo] = CriptografiaHelper::descriptografar($row[$campo]);
            }
        }
    }

    public function carregarPainelComparativo($codEvento, $ciclo, $debug = false) {
        try {
            $sql = sprintf("SELECT ap.cod_agente, i.nome_inscrito AS nome_agente,
                    c.nome AS nome_colegio, c.cod_colegio,
                    COUNT(*) AS total_avaliacoes,
                    AVG(ap.nota_geral) AS media_nota,
                    AVG(CASE ap.integra_atividades WHEN 'Sim' THEN 3 WHEN 'Parcialmente' THEN 2 WHEN 'Nao' THEN 1 ELSE NULL END) AS media_integra,
                    AVG(CASE ap.contribui_participacao WHEN 'Sim' THEN 3 WHEN 'Parcialmente' THEN 2 WHEN 'Nao' THEN 1 ELSE NULL END) AS media_contribui,
                    AVG(CASE ap.favorece_aprendizagem WHEN 'Sim' THEN 3 WHEN 'Parcialmente' THEN 2 WHEN 'Nao' THEN 1 ELSE NULL END) AS media_aprendizagem,
                    AVG(CASE ap.reduz_comportamentos WHEN 'Sim' THEN 3 WHEN 'Parcialmente' THEN 2 WHEN 'Nao' THEN 1 ELSE NULL END) AS media_comportamentos,
                    AVG(CASE ap.promove_autonomia WHEN 'Sim' THEN 3 WHEN 'Parcialmente' THEN 2 WHEN 'Nao' THEN 1 ELSE NULL END) AS media_autonomia,
                    AVG(CASE ap.comunicacao_professor WHEN 'Sim' THEN 3 WHEN 'Parcialmente' THEN 2 WHEN 'Nao' THEN 1 ELSE NULL END) AS media_comunicacao,
                    AVG(CASE ap.segue_orientacoes WHEN 'Sim' THEN 3 WHEN 'Parcialmente' THEN 2 WHEN 'Nao' THEN 1 ELSE NULL END) AS media_orientacoes,
                    AVG(CASE ap.contribui_aula WHEN 'Sim' THEN 3 WHEN 'Parcialmente' THEN 2 WHEN 'Nao' THEN 1 ELSE NULL END) AS media_contribui_aula,
                    AVG(CASE ap.postura_profissional WHEN 'Sim' THEN 3 WHEN 'Parcialmente' THEN 2 WHEN 'Nao' THEN 1 ELSE NULL END) AS media_postura
                FROM acad_avaliacao_pes_professor ap
                INNER JOIN tab_inscritos i ON i.cod_inscrito = ap.cod_agente AND i.cod_evento = ap.cod_evento
                LEFT JOIN acad_lig_agente_aluno vaa ON vaa.cod_agente = ap.cod_agente AND vaa.cod_evento = ap.cod_evento AND vaa.status = 'ATIVADA'
                LEFT JOIN acad_aluno_contexto ac ON ac.cod_aluno = vaa.cod_aluno AND ac.cod_evento = vaa.cod_evento AND ac.status = 'ATIVADA'
                LEFT JOIN acad_colegio c ON c.cod_colegio = ac.cod_colegio AND c.cod_evento = ac.cod_evento
                WHERE ap.cod_evento = '%s' AND ap.ciclo = %d AND ap.status = 'ATIVADA'
                GROUP BY ap.cod_agente, c.cod_colegio
                ORDER BY c.nome ASC, media_nota DESC",
                $codEvento, $ciclo);

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

    public function insert(AvaliacaoPESProfessor $obj, $debug = false) {
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

            $sql = "SELECT * FROM acad_avaliacao_pes_professor WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_avaliacao=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_avaliacao_pes_professor");
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

    public function update(AvaliacaoPESProfessor $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setSessionIdAlteracao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            $valoresOriginais = $this->guardarValoresOriginais($obj);
            $this->criptografarCampos($obj);

            $sql = "SELECT * FROM acad_avaliacao_pes_professor WHERE cod_evento = " . $obj->getCodEvento() . " AND cod_avaliacao = " . $obj->getCodAvaliacao();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_avaliacao_pes_professor");
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

    public function delete(AvaliacaoPESProfessor $obj, $debug = false) {
        try {
            $sql = sprintf('DELETE FROM acad_avaliacao_pes_professor WHERE cod_evento=\'%s\' AND cod_avaliacao=\'%s\'',
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
