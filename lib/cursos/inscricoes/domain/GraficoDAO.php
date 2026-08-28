<?php
class GraficoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    
    public function carregarListaTotalInscritosPorRegionalPR(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT count(i.cod_inscrito) as total, rs.nome  from tab_inscritos i
            inner join tab_opcoes_cidades c on c.nome=i.cidade and c.uf=i.estado
            inner join tab_opcoes_regionais_saude rs on rs.numero=c.regional_saude and c.uf=rs.uf
            where i.cod_evento=\'%s\' and i.status=\'ATIVADA\' and i.estado=\'PR\' 
            GROUP BY rs.nome ORDER BY rs.uf, total desc, rs.numero', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalInscritosPorRegionalPR($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
    public function carregarListaTotalInscritosPorTipoInstituicao(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT COUNT(i.cod_inscrito) AS total, i.tipo_instituicao  FROM tab_inscritos i
            where i.cod_evento=\'%s\' and i.status=\'ATIVADA\' 
            GROUP BY i.tipo_instituicao ORDER BY i.tipo_instituicao;', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalInscritosPorTipoInstituicao($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
    public function carregarListaTotalInscritosPorProfissaoAtuacao(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT COUNT(i.cod_inscrito) AS total, i.profissao_atuacao  FROM tab_inscritos i
            where i.cod_evento=\'%s\' and i.status=\'ATIVADA\' 
            GROUP BY i.profissao_atuacao ORDER BY i.profissao_atuacao;', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalInscritosPorProfissaoAtuacao($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
 
    public function carregarListaTotalInscritosPorTipoInscricao(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT COUNT(i.cod_inscrito) AS total, IFNULL(i.tipo_inscricao,\'OUTROS\') as tipo_inscricao
                FROM tab_inscritos i WHERE i.cod_evento=\'%s\' and i.status=\'ATIVADA\' 
                GROUP BY IFNULL(i.tipo_inscricao,\'OUTROS\')  
                ORDER BY IFNULL(i.tipo_inscricao,\'OUTROS\') ;', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalInscritosPorTipoInscricao($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaTotalTrabalhosPorAreaTematica(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT COUNT(t.cod_trabalho) AS total, t.cod_area_tematica, oa.titulo  FROM tab_trabalhos t
            INNER JOIN tab_opcoes_areas_tematicas oa ON oa.cod_area_tematica=t.cod_area_tematica
            where t.cod_evento=\'%s\' and t.status=\'ATIVADA\' 
            GROUP BY t.cod_area_tematica ORDER BY t.cod_area_tematica;', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalTrabalhosPorAreaTematica($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
    public function carregarListaTotalTrabalhosPorFormatoTexto(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT COUNT(t.cod_trabalho) AS total, t.formato_texto FROM tab_trabalhos t
            where t.cod_evento=\'%s\' and t.status=\'ATIVADA\' 
            GROUP BY t.formato_texto ORDER BY t.formato_texto;', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalTrabalhosPorFormatoTexto($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaTotalTrabalhosPorPremiacao(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT COUNT(t.cod_trabalho) AS total, t.premiacao FROM tab_trabalhos t
            where t.cod_evento=\'%s\' and t.status=\'ATIVADA\' 
            GROUP BY t.premiacao ORDER BY t.premiacao;', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalTrabalhosPorPremiacao($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaTotalTrabalhosPorInstituicao(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT COUNT(t.cod_trabalho) AS total, t.instituicao_trabalho FROM tab_trabalhos t
            where t.cod_evento=\'%s\' and t.status=\'ATIVADA\' 
            GROUP BY t.instituicao_trabalho ORDER BY t.instituicao_trabalho;', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalTrabalhosPorInstituicao($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
    public function carregarListaTotalInscritosPorOficina(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT
                toa.cod_oficina
               ,toa.titulo
               ,IFNULL(toa.numero_vagas,0) as numero_vagas 
               ,toa.status
               ,IFNULL(tti.total_inscricoes,0) as total_inscricoes 
               ,IFNULL(ttp.total_inscricoes_pagas,0) as total_inscricoes_pagas 
               ,IFNULL(ttis.total_inscricoes_isentos,0) as total_inscricoes_isentos 
               ,(
                 Select Count(*) from (SELECT ti.cpf, tlio.cod_oficina
                 FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                 WHERE ((tlio.cod_evento   = ti.cod_evento)
                   and (tlio.cod_inscrito = ti.cod_inscrito)
                   and (ti.status         = \'ATIVADA\'))
                 GROUP BY  ti.cpf,tlio.cod_oficina) as too
                  where toa.cod_oficina= too.cod_oficina
                ) as total_inscricoes_por_cpf
                ,aguardpag.total_inscricoes_boleto_aguardando_pag_por_oficina
                ,toa.ordem
                ,toa.numero_oficial
              FROM tab_opcoes_oficinas toa
              LEFT JOIN (
                 SELECT COUNT(tlio.cod_oficina) AS total_inscricoes, tlio.cod_evento, tlio.cod_oficina
                 FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                 WHERE (tlio.cod_evento   = ti.cod_evento)
                   AND (tlio.cod_inscrito = ti.cod_inscrito)
                   AND (ti.status         = \'ATIVADA\')
                 GROUP BY  tlio.cod_oficina, tlio.cod_evento
                ) AS tti ON tti.cod_evento = toa.cod_evento AND tti.cod_oficina=toa.cod_oficina
              LEFT JOIN (
                 SELECT COUNT(tlio.cod_oficina) AS total_inscricoes_pagas, tlio.cod_evento, tlio.cod_oficina
                 FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                 WHERE (tlio.cod_evento   = ti.cod_evento)
                   AND (tlio.cod_inscrito = ti.cod_inscrito)
                   AND (ti.status         = \'ATIVADA\')
                   AND (ti.cod_inscrito in (SELECT tp.cod_inscrito FROM tab_pagamentos tp 
                                              WHERE tp.status = \'ATIVADA\' OR tp.status IS NULL 
                                              GROUP BY tp.cod_inscrito))
                 GROUP BY  tlio.cod_oficina, tlio.cod_evento
                ) AS ttp ON ttp.cod_evento = toa.cod_evento AND ttp.cod_oficina=toa.cod_oficina
              LEFT JOIN (
                 SELECT COUNT(tlio.cod_oficina) AS total_inscricoes_isentos, tlio.cod_evento, tlio.cod_oficina
                 FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                 WHERE (tlio.cod_evento   = ti.cod_evento)
                   AND (tlio.cod_inscrito = ti.cod_inscrito)
                   AND (ti.status         = \'ATIVADA\')
                   AND (ti.cpf in (SELECT tis.cpf FROM tab_isentos tis 
                                              WHERE (tis.status = \'ATIVADA\' OR tis.status IS NULL) and tis.cod_evento=" . COD_EVENTO . "
                                              GROUP BY tis.cod_isento))
                 GROUP BY  tlio.cod_oficina, tlio.cod_evento
                ) AS ttis ON ttis.cod_evento = toa.cod_evento AND ttis.cod_oficina=toa.cod_oficina
              LEFT JOIN (SELECT COUNT(tlio.cod_oficina) AS total_inscricoes_boleto_aguardando_pag_por_oficina, tlio.cod_evento,  tlio.cod_oficina
                      FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                      WHERE
                          (tlio.cod_evento   = ti.cod_evento)
                      AND (tlio.cod_inscrito = ti.cod_inscrito)
                      AND (ti.status         = \'ATIVADA\')
                      AND (ti.cod_inscrito IN (SELECT cod_inscrito FROM (SELECT cod_evento, cod_inscrito, cod_boleto, valor_documento, data_vencimento
                              , DATE_FORMAT(STR_TO_DATE(data_vencimento,\"%d/%m/%Y\"),\"%Y%m%d\") AS data_vencimento_comparar
                              , DATE_FORMAT(STR_TO_DATE(DATE_SUB(NOW(), INTERVAL 4 DAY),\"%Y-%m-%d\"),\"%Y%m%d\") AS data_referencia_pagamento
                              FROM tab_boletos WHERE flg_pagamento<>1 OR flg_pagamento IS NULL ) AS bolt WHERE bolt.data_referencia_pagamento<=bolt.data_vencimento_comparar)
                           )  GROUP BY  tlio.cod_oficina, tlio.cod_evento
              ) AS aguardpag ON aguardpag.cod_evento = toa.cod_evento AND aguardpag.cod_oficina=toa.cod_oficina 
              WHERE
                (toa.cod_evento = \'%s\') and (toa.tipo=\'Oficina\')
              ORDER BY toa.ordem, toa.status,toa.titulo', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalInscritosPorOficina($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
    public function carregarListaTotalInscritosPorEventoParalelo(Grafico $obj, $debug=false) {
        $obj->setTipo('Evento Paralelo');
        $this->carregarListaTotalInscritosPorSimposio($obj, $debug);
    }
    
    public function carregarListaTotalInscritosPorSimposio(Grafico $obj, $debug=false) {
        try{
            $whereTipo = " and (toa.tipo = 'Simpósio') ";
            $nomeLista = "ListaTotalInscritosPorSimposio";
            if($obj->getTipo()=='Evento Paralelo'){
                $whereTipo = " and (toa.tipo = '".$obj->getTipo()."') ";
                $nomeLista = "ListaTotalInscritosPorEventoParalelo";
            }
            $sql = 'SELECT
                toa.cod_oficina
               ,toa.titulo
               ,toa.numero_vagas
               ,toa.status
               ,tti.total_inscricoes
               ,ttp.total_inscricoes_pagas
               ,ttis.total_inscricoes_isentos
               ,(
                 Select Count(*) from (SELECT ti.cpf, tlio.cod_oficina
                 FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                 WHERE ((tlio.cod_evento   = ti.cod_evento)
                   and (tlio.cod_inscrito = ti.cod_inscrito)
                   and (ti.status         = \'ATIVADA\'))
                 GROUP BY  ti.cpf,tlio.cod_oficina) as too
                  where toa.cod_oficina= too.cod_oficina
                ) as total_inscricoes_por_cpf
                ,aguardpag.total_inscricoes_boleto_aguardando_pag_por_oficina
                ,toa.ordem
                ,toa.numero_oficial
                ,toa.abrev_tipo
              FROM tab_opcoes_oficinas toa
              LEFT JOIN (
                 SELECT COUNT(tlio.cod_oficina) AS total_inscricoes, tlio.cod_evento, tlio.cod_oficina
                 FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                 WHERE (tlio.cod_evento   = ti.cod_evento)
                   AND (tlio.cod_inscrito = ti.cod_inscrito)
                   AND (ti.status         = \'ATIVADA\')
                 GROUP BY  tlio.cod_oficina, tlio.cod_evento
                ) AS tti ON tti.cod_evento = toa.cod_evento AND tti.cod_oficina=toa.cod_oficina
              LEFT JOIN (
                 SELECT COUNT(tlio.cod_oficina) AS total_inscricoes_pagas, tlio.cod_evento, tlio.cod_oficina
                 FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                 WHERE (tlio.cod_evento   = ti.cod_evento)
                   AND (tlio.cod_inscrito = ti.cod_inscrito)
                   AND (ti.status         = \'ATIVADA\')
                   AND (ti.cod_inscrito in (SELECT tp.cod_inscrito FROM tab_pagamentos tp 
                                              WHERE tp.status = \'ATIVADA\' OR tp.status IS NULL 
                                              GROUP BY tp.cod_inscrito))
                 GROUP BY  tlio.cod_oficina, tlio.cod_evento
                ) AS ttp ON ttp.cod_evento = toa.cod_evento AND ttp.cod_oficina=toa.cod_oficina
              LEFT JOIN (
                 SELECT COUNT(tlio.cod_oficina) AS total_inscricoes_isentos, tlio.cod_evento, tlio.cod_oficina
                 FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                 WHERE (tlio.cod_evento   = ti.cod_evento)
                   AND (tlio.cod_inscrito = ti.cod_inscrito)
                   AND (ti.status         = \'ATIVADA\')
                   AND (ti.cpf in (SELECT tis.cpf FROM tab_isentos tis 
                                              WHERE (tis.status = \'ATIVADA\' OR tis.status IS NULL) and tis.cod_evento=" . COD_EVENTO . "
                                              GROUP BY tis.cod_isento))
                 GROUP BY  tlio.cod_oficina, tlio.cod_evento
                ) AS ttis ON ttis.cod_evento = toa.cod_evento AND ttis.cod_oficina=toa.cod_oficina
              LEFT JOIN (SELECT COUNT(tlio.cod_oficina) AS total_inscricoes_boleto_aguardando_pag_por_oficina, tlio.cod_evento,  tlio.cod_oficina
                      FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                      WHERE
                          (tlio.cod_evento   = ti.cod_evento)
                      AND (tlio.cod_inscrito = ti.cod_inscrito)
                      AND (ti.status         = \'ATIVADA\')
                      AND (ti.cod_inscrito IN (SELECT cod_inscrito FROM (SELECT cod_evento, cod_inscrito, cod_boleto, valor_documento, data_vencimento
                              , DATE_FORMAT(STR_TO_DATE(data_vencimento,\'%d/%m/%Y\'),\'%Y%m%d\') AS data_vencimento_comparar
                              , DATE_FORMAT(STR_TO_DATE(DATE_SUB(NOW(), INTERVAL 4 DAY),\'%Y-%m-%d\'),\'%Y%m%d\') AS data_referencia_pagamento
                              FROM tab_boletos WHERE flg_pagamento<>1 OR flg_pagamento IS NULL ) AS bolt WHERE bolt.data_referencia_pagamento<=bolt.data_vencimento_comparar)
                           )  GROUP BY  tlio.cod_oficina, tlio.cod_evento
              ) AS aguardpag ON aguardpag.cod_evento = toa.cod_evento AND aguardpag.cod_oficina=toa.cod_oficina 
              WHERE
                (toa.cod_evento = '.$obj->getCodEvento().')
                '.$whereTipo.'
              ORDER BY toa.ordem, toa.status,toa.titulo';

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
//                $obj->setListaTotalInscritosPorSimposio($return);
                $nomeLista = 'set'.$nomeLista;
                $obj->$nomeLista($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
    public function carregarListaTotalInscritosPorMacrorregiaoPR(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT count(i.cod_inscrito) as total, rs.macrorregiao  from tab_inscritos i
            inner join tab_opcoes_cidades c on c.nome=i.cidade and c.uf=i.estado
            inner join tab_opcoes_regionais_saude rs on rs.numero=c.regional_saude and c.uf=rs.uf
            where i.cod_evento=\'%s\' and i.status=\'ATIVADA\' and i.estado=\'PR\' 
            GROUP BY rs.macrorregiao ORDER BY rs.uf, total desc,rs.macrorregiao', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTotalInscritosPorMacrorregiaoPR($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaTotalAvaliadosPorAvaliadorInscrito(Grafico $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT COUNT(id_inclusao) total, id_inclusao, COUNT(classificacao_status) total_status,  classificacao_status FROM tab_trabalhos_avaliacao_inscricao 
                WHERE cod_evento=\'%s\' GROUP BY id_inclusao, classificacao_status', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaListaTotalAvaliadosPorAvaliadorInscrito($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}
