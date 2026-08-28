<?php
class EnsalamentoDAO extends DAO{

    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Ensalamento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_ensalamento_trabalho_sessao WHERE cod_evento=\'%s\' '
            . ' and cod_sessao= \'%s\' and cod_trabalho = \'%s\''
            . '', $obj->getCodEvento(),$obj->getCodSessao(),$obj->getCodTrabalho());

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

    public function insert(Ensalamento $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
            $sql = "SELECT * FROM tab_ensalamento_trabalho_sessao WHERE cod_evento=".$obj->getCodEvento()." and cod_sessao=0  and cod_trabalho=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_ensalamento_trabalho_sessao");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodEnsalamento($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(Ensalamento $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_ensalamento_trabalho_sessao WHERE cod_evento=\'%s\''
            . ' and cod_sessao= \'%s\' and cod_trabalho= \'%s\'', $obj->getCodEvento(),$obj->getCodSessao(),$obj->getCodTrabalho());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function carregarListaSalasByIdEvento(Ensalamento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM vw_ensalamento_sessao '
                    . 'WHERE cod_evento=\'%s\' ' 
                    . 'order by nome_sala, cod_sessao',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaSalas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
?>