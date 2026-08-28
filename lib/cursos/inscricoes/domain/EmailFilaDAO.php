<?php

class EmailFilaDAO extends DAO
{
    public function __construct()
    {
        $this->tableName = "tab_email_fila";
        parent::__construct();
    }

    public function insert(EmailFila $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);

            $sql = "SELECT * FROM $this->tableName WHERE cod_email_fila=0";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodEmailFila($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insertEmLote(EmailFila $objBase, $listaEmails, $debug = false)
    {
        try {
            $total = 0;
            $datIncl = date('Y-m-d H:i:s');
            $resIncl = $_SESSION['_cod_usuario'];
            $ipIncl = $_SERVER['REMOTE_ADDR'];
            $httpUserAgent = $_SERVER['HTTP_USER_AGENT'];

            foreach ($listaEmails as $email) {
                $sql = sprintf(
                    "INSERT INTO %s (cod_evento, cod_email_template, cod_inscrito, email_destinatario, nome_destinatario, "
                    . "assunto, corpo, status, tentativas, dat_programado, dat_incl, res_incl, ip_incl, http_user_agent_incl) "
                    . "VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', 'PENDENTE', 0, '%s', '%s', '%s', '%s', '%s')",
                    $this->tableName,
                    addslashes($objBase->getCodEvento()),
                    addslashes($email['cod_email_template']),
                    addslashes($email['cod_inscrito']),
                    addslashes($email['email_destinatario']),
                    addslashes($email['nome_destinatario']),
                    addslashes($email['assunto']),
                    addslashes($email['corpo']),
                    addslashes($objBase->getDatProgramado()),
                    $datIncl, $resIncl, $ipIncl, addslashes($httpUserAgent)
                );

                $this->conex->debug = $debug;
                $rs = $this->conex->Execute($sql);
                if ($rs) $total++;
            }
            return $total;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(EmailFila $obj, $debug = false)
    {
        try {
            $obj->setDatAlt(date('Y-m-d H:i:s'));
            $obj->setResAlt($_SESSION['_cod_usuario']);
            $obj->setIpAlt($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlt($_SERVER['HTTP_USER_AGENT']);

            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_email_fila = " . $obj->getCodEmailFila();
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

    public function cancelar(EmailFila $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                "UPDATE %s SET status='CANCELADO', dat_alt='%s', res_alt='%s' "
                . "WHERE cod_email_fila='%s' AND cod_evento='%s' AND status='PENDENTE'",
                $this->tableName,
                date('Y-m-d H:i:s'),
                $_SESSION['_cod_usuario'],
                $obj->getCodEmailFila(),
                $obj->getCodEvento()
            );

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return $this->conex->Affected_Rows() > 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function cancelarPorTemplate(EmailFila $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                "UPDATE %s SET status='CANCELADO', dat_alt='%s', res_alt='%s' "
                . "WHERE cod_email_template='%s' AND cod_evento='%s' AND status='PENDENTE'",
                $this->tableName,
                date('Y-m-d H:i:s'),
                $_SESSION['_cod_usuario'],
                $obj->getCodEmailTemplate(),
                $obj->getCodEvento()
            );

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return $this->conex->Affected_Rows();
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getById(EmailFila $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                "SELECT * FROM %s WHERE cod_email_fila='%s' AND cod_evento='%s'",
                $this->tableName,
                $obj->getCodEmailFila(),
                $obj->getCodEvento()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            foreach ($rs as $rows) {}
            $obj->setAll($rows);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarLista(EmailFila $obj, $debug = false)
    {
        try {
            $where = sprintf("cod_evento='%s'", $obj->getCodEvento());

            if ($obj->getStatus() != '') {
                $where .= sprintf(" AND status='%s'", addslashes($obj->getStatus()));
            }
            if ($obj->getCodEmailTemplate() != '') {
                $where .= sprintf(" AND cod_email_template='%s'", addslashes($obj->getCodEmailTemplate()));
            }

            $sql = "SELECT * FROM " . $this->tableName . " WHERE " . $where . " ORDER BY dat_programado DESC, cod_email_fila DESC";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return array();
            }
            $return = array();
            foreach ($rs as $rows) {
                $obj->setAll($rows);
                $return[] = clone $obj;
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function carregarResumo(EmailFila $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                "SELECT status, COUNT(*) as total FROM %s WHERE cod_evento='%s' GROUP BY status",
                $this->tableName,
                $obj->getCodEvento()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $resumo = array('PENDENTE' => 0, 'ENVIADO' => 0, 'ERRO' => 0, 'CANCELADO' => 0);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $resumo[$rows['status']] = $rows['total'];
                }
            }
            return $resumo;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Busca emails pendentes prontos para envio (para uso do processo de background)
     */
    public function buscarPendentesParaEnvio($limite = 50, $debug = false)
    {
        try {
            $sql = sprintf(
                "SELECT * FROM %s WHERE status='PENDENTE' AND dat_programado <= NOW() ORDER BY dat_programado ASC LIMIT %d",
                $this->tableName,
                intval($limite)
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return array();
            }
            $return = array();
            foreach ($rs as $rows) {
                $return[] = $rows;
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Marca email como enviado (para uso do processo de background)
     */
    public function marcarEnviado($codEmailFila, $debug = false)
    {
        try {
            $sql = sprintf(
                "UPDATE %s SET status='ENVIADO', dat_enviado='%s', tentativas=tentativas+1 WHERE cod_email_fila='%s'",
                $this->tableName,
                date('Y-m-d H:i:s'),
                intval($codEmailFila)
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Marca email com erro (para uso do processo de background)
     */
    public function marcarErro($codEmailFila, $mensagemErro, $debug = false)
    {
        try {
            $sql = sprintf(
                "UPDATE %s SET status='ERRO', erro_mensagem='%s', tentativas=tentativas+1 WHERE cod_email_fila='%s'",
                $this->tableName,
                addslashes($mensagemErro),
                intval($codEmailFila)
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
