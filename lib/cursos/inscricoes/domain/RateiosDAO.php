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
            $sql = sprintf('SELECT fr.*, fcc.nome_centro_custo,
            (SELECT COUNT(cod_rateio_principal) AS total_filho FROM fin_rateios
            WHERE cod_rateio_principal = fr.cod_rateio) AS total_filho FROM fin_rateios fr
            LEFT JOIN sis_gestor AS sg ON sg.cod_gestor = fr.cod_gestor
            LEFT JOIN fin_centro_custo AS fcc ON fcc.cod_centro_custo = fr.cod_centro_custo AND fcc.cod_evento = fr.cod_evento
            WHERE fr.cod_evento=\'%s\'
            ORDER BY
              CAST(SUBSTRING_INDEX(fr.codigo_financeiro_rateio, \'.\', 1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(fr.codigo_financeiro_rateio, \'.0.0.0\'), \'.\', 2), \'.\', -1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(fr.codigo_financeiro_rateio, \'.0.0.0\'), \'.\', 3), \'.\', -1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(fr.codigo_financeiro_rateio, \'.0.0.0\'), \'.\', 4), \'.\', -1) AS UNSIGNED)
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
            ORDER BY COALESCE(fr.cod_rateio_principal, fr.cod_rateio), fr.cod_rateio_principal IS NOT NULL, fr.cod_rateio
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
    public function gerarCodigoFinanceiroRateio(Rateios $obj, $debug = false) {
        try {

            $this->conex->debug = $debug;

            // ==========================
            // CONTA RAIZ
            // ==========================
            if ($obj->getCodRateioPrincipal() == null) {

                $sql = sprintf(
                    "SELECT MAX(CAST(codigo_financeiro_rateio AS UNSIGNED))
                    FROM fin_rateios
                    WHERE cod_evento = '%s' AND (cod_rateio_principal IS NULL OR cod_rateio_principal = '')",
                    $obj->getCodEvento()
                );

                $ultimoCodigo = $this->conex->GetOne($sql);
                $codigoFinanceiroRateio = ($ultimoCodigo ? $ultimoCodigo : 0) + 1;

                return [
                    'codigo_financeiro_rateio' => (string)$codigoFinanceiroRateio
                ];
            }

            // ==========================
            // CONTA FILHA
            // ==========================

            // Buscar código contábil do pai
            $sql = sprintf(
                "SELECT codigo_financeiro_rateio
                FROM fin_rateios
                WHERE cod_rateio = '%s' AND cod_evento = '%s'",
                $obj->getCodRateioPrincipal(),
                $obj->getCodEvento()
            );

            $codigoPai = $this->conex->GetOne($sql);

            if (!$codigoPai) {
                throw new Exception('Rateio principal não encontrado');
            }

            // Buscar maior nível entre os filhos
            $sql = sprintf(
                "SELECT MAX(
                    CAST(SUBSTRING_INDEX(codigo_financeiro_rateio, '.', -1) AS UNSIGNED)
                )
                FROM fin_rateios
                WHERE cod_rateio_principal = '%s' AND cod_evento = '%s'",
                $obj->getCodRateioPrincipal(),
                $obj->getCodEvento()
            );

            $ultimoNivel = $this->conex->GetOne($sql);
            $proximoNivel = ($ultimoNivel ? $ultimoNivel : 0) + 1;

            // Montar novo código contábil
            $codigoFinanceiroRateio = $codigoPai . '.' . $proximoNivel;

            return [
                'codigo_financeiro_rateio' => $codigoFinanceiroRateio
            ];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }

    }

    public function getPercentualPai(Rateios $obj, $debug=false) {
        try {
            $sql = sprintf(
                "SELECT IFNULL(fr.percentual, 0) FROM fin_rateios fr
                WHERE fr.cod_evento = '%s' AND fr.cod_rateio = '%s'",
                $obj->getCodEvento(),
                $obj->getCodRateioPrincipal()
            );

            $this->conex->debug = $debug;
            $percentual = $this->conex->GetOne($sql);
            return floatval($percentual);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    public function getSomaPercentualIrmaos(Rateios $obj, $debug=false) {
        try {
            if ($obj->getCodRateioPrincipal() == null || $obj->getCodRateioPrincipal() == '') {
                $sql = sprintf(
                    "SELECT IFNULL(SUM(fr.percentual), 0) FROM fin_rateios fr
                    WHERE fr.cod_evento = '%s' AND (fr.cod_rateio_principal IS NULL OR fr.cod_rateio_principal = '')",
                    $obj->getCodEvento()
                );
            } else {
                $sql = sprintf(
                    "SELECT IFNULL(SUM(fr.percentual), 0) FROM fin_rateios fr
                    WHERE fr.cod_evento = '%s' AND fr.cod_rateio_principal = '%s'",
                    $obj->getCodEvento(),
                    $obj->getCodRateioPrincipal()
                );
            }

            // Excluir o próprio rateio se estiver editando
            if ($obj->getCodRateio() != null && $obj->getCodRateio() != '') {
                $sql .= sprintf(" AND fr.cod_rateio != '%s'", $obj->getCodRateio());
            }

            $this->conex->debug = $debug;
            $soma = $this->conex->GetOne($sql);
            return floatval($soma);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
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
            $dados = $this->gerarCodigoFinanceiroRateio($obj);
            $obj->setCodigoFinanceiroRateio($dados['codigo_financeiro_rateio']);
            
                        
            $sql = "SELECT * FROM fin_rateios 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_rateio=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("fin_rateios");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            // sem sprintf: o SQL ja vem pronto do GetInsertSQL, e qualquer '%'
            // vindo dos dados (ex.: descricao "10% sobre o bruto") viraria
            // especificador de formato e quebraria com ArgumentCountError
            $sql = $insertSQL;
            
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
            
            // idem ao insert: sem sprintf, senao '%' nos dados quebra o update
            $sql = $updateSQL;
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
