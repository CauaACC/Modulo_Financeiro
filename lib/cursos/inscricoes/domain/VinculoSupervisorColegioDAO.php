<?php

class VinculoSupervisorColegioDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getByIdExiste(VinculoSupervisorColegio $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_vinculo FROM acad_lig_supervisor_colegio WHERE cod_evento='%s' AND cod_vinculo='%s'",
                $obj->getCodEvento(), $obj->getCodVinculo());

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

    public function verificarDuplicidade(VinculoSupervisorColegio $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_vinculo FROM acad_lig_supervisor_colegio
                WHERE cod_evento='%s' AND cod_supervisor='%s' AND cod_colegio='%s' AND status='ATIVADA'",
                $obj->getCodEvento(), $obj->getCodSupervisor(), $obj->getCodColegio());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ($rs && !$rs->EOF) {
                return true;
            }
            return false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaVinculosBySupervisor(VinculoSupervisorColegio $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT v.cod_vinculo, v.cod_evento, v.cod_supervisor, v.cod_colegio, v.status, v.data_inicio,
                    c.nome AS nome_colegio,
                    (SELECT COUNT(*) FROM acad_aluno_contexto a
                     WHERE a.cod_colegio = v.cod_colegio AND a.cod_evento = v.cod_evento AND a.status = 'ATIVADA') as total_alunos
                FROM acad_lig_supervisor_colegio v
                INNER JOIN acad_colegio c ON c.cod_colegio = v.cod_colegio AND c.cod_evento = v.cod_evento
                WHERE v.cod_evento='%s' AND v.cod_supervisor='%s' AND v.status='ATIVADA'
                ORDER BY c.nome ASC",
                $obj->getCodEvento(), $obj->getCodSupervisor());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = array();
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaVinculos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarCodColegiosBySupervisor($codEvento, $codSupervisor, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_colegio FROM acad_lig_supervisor_colegio
                WHERE cod_evento='%s' AND cod_supervisor='%s' AND status='ATIVADA'",
                $codEvento, $codSupervisor);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $codColegios = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $codColegios[] = $row['cod_colegio'];
                }
            }
            return $codColegios;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function carregarContagensPorColegio($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT a.cod_colegio,
                    COUNT(DISTINCT a.cod_aluno) AS total_alunos,
                    COUNT(DISTINCT v.cod_agente) AS total_pes
                FROM acad_aluno_contexto a
                LEFT JOIN acad_lig_agente_aluno v ON v.cod_aluno = a.cod_aluno AND v.cod_evento = a.cod_evento AND v.status = 'ATIVADA'
                WHERE a.cod_evento = '%s' AND a.status = 'ATIVADA'
                GROUP BY a.cod_colegio", $codEvento);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $contagens = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $contagens[$row['cod_colegio']] = array(
                        'alunos' => intval($row['total_alunos']),
                        'pes'    => intval($row['total_pes'])
                    );
                }
            }
            return $contagens;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function insert(VinculoSupervisorColegio $obj, $debug = false) {
        try {
            $obj->setDataInicio(date('Y-m-d H:i:s'));
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM acad_lig_supervisor_colegio WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_vinculo=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_lig_supervisor_colegio");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodVinculo($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(VinculoSupervisorColegio $obj, $debug = false) {
        try {
            $sql = sprintf("UPDATE acad_lig_supervisor_colegio SET status='DESATIVADA', data_fim='%s', data_alteracao='%s', id_alteracao='%s' WHERE cod_evento='%s' AND cod_vinculo='%s'",
                date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $_SESSION['_cod_usuario'],
                $obj->getCodEvento(), $obj->getCodVinculo());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}
