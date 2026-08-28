<?php
class AvaliadorDAO extends DAO{

    public function __construct() {        
        parent::__construct();
        $this->codEvento = COD_EVENTO;
    }  
    public function getById(Avaliador $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT su.*, c.* FROM sis_usuarios su 
            LEFT JOIN tab_convidados c ON su.cod_usuario=c.cod_usuario
            INNER JOIN sis_ligacao_usuarios_grupos  lug ON su.cod_usuario=lug.cod_usuario
            INNER JOIN sis_direitos_grupos_sistemas lgs ON lgs.cod_grupo=lug.cod_grupo AND lgs.cod_evento=lug.cod_evento
            INNER JOIN sis_ligacao_usuarios_eventos lue ON lue.cod_usuario=su.cod_usuario AND lue.cod_evento=lug.cod_evento
            WHERE  lue.cod_evento=\'%s\' 
                AND lgs.cod_grupo   ='.enumGrupo::GR_AVALIADORES_TRABALHOS
            . ' AND lgs.cod_sistema ='.enumSistema::SIS_AVALIACAO_TRABALHO
            . ' AND lgs.cod_direito ='.enumDireito::DIR_ADMINISTRAR
            . ' AND su.cod_usuario=\'%s\'', $obj->getCodEvento(),$obj->getCodAvaliador());

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
    public function getByIdPremiacao(Avaliador $obj, $debug=false) {
        try{

            $sql = sprintf('SELECT su.*, c.* FROM sis_usuarios su 
            LEFT JOIN tab_convidados c ON su.cod_usuario=c.cod_usuario
            INNER JOIN sis_ligacao_usuarios_grupos  lug ON su.cod_usuario=lug.cod_usuario
            INNER JOIN sis_direitos_grupos_sistemas lgs ON lgs.cod_grupo=lug.cod_grupo AND lgs.cod_evento=lug.cod_evento
            INNER JOIN sis_ligacao_usuarios_eventos lue ON lue.cod_usuario=su.cod_usuario AND lue.cod_evento=lug.cod_evento
            WHERE  lue.cod_evento=\'%s\' 
                AND lgs.cod_grupo   ='.enumGrupo::GR_AVALIADORES_TRABALHOS_PREMIACAO
            . ' AND lgs.cod_sistema ='.enumSistema::SIS_AVALIACAO_TRABALHO_PREMIADO
            . ' AND lgs.cod_direito ='.enumDireito::DIR_ADMINISTRAR
            . ' AND su.cod_usuario=\'%s\'', $obj->getCodEvento(),$obj->getCodAvaliador());

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

    public function getByIdExiste(Avaliador $obj, $debug=false) {
       $this->getById($obj, $debug);
    }    
    public function carregarListaAvaliadoresByIdEvento(Avaliador $obj, $debug=false) {
        try{
            
//            $sql = sprintf('SELECT *
//                            , IFNULL(taval.total_designados,0) as total_trab_designados
//                            , IFNULL(tavalP.total_pendentes,0) as total_trab_pendentes
//                            , su.login
//                            FROM tab_convidados c
//                                INNER JOIN `sis_usuarios` su ON su.cod_convidado=c.cod_convidado
//                                INNER JOIN `sis_ligacao_usuarios_grupos` lug ON su.cod_usuario=lug.cod_usuario
//                                INNER JOIN `sis_direitos_grupos_sistemas` lgs ON lgs.cod_grupo=lug.cod_grupo
//                                LEFT JOIN (SELECT tha.cod_avaliador, COUNT(tha.cod_avaliador) AS total_designados FROM tab_historico_avaliacao tha 
//                                            INNER JOIN vw_ultima_avaliacao AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
//                                            INNER JOIN tab_trabalhos tt ON tt.cod_trabalho=tha.cod_trabalho AND tt.status=\'ATIVADA\'
//                                            WHERE tha.cod_evento='.$obj->getCodEvento().' AND tha.status=\'ATIVADA\' GROUP BY tha.cod_avaliador) as taval on taval.cod_avaliador=su.cod_usuario
//                                LEFT JOIN (SELECT tha.cod_avaliador, COUNT(tha.cod_avaliador) AS total_pendentes FROM tab_historico_avaliacao tha 
//                                            INNER JOIN vw_ultima_avaliacao AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
//                                            INNER JOIN tab_trabalhos tt ON tt.cod_trabalho=tha.cod_trabalho AND tt.status=\'ATIVADA\'
//                                            WHERE tha.cod_evento='.$obj->getCodEvento().' AND tha.status=\'ATIVADA\' AND tha.avaliacao=\'SELECIONADO\' GROUP BY tha.cod_avaliador) as tavalP on tavalP.cod_avaliador=su.cod_usuario
//                            WHERE  su.cod_evento=\'%s\' AND lgs.cod_grupo=4 AND lgs.cod_sistema=3 AND lgs.cod_direito=5 
//                            AND lgs.cod_evento=su.cod_evento order by nome_convidado', $obj->getCodEvento());

            $innerJoinAreaTematicaDistribuidor = ' inner join tab_lig_convidados_areas_tematicas tlica on tlica.cod_usuario=c.cod_usuario and tlica.cod_area_tematica in (select cod_area_tematica from tab_lig_convidados_areas_tematicas where cod_evento='.COD_EVENTO.' and cod_usuario='.$_SESSION['_cod_usuario'].') ';
            /** @todo configuração para carregar somente avaliadores cuja área tematica seja a mesma do distribuido */
            if(COD_EVENTO==81){
                $innerJoinAreaTematicaDistribuidor = '';
            }
            
            $sql = sprintf('SELECT lue.cod_evento, c.cod_convidado, su.cod_usuario, c.nome_convidado
            , IFNULL(taval.total_designados,0) AS total_trab_designados
            , IFNULL(tavalP.total_pendentes,0) AS total_trab_pendentes
            , su.login
            FROM tab_convidados c
            INNER JOIN `sis_usuarios` su ON su.cod_usuario=c.cod_usuario
            INNER JOIN `sis_ligacao_usuarios_eventos` lue ON su.cod_usuario=lue.cod_usuario
            INNER JOIN `sis_ligacao_usuarios_grupos`  lug ON su.cod_usuario=lug.cod_usuario AND lue.cod_evento=lug.cod_evento
            INNER JOIN `sis_direitos_grupos_sistemas` lgs ON lgs.cod_grupo=lug.cod_grupo    AND lgs.cod_evento=lug.cod_evento
            LEFT JOIN (SELECT tha.cod_avaliador, COUNT(tha.cod_avaliador) AS total_designados FROM tab_historico_avaliacao tha 
                        INNER JOIN vw_ultima_avaliacao AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
                        INNER JOIN tab_trabalhos tt ON tt.cod_trabalho=tha.cod_trabalho AND tt.status=\'ATIVADA\'
                        WHERE tha.cod_evento='.$obj->getCodEvento().' AND tha.status=\'ATIVADA\' GROUP BY tha.cod_avaliador) AS taval ON taval.cod_avaliador=su.cod_usuario
            LEFT JOIN (SELECT tha.cod_avaliador, COUNT(tha.cod_avaliador) AS total_pendentes FROM tab_historico_avaliacao tha 
                        INNER JOIN vw_ultima_avaliacao AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
                        INNER JOIN tab_trabalhos tt ON tt.cod_trabalho=tha.cod_trabalho AND tt.status=\'ATIVADA\'
                        WHERE tha.cod_evento='.$obj->getCodEvento().' AND tha.status=\'ATIVADA\' AND tha.avaliacao=\'SELECIONADO\' GROUP BY tha.cod_avaliador) AS tavalP ON tavalP.cod_avaliador=su.cod_usuario
            '.$innerJoinAreaTematicaDistribuidor.'
            WHERE lue.cod_evento=\'%s\' AND lgs.cod_grupo=4 AND lgs.cod_sistema=3 AND lgs.cod_direito=5
             order by nome_convidado', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliadores($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaAvaliadoresPremiacaoByIdEvento(Avaliador $obj, $debug=false) {
        try{
            $codGrupo   = enumGrupo::GR_AVALIADORES_TRABALHOS_PREMIACAO;
            $codSistema = enumSistema::SIS_AVALIACAO_TRABALHO_PREMIADO;
            $codDireito = enumDireito::DIR_ADMINISTRAR;
            
            $sql = sprintf('SELECT *
                            , IFNULL(taval.total_designados,0) as total_trab_designados
                            , IFNULL(tavalP.total_pendentes,0) as total_trab_pendentes
                            , su.login
                            FROM tab_convidados c
                                INNER JOIN `sis_usuarios` su ON su.cod_convidado=c.cod_convidado
                                INNER JOIN `sis_ligacao_usuarios_grupos` lug ON su.cod_usuario=lug.cod_usuario
                                INNER JOIN `sis_direitos_grupos_sistemas` lgs ON lgs.cod_grupo=lug.cod_grupo
                                LEFT JOIN (SELECT tha.cod_avaliador, COUNT(tha.cod_avaliador) AS total_designados FROM tab_historico_avaliacao_premiacao tha 
                                            INNER JOIN vw_ultima_avaliacao_premiacao_por_autor AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
                                            INNER JOIN tab_trabalhos tt ON tt.cod_trabalho=tha.cod_trabalho AND tt.status=\'ATIVADA\'
                                            WHERE tha.cod_evento='.$obj->getCodEvento().' AND tha.status=\'ATIVADA\' GROUP BY tha.cod_avaliador) as taval on taval.cod_avaliador=su.cod_usuario
                                LEFT JOIN (SELECT tha.cod_avaliador, COUNT(tha.cod_avaliador) AS total_pendentes FROM tab_historico_avaliacao_premiacao tha 
                                            INNER JOIN vw_ultima_avaliacao_premiacao_por_autor AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
                                            INNER JOIN tab_trabalhos tt ON tt.cod_trabalho=tha.cod_trabalho AND tt.status=\'ATIVADA\'
                                            WHERE tha.cod_evento='.$obj->getCodEvento().' AND tha.status=\'ATIVADA\' AND tha.avaliacao=\'SELECIONADO\' GROUP BY tha.cod_avaliador) as tavalP on tavalP.cod_avaliador=su.cod_usuario
                            WHERE  su.cod_evento=\'%s\' 
                                AND lgs.cod_grupo  ='.$codGrupo.' 
                                AND lgs.cod_sistema='.$codSistema.' 
                                AND lgs.cod_direito='.$codDireito.' 
                                AND lgs.cod_evento=su.cod_evento 
                            ORDER BY nome_convidado', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliadores($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getAll(Avaliador $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_convidados c
            INNER JOIN `sis_usuarios` su ON su.cod_convidado=c.cod_convidado
            INNER JOIN `sis_ligacao_usuarios_grupos` lug ON su.cod_usuario=lug.cod_usuario
            INNER JOIN `sis_direitos_grupos_sistemas` lgs ON lgs.cod_grupo=lug.cod_grupo
            WHERE  su.cod_evento=\'%s\' AND lgs.cod_grupo=4 AND lgs.cod_sistema=3 AND lgs.cod_direito=5 
            AND lgs.cod_evento=su.cod_evento', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliadores($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       

    public function insert(Avaliador $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_avaliadores WHERE cod_evento=".$obj->getCodEvento()." and cod_avaliador=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_avaliadors");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodAvaliador($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Avaliador $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_avaliadores WHERE cod_evento = ".$obj->getCodEvento()." and cod_avaliador = ".$obj->getCodAvaliador(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_avaliadores");
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
    public function delete(Avaliador $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_avaliadores WHERE cod_evento=\'%s\' and cod_avaliador= \'%s\'', $obj->getCodEvento(),$obj->getCodAvaliador());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
