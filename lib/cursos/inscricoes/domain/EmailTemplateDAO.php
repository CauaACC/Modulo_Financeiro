<?php

class EmailTemplateDAO extends DAO
{

    public function __construct()
    {
        $this->tableName = "tab_email_templates";
        parent::__construct();
    }

    public function getById(EmailTemplate $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and cod_email_template= \'%s\' '
                , $obj->getCodEvento(), $obj->getCodEmailTemplate());

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

    public function getByIdExiste(EmailTemplate $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_email_template FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and cod_email_template= \'%s\' '
                , $obj->getCodEvento(), $obj->getCodEmailTemplate());

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

    public function carregarListaEmailTemplateByIdEvento(EmailTemplate $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' '
                , $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEmailTemplates($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(EmailTemplate $obj, $debug = 1)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM $this->tableName WHERE "
                . " cod_evento=" . $obj->getCodEvento() . " and cod_email_template=0 ";

            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            $sql = $insertSQL;

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodEmailTemplate($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(EmailTemplate $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);
            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_evento = " . $obj->getCodEvento()
                . " and cod_email_template = " . $obj->getCodEmailTemplate();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);
            $sql = $updateSQL;
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;

        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(EmailTemplate $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' AND cod_email_template= \'%s\' ', $obj->getCodEvento(), $obj->getCodEmailTemplate());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return $rs !== false;
        } catch (ADODB_Exception $e) {
            echo 'Erro: ' . $e->getMessage();
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarAssuntosOutrosEventos(EmailTemplate $obj, $debug = false)
    {
        try {
            // Evento 62 = evento padrao geral opctec (origem dos templates para importacao)
            $codEventoPadrao = 62;
            $sql = sprintf(
                'SELECT sub.max_cod as cod_email_template, t.assunto '
                . 'FROM ' . $this->tableName . ' t '
                . 'INNER JOIN (SELECT MAX(cod_email_template) as max_cod FROM ' . $this->tableName . ' WHERE cod_evento = \'%s\' AND status = \'ATIVADA\' '
                . 'AND assunto NOT IN (SELECT assunto FROM ' . $this->tableName . ' WHERE cod_evento = \'%s\' AND status = \'ATIVADA\') '
                . 'GROUP BY assunto) sub ON t.cod_email_template = sub.max_cod '
                . 'ORDER BY t.assunto',
                $codEventoPadrao, $obj->getCodEvento()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = array();
                foreach ($rs as $rows) {
                    $return[] = array(
                        'cod' => $rows['cod_email_template'],
                        'assunto' => $rows['assunto']
                    );
                }
                return $return;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function importarEmailTemplatesPorIds(EmailTemplate $obj, $ids, $debug = false)
    {
        try {
            $idsClean = array();
            foreach ($ids as $id) {
                $idsClean[] = intval($id);
            }
            $idsIn = implode(',', $idsClean);

            $sql = sprintf(
                'INSERT INTO tab_email_templates (cod_evento, assunto, corpo, descricao, tipo_destinatario, modelo_chave, app_local, status) '
                . 'SELECT \'%s\' as cod_evento, t.assunto, t.corpo, t.descricao, t.tipo_destinatario, t.modelo_chave, t.app_local, t.status '
                . 'FROM ' . $this->tableName . ' t '
                . 'WHERE t.cod_email_template IN (%s)',
                $obj->getCodEvento(), $idsIn
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $affected = $this->conex->Affected_Rows();
            return $affected > 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function importarEmailTemplates(EmailTemplate $obj, $debug=false) {
        try{
            
            $sql = sprintf('INSERT INTO tab_email_templates (
                    cod_evento, assunto, corpo, descricao, tipo_destinatario, 
                    modelo_chave, app_local, status)
                SELECT '.$obj->getCodEvento().' as cod_evento, assunto, corpo, descricao, tipo_destinatario, 
                    modelo_chave, app_local, status
                FROM tab_email_templates
                WHERE cod_evento=\'%s\' ', $obj->getCodEventoAnt());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}