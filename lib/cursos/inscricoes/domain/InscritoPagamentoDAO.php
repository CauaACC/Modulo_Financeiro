<?php
class InscritoPagamentoDAO extends DAO{

    public function __construct() { 
        $this->tableName = "tab_lig_inscritos_oficinas";
        parent::__construct();
    }  
    public function getById(InscritoPagamento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . 'and cod_inscrito= \'%s\' and cod_oficina= \'%s\'', $obj->getCodEvento(),$obj->getCodInscritoPagamento());

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

    public function getByIdExiste(InscritoPagamento $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito, cod_oficina FROM '.$this->tableName.' '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' AND cod_oficina= \'%s\' '
            , $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodOficina());

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
    public function carregarListaOficinasByIdInscrito(InscritoPagamento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_opcoes_oficinas too '
                    . 'INNER JOIN tab_lig_inscritos_oficinas tlio on tlio.cod_oficina=too.cod_oficina '
                    . 'WHERE tlio.cod_evento=\'%s\' and tlio.cod_inscrito=\'%s\' '
                    . 'order by too.titulo',$obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaOficinas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getAll(InscritoPagamento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE '
            . ' cod_evento='.$obj->getCodEvento().' order by cod_evento desc');

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
    public function insert(InscritoPagamento $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
                        
            $sql = "SELECT * FROM $this->tableName WHERE "
            . " cod_evento=".$obj->getCodEvento()." and cod_inscrito=0 and cod_oficina=0"; 
            
            $rs = $this->conex->Execute($sql);     
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodInscritoPagamento($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(InscritoPagamento $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM '.$this->tableName.' WHERE cod_evento = ".$obj->getCodEvento().""
            . " and cod_inscrito = ".$obj->getCodInscrito()." and cod_oficina = ".$obj->getCodOficina(); 
            
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
    public function delete(InscritoPagamento $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . ' and cod_inscrito= \'%s\' and cod_oficina= \'%s\'', $obj->getCodEvento()
            ,$obj->getCodInscrito(),$obj->getCodOficina());
            
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