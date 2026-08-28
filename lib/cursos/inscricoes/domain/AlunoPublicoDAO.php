<?php

class AlunoPublicoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    private function gerarUUID() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // versão 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variante RFC 4122
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function gerarCodigoExibicao($codEvento) {
        try {
            $sql = sprintf("SELECT COUNT(*) AS total FROM acad_aluno_contexto WHERE cod_evento='%s'", $codEvento);
            $rs = $this->conex->Execute($sql);
            $proximo = ($rs && !$rs->EOF) ? intval($rs->fields['total']) + 1 : 1;
            return 'ALU-' . str_pad($proximo, 3, '0', STR_PAD_LEFT);
        } catch (ADODB_Exception $e) {
            return 'ALU-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
    }

    private function descriptografarObj($obj) {
        $nomeCripto = $obj->getNomeCompleto();
        if ($nomeCripto) {
            $obj->setNomeCompleto(CryptoHelper::decrypt($nomeCripto));
        }
    }

    private function criptografarObj($obj) {
        $nomeOriginal = $obj->getNomeCompleto();
        if ($nomeOriginal) {
            $obj->setIniciais(CryptoHelper::gerarIniciais($nomeOriginal));
            $obj->setNomeCompleto(CryptoHelper::encrypt($nomeOriginal));
        }
    }

    private function restaurarNome($obj, $nomeOriginal) {
        $obj->setNomeCompleto($nomeOriginal);
    }

    public function getById(AlunoPublico $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM acad_aluno_contexto WHERE cod_evento=\'%s\' AND cod_aluno=\'%s\'',
                $obj->getCodEvento(), $obj->getCodAluno());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {}
                $obj->setAll($rows);
                $this->descriptografarObj($obj);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(AlunoPublico $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT cod_aluno FROM acad_aluno_contexto WHERE cod_evento=\'%s\' AND cod_aluno=\'%s\'',
                $obj->getCodEvento(), $obj->getCodAluno());

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

    public function carregarListaAlunoPublicosByIdEvento(AlunoPublico $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT a.*, c.nome AS nome_colegio,
                    (SELECT COUNT(*) FROM acad_registro_pedagogico rp
                     WHERE rp.cod_aluno = a.cod_aluno AND rp.cod_evento = a.cod_evento AND rp.status = 'ATIVADA') AS total_registros,
                    (SELECT COUNT(*) FROM acad_laudo_aluno la
                     WHERE la.cod_aluno = a.cod_aluno AND la.cod_evento = a.cod_evento AND la.status = 'ATIVADA') AS total_documentos,
                    (SELECT GROUP_CONCAT(DISTINCT i.nome_inscrito SEPARATOR ', ')
                     FROM acad_lig_agente_aluno v
                     INNER JOIN tab_inscritos i ON i.cod_inscrito = v.cod_agente AND i.cod_evento = v.cod_evento
                     WHERE v.cod_aluno = a.cod_aluno AND v.cod_evento = a.cod_evento AND v.status = 'ATIVADA') AS nome_agente,
                    (SELECT v.cod_agente
                     FROM acad_lig_agente_aluno v
                     WHERE v.cod_aluno = a.cod_aluno AND v.cod_evento = a.cod_evento AND v.status = 'ATIVADA' LIMIT 1) AS cod_agente_pes,
                    (SELECT i.cpf
                     FROM acad_lig_agente_aluno v
                     INNER JOIN tab_inscritos i ON i.cod_inscrito = v.cod_agente AND i.cod_evento = v.cod_evento
                     WHERE v.cod_aluno = a.cod_aluno AND v.cod_evento = a.cod_evento AND v.status = 'ATIVADA' LIMIT 1) AS cpf_agente
                FROM acad_aluno_contexto a
                LEFT JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                WHERE a.cod_evento='%s' AND a.status='ATIVADA' ORDER BY a.codigo_exibicao ASC",
                $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = array();
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $this->descriptografarObj($obj);
                    $return[] = clone $obj;
                }
                $obj->setListaAlunoPublicos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaAlunoPublicosBySupervisor(AlunoPublico $obj, $idInclusao, $debug = false) {
        try {
            $sql = sprintf("SELECT a.*, c.nome AS nome_colegio,
                    (SELECT COUNT(*) FROM acad_registro_pedagogico rp
                     WHERE rp.cod_aluno = a.cod_aluno AND rp.cod_evento = a.cod_evento AND rp.status = 'ATIVADA') AS total_registros,
                    (SELECT COUNT(*) FROM acad_laudo_aluno la
                     WHERE la.cod_aluno = a.cod_aluno AND la.cod_evento = a.cod_evento AND la.status = 'ATIVADA') AS total_documentos,
                    (SELECT GROUP_CONCAT(DISTINCT i.nome_inscrito SEPARATOR ', ')
                     FROM acad_lig_agente_aluno v
                     INNER JOIN tab_inscritos i ON i.cod_inscrito = v.cod_agente AND i.cod_evento = v.cod_evento
                     WHERE v.cod_aluno = a.cod_aluno AND v.cod_evento = a.cod_evento AND v.status = 'ATIVADA') AS nome_agente
                FROM acad_aluno_contexto a
                LEFT JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                WHERE a.cod_evento='%s' AND a.status='ATIVADA'
                AND (
                    a.id_inclusao='%s'
                    OR a.cod_aluno IN (
                        SELECT vaa.cod_aluno
                        FROM acad_lig_agente_aluno vaa
                        INNER JOIN acad_lig_supervisor_agente vsa
                            ON vsa.cod_agente = vaa.cod_agente AND vsa.cod_evento = vaa.cod_evento
                        WHERE vsa.cod_evento='%s' AND vsa.cod_supervisor='%s'
                            AND vsa.status='ATIVADA' AND vaa.status='ATIVADA'
                    )
                    OR a.cod_colegio IN (
                        SELECT vsc.cod_colegio
                        FROM acad_lig_supervisor_colegio vsc
                        WHERE vsc.cod_evento='%s' AND vsc.cod_supervisor='%s'
                            AND vsc.status='ATIVADA'
                    )
                )
                ORDER BY a.codigo_exibicao ASC",
                $obj->getCodEvento(), addslashes($idInclusao),
                $obj->getCodEvento(), addslashes($idInclusao),
                $obj->getCodEvento(), addslashes($idInclusao));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = array();
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $this->descriptografarObj($obj);
                    $return[] = clone $obj;
                }
                $obj->setListaAlunoPublicos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(AlunoPublico $obj, $debug = false) {
        try {
            $uuid = $this->gerarUUID();
            $obj->setCodAluno($uuid);
            if (!$obj->getCodigoExibicao()) {
                $obj->setCodigoExibicao($this->gerarCodigoExibicao($obj->getCodEvento()));
            }
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $nomeOriginal = $obj->getNomeCompleto();
            $this->criptografarObj($obj);

            $sql = "SELECT * FROM acad_aluno_contexto WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_aluno='0'";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_aluno_contexto");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $this->restaurarNome($obj, $nomeOriginal);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(AlunoPublico $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setSessionIdAlteracao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);

            $nomeOriginal = $obj->getNomeCompleto();
            $this->criptografarObj($obj);

            $sql = "SELECT * FROM acad_aluno_contexto WHERE cod_evento = " . $obj->getCodEvento() . " AND cod_aluno = '" . $obj->getCodAluno() . "'";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_aluno_contexto");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);

            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $this->restaurarNome($obj, $nomeOriginal);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(AlunoPublico $obj, $debug = false) {
        try {
            $sql = sprintf("UPDATE acad_aluno_contexto SET status='DESATIVADA', data_alteracao='%s', id_alteracao='%s' WHERE cod_evento='%s' AND cod_aluno='%s'",
                date('Y-m-d H:i:s'), $_SESSION['_cod_usuario'], $obj->getCodEvento(), $obj->getCodAluno());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}
