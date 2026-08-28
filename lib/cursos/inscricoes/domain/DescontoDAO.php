<?php
class DescontoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function carregarDescontoTotal(Desconto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT d.*, COUNT(*) AS qtd_desconto
                , SUM(porcentagem_desconto) AS porcentagem_total_desconto
                , SUM(valor_desconto) AS valor_total_desconto
                , min(valor_fixo) AS valor_fixo_minimo
            FROM tab_descontos d WHERE d.cod_evento=\'%s\' and d.cpf= \'%s\' and d.status=\'ATIVADA\'
            group by d.cpf', $obj->getCodEvento(),$obj->getCpf());

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
    public function getById(Desconto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_descontos tis
            LEFT JOIN tab_inscritos ti on tis.cpf=ti.cpf
            WHERE tis.cod_evento=\'%s\' and tis.cod_desconto= \'%s\'', $obj->getCodEvento(),$obj->getCodDesconto());

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
    public function getByCpf(Desconto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_descontos tis
            LEFT JOIN tab_inscritos ti on tis.cpf=ti.cpf
            WHERE tis.cod_evento=\'%s\' and tis.cpf= \'%s\'', $obj->getCodEvento(),$obj->getCpf());

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
            $sql = "SELECT ifnull(MAX(cod_desconto),0)+1 FROM tab_descontos";

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
    public function getByIdExiste(Desconto $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cpf FROM tab_descontos WHERE cod_evento=\'%s\' and cod_desconto= \'%s\'', $obj->getCodEvento(),$obj->getCodDesconto());

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
    public function getAll(Desconto $obj, $debug=false) {
        try{
            
//            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_descontos tis
//            LEFT JOIN tab_inscritos ti on tis.cpf=ti.cpf
//            order by tis.nome_desconto desc');
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito as cod_inscricao, ti.nome_inscrito as nome_participante FROM tab_descontos tiac
            LEFT JOIN tab_inscritos ti on tiac.cpf=ti.cpf AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            order by nome_inscrito desc ');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaDescontos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaDescontoByIdEvento(Desconto $obj, $condicoes=false, $debug=false) {
        try{
            switch ($condicoes['ordenar']) {
                case 'nome_desconto':
                    $orderBy = " order by tiac.nome_inscrito ";
                    break;
                case 'cpf':
                    $orderBy = " order by tiac.cpf";
                    break;
                default:
                    $orderBy = " order by tiac.cod_desconto desc ";
                    break;
            }
            
//            $sql = sprintf('SELECT tis.*, ti.cod_inscrito, ti.cod_inscrito as cod_inscricao FROM tab_descontos tis
//            LEFT JOIN tab_inscritos ti on (tis.cpf=ti.cpf)  and ti.cpf<>\'\' and ti.status=\'ATIVADA\' and tis.cod_evento=ti.cod_evento
//            WHERE tis.cod_evento=\'%s\' 
//            '.$orderBy,$obj->getCodEvento());
// , ti.nome_inscrito as nome_participante
            
            $sql = sprintf('SELECT tiac.*,tiac.nome_inscrito, ti.cod_inscrito as cod_inscricao
                , CONCAT("R$ ",tp.valor," - ",tp.forma_pagamento) as desc_pagamento FROM tab_descontos tiac
            LEFT JOIN tab_inscritos ti on tiac.cpf=ti.cpf AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            LEFT JOIN tab_pagamentos tp on tp.cod_inscrito=ti.cod_inscrito AND tp.status=\'ATIVADA\' AND ti.cod_evento=tp.cod_evento
            WHERE tiac.cod_evento=\'%s\'
            '.$orderBy.' ',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaDescontos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Desconto $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM tab_descontos WHERE cod_evento=".$obj->getCodEvento()." and cod_desconto=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_descontos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodDesconto($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Desconto $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM tab_descontos WHERE cod_evento = ".$obj->getCodEvento()." and cod_desconto = ".$obj->getCodDesconto(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_descontos");
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
    public function delete(Desconto $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_descontos WHERE cod_evento=\'%s\' and cod_desconto= \'%s\'', $obj->getCodEvento(),$obj->getCodDesconto());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
