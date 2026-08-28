<?php
class RegistroPedagogicoDAO extends DAO{

    public function __construct() {
        parent::__construct();
    }

    public function getById(RegistroPedagogico $obj, $debug=false) {
        try{

            $sql = sprintf('SELECT * FROM acad_registro_pedagogico WHERE cod_evento=\'%s\' and cod_registro= \'%s\'', $obj->getCodEvento(),$obj->getCodRegistro());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                }
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(RegistroPedagogico $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_registro FROM acad_registro_pedagogico WHERE cod_evento=\'%s\'
            and cod_registro= \'%s\'', $obj->getCodEvento(),$obj->getCodRegistro());

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

    public function carregarListaRegistroPedagogicosByIdEvento(RegistroPedagogico $obj, $debug=false) {
        try{

            $sql = sprintf('SELECT * FROM acad_registro_pedagogico
            WHERE cod_evento=\'%s\' AND status=\'ATIVADA\'
            ORDER BY data_registro DESC',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRegistroPedagogicos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaRegistroPedagogicosByCodAluno(RegistroPedagogico $obj, $debug=false) {
        try{

            $sql = sprintf('SELECT * FROM acad_registro_pedagogico
            WHERE cod_evento=\'%s\' AND cod_aluno=\'%s\' AND status=\'ATIVADA\'
            ORDER BY data_registro DESC',$obj->getCodEvento(),$obj->getCodAluno());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRegistroPedagogicos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByAlunoData(RegistroPedagogico $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_registro FROM acad_registro_pedagogico
                WHERE cod_evento = '%s' AND cod_aluno = '%s' AND data_registro = '%s' AND status = 'ATIVADA'
                LIMIT 1",
                $obj->getCodEvento(), $obj->getCodAluno(), $obj->getDataRegistro());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ($rs && !$rs->EOF) {
                return intval($rs->fields['cod_registro']);
            }
            return false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarPresencaDia($codEvento, $data, $codSupervisor = null, $debug = false) {
        try {
            $filtroSupervisor = '';
            if ($codSupervisor) {
                $filtroSupervisor = sprintf(" AND (
                    a.id_inclusao = '%s'
                    OR a.cod_aluno IN (
                        SELECT vaa2.cod_aluno FROM acad_lig_agente_aluno vaa2
                        INNER JOIN acad_lig_supervisor_agente vsa2 ON vsa2.cod_agente = vaa2.cod_agente AND vsa2.cod_evento = vaa2.cod_evento
                        WHERE vsa2.cod_evento = '%s' AND vsa2.cod_supervisor = '%s' AND vsa2.status = 'ATIVADA' AND vaa2.status = 'ATIVADA'
                    )
                    OR a.cod_colegio IN (
                        SELECT vsc2.cod_colegio FROM acad_lig_supervisor_colegio vsc2
                        WHERE vsc2.cod_evento = '%s' AND vsc2.cod_supervisor = '%s' AND vsc2.status = 'ATIVADA'
                    )
                )", $codSupervisor, $codEvento, $codSupervisor, $codEvento, $codSupervisor);
            }

            $sql = sprintf("SELECT a.cod_aluno, a.codigo_exibicao,
                    a.serie_turma, a.turno,
                    c.nome AS nome_colegio,
                    v.cod_agente,
                    i.nome_inscrito AS nome_agente,
                    r.presenca, r.condicao_inicio, r.condicao_final, r.cod_registro
                FROM acad_aluno_contexto a
                INNER JOIN acad_lig_agente_aluno v ON v.cod_aluno = a.cod_aluno AND v.cod_evento = a.cod_evento AND v.status = 'ATIVADA'
                LEFT JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = v.cod_agente AND i.cod_evento = v.cod_evento
                LEFT JOIN acad_registro_pedagogico r ON r.cod_aluno = a.cod_aluno AND r.cod_evento = a.cod_evento
                    AND r.data_registro = '%s' AND r.status = 'ATIVADA'
                WHERE a.cod_evento = '%s' AND a.status = 'ATIVADA' %s
                ORDER BY c.nome ASC, i.nome_inscrito ASC, a.codigo_exibicao ASC",
                $data, $codEvento, $filtroSupervisor);

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

    public function carregarEstatisticasGerais($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT r.cod_aluno, r.cod_agente, r.data_registro,
                    r.condicao_inicio, r.participacao, r.grau_apoio,
                    r.situacoes, r.condicao_final, r.evolucao_periodo,
                    a.codigo_exibicao, a.cod_colegio,
                    c.nome AS nome_colegio,
                    i.nome_inscrito AS nome_agente
                FROM acad_registro_pedagogico r
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = r.cod_aluno AND a.cod_evento = r.cod_evento AND a.status = 'ATIVADA'
                INNER JOIN acad_lig_agente_aluno v ON v.cod_aluno = r.cod_aluno AND v.cod_agente = r.cod_agente AND v.cod_evento = r.cod_evento AND v.status = 'ATIVADA'
                LEFT JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = r.cod_agente AND i.cod_evento = r.cod_evento
                WHERE r.cod_evento = '%s' AND r.status = 'ATIVADA'
                ORDER BY r.data_registro DESC", $codEvento);

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

    public function insert(RegistroPedagogico $obj, $debug=false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');


            $sql = "SELECT * FROM acad_registro_pedagogico WHERE cod_evento=".$obj->getCodEvento()." and cod_registro=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_registro_pedagogico");
            $fws_ = $obj->getAllForArray($arrayColunas,false);

            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodRegistro($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(RegistroPedagogico $obj, $debug=false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setSessionIdAlteracao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM acad_registro_pedagogico WHERE cod_evento = ".$obj->getCodEvento()." and cod_registro = ".$obj->getCodRegistro();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_registro_pedagogico");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);

            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;

        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(RegistroPedagogico $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM acad_registro_pedagogico WHERE cod_evento=\'%s\' and cod_registro= \'%s\'', $obj->getCodEvento(),$obj->getCodRegistro());

            $this->conex->debug = $debug;

            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
