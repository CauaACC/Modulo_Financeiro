<?php
class UsuarioAreaTematicaDAO extends DAO{

    public function __construct() {        
        parent::__construct();
    }  
    public function getById(UsuarioAreaTematica $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_lig_convidados_areas_tematicas WHERE cod_evento=\'%s\' '
            . ' and cod_usuario= \'%s\' and cod_area_tematica = \'%s\''
            . '', $obj->getCodEvento(),$obj->getCodUsuario(),$obj->getCodAreaTematica());

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

    public function insert(UsuarioAreaTematica $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
            $sql = "SELECT * FROM tab_lig_convidados_areas_tematicas WHERE cod_evento=".$obj->getCodEvento()." and cod_usuario=0  and cod_area_tematica=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_lig_convidados_areas_tematicas");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodUsuarioAreaTematica($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(UsuarioAreaTematica $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_lig_convidados_areas_tematicas WHERE cod_evento=\'%s\''
            . ' and cod_usuario= \'%s\' and cod_area_tematica= \'%s\'', $obj->getCodEvento(),$obj->getCodUsuario(),$obj->getCodAreaTematica());
            
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