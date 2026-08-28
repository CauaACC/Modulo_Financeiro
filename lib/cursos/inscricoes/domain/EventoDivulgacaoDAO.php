<?php

class EventoDivulgacaoDAO extends DAO
{

    public function __construct()
    {
        $this->tableName = "tab_evento_divulgacao";
        parent::__construct();
    }

    public function getByCodEvento(EventoDivulgacao $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $obj->setAll($rs->fields);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByCodEventoExiste(EventoDivulgacao $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_evento FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return !((!$rs) or ($rs->EOF));
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(EventoDivulgacao $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            // Respeita o ativo/inativo escolhido na tela; so assume o padrao
            // quando nada foi informado.
            if ($obj->getStatus() == '') {
                $obj->setStatus('ATIVADA');
            }

            // Recordset vazio de proposito (cod_evento=0 nao existe) — o ADOdb so
            // precisa dele para conhecer as colunas ao montar o INSERT.
            $sql = "SELECT * FROM $this->tableName WHERE cod_evento=0 ";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(EventoDivulgacao $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_evento = " . (int) $obj->getCodEvento();
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

    /**
     * Dados do evento (tab_eventos) usados como sugestao de preenchimento.
     * Retorna o array de colunas ou false.
     */
    public function getDadosEvento($codEvento, $debug = false)
    {
        try {
            $sql = sprintf('SELECT titulo, data_ini, data_fim, periodo, desc_local_realizacao, '
                . 'cidade_uf, duracao, desc_evento, url_prefixo '
                . 'FROM tab_eventos WHERE cod_evento=\'%s\' ', (int) $codEvento);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            return $rs->fields;
        } catch (ADODB_Exception $e) {
            // Sugestao e conveniencia: falha aqui nao pode derrubar a tela.
            return false;
        }
    }

    /**
     * Ultimo projeto (solicitacao) do evento — a origem mais rica de sugestoes
     * (publico-alvo, vagas, programacao, inscricoes). Retorna colunas ou false.
     *
     * tab_projetos NAO existe em todo ambiente (na base do InscriTec de
     * producao, por exemplo, a tela de Projetos nao tem tabela) — por isso a
     * checagem antes: sem ela, toda abertura da tela dispararia erro de SQL.
     */
    public function getDadosProjeto($codEvento, $debug = false)
    {
        try {
            $sqlExiste = "SELECT COUNT(*) AS tem FROM information_schema.TABLES "
                . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tab_projetos'";
            $rs = $this->conex->Execute($sqlExiste);
            if ((!$rs) or ($rs->EOF) or ((int) $rs->fields['tem']) === 0) {
                return false;
            }

            $sql = sprintf('SELECT * FROM tab_projetos WHERE cod_evento=\'%s\' '
                . 'ORDER BY cod_projeto DESC LIMIT 1', (int) $codEvento);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            return $rs->fields;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }
}
