<?php
class InscritoDAO extends DAO{

    public $whereSql;
    public $sqlLeftJoinColunsPerfil = "
             , top.titulo as nome_perfil, top.receber_inscricao ";

            //  , IFNULL(tti.instituicao_trabalho, '') AS instituicao_trabalho         
    public $sqlLeftJoinColunsTrab = "
             , IFNULL(tt.total_trabalhos,0) as total_trabalhos 
             , IFNULL(ta.total_avaliacoes,0) as total_avaliacoes
             ";
     public $sqlLeftJoinColunsAtiv = "         
             , IFNULL(tlio.total_oficinas,0) as total_oficinas              
             , IFNULL(tliom.total_minicursos,0) as total_minicursos
             , IFNULL(tlios.total_simposios,0) as total_simposios              
             , IFNULL(tlioep.total_outras_atividades,0) as total_outras_atividades 
             ";
    
    public $sqlLeftJoinColunsPag = "
             , IFNULL(ip.total_pagamentos,0) as total_pagamentos 
             , IFNULL(ip.valor_pagamentos,0) as valor_pagamentos 
             , IFNULL(tbol.total_boletos,0) as total_boletos 
             , IFNULL(tis.cod_isento, tisc.cod_isento_codigo) AS cod_isento
             , IFNULL(pis.total_perfis_isentos,0) as total_perfis_isentos            
             , IFNULL(iv.cod_voucher,'') as cod_voucher
             , IFNULL(ivq.total_vouchers,0) as total_vouchers
             , IFNULL(ipd.total_pedidos,0) as total_pedidos 
             , IFNULL(ipd.valor_total_pedidos,0) as valor_total_pedidos 
             ";   
    public $sqlLeftJoinColuns = "";
//             , '20000' AS tx_unica_a_vista_valor
     
    public $sqlLeftJoin = "";
//            left join (SELECT count(ip.cod_inscrito) as total_pagamentos,sum(ip.valor) as valor_pagamentos
//                        , ip.cod_inscrito
//                    FROM tab_pagamentos ip WHERE ((ip.forma_pagamento='BOLETO BANCÁRIO')
//                                                or (ip.forma_pagamento='BOLETO') 
//                                                or (ip.forma_pagamento='TESOURARIA') 
//                                                or (ip.forma_pagamento='BOLETO BANCÁRIO UNIFICADO') 
//                                                or (ip.forma_pagamento='CARTAO') 
//                                                or (ip.forma_pagamento='CARTAO_CREDITO')
//                                                or (ip.forma_pagamento='DOACAO') 
//                                                or (ip.forma_pagamento='APOIO') 
//                                                or (ip.forma_pagamento='DEPOSITO CONTA CORRENTE')
//                                                or (ip.forma_pagamento='EMPENHO') 
//                                                or (ip.forma_pagamento='INEXIGIBILIDADE') 
//                                                or (ip.forma_pagamento='BOLETO A COMPENSAR') 
//                                                or (ip.forma_pagamento='CONVÊNIO') 
//                                                or (ip.forma_pagamento='TRANSFERENCIA')
//                                                or (ip.forma_pagamento='PIX')
//                                                or (ip.forma_pagamento='OUTROS')
//                                                or (ip.forma_pagamento='OUTRAS')
//                                                ) and ip.status='ATIVADA'
//                       group by ip.cod_inscrito) as ip 
//                       on ti.cod_inscrito=ip.cod_inscrito
//            left join (SELECT max(ipi.cod_pagamento), ipi.data_pagamento, ipi.cod_inscrito, ipi.cod_pagamento FROM tab_pagamentos ipi
//                        GROUP BY ipi.cod_inscrito) as ipi 
//                       on ti.cod_inscrito=ipi.cod_inscrito
//            left join (SELECT count(tt.cod_inscrito) as total_trabalhos, tt.cod_inscrito FROM tab_trabalhos tt  WHERE tt.status='ATIVADA'
//                       group by tt.cod_inscrito) as tt 
//                       on ti.cod_inscrito=tt.cod_inscrito
//            left join (SELECT max(tti.cod_trabalho), tti.cod_inscrito, tti.instituicao_trabalho FROM tab_trabalhos tti  
//                        WHERE tti.status='ATIVADA' group by tti.cod_inscrito) as tti 
//                       on ti.cod_inscrito=tti.cod_inscrito
//            left join (SELECT count(ta.cod_inscrito) as total_avaliacoes, ta.cod_inscrito FROM tab_trabalhos_avaliacao ta WHERE ta.status='ATIVADA'
//                       group by ta.cod_inscrito) as ta 
//                       on ti.cod_inscrito=ta.cod_inscrito
//            left join (SELECT count(tlio.cod_inscrito) as total_oficinas, tlio.cod_inscrito FROM tab_lig_inscritos_oficinas tlio
//                        INNER JOIN tab_opcoes_oficinas top ON top.cod_oficina=tlio.cod_oficina 
//                        WHERE tlio.status='ATIVADA' AND tlio.cod_evento=".COD_EVENTO." AND top.status='ATIVADA' GROUP BY tlio.cod_inscrito) as tlio 
//                       on ti.cod_inscrito=tlio.cod_inscrito
//            left join (SELECT count(tlio.cod_inscrito) as total_minicursos, tlio.cod_inscrito FROM tab_lig_inscritos_oficinas tlio
//                        INNER JOIN tab_opcoes_oficinas top ON top.cod_oficina=tlio.cod_oficina  and top.tipo='Minicurso'
//                        WHERE tlio.status='ATIVADA' AND tlio.cod_evento=".COD_EVENTO." AND top.status='ATIVADA' GROUP BY tlio.cod_inscrito) as tliom 
//                       on ti.cod_inscrito=tliom.cod_inscrito
//            left join (SELECT count(tlio.cod_inscrito) as total_simposios, tlio.cod_inscrito FROM tab_lig_inscritos_oficinas tlio
//                        INNER JOIN tab_opcoes_oficinas top ON top.cod_oficina=tlio.cod_oficina and top.tipo='Simpósio'
//                        WHERE tlio.status='ATIVADA' AND tlio.cod_evento=".COD_EVENTO." AND top.status='ATIVADA' GROUP BY tlio.cod_inscrito) as tlios 
//                       on ti.cod_inscrito=tlios.cod_inscrito
//            left join (SELECT count(tlio.cod_inscrito) as total_outras_atividades, tlio.cod_inscrito FROM tab_lig_inscritos_oficinas tlio
//                        INNER JOIN tab_opcoes_oficinas top ON top.cod_oficina=tlio.cod_oficina  and top.tipo='Evento Paralelo'
//                        WHERE tlio.status='ATIVADA' AND tlio.cod_evento=".COD_EVENTO." AND top.status='ATIVADA' GROUP BY tlio.cod_inscrito) as tlioep 
//                       on ti.cod_inscrito=tlioep.cod_inscrito
//            left join (SELECT count(tbol.cod_inscrito) as total_boletos, tbol.cod_inscrito FROM tab_boletos tbol WHERE (tbol.status is null OR tbol.status='ATIVADA')
//                       group by tbol.cod_inscrito) as tbol 
//                       on ti.cod_inscrito=tbol.cod_inscrito
//            left join tab_opcoes_perfis top on ti.cod_perfil =  top.cod_perfil 
//            
//            LEFT JOIN (SELECT distinct cpf,cod_isento FROM tab_isentos where cod_evento=".COD_EVENTO.") AS tis ON tis.cpf=ti.cpf
//            LEFT JOIN (SELECT distinct cod_inscrito,cod_isento as cod_isento_codigo FROM tab_isentos_codigo where cod_evento=".COD_EVENTO.") AS tisc ON tisc.cod_inscrito=ti.cod_inscrito
//
//
//            left join (SELECT tpis.cod_inscrito,COUNT(*) AS total_perfis_isentos FROM tab_inscritos tpis WHERE 
//                        tpis.cod_evento = ".COD_EVENTO." 
//                        AND (
//                                (tpis.cod_perfil=35)
//                                OR (tpis.cod_perfil=25)
//                                OR (tpis.cod_inscrito IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip WHERE tlip.cod_perfil IN (25,35) AND tlip.cod_evento=".COD_EVENTO."))
//                                OR (tpis.cod_inscrito IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip 
//                                                            INNER JOIN tab_opcoes_perfis op ON tlip.cod_perfil=op.cod_perfil and op.perfil_isento=1 and op.cod_evento=tlip.cod_evento
//                                                            WHERE tlip.cod_evento=".COD_EVENTO."))
//
//                        )
//                        GROUP BY tpis.cod_inscrito 
//                    ) as pis on pis.cod_inscrito = ti.cod_inscrito
//                    
//            LEFT JOIN (SELECT distinct iv.cod_voucher, cod_inscrito, tv.sequencia_interna FROM tab_lig_inscritos_vouchers iv
//            inner join tab_vouchers tv on tv.cod_voucher=iv.cod_voucher on tv.status='ATIVADA'
//                WHERE iv.status='ATIVADA' and iv.cod_evento=".COD_EVENTO." ) AS iv ON iv.cod_inscrito=ti.cod_inscrito
//                    
//            LEFT JOIN (SELECT count(ivq.cod_voucher) as total_vouchers, cod_inscrito FROM tab_lig_inscritos_vouchers ivq
//                WHERE ivq.status='ATIVADA' and ivq.cod_evento=".COD_EVENTO."  group by cod_inscrito) AS ivq ON ivq.cod_inscrito=ti.cod_inscrito
//     ";

    public function __construct() {
        parent::__construct();
        $this->sqlLeftJoinColuns = $this->sqlLeftJoinColunsPerfil.$this->sqlLeftJoinColunsTrab.$this->sqlLeftJoinColunsAtiv.$this->sqlLeftJoinColunsPag;
    }

    /**
     * Inscritos cadastrados (dat_incl) agrupados por perfil e mes/ano.
     * Parte de tab_opcoes_perfis (LEFT JOIN) -> todo perfil do evento aparece,
     * mesmo sem inscritos cadastrados (registro com ano_mes=NULL).
     * Sem filtro de status nem origem_cadastro.
     */
    public function carregarCadastradosPorMesPerfil($codEvento, $debug=false) {
        try {
            $sql = sprintf("
                SELECT
                    p.cod_perfil,
                    p.titulo AS perfil,
                    DATE_FORMAT(ti.dat_incl, '%%Y-%%m') AS ano_mes,
                    COUNT(DISTINCT ti.cod_inscrito) AS pessoas
                FROM tab_opcoes_perfis p
                LEFT JOIN tab_inscritos ti
                    ON ti.cod_perfil = p.cod_perfil
                   AND ti.cod_evento = p.cod_evento
                   AND ti.dat_incl IS NOT NULL
                WHERE p.cod_evento = %d
                GROUP BY p.cod_perfil, p.titulo, DATE_FORMAT(ti.dat_incl, '%%Y-%%m')
                ORDER BY p.titulo, ano_mes
            ", intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Lista outras inscrições (agendas) do MESMO CPF no MESMO evento, excluindo a inscrição
     * passada em $codInscritoExcluir (normalmente a do usuário logado). Usado pela tela
     * "Minhas Outras Agendas" do fluxo AGENDA. Retorna array de Inscrito com dados da oficina
     * vinculada (data_inicio, titulo_oficina, horario_ini, horario_fim) e do perfil (titulo_perfil).
     */
    public function carregarListaAgendasMesmoCpfMesmoEvento(Inscrito $obj, $codInscritoExcluir, $debug = false) {
        try {
            $cpf       = addslashes($obj->getCpf());
            $email     = addslashes($obj->getEMail());
            $codEvento = (int) $obj->getCodEvento();
            $codExc    = (int) $codInscritoExcluir;
            $sql = "
                SELECT i.cod_inscrito, i.cod_perfil, i.nome_inscrito, i.cpf, i.e_mail, i.cod_evento, i.id_publico,
                       lio.cod_oficina,
                       too.titulo  AS titulo_oficina,
                       too.abrev_tipo AS abrev_tipo,
                       too.data_inicio,
                       too.horario_ini,
                       too.horario_fim,
                       too.local,
                       p.titulo AS titulo_perfil,
                       (SELECT COUNT(*) FROM tab_pagamentos tp
                          WHERE tp.cod_inscrito = i.cod_inscrito
                            AND tp.cod_evento   = i.cod_evento
                            AND tp.status       = 'ATIVADA'
                            AND tp.data_pagamento IS NOT NULL
                            AND tp.data_pagamento <> '0000-00-00 00:00:00') AS qtd_pagamentos_efetuados
                FROM tab_inscritos i
                LEFT JOIN tab_lig_inscritos_oficinas lio
                       ON lio.cod_inscrito = i.cod_inscrito
                      AND lio.cod_evento  = i.cod_evento
                      AND lio.status = 'ATIVADA'
                LEFT JOIN tab_opcoes_oficinas too
                       ON too.cod_oficina = lio.cod_oficina
                LEFT JOIN tab_opcoes_perfis p
                       ON p.cod_perfil = i.cod_perfil
                      AND p.cod_evento = i.cod_evento
                WHERE i.cpf       = '$cpf'
                  AND i.e_mail    = '$email'
                  AND i.cod_evento = $codEvento
                  AND i.status = 'ATIVADA'
                ORDER BY too.data_inicio DESC, i.cod_inscrito DESC
            ";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) || ($rs->EOF)) { return array(); }
            $return = array();
            foreach ($rs as $rows) {
                $obj->setAll($rows);
                $return[] = clone $obj;
            }
            return $return;
        } catch (Throwable $e) {
            // ADODB_Exception ou outro erro — retorna lista vazia (bloco opcional, não pode quebrar a área)
            return array();
        }
    }

    public function getSqlLeftJoin($codEvento, $debug=false){
        
//        $sqlLeftJoinPagamento = " left join (SELECT max(ipi.cod_pagamento), ipi.data_pagamento, ipi.cod_inscrito, ipi.cod_pagamento,ipi.forma_pagamento FROM tab_pagamentos ipi WHERE ipi.status='ATIVADA'
//                        GROUP BY ipi.cod_inscrito) as ipi 
//                       on ti.cod_inscrito=ipi.cod_inscrito "; 
//        $sqlLeftJoinPagamento = " left join (SELECT ipi.cod_pagamento, ipi.data_pagamento, ipi.cod_inscrito, ipi.forma_pagamento 
//            FROM tab_pagamentos ipi 
//            WHERE ipi.cod_pagamento IN (SELECT MAX(ipi.cod_pagamento) FROM tab_pagamentos ipi WHERE ipi.status='ATIVADA' GROUP BY ipi.cod_inscrito))
//            as ipi  on ti.cod_inscrito=ipi.cod_inscrito ";
         $sqlLeftJoinPagamento = "";
                                
        $sqlLeftJoin = "
            left join (SELECT count(ipd.inscrito_id) as total_pedidos, ipd.inscrito_id, sum(ipd.valor_total) as valor_total_pedidos, evento_id FROM pedidos ipd  WHERE (ipd.status='ATIVADA' or ipd.status='aguardando pagamento')
                        GROUP BY ipd.inscrito_id, ipd.evento_id) as ipd ON ti.cod_inscrito=ipd.inscrito_id and ipd.evento_id=ti.cod_evento 
            left join (SELECT count(ip.cod_inscrito) as total_pagamentos, sum(ip.valor) as valor_pagamentos
                        , ip.cod_inscrito
                    FROM tab_pagamentos ip WHERE ((ip.forma_pagamento='BOLETO BANCÁRIO')
                                                or (ip.forma_pagamento='BOLETO') 
                                                or (ip.forma_pagamento='TESOURARIA') 
                                                or (ip.forma_pagamento='BOLETO BANCÁRIO UNIFICADO') 
                                                or (ip.forma_pagamento='CARTAO') 
                                                or (ip.forma_pagamento='CARTAO_CREDITO')
                                                or (ip.forma_pagamento='CARTAO_DEBITO')
                                                or (ip.forma_pagamento='DOACAO') 
                                                or (ip.forma_pagamento='APOIO') 
                                                or (ip.forma_pagamento='DEPOSITO CONTA CORRENTE')
                                                or (ip.forma_pagamento='EMPENHO') 
                                                or (ip.forma_pagamento='INEXIGIBILIDADE') 
                                                or (ip.forma_pagamento='BOLETO A COMPENSAR') 
                                                or (ip.forma_pagamento='CONVÊNIO') 
                                                or (ip.forma_pagamento='TRANSFERENCIA')
                                                or (ip.forma_pagamento='PIX')
                                                or (ip.forma_pagamento='OUTROS')
                                                or (ip.forma_pagamento='OUTRAS')
                                                ) and ip.status='ATIVADA'
                       group by ip.cod_inscrito) as ip 
                       on ti.cod_inscrito=ip.cod_inscrito
           ".$sqlLeftJoinPagamento." 
            left join (SELECT count(tt.cod_inscrito) as total_trabalhos, tt.cod_inscrito FROM tab_trabalhos tt  WHERE tt.status='ATIVADA' AND IFNULL(tt.tipo_documento,'') <> 'COMPROVANTE_PERFIL' /*comprovante de perfil nao conta como trabalho*/
                       group by tt.cod_inscrito) as tt 
                       on ti.cod_inscrito=tt.cod_inscrito

            left join (SELECT max(tti.cod_trabalho), tti.cod_inscrito, tti.instituicao_trabalho FROM tab_trabalhos tti  
                        WHERE tti.status='ATIVADA' group by tti.cod_inscrito, tti.instituicao_trabalho) as tti 
                       on ti.cod_inscrito=tti.cod_inscrito

            left join (SELECT count(ta.cod_inscrito) as total_avaliacoes, ta.cod_inscrito FROM tab_trabalhos_avaliacao ta WHERE ta.status='ATIVADA'
                       group by ta.cod_inscrito) as ta 
                       on ti.cod_inscrito=ta.cod_inscrito
            left join (SELECT count(tlio.cod_inscrito) as total_oficinas, tlio.cod_inscrito, tlio.cod_evento FROM tab_lig_inscritos_oficinas tlio
                        INNER JOIN tab_opcoes_oficinas top ON top.cod_oficina=tlio.cod_oficina 
                        WHERE tlio.status='ATIVADA' AND top.status='ATIVADA' GROUP BY tlio.cod_inscrito, tlio.cod_evento) as tlio 
                       on ti.cod_inscrito=tlio.cod_inscrito AND tlio.cod_evento=ti.cod_evento 
            left join (SELECT count(tlio.cod_inscrito) as total_minicursos, tlio.cod_inscrito, tlio.cod_evento FROM tab_lig_inscritos_oficinas tlio
                        INNER JOIN tab_opcoes_oficinas top ON top.cod_oficina=tlio.cod_oficina  and top.tipo='Minicurso'
                        WHERE tlio.status='ATIVADA' AND top.status='ATIVADA' GROUP BY tlio.cod_inscrito, tlio.cod_evento) as tliom 
                       on ti.cod_inscrito=tliom.cod_inscrito AND tliom.cod_evento=ti.cod_evento 
            left join (SELECT count(tlio.cod_inscrito) as total_simposios, tlio.cod_inscrito, tlio.cod_evento FROM tab_lig_inscritos_oficinas tlio
                        INNER JOIN tab_opcoes_oficinas top ON top.cod_oficina=tlio.cod_oficina and top.tipo='Simpósio'
                        WHERE tlio.status='ATIVADA' AND top.status='ATIVADA' GROUP BY tlio.cod_inscrito, tlio.cod_evento) as tlios 
                       on ti.cod_inscrito=tlios.cod_inscrito AND tlios.cod_evento=ti.cod_evento
            left join (SELECT count(tlio.cod_inscrito) as total_outras_atividades, tlio.cod_inscrito, tlio.cod_evento FROM tab_lig_inscritos_oficinas tlio
                        INNER JOIN tab_opcoes_oficinas top ON top.cod_oficina=tlio.cod_oficina  and top.tipo='Evento Paralelo'
                        WHERE tlio.status='ATIVADA' AND top.status='ATIVADA' GROUP BY tlio.cod_inscrito, tlio.cod_evento) as tlioep 
                       on ti.cod_inscrito=tlioep.cod_inscrito AND tlioep.cod_evento=ti.cod_evento
            left join (SELECT count(tbol.cod_inscrito) as total_boletos, tbol.cod_inscrito FROM tab_boletos tbol WHERE (tbol.status is null OR tbol.status='ATIVADA')
                       group by tbol.cod_inscrito) as tbol 
                       on ti.cod_inscrito=tbol.cod_inscrito
            left join tab_opcoes_perfis top on ti.cod_perfil =  top.cod_perfil 
            
            LEFT JOIN (SELECT distinct cpf,cod_isento, cod_evento FROM tab_isentos) AS tis ON tis.cpf=ti.cpf and tis.cod_evento=ti.cod_evento
            LEFT JOIN (SELECT distinct cod_inscrito,cod_isento as cod_isento_codigo, cod_evento FROM tab_isentos_codigo) AS tisc ON tisc.cod_inscrito=ti.cod_inscrito and tisc.cod_evento=ti.cod_evento


            left join (SELECT tpis.cod_inscrito,COUNT(*) AS total_perfis_isentos, tpis.cod_evento FROM tab_inscritos tpis WHERE 
                                (tpis.cod_perfil IN (SELECT cod_perfil FROM tab_opcoes_perfis WHERE perfil_isento=1))
                                OR (tpis.cod_inscrito IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip 
                                                            INNER JOIN tab_opcoes_perfis op ON tlip.cod_perfil=op.cod_perfil and op.perfil_isento=1 and op.cod_evento=tlip.cod_evento
                                                         )
                                )
                        GROUP BY tpis.cod_inscrito 
                    ) as pis on pis.cod_inscrito = ti.cod_inscrito and pis.cod_evento=ti.cod_evento
                    
            LEFT JOIN (SELECT distinct iv.cod_voucher, cod_inscrito, tv.sequencia_interna, iv.cod_evento FROM tab_lig_inscritos_vouchers iv
                        inner join tab_vouchers tv on tv.cod_voucher=iv.cod_voucher and iv.cod_evento=tv.cod_evento
                        WHERE iv.status='ATIVADA' and tv.status='ATIVADA' ) AS iv ON iv.cod_inscrito=ti.cod_inscrito and iv.cod_evento=ti.cod_evento 
                    
            LEFT JOIN (SELECT count(ivq.cod_voucher) as total_vouchers, cod_inscrito, cod_evento FROM tab_lig_inscritos_vouchers ivq
                        WHERE ivq.status='ATIVADA'  group by cod_inscrito, cod_evento) AS ivq ON ivq.cod_inscrito=ti.cod_inscrito and ivq.cod_evento=ti.cod_evento               
        ";
        return $sqlLeftJoin;
    }
    
    public function carregarWhere($condicoes = false, $debug = false) {

        /** independente da situação, quer dizer extender a busca para 
         * localizar insclusive os registros com situação diferente de ATIVO OU ATIVADO, ou seja, localizar também em cadastros desativados, decidindo ou não se irá aplicar ti.status na busca
         */
        $independenteSituacao = false;
        if($condicoes['negar_filtro']=='1'){
            if($condicoes['e_mail']){
                $this->whereSql .= " and ti.e_mail not like '%".trim($condicoes['e_mail'])."%' ";
                $independenteSituacao = true;
            }
            if($condicoes['estado']){
                $this->whereSql .= " and ti.estado <> '".$condicoes['estado']."' ";
            }
            if($condicoes['cidade']){
                $this->whereSql .= " and ti.cidade <> '".$condicoes['cidade']."' ";
            }
        }else{
            if($condicoes['e_mail']){
                $this->whereSql .= " and ti.e_mail like '%".trim($condicoes['e_mail'])."%' ";
                $independenteSituacao = true;
            }
            if($condicoes['estado']){
                $this->whereSql .= " and ti.estado = '".$condicoes['estado']."' ";
            }
            if($condicoes['cidade']){
                $this->whereSql .= " and ti.cidade = '".$condicoes['cidade']."' ";
            }
        }
        if($condicoes['nome_inscrito']){
            $this->whereSql .= " and (ti.nome_inscrito like '%".trim($condicoes['nome_inscrito'])."%' ";
            $this->whereSql .= " or ti.nome_recibo like '%".trim($condicoes['nome_inscrito'])."%') ";
            $independenteSituacao = true;
        }
        if($condicoes['cod_inscrito']){
            $this->whereSql .= " and ti.cod_inscrito =".trim($condicoes['cod_inscrito'])." ";
            $independenteSituacao = true;
        }
        if($condicoes['id_publico']){
            $this->whereSql .= " and ti.id_publico =".trim($condicoes['id_publico'])." ";
            $independenteSituacao = true;
        }
        if($condicoes['cod_trabalho']){
            $this->whereSql .= " and (ti.cod_inscrito, ti.cod_evento) IN (SELECT cod_inscrito, cod_evento FROM tab_trabalhos WHERE cod_trabalho=".trim($condicoes['cod_trabalho'])." AND status='ATIVADA') ";
            $independenteSituacao = true;
        }
        if($condicoes['cpf']){
            $this->whereSql .= " and (ti.cpf =".trim($condicoes['cpf'])." ";
            $this->whereSql .= " or ti.cpf_recibo =".trim($condicoes['cpf']).") ";
            $independenteSituacao = true;
        }
        if($condicoes['fone_contato']){
            // $this->whereSql .= " and ti.fone_contato like '%".trim($condicoes['fone_contato'])."%' ";
            $this->whereSql .= " and REPLACE(REPLACE(REPLACE(ti.fone_contato, '-', ''), ' ', ''), '(', '') like '%".trim($condicoes['fone_contato'])."%' ";
            $independenteSituacao = true;
        }
        if($condicoes['status_processo']){
            $this->whereSql .= " and ti.status_processo ='".$condicoes['status_processo']."' ";
        }

        if($condicoes['situacao'] and $independenteSituacao == false){
            $this->whereSql .= " and ti.status ='".$condicoes['situacao']."' ";
        }
        if($_SERVER['REMOTE_ADDR']=='201.66.95.230'){
//            $this->whereSql .= " and ti.categoria_vaga is null ";
        }
        if($condicoes['presenca_inscrito']){
            if($condicoes['presenca_inscrito']==''){
            }else if($condicoes['presenca_inscrito']=='SIM'){
                // $this->whereSql .= " and ti.flg_presenca_inscrito IS NOT NULL ";
                $this->whereSql .= " and ti.flg_presenca_inscrito = 1 ";
            }else if($condicoes['presenca_inscrito']=='NAO'){
                $this->whereSql .= " and (ti.flg_presenca_inscrito IS NULL or ti.flg_presenca_inscrito <> 1) ";
            }
        }
        if($condicoes['outros_filtros_inscrito']){
            if($condicoes['outros_filtros_inscrito']=='avaliou_evento'){
                $this->whereSql .= " and ti.avaliou_evento IS NOT NULL ";
            }
        }
        if($condicoes['tipo_inscricao']){
            $this->whereSql .= " and ti.tipo_inscricao= '".$condicoes['tipo_inscricao']."' ";
        }   

        // if($condicoes['cod_perfil']){
        //     $this->whereSql .= ""
        //             . " and "
        //             . "("
        //             . " ti.cod_perfil= '".$condicoes['cod_perfil']."' "
        //             . " or "
        //             . " ti.cod_inscrito in (SELECT ip.cod_inscrito "
        //                     . " FROM tab_lig_inscritos_perfis ip "
        //                     . " WHERE ip.cod_perfil = '".$condicoes['cod_perfil']."'"
        //                     . " and ip.status = 'ATIVADA')"
        //              . " )";
        // }         
        if (!empty($condicoes['cod_perfil'])) {
            
            // echo __LINE__.'-'.$condicoes['cod_perfil'].'<-';
            // Se for array, converte em lista para o IN (...)
            $listaPerfil = $condicoes['cod_perfil'];
            if (is_array($condicoes['cod_perfil'])) {
                $listaPerfis = implode("','", array_map('addslashes', $condicoes['cod_perfil']));
                $filtroPerfil = "IN ('" . $listaPerfis . "')";
            } else {
                $filtroPerfil = "= '" . addslashes($condicoes['cod_perfil']) . "'";
            }
            if($filtroPerfil!=''){
                $this->whereSql .= " AND (
                    ti.cod_perfil $filtroPerfil
                    OR ti.cod_inscrito IN (
                        SELECT ip.cod_inscrito
                        FROM tab_lig_inscritos_perfis ip
                        WHERE ip.cod_perfil $filtroPerfil
                        AND ip.status = 'ATIVADA'
                    )
                )";
            }
        }
   
    }
    public function getByEmail(Inscrito $obj, $debug=false) {
        try{
                    $sql = "SELECT ti.* "
                    . $this->sqlLeftJoinColuns
                    . "FROM tab_inscritos ti "
                    . $this->getSqlLeftJoin($obj->getCodEvento())
                    . " WHERE ti.cod_evento=".$obj->getCodEvento()
                    . " and ti.e_mail= '".$obj->getEMail()."' and ti.status='ATIVADA' ";

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
    public function getByEmailCredencial(Inscrito $obj, $debug=false) {
        try{
            $whereCodEvento = "";
            if($obj->getCodEvento()){
                $whereCodEvento = " and ti.cod_evento=".$obj->getCodEvento();
            }
            $sql = "SELECT ti.* "
            . $this->sqlLeftJoinColuns
            . "FROM tab_inscritos ti "
            . $this->getSqlLeftJoin($obj->getCodEvento())
            . " WHERE ti.e_mail= '".$obj->getEMail()."' and ti.status='ATIVADA' "
            . $whereCodEvento
            ." order by ti.cod_inscrito desc limit 1";

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
    public function localizarInscricaoAtivada(Inscrito $obj, $debug=false) {
        try{
            $sql = "SELECT ti.* "
            . $this->sqlLeftJoinColuns
            . "FROM tab_inscritos ti "
            . $this->getSqlLeftJoin($obj->getCodEvento())
            . " WHERE ti.cod_evento  = ".$obj->getCodEvento();

            $obj->getCodInscrito()?  $sql = $sql . " and ti.cod_inscrito  = ".$obj->getCodInscrito()  : "";
            $obj->getCpf()?          $sql = $sql . " and ti.cpf           = ".$obj->getCpf()          : "";
            $obj->getNomeInscrito()? $sql = $sql . " and ti.nome_inscrito like '%".$obj->getNomeInscrito()."%'" : "";
            $obj->getEMail()?        $sql = $sql . " and ti.e_mail        like '%".$obj->getEMail()."%'"        : "";
            $sql = $sql . " and ti.status='ATIVADA'";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaInscritos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getById(Inscrito $obj, $debug=false) {
        try{
                    $sql = "SELECT ti.*, e.gestor "
                    . $this->sqlLeftJoinColuns
                    . "FROM tab_inscritos ti "
                    . " INNER JOIN tab_eventos e on e.cod_evento=ti.cod_evento "        
                    . $this->getSqlLeftJoin($obj->getCodEvento())
                    . " WHERE ti.cod_evento=".$obj->getCodEvento()
                    . " and ti.cod_inscrito= ".$obj->getCodInscrito();

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
    public function getByQrCode(Inscrito $obj, $debug=false) {
        try{
                    $sql = "SELECT ti.* "
                    . $this->sqlLeftJoinColuns
                    . "FROM tab_inscritos ti "
                    . $this->getSqlLeftJoin($obj->getCodEvento())
                    . " WHERE ti.cod_evento=".$obj->getCodEvento()
                    . " and ti.qr_code= '".$obj->getQrCode()."'";

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
    public function getByIdSemEvento(Inscrito $obj, $debug=false) {
        try{
                    $sql = "SELECT ti.*, op.titulo as titulo_perfil "
                    . "FROM tab_inscritos ti "
                            . " INNER JOIN tab_opcoes_perfis op ON op.cod_perfil=ti.cod_perfil "
                    . "WHERE ti.cod_inscrito= ".$obj->getCodInscrito();

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
            $sql = "SELECT ifnull(MAX(cod_inscrito),0)+1 FROM tab_inscritos";

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
    public function carregarParticipacaoByLoginCpf(Inscrito $obj, $debug=false) {
        try {
            
            $sql = sprintf("SELECT ti.cod_inscrito,ti.cod_evento,ti.nome_inscrito,su.cod_usuario, ti.e_mail"
                    . " FROM tab_inscritos ti "
                    . " left join sis_usuarios su on su.login=ti.e_mail and ti.cod_evento=su.cod_evento "
                    . " WHERE ti.cod_evento='%s' and ti.e_mail= '%s' and ti.cpf='%s'"
                    . " and ti.status = 'ATIVADA'"
                    . " ", $obj->getCodEvento(), $obj->getLogin(), $obj->getCpf());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function carregarParticipacaoByLoginCodInscrito(Inscrito $obj, $debug=false) {
        try {
            
            $sql = sprintf("SELECT ti.cod_inscrito,ti.cod_evento,ti.nome_inscrito,su.cod_usuario, ti.e_mail"
                    . " FROM tab_inscritos ti "
                    . " left join sis_usuarios su on su.login=ti.e_mail and ti.cod_evento=su.cod_evento "
                    . " WHERE ti.cod_evento='%s' and ti.e_mail= '%s' and ti.cod_inscrito='%s'"
                    . " and ti.status = 'ATIVADA'"
                    . " ", $obj->getCodEvento(), $obj->getLogin(), $obj->getCodInscrito());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function carregarParticipacaoByLogin(Inscrito $obj, $debug=false) {
        try {
            
            $sql = sprintf("SELECT ti.cod_inscrito,ti.cod_evento,ti.nome_inscrito,su.cod_usuario, ti.e_mail"
                    . " FROM tab_inscritos ti "
                    . " left join sis_usuarios su on su.login=ti.e_mail and ti.cod_evento=su.cod_evento "
                    . " WHERE ti.cod_evento='%s' and ti.e_mail= '%s'"
                    . " and ti.status = 'ATIVADA'"
                    . " ", $obj->getCodEvento(), $obj->getLogin());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function efetuarLogin(Inscrito $obj, $debug=false) {
        try {
            $whereEmailCodInscrito="";
            if($obj->getEmail()!='' and $obj->getSenha()!=''){
                $whereEmailCodInscrito=" and (ti.e_mail = '".$obj->getEmail()."' and ti.cod_inscrito= '".$obj->getSenha()."')";
            }
            if($obj->getEmail()!='' and $obj->getSenha()!=''){
                $whereEmailCodInscrito=" and (ti.e_mail = '".$obj->getEmail()."' and ti.cpf= '".$obj->getSenha()."')";
            }
            if($obj->getCpf()!='' and $obj->getSenha()!=''){
                $whereEmailCodInscrito=" and (ti.e_mail = '".$obj->getEmail()."' and ti.senha= '".md5($obj->getSenha())."')";
            }

            // Segundo fator obrigatorio: 4 ultimos digitos do CPF tem de bater.
            // Quem nao tem CPF cadastrado (ti.cpf vazio) NAO loga -- decisao do
            // usuario 2026-08-14: o cadastro precisa ser completado antes.
            // EXCECAO (liberados): o campo ainda e pedido, mas nao precisa bater.
            // Hoje: perfil 469 (Membro do NRE) no evento 82 -- 12 sem CPF ficariam trancados.
            $where4cpf = "";
            if ($obj->get4cpf() != '') {
                $where4cpf = " AND ( RIGHT(ti.cpf, 4) = '" . $obj->get4cpf() . "'"
                           . " OR (ti.cod_evento = 82 AND ti.cod_perfil = 469) )";
            }

            // Login por NUMERO da inscricao (permissivo: entra pelo e-mail ou pelo CPF
            // como identificador, sempre com a senha = cod_inscrito). Removidas so as
            // formas perigosas (2026-08-14): CPF como senha e e-mail como senha.
            // As combinacoes com senha_criptografada(md5) ficam (coluna vazia = inofensivas).
            //
            // VERSAO ANTERIOR (6 combinacoes, com CPF-senha e e-mail-senha) preservada
            // abaixo -- se der algum problema, e so trocar o $sql novo por este:
            /*
            $sql = sprintf("SELECT ti.cod_inscrito,ti.cod_evento,ti.nome_inscrito,su.cod_usuario, ti.e_mail"
                    . " FROM tab_inscritos ti "
                    . " left join sis_usuarios su on su.login=ti.e_mail and ti.cod_evento=su.cod_evento "
                    . " WHERE ti.cod_evento='%s' "
                    . " and ((ti.e_mail = '%s' and ti.cod_inscrito= '%s')"
                    . " or (ti.e_mail   = '%s' and ti.cpf  = '%s')"
                    . " or (ti.e_mail   = '%s' and ti.senha_criptografada= '%s')"
                    . " or (ti.cpf = '%s' and ti.e_mail= '%s')"
                    . " or (ti.cpf = '%s' and ti.cod_inscrito= '%s')"
                    . " or (ti.cpf = '%s' and ti.senha_criptografada= '%s')"
                    . ") and ti.status = 'ATIVADA'"
                    . " and ti.origem_cadastro = 'INSCRICAO'"
                    . $where4cpf
                    . " ", $obj->getCodEvento()
                    ,$obj->getEMail(),$obj->getSenha(), $obj->getEMail(),$obj->getSenha(), $obj->getEmail(),md5($obj->getSenha())
                    ,$obj->getCpf(),$obj->getSenha()  , $obj->getCpf(),$obj->getSenha()  , $obj->getCpf(),md5($obj->getSenha()));
            */
            $sql = sprintf("SELECT ti.cod_inscrito,ti.cod_evento,ti.nome_inscrito,su.cod_usuario, ti.e_mail"
                    . " FROM tab_inscritos ti "
                    . " left join sis_usuarios su on su.login=ti.e_mail and ti.cod_evento=su.cod_evento "
                    . " WHERE ti.cod_evento='%s' "
                    . " and ((ti.e_mail = '%s' and ti.cod_inscrito= '%s')"
                    . " or (ti.e_mail   = '%s' and ti.senha_criptografada= '%s')"
                    . " or (ti.cpf = '%s' and ti.cod_inscrito= '%s')"
                    . " or (ti.cpf = '%s' and ti.senha_criptografada= '%s')"
                    . ") and ti.status = 'ATIVADA'"
                    . " and ti.origem_cadastro = 'INSCRICAO'"
                    . $where4cpf
                    . " ", $obj->getCodEvento()
                    ,$obj->getEMail(),$obj->getSenha(), $obj->getEmail(),md5($obj->getSenha())
                    ,$obj->getCpf(),$obj->getSenha(), $obj->getCpf(),md5($obj->getSenha()));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function efetuarLoginSemEvento(Inscrito $obj, $debug=false) {
        try {
            $whereEmailCodInscrito="";
            if($obj->getEmail()!='' and $obj->getSenha()!=''){
                $whereEmailCodInscrito=" and (ti.e_mail = '".$obj->getEmail()."' and ti.cod_inscrito= '".$obj->getSenha()."')";
            }
            if($obj->getEmail()!='' and $obj->getSenha()!=''){
                $whereEmailCodInscrito=" and (ti.e_mail = '".$obj->getEmail()."' and ti.cpf= '".$obj->getSenha()."')";
            }
            if($obj->getCpf()!='' and $obj->getSenha()!=''){
                $whereEmailCodInscrito=" and (ti.e_mail = '".$obj->getEmail()."' and ti.senha= '".md5($obj->getSenha())."')";
            }

            // Segundo fator obrigatorio: 4 ultimos digitos do CPF tem de bater.
            // Quem nao tem CPF cadastrado (ti.cpf vazio) NAO loga -- decisao do
            // usuario 2026-08-14: o cadastro precisa ser completado antes.
            // EXCECAO (liberados): o campo ainda e pedido, mas nao precisa bater.
            // Hoje: perfil 469 (Membro do NRE) no evento 82 -- 12 sem CPF ficariam trancados.
            $where4cpf = "";
            if ($obj->get4cpf() != '') {
                $where4cpf = " AND ( RIGHT(ti.cpf, 4) = '" . $obj->get4cpf() . "'"
                           . " OR (ti.cod_evento = 82 AND ti.cod_perfil = 469) )";
            }
            
            // Mesmas combinacoes de efetuarLogin (sem filtro de evento = acessa qualquer
            // evento). Removidas as formas email+cpf e cpf+email; mantido o login por numero.
            // VERSAO ANTERIOR (6 combinacoes) preservada abaixo para reverter se preciso:
            /*
            $sql = sprintf("SELECT ti.cod_inscrito,ti.cod_evento,ti.nome_inscrito,su.cod_usuario, ti.e_mail"
                    . " FROM tab_inscritos ti "
                    . " left join sis_usuarios su on su.login=ti.e_mail and ti.cod_evento=su.cod_evento "
                    . " WHERE ((ti.e_mail = '%s' and ti.cod_inscrito= '%s')"
                    . " or (ti.e_mail   = '%s' and ti.cpf  = '%s')"
                    . " or (ti.e_mail   = '%s' and ti.senha_criptografada= '%s')"
                    . " or (ti.cpf = '%s' and ti.e_mail= '%s')"
                    . " or (ti.cpf = '%s' and ti.cod_inscrito= '%s')"
                    . " or (ti.cpf = '%s' and ti.senha_criptografada= '%s')"
                    . ") and ti.status = 'ATIVADA'"
                    . " and ti.origem_cadastro = 'INSCRICAO'"
                    . $where4cpf
                    . " ", $obj->getEMail(),$obj->getSenha(), $obj->getEMail(),$obj->getSenha(), $obj->getEmail(),md5($obj->getSenha())
                    ,$obj->getCpf(),$obj->getSenha()  , $obj->getCpf(),$obj->getSenha()  , $obj->getCpf(),md5($obj->getSenha()));
            */
            $sql = sprintf("SELECT ti.cod_inscrito,ti.cod_evento,ti.nome_inscrito,su.cod_usuario, ti.e_mail"
                    . " FROM tab_inscritos ti "
                    . " left join sis_usuarios su on su.login=ti.e_mail and ti.cod_evento=su.cod_evento "
                    . " WHERE ((ti.e_mail = '%s' and ti.cod_inscrito= '%s')"
                    . " or (ti.e_mail   = '%s' and ti.senha_criptografada= '%s')"
                    . " or (ti.cpf = '%s' and ti.cod_inscrito= '%s')"
                    . " or (ti.cpf = '%s' and ti.senha_criptografada= '%s')"
                    . ") and ti.status = 'ATIVADA'"
                    . " and ti.origem_cadastro = 'INSCRICAO'"
                    . $where4cpf
                    . " ", $obj->getEMail(),$obj->getSenha(), $obj->getEmail(),md5($obj->getSenha())
                    ,$obj->getCpf(),$obj->getSenha(), $obj->getCpf(),md5($obj->getSenha()));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function efetuarLoginSisUsuario(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT max(lue.cod_evento) as cod_evento, su.cod_usuario, '
                    . ' IFNULL(su.login,\'Anonimo\') nome_convidado, '
                    . ' su.senha as senha_legada, su.senha_hash '
                    . ' FROM sis_usuarios su '
                    . ' INNER JOIN sis_ligacao_usuarios_eventos lue ON lue.cod_usuario=su.cod_usuario '
                    . ' WHERE su.status = \'ATIVADA\' AND su.login IS NOT NULL'
                    . ' AND su.login= \'%s\' '
                    . ' GROUP BY su.cod_usuario '
                    . ' ORDER BY su.cod_usuario DESC LIMIT 1', addslashes($obj->getEMail()));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
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
    public function getByEmailInscritoApto(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito FROM tab_inscritos_aptos_carga WHERE '
            . ' (email= \'%s\' or emailProf= \'%s\' or emailRes= \'%s\' or email2 = \'%s\') '
            . ' and cod_evento=\'%s\' and status = \'ATIVADA\' ',$obj->getEMail(),$obj->getEMail(),$obj->getEMail(),$obj->getEMail(), $obj->getCodEvento());

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
    public function getByCpfInscritoApto(Inscrito $obj, $debug=false) {
        try {
            
            $sql = sprintf('SELECT cod_inscrito FROM tab_inscritos_aptos_carga WHERE '
            . ' cpf=\'%s\' and cod_evento=\'%s\' and status = \'ATIVADA\' ',$obj->getCpf(), $obj->getCodEvento());

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
    public function getByEmailExiste(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT e_mail FROM tab_inscritos WHERE cod_evento=\'%s\' '
                    . 'and e_mail= \'%s\' and status = \'ATIVADA\' ', $obj->getCodEvento(),$obj->getEMail());

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
    public function getByCpfExisteMesmoPerfil(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cpf FROM tab_inscritos WHERE cod_evento=\'%s\' '
                    . 'and cpf= \'%s\' and cod_perfil= \'%s\' and status = \'ATIVADA\' ', $obj->getCodEvento(),$obj->getCpf(),$obj->getCodPerfil());

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
    public function getByEmailExistePopular(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_inscritos WHERE cod_evento=\'%s\' '
                    . 'and e_mail= \'%s\' and status = \'ATIVADA\' ', $obj->getCodEvento(),$obj->getEMail());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach($rs as $rows){
                    $obj->setAll($rows);
                }                
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function getByCpfExiste(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_inscritos WHERE cod_evento=\'%s\' '
                    . 'and cpf= \'%s\' and status = \'ATIVADA\' ', $obj->getCodEvento(),$obj->getCpf());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach($rs as $rows){
                    $obj->setAll($rows);
                }
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function getByTransacaoIdExiste(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_inscritos WHERE cod_evento=\'%s\' '
                    . 'and transacao_id= \'%s\' and status = \'ATIVADA\' ', $obj->getCodEvento(),$obj->getTransacaoId());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach($rs as $rows){
                    $obj->setAll($rows);
                }
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function getByIdExiste(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito FROM tab_inscritos WHERE cod_evento=\'%s\' '
                    . 'and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());

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
    public function getAll(Inscrito $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_inscritos WHERE cod_evento='.$obj->getCodEvento().' order by cod_evento desc');

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
    public function carregarListaInscritos(Inscrito $obj, $condicoes = false, $debug=false) {
        try{
            $this->carregarWhere($condicoes, $debug);
                    //print_r($condicoes);
            
            if($condicoes['cod_evento']!='' and ($condicoes['cod_evento']!='-1')){
                $this->codEvento = $condicoes['cod_evento'];
                $obj->setCodEvento($this->codEvento);
            }else{
                $this->codEvento = COD_EVENTO;
            }
            // API: busca em todos os eventos sem filtrar por usuario
            if (!empty($condicoes['todos_eventos'])) {
                $lista_eventos = '';
            } else {
                $objEvento = new Evento();
                $objEvento->setCodUsuario($_SESSION['_cod_usuario']);
                $objEvento->carregarListaEventosByIdLogin($debug);

                foreach ($objEvento->getListaEventos() as $objEvL) {
                    $lista_eventos .= ','.$objEvL->getCodEvento();
                }
                $lista_eventos = substr($lista_eventos,1, strlen($lista_eventos));
            }
//            echo $lista_eventos;      
          
            $innerJoinPatrocinadorVoucher = InscritoFiltroDAO::getJoinPatrocinadorVoucher($condicoes);

            $innerJoinAreaTematica= InscritoFiltroDAO::getInnerJoinAreaTematica($condicoes);

            $innerJoinOficina= "";
//          $innerJoinOficina = InscritoFiltroDAO::getInnerJoinOficina($condicoes);            
//            if($condicoes['cod_oficina']){
//                $innerJoinOficina = " inner join tab_lig_inscritos_oficinas lto"
//                        . " on lto.cod_inscrito=ti.cod_inscrito and lto.cod_oficina = ".$condicoes['cod_oficina']." ";
//            }
            $innerJoinMacrorregiao = "";
           if($condicoes['cod_macrorregiao']){
               $innerJoinMacrorregiao = " inner join tab_lig_inscritos_oficinas ltmo"
                       . " on ltmo.cod_inscrito=ti.cod_inscrito 
                       inner join tab_opcoes_oficinas toom on ltmo.cod_oficina = toom.cod_oficina
                        and toom.cod_macrorregiao = ".$condicoes['cod_macrorregiao']." ";
           }

            $whereCodOficina = InscritoFiltroDAO::getWhereCodOficina($condicoes);
            
            $leftJoinOficina= "";
            $leftJoinOficinaCampos= "";
            if($condicoes['cod_oficina']
                or $condicoes['perspectiva']=='listagemComAtividade'
                or $condicoes['perspectiva']=='listagemAvaliacao'
                or $condicoes['perspectiva']=='listagemClassificacao'
                or $condicoes['perspectiva']=='listagemRPA'){
                $leftJoinOficina = " left join tab_lig_inscritos_oficinas llto on llto.cod_inscrito=ti.cod_inscrito and llto.status='ATIVADA'
                left join tab_opcoes_oficinas too on  llto.cod_oficina =  too.cod_oficina";
                $this->sqlLeftJoinColuns = $this->sqlLeftJoinColuns
                                        . " , IFNULL(llto.cod_oficina,'') as cod_oficina
                                        , llto.flg_presenca as flg_presenca_oficina
                                        , too.ordem as ordem_oficina ";
                if(in_array($condicoes['cod_evento'], array(35,52,72,79,90,105,119))){
                     $this->sqlLeftJoinColuns = $this->sqlLeftJoinColuns ." , too.data_inicio as data_agendada ";   
                }
                $this->sqlLeftJoinColuns = $this->sqlLeftJoinColuns .", too.titulo as nome_oficina ";   
            }
            
            $wherePagamentos = InscritoFiltroDAO::getWherePagamentos($condicoes, $this->codEvento);

            $innerJoinPedidoId = "";
            $wherePedidoId = "";
            if($condicoes['pedido_id']!=''){
                $wherePedidoId = " and p.id = '".$condicoes['pedido_id']."' ";
                $innerJoinPedidoId = " left JOIN pedidos p on ti.cod_inscrito=p.inscrito_id  ";
            }   
            if($condicoes['transacao_id']!=''){
                $transacao = $condicoes['transacao_id'];
                $transacao = str_replace('-', '', $transacao);
                $wherePedidoId .= " and REPLACE(tpg.transacao_id, '-', '') = '".$transacao."' ";                
                $innerJoinPedidoId = " left JOIN tab_pagamentos tpg on ti.cod_inscrito=tpg.cod_inscrito  ";
            }   
                     
            $whereQtdPedidos = InscritoFiltroDAO::getWhereQtdPedidos($condicoes);
        
            $whereFormaPagamento = "";
            $sqlLeftJoinPagamento = "";
            $sqlCamposPagamentosIndividual = "";
            $orderByPagamento = "";
            if(($condicoes['tipo_pedido']=='mensalidade')
                    or($condicoes['perspectiva']=='listagemPagamento')
                    or($condicoes['xml_pagamentos']=='104')
                    or($condicoes['forma_pagamento']!='')
                    or($condicoes['data_pagamento_ini'] and $condicoes['data_pagamento_fim'])
                    or($condicoes['data_pagamento_incl_ini'] and $condicoes['data_pagamento_incl_fim'])){
//                    or($condicoes['data_vencimento_ini'] and $condicoes['data_vencimento_fim'])){
            //if(($condicoes['pagamentos']!='-1')or($condicoes['forma_pagamento'])or($condicoes['data_pagamento_ini'] and $condicoes['data_pagamento_fim'])){

                $sqlCamposPagamentosIndividual .= ""
                . ", IFNULL(ipp.data_pagamento,ipp.data_inclusao) as data_pagamento  "
                . ", ipp.cod_pagamento "
                 . ", CONCAT(te.projeto,'-',te.cod_evento,'-',ti.cod_inscrito,'-',ipp.cod_pagamento) as transacao_id "
                . ", CONCAT(te.projeto,'-',te.cod_evento,'-',ti.cod_inscrito,'-',ipp.cod_pagamento) as transacao_id "
                . ", ipp.forma_pagamento "
                . ", ipp.valor as valor_pagamentos "
                . ", calcular_taxa_pagseguro(ipp.forma_pagamento, ipp.valor) as valor_taxa "
                . ", ipp.codigo_transacao "
                ."";
                
                $sqlLeftJoinPagamento = " left join (SELECT ipp.cod_pagamento, ipp.cod_evento,ipp.cod_inscrito, ipp.forma_pagamento, ipp.data_pagamento, ipp.data_inclusao, ipp.status, ipp.pedido_id, ipp.valor
                    , ipp.transacao_id as codigo_transacao
                FROM tab_pagamentos ipp) as ipp  on ti.cod_inscrito=ipp.cod_inscrito and ipp.cod_evento=ti.cod_evento";              
                
                $orderByPagamento = " ,ipp.cod_pagamento ";
                
                if($condicoes['forma_pagamento']=='BOLETO'){
                    $whereFormaPagamento = " and (ipp.forma_pagamento = 'BOLETO' or ipp.forma_pagamento = 'BOLETO BANCÁRIO' or ipp.forma_pagamento = 'BOLETO BANCÁRIO UNIFICADO') ";
                }
                if($condicoes['forma_pagamento']=='DEPOSITO-CC'){
                    $whereFormaPagamento = " and ipp.forma_pagamento = 'DEPOSITO CONTA CORRENTE' "; 
                }
                if($condicoes['forma_pagamento']=='OUTROS'){
                    $whereFormaPagamento = " and ipp.forma_pagamento = 'Outras' ";
                }
                if($condicoes['forma_pagamento']=='EMPENHO'){
                    $whereFormaPagamento = " and ipp.forma_pagamento = 'EMPENHO' ";
                }
                if($condicoes['forma_pagamento']=='CARTAO'){
                    $whereFormaPagamento = " and (ipp.forma_pagamento='CARTAO' or ipp.forma_pagamento='CARTAO_CREDITO' or ipp.forma_pagamento='CARTAO_DEBITO') ";
                }
                if($condicoes['forma_pagamento']=='PIX'){
                    $whereFormaPagamento = " and ipp.forma_pagamento = 'PIX' ";
                }
                if($condicoes['forma_pagamento']=='TESOURARIA'){
                    $whereFormaPagamento = " and ipp.forma_pagamento = 'TESOURARIA' ";
                }
                if($condicoes['forma_pagamento']=='PAG.TERCEIRO'){
                    $whereFormaPagamento = " and ti.institucional = '1' ";
                }
                $whereFormaPagamento .= " and ipp.status = 'ATIVADA' ";

                $whereDataPagamento = InscritoFiltroDAO::getWhereDataPagamento($condicoes);          
            }

            $whereDataInscricao = InscritoFiltroDAO::getWhereDataInscricao($condicoes);
            
            $sqlLeftJoinPedido = "";
            $wherePedidos = "";
            $wherePedidosMensalidade = "";
            $whereTipoPedido = "";
            if(($condicoes['tipo_pedido']=='mensalidade') or ($condicoes['xml_pagamentos']=='104')){
            // if($condicoes['tipo_pedido']=='mensalidade'){
                $pedidoJoin = " inner join ";
                /** @todo nesse tratamento exclui a exigencia de haver um pedido criado para cada pagamento carregado */
                if(COD_EVENTO==31 OR COD_EVENTO==32 OR COD_EVENTO==91){
                    $pedidoJoin = " left join ";
                }
                if ($condicoes['xml_pagamentos_carregados']=='nao-carregados'){ 
                    $pedidoJoin = " left join ";
                }    
                if($condicoes['tipo_pedido']=='mensalidade'){
                   $wherePedidosMensalidade = " and ippe.tipo_pedido='mensalidade' ";
                }                
                $sqlLeftJoinPedido = $pedidoJoin." (SELECT ippe.id, ippe.evento_id, ippe.inscrito_id, ippe.valor_total 
                            , ippe.descricao as descricao_pedido, ippe.status, ippe.tipo as tipo_pedido, ippe.dat_vencimento
                            FROM pedidos ippe WHERE ippe.status<>'DESATIVADA') as ippe 
                                on ippe.inscrito_id = ipp.cod_inscrito 
                               and ippe.evento_id   = ipp.cod_evento 
                               and ippe.id          = ipp.pedido_id 
                               ".$wherePedidosMensalidade;
////                /*somente */
//                if($condicoes['tipo_pedido']=='mensalidade'){
//                    //$wherePedidos = " and ippe.tipo_pedido='mensalidade' ";
//                }
            }
            /*Inscrições com pedido vencido*/
            $sqlDistinct = " distinct ";
            if($condicoes['data_vencimento_ini'] and $condicoes['data_vencimento_fim']){
                $whereDataVencimento= "";
                if($condicoes['data_vencimento_ini'] and $condicoes['data_vencimento_fim']){
                    $whereDataVencimento = " (ippe.dat_vencimento between '".formatDateInsert($condicoes['data_vencimento_ini'])."'"
                                                                  . " and '".formatDateInsert($condicoes['data_vencimento_fim'])."')";
                }                  
                
                $sqlLeftJoinPedido = " 
                inner join (SELECT ippe.id, ippe.evento_id, ippe.inscrito_id, ippe.valor_total
                            , ippe.descricao, ippe.status, ippe.tipo as tipo_pedido, ippe.dat_vencimento
                            FROM pedidos as ippe
                            left join tab_pagamentos p on p.pedido_id=ippe.id and p.status='ATIVADA'
                            WHERE ".$whereDataVencimento." 
                                and ippe.status<>'DESATIVADA' and p.cod_pagamento IS NULL )
                                as ippe 
                                on ippe.inscrito_id = ti.cod_inscrito 
                                and ippe.evento_id  = ti.cod_evento 
                                ";
                  $sqlDistinct = " distinct ";
//                and ippe.id         = ipp.pedido_id 
//                echo $sqlLeftJoinPedido;;
//                $sqlLeftJoinPedido= "";
            }
            
            $sqlLeftJoinCertificado = "";
            $whereCertificados = "";
            if($condicoes['certificado']=='curso'){
                $sqlLeftJoinCertificados = " 
                inner join (SELECT 
                FROM pedidos ippe) as ippe 
                        on ippe.inscrito_id = ipp.cod_inscrito 
                       and ippe.evento_id   = ipp.cod_evento 
                       and ippe.id          = ipp.pedido_id 
                       and (ippe.tipo_pedido = 'mensalidade')";
            }
            
            $whereTipoInscricao = InscritoFiltroDAO::getWhereTipoInscricao($condicoes);         
            
            $whereAlmoco = InscritoFiltroDAO::getWhereAlmoco ($condicoes);

            $leftJoinAvaliacaoTrabalhos = "";
            $selectLeftJoinAvaliacaoTrabalhos = "";
            $whereAvaliacaoTrabalhos = "";
            if($condicoes['avaliacao_trabalhos']){
                 $selectLeftJoinAvaliacaoTrabalhos = " , IFNULL(tt_aprovados.total_aprovados, 0) AS total_trab_aprovados ";
                 $leftJoinAvaliacaoTrabalhos = " LEFT JOIN (
                SELECT COUNT(*) AS total_aprovados, tt.cod_inscrito, tt.resumo_trabalho
                FROM tab_trabalhos tt
                INNER JOIN (
                    SELECT MAX(cod_avaliacao) AS cod_avaliacao, cod_trabalho
                    FROM tab_historico_avaliacao
                    WHERE status = 'ATIVADA' AND cod_evento = ".$this->codEvento."
                    GROUP BY cod_trabalho
                ) AS ult_avaliacao ON ult_avaliacao.cod_trabalho = tt.cod_trabalho
                INNER JOIN tab_historico_avaliacao tha ON tha.cod_avaliacao = ult_avaliacao.cod_avaliacao
                WHERE tha.status = 'ATIVADA'
                AND tha.avaliacao = 'APROVADO'
                AND tt.status = 'ATIVADA'
                GROUP BY tt.cod_inscrito
                ) AS tt_aprovados ON ti.cod_inscrito = tt_aprovados.cod_inscrito ";

                $whereAvaliacaoTrabalhos = " and tt_aprovados.total_aprovados>=".$condicoes['avaliacao_trabalhos']." ";

            }

            $whereTrabalhos = InscritoFiltroDAO::getWhereTrabalhos($condicoes);

            $result = InscritoFiltroDAO::getSqlSituacaoEventoVinculado($condicoes);
            $selectSituacaoEventoVinculado   = $result['select'];
            $leftJoinSituacaoEventoVinculado = $result['leftJoin'];
            $whereSituacaoEventoVinculado    = $result['where'];
            unset($result);

            /** @todo os filtros abaixo estão sendo feito de forma especifica para um tipo documento, o problema é que esses tipos de documentos são dinamicos, podemos criar novos tipos, e da forma que esta, exige a criação de um novo filtro para cada tipo de documento. */
            $result = InscritoFiltroDAO::getSqlContratos($condicoes);
            $selectInscritoContratos   = $result['select'];
            $leftJoinInscritoContratos = $result['leftJoin'];
            $whereContratos            = $result['where'];
            unset($result);

            $result = InscritoFiltroDAO::getFlgAssinado($condicoes);
            $selectFlgAssinado = $result['select'];
            $leftJoinFlgAssinado = $result['leftJoin'];
            $whereFlgAssinado= $result['where'];
            unset($result);
            
            $result = InscritoFiltroDAO::getInscritoRPAs($condicoes);
            $selectInscritoRPAs = $result['select'];
            $leftJoinInscritoRPAs = $result['leftJoin'];
            $whereRPAs= $result['where'];
            unset($result);
           
            $result = InscritoFiltroDAO::getInscritoCompTrans($condicoes);
            $selectInscritoCompTrans = $result['select'];
            $leftJoinInscritoCompTrans = $result['leftJoin'];
            $whereCompTrans= $result['where'];
            unset($result);

            $result = InscritoFiltroDAO::getInscritoTermoDesist($condicoes);
            $selectInscritoTermoDesist = $result['select'];
            $leftJoinInscritoTermoDesist = $result['leftJoin'];
            $whereTermoDesist= $result['where'];
            unset($result);

            $result = InscritoFiltroDAO::getInscritoBoletos($condicoes);
            $selectInscritoBoletos = $result['select'];
            $leftJoinInscritoBoletos = $result['leftJoin'];
            $whereBoletos= $result['where'];
            unset($result);

            $result = InscritoFiltroDAO::getDocsContratacao($condicoes);
            $selectDocsContratacao   = $result['select'];
            $leftJoinDocsContratacao = $result['leftJoin'];
            $whereDocsContratacao    = $result['where'];
            unset($result);
            
            $leftJoinInscritoRecursos = "";
            $whereInscritoRecursos    = "";
            $selectInscritoRecursos    = "";
            if($condicoes['solicitaram_recurso']
                or $condicoes['perspectiva']=='listagemAvaliacao'
                or $condicoes['perspectiva']=='listagemClassificacao'
                or $condicoes['perspectiva']=='listagemRPA'){

                $selectInscritoRecursos    = " ,ttr.recurso_trabalho_solicitar, ttr.recurso_trabalho, ttr.pontos_total, ttr.pontos_total_confirmado, ttr.data_inclusao_recurso, ttr.recurso_trabalho_txt ";

                // Bifurca o LEFT JOIN conforme a config de avaliação do evento.
                // - getTrabAvaliacao() != 1 (Avaliação Via Sistema = Não)
                //     → não faz JOIN nenhum (não tem recurso pra listar)
                // - getTrabAvaliacaoPor() == 'CANDIDATO' (modelo novo, 129+)
                //     → texto do recurso em tab_trabalhos_avaliacao_inscricao.recurso_texto,
                //       filtrado por ordem_etapa=1 via JOIN com tab_avaliacao_etapa
                // - getTrabAvaliacaoPor() == 'ARQUIVO' (modelo antigo)
                //     → subquery legada em tab_trabalhos com tipo_documento='RECURSO'
                $avaliacaoAtiva = false;
                $avaliacaoPor   = '';
                try {
                    $objEvConfTmp = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
                    if ($objEvConfTmp) {
                        $avaliacaoAtiva = ($objEvConfTmp->getTrabAvaliacao() == 1);
                        $avaliacaoPor   = $objEvConfTmp->getTrabAvaliacaoPor();
                    }
                } catch (Exception $e) {}

                if (!$avaliacaoAtiva) {
                    // Avaliação desativada — sem JOIN, sem colunas de recurso
                    $selectInscritoRecursos = "";
                    $leftJoinInscritoRecursos = "";
                } else if ($avaliacaoPor === 'CANDIDATO') {
                    $leftJoinInscritoRecursos = " left join (
                        SELECT a.cod_inscrito,
                               NULL as cod_trabalho_recurso,
                               CASE WHEN a.recurso_texto IS NOT NULL AND a.recurso_texto <> ''
                                    THEN 1 ELSE NULL END as recurso_trabalho_solicitar,
                               a.pontos_total_confirmado as pontos_total,
                               a.pontos_total_confirmado,
                               a.data_alteracao as data_inclusao_recurso,
                               a.recurso_texto as recurso_trabalho_txt,
                               a.recurso_texto as recurso_trabalho
                        FROM tab_trabalhos_avaliacao_inscricao a
                        JOIN tab_avaliacao_etapa e
                          ON e.cod_etapa = a.cod_etapa
                         AND e.cod_evento = a.cod_evento
                        WHERE a.status = 'ATIVADA'
                          AND e.ordem_etapa = 1
                    ) as ttr on ti.cod_inscrito = ttr.cod_inscrito";
                } else if ($avaliacaoPor === 'ARQUIVO') {
                    $leftJoinInscritoRecursos = " left join (SELECT ttr.cod_inscrito, max(ttr.cod_trabalho) as cod_trabalho_recurso
                         , ttr.recurso_trabalho_solicitar, ttr.pontos_total, ttr.pontos_total_confirmado
                         , ttr.data_inclusao as data_inclusao_recurso
                         , ttr.recurso_trabalho as recurso_trabalho_txt
                         , ttr.recurso_trabalho
                        FROM tab_trabalhos ttr WHERE ttr.status='ATIVADA' AND (ttr.tipo_documento='RECURSO' or ttr.recurso_trabalho_solicitar=1) group by ttr.cod_inscrito, ttr.cod_trabalho) as ttr
                           on ti.cod_inscrito=ttr.cod_inscrito";
                } else {
                    // 'Nenhum' (ou vazio com avaliação ativa) — não tem recurso
                    $selectInscritoRecursos = "";
                    $leftJoinInscritoRecursos = "";
                }

                if($condicoes['solicitaram_recurso']==''){
                }else if($condicoes['solicitaram_recurso']=='SIM'){
                    $whereInscritoRecursos = " and ttr.recurso_trabalho_solicitar IS NOT NULL ";
                }else if($condicoes['solicitaram_recurso']=='NAO'){
                    $whereInscritoRecursos = " and ttr.recurso_trabalho_solicitar IS NULL ";
                }
                /*excluindo outras colunas*/
                //$this->sqlLeftJoinColunsPerfil = "";
                //$this->sqlLeftJoinColunsAtiv   = "";
                $this->sqlLeftJoinColunsTrab   = "";
                $this->sqlLeftJoinColunsPag    = "";
                $this->sqlLeftJoinColuns       = "";
            }
            /** listagem avaliação */
            $result = AvaliacaoInscritoDAO::getLeftJoin($obj, $condicoes, $debug);
            $leftJoinAvaliacao      = $result['leftJoin'];
            $whereAvaliacaoInscrito = $result['whereAvaliacaoInscrito'];
            unset($result);

            $leftJoinAvaliacaoCampos = AvaliacaoInscritoDAO::getCampos($obj, $condicoes, $debug);
            $orderByAvaliacao = AvaliacaoInscritoDAO::getOrderBy($obj, $condicoes, $debug);

            // $leftJoinAvaliacao = "";
            // $whereAvaliacaoInscrito = "";
            // $leftJoinAvaliacaoCampos = "";
            // $orderByAvaliacao = "";

            // $condicoes['registro_atividade'] = true;
            $result = RegistroAtividadeDAO::getConsultaRegistroAtividade($condicoes, $obj, $debug);
            $selectRegistroAtividade = $result['select'];
            $leftJoinRegistroAtividade = $result['leftJoin'];
            $whereRegistroAtividade= $result['where'];
            // $debug = (COD_EVENTO == 82? $debug = $result['debug'] : 'false');
            unset($result);            
            
            $whereOficinas= InscritoFiltroDAO::getWhereOficinas($condicoes);

            switch ($condicoes['ordenar']) {
                case 'nome_inscrito':
                    $orderBy = " order by ti.nome_inscrito ".$orderByPagamento;
                    break;
                case 'cpf':
                    $orderBy = " order by ti.cpf,ti.nome_inscrito ".$orderByPagamento;
                    break;
                case 'data_pagamento':
                    $orderBy = " order by ipp.data_pagamento, ti.cpf, ti.nome_inscrito ";
                    break;
                case 'data_agendamento':
                    $orderBy = " order by too.data_inicio, too.titulo ";
                    break;
                case 'cod_inscrito':
                    $orderBy = " order by ti.cod_inscrito desc ";
                default:
                    $orderBy = " order by ti.cod_inscrito desc ".$orderByPagamento;
                    break;
            }
            if ($condicoes['perspectiva'] == 'listagemAvaliacao'
                or $condicoes['perspectiva'] == 'listagemRPA'
                or $condicoes['perspectiva'] == 'listagemClassificacao') {
                if ($orderByAvaliacao) {
                    $orderBy = $orderByAvaliacao;
                }
            }
               
            
            /* @todo somente para evento 44 Edital 001/2023 Entrega Contrato, lanças cidade do 37 para o 44 */
            $innerJoinCarregarCidades = "";
            $selectCarregarCidades    = "";
            // if($this->codEvento==44){
            //     $selectCarregarCidades = ", cidadesEd.nome_regional, ttrc.arquivo_gerado ";
            //     $innerJoinCarregarCidades = " INNER JOIN (SELECT ti.cod_evento, ti.cpf, ti.nome_inscrito, too.cod_oficina, too.titulo as nome_regional 
            //         FROM tab_inscritos ti 
            //         INNER JOIN tab_lig_inscritos_oficinas tlio ON tlio.cod_inscrito=ti.cod_inscrito
            //         INNER JOIN tab_opcoes_oficinas too ON too.cod_oficina=tlio.cod_oficina
            //         INNER JOIN tab_trabalhos t ON t.cod_inscrito=ti.cod_inscrito  and t.tipo_documento is NULL
            //         WHERE ti.cod_evento=37 AND ti.status ='ATIVADA') AS cidadesEd on cidadesEd.cpf=ti.cpf ";
                
            //     $innerJoinCarregarCidades .= " left join (SELECT ttrc.cod_inscrito, ttrc.cod_trabalho , ttrc.arquivo_gerado  
            //         FROM tab_trabalhos ttrc WHERE ttrc.status='ATIVADA' and ttrc.tipo_documento is NULL) as ttrc
            //            on ti.cod_inscrito=ttrc.cod_inscrito";                
            // }
            
            /* @todo somente para evento 46 com avaliação de inscrito Edital 010/2023 Entrega Contrato */
            $selectCarregarAvalInscrito    = "";
            $innerJoinCarregarAvalInscrito = "";
            // if($this->codEvento==46){
            //     $selectCarregarAvalInscrito    = ", avalInscrito.sit_pontos as pontos_primeira_avaliacao, avalInscrito.pontos_total_confirmado"
            //                                     . ", avalInscrito.classificacao, avalInscrito.classificacao_status, ttri.arquivo_gerado ";
            //     $innerJoinCarregarAvalInscrito = " LEFT JOIN (SELECT distinct cod_evento, cod_inscrito, sit_pontos, pontos_total_confirmado, classificacao, classificacao_status "
            //         . "FROM `tab_trabalhos_avaliacao_inscricao` WHERE cod_evento=46) AS avalInscrito on avalInscrito.cod_inscrito=ti.cod_inscrito ";
                
            //     $innerJoinCarregarAvalInscrito .= " left join (SELECT ttri.cod_inscrito, ttri.cod_trabalho , ttri.arquivo_gerado  
            //         FROM tab_trabalhos ttri WHERE ttri.status='ATIVADA' and ttri.cod_evento=46) as ttri
            //            on ti.cod_inscrito=ttri.cod_inscrito "; 
            // }
            
            $wherePagamentoNaoCarregadoXMl = InscritoFiltroDAO::getWherePagamentoNaoCarregadoXM1($condicoes);

            $selectListagemRPA = InscritoFiltroDAO::getSelectListagemRPA($condicoes);

            $selectListagemMBA = InscritoFiltroDAO::getSelectListagemMBA($condicoes);          
            
            $sqlCamposCobranca = " , CONCAT('https://".FWA_Dominio()."/',te.gestor,'/',ti.cod_evento,'/','inscricoes/',ti.qr_code) as url_cobranca ";

//            , ti.info_almoco, ti.dat_incl as data_inscricao
            if(($condicoes['media']=='xls')or($condicoes['media']=='xml')or($condicoes['xml_pagamentos']=='104')or($condicoes['forma_pagamento'])or($condicoes['data_pagamento_ini'] and $condicoes['data_pagamento_fim'])or($condicoes['data_pagamento_incl_ini'] and $condicoes['data_pagamento_incl_fim'])){
            //if(($condicoes['media']=='xls')or($condicoes['media']=='xml')){
                //$debug=true;
                if($condicoes['perspectiva'] == 'listagem'){
                    $sqlCampos = " ti.nome_inscrito as Nome, ti.cpf, ti.e_mail as Email, ti.cidade as Cidade, ti.estado as Estado
                    , ti.fone_contato_ddd
                    , ti.fone_contato
                    , ti.profissao_atuacao
                    , ti.tipo_instituicao
                    , ti.instituicao 
                    , top.titulo as categoria_inscricao "
                    . $selectInscritoRecursos
                    ." ";
                }else
                if($condicoes['perspectiva'] == 'listagemComAtividade' or $condicoes['perspectiva']=='listagemAvaliacao' or $condicoes['perspectiva']=='listagemClassificacao'){
                    $sqlCampos = "ti.cod_inscrito, ti.id_publico, ti.nome_inscrito, ti.nome_apelido AS como_quero_ser_chamado"
                    . ", ti.cpf as cpf_inscrito, ti.e_mail as email_inscrito "
                    . ", ti.fone_contato_ddd"
                    . ", ti.fone_contato"
                    . ", ti.profissao_atuacao"
                    . ", ti.tipo_instituicao"
                    . ", ti.instituicao "
                    . ", ti.endereco, ti.numero, ti.complemento, ti.bairro, ti.cep, ti.cidade, ti.estado "
                    . $this->sqlLeftJoinColunsPerfil
                    . $this->sqlLeftJoinColunsAtiv
                    . $this->sqlLeftJoinColunsTrab
                    . $this->sqlLeftJoinColunsPag
                    . $this->sqlLeftJoinColuns
                    . $selectInscritoRecursos
                    . " ,ti.status "
                    . " ,ti.status_processo "
                    . ", ti.dat_incl "
                    . ", ip.total_pagamentos "
                    . ", CASE 
                            WHEN IFNULL(ip.total_pagamentos, 0) > 0 THEN 'pago'
                            ELSE ''
                        END as status_pagamento "                            
                    . $sqlCamposCobranca
                    ."";
                }else
                if($condicoes['perspectiva'] == 'detalhada'){
                    $sqlCampos = "ti.cod_inscrito, ti.id_publico, ti.nome_inscrito, ti.nome_apelido AS como_quero_ser_chamado"
                    . ", ti.cpf as cpf_inscrito, ti.e_mail as email_inscrito "
                    . ", ti.fone_contato_ddd"
                    . ", ti.fone_contato"
                    . ", ti.profissao_atuacao"
                    . ", ti.tipo_instituicao"
                    . ", ti.instituicao "
                    . ", ti.endereco, ti.numero, ti.complemento, ti.bairro, ti.cep, ti.cidade, ti.estado "
                    . $this->sqlLeftJoinColunsPerfil
                    . $this->sqlLeftJoinColunsAtiv
                    . $this->sqlLeftJoinColunsTrab
                    . $this->sqlLeftJoinColunsPag
                    . $this->sqlLeftJoinColuns
                    . $selectInscritoRecursos
                    . " ,ti.status "
                    . ", ti.dat_incl "
                    . ", ip.total_pagamentos "
                    . ", CASE 
                            WHEN IFNULL(ip.total_pagamentos, 0) > 0 THEN 'pago'
                            ELSE ''
                        END as status_pagamento "                            
                    . $sqlCamposCobranca
                    ."";
                }else
                if($condicoes['perspectiva'] == 'impressao'){
                    $sqlCampos = "ti.cod_inscrito, ti.id_publico, ti.nome_inscrito as nome_participantes"
                    . ", ti.cpf as cpf , ti.e_mail as e-mail"
                    . ", ti.fone_contato_ddd as ddd"
                    . ", ti.fone_contato"
                    . ", ti.cidade, ti.estado as uf"
                    . ", CASE 
                            WHEN IFNULL(ip.total_pagamentos, 0) > 0 THEN 'pago'
                            ELSE ''
                        END as status_pagamento "
                    . "";
                }else{
                    $sqlCampos = "ti.cod_inscrito, ti.id_publico, ti.nome_inscrito, ti.cpf as cpf_inscrito , ti.e_mail as email_inscrito"
                    . ", ti.fone_contato_ddd"
                    . ", ti.fone_contato"
                    . ", ti.profissao_atuacao"
                    . ", ti.tipo_instituicao"
                    . ", ti.instituicao "
                    . ", ti.status "
                    . ", ti.dat_incl "
                    . ", ip.total_pagamentos "
                    . ", IFNULL(tis.cod_isento, tisc.cod_isento_codigo) AS cod_isento "
                    . ", IFNULL(ip.valor_pagamentos,0) as valor_pagamentos  "
                    . ", IFNULL(iv.cod_voucher,'') as cod_voucher  "
                    . "";
       
                    $sqlCampos .= $sqlCamposPagamentosIndividual;
                    if($condicoes['media']!='xml'){
                        $sqlCampos .= $sqlCamposCobranca;
                    }
                }
                
            }else{
                $sqlCampos = " ti.*, ti.nome_apelido AS como_quero_ser_chamado, cod_isento"
                    . ", ti.e_mail as email_inscrito, ti.dat_incl as data_inscricao "
                    . $this->sqlLeftJoinColuns;
            }  
            $whereConvidadoSis = InscritoFiltroDAO::getWhereConvidadoSis($fwsCondicao);

            $selectSisUsuarios = "";
            $leftJoinSisUsuarios = "";
            $whereSisUsuarios = "";
            $leftJoinSisUsuarios = " left join vw_tentativas_login sl on sl.login=ti.e_mail and sl.cod_evento=ti.cod_evento ";
            $selectSisUsuarios = " ,sl.data_inclusao as data_ultimo_login, sl.sucesso, sl.tentantivas_login ";
            if($condicoes['outros_filtros_inscrito']){
                if($condicoes['outros_filtros_inscrito']=='ultimo_login_com_falha'){
                    $whereSisUsuarios = " and sl.sucesso=0 ";
                }
                if($condicoes['outros_filtros_inscrito']=='nunca_logou'){
                    $whereSisUsuarios = " and sl.sucesso is null ";
                }
            }

            if (!empty($condicoes['todos_eventos'])) {
                // API: sem filtro de evento, busca em todos
                $whereCodEvento = "";
            } else if(($condicoes['cod_evento']=='' or COD_EVENTO=='9999') and $lista_eventos!=''){
                $whereCodEvento = " and te.cod_evento in (".$lista_eventos.")";
            }else if($condicoes['cod_evento']=='-1' and $lista_eventos!=''){
                $whereCodEvento = " and te.cod_evento in (".$lista_eventos.")";
            }else if($condicoes['cod_evento']==10){
                $whereCodEvento = " and te.cod_evento=10 ";
            }else{
                //$whereCodEvento = " and te.cod_evento>=10 AND ti.cod_evento=".$this->codEvento;
                $whereCodEvento = " AND ti.cod_evento=".$this->codEvento;
            }

            $sqlInicio = InscritoFiltroDAO::getSqlInicio($condicoes);
            
            if ($condicoes['tipo_pedido']=='mensalidade'){
                $sqlDistinct="";
            }

            $limit = InscritoFiltroDAO::getLimit($condicoes, $this->codEvento);

            $sql = "SELECT ". $sqlDistinct . $sqlInicio  . $sqlCampos 
                    . $selectListagemRPA 
                    . $selectInscritoCompTrans 
                    . $selectInscritoTermoDesist 
                    . $selectInscritoRPAs
                    . $selectFlgAssinado 
                    . $selectListagemMBA
                    . $selectCarregarCidades 
                    . $selectCarregarAvalInscrito
                    . $selectInscritoRecursos
                    . $selectRegistroAtividade
                    . $selectSituacaoEventoVinculado
                    . $selectInscritoContratos
                    . $selectInscritoBoletos
                    . $selectDocsContratacao
                    . $selectSisUsuarios
                    . $leftJoinAvaliacaoCampos
                    . $selectLeftJoinAvaliacaoTrabalhos
                    . "FROM tab_inscritos ti INNER JOIN tab_eventos te ON te.cod_evento=ti.cod_evento "
                    . " LEFT JOIN tab_eventos_configuracao teconf ON te.cod_evento=teconf.cod_evento " 
                    . $this->getSqlLeftJoin($obj->getCodEvento())
                    . $sqlLeftJoinPagamento
                    . $sqlLeftJoinPedido
                    . $leftJoinOficina
                    . $innerJoinAreaTematica
                    . $innerJoinOficina
                    . $innerJoinPatrocinadorVoucher
                    . $leftJoinInscritoRecursos
                    . $leftJoinRegistroAtividade
                    . $innerJoinCarregarCidades
                    . $innerJoinCarregarAvalInscrito
                    . $innerJoinMacrorregiao
                    . $innerJoinPedidoId
                    . $leftJoinSituacaoEventoVinculado
                    . $leftJoinInscritoContratos
                    . $leftJoinInscritoBoletos
                    . $leftJoinInscritoCompTrans
                    . $leftJoinInscritoTermoDesist
                    . $leftJoinInscritoRPAs
                    . $leftJoinFlgAssinado
                    . $leftJoinDocsContratacao
                    . $leftJoinSisUsuarios
                    . $leftJoinAvaliacao
                    . $leftJoinAvaliacaoTrabalhos
                    . " WHERE ti.cod_evento is not null "
                    . $this->whereSql
                    . $wherePagamentos
                    . $wherePagamentoNaoCarregadoXMl
                    . $whereQtdPedidos
                    . $wherePedidos
                    . $wherePedidoId
                    . $whereFormaPagamento
                    . $whereAlmoco
                    . $whereTrabalhos
                    . $whereSituacaoEventoVinculado   
                    . $whereInscritoRecursos   
                    . $whereRegistroAtividade   
                    . $whereAvaliacaoInscrito                                 
                    . $whereOficinas
                    . $whereCodOficina
                    . $whereDataPagamento
                    . $whereDataInscricao
                    . $whereTipoInscricao
                    . $whereConvidadoSis
                    . $whereContratos
                    . $whereBoletos
                    . $whereCompTrans
                    . $whereTermoDesist
                    . $whereRPAs
                    . $whereFlgAssinado
                    . $whereDocsContratacao
                    . $whereCodEvento
                    . $whereSisUsuarios
                    . $whereAvaliacaoTrabalhos
                    . $orderBy
                    . $limit;
                    // . " and ti.cod_evento not in (31,32) "
            
//            if($condicoes['cod_evento']=='' and COD_EVENTO=='9999'){
//                $sql = "SELECT ti.*, te.cod_evento, te.titulo as titulo_evento, te.abrev_evento FROM tab_inscritos ti "
//                        . " INNER JOIN tab_eventos te on te.cod_evento=ti.cod_evento "
//                        . " WHERE ti.cod_evento=te.cod_evento ". $this->whereSql;
//            }

            // $debug=true;
            $ips_permitidos = ['189.90.70.56-','200.203.142.25-', '201.66.95.230-', '177.82.0.28-','45.230.27.11-'];
            if (in_array($_SERVER['REMOTE_ADDR'], $ips_permitidos)) {
//              echo '-'.COD_EVENTO.'-<br>';
                $debug = true;
            }
            // executarLog($sql,"log-sql",true);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            
//            $this->conex->cacheSecs = 20;
//            $rs = $this->conex->cacheExecute($sql);
            
            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaInscritos($return); 
                if(($condicoes['media']=='xls')or($condicoes['media']=='xml')){
                    foreach ($rows as $campo=>$value) {
                        if(is_string($campo))
                            $colunas[$i++] = $campo;
                    }
                    $obj->setColunasListaInscritos($colunas);
                }
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(Inscrito $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            // Pegue os últimos 3 dígitos do código do evento (ou use outro método)
            $codigoBase = $obj->getCodEvento();
            // Combine com um timestamp curto (milissegundos)
            // $timestamp = substr(microtime(true) * 1000, -4);
            $timestamp = substr((int) (microtime(true) * 1000), -4);
            // Adicione 2 números aleatórios para completar 7 caracteres
            $aleatorio = mt_rand(10, 99);
            $obj->setIdPublico($codigoBase . $timestamp . $aleatorio);

            if(!$obj->getStatusProcesso()){
                $obj->setStatusProcesso('INSCRITO');    
            }

            $qrBase = ($obj->getCpf() != '') ? $obj->getCpf() : $obj->getNomeInscrito();
            $obj->setQrCode(hash('sha256', $qrBase.$obj->getCodEvento().$obj->getDatIncl()));

            $sql = "SELECT * FROM tab_inscritos WHERE cod_evento=".$obj->getCodEvento()." and cod_inscrito=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_inscritos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodInscrito($this->conex->Insert_ID());
            if($obj->getCodInscrito()==9500){
                $sql = "INSERT INTO tab_inscritos (cod_inscrito, cod_evento, cod_perfil)VALUES (20000, 10, 0)"; 
                $rs2 = $this->conex->Execute($sql);  
            }
            
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            throw new Exception("Nova Inscriçao não foi salva.");
            return false;
        }
    }
    public function update(Inscrito $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            if($obj->getQrCode()==''){
                $qrBase = ($obj->getCpf() != '') ? $obj->getCpf() : $obj->getNomeInscrito();
                $obj->setQrCode(hash('sha256', $qrBase.$obj->getCodEvento().$obj->getDatIncl()));
            }
            $sql = "SELECT * FROM tab_inscritos WHERE cod_evento = ".$obj->getCodEvento()." and cod_inscrito = ".$obj->getCodInscrito(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_inscritos");
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
    /**
     * UPDATE direcionado apenas de flg_presenca_inscrito (mais dat_alter/res_alter/ip_alter).
     * Evita GetUpdateSQL/getById (que corrompe cod_inscrito e reescreve a linha inteira).
     * Usado pela carga de presenca por inscrito.
     */
    public function marcarPresenca(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf(
                "UPDATE tab_inscritos SET flg_presenca_inscrito = %d, dat_alter = '%s', res_alter = '%s', ip_alter = '%s' "
                . "WHERE cod_evento = %d AND cod_inscrito = %d",
                ($obj->getFlgPresencaInscrito() ? 1 : 0),
                date('Y-m-d H:i:s'),
                addslashes(@$_SESSION['_cod_usuario']),
                addslashes(@$_SERVER['REMOTE_ADDR']),
                (int)$obj->getCodEvento(),
                (int)$obj->getCodInscrito()
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Atualiza SOMENTE flg_bloquear_certificado_geral via UPDATE direcionado.
     * Opt-out do certificado GERAL de participacao (congressista): 1 = nao recebe,
     * 0/NULL = recebe (comportamento antigo). Mesmo padrao de marcarPresenca:
     * sem getById (que corrompe cod_inscrito) e sem GetUpdateSQL (que reescreve a
     * linha inteira). Requer cod_evento e cod_inscrito ja setados.
     */
    public function bloquearCertificadoGeral(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf(
                "UPDATE tab_inscritos SET flg_bloquear_certificado_geral = %d, dat_alter = '%s', res_alter = '%s', ip_alter = '%s' "
                . "WHERE cod_evento = %d AND cod_inscrito = %d",
                ($obj->getFlgBloquearCertificadoGeral() ? 1 : 0),
                date('Y-m-d H:i:s'),
                addslashes(@$_SESSION['_cod_usuario']),
                addslashes(@$_SERVER['REMOTE_ADDR']),
                (int)$obj->getCodEvento(),
                (int)$obj->getCodInscrito()
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Contador de APTOS (presentes no evento) por perfil, para o Painel de
     * Certificados. Espelha o total_inscritos do perfil (principal em
     * tab_inscritos + ligacao em tab_lig_inscritos_perfis), mas so quem esta
     * PRESENTE no evento (flg_presenca_inscrito=1). Presenca e o gate do
     * certificado; o sistema nao registra download, entao "apto" e o numero real.
     *
     * @return array [cod_perfil => quantidade_presentes]
     */
    public function contarAptosPorPerfil($codEvento, $debug=false) {
        $out = array();
        try {
            $sql = sprintf(
                "SELECT cod_perfil, SUM(n) AS n FROM (
                    SELECT i.cod_perfil AS cod_perfil, COUNT(*) AS n
                      FROM tab_inscritos i
                     WHERE i.cod_evento=%d AND i.status='ATIVADA' AND i.flg_presenca_inscrito=1
                     GROUP BY i.cod_perfil
                    UNION ALL
                    SELECT lip.cod_perfil AS cod_perfil, COUNT(*) AS n
                      FROM tab_lig_inscritos_perfis lip
                      JOIN tab_inscritos i2 ON i2.cod_inscrito=lip.cod_inscrito AND i2.cod_evento=lip.cod_evento
                     WHERE lip.cod_evento=%d AND i2.status='ATIVADA' AND i2.flg_presenca_inscrito=1
                     GROUP BY lip.cod_perfil
                ) t GROUP BY cod_perfil",
                (int) $codEvento, (int) $codEvento);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) { $out[(int) $r['cod_perfil']] = (int) $r['n']; }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $out;
    }

    /**
     * Contador de APTOS por ATIVIDADE (presente + atividade libera certificado),
     * reusando a view canonica vw_certificados_emitidos (mesma logica).
     *
     * @return array [cod_oficina => quantidade_aptos]
     */
    public function contarAptosPorOficina($codEvento, $debug=false) {
        $out = array();
        try {
            $sql = sprintf("SELECT cod_oficina, total_certificados AS n
                              FROM vw_certificados_emitidos WHERE cod_evento=%d", (int) $codEvento);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) { $out[(int) $r['cod_oficina']] = (int) $r['n']; }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $out;
    }

    /**
     * Totais do evento para o resumo do Painel: total de inscritos ATIVADA e
     * quantos estao PRESENTES (base do certificado de participacao).
     *
     * @return array ['total' => int, 'presentes' => int]
     */
    public function contarPresencaEvento($codEvento, $debug=false) {
        $out = array('total' => 0, 'presentes' => 0);
        try {
            $sql = sprintf("SELECT COUNT(*) AS total,
                                   SUM(CASE WHEN flg_presenca_inscrito=1 THEN 1 ELSE 0 END) AS presentes
                              FROM tab_inscritos
                             WHERE cod_evento=%d AND status='ATIVADA'", (int) $codEvento);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                $out['total']     = (int) $rs->fields['total'];
                $out['presentes'] = (int) $rs->fields['presentes'];
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $out;
    }

    /**
     * Quais dos CPFs informados ja tem inscrito ATIVADA neste evento.
     *
     * Usado pela critica de conflito da carga dinamica, que roda ANTES de gravar
     * qualquer linha -- por isso e uma consulta unica com IN, e nao um
     * getByCpfExiste por linha do arquivo.
     *
     * @param array $cpfs CPFs como vierem da planilha (com ou sem mascara)
     * @return array [cpf => ['codInscrito','cpf','nomeInscrito','eMail','statusProcesso','datIncl']]
     */
    public function carregarConflitosPorCpf($codEvento, $cpfs, $debug=false) {
        $conflitos = array();
        try {
            // mesmo tratamento do gravador (formatCpfInsert): so digito, 11 posicoes
            $limpos = array();
            foreach ($cpfs as $cpf) {
                $cpf = preg_replace('/[^0-9]/', '', (string) $cpf);
                if ($cpf === '') {
                    continue;
                }
                $cpf = numberPad($cpf, 11);
                $limpos[$cpf] = "'" . $cpf . "'";
            }
            if (empty($limpos)) {
                return $conflitos;
            }

            $sql = sprintf("SELECT cod_inscrito, cpf, nome_inscrito, e_mail, status_processo, dat_incl
                              FROM tab_inscritos
                             WHERE cod_evento=%d AND status='ATIVADA' AND cpf IN (%s)
                             ORDER BY nome_inscrito", (int) $codEvento, implode(',', $limpos));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) {
                    $conflitos[$r['cpf']] = array(
                        'codInscrito'    => $r['cod_inscrito'],
                        'cpf'            => $r['cpf'],
                        'nomeInscrito'   => $r['nome_inscrito'],
                        'eMail'          => $r['e_mail'],
                        'statusProcesso' => $r['status_processo'],
                        'datIncl'        => $r['dat_incl'],
                    );
                }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $conflitos;
    }

    // ========================================================================
    // Recuperacao/definicao de senha do inscrito (Parte 1).
    // Token numa tabela propria (tab_recuperacao_senha_inscrito); o hash bcrypt
    // vai em tab_inscritos.senha_criptografada, em TODAS as contas da pessoa.
    // ========================================================================

    /** Ja existe token ativo (nao usado, nao expirado) para este e-mail? (limite de reenvio) */
    public function tokenRecuperacaoAtivoInscrito($email, $debug=false) {
        try {
            $sql = sprintf("SELECT COUNT(*) AS n FROM tab_recuperacao_senha_inscrito
                             WHERE e_mail='%s' AND usado=0 AND expira>=NOW()", addslashes($email));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return ($rs && !$rs->EOF && (int)$rs->fields['n'] > 0);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** E-mail + CPF da pessoa (linha ATIVADA/INSCRICAO mais recente) ou false. */
    public function getEmailCpfParaRecuperacao($email, $debug=false) {
        try {
            $sql = sprintf("SELECT e_mail, cpf FROM tab_inscritos
                             WHERE e_mail='%s' AND status='ATIVADA' AND origem_cadastro='INSCRICAO'
                             ORDER BY cod_inscrito DESC LIMIT 1", addslashes($email));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return false;
            return array('e_mail'=>$rs->fields['e_mail'], 'cpf'=>$rs->fields['cpf']);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Grava um pedido de recuperacao -- uma linha por pedido (historico + limite). */
    public function salvarTokenRecuperacaoInscrito($email, $cpf, $token, $expira, $debug=false) {
        try {
            $sql = sprintf("INSERT INTO tab_recuperacao_senha_inscrito
                    (e_mail, cpf, token, expira, usado, ip, http_user_agent, dat_incl)
                    VALUES ('%s', %s, '%s', '%s', 0, '%s', '%s', NOW())",
                addslashes($email),
                ($cpf===null || $cpf==='') ? 'NULL' : "'".addslashes($cpf)."'",
                addslashes($token),
                addslashes($expira),
                addslashes(@$_SERVER['REMOTE_ADDR']),
                addslashes(substr((string)@$_SERVER['HTTP_USER_AGENT'],0,255)));
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Resolve o token valido -> ['e_mail','cpf'] ou false. */
    public function getByTokenRecuperacaoInscrito($token, $debug=false) {
        try {
            // minutos_restantes sai pronto do banco para a tela avisar quanto falta
            // sem depender do relogio do servidor de aplicacao.
            $sql = sprintf("SELECT e_mail, cpf, expira,
                                   TIMESTAMPDIFF(MINUTE, NOW(), expira) AS minutos_restantes
                              FROM tab_recuperacao_senha_inscrito
                             WHERE token='%s' AND usado=0 AND expira>=NOW()
                             ORDER BY cod_recuperacao DESC LIMIT 1", addslashes($token));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return false;
            return array('e_mail'=>$rs->fields['e_mail'], 'cpf'=>$rs->fields['cpf'],
                         'expira'=>$rs->fields['expira'],
                         'minutos_restantes'=>(int)$rs->fields['minutos_restantes']);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Marca o token como usado (uso unico). */
    public function marcarTokenRecuperacaoInscritoUsado($token, $debug=false) {
        try {
            $sql = sprintf("UPDATE tab_recuperacao_senha_inscrito
                               SET usado=1, dat_uso=NOW() WHERE token='%s'", addslashes($token));
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Grava o hash bcrypt em TODAS as inscricoes da pessoa (mesmo e-mail + cpf).
     * UPDATE direto de credencial -- nao passa pelo espelho de auditoria.
     * @return int linhas afetadas
     */
    public function definirSenhaHashInscrito($email, $cpf, $hash, $debug=false) {
        try {
            $sqlCpf = ($cpf===null || $cpf==='')
                    ? " AND (cpf IS NULL OR cpf='')"
                    : sprintf(" AND cpf='%s'", addslashes($cpf));
            $sql = sprintf("UPDATE tab_inscritos SET senha_criptografada='%s'
                             WHERE e_mail='%s' AND origem_cadastro='INSCRICAO'%s",
                addslashes($hash), addslashes($email), $sqlCpf);
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return (int) $this->conex->Affected_Rows();
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    /**
     * Candidatos para login por bcrypt: linhas por e-mail (+ 4 ultimos do CPF)
     * ATIVADA/INSCRICAO com senha_criptografada preenchida. O PHP verifica o hash.
     */
    public function carregarCandidatosBcrypt($email, $quatroCpf, $codEvento=null, $debug=false) {
        $out = array();
        try {
            $where4  = ($quatroCpf!=='' && $quatroCpf!==null) ? sprintf(" AND RIGHT(cpf,4)='%s'", addslashes($quatroCpf)) : '';
            $whereEv = ($codEvento!==null && $codEvento!=='')  ? sprintf(" AND cod_evento=%d", (int)$codEvento) : '';
            $sql = sprintf("SELECT cod_inscrito, cod_evento, nome_inscrito, nome_apelido, e_mail, cpf, senha_criptografada
                              FROM tab_inscritos
                             WHERE e_mail='%s' AND status='ATIVADA' AND origem_cadastro='INSCRICAO'
                               AND senha_criptografada IS NOT NULL AND senha_criptografada<>''%s%s
                             ORDER BY cod_inscrito DESC", addslashes($email), $where4, $whereEv);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) { foreach ($rs as $r) { $out[] = $r; } }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $out;
    }

    /**
     * Lista os pedidos de recuperacao/troca de senha do inscrito para a tela admin.
     * Traz, por CPF, a inscricao mais recente (nome, cod_inscrito, cod_evento,
     * url_prefixo -- para os links "logar como"/"abrir inscricao"), o ULTIMO LOGIN
     * COM SUCESSO e a ULTIMA TENTATIVA (data + sucesso) do e-mail em sis_log_acesso
     * (origem inscrito). So leitura -- nao devolve senha.
     * @param array $filtros de(AAAA-MM-DD), ate(AAAA-MM-DD), soUsados(bool)
     */
    public function carregarTrocasSenhaInscrito($filtros=array(), $debug=false) {
        $out = array();
        try {
            $de  = isset($filtros['de'])  ? $filtros['de']  : '';
            $ate = isset($filtros['ate']) ? $filtros['ate'] : '';
            $email = trim((string) (isset($filtros['email']) ? $filtros['email'] : ''));
            $soUsados = !empty($filtros['soUsados']);

            $where = " WHERE 1=1 ";
            if ($soUsados)   { $where .= " AND r.usado = 1 "; }
            if ($de !== '')  { $where .= sprintf(" AND DATE(COALESCE(r.dat_uso, r.dat_incl)) >= '%s' ", addslashes($de)); }
            if ($ate !== '') { $where .= sprintf(" AND DATE(COALESCE(r.dat_uso, r.dat_incl)) <= '%s' ", addslashes($ate)); }
            if ($email !== '') { $where .= sprintf(" AND r.e_mail LIKE '%%%s%%' ", addslashes($email)); }

            $sql = "SELECT r.cod_recuperacao, r.e_mail, r.cpf, r.dat_incl, r.dat_uso, r.usado, r.ip,
                           i2.nome_inscrito AS nome, i2.cod_inscrito AS cod_inscrito,
                           i2.cod_evento AS cod_evento, ev.url_prefixo AS url_prefixo,
                           (SELECT MAX(la.data_inclusao) FROM sis_log_acesso la
                             WHERE la.login=r.e_mail AND la.sucesso=1 AND la.origem='inscrito') AS ultimo_login,
                           (SELECT la2.data_inclusao FROM sis_log_acesso la2
                             WHERE la2.login=r.e_mail AND la2.origem='inscrito'
                             ORDER BY la2.data_inclusao DESC LIMIT 1) AS ult_tentativa,
                           (SELECT la3.sucesso FROM sis_log_acesso la3
                             WHERE la3.login=r.e_mail AND la3.origem='inscrito'
                             ORDER BY la3.data_inclusao DESC LIMIT 1) AS ult_tentativa_sucesso,
                           (SELECT SUM(senha_criptografada LIKE '\$2y\$%') FROM tab_inscritos
                             WHERE e_mail=r.e_mail AND origem_cadastro='INSCRICAO') AS com_senha_propria
                      FROM tab_recuperacao_senha_inscrito r
                      LEFT JOIN (SELECT cpf, MAX(cod_inscrito) AS cod_inscrito FROM tab_inscritos
                                  WHERE origem_cadastro='INSCRICAO' AND cpf IS NOT NULL AND cpf<>''
                                  GROUP BY cpf) ult ON ult.cpf=r.cpf
                      LEFT JOIN tab_inscritos i2 ON i2.cod_inscrito=ult.cod_inscrito
                      LEFT JOIN tab_eventos ev ON ev.cod_evento=i2.cod_evento
                      $where
                      ORDER BY COALESCE(r.dat_uso, r.dat_incl) DESC
                      LIMIT 2000";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) { foreach ($rs as $r) { $out[] = $r; } }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $out;
    }

    /**
     * Resumo de um inscrito por e-mail para a tela "Login de Inscritos" (admin
     * definir senha): nome, cpf (para os 4 digitos), quantas inscricoes ATIVADAS
     * e se ja tem senha propria (bcrypt). So leitura.
     */
    public function buscarInscritoParaSenhaAdmin($email, $debug=false) {
        try {
            $e = addslashes($email);
            $sql = "SELECT
                        (SELECT nome_inscrito FROM tab_inscritos
                          WHERE e_mail='$e' AND origem_cadastro='INSCRICAO' AND nome_inscrito<>''
                          ORDER BY cod_inscrito DESC LIMIT 1) AS nome,
                        (SELECT cpf FROM tab_inscritos
                          WHERE e_mail='$e' AND origem_cadastro='INSCRICAO' AND cpf IS NOT NULL AND cpf<>''
                          ORDER BY cod_inscrito DESC LIMIT 1) AS cpf,
                        SUM(status='ATIVADA') AS ativas,
                        SUM(senha_criptografada LIKE '\$2y\$%') AS com_senha_propria
                      FROM tab_inscritos
                     WHERE e_mail='$e' AND origem_cadastro='INSCRICAO'";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return false;
            $r = $rs->fields;
            if (!$r['nome'] && !$r['cpf'] && (int)$r['ativas'] === 0) return false;
            return array(
                'nome'  => $r['nome'],
                'cpf'   => $r['cpf'],
                'ult4'  => $r['cpf'] ? substr($r['cpf'], -4) : '',
                'ativas'=> (int) $r['ativas'],
                'com_senha_propria' => (int) $r['com_senha_propria'],
            );
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Uma inscricao "representativa" da pessoa por e-mail -- prefere ATIVADA e,
     * entre elas, a mais recente. Usada pra montar os botoes "logar como" e
     * "abrir inscricao" quando so se tem o e-mail (ex: lista de tentativas de
     * login do Dashboard Geral), sem precisar escolher entre varios eventos.
     */
    public function buscarInscritoRepresentativoPorEmail($email, $debug=false) {
        try {
            $e = addslashes($email);
            $sql = "SELECT ti.cod_inscrito, ti.cod_evento, ti.cpf, ti.nome_inscrito AS nome, ev.url_prefixo
                      FROM tab_inscritos ti
                      LEFT JOIN tab_eventos ev ON ev.cod_evento = ti.cod_evento
                     WHERE ti.e_mail = '$e' AND ti.origem_cadastro = 'INSCRICAO'
                     ORDER BY (ti.status = 'ATIVADA') DESC, ti.cod_inscrito DESC
                     LIMIT 1";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return false;
            return $rs->fields;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Quantas pessoas (distintas por e-mail) entre as INSCRICOES ATIVADAS ja
     * definiram senha propria (bcrypt) em tab_inscritos.senha_criptografada --
     * so para dar uma ideia geral de quanto da base ja migrou. Nao indica se a
     * pessoa CHEGOU A USAR a senha para logar (isso o log de acesso nao separa).
     */
    public function resumoMigracaoSenhaPropria($debug=false) {
        try {
            $sql = "SELECT
                        COUNT(DISTINCT e_mail) AS total_pessoas,
                        COUNT(DISTINCT CASE WHEN senha_criptografada LIKE '\$2y\$%' THEN e_mail END) AS com_senha_propria
                      FROM tab_inscritos
                     WHERE origem_cadastro='INSCRICAO' AND status='ATIVADA' AND e_mail<>''";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return array('total' => 0, 'com_senha' => 0);
            return array(
                'total'     => (int) $rs->fields['total_pessoas'],
                'com_senha' => (int) $rs->fields['com_senha_propria'],
            );
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array('total' => 0, 'com_senha' => 0);
        }
    }

    /**
     * UPDATE direcionado APENAS das colunas que vieram mapeadas na carga.
     *
     * Usado quando o usuario, diante da critica de CPF repetido, escolhe substituir
     * os dados atuais. Nao usa GetUpdateSQL (que reescreve a linha inteira e apagaria
     * o que o inscrito ja tinha preenchido em campos fora da planilha) nem getById
     * (que corrompe o cod_inscrito -- ver feedback_inscrito_getbyid_corrompe_cod).
     *
     * @param array $valores [coluna_da_tabela => valor]; colunas fora de tab_inscritos sao ignoradas
     * @return int quantidade de colunas efetivamente atualizadas
     */
    public function atualizarCamposCarga($codEvento, $codInscrito, $valores, $debug=false) {
        try {
            if (empty($valores) || !$codInscrito) {
                return 0;
            }
            $colunasTabela = array_map('strtolower', $this->conex->MetaColumnNames("tab_inscritos"));

            $sets = array();
            foreach ($valores as $coluna => $valor) {
                $coluna = strtolower($coluna);
                // whitelist dupla: formato do nome + existir mesmo na tabela
                if (!preg_match('/^[a-z0-9_]+$/', $coluna) || !in_array($coluna, $colunasTabela)) {
                    continue;
                }
                // chaves e auditoria nao se atualizam por carga
                if (in_array($coluna, array('cod_inscrito', 'cod_evento', 'status', 'dat_incl', 'res_incl', 'versao'))) {
                    continue;
                }
                $sets[] = $coluna . " = '" . addslashes($valor) . "'";
            }
            if (empty($sets)) {
                return 0;
            }

            $sql = sprintf("UPDATE tab_inscritos SET %s, dat_alter='%s', res_alter='%s', ip_alter='%s', versao=versao+1
                             WHERE cod_evento=%d AND cod_inscrito=%d",
                implode(', ', $sets),
                date('Y-m-d H:i:s'),
                addslashes(@$_SESSION['_cod_usuario']),
                addslashes(@$_SERVER['REMOTE_ADDR']),
                (int) $codEvento,
                (int) $codInscrito
            );

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return count($sets);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    public function delete(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_inscritos WHERE cod_evento=\'%s\' '
                    . 'and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;

            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Copia a linha de tab_inscritos para tab_inscritos_excluidos como backup
     * antes de alterar status (DESATIVADA/EXCLUIDA). Nao remove o original.
     */
    public function arquivarParaExcluidos(Inscrito $obj, $debug=false) {
        try {
            $sql = sprintf('INSERT INTO tab_inscritos_excluidos SELECT * FROM tab_inscritos '
                    . 'WHERE cod_evento=\'%s\' AND cod_inscrito=\'%s\'',
                    addslashes($obj->getCodEvento()), addslashes($obj->getCodInscrito()));

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function registrarImpressaoCracha(Inscrito $obj, $debug=false) {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setImpressaoCracha($obj->getImpressaoCracha()+1);
            
            $sql = "SELECT * FROM tab_inscritos WHERE cod_evento = ".$obj->getCodEvento()." and cod_inscrito = ".$obj->getCodInscrito(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_inscritos");
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
public function carregarListaInscritosStatusByIdEvento(Inscrito $obj, $condicoes, $debug = false) {
    try {
        $sqlWhere = "";

        if (!empty($condicoes['codInscrito'])) {
            $sqlWhere .= " AND h.cod_inscrito = '" . addslashes($condicoes['codInscrito']) . "' ";
        }
        if (!empty($condicoes['nomeInscrito'])) {
            $sqlWhere .= " AND ti.nome_inscrito LIKE '%" . addslashes($condicoes['nomeInscrito']) . "%' ";
        }
        if (!empty($condicoes['codPerfil'])) {
            $sqlWhere .= " AND ti.cod_perfil = " . intval($condicoes['codPerfil']) . " ";
        }
        if (!empty($condicoes['data_alteracao_ini'])) {
            $sqlWhere .= " AND h.data_alteracao >= '" . addslashes($condicoes['data_alteracao_ini']) . "' ";
        }
        if (!empty($condicoes['data_alteracao_fim'])) {
            $sqlWhere .= " AND h.data_alteracao <= '" . addslashes($condicoes['data_alteracao_fim']) . " 23:59:59' ";
        }

        $sql = sprintf(
            "SELECT h.id_hist, h.cod_inscrito, h.cod_evento,
                    ti.nome_inscrito, top.titulo as nome_perfil,
                    h.status_antiga, h.status_nova, h.data_alteracao
             FROM tab_hist_status_processo h
             INNER JOIN tab_inscritos ti ON ti.cod_inscrito = h.cod_inscrito AND ti.cod_evento = h.cod_evento
             LEFT JOIN tab_opcoes_perfis top ON top.cod_perfil = ti.cod_perfil
             WHERE h.cod_evento = '%s' %s
             ORDER BY ti.nome_inscrito, h.data_alteracao",
            addslashes($obj->getCodEvento()),
            $sqlWhere
        );
        // echo $sql;
        $this->conex->debug = $debug ? 1 : 0;
        $rs = $this->conex->Execute($sql);

        if (!$rs || $rs->EOF) {
            return false;
        } else {
            $return = [];
            foreach ($rs as $rows) {
                $obj->setAll($rows);
                $return[] = clone $obj;
            }
            $obj->setListaInscritosStatus($return);
            return true;
        }
    } catch (ADODB_Exception $e) {
        $this->enviarMensagem($e);
        return false;
    }
}

}
