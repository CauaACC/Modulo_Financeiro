<?php
class LoginDAO extends DAO{

    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Login $obj, $debug=false) {
        try{
            
//            $sql = sprintf('SELECT * FROM sis_usuarios WHERE cod_evento=\'%s\' '
//            . ' and cod_login= \'%s\'', $obj->getCodEvento(),$obj->getCodLogin());
            $sql = sprintf('SELECT u.* FROM sis_usuarios u 
            inner join sis_ligacao_usuarios_eventos lue ON u.cod_usuario=lue.cod_usuario    
            WHERE lue.cod_evento = %s and cod_login = \'%s\'', $obj->getCodEvento(),$obj->getCodLogin());

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
    public function getByEmail(Login $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT u.* FROM sis_usuarios u   
            WHERE status=\'ATIVADA\' login = \'%s\' LIMIT 1', $obj->getLogin());

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
    public function getByCodConvidado(Login $obj, $debug=false) {
        try{
            
//            $sql = sprintf('SELECT * FROM sis_usuarios WHERE cod_evento=\'%s\' '
//            . ' and cod_convidado= \'%s\'', $obj->getCodEvento(),$obj->getCodConvidado());
            $sql = sprintf('SELECT u.* FROM sis_usuarios u 
            inner join sis_ligacao_usuarios_eventos lue ON u.cod_usuario=lue.cod_usuario    
            WHERE lue.cod_evento = %s and u.cod_convidado= \'%s\'', $obj->getCodEvento(),$obj->getCodConvidado());

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
            $sql = "SELECT ifnull(MAX(cod_login),0)+1 FROM sis_usuarios";

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
    public function getByIdExiste(Login $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_usuario FROM sis_usuarios WHERE cod_evento=\'%s\'
            and cod_login= \'%s\'', $obj->getCodEvento(),$obj->getCodLogin());

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
    public function getAll(Login $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM sis_usuarios order by cod_evento desc');

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
    public function insert(Login $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM sis_usuarios WHERE cod_evento=0 and cod_usuario=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("sis_usuarios");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodUsuario($this->conex->Insert_ID());
            
            /************ LIGAR USUARIOS AO EVENTO *****************************/
            $sqlUE = "INSERT INTO sis_ligacao_usuarios_eventos(cod_evento, cod_usuario, status)values(".$obj->getCodEvento().",".$obj->getCodUsuario().",'ATIVADA')";
            $rsUE = $this->conex->Execute($sqlUE);
                        
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Login $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM sis_usuarios WHERE cod_usuario = ".$obj->getCodUsuario(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("sis_usuarios");
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
    public function delete(Login $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM sis_usuarios WHERE cod_evento=\'%s\' and cod_usuario= \'%s\'', $obj->getCodEvento(),$obj->getCodUsuario());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
