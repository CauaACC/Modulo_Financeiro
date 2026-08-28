<?php
class ItaushoplineRetornoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(ItaushoplineRetorno $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_pag_itaushopline_retorno
            WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\''
            , $obj->getCodEvento(),$obj->getCodArquivo());

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
    public function getLastId(ItaushoplineRetorno $obj, $condicao=false, $debug=false) {
        try {
            $sql = sprintf('SELECT IFNULL(MAX(cod_arquivo),0)+1 FROM tab_pag_itaushopline_retorno
            WHERE cod_evento=\'%s\'', $obj->getCodEvento());
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                //return $rows['0'];
                $obj->setCodArquivo($rows['0']);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getByIdExiste(ItaushoplineRetorno $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM tab_pag_itaushopline_retorno
            WHERE cod_arquivo= \'%s\' and cod_evento=\'%s\''
            ,$obj->getCodArquivo(), $obj->getCodEvento());

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
    public function getAll(ItaushoplineRetorno $obj, $debug=false) {
        try{
            
            $sql = sprintf("SELECT * FROM tab_pag_itaushopline_retorno WHERE cod_evento=\'%s\' order by cod_curso desc", $obj->getCodEvento());

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
    public function carregarListaPagamentosItaushoplineByIdInscrito(ItaushoplineRetorno $obj,$debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM consulta_pagamento_itaushopline 
            WHERE cod_evento=\'%s\' AND cod_inscrito=\'%s\'',$obj->getCodEvento(),$obj->getCodInscrito());
            
            $this->conex->debug = $debug;
            $this->conex->cacheSecs = 3600; //3600*24; // cache 24 hours 
            $rs = $this->conex->Execute($sql);
//            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPagamentosItaushopline($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaItaushoplineArquivosByIdEvento(ItaushoplineRetorno $obj,$debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_pag_itaushopline_retorno 
            WHERE cod_evento=\'%s\' order by cod_arquivo desc',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $this->conex->cacheSecs = 3600; //3600*24; // cache 24 hours 
            $rs = $this->conex->Execute($sql);
//            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaItaushoplineArquivos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaEventosArquivosByIdArquivo(ItaushoplineRetorno $obj,$debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM consulta_pagamento_itaushopline_por_arquivo 
            WHERE cod_arquivo_upload=\'%s\'',$obj->getCodArquivoUpload());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEventosArquivos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaEventosArquivosByIdEvento(ItaushoplineRetorno $obj,$debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM consulta_pagamento_itaushopline_por_arquivo 
            WHERE cod_evento=\'%s\'',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $this->conex->cacheSecs = 3600; //3600*24; // cache 24 hours 
            $rs = $this->conex->Execute($sql);
//            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEventosArquivos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insertCargaRetorno($sqlInsert, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sqlInsert);

            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Model para salvar lista de objeto, sem gerar sql no controller
     */
    public function salvarListaRetorno($obj, $debug=false) {
        $obj->setDatIncl(date('Y-m-d H:i:s'));
        $obj->setResIncl($_SESSION['_cod_usuario']);
        $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
        $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
        $obj->setVersao(1);
        try{
            if (count($obj->getListaRetorno())>=1) {
                $listaRetorno = $obj->getListaRetorno();
                $sqlInsert = "INSERT INTO tab_pag_itaushopline_retorno (
                        tipo_registro ,cod_inscricao ,numero_inscricao ,cb_agencia ,cb_conta ,cb_dac ,uso_empresa 
                        ,numero_carteira ,nosso_numero ,dac_nosso_numero ,cod_carteira ,cod_ocorrencia ,data_ocorrencia 
                        ,n_documento ,confirmacao_nosso_numero ,vencimento ,valor_titulo ,codigo_banco ,ag_cobradora 
                        ,ag_cobradora_dac ,especie ,tarifa_cobranca ,valor_iof ,valor_abatimento ,descontos ,valor_principal 
                        ,juros_mora_multa ,outros_creditos ,boleto_dda ,data_credito ,instr_cancelada ,nome_pagador ,erros 
                        ,cod_inscrito, cod_liquidacao ,numero_sequencial 
                        ,cod_arquivo_upload 
                        ,res_incl ,dat_incl ,ip_incl ,versao ,http_user_agent_incl)
                    VALUES ";
               $i=0;
                foreach ($listaRetorno as $objL) {
                    $sqlInsert .= ($i!=0)? "," : "";
                    $i++;
                    $sqlInsert .= "('".$objL->getTipoRegistro()."', '".$objL->getCodInscricao()."', '".$objL->getNumeroInscricao()."', '".$objL->getCbAgencia()."','".$objL->getCbConta()."','".$objL->getCbDac()."','".$objL->getUsoEmpresa()."'"
                        . ",'".$objL->getNumeroCarteira()."','".$objL->getNossoNumero()."','".$objL->getDacNossoNumero()."','".$objL->getCodCarteira()."','".$objL->getCodOcorrencia()."','".$objL->getDataOcorrencia()."'"
                        . ",'".$objL->getNDocumento()."','".$objL->getConfirmacaoNossoNumero()."','".$objL->getVencimento()."','".$objL->getValorTitulo()."','".$objL->getCodigoBanco()."','".$objL->getAgCobradora()."'"
                        . ",'".$objL->getAgCobradoraDac()."','".$objL->getEspecie()."','".$objL->getTarifaCobranca()."','".$objL->getValorIof()."','".$objL->getValorAbatimento()."','".$objL->getDescontos()."','".$objL->getValorPrincipal()."'"
                        . ",'".$objL->getJurosMoraMulta()."','".$objL->getOutrosCreditos()."','".$objL->getBoletoDda()."','".$objL->getDataCredito()."','".$objL->getInstrCancelada()."','".$objL->getNomePagador()."','".$objL->getErros()."'"
                        . ",'".$objL->getCodInscrito()."','".$objL->getCodLiquidacao()."','".$objL->getNumeroSequencial()."'"
                        . ",'".$objL->getCodArquivo()."'"
                        . ",'".$obj->getResIncl()."','".$obj->getDatIncl()."','".$obj->getIpIncl()."','".$obj->getVersao()."','".$obj->getHttpUserAgentIncl()."')"
                        . "";                    
                }
            }

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sqlInsert);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(ItaushoplineRetorno $obj, $debug=false) {
        try {           
            $obj->setTipoArquivo('Itaushopline');
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
            $sql = "SELECT * FROM tab_pag_itaushopline_retorno
            WHERE cod_evento=".$obj->getCodEvento()." and cod_arquivo=0"; 
            
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_pag_itaushopline_retorno");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            
            //$obj->setCodArquivo($this->conex->Insert_ID()); // criado m�todo GetLastId em substitui��o, para pegar o c�digo arquivo para compor o nome do mesmo nos uploads.
            
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(ItaushoplineRetorno $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_pag_itaushopline_retorno
            WHERE cod_evento = ".$obj->getCodEvento()." and cod_arquivo = ".$obj->getCodArquivo(); 
            
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_pag_itaushopline_retorno");
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
    public function deleteListaRetorno(ItaushoplineRetorno $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_pag_itaushopline_retorno
            WHERE cod_arquivo_upload= \'%s\''
            ,$obj->getCodArquivoUpload());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function delete(ItaushoplineRetorno $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_pag_itaushopline_retorno
            WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\''
            , $obj->getCodEvento(),$obj->getCodArquivo());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
