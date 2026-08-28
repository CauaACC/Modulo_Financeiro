<?php
class TransparenciaDAO extends DAO{
         
    public function __construct() {        
        parent::__construct();
    }  

    /**
     * Constrói a cláusula WHERE para a consulta.
     */
    private function carregarWhere($condicoes = false) {
        // Inicia a cláusula WHERE
        $whereSql['todos'] = " WHERE 1=1 "; 
            
        if (!empty($condicoes['numero_projeto'])) {
            $whereSql['projeto'] .= " AND (pr.numero_projeto LIKE '%".$condicoes['numero_projeto']."%') ";
        }
        if (!empty($condicoes['e_protocolo'])) {
            $whereSql['projeto'] .= " AND (pr.e_protocolo LIKE '%".$condicoes['e_protocolo']."%') ";
        }
        if (!empty($condicoes['proposta'])) {
            $whereSql['projeto'] .= " AND (pr.proposta LIKE '%".$condicoes['proposta']."%') ";
        }
        if (!empty($condicoes['coordenador'])) {
            $whereSql['projeto'] .= " AND (pr.coordenador LIKE '%".$condicoes['coordenador']."%') ";
        }

        // NOVO: Filtro por Período do Projeto (pr.data_inicio e pr.data_fim)
        if (!empty($condicoes['data_de_projeto'])) {
            // Busca projetos que comecem DEPOIS da data informada OU que terminem DEPOIS da data informada
            $data_de_projeto = date('Y-m-d', strtotime(str_replace('/', '-', $condicoes['data_de_projeto'])));
            $whereSql['projeto'] .= " AND pr.data_inicio >= " . $this->conex->qstr($data_de_projeto) . " ";
        }
        if (!empty($condicoes['data_ate_projeto'])) {
            // Busca projetos que terminem ANTES da data informada OU que tenham começado ANTES da data informada
            $data_ate_projeto = date('Y-m-d', strtotime(str_replace('/', '-', $condicoes['data_ate_projeto'])));
            $whereSql['projeto'] .= " AND pr.data_fim <= " . $this->conex->qstr($data_ate_projeto) . " ";
        }

        if (!empty($condicoes['gestor'])) {
            $whereSql['projeto'] .= " AND UPPER(pr.gestor) = UPPER(" . $this->conex->qstr($condicoes['gestor']) . ") ";
            $whereSql['pagamento'] .= " AND UPPER(pg.gestor) = UPPER(" . $this->conex->qstr($condicoes['gestor']) . ") ";
        }

        if (!empty($condicoes['fornecedor'])) {
            $whereSql['pagamento'] .= " AND (pg.fornecedor LIKE '%".$condicoes['fornecedor']."%') ";
        }
        if (!empty($condicoes['cnpj'])) {
            $whereSql['pagamento'] .= " AND (pg.cnpj='".$condicoes['cnpj']."') ";
        }


        // // 2. Filtro por Data (data_de e data_ate)
        // if (!empty($condicoes['data_de'])) {
        //     // Converte a data de dd/mm/aaaa para YYYY-MM-DD
        //     $data_de = date('Y-m-d', strtotime(str_replace('/', '-', $condicoes['data_de'])));
        //     $whereSql .= " AND P.vencimento >= " . $this->conex->qstr($data_de) . " ";
        // }
        // if (!empty($condicoes['data_ate'])) {
        //     // Converte a data de dd/mm/aaaa para YYYY-MM-DD
        //     $data_ate = date('Y-m-d', strtotime(str_replace('/', '-', $condicoes['data_ate'])));
        //     $whereSql .= " AND P.vencimento <= " . $this->conex->qstr($data_ate) . " ";
        // }
        
        // 3. Filtro por Tipo de Pagamento (tipo_pagamento -> wp_pagamentos.fornecedor)
        if (!empty($condicoes['tipo_pagamento'])) {
            $fornecedor = $this->conex->qstr($condicoes['tipo_pagamento']);
            $whereSql['pagamento'] .= " AND P.fornecedor = $fornecedor ";
        }
        // 2. Filtro por Data de Vencimento (pg.vencimento)
        if (!empty($condicoes['data_de_vencimento'])) {
            $data_de = date('Y-m-d', strtotime(str_replace('/', '-', $condicoes['data_de_vencimento'])));
            $whereSql['pagamento'] .= " AND pg.vencimento >= " . $this->conex->qstr($data_de) . " ";
        }
        if (!empty($condicoes['data_ate_vencimento'])) {
            $data_ate = date('Y-m-d', strtotime(str_replace('/', '-', $condicoes['data_ate_vencimento'])));
            $whereSql['pagamento'] .= " AND pg.vencimento <= " . $this->conex->qstr($data_ate) . " ";
        }

        return $whereSql;
    }

    /**
     * Executa a consulta e retorna a lista de pagamentos.
     */
    public function carregarListaPagamentos($condicoes = false, $debug=false) {
        try{
            $whereSqlT = $this->carregarWhere($condicoes);

            $whereSql = $whereSqlT['todos'].' '.$whereSqlT['projeto'].' '.$whereSqlT['pagamento'].' ';
            
            // 4. Implementação da Ordenação (ORDER BY)
            $orderBy = " ORDER BY pg.vencimento DESC "; 
            switch ($condicoes['ordenar']) {
                case 'vencimento_asc':
                    $orderBy = " ORDER BY pg.vencimento ASC ";
                    break;
                case 'vencimento_desc':
                    $orderBy = " ORDER BY pg.vencimento DESC ";
                    break;
                case 'valor_liquido_desc':
                    // Converte o campo string (com vírgula/ponto) em decimal para ordenar corretamente.
                    $orderBy = " ORDER BY CAST(REPLACE(REPLACE(pg.valor_liquido, '.', ''), ',', '.') AS DECIMAL(10,2)) DESC ";
                    break;
            }
            
            $sql = "SELECT pr.numero_projeto, pr.proposta, pr.coordenador, pr.e_protocolo, pr.vinculo
            , pr.data_inicio AS data_inicio_projeto, pr.data_fim AS data_fim_to_projeto
            , pg.valor_bruto, pg.valor_liquido, pg.vencimento, pg.codigo, pg.servico
            , pg.cnpj AS cnpj_fornecedor, pg.fornecedor AS nome_fornecedor, pg.tipo_rpa AS forma_pagamento
            FROM  wp_pagamentos pg LEFT JOIN wp_projetos pr ON pg.numero_projeto = pr.numero_projeto
                {$whereSql}
                AND pg.status_publicacao = 'PUBLICADO'
                {$orderBy}
            ";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                $return = [];
                foreach($rs as $rows){
                    $return[] = $rows;
                }
                return $return;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Executa a consulta e retorna a lista de .
     */
    public function carregarListaProjetos($condicoes = false, $debug=false) {
        try{
            $whereSqlT = $this->carregarWhere($condicoes);
            $whereSql = $whereSqlT['todos'].' '.$whereSqlT['projeto'].' ';

            // 4. Implementação da Ordenação (ORDER BY)
            $orderBy = " ORDER BY pr.proposta DESC ";

            $sql = "SELECT DISTINCT pr.numero_projeto, pr.proposta, pr.coordenador, pr.e_protocolo, pr.data_inicio, pr.data_fim
             FROM  wp_projetos pr
                {$whereSql}
                AND pr.status_publicacao = 'PUBLICADO'
                {$orderBy}
            ";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                $return = [];
                foreach($rs as $rows){
                    $return[] = $rows;
                }
                return $return;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    /**
     * Executa a consulta e retorna a lista de .
     */
    public function carregarListaProjetosOptions($condicoes = false, $debug=false) {
        try{
            $whereSqlT = $this->carregarWhere($condicoes);
            // $whereSql = $whereSqlT['todos'].' '.$whereSqlT['projeto'].' ';
            $whereSql = '';

            // 4. Implementação da Ordenação (ORDER BY)
            $orderBy = " ORDER BY pr.proposta DESC ";

            $sql = "SELECT DISTINCT pr.numero_projeto, pr.proposta, pr.coordenador, pr.e_protocolo, pr.data_inicio, pr.data_fim
             FROM  wp_projetos pr
                WHERE pr.status_publicacao = 'PUBLICADO'
                {$whereSql}
                {$orderBy}
            ";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                $return = [];
                foreach($rs as $rows){
                    $return[] = $rows;
                }
                return $return;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
    public function getByIdProjeto(Transparencia $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tae.* FROM wp_projetos tae
            WHERE tae.numero_projeto=\'%s\' and tae.id= \'%s\'', 
            $obj->getNumeroProjeto(),$obj->getid());

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
    public function getLastIdProjeto($condicao=false, $debug=false) {
        try {
            $sql = "SELECT ifnull(MAX(id),0)+1 FROM wp_projetos";

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
    public function getByIdExisteProjeto(Transparencia $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT tae.* FROM wp_projetos tae 
            WHERE tae.numero_projeto=\'%s\' ', 
            $obj->getNumeroProjeto());

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

    /**
     * Retorna os dados do projeto existente pelo numero_projeto, ou false se não existe.
     */
    public function getProjetoByNumero($numeroProjeto, $debug=false) {
        try {
            $sql = sprintf("SELECT * FROM wp_projetos WHERE numero_projeto='%s'",
                addslashes($numeroProjeto));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            return $rs->fields;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }

public function getByIdExistePagamento(Transparencia $obj, $debug=false, $retornarDados=false) {
    try {
        $sql = sprintf(
            "SELECT tae.*
               FROM wp_pagamentos tae
              WHERE tae.numero_projeto = '%s'
                AND tae.valor_bruto    = '%s'
                AND tae.valor_liquido  = '%s'
                AND tae.vencimento     = '%s'
                AND tae.servico        = '%s'
                AND tae.fornecedor     = '%s'",
            $obj->getNumeroProjeto(),
            $obj->getValorBruto(),
            $obj->getValorLiquido(),
            $obj->getVencimento(),
            $obj->getServico(),
            $obj->getFornecedor()
        );
        if ($obj->getGestor() != '') {
            $sql .= " AND UPPER(tae.gestor) = UPPER(" . $this->conex->qstr($obj->getGestor()) . ")";
        }
        // echo $sql.'<br>';
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);

        if ((!$rs) or ($rs->EOF)) {
            return false;
        } else {
            return $retornarDados ? $rs->fields : true;
        }
    } catch (ADODB_Exception $e) {
        $this->enviarMensagem($e);
        return false;
    }
}

    public function getPagamentoByChave($numeroProjeto, $valorBruto, $valorLiquido, $vencimento, $servico, $fornecedor, $gestor = '', $debug=false) {
        try {
            $sql = sprintf(
                "SELECT * FROM wp_pagamentos
                  WHERE numero_projeto = '%s'
                    AND valor_bruto    = '%s'
                    AND valor_liquido  = '%s'
                    AND vencimento     = '%s'
                    AND servico        = '%s'
                    AND fornecedor     = '%s'",
                addslashes($numeroProjeto),
                addslashes($valorBruto),
                addslashes($valorLiquido),
                addslashes($vencimento),
                addslashes($servico),
                addslashes($fornecedor)
            );
            if ($gestor != '') {
                $sql .= " AND UPPER(gestor) = UPPER(" . $this->conex->qstr($gestor) . ")";
            }
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            return $rs->fields;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }

    public function getAll(Transparencia $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tae.* FROM wp_projetos tae
            WHERE tae.numero_projeto=\'%s\' ', $obj->getNumeroProjeto());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTransparencias($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaTransparenciaByIdEvento(Transparencia $obj, $condicoes=false, $debug=false) {
        try{
            $sql = sprintf('SELECT tae.* FROM wp_projetos tae
            WHERE tae.numero_projeto=\'%s\' ', $obj->getNumeroProjeto());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTransparencia($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**projetoDAO */
    public function insertProjeto(Transparencia $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            $obj->setStatusPublicacao('RASCUNHO');


            $sql = "SELECT * FROM wp_projetos
            WHERE numero_projeto=".$obj->getNumeroProjeto()." and id=0";
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("wp_projetos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = $insertSQL;
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setid($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function updateProjeto(Transparencia $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM wp_projetos WHERE numero_projeto = ".$obj->getNumeroProjeto()." and id = ".$obj->getid(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("wp_projetos");
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
    public function deleteProjeto(Transparencia $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM wp_projetos WHERE numero_projeto=\'%s\' and id= \'%s\'', $obj->getNumeroProjeto(),$obj->getid());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }     

    /**PagamentoDAO */
    public function insertPagamento(Transparencia $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            $obj->setStatusPublicacao('RASCUNHO');


            $sql = "SELECT * FROM wp_pagamentos
            WHERE numero_projeto=".$obj->getNumeroProjeto()." and id=0";
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("wp_pagamentos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = $insertSQL;
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setid($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function updatePagamento(Transparencia $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM wp_pagamentos WHERE numero_projeto = ".$obj->getNumeroProjeto()." and id = ".$obj->getid(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("wp_pagamentos");
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
    public function deletePagamento(Transparencia $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM wp_pagamentos WHERE numero_projeto=\'%s\' and id= \'%s\'', $obj->getNumeroProjeto(),$obj->getid());

            $this->conex->debug = $debug;

            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function deletePagamentosByProjeto($numeroProjeto, $gestor = '', $debug = false) {
        try {
            $sql = "DELETE FROM wp_pagamentos WHERE numero_projeto = " . $this->conex->qstr($numeroProjeto);
            if ($gestor != '') {
                $sql .= " AND UPPER(gestor) = UPPER(" . $this->conex->qstr($gestor) . ")";
            }
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Retorna projetos carregados por um arquivo específico.
     */
    public function listarProjetosPorArquivo($codArquivo, $debug = false) {
        try {
            $sql = "SELECT id, numero_projeto, coordenador, e_protocolo, proposta, vinculo, tipo_pagamento,
                           DATE_FORMAT(data_inicio, '%d/%m/%Y') AS data_inicio,
                           DATE_FORMAT(data_fim, '%d/%m/%Y') AS data_fim
                      FROM wp_projetos
                     WHERE cod_arquivo = " . $this->conex->qstr($codArquivo) . "
                     ORDER BY numero_projeto";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) return [];
            $result = [];
            foreach ($rs as $row) {
                $result[] = $row;
            }
            return $result;
        } catch (ADODB_Exception $e) {
            return [];
        }
    }

    /**
     * Retorna pagamentos carregados por um arquivo específico.
     */
    public function listarPagamentosPorArquivo($codArquivo, $debug = false) {
        try {
            $sql = "SELECT pg.id, pg.numero_projeto, pg.fornecedor, pg.valor_bruto, pg.valor_liquido,
                           DATE_FORMAT(pg.vencimento, '%d/%m/%Y') AS vencimento, pg.servico, pg.cnpj, pg.tipo_rpa
                      FROM wp_pagamentos pg
                     WHERE pg.cod_arquivo = " . $this->conex->qstr($codArquivo) . "
                     ORDER BY pg.numero_projeto, pg.fornecedor";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) return [];
            $result = [];
            foreach ($rs as $row) {
                $result[] = $row;
            }
            return $result;
        } catch (ADODB_Exception $e) {
            return [];
        }
    }

    public function deleteProjetosByArquivo($codArquivo, $debug = false) {
        try {
            $sql = "DELETE FROM wp_projetos WHERE cod_arquivo = " . $this->conex->qstr($codArquivo);
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function deletePagamentosByArquivo($codArquivo, $debug = false) {
        try {
            $sql = "DELETE FROM wp_pagamentos WHERE cod_arquivo = " . $this->conex->qstr($codArquivo);
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    // ==================== GESTAO DE REGISTROS ====================

    public function listarProjetosFiltrados($gestor, $filtros = array(), $debug = false) {
        try {
            $where = sprintf("WHERE UPPER(pr.gestor) = UPPER('%s')", addslashes($gestor));
            if (!empty($filtros['status_publicacao'])) {
                $where .= sprintf(" AND pr.status_publicacao = '%s'", addslashes($filtros['status_publicacao']));
            }
            if (!empty($filtros['cod_arquivo'])) {
                $where .= sprintf(" AND pr.cod_arquivo = '%s'", addslashes($filtros['cod_arquivo']));
            }
            if (!empty($filtros['numero_projeto'])) {
                $where .= sprintf(" AND pr.numero_projeto LIKE '%%%s%%'", addslashes($filtros['numero_projeto']));
            }
            if (!empty($filtros['coordenador'])) {
                $where .= sprintf(" AND pr.coordenador LIKE '%%%s%%'", addslashes($filtros['coordenador']));
            }

            $sql = "SELECT pr.*, a.arquivo_upload, a.data_inclusao AS data_carga
                FROM wp_projetos pr
                LEFT JOIN tab_arquivo a ON pr.cod_arquivo = a.cod_arquivo
                {$where}
                ORDER BY pr.id DESC";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $return = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $return[] = $row;
                }
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function listarPagamentosFiltrados($gestor, $filtros = array(), $debug = false) {
        try {
            $where = sprintf("WHERE UPPER(pg.gestor) = UPPER('%s')", addslashes($gestor));
            if (!empty($filtros['status_publicacao'])) {
                $where .= sprintf(" AND pg.status_publicacao = '%s'", addslashes($filtros['status_publicacao']));
            }
            if (!empty($filtros['cod_arquivo'])) {
                $where .= sprintf(" AND pg.cod_arquivo = '%s'", addslashes($filtros['cod_arquivo']));
            }
            if (!empty($filtros['numero_projeto'])) {
                $where .= sprintf(" AND pg.numero_projeto LIKE '%%%s%%'", addslashes($filtros['numero_projeto']));
            }
            if (!empty($filtros['fornecedor'])) {
                $where .= sprintf(" AND pg.fornecedor LIKE '%%%s%%'", addslashes($filtros['fornecedor']));
            }

            $sql = "SELECT pg.*, a.arquivo_upload, a.data_inclusao AS data_carga
                FROM wp_pagamentos pg
                LEFT JOIN tab_arquivo a ON pg.cod_arquivo = a.cod_arquivo
                {$where}
                ORDER BY pg.id DESC";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $return = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $return[] = $row;
                }
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function getProjetoById($id, $gestor, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM wp_projetos WHERE id = '%s' AND UPPER(gestor) = UPPER('%s')",
                addslashes($id), addslashes($gestor));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                return $rs->fields;
            }
            return false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getPagamentoById($id, $gestor, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM wp_pagamentos WHERE id = '%s' AND UPPER(gestor) = UPPER('%s')",
                addslashes($id), addslashes($gestor));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                return $rs->fields;
            }
            return false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function atualizarStatusPublicacao($tabela, $id, $novoStatus, $gestor, $debug = false) {
        try {
            $sql = sprintf("UPDATE %s SET status_publicacao = '%s', dat_alter = NOW(), res_alter = '%s'
                WHERE id = '%s' AND UPPER(gestor) = UPPER('%s')",
                $tabela, addslashes($novoStatus), $_SESSION['_cod_usuario'],
                addslashes($id), addslashes($gestor));
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function atualizarStatusPublicacaoEmLote($tabela, $ids, $novoStatus, $gestor, $debug = false) {
        try {
            if (empty($ids)) return false;
            $idsLimpos = array_map('intval', $ids);
            $inClause = implode(',', $idsLimpos);
            $sql = sprintf("UPDATE %s SET status_publicacao = '%s', dat_alter = NOW(), res_alter = '%s'
                WHERE id IN (%s) AND UPPER(gestor) = UPPER('%s')",
                $tabela, addslashes($novoStatus), $_SESSION['_cod_usuario'],
                $inClause, addslashes($gestor));
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function atualizarStatusPublicacaoPorArquivo($tabela, $codArquivo, $novoStatus, $gestor, $debug = false) {
        try {
            $sql = sprintf("UPDATE %s SET status_publicacao = '%s', dat_alter = NOW(), res_alter = '%s'
                WHERE cod_arquivo = '%s' AND UPPER(gestor) = UPPER('%s') AND status_publicacao = 'RASCUNHO'",
                $tabela, addslashes($novoStatus), $_SESSION['_cod_usuario'],
                addslashes($codArquivo), addslashes($gestor));
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function contarRegistrosPorStatus($tabela, $gestor, $debug = false) {
        try {
            $sql = sprintf("SELECT status_publicacao, COUNT(*) as total FROM %s
                WHERE UPPER(gestor) = UPPER('%s')
                GROUP BY status_publicacao",
                $tabela, addslashes($gestor));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $return = array('RASCUNHO' => 0, 'PUBLICADO' => 0);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $return[$row['status_publicacao']] = intval($row['total']);
                }
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array('RASCUNHO' => 0, 'PUBLICADO' => 0);
        }
    }

    public function carregarDadosDashboard($gestor, $ano = '', $mes = '', $debug = false) {
        try {
            $g = addslashes($gestor);
            $dados = array();

            // Filtro de data para projetos (usa data_inicio)
            $filtroProjData = '';
            if ($ano != '') {
                $filtroProjData .= " AND YEAR(data_inicio) = '" . addslashes($ano) . "'";
                if ($mes != '') {
                    $filtroProjData .= " AND MONTH(data_inicio) = '" . addslashes($mes) . "'";
                }
            }

            // Filtro de data para pagamentos (usa vencimento)
            $filtroPagData = '';
            if ($ano != '') {
                $filtroPagData .= " AND YEAR(vencimento) = '" . addslashes($ano) . "'";
                if ($mes != '') {
                    $filtroPagData .= " AND MONTH(vencimento) = '" . addslashes($mes) . "'";
                }
            }

            // Contadores projetos
            $sql = "SELECT status_publicacao, COUNT(*) as total FROM wp_projetos WHERE UPPER(gestor) = UPPER('$g') $filtroProjData GROUP BY status_publicacao";
            $rs = $this->conex->Execute($sql);
            $dados['projetos'] = array('RASCUNHO' => 0, 'PUBLICADO' => 0);
            if ($rs && !$rs->EOF) { foreach ($rs as $row) { $dados['projetos'][$row['status_publicacao']] = intval($row['total']); } }

            // Contadores pagamentos
            $sql = "SELECT status_publicacao, COUNT(*) as total FROM wp_pagamentos WHERE UPPER(gestor) = UPPER('$g') $filtroPagData GROUP BY status_publicacao";
            $rs = $this->conex->Execute($sql);
            $dados['pagamentos'] = array('RASCUNHO' => 0, 'PUBLICADO' => 0);
            if ($rs && !$rs->EOF) { foreach ($rs as $row) { $dados['pagamentos'][$row['status_publicacao']] = intval($row['total']); } }

            // Totais financeiros (pagamentos publicados)
            $sql = "SELECT COUNT(*) as total,
                    COALESCE(SUM(CAST(REPLACE(REPLACE(valor_bruto, '.', ''), ',', '.') AS DECIMAL(15,2))), 0) as total_bruto,
                    COALESCE(SUM(CAST(REPLACE(REPLACE(valor_liquido, '.', ''), ',', '.') AS DECIMAL(15,2))), 0) as total_liquido
                    FROM wp_pagamentos WHERE UPPER(gestor) = UPPER('$g') AND status_publicacao = 'PUBLICADO' $filtroPagData";
            $rs = $this->conex->Execute($sql);
            $dados['financeiro'] = array('total_bruto' => 0, 'total_liquido' => 0);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $dados['financeiro']['total_bruto'] = floatval($row['total_bruto']);
                    $dados['financeiro']['total_liquido'] = floatval($row['total_liquido']);
                }
            }

            // Top coordenadores (por qtd de projetos)
            $sql = "SELECT coordenador, COUNT(*) as total FROM wp_projetos WHERE UPPER(gestor) = UPPER('$g') $filtroProjData GROUP BY coordenador ORDER BY total DESC LIMIT 10";
            $rs = $this->conex->Execute($sql);
            $dados['topCoordenadores'] = array();
            if ($rs && !$rs->EOF) { foreach ($rs as $row) { $dados['topCoordenadores'][] = $row; } }

            // Ultimas cargas (5)
            $sql = "SELECT cod_arquivo, arquivo_upload, tipo_arquivo, status_carga, data_inclusao, message_log
                    FROM tab_arquivo WHERE UPPER(gestor) = UPPER('$g') AND tipo_arquivo LIKE 'CARGA_TRANSPARENCIA%'
                    ORDER BY data_inclusao DESC LIMIT 5";
            $rs = $this->conex->Execute($sql);
            $dados['ultimasCargas'] = array();
            if ($rs && !$rs->EOF) { foreach ($rs as $row) { $dados['ultimasCargas'][] = $row; } }

            return $dados;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function listarArquivosCarga($gestor, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_arquivo, arquivo_upload, tipo_arquivo, status_carga, data_inclusao
                FROM tab_arquivo
                WHERE UPPER(gestor) = UPPER('%s') AND tipo_arquivo LIKE 'CARGA_TRANSPARENCIA%%'
                ORDER BY data_inclusao DESC", addslashes($gestor));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $return = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $return[] = $row;
                }
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }
}