<?php
class LancamentosContabeisDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(LancamentosContabeis $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT flc.* FROM fin_lancamentos_contabeis flc
            WHERE flc.cod_evento=\'%s\' and flc.cod_lancamentos_contabeis= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodLancamentosContabeis());

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
            $sql = "SELECT ifnull(MAX(cod_lancamentos_contabeis),0)+1 FROM fin_lancamentos_contabeis";

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
    public function getByIdExiste(LancamentosContabeis $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT flc.* FROM fin_lancamentos_contabeis flc 
            WHERE flc.cod_evento=\'%s\' and flc.cod_lancamentos_contabeis= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodLancamentosContabeis());

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
    public function getAll(LancamentosContabeis $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT flc.* FROM fin_lancamentos_contabeis flc
            WHERE flc.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaLancamentosContabeis($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaLancamentosContabeisByIdEvento(LancamentosContabeis $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT flc.* FROM fin_lancamentos_contabeis flc
            WHERE flc.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaLancamentosContabeis($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(LancamentosContabeis $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM fin_lancamentos_contabeis 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_lancamentos_contabeis=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("fin_lancamentos_contabeis");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setcodLancamentosContabeis($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(LancamentosContabeis $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM fin_lancamentos_contabeis WHERE cod_evento = ".$obj->getCodEvento()." and cod_lancamentos_contabeis = ".$obj->getCodLancamentosContabeis(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("fin_lancamentos_contabeis");
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
    public function delete(LancamentosContabeis $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM fin_lancamentos_contabeis WHERE cod_evento=\'%s\' and cod_lancamentos_contabeis= \'%s\'', $obj->getCodEvento(),$obj->getCodLancamentosContabeis());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }        
}
