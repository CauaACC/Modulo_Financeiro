<?php

class VinculoAgenteAlunoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(VinculoAgenteAluno $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT v.*, a.codigo_exibicao, a.nome_completo, a.iniciais, a.cod_colegio, a.nivel, a.serie_turma, a.turno, c.nome AS nome_colegio
                FROM acad_lig_agente_aluno v
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = v.cod_aluno AND a.cod_evento = v.cod_evento
                LEFT JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                WHERE v.cod_evento=\'%s\' AND v.cod_vinculo=\'%s\' AND v.status=\'ATIVADA\' AND a.status=\'ATIVADA\'',
                $obj->getCodEvento(), $obj->getCodVinculo());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {}
                $obj->setAll($rows);
                if ($obj->getNomeCompleto()) {
                    $obj->setNomeCompleto(CryptoHelper::decrypt($obj->getNomeCompleto()));
                }
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(VinculoAgenteAluno $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT cod_vinculo FROM acad_lig_agente_aluno WHERE cod_evento=\'%s\' AND cod_vinculo=\'%s\'',
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

    public function carregarListaVinculosByAgente(VinculoAgenteAluno $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT v.*, a.codigo_exibicao, a.nome_completo, a.iniciais, a.cod_colegio, a.nivel, a.serie_turma, a.turno, c.nome AS nome_colegio
                FROM acad_lig_agente_aluno v
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = v.cod_aluno AND a.cod_evento = v.cod_evento
                LEFT JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                WHERE v.cod_evento=\'%s\' AND v.cod_agente=\'%s\' AND v.status=\'ATIVADA\' AND a.status=\'ATIVADA\'
                ORDER BY a.codigo_exibicao ASC',
                $obj->getCodEvento(), $obj->getCodAgente());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = array();
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    if ($obj->getNomeCompleto()) {
                        $obj->setNomeCompleto(CryptoHelper::decrypt($obj->getNomeCompleto()));
                    }
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

    public function insert(VinculoAgenteAluno $obj, $debug = false) {
        try {
            $obj->setDataInicio(date('Y-m-d H:i:s'));
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM acad_lig_agente_aluno WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_vinculo=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_lig_agente_aluno");
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

    public function update(VinculoAgenteAluno $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setSessionIdAlteracao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);

            $sql = "SELECT * FROM acad_lig_agente_aluno WHERE cod_evento = " . $obj->getCodEvento() . " AND cod_vinculo = " . $obj->getCodVinculo();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_lig_agente_aluno");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);

            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function contarVinculosPorAgenteTurno(VinculoAgenteAluno $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT a.turno, COUNT(*) AS total
                FROM acad_lig_agente_aluno v
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = v.cod_aluno AND a.cod_evento = v.cod_evento
                WHERE v.cod_evento='%s' AND v.cod_agente='%s' AND v.status='ATIVADA' AND a.status='ATIVADA'
                GROUP BY a.turno",
                $obj->getCodEvento(), $obj->getCodAgente());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $contagem = array('total' => 0);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $turno = $row['turno'];
                    $total = intval($row['total']);
                    $contagem[$turno] = $total;
                    $contagem['total'] += $total;
                }
            }
            return $contagem;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array('total' => 0);
        }
    }

    public function carregarMapaAgenteAluno($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_agente, cod_aluno FROM acad_lig_agente_aluno WHERE cod_evento='%s' AND status='ATIVADA'",
                $codEvento);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $mapa = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $mapa[] = array(
                        'codAgente' => $row['cod_agente'],
                        'codAluno'  => $row['cod_aluno']
                    );
                }
            }
            return $mapa;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function delete(VinculoAgenteAluno $obj, $debug = false) {
        try {
            $sql = sprintf("UPDATE acad_lig_agente_aluno SET status='DESATIVADA', data_fim='%s', data_alteracao='%s', id_alteracao='%s' WHERE cod_evento='%s' AND cod_vinculo='%s'",
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
