<?php
class PrestacaoServicoDAO extends DAO{

    public function __construct() {
        parent::__construct();
    }
    public function carregarPrestacaoServicoTotal(PrestacaoServico $obj, $debug=false) {
        try{

            $sql = sprintf('SELECT * FROM tab_prestacao_servico WHERE cod_evento=\'%s\' '
                . 'and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());

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
    public function getById(PrestacaoServico $obj, $debug=false) {
        try{

            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_prestacao_servico tis
            LEFT JOIN tab_inscritos ti on tis.cod_evento=ti.cod_evento
            WHERE tis.cod_evento=\'%s\' and tis.cod_prestacao_servico= \'%s\'', $obj->getCodEvento(),$obj->getCodPrestacaoServico());

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
//    public function getByCpf(PrestacaoServico $obj, $debug=false) {
//        try{
//
//            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_prestacao_servico tis
//            LEFT JOIN tab_inscritos ti on tis.cpf=ti.cpf
//            WHERE tis.cod_evento=\'%s\' and tis.cpf= \'%s\'', $obj->getCodEvento(),$obj->getCpf());
//
//            $this->conex->debug = $debug;
//            $rs = $this->conex->Execute($sql);
//
//            if((!$rs)or($rs->EOF)) {
//                return false;
//            }else{
//                foreach($rs as $rows){
//                }
//                $obj->setAll($rows);
//                return true;
//            }
//        } catch (ADODB_Exception $e) {
//            $this->enviarMensagem($e);
//            return false;
//        }
//    }
    public function getLastId($condicao=false, $debug=false) {
        try {
            $sql = "SELECT ifnull(MAX(cod_prestacao_servico),0)+1 FROM tab_prestacao_servico";

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
    public function getByIdExiste(PrestacaoServico $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_prestacao_servico WHERE cod_evento=\'%s\' and cod_prestacao_servico= \'%s\'', $obj->getCodEvento(),$obj->getCodPrestacaoServico());

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
    public function getAll(PrestacaoServico $obj, $debug=false) {
        try{

//            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_prestacao_servico tis
//            LEFT JOIN tab_inscritos ti on tis.cpf=ti.cpf
//            order by tis.nome_prestacaoServico desc');
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito as cod_inscricao, ti.nome_inscrito as nome_participante FROM tab_prestacao_servico tiac');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPrestacaoServicos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaPrestacaoServicoByIdEvento(PrestacaoServico $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT tiac.* FROM tab_prestacao_servico tiac WHERE tiac.cod_evento=\'%s\' '.$orderBy.' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPrestacaoServicos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(PrestacaoServico $obj, $debug=false) {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');


            $sql = "SELECT * FROM tab_prestacao_servico WHERE cod_evento=".$obj->getCodEvento()." and cod_prestacao_servico=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("tab_prestacao_servico");
            $fws_ = $obj->getAllForArray($arrayColunas,false);

            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodPrestacaoServico($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(PrestacaoServico $obj, $debug=false) {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM tab_prestacao_servico WHERE cod_evento = ".$obj->getCodEvento()." and cod_prestacao_servico = ".$obj->getCodPrestacaoServico();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("tab_prestacao_servico");
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
    public function delete(PrestacaoServico $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_prestacao_servico WHERE cod_evento=\'%s\' and cod_prestacao_servico= \'%s\'', $obj->getCodEvento(),$obj->getCodPrestacaoServico());

            $this->conex->debug = $debug;

            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}