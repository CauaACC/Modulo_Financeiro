<?php

class RetornoHutecDAO extends DAO
{

    public function __construct()
    {
        $this->tableName = "fin_carga_hutec";
        parent::__construct();
    }

    public function getById(RetornoHutec $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and cod_retorno_hutec= \'%s\' '
                , $obj->getCodEvento(), $obj->getCodRetornoHutec());

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

    public function getByIdExiste(RetornoHutec $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_retorno_hutec FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and cod_retorno_hutec= \'%s\' '
                , $obj->getCodEvento(), $obj->getCodRetornoHutec());

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
    public function getByIdExisteCarga(RetornoHutec $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_retorno_hutec FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\' '
                , $obj->getCodEvento(), $obj->getCodArquivo());

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

    public function carregarListaRetornoHutecByIdEvento(RetornoHutec $obj, $debug = false)
    {
        try {
            $sql = sprintf("SELECT ch.*, p.cod_pagamento as pagamento FROM fin_carga_hutec ch LEFT JOIN tab_pagamentos p ON p.cod_pagamento=ch.cod_pagamento WHERE ch.cod_evento='%s' and ch.tipo_operacao='C' and REPLACE(ch.valor, ',','')>0 order by ch.cod_pagamento ", $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRetornoHutec($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    /**
     * CRÍTICA 1 - Lançado DIFERENTE: mesmo cod_pagamento nos dois lados, mas valor divergente.
     */
    public function getCriticaDivergentes(RetornoHutec $obj, $debug = false)
    {
        $return = array();
        try {
            $sql = sprintf("SELECT ch.cod_pagamento, ch.nome, ch.valor AS valor_hutec, p.valor AS valor_nosso "
                . "FROM fin_carga_hutec ch "
                . "INNER JOIN tab_pagamentos p ON p.cod_evento=ch.cod_evento AND p.cod_pagamento=ch.cod_pagamento "
                . "WHERE ch.cod_evento='%s' AND ch.tipo_operacao='C' "
                . "AND ROUND(REPLACE(ch.valor,',','.')+0,2) <> ROUND(REPLACE(p.valor,',','.')+0,2) "
                . "ORDER BY ch.cod_pagamento ", $obj->getCodEvento());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) { $return[] = $rows; }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $return;
    }

    /**
     * CRÍTICA 2 - A MAIS no retorno: crédito no balancete HUTEC sem pagamento ATIVADA correspondente.
     * O join com tab_pagamentos é filtrado por status='ATIVADA', então também surgem os pagamentos
     * CANCELADO/DEVOLVIDO (que existem na tabela, mas não são válidos) — antes ficavam escondidos
     * porque o join casava por cod_pagamento ignorando o status.
     * Classificação (resolvida na view):
     *   - existe_no_sistema=1  → pagamento existe com status != ATIVADA (status_sistema traz o valor)
     *   - existe_excluido=1    → não existe mais em tab_pagamentos, mas há snapshot em atualizados
     *   - nenhum dos dois      → sem registro (verificar)
     * cod_inscrito serve para linkar à aba Pagamentos do inscrito / tela de Pagamentos Excluídos.
     */
    public function getCriticaSoNoHutec(RetornoHutec $obj, $debug = false)
    {
        $return = array();
        try {
            $sql = sprintf("SELECT ch.cod_pagamento, ch.nome, ch.valor, ch.data, "
                . "MAX(psist.status) AS status_sistema, "
                . "MAX(COALESCE(psist.cod_inscrito, pa.cod_inscrito)) AS cod_inscrito, "
                // transacao do PagSeguro (para localizar na Consulta PagSeguro e achar
                // outros pagamentos da mesma inscricao). Vem do registro que existir.
                . "MAX(COALESCE(psist.transacao_id, pa.transacao_id)) AS transacao_id, "
                . "MAX(CASE WHEN psist.cod_pagamento IS NOT NULL THEN 1 ELSE 0 END) AS existe_no_sistema, "
                . "MAX(CASE WHEN pa.cod_pagamento IS NOT NULL THEN 1 ELSE 0 END) AS existe_excluido "
                . "FROM fin_carga_hutec ch "
                . "LEFT JOIN tab_pagamentos pativa ON pativa.cod_evento=ch.cod_evento AND pativa.cod_pagamento=ch.cod_pagamento AND pativa.status='ATIVADA' "
                . "LEFT JOIN tab_pagamentos psist ON psist.cod_evento=ch.cod_evento AND psist.cod_pagamento=ch.cod_pagamento "
                . "LEFT JOIN tab_pagamentos_atualizados pa ON pa.cod_evento=ch.cod_evento AND pa.cod_pagamento=ch.cod_pagamento "
                . "WHERE ch.cod_evento='%s' AND ch.tipo_operacao='C' AND REPLACE(ch.valor,',','')+0 > 0 "
                . "AND pativa.cod_pagamento IS NULL "
                . "GROUP BY ch.cod_pagamento, ch.nome, ch.valor, ch.data "
                . "ORDER BY ch.cod_pagamento ", $obj->getCodEvento());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) { $return[] = (array) $rows; }
            }

            // Enriquece cada linha: o MESMO inscrito tem outro pagamento ATIVO no sistema?
            // (se tem, o crédito do HUTEC está coberto por outro pagamento e não é problema)
            $inscritos = array();
            foreach ($return as $r) {
                $ci = (int) (isset($r['cod_inscrito']) ? $r['cod_inscrito'] : 0);
                if ($ci > 0) { $inscritos[$ci] = true; }
            }
            $mapAtivo = array();
            if ($inscritos) {
                $listaIn = implode(',', array_map('intval', array_keys($inscritos)));
                $sqlAtivo = sprintf("SELECT cod_inscrito, COUNT(*) AS qtd, SUM(REPLACE(valor,',','.')+0) AS soma "
                    . "FROM tab_pagamentos "
                    . "WHERE cod_evento='%s' AND status='ATIVADA' AND cod_inscrito IN (%s) "
                    . "AND REPLACE(valor,',','.')+0 > 0 "
                    . "GROUP BY cod_inscrito", $obj->getCodEvento(), $listaIn);
                $rsA = $this->conex->Execute($sqlAtivo);
                if ($rsA && !$rsA->EOF) {
                    foreach ($rsA as $ra) {
                        $mapAtivo[(int) $ra['cod_inscrito']] = array(
                            'qtd'  => (int) $ra['qtd'],
                            'soma' => (float) $ra['soma'],
                        );
                    }
                }
            }
            foreach ($return as $k => $r) {
                $ci = (int) (isset($r['cod_inscrito']) ? $r['cod_inscrito'] : 0);
                $return[$k]['outro_ativo_qtd']   = isset($mapAtivo[$ci]) ? $mapAtivo[$ci]['qtd']  : 0;
                $return[$k]['outro_ativo_valor'] = isset($mapAtivo[$ci]) ? $mapAtivo[$ci]['soma'] : 0;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $return;
    }

    /**
     * CRÍTICA 3 - NÃO LANÇADO no retorno: pagamento ativo no sistema que não veio no balancete HUTEC.
     */
    public function getCriticaSoNoNosso(RetornoHutec $obj, $debug = false)
    {
        $return = array();
        try {
            $sql = sprintf("SELECT p.cod_pagamento, i.nome_inscrito AS nome, p.valor, p.forma_pagamento, "
                . "p.transacao_id, p.cod_inscrito "
                . "FROM tab_pagamentos p "
                . "LEFT JOIN tab_inscritos i ON i.cod_evento=p.cod_evento AND i.cod_inscrito=p.cod_inscrito "
                . "LEFT JOIN fin_carga_hutec ch ON ch.cod_evento=p.cod_evento AND ch.cod_pagamento=p.cod_pagamento AND ch.tipo_operacao='C' "
                . "WHERE p.cod_evento='%s' AND p.status='ATIVADA' AND REPLACE(p.valor,',','')+0 > 0 "
                . "AND ch.cod_pagamento IS NULL "
                . "ORDER BY p.cod_pagamento ", $obj->getCodEvento());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) { $return[] = $rows; }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $return;
    }

    public function insert(RetornoHutec $obj, $debug = 1)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM $this->tableName WHERE "
                . " cod_evento=" . $obj->getCodEvento() . " and cod_retorno_hutec=0 ";

            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            // NÃO usar sprintf aqui: o SQL já vem pronto do GetInsertSQL e um '%' no texto
            // (ex.: "75%" na descrição da NF) faria o sprintf exigir argumento e estourar.
            $sql = $insertSQL;

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodRetornoHutec($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(RetornoHutec $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);
            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_evento = " . $obj->getCodEvento()
                . " and cod_retorno_hutec = " . $obj->getCodRetornoHutec();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);
            // idem insert: sem sprintf, para não quebrar com '%' no texto
            $sql = $updateSQL;
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;

        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(RetornoHutec $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' AND cod_retorno_hutec= \'%s\' ', $obj->getCodEvento(), $obj->getCodRetornoHutec());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return $rs !== false;
        } catch (ADODB_Exception $e) {
            echo 'Erro: ' . $e->getMessage();
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function deleteCarga(RetornoHutec $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' AND cod_arquivo= \'%s\' ', $obj->getCodEvento(), $obj->getCodArquivo());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return $rs !== false;
        } catch (ADODB_Exception $e) {
            echo 'Erro: ' . $e->getMessage();
            $this->enviarMensagem($e);
            return false;
        }
    }
}