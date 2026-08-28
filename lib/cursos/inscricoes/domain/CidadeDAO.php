<?php
class CidadeDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Cidade $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_cidades WHERE cod_evento=\'%s\' and cod_area_tematica= \'%s\'', $obj->getCodEvento(),$obj->getCodCidade());

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
            $sql = "SELECT ifnull(MAX(cod_area_tematica),0)+1 FROM tab_opcoes_cidades";

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
    public function getByIdExiste(Cidade $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM tab_opcoes_cidades WHERE cod_evento=\'%s\' and cod_area_tematica= \'%s\'', $obj->getCodEvento(),$obj->getCodCidade());

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
    public function getAll(Cidade $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_cidades order by nome');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaCidades($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarCidadesByIdEstado(Cidade $obj, $debug=false) {
        try{
            
            $sql = sprintf("SELECT * FROM tab_opcoes_cidades WHERE uf = '".$obj->getUf()."' order by nome");

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
                $obj->setListaCidades($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function insert(Cidade $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_opcoes_cidades WHERE cod_evento=".$obj->getCodEvento()." and cod_area_tematica=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_cidades");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodCidade($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Cidade $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_opcoes_cidades WHERE cod_evento = ".$obj->getCodEvento()." and cod_area_tematica = ".$obj->getCodCidade(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_cidades");
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
    public function delete(Cidade $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_opcoes_cidades WHERE cod_evento=\'%s\' and cod_area_tematica= \'%s\'', $obj->getCodEvento(),$obj->getCodCidade());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
?>