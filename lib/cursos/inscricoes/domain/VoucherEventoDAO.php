<?php
class VoucherEventoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(VoucherEvento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_vouchers_evento WHERE cod_evento=\'%s\''
                    . ' and cod_voucher_evento= \'%s\' and status=\'ATIVADA\' ', $obj->getCodEvento(),$obj->getCodVoucherEvento());

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
            $sql = "SELECT ifnull(MAX(cod_voucher_evento),0)+1 FROM tab_vouchers_evento";

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
    public function getByIdExiste(VoucherEvento $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_voucher_evento FROM tab_vouchers_evento WHERE cod_evento=\'%s\' and status=\'ATIVADA\' '
                    . 'and cod_voucher_evento= \'%s\'', $obj->getCodEvento(),$obj->getCodVoucherEvento());

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
    public function getAll(VoucherEvento $obj, $debug=false,$limit = false) {
        try{
//            $sql = sprintf('SELECT * FROM tab_vouchers_evento  where 311 <=id and id <=319  order by cod_voucher_evento');
            $sql = sprintf('SELECT * FROM tab_vouchers_evento  where cod_evento=\'%s\' and status=\'ATIVADA\' '
                    . 'order by cod_voucher_evento desc', $obj->getCodEvento());
            
            $sql = sprintf('SELECT ve.*, vu.total_utilizado
            FROM tab_vouchers_evento ve 
            LEFT JOIN (SELECT COUNT(cod_voucher) total_utilizado, cod_evento, cod_voucher_evento FROM tab_vouchers WHERE (STATUS<>\'DESATIVADO\' OR STATUS IS NULL) 
            AND cod_voucher IN (SELECT cod_voucher FROM tab_lig_inscritos_vouchers) GROUP BY cod_voucher_evento, cod_evento) AS vu ON vu.cod_evento=ve.cod_evento AND vu.cod_voucher_evento=ve.cod_voucher_evento
            WHERE ve.cod_evento=\'%s\' AND ve.status=\'ATIVADA\' ORDER BY ve.cod_voucher_evento DESC', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaVoucherEventos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }            

    public function insert(VoucherEvento $obj, $debug=false) {
        try {           
            $obj->setCreatedAt(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM tab_vouchers_evento WHERE cod_evento=".$obj->getCodEvento()." "
                    . "and cod_voucher_evento=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_vouchers_evento");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            // $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodVoucherEvento($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(VoucherEvento $obj, $debug=false) {
        try {           
            $obj->setUpdatedAt(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_vouchers_evento WHERE cod_evento = ".$obj->getCodEvento()." "
                    . "and cod_voucher_evento = ".$obj->getCodVoucherEvento(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_vouchers_evento");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
            // $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($updateSQL);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function delete(VoucherEvento $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_vouchers_evento WHERE cod_evento=\'%s\' '
                    . 'and cod_voucher_evento= \'%s\'', $obj->getCodEvento(),$obj->getCodVoucherEvento());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function desativar(VoucherEvento $obj, $debug=false) {
        try {
            $sql = sprintf('UPDATE tab_vouchers_evento SET status=\'DESATIVADA\' WHERE cod_evento=\'%s\' '
                    . 'and cod_voucher_evento= \'%s\'', $obj->getCodEvento(),$obj->getCodVoucherEvento());
            $sql2 = sprintf('UPDATE tab_vouchers SET status=\'DESATIVADA\' WHERE cod_evento=\'%s\' '
                    . 'and cod_voucher_evento= \'%s\'', $obj->getCodEvento(),$obj->getCodVoucherEvento());
            
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
