<?php

/**
 * EditalPublicacaoDAO
 *
 * SQL pra geracao do edital de publicacao de uma etapa de avaliacao.
 *
 * Reproduz a query do script Python GerarAvaliacaoFase2_129.py portada pro PHP,
 * com filtro adicional (mesmos rotulos da listagem GerenciarAvaliacaoFase2):
 *   - todos / pendente / avaliado / habilitados / nao_habilitados
 *   - com_recurso / com_docs_ilegiveis
 *
 * Coluna pontos: usa pontos_total_apos_recurso se preenchida, senao pontos_total_confirmado.
 * Coluna classificacao_status: usa classificacao_status_final se preenchida, senao
 * classificacao_status_apos_recurso, senao classificacao_status (regra do COALESCE).
 *
 * Filtro por ordem_etapa permite mesmo SQL pra Fase 1 / Fase 2 / Fase Final.
 */
class EditalPublicacaoDAO extends DAO
{
    /**
     * Carrega lista de candidatos elegiveis pro edital da etapa informada.
     *
     * @param EditalPublicacao $obj  entidade que recebera lista via setListaEditais
     * @param int    $ordemEtapa     ordem da etapa (1, 2, 3...) — usada como rotulo;
     *                               a etapa fisicamente lida depende de $cfg['ordem_etapa_origem']
     * @param string $filtro         filtro da listagem
     * @param array  $cfg            config da fase (vinda de EditalPublicacao::getConfigFases()).
     *                               Chaves relevantes:
     *                               - 'fase_pre_requisito' : ordem da fase anterior cuja H eh pre-req
     *                               - 'ordem_etapa_origem' : le os registros desta outra ordem (Fase Final)
     *                               - 'usa_campos_finais'  : usa pontos_total_final / *_final no SELECT
     * @param bool  $debug
     */
    public function carregarListaEdital(EditalPublicacao $obj, $ordemEtapa, $filtro = 'todos', $cfg = array(), $debug = false)
    {
        try {
            $codEvento  = (int) $obj->getCodEvento();
            $ordemEtapa = (int) $ordemEtapa;

            // Aceita cfg como array (preferencial) ou int (compat retroativa = fase_pre_requisito)
            if (!is_array($cfg)) {
                $cfg = array('fase_pre_requisito' => $cfg);
            }
            $faseAnterior = isset($cfg['fase_pre_requisito']) ? $cfg['fase_pre_requisito'] : null;
            $faseAnt    = (is_null($faseAnterior) || $faseAnterior === '') ? null : (int)$faseAnterior;
            $ordemEtapaFisica = isset($cfg['ordem_etapa_origem']) && (int)$cfg['ordem_etapa_origem'] > 0
                              ? (int) $cfg['ordem_etapa_origem']
                              : $ordemEtapa;
            $usaCamposFinais = !empty($cfg['usa_campos_finais']);

            // Filtro condicional — mesmas regras da listagem da tela
            $whereFiltro = '';
            switch ($filtro) {
                case 'pendente':
                    $whereFiltro = ' AND a.cod_avaliacao IS NULL ';
                    break;
                case 'avaliado':
                    $whereFiltro = ' AND a.cod_avaliacao IS NOT NULL ';
                    break;
                case 'habilitados':
                    $whereFiltro = " AND COALESCE(NULLIF(a.classificacao_status_final, ''),"
                                 . " NULLIF(a.classificacao_status_apos_recurso, ''),"
                                 . " a.classificacao_status) = 'H' ";
                    break;
                case 'nao_habilitados':
                    $whereFiltro = " AND COALESCE(NULLIF(a.classificacao_status_final, ''),"
                                 . " NULLIF(a.classificacao_status_apos_recurso, ''),"
                                 . " a.classificacao_status) = 'I' ";
                    break;
                case 'com_recurso':
                    $whereFiltro = " AND a.recurso_texto IS NOT NULL AND a.recurso_texto <> '' ";
                    break;
                case 'com_docs_ilegiveis':
                    $whereFiltro = " AND EXISTS (
                        SELECT 1 FROM tab_trabalhos tdoc
                         WHERE tdoc.cod_inscrito = i.cod_inscrito
                           AND tdoc.cod_evento   = i.cod_evento
                           AND tdoc.status       = 'ATIVADA'
                           AND tdoc.documento_legivel = 0
                    ) ";
                    break;
            }

            // Pre-requisito: pra fases > 1, exige habilitacao FINAL na fase anterior.
            // Usamos INNER JOIN com a fase anterior + COALESCE(final, apos_recurso, status) = 'H'.
            $joinPreReq  = '';
            $wherePreReq = '';
            if (!is_null($faseAnt)) {
                $joinPreReq = sprintf("
                    JOIN tab_trabalhos_avaliacao_inscricao a_prev
                        ON  a_prev.cod_inscrito = i.cod_inscrito
                        AND a_prev.cod_evento   = i.cod_evento
                        AND a_prev.status       = 'ATIVADA'
                    JOIN tab_avaliacao_etapa e_prev
                        ON  e_prev.cod_etapa   = a_prev.cod_etapa
                        AND e_prev.cod_evento  = a_prev.cod_evento
                        AND e_prev.ordem_etapa = %d
                ", $faseAnt);
                $wherePreReq = " AND COALESCE(
                                    NULLIF(a_prev.classificacao_status_final, ''),
                                    NULLIF(a_prev.classificacao_status_apos_recurso, ''),
                                    a_prev.classificacao_status
                                 ) = 'H' ";
            }

            // Modo "Fase Final consolidada": le DIRETO dos campos *_final
            // (preenchidos pela operacao Consolidar Fase). Tambem filtra
            // pra mostrar apenas registros ja consolidados.
            if ($usaCamposFinais) {
                $selectPontos = " a.pontos_total_final AS pontos, ";
                $selectStatus = " a.classificacao_status_final AS classificacao_status, ";
                $selectClassif = " a.classificacao_final AS classificacao, ";
                $whereSomenteConsolidados = " AND a.pontos_total_final IS NOT NULL ";
            } else {
                $selectPontos = " COALESCE(a.pontos_total_apos_recurso, a.pontos_total_confirmado) AS pontos, ";
                $selectStatus = " COALESCE(
                                    NULLIF(a.classificacao_status_final, ''),
                                    NULLIF(a.classificacao_status_apos_recurso, ''),
                                    a.classificacao_status
                                ) AS classificacao_status, ";
                $selectClassif = " '' AS classificacao, ";
                $whereSomenteConsolidados = '';
            }

            $sql = sprintf("
                SELECT
                    i.cod_inscrito,
                    i.id_publico,
                    fn_nome_title_case(i.nome_inscrito) AS nome_inscrito,
                    i.data_nascimento,
                    TIMESTAMPDIFF(YEAR,
                        COALESCE(
                            STR_TO_DATE(i.data_nascimento, '%%Y-%%m-%%d'),
                            STR_TO_DATE(i.data_nascimento, '%%d/%%m/%%Y')
                        ),
                        CURDATE()
                    ) AS idade,
                    a.cod_avaliacao,
                    %s
                    %s
                    %s
                    a.avaliacao_json
                FROM tab_inscritos i
                %s
                LEFT JOIN tab_trabalhos_avaliacao_inscricao a
                    ON  a.cod_inscrito = i.cod_inscrito
                    AND a.cod_evento   = i.cod_evento
                    AND a.status       = 'ATIVADA'
                    AND a.cod_etapa IN (
                        SELECT cod_etapa FROM tab_avaliacao_etapa
                         WHERE cod_evento = %d AND ordem_etapa = %d AND status = 'ATIVADA'
                    )
                WHERE i.cod_evento = %d
                  AND i.status     = 'ATIVADA'
                  %s
                  %s
                  %s
                ORDER BY i.nome_inscrito
            ",
                $selectPontos, $selectStatus, $selectClassif,
                $joinPreReq, $codEvento, $ordemEtapaFisica, $codEvento,
                $wherePreReq, $whereFiltro, $whereSomenteConsolidados
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $linha = new EditalPublicacao();
                    $linha->setAll($rows);
                    $lista[] = $linha;
                }
            }
            $obj->setListaEditais($lista);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
