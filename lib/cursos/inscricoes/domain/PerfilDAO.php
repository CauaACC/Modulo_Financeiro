<?php
class PerfilDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Perfil $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_perfis WHERE cod_evento=\'%s\' and cod_perfil= \'%s\'', $obj->getCodEvento(),$obj->getCodPerfil());

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
            $sql = "SELECT ifnull(MAX(cod_perfil),0)+1 FROM tab_opcoes_perfis";

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
    public function getByIdExiste(Perfil $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM tab_opcoes_perfis WHERE cod_evento=\'%s\' and cod_perfil= \'%s\'', $obj->getCodEvento(),$obj->getCodPerfil());

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
    public function getAll(Perfil $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_perfis order by cod_evento desc');

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
    public function carregarListaPerfilByIdEvento(Perfil $obj, $orderBy = '', $debug=false) {
        try{
            if($orderBy==''){ 
                $orderBy = ' order by op.ordem, op.cod_perfil desc ';
            }
            if($orderBy=='cod_perfil desc'){ 
                $orderBy = ' order by op.cod_perfil desc ';
            }
            $sql = sprintf('SELECT op.*, i.total_inscritos_i, lip.total_inscritos_p, IFNULL(i.total_inscritos_i, 0) + IFNULL(lip.total_inscritos_p, 0) AS total_inscritos '
                    . ' FROM tab_opcoes_perfis op '
                    . ' left join (select lip.cod_perfil, lip.cod_evento, count(lip.cod_perfil) as total_inscritos_p '
                    . '             from tab_lig_inscritos_perfis lip '
                    . '             where lip.cod_evento=%s group by lip.cod_perfil) as lip on lip.cod_perfil=op.cod_perfil and lip.cod_evento=op.cod_evento '
                    . ' left join (select i.cod_perfil, i.cod_evento, count(i.cod_perfil) as total_inscritos_i '
                    . '             from tab_inscritos i '
                    . '             where i.cod_evento=%s group by i.cod_perfil) as i on i.cod_perfil=op.cod_perfil and i.cod_evento=op.cod_evento '
                    . ' WHERE op.cod_evento=%s '
                    . $orderBy ,$obj->getCodEvento(),$obj->getCodEvento(),$obj->getCodEvento());
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            // $rs = $this->conex->CacheExecute(600, $sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPerfil($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaPerfilInscricaoByIdEvento(Perfil $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT op.* '
                    . ', ( SELECT COUNT(ti.cod_inscrito) FROM tab_inscritos ti  
  WHERE ti.cod_perfil=op.cod_perfil AND ti.cod_evento=op.cod_evento AND ti.status=\'ATIVADA\' GROUP BY ti.cod_evento ) AS total_inscritos_perfil_1 
, ( SELECT COUNT(lip.cod_inscrito) FROM tab_lig_inscritos_perfis lip  
 INNER JOIN tab_inscritos i ON i.cod_inscrito=lip.cod_inscrito AND i.status=\'ATIVADA\' 
 WHERE lip.cod_perfil=op.cod_perfil AND lip.cod_evento=op.cod_evento AND lip.status=\'ATIVADA\' GROUP BY lip.cod_evento ) AS total_inscritos_perfil_2 '
                    . 'FROM tab_opcoes_perfis op '
                    . 'WHERE op.cod_evento=\'%s\' and op.receber_inscricao=1 '
                    . 'order by op.ordem, op.titulo',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            // $rs = $this->conex->CacheExecute(600, $sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPerfil($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaPerfilOptions(Perfil $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT cod_perfil, titulo FROM tab_opcoes_perfis WHERE cod_evento=%s AND status=\'ATIVADA\' ORDER BY ordem, cod_perfil desc', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPerfil($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function toggleCampo(Perfil $obj, $campo, $debug=false) {
        try {
            $camposPermitidos = array(
                'status', 'receber_inscricao', 'verificar_vagas_limitadas',
                'perfil_isento', 'verificar_envio_trabalho',
                'verificar_selecionar_atividade', 'liberar_certificado'
            );
            if (!in_array($campo, $camposPermitidos)) {
                return false;
            }

            if ($campo == 'status') {
                $sql = sprintf("UPDATE tab_opcoes_perfis SET status = IF(status='ATIVADA','DESATIVADA','ATIVADA') WHERE cod_evento=%s AND cod_perfil=%s",
                    $obj->getCodEvento(), $obj->getCodPerfil());
            } else {
                $sql = sprintf("UPDATE tab_opcoes_perfis SET %s = IF(%s=1,0,1) WHERE cod_evento=%s AND cod_perfil=%s",
                    $campo, $campo, $obj->getCodEvento(), $obj->getCodPerfil());
            }

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);

            // Recarregar para retornar o valor atualizado
            $this->getById($obj, $debug);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(Perfil $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_opcoes_perfis WHERE cod_evento=".$obj->getCodEvento()." and cod_perfil=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_perfis");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            echo $debug? $sql."<hr>" : "";
            $rs = $this->conex->Execute($sql);
            $obj->setCodPerfil($this->conex->Insert_ID());
            
            /************ INSERINDO UMA TAXA *****************************/
            $sqlTX = "INSERT INTO tab_opcoes_taxas (cod_evento, cod_perfil, status, tx_unica_a_vista, tx_unica_a_vista_valor)
            values(".$obj->getCodEvento().",".$obj->getCodPerfil().",'DESATIVA','1','0.00')";
            $rsGE = $this->conex->Execute($sqlTX);
            
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Perfil $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $sql = "SELECT * FROM tab_opcoes_perfis WHERE cod_evento = ".$obj->getCodEvento()." and cod_perfil = ".$obj->getCodPerfil(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_perfis");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
            // $sql = sprintf($updateSQL);
            $sql = $updateSQL;
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function delete(Perfil $obj, $debug=false) {
        try {
            $var_sql = "
                        INSERT INTO tab_opcoes_perfis_excluidos
                        SELECT *
                        FROM tab_opcoes_perfis
                        WHERE (cod_perfil = " .$obj->getCodPerfil() . ")
                    ";
            $this->conex->Execute($var_sql);
            $var_sql = "
                        INSERT INTO tab_opcoes_taxas_excluidos
                        SELECT *
                        FROM tab_opcoes_taxas
                        WHERE (cod_perfil = " .$obj->getCodPerfil() . ")
                    ";
            $this->conex->Execute($var_sql);

            $sql = sprintf('DELETE FROM tab_opcoes_perfis WHERE cod_evento=\'%s\' and cod_perfil= \'%s\'', $obj->getCodEvento(),$obj->getCodPerfil());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            
            $sql = sprintf('DELETE FROM tab_opcoes_taxas WHERE cod_evento=\'%s\' and cod_perfil= \'%s\'', $obj->getCodEvento(),$obj->getCodPerfil());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function duplicar(Perfil $obj, $debug=false) {
        try{
            
            $sql = sprintf('INSERT INTO tab_opcoes_perfis(cod_evento, ordem, titulo, perfil_isento, liberar_certificado
                , receber_inscricao, verificar_inscrito_apto, verificar_envio_trabalho, verificar_selecionar_atividade
                , liberar_certificado_congressista, verificar_vagas_limitadas, publico_alvo, pre_requisito, documentacao
                , investimento, descricao, observacao, vagas, vagas_net, vagas_interna, vagas_lista_espera, obs, status
                , periodo_inscricao_inicial, periodo_inscricao_final, ano_referencia, http_user_agent_inclusao, http_user_agent_alteracao
                , data_inclusao, data_alteracao, ip_cliente_inclusao, ip_cliente_alteracao, session_id_inclusao, session_id_alteracao
                , carga_horaria_certificado, qtd_atividade_permite, valores_por_periodo
                , comprovante_exigir, comprovante_titulo, comprovante_instrucao, comprovante_extensoes
                )
                SELECT cod_evento, ordem, concat("Cópia de ",titulo), perfil_isento, liberar_certificado
                , receber_inscricao, verificar_inscrito_apto, verificar_envio_trabalho, verificar_selecionar_atividade
                , liberar_certificado_congressista, verificar_vagas_limitadas, publico_alvo, pre_requisito, documentacao
                , investimento, descricao, observacao, vagas, vagas_net, vagas_interna, vagas_lista_espera, obs, status
                , periodo_inscricao_inicial, periodo_inscricao_final, ano_referencia, http_user_agent_inclusao, http_user_agent_alteracao
                , data_inclusao, data_alteracao, ip_cliente_inclusao, ip_cliente_alteracao, session_id_inclusao, session_id_alteracao
                , carga_horaria_certificado, qtd_atividade_permite, valores_por_periodo
                , comprovante_exigir, comprovante_titulo, comprovante_instrucao, comprovante_extensoes
                FROM tab_opcoes_perfis WHERE cod_evento=\'%s\' and cod_perfil= \'%s\'', $obj->getCodEvento(),$obj->getCodPerfil());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodPerfil($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function importarPerfis(Perfil $obj, $debug=false) {
        try{
            
            $sql = sprintf('INSERT INTO tab_opcoes_perfis(cod_evento, ordem, titulo, perfil_isento, liberar_certificado
                , receber_inscricao, verificar_inscrito_apto, verificar_envio_trabalho, verificar_selecionar_atividade
                , liberar_certificado_congressista, verificar_vagas_limitadas, publico_alvo, pre_requisito, documentacao
                , investimento, descricao, observacao, vagas, vagas_net, vagas_interna, vagas_lista_espera, obs, status
                , periodo_inscricao_inicial, periodo_inscricao_final, ano_referencia, http_user_agent_inclusao, http_user_agent_alteracao
                , data_inclusao, data_alteracao, ip_cliente_inclusao, ip_cliente_alteracao, session_id_inclusao, session_id_alteracao
                , carga_horaria_certificado, qtd_atividade_permite, valores_por_periodo
                , comprovante_exigir, comprovante_titulo, comprovante_instrucao, comprovante_extensoes
                )
                SELECT '.$obj->getCodEvento().' as cod_evento, ordem, concat("Cópia de ",titulo), perfil_isento
                , '.$obj->getLiberarCertificado().'
                , receber_inscricao, verificar_inscrito_apto, verificar_envio_trabalho, verificar_selecionar_atividade
                , liberar_certificado_congressista, verificar_vagas_limitadas, publico_alvo, pre_requisito, documentacao
                , investimento, descricao, observacao, vagas, vagas_net, vagas_interna, vagas_lista_espera, obs, status
                , periodo_inscricao_inicial, periodo_inscricao_final, ano_referencia, http_user_agent_inclusao, http_user_agent_alteracao
                , data_inclusao, data_alteracao, ip_cliente_inclusao, ip_cliente_alteracao, session_id_inclusao, session_id_alteracao
                , carga_horaria_certificado, qtd_atividade_permite, valores_por_periodo
                , comprovante_exigir, comprovante_titulo, comprovante_instrucao, comprovante_extensoes
                FROM tab_opcoes_perfis WHERE cod_evento=\'%s\' ', $obj->getCodEventoAnt());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
