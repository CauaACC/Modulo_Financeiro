<?php
class IsentoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Isento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_isentos_codigo tis
            LEFT JOIN tab_inscritos ti on tis.cpf=ti.cpf
            WHERE tis.cod_evento=\'%s\' and tis.cod_isento= \'%s\'', $obj->getCodEvento(),$obj->getCodIsento());

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
    public function getByCpf(Isento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_isentos_codigo tis
            LEFT JOIN tab_inscritos ti on tis.cpf=ti.cpf
            WHERE tis.cod_evento=\'%s\' and tis.cpf= \'%s\' and ti.status=\'ATIVADA\' ', $obj->getCodEvento(),$obj->getCpf());

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
    public function getByCpfPorPerfil(Isento $obj, $debug=false) {
        try{
            $wherePagamentos = " AND ((ti.cod_perfil in (select cod_perfil from tab_opcoes_perfis where perfil_isento=1 and cod_evento=".$obj->getCodEvento()."))
                or (ti.cod_inscrito IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip 
                    WHERE tlip.cod_perfil IN (select cod_perfil from tab_opcoes_perfis where perfil_isento=1 and cod_evento=".$obj->getCodEvento().")))) ";
            
            $sql = sprintf('SELECT ti.cod_inscrito as cod_inscricao, count(ti.cod_inscrito) as total_isencao FROM tab_inscritos ti 
            WHERE ti.cod_evento=\'%s\' and ti.cpf= \'%s\' and ti.status=\'ATIVADA\' group by ti.cod_inscrito '.$wherePagamentos, $obj->getCodEvento(),$obj->getCpf());

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
    public function getByCpfOld(Isento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tis.* FROM tab_isentos tis
            WHERE tis.cod_evento=\'%s\' and tis.cpf= \'%s\' ', $obj->getCodEvento(),$obj->getCpf());

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
            $sql = "SELECT ifnull(MAX(cod_isento),0)+1 FROM tab_isentos";

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
    public function getByIdExiste(Isento $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cpf FROM tab_isentos_codigo WHERE cod_evento=\'%s\' and cod_isento= \'%s\'', $obj->getCodEvento(),$obj->getCodIsento());

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
    public function getAll(Isento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_isentos_codigo tis
            LEFT JOIN tab_inscritos ti on tis.cpf=ti.cpf
            order by tis.nome_isento desc');

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
    public function carregarListaIsentoByIdEvento(Isento $obj, $condicoes=false, $debug=false) {
        try{
            switch ($condicoes['ordenar']) {
                case 'nome_isento':
                    $orderBy = " order by tis.nome_isento ";
                    break;
                case 'cpf':
                    $orderBy = " order by tis.cpf";
                    break;
                default:
                    $orderBy = " order by tis.cod_isento desc ";
                    break;
            }
            
            $sql = sprintf('SELECT tis.*, ti.cod_inscrito, ti.cod_inscrito as cod_inscricao FROM tab_isentos_codigo tis
            LEFT JOIN tab_inscritos ti on (tis.cpf=ti.cpf)  and ti.cpf<>\'\' and ti.status=\'ATIVADA\' and tis.cod_evento=ti.cod_evento
            WHERE tis.cod_evento=\'%s\' 
            '.$orderBy,$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setListaPerfil('');
                    $obj->setAll($rows);
                    try {
                        $obj->carregarListaPerfil();
                    } catch (Exception $exc) {}
                    $return[] = clone $obj;
                }
                $obj->setListaIsento($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Isento $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM tab_isentos_codigo WHERE cod_evento=".$obj->getCodEvento()." and cod_isento=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_isentos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodIsento($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insertCodigo(Isento $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM tab_isentos_codigo WHERE cod_evento=".$obj->getCodEvento()." and cod_isento=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_isentos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodIsento($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Isento $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM tab_isentos_codigo WHERE cod_evento = ".$obj->getCodEvento()." and cod_isento = ".$obj->getCodIsento(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_isentos");
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
    public function delete(Isento $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_isentos_codigo WHERE cod_evento=\'%s\' and cod_isento= \'%s\'', $obj->getCodEvento(),$obj->getCodIsento());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function deleteCodigoIsento(Isento $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_isentos_codigo WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
