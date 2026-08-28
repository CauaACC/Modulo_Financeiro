<?php
class HistoricoAvaliacaoPremiacaoDAO extends DAO{

    public function __construct() { 
        $this->tableName = "tab_historico_avaliacao_premiacao";
        parent::__construct();
    }  
    public function getById(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
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
    public function getByIdAvaliador(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
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
    public function getUltimaAvaliacaoByIdTrabalho(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tha.* FROM '.$this->tableName.' tha 
            INNER JOIN vw_ultima_avaliacao_premiacao_por_autor AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
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
    public function carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tha.*,su.cod_usuario,su.login,su.nome_usuario,su.status as status_avaliador '
            . ',(IFNULL(tha.criterio_1,0)+IFNULL(tha.criterio_2,0)+IFNULL(tha.criterio_3,0)+IFNULL(tha.criterio_4,0)+IFNULL(tha.criterio_5,0)+IFNULL(tha.criterio_6,0)) AS nota '
            . 'FROM '.$this->tableName.' tha 
            INNER JOIN vw_ultima_avaliacao_premiacao_por_autor AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
            left JOIN sis_usuarios su ON su.cod_usuario=tha.cod_avaliador
            left JOIN tab_convidados c ON c.cod_convidado=su.cod_usuario
            WHERE tha.cod_evento=\'%s\' and tha.cod_inscrito= \'%s\' and tha.cod_trabalho= \'%s\' and tha.status=\'ATIVADA\' ',
            $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaUltimasAvaliacaoPremiacao($return);
                return true;   
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
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
    public function carregarListaAvaliacoesByIdTrabalho(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
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
       
    public function insert(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
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
    public function update(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
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
    public function desativarAvaliacoesAvaliador(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
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
//    public function desativarAvaliacoesAvaliador(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
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
//    public function delete(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
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