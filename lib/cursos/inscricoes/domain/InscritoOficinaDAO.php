<?php
class InscritoOficinaDAO extends DAO{

    public function __construct() { 
        $this->tableName = "tab_lig_inscritos_oficinas";
        parent::__construct();
    }  
    public function getById(InscritoOficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . 'and cod_inscrito= \'%s\' and cod_oficina= \'%s\'', $obj->getCodEvento(),$obj->getCodInscritoOficina());

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
    public function getByCodInscritoCodOficina(InscritoOficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . ' and cod_oficina= \'%s\' and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodOficina(),$obj->getCodInscrito());

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
    public function getByCodOficina(InscritoOficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . 'and cod_oficina= \'%s\'', $obj->getCodEvento(),$obj->getCodOficina());

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
    public function getInscritoOficinaExisteByDtHr(InscritoOficina $obj, $debug=false) {
        try {
            
            $sql = sprintf('SELECT CONCAT(o.data_ini,\'-\',o.horario_ini) AS data_hora_ini FROM tab_lig_inscritos_oficinas  lio
            INNER JOIN tab_opcoes_oficinas o ON o.cod_oficina=lio.cod_oficina AND o.cod_evento=lio.cod_evento
            WHERE lio.cod_evento='. $obj->getCodEvento().' and lio.cod_inscrito='. $obj->getCodInscrito().' 
            AND CONCAT(o.data_ini,\'-\',o.horario_ini) 
                IN (SELECT CONCAT(o.data_ini,\'-\',o.horario_ini) data_hora_ini
                FROM tab_opcoes_oficinas o 
                WHERE o.cod_evento='. $obj->getCodEvento().' AND o.cod_oficina='.$obj->getCodOficina().')');

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
    public function getByIdExiste(InscritoOficina $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito, cod_oficina FROM '.$this->tableName.' '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' AND cod_oficina= \'%s\' '
            , $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodOficina());

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
    public function carregarListaOficinasByIdInscrito(InscritoOficina $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT too.carga_horaria AS carga_horaria_oficina, tlio.carga_horaria AS carga_horaria_inscrito '
                    . ', too.*, tlio.*, top.titulo as titulo_perfil, top.carga_horaria_certificado as carga_horaria_perfil '
                    . 'FROM tab_opcoes_oficinas too '
                    . 'INNER JOIN tab_lig_inscritos_oficinas tlio on tlio.cod_oficina=too.cod_oficina '
                    . 'LEFT JOIN tab_opcoes_perfis top on tlio.cod_perfil=top.cod_perfil '
                    . 'WHERE tlio.cod_evento=\'%s\' and tlio.cod_inscrito=\'%s\' and too.status=\'ATIVADA\' '
                    . 'order by too.ordem, too.titulo',$obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaOficinas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarOficinaByIdInscritoOficina(InscritoOficina $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT too.*, tlio.*, top.titulo as titulo_perfil, top.carga_horaria_certificado as carga_horaria_perfil '
                    . 'FROM tab_opcoes_oficinas too '
                    . 'INNER JOIN tab_lig_inscritos_oficinas tlio on tlio.cod_oficina=too.cod_oficina '
                    . 'LEFT JOIN tab_opcoes_perfis top on tlio.cod_perfil=top.cod_perfil '
                    . 'WHERE tlio.cod_evento=\'%s\' and tlio.cod_ofinica=\'%s\' and tlio.cod_inscrito=\'%s\' and too.status=\'ATIVADA\' '
                    . 'order by too.ordem, too.titulo',$obj->getCodEvento(),$obj->getCodOficina(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaOficinas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaMinistrantesOficinasByIdInscrito(InscritoOficina $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_opcoes_oficinas too '
                    . 'INNER JOIN tab_lig_ministrantes_oficinas tlio on tlio.cod_oficina=too.cod_oficina '
                    . 'WHERE tlio.cod_evento=\'%s\' and tlio.cod_perfil=\'%s\' and tlio.cod_inscrito=\'%s\' and too.status=\'ATIVADA\' '
                    . 'order by too.titulo',$obj->getCodEvento(),$obj->getCodPerfil(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaMinistrantesOficinas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaOficinasLiberadasByIdEvento(InscritoOficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_oficinas WHERE '
            . ' cod_evento='.$obj->getCodEvento().' and status=\'ATIVADA\' order by cod_evento desc');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaOficinas($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function getAll(InscritoOficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE '
            . ' cod_evento='.$obj->getCodEvento().' order by cod_evento desc');

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
    public function insert(InscritoOficina $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            if ($obj->getTipoRelacao() == '') {
                $obj->setTipoRelacao('Participante');
            }   
            $obj->setTipoRelacao('Participante');
                        
            $sql = "SELECT * FROM $this->tableName WHERE "
            . " cod_evento=".$obj->getCodEvento()." and cod_inscrito=0 and cod_oficina=0"; 
            
            $rs = $this->conex->Execute($sql);     
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodInscritoOficina($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(InscritoOficina $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $whereStatus = "";
            if($obj->getStatus()=='ATIVADA'){
                $whereStatus = " and status='ATIVADA' ";
            }
            $sql = "SELECT * FROM ".$this->tableName." WHERE cod_evento = ".$obj->getCodEvento().""
            . " and cod_inscrito = ".$obj->getCodInscrito()." and cod_oficina = ".$obj->getCodOficina(). $whereStatus; 
            
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
    public function delete(InscritoOficina $obj, $debug=false) {
        try {
            $resultado = $this->excluirComAuditoria(
                $obj->getCodEvento(),
                $obj->getCodInscrito(),
                $obj->getCodOficina(),
                $obj->getTipoRelacao(),
                $obj->getMotivoExclusao(),
                $debug
            );
            return $resultado['ok'];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Colunas da tabela base. O espelho de exclusao tem estas MAIS as colunas de
     * auditoria (motivo/usuario/ip/data/id_exclusao), entao a copia nao pode
     * mais ser "INSERT ... SELECT *" -- a contagem nao bate.
     */
    private function colunasBase() {
        $colunas = $this->conex->MetaColumnNames($this->tableName);
        if(empty($colunas)){
            return array();
        }
        return array_values($colunas);
    }

    /**
     * WHERE que identifica UMA ligacao inscrito x atividade.
     *
     * O tipo_relacao faz parte da chave primaria: o mesmo inscrito aparece na
     * mesma atividade como 'Participante', 'Participante 2', 'Ministrante'...
     * Sem ele no WHERE, excluir uma participacao levava TODAS as outras junto.
     */
    private function whereLigacao($codEvento, $codInscrito, $codOficina, $tipoRelacao) {
        $where = " cod_evento=".(int)$codEvento
               . " AND cod_inscrito=".(int)$codInscrito
               . " AND cod_oficina=".(int)$codOficina;
        if($tipoRelacao===null || $tipoRelacao===''){
            $where .= " AND (tipo_relacao IS NULL OR tipo_relacao='')";
        }else{
            $where .= " AND tipo_relacao='".addslashes($tipoRelacao)."'";
        }
        return $where;
    }

    /**
     * Exclui UMA ligacao inscrito x atividade guardando quem excluiu, quando,
     * de onde e por que no espelho tab_lig_inscritos_oficinas_excluidos.
     * So apaga depois de confirmar que a copia entrou.
     *
     * Retorna array('ok'=>bool, 'msg'=>string).
     */
    public function excluirComAuditoria($codEvento, $codInscrito, $codOficina, $tipoRelacao, $motivo='', $debug=false) {
        try {
            if((int)$codEvento<=0 || (int)$codInscrito<=0 || (int)$codOficina<=0){
                return array('ok'=>false, 'msg'=>'Parâmetros inválidos para a exclusão da atividade.');
            }

            $colunas = $this->colunasBase();
            if(empty($colunas)){
                return array('ok'=>false, 'msg'=>'Não foi possível ler a estrutura da tabela de atividades.');
            }
            $listaCol = '`'.implode('`,`', $colunas).'`';
            $where    = $this->whereLigacao($codEvento, $codInscrito, $codOficina, $tipoRelacao);

            $usuario   = (int)(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0);
            $ipCliente = addslashes(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $motivoEsc = addslashes(trim((string)$motivo));

            $sql = "INSERT INTO ".$this->tableName."_excluidos ($listaCol, motivo_exclusao, usuario_exclusao, ip_cliente_exclusao, data_exclusao)
                    SELECT $listaCol, '$motivoEsc', $usuario, '$ipCliente', NOW()
                    FROM ".$this->tableName."
                    WHERE $where";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if(!$rs){
                return array('ok'=>false, 'msg'=>'Falha ao gravar a cópia de segurança da atividade. Nada foi excluído.');
            }
            if((int)$this->conex->Affected_Rows() <= 0){
                return array('ok'=>false, 'msg'=>'Atividade não encontrada para exclusão — talvez já tenha sido excluída.');
            }

            $rsDel = $this->conex->Execute("DELETE FROM ".$this->tableName." WHERE $where");
            if(!$rsDel){
                return array('ok'=>false, 'msg'=>'A cópia de segurança foi gravada, mas a exclusão falhou.');
            }

            return array('ok'=>true, 'msg'=>'Atividade excluída. O registro pode ser recuperado em "Atividades excluídas".');
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array('ok'=>false, 'msg'=>'Erro ao excluir a atividade.');
        }
    }

    /**
     * Quantas atividades excluidas ainda podem ser recuperadas (a ligacao nao
     * voltou para a tabela base). Serve para so mostrar o botao quando ha algo.
     */
    public function contarExcluidos($codEvento=null, $codInscrito=null, $debug=false) {
        try {
            $sql = "SELECT COUNT(*) AS total
                    FROM ".$this->tableName."_excluidos ex
                    LEFT JOIN ".$this->tableName." tl
                           ON tl.cod_evento   = ex.cod_evento
                          AND tl.cod_inscrito = ex.cod_inscrito
                          AND tl.cod_oficina  = ex.cod_oficina
                          AND tl.tipo_relacao <=> ex.tipo_relacao
                    WHERE tl.cod_inscrito IS NULL "
                 . $this->filtroExcluidos($codEvento, $codInscrito);

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

    private function filtroExcluidos($codEvento=null, $codInscrito=null) {
        $filtro = '';
        if($codEvento!==null && $codEvento!==''){
            $filtro .= " AND ex.cod_evento=".(int)$codEvento;
        }
        if($codInscrito!==null && $codInscrito!==''){
            $filtro .= " AND ex.cod_inscrito=".(int)$codInscrito;
        }
        return $filtro;
    }

    /**
     * Lista as atividades excluidas (espelho), marcando quais ja voltaram para a
     * base -- essas nao podem ser restauradas de novo.
     */
    public function listarExcluidos($codEvento=null, $codInscrito=null, $debug=false) {
        try {
            $sql = "SELECT ex.id_exclusao, ex.cod_evento, ex.cod_inscrito, ex.cod_oficina, ex.tipo_relacao,
                           ex.flg_presenca, ex.frequencia, ex.carga_horaria, ex.carga_horaria_pratica,
                           ex.carga_horaria_teorica, ex.cod_perfil, ex.data_inscricao, ex.status,
                           ex.motivo_exclusao, ex.usuario_exclusao, ex.ip_cliente_exclusao, ex.data_exclusao,
                           o.titulo AS titulo_oficina, o.tipo AS tipo_oficina, o.ordem AS ordem_oficina,
                           o.data_inicio AS data_inicio_oficina,
                           i.nome_inscrito AS nome_inscrito,
                           e.titulo AS titulo_evento,
                           u.login AS login_usuario_exclusao,
                           CASE WHEN tl.cod_inscrito IS NULL THEN 0 ELSE 1 END AS ja_restaurada
                    FROM ".$this->tableName."_excluidos ex
                    LEFT JOIN ".$this->tableName." tl
                           ON tl.cod_evento   = ex.cod_evento
                          AND tl.cod_inscrito = ex.cod_inscrito
                          AND tl.cod_oficina  = ex.cod_oficina
                          AND tl.tipo_relacao <=> ex.tipo_relacao
                    LEFT JOIN tab_opcoes_oficinas o ON o.cod_oficina  = ex.cod_oficina AND o.cod_evento = ex.cod_evento
                    LEFT JOIN tab_inscritos i       ON i.cod_inscrito = ex.cod_inscrito
                    LEFT JOIN tab_eventos e         ON e.cod_evento   = ex.cod_evento
                    LEFT JOIN sis_usuarios u        ON u.cod_usuario  = ex.usuario_exclusao
                    WHERE 1=1 "
                 . $this->filtroExcluidos($codEvento, $codInscrito)
                 . " ORDER BY ex.data_exclusao DESC, ex.id_exclusao DESC";

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

    /**
     * Devolve para tab_lig_inscritos_oficinas a linha guardada no espelho.
     * Restaura pelo id_exclusao (uma linha exata), nunca pelo par
     * inscrito+atividade -- que pode ter varias relacoes e varias exclusoes.
     *
     * Retorna array('ok'=>bool, 'msg'=>string).
     */
    public function restaurarExcluido($idExclusao, $debug=false) {
        try {
            $idExclusao = (int)$idExclusao;
            if($idExclusao<=0){
                return array('ok'=>false, 'msg'=>'Registro inválido para restauração.');
            }

            $rsEx = $this->conex->Execute("SELECT cod_evento, cod_inscrito, cod_oficina, tipo_relacao
                                             FROM ".$this->tableName."_excluidos
                                            WHERE id_exclusao=$idExclusao LIMIT 1");
            if((!$rsEx) || $rsEx->EOF){
                return array('ok'=>false, 'msg'=>'Registro excluído não encontrado.');
            }
            $codEvento    = (int)$rsEx->fields['cod_evento'];
            $codInscrito  = (int)$rsEx->fields['cod_inscrito'];
            $codOficina   = (int)$rsEx->fields['cod_oficina'];
            $tipoRelacao  = $rsEx->fields['tipo_relacao'];

            $where = $this->whereLigacao($codEvento, $codInscrito, $codOficina, $tipoRelacao);

            $rsExiste = $this->conex->Execute("SELECT cod_inscrito FROM ".$this->tableName." WHERE $where LIMIT 1");
            if($rsExiste && !$rsExiste->EOF){
                return array('ok'=>false, 'msg'=>'Esta atividade já consta no cadastro do inscrito — nada a restaurar.');
            }

            $colunas = $this->colunasBase();
            if(empty($colunas)){
                return array('ok'=>false, 'msg'=>'Não foi possível ler a estrutura da tabela de atividades.');
            }
            $listaCol = '`'.implode('`,`', $colunas).'`';

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute("INSERT INTO ".$this->tableName." ($listaCol)
                                         SELECT $listaCol FROM ".$this->tableName."_excluidos
                                          WHERE id_exclusao=$idExclusao");
            if(!$rs){
                return array('ok'=>false, 'msg'=>'Falha ao restaurar a atividade.');
            }

            // Marca no proprio espelho que a linha voltou -- a auditoria conta a
            // historia toda, nao so a exclusao.
            try {
                $usuario = (int)(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0);
                $marker  = addslashes('RESTAURADO em '.date('Y-m-d H:i:s').' por user '.$usuario);
                $this->conex->Execute("UPDATE ".$this->tableName."_excluidos
                                          SET motivo_exclusao = CONCAT(IFNULL(motivo_exclusao,''),' | $marker')
                                        WHERE id_exclusao=$idExclusao");
            } catch (Exception $e) { /* auditoria nao quebra a restauracao */ }

            return array('ok'=>true, 'msg'=>'Atividade restaurada para o cadastro do inscrito.');
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array('ok'=>false, 'msg'=>'Erro ao restaurar a atividade.');
        }
    }
}
?>