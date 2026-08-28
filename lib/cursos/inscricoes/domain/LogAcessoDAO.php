<?php
class LogAcessoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(LogAcesso $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM sis_log_acesso WHERE cod_evento=\'%s\' and cod_log_acesso= \'%s\''
                    . '  and sucesso = 1 ', $obj->getCodEvento(),$obj->getCodLogAcesso());

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
            $sql = "SELECT ifnull(MAX(cod_log_acesso),0)+1 FROM sis_log_acesso";

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
    public function getByIdExiste(LogAcesso $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_log_acesso FROM sis_log_acesso '
                    . 'WHERE cod_evento=\'%s\' and cod_log_acesso= \'%s\' and sucesso = 1', $obj->getCodEvento(),$obj->getCodLogAcesso());

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
    public function getAll(LogAcesso $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM sis_log_acesso order by cod_evento desc');

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
    public function carregarListaLogAcessoByIdEvento(LogAcesso $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM sis_log_acesso '
                    . 'WHERE cod_evento=\'%s\'  and sucesso = 1 ' 
                    . 'order by ordem, titulo',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaLogAcesso($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaLogAcessoByLogin(LogAcesso $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM sis_log_acesso '
                    . 'WHERE cod_evento=\'%s\' and login=\'%s\' and sucesso = 1 ' 
                    . 'order by ordem, titulo',$obj->getCodEvento(),$obj->getLogin());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaLogAcesso($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarUltimoAcessoByLogin(LogAcesso $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM sis_log_acesso '
                    . 'WHERE cod_evento=\'%s\' and login=\'%s\' '
                    . 'cod_log_acesso = (select max(cod_log_acesso) from sis_log_acesso where cod_evento=\'%s\' and login=\'%s\' and sucesso = 1)' 
                    . 'order by ordem, titulo',$obj->getCodEvento(),$obj->getLogin(),$obj->getCodEvento(),$obj->getLogin());

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
    public function insert(LogAcesso $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setRespInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionIdInclusao($_COOKIE["PHPSESSID"]);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM sis_log_acesso WHERE cod_evento=".$obj->getCodEvento()." and cod_log_acesso=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("sis_log_acesso");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodLogAcesso($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(LogAcesso $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setRespAlteracao($_SESSION['_cod_usuario']);
            $obj->setIpClienteAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionIdAlteracao($_COOKIE["PHPSESSID"]);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM sis_log_acesso WHERE cod_evento = ".$obj->getCodEvento()." and cod_log_acesso = ".$obj->getCodLogAcesso(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("sis_log_acesso");
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
    public function delete(LogAcesso $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM sis_log_acesso WHERE cod_evento=\'%s\' and cod_log_acesso= \'%s\'', $obj->getCodEvento(),$obj->getCodLogAcesso());
            
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