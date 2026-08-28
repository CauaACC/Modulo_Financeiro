<?php
class ComunicadoEnvioDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(ComunicadoEnvio $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_comunicados_envio '
            . 'WHERE cod_evento=\'%s\' and cod_comunicado_envio= \'%s\'', $obj->getCodEvento(),$obj->getCodComunicadoEnvio());

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
            $sql = "SELECT ifnull(MAX(cod_comunicado_envio),0)+1 FROM tab_comunicados_envio";

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
    public function getByIdExiste(ComunicadoEnvio $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_comunicado_envio FROM tab_comunicados_envio '
                    . 'WHERE cod_evento=\'%s\' and cod_comunicado_envio= \'%s\'', $obj->getCodEvento(),$obj->getCodComunicadoEnvio());

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
    public function getEmailEnviado(ComunicadoEnvio $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_comunicado_envio FROM tab_comunicados_envio '
            . 'WHERE cod_evento=\'%s\' and tipo= \'%s\' and cod_destinatario= \'%s\' and destinatario_email= \'%s\''
            , $obj->getCodEvento(),$obj->getTipo(),$obj->getCodDestinatario(),$obj->getDestinatarioEmail());

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
    public function getAll(ComunicadoEnvio $obj, $debug=false,$limit = false) {
        try{

            $sql = sprintf('SELECT * FROM tab_comunicados_envio  where cod_evento='.COD_EVENTO.' order by cod_comunicado_envio');
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaComunicadoEnvios($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }            

    public function insert(ComunicadoEnvio $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_comunicados_envio WHERE cod_evento=".$obj->getCodEvento()." and cod_comunicado_envio=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_comunicados_envio");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodComunicadoEnvio($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(ComunicadoEnvio $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_comunicados_envio "
            . "WHERE cod_evento = ".$obj->getCodEvento()." and cod_comunicado_envio = ".$obj->getCodComunicadoEnvio(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_comunicados_envio");
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
    public function delete(ComunicadoEnvio $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_comunicados_envio '
            . 'WHERE cod_evento=\'%s\' and cod_comunicado_envio= \'%s\'', $obj->getCodEvento(),$obj->getCodComunicadoEnvio());
            
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