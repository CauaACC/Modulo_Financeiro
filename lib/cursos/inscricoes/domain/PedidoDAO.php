<?php
class PedidoDAO extends DAO{

    public function __construct() { 
        $this->tableName = "pedidos";
        parent::__construct();
    }  
    public function getById(Pedido $obj, $debug=false) {
        try{
            
            if($obj->getPedidoId()!=''){
                $obj->setId($obj->getPedidoId());
            }
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE evento_id=\'%s\' '
            . 'and id = \'%s\' ', $obj->getEventoId(),$obj->getId());

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
    public function getByInscritoId(Pedido $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE evento_id=\'%s\' '
            . ' and  inscrito_id= \'%s\'', $obj->getEventoId(),$obj->getInscritoId());

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
    public function getByIdExiste(Pedido $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT id FROM '.$this->tableName.' '
            . 'WHERE evento_id=\'%s\' and id= \'%s\' '
            , $obj->getEventoId(),$obj->getId());

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
    /**
     * Existe pedido de mensalidade (nao desativado) para o inscrito no evento?
     * Trava da geracao automatica pos-inscricao: em erro retorna true (na duvida
     * NAO gera — as parcelas podem ser geradas depois pela Acao em Lote).
     */
    public function existePedidoMensalidade($codEvento, $codInscrito, $debug=false) {
        try {
            $sql = 'SELECT id FROM '.$this->tableName.' '
            . ' WHERE evento_id='.intval($codEvento).' and inscrito_id='.intval($codInscrito)
            . ' and tipo=\'mensalidade\' and status<>\'DESATIVADA\' limit 1 ';

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return true;
        }
    }
    public function carregarListaPedidosByInscritoId(Pedido $obj, $debug=false) {
        try{
            // Desempate por p.id pra garantir ordem estavel quando varios pedidos
            // tem a mesma data de vencimento (ex: parcelas com mesmo dia).
            $orderBy = ' order by p.dat_vencimento ASC, p.id ASC ';
            if($obj->getCodEvento()=='45'){
                $orderBy = ' order by p.referencia ASC, p.id ASC ';
            }
            $sql = sprintf('SELECT p.*, tp.cod_pagamento, tp.valor valor_pagamento, tp.status as status_pagamento, tp.data_pagamento, tp.forma_pagamento, tp.transacao_id FROM pedidos p '
            . ' left join tab_pagamentos tp on tp.cod_evento=p.evento_id and tp.cod_inscrito=p.inscrito_id and tp.pedido_id=p.id and tp.status=\'ATIVADA\' '
            . ' WHERE p.evento_id=\'%s\' and p.inscrito_id= \'%s\' '.$orderBy.' '
            , $obj->getEventoId(),$obj->getInscritoId());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPedidos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getAll(Pedido $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE '
            . ' evento_id='.$obj->getEventoId().' order by evento_id desc');

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
    public function insert(Pedido $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            $obj->setQrCode(hash('sha256', $obj->getCodEvento().$obj->getInscritoId().$obj->getDatIncl()));

            $sql = "SELECT * FROM $this->tableName WHERE "
            . " evento_id=".$obj->getEventoId()." and id=0 "; 
            
            $rs = $this->conex->Execute($sql);     
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setId($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Pedido $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            if($obj->getQrCode()==''){
                $obj->setQrCode(hash('sha256', $obj->getCodEvento().$obj->getInscritoId().$obj->getDatIncl()));
            }    

            $sql = "SELECT * FROM ".$this->tableName." WHERE evento_id = ".$obj->getEventoId().""
            . " and inscrito_id = ".$obj->getInscritoId() ; 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
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
    public function delete(Pedido $obj, $debug=false) {
        try {

            $sql = sprintf('DELETE FROM '.$this->tableName.' WHERE '
            . 'evento_id=\'%s\' and inscrito_id= \'%s\' and id= \'%s\''
            . ' ',$obj->getEventoId(),$obj->getInscritoId(),$obj->getId());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function restaurar(Pedido $obj, $debug=false) {
        try {
            $codEvento  = (int)$obj->getEventoId();
            $codPedido  = (int)$obj->getId();

            if($codEvento<=0 || $codPedido<=0){
                return false;
            }

            // Verifica se o pedido ja existe (nao pode restaurar duplicado)
            $sqlCheck = "SELECT id FROM pedidos WHERE id=$codPedido AND evento_id=$codEvento LIMIT 1";
            $rsCheck  = $this->conex->Execute($sqlCheck);
            if($rsCheck && !$rsCheck->EOF){
                return false; // ja existe, nao restaura
            }

            // Lista colunas de pedidos para INSERT explicito (pedidos_atualizados tem colunas extras de auditoria)
            $arrayColunas = $this->conex->MetaColumnNames('pedidos');
            if(empty($arrayColunas)){
                return false;
            }
            $listaCol = '`'.implode('`,`', array_values($arrayColunas)).'`';

            // Pega o snapshot mais relevante: prioriza tipo_acao='EXCLUSAO', depois data_acao DESC
            $sql = "INSERT INTO pedidos ($listaCol)
                    SELECT $listaCol FROM pedidos_atualizados
                    WHERE evento_id=$codEvento AND id=$codPedido
                    ORDER BY
                        CASE WHEN tipo_acao='EXCLUSAO' THEN 0 ELSE 1 END,
                        data_acao DESC,
                        usuario_acao DESC
                    LIMIT 1";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if(!$rs){
                return false;
            }

            // Registra na auditoria (no proprio pedidos_atualizados) que houve restauracao.
            // Nao falha a operacao principal se a marcacao der problema.
            try {
                $usuario = (int)($_SESSION['_cod_usuario'] ?? 0);
                $marker  = addslashes('RESTAURADO em '.date('Y-m-d H:i:s').' por user '.$usuario);
                $sqlMark = "UPDATE pedidos_atualizados
                            SET motivo_acao = CONCAT(IFNULL(motivo_acao,''),' | $marker')
                            WHERE id=$codPedido AND evento_id=$codEvento
                              AND tipo_acao='EXCLUSAO'";
                $this->conex->Execute($sqlMark);
            } catch (Exception $e) { /* auditoria nao quebra restauracao */ }

            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * WHERE dos pedidos que podem ter o vencimento alterado em lote.
     *
     * Fica de fora: pedido desativado, pago (tem pagamento ATIVADA ou status
     * 'Pago via acerto'), sem data de vencimento e ja vencido — mexer no que
     * ja venceu reescreveria cobranca em curso.
     */
    /**
     * O inscrito tem parcelas/pedidos EM ABERTO no evento? (pedido vivo sem
     * pagamento ATIVADA — vencido ou futuro). Usado pela PoliticaCancelamento:
     * curso cobrando mensalidade nao cancela direto pelo bot.
     * @return int quantidade de parcelas em aberto
     */
    public function contarParcelasEmAberto($codEvento, $codInscrito, $debug=false) {
        try {
            $sql = "SELECT COUNT(*) as total FROM pedidos p
                     WHERE p.evento_id = ".(int)$codEvento."
                       AND p.inscrito_id = ".(int)$codInscrito."
                       AND p.status <> 'DESATIVADA'
                       AND p.status <> 'Pago via acerto'
                       AND NOT EXISTS (
                           SELECT 1 FROM tab_pagamentos tp
                            WHERE tp.pedido_id = p.id
                              AND tp.cod_evento = p.evento_id
                              AND tp.status = 'ATIVADA'
                       )";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return 0;
            }
            return intval($rs->fields['total']);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    private function whereVencimentoAlteravel($codEvento, $codInscrito) {
        $where = " WHERE p.evento_id = ".(int)$codEvento."
                     AND p.status <> 'DESATIVADA'
                     AND p.status <> 'Pago via acerto'
                     AND p.dat_vencimento IS NOT NULL
                     AND p.dat_vencimento <> '0000-00-00'
                     AND p.dat_vencimento >= CURDATE()
                     AND NOT EXISTS (
                         SELECT 1 FROM tab_pagamentos tp
                          WHERE tp.pedido_id = p.id
                            AND tp.cod_evento = p.evento_id
                            AND tp.status = 'ATIVADA'
                     ) ";
        if ($codInscrito !== null && $codInscrito !== '') {
            $where .= " AND p.inscrito_id = ".(int)$codInscrito." ";
        }
        return $where;
    }

    /**
     * Pedidos pendentes vencendo daqui a X dias (mesma regra de "pendente"
     * de whereVencimentoAlteravel: nao desativado, nao pago via acerto, sem
     * pagamento ATIVADA vinculado), filtrados pelos eventos com
     * aviso_vencimento_habilitado=1 e excluindo pedidos que ja receberam o
     * aviso para esse N de dias (tab_pedidos_aviso_vencimento_enviado) —
     * usado pela tarefa de cron AvisoVencimentoProximoTarefa.
     * @return array de linhas (id, evento_id, inscrito_id, dat_vencimento, valor_total, referencia, descricao)
     */
    public function buscarPedidosVencendoEm($dias, $codEvento = null, $debug=false) {
        try {
            $dias = (int)$dias;
            $sql = "SELECT p.id, p.evento_id, p.inscrito_id, p.dat_vencimento,
                           p.valor_total, p.referencia, p.descricao
                      FROM pedidos p
                      INNER JOIN tab_eventos_configuracao ec ON ec.cod_evento = p.evento_id
                     WHERE ec.aviso_vencimento_habilitado = 1
                       AND p.status <> 'DESATIVADA'
                       AND p.status <> 'Pago via acerto'
                       AND p.dat_vencimento IS NOT NULL
                       AND p.dat_vencimento <> '0000-00-00'
                       AND p.dat_vencimento = DATE_ADD(CURDATE(), INTERVAL ".$dias." DAY)
                       AND NOT EXISTS (
                           SELECT 1 FROM tab_pagamentos tp
                            WHERE tp.pedido_id = p.id
                              AND tp.cod_evento = p.evento_id
                              AND tp.status = 'ATIVADA'
                       )
                       AND NOT EXISTS (
                           SELECT 1 FROM tab_pedidos_aviso_vencimento_enviado av
                            WHERE av.cod_pedido = p.id
                              AND av.dias_antecedencia = ".$dias."
                       )";
            if ($codEvento !== null) {
                $sql .= " AND p.evento_id = ".(int)$codEvento;
            }
            $sql .= " ORDER BY p.evento_id, p.id";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) || $rs->EOF) {
                return array();
            }
            $return = array();
            foreach ($rs as $row) {
                $return[] = $row;
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /** Registra que o aviso de vencimento (N dias) ja foi enviado para este pedido — evita duplicar. */
    public function registrarAvisoVencimentoEnviado($codPedido, $dias, $debug=false) {
        try {
            $sql = "INSERT INTO tab_pedidos_aviso_vencimento_enviado (cod_pedido, dias_antecedencia, dat_envio)
                     VALUES (".(int)$codPedido.", ".(int)$dias.", NOW())";
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql) ? true : false;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }

    /**
     * Expressao SQL que troca so o DIA do vencimento, preservando mes/ano.
     * Mes que nao tem o dia pedido (31 em fevereiro) recebe o ultimo dia do mes.
     */
    private function expressaoNovoDiaVencimento($novoDia) {
        $novoDia = (int)$novoDia;
        return " DATE_ADD(
                    DATE_SUB(p.dat_vencimento, INTERVAL DAY(p.dat_vencimento)-1 DAY),
                    INTERVAL LEAST($novoDia, DAY(LAST_DAY(p.dat_vencimento)))-1 DAY
                 ) ";
    }

    /**
     * Previa: o que a alteracao de dia faria, sem gravar nada.
     * Retorna array de linhas com o vencimento atual e o que ficaria.
     */
    public function listarPrevisaoNovoDiaVencimento($codEvento, $codInscrito, $novoDia, $debug=false) {
        try {
            $sql = "SELECT p.id, p.inscrito_id, p.referencia, p.descricao, p.valor_total,
                           p.dat_vencimento AS vencimento_atual,
                           ".$this->expressaoNovoDiaVencimento($novoDia)." AS vencimento_novo
                      FROM pedidos p
                      ".$this->whereVencimentoAlteravel($codEvento, $codInscrito)."
                     ORDER BY p.dat_vencimento ASC, p.id ASC";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) || $rs->EOF) {
                return array();
            }
            $return = array();
            foreach ($rs as $row) {
                $return[] = $row;
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Troca o dia do vencimento dos pedidos elegiveis de um inscrito.
     *
     * Grava snapshot em pedidos_atualizados antes do UPDATE, no mesmo formato da
     * alteracao individual (view/admin/inscritos-pedido-cadastro-alterar-grava.php),
     * para o pedido continuar recuperavel.
     *
     * @return int quantidade de pedidos alterados
     */
    public function alterarDiaVencimentoEmLote($codEvento, $codInscrito, $novoDia, $debug=false) {
        try {
            $novoDia = (int)$novoDia;
            if ($novoDia < 1 || $novoDia > 31) {
                return 0;
            }
            $where = $this->whereVencimentoAlteravel($codEvento, $codInscrito);

            $arrayColunas = $this->conex->MetaColumnNames('pedidos');
            if (empty($arrayColunas)) {
                return 0;
            }
            $listaCol = '`'.implode('`,`', array_values($arrayColunas)).'`';
            /* prefixa com o alias p. para o SELECT do INSERT bater com o WHERE */
            $listaColSelect = 'p.`'.implode('`,p.`', array_values($arrayColunas)).'`';

            $usuario = (int)(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0);
            $ip      = addslashes(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $motivo  = addslashes('Alteracao de vencimento em lote: dia '.$novoDia);

            $this->conex->debug = $debug;

            $sqlAuditoria = "INSERT INTO pedidos_atualizados
                             ($listaCol, tipo_acao, motivo_acao, usuario_acao, ip_cliente_acao, data_acao)
                             SELECT $listaColSelect, 'ALTERACAO', '$motivo', $usuario, '$ip', NOW()
                               FROM pedidos p
                               $where";
            if (!$this->conex->Execute($sqlAuditoria)) {
                return 0;   // sem snapshot nao altera
            }

            /* pedidos nao tem colunas de auditoria proprias (so updated_at/versao) —
               quem alterou fica registrado em pedidos_atualizados */
            $sqlUpdate = "UPDATE pedidos p
                             SET p.dat_vencimento = ".$this->expressaoNovoDiaVencimento($novoDia).",
                                 p.updated_at = NOW(),
                                 p.versao     = IFNULL(p.versao,1) + 1
                          $where";
            if (!$this->conex->Execute($sqlUpdate)) {
                return 0;
            }
            return (int)$this->conex->Affected_Rows();
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    /**
     * Conta quantos pedidos excluidos (distintos) existem para os filtros dados.
     * Usado para so exibir o botao "Listar excluidos" quando ha algo a listar.
     */
    public function contarExcluidos($codEvento=null, $codInscrito=null, $debug=false) {
        try {
            $where = " WHERE p.id IS NULL ";
            if($codEvento!==null && $codEvento!==''){
                $where .= " AND pa.evento_id=".(int)$codEvento;
            }
            if($codInscrito!==null && $codInscrito!==''){
                $where .= " AND pa.inscrito_id=".(int)$codInscrito;
            }
            $sql = "SELECT COUNT(DISTINCT pa.id) AS total
                    FROM pedidos_atualizados pa
                    LEFT JOIN pedidos p ON p.id = pa.id AND p.evento_id = pa.evento_id
                    $where";
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

    public function listarExcluidos($codEvento=null, $codInscrito=null, $debug=false) {
        try {
            $where = " WHERE p.id IS NULL ";
            if($codEvento!==null && $codEvento!==''){
                $where .= " AND pa.evento_id=".(int)$codEvento;
            }
            if($codInscrito!==null && $codInscrito!==''){
                $where .= " AND pa.inscrito_id=".(int)$codInscrito;
            }

            // Subquery pega o snapshot mais relevante por (id, evento_id):
            // prioriza tipo_acao='EXCLUSAO', depois data_acao DESC
            $sql = "
                SELECT pa.id, pa.evento_id, pa.inscrito_id, pa.descricao, pa.descricao_completa,
                       pa.valor_total, pa.status, pa.dat_vencimento, pa.referencia,
                       pa.tipo_acao, pa.motivo_acao, pa.usuario_acao, pa.ip_cliente_acao, pa.data_acao,
                       i.nome_inscrito AS nome_inscrito, i.cpf AS cpf_inscrito,
                       e.titulo AS titulo_evento,
                       u.login AS login_usuario_acao
                FROM pedidos_atualizados pa
                LEFT JOIN pedidos p          ON p.id = pa.id AND p.evento_id = pa.evento_id
                LEFT JOIN tab_inscritos i    ON i.cod_inscrito = pa.inscrito_id
                LEFT JOIN tab_eventos e      ON e.cod_evento = pa.evento_id
                LEFT JOIN sis_usuarios u     ON u.cod_usuario = pa.usuario_acao
                $where
                  AND NOT EXISTS (
                      SELECT 1 FROM pedidos_atualizados pa2
                      WHERE pa2.id=pa.id AND pa2.evento_id=pa.evento_id
                        AND (
                            (pa.tipo_acao<=>'EXCLUSAO') < (pa2.tipo_acao<=>'EXCLUSAO')
                            OR (
                                (pa.tipo_acao<=>'EXCLUSAO') = (pa2.tipo_acao<=>'EXCLUSAO')
                                AND COALESCE(pa2.data_acao,'1900-01-01') > COALESCE(pa.data_acao,'1900-01-01')
                            )
                        )
                  )
                ORDER BY pa.data_acao DESC, pa.id DESC
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
