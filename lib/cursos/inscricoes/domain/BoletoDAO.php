<?php
class BoletoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Boleto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos WHERE cod_evento=\'%s\' and cod_boleto= \'%s\'', $obj->getCodEvento(),$obj->getCodBoleto());

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
    public function getBoletoByNumeroDocumento(Boleto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos WHERE cod_evento=\'%s\' 
                and numero_documento= \'%s\'', $obj->getCodEvento(),$obj->getNumeroDocumento());

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
    public function getBoletoByPedido(Boleto $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_boletos WHERE cod_evento=\'%s\'
                and pedido_id= \'%s\' ORDER BY cod_boleto DESC LIMIT 1', $obj->getCodEvento(), $obj->getPedidoId());

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
    public function getNextSequenciaBoleto(Boleto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT  ifnull(count(cod_inscrito),0)+1  as next_sequencia_boleto
             FROM tab_boletos where cod_evento=\'%s\' and cod_inscrito= \'%s\' '
                , $obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                }
                return $rows['next_sequencia_boleto'];
                // return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getUltimoBoletoByIdInscrito(Boleto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos where
                cod_boleto = (SELECT max(cod_boleto) FROM tab_boletos WHERE (STATUS IS NULL or status=\'ATIVADA\') AND cod_evento=\'%s\' and cod_inscrito= \'%s\')'
                , $obj->getCodEvento(),$obj->getCodInscrito());

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
    public function getUltimoBoletoByIdInscritoValor(Boleto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos where
                cod_boleto = (SELECT max(cod_boleto) FROM tab_boletos WHERE (STATUS IS NULL or status=\'ATIVADA\') AND cod_evento=\'%s\' and cod_inscrito= \'%s\' and valor_boleto= \'%s\')'
                , $obj->getCodEvento(),$obj->getCodInscrito());

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
            $sql = "SELECT ifnull(MAX(cod_boleto),0)+1 FROM tab_boletos";

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
    public function getByIdExiste(Boleto $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_evento FROM tab_boletos WHERE cod_evento=\'%s\' 
                and numero_documento= \'%s\'', $obj->getCodEvento(),$obj->getNumeroDocumento());

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
    public function getAll(Boleto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_boletos order by cod_evento desc');

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
    public function carregarListaBoletosByIdEvento(Boleto $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_boletos '
                    . 'WHERE cod_evento=\'%s\' '
                    . 'order by cod_boleto',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaBoletos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaBoletosByCodInscrito(Boleto $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_boletos '
                    . 'WHERE cod_evento=\'%s\' and cod_inscrito = \'%s\' '
                    . 'order by cod_boleto',$obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaBoletos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Boleto $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionId($_COOKIE["PHPSESSID"]);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_boletos WHERE cod_evento=".$obj->getCodEvento()." and cod_boleto=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_boletos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = $insertSQL;
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodBoleto($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Boleto $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setSessionId($_COOKIE["PHPSESSID"]);
            $obj->setVersao(1);

            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_boletos WHERE cod_evento = ".$obj->getCodEvento()." and cod_boleto = ".$obj->getCodBoleto(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_boletos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
            // $sql = sprintf($updateSQL);
            $sql = $updateSQL;
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function delete(Boleto $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_boletos WHERE cod_evento=\'%s\' and cod_boleto= \'%s\'', $obj->getCodEvento(),$obj->getCodBoleto());
            
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