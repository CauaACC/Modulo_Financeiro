<?php
class TrabalhoAvaliacaoPremiacaoDAO extends DAO{

    public function __construct() { 
        $this->tableName = "tab_trabalhos_avaliacao_premiacao";
        parent::__construct();
    }  
    public function getById(TrabalhoAvaliacaoPremiacao $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . 'and cod_inscrito= \'%s\' and cod_trabalho= \'%s\' and status=\'ATIVADA\' ',
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

    public function getByIdExiste(TrabalhoAvaliacaoPremiacao $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito, cod_trabalho FROM '.$this->tableName.' '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' AND cod_trabalho= \'%s\' and status=\'ATIVADA\' '
            , $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho());

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
    public function carregarListaAvaliacoesByIdTrabalho(TrabalhoAvaliacaoPremiacao $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM '.$this->tableName.' tt '
                    . 'WHERE tt.cod_evento=\'%s\' and tt.cod_trabalho=\'%s\' and tt.status=\'ATIVADA\' '
                    . 'order by tt.titulo',$obj->getCodEvento(),$obj->getCodTrabalho());

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
       
    public function insert(TrabalhoAvaliacaoPremiacao $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_login']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
                        
            $sql = "SELECT * FROM ".$this->tableName." WHERE "
            . " cod_evento=".$obj->getCodEvento()." and cod_inscrito=0 and cod_trabalho=0  and status='ATIVADA'"; 
            
            $rs = $this->conex->Execute($sql);     
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
//            $insertSQL = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodTrabalhoAvaliacao($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(TrabalhoAvaliacaoPremiacao $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_login']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM ".$this->tableName." WHERE cod_evento = ".$obj->getCodEvento()."  and status='ATIVADA' "
            . " and cod_inscrito = ".$obj->getCodInscrito()." and cod_trabalho = ".$obj->getCodTrabalho(); 
            
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
    public function delete(HistoricoAvaliacaoPremiacao $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . ' and cod_inscrito= \'%s\' and cod_trabalho= \'%s\'', $obj->getCodEvento()
            ,$obj->getCodInscrito(),$obj->getCodTrabalho());
            
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