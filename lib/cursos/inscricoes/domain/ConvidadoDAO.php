<?php
class ConvidadoDAO extends DAO{

    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Convidado $obj, $debug=false) {
        try{
            
//            $sql = sprintf('SELECT * FROM tab_convidados WHERE cod_evento=\'%s\' '
//            . ' and cod_convidado= \'%s\'', $obj->getCodEvento(),$obj->getCodConvidado());
            $sql = sprintf('SELECT * FROM tab_convidados WHERE cod_convidado= \'%s\'', $obj->getCodConvidado());

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
    public function getByCpf(Convidado $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_convidados cpf= \'%s\'', $obj->getCpf());

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
    public function getByEmail(Convidado $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_convidados WHERE e_mail= \'%s\' ', $obj->getEMail());

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
    public function getByEmailDesc(Convidado $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_convidados WHERE e_mail= \'%s\' order by cod_convidado desc ', $obj->getEMail());

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
            $sql = "SELECT ifnull(MAX(cod_convidado),0)+1 FROM tab_convidados";

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
    public function getByIdExiste(Convidado $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_convidado FROM tab_convidados WHERE cod_convidado= \'%s\'', $obj->getCodConvidado());

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
    public function getAll(Convidado $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_convidados order by cod_evento desc');

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

    public function insert(Convidado $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus("ATIVADO");
                        
            $sql = "SELECT * FROM tab_convidados WHERE cod_convidado=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_convidados");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodConvidado($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Convidado $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_convidados WHERE cod_convidado = ".$obj->getCodConvidado(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_convidados");
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
    public function delete(Convidado $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_convidados WHERE cod_evento=\'%s\' and cod_convidado= \'%s\'', $obj->getCodEvento(),$obj->getCodConvidado());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
