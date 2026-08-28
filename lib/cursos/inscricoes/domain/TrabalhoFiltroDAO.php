<?php
class TrabalhoFiltroDAO {
    public static function getSala ($condicoes) {
        $whereSala = "";
        $innerJoinSala = "";
        if($condicoes['nome_sala']){
            $selectJoinSala = " , es.nome_sala";
//                $innerJoinSala = "inner join vw_ensalamento_sessao es on es.nome_sala = '".$condicoes['nome_sala']."' and ttt.cod_trabalho=es.cod_trabalho";
            $whereSala = " and es.nome_sala = '".$condicoes['nome_sala']."'";
            $condicoes['ordenar'] = "nome_sala";
        }
        return [
            'where' => $whereSala,
            'innerJoin' => $innerJoinSala
        ];
    }

    public static function getWhereSituacao ($condicoes) {
        $whereSituacao = "";
        if ($condicoes['situacao'] == 'ATIVADA'){
            $whereSituacao = " and tt.status ='ATIVADA' ";
        }else if($condicoes['situacao']){
            $whereSituacao = " and tt.status ='".$condicoes['situacao']."' ";
        }else{
            $whereSituacao = " and tt.status ='ATIVADA' ";
        }
        return $whereSituacao;
    }

    public static function getWhereTituloTrabalho ($condicoes) {
        $whereTituloTrabalho = "";
        if($condicoes['titulo_trabalho']){
            $whereTituloTrabalho = " and tt.titulo_trabalho like '%".$condicoes['titulo_trabalho']."%' ";
        }
        return $whereTituloTrabalho;
    }

    public static function getWhereTituloTextoPalavraTrabalho ($condicoes) {
        $whereTituloTextoPalavraTrabalho = "";
        if($condicoes['titulo_texto_trabalho']){
            $whereTituloTextoPalavraTrabalho = " and "
            . "(tt.titulo_trabalho like '%".$condicoes['titulo_texto_trabalho']."%'"
            . " or tt.resumo_trabalho like '%".$condicoes['titulo_texto_trabalho']."%'"
            . " or tt.palavras_chave like '%".$condicoes['titulo_texto_trabalho']."%'"
            . ") ";
        }
        return $whereTituloTextoPalavraTrabalho;
    }

    public static function getWhereFormatoTexto ($condicoes) {
        $whereFormatoTexto = "";
        if($condicoes['formato_texto']){
            if($condicoes['formato_texto']=='relato'){
                $condicoes['formato_texto'] = "Relato de Experiência";
            }
            if($condicoes['formato_texto']=='pesquisa'){
//                    if($_SESSION['cod_evento']==7){
                    $condicoes['formato_texto'] = "Trabalho de Pesquisa";
//                    }else{
//                        $condicoes['formato_texto'] = "Pesquisa Original";
//                    }
            }
            
            if($condicoes['formato_texto']=='-1'){
                $whereFormatoTexto = " and tt.formato_texto IS NULL ";
            }else{
                $whereFormatoTexto = " and tt.formato_texto ='".$condicoes['formato_texto']."' ";
            }
        }
        return $whereFormatoTexto;
    }

    public static function getWherePalavraChave ($condicoes) {
        $wherePalavrasChave = "";
        if($condicoes['palavras_chave']){
            $wherePalavrasChave = " and tt.palavras_chave like '%".$condicoes['palavras_chave']."%' ";
        }
        return $wherePalavrasChave;
    }

    public static function getWhereCoAutor ($condicoes) {
        $whereCoAutor = "";
        if($condicoes['co_autor']){
            $whereCoAutor = " and (tt.nome_co_autor_1 like '%".$condicoes['co_autor']."%'"
                    . " or tt.nome_co_autor_2 like '%".$condicoes['co_autor']."%'  "
                    . " or tt.nome_co_autor_3 like '%".$condicoes['co_autor']."%'  "
                    . " or tt.nome_co_autor_4 like '%".$condicoes['co_autor']."%'  "
                    . " or tt.nome_co_autor_5 like '%".$condicoes['co_autor']."%'  "
                    . " or tt.nome_co_autor_6 like '%".$condicoes['co_autor']."%' ) ";
        }
        return $whereCoAutor;
    }

    public static function getWhereAreaTematica ($condicoes) {
        $whereAreaTematica= "";
        if($condicoes['cod_area_tematica']){
            $whereAreaTematica = " and tt.cod_area_tematica = ".$condicoes['cod_area_tematica']." ";
        }
        if($condicoes['perspectiva'] != 'ANAIS'){
            if ($condicoes['perspectiva']!='INSCRITO'){
                if (COD_EVENTO==70 AND FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)) {
                    $whereAreaTematica = " and tt.cod_area_tematica in (select cod_area_tematica from tab_lig_convidados_areas_tematicas where cod_evento=".COD_EVENTO." and cod_usuario=".$_SESSION['_cod_usuario'].") ";
                }
            }
        }
        return $whereAreaTematica;
    }

    public static function getWhereTrabalhos ($condicoes) {
        $whereTrabalhos= "";
        if($condicoes['trabalhos']){
            $whereTrabalhos = " and ttt.total_trabalhos = '".$condicoes['trabalhos']."' ";
            if($condicoes['trabalhos']=='-1'){
                $whereTrabalhos = " and IFNULL(ttt.total_trabalhos,0) <=0 ";
            }
            if($condicoes['trabalhos']=='1'){
                $whereTrabalhos = " and ttt.total_trabalhos >= 1 ";
            }
            if($condicoes['trabalhos']=='2'){
                $whereTrabalhos = " and ttt.total_trabalhos >= 2 ";
            }
        
        }
        return $whereTrabalhos;
    }

    public static function getWherePagamentos ($condicoes, $codEvento) {
        $wherePagamentos= "";
        if($condicoes['pagamentos']){
            $wherePagamentos = " and ip.total_pagamentos = '".$condicoes['pagamentos']."' ";
            /*não pagantes*/
            if($condicoes['pagamentos']=='-1'){
                // $wherePagamentos = " and IFNULL(ip.total_pagamentos,0) <=0 and cod_voucher is null and cod_isento is null ";
                $wherePagamentos = " AND (IFNULL(ip.total_pagamentos,0)<=0) "
                . " and (ti.cod_inscrito not in (select distinct cod_inscrito from tab_isentos_codigo where cod_evento=".$codEvento.")) "
                . " AND (cod_isento IS NULL OR cod_isento='') "
                . " AND (ti.cod_inscrito NOT IN (SELECT liv.cod_inscrito FROM tab_lig_inscritos_vouchers liv "
                    . "INNER JOIN tab_vouchers v on v.cod_voucher=liv.cod_voucher AND v.porcentagem=100 "
                   . " WHERE liv.cod_evento=".$codEvento." )) "
                . " AND (ti.cod_perfil not in (select cod_perfil from tab_opcoes_perfis where perfil_isento=1)) "
                . " AND (ti.cod_inscrito NOT IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip 
                                             WHERE cod_perfil IN (select cod_perfil from tab_opcoes_perfis where perfil_isento=1))) "
                . " AND (ti.cod_inscrito NOT IN (SELECT cod_inscrito FROM tab_inscritos WHERE cod_evento=".$codEvento."
                                             AND cpf IN (SELECT REPLACE(REPLACE(cpf,'.',''),'-','') FROM tab_descontos WHERE cod_evento=".$codEvento." AND porcentagem_desconto=100))) "
                . "";
            }
            if($condicoes['pagamentos']=='99'){
                $wherePagamentos = "and ("
                    . " ti.cod_inscrito IN (SELECT liv.cod_inscrito FROM tab_lig_inscritos_vouchers liv "
                    . " INNER JOIN tab_vouchers v on v.cod_voucher=liv.cod_voucher AND v.porcentagem=100 "
                    . " WHERE liv.cod_evento=".$codEvento." ) "
                    . " or ip.total_pagamentos >= 1 "
                    . " or tis.cod_isento<>'' "
                    . " or ti.cod_inscrito in (select distinct cod_inscrito from tab_isentos_codigo )"
                    . " or ti.cod_perfil in (select cod_perfil from tab_opcoes_perfis where perfil_isento=1)"
                    . " or ti.cod_inscrito IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip 
                                                WHERE cod_perfil IN (select cod_perfil from tab_opcoes_perfis where perfil_isento=1))"
                    . " or ti.cod_inscrito IN (SELECT cod_inscrito FROM tab_inscritos WHERE cod_evento=".$codEvento."
                                                AND cpf IN (SELECT REPLACE(REPLACE(cpf,'.',''),'-','') FROM tab_descontos WHERE cod_evento=".$codEvento." AND porcentagem_desconto=100))"
                    . ")  ";
            }
            /*Pagos menos isentos*/
            if($condicoes['pagamentos']=='102'){
                $wherePagamentos = "and ("
                    . " ip.total_pagamentos >= 1 "
                    . ")";
            }
            /*Isentos Indicados*/
            if($condicoes['pagamentos']=='98'){
                $wherePagamentos = " and (tis.cod_isento<>''"
                    . " or ti.cod_inscrito in (select distinct cod_inscrito from tab_isentos_codigo))";
            }
            /*Isentos por Perfil*/
            if(($condicoes['pagamentos']=='ISENTO-PERFIL')){
                $wherePagamentos = " AND (ti.cod_perfil in (select cod_perfil from tab_opcoes_perfis where perfil_isento=1))
                    or (ti.cod_inscrito IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip 
                        WHERE cod_perfil IN (select cod_perfil from tab_opcoes_perfis where perfil_isento=1)))";
            }
            /*Desconto por Campanha*/
            if($condicoes['pagamentos']=='101'){
                $wherePagamentos = " and ti.cod_inscrito IN (SELECT cod_inscrito FROM tab_inscritos WHERE cod_evento=".$codEvento."
                    AND cpf IN (SELECT REPLACE(REPLACE(cpf,'.',''),'-','') FROM tab_descontos WHERE cod_evento=".$codEvento.")) "; // AND porcentagem_desconto=100)) ";
            }
            /*Isentos por Vouchers*/
            if($condicoes['pagamentos']=='100'){
//                    $wherePagamentos = " and total_vouchers >= 1 ";
                $wherePagamentos = " and ti.cod_inscrito IN (SELECT liv.cod_inscrito FROM tab_lig_inscritos_vouchers liv "
                    . " INNER JOIN tab_vouchers v on v.cod_voucher=liv.cod_voucher AND v.porcentagem=100 "
                    . " WHERE liv.cod_evento=".$codEvento." ) ";
            }                
        }
        return $wherePagamentos;
    }

    public static function getWhereTipoDocumento ($condicoes) {
        $whereTipoDocumento= "";
        if($condicoes['tipoDocumento']){
            $whereTipoDocumento = " and tt.tipo_documento = '".$condicoes['tipoDocumento']."' ";
        }
        return $whereTipoDocumento;
    }

    public static function getWherePremiacao ($condicoes) {
        $wherePremiacao= "";
        if($condicoes['premiacao']){
            $wherePremiacao = " and tt.premiacao = '".$condicoes['premiacao']."' ";
        }
        return $wherePremiacao;
    }

    public static function getWherePremiado ($condicoes) {
        $wherePremiado= "";
        if($condicoes['premiado']){
            $wherePremiado = " and tt.flg_premiado = '".$condicoes['premiado']."' ";
        }
        return $wherePremiado;
    }

    public static function getWhereConsultaPresenca ($condicoes) {
        $whereConsultaPresenca= "";
        if($condicoes['consultaPresenca']){
            /*só pode esatar presente quem esta com trabalho aprovado*/
            /*essa restrição foi removida, pois nem todos os eventos efetuam avaliação pelo sistema*/
            //$condicoes['avaliacao']='aprovado';
            if($condicoes['consultaPresenca']=='compareceram'){
                $whereConsultaPresenca = " and tt.flg_presenca = 1 ";
            }else{
                $whereConsultaPresenca = " and (tt.flg_presenca is NULL or tt.flg_presenca = 0 or tt.flg_presenca = '') ";
            }
        }
        return $whereConsultaPresenca;
    }

    public static function getWhereConsultaAnexoouArqApresentacao ($condicoes, $objEvConf) {
        $whereConsultaAnexouArqApresentacao= "";
        if($condicoes['consultaAnexoArqApresentacao'] and $objEvConf->getApresentacaoAnexar()!=''){
            /*só pode anexar quem esta com trabalho aprovado*/
//                $condicoes['avaliacao']='aprovado';
            if($condicoes['consultaAnexoArqApresentacao']=='anexaram'){
                $whereConsultaAnexouArqApresentacao = " and (cod_arquivo_apresentacao <>'' or cod_arquivo_apresentacao is not null) ";
                if($condicoes['dataInclusaoAnexo']){
                    $whereConsultaAnexouArqApresentacao .= " and (data_inclusao_arq_apresentacao >='".formatDateInsert($condicoes['dataInclusaoAnexo'])."') ";
                }
            }else{
                $whereConsultaAnexouArqApresentacao = " and (cod_arquivo_apresentacao is null or cod_arquivo_apresentacao = '')";
                if($condicoes['dataInclusaoAnexo']){
                    $whereConsultaAnexouArqApresentacao .= " and (data_inclusao_arq_apresentacao >='".formatDateInsert($condicoes['dataInclusaoAnexo'])."') ";
                }
            }
        }
        return $whereConsultaAnexouArqApresentacao;
    }

    public static function getWhereConsultaAnexoouArqVideo ($condicoes) {
        $whereConsultaAnexouArqVideo= "";
        if($condicoes['consultaAnexoArqVideo']){
            /*só pode anexar quem esta com trabalho aprovado*/
//                $condicoes['avaliacao']='aprovado';
            if($condicoes['consultaAnexoArqVideo']=='anexaram'){
                $whereConsultaAnexouArqVideo = " and (cod_arquivo_video <>'' or cod_arquivo_video is not null) ";
            }else{
                $whereConsultaAnexouArqVideo = " and (cod_arquivo_video is null or cod_arquivo_video = '')";
            }
        }
        return $whereConsultaAnexouArqVideo;
    }

    public static function getWhereConsultaPremiados ($condicoes) {
        $whereConsultaPremiados= "";
        if($condicoes['consultaPremiados']){
            if($condicoes['consultaPremiados']=='premiados'){
                $whereConsultaPremiados = " and tt.flg_premiado = 1 ";
            }
            if($condicoes['consultaPremiados']=='indicado_pela_comissao'){
                $whereConsultaPremiados = " and tt.flg_indicado_comissao = 1 ";
            }
            if($condicoes['consultaPremiados']=='indicado_pelo_avaliador'){
                // $whereConsultaPremiados = " and tha.indicar_premiacao = 1 ";
                $condicoes['avaliacao']='aprovado-indicado-premiacao';
            }
        }
        return $whereConsultaPremiados;
    }

    public static function getWhereAvaliador ($condicoes) {
        $whereAvaliador = "";
        if($condicoes['cod_avaliador']!=''){
            $whereAvaliador = " and (tha.cod_avaliador=".$condicoes['cod_avaliador'].") ";
        }
        return $whereAvaliador;
    }

    public static function getWhereHistoriaAvaliacao ($condicoes) {
        $whereHistoricoAvaliacao = "";
        if($condicoes['avaliacao']=='aprovado'){
            $whereHistoricoAvaliacao = " and (tha.avaliacao='APROVADO' AND ta.avaliacao='APROVADO') ";
        }
        if($condicoes['avaliacao']=='aprovado-indicado-premiacao'){
            $whereHistoricoAvaliacao = " and (tha.avaliacao='APROVADO' AND tha.indicar_premiacao=1) ";
        }
        if($condicoes['avaliacao']=='aprovado-nao-indicado-premiacao'){
            $whereHistoricoAvaliacao = " and (tha.avaliacao='APROVADO' AND tha.indicar_premiacao<>1) ";
        }
        if($condicoes['avaliacao']=='reprovado'){
            $whereHistoricoAvaliacao = " and (tha.avaliacao='REPROVADO' or ta.avaliacao='REPROVADO') ";
        }
        if($condicoes['avaliacao']=='devolvido'){
            $whereHistoricoAvaliacao = " and (tt.flg_enviado_avaliacao=0 and tha.avaliacao='DEVOLVIDO') ";
        }
        if($condicoes['avaliacao']=='selecionado'){
            $whereHistoricoAvaliacao = " and (tha.avaliacao='SELECIONADO') ";
        }
        if($condicoes['avaliacao']=='re_submetido'){
            $whereHistoricoAvaliacao = " and (tha.avaliacao='RE-SUBMETIDO') ";
        }
        if($condicoes['avaliacao']=='submetido'){
            $whereHistoricoAvaliacao = " and (tt.flg_enviado_avaliacao=1 and (tha.avaliacao is null or tha.avaliacao='SUBMETIDO')) ";
        }
        if($condicoes['avaliacao']=='nao_enviou_avaliacao'){
            $whereHistoricoAvaliacao = " and (tt.flg_enviado_avaliacao is null and (tha.avaliacao is null or tha.avaliacao='')) ";
        }
        /*quando todo trabalho enviado esta automáticamente disponível para avaliação*/
        if(($condicoes['avaliacao']=='submetido')or($condicoes['avaliacao']=='nao_enviou_avaliacao')){
            $whereHistoricoAvaliacao = " and "
                    . "  (tha.avaliacao is null or tha.avaliacao=''or tha.avaliacao='SUBMETIDO') ";
        }
        return $whereHistoricoAvaliacao;
    }
}