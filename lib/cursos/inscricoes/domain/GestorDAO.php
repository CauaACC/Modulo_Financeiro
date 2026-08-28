<?php
class GestorDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Gestor $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM sis_gestor WHERE cod_gestor=\'%s\'', $obj->getCodGestor());

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
            $sql = "SELECT ifnull(MAX(cod_gestor),0)+1 FROM sis_gestor";

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
    public function getByIdExiste(Gestor $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT id FROM sis_gestor WHERE cod_gestor=\'%s\' ', $obj->getCodGestor());

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
    public function getAll(Gestor $obj, $debug=false,$limit = false) {
        try {
            $sql = sprintf('SELECT * FROM sis_gestor ');

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

    public function getListaGestoresByIdUsuario(Gestor $obj, $debug=false) {
        return $this->getListaGestoresByCodUsuario($obj, $debug);
    }
    public function getListaGestoresByCodUsuario(Gestor $obj, $debug=false) {
        try{

            $sql = sprintf('SELECT g.* FROM sis_gestor g '
                    . ' inner join sis_ligacao_usuarios_gestor lg on lg.cod_gestor=g.cod_gestor'
                    . ' where lg.cod_usuario=\'%s\' and (g.status=\'ATIVADO\') '
                    . ' order by g.nome_gestor ', $obj->getCodUsuario());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                $total_registros = 0;
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $total_registros++;
                    $return[] = clone $obj;
                }
                $obj->setListaGestores($return);
                $obj->setCountListaGestores($total_registros);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    

    /**
     * E-mail do financeiro do gestor dono do evento.
     * O vinculo evento->gestor e por NOME (tab_eventos.gestor VARCHAR x
     * sis_gestor.nome_gestor), mesmo JOIN de GerenciarCatalogoContabilAC::resolverGestor().
     * @return string|null e-mail ou null quando nao ha gestor/e-mail cadastrado
     */
    public function getEmailFinanceiroByEvento($codEvento, $debug=false) {
        try {
            $sql = sprintf('SELECT g.email_financeiro FROM tab_eventos e '
                    . ' INNER JOIN sis_gestor g ON UPPER(g.nome_gestor) = UPPER(e.gestor) '
                    . ' WHERE e.cod_evento = %d LIMIT 1', intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return null;
            }
            $email = trim((string)$rs->fields['email_financeiro']);
            return $email !== '' ? $email : null;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return null;
        }
    }

    public function insert(Gestor $obj, $debug=false) {
        try {           
            $obj->setCreatedAt(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM sis_gestor WHERE cod_gestor=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("sis_gestor");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodGestor($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Gestor $obj, $debug=false) {
        try {           
            $obj->setUpdatedAt(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM sis_gestor WHERE cod_gestor = ".$obj->getCodGestor(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("sis_gestor");
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
    public function delete(Gestor $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM sis_gestor WHERE cod_gestor= \'%s\'', $obj->getCodGestor());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
