<?php
class PrevisaoOrcamentariaDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(PrevisaoOrcamentaria $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM adm_previsao_orcamentaria WHERE cod_evento=\'%s\' and cod_previsao_orcamentaria= \'%s\'', $obj->getCodEvento(),$obj->getCodPrevisaoOrcamentaria());

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
            $sql = "SELECT ifnull(MAX(cod_previsao_orcamentaria),0)+1 FROM adm_previsao_orcamentaria";

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
    public function getByIdExiste(PrevisaoOrcamentaria $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM adm_previsao_orcamentaria WHERE cod_evento=\'%s\' and cod_previsao_orcamentaria= \'%s\'', $obj->getCodEvento(),$obj->getCodPrevisaoOrcamentaria());

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
    public function getAll(PrevisaoOrcamentaria $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM adm_previsao_orcamentaria order by cod_evento desc');

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
    public function carregarListaPrevisaoOrcamentariasByIdEvento(PrevisaoOrcamentaria $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM adm_previsao_orcamentaria WHERE cod_evento=\'%s\' order by cod_previsao_orcamentaria desc',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPrevisaoOrcamentarias($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(PrevisaoOrcamentaria $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM adm_previsao_orcamentaria WHERE cod_evento=".$obj->getCodEvento()." and cod_previsao_orcamentaria=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("adm_previsao_orcamentaria");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodPrevisaoOrcamentaria($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(PrevisaoOrcamentaria $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM adm_previsao_orcamentaria WHERE cod_evento = ".$obj->getCodEvento()." and cod_previsao_orcamentaria = ".$obj->getCodPrevisaoOrcamentaria(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("adm_previsao_orcamentaria");
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
    public function delete(PrevisaoOrcamentaria $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM adm_previsao_orcamentaria WHERE cod_evento=\'%s\' and cod_previsao_orcamentaria= \'%s\'', $obj->getCodEvento(),$obj->getCodPrevisaoOrcamentaria());
            
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