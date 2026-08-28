<?php
class ArquivoAtividadeDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getByIdExiste(ArquivoAtividade $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_arquivo_atividade '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo_atividade= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivoAtividade());

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
    public function getById(ArquivoAtividade $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_arquivo_atividade '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo_atividade= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivoAtividade());

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
            $sql = "SELECT ifnull(MAX(cod_arquivo_atividade),0)+1 FROM tab_arquivo_atividade";

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
    public function getByIdTrabalhoExiste(ArquivoAtividade $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_arquivo_atividade '
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
    public function getAll(ArquivoAtividade $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_arquivo_atividade order by cod_evento desc');

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
    public function carregarListaArquivoAtividadeByIdEvento(ArquivoAtividade $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_arquivo_atividade '
                    . ' WHERE cod_evento=\'%s\' AND status=\'ATIVADA\' '
                    . $whereSql
                    . ' ORDER BY cod_arquivo_atividade DESC'
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
                $obj->setListaArquivoAtividade($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarArquivoAtividadeOficinaAssCoordenadorBy(ArquivoAtividade $obj, $condicoes, $debug=false) {
        try{
            $whereSql = " and cod_oficina = ".$obj->getCodOficina();
            
            $sql = sprintf('SELECT * FROM tab_arquivo_atividade '
            . ' WHERE cod_evento=\'%s\' AND status=\'ATIVADA\'  AND tipo_arquivo_atividade=\'ASSINATURA-COORDENADOR\' '
            . $whereSql
            . ' order by versao desc',$obj->getCodEvento());

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
    public function carregarArquivoAtividadeOficinaProgramaBy(ArquivoAtividade $obj, $condicoes, $debug=false) {
        try{
            $whereSql = " and cod_oficina = ".$obj->getCodOficina();

            $sql = sprintf('SELECT * FROM tab_arquivo_atividade '
            . ' WHERE cod_evento=\'%s\' AND status=\'ATIVADA\' AND tipo_arquivo_atividade=\'PROGRAMA\' '
            . $whereSql
            . ' order by versao desc',$obj->getCodEvento());

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
    public function insert(ArquivoAtividade $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessionIdInclusao($_SESSION['_cod_inscrito']);
            $obj->setSessionIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            if($obj->getVersao()==''){
                $obj->setVersao(1);
            }
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM tab_arquivo_atividade "
            . "WHERE cod_evento=".$obj->getCodEvento()." and cod_arquivo_atividade=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_arquivo_atividade");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodArquivoAtividade($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(ArquivoAtividade $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessionIdAlteracao($_SESSION['_cod_inscrito']);
            $obj->setSessionIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_arquivo_atividade"
            . " WHERE cod_evento = ".$obj->getCodEvento()." and cod_arquivo_atividade = ".$obj->getCodArquivoAtividade(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_arquivo_atividade");
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
    public function delete(ArquivoAtividade $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_arquivo_atividade '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo_atividade= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivoAtividade());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function desativar(ArquivoAtividade $obj, $debug=false) {
        try {
            $sql = sprintf('UPDATE tab_arquivo_atividade SET status=\'DESATIVADA\' ' 
            . 'WHERE cod_evento=\'%s\' and cod_arquivo_atividade= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivoAtividade());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
