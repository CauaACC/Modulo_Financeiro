<?php
class CertificadoEventoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(CertificadoEvento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_certificados_evento WHERE cod_evento=\'%s\''
                    . ' and cod_certificado_evento= \'%s\' and status=\'ATIVADA\' ', $obj->getCodEvento(),$obj->getCodCertificadoEvento());

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
            $sql = "SELECT ifnull(MAX(cod_certificado_evento),0)+1 FROM tab_certificados_evento";

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
    public function getByIdExiste(CertificadoEvento $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_certificado_evento FROM tab_certificados_evento WHERE cod_evento=\'%s\' and status=\'ATIVADA\' '
                    . 'and cod_certificado_evento= \'%s\'', $obj->getCodEvento(),$obj->getCodCertificadoEvento());

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
    public function getAll(CertificadoEvento $obj, $debug=false,$limit = false) {
        try{
//            $sql = sprintf('SELECT * FROM tab_certificados_evento  where 311 <=id and id <=319  order by cod_certificado_evento');
            $sql = sprintf('SELECT * FROM tab_certificados_evento  where cod_evento=\'%s\' and status=\'ATIVADA\' '
                    . 'order by cod_certificado_evento desc', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaCertificadoEventos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }            

    public function insert(CertificadoEvento $obj, $debug=false) {
        try {           
            $obj->setCreatedAt(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM tab_certificados_evento WHERE cod_evento=".$obj->getCodEvento()." "
                    . "and cod_certificado_evento=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_certificados_evento");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodCertificadoEvento($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(CertificadoEvento $obj, $debug=false) {
        try {           
            $obj->setUpdatedAt(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_certificados_evento WHERE cod_evento = ".$obj->getCodEvento()." "
                    . "and cod_certificado_evento = ".$obj->getCodCertificadoEvento(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_certificados_evento");
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
    public function delete(CertificadoEvento $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_certificados_evento WHERE cod_evento=\'%s\' '
                    . 'and cod_certificado_evento= \'%s\'', $obj->getCodEvento(),$obj->getCodCertificadoEvento());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function desativar(CertificadoEvento $obj, $debug=false) {
        try {
            $sql = sprintf('UPDATE tab_certificados_evento SET status=\'DESATIVADA\' WHERE cod_evento=\'%s\' '
                    . 'and cod_certificado_evento= \'%s\'', $obj->getCodEvento(),$obj->getCodCertificadoEvento());
            $sql2 = sprintf('UPDATE tab_certificados SET status=\'DESATIVADA\' WHERE cod_evento=\'%s\' '
                    . 'and cod_certificado_evento= \'%s\'', $obj->getCodEvento(),$obj->getCodCertificadoEvento());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            $rs = $this->conex->Execute($sql2);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
