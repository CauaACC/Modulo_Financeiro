<?php
class AvaliacaoInscritoDAO extends DAO{

    public function __construct() { 
        $this->tableName = "tab_trabalhos_avaliacao_inscricao";
        parent::__construct();
    }  
    public function getById(AvaliacaoInscrito $obj, $debug=false) {
        try{

//            Estava dando este erro  ⬇
            //{ "statusMessage":"", "message": "Não foi possível executar o comando desejado.
            //ADODB_Exception.Unknown column \'tiac.cod_inscrito\' in \'on clause\'", "codAvaliacao":"" }

            //$sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_trabalhos_avaliacao_inscricao tis
            //LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            //WHERE tis.cod_evento=\'%s\' and tis.cod_avaliacao= \'%s\'', $obj->getCodEvento(),$obj->getCodAvaliacao());

            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao, ti.nome_inscrito FROM tab_trabalhos_avaliacao_inscricao tis
            LEFT JOIN tab_inscritos ti ON tis.cod_inscrito = ti.cod_inscrito AND ti.status = \'ATIVADA\' AND ti.cod_evento = tis.cod_evento
            WHERE tis.cod_evento = \'%s\' and tis.cod_avaliacao = \'%s\'', $obj->getCodEvento(), $obj->getCodAvaliacao());

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
    public function getByIdInscrito(AvaliacaoInscrito $obj, $debug=false) {
        try{

//            Estava dando este erro  ⬇
            //{ "statusMessage":"", "message": "Não foi possível executar o comando desejado.
            //ADODB_Exception.Unknown column \'tiac.cod_inscrito\' in \'on clause\'", "codAvaliacao":"" }

            //$sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_trabalhos_avaliacao_inscricao tis
            //LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            //WHERE tis.cod_evento=\'%s\' and tis.cod_avaliacao= \'%s\'', $obj->getCodEvento(),$obj->getCodAvaliacao());

            $filtrarTipoArquivo = " and tt.tipo_documento is null ";
            $filtrarTipoArquivo = "";

            // Filtro opcional por ordem de etapa (Fase 1, Fase 2 etc.).
            // Quando $obj->getOrdemEtapa() está setado, restringe o JOIN de tab_trabalhos_avaliacao_inscricao
            // à etapa indicada via tab_avaliacao_etapa.ordem_etapa. Sem set, comportamento legado.
            $ordemEtapa = $obj->getOrdemEtapa();
            $joinEtapa = '';
            $whereEtapa = '';
            if ($ordemEtapa) {
                $joinEtapa = ' LEFT JOIN tab_avaliacao_etapa tae ON tae.cod_etapa = tis.cod_etapa AND tae.cod_evento = tis.cod_evento ';
                $whereEtapa = sprintf(' AND (tae.ordem_etapa = \'%s\' OR tis.cod_avaliacao IS NULL) ', (int)$ordemEtapa);
            }

            $sql = sprintf('SELECT COALESCE(tis.cod_inscrito, ti.cod_inscrito) AS cod_inscricao, ti.nome_inscrito, ti.nis, tis.*, tt.pontos_total AS pontos_informados_trabalho, ti.institucional, top.cod_perfil, top.titulo as titulo_perfil
                , tt.arquivo_gerado, tt.criterios_respostas, tt.criterios_pesos, tt.criterios_pontos, tt.pontos_total, tt.resumo_trabalho
                FROM tab_inscritos ti
                LEFT JOIN tab_trabalhos_avaliacao_inscricao tis ON tis.cod_inscrito = ti.cod_inscrito AND ti.cod_evento = tis.cod_evento
                '.$joinEtapa.'
                LEFT JOIN tab_trabalhos tt ON tt.cod_inscrito = ti.cod_inscrito '.$filtrarTipoArquivo.' AND ti.cod_evento = tt.cod_evento
                LEFT JOIN tab_opcoes_perfis top ON top.cod_perfil=ti.cod_perfil AND ti.cod_evento = top.cod_evento
                WHERE ti.cod_evento = \'%s\' and ti.cod_inscrito = \'%s\' '.$whereEtapa, $obj->getCodEvento(), $obj->getCodInscrito());

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
    public function getListaRecursosSolicitadosByIdInscrito(AvaliacaoInscrito $obj, $debug=false) {
        try{
            $sql = sprintf("select t.cod_inscrito, t.cod_trabalho, t.recurso_trabalho, t.recurso_trabalho_solicitar, t.arquivo_gerado
             from tab_trabalhos t where t.recurso_trabalho_solicitar=1 and t.cod_evento=%s and t.cod_inscrito=%s", $obj->getCodEvento(), $obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRecursosSolicitados($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaDocumentosByIdInscrito(AvaliacaoInscrito $obj, $debug=false) {
        try{
            $sql = sprintf("select t.cod_inscrito, t.cod_trabalho, t.recurso_trabalho, t.recurso_trabalho_solicitar, t.arquivo_gerado, t.tipo_documento, t.slug_tipo_documento
             , t.documento_legivel, t.documento_legivel_obs, t.documento_legivel_avaliado_em, t.documento_legivel_avaliado_por
             from tab_trabalhos t where t.status='ATIVADA' and t.cod_evento=%s and t.cod_inscrito=%s
             and (slug_tipo_documento<>'recurso' and slug_tipo_documento<>'criterios_pontuacao')
            ORDER BY CASE t.slug_tipo_documento
                WHEN 'documento_com_foto'        THEN 1
                WHEN 'cpf'                       THEN 2
                WHEN 'titulo_eleitoral'          THEN 3
                WHEN 'comprovante_qualificacao'  THEN 4
                WHEN 'comprovante_endereco'      THEN 5
                WHEN 'comprovacao_tempo_servico' THEN 6
                WHEN 'curriculo'                 THEN 7
                WHEN 'criterios_pontuacao'       THEN 8
                WHEN 'recurso'                   THEN 9
                ELSE 99
            END
             ", $obj->getCodEvento(), $obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaDocumentos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getLastId($condicao=false, $debug=false) {
        try {
            $sql = "SELECT ifnull(MAX(cod_avaliacao),0)+1 FROM tab_trabalhos_avaliacao_inscricao";

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
    public function getByIdExiste(AvaliacaoInscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito, cod_avaliacao FROM '.$this->tableName.' '
            . 'WHERE cod_evento=\'%s\' AND cod_avaliacao= \'%s\' '
            , $obj->getCodEvento(),$obj->getCodAvaliacao());
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
    public function getAll(AvaliacaoInscrito $obj, $debug=false) {
        try{
//            $sql = sprintf('SELECT tis.*, ti.cod_inscrito as cod_inscricao FROM tab_trabalhos_avaliacao_inscricao tis
//            LEFT JOIN tab_inscritos ti on tis.cpf=ti.cpf
//            order by tis.nome_desconto desc');
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito as cod_inscricao, ti.nome_inscrito 
                FROM tab_trabalhos_avaliacao_inscricao tiac 
                LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            order by nome_inscrito desc ');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliacaoInscritos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
   public function carregarListaAvaliacaoInscritoByIdEvento(AvaliacaoInscrito $obj, $condicoes=false, $debug=false) {
        try{
            $ordenar = is_array($condicoes) && isset($condicoes['ordenar']) ? $condicoes['ordenar'] : '';
            $filtro  = is_array($condicoes) && isset($condicoes['filtro']) ? $condicoes['filtro'] : 'todos';
            $ordemEtapa = is_array($condicoes) && isset($condicoes['ordemEtapa']) ? (int)$condicoes['ordemEtapa'] : null;

            switch ($ordenar) {
                case 'nome_inscrito':
                    $orderBy = " order by tiac.nome_inscrito ";
                    break;
                default:
                    $orderBy = " order by tiac.cod_avaliacao desc ";
                    break;
            }
            $whereExtra = "";
            if($obj->getCodInscrito()){
                $whereExtra .= " and tiac.cod_inscrito = ".$obj->getCodInscrito()." ";
            }

            // Filtro opcional por etapa (ordem_etapa = 1 → Fase 1, 2 → Fase 2 etc.)
            $joinEtapa = '';
            if ($ordemEtapa) {
                $joinEtapa = ' INNER JOIN tab_avaliacao_etapa tae ON tae.cod_etapa = tiac.cod_etapa AND tae.cod_evento = tiac.cod_evento ';
                $whereExtra .= sprintf(" AND tae.ordem_etapa = '%d' ", $ordemEtapa);
            }

            // Filtro por tipo
            switch ($filtro) {
                case 'com_recurso':
                    $whereExtra .= " and sr.total_recursos > 0 ";
                    break;
                case 'habilitados':
                    $whereExtra .= " and tiac.classificacao_status = 'H' ";
                    break;
                case 'nao_habilitados':
                    $whereExtra .= " and tiac.classificacao_status = 'I' ";
                    break;
                case 'nao_avaliados':
                    $whereExtra .= " and (tiac.classificacao_status = '' OR tiac.classificacao_status IS NULL) ";
                    break;
                // Recursos da 1ª fase (evento 129 e similares): texto em tiac.recurso_texto
                case 'recurso_fase_1_pendente':
                    $whereExtra .= " AND tiac.recurso_texto IS NOT NULL AND tiac.recurso_texto <> '' ";
                    $whereExtra .= " AND (tiac.classificacao_status_apos_recurso IS NULL OR tiac.classificacao_status_apos_recurso = '') ";
                    break;
                case 'recurso_fase_1_avaliado':
                    $whereExtra .= " AND tiac.recurso_texto IS NOT NULL AND tiac.recurso_texto <> '' ";
                    $whereExtra .= " AND tiac.classificacao_status_apos_recurso IN ('H','I') ";
                    break;
                case 'recurso_fase_1_qualquer':
                    $whereExtra .= " AND tiac.recurso_texto IS NOT NULL AND tiac.recurso_texto <> '' ";
                    break;
            }

            // total_recursos: une o esquema antigo (tab_trabalhos.recurso_trabalho_solicitar) com o novo
            // (tab_trabalhos_avaliacao_inscricao.recurso_texto). Eventos antigos contam só do primeiro;
            // 129 e seguintes contam do segundo. Eventos mistos somam os dois.
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito, ti.nome_inscrito, ti.cpf ,tiac.sit_pontos as pontos_informados
                , tt.arquivo_gerado, tt.criterios_respostas, tt.criterios_pesos, tt.criterios_pontos, tt.pontos_total
                , sr.total_recursos
            FROM tab_trabalhos_avaliacao_inscricao as tiac
            LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            '.$joinEtapa.'
            LEFT JOIN tab_trabalhos tt ON tt.cod_inscrito = ti.cod_inscrito AND ti.cod_evento = tt.cod_evento
            left join (
                SELECT cod_inscrito, COUNT(*) AS total_recursos FROM (
                    SELECT cod_inscrito FROM tab_trabalhos
                        WHERE cod_evento='.$obj->getCodEvento().' AND recurso_trabalho_solicitar=1 AND status=\'ATIVADA\'
                    UNION ALL
                    SELECT cod_inscrito FROM tab_trabalhos_avaliacao_inscricao
                        WHERE cod_evento='.$obj->getCodEvento().' AND recurso_texto IS NOT NULL AND recurso_texto <> \'\' AND status=\'ATIVADA\'
                ) u GROUP BY cod_inscrito
            ) as sr on sr.cod_inscrito=ti.cod_inscrito
            WHERE tiac.cod_evento=\'%s\' '.$whereExtra.' '.$orderBy.' ',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliacaoInscritos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Lista os inscritos elegiveis para a Fase 2 (Anexo II) do evento.
     *
     * Elegivel = inscrito habilitado na Fase 1 final
     * (classificacao_status_final = 'H' na linha de ordem_etapa = 1).
     *
     * Faz LEFT JOIN com a Fase 2 (ordem_etapa=2) para mostrar quem ja foi
     * avaliado e quem esta pendente:
     *   - tia2.cod_avaliacao IS NULL  -> Pendente avaliacao
     *   - tia2.cod_avaliacao IS NOT NULL e tia2.classificacao_status preenchido -> Avaliado
     *
     * Filtros via $condicoes['filtro']:
     *   - 'pendente'   -> tia2.cod_avaliacao IS NULL
     *   - 'avaliado'   -> tia2.cod_avaliacao IS NOT NULL
     *   - 'todos'      -> sem filtro adicional
     */
    public function carregarListaInscritosElegiveisFase2(AvaliacaoInscrito $obj, $condicoes=false, $debug=false) {
        try {
            $filtro = is_array($condicoes) && isset($condicoes['filtro']) ? $condicoes['filtro'] : 'todos';

            $whereFiltro = '';
            switch ($filtro) {
                case 'pendente':
                    $whereFiltro = ' AND tia2.cod_avaliacao IS NULL ';
                    break;
                case 'avaliado':
                    $whereFiltro = ' AND tia2.cod_avaliacao IS NOT NULL ';
                    break;
                case 'habilitados':
                    $whereFiltro = " AND tia2.classificacao_status = 'H' ";
                    break;
                case 'nao_habilitados':
                    $whereFiltro = " AND tia2.classificacao_status = 'I' ";
                    break;
                case 'com_recurso':
                    $whereFiltro = " AND tia2.recurso_texto IS NOT NULL AND tia2.recurso_texto <> '' ";
                    break;
                case 'recurso_pendente':
                    // Inscrito enviou recurso mas ainda nao foi analisado
                    $whereFiltro = " AND tia2.recurso_texto IS NOT NULL AND tia2.recurso_texto <> '' "
                                 . " AND (tia2.classificacao_status_apos_recurso IS NULL OR tia2.classificacao_status_apos_recurso = '') ";
                    break;
                case 'recurso_avaliado':
                    // Inscrito enviou recurso E ja foi analisado
                    $whereFiltro = " AND tia2.recurso_texto IS NOT NULL AND tia2.recurso_texto <> '' "
                                 . " AND tia2.classificacao_status_apos_recurso IN ('H','I') ";
                    break;
                case 'com_docs_ilegiveis':
                    $whereFiltro = " AND EXISTS (
                        SELECT 1 FROM tab_trabalhos tdoc_f
                         WHERE tdoc_f.cod_inscrito = i.cod_inscrito
                           AND tdoc_f.cod_evento   = i.cod_evento
                           AND tdoc_f.status       = 'ATIVADA'
                           AND tdoc_f.documento_legivel = 0
                    ) ";
                    break;
            }

            $sql = sprintf("
                SELECT
                    i.cod_inscrito,
                    i.id_publico,
                    i.nome_inscrito,
                    i.cpf,
                    i.e_mail,
                    tia1.classificacao_status_final AS status_fase1_final,
                    tia2.cod_avaliacao              AS cod_avaliacao_fase2,
                    tia2.pontos_informados_trabalho AS pontos_informados_fase2,
                    tia2.pontos_total_confirmado    AS pontos_fase2,
                    tia2.classificacao_status       AS classif_status_fase2,
                    tia2.recurso_texto              AS recurso_texto_fase2,
                    tia2.pontos_total_apos_recurso  AS pontos_apos_recurso_fase2,
                    tia2.classificacao_status_apos_recurso AS classif_apos_recurso_fase2,
                    tia2.parecer_avaliador          AS parecer_avaliador_fase2,
                    tia2.avaliacao_json             AS avaliacao_json_fase2,
                    (SELECT COUNT(*) FROM tab_trabalhos tdoc
                       WHERE tdoc.cod_inscrito = i.cod_inscrito
                         AND tdoc.cod_evento   = i.cod_evento
                         AND tdoc.status       = 'ATIVADA'
                         AND tdoc.slug_tipo_documento NOT IN ('recurso','criterios_pontuacao')
                    ) AS qtd_docs_total,
                    (SELECT COUNT(*) FROM tab_trabalhos tdoc
                       WHERE tdoc.cod_inscrito = i.cod_inscrito
                         AND tdoc.cod_evento   = i.cod_evento
                         AND tdoc.status       = 'ATIVADA'
                         AND tdoc.slug_tipo_documento NOT IN ('recurso','criterios_pontuacao')
                         AND tdoc.documento_legivel = 0
                    ) AS qtd_docs_ilegiveis
                FROM tab_inscritos i
                JOIN tab_trabalhos_avaliacao_inscricao tia1
                    ON tia1.cod_inscrito = i.cod_inscrito
                   AND tia1.cod_evento   = i.cod_evento
                   AND tia1.status       = 'ATIVADA'
                JOIN tab_avaliacao_etapa tae1
                    ON tae1.cod_etapa  = tia1.cod_etapa
                   AND tae1.cod_evento = tia1.cod_evento
                   AND tae1.ordem_etapa = 1
                LEFT JOIN tab_trabalhos_avaliacao_inscricao tia2
                    ON tia2.cod_inscrito = i.cod_inscrito
                   AND tia2.cod_evento   = i.cod_evento
                   AND tia2.status       = 'ATIVADA'
                   AND tia2.cod_etapa IN (
                        SELECT cod_etapa FROM tab_avaliacao_etapa
                         WHERE cod_evento = '%s' AND ordem_etapa = 2 AND status = 'ATIVADA'
                   )
                WHERE i.cod_evento = '%s'
                  AND i.status     = 'ATIVADA'
                  AND COALESCE(
                        NULLIF(tia1.classificacao_status_final, ''),
                        NULLIF(tia1.classificacao_status_apos_recurso, ''),
                        tia1.classificacao_status
                      ) = 'H'
                  %s
                ORDER BY i.nome_inscrito
            ", $obj->getCodEvento(), $obj->getCodEvento(), $whereFiltro);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                $obj->setListaAvaliacaoInscritos(array());
                return false;
            }
            $return = array();
            foreach ($rs as $rows) {
                $obj->setAll($rows);
                $return[] = clone $obj;
            }
            $obj->setListaAvaliacaoInscritos($return);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdAvaliador(AvaliacaoInscrito $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM '.$this->tableName.' WHERE cod_evento=\'%s\' '
            . 'and cod_inscrito= \'%s\' and cod_avaliador= \'%s\' and status=\'ATIVADA\' ',
            $obj->getCodEvento(),$obj->getCodAvaliador());

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
    
    public function carregarListaAvaliacaoInscritoByIdInscrito(AvaliacaoInscrito $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT thi.* FROM '.$this->tableName.' thi 
            WHERE thi.cod_evento=\'%s\' and thi.cod_inscrito= \'%s\' and status=\'ATIVADA\' ',
            $obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliacaoInscrito($return);
                return true; 
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    /**
     * Salva (insere ou atualiza) o texto do recurso na etapa 1 (ordem_etapa=1)
     * de um inscrito. Marca recurso_status='AGUARDANDO' e data_alteracao=NOW.
     * Retorna true em sucesso, false caso o registro da etapa não exista.
     */
    public function salvarRecursoFase1ByIdInscrito(AvaliacaoInscrito $obj, $debug=false) {
        try {
            // Localiza o cod_avaliacao da etapa 1 do inscrito
            $sqlBusca = sprintf("SELECT a.cod_avaliacao
                FROM %s a
                JOIN tab_avaliacao_etapa e
                       ON e.cod_etapa  = a.cod_etapa
                      AND e.cod_evento = a.cod_evento
                WHERE a.cod_evento   = '%s'
                  AND a.cod_inscrito = '%s'
                  AND a.status       = 'ATIVADA'
                  AND e.ordem_etapa  = 1
                LIMIT 1",
                $this->tableName, $obj->getCodEvento(), $obj->getCodInscrito());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sqlBusca);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            $codAvaliacao = $rs->fields['cod_avaliacao'];

            $textoEscapado = addslashes($obj->getRecursoTexto());
            $idAlteracao   = (string) ($_SESSION['_cod_inscrito'] ?? 'inscrito');
            $ipAlteracao   = $_SERVER['REMOTE_ADDR'] ?? '';
            $sessionId     = session_id();
            $userAgent     = addslashes($_SERVER['HTTP_USER_AGENT'] ?? '');

            $sqlUpdate = sprintf("UPDATE %s
                SET recurso_texto             = '%s',
                    recurso_status            = 'AGUARDANDO',
                    data_alteracao            = '%s',
                    ip_alteracao              = '%s',
                    id_alteracao              = '%s',
                    http_user_agent_alteracao = '%s'
                WHERE cod_avaliacao = %d
                LIMIT 1",
                $this->tableName,
                $textoEscapado,
                date('Y-m-d H:i:s'),
                $ipAlteracao,
                $idAlteracao,
                $userAgent,
                (int) $codAvaliacao);

            $this->conex->Execute($sqlUpdate);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Salva o texto do recurso da fase 2 (ordem_etapa=2) do inscrito.
     * Mesmo padrao do salvarRecursoFase1ByIdInscrito. Aceita DEFERIDO e
     * INDEFERIDO (deferido pode contestar nota pedindo aumento).
     */
    public function salvarRecursoFase2ByIdInscrito(AvaliacaoInscrito $obj, $debug=false) {
        try {
            $sqlBusca = sprintf("SELECT a.cod_avaliacao
                FROM %s a
                JOIN tab_avaliacao_etapa e
                       ON e.cod_etapa  = a.cod_etapa
                      AND e.cod_evento = a.cod_evento
                WHERE a.cod_evento   = '%s'
                  AND a.cod_inscrito = '%s'
                  AND a.status       = 'ATIVADA'
                  AND e.ordem_etapa  = 2
                LIMIT 1",
                $this->tableName, $obj->getCodEvento(), $obj->getCodInscrito());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sqlBusca);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            $codAvaliacao = $rs->fields['cod_avaliacao'];

            $textoEscapado = addslashes($obj->getRecursoTexto());
            $idAlteracao   = (string) ($_SESSION['_cod_inscrito'] ?? 'inscrito');
            $ipAlteracao   = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent     = addslashes($_SERVER['HTTP_USER_AGENT'] ?? '');

            $sqlUpdate = sprintf("UPDATE %s
                SET recurso_texto             = '%s',
                    recurso_status            = 'AGUARDANDO',
                    data_alteracao            = '%s',
                    ip_alteracao              = '%s',
                    id_alteracao              = '%s',
                    http_user_agent_alteracao = '%s'
                WHERE cod_avaliacao = %d
                LIMIT 1",
                $this->tableName,
                $textoEscapado,
                date('Y-m-d H:i:s'),
                $ipAlteracao,
                $idAlteracao,
                $userAgent,
                (int) $codAvaliacao);

            $this->conex->Execute($sqlUpdate);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Carrega o registro da etapa 1 (primeira etapa do evento, ordem_etapa=1)
     * para um inscrito. JOIN com tab_avaliacao_etapa torna o filtro portável
     * entre eventos sem hardcodar cod_etapa global.
     */
    public function carregarAvaliacaoFase1ByIdInscrito(AvaliacaoInscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT a.*
                FROM %s a
                JOIN tab_avaliacao_etapa e
                       ON e.cod_etapa  = a.cod_etapa
                      AND e.cod_evento = a.cod_evento
                WHERE a.cod_evento   = \'%s\'
                  AND a.cod_inscrito = \'%s\'
                  AND a.status       = \'ATIVADA\'
                  AND e.ordem_etapa  = 1
                LIMIT 1',
                $this->tableName, $obj->getCodEvento(), $obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            foreach ($rs as $rows) {}
            $obj->setAll($rows);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Carrega avaliacao da fase 2 (ordem_etapa=2) do inscrito.
     * Usado pra exibir o bloco DEFERIDO/INDEFERIDO + nota na area do inscrito.
     */
    public function carregarAvaliacaoFase2ByIdInscrito(AvaliacaoInscrito $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT a.*
                FROM %s a
                JOIN tab_avaliacao_etapa e
                       ON e.cod_etapa  = a.cod_etapa
                      AND e.cod_evento = a.cod_evento
                WHERE a.cod_evento   = \'%s\'
                  AND a.cod_inscrito = \'%s\'
                  AND a.status       = \'ATIVADA\'
                  AND e.ordem_etapa  = 2
                LIMIT 1',
                $this->tableName, $obj->getCodEvento(), $obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            foreach ($rs as $rows) {}
            $obj->setAll($rows);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaAvaliacaoInscritoByIdInscritoProRedeFase1(AvaliacaoInscrito $obj, $debug=false) {
        try{
            
            $sql = "SELECT thi.*
             FROM vw_inscritos_docs_status thi 
            WHERE thi.cod_evento=".$obj->getCodEvento()." and thi.cod_inscrito= '".$obj->getCodInscrito()."' and status='ATIVADA' ";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliacaoInscrito($return);
                return true; 
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaAvaliacaoInscritoByIdInscritoProRedeFase2(AvaliacaoInscrito $obj, $debug=false) {
        try{
            
            $sql = "SELECT thi.*, a.*
             FROM vw_inscritos_docs_status thi 
             LEFT JOIN tab_trabalhos_avaliacao_inscricao a ON thi.cod_inscrito = a.cod_inscrito
            WHERE thi.cod_evento=".$obj->getCodEvento()." and thi.cod_inscrito= '".$obj->getCodInscrito()."' and thi.status='ATIVADA' ";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliacaoInscrito($return);
                return true; 
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getAvaliacaoRecursoProRedeFase1(AvaliacaoInscrito $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_trabalhos_avaliacao_inscricao WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' and status=\'ATIVADA\' ',
            $obj->getCodEvento(),$obj->getCodInscrito());

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

    public function getUltimaAvaliacaoByIdTrabalho(AvaliacaoInscrito $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT thi.* FROM '.$this->tableName.' thi 
            INNER JOIN vw_ultima_avaliacao_premiacao_por_autor AS maxthi ON thi.cod_avaliacao=maxthi.cod_avaliacao
            WHERE thi.cod_evento=\'%s\' and thi.cod_inscrito= \'%s\' and thi.cod_trabalho= \'%s\' and status=\'ATIVADA\' ',
            $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho());

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
    
    public function carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho(AvaliacaoInscrito $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT thi.*,su.cod_usuario,su.login,su.nome_usuario,su.status as status_avaliador '
            . ',(IFNULL(thi.criterio_1,0)+IFNULL(thi.criterio_2,0)+IFNULL(thi.criterio_3,0)+IFNULL(thi.criterio_4,0)+IFNULL(thi.criterio_5,0)+IFNULL(thi.criterio_6,0)) AS nota '
            . 'FROM '.$this->tableName.' thi 
            INNER JOIN vw_ultima_avaliacao_premiacao_por_autor AS maxthi ON thi.cod_avaliacao=maxthi.cod_avaliacao
            left JOIN sis_usuarios su ON su.cod_usuario=thi.cod_avaliador
            left JOIN tab_convidados c ON c.cod_convidado=su.cod_usuario
            WHERE thi.cod_evento=\'%s\' and thi.cod_inscrito= \'%s\' and thi.cod_trabalho= \'%s\' and thi.status=\'ATIVADA\' ',
            $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getCodTrabalho());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaUltimasAvaliacaoPremiacao($return);
                return true;   
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
  
    public function carregarListaAvaliacoesByIdTrabalho(AvaliacaoInscrito $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM '.$this->tableName.' tt '
                    . 'WHERE tt.cod_evento=\'%s\' and tt.cod_trabalho=\'%s\' '
                    . 'order by tt.cod_avaliacao desc',$obj->getCodEvento(),$obj->getCodTrabalho());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaAvaliacoes($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
       
    public function insert(AvaliacaoInscrito $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_login']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
                        
            $sql = "SELECT * FROM ".$this->tableName." WHERE "
            . " cod_evento=".$obj->getCodEvento()." and cod_inscrito=0 and cod_avaliacao=0"; 
            
            $rs = $this->conex->Execute($sql);     
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
//            $insertSQL = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodAvaliacao($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    
    public function update(AvaliacaoInscrito $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_login']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');

            /************ log atualizacao *****************************/
            $sqlGE = "INSERT INTO tab_trabalhos_avaliacao_inscricao_atualizados 
            SELECT * FROM tab_trabalhos_avaliacao_inscricao WHERE cod_evento=".$obj->getCodEvento()." and cod_avaliacao = ".$obj->getCodAvaliacao();
            $rsGE = $this->conex->Execute($sqlGE);

            $sql = "SELECT * FROM tab_trabalhos_avaliacao_inscricao WHERE cod_evento = ".$obj->getCodEvento()." and cod_avaliacao = ".$obj->getCodAvaliacao();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("tab_trabalhos_avaliacao_inscricao");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
//            $updateSQL = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($updateSQL);           
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(AvaliacaoInscrito $obj, $debug=false) {
        try {
            
            /************ log exclusao *****************************/
            $sqlGE = "INSERT INTO tab_trabalhos_avaliacao_inscricao_excluidos 
            SELECT * FROM tab_trabalhos_avaliacao_inscricao WHERE cod_evento=".$obj->getCodEvento()." and cod_avaliacao = ".$obj->getCodAvaliacao();
            $rsGE = $this->conex->Execute($sqlGE);
            
            $sql = sprintf('DELETE FROM tab_trabalhos_avaliacao_inscricao '
                    . 'WHERE cod_evento=\'%s\' and cod_avaliacao= \'%s\' '
                    ,$obj->getCodEvento(),$obj->getCodAvaliacao());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    
   public function desativarAvaliacoesAvaliador(AvaliacaoInscrito $obj, $debug=false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_login']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('DESATIVADO');

            $sql = "UPDATE ".$this->tableName." SET status='DESATIVADO'
                    WHERE cod_evento = ".$obj->getCodEvento()." 
                        AND cod_inscrito = ".$obj->getCodInscrito()."
                        AND cod_avaliador = ".$obj->getCodAvaliador()." 
                        AND STATUS='ATIVADA'";
            
//            $rs = $this->conex->Execute($sql); 
//            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
//            $fws_ = $obj->getAllForArray($arrayColunas,false);
//            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
//            
//            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    
//    public function desativarAvaliacoesAvaliador(AvaliacaoInscrito $obj, $debug=false) {
//        try {
//            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
//            $obj->setIdAlteracao($_SESSION['_login']);
//            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
//            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
//            $obj->setVersao($obj->getVersao()+1);
//            $obj->setStatus('DESATIVADO');
//            
//            $sql = "SELECT * FROM ".$this->tableName." WHERE cod_evento = ".$obj->getCodEvento().""
//            . " and cod_inscrito = ".$obj->getCodInscrito()." and cod_trabalho = ".$obj->getCodTrabalho()
//            . " and cod_avaliador = ".$obj->getCodAvaliador()." and status='ATIVADA' "; 
//            
//            $rs = $this->conex->Execute($sql); 
//            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
//            $fws_ = $obj->getAllForArray($arrayColunas,false);
//            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
//            
//            $sql = sprintf($updateSQL);
//            $this->conex->debug = $debug;
//            $rs = $this->conex->Execute($sql);
//            return $rs;
//            
//        } catch (ADODB_Exception $e) {
//            $this->enviarMensagem($e);
//            return false;
//        }
//    }    

    /**
     * Essa função é usada nas consultas de listagens gerais.
     * @param object $obj        Objeto contendo informações adicionais para a construção do JOIN.
     * @param array $condicoes   Condições específicas para customizar o JOIN.
     * @param bool $debug        Define se a query deve ser exibida para debug (padrão: false).
     * @return string            A cláusula LEFT JOIN gerada.
     * @note Esta função é utilizada em listagens gerais e deve ser mantida consistente com as necessidades do sistema.
     */
    public static function getLeftJoin($obj, $condicoes, $debug = false) {
        $whereAvaliacaoInscrito = "";
        $leftJoin = "";
        if ($condicoes['perspectiva'] === 'listagemAvaliacao' || $condicoes['perspectiva'] === 'listagemClassificacao') {
            // dd($condicoes);
            
            $whereAvaliacaoInscrito = "";
            if($condicoes['avaliacao_inscrito']==''){
            }else if($condicoes['avaliacao_inscrito']=='AVALIADO'){
                $whereAvaliacaoInscrito = " and taia.classificacao_status IS NOT NULL ";
            }else if($condicoes['avaliacao_inscrito']=='NAO_AVALIADO'){
                $whereAvaliacaoInscrito = " and taia.classificacao_status IS NULL ";
            }else if($condicoes['avaliacao_inscrito']=='HABILITADO'){
                $whereAvaliacaoInscrito = " and taia.classificacao_status='H' ";
            }else if($condicoes['avaliacao_inscrito']=='HABILITADO_FINAL'){
                $whereAvaliacaoInscrito = " and taia.classificacao_status_final='H' ";
            }else if($condicoes['avaliacao_inscrito']=='NAO_HABILITADO'){
                $whereAvaliacaoInscrito = " and taia.classificacao_status='I' ";
            }else if($condicoes['avaliacao_inscrito']=='RECURSO_NAO_AVALIADO'){
                $whereAvaliacaoInscrito = " and taia.classificacao_status_apos_recurso is null ";
            }else if($condicoes['avaliacao_inscrito']=='RECURSO_AVALIADO'){
                $whereAvaliacaoInscrito = " and taia.classificacao_status_apos_recurso is not null ";
            }            
            $leftJoin = "
                LEFT JOIN tab_trabalhos_avaliacao_inscricao taia 
                ON taia.cod_inscrito = ti.cod_inscrito AND taia.status = 'ATIVADA'
                LEFT JOIN (
                    SELECT t.cod_evento, t.cod_trabalho, t.cod_inscrito, t.pontos_total, t.recurso_trabalho
                        ,(SELECT xx.recurso_trabalho 
                            FROM tab_trabalhos xx
                            WHERE xx.cod_inscrito  = t.cod_inscrito
                            AND xx.cod_evento      = t.cod_evento
                            AND xx.tipo_documento = 'RECURSO'
                            AND xx.status         = 'ATIVADA'
                            ORDER BY xx.cod_trabalho DESC
                        LIMIT 1
                        ) AS texto_recurso_inscrito                     
                    FROM tab_trabalhos t
                    WHERE t.status = 'ATIVADA'  and t.tipo_documento<>'RECURSO'
                    AND t.cod_trabalho = (
                        SELECT MAX(t2.cod_trabalho)
                        FROM tab_trabalhos t2
                        WHERE t2.cod_inscrito = t.cod_inscrito
                            AND t2.status = 'ATIVADA' and t2.tipo_documento<> 'RECURSO'
                    )
                ) tavi 
                ON tavi.cod_inscrito = ti.cod_inscrito 
            ";
     
            // Filtro por posição na classificação
            if (!empty($condicoes['classificacao_ate'])) {
                $posicao = intval($condicoes['classificacao_ate']);
                $whereAvaliacaoInscrito .= " AND taia.classificacao_final IS NOT NULL AND taia.classificacao_final != '' AND CAST(taia.classificacao_final AS UNSIGNED) <= " . $posicao . " ";
            }

            if ($debug) {
                error_log("Left Join Avaliacao: " . $leftJoin);
                error_log("Filtro Avaliação: " . $whereAvaliacaoInscrito);
            }
        }
        return ['leftJoin'=>$leftJoin, 'whereAvaliacaoInscrito'=>$whereAvaliacaoInscrito]; // Retorna o JOIN + Filtro
    }

    public static function getCampos($obj, $condicoes, $debug = false) {
        if ($condicoes['perspectiva'] === 'listagemAvaliacao' || $condicoes['perspectiva'] === 'listagemClassificacao') {
            $campos = "
                , taia.cod_avaliador AS cod_avaliador_inscrito
                , taia.id_inclusao as id_inclusao_avaliacao
                , taia.id_alteracao as id_alteracao_avaliacao
                , taia.data_inclusao as data_inclusao_avaliacao
                , taia.data_alteracao as data_alteracao_avaliacao
                , taia.pontos_informados_trabalho
                , taia.pontos_total_confirmado
                , taia.pontos_total_apos_recurso
                , taia.classificacao
                , taia.classificacao_status
                , taia.classificacao_final
                , taia.classificacao_status_final
                , tavi.pontos_total
                , taia.parecer_avaliador
                , tavi.recurso_trabalho
                , tavi.texto_recurso_inscrito
                , taia.classificacao_status_apos_recurso
                , taia.recurso_parecer as parecer_avaliador_apos_recurso
                , too.titulo as nome_oficina 
                , top.titulo as nome_perfil 
            ";
            if ($debug) {
                error_log("Campos Avaliacao: " . $campos);
            }
            return $campos;
        }
        return "";
    }

    public static function getOrderBy($obj, $condicoes, $debug = false) {
        $order = "";
        if ($condicoes['perspectiva'] === 'listagemAvaliacao' || $condicoes['perspectiva'] === 'listagemClassificacao') {
            if($condicoes['ordenar'] == 'nota_informada'){
                $order = " ORDER BY tavi.pontos_total desc, too.ordem, ti.nome_inscrito ";
            }else if ($condicoes['ordenar'] == 'nota_confirmada'){
                $order = " ORDER BY tavi.pontos_total_confirmado desc, ti.nome_inscrito ";
            }else if ($condicoes['ordenar'] == 'classificacao_final'){
                $order = " ORDER BY CASE 
                                        WHEN taia.classificacao_final IS NULL OR taia.classificacao_final = '' THEN 999999
                                        ELSE CAST(taia.classificacao_final AS UNSIGNED)
                                    END, ti.nome_inscrito ";
            }
            if ($debug) {
                error_log("Order By Avaliacao: " . $order);
            }
        }
        return $order;
    }
}
