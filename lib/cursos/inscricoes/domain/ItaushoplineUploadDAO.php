<?php
class ItaushoplineUploadDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(ItaushoplineUpload $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM mod_fin_itaushopline_arquivo
            WHERE cod_evento=\'%s\' and cod_itaushopline_arquivo= \'%s\''
            , $obj->getCodEvento(),$obj->getCodItaushoplineArquivo());

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
    public function getLastId(ItaushoplineUpload $obj, $condicao=false, $debug=false) {
        try {
            $sql = sprintf('SELECT IFNULL(MAX(cod_itaushopline_arquivo),0)+1 FROM mod_fin_itaushopline_arquivo
            WHERE cod_evento=\'%s\'', $obj->getCodEvento());
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                //return $rows['0'];
                $obj->setCodItaushoplineArquivo($rows['0']);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getByIdExiste(ItaushoplineUpload $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM mod_fin_itaushopline_arquivo
            WHERE cod_itaushopline_arquivo= \'%s\' and cod_evento=\'%s\''
            ,$obj->getCodItaushoplineArquivo(), $obj->getCodEvento());

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
    public function getAll(ItaushoplineUpload $obj, $debug=false) {
        try{
            
            $sql = sprintf("SELECT * FROM mod_fin_itaushopline_arquivo WHERE cod_evento=\'%s\' order by cod_curso desc", $obj->getCodEvento());

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
    public function carregarListaItaushoplineArquivosByIdEvento(ItaushoplineUpload $obj,$debug=false) {
        try{
            
            $sql = sprintf('SELECT itauA.*, (SELECT IFNULL(COUNT(*),0) FROM mod_fin_itaushopline_retorno
                WHERE cod_arquivo_upload=itauA.cod_itaushopline_arquivo
                GROUP BY cod_arquivo_upload) as total_boletos_carregados FROM mod_fin_itaushopline_arquivo itauA
            WHERE itauA.cod_evento=\'%s\' order by itauA.cod_itaushopline_arquivo desc',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaItaushoplineArquivos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
    public function insert(ItaushoplineUpload $obj, $debug=false) {
        try {           
            $obj->setTipoArquivo('Itaushopline');
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
            $sql = "SELECT * FROM mod_fin_itaushopline_arquivo
            WHERE cod_evento=".$obj->getCodEvento()." and cod_itaushopline_arquivo=0"; 
            
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("mod_fin_itaushopline_arquivo");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            
            //$obj->setCodItaushoplineArquivo($this->conex->Insert_ID()); // criado m�todo GetLastId em substitui��o, para pegar o c�digo arquivo para compor o nome do mesmo nos uploads.
            
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(ItaushoplineUpload $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM mod_fin_itaushopline_arquivo
            WHERE cod_evento = ".$obj->getCodEvento()." and cod_itaushopline_arquivo = ".$obj->getCodItaushoplineArquivo(); 
            
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("mod_fin_itaushopline_arquivo");
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
    public function delete(ItaushoplineUpload $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM mod_fin_itaushopline_arquivo
            WHERE cod_evento=\'%s\' and cod_itaushopline_arquivo= \'%s\''
            , $obj->getCodEvento(),$obj->getCodItaushoplineArquivo());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
