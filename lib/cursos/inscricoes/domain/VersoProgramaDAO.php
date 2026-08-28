<?php

/**
 * DAO do Editor de Programa (verso do certificado).
 * Todo o SQL/acesso ao banco mora aqui. Tabela: tab_verso_programa.
 */
class VersoProgramaDAO extends DAO
{
    public function __construct()
    {
        $this->tableName = "tab_verso_programa";
        parent::__construct();
    }

    /**
     * Carrega o registro do escopo (evento + oficina) para dentro do objeto.
     * cod_oficina 0 = geral do evento. Retorna true se encontrou.
     */
    public function getByEventoOficina(VersoPrograma $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=%d AND cod_oficina=%d LIMIT 1',
                (int) $obj->getCodEvento(), (int) $obj->getCodOficina()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            $obj->setAll($rs->fields);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByCod(VersoPrograma $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=%d AND cod_verso_programa=%d LIMIT 1',
                (int) $obj->getCodEvento(), (int) $obj->getCodVersoPrograma()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            $obj->setAll($rs->fields);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Retorna o cod_verso_programa do escopo, ou false se nao existir. */
    public function getCodByEscopo(VersoPrograma $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT cod_verso_programa FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=%d AND cod_oficina=%d LIMIT 1',
                (int) $obj->getCodEvento(), (int) $obj->getCodOficina()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            return $rs->fields['cod_verso_programa'];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function existePorEscopo(VersoPrograma $obj, $debug = false)
    {
        return $this->getCodByEscopo($obj, $debug) !== false;
    }

    /**
     * Lista os escopos do evento que ja tem programa cadastrado, para a tela
     * mostrar ao coordenador o que existe e o que falta (evita recriar por
     * engano um programa que ja estava la).
     */
    public function listarEscopos(VersoPrograma $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT cod_oficina, titulo, arquivo_gerado, dat_alter, dat_incl '
                . 'FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=%d ORDER BY cod_oficina',
                (int) $obj->getCodEvento()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs) {
                return array();
            }
            $lista = array();
            while (!$rs->EOF) {
                $lista[] = $rs->fields;
                $rs->MoveNext();
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function insert(VersoPrograma $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM $this->tableName WHERE cod_verso_programa=0 ";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodVersoPrograma($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(VersoPrograma $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setVersao(((int) $obj->getVersao()) + 1);
            // O objeto vem do POST (sem status); nao deixar o GetUpdateSQL gravar
            // status vazio/NULL (isso "sumia" o registro nas consultas). Verso nao
            // usa soft-delete, entao sempre ATIVADA.
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM " . $this->tableName
                . " WHERE cod_evento=" . (int) $obj->getCodEvento()
                . " AND cod_verso_programa=" . (int) $obj->getCodVersoPrograma();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
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

    public function delete(VersoPrograma $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'DELETE FROM ' . $this->tableName . ' WHERE cod_evento=%d AND cod_verso_programa=%d',
                (int) $obj->getCodEvento(), (int) $obj->getCodVersoPrograma()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
