<?php
class ArquivoBoletoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(ArquivoBoleto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_arquivo_boleto '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo_boleto= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivoBoleto());

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
            $sql = "SELECT ifnull(MAX(cod_arquivo_boleto),0)+1 FROM tab_arquivo_boleto";

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
    public function getByIdExiste(ArquivoBoleto $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_arquivo_boleto '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' and cod_trabalho= \'%s\' '
            . '', $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function getAll(ArquivoBoleto $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_arquivo_boleto order by cod_evento desc');

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
    public function carregarListaArquivoBoletoByIdEvento(ArquivoBoleto $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_arquivo_boleto '
                    . ' WHERE cod_evento=\'%s\' AND status=\'ATIVADA\' '
                    . $whereSql
                    . '',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaArquivoBoleto($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaArquivoBoletoBy(ArquivoBoleto $obj, $condicoes, $debug=false) {
        try{
            $whereSql = "";
            if($condicoes=='cod_trabalho' and $obj->getCodTrabalho()!=''){
                $whereSql = " and cod_trabalho = ".$obj->getCodTrabalho();
            }
            if($condicoes=='cod_inscrito' and $obj->getCodInscrito()!=''){
                $whereSql = " and cod_inscrito = ".$obj->getCodInscrito();
            }
            $sql = sprintf('SELECT * FROM tab_arquivo_boleto '
            . ' WHERE cod_evento=\'%s\' AND status=\'ATIVADA\' '
            . $whereSql
            . ' order by versao',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaArquivoBoleto($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(ArquivoBoleto $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessionIdInclusao($_SESSION['_cod_inscrito']);
            $obj->setSessionIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            if($obj->getVersao()==''){
                $obj->setVersao(1);
            }
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM tab_arquivo_boleto "
            . "WHERE cod_evento=".$obj->getCodEvento()." and cod_arquivo_boleto=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_arquivo_boleto");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodArquivoBoleto($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(ArquivoBoleto $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessionIdAlteracao($_SESSION['_cod_inscrito']);
            $obj->setSessionIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_arquivo_boleto"
            . " WHERE cod_evento = ".$obj->getCodEvento()." and cod_arquivo_boleto = ".$obj->getCodArquivoBoleto(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_arquivo_boleto");
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
    public function delete(ArquivoBoleto $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_arquivo_boleto '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo_boleto= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivoBoleto());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function desativar(ArquivoBoleto $obj, $debug=false) {
        try {
            $sql = sprintf('UPDATE tab_arquivo_boleto SET status=\'DESATIVADA\' ' 
            . 'WHERE cod_evento=\'%s\' and cod_trabalho= \'%s\'', $obj->getCodEvento(),$obj->getCodTrabalho());
            
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