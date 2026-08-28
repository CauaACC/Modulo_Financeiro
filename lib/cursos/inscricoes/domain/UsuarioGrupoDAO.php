<?php
class UsuarioGrupoDAO extends DAO{

    public function __construct() {        
        parent::__construct();
    }  
    public function getById(UsuarioGrupo $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM sis_ligacao_usuarios_grupos WHERE cod_evento=\'%s\' '
            . ' and cod_usuario= \'%s\' and cod_grupo = \'%s\''
            . '', $obj->getCodEvento(),$obj->getCodUsuario(),$obj->getCodGrupo());

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

    public function insert(UsuarioGrupo $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
            $sql = "SELECT * FROM sis_ligacao_usuarios_grupos WHERE cod_evento=".$obj->getCodEvento()." and cod_usuario=0  and cod_grupo=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("sis_ligacao_usuarios_grupos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodUsuarioGrupo($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(UsuarioGrupo $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM sis_ligacao_usuarios_grupos WHERE cod_evento=\'%s\''
            . ' and cod_usuario= \'%s\' and cod_grupo= \'%s\'', $obj->getCodEvento(),$obj->getCodUsuario(),$obj->getCodGrupo());
            
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