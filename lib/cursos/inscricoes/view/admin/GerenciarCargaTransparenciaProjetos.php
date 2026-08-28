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
    var _publicarTipo = 'projeto';
    var _publicarIds = [];
    var _rejeitarTipo = 'projeto';
    var _rejeitarIds = [];

    // ===== DOCUMENT READY =====
    $(document).ready(function() {

        // Carregar dados iniciais
        carregarTabelaArquivos();
        carregarArquivosSelect();
        carregarTabelaProjetos();

        // Botoes de envio
        $('#btnSalvarProjetos').click(function() {
            salvarArquivo();
        });
        $('#btnLimparProjetos').click(function() {
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
        $('#btnFiltrarArqProjetos').click(function() {
            carregarTabelaArquivos();
        });
        $('#btnLimparFiltroArqProjetos').click(function() {
            $('#statusFiltroProjetos').val('');
            $('#nomeArquivoFiltroProjetos').val('');
            $('#dataDeProjetos').val('');
            $('#dataAteProjetos').val('');
            carregarTabelaArquivos();
        });

        // Filtros - Registros
        $('#btnFiltrarProjetos').click(function() {
            carregarTabelaProjetos();
        });
        $('#filtroBuscaProjeto, #filtroEProtocoloProjeto, #filtroNrProjetoProjeto, #filtroCoordenadorProjeto, #filtroPropostaProjeto').keypress(function(e) {
            if (e.which === 13) carregarTabelaProjetos();
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
        $('#formArquivoProjetos').ajaxSubmit({
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
                $('#modalUploadProjetos').modal('hide');
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaArquivos();
                    limparCampos('formArquivoProjetos');
                    carregarArquivosSelect();
                    if ($('#chkCarregarAutoProjetos').is(':checked') && data.codArquivo) {
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
                'tipoFiltro': 'CARGA_TRANSPARENCIA_PROJETOS',
                'statusFiltro': $('#statusFiltroProjetos').val() || '',
                'nomeArquivoFiltro': $('#nomeArquivoFiltroProjetos').val() || '',
                'dataDe': $('#dataDeProjetos').val() || '',
                'dataAte': $('#dataAteProjetos').val() || '',
                '_submit_check': '1'
            },
            dataType: "html",
            cache: false,
            beforeSend: function() {
                $('#listaArquivosProjetos').html('<div class="text-center text-muted" style="padding:20px 0;"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');
            },
            success: function(html) {
                $('#listaArquivosProjetos').html(html);
            },
            error: function(xhr, status, error) {
                $('#listaArquivosProjetos').html('<div class="text-center text-danger" style="padding:20px 0;">Erro ao carregar: ' + error + '</div>');
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
        var tipoLabel = 'projeto(s)';

        var html = '<p><strong>' + data.novos + '</strong> ' + tipoLabel + ' novo(s) ser&atilde;o inseridos.</p>';
        html += '<p><strong>' + data.conflitos.length + '</strong> ' + tipoLabel + ' j&aacute; existem no banco:</p>';
        html += '<div class="table-responsive"><table class="table table-bordered table-condensed" style="font-size:12px;">';

        var colChave = 'N&ordm; Projeto';
        var campos = [
            {key: 'coordenador', label: 'Coordenador'},
            {key: 'e_protocolo', label: 'E-Protocolo'},
            {key: 'vinculo', label: 'V&iacute;nculo'},
            {key: 'tipo_pagamento', label: 'Tipo Pagamento'},
            {key: 'data_inicio', label: 'Data In&iacute;cio'},
            {key: 'data_fim', label: 'Data Fim'},
            {key: 'proposta', label: 'Proposta'}
        ];

        html += '<thead><tr><th>' + colChave + '</th><th>Campo</th><th style="background:#f2dede;">Atual (banco)</th><th style="background:#dff0d8;">Novo (arquivo)</th></tr></thead><tbody>';

        for (var i = 0; i < data.conflitos.length; i++) {
            var c = data.conflitos[i];
            var primeiro = true;
            var linhasConflito = '';

            var chaveTexto = c.novo.numero_projeto || '';

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
                    carregarTabelaProjetos();
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
        var codExcluir = _excluirCodArquivo;
        _excluirCodArquivo = null;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codArquivo': codExcluir, 'c': 'GerenciarCargaTransparencia', 'm': 'excluirArquivo', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    carregarTabelaArquivos();
                    carregarTabelaProjetos();
                    carregarArquivosSelect();
                }
            },
            error: function(xhr, status, error) {
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao excluir: ' + error);
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
        var titulo = 'Projetos Carregados (' + data.total + ')';

        if (data.tipo == 'projetos') {
            html += '<div class="table-responsive"><table class="table table-bordered table-condensed table-striped" style="font-size:12px;">';
            html += '<thead style="background:#d9edf7;"><tr>';
            html += '<th>N&ordm; Projeto</th><th>Coordenador</th><th>E-Protocolo</th><th>Proposta</th><th>V&iacute;nculo</th><th>Tipo Pgto</th><th>In&iacute;cio</th><th>Fim</th>';
            html += '</tr></thead><tbody>';
            for (var i = 0; i < data.dados.length; i++) {
                var d = data.dados[i];
                html += '<tr>';
                html += '<td>' + (d.numero_projeto || '') + '</td>';
                html += '<td>' + (d.coordenador || '') + '</td>';
                html += '<td>' + (d.e_protocolo || '') + '</td>';
                html += '<td>' + (d.proposta || '') + '</td>';
                html += '<td>' + (d.vinculo || '') + '</td>';
                html += '<td>' + (d.tipo_pagamento || '') + '</td>';
                html += '<td>' + (d.data_inicio || '') + '</td>';
                html += '<td>' + (d.data_fim || '') + '</td>';
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
        var $form = formId ? $('#' + formId) : $('#formArquivoProjetos');
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
                    var optsProjeto = '<option value="">Todos</option>';
                    for (var i = 0; i < data.arquivos.length; i++) {
                        var arq = data.arquivos[i];
                        if (arq.tipo === 'CARGA_TRANSPARENCIA_PROJETOS') {
                            optsProjeto += '<option value="' + arq.id + '">' + arq.nome + '</option>';
                        }
                    }
                    $('#filtroArquivoProjeto').html(optsProjeto);
                }
            }
        });
    }

    // ===== TABELA REGISTROS PROJETOS =====
    function carregarTabelaProjetos() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'carregarTabelaProjetos',
                'status_publicacao': $('#filtroStatusProjeto').val(),
                'cod_arquivo': $('#filtroArquivoProjeto').val(),
                'busca': $('#filtroBuscaProjeto').val(),
                'e_protocolo': $('#filtroEProtocoloProjeto').val(),
                'numero_projeto': $('#filtroNrProjetoProjeto').val(),
                'coordenador': $('#filtroCoordenadorProjeto').val(),
                'proposta': $('#filtroPropostaProjeto').val(),
                'data_de': $('#filtroDataDeProjeto').val(),
                'data_ate': $('#filtroDataAteProjeto').val(),
                '_submit_check': '1'
            },
            dataType: "html",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(html) {
                $('#listaProjetos').html(html);
                bindCheckboxSelecionar();
            },
            error: function(xhr, status, error) {
                $('#listaProjetos').html('<div class="text-center text-danger" style="padding:20px 0;">Erro ao carregar: ' + error + '</div>');
            },
            complete: function() { stopLoading(); }
        });
    }

    // Rebind checkboxes apos carregar tabela
    function bindCheckboxSelecionar() {
        $('#listaProjetos').off('change', '#chkHeadProjeto').on('change', '#chkHeadProjeto', function() {
            $('#listaProjetos input.chk-registro').prop('checked', $(this).is(':checked'));
        });
    }

    // ===== EDITAR PROJETO =====
    function abrirEditarProjeto(id) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'editarProjeto',
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
                $('#editProjetoId').val(data.id);
                $('#editProjetoNumero').val(data.numero_projeto);
                $('#editProjetoCoordenador').val(data.coordenador);
                $('#editProjetoEProtocolo').val(data.e_protocolo);
                $('#editProjetoProposta').val(data.proposta);
                $('#editProjetoVinculo').val(data.vinculo);
                $('#editProjetoTipoPagamento').val(data.tipo_pagamento);
                $('#editProjetoDataInicio').val(data.data_inicio);
                $('#editProjetoDataFim').val(data.data_fim);
                $('#modalEditarProjeto').modal('show');
            },
            error: function(xhr, status, error) {
                stopLoading();
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao carregar projeto: ' + error);
            }
        });
    }

    function salvarProjeto() {
        $('#formEditarProjeto').ajaxSubmit({
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    $('#modalEditarProjeto').modal('hide');
                    carregarTabelaProjetos();
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
                    carregarTabelaProjetos();
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
                    carregarTabelaProjetos();
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
        $('#listaProjetos input[type="checkbox"].chk-registro:checked').each(function() {
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
    <a class="submenu-btn submenu-btn-ativo" href="#" data-secao="secEnvioProjetos" onclick="mostrarSecao('secEnvioProjetos'); return false;"><i class="fa fa-upload"></i> Envio de Arquivos</a>
    <a class="submenu-btn" href="#" data-secao="secRegistrosProjetos" onclick="mostrarSecao('secRegistrosProjetos'); return false;"><i class="fa fa-list-alt"></i> Publicar Registros</a>
</div>


<!-- ===== SECAO: ENVIO DE ARQUIVOS (Projetos) ===== -->
<div id="secEnvioProjetos" class="secao-conteudo">

<!-- Arquivos Enviados -->
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 18px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:16px;"><i class="fa fa-files-o"></i> Arquivos Enviados &mdash; Projetos</h4>
        <div>
            <button type="button" class="btn btn-sm btn-info" onclick="$('#modalModeloProjetos').modal('show');"><i class="fa fa-info-circle"></i> Modelo CSV</button>
            <button type="button" class="abriModal" onclick="$('#modalUploadProjetos').modal('show');"><i class="fa fa-upload"></i> Enviar Arquivo</button>
        </div>
    </div>
    <div style="padding:12px 18px;">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-sm-2">
                <label class="label-filtro" for="statusFiltroProjetos">Situa&ccedil;&atilde;o:</label>
                <select class="form-control input-sm" id="statusFiltroProjetos">
                    <option value="">Todos</option>
                    <option value="AGUARDANDO">Aguardando</option>
                    <option value="CARREGADO">Carregado</option>
                    <option value="PARCIALMENTE_CARREGADO">Parcialmente Carregado</option>
                    <option value="ASSOCIADO">Associado</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label class="label-filtro" for="nomeArquivoFiltroProjetos">Nome do Arquivo:</label>
                <input type="text" class="form-control input-sm" id="nomeArquivoFiltroProjetos" placeholder="Parte do nome">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro" for="dataDeProjetos">Data De:</label>
                <input type="date" class="form-control input-sm" id="dataDeProjetos">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro" for="dataAteProjetos">Data At&eacute;:</label>
                <input type="date" class="form-control input-sm" id="dataAteProjetos">
            </div>
            <div class="col-sm-3" style="padding-top:25px;">
                <button class="btn btn-sm modalBtnSalvar" type="button" id="btnFiltrarArqProjetos"><i class="fa fa-search"></i> Filtrar</button>
                <button class="btn btn-sm modalBtnCancelar" type="button" id="btnLimparFiltroArqProjetos">Limpar</button>
            </div>
        </div>
    </div>
    <div id="listaArquivosProjetos">
        <div class="text-center text-muted" style="padding:20px 0;"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>
    </div>
</div>

</div><!-- /secEnvioProjetos -->

<!-- ===== SECAO: REGISTROS (Projetos) ===== -->
<div id="secRegistrosProjetos" class="secao-conteudo" style="display:none;">
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 18px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:16px;"><i class="fa fa-list-alt"></i> Registros de Projetos</h4>
        <div>
            <button class="btn btn-sm btn-success" type="button" onclick="publicarSelecionados('projeto')"><i class="fa fa-check"></i> Publicar Selecionados</button>
            <button class="btn btn-sm btn-danger" type="button" onclick="rejeitarSelecionados('projeto')"><i class="fa fa-times"></i> Rejeitar Selecionados</button>
        </div>
    </div>
    <div style="padding:12px 18px;">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-sm-2">
                <label class="label-filtro">Status:</label>
                <select class="form-control input-sm" id="filtroStatusProjeto">
                    <option value="">Todos</option>
                    <option value="RASCUNHO">Rascunho</option>
                    <option value="PUBLICADO">Publicado</option>
                </select>
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">Arquivo:</label>
                <select class="form-control input-sm" id="filtroArquivoProjeto">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">E-Protocolo:</label>
                <input type="text" class="form-control input-sm" id="filtroEProtocoloProjeto" placeholder="E-protocolo">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">N&ordm; Projeto:</label>
                <input type="text" class="form-control input-sm" id="filtroNrProjetoProjeto" placeholder="N&ordm; projeto">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">Coordenador:</label>
                <input type="text" class="form-control input-sm" id="filtroCoordenadorProjeto" placeholder="Coordenador">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">Proposta:</label>
                <input type="text" class="form-control input-sm" id="filtroPropostaProjeto" placeholder="Proposta">
            </div>
        </div>
        <div class="row" style="margin-bottom:10px;">
            <div class="col-sm-2">
                <label class="label-filtro">Per&iacute;odo De:</label>
                <input type="date" class="form-control input-sm" id="filtroDataDeProjeto">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">Per&iacute;odo At&eacute;:</label>
                <input type="date" class="form-control input-sm" id="filtroDataAteProjeto">
            </div>
            <div class="col-sm-2" style="padding-top:25px;">
                <button class="btn btn-sm btn-primary" type="button" id="btnFiltrarProjetos"><i class="fa fa-search"></i> Filtrar</button>
            </div>
        </div>
    </div>
    <div id="listaProjetos">
        <div class="text-center text-muted" style="padding:30px 0;"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>
    </div>
</div>

</div><!-- /secRegistrosProjetos -->


<!-- ===== MODAL MODELO CSV - PROJETOS ===== -->
<?php $pathModelos = WB_PATH . '/resources/public/transparencia/'; ?>
<div class="modal fade" id="modalModeloProjetos" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-info-circle"></i> Modelo CSV &mdash; Projetos</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="max-height:500px; overflow-y:auto;">
                <div class="alert alert-info" style="font-size:12px;">
                    <i class="fa fa-info-circle"></i> Formato <strong>CSV</strong> separado por ponto-e-v&iacute;rgula (<code>;</code>). A primeira linha deve ser o cabe&ccedil;alho.
                </div>
                <a href="<?php echo $pathModelos ?>modelo-carga-projetos.csv" download class="btn btn-sm btn-default" style="margin-bottom:10px;"><i class="fa fa-download"></i> Baixar modelo CSV</a>
                <p><strong>Colunas esperadas:</strong></p>
                <table class="table table-bordered table-condensed" style="font-size:12px;">
                    <thead style="background:#d9edf7;"><tr><th>#</th><th>Coluna</th><th>Obrigat&oacute;rio</th><th>Formato / Observa&ccedil;&atilde;o</th></tr></thead>
                    <tbody>
                        <tr><td>1</td><td><em>(vazia)</em></td><td>N&atilde;o</td><td>Coluna inicial vazia (ignorada)</td></tr>
                        <tr><td>2</td><td>Data inicio de projeto</td><td>Sim</td><td>DD/MM/AAAA</td></tr>
                        <tr><td>3</td><td>Data fim do projeto</td><td>N&atilde;o</td><td>DD/MM/AAAA</td></tr>
                        <tr><td>4</td><td>Eprotocolo</td><td>Sim</td><td>Ex: CONV - EXEMPLO</td></tr>
                        <tr><td>5</td><td><em>(tipo pagamento)</em></td><td>N&atilde;o</td><td>RPA, NFSe</td></tr>
                        <tr><td>6</td><td>V&iacute;nculo</td><td>N&atilde;o</td><td>Ex: servidor p&uacute;blico</td></tr>
                        <tr><td>7</td><td>Coordenador</td><td>Sim</td><td>Nome do coordenador</td></tr>
                        <tr><td>8</td><td>Proposta do Projeto</td><td>N&atilde;o</td><td>Pode ficar vazia</td></tr>
                        <tr><td>9</td><td>n&ordm; Projeto</td><td>Sim</td><td>Ex: 001/2024 (chave &uacute;nica)</td></tr>
                    </tbody>
                </table>
                <p><strong>Exemplo:</strong></p>
                <pre style="font-size:11px; background:#f5f5f5; padding:10px; overflow-x:auto;">;Data inicio de projeto;Data fim do projeto;Eprotocolo;;Vinculo;Coordenador;Proposta do Projeto;n&ordm; Projeto
;01/09/2024;01/09/2025;CONV - EXEMPLO;RPA;servidor publico;Ana Beatriz Souza;;001/2024</pre>
                <div class="alert alert-warning" style="font-size:12px; margin-top:10px; margin-bottom:0;">
                    <i class="fa fa-exclamation-triangle"></i> <strong>Duplicados:</strong> identificados pelo <strong>n&ordm; Projeto</strong>. Se existir, o sistema mostra comparativo para substituir ou pular.
                </div>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL UPLOAD PROJETOS ===== -->
<div class="modal fade" id="modalUploadProjetos" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-upload"></i> Enviar Arquivo CSV &mdash; Projetos</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formArquivoProjetos" name="formArquivoProjetos" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarCargaTransparencia" name="c">
                    <input type="hidden" value="salvarCargaTransparencia" name="m">
                    <input type="hidden" name="codEvento" value="<?php echo COD_EVENTO ?>">
                    <input type="hidden" name="tipoArquivo" value="CARGA_TRANSPARENCIA_PROJETOS">
                    <input type="hidden" name="novoCodArquivo" value="0">
                    <input type="hidden" name="codArquivo" value="">

                    <div class="form-group">
                        <label class="label-filtro">Selecionar Arquivo:</label>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" onclick="$('#arquivoProjetos').click();" style="height:34px;"><i class="fa fa-folder-open"></i> Procurar</button>
                            </span>
                            <input type="text" class="form-control nomeArquivoTransp" readonly placeholder="Nenhum arquivo selecionado" style="height:34px;">
                        </div>
                        <input type="file" name="arquivo" id="arquivoProjetos" style="display:none;" onchange="$(this).closest('form').find('.nomeArquivoTransp').val(this.files.length ? this.files[0].name : '');">
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <label style="font-weight:normal;">
                            <input type="checkbox" id="chkCarregarAutoProjetos" checked> Carregar automaticamente ap&oacute;s envio
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal" onclick="limparCampos('formArquivoProjetos');">Cancelar</button>
                <button type="button" class="btn modalBtnSalvar" id="btnSalvarProjetos"><i class="fa fa-upload"></i> Enviar</button>
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
                <p>Deseja realmente excluir este arquivo e <strong>todos os dados carregados</strong> (projetos) associados a ele?</p>
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

<!-- ===== MODAL EDITAR PROJETO ===== -->
<div class="modal fade" id="modalEditarProjeto" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-edit"></i> Editar Projeto</h4>
            </div>
            <div class="modal-body">
                <form id="formEditarProjeto" name="formEditarProjeto" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <input type="hidden" name="c" value="GerenciarRegistrosTransparencia">
                    <input type="hidden" name="m" value="salvarProjeto">
                    <input type="hidden" name="_submit_check" value="1">
                    <input type="hidden" name="id" id="editProjetoId">

                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editProjetoNumero">N&ordm; Projeto:</label>
                            <input type="text" class="form-control" id="editProjetoNumero" name="numero_projeto">
                        </div>
                        <div class="form-group col-sm-8">
                            <label for="editProjetoCoordenador">Coordenador:</label>
                            <input type="text" class="form-control" id="editProjetoCoordenador" name="coordenador">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editProjetoEProtocolo">E-Protocolo:</label>
                            <input type="text" class="form-control" id="editProjetoEProtocolo" name="e_protocolo">
                        </div>
                        <div class="form-group col-sm-8">
                            <label for="editProjetoProposta">Proposta:</label>
                            <input type="text" class="form-control" id="editProjetoProposta" name="proposta">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editProjetoVinculo">V&iacute;nculo:</label>
                            <input type="text" class="form-control" id="editProjetoVinculo" name="vinculo">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editProjetoTipoPagamento">Tipo Pagamento:</label>
                            <input type="text" class="form-control" id="editProjetoTipoPagamento" name="tipo_pagamento">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editProjetoDataInicio">Data In&iacute;cio:</label>
                            <input type="date" class="form-control" id="editProjetoDataInicio" name="data_inicio">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editProjetoDataFim">Data Fim:</label>
                            <input type="date" class="form-control" id="editProjetoDataFim" name="data_fim">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" onclick="salvarProjeto()"><i class="fa fa-save"></i> Salvar</button>
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
