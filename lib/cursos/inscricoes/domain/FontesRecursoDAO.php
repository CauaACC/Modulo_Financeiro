<?php
class FontesRecursoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(FontesRecurso $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT ffr.* FROM fin_fontes_recurso ffr
            WHERE ffr.cod_evento=\'%s\' and ffr.cod_fontes_recurso= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodFontesRecurso());

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
            $sql = "SELECT ifnull(MAX(cod_fontes_recurso),0)+1 FROM fin_fontes_recurso";

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
    public function getByIdExiste(FontesRecurso $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT ffr.* FROM fin_fontes_recurso ffr 
            WHERE ffr.cod_evento=\'%s\' and ffr.cod_fontes_recurso= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodFontesRecurso());

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
    public function getAll(FontesRecurso $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT ffr.* FROM fin_fontes_recurso ffr
            WHERE ffr.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaFontesRecursos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaFontesRecursoByIdEvento(FontesRecurso $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT ffr.* FROM fin_fontes_recurso ffr
            WHERE ffr.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaFontesRecurso($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(FontesRecurso $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM fin_fontes_recurso 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_fontes_recurso=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("fin_fontes_recurso");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodFontesRecurso($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(FontesRecurso $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM fin_fontes_recurso WHERE cod_evento = ".$obj->getCodEvento()." and cod_fontes_recurso = ".$obj->getCodFontesRecurso(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("fin_fontes_recurso");
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
    public function delete(FontesRecurso $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM fin_fontes_recurso WHERE cod_evento=\'%s\' and cod_fontes_recurso= \'%s\'', $obj->getCodEvento(),$obj->getCodFontesRecurso());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }        
}
