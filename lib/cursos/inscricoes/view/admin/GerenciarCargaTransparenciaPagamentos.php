<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)
    && !FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_CARGA_TRANSPARENCIA, 5, false, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/acesso-negado.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}
$objSRV = new GerenciarCargaTransparenciaAC();
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<script type="text/javascript">

    // ===== VARIAVEIS GLOBAIS =====
    var _cargaCodArquivo = null;
    var _cargaReprocessar = false;
    var _excluirCodArquivo = null;
    var _publicarTipo = 'pagamento';
    var _publicarIds = [];
    var _rejeitarTipo = 'pagamento';
    var _rejeitarIds = [];
    // ===== DOCUMENT READY =====
    $(document).ready(function() {

        // Carregar dados iniciais
        carregarTabelaArquivos();
        carregarArquivosSelect();
        carregarTabelaPagamentos();

        // Botoes de envio
        $('#btnSalvarPagamentos').click(function() {
            salvarArquivo();
        });
        $('#btnLimparPagamentos').click(function() {
            limparCampos();
        });

        // Delegated events - Arquivos
        $(document).on('click', '.excluir', function() {
            var id = $(this).parents("tr").attr("id");
            excluirArquivo(id);
        });
        $(document).on('click', '.botaoCarga', function() {
            $('html,body').scrollTop(0);
            var id = $(this).parents("tr").attr("id");
            carregarCargaDados(id);
        });
        $(document).on('click', '.botaoReprocessar', function() {
            $('html,body').scrollTop(0);
            var id = $(this).parents("tr").attr("id");
            carregarCargaDados(id, true);
        });
        $(document).on('click', '.botaoVisualizar', function() {
            var id = $(this).parents("tr").attr("id");
            visualizarDadosCarga(id);
        });
        $(document).on('click', '.mostrarLog', function(event) {
            event.preventDefault();
            $(this).closest('tr').find('.messageLog').toggle();
        });

        // Filtros - Arquivos
        $('#btnFiltrarArqPagamentos').click(function() {
            carregarTabelaArquivos();
        });
        $('#btnLimparFiltroArqPagamentos').click(function() {
            $('#statusFiltroPagamentos').val('');
            $('#nomeArquivoFiltroPagamentos').val('');
            $('#dataDePagamentos').val('');
            $('#dataAtePagamentos').val('');
            carregarTabelaArquivos();
        });

        // Filtros - Registros
        $('#btnFiltrarPagamentos').click(function() {
            carregarTabelaPagamentos();
        });
        $('#filtroBuscaPagamento, #filtroFornecedorPagamento, #filtroCnpjPagamento, #filtroNrProjetoPagamento').keypress(function(e) {
            if (e.which === 13) carregarTabelaPagamentos();
        });

        // Confirmar publicacao
        $('#btnConfirmarPublicar').click(function() {
            $('#modalConfirmarPublicar').modal('hide');
            executarPublicacaoEmLote();
        });

        // Confirmar rejeicao
        $('#btnConfirmarRejeitar').click(function() {
            $('#modalConfirmarRejeitar').modal('hide');
            executarRejeicaoEmLote();
        });
    });

    // ===== SECOES (Envio / Registros) =====
    function mostrarSecao(secaoId) {
        $('.secao-conteudo').hide();
        $('#' + secaoId).show();
        $('.submenu-btn').removeClass('submenu-btn-ativo');
        $('.submenu-btn[data-secao="' + secaoId + '"]').addClass('submenu-btn-ativo');
    }

    // ===== UPLOAD DE ARQUIVO =====
    function salvarArquivo() {
        $('#formArquivoPagamentos').ajaxSubmit({
            target: '#mensagem'
            , dataType: 'json'
            , cache: false
            , beforeSend: function() {
                startLoading();
            }
            , timeout: 30000
            , error: function(ajax) {
                showResponse(ajax);
            }
            , success: function(data) {
                $('#modalUploadPagamentos').modal('hide');
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaArquivos();
                    limparCampos('formArquivoPagamentos');
                    carregarArquivosSelect();
                    if ($('#chkCarregarAutoPagamentos').is(':checked') && data.codArquivo) {
                        setTimeout(function() {
                            carregarCargaDados(data.codArquivo);
                        }, 500);
                    }
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }

    // ===== TABELA DE ARQUIVOS =====
    function carregarTabelaArquivos() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarCargaTransparencia',
                'm': 'carregarTabelaArquivos',
                'tipoFiltro': 'CARGA_TRANSPARENCIA_PAGAMENTOS',
                'statusFiltro': $('#statusFiltroPagamentos').val() || '',
                'nomeArquivoFiltro': $('#nomeArquivoFiltroPagamentos').val() || '',
                'dataDe': $('#dataDePagamentos').val() || '',
                'dataAte': $('#dataAtePagamentos').val() || '',
                '_submit_check': '1'
            },
            dataType: "html",
            cache: false,
            beforeSend: function() {
                $('#listaArquivosPagamentos').html('<div class="text-center text-muted" style="padding:20px 0;"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');
            },
            success: function(html) {
                $('#listaArquivosPagamentos').html(html);
            },
            error: function(xhr, status, error) {
                $('#listaArquivosPagamentos').html('<div class="text-center text-danger" style="padding:20px 0;">Erro ao carregar: ' + error + '</div>');
            }
        });
    }

    // ===== CARGA DE DADOS =====
    function carregarCargaDados(codArquivo, reprocessar) {
        if (!codArquivo) {
            loading('div#erro', 'show');
            $('div#erro').html('C&oacute;digo do arquivo n&atilde;o foi informado!');
            return;
        }
        _cargaReprocessar = reprocessar || false;
        verificarConflitosAntes(codArquivo);
    }

    function verificarConflitosAntes(codArquivo) {
        _cargaCodArquivo = codArquivo;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codArquivo': codArquivo,
                'c': 'GerenciarCargaTransparencia',
                'm': 'verificarConflitos',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                stopLoading();
                if (data.statusMessage == '0') {
                    loading('div#erro', 'show');
                    $('div#erro').html(data.message);
                    return;
                }
                if (data.temConflitos && data.conflitos.length > 0) {
                    montarModalConflitos(data);
                    $('#modalConflitos').modal('show');
                } else {
                    executarCargaDados(codArquivo, 'pular');
                }
            },
            error: function(xhr, status, error) {
                stopLoading();
                var msg = 'Erro ao verificar conflitos: ' + error;
                if (xhr.responseText) {
                    msg += '<br><small>' + $('<span>').text(xhr.responseText.substring(0, 500)).html() + '</small>';
                }
                loading('div#erro', 'show');
                $('div#erro').html(msg);
            }
        });
    }

    function montarModalConflitos(data) {
        var tipoLabel = 'pagamento(s)';

        var html = '<p><strong>' + data.novos + '</strong> ' + tipoLabel + ' novo(s) ser&atilde;o inseridos.</p>';
        html += '<p><strong>' + data.conflitos.length + '</strong> ' + tipoLabel + ' j&aacute; existem no banco:</p>';
        html += '<div class="table-responsive"><table class="table table-bordered table-condensed" style="font-size:12px;">';

        var colChave = 'Fornecedor / Projeto';
        var campos = [
            {key: 'valor_bruto', label: 'Valor Bruto'},
            {key: 'valor_liquido', label: 'Valor L&iacute;quido'},
            {key: 'vencimento', label: 'Vencimento'},
            {key: 'servico', label: 'Servi&ccedil;o'},
            {key: 'cnpj', label: 'CNPJ'},
            {key: 'tipo_pagamento', label: 'Tipo Pagamento'}
        ];

        html += '<thead><tr><th>' + colChave + '</th><th>Campo</th><th style="background:#f2dede;">Atual (banco)</th><th style="background:#dff0d8;">Novo (arquivo)</th></tr></thead><tbody>';

        for (var i = 0; i < data.conflitos.length; i++) {
            var c = data.conflitos[i];
            var primeiro = true;
            var linhasConflito = '';

            var chaveTexto = (c.novo.fornecedor || '') + ' / ' + (c.novo.numero_projeto || '');

            for (var j = 0; j < campos.length; j++) {
                var atual = (c.atual[campos[j].key] || '').toString().trim();
                var novo  = (c.novo[campos[j].key] || '').toString().trim();
                var diff  = (atual !== novo);

                linhasConflito += '<tr>';
                if (primeiro) {
                    linhasConflito += '<td rowspan="' + campos.length + '" style="vertical-align:middle;font-weight:bold;">' + $('<span>').text(chaveTexto).html() + '</td>';
                    primeiro = false;
                }
                linhasConflito += '<td>' + campos[j].label + '</td>';
                linhasConflito += '<td' + (diff ? ' style="background:#f2dede;font-weight:bold;"' : '') + '>' + $('<span>').text(atual).html() + '</td>';
                linhasConflito += '<td' + (diff ? ' style="background:#dff0d8;font-weight:bold;"' : '') + '>' + $('<span>').text(novo).html() + '</td>';
                linhasConflito += '</tr>';
            }
            html += linhasConflito;
            if (i < data.conflitos.length - 1) {
                html += '<tr><td colspan="4" style="border:0;padding:2px;"></td></tr>';
            }
        }
        html += '</tbody></table></div>';

        $('#modalConflitosBody').html(html);
    }

    function confirmarCargaSubstituir() {
        $('#modalConflitos').modal('hide');
        executarCargaDados(_cargaCodArquivo, 'substituir');
    }

    function confirmarCargaPular() {
        $('#modalConflitos').modal('hide');
        executarCargaDados(_cargaCodArquivo, 'pular');
    }

    function executarCargaDados(codArquivo, acaoExistentes) {
        var substituir = _cargaReprocessar ? '1' : '0';
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codEvento': '<?php echo COD_EVENTO ?>',
                'codArquivo': codArquivo,
                'substituir': substituir,
                'acao_existentes': acaoExistentes,
                'c': 'GerenciarCargaTransparencia',
                'm': 'carregarCargaDados',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJsonSemFade(data);
                if (data.statusMessage) {
                    carregarTabelaArquivos();
                    carregarTabelaPagamentos();
                    carregarArquivosSelect();
                }
            },
            error: function(xhr, status, error) {
                stopLoading();
                var msg = 'Erro ao carregar dados: ' + error;
                if (xhr.responseText) {
                    msg += '<br><small>' + $('<span>').text(xhr.responseText.substring(0, 500)).html() + '</small>';
                }
                loading('div#erro', 'show');
                $('div#erro').html(msg);
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== EXCLUSAO DE ARQUIVO =====
    function excluirArquivo(codArquivo) {
        _excluirCodArquivo = codArquivo;
        $('#modalExcluir').modal('show');
    }

    function confirmarExclusao() {
        $('#modalExcluir').modal('hide');
        if (!_excluirCodArquivo) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codArquivo': _excluirCodArquivo, 'c': 'GerenciarCargaTransparencia', 'm': 'excluirArquivo', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaArquivos();
                    carregarTabelaPagamentos();
                    carregarArquivosSelect();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== VISUALIZAR DADOS =====
    function visualizarDadosCarga(codArquivo) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codArquivo': codArquivo,
                'c': 'GerenciarCargaTransparencia',
                'm': 'visualizarDadosCarga',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                stopLoading();
                if (data.statusMessage == '0') {
                    loading('div#erro', 'show');
                    $('div#erro').html(data.message);
                    return;
                }
                montarModalVisualizacao(data);
                $('#modalVisualizarDados').modal('show');
            },
            error: function(xhr, status, error) {
                stopLoading();
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao carregar dados: ' + error);
            }
        });
    }

    function montarModalVisualizacao(data) {
        var html = '';
        var titulo = 'Pagamentos Carregados (' + data.total + ')';

        if (data.tipo == 'pagamentos') {
            html += '<div class="table-responsive"><table class="table table-bordered table-condensed table-striped" style="font-size:12px;">';
            html += '<thead style="background:#dff0d8;"><tr>';
            html += '<th>N&ordm; Projeto</th><th>Fornecedor</th><th>Valor Bruto</th><th>Valor L&iacute;quido</th><th>Vencimento</th><th>Servi&ccedil;o</th><th>CNPJ</th><th>Tipo</th>';
            html += '</tr></thead><tbody>';
            for (var i = 0; i < data.dados.length; i++) {
                var d = data.dados[i];
                html += '<tr>';
                html += '<td>' + (d.numero_projeto || '') + '</td>';
                html += '<td>' + (d.fornecedor || '') + '</td>';
                html += '<td>' + (d.valor_bruto || '') + '</td>';
                html += '<td>' + (d.valor_liquido || '') + '</td>';
                html += '<td>' + (d.vencimento || '') + '</td>';
                html += '<td>' + (d.servico || '') + '</td>';
                html += '<td>' + (d.cnpj || '') + '</td>';
                html += '<td>' + (d.tipo_rpa || '') + '</td>';
                html += '</tr>';
            }
            html += '</tbody></table></div>';
        }

        if (data.total == 0) {
            html = '<div class="alert alert-warning">Nenhum registro encontrado para este arquivo.</div>';
        }

        $('#modalVisualizarTitulo').text(titulo);
        $('#modalVisualizarBody').html(html);
    }

    // ===== LIMPAR CAMPOS =====
    function limparCampos(formId) {
        var $form = formId ? $('#' + formId) : $('#formArquivoPagamentos');
        $form.find('input[type="file"]').val('');
        $form.find('.nomeArquivoTransp').val('');
        $form.find('input[name="codArquivo"]').val('');
        $form.find('input[name="novoCodArquivo"]').val('0');
    }

    // ===== ARQUIVOS (populate selects dos filtros de registros) =====
    function carregarArquivosSelect() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'carregarArquivosCarga',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data && data.arquivos) {
                    var optsPagamento = '<option value="">Todos</option>';
                    for (var i = 0; i < data.arquivos.length; i++) {
                        var arq = data.arquivos[i];
                        if (arq.tipo === 'CARGA_TRANSPARENCIA_PAGAMENTOS') {
                            optsPagamento += '<option value="' + arq.id + '">' + arq.nome + '</option>';
                        }
                    }
                    $('#filtroArquivoPagamento').html(optsPagamento);
                }
            }
        });
    }

    // ===== TABELA REGISTROS PAGAMENTOS =====
    function carregarTabelaPagamentos() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'carregarTabelaPagamentos',
                'status_publicacao': $('#filtroStatusPagamento').val(),
                'cod_arquivo': $('#filtroArquivoPagamento').val(),
                'busca': $('#filtroBuscaPagamento').val(),
                'fornecedor': $('#filtroFornecedorPagamento').val(),
                'cnpj': $('#filtroCnpjPagamento').val(),
                'numero_projeto': $('#filtroNrProjetoPagamento').val(),
                'data_de': $('#filtroDataDePagamento').val(),
                'data_ate': $('#filtroDataAtePagamento').val(),
                '_submit_check': '1'
            },
            dataType: "html",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(html) {
                $('#listaPagamentos').html(html);
                bindCheckboxSelecionar();
            },
            error: function(xhr, status, error) {
                $('#listaPagamentos').html('<div class="text-center text-danger" style="padding:20px 0;">Erro ao carregar: ' + error + '</div>');
            },
            complete: function() { stopLoading(); }
        });
    }

    // Rebind checkboxes apos carregar tabela
    function bindCheckboxSelecionar() {
        $('#listaPagamentos').off('change', '#chkHeadPagamento').on('change', '#chkHeadPagamento', function() {
            $('#listaPagamentos input.chk-registro').prop('checked', $(this).is(':checked'));
        });
    }

    // ===== EDITAR PAGAMENTO =====
    function abrirEditarPagamento(id) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'editarPagamento',
                'id': id,
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                stopLoading();
                if (data.statusMessage == '0') {
                    loading('div#erro', 'show');
                    $('div#erro').html(data.message);
                    return;
                }
                $('#editPagamentoId').val(data.id);
                $('#editPagamentoNumeroProjeto').val(data.numero_projeto);
                $('#editPagamentoFornecedor').val(data.fornecedor);
                $('#editPagamentoValorBruto').val(data.valor_bruto);
                $('#editPagamentoValorLiquido').val(data.valor_liquido);
                $('#editPagamentoVencimento').val(data.vencimento);
                $('#editPagamentoServico').val(data.servico);
                $('#editPagamentoCnpj').val(data.cnpj);
                $('#editPagamentoTipoRpa').val(data.tipo_rpa);
                $('#modalEditarPagamento').modal('show');
            },
            error: function(xhr, status, error) {
                stopLoading();
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao carregar pagamento: ' + error);
            }
        });
    }

    function salvarPagamento() {
        $('#formEditarPagamento').ajaxSubmit({
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    $('#modalEditarPagamento').modal('hide');
                    carregarTabelaPagamentos();
                }
            },
            error: function(ajax) {
                showResponse(ajax);
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== PUBLICAR SELECIONADOS =====
    function publicarSelecionados(tipo) {
        var ids = coletarIdsSelecionados();
        if (ids.length === 0) {
            loading('div#erro', 'show');
            $('div#erro').html('Nenhum registro selecionado.');
            return;
        }
        _publicarTipo = tipo;
        _publicarIds = ids;
        $('#qtdPublicar').text(ids.length);
        $('#modalConfirmarPublicar').modal('show');
    }

    function executarPublicacaoEmLote() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'publicarEmLote',
                'tipo': _publicarTipo,
                'ids': JSON.stringify(_publicarIds),
                'status': _publicarStatus,
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    carregarTabelaPagamentos();
                }
            },
            error: function(ajax) {
                showResponse(ajax);
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== REJEITAR SELECIONADOS =====
    function rejeitarSelecionados(tipo) {
        var ids = coletarIdsSelecionados();
        if (ids.length === 0) {
            loading('div#erro', 'show');
            $('div#erro').html('Nenhum registro selecionado.');
            return;
        }
        _rejeitarTipo = tipo;
        _rejeitarIds = ids;
        $('#qtdRejeitar').text(ids.length);
        $('#modalConfirmarRejeitar').modal('show');
    }

    function executarRejeicaoEmLote() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'rejeitarRegistro',
                'tipo': _rejeitarTipo,
                'ids': JSON.stringify(_rejeitarIds),
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    carregarTabelaPagamentos();
                }
            },
            error: function(ajax) {
                showResponse(ajax);
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== PUBLICAR REGISTRO INDIVIDUAL =====
    var _publicarStatus = 'PUBLICADO';

    function publicarRegistro(tipo, id, status) {
        _publicarTipo = tipo;
        _publicarIds = [id];
        _publicarStatus = status || 'PUBLICADO';
        $('#qtdPublicar').text(1);
        if (_publicarStatus === 'RASCUNHO') {
            $('#modalConfirmarPublicar .modal-title').html('<i class="fa fa-eye-slash"></i> Confirmar Despublica\u00e7\u00e3o');
            $('#modalConfirmarPublicar .modal-header').css('background-color', '#d9534f').css('border-color', '#d43f3a');
            $('#modalConfirmarPublicar .modal-body p:first').html('Deseja despublicar <strong>1</strong> registro(s) selecionado(s)?');
            $('#modalConfirmarPublicar .modal-body p:last').html('Ap\u00f3s despublicados, os registros <strong>n\u00e3o</strong> ficar\u00e3o vis\u00edveis no Portal da Transpar\u00eancia.');
            $('#btnConfirmarPublicar').removeClass('btn-success').addClass('btn-danger').html('<i class="fa fa-eye-slash"></i> Despublicar');
        } else {
            $('#modalConfirmarPublicar .modal-title').html('<i class="fa fa-check-circle"></i> Confirmar Publica\u00e7\u00e3o');
            $('#modalConfirmarPublicar .modal-header').css('background-color', '').css('border-color', '');
            $('#modalConfirmarPublicar .modal-body p:first').html('Deseja publicar <strong>' + _publicarIds.length + '</strong> registro(s) selecionado(s)?');
            $('#modalConfirmarPublicar .modal-body p:last').html('Ap\u00f3s publicados, os registros ficar\u00e3o vis\u00edveis no Portal da Transpar\u00eancia.');
            $('#btnConfirmarPublicar').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-check"></i> Publicar');
        }
        $('#modalConfirmarPublicar').modal('show');
    }

    // ===== HELPERS =====
    function coletarIdsSelecionados() {
        var ids = [];
        $('#listaPagamentos input[type="checkbox"].chk-registro:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }
</script>

<div class="page-header">
    <div class="page-context">&Aacute;rea Administrativa</div>
</div>
<?php
require_once(dirname(__FILE__)."/area_admin_menu_administracao.php");
?>

<!-- Divs flutuantes de sucesso/erro -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<style>
    .secao-titulo {
        font-size: 14px;
        font-weight: bold;
        color: #555;
        margin: 18px 0 10px 0;
        padding-bottom: 5px;
        border-bottom: 1px solid #eee;
    }
</style>

<!-- Sub-menu: Envio / Registros -->
<div class="submenu-area" style="margin-bottom:0;">
    <a class="submenu-btn submenu-btn-ativo" href="#" data-secao="secEnvioPagamentos" onclick="mostrarSecao('secEnvioPagamentos'); return false;"><i class="fa fa-upload"></i> Envio de Arquivos</a>
    <a class="submenu-btn" href="#" data-secao="secRegistrosPagamentos" onclick="mostrarSecao('secRegistrosPagamentos'); return false;"><i class="fa fa-list-alt"></i> Publicar Registros</a>
</div>


<!-- ===== SECAO: ENVIO DE ARQUIVOS (Pagamentos) ===== -->
<div id="secEnvioPagamentos" class="secao-conteudo">

<!-- Arquivos Enviados -->
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 18px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:16px;"><i class="fa fa-files-o"></i> Arquivos Enviados &mdash; Pagamentos</h4>
        <div>
            <button type="button" class="btn btn-sm btn-info" onclick="$('#modalModeloPagamentos').modal('show');"><i class="fa fa-info-circle"></i> Modelo CSV</button>
            <button type="button" class="abriModal" onclick="$('#modalUploadPagamentos').modal('show');"><i class="fa fa-upload"></i> Enviar Arquivo</button>
        </div>
    </div>
    <div style="padding:12px 18px;">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-sm-2">
                <label class="label-filtro" for="statusFiltroPagamentos">Situa&ccedil;&atilde;o:</label>
                <select class="form-control input-sm" id="statusFiltroPagamentos">
                    <option value="">Todos</option>
                    <option value="AGUARDANDO">Aguardando</option>
                    <option value="CARREGADO">Carregado</option>
                    <option value="PARCIALMENTE_CARREGADO">Parcialmente Carregado</option>
                    <option value="ASSOCIADO">Associado</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label class="label-filtro" for="nomeArquivoFiltroPagamentos">Nome do Arquivo:</label>
                <input type="text" class="form-control input-sm" id="nomeArquivoFiltroPagamentos" placeholder="Parte do nome">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro" for="dataDePagamentos">Data De:</label>
                <input type="date" class="form-control input-sm" id="dataDePagamentos">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro" for="dataAtePagamentos">Data At&eacute;:</label>
                <input type="date" class="form-control input-sm" id="dataAtePagamentos">
            </div>
            <div class="col-sm-3" style="padding-top:25px;">
                <button class="btn btn-sm modalBtnSalvar" type="button" id="btnFiltrarArqPagamentos"><i class="fa fa-search"></i> Filtrar</button>
                <button class="btn btn-sm modalBtnCancelar" type="button" id="btnLimparFiltroArqPagamentos">Limpar</button>
            </div>
        </div>
    </div>
    <div id="listaArquivosPagamentos">
        <div class="text-center text-muted" style="padding:20px 0;"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>
    </div>
</div>

</div><!-- /secEnvioPagamentos -->

<!-- ===== SECAO: REGISTROS (Pagamentos) ===== -->
<div id="secRegistrosPagamentos" class="secao-conteudo" style="display:none;">
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 18px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:16px;"><i class="fa fa-list-alt"></i> Registros de Pagamentos</h4>
        <div>
            <button class="btn btn-sm btn-success" type="button" onclick="publicarSelecionados('pagamento')"><i class="fa fa-check"></i> Publicar Selecionados</button>
            <button class="btn btn-sm btn-danger" type="button" onclick="rejeitarSelecionados('pagamento')"><i class="fa fa-times"></i> Rejeitar Selecionados</button>
        </div>
    </div>
    <div style="padding:12px 18px;">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-sm-2">
                <label class="label-filtro">Status:</label>
                <select class="form-control input-sm" id="filtroStatusPagamento">
                    <option value="">Todos</option>
                    <option value="RASCUNHO">Rascunho</option>
                    <option value="PUBLICADO">Publicado</option>
                </select>
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">Arquivo:</label>
                <select class="form-control input-sm" id="filtroArquivoPagamento">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">Fornecedor:</label>
                <input type="text" class="form-control input-sm" id="filtroFornecedorPagamento" placeholder="Fornecedor">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">CNPJ:</label>
                <input type="text" class="form-control input-sm" id="filtroCnpjPagamento" placeholder="CNPJ">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">N&ordm; Projeto:</label>
                <input type="text" class="form-control input-sm" id="filtroNrProjetoPagamento" placeholder="N&ordm; projeto">
            </div>
        </div>
        <div class="row" style="margin-bottom:10px;">
            <div class="col-sm-2">
                <label class="label-filtro">Vencimento De:</label>
                <input type="date" class="form-control input-sm" id="filtroDataDePagamento">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">Vencimento At&eacute;:</label>
                <input type="date" class="form-control input-sm" id="filtroDataAtePagamento">
            </div>
            <div class="col-sm-2" style="padding-top:25px;">
                <button class="btn btn-sm btn-primary" type="button" id="btnFiltrarPagamentos"><i class="fa fa-search"></i> Filtrar</button>
            </div>
        </div>
    </div>
    <div id="listaPagamentos">
        <div class="text-center text-muted" style="padding:30px 0;"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>
    </div>
</div>

</div><!-- /secRegistrosPagamentos -->


<!-- ===== MODAL MODELO CSV - PAGAMENTOS ===== -->
<?php $pathModelos = WB_PATH . '/resources/public/transparencia/'; ?>
<div class="modal fade" id="modalModeloPagamentos" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-info-circle"></i> Modelo CSV &mdash; Pagamentos</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="max-height:500px; overflow-y:auto;">
                <div class="alert alert-info" style="font-size:12px; margin-bottom:15px;">
                    <i class="fa fa-info-circle"></i> Formato <strong>CSV</strong> separado por ponto-e-v&iacute;rgula (<code>;</code>). O formato &eacute; detectado automaticamente pelo cabe&ccedil;alho. Clique no formato para ver detalhes.
                </div>

                <style>
                    .sanfona-titulo { display:block; padding:10px 15px; background:#f5f6f8; border:1px solid #e0e0e0; border-radius:4px; margin-bottom:4px; cursor:pointer; font-size:13px; font-weight:600; color:#555; text-decoration:none; }
                    .sanfona-titulo:hover { background:#eef0f3; color:#333; text-decoration:none; }
                    .sanfona-titulo i.fa-chevron-right { transition:transform 0.2s; margin-right:8px; font-size:11px; }
                    .sanfona-titulo.aberto i.fa-chevron-right { transform:rotate(90deg); }
                    .sanfona-conteudo { display:none; padding:12px 15px; border:1px solid #e0e0e0; border-top:0; border-radius:0 0 4px 4px; margin-bottom:8px; margin-top:-4px; }
                </style>

                <!-- RPA com ID -->
                <a class="sanfona-titulo" onclick="$(this).toggleClass('aberto').next('.sanfona-conteudo').slideToggle(200);">
                    <i class="fa fa-chevron-right"></i> <i class="fa fa-money"></i> RPA com ID
                </a>
                <div class="sanfona-conteudo">
                    <a href="<?php echo $pathModelos ?>modelo-carga-pagamentos-rpa-com-id.csv" download class="btn btn-xs btn-default" style="margin-bottom:8px;"><i class="fa fa-download"></i> Baixar modelo</a>
                    <table class="table table-bordered table-condensed" style="font-size:12px;">
                        <thead style="background:#dff0d8;"><tr><th>#</th><th>Coluna</th><th>Obrigat&oacute;rio</th><th>Observa&ccedil;&atilde;o</th></tr></thead>
                        <tbody>
                            <tr><td>1</td><td>ID</td><td>Sim</td><td>Identificador num&eacute;rico</td></tr>
                            <tr><td>2</td><td>Valor Bruto RPA</td><td>Sim</td><td>Ex: 1500,00</td></tr>
                            <tr><td>3</td><td>Vencimento</td><td>Sim</td><td>DD/MM/AAAA</td></tr>
                            <tr><td>4</td><td>Valor L&iacute;quido</td><td>Sim</td><td>Ex: 1200,00</td></tr>
                            <tr><td>5</td><td>C&oacute;digo</td><td>N&atilde;o</td><td>C&oacute;digo interno</td></tr>
                            <tr><td>6</td><td>Servi&ccedil;o</td><td>Sim</td><td>Ex: Consultoria Tecnica</td></tr>
                            <tr><td>7</td><td>Projeto</td><td>Sim</td><td><code>NrProjeto-NomeProjeto</code></td></tr>
                            <tr><td>8</td><td>Fich&aacute;rio</td><td>Sim</td><td>Fornecedor/prestador</td></tr>
                            <tr><td>9</td><td>CNPJ</td><td>N&atilde;o</td><td>CPF ou CNPJ</td></tr>
                            <tr><td>10</td><td>Tipo de RPA</td><td>N&atilde;o</td><td>Ex: RPA, NFSe</td></tr>
                        </tbody>
                    </table>
                    <pre style="font-size:11px; background:#f5f5f5; padding:10px; overflow-x:auto;">ID;Valor Bruto RPA;Vencimento;Valor Liquido;Codigo;Servico;Projeto;Fichario;CNPJ;Tipo de RPA
1;1500,00;15/03/2024;1200,00;COD001;Consultoria Tecnica;001/2024-Projeto Exemplo;Empresa Modelo Ltda;12.345.678/0001-99;RPA</pre>
                </div>

                <!-- RPA sem ID -->
                <a class="sanfona-titulo" onclick="$(this).toggleClass('aberto').next('.sanfona-conteudo').slideToggle(200);">
                    <i class="fa fa-chevron-right"></i> <i class="fa fa-money"></i> RPA sem ID
                </a>
                <div class="sanfona-conteudo">
                    <a href="<?php echo $pathModelos ?>modelo-carga-pagamentos-rpa-sem-id.csv" download class="btn btn-xs btn-default" style="margin-bottom:8px;"><i class="fa fa-download"></i> Baixar modelo</a>
                    <p style="font-size:12px;">Mesmo formato do RPA com ID, por&eacute;m <strong>sem a coluna ID</strong>. A detec&ccedil;&atilde;o &eacute; autom&aacute;tica.</p>
                    <p style="font-size:12px;"><strong>Exemplo:</strong></p>
                    <pre style="font-size:11px; background:#f5f5f5; padding:10px; overflow-x:auto;">Valor Bruto RPA;Vencimento;Valor Liquido;Codigo;Servico;Projeto;Fichario;CNPJ;Tipo de RPA
1500,00;15/03/2024;1200,00;COD001;Consultoria Tecnica;001/2024-Projeto Exemplo;Empresa Modelo Ltda;12.345.678/0001-99;RPA</pre>
                </div>

                <!-- RPA com ID Duplo -->
                <a class="sanfona-titulo" onclick="$(this).toggleClass('aberto').next('.sanfona-conteudo').slideToggle(200);">
                    <i class="fa fa-chevron-right"></i> <i class="fa fa-money"></i> RPA com ID Duplo
                </a>
                <div class="sanfona-conteudo">
                    <p style="font-size:12px;">Varia&ccedil;&atilde;o com <strong>duas colunas ID</strong> no in&iacute;cio. O sistema detecta automaticamente.</p>
                    <p style="font-size:12px;"><strong>Exemplo:</strong></p>
                    <pre style="font-size:11px; background:#f5f5f5; padding:10px; overflow-x:auto;">ID;ID;Valor Bruto RPA;Vencimento;;Valor Liquido;Codigo;Servico;;Projeto;Fichario;CNPJ;Tipo de RPA
1;101;1500,00;15/03/2024;;1200,00;COD001;Consultoria Tecnica;;001/2024-Projeto Exemplo;Empresa Modelo Ltda;12.345.678/0001-99;RPA</pre>
                </div>

                <!-- Bolsa / PREDUC -->
                <a class="sanfona-titulo" onclick="$(this).toggleClass('aberto').next('.sanfona-conteudo').slideToggle(200);">
                    <i class="fa fa-chevron-right"></i> <i class="fa fa-graduation-cap"></i> Bolsa / PREDUC
                </a>
                <div class="sanfona-conteudo">
                    <a href="<?php echo $pathModelos ?>modelo-carga-pagamentos-bolsa.csv" download class="btn btn-xs btn-default" style="margin-bottom:8px;"><i class="fa fa-download"></i> Baixar modelo</a>
                    <p style="font-size:12px;">Formato espec&iacute;fico para pagamentos de bolsa. Cabe&ccedil;alho come&ccedil;a com <code>Nome;Vinculo;</code></p>
                    <table class="table table-bordered table-condensed" style="font-size:12px;">
                        <thead style="background:#dff0d8;"><tr><th>#</th><th>Coluna</th><th>Observa&ccedil;&atilde;o</th></tr></thead>
                        <tbody>
                            <tr><td>1</td><td>Nome</td><td>Nome do bolsista (fornecedor)</td></tr>
                            <tr><td>2</td><td>Vinculo</td><td>Ex: servidor p&uacute;blico</td></tr>
                            <tr><td>3</td><td>Nr Projeto</td><td>Ex: 001/2024</td></tr>
                            <tr><td>4</td><td>Projeto</td><td>Nome do projeto</td></tr>
                            <tr><td>5</td><td>Eprotocolo</td><td><em>Opcional</em> &mdash; Se presente, desloca as colunas seguintes</td></tr>
                            <tr><td>6</td><td>Data Inicio</td><td>DD/MM/AAAA</td></tr>
                            <tr><td>7</td><td>Data Fim</td><td>DD/MM/AAAA</td></tr>
                            <tr><td>8</td><td>Coordenador</td><td>Nome do coordenador</td></tr>
                            <tr><td>9</td><td>Atividade</td><td>Descri&ccedil;&atilde;o da atividade (servi&ccedil;o)</td></tr>
                            <tr><td>10</td><td>Pagamentos</td><td>Data do pagamento DD/MM/AAAA</td></tr>
                            <tr><td>11</td><td>Tipo</td><td>Ex: PREDUC</td></tr>
                            <tr><td>12</td><td>Valor Bruto</td><td>Ex: 1500,00</td></tr>
                            <tr><td>13</td><td>Valor L&iacute;quido</td><td>Ex: 1200,00</td></tr>
                        </tbody>
                    </table>
                    <p style="font-size:12px;"><strong>Exemplo (com Eprotocolo):</strong></p>
                    <pre style="font-size:11px; background:#f5f5f5; padding:10px; overflow-x:auto;">Nome;Vinculo;Nr Projeto;Projeto;Eprotocolo;Data Inicio;Data Fim;Coordenador;Atividade;Pagamentos;Tipo;Valor Bruto;Valor Liquido
Mariana Exemplo Santos;servidor publico;001/2024;Projeto Exemplo;CONV - EXEMPLO;01/03/2024;01/03/2025;Carlos Eduardo Lima;Bolsa Pesquisa;15/04/2024;PREDUC;1500,00;1200,00</pre>
                    <p style="font-size:12px;"><strong>Exemplo (sem Eprotocolo):</strong></p>
                    <pre style="font-size:11px; background:#f5f5f5; padding:10px; overflow-x:auto;">Nome;Vinculo;Nr Projeto;Projeto;Data Inicio;Data Fim;Coordenador;Atividade;Pagamentos;Tipo;Valor Bruto;Valor Liquido
Pedro Ficticio Oliveira;celetista;002/2024;Projeto Teste;01/06/2024;01/06/2025;Fernanda Oliveira Costa;Bolsa Extensao;20/05/2024;PREDUC;2000,00;1600,00</pre>
                </div>

                <div class="alert alert-warning" style="font-size:12px; margin-top:15px; margin-bottom:0;">
                    <i class="fa fa-exclamation-triangle"></i> <strong>Observa&ccedil;&otilde;es gerais:</strong>
                    <ul style="margin-top:5px; margin-bottom:0;">
                        <li>Separador: ponto-e-v&iacute;rgula (<code>;</code>)</li>
                        <li>Codifica&ccedil;&atilde;o aceita: UTF-8 ou Windows-1252 (ISO-8859-1). O sistema converte automaticamente.</li>
                        <li>A primeira linha do arquivo deve conter o cabe&ccedil;alho (nomes das colunas).</li>
                        <li>Linhas vazias no in&iacute;cio do arquivo s&atilde;o ignoradas automaticamente.</li>
                        <li>Se o arquivo j&aacute; foi enviado anteriormente com o mesmo nome, ele ser&aacute; substitu&iacute;do.</li>
                        <li><strong>Detec&ccedil;&atilde;o de duplicados:</strong> ao carregar, o sistema compara cada registro com o banco e permite escolher entre <em>Substituir</em> ou <em>Pular</em>.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL UPLOAD PAGAMENTOS ===== -->
<div class="modal fade" id="modalUploadPagamentos" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-upload"></i> Enviar Arquivo CSV &mdash; Pagamentos</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formArquivoPagamentos" name="formArquivoPagamentos" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarCargaTransparencia" name="c">
                    <input type="hidden" value="salvarCargaTransparencia" name="m">
                    <input type="hidden" name="codEvento" value="<?php echo COD_EVENTO ?>">
                    <input type="hidden" name="tipoArquivo" value="CARGA_TRANSPARENCIA_PAGAMENTOS">
                    <input type="hidden" name="novoCodArquivo" value="0">
                    <input type="hidden" name="codArquivo" value="">

                    <div class="form-group">
                        <label class="label-filtro">Selecionar Arquivo:</label>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" onclick="$('#arquivoPagamentos').click();" style="height:34px;"><i class="fa fa-folder-open"></i> Procurar</button>
                            </span>
                            <input type="text" class="form-control nomeArquivoTransp" readonly placeholder="Nenhum arquivo selecionado" style="height:34px;">
                        </div>
                        <input type="file" name="arquivo" id="arquivoPagamentos" style="display:none;" onchange="$(this).closest('form').find('.nomeArquivoTransp').val(this.files.length ? this.files[0].name : '');">
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <label style="font-weight:normal;">
                            <input type="checkbox" id="chkCarregarAutoPagamentos" checked> Carregar automaticamente ap&oacute;s envio
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal" onclick="limparCampos('formArquivoPagamentos');">Cancelar</button>
                <button type="button" class="btn modalBtnSalvar" id="btnSalvarPagamentos"><i class="fa fa-upload"></i> Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL EXCLUIR ARQUIVO ===== -->
<div class="modal fade" id="modalExcluir" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este arquivo e <strong>todos os dados carregados</strong> (pagamentos) associados a ele?</p>
                <div class="alert alert-danger" style="margin-bottom:0;">
                    <i class="fa fa-warning"></i> Esta a&ccedil;&atilde;o &eacute; irrevers&iacute;vel.
                </div>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL CONFLITOS ===== -->
<div class="modal fade" id="modalConflitos" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho" style="background:linear-gradient(135deg, #e67e22, #f0ad4e);">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Registros j&aacute; existentes</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="modalConflitosBody" style="max-height:500px; overflow-y:auto;">
                <p>Carregando...</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" onclick="confirmarCargaPular()">
                    <i class="fa fa-forward"></i> Pular existentes
                </button>
                <button type="button" class="btn btn-warning modalBtnExcluir" onclick="confirmarCargaSubstituir()" style="background:#e67e22; border-color:#e67e22;">
                    <i class="fa fa-refresh"></i> Substituir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL VISUALIZAR DADOS ===== -->
<div class="modal fade" id="modalVisualizarDados" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document" style="width:90%;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-table"></i> <span id="modalVisualizarTitulo">Dados Carregados</span></h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="modalVisualizarBody" style="max-height:500px; overflow-y:auto;">
                <p>Carregando...</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL EDITAR PAGAMENTO ===== -->
<div class="modal fade" id="modalEditarPagamento" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-edit"></i> Editar Pagamento</h4>
            </div>
            <div class="modal-body">
                <form id="formEditarPagamento" name="formEditarPagamento" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <input type="hidden" name="c" value="GerenciarRegistrosTransparencia">
                    <input type="hidden" name="m" value="salvarPagamento">
                    <input type="hidden" name="_submit_check" value="1">
                    <input type="hidden" name="id" id="editPagamentoId">

                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoNumeroProjeto">N&ordm; Projeto:</label>
                            <input type="text" class="form-control" id="editPagamentoNumeroProjeto" name="numero_projeto">
                        </div>
                        <div class="form-group col-sm-8">
                            <label for="editPagamentoFornecedor">Fornecedor:</label>
                            <input type="text" class="form-control" id="editPagamentoFornecedor" name="fornecedor">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoValorBruto">Valor Bruto:</label>
                            <input type="text" class="form-control" id="editPagamentoValorBruto" name="valor_bruto">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoValorLiquido">Valor L&iacute;quido:</label>
                            <input type="text" class="form-control" id="editPagamentoValorLiquido" name="valor_liquido">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoVencimento">Vencimento:</label>
                            <input type="date" class="form-control" id="editPagamentoVencimento" name="vencimento">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-8">
                            <label for="editPagamentoServico">Servi&ccedil;o:</label>
                            <input type="text" class="form-control" id="editPagamentoServico" name="servico">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoCnpj">CNPJ:</label>
                            <input type="text" class="form-control" id="editPagamentoCnpj" name="cnpj">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoTipoRpa">Tipo RPA:</label>
                            <input type="text" class="form-control" id="editPagamentoTipoRpa" name="tipo_rpa">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" onclick="salvarPagamento()"><i class="fa fa-save"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL CONFIRMAR PUBLICACAO ===== -->
<div class="modal fade" id="modalConfirmarPublicar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-check-circle"></i> Confirmar Publica&ccedil;&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja publicar <strong><span id="qtdPublicar">0</span></strong> registro(s) selecionado(s)?</p>
                <p>Ap&oacute;s publicados, os registros ficar&atilde;o vis&iacute;veis no Portal da Transpar&ecirc;ncia.</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarPublicar"><i class="fa fa-check"></i> Publicar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL CONFIRMAR REJEICAO ===== -->
<div class="modal fade" id="modalConfirmarRejeitar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Rejei&ccedil;&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja rejeitar <strong><span id="qtdRejeitar">0</span></strong> registro(s) selecionado(s)?</p>
                <div class="alert alert-danger" style="margin-bottom:0;">
                    <i class="fa fa-warning"></i> Os registros rejeitados ser&atilde;o removidos permanentemente. Esta a&ccedil;&atilde;o &eacute; irrevers&iacute;vel.
                </div>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarRejeitar"><i class="fa fa-times"></i> Rejeitar</button>
            </div>
        </div>
    </div>
</div>
<div style="height:300px;"></div>
