<?php

class AvaliacaoInicialDAO extends DAO {

    private $camposCriptografados = array('habilidades', 'comportamentosDificeis', 'comportamentosOutros', 'demandasEscolares');

    public function __construct() {
        parent::__construct();
    }

    private function descriptografarCampos(AvaliacaoInicial $obj) {
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $setter = 'set' . ucfirst($campo);
            $valor = $obj->$getter();
            if ($valor !== null && $valor !== '') {
                $obj->$setter(CriptografiaHelper::descriptografar($valor));
            }
        }
    }

    private function criptografarCampos(AvaliacaoInicial $obj) {
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $setter = 'set' . ucfirst($campo);
            $valor = $obj->$getter();
            if ($valor !== null && $valor !== '') {
                $obj->$setter(CriptografiaHelper::criptografar($valor));
            }
        }
    }

    private function guardarValoresOriginais(AvaliacaoInicial $obj) {
        $valores = array();
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $valores[$campo] = $obj->$getter();
        }
        return $valores;
    }

    private function restaurarCamposOriginais(AvaliacaoInicial $obj, $valoresOriginais) {
        foreach ($valoresOriginais as $campo => $valor) {
            $setter = 'set' . ucfirst($campo);
            $obj->$setter($valor);
        }
    }

    public function getById(AvaliacaoInicial $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM acad_avaliacao_inicial WHERE cod_evento=\'%s\' AND cod_avaliacao=\'%s\'',
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

    public function getByIdExiste(AvaliacaoInicial $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT cod_avaliacao FROM acad_avaliacao_inicial WHERE cod_evento=\'%s\' AND cod_avaliacao=\'%s\'',
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

    public function carregarListaAvaliacoesByCodAluno(AvaliacaoInicial $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_avaliacao_inicial
                WHERE cod_evento='%s' AND cod_aluno='%s' AND status='ATIVADA'
                ORDER BY data_avaliacao DESC",
                $obj->getCodEvento(), $obj->getCodAluno());

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

    public function insert(AvaliacaoInicial $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            // Criptografar campos sensiveis antes de salvar
            $valoresOriginais = $this->guardarValoresOriginais($obj);
            $this->criptografarCampos($obj);

            $sql = "SELECT * FROM acad_avaliacao_inicial WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_avaliacao=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_avaliacao_inicial");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodAvaliacao($this->conex->Insert_ID());

            // Restaurar valores originais no objeto
            $this->restaurarCamposOriginais($obj, $valoresOriginais);

            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(AvaliacaoInicial $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setSessionIdAlteracao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            // Criptografar campos sensiveis antes de salvar
            $valoresOriginais = $this->guardarValoresOriginais($obj);
            $this->criptografarCampos($obj);

            $sql = "SELECT * FROM acad_avaliacao_inicial WHERE cod_evento = " . $obj->getCodEvento() . " AND cod_avaliacao = " . $obj->getCodAvaliacao();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_avaliacao_inicial");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);

            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            // Restaurar valores originais no objeto
            $this->restaurarCamposOriginais($obj, $valoresOriginais);

            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarAlunosComStatusPorProgramacao($codEvento, $tipoAvaliacao, $dataLiberacao, $dataFechamento, $debug = false) {
        try {
            $sql = sprintf("SELECT a.cod_aluno, a.codigo_exibicao, a.iniciais, a.serie_turma, a.turno,
                    c.nome AS nome_colegio,
                    av.cod_avaliacao, av.data_avaliacao, av.data_inclusao AS data_preenchimento
                FROM acad_aluno_contexto a
                LEFT JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                LEFT JOIN acad_avaliacao_inicial av ON av.cod_aluno = a.cod_aluno AND av.cod_evento = a.cod_evento
                    AND av.status='ATIVADA' AND av.tipo_avaliacao='%s'
                    AND av.data_avaliacao BETWEEN '%s' AND '%s'
                WHERE a.cod_evento='%s' AND a.status='ATIVADA'
                ORDER BY a.codigo_exibicao ASC",
                $tipoAvaliacao, $dataLiberacao, $dataFechamento, $codEvento);

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

    public function delete(AvaliacaoInicial $obj, $debug = false) {
        try {
            $sql = sprintf('DELETE FROM acad_avaliacao_inicial WHERE cod_evento=\'%s\' AND cod_avaliacao=\'%s\'',
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
