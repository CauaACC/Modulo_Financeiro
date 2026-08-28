<?php
class AreaTematicaDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(AreaTematica $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_areas_tematicas WHERE cod_evento=\'%s\' and cod_area_tematica= \'%s\'', $obj->getCodEvento(),$obj->getCodAreaTematica());

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
            $sql = "SELECT ifnull(MAX(cod_area_tematica),0)+1 FROM tab_opcoes_areas_tematicas";

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
    public function getByIdExiste(AreaTematica $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM tab_opcoes_areas_tematicas WHERE cod_evento=\'%s\' and cod_area_tematica= \'%s\'', $obj->getCodEvento(),$obj->getCodAreaTematica());

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
    public function getAll(AreaTematica $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_areas_tematicas order by cod_evento desc');

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
    public function carregarListaAreaTematicasByIdEvento(AreaTematica $obj, $debug=false) {
        return $this->carregarListaAreaTematicaByIdEvento($obj, $debug);
    }

    public function carregarListaAreaTematicaByIdEvento(AreaTematica $obj, $debug=false) {
        try{

            // $sql = sprintf('SELECT * FROM tab_opcoes_areas_tematicas '
            //         . 'WHERE cod_evento=\'%s\' and status=\'ATIVADO\' ' 
            //         . 'order by ordem, titulo',$obj->getCodEvento());

            $sql = sprintf("SELECT ta.*, tb.total_inscritos FROM tab_opcoes_areas_tematicas ta
            LEFT JOIN (SELECT COUNT(tba.cod_trabalho) AS total_inscritos,tba.cod_evento, tba.cod_area_tematica 
                                FROM tab_trabalhos tba, tab_inscritos ti 
                                WHERE (tba.cod_inscrito     =ti.cod_inscrito)
                                AND(ti.status            ='ATIVADA')
                                GROUP BY tba.cod_area_tematica, tba.cod_evento) AS tb ON tb.cod_evento=ta.cod_evento AND tb.cod_area_tematica=ta.cod_area_tematica
            WHERE ta.cod_evento='%s' AND ta.status='ATIVADO' 
            ORDER BY ta.ordem, ta.titulo",$obj->getCodEvento());

            $this->conex->debug = $debug;
            // $this->conex->cacheSecs = 10;
            // $rs = $this->conex->cacheExecute($sql);
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAreaTematica($return);
                $obj->setListaAreasTematicas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaAreasTematicasByIdUsuario(AreaTematica $obj, $debug=false) {
        try{
            $sql = sprintf("SELECT ta.*, tlca.cod_usuario FROM tab_opcoes_areas_tematicas ta 
                INNER JOIN tab_lig_convidados_areas_tematicas tlca ON tlca.cod_area_tematica = ta.cod_area_tematica AND tlca.cod_evento=ta.cod_evento
            WHERE tlca.cod_evento='%s' AND tlca.cod_usuario='%s' AND ta.status='ATIVADO' 
            ORDER BY ta.ordem, ta.titulo",$obj->getCodEvento(),$obj->getCodUsuario());

            $this->conex->debug = $debug;
            // $this->conex->cacheSecs = 10;
            // $rs = $this->conex->cacheExecute($sql);
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAreasTematicas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(AreaTematica $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_opcoes_areas_tematicas WHERE cod_evento=".$obj->getCodEvento()." and cod_area_tematica=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_areas_tematicas");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodAreaTematica($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(AreaTematica $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_opcoes_areas_tematicas WHERE cod_evento = ".$obj->getCodEvento()." and cod_area_tematica = ".$obj->getCodAreaTematica(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_areas_tematicas");
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
    public function delete(AreaTematica $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_opcoes_areas_tematicas WHERE cod_evento=\'%s\' and cod_area_tematica= \'%s\'', $obj->getCodEvento(),$obj->getCodAreaTematica());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
