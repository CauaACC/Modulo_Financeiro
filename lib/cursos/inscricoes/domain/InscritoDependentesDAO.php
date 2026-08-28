<?php
class InscritoDependentesDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(InscritoDependentes $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tid.* FROM tab_inscrito_dependentes tid
            WHERE tid.cod_evento=\'%s\' and tid.cod_dependente= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodDependente());

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
            $sql = "SELECT ifnull(MAX(cod_dependente),0)+1 FROM tab_inscrito_dependentes";

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
    public function getByIdExiste(InscritoDependentes $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT tid.* FROM tab_inscrito_dependentes tid 
            WHERE tid.cod_evento=\'%s\' and tid.cod_dependente= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodDependente());

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
    public function getAll(InscritoDependentes $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tid.* FROM tab_inscrito_dependentes tid
            WHERE tid.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaInscritoDependentes($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaInscritoDependentesByIdEvento(InscritoDependentes $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT tid.* FROM tab_inscrito_dependentes tid
            WHERE tid.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaInscritoDependentes($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(InscritoDependentes $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
                        
            $sql = "SELECT * FROM tab_inscrito_dependentes 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_dependente=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_inscrito_dependentes");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodInscritoDependentes($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(InscritoDependentes $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM tab_inscrito_dependentes WHERE cod_evento = ".$obj->getCodEvento()." and cod_dependente = ".$obj->getCodDependente(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_inscrito_dependentes");
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
    public function delete(InscritoDependentes $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_inscrito_dependentes WHERE cod_evento=\'%s\' and cod_dependente= \'%s\'', $obj->getCodEvento(),$obj->getCodDependente());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }        
}
