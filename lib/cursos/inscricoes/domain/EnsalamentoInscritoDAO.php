<?php
class EnsalamentoInscritoDAO extends DAO{

    public function __construct() {
        parent::__construct();
    }

    /**
     * Carrega a linha existente do ensalamento do inscrito no evento (para decidir insert x update).
     * Faz setAll da linha completa (SELECT * puro, sem joins -> seguro) para que o update
     * preserve dat_incl/res_incl/versao. A entidade reaplica os campos novos apos este carregamento.
     */
    public function getByInscrito(EnsalamentoInscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_ensalamento_inscrito '
                    . 'WHERE cod_evento=\'%s\' and cod_inscrito=\'%s\'',
                    $obj->getCodEvento(), $obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs) or ($rs->EOF)) {
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

    public function insert(EnsalamentoInscrito $obj, $debug=false) {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM tab_ensalamento_inscrito WHERE cod_evento=".$obj->getCodEvento()." and cod_ensalamento_inscrito=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("tab_ensalamento_inscrito");
            $fws_ = $obj->getAllForArray($arrayColunas,false);

            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodEnsalamentoInscrito($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(EnsalamentoInscrito $obj, $debug=false) {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM tab_ensalamento_inscrito WHERE cod_evento = ".$obj->getCodEvento()." and cod_ensalamento_inscrito = ".$obj->getCodEnsalamentoInscrito();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("tab_ensalamento_inscrito");
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

    public function carregarListaByEvento(EnsalamentoInscrito $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT ei.*, ti.nome_inscrito, ti.cpf as cpf_inscrito, ti.e_mail '
                    . 'FROM tab_ensalamento_inscrito ei '
                    . 'INNER JOIN tab_inscritos ti on ti.cod_inscrito=ei.cod_inscrito and ti.cod_evento=ei.cod_evento '
                    . 'WHERE ei.cod_evento=\'%s\' and ei.status=\'ATIVADA\' '
                    . 'order by ei.sala, ei.dia, ei.hora, ti.nome_inscrito', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs) or ($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEnsalamentoInscrito($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
?>
