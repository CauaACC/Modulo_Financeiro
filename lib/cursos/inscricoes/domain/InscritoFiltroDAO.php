<?php
class InscritoFiltroDAO {
    public static function getWherePagamentos($condicoes, $codEvento) {
        $wherePagamentos = "";
        if ($condicoes['pagamentosQtdAte']) {
            if ($condicoes['pagamentosQtdAte'] >= 1 and $condicoes['pagamentosQtdAte'] <= 20) {
                $wherePagamentos .= " and ip.total_pagamentos <= '" . $condicoes['pagamentosQtdAte'] . "' ";
            }
        }
        if ($condicoes['pagamentosQtdMaior']) {
            if ($condicoes['pagamentosQtdMaior'] >= 1 and $condicoes['pagamentosQtdMaior'] <= 20) {
                $wherePagamentos .= " and ip.total_pagamentos >= '" . $condicoes['pagamentosQtdMaior'] . "' ";
            }
        }
        if ($condicoes['pagamentosQtdMenor']) {
            if ($condicoes['pagamentosQtdMenor'] >= 1 and $condicoes['pagamentosQtdMenor'] <= 20) {
                $wherePagamentos .= " and ip.total_pagamentos <= '" . $condicoes['pagamentosQtdMenor'] . "' ";
            }
        }
        if ($condicoes['pagamentos']) {
            if ($condicoes['pagamentos'] >= 1 and $condicoes['pagamentos'] <= 20) {
                $wherePagamentos = " and ip.total_pagamentos = '" . $condicoes['pagamentos'] . "' ";
            }
            /*não pagantes*/
            if ($condicoes['pagamentos'] == '-1') {
                $wherePagamentos = " AND (IFNULL(ip.total_pagamentos,0)<=0) "
                    . " and (ti.cod_inscrito not in (select distinct cod_inscrito from tab_isentos_codigo where cod_evento=" . $codEvento . ")) "
                    . " AND (cod_isento IS NULL OR cod_isento='') "
                    . " AND (ti.cod_inscrito NOT IN (SELECT liv.cod_inscrito FROM tab_lig_inscritos_vouchers liv "
                    . "INNER JOIN tab_vouchers v on v.cod_voucher=liv.cod_voucher AND v.porcentagem=100 "
                    . " WHERE liv.cod_evento=" . $codEvento . " )) "
                    . " AND (ti.cod_perfil not in (select cod_perfil from tab_opcoes_perfis where perfil_isento=1)) "
                    . " AND (ti.cod_inscrito NOT IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip 
                                             WHERE cod_perfil IN (select cod_perfil from tab_opcoes_perfis where perfil_isento=1))) "
                    . " AND (ti.cod_inscrito NOT IN (SELECT cod_inscrito FROM tab_inscritos WHERE cod_evento=" . $codEvento . "
                                             AND cpf IN (SELECT REPLACE(REPLACE(cpf,'.',''),'-','') FROM tab_descontos WHERE cod_evento=" . $codEvento . " AND porcentagem_desconto=100))) "
                    . "";
            }
            /*Pagos*/
            if ($condicoes['pagamentos'] == '99') {
                $wherePagamentos = "and ("
                    . " ti.cod_inscrito IN (SELECT liv.cod_inscrito FROM tab_lig_inscritos_vouchers liv "
                    . " INNER JOIN tab_vouchers v on v.cod_voucher=liv.cod_voucher AND v.porcentagem=100 "
                    . " WHERE liv.cod_evento=" . $codEvento . " ) "
                    . " or ip.total_pagamentos >= 1 "
                    . " or tis.cod_isento<>'' "
                    . " or ti.cod_inscrito in (select distinct cod_inscrito from tab_isentos_codigo )"
                    . " or ti.cod_perfil in (select cod_perfil from tab_opcoes_perfis where perfil_isento=1)"
                    . " or ti.cod_inscrito IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip 
                                            WHERE cod_perfil IN (select cod_perfil from tab_opcoes_perfis where perfil_isento=1))"
                    . " or ti.cod_inscrito IN (SELECT cod_inscrito FROM tab_inscritos WHERE cod_evento=" . $codEvento . "
                                            AND cpf IN (SELECT REPLACE(REPLACE(cpf,'.',''),'-','') FROM tab_descontos WHERE cod_evento=" . $codEvento . " AND porcentagem_desconto=100))"
                    . ")  ";
            }
            /*Pagos menos isentos*/
            if ($condicoes['pagamentos'] == '102') {
                $wherePagamentos = "and ("
                    . " ip.total_pagamentos >= 1 "
                    . ")";
            }
            /*Isentos Indicados*/
            if ($condicoes['pagamentos'] == '98') {
                $wherePagamentos = " and (tis.cod_isento<>''"
                    . " or ti.cod_inscrito in (select distinct cod_inscrito from tab_isentos_codigo))";
            }
            /*Isentos por Perfil*/
            if (($condicoes['pagamentos'] == 'ISENTO-PERFIL')) {
                $wherePagamentos = " AND ((ti.cod_perfil in (select cod_perfil from tab_opcoes_perfis where perfil_isento=1 and cod_evento=" . $codEvento . "))
                or (ti.cod_inscrito IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip 
                    WHERE cod_perfil IN (select cod_perfil from tab_opcoes_perfis where perfil_isento=1 and cod_evento=" . $codEvento . ")))) ";
            }
            /*Desconto por Campanha*/
            if ($condicoes['pagamentos'] == '101') {
                $wherePagamentos = " and ti.cod_inscrito IN (SELECT cod_inscrito FROM tab_inscritos WHERE cod_evento=" . $codEvento . "
                AND cpf IN (SELECT REPLACE(REPLACE(cpf,'.',''),'-','') FROM tab_descontos WHERE cod_evento=" . $codEvento . ")) "; // AND porcentagem_desconto=100)) ";
            }
            /*Isentos por Vouchers*/
            if ($condicoes['pagamentos'] == '100') {
                //                    $wherePagamentos = " and total_vouchers >= 1 ";
                $wherePagamentos = " and ti.cod_inscrito IN (SELECT liv.cod_inscrito FROM tab_lig_inscritos_vouchers liv "
                    . " INNER JOIN tab_vouchers v on v.cod_voucher=liv.cod_voucher AND v.porcentagem=100 "
                    . " WHERE liv.cod_evento=" . $codEvento . " ) ";
            }
            /*Usou voucher*/
            /*Aqui é possível filtrar ainda por v.valor_inscricao, v.porcentagem*/
            if ($condicoes['pagamentos'] == '103') {
                $wherePagamentos = " and ti.cod_inscrito IN (SELECT liv.cod_inscrito FROM tab_lig_inscritos_vouchers liv "
                    . " INNER JOIN tab_vouchers v ON v.cod_voucher=liv.cod_voucher  "
                    . " WHERE liv.cod_evento=" . $codEvento . " ) ";
            }
            /*Desconto por voucher*/
            /*Aqui é possível filtrar ainda por v.valor_inscricao, v.porcentagem*/
            //                if($condicoes['pagamentos']=='104'){
            //                    $wherePagamentos = " and ti.cod_inscrito IN (SELECT liv,cod_inscrito FROM tab_lig_inscritos_vouchers liv "
            //                        . " INNER JOIN tab_vouchers v on v.cod_voucher=liv.cod_voucher and v.status<>'DESATIVADO' AND v.porcentagem "
            //                        . " WHERE liv.cod_evento=".$codEvento." ) ";
            //                }
        }

        //        $sqlLeftJoinPagamento = " left join (SELECT ipi.cod_pagamento, ipi.data_pagamento, ipi.cod_inscrito, ipi.forma_pagamento 
        //            FROM tab_pagamentos ipi 
        //            WHERE ipi.cod_pagamento IN (SELECT MAX(ipi.cod_pagamento) FROM tab_pagamentos ipi WHERE ipi.status='ATIVADA' GROUP BY ipi.cod_inscrito))
        //            as ipi  on ti.cod_inscrito=ipi.cod_inscrito ";
        return $wherePagamentos;
    }
    public static function getWhereCodOficina ($condicoes) {
        $whereCodOficina= "";
        if($condicoes['cod_oficina']){
            $whereCodOficina = " and llto.cod_oficina = ".$condicoes['cod_oficina']." ";
        }
        return $whereCodOficina;
    }
    public static function getWhereQtdPedidos ($condicoes) {
        $whereQtdPedidos = "";
        if($condicoes['pedidos']!=''){
            if($condicoes['pedidos']=='-1'){
                $whereQtdPedidos .= " and (ipd.total_pedidos = 0 or ipd.total_pedidos is null) ";
            }else{
                $whereQtdPedidos .= " and ipd.total_pedidos = ".$condicoes['pedidos']." ";
            }
        }
        if($condicoes['pedidosQtdAte']!=''){
            if($condicoes['pedidosQtdAte']=='-1'){
                $whereQtdPedidos .= " and (ipd.total_pedidos = 0 or ipd.total_pedidos is null) ";
            }else{
                $whereQtdPedidos .= " and ipd.total_pedidos <= ".$condicoes['pedidos']." ";
            }
        }
        return $whereQtdPedidos;
    }
    public static function getWhereDataPagamento ($condicoes) {
        $whereDataPagamento = "";
        if($condicoes['data_pagamento_ini'] and $condicoes['data_pagamento_fim']){
            // Acrescente ' 00:00:00' ao início do período
            $condicoes['data_pagamento_ini'] .= ' 00:00:00';
            // Acrescente ' 23:59:59' ao final do período
            $condicoes['data_pagamento_fim'] .= ' 23:59:59';
            $whereDataPagamento = "and (ipp.data_pagamento between '".formatDateInsert($condicoes['data_pagamento_ini'])."' and '".formatDateInsert($condicoes['data_pagamento_fim'])."')";
        }             
        if($condicoes['data_pagamento_incl_ini'] and $condicoes['data_pagamento_incl_fim']){
            // Acrescente ' 00:00:00' ao início do período
            $condicoes['data_pagamento_incl_ini'] .= ' 00:00:00';
            // Acrescente ' 23:59:59' ao final do período
            $condicoes['data_pagamento_incl_fim'] .= ' 23:59:59';
            $whereDataPagamento = "and (ipp.data_inclusao between '".formatDateInsert($condicoes['data_pagamento_incl_ini'])."' and '".formatDateInsert($condicoes['data_pagamento_incl_fim'])."')";
        }
        return $whereDataPagamento;
    }
    public static function getWhereDataInscricao ($condicoes) {
        $whereDataInscricao = "";
        if($condicoes['data_inscricao_ini'] and $condicoes['data_inscricao_fim']){
            // Acrescente ' 00:00:00' ao início do período
            $condicoes['data_inscricao_ini'] .= ' 00:00:00';
            // Acrescente ' 23:59:59' ao final do período
            $condicoes['data_inscricao_fim'] .= ' 23:59:59';
            $whereDataInscricao = "and (ti.dat_incl between '".formatDateInsert($condicoes['data_inscricao_ini'])."' and '".formatDateInsert($condicoes['data_inscricao_fim'])."')";
        } 
        return $whereDataInscricao;
    }
    public static function getWhereAlmoco ($condicoes) {
        $whereAlmoco = "";
        if($condicoes['almoco']){
            if($condicoes['almoco']=='SIM'){
                $whereAlmoco = " and (ti.info_almoco <>'' and ti.info_almoco <>'null' and ti.info_almoco <>'0') ";
            }
            if($condicoes['almoco']=='NAO'){
                $whereAlmoco = " and (ti.info_almoco is NULL OR ti.info_almoco = '' or  ti.info_almoco ='null')";
            }
        }
        return $whereAlmoco;
    }
    public static function getWhereTipoInscricao ($condicoes) {
        $whereTipoInscricao = "";
        if($condicoes['tipo_perfil']){     
            $whereTipoInscricaoINSCRITO = " AND ("
                . " (ti.cod_inscrito IN (SELECT slip.cod_inscrito FROM tab_lig_inscritos_perfis slip "
                . "     left join tab_opcoes_perfis sop on slip.cod_perfil = sop.cod_perfil"
                . "     WHERE sop.receber_inscricao = 1 )) "
                . " or (ti.cod_perfil IN (SELECT sop.cod_perfil FROM tab_opcoes_perfis sop "
                . "     WHERE sop.receber_inscricao = 1 ))"
                . ")";

            $whereTipoInscricaoOUTROS = " AND ("
                . " (ti.cod_inscrito IN (SELECT slip.cod_inscrito FROM tab_lig_inscritos_perfis slip "
                . "     left join tab_opcoes_perfis sop on slip.cod_perfil = sop.cod_perfil"
                . "     WHERE  sop.receber_inscricao is null or sop.receber_inscricao=0 )) "
                . " or (ti.cod_perfil IN (SELECT sop.cod_perfil FROM tab_opcoes_perfis sop "
                . "     WHERE sop.receber_inscricao is null or sop.receber_inscricao=0))"
                . ")";
            
            if($condicoes['tipo_perfil']=='INSCRITO'){
                $whereTipoInscricao = $whereTipoInscricaoINSCRITO;
            }   
            if($condicoes['tipo_perfil']=='OUTROS'){
                $whereTipoInscricao = $whereTipoInscricaoOUTROS;
            }
            if($condicoes['tipo_perfil']=='AMBOS'){
                $whereTipoInscricao = $whereTipoInscricaoINSCRITO . $whereTipoInscricaoOUTROS;
            }
        }
        return $whereTipoInscricao;
    }

    public static function getWhereTrabalhos ($condicoes) {
        $whereTrabalhos= "";
        if($condicoes['trabalhos']){
            $whereTrabalhos = " and tt.total_trabalhos = '".$condicoes['trabalhos']."' ";
            if($condicoes['trabalhos']=='-1'){
                $whereTrabalhos = " and IFNULL(tt.total_trabalhos,0) <=0 ";
            }
            if($condicoes['trabalhos']=='1'){
                $whereTrabalhos = " and tt.total_trabalhos = 1 ";
            }
            if($condicoes['trabalhos']=='1or'){
                $whereTrabalhos = " and tt.total_trabalhos >= 1 ";
            }
            if($condicoes['trabalhos']=='2'){
                $whereTrabalhos = " and tt.total_trabalhos >= 2 ";
            }
        }
        return $whereTrabalhos;
    }

    public static function getWhereOficinas ($condicoes) {
        $whereOficinas= "";
        if($condicoes['oficinas']){
            $whereOficinas = " and tlio.total_oficinas = '".$condicoes['oficinas']."' ";
            if($condicoes['oficinas']=='-1'){
                $whereOficinas = " and IFNULL(tlio.total_oficinas,0) <=0 ";
            }
            if($condicoes['oficinas']=='1'){
                $whereOficinas = " and tlio.total_oficinas >= 1 ";
            }
            if($condicoes['oficinas']=='2'){
                $whereOficinas = " and tlio.total_oficinas >= 2 ";
            }
        
        }
        return $whereOficinas;
    }

    public static function getWherePagamentoNaoCarregadoXM1 ($condicoes) {
        $wherePagamentoNaoCarregadoXMl = "";
        if ($condicoes['xml_pagamentos_carregados']=='nao-carregados'){ 
            $wherePagamentoNaoCarregadoXMl = " and ipp.cod_pagamento not in (SELECT DISTINCT cod_pagamento FROM fin_carga_hutec)";
        }
        return $wherePagamentoNaoCarregadoXMl;
    }

    public static function getSelectListagemRPA ($condicoes) {
        $selectListagemRPA = "";
        if($condicoes['perspectiva']=='listagemRPA' or $condicoes['perspectiva'] == 'detalhada' or $condicoes['perspectiva']=='listagemAvaliacao' ) {
            $selectListagemRPA = " ,ti.pis ,ti.rg, ti.orgao_expedidor, ti.rg_data_emissao, ti.rg_cidade_emissao, ti.rg_estado_emissao, ti.e_mail_2"
            . ", ti.data_nascimento , ti.cidade_nascimento , ti.estado_nascimento , ti.estado_civil , ti.raca , ti.grau_escolaridade , ti.profissao_atuacao "
            . ", ti.possui_outro_vinculo_empregaticio , ti.endereco , ti.numero , ti.complemento , ti.bairro , ti.cep , ti.cidade , ti.estado , ti.pais "
            . ", ti.fone_contato_ddd , ti.fone_contato , ti.nm_banco, ti.db_banco , ti.db_agencia , ti.db_agencia_dv , ti.db_conta_corrente , ti.db_conta_corrente_dv "
            . ", ti.db_pix , ti.categoria_vaga, ti.categoria_agrupada, ti.possui_outro_vinculo_empregaticio ";
        }
        return $selectListagemRPA; 
    }

    public static function getSelectListagemMBA ($condicoes) {
        $selectListagemMBA = "";
        if($condicoes['perspectiva']=='listagemMBA' or $condicoes['perspectiva'] == 'detalhada'){
            $selectListagemMBA = " ,ti.e_mail_2, ti.rg, ti.data_nascimento"
                    . ", ti.graduacao, ti.graduacao_ano, ti.graduacao_ies "
                    . ", ti.pos_graduacao, ti.pos_graduacao_ano, ti.pos_graduacao_ies "
                    . ", ti.pretensao_carreira, ti.como_ficou_sabendo, ti.qual_motivo_inscreveu "
                    . ", ti.empresa_atual, ti.cargo_atual ";
        }
        return $selectListagemMBA; 
    }

    public static function getWhereConvidadoSis ($fwsCondicao) {
        $whereConvidadoSis = "";
        if(($fwsCondicao['convidadoSis']!='') or ($_SERVER['REMOTE_ADDR']=='189.90.70.566')){
            $fwsCondicao['convidadoSis'] = "GR_AVALIADORES_TRABALHOS";
            $whereConvidadoSis = " and ti.cod_inscrito in (SELECT distinct i.cod_inscrito FROM tab_inscritos i 
            INNER JOIN tab_convidados tc ON ((tc.e_mail=i.e_mail) or (tc.nome_convidado=i.nome_inscrito))
            INNER JOIN sis_usuarios su ON su.login = tc.e_mail AND su.cod_evento=tc.cod_evento
            INNER JOIN sis_ligacao_usuarios_grupos lug ON lug.cod_usuario=su.cod_usuario AND su.cod_evento=lug.cod_evento
            INNER JOIN sis_grupos sg ON sg.cod_grupo=lug.cod_grupo
            WHERE lug.cod_evento=i.cod_evento AND sg.enum_grupo='".$fwsCondicao['convidadoSis']."')";
        }
        return $whereConvidadoSis;
    }

    public static function getSqlInicio ($condicoes) {
        $sqlInicio = " te.titulo as titulo_evento, te.gestor, te.projeto, te.url_prefixo, ";
        if(($condicoes['media']!='xls')and($condicoes['media']!='xml')){
            $sqlInicio .= " teconf.aviso_mensalidade, teconf.aviso_pagamento, teconf.aviso_sala_online, teconf.emitir_certificado, teconf.trab_submissao, ";
        }
//            if($condicoes['perspectiva'] == 'impressao'){
//                $sqlInicio = "";
//            }
        /* permitir mostrar registros duplicados*/
        return $sqlInicio;
    }

    public static function getJoinPatrocinadorVoucher ($condicoes) {
        $innerJoinPatrocinadorVoucher= "";
        if($condicoes['patrocinador_voucher']){
            $innerJoinPatrocinadorVoucher = " inner join tab_lig_inscritos_vouchers liv"
                    . " on liv.cod_inscrito=ti.cod_inscrito "
                    . " inner join tab_vouchers tv on tv.cod_voucher=liv.cod_voucher "
                    . " and tv.patrocinador = '".$condicoes['patrocinador_voucher']."' ";
        }
        return $innerJoinPatrocinadorVoucher;
    }

    public static function getInnerJoinAreaTematica ($condicoes) {
        $innerJoinAreaTematica= "";
        if($condicoes['cod_area_tematica']){
            $innerJoinAreaTematica = " inner join tab_trabalhos lit"
                    . " on lit.cod_inscrito=ti.cod_inscrito and lit.cod_area_tematica = ".$condicoes['cod_area_tematica']." ";
        }
        return $innerJoinAreaTematica;
    }

//    public static function getInnerJoinOficina ($condicoes) {
//        $innerJoinOficina= "";
//                    if($condicoes['cod_oficina']){
//                        $innerJoinOficina = " inner join tab_lig_inscritos_oficinas lto"
//                               . " on lto.cod_inscrito=ti.cod_inscrito and lto.cod_oficina = ".$condicoes['cod_oficina']." ";
//                    }
//          return $innerJoinOficina;
//    }

    public static function getSqlContratos ($condicoes) {
        $selectInscritoContratos = "";
        $leftJoinInscritoContratos = "";
        $whereContratos= "";
        if($condicoes['contrato_anexo']){
            $selectInscritoContratos    = " ,tarc.cod_arquivo_contrato, tarc.total_contratos ";
            $leftJoinInscritoContratos = " left join (SELECT tarc.cod_arquivo_contrato, tarc.cod_inscrito, count(tarc.cod_arquivo_contrato) total_contratos
            FROM tab_arquivo_contrato tarc WHERE tarc.status='ATIVADA' group by tarc.cod_inscrito) as tarc
                on ti.cod_inscrito=tarc.cod_inscrito ";
            if($condicoes['contrato_anexo']=='-1'){
                $whereContratos = " and IFNULL(tarc.total_contratos,0) <=0 ";
            }
            if($condicoes['contrato_anexo']=='1'){
                $whereContratos = " and tarc.total_contratos >= 1 ";
            }
            // if($condicoes['trabalhos']=='2'){
            //     $whereTrabalhos = " and tt.total_trabalhos >= 2 ";
            // }

        }  
        return [
                'select' => $selectInscritoContratos,
                'leftJoin' => $leftJoinInscritoContratos,
                'where' => $whereContratos
                ];
    }

    public static function getFlgAssinado ($condicoes) {
        $selectFlgAssinado = "";
        $leftJoinFlgAssinado = "";
        $whereFlgAssinado= "";
        if($condicoes['flg_assinado']){
            $selectFlgAssinado   = " ,trass.cod_trabalho, trass.flg_assinado ";
            $leftJoinFlgAssinado = " left join (SELECT trass.cod_trabalho, trass.flg_assinado 
            FROM tab_trabalhos trass WHERE trass.status='ATIVADA' and trass.tipo_documento='' group by ti.cod_inscrito) as trass
                on ti.cod_inscrito=trass.cod_inscrito ";

            /* filtrando o contrato assinado*/ 
            if($condicoes['flg_assinado']=='-1'){
                $whereFlgAssinado .= " and trass.flg_assinado = '-1' ";
            }
            if($condicoes['flg_assinado']=='1'){
                $whereFlgAssinado .= " and trass.flg_assinado = 1 ";
            }
        }
        return [
            'select' => $selectFlgAssinado,
            'leftJoin' => $leftJoinFlgAssinado,
            'where' => $whereFlgAssinado
        ];
    }


    public static function getInscritoRPAs ($condicoes) {
        $selectInscritoRPAs = "";
        $leftJoinInscritoRPAs = "";
        $whereRPAs= "";
        if($condicoes['rpa_anexo']){
            $selectInscritoRPAs    = " ,trpa.cod_trabalho, trpa.total_rpas ";
            $leftJoinInscritoRPAs = " left join (SELECT trpa.cod_trabalho, trpa.cod_inscrito, count(trpa.cod_trabalho) total_rpas 
            FROM tab_trabalhos trpa WHERE trpa.status='ATIVADA' and trpa.tipo_documento='RPA' group by trpa.cod_inscrito) as trpa
                on ti.cod_inscrito=trpa.cod_inscrito ";

            if($condicoes['rpa_anexo']=='-1'){
                $whereRPAs = " and IFNULL(trpa.total_rpas,0) <=0 ";
            }
            if($condicoes['rpa_anexo']=='1'){
                $whereRPAs = " and trpa.total_rpas >= 1 ";
            }
            // if($condicoes['trabalhos']=='2'){
            //     $whereTrabalhos = " and tt.total_trabalhos >= 2 ";
            // }
        }
        return [
            'select' => $selectInscritoRPAs,
            'leftJoin' => $leftJoinInscritoRPAs,
            'where' => $whereRPAs
        ];
    }
    public static function getInscritoCompTrans ($condicoes) {
        $selectInscritoCompTrans = "";
        $leftJoinInscritoCompTrans = "";
        $whereCompTrans= "";
        if($condicoes['comp_transf_anexo']){
            $selectInscritoCompTrans    = " ,tcomptrans.cod_trabalho, tcomptrans.total_comp_tranfs ";
            $leftJoinInscritoCompTrans = " left join (SELECT tcomptrans.cod_trabalho, tcomptrans.cod_inscrito, count(tcomptrans.cod_trabalho) total_comp_tranfs 
            FROM tab_trabalhos tcomptrans WHERE tcomptrans.status='ATIVADA' and tcomptrans.tipo_documento='COMPROVANTE DE TRANSFERÊNCIA' group by tcomptrans.cod_inscrito) as tcomptrans
                on ti.cod_inscrito=tcomptrans.cod_inscrito ";

            if($condicoes['comp_transf_anexo']=='-1'){
                $whereCompTrans = " and IFNULL(tcomptrans.total_comp_tranfs,0) <=0 ";
            }
            if($condicoes['comp_transf_anexo']=='1'){
                $whereCompTrans = " and tcomptrans.total_comp_tranfs >= 1 ";
            }
            // if($condicoes['trabalhos']=='2'){
            //     $whereCompTrans = " and tt.total_trabalhos >= 2 ";
            // }
        }
        return [
            'select' => $selectInscritoCompTrans,
            'leftjoin' => $leftJoinInscritoCompTrans,
            'where' => $whereCompTrans
        ];
    }
    public static function getInscritoTermoDesist ($condicoes) {
        $selectInscritoTermoDesist = "";
        $leftJoinInscritoTermoDesist = "";
        $whereTermoDesist= "";
        if($condicoes['termo_desist_anexo']){
            $selectInscritoTermoDesist    = " ,ttermodesist.cod_trabalho, ttermodesist.total_termo_desists ";
            $leftJoinInscritoTermoDesist = " left join (SELECT ttermodesist.cod_trabalho, ttermodesist.cod_inscrito, count(ttermodesist.cod_trabalho) total_termo_desists 
            FROM tab_trabalhos ttermodesist WHERE ttermodesist.status='ATIVADA' and ttermodesist.tipo_documento='TERMO DE DISTÊNCIA' group by ttermodesist.cod_inscrito) as ttermodesist
                on ti.cod_inscrito=ttermodesist.cod_inscrito ";

            if($condicoes['termo_desist_anexo']=='-1'){
                $whereTermoDesist = " and IFNULL(ttermodesist.total_termo_desists,0) <=0 ";
            }
            if($condicoes['termo_desist_anexo']=='1'){
                $whereTermoDesist = " and ttermodesist.total_termo_desists >= 1 ";
            }
            // if($condicoes['trabalhos']=='2'){
            //     $whereTermoDesist = " and tt.total_trabalhos >= 2 ";
            // }
        }
        return [
            'select' => $selectInscritoTermoDesist,
            'leftJoin' => $leftJoinInscritoTermoDesist,
            'where' => $whereTermoDesist
        ];
    }
    public static function getInscritoBoletos ($condicoes) {
        $selectInscritoBoletos = "";
        $leftJoinInscritoBoletos = "";
        $whereBoletos= "";
        if($condicoes['boleto_anexo']){
            $selectInscritoBoletos    = " ,tarc.cod_arquivo_boleto, tarc.total_boletos ";
            $leftJoinInscritoboletos = " left join (SELECT tarc.cod_arquivo_boleto, tarc.cod_inscrito, count(tarc.cod_arquivo_boleto) total_boletos 
            FROM tab_arquivo_boleto tarc WHERE tarc.status='ATIVADA' group by tarc.cod_inscrito) as tarc
                on ti.cod_inscrito=tarc.cod_inscrito ";

            $whereBoletos = " ";
            if($condicoes['boleto_anexo']=='-1'){
                $whereBoletos = " and IFNULL(tarc.total_boletos,0) <=0 ";
            }
            if($condicoes['boleto_anexo']=='1'){
                $whereBoletos = " and tarc.total_boletos >= 1 ";
            }
            // if($condicoes['trabalhos']=='2'){
            //     $whereTrabalhos = " and tt.total_trabalhos >= 2 ";
            // }
        }
        return [
            'select' => $selectInscritoBoletos,
            'leftjoin' => $leftJoinInscritoBoletos,
            'where' => $whereBoletos
        ];
    }
    public static function getSqlSituacaoEventoVinculado($condicoes) {
        $selectSituacaoEventoVinculado   = "";
        $leftJoinSituacaoEventoVinculado = "";
        $whereSituacaoEventoVinculado    = "";
    
        if (!empty($condicoes['cod_evento_vinculado'])) {
            // echo __FILE__.'-';
            $codEventoVinculado = (int) $condicoes['cod_evento_vinculado'];
    
            // Seleciona os campos de status e status_processo do evento vinculado
            $selectSituacaoEventoVinculado = " , tivinc.status as status_vinculado, tivinc.status_processo as status_processo_vinculado ";
            $leftJoinSituacaoEventoVinculado = " LEFT JOIN (
                SELECT tiv.cpf, tiv.status status , tiv.status_processo
                FROM tab_inscritos tiv
                WHERE tiv.cod_evento = {$codEventoVinculado}
            ) as tivinc ON ti.cpf = tivinc.cpf ";
    
            // Garante que só pega registros onde há correspondência no evento vinculado

            $whereSituacaoEventoVinculado = "";
            if (!empty($condicoes['status_processo_vinculado'])) {
                if ($condicoes['status_processo_vinculado'] == 'VINCULADO') {
                    // echo __FILE__.'-';
                    $whereSituacaoEventoVinculado .= " AND tivinc.status_processo IS NOT NULL ";
                } elseif ($condicoes['status_processo_vinculado'] == 'NAO_VINCULADO') {
                    // echo __FILE__.'-';
                    $whereSituacaoEventoVinculado .= " AND tivinc.status_processo IS NULL ";
                } elseif ($condicoes['status_processo_vinculado'] == 'CANCELADO') {
                    // echo __FILE__.'-';
                    $whereSituacaoEventoVinculado .= " AND tivinc.status_processo ='DESISTENTE' ";
                } else {
                    // echo __FILE__.'-';
                    $whereSituacaoEventoVinculado .= " AND tivinc.status_processo = '" . $condicoes['status_processo_vinculado'] . "' ";
                }
            }
        }
    
        return [
            'select' => $selectSituacaoEventoVinculado,
            'leftJoin' => $leftJoinSituacaoEventoVinculado,
            'where' => $whereSituacaoEventoVinculado
        ];
    }
    
    public static function getDocsContratacao($condicoes) {
        $selectDocsContratacao = "";
        $leftJoinDocsContratacao = "";
        $whereDocsContratacao = "";
        $docsFalta = is_array($condicoes['doc_contratacao_falta']) ? $condicoes['doc_contratacao_falta'] : [];

        if ($condicoes['docs_contratacao'] || !empty($docsFalta)) {
            $slugs = "'ctps','certidao_nascimento','foto_cracha','certificado_reservista','certidao_antecedentes'";
            $selectDocsContratacao = " , IFNULL(tdoccontr.total_docs_contratacao, 0) as total_docs_contratacao ";
            $leftJoinDocsContratacao = " LEFT JOIN (
                SELECT tdc.cod_inscrito, COUNT(DISTINCT tdc.slug_tipo_documento) as total_docs_contratacao
                FROM tab_trabalhos tdc
                WHERE tdc.status='ATIVADA'
                AND tdc.slug_tipo_documento IN (" . $slugs . ")
                GROUP BY tdc.cod_inscrito
            ) as tdoccontr ON ti.cod_inscrito = tdoccontr.cod_inscrito ";

            if ($condicoes['docs_contratacao'] == 'pendente') {
                $whereDocsContratacao = " AND (IFNULL(tdoccontr.total_docs_contratacao, 0) < 5) ";
            }
            if ($condicoes['docs_contratacao'] == 'completo') {
                $whereDocsContratacao = " AND (tdoccontr.total_docs_contratacao >= 4) ";
            }

            // Filtro por documento específico faltante
            if (!empty($docsFalta)) {
                $slugsPermitidos = ['ctps','certidao_nascimento','foto_cracha','certificado_reservista','certidao_antecedentes'];
                foreach ($docsFalta as $slug) {
                    $slug = trim($slug);
                    if (in_array($slug, $slugsPermitidos)) {
                        $whereDocsContratacao .= " AND ti.cod_inscrito NOT IN (
                            SELECT tdcf.cod_inscrito FROM tab_trabalhos tdcf
                            WHERE tdcf.status='ATIVADA' AND tdcf.slug_tipo_documento='" . addslashes($slug) . "'
                        ) ";
                    }
                }
            }
        }
        return [
            'select' => $selectDocsContratacao,
            'leftJoin' => $leftJoinDocsContratacao,
            'where' => $whereDocsContratacao
        ];
    }

    public static function getLimit($condicoes, $codEvento) {
        $limit = "";
        // Intervalor de registros
        if (!empty($condicoes['intervalo_registros'])) {
            // formato esperado: offset-quantidade (ex: 30-30)
            [$offset, $qtde] = explode('-', $condicoes['intervalo_registros']);
            $offset = intval($offset);
            $qtde   = intval($qtde);
            $limit = " LIMIT {$offset}, {$qtde} ";
            // echo $limit;
            return $limit;
        }        
        // Quantidade de registros limite
        if (!empty($condicoes['limite_registros'])) {
            $limit = " limit " . intval($condicoes['limite_registros']) . " ";
            return $limit;
        }
        // // if($codEvento==115){
        // //     $limit = " limit 60 ";
        // // }
        // return $limit;
    }
}
