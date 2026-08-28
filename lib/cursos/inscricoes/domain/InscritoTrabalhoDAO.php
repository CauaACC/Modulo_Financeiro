<?php
class InscritoTrabalhoDAO extends DAO{

    public function __construct() { 
        $this->tableName = "tab_trabalhos";
        parent::__construct();
    }  
    public function getById(InscritoTrabalho $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tt.*, tha.avaliacao FROM '.$this->tableName.' tt '
            . ' left join (SELECT tha.* FROM tab_historico_avaliacao tha 
                    INNER JOIN (SELECT MAX(maxtha.cod_avaliacao) AS cod_avaliacao,maxtha.cod_trabalho 
                                    FROM tab_historico_avaliacao maxtha 
                                    WHERE maxtha.status=\'ATIVADA\' and maxtha.cod_evento='.$obj->getCodEvento().' 
                                    GROUP BY maxtha.cod_trabalho
                                ) AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
                    WHERE tha.status=\'ATIVADA\' 
                    ORDER BY tha.cod_trabalho, tha.cod_avaliacao
               ) as tha on tha.cod_trabalho=tt.cod_trabalho '
            . ' WHERE tt.cod_evento=\'%s\'  and tt.cod_inscrito= \'%s\' and tt.cod_trabalho= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho());

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

    public function getByIdExiste(InscritoTrabalho $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito, cod_trabalho FROM '.$this->tableName.' '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' AND cod_trabalho= \'%s\' '
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
    public function carregarListaTrabalhosByIdInscrito(InscritoTrabalho $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM '.$this->tableName.' tt '
                    . 'WHERE tt.cod_evento=\'%s\' and tt.cod_inscrito=\'%s\' '
                    . 'order by tt.titulo_trabalho',$obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTrabalhos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getAll(InscritoTrabalho $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE '
            . ' cod_evento='.$obj->getCodEvento().' order by titulo desc');

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
    public function insert(InscritoTrabalho $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
                        
            $sql = "SELECT * FROM $this->tableName WHERE "
            . " cod_evento=".$obj->getCodEvento()." and cod_inscrito=0 and cod_trabalho=0"; 
            
            $rs = $this->conex->Execute($sql);     
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodInscritoTrabalho($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(InscritoTrabalho $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM '.$this->tableName.' WHERE cod_evento = ".$obj->getCodEvento().""
            . " and cod_inscrito = ".$obj->getCodInscrito()." and cod_trabalho = ".$obj->getCodTrabalho(); 
            
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
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
    public function delete(InscritoTrabalho $obj, $debug=false) {
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