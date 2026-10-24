<?php
class CentroCustoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(CentroCusto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT fcc.* FROM fin_centro_custo fcc
            WHERE fcc.cod_evento=\'%s\' and fcc.cod_centro_custo= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodCentroCusto());

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
            $sql = "SELECT ifnull(MAX(cod_centro_custo),0)+1 FROM fin_centro_custo";

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
    public function getByIdExiste(CentroCusto $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT fcc.* FROM fin_centro_custo fcc 
            WHERE fcc.cod_evento=\'%s\' and fcc.cod_centro_custo= \'%s\'', 
            $obj->getCodEvento(),$obj->getCodCentroCusto());

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
    public function getAll(CentroCusto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT fcc.* FROM fin_centro_custo fcc
            WHERE fcc.cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaCentroCustos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaCentroCustoByIdEvento(CentroCusto $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT fcc.*, (SELECT COUNT(cod_centro_custo_pai) AS total_filho FROM fin_centro_custo
            WHERE cod_centro_custo_pai = fcc.cod_centro_custo) AS total_filho FROM fin_centro_custo fcc
            LEFT JOIN sis_gestor AS sg ON sg.cod_gestor = fcc.cod_gestor
            WHERE fcc.cod_evento=\'%s\'
            ORDER BY
              CAST(SUBSTRING_INDEX(fcc.codigo_financeiro, \'.\', 1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(fcc.codigo_financeiro, \'.0.0.0\'), \'.\', 2), \'.\', -1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(fcc.codigo_financeiro, \'.0.0.0\'), \'.\', 3), \'.\', -1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(fcc.codigo_financeiro, \'.0.0.0\'), \'.\', 4), \'.\', -1) AS UNSIGNED)
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
                $obj->setListaCentroCusto($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaCentroCustoByIdEventoPai(CentroCusto $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT fcc.* FROM fin_centro_custo fcc
            LEFT JOIN sis_gestor AS sg ON sg.cod_gestor = fcc.cod_gestor
            WHERE fcc.cod_evento=\'%s\'
            ORDER BY
              CAST(SUBSTRING_INDEX(fcc.codigo_financeiro, \'.\', 1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(fcc.codigo_financeiro, \'.0.0.0\'), \'.\', 2), \'.\', -1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(fcc.codigo_financeiro, \'.0.0.0\'), \'.\', 3), \'.\', -1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(fcc.codigo_financeiro, \'.0.0.0\'), \'.\', 4), \'.\', -1) AS UNSIGNED)
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
                $obj->setListaCentroCusto($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function gerarCodigoFinanceiro(CentroCusto $obj, $debug = false) {
        try {

            $this->conex->debug = $debug;

            // Raiz: proximo inteiro
            if ($obj->getCodCentroCustoPai() == null) {
                $sql = "SELECT MAX(CAST(codigo_financeiro AS UNSIGNED))
                        FROM fin_centro_custo
                        WHERE cod_centro_custo_pai IS NULL";

                $ultimoCodigo = $this->conex->GetOne($sql);
                $codigoFinanceiro = ($ultimoCodigo ? $ultimoCodigo : 0) + 1;

                return ['codigo_financeiro' => (string)$codigoFinanceiro];
            }

            // Filho: <codigo_do_pai>.<proximo_nivel>
            $sql = sprintf(
                "SELECT codigo_financeiro FROM fin_centro_custo
                 WHERE cod_centro_custo = '%s'",
                $obj->getCodCentroCustoPai()
            );
            $codigoPai = $this->conex->GetOne($sql);
            if (!$codigoPai) {
                throw new Exception('Centro de custo pai nao encontrado');
            }

            $sql = sprintf(
                "SELECT MAX(CAST(SUBSTRING_INDEX(codigo_financeiro, '.', -1) AS UNSIGNED))
                 FROM fin_centro_custo
                 WHERE cod_centro_custo_pai = '%s'",
                $obj->getCodCentroCustoPai()
            );
            $ultimoNivel = $this->conex->GetOne($sql);
            $proximoNivel = ($ultimoNivel ? $ultimoNivel : 0) + 1;

            return ['codigo_financeiro' => $codigoPai . '.' . $proximoNivel];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(CentroCusto $obj, $debug=false) {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            // Gera codigo_financeiro hierarquico so quando o usuario nao informou
            if ($obj->getCodigoFinanceiro() === null || trim($obj->getCodigoFinanceiro()) === '') {
                $dados = $this->gerarCodigoFinanceiro($obj);
                $obj->setCodigoFinanceiro($dados['codigo_financeiro']);
            }
            
                        
            $sql = "SELECT * FROM fin_centro_custo 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_centro_custo=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("fin_centro_custo");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodCentroCusto($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(CentroCusto $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM fin_centro_custo WHERE cod_evento = ".$obj->getCodEvento()." and cod_centro_custo = ".$obj->getCodCentroCusto(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("fin_centro_custo");
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
    public function delete(CentroCusto $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM fin_centro_custo WHERE cod_evento=\'%s\' and cod_centro_custo= \'%s\'', $obj->getCodEvento(),$obj->getCodCentroCusto());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }        
}
