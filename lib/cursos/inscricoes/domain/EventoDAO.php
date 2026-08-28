<?php
class EventoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getByCodInscrito(Evento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT e.* FROM tab_eventos e 
            INNER JOIN tab_inscritos i on i.cod_evento=e.cod_evento
            WHERE i.cod_inscrito=\'%s\' ', $obj->getCodInscrito());
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
    public function getById(Evento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_eventos WHERE cod_evento=\'%s\' ', $obj->getCodEvento());
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
    public function getByUrlPrefixo(Evento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_eventos WHERE url_prefixo=\'%s\' ', $obj->getUrlPrefixo());
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
            $sql = "SELECT ifnull(MAX(cod_evento),0)+1 FROM tab_eventos";

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
    public function getByIdExiste(Evento $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_evento FROM tab_eventos WHERE cod_evento=\'%s\' ', $obj->getCodEvento());

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
    public function carregarListaEventosByGestor(Evento $obj, $debug=false) {
        try{
            $whereGestor = "gestor='OPCTEC'";
            if($obj->getGestor()!=''){
                $whereGestor = "gestor='". strtolower($obj->getGestor())."'";
            }
            $sql = sprintf('SELECT * FROM tab_eventos WHERE '.$whereGestor.' order by cod_evento desc, titulo');
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
    
    public function carregarListaEventosByIdEvento(Evento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT distinct e.*, i.nome_inscrito, i.cpf, i.e_mail, i.cod_inscrito FROM tab_eventos e '
                    . 'inner join tab_inscritos i on i.cod_evento=e.cod_evento '
                    . 'where e.cod_evento="'.$obj->getCodEvento().'" '
                    . ' and e.status="ATIVADO"  and i.status="ATIVADA" '
                    . ' order by cod_evento desc, titulo');
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
    public function carregarListaEventosByEmailInscrito(Evento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT distinct e.*, i.nome_inscrito, i.cpf, i.e_mail, i.cod_inscrito FROM tab_eventos e '
                    . 'inner join tab_inscritos i on i.cod_evento=e.cod_evento '
                    . 'where i.e_mail="'.$obj->getEmailInscrito().'" '
                    . ' and e.status="ATIVADO"  and i.status="ATIVADA" '
                    . ' order by cod_evento desc, titulo');
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
    public function carregarListaEventosByCpfInscrito(Evento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT distinct e.*, i.nome_inscrito, i.cpf, i.e_mail, i.cod_inscrito FROM tab_eventos e '
                    . 'inner join tab_inscritos i on i.cod_evento=e.cod_evento '
                    . 'where i.cpf="'.$obj->getCpfInscrito().'" '
                    . ' and e.status="ATIVADO"  and i.status="ATIVADA" '
                    . ' order by cod_evento desc, titulo');
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
    public function carregarListaEventosByCpfInscritoApto(Evento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT distinct e.*, i.nome_inscrito, i.cpf, i.email, ii.cod_inscrito FROM tab_eventos e '
                    . ' inner join tab_inscritos_aptos i on i.cod_evento=e.cod_evento '
                    . ' left join tab_inscritos ii on ii.cpf=i.cpf and i.cod_evento=ii.cod_evento and ii.status="ATIVADA" '
                    . ' where i.cpf="'.$obj->getCpfInscrito().'" '
                    . ' and e.status="ATIVADO"  and i.status="ATIVADA" '
                    . ' order by cod_evento desc, titulo');
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
    public function carregarListaEventosByIdLogin(Evento $obj, $debug=false) {
        try{
            if ($obj->getCodUsuario() == '') {
                return false;
            }

            $whereCondicoes = "";
            if($obj->getDataInscricaoIni() and $obj->getDataInscricaoFim()){
                $dataInscricaoIni= $obj->getDataInscricaoIni().' 00:00:00';
                $dataInscricaoFim= $obj->getDataInscricaoFim().' 23:59:59';
                $whereCondicoes .= " and (evc.data_inscricao_ini between '".formatDateInsert($dataInscricaoIni)."' and '".formatDateInsert($dataInscricaoFim)."') ";
            } 
            if($obj->getRealizacaoIni() and $obj->getRealizacaoFim()){
                $realizacaoIni= $obj->getRealizacaoIni().' 00:00:00';
                $realizacaoFim= $obj->getRealizacaoFim().' 23:59:59';
                $whereCondicoes .= " and (evc.realizacao_ini between '".formatDateInsert($realizacaoIni)."' and '".formatDateInsert($realizacaoFim)."') ";
            } 
            if ($obj->getStatus() != '') {
                $whereCondicoes .= " and e.status = '" . $obj->getStatus() . "' ";
            }            
            if($obj->getRecebePagamentoMensalidade()=='1'){
                $whereCondicoes .= " and evc.recebe_pagamento_mensalidade = 1 ";
            }
            if($obj->getRecebePagamentoMensalidade()=='2'){
                $whereCondicoes .= " AND COALESCE(TRIM(evc.recebe_pagamento_mensalidade), '') <> '1' ";
            }
            if($obj->getRecebePagamento()=='1'){
                $whereCondicoes .= " and evc.recebe_pagamento = 1 ";
            }
            if($obj->getRecebePagamento()=='2'){
                $whereCondicoes .= " AND COALESCE(TRIM(evc.recebe_pagamento), '') <> '1' ";
            }
            if($obj->getTrabSubmissao()=='1'){
                $whereCondicoes .= " and evc.trab_submissao = 1 ";
            }
            if($obj->getTrabSubmissao()=='2'){
                $whereCondicoes .= " AND COALESCE(TRIM(evc.trab_submissao), '') <> '1' ";
            }
            if($obj->getCodEvento()!=''){
                $whereCondicoes .= " and e.cod_evento = '".$obj->getCodEvento()."' ";
            }
            if($obj->getGestor()!=''){
                $whereCondicoes .= " and e.gestor = '".$obj->getGestor()."' ";
            }
            if($obj->getProjeto()!=''){
                $whereCondicoes .= " and e.projeto like '%".$obj->getProjeto()."%' ";
            }
            if($obj->getTitulo()!=''){
                /* O campo de titulo tambem procura no PROJETO (codigo interno).
                 * Motivo: quem conhece o evento pelo codigo digita ele na caixa
                 * de titulo, que e a maior e a mais obvia da tela, e recebia
                 * "nenhum resultado" mesmo com o evento existindo. O filtro
                 * proprio de projeto continua acima, para quem quer restringir
                 * so por ele. */
                $whereCondicoes .= " and (e.titulo like '%".$obj->getTitulo()."%'"
                                 . "   or e.projeto like '%".$obj->getTitulo()."%') ";
            }
            $innerJoinInscrito = false;
            if($obj->getNomeInscrito()!=''){
                $innerJoinInscrito = true;
                $whereCondicoes .= " and (ti.nome_inscrito like '%".$obj->getNomeInscrito()."%' or ti.nome_recibo like '%".$obj->getNomeInscrito()."%') ";
            }
            if($obj->getFoneContato()!=''){
                $innerJoinInscrito = true;
                // $whereCondicoes .= " and ti.fone_contato like '%".$obj->getFoneContato()."%' ";
                $whereCondicoes .= " and REPLACE(REPLACE(REPLACE(ti.fone_contato, '-', ''), ' ', ''), '(', '') like '%".trim($obj->getFoneContato())."%' ";
            }
            if($obj->getCodInscrito()!=''){
                $innerJoinInscrito = true;
                $whereCondicoes .= " and ti.cod_inscrito = '".$obj->getCodInscrito()."' ";
            }
            if($obj->getIdPublico()!=''){
                $innerJoinInscrito = true;
                $whereCondicoes .= " and ti.id_publico = '".$obj->getIdPublico()."' ";
            }
            if($obj->getEEmail()!=''){
                $innerJoinInscrito = true;
                $whereCondicoes .= " and ti.e_mail like '%".$obj->getEEmail()."%' ";
            }
            if($obj->getCpf()!=''){
                $innerJoinInscrito = true;
                $whereCondicoes .= " and (ti.cpf like '%".$obj->getCpf()."%' or ti.cpf_recibo =".$obj->getCpf().")";
            }
            $innerJoinTrabalho = "";
            if($obj->getCodTrabalho()!=''){
                $innerJoinInscrito = true;
                $whereCondicoes .= " and t.cod_trabalho = '".$obj->getCodTrabalho()."' ";
                $innerJoinTrabalho = " left JOIN tab_trabalhos t on ti.cod_inscrito=t.cod_inscrito  ";
            }
            $innerJoinPedido = "";
            if($obj->getPedidoId()!=''){
                $innerJoinInscrito = true;
                $whereCondicoes .= " and p.id = '".$obj->getPedidoId()."' ";
                $innerJoinPedido = " left JOIN pedidos p on ti.cod_inscrito=p.inscrito_id  ";
            }
            $innerJoinPagamento = "";
            if($obj->getTransacaoId()!=''){
                $innerJoinInscrito = true;
                $transacao = str_replace('-', '', $obj->getTransacaoId());
                $whereCondicoes .= " and  REPLACE(pg.transacao_id, '-', '') = '".$transacao."' ";
                $innerJoinPagamento = " left JOIN tab_pagamentos pg on ti.cod_inscrito=pg.cod_inscrito  ";
            }
            
            if ($innerJoinInscrito == true){
                $innerJoinInscrito = " LEFT JOIN tab_inscritos ti on ti.cod_evento=e.cod_evento ";
            }
            
            $sql = "SELECT distinct e.* "
                    . ", ( SELECT count(i.cod_inscrito) FROM tab_inscritos i WHERE i.status='ATIVADA' AND i.cod_evento=e.cod_evento GROUP BY i.cod_evento ) as total_inscritos "
                    . ", ( SELECT SUM(op.vagas)           FROM tab_opcoes_perfis op           WHERE op.cod_evento=e.cod_evento  AND op.status='ATIVADA'  GROUP BY op.cod_evento )  AS total_vagas_perfis "
                    . ", ( SELECT count(lip.cod_inscrito) FROM tab_lig_inscritos_perfis lip  "
                    . "      INNER JOIN tab_inscritos i on i.cod_inscrito=lip.cod_inscrito and i.status='ATIVADA' "
                    . "      WHERE lip.cod_evento=e.cod_evento AND lip.status='ATIVADA' GROUP BY lip.cod_evento ) AS total_inscritos_perfis "
                    . ", ( SELECT SUM(oo.numero_vagas)    FROM tab_opcoes_oficinas oo         WHERE oo.cod_evento=e.cod_evento  AND oo.status='ATIVADA'  GROUP BY oo.cod_evento )  AS total_vagas_atividades "
                    . ", ( SELECT count(lio.cod_inscrito) FROM tab_lig_inscritos_oficinas lio "
                    . "      INNER JOIN tab_inscritos i on i.cod_inscrito=lio.cod_inscrito and i.status='ATIVADA' "
                    . "      WHERE lio.cod_evento=e.cod_evento AND lio.status='ATIVADA' GROUP BY lio.cod_evento ) AS total_inscritos_atividades "
                    . " FROM tab_eventos e "
                    . " left join tab_eventos_configuracao evc on e.cod_evento=evc.cod_evento "
                    . " INNER JOIN sis_ligacao_usuarios_eventos lue ON e.cod_evento=lue.cod_evento "
                    . " INNER JOIN sis_usuarios u ON u.cod_usuario=lue.cod_usuario "
                    . " " . $innerJoinInscrito . $innerJoinTrabalho . $innerJoinPedido . $innerJoinPagamento
                    . " WHERE u.cod_usuario=".$obj->getCodUsuario()
                    . $whereCondicoes
                    . " order by e.cod_evento desc ";
                    
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
    public function carregarListaEventos(Evento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_eventos WHERE visibilidade=\'PUBLICO\' order by gestor, cod_evento desc, titulo');
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
    public function getAll(Evento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_eventos order by cod_evento desc');

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
    public function carregarListaEventossByIdEvento(Evento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_eventos WHERE cod_evento=\'%s\' order by cod_evento desc',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEventoss($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(Evento $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setVisibilidade('OCULTO');
            $obj->setStatus('ATIVADO');
            
                        
            $sql = "SELECT * FROM tab_eventos WHERE cod_evento=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_eventos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodEvento($this->conex->Insert_ID());

            $this->carregarPermissoes($obj, $debug);
            
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Evento $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_eventos WHERE cod_evento = ".$obj->getCodEvento(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_eventos");
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
    public function delete(Evento $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_eventos WHERE cod_evento=\'%s\' ', $obj->getCodEvento());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function importarConfDireitoGrupoSistema(Evento $obj, $debug=false) {
        try {
            $sql = sprintf('INSERT INTO `sis_direitos_grupos_sistemas` (cod_grupo, cod_sistema, cod_direito,cod_evento)
            SELECT cod_grupo, cod_sistema, cod_direito, '.$obj->getCodEventoDestino().' FROM `sis_direitos_grupos_sistemas`'
                    . ' WHERE cod_evento='.$obj->getCodEventoOrigem());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }   
    
    public function carregarListaAdminGeral(Evento $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM sis_usuarios WHERE '
            . 'cod_usuario IN (SELECT cod_usuario FROM `sis_ligacao_usuarios_grupos` WHERE cod_grupo=999');
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    
    /**
     * Descobre de qual evento herdar a equipe: o ATIVADO mais recente do MESMO
     * gestor.
     *
     * Por que do mesmo gestor e nao de um codigo fixo: o resto deste metodo usa
     * o evento 91 como molde, e 91 e do HUTEC. Isso e inofensivo enquanto se
     * copia so DIREITO DE GRUPO (a definicao do que cada grupo faz), mas copiar
     * os USUARIOS do 91 colocaria a equipe do HUTEC dentro de evento de AREMAC,
     * CRESSPR, INESCO e demais. Herdando do ultimo evento do proprio gestor a
     * regra vale para todos sem codigo fixo, e a equipe se mantem sozinha.
     *
     * Sem evento anterior (gestor novo, ou gestor em branco), devolve null e o
     * chamador simplesmente nao herda ninguem — o criador continua garantido
     * pelos blocos que ja existiam.
     */
    private function eventoMoldeDoGestor($obj) {
        $gestor = trim((string) $obj->getGestor());
        if ($gestor === '') {
            return null;
        }
        $gestorSql = $this->conex->qstr($gestor);
        $codAtual  = (int) $obj->getCodEvento();

        /* 1) Modelo definido na tela do gestor, se houver.
         *
         * Sem isso o molde era sempre "o evento de codigo mais alto que esteja
         * ATIVADO" -- o ultimo criado, fosse ele real ou um teste. Com 103
         * eventos ativos so no HUTEC, a equipe de todo projeto novo dependia de
         * qual foi o ultimo evento que alguem criou.
         *
         * O evento escolhido ainda precisa existir, estar ATIVADO e ser do
         * mesmo gestor: apagar ou desativar o modelo nao pode deixar o projeto
         * novo sem equipe nenhuma -- nesse caso cai no criterio antigo. */
        $sqlModelo = "SELECT e.cod_evento"
                   . "  FROM sis_gestor g"
                   . "  JOIN tab_eventos e ON e.cod_evento = g.cod_evento_modelo"
                   . " WHERE UPPER(g.nome_gestor) = UPPER(".$gestorSql.")"
                   . "   AND g.cod_evento_modelo IS NOT NULL"
                   . "   AND e.status = 'ATIVADO'"
                   . "   AND UPPER(e.gestor) = UPPER(".$gestorSql.")"
                   . "   AND e.cod_evento <> ".$codAtual
                   . " LIMIT 1";
        $rsModelo = $this->conex->Execute($sqlModelo);
        if ($rsModelo and !$rsModelo->EOF) {
            return (int) $rsModelo->fields[0];
        }

        /* 2) Criterio antigo: o ultimo evento ativo do gestor.
         *
         * Comparacao sem caixa: o cadastro grava 'HUTEC' e a tela as vezes manda
         * 'hutec' (carregarListaEventosByGestor ja faz strtolower por isso). */
        $sql = "SELECT cod_evento FROM tab_eventos"
             . " WHERE UPPER(gestor) = UPPER(".$gestorSql.")"
             . "   AND status = 'ATIVADO'"
             . "   AND cod_evento <> ".$codAtual
             . " ORDER BY cod_evento DESC LIMIT 1";
        $rs = $this->conex->Execute($sql);
        if ($rs and !$rs->EOF) {
            return (int) $rs->fields[0];
        }
        return null;
    }

    public function carregarPermissoes($obj, $debug=false){
        /************ Carregar GRUPOS AOS EVENTOS *****************************/
        $sqlGE = "INSERT INTO sis_direitos_grupos_sistemas (cod_grupo, cod_sistema, cod_direito, cod_evento)
        SELECT cod_grupo, cod_sistema, cod_direito, ".$obj->getCodEvento()." as cod_evento FROM `sis_direitos_grupos_sistemas` WHERE cod_evento=91";
        $rsGE = $this->conex->Execute($sqlGE);


        /************ LIGAR USUARIO Responsável pela AÇÃO AOS GRUPOS padrões do gestor ****************************/
        /* Copia os grupos que o próprio usuário tem no evento base (91).
           Se não tiver grupos no 91, copia do usuário 665 (Hutec) como fallback */
        $codUsuarioCriador = $obj->getResIncl();
        $sqlVerifica = "SELECT COUNT(*) as total FROM sis_ligacao_usuarios_grupos WHERE cod_usuario=" . $codUsuarioCriador . " AND cod_evento=91";
        $rsVerifica = $this->conex->Execute($sqlVerifica);
        $usuarioBase = ($rsVerifica && !$rsVerifica->EOF && $rsVerifica->fields['total'] > 0) ? $codUsuarioCriador : 665;

        $sqlUG = "INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento)
        SELECT ".$codUsuarioCriador." AS cod_usuario, cod_grupo, ".$obj->getCodEvento()." as cod_evento FROM `sis_ligacao_usuarios_grupos` WHERE cod_usuario=".$usuarioBase." AND cod_evento=91";
        $rsUG = $this->conex->Execute($sqlUG);

        /************ QUEM CRIA ADMINISTRA O QUE CRIOU ***************************
         * Os grupos herdados acima sao operacionais (inscritos, financeiro, documentos,
         * mensagens) e NENHUM deles concede SIS_GERENCIAR_EVENTO. Sem esta linha o
         * criador via o evento na lista e tomava acesso-negado ao abrir para configurar
         * -- o "evento some" relatado em 2026-08-04.
         *
         * So vale para o evento recem-criado: nos outros ele continua com o que tinha.
         * O direito "grupo administradores -> Administracao do Sistema" ja veio no molde
         * copiado no inicio deste metodo, entao basta a ligacao do usuario ao grupo.
         */
        $sqlAdminEvento = "INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento)
        SELECT ".$codUsuarioCriador.", ".enumGrupo::GR_ADMINISTRADORES.", ".$obj->getCodEvento()." FROM DUAL
         WHERE NOT EXISTS (SELECT 1 FROM sis_ligacao_usuarios_grupos x
                            WHERE x.cod_usuario = ".$codUsuarioCriador."
                              AND x.cod_grupo   = ".enumGrupo::GR_ADMINISTRADORES."
                              AND x.cod_evento  = ".$obj->getCodEvento().")";
        $this->conex->Execute($sqlAdminEvento);

        /************ LIGAR USUARIO Responsável pela AÇÃO AO EVENTO *****************************/
        $sqlUE = "INSERT INTO sis_ligacao_usuarios_eventos(cod_evento, cod_usuario, status)values(".$obj->getCodEvento().",".$obj->getResIncl().",'ATIVADA')";
        $rsUE = $this->conex->Execute($sqlUE);

        /************ LIGAR USUARIO CORINGA PADRA AO EVENTO *****************************/
        if($obj->getResIncl()!=668){
            /* @todo usuário 668 Opctec, aqui talvez seja necessário inserir
            a busca por usuários administradores da opctec
            aqui estamos forçando a ligação do usuário coringa 668 ao evento
             */
            $coringa = 668; # adicionando usuario coringa
            $sqlUG = "INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento)
            SELECT ".$coringa." AS cod_usuario, cod_grupo, ".$obj->getCodEvento()." as cod_evento FROM `sis_ligacao_usuarios_grupos` WHERE cod_usuario=668 AND cod_evento=91";
            $rsUG = $this->conex->Execute($sqlUG);
            $sqlUE = "INSERT INTO sis_ligacao_usuarios_eventos(cod_evento, cod_usuario, status)values(".$obj->getCodEvento().",".$coringa.",'ATIVADA')";
            $rsUE = $this->conex->Execute($sqlUE);
        }
        /************ LIGAR USUARIO CORINGA PADRA AO EVENTO *****************************/
        if($obj->getResIncl()==668){
            $coringa = 665; # adicionando usuario coringa
            $sqlUG = "INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento)
            SELECT ".$coringa." AS cod_usuario, cod_grupo, ".$obj->getCodEvento()." as cod_evento FROM `sis_ligacao_usuarios_grupos` WHERE cod_usuario=665 AND cod_evento=91";
            $rsUG = $this->conex->Execute($sqlUG);
            $sqlUE = "INSERT INTO sis_ligacao_usuarios_eventos(cod_evento, cod_usuario, status)values(".$obj->getCodEvento().",".$coringa.",'ATIVADA')";
            $rsUE = $this->conex->Execute($sqlUE);
        }

        $this->herdarEquipeDoGestor($obj, $debug);
    }

    /**
     * Da ao evento recem-criado a MESMA equipe do ultimo evento do gestor.
     *
     * Antes daqui, um evento novo nascia visivel para duas pessoas: o criador e
     * o coringa 668. A lista de projetos so mostra quem tem linha em
     * sis_ligacao_usuarios_eventos, entao o resto da equipe simplesmente nao
     * via o evento e precisava ser adicionado a mao, um por um. Era por isso
     * que os eventos antigos tinham 10, 18, 28 usuarios e os recentes, 2.
     *
     * Dois cuidados:
     *
     * 1. GRUPOS ANTES DO VINCULO COM O EVENTO. So ligar a pessoa ao evento a faz
     *    ver o item na lista e tomar "acesso negado" ao abrir — o mesmo sintoma
     *    que o bloco do GR_ADMINISTRADORES acima corrigiu para o criador.
     *
     * 2. Tudo com NOT EXISTS. Este metodo roda depois dos blocos acima, que ja
     *    inseriram criador e coringa; sem a guarda, quem estivesse nos dois
     *    lados ganharia linha repetida.
     */
    private function herdarEquipeDoGestor($obj, $debug=false) {
        $molde = $this->eventoMoldeDoGestor($obj);
        if (!$molde) {
            return; /* primeiro evento do gestor: nao ha equipe a herdar */
        }
        $codEvento = (int) $obj->getCodEvento();

        $sqlGrupos = "INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento)
            SELECT m.cod_usuario, m.cod_grupo, ".$codEvento."
              FROM sis_ligacao_usuarios_grupos m
             WHERE m.cod_evento = ".$molde."
               AND NOT EXISTS (SELECT 1 FROM sis_ligacao_usuarios_grupos x
                                WHERE x.cod_evento  = ".$codEvento."
                                  AND x.cod_usuario = m.cod_usuario
                                  AND x.cod_grupo   = m.cod_grupo)";
        $this->conex->debug = $debug;
        $this->conex->Execute($sqlGrupos);

        $sqlEvento = "INSERT INTO sis_ligacao_usuarios_eventos (cod_evento, cod_usuario, status)
            SELECT ".$codEvento.", m.cod_usuario, 'ATIVADA'
              FROM sis_ligacao_usuarios_eventos m
             WHERE m.cod_evento = ".$molde."
               AND NOT EXISTS (SELECT 1 FROM sis_ligacao_usuarios_eventos x
                                WHERE x.cod_evento  = ".$codEvento."
                                  AND x.cod_usuario = m.cod_usuario)";
        $this->conex->Execute($sqlEvento);
    }

    public function clonarDireitosUsuario($codFonte, $codDestino, $evtFonte, $evtDestino)
    {
        // 1) Clonar direitos de grupos do evento
        $sql = "
            INSERT INTO sis_direitos_grupos_sistemas (cod_grupo, cod_sistema, cod_direito, cod_evento)
            SELECT cod_grupo, cod_sistema, cod_direito, ?
            FROM sis_direitos_grupos_sistemas
            WHERE cod_evento = ?
              AND NOT EXISTS (
                SELECT 1
                FROM sis_direitos_grupos_sistemas x
                WHERE x.cod_evento = ? 
                  AND x.cod_grupo  = sis_direitos_grupos_sistemas.cod_grupo
              )
        ";
        $this->conex->Execute($sql, [$evtDestino, $evtFonte, $evtDestino]);
    
        // 2) Clonar vínculo usuário→grupos e usuário→evento (inclui fallback)
        $fallback = ($codDestino != 668 ? 668 : 665);
        foreach ([$codDestino, $fallback] as $usr) {
            // 2.1) Grupos
            $sql = "
                INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento)
                SELECT ?, cod_grupo, ?
                FROM sis_ligacao_usuarios_grupos
                WHERE cod_usuario = ? AND cod_evento = ?
                  AND NOT EXISTS (
                    SELECT 1
                    FROM sis_ligacao_usuarios_grupos y
                    WHERE y.cod_usuario = ? 
                      AND y.cod_grupo   = sis_ligacao_usuarios_grupos.cod_grupo
                      AND y.cod_evento  = ?
                  )
            ";
            $this->conex->Execute($sql, [
                $usr, $evtDestino,    // destino
                $codFonte, $evtFonte, // origem
                $usr, $evtDestino     // checagem de duplicado
            ]);
    
            // 2.2) Evento
            $sql = "
                INSERT INTO sis_ligacao_usuarios_eventos (cod_evento, cod_usuario, status)
                SELECT ?, ?, 'ATIVADA' FROM DUAL
                WHERE NOT EXISTS (
                  SELECT 1
                  FROM sis_ligacao_usuarios_eventos z
                  WHERE z.cod_evento  = ? 
                    AND z.cod_usuario = ?
                )
            ";
            $this->conex->Execute($sql, [
                $evtDestino, $usr,   // destino
                $evtDestino, $usr    // checagem de duplicado
            ]);
        }
    }
    
    /* * Atualizar os direitos de grupos do evento 80 para o evento a ser atualizado, 
     * @return void
     */
    public function atualizarModulosDireitosGruposEvento($evtFonte, $evtDestino){
        $evtOrigem = 80;
        $sql = "INSERT INTO sis_direitos_grupos_sistemas (cod_grupo, cod_sistema, cod_direito, cod_evento)
        SELECT cod_grupo, cod_sistema, cod_direito, ".$evtDestino."
        FROM sis_direitos_grupos_sistemas
        WHERE cod_evento = ".$evtOrigem."
            AND NOT EXISTS (
            SELECT 1
            FROM sis_direitos_grupos_sistemas X
            WHERE x.cod_evento = ".$evtDestino."
                AND x.cod_grupo  = sis_direitos_grupos_sistemas.cod_grupo
            )";
        $this->conex->Execute($sql);
    }
}