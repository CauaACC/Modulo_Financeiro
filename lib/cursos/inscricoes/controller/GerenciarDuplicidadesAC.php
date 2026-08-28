<?php

/**
 * Deteccao de pagamento em duplicidade no financeiro do evento.
 *
 * O que separa parcelamento de duplicidade e o `pedido_id`:
 *   - mensalidade/parcelamento -> N pagamentos com N pedido_id DISTINTOS
 *     (cada parcela e uma cobranca propria);
 *   - duplicidade -> o MESMO pedido_id pago 2+ vezes (boleto reemitido, dois
 *     cliques, PIX repetido).
 * Parcelamento nunca gera pedido repetido, por isso o criterio e confiavel.
 *
 * A confirmacao forte vem do gateway: contar quantas transacoes daquele pedido
 * estao 'Aprovada' em pagseguro_transacoes. Se 2+, o dinheiro entrou duas vezes.
 *
 * Fica em arquivo proprio de proposito: GerenciarRelatoriosFinanceirosAC e sua
 * view sao mexidos por varias frentes, e esta tela nao precisa disputar espaco.
 *
 * A tela APONTA; a decisao (devolver, justificar, ignorar) e humana -- ha casos
 * legitimos que caem no filtro, como carga/importacao antiga.
 */
class GerenciarDuplicidadesAC {

    private $conex;

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        ConexaoDB::getInstance();
        $this->conex = ConexaoDB::getConexaoDB();
    }

    /**
     * Bloco 1 -- mesmo pedido pago 2+ vezes. Criterio definitivo.
     * Traz a contagem de transacoes aprovadas no gateway ao lado, quando existe
     * (pagamento sem transacao_id nao casa -- e por isso existe o bloco 2).
     *
     * Conta ATIVADA e DEVOLVIDO, nao so ATIVADA: DEVOLVIDO significa que o
     * dinheiro ENTROU e voltou -- continua sendo uma cobranca paga duas vezes,
     * e e o caso mais importante de mostrar (foi o que originou esta tela).
     * CANCELADO e DESATIVADA ficam de fora de proposito: sao a tentativa que
     * nao vingou (cartao recusado, registro cancelado), entao pedido com
     * "1 cancelada + 1 aprovada" NAO e duplicidade.
     */
    public function carregarMesmoPedido($debug = false) {
        try {
            $sql = sprintf("
                SELECT p.pedido_id,
                       COUNT(*) AS qtd_pagamentos,
                       SUM(p.valor) AS total,
                       MIN(p.data_pagamento) AS primeira,
                       MAX(p.data_pagamento) AS ultima,
                       GROUP_CONCAT(CONCAT(p.cod_pagamento, ':', COALESCE(p.status,'')) ORDER BY p.cod_pagamento SEPARATOR ', ') AS pagamentos,
                       MAX(COALESCE(i.nome_inscrito, '')) AS nome,
                       MAX(p.cod_inscrito) AS cod_inscrito,
                       (SELECT COUNT(*)
                          FROM tab_pagamentos p2
                          JOIN pagseguro_transacoes t
                            ON t.codigo_transacao = REPLACE(p2.transacao_id, 'ORDE_', '')
                           AND t.status = 'Aprovada'
                         WHERE p2.cod_evento = p.cod_evento
                           AND p2.pedido_id = p.pedido_id) AS aprovadas_gateway
                  FROM tab_pagamentos p
                  LEFT JOIN tab_inscritos i ON i.cod_inscrito = p.cod_inscrito
                                           AND i.cod_evento = p.cod_evento
                 WHERE p.cod_evento = '%s'
                   AND p.pedido_id > 0
                   AND p.status IN ('ATIVADA','DEVOLVIDO')
                 GROUP BY p.pedido_id
                HAVING COUNT(*) > 1
                 ORDER BY total DESC
            ", COD_EVENTO);

            return $this->executarLista($sql, $debug);
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Bloco 2 -- mesmo inscrito e mesmo valor dentro de 7 dias.
     * Existe para o que o bloco 1 nao enxerga: pagamento sem pedido_id (boleto
     * Itau, PIX manual, carga). Aqui a chance de falso positivo e maior, por
     * isso vem separado, e nao misturado ao criterio definitivo.
     */
    public function carregarMesmoValorJanela($debug = false) {
        try {
            $sql = sprintf("
                SELECT a.cod_inscrito,
                       COALESCE(i.nome_inscrito, '') AS nome,
                       a.valor,
                       COUNT(*) AS qtd_pares,
                       GROUP_CONCAT(DISTINCT CONCAT(a.cod_pagamento, ' x ', b.cod_pagamento) SEPARATOR ', ') AS pares,
                       MIN(a.data_pagamento) AS primeira,
                       MAX(b.data_pagamento) AS ultima
                  FROM tab_pagamentos a
                  JOIN tab_pagamentos b
                    ON b.cod_evento = a.cod_evento
                   AND b.cod_inscrito = a.cod_inscrito
                   AND b.valor = a.valor
                   AND b.cod_pagamento > a.cod_pagamento
                   AND b.status IN ('ATIVADA','DEVOLVIDO')
                   AND ABS(DATEDIFF(b.data_pagamento, a.data_pagamento)) <= 7
                  LEFT JOIN tab_inscritos i ON i.cod_inscrito = a.cod_inscrito
                                           AND i.cod_evento = a.cod_evento
                 WHERE a.cod_evento = '%s'
                   AND a.status IN ('ATIVADA','DEVOLVIDO')
                   AND a.cod_inscrito IS NOT NULL
                   AND a.valor > 0
                   AND (a.pedido_id IS NULL OR a.pedido_id = 0
                        OR b.pedido_id IS NULL OR b.pedido_id = 0
                        OR a.pedido_id <> b.pedido_id)
                 GROUP BY a.cod_inscrito, a.valor
                 ORDER BY a.valor DESC
            ", COD_EVENTO);

            return $this->executarLista($sql, $debug);
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Bloco 3 -- rede de seguranca: quem pagou mais do que o esperado.
     * Nao depende de pedido repetido nem de janela de data; pega o caso que
     * escapou dos dois criterios acima.
     *
     * O "esperado" e a mesma conta da listagem de inscritos (InscritoDAO:232):
     * soma de `pedidos.valor_total` com status ATIVADA ou aguardando pagamento.
     * Nao existe valor esperado em tab_inscritos.
     */
    public function carregarPagouMaior($debug = false) {
        try {
            $sql = sprintf("
                SELECT p.cod_inscrito,
                       COALESCE(i.nome_inscrito, '') AS nome,
                       COUNT(*) AS qtd_pagamentos,
                       SUM(p.valor) AS total_pago,
                       ped.valor_total_pedidos AS esperado,
                       SUM(p.valor) - ped.valor_total_pedidos AS diferenca
                  FROM tab_pagamentos p
                  LEFT JOIN tab_inscritos i ON i.cod_inscrito = p.cod_inscrito
                                           AND i.cod_evento = p.cod_evento
                  JOIN (SELECT inscrito_id, evento_id, SUM(valor_total) AS valor_total_pedidos
                          FROM pedidos
                         WHERE status = 'ATIVADA' OR status = 'aguardando pagamento'
                         GROUP BY inscrito_id, evento_id) ped
                    ON ped.inscrito_id = p.cod_inscrito
                   AND ped.evento_id = p.cod_evento
                 WHERE p.cod_evento = '%s'
                   AND p.status = 'ATIVADA'
                   AND ped.valor_total_pedidos > 0
                 GROUP BY p.cod_inscrito, ped.valor_total_pedidos
                HAVING SUM(p.valor) > ped.valor_total_pedidos + 0.01
                 ORDER BY diferenca DESC
            ", COD_EVENTO);

            return $this->executarLista($sql, $debug);
        } catch (Exception $e) {
            return array();
        }
    }

    private function executarLista($sql, $debug = false) {
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        $lista = array();
        if ($rs && !$rs->EOF) {
            foreach ($rs as $rows) {
                $lista[] = $rows;
            }
        }
        return $lista;
    }

    /** Bloco 1: o unico com confirmacao do gateway. */
    public function carregarTabelaMesmoPedido($fws_, $debug = false) {
        $lista = $this->carregarMesmoPedido($debug);
        $ii = 0;
        foreach ($lista as $r) {
            $ii++;
            $aprovadas = intval($r['aprovadas_gateway']);
            ?>
            <tr>
                <td class="col-codigo">
                    <a href="<?php echo $this->linkLancamentos('pedido', $r['pedido_id']); ?>"
                       title="Ver os lançamentos deste pedido"><?php echo $r['pedido_id']; ?></a>
                </td>
                <td class="col-nome"><?php echo $r['nome']; ?></td>
                <td class="col-num"><?php echo $r['qtd_pagamentos']; ?></td>
                <td class="col-num">R$ <?php echo number_format(floatval($r['total']), 2, ',', '.'); ?></td>
                <td class="col-codigo"><?php echo $r['pagamentos']; ?></td>
                <td class="col-data"><?php echo $this->dataBr($r['primeira']); ?></td>
                <td class="col-data"><?php echo $this->dataBr($r['ultima']); ?></td>
                <td class="col-badge">
                    <?php if ($aprovadas > 1) { ?>
                        <span class="badge-tipo"><span class="dot dot-red"></span><?php echo $aprovadas; ?> aprovadas</span>
                    <?php } elseif ($aprovadas == 1) { ?>
                        <span class="badge-tipo"><span class="dot dot-green"></span>1 aprovada</span>
                    <?php } else { ?>
                        <span style="color:#999; font-size:12px;">sem transação</span>
                    <?php } ?>
                </td>
                <td class="col-badge">
                    <a class="btn btn-sm" href="<?php echo $this->linkLancamentos('pedido', $r['pedido_id']); ?>"
                       title="Abre a tela de Lançamentos já filtrada por este pedido">
                        <i class="fa fa-list-alt"></i> Lançamentos
                    </a>
                </td>
            </tr>
            <?php
        }
        if ($ii == 0) {
            echo "<tr><td colspan='9' style='padding:14px; color:#888;'>Nenhum pedido pago mais de uma vez neste evento.</td></tr>";
        }
    }

    /** Bloco 2: heuristica -- pode ter falso positivo, o texto avisa. */
    public function carregarTabelaMesmoValor($fws_, $debug = false) {
        $lista = $this->carregarMesmoValorJanela($debug);
        $ii = 0;
        foreach ($lista as $r) {
            $ii++;
            ?>
            <tr>
                <td class="col-nome"><?php echo $r['nome']; ?></td>
                <td class="col-num">R$ <?php echo number_format(floatval($r['valor']), 2, ',', '.'); ?></td>
                <td class="col-num"><?php echo $r['qtd_pares']; ?></td>
                <td class="col-codigo"><?php echo $r['pares']; ?></td>
                <td class="col-data"><?php echo $this->dataBr($r['primeira']); ?></td>
                <td class="col-data"><?php echo $this->dataBr($r['ultima']); ?></td>
                <td class="col-badge">
                    <a class="btn btn-sm" href="<?php echo $this->linkLancamentos('inscrito', $r['cod_inscrito']); ?>"
                       title="Abre a tela de Lançamentos já filtrada por este inscrito">
                        <i class="fa fa-list-alt"></i> Lançamentos
                    </a>
                </td>
            </tr>
            <?php
        }
        if ($ii == 0) {
            echo "<tr><td colspan='7' style='padding:14px; color:#888;'>Nenhum pagamento repetido de mesmo valor em até 7 dias.</td></tr>";
        }
    }

    /** Bloco 3: rede de seguranca. */
    public function carregarTabelaPagouMaior($fws_, $debug = false) {
        $lista = $this->carregarPagouMaior($debug);
        $ii = 0;
        foreach ($lista as $r) {
            $ii++;
            ?>
            <tr>
                <td class="col-nome"><?php echo $r['nome']; ?></td>
                <td class="col-num"><?php echo $r['qtd_pagamentos']; ?></td>
                <td class="col-num">R$ <?php echo number_format(floatval($r['esperado']), 2, ',', '.'); ?></td>
                <td class="col-num">R$ <?php echo number_format(floatval($r['total_pago']), 2, ',', '.'); ?></td>
                <td class="col-num" style="font-weight:600; color:#c0392b;">R$ <?php echo number_format(floatval($r['diferenca']), 2, ',', '.'); ?></td>
                <td class="col-badge">
                    <a class="btn btn-sm" href="<?php echo $this->linkLancamentos('inscrito', $r['cod_inscrito']); ?>"
                       title="Abre a tela de Lançamentos já filtrada por este inscrito">
                        <i class="fa fa-list-alt"></i> Lançamentos
                    </a>
                </td>
            </tr>
            <?php
        }
        if ($ii == 0) {
            echo "<tr><td colspan='6' style='padding:14px; color:#888;'>Ninguém pagou acima do valor esperado.</td></tr>";
        }
    }

    private function dataBr($data) {
        return $data ? date('d/m/Y', strtotime($data)) : '';
    }

    /**
     * Link para a tela de Lancamentos ja filtrada. A tela le esses parametros da
     * URL e aplica o recorte na abertura, mostrando um aviso com "ver todos".
     * $tipo: pedido | inscrito | pagamento
     */
    private function linkLancamentos($tipo, $valor) {
        return 'index.php?content=view/admin/GerenciarLancamentosContabeis.php&'
             . rawurlencode($tipo) . '=' . rawurlencode($valor);
    }
}
