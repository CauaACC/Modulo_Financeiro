<?php
class InscritoVoucherDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function inscritoPossuiVoucher(InscritoVoucher $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT liv.* FROM tab_lig_inscritos_vouchers liv
            inner join tab_vouchers tv on tv.cod_voucher=liv.cod_voucher and tv.status=\'ATIVADA\'
            WHERE liv.cod_evento=\'%s\' and liv.cod_inscrito= \'%s\' and liv.status=\'ATIVADA\''
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
    public function voucherUtilizado(InscritoVoucher $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT liv.* FROM tab_lig_inscritos_vouchers liv
            inner join tab_vouchers tv on tv.cod_voucher=liv.cod_voucher and tv.status=\'ATIVADA\'
            WHERE liv.cod_evento=\'%s\' and liv.cod_voucher= \'%s\''
            , $obj->getCodEvento(),$obj->getCodVoucher());

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
    public function getById(InscritoVoucher $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT liv.* FROM tab_lig_inscritos_vouchers liv
            inner join tab_vouchers tv on tv.cod_voucher=liv.cod_voucher and tv.status=\'ATIVADA\'
            WHERE liv.cod_evento=\'%s\' and liv.cod_voucher= \'%s\' and cod_inscrito= \'%s\''
            , $obj->getCodEvento(),$obj->getCodVoucher(),$obj->getCodInscrito());

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

    public function getByIdExiste(InscritoVoucher $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT liv.* FROM tab_lig_inscritos_vouchers liv
            inner join tab_vouchers tv on tv.cod_voucher=liv.cod_voucher and tv.status=\'ATIVADA\'
            WHERE liv.cod_evento=\'%s\' and liv.cod_voucher= \'%s\' and cod_inscrito= \'%s\''
            , $obj->getCodEvento(),$obj->getCodVoucher(),$obj->getCodInscrito());

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
    public function getListaVoucherByIdInscrito(InscritoVoucher $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT lp.*, op.titulo FROM tab_lig_inscritos_vouchers lp
                inner join tab_opcoes_perfis op on op.cod_voucher=lp.cod_voucher
            WHERE lp.cod_evento=\'%s\' and lp.cod_inscrito=\'%s\''
            , $obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaVoucher($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       

    public function insert(InscritoVoucher $obj, $debug=false) {
        try {           
            $obj->setStatus('ATIVADA');
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
            $sql = "SELECT * FROM tab_lig_inscritos_vouchers 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_voucher=0  and cod_inscrito=0"; 
            
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_lig_inscritos_vouchers");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
           
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(InscritoVoucher $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_lig_inscritos_vouchers 
            WHERE cod_evento = ".$obj->getCodEvento()." and cod_inscrito = ".$obj->getCodInscrito()." and cod_voucher = '".$obj->getCodVoucher()."'"; 
            
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_lig_inscritos_vouchers");
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
    public function delete(InscritoVoucher $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_lig_inscritos_vouchers 
            WHERE cod_evento=\'%s\' and cod_voucher= \'%s\' and cod_inscrito= \'%s\''
            , $obj->getCodEvento(),$obj->getCodVoucher(),$obj->getCodInscrito());
            
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