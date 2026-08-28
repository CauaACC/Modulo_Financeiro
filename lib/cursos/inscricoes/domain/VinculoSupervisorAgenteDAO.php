<?php

class VinculoSupervisorAgenteDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getByIdExiste(VinculoSupervisorAgente $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT cod_vinculo FROM acad_lig_supervisor_agente WHERE cod_evento=\'%s\' AND cod_vinculo=\'%s\'',
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

    public function verificarDuplicidade(VinculoSupervisorAgente $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_vinculo FROM acad_lig_supervisor_agente
                WHERE cod_evento='%s' AND cod_supervisor='%s' AND cod_agente='%s' AND status='ATIVADA'",
                $obj->getCodEvento(), $obj->getCodSupervisor(), $obj->getCodAgente());

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

    public function carregarListaVinculosBySupervisor(VinculoSupervisorAgente $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT v.cod_vinculo, v.cod_evento, v.cod_supervisor, v.cod_agente, v.status, v.data_inicio,
                    i.nome_inscrito, i.cpf, i.status_processo,
                    (SELECT COUNT(*) FROM acad_lig_agente_aluno aa
                     INNER JOIN acad_aluno_contexto ac ON ac.cod_aluno = aa.cod_aluno AND ac.cod_evento = aa.cod_evento AND ac.status = 'ATIVADA'
                     WHERE aa.cod_agente = v.cod_agente AND aa.cod_evento = v.cod_evento AND aa.status = 'ATIVADA') as total_alunos
                FROM acad_lig_supervisor_agente v
                INNER JOIN tab_inscritos i ON i.cod_inscrito = v.cod_agente AND i.cod_evento = v.cod_evento
                WHERE v.cod_evento='%s' AND v.cod_supervisor='%s' AND v.status='ATIVADA'
                ORDER BY i.nome_inscrito ASC",
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

    public function carregarCodAgentesBySupervisor($codEvento, $codSupervisor, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_agente FROM acad_lig_supervisor_agente
                WHERE cod_evento='%s' AND cod_supervisor='%s' AND status='ATIVADA'",
                $codEvento, $codSupervisor);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $codAgentes = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $codAgentes[] = $row['cod_agente'];
                }
            }
            return $codAgentes;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function insert(VinculoSupervisorAgente $obj, $debug = false) {
        try {
            $obj->setDataInicio(date('Y-m-d H:i:s'));
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM acad_lig_supervisor_agente WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_vinculo=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_lig_supervisor_agente");
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

    public function delete(VinculoSupervisorAgente $obj, $debug = false) {
        try {
            $sql = sprintf("UPDATE acad_lig_supervisor_agente SET status='DESATIVADA', data_fim='%s', data_alteracao='%s', id_alteracao='%s' WHERE cod_evento='%s' AND cod_vinculo='%s'",
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
