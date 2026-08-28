<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarAvaliacaoPESProfessorAC();
?>
<style>
    .campo-invalido { border-color: #d9534f !important; box-shadow: 0 0 4px rgba(217,83,79,0.4) !important; }
    .campo-invalido-grupo { outline: 2px solid #d9534f; outline-offset: 2px; border-radius: 4px; }
    #modalAvalProf .modal-body { max-height: 70vh; overflow-y: auto; }
    .nota-btn { display:inline-block; width:36px; height:36px; line-height:36px; text-align:center; border:1px solid #ccc; border-radius:4px; margin:2px; cursor:pointer; font-weight:600; font-size:14px; }
    .nota-btn:hover { background:#e3f2fd; }
    .nota-btn.selecionada { background:#2196F3; color:#fff; border-color:#1976D2; }
    .painel-colegio { margin-bottom:20px; }
    .painel-colegio h5 { background:#E3F2FD; padding:8px 12px; border-radius:4px; color:#1565C0; font-weight:600; margin-bottom:8px; }
    .media-nota { font-size:18px; font-weight:700; }
    .media-boa { color:#2E7D32; }
    .media-media { color:#E65100; }
    .media-baixa { color:#C62828; }
</style>
<script type="text/javascript">
    var isAdminPedagogico = <?php echo $_isAdminPedagogico ? 'true' : 'false' ?>;

    var listaPerguntas = [
        {campo: 'integraAtividades',     texto: 'O PES atua de forma integrada \u00e0s atividades propostas em sala?'},
        {campo: 'contribuiParticipacao', texto: 'O PES contribui para que o aluno participe das atividades da turma?'},
        {campo: 'favoreceAprendizagem',  texto: 'O PES favorece a aprendizagem do aluno?'},
        {campo: 'reduzComportamentos',   texto: 'O PES ajuda na redu\u00e7\u00e3o de comportamentos que dificultam a aula?'},
        {campo: 'promoveAutonomia',      texto: 'O PES promove a autonomia do aluno (evita depend\u00eancia excessiva)?'},
        {campo: 'comunicacaoProfessor',  texto: 'O PES mant\u00e9m comunica\u00e7\u00e3o adequada com o professor regente?'},
        {campo: 'segueOrientacoes',      texto: 'O PES segue as orienta\u00e7\u00f5es pedag\u00f3gicas da sala?'},
        {campo: 'contribuiAula',         texto: 'A presen\u00e7a do PES contribui positivamente para o andamento da aula?'},
        {campo: 'posturaProfissional',   texto: 'O PES mant\u00e9m postura profissional adequada no ambiente escolar?'}
    ];

    var listaOpcoes = [
        {valor: 'NaoObservado', label: 'N\u00e3o observado (0)'},
        {valor: 'Nao',          label: 'N\u00e3o (1)'},
        {valor: 'Parcialmente', label: 'Parcialmente (2)'},
        {valor: 'Sim',          label: 'Sim (3)'}
    ];

    $(document).ready(function() {
        if (isAdminPedagogico) {
            $('#areaSelectorSupervisor').show();
            carregarListaSupervisores();

            $('#avalProfSelectSupervisor').on('change', function() {
                if ($(this).val()) {
                    carregarTabelaPES();
                    $('#avalProfAreaTabela').show();
                } else {
                    $('#avalProfAreaTabela').hide();
                }
            });
        } else {
            $('#areaSelectorSupervisor').hide();
            carregarTabelaPES();
            $('#avalProfAreaTabela').show();
        }

        $('#avalProfCiclo').on('change', function() {
            carregarTabelaPES();
        });

        montarPerguntasHTML();

        $('#btnSalvarAvalProf').click(function() {
            salvarAvaliacao();
        });

        $(document).on('change keyup', '#formAvalProf select, #formAvalProf input, #formAvalProf textarea', function() {
            $(this).removeClass('campo-invalido');
            $(this).closest('tr').removeClass('campo-invalido-grupo');
        });

        // Tab painel comparativo
        $('a[href="#tabPainel"]').on('shown.bs.tab click', function() {
            carregarPainelComparativo();
        });
    });

    // ===================== SUPERVISORES (ADMIN) =====================

    function carregarListaSupervisores() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarAvaliacaoPESProfessor', 'm': 'carregarListaSupervisoresParaSelect', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            success: function(data) {
                var select = $('#avalProfSelectSupervisor');
                select.find('option:not(:first)').remove();
                if (data.supervisores && data.supervisores.length > 0) {
                    $.each(data.supervisores, function(i, s) {
                        select.append('<option value="' + s.codUsuario + '">' + s.nome + '</option>');
                    });
                }
            }
        });
    }

    // ===================== TABELA PES =====================

    function carregarTabelaPES() {
        var codSupervisor = isAdminPedagogico ? ($('#avalProfSelectSupervisor').val() || 0) : 0;
        var ciclo = $('#avalProfCiclo').val();

        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarAvaliacaoPESProfessor', 'm': 'carregarTabelaPES', 'codSupervisor': codSupervisor, 'ciclo': ciclo, '_submit_check': '1'},
            function(html) {
                $("table#tabelaPESProf tbody.inputs tr").remove();
                $("table#tabelaPESProf tbody.inputs").append(html);

                $('.btn-nova-aval-prof').click(function() {
                    var codAgente = $(this).data('cod-agente');
                    var nome = $(this).data('nome');
                    abrirModalNovaAvaliacao(codAgente, nome);
                });

                $('.btn-ver-avaliacoes').click(function() {
                    var codAgente = $(this).data('cod-agente');
                    var nome = $(this).data('nome');
                    abrirModalListaAvaliacoes(codAgente, nome);
                });
            }
        );
    }

    // ===================== MONTAR PERGUNTAS =====================

    function montarPerguntasHTML() {
        var html = '';
        html += '<p style="font-size:13px; color:#555; margin-bottom:10px;"><em>Na sua impress\u00e3o GERAL (na maioria dos dias) sobre a atividade do PES, voc\u00ea considera que:</em></p>';
        html += '<table class="table table-bordered table-condensed" style="font-size:13px;">';
        html += '<thead><tr><th style="width:40%;">Crit\u00e9rio</th>';
        for (var o = 0; o < listaOpcoes.length; o++) {
            html += '<th class="text-center" style="width:15%;">' + listaOpcoes[o].label + '</th>';
        }
        html += '</tr></thead><tbody>';

        for (var i = 0; i < listaPerguntas.length; i++) {
            var num = i + 1;
            var campo = listaPerguntas[i].campo;
            html += '<tr>';
            html += '<td>' + num + '. ' + listaPerguntas[i].texto + '</td>';
            for (var o = 0; o < listaOpcoes.length; o++) {
                html += '<td class="text-center">';
                html += '<input type="radio" name="' + campo + '" value="' + listaOpcoes[o].valor + '">';
                html += '</td>';
            }
            html += '</tr>';
        }

        html += '</tbody></table>';

        // Nota geral
        html += '<div style="margin-top:15px; padding:12px; background:#f9f9f9; border-radius:6px;">';
        html += '<label style="font-weight:600;">Considerando uma escala de 0 a 10, qual nota voc\u00ea daria para o trabalho deste PES? <span class="text-danger">*</span></label>';
        html += '<div style="margin-top:8px;">';
        for (var n = 0; n <= 10; n++) {
            html += '<span class="nota-btn" data-nota="' + n + '">' + n + '</span>';
        }
        html += '<input type="hidden" name="notaGeral" id="avalProfNotaGeral" value="">';
        html += '</div></div>';

        $('#areaPerguntasProf').html(html);

        // Bind nota
        $(document).off('click', '.nota-btn').on('click', '.nota-btn', function() {
            $('.nota-btn').removeClass('selecionada');
            $(this).addClass('selecionada');
            $('#avalProfNotaGeral').val($(this).data('nota'));
        });
    }

    // ===================== MODAL NOVA AVALIACAO =====================

    function abrirModalNovaAvaliacao(codAgente, nome) {
        limparCampos();
        $('#modalAvalProfMsg').html('');
        $('#avalProfCodAgente').val(codAgente);
        $('#avalProfNomeAgente').text(nome);
        $('#avalProfCicloHidden').val($('#avalProfCiclo').val());

        var hoje = new Date();
        var dataFormatada = hoje.getFullYear() + '-' + String(hoje.getMonth() + 1).padStart(2, '0') + '-' + String(hoje.getDate()).padStart(2, '0');
        $('#avalProfDataAvaliacao').val(dataFormatada);

        $('#modalAvalProfLabel').text('Avalia\u00e7\u00e3o do PES por Professor - ' + nome);
        $('#modalAvalProf').modal('show');
    }

    function editarAvaliacao(codAvaliacao) {
        if (!codAvaliacao) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codAvaliacao': codAvaliacao, 'c': 'GerenciarAvaliacaoPESProfessor', 'm': 'editarAvaliacao', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                if (!data.statusMessage) return;
                limparCampos();
                $('#modalAvalProfMsg').html('');
                $('#avalProfCodAvaliacao').val(data.codAvaliacao);
                $('#avalProfCodAgente').val(data.codAgente);
                $('#avalProfNomeProfessor').val(data.nomeProfessor);
                $('#avalProfCicloHidden').val(data.ciclo);
                $('#avalProfDataAvaliacao').val(data.dataAvaliacao);
                $('#avalProfObservacoes').val(data.observacoes);

                for (var i = 0; i < listaPerguntas.length; i++) {
                    var campo = listaPerguntas[i].campo;
                    if (data[campo]) {
                        $('input[name="' + campo + '"][value="' + data[campo] + '"]').prop('checked', true);
                    }
                }

                if (data.notaGeral !== null && data.notaGeral !== '') {
                    $('#avalProfNotaGeral').val(data.notaGeral);
                    $('.nota-btn[data-nota="' + data.notaGeral + '"]').addClass('selecionada');
                }

                $('#avalProfNovoCodAvaliacao').val(1);
                $('#modalAvalProfLabel').text('Editar Avalia\u00e7\u00e3o');
                $('#modalAvalProf').modal('show');
                $('#modalAvalProf .modal-body').scrollTop(0);
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===================== MODAL LISTA AVALIACOES =====================

    function abrirModalListaAvaliacoes(codAgente, nome) {
        var ciclo = $('#avalProfCiclo').val();
        $('#modalListaLabel').text('Avalia\u00e7\u00f5es de ' + nome + ' - ' + getCicloNome(ciclo));
        $('#modalListaCorpo').html('<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando...</td></tr>');
        $('#modalListaAvaliacoes').modal('show');

        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codAgente': codAgente, 'ciclo': ciclo, 'c': 'GerenciarAvaliacaoPESProfessor', 'm': 'carregarAvaliacoesPorAgente', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            success: function(data) {
                if (!data.statusMessage || !data.avaliacoes || data.avaliacoes.length === 0) {
                    $('#modalListaCorpo').html('<tr><td colspan="4" class="text-center text-muted">Nenhuma avalia\u00e7\u00e3o encontrada.</td></tr>');
                    return;
                }
                var html = '';
                for (var i = 0; i < data.avaliacoes.length; i++) {
                    var av = data.avaliacoes[i];
                    var dataFmt = av.data_avaliacao ? av.data_avaliacao.split('-').reverse().join('/') : '';
                    html += '<tr>';
                    html += '<td>' + (av.nome_professor || '') + '</td>';
                    html += '<td class="text-center">' + (av.nota_geral !== null ? av.nota_geral : '-') + '</td>';
                    html += '<td class="text-center">' + dataFmt + '</td>';
                    html += '<td class="text-center text-nowrap">';
                    html += '<button class="btn btn-xs btn-info" onclick="$(\'#modalListaAvaliacoes\').modal(\'hide\'); editarAvaliacao(' + av.cod_avaliacao + ')"><i class="fa fa-pencil"></i></button> ';
                    html += '<button class="btn btn-xs btn-danger" onclick="confirmarExclusao(' + av.cod_avaliacao + ')"><i class="fa fa-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                }
                $('#modalListaCorpo').html(html);
            }
        });
    }

    function getCicloNome(ciclo) {
        if (ciclo == 1) return '1\u00ba Ciclo';
        if (ciclo == 2) return '2\u00ba Ciclo';
        if (ciclo == 3) return '3\u00ba Ciclo';
        return 'Ciclo ' + ciclo;
    }

    // ===================== EXCLUSAO =====================

    var _codAvalExcluir = null;

    function confirmarExclusao(codAvaliacao) {
        _codAvalExcluir = codAvaliacao;
        $('#modalExcluirAvalProf').modal('show');
    }

    function executarExclusao() {
        if (!_codAvalExcluir) return;
        $('#modalExcluirAvalProf').modal('hide');
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codAvaliacao': _codAvalExcluir, 'c': 'GerenciarAvaliacaoPESProfessor', 'm': 'excluirAvaliacao', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaPES();
                    $('#modalListaAvaliacoes').modal('hide');
                }
            },
            complete: function() { stopLoading(); _codAvalExcluir = null; }
        });
    }

    // ===================== VALIDACAO =====================

    function validarFormulario() {
        var erros = [];
        $('#formAvalProf .campo-invalido').removeClass('campo-invalido');
        $('#formAvalProf .campo-invalido-grupo').removeClass('campo-invalido-grupo');

        if (!$('#avalProfNomeProfessor').val().trim()) {
            erros.push('Nome do professor');
            $('#avalProfNomeProfessor').addClass('campo-invalido');
        }
        if (!$('#avalProfDataAvaliacao').val()) {
            erros.push('Data da avalia\u00e7\u00e3o');
            $('#avalProfDataAvaliacao').addClass('campo-invalido');
        }

        for (var i = 0; i < listaPerguntas.length; i++) {
            var campo = listaPerguntas[i].campo;
            if ($('input[name="' + campo + '"]:checked').length === 0) {
                erros.push('Pergunta ' + (i + 1));
                $('input[name="' + campo + '"]').closest('tr').addClass('campo-invalido-grupo');
            }
        }

        if ($('#avalProfNotaGeral').val() === '') {
            erros.push('Nota geral (0 a 10)');
        }

        return erros;
    }

    // ===================== SALVAR =====================

    function salvarAvaliacao() {
        var erros = validarFormulario();
        if (erros.length > 0) {
            var html = '<div class="alert alert-danger"><strong>Preencha os campos obrigat\u00f3rios:</strong><ul style="margin:5px 0 0 0;">';
            for (var i = 0; i < erros.length; i++) {
                html += '<li>' + erros[i] + '</li>';
            }
            html += '</ul></div>';
            $('#modalAvalProfMsg').html(html);
            $('#modalAvalProf .modal-body').scrollTop(0);
            return;
        }

        $('#modalAvalProfMsg').html('');

        if (isAdminPedagogico) {
            $('#avalProfCodSupervisor').val($('#avalProfSelectSupervisor').val());
        }

        $('#formAvalProf').ajaxSubmit({
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            timeout: 30000,
            error: function(ajax) {
                $('#modalAvalProfMsg').html('<div class="alert alert-danger">' + (ajax.responseText || 'Erro ao processar.') + '</div>');
            },
            success: function(data) {
                if (data.statusMessage) {
                    carregarTabelaPES();
                    limparCampos();
                    $('#modalAvalProf').modal('hide');
                    processJson(data);
                } else {
                    $('#modalAvalProfMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===================== PAINEL COMPARATIVO =====================

    function carregarPainelComparativo() {
        var ciclo = $('#avalProfCicloPainel').val() || $('#avalProfCiclo').val();
        $('#painelComparativoConteudo').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'ciclo': ciclo, 'c': 'GerenciarAvaliacaoPESProfessor', 'm': 'carregarPainelComparativo', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            success: function(data) {
                if (!data.statusMessage || !data.porColegio || Object.keys(data.porColegio).length === 0) {
                    $('#painelComparativoConteudo').html('<div class="alert alert-info"><i class="fa fa-info-circle"></i> Nenhuma avalia\u00e7\u00e3o encontrada para este ciclo.</div>');
                    return;
                }

                var html = '';
                $.each(data.porColegio, function(nomeColegio, agentes) {
                    html += '<div class="painel-colegio">';
                    html += '<h5><i class="fa fa-building"></i> ' + nomeColegio + '</h5>';
                    html += '<table class="table table-bordered table-condensed" style="font-size:12px;">';
                    html += '<thead><tr><th>PES</th><th class="text-center">Aval.</th><th class="text-center">Nota M\u00e9dia</th><th class="text-center">Integra</th><th class="text-center">Participa\u00e7\u00e3o</th><th class="text-center">Aprendiz.</th><th class="text-center">Comport.</th><th class="text-center">Autonomia</th><th class="text-center">Comunic.</th><th class="text-center">Orient.</th><th class="text-center">Aula</th><th class="text-center">Postura</th></tr></thead><tbody>';

                    for (var i = 0; i < agentes.length; i++) {
                        var ag = agentes[i];
                        var classeNota = ag.mediaNota >= 7 ? 'media-boa' : (ag.mediaNota >= 5 ? 'media-media' : 'media-baixa');
                        html += '<tr>';
                        html += '<td>' + ag.nomeAgente + '</td>';
                        html += '<td class="text-center">' + ag.totalAvaliacoes + '</td>';
                        html += '<td class="text-center media-nota ' + classeNota + '">' + ag.mediaNota + '</td>';
                        html += '<td class="text-center">' + formatMedia(ag.mediaIntegra) + '</td>';
                        html += '<td class="text-center">' + formatMedia(ag.mediaContribui) + '</td>';
                        html += '<td class="text-center">' + formatMedia(ag.mediaAprendizagem) + '</td>';
                        html += '<td class="text-center">' + formatMedia(ag.mediaComportamentos) + '</td>';
                        html += '<td class="text-center">' + formatMedia(ag.mediaAutonomia) + '</td>';
                        html += '<td class="text-center">' + formatMedia(ag.mediaComunicacao) + '</td>';
                        html += '<td class="text-center">' + formatMedia(ag.mediaOrientacoes) + '</td>';
                        html += '<td class="text-center">' + formatMedia(ag.mediaContribuiAula) + '</td>';
                        html += '<td class="text-center">' + formatMedia(ag.mediaPostura) + '</td>';
                        html += '</tr>';
                    }

                    html += '</tbody></table></div>';
                });

                $('#painelComparativoConteudo').html(html);
            }
        });
    }

    function formatMedia(valor) {
        if (!valor || valor == 0) return '-';
        var classe = valor >= 2.5 ? 'media-boa' : (valor >= 1.5 ? 'media-media' : 'media-baixa');
        return '<span class="' + classe + '">' + valor + '/3</span>';
    }

    // ===================== LIMPAR =====================

    function limparCampos() {
        $('#avalProfCodAvaliacao').val('');
        $('#avalProfCodAgente').val('');
        $('#avalProfCodSupervisor').val('');
        $('#avalProfNomeProfessor').val('');
        $('#avalProfDataAvaliacao').val('');
        $('#avalProfObservacoes').val('');
        $('#avalProfNotaGeral').val('');
        $('#areaPerguntasProf input[type="radio"]').prop('checked', false);
        $('.nota-btn').removeClass('selecionada');
        $('#avalProfNovoCodAvaliacao').val(0);
        $('#avalProfNomeAgente').text('');
    }
</script>

<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><i class="fa fa-graduation-cap"></i> Avalia&ccedil;&atilde;o do PES por Professores</h4>
        </div>
        <div class="panel-body">

            <p class="text-muted" style="font-size:13px; margin-bottom:15px;">
                <i class="fa fa-info-circle"></i>
                Avalia&ccedil;&atilde;o do desempenho do PES em sala de aula, preenchida pelo professor regente.
                O supervisor seleciona o PES e entrega o dispositivo ao professor para preenchimento.
            </p>

            <!-- Tabs -->
            <ul class="nav nav-tabs" style="margin-bottom:15px;">
                <li class="active"><a data-toggle="tab" href="#tabAvaliacoes">Avalia&ccedil;&otilde;es</a></li>
                <?php if ($_isAdminPedagogico): ?>
                <li><a data-toggle="tab" href="#tabPainel">Painel Comparativo</a></li>
                <?php endif; ?>
            </ul>

            <div class="tab-content">

                <!-- Tab Avaliacoes -->
                <div id="tabAvaliacoes" class="tab-pane fade in active">

                    <div class="row" style="margin-bottom:15px;">
                        <!-- Seletor de Supervisor (admin) -->
                        <div id="areaSelectorSupervisor" class="col-sm-5" style="display:none;">
                            <label for="avalProfSelectSupervisor">Supervisor:</label>
                            <select id="avalProfSelectSupervisor" class="form-control">
                                <option value="">Selecione um supervisor...</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label for="avalProfCiclo">Ciclo:</label>
                            <select id="avalProfCiclo" class="form-control">
                                <option value="1">1&ordm; Ciclo (In&iacute;cio)</option>
                                <option value="2">2&ordm; Ciclo (Meio do ano)</option>
                                <option value="3">3&ordm; Ciclo (Final do ano)</option>
                            </select>
                        </div>
                    </div>

                    <div id="avalProfAreaTabela" style="display:none;">
                        <div id="carregando" class="shown hidden">
                            <div class="imagem">
                                <div class="frase">Carregando...</div>
                                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
                            </div>
                        </div>
                        <div id="mensagem"></div>

                        <div class="table-responsive">
                            <table id="tabelaPESProf" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome do PES</th>
                                        <th class="text-center">Avalia&ccedil;&otilde;es do Ciclo</th>
                                        <th class="text-center">A&ccedil;&otilde;es</th>
                                    </tr>
                                </thead>
                                <tbody class="inputs">
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <?php if ($_isAdminPedagogico): ?>
                <!-- Tab Painel Comparativo -->
                <div id="tabPainel" class="tab-pane fade">
                    <div class="row" style="margin-bottom:15px;">
                        <div class="col-sm-3">
                            <label for="avalProfCicloPainel">Ciclo:</label>
                            <select id="avalProfCicloPainel" class="form-control" onchange="carregarPainelComparativo()">
                                <option value="1">1&ordm; Ciclo (In&iacute;cio)</option>
                                <option value="2">2&ordm; Ciclo (Meio do ano)</option>
                                <option value="3">3&ordm; Ciclo (Final do ano)</option>
                            </select>
                        </div>
                    </div>
                    <div id="painelComparativoConteudo">
                        <div class="alert alert-info"><i class="fa fa-info-circle"></i> Selecione um ciclo para visualizar o painel comparativo.</div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</div>

<!-- Modal Avaliacao -->
<div class="modal fade" id="modalAvalProf" tabindex="-1" role="dialog" aria-labelledby="modalAvalProfLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formAvalProf" name="formAvalProf" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalAvalProfLabel">Avalia&ccedil;&atilde;o do PES por Professor</h4>
                </div>
                <div class="modal-body">

                    <div id="modalAvalProfMsg"></div>

                    <div class="well well-sm" style="margin-bottom:15px;">
                        <strong>PES:</strong> <span id="avalProfNomeAgente"></span>
                    </div>

                    <div class="row">
                        <div class="col-sm-5">
                            <div class="form-group">
                                <label for="avalProfNomeProfessor">Nome do Professor: <span class="text-danger">*</span></label>
                                <input type="text" id="avalProfNomeProfessor" name="nomeProfessor" class="form-control" placeholder="Nome completo do professor">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="avalProfDataAvaliacao">Data: <span class="text-danger">*</span></label>
                                <input type="date" id="avalProfDataAvaliacao" name="dataAvaliacao" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-info" style="margin-top:10px;">
                        <div class="panel-heading" style="font-size:13px;">
                            <strong>Avalia&ccedil;&atilde;o</strong> - Marque uma op&ccedil;&atilde;o para cada crit&eacute;rio <span class="text-danger">*</span>
                        </div>
                        <div class="panel-body" style="padding:8px;">
                            <div id="areaPerguntasProf"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="avalProfObservacoes">Observa&ccedil;&otilde;es (opcional):</label>
                        <textarea id="avalProfObservacoes" name="observacoes" class="form-control" rows="3"
                                  placeholder="Observa&ccedil;&otilde;es adicionais..."></textarea>
                    </div>

                    <input type="hidden" name="codAvaliacao" id="avalProfCodAvaliacao" value="">
                    <input type="hidden" name="codAgente" id="avalProfCodAgente" value="">
                    <input type="hidden" name="codSupervisor" id="avalProfCodSupervisor" value="">
                    <input type="hidden" name="ciclo" id="avalProfCicloHidden" value="1">
                    <input type="hidden" name="novoCodAvaliacao" id="avalProfNovoCodAvaliacao" value="0">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarAvaliacaoPESProfessor" name="c">
                    <input type="hidden" value="salvarAvaliacao" name="m">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarAvalProf"><i class="fa fa-save"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Lista Avaliacoes -->
<div class="modal fade" id="modalListaAvaliacoes" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="modalListaLabel">Avalia&ccedil;&otilde;es</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-condensed" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>Professor</th>
                            <th class="text-center">Nota</th>
                            <th class="text-center">Data</th>
                            <th class="text-center">A&ccedil;&otilde;es</th>
                        </tr>
                    </thead>
                    <tbody id="modalListaCorpo">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusao -->
<div class="modal fade" id="modalExcluirAvalProf" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir esta avalia&ccedil;&atilde;o?</p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="executarExclusao()"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>
