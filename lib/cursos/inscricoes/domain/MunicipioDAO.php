<?php

class MunicipioDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(Municipio $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM fin_municipios WHERE municipio_id = \'%s\'', $obj->getMunicipioId());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getAll(Municipio $obj, $debug = false) {
        try {
            $sql = 'SELECT * FROM fin_municipios ORDER BY nome ASC';
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaMunicipios($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Municipio $obj, $debug = false) {
        try {
            $sql = sprintf("INSERT INTO fin_municipios (nome, uf, codigo_municipio, url_api, observacoes) VALUES ('%s', '%s', '%s', '%s', '%s')",
                $obj->getNome(),
                $obj->getUf(),
                $obj->getCodigoMunicipio(),
                $obj->getUrlApi(),
                $obj->getObservacoes()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setMunicipioId($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(Municipio $obj, $debug = false) {
        try {
            $sql = sprintf("UPDATE fin_municipios SET nome = '%s', uf = '%s', codigo_municipio = '%s', url_api = '%s', observacoes = '%s' WHERE municipio_id = %d",
                $obj->getNome(),
                $obj->getUf(),
                $obj->getCodigoMunicipio(),
                $obj->getUrlApi(),
                $obj->getObservacoes(),
                $obj->getMunicipioId()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(Municipio $obj, $debug = false) {
        try {
            $sql = sprintf('DELETE FROM fin_municipios WHERE municipio_id = %d', $obj->getMunicipioId());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
