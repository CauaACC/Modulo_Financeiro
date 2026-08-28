<?php
require_once(dirname(__FILE__)."/../../include/config.php");

// Verificar permissão de financeiro
@$cod_msg = false;
if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

$tituloPagina = 'Consultar Transação PagSeguro:';
$submenu = 'area_admin_menu_projetos.php';
require_once(dirname(__FILE__)."/../../view/admin/cabecalho-geral.php");

// Carregar lista de maquininhas para o select
$objPagDAO = new PagamentoDAO();
$listaMaquininhas = $objPagDAO->listarMaquininhasDistintas();
// Os status vem do proprio banco, como as maquininhas. A lista fixa que existia
// aqui nao batia com os dados: oferecia status inexistentes e nao tinha
// "Aprovada", que e' o status da maioria das transacoes.
$listaStatus = $objPagDAO->listarStatusDistintos();
?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#formConsultaPagSeguro').on('submit', function(e) {
            e.preventDefault();
            var transacaoId = $.trim($('#transacao_id').val());
            if (transacaoId) {
                buscarTransacao();
            } else {
                buscarPorFiltros();
            }
        });
        $('#btnLimpar, #btnLimparTopo').click(function() {
            limparCampos();
        });
        $('#btnBuscarFiltros').click(function() {
            buscarPorFiltros();
        });
        // Se veio com transacao_auto (ou transacao_id) na URL, buscar automaticamente
        var urlParams = new URLSearchParams(window.location.search);
        var transacaoAuto = urlParams.get('transacao_auto') || urlParams.get('transacao_id');
        if (transacaoAuto) {
            $('#transacao_id').val(transacaoAuto);
            buscarTransacao();
        }
        // Ao selecionar um status, preencher período padrão (últimos 30 dias) se vazio
        $('#filtro_status').change(function() {
            if ($(this).val() && !$('#filtro_data_ini').val() && !$('#filtro_data_fim').val()) {
                var hoje = new Date();
                var ini = new Date();
                ini.setDate(ini.getDate() - 30);
                $('#filtro_data_fim').val(hoje.toISOString().substring(0, 10));
                $('#filtro_data_ini').val(ini.toISOString().substring(0, 10));
            }
        });
    });

    // Badge compacto do status do PagSeguro (verde = ok, vermelho = problema, amarelo = pendente)
    function bannerStatus(statusTexto) {
        var s = (statusTexto || '').toLowerCase();
        if (!s) return '';
        var cor, fundo, icone, prefixo;
        // ATENÇÃO: classificarStatusPS testa 'aguardando'/'análise' ANTES de 'paga' —
        // senão "aguardando pagamento" cai no teste de 'paga' e vira banner verde.
        var classe = classificarStatusPS(s);
        if (classe === 'pago') {
            cor = '#3c763d'; fundo = '#dff0d8'; icone = 'fa-check-circle'; prefixo = 'Transação está ';
        } else if (classe === 'negativo') {
            cor = '#a94442'; fundo = '#f2dede'; icone = 'fa-times-circle';
            prefixo = (s.indexOf('disputa') >= 0) ? 'Transação está ' : 'Transação foi ';
        } else {
            cor = '#8a6d3b'; fundo = '#fcf8e3'; icone = 'fa-clock-o'; prefixo = 'Transação está ';
        }
        return '<span style="display:inline-block; padding:6px 14px; border-radius:4px;'
             + ' background:' + fundo + '; color:' + cor + '; border:1px solid ' + cor + ';'
             + ' font-size:15px; font-weight:bold; white-space:nowrap;">'
             + '<i class="fa ' + icone + '"></i> ' + prefixo + statusTexto.toLowerCase()
             + '</span>';
    }

    function limparCampos() {
        $('#transacao_id').val('');
        $('#filtro_nome').val('');
        $('#filtro_email').val('');
        $('#filtro_referencia').val('');
        $('#filtro_status').val('');
        $('#filtro_pag_status').val('');
        $('#filtro_maquininha').val('');
        $('#filtro_data_ini').val('');
        $('#filtro_data_fim').val('');
        $('#resultadoPagSeguro').html('');
        $('#transacao_id').focus();
    }

    function montarPanel(titulo, campos) {
        var html = '<div class="panel panel-default">';
        html += '<div class="panel-heading"><strong>' + titulo + '</strong></div>';
        html += '<div class="panel-body">';
        html += '<table class="table table-bordered table-striped">';
        html += '<thead><tr><th style="width:200px">Campo</th><th>Valor</th></tr></thead><tbody>';
        for (var i = 0; i < campos.length; i++) {
            html += '<tr><td><strong>' + campos[i].label + '</strong></td>';
            html += '<td>' + (campos[i].valor ? campos[i].valor : '-') + '</td></tr>';
        }
        html += '</tbody></table></div></div>';
        return html;
    }

    function buscarTransacao() {
        var transacaoId = $.trim($('#transacao_id').val());
        if (!transacaoId) {
            $('#resultadoPagSeguro').html('<div class="alert alert-warning">Informe o código da transação.</div>');
            return;
        }
        $('#resultadoPagSeguro').html('<div class="alert alert-info">Buscando...</div>');
        $.ajax({
            url: '<?php echo WB_URL_COMMAND ?>&t='+Math.random(),
            type: 'POST',
            data: {
                c: 'GerenciarConsultaPagSeguro',
                m: 'consultar',
                transacao_id: transacaoId
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.sucesso) {
                    var html = '';

                    // Barra de ações no topo: status + abrir inscrição (na aba Pagamentos, tela de edição)
                    var statusPS = '';
                    for (var k = 0; k < resp.pagseguro.length; k++) {
                        if (resp.pagseguro[k].label === 'Status') { statusPS = resp.pagseguro[k].valor; break; }
                    }
                    html += '<div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:14px;">';
                    html += bannerStatus(statusPS);
                    if (resp.linkInscrito) {
                        html += '<a href="' + resp.linkInscrito + '#pagamentos" target="_blank" class="btn btn-sm btn-primary">';
                        html += '<i class="fa fa-user"></i> Abrir Inscrição' + (resp.nomeInscrito ? ' — ' + resp.nomeInscrito : '') + '</a>';
                    }
                    html += '</div>';

                    // Dados PagSeguro
                    html += montarPanel('Dados da Transação PagSeguro', resp.pagseguro);

                    // Evento associado à maquininha
                    if (resp.eventoMaquininha) {
                        html += '<div class="alert alert-success">';
                        html += '<i class="fa fa-credit-card"></i> ';
                        html += '<strong>Evento da Maquininha:</strong> ' + resp.eventoMaquininha;
                        html += '</div>';
                    }

                    // Dados do Pagamento
                    if (resp.pagamento) {
                        html += montarPanel('Dados do Pagamento Registrado', resp.pagamento);
                    } else {
                        html += '<div class="alert alert-info">Essa transação não foi associada a nenhum registro de pagamento.</div>';
                    }

                    // Dados do Pedido
                    if (resp.pedido) {
                        html += montarPanel('Dados do Pedido', resp.pedido);
                    } else if (resp.pagamento) {
                        html += '<div class="alert alert-info">Nenhum pedido associado encontrado.</div>';
                    }

                    // Repetir o status no final (o painel de dados é longo)
                    html += '<div style="margin-top:10px;">' + bannerStatus(statusPS) + '</div>';

                    $('#resultadoPagSeguro').html(html);
                } else {
                    $('#resultadoPagSeguro').html('<div class="alert alert-warning">' + (resp.mensagem || resp.message || 'Não foi possível consultar a transação.') + '</div>');
                }
            },
            error: function() {
                $('#resultadoPagSeguro').html('<div class="alert alert-danger">Erro ao consultar. Tente novamente.</div>');
            }
        });
    }

    // Classifica o status do PagSeguro em: pago / negativo (cancelada, devolvida, disputa) / pendente
    function classificarStatusPS(statusTexto) {
        var s = (statusTexto || '').toLowerCase();
        if (!s) return '';
        if (s.indexOf('aguardando') >= 0 || s.indexOf('anális') >= 0 || s.indexOf('analis') >= 0) return 'pendente';
        if (s.indexOf('cancel') >= 0 || s.indexOf('devolv') >= 0 || s.indexOf('estorn') >= 0 || s.indexOf('disputa') >= 0) return 'negativo';
        if (s.indexOf('paga') >= 0 || s.indexOf('aprovad') >= 0 || s.indexOf('dispon') >= 0) return 'pago';
        return 'pendente';
    }

    // As divergências vêm prontas do servidor (PagamentoDivergencia), a mesma
    // regra usada nas abas Pedidos e Pagamentos do cadastro do inscrito.
    function escaparHtml(txt) {
        return String(txt).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Célula de data: mostra dd/mm/aaaa e guarda o valor cru (aaaa-mm-dd) para a ordenação
    function celulaData(texto, ordem) {
        var vazio = !texto || texto.indexOf('00/00') === 0 || (ordem && ordem.indexOf('0000') === 0);
        var ordenacao = vazio ? '' : (ordem || texto);
        return '<td style="white-space:nowrap;" data-sort="' + ordenacao + '">'
             + (vazio ? '-' : texto) + '</td>';
    }

    function buscarPorFiltros() {
        var nome       = $.trim($('#filtro_nome').val());
        var email      = $.trim($('#filtro_email').val());
        var referencia = $.trim($('#filtro_referencia').val());
        var status     = $('#filtro_status').val();
        var pagStatus  = $('#filtro_pag_status').val();
        var maquininha = $('#filtro_maquininha').val();
        var dataIni    = $('#filtro_data_ini').val();
        var dataFim    = $('#filtro_data_fim').val();

        if (!nome && !email && !referencia && !status && !pagStatus && !maquininha) {
            $('#resultadoPagSeguro').html('<div class="alert alert-warning">Informe ao menos um filtro.</div>');
            return;
        }
        // Se filtrou por status/pgto/maquininha sem nome/email/cpf, exigir período
        if ((status || pagStatus || maquininha) && !nome && !email && !referencia) {
            if (!dataIni || !dataFim) {
                $('#resultadoPagSeguro').html('<div class="alert alert-warning">Ao filtrar por status ou maquininha sem outros filtros, informe o período.</div>');
                return;
            }
        }
        $('#resultadoPagSeguro').html('<div class="alert alert-info">Buscando...</div>');
        $.ajax({
            url: '<?php echo WB_URL_COMMAND ?>&t='+Math.random(),
            type: 'POST',
            data: {
                c: 'GerenciarConsultaPagSeguro',
                m: 'buscarPorFiltros',
                filtro_nome: nome,
                filtro_email: email,
                filtro_referencia: referencia,
                filtro_status: status,
                filtro_pag_status: pagStatus,
                filtro_maquininha: maquininha,
                filtro_data_ini: dataIni,
                filtro_data_fim: dataFim
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.sucesso) {
                    var html = '';
                    var totalDivergentes = 0;
                    if (resp.limitado) {
                        html += '<div class="alert alert-warning">Exibindo as ' + resp.totalRegistros + ' primeiras transações. Refine os filtros ou reduza o período para ver todos os resultados.</div>';
                    } else {
                        html += '<div class="alert alert-success">' + resp.totalRegistros + ' transação(ões) encontrada(s).</div>';
                    }
                    html += '<div id="avisoDivergencia"></div>';
                    html += '<table id="tblResultado" class="table table-bordered table-striped table-hover" style="font-size:12px;">';
                    html += '<thead><tr>';
                    html += '<th class="sortable" data-col="0" style="cursor:pointer;" title="Ordenar por código">Código Transação <i class="fa fa-sort" style="color:#999;"></i></th>';
                    html += '<th class="sortable" data-col="1" style="cursor:pointer;white-space:nowrap;" title="Data em que a transação foi criada no PagSeguro">Data Transação <i class="fa fa-sort" style="color:#999;"></i></th>';
                    html += '<th class="sortable" data-col="2" style="cursor:pointer;white-space:nowrap;" title="Data prevista de liberação do valor pelo PagSeguro">Prev. Liberação <i class="fa fa-sort" style="color:#999;"></i></th>';
                    html += '<th class="sortable" data-col="3" style="cursor:pointer;white-space:nowrap;" title="Vencimento do pedido no sistema">Vencimento <i class="fa fa-sort" style="color:#999;"></i></th>';
                    html += '<th class="sortable" data-col="4" style="cursor:pointer;white-space:nowrap;" title="Data do pagamento registrado no sistema">Data Pagamento <i class="fa fa-sort" style="color:#999;"></i></th>';
                    html += '<th class="sortable" data-col="5" style="cursor:pointer;" title="Ordenar por comprador">Comprador / E-mail <i class="fa fa-sort" style="color:#999;"></i></th>';
                    html += '<th class="sortable" data-col="6" style="cursor:pointer;" title="Ordenar por inscrito">Inscrito <i class="fa fa-sort" style="color:#999;"></i></th>';
                    html += '<th class="sortable" data-col="7" style="cursor:pointer;" title="Ordenar por valor">Valor <i class="fa fa-sort" style="color:#999;"></i></th>';
                    html += '<th class="sortable" data-col="8" style="cursor:pointer;" title="Ordenar por status">Status / Forma Pgto <i class="fa fa-sort" style="color:#999;"></i></th>';
                    html += '</tr></thead><tbody>';
                    for (var i = 0; i < resp.registros.length; i++) {
                        var r = resp.registros[i];
                        // Montar situação do vínculo (exibida no código da transação)
                        var situacao = '';
                        // Montar coluna Inscrito
                        var cellInscritoContent = '';
                        var cellStatusExtra = '';
                        if (r.pag_cod_inscrito) {
                            var baseUrl = r.url_prefixo
                                ? '<?php echo $PROTOCOLO ?>://<?php echo FWA_Dominio() ?>/' + r.url_prefixo + '/inscricoes/index.php'
                                : '<?php echo WB_URL_VIEW ?>';
                            var linkInscrito = baseUrl + '?content=view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito=' + r.pag_cod_inscrito + '&cod_evento=' + r.pag_cod_evento;
                            cellInscritoContent = '<a href="' + linkInscrito + '" target="_blank" title="Abrir inscrição">' + (r.nome_inscrito || '#' + r.pag_cod_inscrito) + '</a>';
                            cellInscritoContent += '<br>#' + r.pag_cod_inscrito + ' (Ev.' + r.pag_cod_evento + ')';
                            cellInscritoContent += '<br><small>Pgto: ' + ((r.pag_status || '-').toUpperCase()) + '</small>';
                        } else {
                            cellInscritoContent = '<small style="color:#999;"><i class="fa fa-unlink"></i> Sem vínculo</small>';
                        }

                        var nomeExibir = r.nome_comprador || r.nome_cliente || '-';
                        var emailExibir = r.email_comprador || r.email_cliente || '';

                        // Coluna Comprador / E-mail (juntos)
                        var cellComprador = '';
                        if (nomeExibir !== '-') {
                            cellComprador += '<a href="#" class="link-filtro-nome" data-nome="' + nomeExibir + '" title="Filtrar por este nome">' + nomeExibir + '</a>';
                        } else {
                            cellComprador += '-';
                        }
                        if (emailExibir) {
                            cellComprador += '<br><small><a href="#" class="link-filtro-email" data-email="' + emailExibir + '" title="Filtrar por este e-mail">' + emailExibir + '</a></small>';
                        }

                        // Divergências entre o pagamento do sistema e a transação do PagSeguro
                        var divergencias = r.divergencias || [];
                        if (divergencias.length) {
                            totalDivergentes++;
                            cellStatusExtra = '';
                            for (var d = 0; d < divergencias.length; d++) {
                                cellStatusExtra += '<br><span style="color:#a94442;font-weight:bold;">'
                                                 + '<i class="fa fa-exclamation-triangle"></i> ' + escaparHtml(divergencias[d]) + '</span>';
                            }
                        }

                        html += '<tr class="' + (divergencias.length ? 'linha-divergente' : 'linha-ok') + '"'
                              + (divergencias.length ? ' style="background:#fdf2f2;"' : '') + '>';
                        html += '<td style="word-break:break-all;"><a href="#" class="link-transacao" data-transacao="' + r.cod_transacao + '" title="Abrir detalhes em nova aba">' + r.cod_transacao + '</a></td>';
                        html += celulaData(r.data_transacao, r.data_transacao_ord);
                        html += celulaData(r.data_prevista_liberacao, r.data_prevista_liberacao_ord);
                        html += celulaData(r.data_vencimento, r.data_vencimento_ord);
                        html += celulaData(r.data_pagamento, r.data_pagamento_ord);
                        html += '<td>' + cellComprador + '</td>';
                        html += '<td>' + cellInscritoContent + '</td>';
                        html += '<td style="white-space:nowrap;">' + (r.valor_bruto || '-') + '</td>';
                        html += '<td>' + (r.status || '-') + '<br><small>' + (r.forma_pagamento || '-') + '</small>' + cellStatusExtra + '</td>';
                        html += '</tr>';
                    }
                    html += '</tbody></table>';
                    $('#resultadoPagSeguro').html(html);

                    // Resumo das divergências entre sistema e PagSeguro
                    if (totalDivergentes > 0) {
                        $('#avisoDivergencia').html(
                            '<div class="alert alert-danger" style="margin-bottom:10px;">'
                          + '<i class="fa fa-exclamation-triangle"></i> <strong>' + totalDivergentes
                          + ' transação(ões) com divergência</strong> entre o pagamento registrado no sistema e a transação do PagSeguro. '
                          + '<label style="font-weight:normal; margin-left:10px;">'
                          + '<input type="checkbox" id="chkSoDivergentes"> Mostrar só as divergentes</label>'
                          + '</div>');
                        $('#chkSoDivergentes').change(function() {
                            if ($(this).is(':checked')) {
                                $('#tblResultado tbody tr.linha-ok').hide();
                            } else {
                                $('#tblResultado tbody tr.linha-ok').show();
                            }
                        });
                    }

                    // Ao clicar no código da transação, abrir detalhes em nova aba
                    $('.link-transacao').click(function(e) {
                        e.preventDefault();
                        var cod = $(this).data('transacao');
                        var url = '<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarConsultaPagSeguro.php&transacao_auto=' + encodeURIComponent(cod);
                        window.open(url, '_blank');
                    });
                    // Ao clicar no nome, filtrar por nome
                    $('.link-filtro-nome').click(function(e) {
                        e.preventDefault();
                        $('#filtro_nome').val($(this).data('nome'));
                        $('#filtro_email').val('');

                        buscarPorFiltros();
                    });
                    // Ao clicar no email, filtrar por email
                    $('.link-filtro-email').click(function(e) {
                        e.preventDefault();
                        $('#filtro_email').val($(this).data('email'));
                        $('#filtro_nome').val('');

                        buscarPorFiltros();
                    });
                    ativarOrdenacao();
                } else {
                    // Sem mensagem do servidor NAO se afirma "nenhuma encontrada":
                    // era o que aparecia com a sessao expirada, dando a entender que
                    // nao havia dados quando o problema era falta de login.
                    $('#resultadoPagSeguro').html('<div class="alert alert-warning">' + (resp.mensagem || resp.message || 'Não foi possível concluir a consulta.') + '</div>');
                }
            },
            error: function() {
                $('#resultadoPagSeguro').html('<div class="alert alert-danger">Erro ao consultar. Tente novamente.</div>');
            }
        });
    }

    function ativarOrdenacao() {
        var sortDir = {};
        $('#tblResultado').on('click', 'th.sortable', function() {
            var col = $(this).data('col');
            var asc = sortDir[col] !== undefined ? !sortDir[col] : true;
            sortDir[col] = asc;

            var tbody = $('#tblResultado tbody');
            var rows = tbody.find('tr').get();

            rows.sort(function(a, b) {
                var tdA = $(a).find('td').eq(col);
                var tdB = $(b).find('td').eq(col);

                // Colunas de data trazem o valor cru em data-sort (aaaa-mm-dd): ordem lexicográfica = cronológica
                var ordA = tdA.attr('data-sort');
                var ordB = tdB.attr('data-sort');
                if (ordA !== undefined && ordB !== undefined) {
                    if (ordA === ordB) return 0;
                    if (!ordA) return 1;   // sem data vai sempre para o fim
                    if (!ordB) return -1;
                    return asc ? (ordA < ordB ? -1 : 1) : (ordA < ordB ? 1 : -1);
                }

                var cellA = tdA.text().trim().toLowerCase();
                var cellB = tdB.text().trim().toLowerCase();

                // Tentar comparar como número (para valor)
                var numA = parseFloat(cellA.replace(/[^\d,.-]/g, '').replace(',', '.'));
                var numB = parseFloat(cellB.replace(/[^\d,.-]/g, '').replace(',', '.'));
                if (!isNaN(numA) && !isNaN(numB)) {
                    return asc ? numA - numB : numB - numA;
                }

                // Comparar como texto
                if (cellA < cellB) return asc ? -1 : 1;
                if (cellA > cellB) return asc ? 1 : -1;
                return 0;
            });

            tbody.empty();
            $.each(rows, function(i, row) { tbody.append(row); });

            // Atualizar ícone
            $('#tblResultado th.sortable i').removeClass('fa-sort-asc fa-sort-desc').addClass('fa-sort').css('color', '#999');
            $(this).find('i').removeClass('fa-sort').addClass(asc ? 'fa-sort-asc' : 'fa-sort-desc').css('color', '#333');
        });
    }
</script>

        <form id="formConsultaPagSeguro" name="formConsultaPagSeguro">
            <div class="row" style="margin-bottom: 10px;">
                <div class="col-sm-4">
                    <label for="transacao_id">Código da Transação:</label>
                    <input type="text" id="transacao_id" name="transacao_id"
                           class="form-control input-sm" placeholder="Ex: XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX" autofocus>
                </div>
                <div class="col-sm-3" style="padding-top: 24px;">
                    <button class="btn btn-sm btn-primary" type="submit">Buscar</button>
                    <button class="btn btn-sm btn-default" type="button" id="btnLimparTopo">Limpar</button>
                </div>
            </div>
            <hr style="margin:5px 0 10px 0;">
            <div class="row" style="margin-bottom: 5px;">
                <div class="col-sm-3">
                    <label for="filtro_nome">Nome (ou parte):</label>
                    <input type="text" id="filtro_nome" name="filtro_nome"
                           class="form-control input-sm" placeholder="Ex: João Silva">
                </div>
                <div class="col-sm-3">
                    <label for="filtro_email">E-mail:</label>
                    <input type="text" id="filtro_email" name="filtro_email"
                           class="form-control input-sm" placeholder="Ex: joao@email.com">
                </div>
                <div class="col-sm-3">
                    <label for="filtro_referencia">Cód. Referência (pedido):</label>
                    <input type="text" id="filtro_referencia" name="filtro_referencia"
                           class="form-control input-sm" placeholder="Ex: 123 ou parte do código">
                </div>
            </div>
            <div class="row" style="margin-bottom: 10px;">
                <div class="col-sm-2">
                    <label for="filtro_status">Status:</label>
                    <select id="filtro_status" name="filtro_status" class="form-control input-sm">
                        <option value="">-- Todos --</option>
                        <?php foreach ($listaStatus as $st): ?>
                        <option value="<?php echo htmlspecialchars($st) ?>"><?php echo htmlspecialchars($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label for="filtro_pag_status">Pgto Sistema:</label>
                    <select id="filtro_pag_status" name="filtro_pag_status" class="form-control input-sm">
                        <option value="">-- Todos --</option>
                        <option value="ATIVADA">Ativado</option>
                        <option value="DESATIVADA">Desativado</option>
                        <option value="CANCELADO">Cancelado</option>
                        <option value="SEM_VINCULO">Sem vínculo</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label for="filtro_maquininha">Maquininha:</label>
                    <select id="filtro_maquininha" name="filtro_maquininha" class="form-control input-sm">
                        <option value="">-- Todas --</option>
                        <?php foreach ($listaMaquininhas as $maq): ?>
                        <option value="<?php echo htmlspecialchars($maq) ?>"><?php echo htmlspecialchars($maq) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row" style="margin-bottom: 10px;">
                <div class="col-sm-2">
                    <label for="filtro_data_ini">Período de:</label>
                    <input type="date" id="filtro_data_ini" name="filtro_data_ini" class="form-control input-sm">
                </div>
                <div class="col-sm-2">
                    <label for="filtro_data_fim">Até:</label>
                    <input type="date" id="filtro_data_fim" name="filtro_data_fim" class="form-control input-sm">
                </div>
                <div class="col-sm-2" style="padding-top: 24px;">
                    <button class="btn btn-sm btn-info" type="button" id="btnBuscarFiltros">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                    <button class="btn btn-sm btn-default" type="button" id="btnLimpar">Limpar</button>
                </div>
            </div>
        </form>
        <hr style="margin:12px 0;">
        <div id="resultadoPagSeguro" style="margin-top:15px;"></div>
    </div><!-- panel-body -->
</div><!-- panel -->
</div><!-- panel-group -->
