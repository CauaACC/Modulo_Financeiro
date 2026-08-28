<?php
class ArquivoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getByIdExiste(Arquivo $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_arquivo '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivo());

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
    public function getById(Arquivo $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_arquivo '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivo());

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
    public function getByNomeExiste(Arquivo $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_arquivo '
            . 'WHERE cod_evento=\'%s\' and arquivo_upload= \'%s\'', $obj->getCodEvento(),$obj->getArquivoUpload());

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
            $sql = "SELECT ifnull(MAX(cod_arquivo),0)+1 FROM tab_arquivo";

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
    public function getByIdTrabalhoExiste(Arquivo $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_arquivo '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' and cod_trabalho= \'%s\' '
            . '', $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function getAll(Arquivo $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_arquivo order by cod_evento desc');

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
    public function carregarListaArquivoByIdEvento(Arquivo $obj, $debug=false) {
        try{
            $whereSql = "";
            if($obj->getTipoArquivo()!=''){
                $whereSql = " and ta.tipo_arquivo = '".$obj->getTipoArquivo()."' ";
            }
            if($obj->getArrayTipoArquivo()!=''){
                $arrayTipoArquivo = $obj->getArrayTipoArquivo();
                $arrayTipoArquivo = "('" . implode("','", $arrayTipoArquivo) . "')";
                $whereSql = " and ta.tipo_arquivo in ".$arrayTipoArquivo." ";
            }
            $sql = sprintf('SELECT ta.*, op.titulo as nome_perfil FROM tab_arquivo ta '
                    . ' left join tab_opcoes_perfis op on op.cod_perfil = ta.cod_perfil '
                    . ' WHERE ta.cod_evento=\'%s\' AND ta.status=\'ATIVADA\' '
                    . $whereSql
                    . ' ORDER BY ta.cod_arquivo DESC'
                    . '',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaArquivo($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaArquivoCargaProjetosPgByIdEvento(Arquivo $obj, $debug=false) {
        try{
            $whereSql = "";
            // if($obj->getTipoArquivo()!=''){
                $whereSql = " and (ta.tipo_arquivo = 'CARGA_TRANSPARENCIA_PROJETOS' or ta.tipo_arquivo = 'CARGA_TRANSPARENCIA_PAGAMENTOS') ";
            // }
            // if($obj->getArrayTipoArquivo()!=''){
            //     $arrayTipoArquivo = $obj->getArrayTipoArquivo();
            //     $arrayTipoArquivo = "('" . implode("','", $arrayTipoArquivo) . "')";
            //     $whereSql = " and ta.tipo_arquivo in ".$arrayTipoArquivo." ";
            // }
            $sql = sprintf('SELECT ta.*, op.titulo as nome_perfil FROM tab_arquivo ta '
                    . ' left join tab_opcoes_perfis op on op.cod_perfil = ta.cod_perfil '
                    . ' WHERE ta.cod_evento=\'%s\' AND ta.status=\'ATIVADA\' '
                    . $whereSql
                    . ' ORDER BY ta.cod_arquivo DESC'
                    . '',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaArquivo($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaCargaTransparenciaFiltrada(Arquivo $obj, $filtros=array(), $debug=false) {
        try{
            $whereSql = " AND (ta.tipo_arquivo = 'CARGA_TRANSPARENCIA_PROJETOS' OR ta.tipo_arquivo = 'CARGA_TRANSPARENCIA_PAGAMENTOS') ";

            if(!empty($filtros['tipoFiltro'])){
                $whereSql = " AND ta.tipo_arquivo = '".addslashes($filtros['tipoFiltro'])."' ";
            }
            if(!empty($filtros['gestorFiltro'])){
                $whereSql .= " AND ta.gestor = '".addslashes($filtros['gestorFiltro'])."' ";
            }
            if(!empty($filtros['dataDe'])){
                $whereSql .= " AND ta.data_inclusao >= '".addslashes($filtros['dataDe'])." 00:00:00' ";
            }
            if(!empty($filtros['dataAte'])){
                $whereSql .= " AND ta.data_inclusao <= '".addslashes($filtros['dataAte'])." 23:59:59' ";
            }
            if(!empty($filtros['statusFiltro'])){
                if($filtros['statusFiltro'] == 'AGUARDANDO'){
                    $whereSql .= " AND (ta.status_carga IS NULL OR ta.status_carga = '') ";
                } else {
                    $whereSql .= " AND ta.status_carga = '".addslashes($filtros['statusFiltro'])."' ";
                }
            }
            if(!empty($filtros['nomeArquivoFiltro'])){
                $whereSql .= " AND ta.arquivo_upload LIKE '%".addslashes($filtros['nomeArquivoFiltro'])."%' ";
            }

            $sql = 'SELECT ta.*, op.titulo as nome_perfil, ev.titulo as nome_evento, ev.gestor as gestor_evento'
                    . ' FROM tab_arquivo ta'
                    . ' LEFT JOIN tab_opcoes_perfis op ON op.cod_perfil = ta.cod_perfil'
                    . ' LEFT JOIN tab_eventos ev ON ev.cod_evento = ta.cod_evento'
                    . ' WHERE ta.status=\'ATIVADA\''
                    . $whereSql
                    . ' ORDER BY ta.cod_arquivo DESC';

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaArquivo($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarArquivoOficinaAssCoordenadorBy(Arquivo $obj, $condicoes, $debug=false) {
        try{
            $whereSql = " and cod_oficina = ".$obj->getCodOficina();
            
            $sql = sprintf('SELECT * FROM tab_arquivo '
            . ' WHERE cod_evento=\'%s\' AND status=\'ATIVADA\'  AND tipo_arquivo=\'ASSINATURA-COORDENADOR\' '
            . $whereSql
            . ' order by versao desc',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function carregarArquivoOficinaProgramaBy(Arquivo $obj, $condicoes, $debug=false) {
        try{
            $whereSql = " and cod_oficina = ".$obj->getCodOficina();

            $sql = sprintf('SELECT * FROM tab_arquivo '
            . ' WHERE cod_evento=\'%s\' AND status=\'ATIVADA\' AND tipo_arquivo=\'PROGRAMA\' '
            . $whereSql
            . ' order by versao desc',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function insert(Arquivo $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessionIdInclusao($_SESSION['_cod_inscrito']);
            $obj->setSessionIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            if($obj->getVersao()==''){
                $obj->setVersao(1);
            }
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM tab_arquivo "
            . "WHERE cod_evento=".$obj->getCodEvento()." and cod_arquivo=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_arquivo");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodArquivo($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Arquivo $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessionIdAlteracao($_SESSION['_cod_inscrito']);
            $obj->setSessionIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_arquivo"
            . " WHERE cod_evento = ".$obj->getCodEvento()." and cod_arquivo = ".$obj->getCodArquivo(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_arquivo");
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
    public function getByCodArquivo(Arquivo $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_arquivo WHERE cod_arquivo= \'%s\'', $obj->getCodArquivo());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {}
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(Arquivo $obj, $debug=false) {
        try {
            // Tentar primeiro com cod_evento (padrao)
            $sql = sprintf('DELETE FROM tab_arquivo '
            . 'WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivo());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            // Se nenhuma linha afetada, tentar sem cod_evento (registros de transparencia podem ter cod_evento diferente)
            if ($this->conex->Affected_Rows() == 0) {
                $sql = sprintf('DELETE FROM tab_arquivo WHERE cod_arquivo= \'%s\'', $obj->getCodArquivo());
                $this->conex->debug = $debug;
                $rs = $this->conex->Execute($sql);
            }

            return ($this->conex->Affected_Rows() > 0);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function desativar(Arquivo $obj, $debug=false) {
        try {
            $sql = sprintf('UPDATE tab_arquivo SET status=\'DESATIVADA\' ' 
            . 'WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\'', $obj->getCodEvento(),$obj->getCodArquivo());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
