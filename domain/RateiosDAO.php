<?php
class RateiosDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Rateios $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT fr.* FROM fin_rateios fr
            WHERE fr.cod_evento=\'%s\' and fr.cod_rateio= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodRateio());

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
            $sql = "SELECT ifnull(MAX(cod_rateio),0)+1 FROM fin_rateios";

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
    public function getByIdExiste(Rateios $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT fr.* FROM fin_rateios fr 
            WHERE fr.cod_evento=\'%s\' and fr.cod_rateio= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodRateio());

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
    public function getAll(Rateios $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT fr.* FROM fin_rateios fr
            WHERE fr.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRateioss($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaRateiosByIdEvento(Rateios $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT fr.*, (SELECT COUNT(cod_rateio_pai) AS total_filho FROM fin_rateios 
            WHERE cod_rateio_pai = fr.cod_rateio) AS total_filho FROM fin_rateios fr
            LEFT JOIN sis_gestor AS sg ON sg.cod_gestor = fr.cod_gestor
            WHERE fr.cod_evento=\'%s\'
            ORDER BY fr.cod_rateio
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
                $obj->setListaRateios($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaRateiosByIdEventoPai(Rateios $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT fr.* FROM fin_rateios fr
            LEFT JOIN sis_gestor AS sg ON sg.cod_gestor = fr.cod_gestor
            WHERE fr.cod_evento=\'%s\'
            ORDER BY fr.cod_rateio 
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
                $obj->setListaRateios($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function gerarCodigoContabilRateio(Rateios $obj, $debug = false) {
        try {

            $this->conex->debug = $debug;

            // ==========================
            // CONTA RAIZ
            // ==========================
            if ($obj->getCodRateioPai() == null) {

                $sql = "
                    SELECT MAX(CAST(fr.cod_rateio AS UNSIGNED))
                    FROM fin_rateios
                    WHERE cod_rateio_pai IS NULL
                ";

                $ultimoCodigo = $this->conex->GetOne($sql);
                $codigoContabilRateio = ($ultimoCodigo ? $ultimoCodigo : 0) + 1;

                return [
                    'fr.cod_rateio'   => (string)$codigoContabilRateio
                ];
            }

            // ==========================
            // CONTA FILHA
            // ==========================

            // 1️⃣ Buscar código contábil do pai
            $sql = sprintf(
                "SELECT fr.cod_rateio
                FROM fin_rateios
                WHERE cod_rateio = '%s'",
                $obj->getCodRateioPai()
            );

            $codigoPai = $this->conex->GetOne($sql);

            if (!$codigoPai) {
                throw new Exception('Conta pai não encontrada');
            }

            // 2️⃣ Buscar maior nível entre os filhos
            $sql = sprintf(
                "SELECT MAX(
                    CAST(SUBSTRING_INDEX(fr.cod_rateio, '.', -1) AS UNSIGNED)
                )
                FROM fin_rateios
                WHERE cod_rateio_pai = '%s'",
                $obj->getCodRateioPai()
            );

            $ultimoNivel = $this->conex->GetOne($sql);
            $proximoNivel = ($ultimoNivel ? $ultimoNivel : 0) + 1;

            // 3️⃣ Montar novo código contábil
            $codigoContabilRateio = $codigoPai . '.' . $proximoNivel;

            return [
                'fr.cod_rateio'   => $codigoContabilRateio
            ];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        } 
    
    }

    public function insert(Rateios $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            $dados = $this->gerarCodigoContabilRateio($obj);
            $obj->setCodigoContabilRateio($dados['fr.cod_rateio']);
            
                        
            $sql = "SELECT * FROM fin_rateios 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_rateio=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("fin_rateios");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setcodRateio($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Rateios $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM fin_rateios WHERE cod_evento = ".$obj->getCodEvento()." and cod_rateio = ".$obj->getCodRateio(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("fin_rateios");
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
    public function delete(Rateios $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM fin_rateios WHERE cod_evento=\'%s\' and cod_rateio= \'%s\'', $obj->getCodEvento(),$obj->getCodRateio());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }        
}
