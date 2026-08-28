<?php
class EtapaDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Etapa $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tae.* FROM tab_avaliacao_etapa tae
            WHERE tae.cod_evento=\'%s\' and tae.cod_etapa= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodEtapa());

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
            $sql = "SELECT ifnull(MAX(cod_etapa),0)+1 FROM tab_avaliacao_etapa";

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
    public function getByIdExiste(Etapa $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT tae.* FROM tab_avaliacao_etapa tae 
            WHERE tae.cod_evento=\'%s\' and tae.cod_etapa= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodEtapa());

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
    public function getAll(Etapa $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tae.* FROM tab_avaliacao_etapa tae
            WHERE tae.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEtapas($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaEtapaByIdEvento(Etapa $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT tae.* FROM tab_avaliacao_etapa tae
            WHERE tae.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEtapa($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Busca a etapa por (cod_evento, ordem_etapa). Quando $somenteAtivas=true,
     * filtra adicionalmente por situacao=1 (Em andamento).
     * Retorna o cod_etapa (int) ou null se nao encontrar.
     */
    public function getEtapaByOrdem(Etapa $obj, $ordemEtapa, $somenteAtivas=true, $debug=false) {
        try {
            $whereAtiva = $somenteAtivas ? ' AND tae.situacao = 1 ' : '';
            $sql = sprintf("SELECT tae.* FROM tab_avaliacao_etapa tae
                WHERE tae.cod_evento = '%s'
                  AND tae.ordem_etapa = '%d'
                  AND tae.status = 'ATIVADA'
                  %s
                ORDER BY tae.cod_etapa DESC LIMIT 1",
                $obj->getCodEvento(), (int)$ordemEtapa, $whereAtiva);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return null;
            }
            foreach ($rs as $rows) {}
            $obj->setAll($rows);
            return (int)$obj->getCodEtapa();
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return null;
        }
    }

    public function insert(Etapa $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM tab_avaliacao_etapa 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_etapa=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_avaliacao_etapa");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodEtapa($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Etapa $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM tab_avaliacao_etapa WHERE cod_evento = ".$obj->getCodEvento()." and cod_etapa = ".$obj->getCodEtapa(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_avaliacao_etapa");
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
    public function delete(Etapa $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_avaliacao_etapa WHERE cod_evento=\'%s\' and cod_etapa= \'%s\'', $obj->getCodEvento(),$obj->getCodEtapa());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }        
}
