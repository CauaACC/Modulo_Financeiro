<?php
class PlanoContasDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(PlanoContas $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT fpc.* FROM fin_plano_contas fpc
            WHERE fpc.cod_evento=\'%s\' and fpc.cod_plano_contas= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodPlanoContas());

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
            $sql = "SELECT ifnull(MAX(cod_plano_contas),0)+1 FROM fin_plano_contas";

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
    public function getByIdExiste(PlanoContas $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT fpc.* FROM fin_plano_contas fpc 
            WHERE fpc.cod_evento=\'%s\' and fpc.cod_plano_contas= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodPlanoContas());

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
    public function getAll(PlanoContas $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT fpc.* FROM fin_plano_contas fpc
            WHERE fpc.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPlanoContass($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaPlanoContasByIdEvento(PlanoContas $obj, $condicoes=false, $debug=false) {
        try{
            $wherePlano = '';
            if ($condicoes['natureza_saldo']=='DEBITO'){
                $wherePlano =  ' AND fpc.natureza_saldo = \'DEBITO\' AND fpc.aceita_lancamento = 1 ';
            }
            if ($condicoes['natureza_saldo']=='CREDITO'){
                $wherePlano =  ' AND fpc.natureza_saldo = \'CREDITO\' AND fpc.aceita_lancamento = 1 ';
            }
            $sql = sprintf('SELECT fpc.*, (SELECT COUNT(cod_plano_contas_pai) AS total_filho FROM fin_plano_contas WHERE cod_plano_contas_pai = fpc.cod_plano_contas) AS total_filho FROM fin_plano_contas fpc
            LEFT JOIN sis_gestor AS sg ON sg.cod_gestor = fpc.cod_gestor
            WHERE fpc.cod_evento=\'%s\' '.$wherePlano.'
            ORDER BY codigo_contabil 
            ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPlanoContas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaPlanoContasByIdEventoDebito(PlanoContas $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT fpc.* FROM fin_plano_contas fpc
            LEFT JOIN sis_gestor AS sg ON sg.cod_gestor = fpc.cod_gestor
            WHERE fpc.cod_evento=\'%s\' AND fpc.natureza_saldo = \'DEBITO\' AND fpc.aceita_lancamento = 1
            ORDER BY codigo_contabil 
            ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPlanoContas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaPlanoContasByIdEventoCredito(PlanoContas $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT fpc.* FROM fin_plano_contas fpc
            LEFT JOIN sis_gestor AS sg ON sg.cod_gestor = fpc.cod_gestor
            WHERE fpc.cod_evento=\'%s\' AND fpc.natureza_saldo = \'CREDITO\' AND fpc.aceita_lancamento = 1
            ORDER BY codigo_contabil 
            ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPlanoContas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaPlanoContasByIdEventoPai(PlanoContas $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT fpc.* FROM fin_plano_contas fpc
            LEFT JOIN sis_gestor AS sg ON sg.cod_gestor = fpc.cod_gestor
            WHERE fpc.cod_evento=\'%s\'
            ORDER BY codigo_contabil 
            ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPlanoContas($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function gerarCodigoContabil(PlanoContas $obj, $debug = false) {
        try {

            $this->conex->debug = $debug;

            // ==========================
            // CONTA RAIZ
            // ==========================
            if ($obj->getCodPlanoContasPai() == null) {

                $sql = "
                    SELECT MAX(CAST(codigo_contabil AS UNSIGNED))
                    FROM fin_plano_contas
                    WHERE cod_plano_contas_pai IS NULL
                ";

                $ultimoCodigo = $this->conex->GetOne($sql);
                $codigoContabil = ($ultimoCodigo ? $ultimoCodigo : 0) + 1;

                return [
                    'codigo_contabil'   => (string)$codigoContabil,
                    'aceita_lancamento' => 0
                ];
            }

            // ==========================
            // CONTA FILHA
            // ==========================

            // 1️⃣ Buscar código contábil do pai
            $sql = sprintf(
                "SELECT codigo_contabil
                FROM fin_plano_contas
                WHERE cod_plano_contas = '%s'",
                $obj->getCodPlanoContasPai()
            );

            $codigoPai = $this->conex->GetOne($sql);

            if (!$codigoPai) {
                throw new Exception('Conta pai não encontrada');
            }

            // 2️⃣ Buscar maior nível entre os filhos
            $sql = sprintf(
                "SELECT MAX(
                    CAST(SUBSTRING_INDEX(codigo_contabil, '.', -1) AS UNSIGNED)
                )
                FROM fin_plano_contas
                WHERE cod_plano_contas_pai = '%s'",
                $obj->getCodPlanoContasPai()
            );

            $ultimoNivel = $this->conex->GetOne($sql);
            $proximoNivel = ($ultimoNivel ? $ultimoNivel : 0) + 1;

            // 3️⃣ Montar novo código contábil
            $codigoContabil = $codigoPai . '.' . $proximoNivel;

            /* 4️⃣ Pai vira conta sintética (não lançável)
            $sql = sprintf(
                "UPDATE fin_plano_contas
                SET aceita_lancamento = 0
                WHERE cod_plano_contas = '%s'",
                $obj->getCodPlanoContasPai()
            );
            $this->conex->Execute($sql);
            */
            return [
                'codigo_contabil'   => $codigoContabil
            ];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        } 
    
    }
    public function insert(PlanoContas $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            $dados = $this->gerarCodigoContabil($obj);
            $obj->setCodigoContabil($dados['codigo_contabil']);
                        
            $sql = "SELECT * FROM fin_plano_contas 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_plano_contas=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("fin_plano_contas");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodPlanoContas($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(PlanoContas $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM fin_plano_contas WHERE cod_evento = ".$obj->getCodEvento()." and cod_plano_contas = ".$obj->getCodPlanoContas(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("fin_plano_contas");
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
    public function delete(PlanoContas $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM fin_plano_contas WHERE cod_evento=\'%s\' and cod_plano_contas= \'%s\'', $obj->getCodEvento(),$obj->getCodPlanoContas());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }        
}
