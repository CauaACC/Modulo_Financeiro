<?php

class GerenciarRelatoriosFinanceirosAC {

    private $conex;

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        ConexaoDB::getInstance();
        $this->conex = ConexaoDB::getConexaoDB();
    }

    public function carregarResumoFinanceiro($fws_, $debug = false) {
        try {
            $codEvento = COD_EVENTO;
            $whereData = $this->montarWhereData($fws_);

            $sql = sprintf("
                SELECT
                    COALESCE(SUM(CASE WHEN flg_lancamento = 'C' THEN valor ELSE 0 END), 0) AS total_creditos,
                    COALESCE(SUM(CASE WHEN flg_lancamento = 'D' THEN valor ELSE 0 END), 0) AS total_debitos,
                    COALESCE(SUM(CASE WHEN flg_lancamento = 'C' THEN valor ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN flg_lancamento = 'D' THEN valor ELSE 0 END), 0) AS saldo,
                    COUNT(*) AS qtd_lancamentos,
                    COALESCE(SUM(CASE WHEN conciliado = 1 THEN 1 ELSE 0 END), 0) AS qtd_conciliados,
                    COALESCE(SUM(CASE WHEN conciliado = 0 OR conciliado IS NULL THEN 1 ELSE 0 END), 0) AS qtd_nao_conciliados
                FROM fin_lancamentos
                WHERE cod_evento = '%s' %s
            ", $codEvento, $whereData);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return [
                    'total_creditos' => 0,
                    'total_debitos' => 0,
                    'saldo' => 0,
                    'qtd_lancamentos' => 0,
                    'qtd_conciliados' => 0,
                    'qtd_nao_conciliados' => 0
                ];
            }

            foreach ($rs as $rows) {}
            return $rows;

        } catch (ADODB_Exception $e) {
            return [
                'total_creditos' => 0,
                'total_debitos' => 0,
                'saldo' => 0,
                'qtd_lancamentos' => 0,
                'qtd_conciliados' => 0,
                'qtd_nao_conciliados' => 0
            ];
        }
    }

    public function carregarLancamentosPorTipo($fws_, $debug = false) {
        try {
            $codEvento = COD_EVENTO;
            $whereData = $this->montarWhereData($fws_);

            $sql = sprintf("
                SELECT
                    tipo_lancamento,
                    flg_lancamento,
                    COUNT(*) AS qtd,
                    COALESCE(SUM(valor), 0) AS total_valor
                FROM fin_lancamentos
                WHERE cod_evento = '%s' %s
                GROUP BY tipo_lancamento, flg_lancamento
                ORDER BY tipo_lancamento
            ", $codEvento, $whereData);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $result = [];
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $result[] = $rows;
                }
            }
            return $result;

        } catch (ADODB_Exception $e) {
            return [];
        }
    }

    public function carregarLancamentosPorPlanoContas($fws_, $debug = false) {
        try {
            $codEvento = COD_EVENTO;
            $whereData = $this->montarWhereData($fws_);

            $sql = sprintf("
                SELECT
                    fpc.cod_plano_contas,
                    fpc.codigo_financeiro,
                    fpc.nome_plano_contas,
                    fpc.tipo_conta,
                    fpc.natureza_saldo,
                    COUNT(flc.cod_lancamentos) AS qtd,
                    COALESCE(SUM(flc.valor), 0) AS total_valor
                FROM fin_plano_contas fpc
                LEFT JOIN fin_lancamentos flc
                    ON flc.cod_plano_contas = fpc.cod_plano_contas
                    AND flc.cod_evento = fpc.cod_evento
                    %s
                WHERE fpc.cod_evento = '%s'
                    AND fpc.aceita_lancamento = 1
                GROUP BY fpc.cod_plano_contas, fpc.codigo_financeiro, fpc.nome_plano_contas, fpc.tipo_conta, fpc.natureza_saldo
                HAVING qtd > 0
                ORDER BY fpc.codigo_financeiro
            ", $this->montarWhereDataAlias($fws_, 'flc'), $codEvento);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $result = [];
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $result[] = $rows;
                }
            }
            return $result;

        } catch (ADODB_Exception $e) {
            return [];
        }
    }

    public function carregarTabelaLancamentos($fws_, $debug = false) {
        try {
            $codEvento = COD_EVENTO;
            $whereData = $this->montarWhereData($fws_);
            $whereTipo = '';
            $whereConciliado = '';

            if (!empty($fws_['filtroTipo'])) {
                $whereTipo = sprintf(" AND tipo_lancamento = '%s'", $fws_['filtroTipo']);
            }
            if ($fws_['filtroConciliado'] !== '' && $fws_['filtroConciliado'] !== null && isset($fws_['filtroConciliado'])) {
                $whereConciliado = sprintf(" AND conciliado = '%s'", $fws_['filtroConciliado']);
            }

            $sql = sprintf("
                SELECT flc.*, fpc.nome_plano_contas, fpc.codigo_financeiro
                FROM fin_lancamentos flc
                LEFT JOIN fin_plano_contas fpc ON fpc.cod_plano_contas = flc.cod_plano_contas AND fpc.cod_evento = flc.cod_evento
                WHERE flc.cod_evento = '%s' %s %s %s
                ORDER BY flc.data_competencia DESC, flc.cod_lancamentos DESC
            ", $codEvento, $whereData, $whereTipo, $whereConciliado);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $ii = 0;
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $ii++;
                    $tipo = $rows['tipo_lancamento'];
                    $dotTipo = 'dot-blue';
                    if ($tipo == 'VENDA') $dotTipo = 'dot-green';
                    if ($tipo == 'DESPESA_PAGA') $dotTipo = 'dot-red';
                    if ($tipo == 'PAGAMENTO_FORNECEDOR') $dotTipo = 'dot-orange';

                    $concVal = $rows['conciliado'];
                    $concTxt = ($concVal == 1) ? 'SIM' : 'N&Atilde;O';
                    $dotConc = ($concVal == 1) ? 'dot-green' : 'dot-red';

                    $flgTxt = ($rows['flg_lancamento'] == 'C') ? 'CR&Eacute;DITO' : 'D&Eacute;BITO';
                    $dotFlg = ($rows['flg_lancamento'] == 'C') ? 'dot-green' : 'dot-red';
                    ?>
                    <tr>
                        <td class="col-data"><?php echo formatDateView($rows['data_competencia']); ?></td>
                        <td class="col-data"><?php echo formatDateView($rows['data_financeira']); ?></td>
                        <td><span class="badge-tipo"><span class="dot <?php echo $dotTipo; ?>"></span><?php echo $tipo; ?></span></td>
                        <td><span class="badge-tipo"><span class="dot <?php echo $dotFlg; ?>"></span><?php echo $flgTxt; ?></span></td>
                        <td class="col-nome" style="text-align:right;">R$ <?php echo number_format($rows['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo $rows['codigo_financeiro'] . ' - ' . $rows['nome_plano_contas']; ?></td>
                        <td><?php echo $rows['origem_lancamento']; ?></td>
                        <td><?php echo $rows['historico']; ?></td>
                        <td><span class="badge-tipo"><span class="dot <?php echo $dotConc; ?>"></span><?php echo $concTxt; ?></span></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody><tfoot><tr><td colspan="9" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot><tbody class="inputs" style="display:none;">
            <?php

        } catch (Exception $e) {
            echo "<tr><td colspan='9'>";
            echo $e->getMessage() ? $e->getMessage() : "Nenhum lan&ccedil;amento encontrado.";
            echo "</td></tr>";
        }
    }

    public function carregarTabelaRateios($fws_, $debug = false) {
        try {
            $obj = new Rateios();
            $obj->carregarListaRateios(false, $debug);
            $lista = $obj->getListaRateios();
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr>
                    <td><?php echo $objL->getCodigoFinanceiroRateio(); ?></td>
                    <td class="col-nome"><?php echo $objL->getNomeRateio(); ?></td>
                    <td style="text-align:right;"><?php echo $objL->getPercentual(); ?>%</td>
                    <td><?php echo $objL->getDescricao(); ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody><tfoot><tr><td colspan="4" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot><tbody class="inputs" style="display:none;">
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>";
            echo $e->getMessage() ? $e->getMessage() : "Nenhum rateio cadastrado.";
            echo "</td></tr>";
        }
    }

    public function carregarTabelaCentroCusto($fws_, $debug = false) {
        try {
            $obj = new CentroCusto();
            $obj->carregarListaCentroCusto(false, $debug);
            $lista = $obj->getListaCentroCusto();
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr>
                    <td><?php echo $objL->getCodigoFinanceiro(); ?></td>
                    <td class="col-nome"><?php echo $objL->getNomeCentroCusto(); ?></td>
                    <td><?php echo $objL->getDescricao(); ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody><tfoot><tr><td colspan="3" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot><tbody class="inputs" style="display:none;">
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='3'>";
            echo $e->getMessage() ? $e->getMessage() : "Nenhum centro de custo cadastrado.";
            echo "</td></tr>";
        }
    }

    public function carregarTabelaFontesRecurso($fws_, $debug = false) {
        try {
            $obj = new FontesRecurso();
            $obj->carregarListaFontesRecurso(false, $debug);
            $lista = $obj->getListaFontesRecurso();
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr>
                    <td class="col-nome"><?php echo $objL->getNomeFontesRecurso(); ?></td>
                    <td><?php echo $objL->getDescricao(); ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody><tfoot><tr><td colspan="2" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot><tbody class="inputs" style="display:none;">
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='2'>";
            echo $e->getMessage() ? $e->getMessage() : "Nenhuma fonte de recurso cadastrada.";
            echo "</td></tr>";
        }
    }

    public function carregarTabelaPlanoContas($fws_, $debug = false) {
        try {
            $obj = new PlanoContas();
            $obj->carregarListaPlanoContas(false, $debug);
            $lista = $obj->getListaPlanoContas();
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;
                $tipoConta = $objL->getTipoConta();
                $dotCor = 'dot-blue';
                if ($tipoConta == 'RECEITA') $dotCor = 'dot-green';
                if ($tipoConta == 'DESPESA') $dotCor = 'dot-red';
                if ($tipoConta == 'ATIVO') $dotCor = 'dot-blue';
                if ($tipoConta == 'PASSIVO') $dotCor = 'dot-orange';
                if ($tipoConta == 'PATRIMONIO_LIQUIDO') $dotCor = 'dot-purple';
                ?>
                <tr>
                    <td><?php echo $objL->getCodigoFinanceiro(); ?></td>
                    <td class="col-nome"><?php echo $objL->getNomePlanoContas(); ?></td>
                    <td><span class="badge-tipo"><span class="dot <?php echo $dotCor; ?>"></span><?php echo $tipoConta; ?></span></td>
                    <td><?php echo $objL->getNaturezaSaldo(); ?></td>
                    <td><?php $objL->getDesAceitaLancamento(); ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody><tfoot><tr><td colspan="5" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot><tbody class="inputs" style="display:none;">
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='5'>";
            echo $e->getMessage() ? $e->getMessage() : "Nenhum plano de contas cadastrado.";
            echo "</td></tr>";
        }
    }

    public function carregarDadosBalancete($fws_, $debug = false) {
        try {
            $codEvento = COD_EVENTO;
            $whereData = $this->montarWhereDataAlias($fws_, 'flc');

            $sql = sprintf("
                SELECT
                    fpc.cod_plano_contas,
                    fpc.codigo_financeiro,
                    fpc.nome_plano_contas,
                    fpc.tipo_conta,
                    fpc.natureza_saldo,
                    fpc.cod_plano_contas_pai,
                    fpc.aceita_lancamento,
                    COALESCE(SUM(CASE WHEN flc.flg_lancamento = 'D' THEN flc.valor ELSE 0 END), 0) AS total_debito,
                    COALESCE(SUM(CASE WHEN flc.flg_lancamento = 'C' THEN flc.valor ELSE 0 END), 0) AS total_credito
                FROM fin_plano_contas fpc
                LEFT JOIN fin_lancamentos flc
                    ON flc.cod_plano_contas = fpc.cod_plano_contas
                    AND flc.cod_evento = fpc.cod_evento
                    %s
                WHERE fpc.cod_evento = '%s'
                GROUP BY fpc.cod_plano_contas, fpc.codigo_financeiro, fpc.nome_plano_contas,
                         fpc.tipo_conta, fpc.natureza_saldo, fpc.cod_plano_contas_pai, fpc.aceita_lancamento
                ORDER BY fpc.codigo_financeiro
            ", $whereData, $codEvento);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $contas = [];
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $rows['total_debito'] = floatval($rows['total_debito']);
                    $rows['total_credito'] = floatval($rows['total_credito']);
                    $contas[] = $rows;
                }
            }

            // Indexar por cod_plano_contas
            $indexado = [];
            foreach ($contas as $i => $c) {
                $indexado[$c['cod_plano_contas']] = $i;
            }

            // Agregar filhos nos pais (de baixo pra cima)
            for ($i = count($contas) - 1; $i >= 0; $i--) {
                $c = $contas[$i];
                if (!empty($c['cod_plano_contas_pai']) && isset($indexado[$c['cod_plano_contas_pai']])) {
                    $parentIdx = $indexado[$c['cod_plano_contas_pai']];
                    $contas[$parentIdx]['total_debito'] += $c['total_debito'];
                    $contas[$parentIdx]['total_credito'] += $c['total_credito'];
                }
            }

            return $contas;

        } catch (ADODB_Exception $e) {
            return [];
        }
    }

    /**
     * Receita que veio de pagamento com inscricao CANCELADA/DEVOLVIDA no sistema.
     *
     * Financeiramente e receita normal (o dinheiro entrou), por isso fica somada
     * nas rubricas -- nao existe rubrica de "cancelamento". Mas para o
     * coordenador isso e RISCO: parte do saldo pode ser reclamada de volta.
     * Dai a ressalva no rodape, e nao uma linha no corpo do balancete.
     */
    public function totalCanceladosRecebidos($fws_, $debug = false) {
        try {
            $whereData = $this->montarWhereDataAlias($fws_, 'flc');
            $sql = sprintf("
                SELECT COUNT(DISTINCT flc.cod_pagamento) AS qtd,
                       COALESCE(SUM(flc.valor), 0) AS total
                  FROM fin_lancamentos flc
                 INNER JOIN tab_pagamentos p ON p.cod_pagamento = flc.cod_pagamento
                                            AND p.cod_evento = flc.cod_evento
                 WHERE flc.cod_evento = '%s'
                   AND flc.flg_lancamento = 'C'
                   AND p.status IN ('CANCELADO','DEVOLVIDO') %s
            ", COD_EVENTO, $whereData);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                return array('qtd' => intval($rs->fields['qtd']), 'total' => floatval($rs->fields['total']));
            }
        } catch (ADODB_Exception $e) {
            // ressalva e informativa: se falhar, o balancete segue normal
        }
        return array('qtd' => 0, 'total' => 0);
    }

    public function carregarTabelaBalancete($fws_, $debug = false) {
        try {
            $dados = $this->carregarDadosBalancete($fws_, $debug);

            $totalDebito = 0;
            $totalCredito = 0;
            $ii = 0;
            // Balancete de resultado = so contas de RECEITA/DESPESA (padrao dos projetos,
            // igual ao balancete da HUTEC). Nesse caso a diferenca D x C nao e erro de
            // fechamento: e o resultado do periodo (superavit/deficit).
            $ehResultado = true;
            foreach ($dados as $c) {
                if ($c['tipo_conta'] != 'RECEITA' && $c['tipo_conta'] != 'DESPESA') {
                    if ($c['total_debito'] != 0 || $c['total_credito'] != 0) {
                        $ehResultado = false;
                    }
                }
            }

            foreach ($dados as $c) {
                $ehPai = empty($c['cod_plano_contas_pai']);

                // Pular contas sem movimentacao
                if ($c['total_debito'] == 0 && $c['total_credito'] == 0) {
                    continue;
                }

                $ii++;

                // Linha em branco antes de cada novo grupo (conta-pai), menos o primeiro,
                // pra separar visualmente um agrupamento do outro
                if ($ehPai && $ii > 1) {
                    echo '<tr class="balancete-spacer"><td colspan="6"></td></tr>';
                }

                // Somar apenas contas raiz para o total geral (ja incluem filhos agregados)
                if ($ehPai) {
                    $totalDebito += $c['total_debito'];
                    $totalCredito += $c['total_credito'];
                }

                $saldo = $c['total_debito'] - $c['total_credito'];
                $classeHierarquia = $ehPai ? 'balancete-pai' : 'balancete-filho';

                $dotCor = 'dot-blue';
                if ($c['tipo_conta'] == 'RECEITA') $dotCor = 'dot-green';
                if ($c['tipo_conta'] == 'DESPESA') $dotCor = 'dot-red';
                if ($c['tipo_conta'] == 'PASSIVO') $dotCor = 'dot-orange';
                if ($c['tipo_conta'] == 'PATRIMONIO_LIQUIDO') $dotCor = 'dot-purple';
                ?>
                <?php
                // Conta que recebe lancamento vira linha clicavel: leva ao Razao
                // (o extrato dela) sem sair do relatorio, mantendo os filtros.
                $ehDetalhavel = !empty($c['aceita_lancamento']);
                ?>
                <tr class="<?php echo $classeHierarquia; ?>"
                    <?php if ($ehDetalhavel) { ?>
                        data-conta="<?php echo $c['cod_plano_contas']; ?>"
                        style="cursor:pointer;"
                        title="Ver o extrato desta conta no Razão"
                    <?php } ?>>
                    <td>
                        <?php if (!$ehPai) { ?><span class="tree-indent"></span><?php } ?>
                        <?php echo $c['codigo_financeiro']; ?>
                    </td>
                    <td class="col-nome">
                        <?php if (!$ehPai) { ?><span class="tree-connector">└</span><?php } ?>
                        <?php echo $c['nome_plano_contas']; ?>
                        <?php if ($ehDetalhavel) { ?>
                            <i class="fa fa-search" style="margin-left:6px; font-size:11px; color:#bbb;"></i>
                        <?php } ?>
                    </td>
                    <td><span class="badge-tipo"><span class="dot <?php echo $dotCor; ?>"></span><?php echo $c['tipo_conta']; ?></span></td>
                    <td style="text-align:right;">R$ <?php echo number_format($c['total_debito'], 2, ',', '.'); ?></td>
                    <td style="text-align:right;">R$ <?php echo number_format($c['total_credito'], 2, ',', '.'); ?></td>
                    <td style="text-align:right; font-weight:600;">
                        R$ <?php echo number_format(abs($saldo), 2, ',', '.'); ?>
                        <span style="font-size:11px; color:#999;"><?php echo $saldo >= 0 ? 'D' : 'C'; ?></span>
                    </td>
                </tr>
                <?php
            }

            $diferenca = $totalDebito - $totalCredito;
            ?>
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid #333; font-weight:700; font-size:13px;">
                    <td colspan="3" style="padding:10px;">TOTAIS</td>
                    <td style="text-align:right; padding:10px;">R$ <?php echo number_format($totalDebito, 2, ',', '.'); ?></td>
                    <td style="text-align:right; padding:10px;">R$ <?php echo number_format($totalCredito, 2, ',', '.'); ?></td>
                    <?php
                    // No balancete de resultado o saldo positivo e superavit (credito > despesa).
                    $resultado = $totalCredito - $totalDebito;
                    $corTotal = $ehResultado
                        ? ($resultado >= 0 ? '#27ae60' : '#e74c3c')
                        : (abs($diferenca) < 0.01 ? '#27ae60' : '#e74c3c');
                    ?>
                    <td style="text-align:right; padding:10px; font-weight:700; color:<?php echo $corTotal; ?>;">
                        R$ <?php echo number_format(abs($diferenca), 2, ',', '.'); ?>
                        <?php if (abs($diferenca) >= 0.01) { ?>
                            <span style="font-size:11px;"><?php echo $diferenca >= 0 ? 'D' : 'C'; ?></span>
                        <?php } ?>
                    </td>
                </tr>
                <?php if ($ehResultado) { ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:<?php echo $resultado >= 0 ? '#27ae60' : '#e74c3c'; ?>; font-weight:600; padding:10px; font-size:12px;">
                        <i class="fa fa-<?php echo $resultado >= 0 ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        Resultado do per&iacute;odo: R$ <?php echo number_format(abs($resultado), 2, ',', '.'); ?>
                        <?php echo $resultado >= 0 ? '(super&aacute;vit)' : '(d&eacute;ficit)'; ?>
                        &mdash; receitas menos despesas.
                    </td>
                </tr>
                <?php } elseif (abs($diferenca) < 0.01) { ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#27ae60; font-weight:600; padding:10px; font-size:12px;">
                        <i class="fa fa-check-circle"></i> Balancete equilibrado &mdash; D&eacute;bitos e Cr&eacute;ditos conferem.
                    </td>
                </tr>
                <?php } else { ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#e74c3c; font-weight:600; padding:10px; font-size:12px;">
                        <i class="fa fa-exclamation-triangle"></i> Diferen&ccedil;a de R$ <?php echo number_format(abs($diferenca), 2, ',', '.'); ?> entre D&eacute;bitos e Cr&eacute;ditos.
                    </td>
                </tr>
                <?php } ?>
                <?php
                // Ressalva: receita de inscricao cancelada (ver totalCanceladosRecebidos)
                $canc = $this->totalCanceladosRecebidos($fws_, $debug);
                if ($canc['qtd'] > 0) { ?>
                <tr>
                    <td colspan="6" style="padding:10px; font-size:12px; color:#8a6d3b; background:#fdf6e3; border-top:1px solid #f0e0b0;">
                        <i class="fa fa-info-circle" style="margin-right:6px;"></i>
                        <strong>Ressalva:</strong> R$ <?php echo number_format($canc['total'], 2, ',', '.'); ?>
                        da receita acima <?php echo $canc['qtd'] == 1 ? 'corresponde a 1 pagamento' : 'correspondem a ' . $canc['qtd'] . ' pagamentos'; ?>
                        com inscri&ccedil;&atilde;o cancelada ou devolvida no sistema.
                        Confira no gateway se cada um liquidou: se o dinheiro entrou, &eacute; receita
                        &mdash; mas pode virar pedido de devolu&ccedil;&atilde;o; se n&atilde;o entrou, o lan&ccedil;amento n&atilde;o deveria existir.
                    </td>
                </tr>
                <?php } ?>
                <tr><td colspan="6" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> conta<?php echo $ii != 1 ? 's' : '' ?> com movimenta&ccedil;&atilde;o</td></tr>
            </tfoot>
            <tbody class="inputs" style="display:none;">
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='6'>";
            echo $e->getMessage() ? $e->getMessage() : "Nenhum dado para o balancete.";
            echo "</td></tr>";
        }
    }

    /**
     * Razao: o extrato de uma conta. O balancete mostra o saldo de cada rubrica;
     * aqui se ve o que formou aquele saldo, lancamento a lancamento, com o saldo
     * correndo linha a linha.
     */
    public function carregarTabelaRazao($fws_, $debug = false) {
        try {
            $codEvento = COD_EVENTO;
            $codConta = isset($fws_['codPlanoContasRazao']) ? intval($fws_['codPlanoContasRazao']) : 0;
            if (!$codConta) {
                echo "<tr><td colspan='7' style='padding:14px; color:#888;'>Escolha uma conta para ver o extrato.</td></tr>";
                return;
            }
            $whereData = $this->montarWhereDataAlias($fws_, 'flc');

            $sql = sprintf("
                SELECT flc.data_competencia, flc.data_financeira, flc.flg_lancamento, flc.valor,
                       flc.historico, flc.num_documento, flc.origem_lancamento, flc.conciliado,
                       cc.nome_centro_custo,
                       COALESCE(i.nome_inscrito, flc.favorecido) AS favorecido
                  FROM fin_lancamentos flc
                  LEFT JOIN fin_centro_custo cc ON cc.cod_centro_custo = flc.cod_centro_custo
                  LEFT JOIN tab_inscritos i ON i.cod_inscrito = flc.cod_inscrito AND i.cod_evento = flc.cod_evento
                 WHERE flc.cod_evento = '%s' AND flc.cod_plano_contas = '%s' %s
                 ORDER BY flc.data_competencia, flc.cod_lancamentos
            ", $codEvento, $codConta, $whereData);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $saldo = 0;
            $totalD = 0;
            $totalC = 0;
            $ii = 0;
            if ($rs) {
                foreach ($rs as $r) {
                    $ii++;
                    $ehDebito = ($r['flg_lancamento'] == 'D');
                    $valor = floatval($r['valor']);
                    if ($ehDebito) { $totalD += $valor; $saldo += $valor; }
                    else           { $totalC += $valor; $saldo -= $valor; }
                    ?>
                    <tr>
                        <td class="col-data"><?php echo $r['data_competencia'] ? date('d/m/Y', strtotime($r['data_competencia'])) : ''; ?></td>
                        <td class="col-nome"><?php echo $r['favorecido']; ?></td>
                        <td><?php echo $r['historico']; ?></td>
                        <td class="col-codigo"><?php echo $r['num_documento']; ?></td>
                        <td class="col-num"><?php echo $ehDebito ? 'R$ ' . number_format($valor, 2, ',', '.') : ''; ?></td>
                        <td class="col-num"><?php echo !$ehDebito ? 'R$ ' . number_format($valor, 2, ',', '.') : ''; ?></td>
                        <td class="col-num" style="font-weight:600;">
                            R$ <?php echo number_format(abs($saldo), 2, ',', '.'); ?>
                            <span style="font-size:11px; color:#999;"><?php echo $saldo >= 0 ? 'D' : 'C'; ?></span>
                        </td>
                    </tr>
                    <?php
                }
            }
            if ($ii == 0) {
                echo "<tr><td colspan='7' style='padding:14px; color:#888;'>Nenhum lan&ccedil;amento nesta conta no per&iacute;odo.</td></tr>";
            }
            ?>
            <tr style="border-top:2px solid #333; font-weight:700;">
                <td colspan="4" style="padding:10px;">TOTAIS</td>
                <td class="col-num" style="padding:10px;">R$ <?php echo number_format($totalD, 2, ',', '.'); ?></td>
                <td class="col-num" style="padding:10px;">R$ <?php echo number_format($totalC, 2, ',', '.'); ?></td>
                <td class="col-num" style="padding:10px;">
                    R$ <?php echo number_format(abs($saldo), 2, ',', '.'); ?>
                    <span style="font-size:11px;"><?php echo $saldo >= 0 ? 'D' : 'C'; ?></span>
                </td>
            </tr>
            <tr><td colspan="7" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> lan&ccedil;amento<?php echo $ii != 1 ? 's' : '' ?></td></tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='7'>";
            echo $e->getMessage() ? $e->getMessage() : "Nenhum lan&ccedil;amento encontrado.";
            echo "</td></tr>";
        }
    }

    /**
     * Exporta o balancete em Excel no mesmo formato do arquivo da fundacao:
     * cabecalho com o projeto e o periodo, os grupos 01 RECEITAS / 02 DESPESAS
     * com suas rubricas, os lancamentos detalhados sob cada rubrica e os
     * totais no rodape.
     */
    public function exportarBalancete($fws_, $debug = false) {
        $autoload = dirname(__FILE__) . '/../lib/spreadsheet/vendor/autoload.php';
        if (!file_exists($autoload)) {
            throw new Exception("Biblioteca PhpSpreadsheet nao encontrada no servidor.");
        }
        require_once($autoload);

        $contas   = $this->carregarDadosBalancete($fws_, $debug);
        $detalhes = $this->carregarDetalhePorConta($fws_, $debug);
        $cab      = $this->carregarCabecalhoBalancete();

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('Balancete');

        $larguras = array('A' => 12, 'B' => 12, 'C' => 40, 'D' => 36, 'E' => 14, 'F' => 14, 'G' => 15);
        foreach ($larguras as $col => $w) {
            $sh->getColumnDimension($col)->setWidth($w);
        }

        $l = 1;
        // ------------------------------------------------------------ cabecalho
        $sh->setCellValue('A' . $l, 'BALANCETE GERENCIAL POR CENTRO DE CUSTO');
        $sh->mergeCells('A' . $l . ':D' . $l);
        $sh->setCellValue('E' . $l, $cab['gestor']);
        $sh->mergeCells('E' . $l . ':G' . $l);
        $sh->getStyle('A' . $l . ':G' . $l)->getFont()->setBold(true)->setSize(12);
        $l++;

        $sh->setCellValue('A' . $l, $cab['evento']);
        $sh->mergeCells('A' . $l . ':D' . $l);
        $sh->setCellValue('E' . $l, 'Emitido em ' . date('d/m/Y H:i'));
        $sh->mergeCells('E' . $l . ':G' . $l);
        $l++;

        $periodo  = 'Periodo: ';
        $periodo .= !empty($fws_['dataInicio']) ? date('d/m/Y', strtotime($fws_['dataInicio'])) : '(inicio)';
        $periodo .= ' a ';
        $periodo .= !empty($fws_['dataFim']) ? date('d/m/Y', strtotime($fws_['dataFim'])) : '(fim)';
        if ($cab['centro']) {
            $periodo .= '     Centro de Custo: ' . $cab['centro'];
            if ($cab['centro_codigo']) {
                $periodo .= ' (' . $cab['centro_codigo'] . ')';
            }
        }
        $sh->setCellValue('A' . $l, $periodo);
        $sh->mergeCells('A' . $l . ':G' . $l);
        $sh->getStyle('A' . $l)->getFont()->setSize(9);
        $l += 2;

        // --------------------------------------------------------- cabecalho da grade
        $inicioGrade = $l;
        $sh->setCellValue('A' . $l, 'CODIGO');
        $sh->setCellValue('B' . $l, 'DATA');
        $sh->setCellValue('C' . $l, 'CONTA / FAVORECIDO');
        $sh->setCellValue('D' . $l, 'HISTORICO');
        $sh->setCellValue('E' . $l, 'CREDITO');
        $sh->setCellValue('F' . $l, 'DEBITO');
        $sh->setCellValue('G' . $l, 'SALDO');
        $sh->getStyle('A' . $l . ':G' . $l)->applyFromArray(array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'fill' => array('fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('rgb' => '2E5C8A')),
        ));
        $sh->freezePane('A' . ($l + 1));
        $l++;

        // ---------------------------------------------------------------- corpo
        $totalD = 0;
        $totalC = 0;
        foreach ($contas as $c) {
            if ($c['total_debito'] == 0 && $c['total_credito'] == 0) {
                continue;   // conta sem movimento nao entra, como no arquivo original
            }
            $ehGrupo = empty($c['cod_plano_contas_pai']);
            if ($ehGrupo) {
                $totalD += $c['total_debito'];
                $totalC += $c['total_credito'];
            }

            $sh->setCellValue('A' . $l, $c['codigo_financeiro']);
            $sh->setCellValue('C' . $l, ($ehGrupo ? '' : '    ') . $this->paraUtf8($c['nome_plano_contas']));
            if ($c['total_credito'] > 0) { $sh->setCellValue('E' . $l, floatval($c['total_credito'])); }
            if ($c['total_debito'] > 0)  { $sh->setCellValue('F' . $l, floatval($c['total_debito'])); }
            $sh->setCellValue('G' . $l, floatval($c['total_credito']) - floatval($c['total_debito']));
            $sh->getStyle('A' . $l . ':G' . $l)->applyFromArray(array(
                'font' => array('bold' => true),
                'fill' => array('fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => array('rgb' => $ehGrupo ? 'DCE6F1' : 'F2F2F2')),
            ));
            $l++;

            // os lancamentos que formaram o saldo daquela rubrica
            if (!empty($detalhes[$c['cod_plano_contas']])) {
                foreach ($detalhes[$c['cod_plano_contas']] as $d) {
                    $sh->setCellValue('A' . $l, $d['num_documento']);
                    $sh->setCellValue('B' . $l, $d['data_competencia'] ? date('d/m/Y', strtotime($d['data_competencia'])) : '');
                    $sh->setCellValue('C' . $l, '        ' . $this->paraUtf8($d['favorecido']));
                    $sh->setCellValue('D' . $l, $this->paraUtf8($d['historico']));
                    $sh->setCellValue($d['flg_lancamento'] == 'C' ? 'E' . $l : 'F' . $l, floatval($d['valor']));
                    $l++;
                }
            }
        }

        // --------------------------------------------------------------- totais
        $l++;
        $sh->setCellValue('C' . $l, 'TOTAL GERAL');
        $sh->setCellValue('E' . $l, $totalC);
        $sh->setCellValue('F' . $l, $totalD);
        $sh->setCellValue('G' . $l, $totalC - $totalD);
        $sh->getStyle('A' . $l . ':G' . $l)->applyFromArray(array(
            'font' => array('bold' => true),
            'borders' => array('top' => array('borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE)),
        ));
        $l++;
        $resultado = $totalC - $totalD;
        $sh->setCellValue('C' . $l, 'Resultado do periodo: ' . ($resultado >= 0 ? 'superavit' : 'deficit'));
        $sh->mergeCells('C' . $l . ':D' . $l);
        $sh->setCellValue('G' . $l, abs($resultado));

        $sh->getStyle('E' . $inicioGrade . ':G' . $l)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');

        // O resumo viaja junto com o balancete: e o mesmo arquivo que vai para a
        // fundacao e para a prestacao de contas, entao o contexto tem que ir na
        // mesma pasta -- planilha separada se perde ou chega desatualizada.
        $this->abaResumo($ss, $fws_, $cab, $debug);
        $ss->setActiveSheetIndex(0);

        $nome = 'balancete-' . COD_EVENTO . '-' . date('Ymd-Hi') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nome . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);
        $writer->save('php://output');
        exit();
    }

    /**
     * Segunda aba da planilha do balancete: o mesmo Resumo da tela (totais do
     * periodo, lancamentos por tipo e por plano de contas), para quem recebe o
     * arquivo entender os numeros sem precisar de um segundo anexo.
     */
    private function abaResumo($ss, $fws_, $cab, $debug = false) {
        $resumo   = $this->carregarResumoFinanceiro($fws_, $debug);
        $porTipo  = $this->carregarLancamentosPorTipo($fws_, $debug);
        $porPlano = $this->carregarLancamentosPorPlanoContas($fws_, $debug);

        $sh = $ss->createSheet();
        $sh->setTitle('Resumo');

        foreach (array('A' => 14, 'B' => 44, 'C' => 16, 'D' => 12, 'E' => 16) as $col => $w) {
            $sh->getColumnDimension($col)->setWidth($w);
        }

        $tituloBloco = function($sh, $l, $texto) {
            $sh->setCellValue('A' . $l, $texto);
            $sh->mergeCells('A' . $l . ':E' . $l);
            $sh->getStyle('A' . $l)->getFont()->setBold(true)->setSize(11);
        };
        $cabecalhoGrade = function($sh, $l, $cols) {
            foreach ($cols as $col => $texto) {
                $sh->setCellValue($col . $l, $texto);
            }
            $sh->getStyle('A' . $l . ':E' . $l)->applyFromArray(array(
                'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
                'fill' => array('fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => array('rgb' => '2E5C8A')),
            ));
        };

        $l = 1;
        $sh->setCellValue('A' . $l, 'RESUMO FINANCEIRO');
        $sh->mergeCells('A' . $l . ':E' . $l);
        $sh->getStyle('A' . $l)->getFont()->setBold(true)->setSize(12);
        $l++;
        $sh->setCellValue('A' . $l, $cab['evento']);
        $sh->mergeCells('A' . $l . ':C' . $l);
        $sh->setCellValue('D' . $l, 'Emitido em ' . date('d/m/Y H:i'));
        $sh->mergeCells('D' . $l . ':E' . $l);
        $l += 2;

        // ---------------------------------------------------------- totais
        $tituloBloco($sh, $l, 'TOTAIS DO PERIODO');
        $l++;
        $linhas = array(
            array('Total de creditos', floatval($resumo['total_creditos'])),
            array('Total de debitos',  floatval($resumo['total_debitos'])),
            array('Saldo',             floatval($resumo['saldo'])),
        );
        foreach ($linhas as $ln) {
            $sh->setCellValue('B' . $l, $ln[0]);
            $sh->setCellValue('C' . $l, $ln[1]);
            $sh->getStyle('C' . $l)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
            $l++;
        }
        $sh->setCellValue('B' . $l, 'Lancamentos');
        $sh->setCellValue('C' . $l, intval($resumo['qtd_lancamentos']));
        $l++;
        $sh->setCellValue('B' . $l, 'Conciliados / pendentes');
        $sh->setCellValue('C' . $l, intval($resumo['qtd_conciliados']) . ' / ' . intval($resumo['qtd_nao_conciliados']));
        $l += 2;

        // ------------------------------------------------------- por tipo
        $tituloBloco($sh, $l, 'LANCAMENTOS POR TIPO');
        $l++;
        $cabecalhoGrade($sh, $l, array('A' => 'TIPO', 'B' => 'NATUREZA', 'C' => 'QUANTIDADE', 'D' => '', 'E' => 'VALOR TOTAL'));
        $l++;
        foreach ($porTipo as $t) {
            $sh->setCellValue('A' . $l, $t['tipo_lancamento']);
            $sh->setCellValue('B' . $l, $t['flg_lancamento'] == 'C' ? 'Credito' : 'Debito');
            $sh->setCellValue('C' . $l, intval($t['qtd']));
            $sh->setCellValue('E' . $l, floatval($t['total_valor']));
            $sh->getStyle('E' . $l)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
            $l++;
        }
        $l++;

        // --------------------------------------------- por plano de contas
        $tituloBloco($sh, $l, 'LANCAMENTOS POR PLANO DE CONTAS');
        $l++;
        $cabecalhoGrade($sh, $l, array('A' => 'CODIGO', 'B' => 'CONTA', 'C' => 'TIPO', 'D' => 'QTD', 'E' => 'VALOR TOTAL'));
        $l++;
        foreach ($porPlano as $p) {
            if (intval($p['qtd']) == 0) {
                continue;   // conta sem movimento nao entra, igual ao balancete
            }
            $sh->setCellValue('A' . $l, $p['codigo_financeiro']);
            $sh->setCellValue('B' . $l, $this->paraUtf8($p['nome_plano_contas']));
            $sh->setCellValue('C' . $l, $p['tipo_conta']);
            $sh->setCellValue('D' . $l, intval($p['qtd']));
            $sh->setCellValue('E' . $l, floatval($p['total_valor']));
            $sh->getStyle('E' . $l)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
            $l++;
        }

        // ressalva de cancelados, quando houver -- mesma do rodape do balancete
        $canc = $this->totalCanceladosRecebidos($fws_, $debug);
        if ($canc['qtd'] > 0) {
            $l += 2;
            $sh->setCellValue('A' . $l, sprintf(
                'Ressalva: R$ %s da receita correspondem a %d pagamento(s) com inscricao cancelada ou devolvida no sistema.',
                number_format($canc['total'], 2, ',', '.'), $canc['qtd']));
            $sh->mergeCells('A' . $l . ':E' . $l);
            $sh->getStyle('A' . $l)->getFont()->setItalic(true);
        }
    }

    /**
     * Exporta em Excel a aba escolhida (Resumo, Razao, Posicao de caixa,
     * Lancamentos, Plano de Contas, Rateios, Centro de Custo, Fontes).
     * Vem por GET (window.location), entao os filtros sao lidos e validados
     * a partir do $_GET (o Command so copia c/m para $fws_).
     */
    public function exportarAba($fws_, $debug = false) {
        // filtros validados (fecha a porta pra SQL injection e cobre o GET)
        $g = function($k) { return isset($_GET[$k]) ? $_GET[$k] : ''; };
        $fws_['dataInicio'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $g('dataInicio')) ? $g('dataInicio') : '';
        $fws_['dataFim']    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $g('dataFim')) ? $g('dataFim') : '';
        $fws_['filtroTipo'] = preg_replace('/[^A-Z_]/', '', strtoupper($g('filtroTipo')));
        $fws_['filtroConciliado'] = in_array($g('filtroConciliado'), array('0', '1'), true) ? $g('filtroConciliado') : '';
        $fws_['codPlanoContasRazao'] = intval($g('codPlanoContasRazao'));
        $aba = preg_replace('/[^a-z]/', '', strtolower($g('aba')));

        $blocos = array();
        switch ($aba) {
            case 'lancamentos':
                $titulo = 'Lancamentos'; $arquivo = 'lancamentos';
                $blocos[] = $this->blocoLancamentos($fws_, $debug);
                break;
            case 'razao':
                $titulo = 'Razao'; $arquivo = 'razao';
                $blocos[] = $this->blocoRazao($fws_, $debug);
                break;
            case 'caixa':
                $titulo = 'Posicao de Caixa'; $arquivo = 'posicao-caixa';
                $blocos[] = $this->blocoPosicaoCaixa($fws_, $debug);
                break;
            case 'resumo':
                $titulo = 'Resumo'; $arquivo = 'resumo';
                $blocos[] = $this->blocoResumoPorTipo($fws_, $debug);
                $blocos[] = $this->blocoResumoPorPlano($fws_, $debug);
                break;
            case 'planocontas':
                $titulo = 'Plano de Contas'; $arquivo = 'plano-de-contas';
                $blocos[] = $this->blocoPlanoContas($debug);
                break;
            case 'rateios':
                $titulo = 'Rateios'; $arquivo = 'rateios';
                $blocos[] = $this->blocoRateios($debug);
                break;
            case 'centrocusto':
                $titulo = 'Centros de Custo'; $arquivo = 'centros-de-custo';
                $blocos[] = $this->blocoCentroCusto($debug);
                break;
            case 'fontesrecurso':
                $titulo = 'Fontes de Recurso'; $arquivo = 'fontes-de-recurso';
                $blocos[] = $this->blocoFontesRecurso($debug);
                break;
            default:
                throw new Exception('Aba desconhecida para exportacao.');
        }

        $this->streamPlanilha($arquivo, $titulo, $blocos, $fws_);
    }

    /** Monta um .xlsx a partir de blocos (titulo + colunas + linhas). */
    private function streamPlanilha($nomeArquivo, $tituloAba, $blocos, $fws_) {
        $autoload = dirname(__FILE__) . '/../lib/spreadsheet/vendor/autoload.php';
        if (!file_exists($autoload)) {
            throw new Exception("Biblioteca PhpSpreadsheet nao encontrada no servidor.");
        }
        require_once($autoload);

        $cab = $this->carregarCabecalhoBalancete();
        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle(substr($tituloAba, 0, 31));

        $l = 1;
        $sh->setCellValue('A' . $l, $this->paraUtf8($tituloAba) . ' - ' . $cab['evento']);
        $sh->getStyle('A' . $l)->getFont()->setBold(true)->setSize(12);
        $l++;
        $periodo  = 'Periodo: ';
        $periodo .= !empty($fws_['dataInicio']) ? date('d/m/Y', strtotime($fws_['dataInicio'])) : '(inicio)';
        $periodo .= ' a ';
        $periodo .= !empty($fws_['dataFim']) ? date('d/m/Y', strtotime($fws_['dataFim'])) : '(fim)';
        $sh->setCellValue('A' . $l, $periodo . '     Emitido em ' . date('d/m/Y H:i'));
        $sh->getStyle('A' . $l)->getFont()->setSize(9);
        $l += 2;

        $maxCols = 1;
        foreach ($blocos as $bloco) {
            $ncol = count($bloco['colunas']);
            if ($ncol > $maxCols) { $maxCols = $ncol; }

            if (!empty($bloco['titulo'])) {
                $sh->setCellValue('A' . $l, $this->paraUtf8($bloco['titulo']));
                $sh->getStyle('A' . $l)->getFont()->setBold(true);
                $l++;
            }

            for ($i = 0; $i < $ncol; $i++) {
                $sh->setCellValue($this->colLetra($i) . $l, $this->paraUtf8($bloco['colunas'][$i]));
            }
            $sh->getStyle('A' . $l . ':' . $this->colLetra($ncol - 1) . $l)->applyFromArray(array(
                'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
                'fill' => array('fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => array('rgb' => '2E5C8A')),
            ));
            $l++;

            $inicioDados = $l;
            foreach ($bloco['linhas'] as $linha) {
                for ($i = 0; $i < $ncol; $i++) {
                    $val = isset($linha[$i]) ? $linha[$i] : '';
                    if (is_string($val)) { $val = $this->paraUtf8($val); }
                    $sh->setCellValue($this->colLetra($i) . $l, $val);
                }
                $l++;
            }
            if (!empty($bloco['numCols']) && $l > $inicioDados) {
                foreach ($bloco['numCols'] as $ci) {
                    $letra = $this->colLetra($ci);
                    $sh->getStyle($letra . $inicioDados . ':' . $letra . ($l - 1))
                       ->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
                }
            }
            $l += 2;
        }

        for ($i = 0; $i < $maxCols; $i++) {
            $sh->getColumnDimension($this->colLetra($i))->setWidth($i === 0 ? 16 : 24);
        }

        $nome = $nomeArquivo . '-' . COD_EVENTO . '-' . date('Ymd-Hi') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nome . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);
        $writer->save('php://output');
        exit();
    }

    private function colLetra($i) {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
    }

    private function blocoLancamentos($fws_, $debug) {
        $whereData = $this->montarWhereData($fws_);
        $whereTipo = !empty($fws_['filtroTipo']) ? sprintf(" AND tipo_lancamento = '%s'", $fws_['filtroTipo']) : '';
        $whereConc = ($fws_['filtroConciliado'] !== '') ? sprintf(" AND conciliado = '%s'", $fws_['filtroConciliado']) : '';
        $sql = sprintf("
            SELECT flc.*, fpc.nome_plano_contas, fpc.codigo_financeiro
              FROM fin_lancamentos flc
              LEFT JOIN fin_plano_contas fpc ON fpc.cod_plano_contas = flc.cod_plano_contas AND fpc.cod_evento = flc.cod_evento
             WHERE flc.cod_evento = '%s' %s %s %s
             ORDER BY flc.data_competencia DESC, flc.cod_lancamentos DESC
        ", COD_EVENTO, $whereData, $whereTipo, $whereConc);
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        $linhas = array();
        if ($rs) {
            foreach ($rs as $r) {
                $linhas[] = array(
                    $r['data_competencia'] ? date('d/m/Y', strtotime($r['data_competencia'])) : '',
                    $r['data_financeira'] ? date('d/m/Y', strtotime($r['data_financeira'])) : '',
                    $r['tipo_lancamento'],
                    $r['flg_lancamento'] == 'C' ? 'CREDITO' : 'DEBITO',
                    floatval($r['valor']),
                    trim($r['codigo_financeiro'] . ' - ' . $r['nome_plano_contas'], ' -'),
                    $r['origem_lancamento'],
                    $r['historico'],
                    $r['conciliado'] == 1 ? 'SIM' : 'NAO',
                );
            }
        }
        return array(
            'colunas' => array('Dt. Competencia', 'Dt. Financeira', 'Tipo', 'Natureza', 'Valor', 'Plano de Contas', 'Origem', 'Historico', 'Conciliado'),
            'numCols' => array(4),
            'linhas'  => $linhas,
        );
    }

    private function blocoRazao($fws_, $debug) {
        $codConta = intval($fws_['codPlanoContasRazao']);
        if (!$codConta) {
            throw new Exception('Escolha uma conta na aba Razao antes de exportar.');
        }
        $whereData = $this->montarWhereDataAlias($fws_, 'flc');
        $sql = sprintf("
            SELECT flc.data_competencia, flc.flg_lancamento, flc.valor, flc.historico, flc.num_documento,
                   COALESCE(i.nome_inscrito, flc.favorecido) AS favorecido
              FROM fin_lancamentos flc
              LEFT JOIN tab_inscritos i ON i.cod_inscrito = flc.cod_inscrito AND i.cod_evento = flc.cod_evento
             WHERE flc.cod_evento = '%s' AND flc.cod_plano_contas = '%s' %s
             ORDER BY flc.data_competencia, flc.cod_lancamentos
        ", COD_EVENTO, $codConta, $whereData);
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        $saldo = 0; $linhas = array();
        if ($rs) {
            foreach ($rs as $r) {
                $ehD = ($r['flg_lancamento'] == 'D');
                $v = floatval($r['valor']);
                $saldo += $ehD ? $v : -$v;
                $linhas[] = array(
                    $r['data_competencia'] ? date('d/m/Y', strtotime($r['data_competencia'])) : '',
                    $r['favorecido'],
                    $r['historico'],
                    $r['num_documento'],
                    $ehD ? $v : '',
                    !$ehD ? $v : '',
                    $saldo,
                );
            }
        }
        return array(
            'colunas' => array('Data', 'Favorecido', 'Historico', 'Documento', 'Debito', 'Credito', 'Saldo'),
            'numCols' => array(4, 5, 6),
            'linhas'  => $linhas,
        );
    }

    private function blocoPosicaoCaixa($fws_, $debug) {
        $whereData = $this->montarWhereDataAlias($fws_, 'flc');
        $sql = sprintf("
            SELECT fr.nome_fontes_recurso, COALESCE(fr.saldo_inicial, 0) AS saldo_inicial, fr.data_saldo_inicial,
                   COALESCE(SUM(CASE WHEN flc.flg_lancamento = 'C' THEN flc.valor ELSE 0 END), 0) AS entradas,
                   COALESCE(SUM(CASE WHEN flc.flg_lancamento = 'D' THEN flc.valor ELSE 0 END), 0) AS saidas
              FROM fin_fontes_recurso fr
              LEFT JOIN fin_lancamentos flc
                     ON flc.cod_fontes_recurso = fr.cod_fontes_recurso AND flc.cod_evento = fr.cod_evento %s
             WHERE fr.cod_evento = '%s' AND fr.status = 'ATIVADA'
             GROUP BY fr.cod_fontes_recurso, fr.nome_fontes_recurso, fr.saldo_inicial, fr.data_saldo_inicial
             ORDER BY fr.nome_fontes_recurso
        ", $whereData, COD_EVENTO);
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        $linhas = array();
        if ($rs) {
            foreach ($rs as $r) {
                $ini = floatval($r['saldo_inicial']); $ent = floatval($r['entradas']); $sai = floatval($r['saidas']);
                $linhas[] = array(
                    $r['nome_fontes_recurso'],
                    $r['data_saldo_inicial'] ? date('d/m/Y', strtotime($r['data_saldo_inicial'])) : '',
                    $ini, $ent, $sai, $ini + $ent - $sai,
                );
            }
        }
        return array(
            'colunas' => array('Conta', 'Saldo desde', 'Saldo inicial', 'Entradas', 'Saidas', 'Saldo atual'),
            'numCols' => array(2, 3, 4, 5),
            'linhas'  => $linhas,
        );
    }

    private function blocoResumoPorTipo($fws_, $debug) {
        $dados = $this->carregarLancamentosPorTipo($fws_, $debug);
        $linhas = array();
        if (!empty($dados)) {
            foreach ($dados as $t) {
                $linhas[] = array($t['tipo_lancamento'], $t['flg_lancamento'] == 'C' ? 'CREDITO' : 'DEBITO', intval($t['qtd']), floatval($t['total_valor']));
            }
        }
        return array('titulo' => 'Lancamentos por Tipo', 'colunas' => array('Tipo', 'Natureza', 'Quantidade', 'Valor Total'), 'numCols' => array(3), 'linhas' => $linhas);
    }

    private function blocoResumoPorPlano($fws_, $debug) {
        $dados = $this->carregarLancamentosPorPlanoContas($fws_, $debug);
        $linhas = array();
        if (!empty($dados)) {
            foreach ($dados as $p) {
                $linhas[] = array($p['codigo_financeiro'], $p['nome_plano_contas'], $p['tipo_conta'], intval($p['qtd']), floatval($p['total_valor']));
            }
        }
        return array('titulo' => 'Lancamentos por Plano de Contas', 'colunas' => array('Codigo', 'Conta', 'Tipo', 'Quantidade', 'Valor Total'), 'numCols' => array(4), 'linhas' => $linhas);
    }

    private function blocoPlanoContas($debug) {
        $linhas = array();
        try {
            $obj = new PlanoContas();
            $obj->carregarListaPlanoContas(false, $debug);
            foreach ((array) $obj->getListaPlanoContas() as $o) {
                $linhas[] = array($o->getCodigoFinanceiro(), $o->getNomePlanoContas(), $o->getTipoConta(), $o->getNaturezaSaldo(), $o->getAceitaLancamento() == 1 ? 'SIM' : 'NAO');
            }
        } catch (Exception $e) {}
        return array('colunas' => array('Codigo', 'Nome', 'Tipo', 'Natureza Saldo', 'Aceita Lancamento'), 'linhas' => $linhas);
    }

    private function blocoRateios($debug) {
        $linhas = array();
        try {
            $obj = new Rateios();
            $obj->carregarListaRateios(false, $debug);
            foreach ((array) $obj->getListaRateios() as $o) {
                $linhas[] = array($o->getCodigoFinanceiroRateio(), $o->getNomeRateio(), floatval($o->getPercentual()), $o->getDescricao());
            }
        } catch (Exception $e) {}
        return array('colunas' => array('Codigo', 'Nome', 'Percentual (%)', 'Descricao'), 'numCols' => array(2), 'linhas' => $linhas);
    }

    private function blocoCentroCusto($debug) {
        $linhas = array();
        try {
            $obj = new CentroCusto();
            $obj->carregarListaCentroCusto(false, $debug);
            foreach ((array) $obj->getListaCentroCusto() as $o) {
                $linhas[] = array($o->getCodigoFinanceiro(), $o->getNomeCentroCusto(), $o->getDescricao());
            }
        } catch (Exception $e) {}
        return array('colunas' => array('Codigo', 'Nome', 'Descricao'), 'linhas' => $linhas);
    }

    private function blocoFontesRecurso($debug) {
        $linhas = array();
        try {
            $obj = new FontesRecurso();
            $obj->carregarListaFontesRecurso(false, $debug);
            foreach ((array) $obj->getListaFontesRecurso() as $o) {
                $linhas[] = array($o->getNomeFontesRecurso(), $o->getDescricao());
            }
        } catch (Exception $e) {}
        return array('colunas' => array('Nome', 'Descricao'), 'linhas' => $linhas);
    }

    /**
     * Lancamentos agrupados por conta, para o detalhe do export.
     */
    private function carregarDetalhePorConta($fws_, $debug = false) {
        $whereData = $this->montarWhereDataAlias($fws_, 'flc');
        $sql = sprintf("
            SELECT flc.cod_plano_contas, flc.data_competencia, flc.flg_lancamento, flc.valor,
                   flc.historico, flc.num_documento,
                   COALESCE(i.nome_inscrito, flc.favorecido, '') AS favorecido
              FROM fin_lancamentos flc
              LEFT JOIN tab_inscritos i ON i.cod_inscrito = flc.cod_inscrito AND i.cod_evento = flc.cod_evento
             WHERE flc.cod_evento = '%s' %s
             ORDER BY flc.cod_plano_contas, flc.data_competencia, flc.cod_lancamentos
        ", COD_EVENTO, $whereData);

        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        $por = array();
        if ($rs) {
            foreach ($rs as $r) {
                $por[$r['cod_plano_contas']][] = $r;
            }
        }
        return $por;
    }

    private function carregarCabecalhoBalancete() {
        $sql = sprintf("SELECT COALESCE(NULLIF(TRIM(e.abrev_evento),''), e.titulo) AS evento, e.gestor
                          FROM tab_eventos e WHERE e.cod_evento = '%s' LIMIT 1", COD_EVENTO);
        $rs = $this->conex->Execute($sql);
        $evento = ($rs && !$rs->EOF) ? $this->paraUtf8($rs->fields['evento']) : ('Evento ' . COD_EVENTO);
        $gestor = ($rs && !$rs->EOF) ? $this->paraUtf8($rs->fields['gestor']) : '';

        $sql = sprintf("SELECT nome_centro_custo, codigo_financeiro FROM fin_centro_custo
                         WHERE cod_evento = '%s' AND status = 'ATIVADA'
                         ORDER BY cod_centro_custo LIMIT 1", COD_EVENTO);
        $rs = $this->conex->Execute($sql);
        return array(
            'evento'        => $evento,
            'gestor'        => $gestor,
            'centro'        => ($rs && !$rs->EOF) ? $this->paraUtf8($rs->fields['nome_centro_custo']) : '',
            'centro_codigo' => ($rs && !$rs->EOF) ? $rs->fields['codigo_financeiro'] : '',
        );
    }

    /** O banco devolve ora latin1, ora utf8: so converte o que precisa. */
    private function paraUtf8($texto) {
        if ($texto === null || $texto === '') {
            return '';
        }
        return mb_detect_encoding($texto, 'UTF-8', true) ? $texto : utf8_encode($texto);
    }

    /**
     * Posicao de caixa: por conta, saldo_inicial + entradas - saidas = saldo
     * atual. E o numero que bate com o extrato do banco. Separado do balancete
     * de resultado -- saldo inicial e patrimonial, nao entra em receita/despesa.
     * O total das contas e o saldo do projeto inteiro.
     */
    public function carregarTabelaPosicaoCaixa($fws_, $debug = false) {
        try {
            $codEvento = COD_EVENTO;
            $whereData = $this->montarWhereDataAlias($fws_, 'flc');

            $sql = sprintf("
                SELECT fr.cod_fontes_recurso, fr.nome_fontes_recurso,
                       COALESCE(fr.saldo_inicial, 0) AS saldo_inicial, fr.data_saldo_inicial,
                       COALESCE(SUM(CASE WHEN flc.flg_lancamento = 'C' THEN flc.valor ELSE 0 END), 0) AS entradas,
                       COALESCE(SUM(CASE WHEN flc.flg_lancamento = 'D' THEN flc.valor ELSE 0 END), 0) AS saidas
                  FROM fin_fontes_recurso fr
                  LEFT JOIN fin_lancamentos flc
                         ON flc.cod_fontes_recurso = fr.cod_fontes_recurso
                        AND flc.cod_evento = fr.cod_evento %s
                 WHERE fr.cod_evento = '%s' AND fr.status = 'ATIVADA'
                 GROUP BY fr.cod_fontes_recurso, fr.nome_fontes_recurso, fr.saldo_inicial, fr.data_saldo_inicial
                 ORDER BY fr.nome_fontes_recurso
            ", $whereData, $codEvento);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $tSaldoIni = 0; $tEnt = 0; $tSai = 0; $ii = 0;
            if ($rs) {
                foreach ($rs as $r) {
                    $ii++;
                    $ini = floatval($r['saldo_inicial']);
                    $ent = floatval($r['entradas']);
                    $sai = floatval($r['saidas']);
                    $atual = $ini + $ent - $sai;
                    $tSaldoIni += $ini; $tEnt += $ent; $tSai += $sai;
                    ?>
                    <tr>
                        <td class="col-nome"><?php echo $this->paraUtf8($r['nome_fontes_recurso']); ?></td>
                        <td class="col-data"><?php echo $r['data_saldo_inicial'] ? date('d/m/Y', strtotime($r['data_saldo_inicial'])) : '<span style="color:#bbb;">&mdash;</span>'; ?></td>
                        <td class="col-num">R$ <?php echo number_format($ini, 2, ',', '.'); ?></td>
                        <td class="col-num" style="color:#27ae60;">R$ <?php echo number_format($ent, 2, ',', '.'); ?></td>
                        <td class="col-num" style="color:#e74c3c;">R$ <?php echo number_format($sai, 2, ',', '.'); ?></td>
                        <td class="col-num" style="font-weight:700;">R$ <?php echo number_format($atual, 2, ',', '.'); ?></td>
                    </tr>
                    <?php
                }
            }
            if ($ii == 0) {
                echo "<tr><td colspan='6' style='padding:14px; color:#888;'>Nenhuma fonte de recurso cadastrada. Cadastre em Financeiro &gt; Financeiro &gt; Fontes de Recurso.</td></tr>";
            }
            $tAtual = $tSaldoIni + $tEnt - $tSai;
            ?>
            <tr style="border-top:2px solid #333; font-weight:700; font-size:13px;">
                <td colspan="2" style="padding:10px;">TOTAL DO PROJETO</td>
                <td class="col-num" style="padding:10px;">R$ <?php echo number_format($tSaldoIni, 2, ',', '.'); ?></td>
                <td class="col-num" style="padding:10px;">R$ <?php echo number_format($tEnt, 2, ',', '.'); ?></td>
                <td class="col-num" style="padding:10px;">R$ <?php echo number_format($tSai, 2, ',', '.'); ?></td>
                <td class="col-num" style="padding:10px;">R$ <?php echo number_format($tAtual, 2, ',', '.'); ?></td>
            </tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='6'>";
            echo $e->getMessage() ? $e->getMessage() : "Nao foi possivel montar a posicao de caixa.";
            echo "</td></tr>";
        }
    }

    private function montarWhereData($fws_) {
        $where = '';
        if (!empty($fws_['dataInicio'])) {
            $where .= sprintf(" AND data_competencia >= '%s'", $fws_['dataInicio']);
        }
        if (!empty($fws_['dataFim'])) {
            $where .= sprintf(" AND data_competencia <= '%s'", $fws_['dataFim']);
        }
        return $where;
    }

    private function montarWhereDataAlias($fws_, $alias) {
        $where = '';
        if (!empty($fws_['dataInicio'])) {
            $where .= sprintf(" AND %s.data_competencia >= '%s'", $alias, $fws_['dataInicio']);
        }
        if (!empty($fws_['dataFim'])) {
            $where .= sprintf(" AND %s.data_competencia <= '%s'", $alias, $fws_['dataFim']);
        }
        return $where;
    }

    public function carregarResumoFinanceiroAjax($fws_, $debug = false) {
        $resumo = $this->carregarResumoFinanceiro($fws_, $debug);
        $porTipo = $this->carregarLancamentosPorTipo($fws_, $debug);
        $porPlano = $this->carregarLancamentosPorPlanoContas($fws_, $debug);

        echo json_encode([
            'resumo' => $resumo,
            'porTipo' => $porTipo,
            'porPlano' => $porPlano
        ]);
        exit();
    }
}
