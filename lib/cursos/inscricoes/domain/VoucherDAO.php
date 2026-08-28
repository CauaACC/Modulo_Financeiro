<?php
class VoucherDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Voucher $obj, $debug=false) {
        try{
            
            $where = '';
            if($obj->getStatus()=='ATIVADA'){
                $where = " AND (status='ATIVADA' OR status is NULL) ";
            }
            $sql = sprintf('SELECT * FROM tab_vouchers WHERE cod_evento=\'%s\' and cod_voucher= \'%s\' '.$where.' ', $obj->getCodEvento(),$obj->getCodVoucher());

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
    public function testeQtdMaximaBySiglaCategoria(Voucher $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tve.max_utilizacao, tu.total_utilizado
                FROM tab_vouchers_evento tve
                INNER JOIN (SELECT COUNT(cod_voucher_evento) AS total_utilizado, cod_evento, cod_voucher_evento 
                        FROM vw_voucher_utilizado GROUP BY cod_voucher_evento, cod_evento) AS tu ON tu.cod_voucher_evento=tve.cod_voucher_evento
                WHERE tve.cod_voucher_evento = (SELECT cod_voucher_evento FROM tab_vouchers 
                                                WHERE cod_evento=\'%s\' and cod_voucher= \'%s\')', $obj->getCodEvento(),$obj->getCodVoucher());

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
            $sql = "SELECT ifnull(MAX(cod_voucher),0)+1 FROM tab_vouchers";

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
    public function getLastSequenciaBySigla($obj, $debug=false) {
        try {
            $sql = 'SELECT ifnull(MAX(sequencia_interna),0)+1 FROM tab_vouchers '
                    . 'WHERE cod_evento=\''.$obj->getCodEvento().'\' and sigla=\''.$obj->getSigla().'\' ';

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
    public function getByIdExiste(Voucher $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT id FROM tab_vouchers WHERE cod_evento=\'%s\' and cod_voucher= \'%s\'', $obj->getCodEvento(),$obj->getCodVoucher());

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
    public function getAll(Voucher $obj, $debug=false,$limit = false) {
        try{
            $whereVoucherEvento = "";
//            if($obj->getCodVoucherEvento()){
                $whereVoucherEvento = " AND cod_voucher_evento = ".$obj->getCodVoucherEvento()."";
//            }
            //$sql = sprintf('SELECT * FROM tab_vouchers  where cod_evento=\'%s\'  order by cod_voucher', $obj->getCodEvento());
            if($limit!=FALSE){
                $limit = explode(",", $limit);
                $sql = sprintf('SELECT * FROM tab_vouchers where '
                        . '  cod_evento=\'%s\''
                        . ' '.$whereVoucherEvento
                        . ' and '.$limit[0].' <=sequencia_interna and sequencia_interna <='.$limit[1]
                        . ' AND status=\'ATIVADA\' '
                        . ' order by sigla,sequencia_interna', $obj->getCodEvento());
            }else{
                throw new Exception("É necessário definir um intervalo de impressão definido!");
            }

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaVouchers($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function getListaVouchersByCodVoucherEvento(Voucher $obj, $debug=false,$limit = false) {
        try{
            $whereLimit = '';
            if($limit!=false){
                $whereLimit = ' and sequencia_interna>='.$limit['inicial'].' and sequencia_interna<='.$limit['final'].' ';
            }
//            $whereNaoUsado = '';
//            if($naoUsado!=false){
                $whereNaoUsado = ' and cod_voucher not in (select cod_voucher from tab_lig_inscritos_vouchers where cod_evento='.$obj->getCodEvento().') ';
//            }
            $sql = sprintf('SELECT * FROM tab_vouchers  where cod_evento=\'%s\' and cod_voucher_evento=\'%s\' and (status<>\'DESATIVADO\' OR status is null) '
                    . $whereLimit
                    . $whereNaoUsado
                    . ' order by sequencia_interna', $obj->getCodEvento(), $obj->getCodVoucherEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaVouchers($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function getListaVouchersByCodEvento(Voucher $obj, $debug=false,$limit = false) {
        try{

            //$sql = sprintf('SELECT * FROM tab_vouchers  where cod_evento=\'%s\'  order by sequencia_interna limit 50', $obj->getCodEvento());
            $sql = sprintf('SELECT * FROM tab_vouchers  where cod_evento=\'%s\' '
                    . 'and sequencia_interna>=101 and sequencia_interna<=200 '
                    . 'order by sequencia_interna', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaVouchers($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function getListaPatrocinadoresVoucherByIdEvento(Voucher $obj, $debug=false,$limit = false) {
        try{
            $sql = sprintf('SELECT COUNT(tv.patrocinador) total_gerado, liv.total_utilizado, tv.patrocinador, tv.cod_evento 
                            FROM tab_vouchers tv
                            LEFT JOIN (SELECT COUNT(liv.cod_voucher) total_utilizado, tv.patrocinador,liv.cod_evento FROM tab_lig_inscritos_vouchers liv
                                       INNER JOIN tab_vouchers AS tv ON tv.cod_voucher=liv.cod_voucher
                                       GROUP BY tv.patrocinador,tv.cod_evento) AS liv
                            ON liv.patrocinador=tv.patrocinador AND liv.cod_evento=tv.cod_evento
                            WHERE tv.cod_evento=\'%s\' and (tv.status=\'ATIVADA\' OR tv.status is null) GROUP BY tv.patrocinador ', $obj->getCodEvento());
            $this->conex->debug = $debug;
            // $this->conex->cacheSecs = 10;
            // $rs = $this->conex->cacheExecute($sql);
            $rs = $this->conex->CacheExecute(3600, $sql);
            //$rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPatrocinadoresVoucherByIdEvento($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       

    public function insert(Voucher $obj, $debug=false) {
        try {           
            $obj->setCreatedAt(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM tab_vouchers WHERE cod_evento=".$obj->getCodEvento()." and cod_voucher=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_vouchers");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            // $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodVoucher($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Voucher $obj, $debug=false) {
        try {           
            $obj->setUpdatedAt(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_vouchers WHERE cod_evento = ".$obj->getCodEvento()." and cod_voucher = ".$obj->getCodVoucher(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_vouchers");
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
    public function delete(Voucher $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_vouchers WHERE cod_evento=\'%s\' and cod_voucher= \'%s\'', $obj->getCodEvento(),$obj->getCodVoucher());
            
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