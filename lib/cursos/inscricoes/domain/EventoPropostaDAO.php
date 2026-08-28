<?php

class EventoPropostaDAO extends DAO
{

    public function __construct()
    {
        $this->tableName = "tab_evento_proposta";
        parent::__construct();
    }

    public function getByCodEvento(EventoProposta $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' ',
                (int) $obj->getCodEvento());
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

    public function getByCodEventoExiste(EventoProposta $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_evento FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' ',
                (int) $obj->getCodEvento());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return !((!$rs) or ($rs->EOF));
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(EventoProposta $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            if ($obj->getStatus() == '') {
                $obj->setStatus('ATIVADA');
            }

            $sql = "SELECT * FROM $this->tableName WHERE cod_evento=0 ";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);

            $this->conex->debug = $debug;
            return $this->conex->Execute($insertSQL);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(EventoProposta $obj, $debug = false)
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
            return $this->conex->Execute($updateSQL);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Dados do evento que a proposta APENAS EXIBE (nao duplica): identificacao,
     * coordenacao e o que ja esta cadastrado em outras telas. Retorna array.
     */
    public function getResumoEvento($codEvento, $debug = false)
    {
        $resumo = array();
        try {
            $sql = sprintf('SELECT titulo, sigla, finalidade, categoria, atribuicao, '
                . 'coordenadores, nome_contato, email_contato, fone_organizador, '
                . 'data_ini, data_fim, periodo, desc_local_realizacao, cidade_uf, '
                . 'duracao, gestor, status_projeto, status_processo, visibilidade '
                . 'FROM tab_eventos WHERE cod_evento = %d', (int) $codEvento);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs and !$rs->EOF) {
                $resumo = $rs->fields;
            }
        } catch (ADODB_Exception $e) {
            return $resumo;
        }

        // Modalidade (presencial/distancia/hibrido) vive na configuracao do evento.
        try {
            $rs = $this->conex->Execute(sprintf(
                'SELECT tipo_projeto FROM tab_eventos_configuracao WHERE cod_evento = %d', (int) $codEvento));
            $resumo['tipo_projeto'] = ($rs and !$rs->EOF) ? $rs->fields['tipo_projeto'] : '';
        } catch (ADODB_Exception $e) {
            $resumo['tipo_projeto'] = '';
        }

        // Contagens do que a proposta puxa de outras telas — evita redigitar e
        // mostra ao coordenador se aquela parte ja esta preenchida.
        $resumo['qtd_atividades'] = $this->contar('tab_opcoes_oficinas', $codEvento);
        $resumo['qtd_orcamento']  = $this->contar('tab_opcoes_previsao_orcamentaria', $codEvento);
        return $resumo;
    }

    /**
     * Colunas de tab_eventos que a tela da Proposta pode alterar. Whitelist
     * fechada: qualquer outra coluna vinda do navegador e ignorada.
     *
     * `gestor` fica de fora de proposito — e administrativo, define permissoes
     * e o financeiro do evento; muda so pelo cadastro do evento.
     */
    public static function getColunasEventoEditaveis()
    {
        return array(
            'titulo', 'sigla', 'finalidade', 'categoria', 'coordenadores',
            'nome_contato', 'email_contato', 'fone_organizador',
            'desc_local_realizacao', 'cidade_uf', 'periodo', 'duracao',
        );
    }

    /**
     * Grava em tab_eventos os campos de identificacao editados na Proposta.
     * Recebe array coluna => valor; aplica so o que estiver na whitelist.
     */
    public function atualizarDadosEvento($codEvento, array $dados, $debug = false)
    {
        $permitidas = self::getColunasEventoEditaveis();
        $sets = array();
        foreach ($dados as $coluna => $valor) {
            if (!in_array($coluna, $permitidas, true)) {
                continue;
            }
            $sets[] = sprintf("%s = %s", $coluna,
                ($valor === null || $valor === '') ? 'NULL' : "'" . addslashesopctec($valor) . "'");
        }
        if (empty($sets)) {
            return true;
        }
        try {
            $sql = sprintf('UPDATE tab_eventos SET %s WHERE cod_evento = %d',
                implode(', ', $sets), (int) $codEvento);
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql) !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    private function contar($tabela, $codEvento)
    {
        try {
            $rs = $this->conex->Execute(sprintf(
                'SELECT COUNT(*) AS qtd FROM %s WHERE cod_evento = %d', $tabela, (int) $codEvento));
            return ($rs and !$rs->EOF) ? (int) $rs->fields['qtd'] : 0;
        } catch (ADODB_Exception $e) {
            return 0;
        }
    }

    /**
     * Configuracao do formulario de pre-avaliacao do gestor do evento.
     * O gestor vem de tab_eventos.gestor (nome), casado com sis_gestor.nome_gestor.
     * Retorna array {usa, campos_json, nome_gestor} ou false.
     */
    public function getPreAvaliacaoDoGestor($codEvento, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT g.nome_gestor, g.proposta_pre_avaliacao, g.proposta_pre_avaliacao_campos '
                . 'FROM tab_eventos e '
                . 'INNER JOIN sis_gestor g ON g.nome_gestor = e.gestor '
                . 'WHERE e.cod_evento = %d LIMIT 1', (int) $codEvento);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            return array(
                'nome_gestor' => $rs->fields['nome_gestor'],
                'usa'         => (int) $rs->fields['proposta_pre_avaliacao'] === 1,
                'campos_json' => $rs->fields['proposta_pre_avaliacao_campos'],
            );
        } catch (ADODB_Exception $e) {
            // Ambiente sem as colunas novas nao pode derrubar a tela.
            return false;
        }
    }
}
