<?php
class ArquivoApresentacaoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(ArquivoApresentacao $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_arquivo_apresentacao '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo_apresentacao= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivoApresentacao());

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
            $sql = "SELECT ifnull(MAX(cod_arquivo_apresentacao),0)+1 FROM tab_arquivo_apresentacao";

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
    public function getByIdExiste(ArquivoApresentacao $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_arquivo_apresentacao '
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
    public function getAll(ArquivoApresentacao $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_arquivo_apresentacao order by cod_evento desc');

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
    public function carregarListaArquivoApresentacaoByIdEvento(ArquivoApresentacao $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_arquivo_apresentacao '
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
                $obj->setListaArquivoApresentacao($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaArquivoApresentacaoBy(ArquivoApresentacao $obj, $condicoes, $debug=false) {
        try{
            $whereSql = "";
            if($condicoes=='cod_trabalho' and $obj->getCodTrabalho()!=''){
                $whereSql = " and cod_trabalho = ".$obj->getCodTrabalho();
            }
            $sql = sprintf('SELECT * FROM tab_arquivo_apresentacao '
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
                $obj->setListaArquivoApresentacao($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(ArquivoApresentacao $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessionIdInclusao($_SESSION['_cod_inscrito']);
            $obj->setSessionIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            if($obj->getVersao()==''){
                $obj->setVersao(1);
            }
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM tab_arquivo_apresentacao "
            . "WHERE cod_evento=".$obj->getCodEvento()." and cod_arquivo_apresentacao=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_arquivo_apresentacao");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodArquivoApresentacao($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(ArquivoApresentacao $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessionIdAlteracao($_SESSION['_cod_inscrito']);
            $obj->setSessionIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_arquivo_apresentacao"
            . " WHERE cod_evento = ".$obj->getCodEvento()." and cod_arquivo_apresentacao = ".$obj->getCodArquivoApresentacao(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_arquivo_apresentacao");
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
    public function delete(ArquivoApresentacao $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_arquivo_apresentacao '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo_apresentacao= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivoApresentacao());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function desativar(ArquivoApresentacao $obj, $debug=false) {
        try {
            $sql = sprintf('UPDATE tab_arquivo_apresentacao SET status=\'DESATIVADA\' ' 
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