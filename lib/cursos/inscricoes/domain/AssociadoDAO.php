<?php
class AssociadoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getByCpf(Associado $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito as cod_inscricao FROM tab_inscritos_aptos_carga tiac
            LEFT JOIN tab_inscritos ti on tiac.cpf=ti.cpf AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            WHERE tiac.cod_evento=\'%s\' and tiac.cpf= \'%s\'', $obj->getCodEvento(),$obj->getCpf());

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
    public function getById(Associado $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito as cod_inscricao FROM tab_inscritos_aptos_carga tiac
            LEFT JOIN tab_inscritos ti on tiac.cpf=ti.cpf AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            WHERE tiac.cod_evento=\'%s\' and tiac.cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());

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
            $sql = "SELECT ifnull(MAX(cod_inscrito),0)+1 FROM tab_inscritos_aptos_carga";

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
    public function getByIdExiste(Associado $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito FROM tab_inscritos_aptos_carga WHERE cod_evento=\'%s\'
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
    public function getAll(Associado $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito as cod_inscricao FROM tab_inscritos_aptos_carga tiac
            LEFT JOIN tab_inscritos ti on tiac.cpf=ti.cpf AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            order by cod_evento desc ');

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
    public function carregarListaAssociadosByIdEvento(Associado $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito as cod_inscricao FROM tab_inscritos_aptos_carga tiac
            LEFT JOIN tab_inscritos ti on tiac.cpf=ti.cpf AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            WHERE tiac.cod_evento=\'%s\'
            order by nome_inscrito',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAssociados($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(Associado $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM tab_inscritos_aptos_carga WHERE cod_evento=".$obj->getCodEvento()." and cod_inscrito=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_inscritos_aptos_carga");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodInscrito($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Associado $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM tab_inscritos_aptos_carga WHERE cod_evento = ".$obj->getCodEvento()." and cod_inscrito = ".$obj->getCodInscrito(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_inscritos_aptos_carga");
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
    public function delete(Associado $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_inscritos_aptos_carga WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
