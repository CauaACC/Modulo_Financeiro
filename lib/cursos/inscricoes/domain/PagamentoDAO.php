<?php
class PagamentoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Pagamento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_pagamentos WHERE cod_evento=\'%s\' and cod_pagamento= \'%s\'', $obj->getCodEvento(),$obj->getCodPagamento());

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
    public function getByTransacaoId(Pagamento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_pagamentos WHERE cod_evento=\'%s\' and transacao_id= \'%s\'', $obj->getCodEvento(),$obj->getTransacaoId());

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
    public function getPagamentoByPedidoId(Pagamento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_pagamentos WHERE cod_evento=\'%s\' 
                and pedido_id= \'%s\'', $obj->getCodEvento(),$obj->getPedidoId());

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
    public function getPagamentoByUltimo(Pagamento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_pagamentos WHERE cod_evento=\'%s\' 
                and cod_inscrito= \'%s\' ORDER BY cod_pagamento DESC LIMIT 1', $obj->getCodEvento(),$obj->getCodInscrito());

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
    public function getPagamentoByNumeroDocumento(Pagamento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_pagamentos WHERE cod_evento=\'%s\' 
                and numero_documento= \'%s\'', $obj->getCodEvento(),$obj->getNumeroDocumento());

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
    public function getUltimoPagamentoByIdInscrito(Pagamento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_pagamentos where
                cod_pagamento = (SELECT max(cod_pagamento) FROM tab_pagamentos WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\')'
                , $obj->getCodEvento(),$obj->getCodInscrito());

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
            $sql = "SELECT ifnull(MAX(cod_pagamento),0)+1 FROM tab_pagamentos";

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
    public function getByIdExiste(Pagamento $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_evento FROM tab_pagamentos WHERE cod_evento=\'%s\' 
                and numero_documento= \'%s\'', $obj->getCodEvento(),$obj->getNumeroDocumento());

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
    public function getAll(Pagamento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_pagamentos order by cod_evento desc');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEventos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaPagamentosByIdEvento(Pagamento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_pagamentos '
                    . 'WHERE cod_evento=\'%s\' '
                    . 'order by cod_pagamento',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPagamentos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaPagamentos10UltimosByIdEvento(Pagamento $obj, $debug=false) {
        try{
            // $sql = sprintf('SELECT * FROM tab_pagamentos '
            //         . 'WHERE cod_evento=\'%s\' '
            //         . 'order by cod_pagamento desc limit 10',$obj->getCodEvento());

            $sql = sprintf("SELECT p.cod_evento, p.cod_inscrito, i.nome_inscrito, p.valor, p.data_pagamento, p.data_inclusao, p.forma_pagamento
            FROM tab_pagamentos p INNER JOIN tab_inscritos i ON i.cod_inscrito=p.cod_inscrito
            WHERE p.status='ATIVADA' AND p.cod_evento=%d ORDER BY p.data_pagamento DESC LIMIT 10",$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPagamentos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaPagamentosByCodInscrito(Pagamento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_pagamentos '
                    . 'WHERE cod_evento=\'%s\' and cod_inscrito = \'%s\' '
                    . 'order by cod_pagamento',$obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPagamentos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Pagamento $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setRespInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionIdInclusao($_COOKIE["PHPSESSID"]);
            $obj->setVersao(1);
                        
            $sql = "SELECT * FROM tab_pagamentos WHERE cod_evento=".$obj->getCodEvento()." and cod_pagamento=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_pagamentos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = $insertSQL;

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodPagamento($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Pagamento $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setRespAlteracao($_SESSION['_cod_usuario']);
            $obj->setIpClienteAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionIdAlteracao($_COOKIE["PHPSESSID"]);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_pagamentos WHERE cod_evento = ".$obj->getCodEvento()." and cod_pagamento = ".$obj->getCodPagamento(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_pagamentos");
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
    public function delete(Pagamento $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_pagamentos WHERE cod_evento=\'%s\' and cod_pagamento= \'%s\'', $obj->getCodEvento(),$obj->getCodPagamento());

            $this->conex->debug = $debug;

            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    /**
     * Reinsere em tab_pagamentos o snapshot mais recente preservado em
     * tab_pagamentos_atualizados (mesma logica de dedup de listarExcluidos()).
     * Ao contrario de pedidos_atualizados, esta tabela espelho nao tem coluna
     * de tipo de acao/motivo -- so o dedup por data mesmo. Nao restaura se o
     * cod_pagamento ja existir em tab_pagamentos.
     */
    public function restaurar(Pagamento $obj, $debug=false) {
        try {
            $codEvento    = (int)$obj->getCodEvento();
            $codPagamento = (int)$obj->getCodPagamento();

            if($codEvento<=0 || $codPagamento<=0){
                return false;
            }

            $sqlCheck = "SELECT cod_pagamento FROM tab_pagamentos WHERE cod_pagamento=$codPagamento AND cod_evento=$codEvento LIMIT 1";
            $rsCheck  = $this->conex->Execute($sqlCheck);
            if($rsCheck && !$rsCheck->EOF){
                return false; // ja existe, nao restaura
            }

            $arrayColunas = $this->conex->MetaColumnNames('tab_pagamentos');
            if(empty($arrayColunas)){
                return false;
            }
            $listaCol = '`'.implode('`,`', array_values($arrayColunas)).'`';

            $sql = "INSERT INTO tab_pagamentos ($listaCol)
                    SELECT $listaCol FROM tab_pagamentos_atualizados
                    WHERE cod_pagamento=$codPagamento AND cod_evento=$codEvento
                    ORDER BY COALESCE(data_alteracao, data_inclusao, '') DESC
                    LIMIT 1";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if(!$rs){
                return false;
            }
            return (int)$this->conex->Affected_Rows() > 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Resumo dos pagamentos "vivos" do inscrito no evento (ignora CANCELADO/
     * DEVOLVIDO/ESTORNADO/EXCLUÍDA/DESATIVA). Usado pelo aviso ao financeiro
     * quando uma inscricao paga e cancelada.
     * @return array ['total'=>int, 'valor'=>float, 'formas'=>string]
     */
    public function resumoPagamentosAtivos($codEvento, $codInscrito, $debug=false) {
        $vazio = array('total' => 0, 'valor' => 0.0, 'formas' => '');
        try {
            $sql = sprintf("SELECT COUNT(*) as total, IFNULL(SUM(valor),0) as valor"
                    . ", IFNULL(GROUP_CONCAT(DISTINCT forma_pagamento SEPARATOR ', '),'') as formas "
                    . " FROM tab_pagamentos "
                    . " WHERE cod_evento = %d AND cod_inscrito = %d "
                    . " AND (status IS NULL OR TRIM(status) NOT IN ('CANCELADO','DEVOLVIDO','ESTORNADO','EXCLUÍDA','DESATIVA'))",
                    intval($codEvento), intval($codInscrito));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return $vazio;
            }
            return array(
                'total'  => intval($rs->fields['total']),
                'valor'  => floatval($rs->fields['valor']),
                'formas' => (string)$rs->fields['formas'],
            );
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return $vazio;
        }
    }

    /**
     * Snapshot do pagamento em tab_pagamentos_atualizados (espelho unico de
     * auditoria — mesmo INSERT..SELECT dos -grava de alterar/excluir).
     * @return int linhas arquivadas (0 = pagamento nao existe no evento)
     */
    public function arquivarParaAtualizados($codPagamento, $codEvento, $debug=false) {
        try {
            $sql = sprintf("INSERT INTO tab_pagamentos_atualizados "
                    . " SELECT * FROM tab_pagamentos "
                    . " WHERE cod_pagamento = %d AND cod_evento = %d",
                    intval($codPagamento), intval($codEvento));

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return intval($this->conex->Affected_Rows());
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    /**
     * Troca APENAS o status do pagamento, com os campos de auditoria de
     * alteracao. Chamar arquivarParaAtualizados() ANTES.
     * @return bool
     */
    public function atualizarStatus($codPagamento, $codEvento, $novoStatus, $debug=false) {
        try {
            $sql = sprintf("UPDATE tab_pagamentos SET "
                    . " status = '%s',"
                    . " data_alteracao = '%s',"
                    . " resp_alteracao = '%s',"
                    . " session_id_alteracao = '%s',"
                    . " http_user_agent_alteracao = '%s',"
                    . " ip_cliente_alteracao = '%s' "
                    . " WHERE cod_pagamento = %d AND cod_evento = %d",
                    addslashes($novoStatus),
                    date('Y-m-d H:i:s'),
                    addslashes(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : ''),
                    addslashes(isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : ''),
                    addslashes(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
                    addslashes(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
                    intval($codPagamento), intval($codEvento));

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return intval($this->conex->Affected_Rows()) > 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaMensalidades(Pagamento $obj, $debug=false) {
        try {

            $sql = $this->gerarSqlMensalidade($obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = [];
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaMensalidades($return);
                foreach ($rows as $campo=>$value) {
                    if(is_string($campo))
                        $colunas[$i++] = $campo;
                }
                $obj->setColunasMensalidades($colunas);                
                return true;   
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    function buscarDescricoesMensalidades($cod_evento) {
        $sql = "SELECT DISTINCT referencia FROM pedidos 
                WHERE evento_id = $cod_evento 
                  AND status = 'ATIVADA' order by ordem, referencia, id";
        $rs = $this->conex->Execute($sql);
    
        // $referencias = [];
        foreach ($rs as $row) {
            // $desc = $row['descricao'];
            $desc = $row['referencia'];
            $alias = strtolower(preg_replace('/[^a-z0-9]/i', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $desc)));
            $referencias[$desc] = $alias;
        }
        return $referencias;
    }
    
    function gerarSqlMensalidade($cod_evento) {
        $selectColumns = [
            "ti.cod_inscrito",
            "ti.status",
            "ti.status_processo",
            "DATE_FORMAT(ti.dat_incl, '%d/%m/%Y') AS data_inscricao",
            "UPPER(ti.nome_inscrito) AS nome_inscrito",
        ];
        # '%Ref. Inscri%' => 'inscricao',
        // if($cod_evento==45){
        //     $referencias = [
        //         '%1°Mensalidade%' => 'marco',
        //         '%2°Mensalidade%' => 'abril',
        //         '%3º Mensalidade%' => 'maio',
        //         '%4º Mensalidade%' => 'junho',
        //         '%5º Mensalidade%' => 'julho',
        //         '%6º Mensalidade%' => 'agosto',
        //         '%7º Mensalidade%' => 'setembro',
        //         '%8º Mensalidade%' => 'outubro',
        //         '%9º Mensalidade%' => 'novembro',
        //         '%10º Mensalidade%' => 'dezembro',
        //         '%11º Mensalidade%' => 'jan25',
        //         '%12º Mensalidade%' => 'fev25',
        //         '%13º Mensalidade%' => 'marco25',
        //         '%14º Mensalidade%' => 'abril25',
        //         '%15º Mensalidade%' => 'maio25',
        //         '%Acerto%' => 'soma_pg_acerto'
        //     ];  
        // }
        if($cod_evento==58){
            $referencias = [
                '%1º Mensalidade%' => 'abril',
                '%2º Mensalidade%' => 'maio',
                '%3º Mensalidade%' => 'junho',
                '%4º Mensalidade%' => 'julho',
                '%5º Mensalidade%' => 'agosto',
                '%6º Mensalidade%' => 'setembro',
                '%7º Mensalidade%' => 'outubro',
                '%8º Mensalidade%' => 'novembro',
                '%9º Mensalidade%' => 'dezembro',
                '%10º Mensalidade%' => 'jan25',
                '%11º Mensalidade%' => 'fev25',
                '%12º Mensalidade%' => 'marco25',
                '%13º Mensalidade%' => 'abril25',
                '%Acerto%' => 'soma_pg_acerto'
            ];  
        }
        if($cod_evento>=89 or $cod_evento==45){
            $referencias = $this->buscarDescricoesMensalidades($cod_evento); 
        }
        // $referencias = $this->buscarDescricoesMensalidades($cod_evento);
        // dd($referencias);
        // $referencias['%Acerto%'] = 'soma_pg_acerto'; // adicional fixo

        foreach ($referencias as $key => $desc) {
            $alias = strtolower(str_replace(['°', '-'], ['', '_'], $desc));
            if($cod_evento>=89 or $cod_evento==45){
                $selectColumns[] = "COALESCE(REPLACE(SUM(CASE WHEN p.referencia = '$key' THEN pg.valor END), '.', ','), 'pendente') AS $alias";
            }else{
                $selectColumns[] = "COALESCE(REPLACE(SUM(CASE WHEN p.descricao LIKE '$key' THEN pg.valor END), '.', ','), 'pendente') AS $alias";
            }
        }
        // dd($selectColumns);
        
        $sql = "SELECT ".implode(", ", $selectColumns)." 
                , CONCAT('https://opctec.net.br/hutec/".$cod_evento."/inscricoes/', ti.qr_code) AS link_pagamento
                FROM tab_inscritos ti
                LEFT JOIN pedidos p ON p.inscrito_id = ti.cod_inscrito AND (p.status='ATIVADA' OR p.status='Pago via acerto') AND p.evento_id = $cod_evento
                LEFT JOIN vw_pagamentos_geral AS pg ON pg.cod_inscrito = p.inscrito_id AND pg.pedido_id = p.id AND pg.status='ATIVADA'
                WHERE ti.cod_evento = $cod_evento 
                  AND ti.status = 'ATIVADA' 
                  AND EXISTS (SELECT 1 FROM tab_pagamentos pg WHERE pg.cod_inscrito = ti.cod_inscrito)
                GROUP BY ti.cod_inscrito, ti.status, ti.status_processo, ti.dat_incl, ti.nome_inscrito
                ORDER BY ti.status desc, ti.status_processo desc, ti.nome_inscrito";
        //dd($sql);
        return $sql;
    }
    public function carregarListaAcertos(Pagamento $obj, $debug=false) {
        try {

            $sql = $this->gerarSqlAcerto($obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = [];
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAcertos($return);
                foreach ($rows as $campo=>$value) {
                    if(is_string($campo))
                        $colunas[$i++] = $campo;
                }
                $obj->setColunasAcertos($colunas);                
                return true;   
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    function buscarDescricoesAcertos($cod_evento) {
        $sql = "SELECT DISTINCT referencia FROM pedidos 
                WHERE evento_id = $cod_evento and descricao like '%acerto%'
                  AND status = 'ATIVADA' order by ordem, referencia, id";
        $rs = $this->conex->Execute($sql);
    
        // $referencias = [];
        foreach ($rs as $row) {
            // $desc = $row['descricao'];
            $desc = $row['referencia'];
            $alias = strtolower(preg_replace('/[^a-z0-9]/i', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $desc)));
            $referencias[$desc] = $alias;
        }
        return $referencias;
    }
    
    public function getByCodTransacaoPagSeguro($codigoTransacao, $debug=false) {
        try {
            $sql = sprintf("SELECT * FROM pagseguro_transacoes WHERE codigo_transacao = '%s'",
                addslashes($codigoTransacao));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                return $rows;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getPagamentoByTransacaoIdRaw($transacaoId, $debug=false) {
        try {
            $sql = sprintf("SELECT * FROM tab_pagamentos WHERE transacao_id = '%s'",
                addslashes($transacaoId));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                return $rows;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getPedidoByIdRaw($eventoId, $pedidoId, $debug=false) {
        try {
            $sql = sprintf("SELECT * FROM pedidos WHERE evento_id = '%s' AND id = '%s'",
                addslashes($eventoId), addslashes($pedidoId));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                return $rows;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getEventoByMaquininha($identificacaoMaquininha, $debug=false) {
        try {
            $sql = sprintf(
                "SELECT ec.cod_evento, e.titulo
                 FROM tab_eventos_configuracao ec
                 INNER JOIN tab_eventos e ON e.cod_evento = ec.cod_evento
                 WHERE FIND_IN_SET('%s', REPLACE(ec.pag_seguro_serial_maquina, ' ', ''))
                    OR ec.pag_seguro_serial_maquina = '%s'",
                addslashes($identificacaoMaquininha),
                addslashes($identificacaoMaquininha)
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                return $rows;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getInscritoNomeByIds($codEvento, $codInscrito, $debug=false) {
        try {
            $sql = sprintf(
                "SELECT nome_inscrito FROM tab_inscritos WHERE cod_evento = '%s' AND cod_inscrito = '%s'",
                addslashes($codEvento),
                addslashes($codInscrito)
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                return $rows;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getUrlPrefixoByEvento($codEvento, $debug=false) {
        try {
            $sql = sprintf("SELECT url_prefixo FROM tab_eventos WHERE cod_evento = '%s'",
                addslashes($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            foreach ($rs as $row) {}
            return $row['url_prefixo'];

        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function listarMaquininhasDistintas($debug=false) {
        try {
            $sql = "SELECT DISTINCT identificacao_maquininha
                    FROM pagseguro_transacoes
                    WHERE identificacao_maquininha IS NOT NULL AND identificacao_maquininha != ''
                    ORDER BY identificacao_maquininha";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return [];
            }

            $lista = [];
            foreach ($rs as $row) {
                $lista[] = $row['identificacao_maquininha'];
            }
            return $lista;

        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return [];
        }
    }

    /**
     * Status que realmente existem em pagseguro_transacoes.
     * O filtro da tela usava uma lista fixa que nao batia com os dados: oferecia
     * "Paga", "Disponivel", "Em analise" e "Em disputa" (zero registros) e nao
     * tinha "Aprovada", que e' o status da maioria das transacoes.
     */
    public function listarStatusDistintos($debug=false) {
        try {
            $sql = "SELECT DISTINCT status
                    FROM pagseguro_transacoes
                    WHERE status IS NOT NULL AND status != ''
                    ORDER BY status";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return [];
            }

            $lista = [];
            foreach ($rs as $row) {
                $lista[] = $row['status'];
            }
            return $lista;

        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return [];
        }
    }

    public function buscarTransacoesPorFiltros($nome='', $email='', $cpf='', $status='', $dataIni='', $dataFim='', $maquininha='', $pagStatus='', $referencia='', $debug=false) {
        try {
            $where = [];

            if ($nome != '') {
                $nome = addslashes($nome);
                $where[] = "(pt.nome_comprador LIKE '%$nome%' OR pt.nome_cliente LIKE '%$nome%')";
            }

            if ($email != '') {
                $email = addslashes($email);
                $where[] = "(pt.email_comprador LIKE '%$email%' OR pt.email_cliente LIKE '%$email%')";
            }

            if ($cpf != '') {
                $cpf = preg_replace('/[^0-9]/', '', $cpf);
                $cpf = addslashes($cpf);
                $where[] = "EXISTS (
                    SELECT 1 FROM tab_pagamentos tp
                    INNER JOIN tab_inscritos ti ON ti.cod_inscrito = tp.cod_inscrito AND ti.cod_evento = tp.cod_evento
                    WHERE tp.transacao_id = pt.codigo_transacao
                      AND REPLACE(REPLACE(REPLACE(ti.cpf, '.', ''), '-', ''), ' ', '') = '$cpf'
                )";
            }

            if ($status != '') {
                $status = addslashes($status);
                $where[] = "pt.status = '$status'";
            }

            if ($dataIni != '' && $dataFim != '') {
                $dataIni = addslashes($dataIni);
                $dataFim = addslashes($dataFim);
                $where[] = "pt.data_transacao BETWEEN '$dataIni 00:00:00' AND '$dataFim 23:59:59'";
            }

            if ($maquininha != '') {
                $maquininha = addslashes($maquininha);
                $where[] = "pt.identificacao_maquininha = '$maquininha'";
            }

            if ($referencia != '') {
                $referencia = addslashes($referencia);
                $where[] = "pt.codigo_referencia LIKE '%$referencia%'";
            }

            if ($pagStatus != '') {
                if ($pagStatus === 'SEM_VINCULO') {
                    $where[] = "tp2.cod_inscrito IS NULL";
                } else {
                    $pagStatus = addslashes($pagStatus);
                    $where[] = "tp2.status = '$pagStatus'";
                }
            }

            if (empty($where)) {
                return false;
            }

            $sql = "SELECT pt.codigo_transacao AS cod_transacao,
                           DATE_FORMAT(pt.data_transacao, '%d/%m/%Y %H:%i') AS data_transacao,
                           pt.data_transacao AS data_transacao_ord,
                           DATE_FORMAT(pt.data_prevista_liberacao, '%d/%m/%Y') AS data_prevista_liberacao,
                           pt.data_prevista_liberacao AS data_prevista_liberacao_ord,
                           DATE_FORMAT(tp2.data_pagamento, '%d/%m/%Y') AS data_pagamento,
                           tp2.data_pagamento AS data_pagamento_ord,
                           DATE_FORMAT(pd.dat_vencimento, '%d/%m/%Y') AS data_vencimento,
                           pd.dat_vencimento AS data_vencimento_ord,
                           pt.nome_comprador, pt.nome_cliente,
                           pt.email_comprador, pt.email_cliente,
                           REPLACE(pt.valor_bruto, '.', ',') AS valor_bruto,
                           pt.valor_bruto AS valor_bruto_num,
                           tp2.valor AS pag_valor,
                           tp2.forma_pagamento AS pag_forma_pagamento,
                           pt.status, pt.forma_pagamento,
                           COALESCE(ti2.nome_inscrito, '') AS nome_inscrito,
                           tp2.cod_evento AS pag_cod_evento,
                           tp2.cod_inscrito AS pag_cod_inscrito,
                           tp2.status AS pag_status,
                           ti2.status AS inscrito_status,
                           COALESCE(ev.url_prefixo, '') AS url_prefixo
                    FROM pagseguro_transacoes pt
                    LEFT JOIN tab_pagamentos tp2 ON tp2.transacao_id = pt.codigo_transacao
                    LEFT JOIN tab_inscritos ti2 ON ti2.cod_inscrito = tp2.cod_inscrito AND ti2.cod_evento = tp2.cod_evento
                    LEFT JOIN tab_eventos ev ON ev.cod_evento = tp2.cod_evento
                    LEFT JOIN pedidos pd ON pd.id = tp2.pedido_id AND pd.evento_id = tp2.cod_evento
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY pt.data_transacao DESC
                    LIMIT 200";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }

            $registros = [];
            foreach ($rs as $row) {
                $registros[] = $row;
            }
            return $registros;

        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    function gerarSqlAcerto($cod_evento) {
        $selectColumns = [
            "ti.cod_inscrito",
            "ti.status",
            "ti.status_processo",
            "DATE_FORMAT(ti.dat_incl, '%d/%m/%Y') AS data_inscricao",
            "UPPER(ti.nome_inscrito) AS nome_inscrito",
        ];
        # '%Ref. Inscri%' => 'inscricao',

        $referencias = $this->buscarDescricoesAcertos($cod_evento);
        // dd($referencias);
        $referencias['%Acerto%'] = 'soma_pg_acerto'; // adicional fixo

        foreach ($referencias as $key => $desc) {
            $alias = strtolower(str_replace(['°', '-'], ['', '_'], $desc));
            if($cod_evento==45){
                $selectColumns[] = "COALESCE(REPLACE(SUM(CASE WHEN p.referencia = '$key' THEN pg.valor END), '.', ','), 'pendente') AS $alias";
            }else{
                $selectColumns[] = "COALESCE(REPLACE(SUM(CASE WHEN p.descricao LIKE '$key' THEN pg.valor END), '.', ','), 'pendente') AS $alias";
            }
        }
        // dd($selectColumns);
        
        $sql = "SELECT ".implode(", ", $selectColumns)." 
                FROM tab_inscritos ti
                LEFT JOIN pedidos p ON p.inscrito_id = ti.cod_inscrito AND p.status='ATIVADA' AND p.evento_id = $cod_evento
                LEFT JOIN tab_pagamentos pg ON pg.cod_inscrito = p.inscrito_id AND pg.pedido_id = p.id AND pg.status='ATIVADA'
                WHERE ti.cod_evento = $cod_evento 
                  AND ti.status = 'ATIVADA' 
                  AND EXISTS (SELECT 1 FROM tab_pagamentos pg WHERE pg.cod_inscrito = ti.cod_inscrito)
                GROUP BY ti.cod_inscrito, ti.status, ti.status_processo, ti.dat_incl, ti.nome_inscrito
                ORDER BY ti.status desc, ti.status_processo desc, ti.nome_inscrito";
        // dd($sql);
        return $sql;
    }

    /**
     * Conta quantos pagamentos excluidos (distintos) existem para os filtros dados.
     * Usado para so exibir o botao "Listar excluidos" quando ha algo a listar.
     */
    public function contarExcluidos($codEvento=null, $codInscrito=null, $debug=false) {
        try {
            $where = " WHERE tpg.cod_pagamento IS NULL ";
            if($codEvento!==null && $codEvento!==''){
                $where .= " AND pa.cod_evento=".(int)$codEvento;
            }
            if($codInscrito!==null && $codInscrito!==''){
                $where .= " AND pa.cod_inscrito=".(int)$codInscrito;
            }
            $sql = "
                SELECT COUNT(DISTINCT pa.cod_pagamento) AS total
                FROM tab_pagamentos_atualizados pa
                LEFT JOIN tab_pagamentos tpg ON tpg.cod_pagamento = pa.cod_pagamento AND tpg.cod_evento = pa.cod_evento
                $where
            ";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if((!$rs) || $rs->EOF){
                return 0;
            }
            return (int)$rs->fields['total'];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    /**
     * Lista pagamentos excluidos: registros preservados em tab_pagamentos_atualizados
     * cujo (cod_pagamento, cod_evento) nao existe mais em tab_pagamentos.
     * Como a alteracao tambem gera snapshot, mantem apenas o snapshot mais recente
     * por (cod_pagamento, cod_evento). Somente consulta (view-only).
     */
    public function listarExcluidos($codEvento=null, $codInscrito=null, $debug=false) {
        try {
            $where = " WHERE tpg.cod_pagamento IS NULL ";
            if($codEvento!==null && $codEvento!==''){
                $where .= " AND pa.cod_evento=".(int)$codEvento;
            }
            if($codInscrito!==null && $codInscrito!==''){
                $where .= " AND pa.cod_inscrito=".(int)$codInscrito;
            }

            $sql = "
                SELECT pa.cod_evento, pa.cod_pagamento, pa.cod_inscrito, pa.valor, pa.forma_pagamento,
                       pa.campo_7 AS numero_boleto, pa.data_inclusao, pa.data_alteracao, pa.observacao,
                       pa.data_pagamento, pa.numero_empenho, pa.data_emissao_recibo, pa.status,
                       pa.pedido_id, pa.transacao_id,
                       i.nome_inscrito AS nome_inscrito, i.cpf AS cpf_inscrito,
                       e.titulo AS titulo_evento,
                       ped.descricao AS pedido_titulo
                FROM tab_pagamentos_atualizados pa
                LEFT JOIN tab_pagamentos tpg ON tpg.cod_pagamento = pa.cod_pagamento AND tpg.cod_evento = pa.cod_evento
                LEFT JOIN tab_inscritos i    ON i.cod_inscrito = pa.cod_inscrito
                LEFT JOIN tab_eventos e       ON e.cod_evento = pa.cod_evento
                LEFT JOIN pedidos ped         ON ped.id = pa.pedido_id
                $where
                  AND NOT EXISTS (
                      SELECT 1 FROM tab_pagamentos_atualizados pa2
                      WHERE pa2.cod_pagamento = pa.cod_pagamento
                        AND pa2.cod_evento = pa.cod_evento
                        AND COALESCE(pa2.data_alteracao, pa2.data_inclusao, '') > COALESCE(pa.data_alteracao, pa.data_inclusao, '')
                  )
                ORDER BY COALESCE(pa.data_alteracao, pa.data_inclusao, '') DESC, pa.cod_pagamento DESC
            ";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs) || $rs->EOF){
                return array();
            }
            $return = array();
            foreach($rs as $row){
                $return[] = $row;
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }
}
