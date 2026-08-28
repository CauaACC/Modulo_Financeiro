<?php
class HistoricoAvaliacaoDAO extends DAO{

    public function __construct() { 
        $this->tableName = "tab_historico_avaliacao";
        parent::__construct();
    }  
    public function getById(HistoricoAvaliacao $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . 'and cod_inscrito= \'%s\' and cod_trabalho= \'%s\' and cod_avaliacao= \'%s\' and status=\'ATIVADA\' ',
            $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho(),$obj->getCodAvaliacao());

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
    public function getByIdAvaliador(HistoricoAvaliacao $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . 'and cod_inscrito= \'%s\' and cod_trabalho= \'%s\' and cod_avaliador= \'%s\' and status=\'ATIVADA\' ',
            $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho(),$obj->getCodAvaliador());

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
    public function getUltimaAvaliacaoByIdTrabalho(HistoricoAvaliacao $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tha.* FROM tab_historico_avaliacao tha 
            INNER JOIN vw_ultima_avaliacao AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
            WHERE tha.cod_evento=\'%s\' and tha.cod_inscrito= \'%s\' and tha.cod_trabalho= \'%s\' and status=\'ATIVADA\' ',
            $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho());

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

    public function getByIdExiste(HistoricoAvaliacao $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito, cod_trabalho FROM '.$this->tableName.' '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' AND cod_trabalho= \'%s\' AND cod_avaliacao= \'%s\' '
            , $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho(),$obj->getCodAvaliacao());

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
    public function carregarListaAvaliacoesByIdTrabalho(HistoricoAvaliacao $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM '.$this->tableName.' tt '
                    . 'WHERE tt.cod_evento=\'%s\' and tt.cod_trabalho=\'%s\' '
                    . 'order by tt.cod_avaliacao desc',$obj->getCodEvento(),$obj->getCodTrabalho());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliacoes($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaEstatisticaAvaliacaoByIdEvento(HistoricoAvaliacao $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT COUNT(ta.avaliacao) total, ta.avaliacao, ta.cod_evento '
                    . 'FROM tab_historico_avaliacao ta '
                    . ' INNER JOIN `vw_ultima_avaliacao` vua ON vua.cod_avaliacao=ta.cod_avaliacao '
                    . ' inner join tab_trabalhos tt on ta.cod_trabalho=tt.cod_trabalho and tt.status=\'ATIVADA\' '
                    . ' inner join tab_inscritos ti on ti.cod_inscrito=ta.cod_inscrito and ti.status=\'ATIVADA\' '
                    . 'WHERE ta.status=\'ATIVADA\' and ta.cod_evento=\'%s\' GROUP BY ta.avaliacao',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEstatisticaAvaliacaoByIdEvento($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(HistoricoAvaliacao $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_login']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
                        
            $sql = "SELECT * FROM ".$this->tableName." WHERE "
            . " cod_evento=".$obj->getCodEvento()." and cod_inscrito=0 and cod_trabalho=0 and cod_avaliacao=0"; 
            
            $rs = $this->conex->Execute($sql);     
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
//            $insertSQL = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodAvaliacao($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(HistoricoAvaliacao $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_login']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM ".$this->tableName." WHERE cod_evento = ".$obj->getCodEvento().""
            . " and cod_inscrito = ".$obj->getCodInscrito()." and cod_trabalho = ".$obj->getCodTrabalho()
            . " and cod_avaliacao = ".$obj->getCodAvaliacao(); 
            
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
//            $updateSQL = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($updateSQL);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function desativarAvaliacoesAvaliador(HistoricoAvaliacao $obj, $debug=false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_login']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('DESATIVADO');

            $sql = "UPDATE ".$this->tableName." SET status='DESATIVADO'
                    WHERE cod_evento = ".$obj->getCodEvento()." 
                        AND cod_inscrito = ".$obj->getCodInscrito()."
                        AND cod_trabalho = ".$obj->getCodTrabalho()." 
                        AND cod_avaliador = ".$obj->getCodAvaliador()." 
                        AND STATUS='ATIVADA'";
            
//            $rs = $this->conex->Execute($sql); 
//            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
//            $fws_ = $obj->getAllForArray($arrayColunas,false);
//            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
//            
//            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
//    public function desativarAvaliacoesAvaliador(HistoricoAvaliacao $obj, $debug=false) {
//        try {
//            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
//            $obj->setIdAlteracao($_SESSION['_login']);
//            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
//            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
//            $obj->setVersao($obj->getVersao()+1);
//            $obj->setStatus('DESATIVADO');
//            
//            $sql = "SELECT * FROM ".$this->tableName." WHERE cod_evento = ".$obj->getCodEvento().""
//            . " and cod_inscrito = ".$obj->getCodInscrito()." and cod_trabalho = ".$obj->getCodTrabalho()
//            . " and cod_avaliador = ".$obj->getCodAvaliador()." and status='ATIVADA' "; 
//            
//            $rs = $this->conex->Execute($sql); 
//            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
//            $fws_ = $obj->getAllForArray($arrayColunas,false);
//            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
//            
//            $sql = sprintf($updateSQL);
//            $this->conex->debug = $debug;
//            $rs = $this->conex->Execute($sql);
//            return $rs;
//            
//        } catch (ADODB_Exception $e) {
//            $this->enviarMensagem($e);
//            return false;
//        }
//    }    
//    public function delete(HistoricoAvaliacao $obj, $debug=false) {
//        try {
//            $sql = sprintf('DELETE FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
//            . ' and cod_inscrito= \'%s\' and cod_trabalho= \'%s\'', $obj->getCodEvento()
//            ,$obj->getCodInscrito(),$obj->getCodTrabalho(),$obj->getCodAvaliacao());
//            
//            $this->conex->debug = $debug;
//            
//            $rs = $this->conex->Execute($sql);
//            return true;
//        } catch (ADODB_Exception $e) {
//            $this->enviarMensagem($e);
//            return false;
//        }
//    }    
}
?>