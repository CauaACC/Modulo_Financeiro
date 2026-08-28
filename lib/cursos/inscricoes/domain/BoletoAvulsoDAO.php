<?php
class BoletoAvulsoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(BoletoAvulso $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tb.*, ti.* FROM tab_boletos tb
            INNER JOIN tab_inscritos ti on ti.cod_inscrito=tb.cod_inscrito and ti.cod_evento=tb.cod_evento
            WHERE tb.cod_evento=\'%s\' and tb.cod_inscrito= \'%s\' and tb.cod_boleto= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodBoleto());

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
    public function getByIdPedido(BoletoAvulso $obj, $debug=false) {
        try{
            
            $pedido = numberPad($obj->getPedido(), 7);
            $sql = sprintf('SELECT tb.*, ti.* FROM tab_boletos tb
            INNER JOIN tab_inscritos ti on ti.cod_inscrito=tb.cod_inscrito and ti.cod_evento=tb.cod_evento
            WHERE tb.cod_evento=\'%s\' and tb.cod_inscrito= \'%s\' and LEFT(tb.numero_documento, LENGTH(tb.numero_documento) - 3) =\'%s\' ORDER BY tb.cod_boleto DESC LIMIT 1;', $obj->getCodEvento(),$obj->getCodInscrito(),$pedido);

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
            $sql = "SELECT ifnull(MAX(cod_inscrito),0)+1 FROM tab_boletos";

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
    public function getByIdExiste(BoletoAvulso $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito, cod_boleto FROM tab_boletos WHERE cod_evento=\'%s\'
            and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());

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
    public function getAll(BoletoAvulso $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tb.*, ti.* FROM tab_boletos tb
            INNER JOIN tab_inscritos ti on ti.cod_inscrito=tb.cod_inscrito AND tb.cod_evento=ti.cod_evento
            WHERE tb.cod_evento = '.$obj->getCodEvento().'
            order by tb.cod_boleto desc ');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaBoletosAvulsos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaBoletosAvulsosByIdInscrito(BoletoAvulso $obj, $condicao,$debug=false) {
        try{
            $whereSomenteExtra = "";
            if($condicao['somenteBoletoExtra']){   
                $whereSomenteExtra = " and tb.extra=1 ";
            }
            $whereSomenteNaoQuitado = "";
            if($condicao['somenteNaoQuitado']){   
                $whereSomenteNaoQuitado = " and tb.flg_pagamento is null ";
            }
            
            $sql = sprintf('SELECT tb.*, ti.* FROM tab_boletos tb
            INNER JOIN tab_inscritos ti on ti.cod_inscrito=tb.cod_inscrito and tb.cod_evento=ti.cod_evento
            WHERE tb.cod_evento = '.$obj->getCodEvento().' and tb.cod_inscrito='.$obj->getCodInscrito().'
                ' .$whereSomenteExtra. ' 
                ' .$whereSomenteNaoQuitado. ' 
                and (tb.status is null OR tb.status="ATIVADA")
            order by tb.cod_boleto desc ');


            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaBoletosAvulsos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(BoletoAvulso $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            $this->conex->debug = $debug;
                        
            $sql = "SELECT * FROM tab_boletos WHERE cod_evento=".$obj->getCodEvento()." and cod_boleto=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_boletos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            // $sql = sprintf($insertSQL);
            $sql = $insertSQL;
            
            $rs = $this->conex->Execute($sql);
            $obj->setCodBoleto($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(BoletoAvulso $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM tab_boletos WHERE cod_evento = ".$obj->getCodEvento()." and cod_boleto = ".$obj->getCodBoleto(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_boletos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
            $sql = $updateSQL;
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function delete(BoletoAvulso $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_boletos WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' and cod_boleto= \'%s\''
                    , $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodBoleto());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function desativar(BoletoAvulso $obj, $debug=false) {
        try {
            $sql = sprintf('UPDATE tab_boletos SET status=\'DESATIVADA\' WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' and cod_boleto= \'%s\''
                    , $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodBoleto());
            
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