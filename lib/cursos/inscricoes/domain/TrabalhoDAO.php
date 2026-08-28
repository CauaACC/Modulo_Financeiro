<?php
class TrabalhoDAO extends DAO{
         
    public $sqlLeftJoinColuns = " , IFNULL(ip.total_pagamentos,0) as total_pagamentos "
                               ." , IFNULL(ip.valor_pagamentos,0) as valor_pagamentos " 
                               ." , IFNULL(ttt.total_trabalhos,0) as total_trabalhos "
                               ." , tha.cod_avaliador, tha.avaliacao, tha.e_mail_enviado , tha.id_inclusao, tha.data_inclusao as data_tramitacao "
                               ." , ta.observacao, ta.avaliacao as avaliacao_final, ta.criterio_1, ta.criterio_2, ta.criterio_3, ta.criterio_4, ta.criterio_5, ta.criterio_6 "
                               ." , (ta.criterio_1+ ta.criterio_2+ ta.criterio_3+ ta.criterio_4+ ta.criterio_5+ ta.criterio_6) AS total_nota_primeira_avalicao "
                               ." , c.e_mail_avaliador, c.nome_avaliador "
                               ." , IFNULL(tis.cod_isento, '') AS cod_isento "
                            
                               ." , IFNULL(thap.total_nota_premiacao,0) as total_nota_premiacao "
                               ." , IFNULL(thap.qtd_avaliacao_premiacao,0) as qtd_avaliacao_premiacao "
                               ." , IFNULL(iv.cod_voucher,'') as cod_voucher "
                               ." , IFNULL(ivq.total_vouchers,0) as total_vouchers "
                               
                               ." , IFNULL(taprc.total_arquivos_apresentacao,0) as total_arquivos_apresentacao "
                               ." , IFNULL(taprar.cod_arquivo_apresentacao,'') as cod_arquivo_apresentacao, taprar.arquivo_gerado_apresentacao, taprar.data_inclusao_arq_apresentacao "
                               ." , IFNULL(tavulso.cod_arquivo_avulso,'') as cod_arquivo_avulso, tavulso.arquivo_gerado_avulso, tavulso.data_inclusao_arq_avulso "
                               
                               ." , IFNULL(tapvi.cod_arquivo_video,'') as cod_arquivo_video, tapvi.arquivo_gerado_video "
//                               ." , taprar.arquivo_gerado_apresentacao as arquivo_apresentacao, taprar.cod_arquivo_apresentacao "
                               
                                . "";
    
    public $sqlLeftJoin = "
            left join (SELECT count(ip.cod_inscrito) as total_pagamentos, sum(ip.valor) as valor_pagamentos, ip.cod_inscrito FROM tab_pagamentos ip 
                       group by ip.cod_inscrito) as ip 
                       on tt.cod_inscrito=ip.cod_inscrito
            left join (SELECT count(ttt.cod_inscrito) as total_trabalhos, ttt.cod_inscrito 
                        FROM tab_trabalhos ttt WHERE IFNULL(ttt.tipo_documento,'') <> 'COMPROVANTE_PERFIL' 
                       group by ttt.cod_inscrito) as ttt on ttt.cod_inscrito=tt.cod_inscrito
            left join (SELECT tha.* FROM tab_historico_avaliacao tha 
                            INNER JOIN (SELECT MAX(maxtha.cod_avaliacao) AS cod_avaliacao,maxtha.cod_trabalho 
                                            FROM tab_historico_avaliacao maxtha 
                                            WHERE maxtha.status='ATIVADA' and maxtha.cod_evento=".COD_EVENTO." 
                                            GROUP BY maxtha.cod_trabalho
                                        ) AS maxtha ON tha.cod_avaliacao=maxtha.cod_avaliacao
                            WHERE tha.status='ATIVADA' 
                            ORDER BY tha.cod_trabalho, tha.cod_avaliacao
                       ) as tha on tha.cod_trabalho=tt.cod_trabalho
            left join tab_trabalhos_avaliacao ta on tt.cod_trabalho = ta.cod_trabalho AND ta.status='ATIVADA'  AND ta.avaliacao<>'Cancelada'
            left join (SELECT su.login AS e_mail_avaliador,c.nome_convidado as nome_avaliador, su.cod_usuario AS cod_avaliador FROM tab_convidados c INNER JOIN sis_usuarios su ON su.cod_usuario=c.cod_usuario) c ON c.cod_avaliador=tha.cod_avaliador "

             ." LEFT JOIN (SELECT cpf,cod_isento, cod_evento FROM tab_isentos where cod_evento=".COD_EVENTO.") AS tis ON tis.cpf=ti.cpf and ti.cod_evento=tis.cod_evento
            LEFT JOIN (SELECT thap.cod_evento, thap.cod_inscrito, thap.cod_trabalho, count(thap.cod_trabalho) as qtd_avaliacao_premiacao, SUM(thap.nota) AS total_nota_premiacao FROM 
                        vw_nota_avaliacao_premiacao thap
                        GROUP BY thap.cod_trabalho) AS thap on thap.cod_trabalho=tt.cod_trabalho
                    
            LEFT JOIN (SELECT distinct iv.cod_voucher, cod_inscrito FROM tab_lig_inscritos_vouchers iv
                WHERE iv.status='ATIVADA' and iv.cod_evento=".COD_EVENTO." ) AS iv ON iv.cod_inscrito=ti.cod_inscrito
                    
            LEFT JOIN (SELECT count(ivq.cod_voucher) as total_vouchers, cod_inscrito FROM tab_lig_inscritos_vouchers ivq
                WHERE ivq.status='ATIVADA' and ivq.cod_evento=".COD_EVENTO."  group by cod_inscrito) AS ivq ON ivq.cod_inscrito=ti.cod_inscrito
                    
            LEFT JOIN (SELECT count(taprc.cod_inscrito) as total_arquivos_apresentacao, cod_inscrito FROM tab_arquivo_apresentacao taprc
                WHERE taprc.status='ATIVADA' and taprc.cod_evento=".COD_EVENTO."  group by cod_inscrito)
                    AS taprc ON taprc.cod_inscrito=ti.cod_inscrito   

            LEFT JOIN (SELECT taprar.arquivo_gerado as arquivo_gerado_apresentacao, max(taprar.cod_arquivo_apresentacao) as cod_arquivo_apresentacao, taprar.cod_inscrito, taprar.cod_trabalho, taprar.data_inclusao as data_inclusao_arq_apresentacao
                FROM tab_arquivo_apresentacao taprar WHERE taprar.cod_evento=".COD_EVENTO." AND taprar.status='ATIVADA' GROUP BY taprar.cod_trabalho) AS taprar
                    ON taprar.cod_inscrito=tt.cod_inscrito and taprar.cod_trabalho=tt.cod_trabalho 

            LEFT JOIN (SELECT tavulso.arquivo_gerado as arquivo_gerado_avulso, max(tavulso.cod_arquivo_avulso) as cod_arquivo_avulso, tavulso.cod_inscrito, tavulso.cod_trabalho, tavulso.data_inclusao as data_inclusao_arq_avulso
                FROM tab_arquivo_avulso tavulso WHERE tavulso.cod_evento=".COD_EVENTO." AND tavulso.status='ATIVADA' GROUP BY tavulso.cod_trabalho) AS tavulso
                    ON tavulso.cod_inscrito=tt.cod_inscrito and tavulso.cod_trabalho=tt.cod_trabalho 

            LEFT JOIN (SELECT tapvi.arquivo_gerado as arquivo_gerado_video, max(tapvi.cod_arquivo_video) as cod_arquivo_video, tapvi.cod_inscrito, tapvi.cod_trabalho
                FROM tab_arquivo_video tapvi WHERE tapvi.cod_evento=".COD_EVENTO." AND tapvi.status='ATIVADA' GROUP BY tapvi.cod_trabalho) AS tapvi
                    ON tapvi.cod_inscrito=tt.cod_inscrito and tapvi.cod_trabalho=tt.cod_trabalho 

                 ";   
    
//             LEFT JOIN (SELECT arquivo_gerado as arquivo_gerado_apresentacao, cod_arquivo_apresentacao, cod_inscrito, cod_trabalho FROM tab_arquivo_apresentacao WHERE cod_evento=".COD_EVENTO." AND
//                cod_arquivo_apresentacao IN (SELECT MAX(cod_arquivo_apresentacao) FROM `tab_arquivo_apresentacao` 
//                WHERE cod_evento=".COD_EVENTO." AND STATUS='ATIVADA' GROUP BY cod_trabalho ORDER BY cod_arquivo_apresentacao)
//                ) AS taprar ON taprar.cod_inscrito=tt.cod_inscrito and taprar.cod_trabalho=tt.cod_trabalho 
//
//            LEFT JOIN (SELECT arquivo_gerado as arquivo_gerado_video, cod_arquivo_video, cod_inscrito, cod_trabalho FROM tab_arquivo_video WHERE cod_evento=".COD_EVENTO." AND
//                cod_arquivo_video IN (SELECT MAX(cod_arquivo_video) FROM `tab_arquivo_video` 
//                WHERE cod_evento=".COD_EVENTO." AND STATUS='ATIVADA' GROUP BY cod_trabalho ORDER BY cod_arquivo_video)
//               ) AS taprvi ON taprvi.cod_inscrito=tt.cod_inscrito and taprvi.cod_trabalho=tt.cod_trabalho 
//    
//            LEFT JOIN (SELECT * FROM tab_arquivo_apresentacao WHERE cod_evento = ".COD_EVENTO." AND
//                cod_arquivo_apresentacao IN (SELECT MAX(cod_arquivo_apresentacao) FROM `tab_arquivo_apresentacao` 
//                WHERE cod_evento = ".COD_EVENTO." GROUP BY cod_trabalho ORDER BY cod_arquivo_apresentacao)) 
//                AS tapr ON tapr.cod_inscrito=tt.cod_inscrito and  tapr.cod_trabalho=tt.cod_trabalho


    public function __construct() {        
        parent::__construct();
    }  
    
    public function carregarListaTipoDocumentos(Trabalho $obj, $debug=false) {
        try{

            $sql = sprintf('SELECT distinct t.tipo_documento FROM tab_trabalhos t '
                    . ' where t.cod_evento='.$obj->getCodEvento().' and t.status=\'ATIVADA\' '
                    . ' order by t.tipo_documento ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            // $rs = $this->conex->Execute($sql);
            $rs = $this->conex->CacheExecute(600, $sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                $total_registros = 0;
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $total_registros++;
                    $return[] = clone $obj;
                }
                $obj->setListaTipoDocumentos($return);
                $obj->setCountListaTipoDocumentos($total_registros);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }  

    public function carregarDadosTrabalhos(Trabalho $obj, $condicao=false, $debug=false){
        try{
            $sql = sprintf('SELECT IFNULL(COUNT(t.cod_trabalho),0) AS total_trabalhos FROM tab_trabalhos t 
            INNER JOIN tab_inscritos i ON i.cod_inscrito=t.cod_inscrito AND i.status=\'ATIVADA\'
            WHERE t.cod_evento=\'%s\' AND t.status=\'ATIVADA\' AND IFNULL(t.tipo_documento,\'\') <> \'COMPROVANTE_PERFIL\'', $obj->getCodEvento());
            
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
    
    public function getById(Trabalho $obj, $debug=false) {
        try{
            $whereCodInscrito = "";
            if($obj->getCodInscrito()!=''){
                $whereCodInscrito = " and tt.cod_inscrito=".$obj->getCodInscrito()." ";
            }
            $sql = sprintf('SELECT ti.nome_inscrito, toa.titulo, tt.* '
                    . ' FROM tab_trabalhos tt '
                    . ' INNER JOIN tab_inscritos ti ON ti.cod_inscrito=tt.cod_inscrito '
                    . ' LEFT JOIN tab_opcoes_areas_tematicas toa on tt.cod_area_tematica = toa.cod_area_tematica '
                    . ' WHERE tt.cod_evento=\'%s\' '
            . 'and tt.cod_trabalho=\'%s\' '.$whereCodInscrito.' ', $obj->getCodEvento(),$obj->getCodTrabalho());

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

    public function getByCapituloUltimo(Trabalho $obj, $debug=false) {
        try{
            $whereCodInscrito = "";
            if($obj->getCodInscrito()!=''){
            }
            $whereCodInscrito .= " and tt.cod_inscrito=".$obj->getCodInscrito()." ";
            $whereCodInscrito .= " and tt.cod_area_tematica=".$obj->getCodAreaTematica()." ";
            $sql = sprintf('SELECT ti.nome_inscrito, toa.titulo, tt.* '
                    . ' FROM tab_trabalhos tt '
                    . ' INNER JOIN tab_inscritos ti ON ti.cod_inscrito=tt.cod_inscrito '
                    . ' LEFT JOIN tab_opcoes_areas_tematicas toa on tt.cod_area_tematica = toa.cod_area_tematica '
                    . ' WHERE tt.cod_evento=\'%s\' '
            . ' '.$whereCodInscrito.' ORDER BY versao DESC LIMIT 1', $obj->getCodEvento(),$obj->getCodTrabalho());

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

    public function getByDocumentoUltimo(Trabalho $obj, $debug=false) {
        try{
            $whereCodInscrito = "";
            if($obj->getCodInscrito()!=''){
            }
            $whereCodInscrito .= " and tt.cod_inscrito=".$obj->getCodInscrito()." ";
            $whereCodInscrito .= " and tt.tipo_documento='".$obj->getTipoDocumento()."' ";
            if ($obj->getSlugTipoDocumento()){
                $whereCodInscrito .= " and tt.slug_tipo_documento='".$obj->getSlugTipoDocumento()."' ";
            }
            $sql = sprintf('SELECT ti.nome_inscrito, toa.titulo, tt.* '
                    . ' FROM tab_trabalhos tt '
                    . ' INNER JOIN tab_inscritos ti ON ti.cod_inscrito=tt.cod_inscrito '
                    . ' LEFT JOIN tab_opcoes_areas_tematicas toa on tt.cod_area_tematica = toa.cod_area_tematica '
                    . ' WHERE tt.cod_evento=\'%s\' '
            . ' '.$whereCodInscrito.' ORDER BY tt.cod_trabalho desc, tt.versao DESC LIMIT 1', $obj->getCodEvento());

            // . ' '.$whereCodInscrito.' ORDER BY tt.versao DESC LIMIT 1', $obj->getCodEvento(),$obj->getCodTrabalho());

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

    public function getByIdTrabalho(Trabalho $obj, $debug=false) {
        try{
            $whereAvaliacao = '';
            if($obj->getFlgEnviadoAvaliacao() == 1){
                $whereAvaliacao = ' and tt.flg_enviado_avaliacao = '.$obj->getFlgEnviadoAvaliacao();
            }
            $sql = sprintf('SELECT tt.*, toa.titulo as titulo_area_tematica FROM tab_trabalhos tt
            left JOIN tab_opcoes_areas_tematicas toa ON toa.cod_area_tematica=tt.cod_area_tematica    
            INNER JOIN tab_inscritos ti ON ti.cod_inscrito=tt.cod_inscrito and ti.status=\'ATIVADA\' 
            WHERE tt.cod_evento=\'%s\' '
            . $whereAvaliacao
            . ' and tt.cod_trabalho= \'%s\'', $obj->getCodEvento(),$obj->getCodTrabalho());

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
            $sql = "SELECT IFNULL(MAX(cod_trabalho),0)+1 FROM tab_trabalhos";

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
    public function getByIdExiste(Trabalho $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM tab_trabalhos WHERE cod_evento=\'%s\' and cod_trabalho= \'%s\'', $obj->getCodEvento(),$obj->getCodTrabalho());

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
    public function getAll(Trabalho $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_trabalhos order by cod_evento desc');

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
    public function carregarWhere($condicoes = false, $debug = false) {
        $independenteSituacao = false;
        if($condicoes['nome_inscrito']){
            $this->whereSql .= " and ti.nome_inscrito like '%".$condicoes['nome_inscrito']."%' ";
            $independenteSituacao = true;
        }
        if($condicoes['e_mail']){
            $this->whereSql .= " and ti.e_mail = '".$condicoes['e_mail']."' ";
            $independenteSituacao = true;
        }
        if($condicoes['cpf']){
            $this->whereSql .= " and ti.cpf = '".$condicoes['cpf']."' ";
            $independenteSituacao = true;
        }
        if($condicoes['cod_inscrito']){
            $this->whereSql .= " and ti.cod_inscrito =".$condicoes['cod_inscrito']." ";
            $independenteSituacao = true;
        }
        if($condicoes['cod_trabalho']){
            $this->whereSql .= " and tt.cod_trabalho =".$condicoes['cod_trabalho']." ";
            $independenteSituacao = true;
        }
        if($condicoes['situacaoInscrito']){
            $this->whereSql .= " and ti.status ='".$condicoes['situacao']."' ";
        }
        if($condicoes['estado']){
            $this->whereSql .= " and ti.estado = '".$condicoes['estado']."' ";
        }
        if($condicoes['cidade']){
            $this->whereSql .= " and ti.cidade = '".$condicoes['cidade']."' ";
        }
            /*com relação ao inscrito*/
//        if($condicoes['situacao_inscrito']){
        if($condicoes['situacao_inscrito'] and $independenteSituacao == false){
            $this->whereSql .= " and ti.status ='".$condicoes['situacao_inscrito']."' ";
        }
        if($condicoes['presenca_inscrito']){
            if($condicoes['presenca_inscrito']==''){
            }else if($condicoes['presenca_inscrito']=='SIM'){
                $this->whereSql .= " and ti.flg_presenca_inscrito IS NOT NULL ";
            }else if($condicoes['presenca_inscrito']=='NAO'){
                $this->whereSql .= " and ti.flg_presenca_inscrito IS NULL ";
            }
        }
        if($condicoes['outros_filtros_inscrito']){
            if($condicoes['outros_filtros_inscrito']=='avaliou_evento'){
                $this->whereSql .= " and ti.avaliou_evento IS NOT NULL ";
            }
        }
        if($condicoes['flg_assinado']!=''){
            $this->whereSql .= " and (tt.flg_assinado=".$condicoes['flg_assinado'].") ";
        }             
        if($condicoes['tipo_documento']!=''){
            $this->whereSql .= " and (tt.tipo_documento='".$condicoes['tipo_documento']."') ";
        }         
    }
    public function carregarListaTrabalhosAceitosByIdInscrito(Trabalho $obj, $condicoes = false, $debug=false) {
        $condicoes['perspectiva'] = 'INSCRITO';
        return $this->carregarListaTrabalhos($obj, $condicoes, $debug);
    }    
    public function carregarListaTrabalhos(Trabalho $obj, $condicoes = false, $debug=false) {
        try{
            $this->carregarWhere($condicoes, $debug);
            if($condicoes['cod_evento']!='' and ($condicoes['cod_evento']!='-1')){
                $this->codEvento = $condicoes['cod_evento'];
                $obj->setCodEvento($this->codEvento);
            }else{
                $this->codEvento = COD_EVENTO;
            }
            $objEvConf = $obj->getObjEvConf();

            $result = TrabalhoFiltroDAO::getSala($condicoes);
            $whereSala = $result['where'];
            $innerJoinSala = $result['innerJoin'];
            unset($result);

            $whereSituacao = TrabalhoFiltroDAO::getWhereSituacao($condicoes);

            $whereTituloTrabalho = TrabalhoFiltroDAO::getWhereTituloTrabalho($condicoes);

            $whereTituloTextoPalavraTrabalho = TrabalhoFiltroDAO::getWhereTituloTextoPalavraTrabalho($condicoes);

            $whereFormatoTexto = TrabalhoFiltroDAO::getWhereFormatoTexto($condicoes);

            $wherePalavrasChave = TrabalhoFiltroDAO::getWherePalavraChave($condicoes);

            $whereCoAutor = TrabalhoFiltroDAO::getWhereCoAutor($condicoes);

            $whereAreaTematica= TrabalhoFiltroDAO::getWhereAreaTematica($condicoes);
            $whereTrabalhos = "";
            $whereTrabalhos.= TrabalhoFiltroDAO::getWhereTrabalhos($condicoes);
            $whereTrabalhos.= TrabalhoFiltroDAO::getWhereTipoDocumento($condicoes);
            if(!$condicoes['tipoDocumento']){ /*comprovante de perfil (dados pessoais) nao e trabalho: so aparece se filtrado explicitamente*/
                $whereTrabalhos.= " and IFNULL(tt.tipo_documento,'') <> 'COMPROVANTE_PERFIL' ";
            }

            $wherePagamentos= TrabalhoFiltroDAO::getWherePagamentos($condicoes, $this->$codEvento);

            $wherePremiacao= TrabalhoFiltroDAO::getWherePremiacao($condicoes);

            $wherePremiado= TrabalhoFiltroDAO::getWherePremiado($condicoes);

            $whereConsultaPresenca= TrabalhoFiltroDAO::getWhereConsultaPresenca($condicoes);

            $whereConsultaAnexouArqApresentacao= TrabalhoFiltroDAo::getWhereConsultaAnexoouArqApresentacao($condicoes, $objEvConf);

            $whereConsultaAnexouArqVideo= TrabalhoFiltroDAO::getWhereConsultaAnexoouArqVideo($condicoes);

            $whereConsultaPremiados= TrabalhoFiltroDAO::getWhereConsultaPremiados($condicoes);

            $whereAvaliacao= "";
//            if($condicoes['avaliacao']=='aprovado'){
//                $whereAvaliacao = " and ((ta.avaliacao='APROVADO')
//                                            or(ta.avaliacao='APROVADO-REPROVADO')
//                                            or(ta.avaliacao='APROVADO-CA-REPROVADO')
//                                            or(ta.avaliacao='APROVADO-REPROVADO-REPROVADO')
//                                            or(ta.avaliacao='REALOCADO-TEMA-15-APROVADO')
//                                            or(ta.avaliacao='REALOCADO-TEMA-15-APROVADO-REPROVADO')
//                                         ) ";
//            }
//            if($condicoes['avaliacao']=='reprovado'){
//                $whereAvaliacao = " and (SUBSTR(ta.avaliacao,1,9)='REPROVADO') ";
//            }
//            if($condicoes['avaliacao']=='devolvido'){
//                $whereAvaliacao = " and (SUBSTR(ta.avaliacao,1,9)='REPROVADO') ";
//            }
//            if($condicoes['avaliacao']=='enviou_avaliacao'){
//                $whereAvaliacao = " and (tt.flg_enviado_avaliacao=1) ";
//            }

            $whereAvaliador = TrabalhoFiltroDAO::getWhereAvaliador($condicoes);
            
            $whereHistoricoAvaliacao = TrabalhoFiltroDAO::getWhereHistoriaAvaliacao($condicoes);
                
            $innerJoinEnsalamento = " left JOIN vw_ensalamento es ON tt.cod_trabalho=es.cod_trabalho ";
            $innerJoinEnsalamentoSelect = " , es.nome_bloco, es.nome_sala,es.data, es.hora_ini, es.turno ";
            if($condicoes['perspectiva']=='ensalamento'){
                $innerJoinEnsalamento = ""
                . " INNER JOIN vw_ensalamento es ON tt.cod_trabalho=es.cod_trabalho ";
                $condicoes['ordenar'] = "ensalamento";
            }
                          
            if($debug == 1){
                echo "->".$condicoes['ordenar']."<-<hr>";
            }
            
            switch ($condicoes['ordenar']) {
                case 'nome_inscrito':
                case 'inscrito':
                    $orderBy = " order by ti.nome_inscrito ";
                    break;
                case 'titulo_trabalho':
                    $orderBy = " order by tt.titulo_trabalho ";
                    break;
                case 'cod_trabalho':
                    $orderBy = " order by tt.cod_trabalho";
                    break;
                case 'cod_inscrito':
                    $orderBy = " order by tt.cod_inscrito";
                    break;
                case 'area_tematica':
                    $orderBy = " order by toa.ordem, titulo_area_tematica, tha.indicar_premiacao desc , tt.cod_trabalho";
                    break;
                case 'ordem_area_tematica':
                    $orderBy = " order by ordem_area_tematica,titulo_area_tematica, tha.indicar_premiacao desc , tt.cod_trabalho";
                    break;
                case 'ensalamento':
                case 'nome_sala':
                    $orderBy = " ORDER BY tt.cod_evento, es.cod_sessao";
                    break;
                case 'data_inclusao_arq_apresentacao':
                    $orderBy = " ORDER BY taprar.data_inclusao_arq_apresentacao desc";
                    break;
                case 'pontos_total':
                    $orderBy = " ORDER BY CAST(tt.pontos_total AS UNSIGNED) DESC";
                    break;
                case 'total_nota_premiacao':
                    $orderBy = " order by thap.total_nota_premiacao ";
                    break;
                default:
                    $orderBy = "order by tt.cod_inscrito desc";
                    break;
            }
            if($condicoes['media']=='xls'){
                //$debug=true;
                $condicoes['campos_exportar'] = '';
                if($condicoes['campos_exportar']!=''){
                    $sqlCampos = " ti.nome_inscrito as Nome"
                            . ", ti.e_mail "
                            . ", ti.cidade as Cidade, ti.estado as Estado"
                            . ", ti.instituicao, ti.categoria_vaga "
                            . ", tt.autor_apresentador_nome "
                            . ", tt.nome_co_autor_1, nome_co_autor_2, nome_co_autor_3, nome_co_autor_4, nome_co_autor_5, nome_co_autor_6 "
                            . ", TRIM(BOTH '|' FROM 
                                    CONCAT_WS('|', 
                                        NULLIF(tt.nome_co_autor_1, ''), 
                                        NULLIF(tt.nome_co_autor_2, ''), 
                                        NULLIF(tt.nome_co_autor_3, ''), 
                                        NULLIF(tt.nome_co_autor_4, ''), 
                                        NULLIF(tt.nome_co_autor_5, ''), 
                                        NULLIF(tt.nome_co_autor_6, '')
                                    )
                                ) AS co_autores  "                            
                            . ", toa.titulo AS titulo_area_tematica "
                            . ", tha.indicar_premiacao "
                            . ", tt.instituicao_trabalho "
                            . ", tt.resumo_trabaho "
                            . ", tt.referencias_trabaho "
                            . ", IFNULL(thap.total_nota_premiacao,0) as total_nota_premiacao "
                            . ", tt.pontos_total ";
                    
                }else{
                    $sqlCampos = "ti.id_publico, tt.cod_inscrito "
                            . ",SUBSTRING_INDEX(SUBSTRING_INDEX(ti.nome_inscrito, ' ', 1), ' ', -1)  AS primeironome "
                            . ",TRIM( SUBSTR(ti.nome_inscrito, LOCATE(' ', ti.nome_inscrito)) ) AS sobrenome "
                            . ",ti.nome_inscrito as Nome, ti.cpf, ti.e_mail as Email, ti.cidade as Cidade, ti.estado as Estado
                        , ti.fone_contato_ddd, ti.fone_contato, ti.profissao_atuacao, ti.tipo_instituicao, ti.instituicao, ti.categoria_vaga "
                    . ", tt.cod_trabalho,tt.titulo_trabalho "
                    . ", tt.autor_apresentador_nome "
                    . ", tt.nome_co_autor_1, nome_co_autor_2, nome_co_autor_3, nome_co_autor_4, nome_co_autor_5, nome_co_autor_6 "
                    . ", TRIM(BOTH '|' FROM 
                            CONCAT_WS('|', 
                                NULLIF(tt.nome_co_autor_1, ''), 
                                NULLIF(tt.nome_co_autor_2, ''), 
                                NULLIF(tt.nome_co_autor_3, ''), 
                                NULLIF(tt.nome_co_autor_4, ''), 
                                NULLIF(tt.nome_co_autor_5, ''), 
                                NULLIF(tt.nome_co_autor_6, '')
                            )
                        ) AS co_autores  "
                    . ", toa.cod_area_tematica, toa.titulo AS titulo_area_tematica"
                    . ", toa.ordem AS ordem_area_tematica,tt.palavras_chave, tt.instituicao_trabalho"
                    . ", tt.formato_texto, tt.formato_apresentacao "
                    . ", tt.resumo_trabalho, tt.referencias_trabalho , ti.cidade, ti.estado, ti.pais"
                    . ", tt.status, tt.arquivo_gerado, tt.arquivo_identificado_gerado, tha.indicar_premiacao, tt.pontos_total "
                    . ", tt.tipo_documento, tt.flg_assinado "
                    . $this->sqlLeftJoinColuns
                    . $innerJoinEnsalamentoSelect;
                }
            }else{
                $sqlCampos = "ti.*,tt.*, ti.e_mail as email_inscrito, ti.status as status_inscrito, tt.status as status_trabalho "
                . ", toa.cod_area_tematica , toa.ordem , toa.titulo, toa.titulo as titulo_area_tematica, toa.ordem AS ordem_area_tematica, tha.indicar_premiacao "
                . $this->sqlLeftJoinColuns
                . $innerJoinEnsalamentoSelect;
            }           

            $selectslqLeftJoinLogSisUsuario = "";
            $slqLeftJoinLogSisUsuario = "";
            if($naoExecutar){
                $selectslqLeftJoinLogSisUsuario = " , IFNULL(l.data_inclusao, '') AS data_ultimo_acesso_avaliador ";
                $slqLeftJoinLogSisUsuario = " left join (SELECT MAX(l.cod_log_acesso), l.resp_inclusao, l.data_inclusao, l.login FROM sis_log_acesso l WHERE sucesso=1 and cod_evento=".COD_EVENTO." GROUP BY l.login) l ON l.resp_inclusao=tha.cod_avaliador ";
            }

           
            $sql = "SELECT  ". $sqlCampos . $selectslqLeftJoinLogSisUsuario
                    . "FROM tab_trabalhos tt "
                    . " INNER JOIN tab_inscritos ti on ti.cod_inscrito = tt.cod_inscrito "
                    . $this->sqlLeftJoin
                    . $slqLeftJoinLogSisUsuario
                    . $innerJoinEnsalamento
                    . $innerJoinSala
                    . " LEFT JOIN tab_opcoes_areas_tematicas toa on tt.cod_area_tematica=toa.cod_area_tematica "
                    . " WHERE tt.cod_evento=".$obj->getCodEvento()." "
                    . $this->whereSql
                    . $whereSituacao
                    . $whereTituloTrabalho
                    . $whereTituloTextoPalavraTrabalho
                    . $whereFormatoTexto
                    . $whereAreaTematica
                    . $whereTrabalhos
                    . $wherePagamentos
                    . $whereAvaliacao
                    . $whereAvaliador
                    . $whereHistoricoAvaliacao
                    . $wherePremiacao
                    . $wherePremiado
                    . $wherePalavrasChave
                    . $whereCoAutor
                    . $whereConsultaPresenca
                    . $whereConsultaPremiados
                    . $whereConsultaAnexouArqApresentacao
                    . $whereConsultaAnexouArqVideo
                    . $whereSala
                    . $orderBy;
            
            $ips_permitidos = ['200.203.142.25-', '201.66.95.230-', '177.82.0.28','45.230.27.11-'];
            $debug = false;
            if (in_array($_SERVER['REMOTE_ADDR'], $ips_permitidos)) {
//              echo '-'.COD_EVENTO.'-<br>';
                // $debug = true;
                $condicoes['cache']=false;
            }
                                      
            if($debug){
                $this->conex->debug = $debug;
                $rs = $this->conex->Execute($sql);
            }else{
                $this->conex->debug = $debug;
                // if($condicoes['cache']){
                //     $this->conex->cacheSecs = 3600;
                //     $rs = $this->conex->cacheExecute($sql);
                // }else{
                    $rs = $this->conex->Execute($sql);
                // }
            }

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaTrabalhos($return);
                if($condicoes['media']=='xls'){
                    foreach ($rows as $campo=>$value) {
                        if(is_string($campo))
                            $colunas[$i++] = $campo;
                    }
                    $obj->setColunasListaTrabalhos($colunas);
                }
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(Trabalho $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_trabalhos WHERE cod_evento=".$obj->getCodEvento()." and cod_trabalho=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_trabalhos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $sql = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
//            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodTrabalho($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Trabalho $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sqlBk= "INSERT INTO tab_trabalhos_atualizados
            SELECT * FROM tab_trabalhos
            WHERE cod_evento = ".$obj->getCodEvento()." and cod_trabalho = ".$obj->getCodTrabalho();   
            $rsBk = $this->conex->Execute($sqlBk); 
            
            $sql = "SELECT * FROM tab_trabalhos WHERE cod_evento = ".$obj->getCodEvento()." and cod_trabalho = ".$obj->getCodTrabalho(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_trabalhos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $sql = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
//            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    /**
     * Atualiza somente os campos de legibilidade do documento. Update direto
     * sem backup em tab_trabalhos_atualizados — flag de revisao do avaliador,
     * nao altera dados originais do upload.
     */
    public function marcarLegibilidade(Trabalho $obj, $debug=false) {
        try {
            $codTrabalho = (int) $obj->getCodTrabalho();
            $codEvento   = (int) $obj->getCodEvento();
            $legivel     = $obj->getDocumentoLegivel();
            $codUsuario  = (int) ($_SESSION['_cod_usuario'] ?? 0);

            if ($legivel === '' || $legivel === null) {
                $legivelSql = 'NULL';
                $avaliadoEmSql = 'NULL';
                $avaliadoPorSql = 'NULL';
            } else {
                $legivelSql = ((int)$legivel === 0 ? '0' : '1');
                $avaliadoEmSql = "'" . date('Y-m-d H:i:s') . "'";
                $avaliadoPorSql = $codUsuario > 0 ? (string)$codUsuario : 'NULL';
            }

            $obs = (string) $obj->getDocumentoLegivelObs();
            $obsSql = ($obs === '') ? 'NULL' : "'" . addslashes(mb_substr($obs, 0, 255)) . "'";

            $sql = sprintf(
                "UPDATE tab_trabalhos
                    SET documento_legivel = %s,
                        documento_legivel_obs = %s,
                        documento_legivel_avaliado_em = %s,
                        documento_legivel_avaliado_por = %s
                  WHERE cod_trabalho = %d AND cod_evento = %d",
                $legivelSql, $obsSql, $avaliadoEmSql, $avaliadoPorSql,
                $codTrabalho, $codEvento
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getSlugsDocumentosEntregues(Trabalho $obj, $slugs, $debug=false) {
        try {
            $slugsIn = "'" . implode("','", $slugs) . "'";
            $sql = "SELECT DISTINCT tt.slug_tipo_documento FROM tab_trabalhos tt"
                 . " WHERE tt.cod_inscrito=" . intval($obj->getCodInscrito())
                 . " AND tt.cod_evento=" . intval($obj->getCodEvento())
                 . " AND tt.status='ATIVADA'"
                 . " AND tt.slug_tipo_documento IN (" . $slugsIn . ")";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $entregues = [];
            while (!$rs->EOF) {
                $entregues[] = $rs->fields['slug_tipo_documento'];
                $rs->MoveNext();
            }
            return $entregues;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return [];
        }
    }

    public function delete(Trabalho $obj, $debug=false) {
        try {
            $sqlBk= "INSERT INTO tab_trabalhos_excluidos
            SELECT * FROM tab_trabalhos
            WHERE cod_evento = ".$obj->getCodEvento()." and cod_trabalho = ".$obj->getCodTrabalho(); 
            $rsBk = $this->conex->Execute($sqlBk);             
            
            $sql = sprintf('DELETE FROM tab_trabalhos WHERE cod_evento=\'%s\' and cod_trabalho= \'%s\'', $obj->getCodEvento(),$obj->getCodTrabalho());
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
