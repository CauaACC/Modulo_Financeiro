<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarRetornoHutecAC();
?>
<script type="text/javascript">
    $(document).ready(function () {

        $('#btnSalvar').click(function () {
            salvarRetornoHutec();
        });
        $('#btnSalvarComoNovo').click(function () {
            $('#novoId').val(0);
            $('#salvarRetornoHutec').val('Adicionar');
            salvarRetornoHutec();
        });
        $('#btnLimpar').click(function () {
            limparCampos();
        });
        $('.editar').click(function () {
            var id = $(this).parents("tr").attr("id");
            editarRetornoHutec(id);
        });
        $('.excluir').click(function () {
            var id = $(this).parents("tr").attr("id");
            excluirRetornoHutec(id);
        });

        // Ordenação por coluna nas tabelas da crítica (clicar no cabeçalho).
        if ($.tablesorter) {
            // Parser de moeda BR ("1.234,56") para a coluna Valor ordenar numericamente.
            if (!$.tablesorter.getParserById || !$.tablesorter.getParserById('moedaBR')) {
                $.tablesorter.addParser({
                    id: 'moedaBR',
                    is: function (s) { return /^\s*R?\$?\s*-?[\d\.]+,\d{2}\s*$/.test(s); },
                    format: function (s) {
                        return $.tablesorter.formatFloat(
                            s.replace(/[^\d,\-]/g, '').replace(/\./g, '').replace(',', '.')
                        );
                    },
                    type: 'numeric'
                });
            }
            $('table.tablesorter').each(function () {
                // não inicializa em tabela vazia (só a linha "Nenhum." com colspan)
                if ($(this).find('tbody tr td[colspan]').length === 0) {
                    $(this).tablesorter({ widgets: ['zebra'] });
                }
            });
        }
    });

    //funçães globais
    function carregarTabelaRetornoHutec() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random()
            , {'c': 'GerenciarRetornoHutec', 'm': 'carregarTabelaRetornoHutec', '_submit_check': '1'}
            , function (html) {
                $("table tbody.inputs tr").remove();
                $("table").trigger("update");
                // append the "ajax'd" data to the table body
                $("table tbody.inputs").append(html);
                // let the plugin know that we made a update
                $("table").trigger("update");
                //            $("table").tablesorter({
                //                sortList: [[1,2]]
                //            });
                //eventos para manipulação de registros
                $('.editar').click(function () {
                    var id = $(this).parents("tr").attr("id");
                    editarRetornoHutec(id);
                });
                $('.excluir').click(function () {
                    var id = $(this).parents("tr").attr("id");
                    excluirRetornoHutec(id);
                });
            }
        );
    }

    function salvarRetornoHutec() {
        $('#formRetornoHutec').ajaxSubmit({
            target: '#mensagem'
            , dataType: 'json'
            , async: false
            , cache: false
            , beforeSend: function () {
                startLoading();
            }
            , timeout: 4000
            , error: function (ajax) {
                showResponse(ajax);
            }
            , success: function (data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaRetornoHutec();
                    limparCampos();
                }
            }
            , complete: function () {
                stopLoading();
            }
        });
    }

    function editarRetornoHutec(codRetornoHutec) {
        if (codRetornoHutec) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codRetornoHutec': codRetornoHutec,
                    'c': 'GerenciarRetornoHutec',
                    'm': 'editarRetornoHutec',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    console.log('Dados obtidos:', data);
                    //Setando valores para alteração
                    $('#nome').val(data.nome);
                    $('#updateRetornoHutec').val(1);

                    $('#codRetornoHutec').val(data.codRetornoHutec);
                    $('#salvarRetornoHutec').val('Salvar Alterações');
                    $('html,body').scrollTop(0);
                },
                complete: function () {
                    stopLoading();
                },
                error: function (xhr, status, error) {
                    console.error('Erro na solicitação AJAX:', error);
                }
            });
        } else {
            alert('Código do RetornoHutec não informado!');
        }
    }

    function excluirRetornoHutec(codRetornoHutec) {
        if (confirm('Deseja realmente excluir RetornoHutec?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codRetornoHutec': codRetornoHutec,
                    'c': 'GerenciarRetornoHutec',
                    'm': 'excluirRetornoHutec',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaRetornoHutec();
                    }
                    limparCampos();
                }
                , complete: function () {
                    stopLoading();
                }
            });
        }
    }

    function limparCampos() {
        $('#nome').val('');
        $('#updateRetornoHutec').val(0);
        $('#codRetornoHutec').val('');
        $('#salvarRetornoHutec').val('Adicionar');
        $('#imagem').val('');
    }

</script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<!-- Inclua o jQuery primeiro -->
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-1.3.2.min.js"></script>
<!-- Em seguida, inclua os plugins do jQuery -->
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-ui-1.7.3.custom.min.js"></script>
<script type="text/javascript"
        src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/i18n/ui.datepicker-pt-BR.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/ui.dialog.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<!-- Adicione o plugin do tablesorter após o jQuery -->
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<!-- Adicione o seu script personalizado -->
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_financeiro.php");
?>
<div class="panel-group">
    <?php
    if (!function_exists('_rhMoney')) {
        function _rhMoney($v) { return number_format((float) str_replace(',', '.', $v), 2, ',', '.'); }
    }
    if (!function_exists('_rhLupa')) {
        function _rhLupa($nome) {
            $nome = trim($nome);
            if ($nome === '') { return ''; }
            $url = WB_URL_VIEW . '?content=view/admin/consulta-dados-congresso-listagem-inscritos.php'
                 . '&cod_evento=' . COD_EVENTO
                 . '&nome_inscrito=' . urlencode($nome);
            return ' <a href="' . htmlspecialchars($url) . '" target="_blank"'
                 . ' title="Localizar &quot;' . htmlspecialchars($nome) . '&quot; na listagem de inscritos"'
                 . ' style="margin-left:4px;"><i class="fa-solid fa-magnifying-glass"></i></a>';
        }
    }
    // Link "ver" para a tela de Pagamentos Excluídos (pagamento deletado), filtrado pelo inscrito.
    if (!function_exists('_rhLinkExcluido')) {
        function _rhLinkExcluido($codInscrito) {
            $codInscrito = (int) $codInscrito;
            if ($codInscrito <= 0) { return ''; }
            $url = 'index.php?content=view/admin/GerenciarPagamentoExcluido.php'
                 . '&cod_evento=' . COD_EVENTO . '&cod_inscrito=' . $codInscrito;
            return ' <a href="' . htmlspecialchars($url) . '" target="_blank"'
                 . ' title="Ver o pagamento excluído deste inscrito"'
                 . ' style="margin-left:4px;"><i class="fa-solid fa-eye"></i> ver</a>';
        }
    }
    // Link "ver" para a aba Pagamentos do inscrito (pagamento cancelado/devolvido, ainda na tabela).
    if (!function_exists('_rhLinkPagamentoInscrito')) {
        function _rhLinkPagamentoInscrito($codInscrito) {
            $codInscrito = (int) $codInscrito;
            if ($codInscrito <= 0) { return ''; }
            $url = 'index.php?content=view/admin/inscritos-cadastro-ok.php'
                 . '&acao=Visualizar&cod_evento=' . COD_EVENTO . '&cod_inscrito=' . $codInscrito;
            return ' <a href="' . htmlspecialchars($url) . '" target="_blank"'
                 . ' title="Abrir a inscrição para conferir o pagamento cancelado/devolvido"'
                 . ' style="margin-left:4px;"><i class="fa-solid fa-eye"></i> ver</a>';
        }
    }
    // Célula da transação PagSeguro: mostra o código e, se houver, liga na
    // Consulta PagSeguro (localiza o pagamento e outros da mesma inscrição).
    if (!function_exists('_rhTransacao')) {
        function _rhTransacao($transacaoId, $codInscrito = 0) {
            $transacaoId = trim($transacaoId);
            if ($transacaoId === '') {
                return '<span style="color:#bbb;">&mdash;</span>';
            }
            $curto = strlen($transacaoId) > 14 ? substr($transacaoId, 0, 8) . '…' . substr($transacaoId, -4) : $transacaoId;
            $url = 'index.php?content=view/admin/GerenciarConsultaPagSeguro.php'
                 . '&transacao_id=' . urlencode($transacaoId);
            $html = '<a href="' . htmlspecialchars($url) . '" target="_blank"'
                  . ' title="Consultar esta transação no PagSeguro: ' . htmlspecialchars($transacaoId) . '"'
                  . ' style="font-family:monospace; font-size:12px;">' . htmlspecialchars($curto) . '</a>';
            // atalho para os outros pagamentos da mesma inscrição
            if ((int) $codInscrito > 0) {
                $urlInsc = 'index.php?content=view/admin/inscritos-cadastro-ok.php'
                         . '&acao=Visualizar&cod_evento=' . COD_EVENTO . '&cod_inscrito=' . (int) $codInscrito;
                $html .= ' <a href="' . htmlspecialchars($urlInsc) . '" target="_blank"'
                       . ' title="Ver todos os pagamentos desta inscrição"'
                       . ' style="margin-left:4px; color:#888;"><i class="fa-solid fa-list"></i></a>';
            }
            return $html;
        }
    }
    // Monta a célula "Situação no sistema" do bloco 2a conforme o tipo do lançamento.
    if (!function_exists('_rhSituacaoSistema')) {
        function _rhSituacaoSistema($r) {
            if (!empty($r['existe_no_sistema'])) {
                $st = trim((string) $r['status_sistema']);
                return '<span style="color:#a94442;font-weight:bold;"><i class="fa-solid fa-ban"></i> '
                     . htmlspecialchars($st !== '' ? $st : 'CANCELADO') . '</span>'
                     . _rhLinkPagamentoInscrito($r['cod_inscrito']);
            }
            if (!empty($r['existe_excluido'])) {
                return '<span style="color:#8a6d3b;"><i class="fa-solid fa-trash-can"></i> Excluído</span>'
                     . _rhLinkExcluido($r['cod_inscrito']);
            }
            return '';
        }
    }
    // Célula "Outro pgto ativo?": indica se o mesmo inscrito tem outro pagamento ATIVO no sistema.
    if (!function_exists('_rhOutroAtivo')) {
        function _rhOutroAtivo($r) {
            $qtd   = (int) (isset($r['outro_ativo_qtd']) ? $r['outro_ativo_qtd'] : 0);
            $valor = (float) (isset($r['outro_ativo_valor']) ? $r['outro_ativo_valor'] : 0);
            if ($qtd <= 0) {
                return '<span class="text-muted"><i class="fa-solid fa-minus"></i> Não</span>';
            }
            $txtQtd = $qtd > 1 ? ' (' . $qtd . ')' : '';
            return '<span style="color:#3c763d;font-weight:bold;"><i class="fa-solid fa-circle-check"></i> Sim' . $txtQtd
                 . '</span> <span class="text-muted">R$ ' . _rhMoney($valor) . '</span>';
        }
    }
    $critica = $objSRV->getCritica($debug);
    // Divide "a mais no retorno" entre cancelados/excluídos (esperado) e sem registro (verificar)
    $soHutecCancelado = array(); $soHutecSemReg = array();
    foreach ($critica['soHutec'] as $r) {
        if (!empty($r['existe_no_sistema']) || !empty($r['existe_excluido'])) { $soHutecCancelado[] = $r; }
        else { $soHutecSemReg[] = $r; }
    }
    $somaCancelado = 0; foreach ($soHutecCancelado as $r) { $somaCancelado += (float) str_replace(',', '.', $r['valor']); }
    $somaSemReg    = 0; foreach ($soHutecSemReg as $r)    { $somaSemReg    += (float) str_replace(',', '.', $r['valor']); }
    $somaHutec = $somaCancelado + $somaSemReg;
    $somaNosso = 0; foreach ($critica['soNosso'] as $r) { $somaNosso += (float) str_replace(',', '.', $r['valor']); }
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Crítica da Conciliação — HUTEC × Sistema:</h4>
        </div>
        <div class="panel-body">
            <?php
            // Helpers de "card" com faixa de titulo colorida (inline, independente do CSS)
            if (!function_exists('_rhCardIni')) {
                function _rhCardIni($titulo, $sub, $cor, $qtd = null) {
                    echo '<div style="border:1px solid #ddd;border-radius:6px;margin-bottom:22px;overflow:hidden;">';
                    echo '<div style="padding:9px 14px;background:'.$cor.';color:#fff;">';
                    echo '<span style="font-size:15px;font-weight:bold;">'.$titulo.'</span>';
                    if ($qtd !== null) {
                        echo ' <span style="background:rgba(255,255,255,.28);border-radius:12px;padding:1px 10px;font-size:12px;font-weight:bold;margin-left:4px;">'.$qtd.'</span>';
                    }
                    if ($sub !== '') { echo '<div style="font-size:12px;opacity:.92;margin-top:3px;">'.$sub.'</div>'; }
                    echo '</div><div style="padding:12px 14px;">';
                }
            }
            if (!function_exists('_rhCardFim')) {
                function _rhCardFim($totalHtml = '', $corRodape = '#f5f5f5') {
                    echo '</div>';
                    if ($totalHtml !== '') {
                        echo '<div style="padding:8px 14px;background:'.$corRodape.';border-top:1px solid #eee;text-align:right;font-weight:bold;">'.$totalHtml.'</div>';
                    }
                    echo '</div>';
                }
            }
            ?>

            <?php // ===== BLOCO 1 — Lançado diferente ===== ?>
            <?php _rhCardIni('1) Lançado diferente', 'Mesmo cód. de pagamento nos dois lados, mas valor divergente.', '#d9534f', count($critica['diferente'])); ?>
            <table class="table table-striped table-hover tablesorter" style="margin-bottom:0;">
                <thead>
                <tr>
                    <th>Cód. Pagamento</th><th>Nome</th>
                    <th class="text-end">Valor HUTEC</th><th class="text-end">Valor Sistema</th><th class="text-end">Diferença</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$critica['diferente']) { ?>
                    <tr><td colspan="5" class="text-muted">Nenhum lançamento com valor divergente.</td></tr>
                <?php } else foreach ($critica['diferente'] as $r) {
                    $dif = (float) str_replace(',', '.', $r['valor_nosso']) - (float) str_replace(',', '.', $r['valor_hutec']); ?>
                    <tr>
                        <td><?php echo $r['cod_pagamento'] ?></td>
                        <td><?php echo $r['nome'] ?><?php echo _rhLupa($r['nome']) ?></td>
                        <td class="text-end"><?php echo _rhMoney($r['valor_hutec']) ?></td>
                        <td class="text-end"><?php echo _rhMoney($r['valor_nosso']) ?></td>
                        <td class="text-end text-danger"><?php echo _rhMoney($dif) ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php _rhCardFim(); ?>

            <?php // ===== BLOCO 2 — A mais no retorno HUTEC (2a cancelados + 2b sem registro) ===== ?>
            <div style="margin:0 0 10px 0;">
                <span style="font-size:15px;font-weight:bold;color:#8a6d3b;">2) A mais no retorno HUTEC</span>
                <span class="text-muted" style="font-size:12px;">— recebido no balancete sem pagamento ativo no sistema (total: <?php echo count($critica['soHutec']) ?> — R$ <?php echo _rhMoney($somaHutec) ?>)</span>
                <form method="get" action="index.php" target="_blank" style="display:inline-block;margin-left:8px;">
                    <input type="hidden" name="content" value="view/admin/GerenciarPagamentoExcluido.php">
                    <input type="hidden" name="cod_evento" value="<?php echo COD_EVENTO ?>">
                    <button type="submit" class="btn btn-xs btn-default" title="Abre a tela de Pagamentos Excluídos/Cancelados deste evento para conferência">
                        <i class="fa-solid fa-eye"></i> Ver pagamentos excluídos/cancelados do evento
                    </button>
                </form>
            </div>

            <?php _rhCardIni('2a) Cancelados / excluídos no sistema',
                '<i class="fa-solid fa-circle-check"></i> Esperado — pagamento CANCELADO/DEVOLVIDO ou excluído no sistema, mas o HUTEC recebeu (crédito no balancete). Confira cada um no link &quot;ver&quot;.',
                '#5cb85c', count($soHutecCancelado)); ?>
            <table class="table table-striped table-hover tablesorter" style="margin-bottom:0;">
                <thead>
                <tr><th>Cód. Pagamento</th><th>Transação</th><th>Data</th><th>Nome</th><th class="text-end">Valor</th><th>Situação no sistema</th><th>Outro pgto ativo?</th></tr>
                </thead>
                <tbody>
                <?php if (!$soHutecCancelado) { ?>
                    <tr><td colspan="7" class="text-muted">Nenhum.</td></tr>
                <?php } else foreach ($soHutecCancelado as $r) { ?>
                    <tr>
                        <td><?php echo $r['cod_pagamento'] ?></td>
                        <td><?php echo _rhTransacao($r['transacao_id'], $r['cod_inscrito']) ?></td>
                        <td><?php echo $r['data'] ?></td>
                        <td><?php echo $r['nome'] ?><?php echo _rhLupa($r['nome']) ?></td>
                        <td class="text-end"><?php echo _rhMoney($r['valor']) ?></td>
                        <td><?php echo _rhSituacaoSistema($r) ?></td>
                        <td><?php echo _rhOutroAtivo($r) ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php _rhCardFim('Cancelados/excluídos: '.count($soHutecCancelado).' — R$ '._rhMoney($somaCancelado), '#dff0d8'); ?>

            <?php _rhCardIni('2b) Sem registro no sistema',
                '<i class="fa-solid fa-triangle-exclamation"></i> VERIFICAR — não achado em pagamentos ativos nem excluídos. Pode ser lançamento manual (NF) ou algo a conciliar.',
                '#f0ad4e', count($soHutecSemReg)); ?>
            <table class="table table-striped table-hover tablesorter" style="margin-bottom:0;">
                <thead>
                <tr><th>Cód. Pagamento</th><th>Data</th><th>Nome</th><th class="text-end">Valor</th></tr>
                </thead>
                <tbody>
                <?php if (!$soHutecSemReg) { ?>
                    <tr><td colspan="4" class="text-muted">Nenhum.</td></tr>
                <?php } else foreach ($soHutecSemReg as $r) { ?>
                    <tr>
                        <td><?php echo $r['cod_pagamento'] ?></td>
                        <td><?php echo $r['data'] ?></td>
                        <td><?php echo $r['nome'] ?><?php echo _rhLupa($r['nome']) ?></td>
                        <td class="text-end"><?php echo _rhMoney($r['valor']) ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php _rhCardFim('Sem registro (verificar): '.count($soHutecSemReg).' — R$ '._rhMoney($somaSemReg), '#fcf8e3'); ?>

            <?php // ===== BLOCO 3 — Não lançado no retorno ===== ?>
            <?php _rhCardIni('3) Não lançado no retorno', 'Pago no sistema (ATIVADA), mas ausente no balancete HUTEC.', '#337ab7', count($critica['soNosso'])); ?>
            <table class="table table-striped table-hover tablesorter" style="margin-bottom:0;">
                <thead>
                <tr><th>Cód. Pagamento</th><th>Transação</th><th>Nome</th><th>Forma</th><th class="text-end">Valor</th></tr>
                </thead>
                <tbody>
                <?php if (!$critica['soNosso']) { ?>
                    <tr><td colspan="5" class="text-muted">Nenhum.</td></tr>
                <?php } else foreach ($critica['soNosso'] as $r) { ?>
                    <tr>
                        <td><?php echo $r['cod_pagamento'] ?></td>
                        <td><?php echo _rhTransacao($r['transacao_id'], $r['cod_inscrito']) ?></td>
                        <td><?php echo $r['nome'] ?><?php echo _rhLupa($r['nome']) ?></td>
                        <td><?php echo $r['forma_pagamento'] ?></td>
                        <td class="text-end"><?php echo _rhMoney($r['valor']) ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php
            $totalNaoLancado = 'Total não lançado: '.count($critica['soNosso']).' — R$ '._rhMoney($somaNosso);
            ob_start(); ?>
                <form method="post" action="<?php echo WB_URL_VIEW ?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php" style="display:inline-block;margin:0 12px 0 0;">
                    <input type="hidden" name="listar" value="1">
                    <input type="hidden" name="cod_evento" value="<?php echo COD_EVENTO ?>">
                    <input type="hidden" name="xml_pagamentos_carregados" value="nao-carregados">
                    <button type="submit" class="btn btn-xs btn-primary" title="Abre a listagem de inscritos cujos pagamentos NÃO constam no Balancete carregado">
                        <i class="fa-solid fa-triangle-exclamation"></i> Listar pagamentos não carregados
                    </button>
                </form>
            <?php $btnNaoLancado = ob_get_clean(); ?>
            <?php _rhCardFim('<span style="float:left;font-weight:normal;">'.$btnNaoLancado.'</span>'.$totalNaoLancado, '#d9edf7'); ?>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Relatório Balancete Hutec:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
            ?>
            <form id="formRetornoHutec" name="formRetornoHutec" action="<?php echo WB_URL_COMMAND ?>&dummy="
                  method="post">

                <!-- <div class="form-group col-sm-3" style="padding-left: 0px;">
                    <label for="nome">Nome: </label>
                    <input type="text" name="nome" value="">
                </div> -->

                <!-- <div class="form-group col-sm-12" style="padding-left: 0px;">
                    <div id="carregando" class="shown hidden">
                        <div class="imagem">
                            <div class="frase">Carregando...</div>
                            <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
                        </div>
                    </div>
                    <div id="mensagem"></div>
                    <div id="sucesso" class="hidden"></div>
                    <div id="erro" class="hidden"></div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarRetornoHutec" name="c">
                    <input type="hidden" value="salvarRetornoHutec" name="m">
                    <input type="hidden" name="updateRetornoHutec" id="updateRetornoHutec" value="0"/>
                    <input type="hidden" name="codRetornoHutec" id="codRetornoHutec" value=""/>
                    <button class="btn btn-xs btn-primary" type="button" id="btnSalvar">Salvar</button>
                    <button class="btn btn-xs btn-primary" type="button" id="btnLimpar">Limpar Campos</button>
                </div> -->
            </form>
        </div>
        <?php } catch (Exception $exc) { ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
        <?php } ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <b>Lista Gerenciar Arquivo Balancete:</b>
            <div style="margin: 8px 0; display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <label for="filtroSituacaoRH" style="margin: 0;"><b>Situação:</b></label>
                <select id="filtroSituacaoRH" class="form-control" style="width: auto; display: inline-block;">
                    <option value="">Todos</option>
                    <option value="VERIFICAR">Somente a verificar</option>
                    <option value="OK">Somente OK</option>
                </select>
                <input type="text" id="filtroBuscaRH" class="form-control" style="width: 240px; display: inline-block;"
                       placeholder="Buscar nome / cód. pagamento / descrição">
                <span id="filtroContadorRH" class="text-muted"></span>
            </div>
            <table class="table table-striped table-hover" id="itens">
                <thead>
                <tr>
                    <th width="15px" data-nosort>#</th>
                    <th data-tipo="data">Data</th>
                    <th>Cod.Pagamento</th>
                    <th>Nome</th>
                    <th>Descricao</th>
                    <th>Cod.Extra</th>
                    <th class="text-end" data-tipo="num">Valor</th>
                    <th class="text-center">Operação</th>
                    <th class="text-center">Situação</th>
                </tr>
                </thead>
                <tbody class="inputs">
                <?php
                $objSRV->carregarTabelaRetornoHutec($fws_, $debug);
                ?>
                </tbody>
            </table>
            <div class="alert alert-info" role="alert" style="margin-top: 10px;">
                <b>Total de registros carregados:</b> <?php echo $objSRV->totalRegistros ?>
                &nbsp;&nbsp;—&nbsp;&nbsp;
                <b>Total carregado (créditos):</b> R$ <?php echo number_format($objSRV->valorTotal, 2, ',', '.') ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Ordenação por coluna + filtro (vanilla JS, independente da versão de jQuery da página)
    (function () {
        var table = document.getElementById('itens');
        if (!table) { return; }
        var tbody = table.querySelector('tbody.inputs') || table.querySelector('tbody');
        if (!tbody) { return; }

        function linhasDados() {
            return Array.prototype.slice.call(tbody.querySelectorAll('tr'))
                .filter(function (tr) { return tr.querySelectorAll('td').length > 1; });
        }

        // ---------- Filtro ----------
        var selSit = document.getElementById('filtroSituacaoRH');
        var txtBusca = document.getElementById('filtroBuscaRH');
        var contador = document.getElementById('filtroContadorRH');

        function aplicarFiltro() {
            var sit = selSit ? selSit.value : '';
            var termo = txtBusca ? txtBusca.value.trim().toLowerCase() : '';
            var visiveis = 0, total = 0;
            linhasDados().forEach(function (tr) {
                total++;
                var okSit = !sit || tr.getAttribute('data-situacao') === sit;
                var okTermo = !termo || tr.textContent.toLowerCase().indexOf(termo) !== -1;
                var mostrar = okSit && okTermo;
                tr.style.display = mostrar ? '' : 'none';
                if (mostrar) { visiveis++; }
            });
            if (contador) { contador.textContent = visiveis + ' de ' + total + ' registros'; }
        }
        if (selSit) { selSit.addEventListener('change', aplicarFiltro); }
        if (txtBusca) { txtBusca.addEventListener('input', aplicarFiltro); }

        // ---------- Ordenação ----------
        var ths = Array.prototype.slice.call(table.querySelectorAll('thead th'));
        var sortDir = {};

        function parseVal(txt, tipo) {
            txt = (txt || '').trim();
            if (tipo === 'num') {
                var n = parseFloat(txt.replace(/\./g, '').replace(',', '.').replace(/[^0-9.\-]/g, ''));
                return isNaN(n) ? -Infinity : n;
            }
            if (tipo === 'data') {
                var m = txt.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                if (m) { return new Date(+m[3], +m[2] - 1, +m[1]).getTime(); }
                return -Infinity;
            }
            return txt.toLowerCase();
        }

        ths.forEach(function (th, idx) {
            if (th.hasAttribute('data-nosort')) { return; }
            th.style.cursor = 'pointer';
            th.title = 'Clique para ordenar';
            th.addEventListener('click', function () {
                var tipo = th.getAttribute('data-tipo') || 'texto';
                var asc = sortDir[idx] !== 'asc';
                sortDir = {};
                sortDir[idx] = asc ? 'asc' : 'desc';

                var rows = linhasDados();
                rows.sort(function (a, b) {
                    var va = parseVal(a.cells[idx] ? a.cells[idx].textContent : '', tipo);
                    var vb = parseVal(b.cells[idx] ? b.cells[idx].textContent : '', tipo);
                    if (va < vb) { return asc ? -1 : 1; }
                    if (va > vb) { return asc ? 1 : -1; }
                    return 0;
                });
                rows.forEach(function (tr) { tbody.appendChild(tr); });

                ths.forEach(function (t) {
                    var c = t.querySelector('.rh-caret');
                    if (c) { c.parentNode.removeChild(c); }
                });
                var caret = document.createElement('span');
                caret.className = 'rh-caret';
                caret.textContent = asc ? ' ▲' : ' ▼';
                th.appendChild(caret);
            });
        });

        aplicarFiltro();
    })();
</script>