<?php

class LaudoAlunoDAO extends DAO {

    private $camposCriptografados = array('descricao', 'conclusao', 'recomendacao');

    public function __construct() {
        parent::__construct();
    }

    private function descriptografarCampos(LaudoAluno $obj) {
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $setter = 'set' . ucfirst($campo);
            $valor = $obj->$getter();
            if ($valor !== null && $valor !== '') {
                $obj->$setter(CriptografiaHelper::descriptografar($valor));
            }
        }
    }

    private function criptografarCampos(LaudoAluno $obj) {
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $setter = 'set' . ucfirst($campo);
            $valor = $obj->$getter();
            if ($valor !== null && $valor !== '') {
                $obj->$setter(CriptografiaHelper::criptografar($valor));
            }
        }
    }

    private function restaurarCamposOriginais(LaudoAluno $obj, $valoresOriginais) {
        foreach ($valoresOriginais as $campo => $valor) {
            $setter = 'set' . ucfirst($campo);
            $obj->$setter($valor);
        }
    }

    private function guardarValoresOriginais(LaudoAluno $obj) {
        $valores = array();
        foreach ($this->camposCriptografados as $campo) {
            $getter = 'get' . ucfirst($campo);
            $valores[$campo] = $obj->$getter();
        }
        return $valores;
    }

    public function getById(LaudoAluno $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM acad_laudo_aluno WHERE cod_evento=\'%s\' AND cod_laudo=\'%s\'',
                $obj->getCodEvento(), $obj->getCodLaudo());

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

    public function getByIdExiste(LaudoAluno $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT cod_laudo FROM acad_laudo_aluno WHERE cod_evento=\'%s\' AND cod_laudo=\'%s\'',
                $obj->getCodEvento(), $obj->getCodLaudo());

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

    public function carregarListaLaudosByCodAluno(LaudoAluno $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_laudo_aluno
                WHERE cod_evento='%s' AND cod_aluno='%s' AND status='ATIVADA'
                ORDER BY data_laudo DESC",
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
                $obj->setListaLaudos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(LaudoAluno $obj, $debug = false) {
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

            $sql = "SELECT * FROM acad_laudo_aluno WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_laudo=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_laudo_aluno");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodLaudo($this->conex->Insert_ID());

            // Restaurar valores originais no objeto (para uso posterior)
            $this->restaurarCamposOriginais($obj, $valoresOriginais);

            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(LaudoAluno $obj, $debug = false) {
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

            $sql = "SELECT * FROM acad_laudo_aluno WHERE cod_evento = " . $obj->getCodEvento() . " AND cod_laudo = " . $obj->getCodLaudo();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_laudo_aluno");
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

    public function delete(LaudoAluno $obj, $debug = false) {
        try {
            $sql = sprintf('DELETE FROM acad_laudo_aluno WHERE cod_evento=\'%s\' AND cod_laudo=\'%s\'',
                $obj->getCodEvento(), $obj->getCodLaudo());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}
