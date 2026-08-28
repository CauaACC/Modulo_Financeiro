<?php

/**
 * Leitura das views de certificado para o modulo GESTOR (fora do evento).
 *
 *   vw_certificados_emitidos  -> 1 linha por ATIVIDADE
 *        gestor, cod_evento, cod_oficina, titulo (da oficina), total_certificados
 *        (conta ligacao inscrito<->oficina com flg_presenca=1 e oficina que libera certificado)
 *
 *   vw_certificados_cobranca  -> 1 linha por EVENTO
 *        gestor, cod_evento, titulo, qtd_certificados_em, qtd_certificados_cob, qtd_a_cobrar
 *        (emitidos x fin_certificados_notafiscal)
 *
 *   vw_certificados_cobranca_completa -> 1 linha por EVENTO, base da COBRANCA
 *        gestor, cod_evento, qtd_pessoas, qtd_atividade, qtd_geral, qtd_perfil,
 *        qtd_pessoas_cob, qtd_a_cobrar
 *
 * POR QUE A VIEW NOVA (2026-07-28)
 * -------------------------------------------------------------------------
 * A vw_certificados_cobranca so enxerga certificado POR ATIVIDADE, porque nasce da
 * vw_certificados_emitidos (que parte de tab_lig_inscritos_oficinas). Existem TRES
 * mecanismos de emissao -- atividade, GERAL de participacao (certificado_congressista=1)
 * e por PERFIL (tab_opcoes_perfis.liberar_certificado=1) -- entao evento sem atividade
 * cadastrada nunca aparecia na cobranca. Ex.: evento 124 "Tratamento de Feridas
 * Complexas e Queimaduras", 18 presentes e zero linha.
 *
 * A vw_certificados_cobranca_completa cobre os tres. As views antigas ficaram
 * INTOCADAS (a emitidos continua por cod_oficina, usada pelo Painel de Certificados
 * em InscritoDAO::contarAptosPorOficina).
 *
 * REGRA: cobra-se POR PESSOA. A mesma pessoa pode receber varios certificados no
 * evento e conta UMA vez -- por isso qtd_pessoas e a base, e qtd_atividade/geral/perfil
 * sao apenas INFORMATIVAS (contam documentos). A soma delas NAO bate com qtd_pessoas.
 *
 * ATENCAO: a coluna "titulo" da vw_certificados_cobranca NAO e o titulo do evento --
 * ela vem do GROUP BY cod_evento sobre a view por oficina, entao traz o nome de UMA
 * atividade qualquer. Por isso as consultas aqui fazem join com tab_eventos e usam
 * e.titulo. As views ficam intocadas (outros consumidores continuam iguais).
 *
 * Somente leitura: nenhuma escrita em fin_certificados_notafiscal.
 */
class CertificadoGestorDAO extends DAO {

    /** Teto de linhas por consulta -- as duas views sao pequenas, e so um freio. */
    const LIMITE = 2000;

    /**
     * Gestores que aparecem nas views (para o select do filtro).
     * @return array lista de strings
     */
    public function listarGestores($debug = false) {
        $lista = array();
        try {
            $sql = "SELECT DISTINCT gestor
                      FROM vw_certificados_emitidos
                     WHERE gestor IS NOT NULL AND gestor != ''
                     ORDER BY gestor";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) { $lista[] = $r['gestor']; }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $lista;
    }

    /**
     * Cobranca por EVENTO, contada POR PESSOA (vw_certificados_cobranca_completa).
     * Filtros opcionais: gestor, texto (titulo/codigo) e "somente com saldo a cobrar".
     *
     * pessoas = base cobravel. atividade/geral/perfil = informativo (documentos).
     *
     * @return array linhas com cod_evento, titulo, gestor, pessoas, atividade,
     *               geral, perfil, cobrados, a_cobrar
     */
    public function listarCobranca($gestor = '', $busca = '', $somenteACobrar = false, $debug = false) {
        $lista = array();
        try {
            $where = array();
            if ($gestor !== '') {
                $where[] = "c.gestor = " . $this->conex->qstr($gestor);
            }
            if ($busca !== '') {
                // codigo exato OU parte do titulo do evento
                $like = $this->conex->qstr('%' . $busca . '%');
                $w = "(e.titulo LIKE $like";
                if (ctype_digit($busca)) {
                    $w .= " OR c.cod_evento = " . (int) $busca;
                }
                $where[] = $w . ")";
            }
            if ($somenteACobrar) {
                $where[] = "c.qtd_a_cobrar > 0";
            }
            $sql = "SELECT c.cod_evento,
                           c.gestor,
                           e.titulo AS titulo_evento,
                           e.status AS status_evento,
                           -- a partir de quando o inscrito consegue emitir. NAO confundir com
                           -- certificado_data_emissao, que e a data impressa no certificado.
                           cfg.certificado_dat_ini AS liberacao,
                           c.qtd_pessoas     AS pessoas,
                           c.qtd_atividade   AS atividade,
                           c.qtd_geral       AS geral,
                           c.qtd_perfil      AS perfil,
                           c.qtd_pessoas_cob AS cobrados,
                           c.qtd_a_cobrar    AS a_cobrar
                      FROM vw_certificados_cobranca_completa c
                      JOIN tab_eventos e ON e.cod_evento = c.cod_evento
                      LEFT JOIN tab_eventos_configuracao cfg ON cfg.cod_evento = c.cod_evento"
                 . (count($where) ? " WHERE " . implode(' AND ', $where) : "")
                 . " ORDER BY c.cod_evento DESC
                     LIMIT " . self::LIMITE;
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) {
                    $lista[] = array(
                        'cod_evento' => (int) $r['cod_evento'],
                        'gestor'     => $r['gestor'],
                        'titulo'     => $r['titulo_evento'],
                        'status'     => $r['status_evento'],
                        'liberacao'  => $r['liberacao'],
                        'pessoas'    => (int) $r['pessoas'],
                        'atividade'  => (int) $r['atividade'],
                        'geral'      => (int) $r['geral'],
                        'perfil'     => (int) $r['perfil'],
                        'cobrados'   => (int) $r['cobrados'],
                        'a_cobrar'   => (int) $r['a_cobrar'],
                    );
                }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $lista;
    }

    /**
     * PESSOAS de um evento que tem direito a pelo menos um certificado -- e por qual
     * mecanismo. E o drill-down da aba Cobranca: a mesma regra que produz qtd_pessoas
     * na vw_certificados_cobranca_completa, so que aberta nome a nome.
     *
     * Serve para conferir o numero antes de cobrar. Funciona para QUALQUER evento,
     * inclusive os que nao tem atividade nenhuma (so geral/perfil) -- que era o caso
     * onde a aba Emitidos nao ajudava.
     *
     * @return array linhas com cod_inscrito, nome, cpf, email e os flags
     *               por_atividade / por_geral / por_perfil
     */
    public function listarPessoasEvento($codEvento, $debug = false) {
        $lista = array();
        $cod = (int) $codEvento;
        if ($cod <= 0) {
            return $lista;
        }
        try {
            $sql = "SELECT i.cod_inscrito,
                           i.nome_inscrito,
                           i.cpf,
                           i.e_mail,
                           MAX(x.ativ) AS por_atividade,
                           MAX(x.ger)  AS por_geral,
                           MAX(x.prf)  AS por_perfil
                      FROM (
                          SELECT lio.cod_inscrito, 1 AS ativ, 0 AS ger, 0 AS prf
                            FROM tab_lig_inscritos_oficinas lio
                            JOIN tab_opcoes_oficinas o ON o.cod_oficina = lio.cod_oficina
                            JOIN tab_inscritos i2 ON i2.cod_inscrito = lio.cod_inscrito AND i2.cod_evento = lio.cod_evento
                           WHERE lio.cod_evento = $cod AND lio.flg_presenca = 1
                             AND o.liberar_certificado = 1 AND i2.status = 'ATIVADA'
                          UNION ALL
                          SELECT i3.cod_inscrito, 0, 1, 0
                            FROM tab_inscritos i3
                            JOIN tab_eventos_configuracao ec ON ec.cod_evento = i3.cod_evento
                           WHERE i3.cod_evento = $cod AND ec.certificado_congressista = 1
                             AND i3.status = 'ATIVADA' AND i3.flg_presenca_inscrito = 1
                             AND (i3.flg_bloquear_certificado_geral IS NULL OR i3.flg_bloquear_certificado_geral = 0)
                          UNION ALL
                          SELECT i4.cod_inscrito, 0, 0, 1
                            FROM tab_inscritos i4
                            JOIN tab_opcoes_perfis p ON p.cod_perfil = i4.cod_perfil AND p.cod_evento = i4.cod_evento
                           WHERE i4.cod_evento = $cod AND i4.status = 'ATIVADA'
                             AND i4.flg_presenca_inscrito = 1 AND p.liberar_certificado = 1
                          UNION ALL
                          SELECT lip.cod_inscrito, 0, 0, 1
                            FROM tab_lig_inscritos_perfis lip
                            JOIN tab_inscritos i5 ON i5.cod_inscrito = lip.cod_inscrito AND i5.cod_evento = lip.cod_evento
                            JOIN tab_opcoes_perfis p2 ON p2.cod_perfil = lip.cod_perfil AND p2.cod_evento = lip.cod_evento
                           WHERE lip.cod_evento = $cod AND i5.status = 'ATIVADA'
                             AND i5.flg_presenca_inscrito = 1 AND p2.liberar_certificado = 1
                      ) x
                      JOIN tab_inscritos i ON i.cod_inscrito = x.cod_inscrito AND i.cod_evento = $cod
                     GROUP BY i.cod_inscrito, i.nome_inscrito, i.cpf, i.e_mail
                     ORDER BY i.nome_inscrito
                     LIMIT " . self::LIMITE;
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) {
                    $lista[] = array(
                        'cod_inscrito'  => (int) $r['cod_inscrito'],
                        'nome'          => $r['nome_inscrito'],
                        'cpf'           => $r['cpf'],
                        'email'         => $r['e_mail'],
                        'por_atividade' => (int) $r['por_atividade'],
                        'por_geral'     => (int) $r['por_geral'],
                        'por_perfil'    => (int) $r['por_perfil'],
                    );
                }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $lista;
    }

    /**
     * Emitidos por ATIVIDADE. Filtros opcionais: gestor, texto e um evento
     * especifico (usado pelo drill-down a partir da aba Cobranca).
     *
     * @return array linhas com cod_evento, titulo_evento, cod_oficina, titulo_atividade, total
     */
    public function listarEmitidos($gestor = '', $busca = '', $codEvento = 0, $debug = false) {
        $lista = array();
        try {
            $where = array();
            if ($gestor !== '') {
                $where[] = "v.gestor = " . $this->conex->qstr($gestor);
            }
            if ((int) $codEvento > 0) {
                $where[] = "v.cod_evento = " . (int) $codEvento;
            }
            if ($busca !== '') {
                $like = $this->conex->qstr('%' . $busca . '%');
                $w = "(v.titulo LIKE $like OR e.titulo LIKE $like";
                if (ctype_digit($busca)) {
                    $w .= " OR v.cod_evento = " . (int) $busca;
                }
                $where[] = $w . ")";
            }
            $sql = "SELECT v.cod_evento,
                           v.cod_oficina,
                           v.gestor,
                           v.titulo AS titulo_atividade,
                           v.total_certificados,
                           e.titulo AS titulo_evento
                      FROM vw_certificados_emitidos v
                      JOIN tab_eventos e ON e.cod_evento = v.cod_evento"
                 . (count($where) ? " WHERE " . implode(' AND ', $where) : "")
                 . " ORDER BY v.cod_evento DESC, v.total_certificados DESC
                     LIMIT " . self::LIMITE;
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) {
                    $lista[] = array(
                        'cod_evento'       => (int) $r['cod_evento'],
                        'cod_oficina'      => (int) $r['cod_oficina'],
                        'gestor'           => $r['gestor'],
                        'titulo_evento'    => $r['titulo_evento'],
                        'titulo_atividade' => $r['titulo_atividade'],
                        'total'            => (int) $r['total_certificados'],
                    );
                }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $lista;
    }
}
