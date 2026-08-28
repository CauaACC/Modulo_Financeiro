<?php

class BoletoPagSeguroDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(BoletoPagSeguro $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos_pagseguro_retorno '
                    . 'WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\''
                    . ' and transacao_id= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivo(),$obj->getTransacaoId());

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
    public function getBoletoPagSeguroByTransacaoId(BoletoPagSeguro $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos_pagseguro_retorno WHERE cod_evento=\'%s\' 
                and transacao_id= \'%s\'', $obj->getCodEvento(),$obj->getTransacaoId());

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
            $sql = "SELECT ifnull(MAX(cod_arquivo),0)+1 FROM tab_boletos_pagseguro_retorno";

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
    public function getByIdExiste(BoletoPagSeguro $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT transacao_id FROM tab_boletos_pagseguro_retorno WHERE cod_evento=\'%s\' 
                and transacao_id= \'%s\'', $obj->getCodEvento(),$obj->getTransacaoId());

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
    public function getAll(BoletoPagSeguro $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos_pagseguro_retorno order by cod_evento desc');

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
    public function carregarListaBoletoPagSegurosByIdEvento(BoletoPagSeguro $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_boletos_pagseguro_retorno '
                    . 'WHERE cod_evento=\'%s\' '
                    . 'order by cod_arquivo',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaBoletoPagSeguros($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(BoletoPagSeguro $obj, $debug=false) {
        try {      
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionId($_COOKIE["PHPSESSID"]);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_boletos_pagseguro_retorno "
                    . "WHERE cod_evento=".$obj->getCodEvento()." and transacao_id='0'"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_boletos_pagseguro_retorno");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(BoletoPagSeguro $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionId($_COOKIE["PHPSESSID"]);
            $obj->setVersao(1);

            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_boletos_pagseguro_retorno WHERE cod_evento = ".$obj->getCodEvento()." and transacao_id = ".$obj->getTransacaoId(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_boletos_pagseguro_retorno");
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
    public function delete(BoletoPagSeguro $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_boletos_pagseguro_retorno '
            . 'WHERE cod_evento=\'%s\' and transacao_id= \'%s\'', $obj->getCodEvento(),$obj->getTransacaoId());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function deleteByCodArquivo(BoletoPagSeguro $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_boletos_pagseguro_retorno '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivo());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
