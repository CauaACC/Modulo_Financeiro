<?php
class ModeloContratoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(ModeloContrato $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM tab_modelo_contrato '
                . 'WHERE cod_evento=\'%s\' AND cod_modelo_contrato=\'%s\'',
                $obj->getCodEvento(), $obj->getCodModeloContrato());

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

    public function getByIdExiste(ModeloContrato $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM tab_modelo_contrato '
                . 'WHERE cod_modelo_contrato=\'%s\'',
                $obj->getCodModeloContrato());

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

    public function carregarListaByEvento(ModeloContrato $obj, $debug = false) {
        try {
            $sql = sprintf('SELECT * FROM tab_modelo_contrato '
                . 'WHERE cod_evento=\'%s\' AND status=\'ATIVADA\' '
                . 'ORDER BY ordem, nome',
                $obj->getCodEvento());

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
                $obj->setListaModeloContrato($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(ModeloContrato $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessionIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            if ($obj->getVersao() == '') {
                $obj->setVersao(1);
            }
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM tab_modelo_contrato "
                . "WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_modelo_contrato=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("tab_modelo_contrato");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodModeloContrato($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(ModeloContrato $obj, $debug = false) {
        try {
            $sql = "UPDATE tab_modelo_contrato SET "
                . " nome = " . $this->conex->qstr($obj->getNome())
                . ", descricao = " . $this->conex->qstr($obj->getDescricao())
                . ", ordem = " . intval($obj->getOrdem())
                . ", data_alteracao = " . $this->conex->qstr(date('Y-m-d H:i:s'))
                . ", session_id_alteracao = " . $this->conex->qstr($_SESSION['_cod_usuario'])
                . ", versao = versao + 1";

            // formato_saida pode não existir em bancos antigos
            try {
                $cols = $this->conex->MetaColumnNames("tab_modelo_contrato");
                if (in_array('FORMATO_SAIDA', $cols) || in_array('formato_saida', $cols)) {
                    $sql .= ", formato_saida = " . $this->conex->qstr($obj->getFormatoSaida() ?: 'DOCX');
                }
            } catch (Exception $e) {};

            if ($obj->getArquivoModelo()) {
                $sql .= ", arquivo_modelo = " . $this->conex->qstr($obj->getArquivoModelo());
            }
            if ($obj->getArquivoUploadNome()) {
                $sql .= ", arquivo_upload_nome = " . $this->conex->qstr($obj->getArquivoUploadNome());
            }
            if ($obj->getCamposObrigatorios()) {
                $sql .= ", campos_obrigatorios = " . $this->conex->qstr($obj->getCamposObrigatorios());
            }

            $sql .= " WHERE cod_modelo_contrato = " . intval($obj->getCodModeloContrato());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(ModeloContrato $obj, $debug = false) {
        try {
            $sql = sprintf('UPDATE tab_modelo_contrato SET status=\'DESATIVADA\' '
                . 'WHERE cod_modelo_contrato=\'%s\'',
                $obj->getCodModeloContrato());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
?>