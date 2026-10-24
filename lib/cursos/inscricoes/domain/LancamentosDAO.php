<?php
class LancamentosDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Lancamentos $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT flc.* FROM fin_lancamentos flc
            WHERE flc.cod_evento=\'%s\' and flc.cod_lancamentos= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodLancamentos());

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
    public function getLastId($condicao=false, $debug=false) {
        try {
            $sql = "SELECT ifnull(MAX(cod_lancamentos),0)+1 FROM fin_lancamentos";

            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {

                }
                return $rows['0'];
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getByIdExiste(Lancamentos $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT flc.* FROM fin_lancamentos flc 
            WHERE flc.cod_evento=\'%s\' and flc.cod_lancamentos= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodLancamentos());

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
    public function getAll(Lancamentos $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT flc.* FROM fin_lancamentos flc
            WHERE flc.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaLancamentos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaLancamentosByIdEvento(Lancamentos $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT flc.* FROM fin_lancamentos flc
            WHERE flc.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaLancamentos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Lancamentos $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM fin_lancamentos 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_lancamentos=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("fin_lancamentos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setcodLancamentos($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Lancamentos $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM fin_lancamentos WHERE cod_evento = ".$obj->getCodEvento()." and cod_lancamentos = ".$obj->getCodLancamentos(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("fin_lancamentos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function existeLancamentoByCodPagamento(Lancamentos $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM fin_lancamentos
            WHERE cod_evento=\'%s\' AND cod_pagamento=\'%s\' LIMIT 1',
            $obj->getCodEvento(), $obj->getCodPagamento());

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
    public function delete(Lancamentos $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM fin_lancamentos WHERE cod_evento=\'%s\' and cod_lancamentos= \'%s\'', $obj->getCodEvento(),$obj->getCodLancamentos());

            $this->conex->debug = $debug;

            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function deleteByCodPagamento(Lancamentos $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM fin_lancamentos WHERE cod_evento=\'%s\' AND cod_pagamento=\'%s\'',
            $obj->getCodEvento(), $obj->getCodPagamento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Listagem da tela de Lancamentos com filtros.
     *
     * Devolve as linhas cruas (array associativo) em vez de VOs porque a tela
     * mostra dados que nao existem em fin_lancamentos: o nome da
     * conta (fin_plano_contas) e o nome do favorecido (tab_inscritos).
     *
     * Filtros aceitos (todos opcionais): dataInicio, dataFim (sobre a data de
     * competencia), codPlanoContas, tipoLancamento, origemLancamento,
     * conciliado ('SIM'/'NAO') e busca livre (historico, documento, favorecido).
     */
    public function carregarListaFiltrada(Lancamentos $obj, $filtros = array(), $debug = false) {
        try {
            // NOTA: fin_lancamentos nao possui as colunas num_documento,
            // arquivo_comprovante, cod_inscrito nem favorecido (essas sao gravadas
            // apenas em fin_lancamentos_excluidos). O SELECT devolve NULL
            // para elas para nao quebrar o front-end, e os filtros que dependiam
            // dessas colunas foram removidos.
            $where = '';

            if (!empty($filtros['dataInicio'])) {
                $where .= ' AND flc.data_competencia >= ' . $this->conex->qstr($filtros['dataInicio']);
            }
            if (!empty($filtros['dataFim'])) {
                $where .= ' AND flc.data_competencia <= ' . $this->conex->qstr($filtros['dataFim']);
            }
            if (!empty($filtros['codPlanoContas'])) {
                $where .= sprintf(' AND flc.cod_plano_contas = %d', intval($filtros['codPlanoContas']));
            }
            if (!empty($filtros['tipoLancamento'])) {
                $where .= ' AND flc.tipo_lancamento = ' . $this->conex->qstr($filtros['tipoLancamento']);
            }
            if (!empty($filtros['origemLancamento'])) {
                $where .= ' AND flc.origem_lancamento = ' . $this->conex->qstr($filtros['origemLancamento']);
            }
            // conciliado gravado ora como 1, ora como 'SIM' (tela x geracao de lancamentos)
            if (!empty($filtros['conciliado'])) {
                if ($filtros['conciliado'] == 'SIM') {
                    $where .= " AND flc.conciliado IN ('1','SIM')";
                } else {
                    $where .= " AND (flc.conciliado IS NULL OR flc.conciliado NOT IN ('1','SIM'))";
                }
            }
            // filtros diretos -- usados pelos links da aba Duplicidades
            if (!empty($filtros['filtroPagamento'])) {
                $where .= sprintf(' AND flc.cod_pagamento = %d', intval($filtros['filtroPagamento']));
            }
            // pedido nao existe no lancamento: chega via tab_pagamentos
            if (!empty($filtros['filtroPedido'])) {
                $where .= sprintf(' AND EXISTS (SELECT 1 FROM tab_pagamentos pp
                                                 WHERE pp.cod_evento = flc.cod_evento
                                                   AND pp.cod_pagamento = flc.cod_pagamento
                                                   AND pp.pedido_id = %d)', intval($filtros['filtroPedido']));
            }
            if (!empty($filtros['busca'])) {
                $b = $this->conex->qstr('%' . $filtros['busca'] . '%');
                $cond = sprintf('flc.historico LIKE %s', $b);
                // termo so com digitos tambem procura por codigo de pagamento/pedido
                if (ctype_digit(trim($filtros['busca']))) {
                    $n = intval($filtros['busca']);
                    $cond .= sprintf(' OR flc.cod_pagamento = %d
                                       OR EXISTS (SELECT 1 FROM tab_pagamentos pp
                                                   WHERE pp.cod_evento = flc.cod_evento
                                                     AND pp.cod_pagamento = flc.cod_pagamento
                                                     AND pp.pedido_id = %d)', $n, $n);
                }
                $where .= ' AND (' . $cond . ')';
            }

            $sql = sprintf('SELECT flc.cod_lancamentos, flc.data_competencia, flc.data_financeira,
                                   flc.tipo_lancamento, flc.valor, flc.flg_lancamento, flc.origem_lancamento,
                                   flc.historico, flc.conciliado,
                                   NULL AS num_documento, NULL AS arquivo_comprovante, NULL AS favorecido,
                                   fpc.codigo_financeiro, fpc.nome_plano_contas
                              FROM fin_lancamentos flc
                              LEFT JOIN fin_plano_contas fpc ON fpc.cod_plano_contas = flc.cod_plano_contas
                                                            AND fpc.cod_evento = flc.cod_evento
                             WHERE flc.cod_evento = %d %s
                             ORDER BY flc.data_competencia DESC, flc.cod_lancamentos DESC',
                           $obj->getCodEvento(), $where);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $lista[] = $rows;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }
}
