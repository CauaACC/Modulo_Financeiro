<?php
class BoletoRemessaDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(BoletoRemessa $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos_remessas WHERE cod_evento=\'%s\' and cod_boleto_remessa= \'%s\'', $obj->getCodEvento(),$obj->getCodBoletoRemessa());

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

    public function getLastId(BoletoRemessa $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT ifnull(MAX(cod_boleto_remessa),0)+1 FROM tab_boletos_remessas WHERE cod_evento=\'%s\'', $obj->getCodEvento());

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
    public function getLastArquivoId(BoletoRemessa $obj, $debug=false) {
        try {
            $sql = 'SELECT IFNULL(COUNT(cod_boleto_remessa),0)+1 AS seq_arquivo_dia 
            FROM tab_boletos_remessas WHERE cod_evento='.$obj->getCodEvento().' AND 
            DATE_FORMAT(dat_incl, \'%Y-%m-%d\') = STR_TO_DATE(CURDATE(), \'%Y-%m-%d\') ';

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
    public function getByIdExiste(BoletoRemessa $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_evento FROM tab_boletos_remessas WHERE cod_evento=\'%s\' 
                and cod_boleto_remessa = \'%s\'', $obj->getCodEvento(),$obj->getCodBoletoRemessa());

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
    public function getAll(BoletoRemessa $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos_remessas order by cod_evento desc');

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
    public function carregarListaBoletoRemessasByIdEvento(BoletoRemessa $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_boletos_remessas '
                    . 'WHERE cod_evento=\'%s\' '
                    . 'order by cod_boleto_remessa',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaBoletoRemessas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(BoletoRemessa $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionId($_COOKIE["PHPSESSID"]);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_boletos_remessas WHERE cod_evento=".$obj->getCodEvento()." and cod_boleto_remessa=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_boletos_remessas");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodBoletoRemessa($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(BoletoRemessa $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionId($_COOKIE["PHPSESSID"]);
            $obj->setVersao(1);

            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_boletos_remessas WHERE cod_evento = ".$obj->getCodEvento()." and cod_boleto_remessa = ".$obj->getCodBoletoRemessa(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_boletos_remessas");
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
    public function delete(BoletoRemessa $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_boletos_remessas WHERE cod_evento=\'%s\' and cod_boleto_remessa= \'%s\'', $obj->getCodEvento(),$obj->getCodBoletoRemessa());
            
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