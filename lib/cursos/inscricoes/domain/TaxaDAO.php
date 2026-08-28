<?php
class TaxaDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Taxa $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_taxas WHERE cod_evento=\'%s\' '
                    . 'and cod_taxa= \'%s\'', $obj->getCodEvento(),$obj->getCodTaxa());

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
            $sql = "SELECT ifnull(MAX(cod_taxa),0)+1 FROM tab_opcoes_taxas";

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
    public function getByIdExiste(Taxa $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_taxa FROM tab_opcoes_taxas WHERE cod_evento=\'%s\' and cod_taxa= \'%s\'', $obj->getCodEvento(),$obj->getCodTaxa());

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
    public function getAll(Taxa $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_taxas order by cod_evento desc');

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
    public function carregarListaTaxasByIdEvento(Taxa $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_opcoes_taxas '
                    . ' WHERE cod_evento=\'%s\' '
                    . $whereSql
                    . ' order by ordem, titulo',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTaxas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaTaxas(Taxa $obj, $condicoes=false, $debug=false) {
        try{
            $whereSql = "";
            $codPerfil = $obj->getCodPerfil();
            if($obj->getCodPerfil()!='' and $codPerfil!='Array'){
                $whereSql = " and ot.cod_perfil=".$obj->getCodPerfil()." ";
            }
            $sql = sprintf('SELECT op.titulo as titulo_perfil, ot.* FROM tab_opcoes_taxas ot inner join tab_opcoes_perfis op on op.cod_perfil=ot.cod_perfil '
                    . ' WHERE ot.cod_evento=\'%s\' and ot.status=\'ATIVADA\' '
                    . $whereSql
                    . ' order by ot.ordem, ot.titulo',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTaxas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaTaxasBy(Taxa $obj, $condicoes, $orderby=false, $debug=false) {
        try{
            $whereSql = "";
            if($obj->getCodPerfil()){
                $whereSql .= " and cod_perfil = ".$obj->getCodPerfil();
            }
            if (is_array($condicoes)){
                if($condicoes['data_hoje']){
                    $whereSql .= " and ('".$condicoes['data_hoje']."' 
                        BETWEEN periodo_inicial AND periodo_final) ";
                }
                if($condicoes['periodo_inicial']){
                    $whereSql .= " and periodo_inicial  ".$condicoes['periodo_inicial'];
                }
                if($condicoes['periodo_final']){
                    $whereSql .= " and periodo_final = ".$condicoes['periodo_final'];
                }
            }    
            if($orderby){
                $orderby = ' order by periodo_inicial ';
            }else{
                $orderby = ' order by ordem, titulo ';
            }
            $sql = sprintf('SELECT * FROM tab_opcoes_taxas '
                    . ' WHERE cod_evento=\'%s\' '
                    . $whereSql
                    . $orderby
                    . ' ',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTaxas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaTaxasByMensalidade(Taxa $obj, $condicoes, $orderby=false, $debug=false) {
        try{
            $whereSql = "";
            if($obj->getCodPerfil()){
                $whereSql .= " and cod_perfil = ".$obj->getCodPerfil();
            }
            if (is_array($condicoes)){
                if($condicoes['data_hoje']){
                    $whereSql .= " and ('".$condicoes['data_hoje']."' 
                        BETWEEN periodo_inicial AND periodo_final) ";
                }
                if($condicoes['periodo_inicial']){
                    $whereSql .= " and periodo_inicial  ".$condicoes['periodo_inicial'];
                }
                if($condicoes['periodo_final']){
                    $whereSql .= " and periodo_final = ".$condicoes['periodo_final'];
                }
            }    

            /** @todo inserir tipo para mensalidade*/
            $whereSql .= " and titulo = 'Mensalidade' and status='D' ";

            if($orderby){
                $orderby = ' order by periodo_inicial ';
            }else{
                $orderby = ' order by ordem, titulo ';
            }
            $sql = sprintf('SELECT * FROM tab_opcoes_taxas '
                    . ' WHERE cod_evento=\'%s\' '
                    . $whereSql
                    . $orderby
                    . ' ',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTaxas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(Taxa $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_opcoes_taxas WHERE cod_evento=".$obj->getCodEvento()." and cod_taxa=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_taxas");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodTaxa($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Taxa $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_opcoes_taxas WHERE cod_evento = ".$obj->getCodEvento()." and cod_taxa = ".$obj->getCodTaxa(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_taxas");
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
    public function delete(Taxa $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_opcoes_taxas WHERE cod_evento=\'%s\' and cod_taxa= \'%s\'', $obj->getCodEvento(),$obj->getCodTaxa());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function duplicar(Taxa $obj, $debug=false) {
        try{
            
            $sql = sprintf('INSERT INTO tab_opcoes_taxas(cod_evento,cod_perfil,cod_oficina,seq_itaushopline,titulo
                ,ordem,status,tx_unica_a_vista,tx_unica_a_vista_valor
                ,tx_unica_1_parcela,tx_unica_2_parcela,tx_unica_3_parcela,tx_unica_4_parcela,tx_unica_5_parcela,tx_unica_6_parcela
                ,tx_comunidade_interna_a_vista
                ,tx_comunidade_interna_1_parcela,tx_comunidade_interna_2_parcela,tx_comunidade_interna_3_parcela,tx_comunidade_interna_4_parcela
                ,tx_comunidade_externa_a_vista,tx_comunidade_externa_1_parcela,tx_comunidade_externa_2_parcela,tx_comunidade_externa_3_parcela,tx_comunidade_externa_4_parcela
                ,periodo_inicial,periodo_final,max_data_vencimento,dias_vencimento
                ,valor_desconto,data_desconto,valor_desconto_1,data_desconto_1,valor_desconto_2,data_desconto_2
                )
                SELECT cod_evento,cod_perfil,cod_oficina,seq_itaushopline,titulo
                ,ordem,status,tx_unica_a_vista,tx_unica_a_vista_valor
                ,tx_unica_1_parcela,tx_unica_2_parcela,tx_unica_3_parcela,tx_unica_4_parcela,tx_unica_5_parcela,tx_unica_6_parcela
                ,tx_comunidade_interna_a_vista
                ,tx_comunidade_interna_1_parcela,tx_comunidade_interna_2_parcela,tx_comunidade_interna_3_parcela,tx_comunidade_interna_4_parcela
                ,tx_comunidade_externa_a_vista,tx_comunidade_externa_1_parcela,tx_comunidade_externa_2_parcela,tx_comunidade_externa_3_parcela,tx_comunidade_externa_4_parcela
                ,periodo_inicial,periodo_final,max_data_vencimento,dias_vencimento
                ,valor_desconto,data_desconto,valor_desconto_1,data_desconto_1,valor_desconto_2,data_desconto_2
                FROM tab_opcoes_taxas WHERE cod_evento=\'%s\' and cod_taxa= \'%s\'', $obj->getCodEvento(),$obj->getCodTaxa());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodTaxa($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function importarTaxas(Taxa $obj, $debug=false) {
        try{
            
            $sql = sprintf("INSERT INTO tab_opcoes_taxas (cod_evento, cod_perfil, titulo, tx_unica_a_vista_valor)
                SELECT ".$obj->getCodEvento()." AS cod_evento,    
                    p_novo.cod_perfil,     
                    ta.titulo,            
                    ta.tx_unica_a_vista_valor  
                FROM
                    tab_opcoes_perfis p_antigo
                JOIN tab_opcoes_perfis p_novo
                    ON p_novo.cod_evento = ".$obj->getCodEvento()."
                    AND p_novo.titulo = CONCAT('Cópia de ', p_antigo.titulo)
                JOIN tab_opcoes_taxas ta
                    ON ta.cod_evento = p_antigo.cod_evento
                AND ta.cod_perfil = p_antigo.cod_perfil
                WHERE
                    p_antigo.cod_evento = %s AND p_antigo.status='ATIVADA' AND ta.tx_unica_a_vista_valor<>'0.00' "
                     , $obj->getCodEventoAnt());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}
