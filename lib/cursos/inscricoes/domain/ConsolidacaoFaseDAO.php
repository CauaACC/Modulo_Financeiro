<?php

/**
 * ConsolidacaoFaseDAO
 *
 * SQL para a operacao de "Consolidar Fase" — preenche as colunas
 * pontos_total_final, classificacao_status_final e classificacao_final
 * em tab_trabalhos_avaliacao_inscricao a partir do estado pos-recurso
 * (ou pos-avaliacao quando nao houve recurso).
 *
 * Mantem os artefatos anteriores intactos (pontos_total_confirmado,
 * pontos_total_apos_recurso, classificacao_status, classificacao_status_apos_recurso).
 *
 * Registra cada execucao em sis_log_consolidacao_etapa (append-only).
 */
class ConsolidacaoFaseDAO extends DAO
{
    /**
     * Recursos pendentes de analise na etapa: o inscrito enviou recurso
     * (recurso_texto preenchido) mas o avaliador ainda nao gravou a
     * decisao (classificacao_status_apos_recurso vazio).
     *
     * Bloqueia a consolidacao se houver pendencias.
     */
    public function carregarListaRecursosPendentes(ConsolidacaoFase $obj, $debug = false)
    {
        try {
            $codEvento  = (int) $obj->getCodEvento();
            $ordemEtapa = (int) $obj->getOrdemEtapa();
            $sql = sprintf("
                SELECT
                    i.cod_inscrito,
                    i.id_publico,
                    i.nome_inscrito,
                    a.cod_avaliacao,
                    a.recurso_status
                FROM tab_trabalhos_avaliacao_inscricao a
                JOIN tab_avaliacao_etapa e
                       ON e.cod_etapa  = a.cod_etapa
                      AND e.cod_evento = a.cod_evento
                JOIN tab_inscritos i
                       ON i.cod_inscrito = a.cod_inscrito
                      AND i.cod_evento   = a.cod_evento
                      AND i.status       = 'ATIVADA'
                WHERE a.cod_evento  = '%d'
                  AND a.status      = 'ATIVADA'
                  AND e.ordem_etapa = '%d'
                  AND a.recurso_texto IS NOT NULL
                  AND a.recurso_texto <> ''
                  AND (a.classificacao_status_apos_recurso IS NULL OR a.classificacao_status_apos_recurso = '')
                ORDER BY i.nome_inscrito
            ", $codEvento, $ordemEtapa);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $linha = new ConsolidacaoFase();
                    $linha->setAll($rows);
                    $lista[] = $linha;
                }
            }
            $obj->setListaRecursosPendentes($lista);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Carrega registros da etapa para consolidar, ja com COALESCE aplicado
     * para definir pontos efetivos e status efetivo.
     *
     * - pontos_efetivos    = COALESCE(pontos_total_apos_recurso, pontos_total_confirmado)
     * - status_efetivo     = COALESCE(NULLIF(classificacao_status_apos_recurso,''), classificacao_status)
     *
     * O calculo da classificacao_final (1..N para deferidos, NULL para indeferidos)
     * eh feito em PHP pela entidade ConsolidacaoFase aplicando o desempate
     * com TITULACAO_RANK + EXPERIENCIA_CHAVES (mesmas constantes do EditalPublicacao).
     */
    public function carregarRegistrosParaConsolidar(ConsolidacaoFase $obj, $debug = false)
    {
        try {
            $codEvento  = (int) $obj->getCodEvento();
            $ordemEtapa = (int) $obj->getOrdemEtapa();
            // data_nascimento eh varchar — aceita tanto ISO ('1978-03-28') como
            // formato brasileiro ('28/03/1978'). COALESCE pega o primeiro que
            // STR_TO_DATE conseguir parsear.
            $sql = sprintf("
                SELECT
                    a.cod_avaliacao,
                    a.cod_inscrito,
                    a.cod_etapa,
                    i.id_publico,
                    i.nome_inscrito,
                    i.data_nascimento,
                    TIMESTAMPDIFF(YEAR,
                        COALESCE(
                            STR_TO_DATE(i.data_nascimento, '%%Y-%%m-%%d'),
                            STR_TO_DATE(i.data_nascimento, '%%d/%%m/%%Y')
                        ),
                        CURDATE()
                    ) AS idade,
                    COALESCE(a.pontos_total_apos_recurso, a.pontos_total_confirmado) AS pontos_efetivos,
                    COALESCE(
                        NULLIF(a.classificacao_status_apos_recurso, ''),
                        a.classificacao_status
                    ) AS status_efetivo,
                    a.avaliacao_json
                FROM tab_trabalhos_avaliacao_inscricao a
                JOIN tab_avaliacao_etapa e
                       ON e.cod_etapa  = a.cod_etapa
                      AND e.cod_evento = a.cod_evento
                JOIN tab_inscritos i
                       ON i.cod_inscrito = a.cod_inscrito
                      AND i.cod_evento   = a.cod_evento
                      AND i.status       = 'ATIVADA'
                WHERE a.cod_evento  = '%d'
                  AND a.status      = 'ATIVADA'
                  AND e.ordem_etapa = '%d'
                ORDER BY i.nome_inscrito
            ", $codEvento, $ordemEtapa);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $linha = new ConsolidacaoFase();
                    $linha->setAll($rows);
                    $lista[] = $linha;
                }
            }
            $obj->setListaRegistros($lista);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Grava os campos finais + criterios de desempate em uma linha da
     * tab_trabalhos_avaliacao_inscricao. Os 3 criterios sao gravados
     * para auditoria/transparencia da classificacao (so para deferidos —
     * indeferidos nao entram no desempate e ficam NULL).
     *
     * @param int        $codAvaliacao
     * @param string     $pontosFinal             pontos efetivos (varchar(5) — string com ate "999.9")
     * @param string     $statusFinal             'H' | 'I' | ''
     * @param int|null   $classificacaoFinal      posicao 1..N para deferidos, NULL para indeferidos
     * @param int|null   $nivelTitulacao          rank derivado de avaliacao_json (10..90), NULL para indeferidos
     * @param int|null   $mesesExperiencia        soma das chaves 3.x de avaliacao_json, NULL para indeferidos
     * @param int|null   $idade                   idade no momento da consolidacao, NULL para indeferidos
     */
    public function atualizarConsolidacao($codAvaliacao, $pontosFinal, $statusFinal, $classificacaoFinal,
                                          $nivelTitulacao = null, $mesesExperiencia = null, $idade = null,
                                          $debug = false)
    {
        try {
            $pontosEsc = addslashes((string) $pontosFinal);
            $statusEsc = addslashes((string) $statusFinal);
            $classifSql   = is_null($classificacaoFinal) || $classificacaoFinal === '' ? 'NULL' : "'" . (int)$classificacaoFinal . "'";
            $titulacaoSql = is_null($nivelTitulacao)     || $nivelTitulacao === ''     ? 'NULL' : (int) $nivelTitulacao;
            $mesesSql     = is_null($mesesExperiencia)   || $mesesExperiencia === ''   ? 'NULL' : (int) $mesesExperiencia;
            $idadeSql     = is_null($idade)              || $idade === ''              ? 'NULL' : (int) $idade;

            $idAlteracao = (string) ($_SESSION['_login'] ?? '');
            $ipAlteracao = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent   = addslashes($_SERVER['HTTP_USER_AGENT'] ?? '');

            $sql = sprintf("UPDATE tab_trabalhos_avaliacao_inscricao
                SET pontos_total_final            = '%s',
                    classificacao_status_final    = '%s',
                    classificacao_final           = %s,
                    desempate_nivel_titulacao     = %s,
                    desempate_meses_experiencia   = %s,
                    desempate_idade               = %s,
                    data_alteracao                = '%s',
                    ip_alteracao                  = '%s',
                    id_alteracao                  = '%s',
                    http_user_agent_alteracao     = '%s'
                WHERE cod_avaliacao = %d
                LIMIT 1",
                $pontosEsc,
                $statusEsc,
                $classifSql,
                $titulacaoSql,
                $mesesSql,
                $idadeSql,
                date('Y-m-d H:i:s'),
                $ipAlteracao,
                addslashes($idAlteracao),
                $userAgent,
                (int) $codAvaliacao
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
     * Resolve cod_etapa de uma ordem_etapa do evento (linha ativa).
     * Retorna null se nao existir.
     */
    public function getCodEtapaPorOrdem($codEvento, $ordemEtapa, $debug = false)
    {
        try {
            $sql = sprintf("SELECT cod_etapa FROM tab_avaliacao_etapa
                WHERE cod_evento = '%d' AND ordem_etapa = '%d' AND status = 'ATIVADA'
                LIMIT 1",
                (int)$codEvento, (int)$ordemEtapa
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) {
                return null;
            }
            return (int) $rs->fields['cod_etapa'];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return null;
        }
    }

    /**
     * Append no log: registra a execucao da consolidacao com totais e autor.
     */
    public function registrarLog(ConsolidacaoFase $obj, $debug = false)
    {
        try {
            $idConsolid = (string) ($_SESSION['_login'] ?? '');
            $ipConsolid = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent  = addslashes($_SERVER['HTTP_USER_AGENT'] ?? '');
            $obs        = addslashes((string) $obj->getObservacao());

            $codEtapaSql = $obj->getCodEtapa()
                ? "'" . (int) $obj->getCodEtapa() . "'"
                : 'NULL';

            $sql = sprintf("INSERT INTO sis_log_consolidacao_etapa
                (cod_evento, ordem_etapa, cod_etapa,
                 total_inscritos, total_deferidos, total_indeferidos,
                 data_consolidacao, id_consolidacao, ip_consolidacao,
                 http_user_agent, observacao)
                VALUES (%d, %d, %s, %d, %d, %d, '%s', '%s', '%s', '%s', '%s')",
                (int) $obj->getCodEvento(),
                (int) $obj->getOrdemEtapa(),
                $codEtapaSql,
                (int) $obj->getTotalInscritos(),
                (int) $obj->getTotalDeferidos(),
                (int) $obj->getTotalIndeferidos(),
                date('Y-m-d H:i:s'),
                addslashes($idConsolid),
                $ipConsolid,
                $userAgent,
                $obs
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
     * Carrega a ultima consolidacao registrada para o evento + ordem_etapa.
     * Util pra view exibir "consolidado em X por Y".
     * Retorna null se nunca foi consolidada.
     */
    public function carregarUltimaConsolidacao(ConsolidacaoFase $obj, $debug = false)
    {
        try {
            $sql = sprintf("SELECT * FROM sis_log_consolidacao_etapa
                WHERE cod_evento = '%d' AND ordem_etapa = '%d'
                ORDER BY data_consolidacao DESC
                LIMIT 1",
                (int) $obj->getCodEvento(),
                (int) $obj->getOrdemEtapa()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) {
                return null;
            }
            $linha = new ConsolidacaoFase();
            $linha->setAll($rs->fields);
            return $linha;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return null;
        }
    }

    /**
     * Carrega lista consolidada (registros com pontos_total_final preenchido)
     * para a view exibir, ordenada por classificacao_final ASC (deferidos) e
     * depois indeferidos por nome.
     */
    public function carregarListaConsolidada(ConsolidacaoFase $obj, $debug = false)
    {
        try {
            $codEvento  = (int) $obj->getCodEvento();
            $ordemEtapa = (int) $obj->getOrdemEtapa();

            $sql = sprintf("
                SELECT
                    a.cod_avaliacao,
                    a.cod_inscrito,
                    i.id_publico,
                    fn_nome_title_case(i.nome_inscrito) AS nome_inscrito,
                    a.pontos_total_final,
                    a.classificacao_status_final,
                    a.classificacao_final,
                    a.pontos_total_confirmado,
                    a.pontos_total_apos_recurso,
                    a.classificacao_status,
                    a.classificacao_status_apos_recurso,
                    a.recurso_texto,
                    a.recurso_status
                FROM tab_trabalhos_avaliacao_inscricao a
                JOIN tab_avaliacao_etapa e
                       ON e.cod_etapa  = a.cod_etapa
                      AND e.cod_evento = a.cod_evento
                JOIN tab_inscritos i
                       ON i.cod_inscrito = a.cod_inscrito
                      AND i.cod_evento   = a.cod_evento
                      AND i.status       = 'ATIVADA'
                WHERE a.cod_evento  = '%d'
                  AND a.status      = 'ATIVADA'
                  AND e.ordem_etapa = '%d'
                  AND a.pontos_total_final IS NOT NULL
                ORDER BY
                    CASE WHEN a.classificacao_status_final = 'H' THEN 0 ELSE 1 END,
                    CASE
                        WHEN a.classificacao_final IS NULL OR a.classificacao_final = '' THEN 999999
                        ELSE CAST(a.classificacao_final AS UNSIGNED)
                    END,
                    i.nome_inscrito
            ", $codEvento, $ordemEtapa);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $linha = new ConsolidacaoFase();
                    $linha->setAll($rows);
                    $lista[] = $linha;
                }
            }
            $obj->setListaConsolidada($lista);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
