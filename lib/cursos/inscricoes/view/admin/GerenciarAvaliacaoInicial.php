<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarAvaliacaoInicialAC();
$codAlunoParam = isset($fwsGet_['codAluno']) ? $fwsGet_['codAluno'] : '';
?>
<style>
    .campo-invalido { border-color: #d9534f !important; box-shadow: 0 0 4px rgba(217,83,79,0.4) !important; }
    .campo-invalido-grupo { outline: 2px solid #d9534f; outline-offset: 2px; border-radius: 4px; }
    #modalAvaliacao .modal-body { max-height: 70vh; overflow-y: auto; }
</style>
<script type="text/javascript">
    var avalCodAluno = '<?php echo addslashes($codAlunoParam) ?>';

    var listaHabilidades = [
        'Comunicacao',
        'Transicoes',
        'Tolerancia',
        'Autoestimulacao motora ou vocal',
        'Atraso de tarefas',
        'Habilidades Sociais/Brincar',
        'Questoes sensoriais',
        'Seguir Instrucoes de Grupo',
        'Outra'
    ];

    var listaComportamentos = [
        'Choro intenso',
        'Gritos frequentes',
        'Arremesso/destruicao de objetos',
        'Agressao contra adultos',
        'Agressao contra criancas',
        'Agressao contra si proprio',
        'Jogar-se no chao'
    ];

    $(document).ready(function() {
        // Tab 1 - Por Programacao
        $('#avalSelectProgramacao').on('change', function() {
            var codProg = $(this).val();
            if (codProg) {
                carregarTabelaAlunosPorProgramacao(codProg);
                $('#avalAreaTabelaProg').show();
            } else {
                $('#avalAreaTabelaProg').hide();
            }
        });

        carregarListaProgramacoes();

        // Tab 2 - Por Aluno (registrar handler ANTES de carregar lista, pois o trigger('change') dispara dentro do AJAX)
        $('#avalSelectAluno').on('change', function() {
            var codAluno = $(this).val();
            if (codAluno) {
                carregarTabelaAvaliacoes(codAluno);
                $('#avalAreaTabela').show();
            } else {
                $('#avalAreaTabela').hide();
            }
        });

        carregarListaAlunosAval();

        // Modal
        $('#btnSalvarAvaliacao').click(function() {
            salvarAvaliacao();
        });
        $('#btnLimparAvaliacao').click(function() {
            limparCamposAvaliacao();
        });

        $('#avalFazTerapia').on('change', function() {
            toggleTerapiaDetalhes($(this).val());
        });

        montarHabilidadesHTML();
        montarComportamentosHTML();

        // Limpar marcacao de erro ao interagir com o campo
        $(document).on('change keyup', '#formAvaliacao select, #formAvaliacao input, #formAvaliacao textarea', function() {
            $(this).removeClass('campo-invalido');
            $(this).closest('tr').removeClass('campo-invalido-grupo');
            $(this).closest('#areaComportamentos').removeClass('campo-invalido-grupo');
        });

        // Se veio com codAluno na URL, ir para aba Por Aluno
        if (avalCodAluno) {
            $('#tabAvalAluno').tab('show');
        }
    });

    // ===================== TAB 1 - POR PROGRAMACAO =====================

    function carregarListaProgramacoes() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarAvaliacaoInicial', 'm': 'carregarListaProgramacoesParaSelect', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            success: function(data) {
                var select = $('#avalSelectProgramacao');
                select.find('option:not(:first)').remove();
                if (data.programacoes && data.programacoes.length > 0) {
                    $.each(data.programacoes, function(i, p) {
                        var label = p.descricao;
                        if (p.tipoAvaliacao) label += ' [' + p.tipoAvaliacao + ']';
                        label += ' (' + p.situacao + ')';
                        select.append('<option value="' + p.codProgramacao + '" data-tipo="' + (p.tipoAvaliacao || '') + '">' + label + '</option>');
                    });
                    // Auto-selecionar a primeira programacao
                    select.find('option:eq(1)').prop('selected', true);
                    select.trigger('change');
                }
            }
        });
    }

    function carregarTabelaAlunosPorProgramacao(codProgramacao) {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarAvaliacaoInicial', 'm': 'carregarTabelaAlunosPorProgramacao', 'codProgramacao': codProgramacao, '_submit_check': '1'},
            function(html) {
                $("table#tabelaAlunosProg tbody.inputs tr").remove();
                $("table#tabelaAlunosProg tbody.inputs").append(html);

                // Botao nova avaliacao a partir da tab programacao
                $('.nova-avaliacao-prog').click(function() {
                    var codAluno = $(this).data('cod-aluno');
                    var tipoProg = $('#avalSelectProgramacao option:selected').data('tipo');
                    abrirModalNovaAvaliacaoComDados(codAluno, tipoProg);
                });

                // Botao ver avaliacao
                $('.ver-avaliacao-prog').click(function() {
                    var codAval = $(this).data('cod-avaliacao');
                    editarAvaliacao(codAval);
                });
            }
        );
    }

    function abrirModalNovaAvaliacaoComDados(codAluno, tipoAvaliacao) {
        limparCamposAvaliacao();
        $('#modalAvalMsg').html('');
        $('#avalCodAluno').val(codAluno);
        avalOrigemTab = 'programacao';
        if (tipoAvaliacao) {
            $('#avalTipoAvaliacao').val(tipoAvaliacao);
        }
        $('#modalAvaliacao').modal('show');
    }

    var avalOrigemTab = 'aluno';

    // ===================== TAB 2 - POR ALUNO =====================

    function toggleTerapiaDetalhes(val) {
        if (val === 'Sim') {
            $('#areaTerapiaDetalhes').show();
        } else {
            $('#areaTerapiaDetalhes').hide();
            $('#avalTerapiaLocal').val('');
            $('#avalTerapiaTipos').val('');
        }
    }

    function montarHabilidadesHTML() {
        var html = '';
        html += '<table class="table table-bordered table-condensed" style="font-size:13px;">';
        html += '<thead><tr><th style="width:35%;">Habilidade</th><th style="width:40%;">N&iacute;vel de Prioridade</th><th style="width:25%;">Ordem (1-9)</th></tr></thead>';
        html += '<tbody>';
        for (var i = 0; i < listaHabilidades.length; i++) {
            var nome = listaHabilidades[i];
            var key = 'hab_' + i;
            html += '<tr>';
            html += '<td>' + nome + '</td>';
            html += '<td>';
            html += '<label class="radio-inline" style="margin-right:12px;"><input type="radio" name="habilidades[' + key + '][nivel]" value="1"> 1-Baixa</label> ';
            html += '<label class="radio-inline" style="margin-right:12px;"><input type="radio" name="habilidades[' + key + '][nivel]" value="2"> 2-M&eacute;dia</label> ';
            html += '<label class="radio-inline"><input type="radio" name="habilidades[' + key + '][nivel]" value="3"> 3-Alta</label>';
            html += '</td>';
            html += '<td><select name="habilidades[' + key + '][ordem]" class="form-control input-sm">';
            html += '<option value="">-</option>';
            for (var o = 1; o <= 9; o++) {
                html += '<option value="' + o + '">' + o + '</option>';
            }
            html += '</select></td>';
            html += '</tr>';
        }
        html += '</tbody></table>';
        $('#areaHabilidades').html(html);
    }

    function montarComportamentosHTML() {
        var html = '';
        for (var i = 0; i < listaComportamentos.length; i++) {
            html += '<div class="checkbox" style="margin:4px 0;"><label>';
            html += '<input type="checkbox" name="comportamentosDificeis[]" value="' + listaComportamentos[i] + '"> ' + listaComportamentos[i];
            html += '</label></div>';
        }
        html += '<div class="checkbox" style="margin:4px 0;"><label>';
        html += '<input type="checkbox" name="comportamentosDificeis[]" value="Outros" id="chkCompOutros"> Outros';
        html += '</label></div>';
        html += '<div id="areaCompOutros" style="display:none; margin-top:5px;">';
        html += '<input type="text" id="avalCompOutros" name="comportamentosOutros" class="form-control" maxlength="500" placeholder="Descreva outros comportamentos...">';
        html += '</div>';
        $('#areaComportamentos').html(html);

        $(document).on('change', '#chkCompOutros', function() {
            if ($(this).is(':checked')) {
                $('#areaCompOutros').show();
            } else {
                $('#areaCompOutros').hide();
                $('#avalCompOutros').val('');
            }
        });
    }

    function carregarListaAlunosAval() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarAluno', 'm': 'carregarListaAlunos', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            success: function(data) {
                var select = $('#avalSelectAluno');
                select.find('option:not(:first)').remove();
                if (data.alunos) {
                    $.each(data.alunos, function(i, al) {
                        var label = al.codigoExibicao;
                        if (al.iniciais) label += ' - ' + al.iniciais;
                        if (al.nomeColegio) label += ' (' + al.nomeColegio + ')';
                        select.append('<option value="' + al.codAluno + '">' + label + '</option>');
                    });
                }
                if (avalCodAluno) {
                    select.val(avalCodAluno).trigger('change');
                }
            }
        });
    }

    function carregarTabelaAvaliacoes(codAluno) {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarAvaliacaoInicial', 'm': 'carregarTabelaAvaliacoes', 'codAluno': codAluno, '_submit_check': '1'},
            function(html) {
                $("table#tabelaAvaliacoes tbody.inputs tr").remove();
                $("table#tabelaAvaliacoes tbody.inputs").append(html);

                $('.editar-avaliacao').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    editarAvaliacao(id);
                });
                $('.excluir-avaliacao').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    excluirAvaliacao(id);
                });
            }
        );
    }

    // ===================== VALIDACAO E MODAL =====================

    function validarFormAvaliacao() {
        var erros = [];
        $('#formAvaliacao .campo-invalido').removeClass('campo-invalido');
        $('#formAvaliacao .campo-invalido-grupo').removeClass('campo-invalido-grupo');

        if (!$('#avalTipoAvaliacao').val()) {
            erros.push('Tipo de Avalia\u00e7\u00e3o');
            $('#avalTipoAvaliacao').addClass('campo-invalido');
        }
        if (!$('#avalDataAvaliacao').val()) {
            erros.push('Data da Avalia\u00e7\u00e3o');
            $('#avalDataAvaliacao').addClass('campo-invalido');
        }
        if (!$('#avalNivelSuporte').val()) {
            erros.push('N\u00edvel de Suporte');
            $('#avalNivelSuporte').addClass('campo-invalido');
        }
        if (!$('#avalFazTerapia').val()) {
            erros.push('Faz terapia (contraturno)');
            $('#avalFazTerapia').addClass('campo-invalido');
        }
        if ($('#avalFazTerapia').val() === 'Sim') {
            if (!$('#avalTerapiaLocal').val()) {
                erros.push('Local da terapia');
                $('#avalTerapiaLocal').addClass('campo-invalido');
            }
            if (!$('#avalTerapiaTipos').val()) {
                erros.push('Tipos de terapia');
                $('#avalTerapiaTipos').addClass('campo-invalido');
            }
        }
        if (!$('#avalDemandasEscolares').val() || !$('#avalDemandasEscolares').val().trim()) {
            erros.push('Demandas em contexto escolar');
            $('#avalDemandasEscolares').addClass('campo-invalido');
        }
        for (var i = 0; i < listaHabilidades.length; i++) {
            var key = 'hab_' + i;
            var nivelMarcado = $('input[name="habilidades[' + key + '][nivel]"]:checked').length > 0;
            var ordemSelecionada = $('select[name="habilidades[' + key + '][ordem]"]').val();
            if (!nivelMarcado || !ordemSelecionada) {
                erros.push('Habilidade: ' + listaHabilidades[i] + (!nivelMarcado ? ' (n\u00edvel)' : '') + (!ordemSelecionada ? ' (ordem)' : ''));
                var tr = $('input[name="habilidades[' + key + '][nivel]"]').closest('tr');
                tr.addClass('campo-invalido-grupo');
            }
        }
        if ($('input[name="comportamentosDificeis[]"]:checked').length === 0) {
            erros.push('Comportamentos dif\u00edceis (marque pelo menos um)');
            $('#areaComportamentos').addClass('campo-invalido-grupo');
        }
        if ($('#chkCompOutros').is(':checked') && !$('#avalCompOutros').val().trim()) {
            erros.push('Descri\u00e7\u00e3o de outros comportamentos');
            $('#avalCompOutros').addClass('campo-invalido');
        }

        return erros;
    }

    function salvarAvaliacao() {
        var codAluno = $('#avalCodAluno').val();
        if (!codAluno) {
            // Tentar pegar do select da tab 2
            codAluno = $('#avalSelectAluno').val();
        }
        if (!codAluno) {
            $('#modalAvalMsg').html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Nenhum aluno selecionado.</div>');
            return;
        }

        var erros = validarFormAvaliacao();
        if (erros.length > 0) {
            var html = '<div class="alert alert-danger"><strong>Preencha os campos obrigat\u00f3rios:</strong><ul style="margin:5px 0 0 0;">';
            for (var i = 0; i < erros.length; i++) {
                html += '<li>' + erros[i] + '</li>';
            }
            html += '</ul></div>';
            $('#modalAvalMsg').html(html);
            $('#modalAvaliacao .modal-body').scrollTop(0);
            return;
        }

        $('#avalCodAluno').val(codAluno);
        $('#modalAvalMsg').html('');

        $('#formAvaliacao').ajaxSubmit({
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            timeout: 30000,
            error: function(ajax) {
                $('#modalAvalMsg').html('<div class="alert alert-danger">' + (ajax.responseText || 'Erro ao processar.') + '</div>');
            },
            success: function(data) {
                if (data.statusMessage) {
                    // Recarregar a tabela da aba de origem
                    if (avalOrigemTab === 'programacao') {
                        var codProg = $('#avalSelectProgramacao').val();
                        if (codProg) carregarTabelaAlunosPorProgramacao(codProg);
                    } else {
                        var codAlunoTab = $('#avalSelectAluno').val();
                        if (codAlunoTab) carregarTabelaAvaliacoes(codAlunoTab);
                    }
                    limparCamposAvaliacao();
                    $('#modalAvaliacao').modal('hide');
                    processJson(data);
                } else {
                    $('#modalAvalMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function editarAvaliacao(codAvaliacao) {
        if (!codAvaliacao) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codAvaliacao': codAvaliacao, 'c': 'GerenciarAvaliacaoInicial', 'm': 'editarAvaliacao', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                limparCamposAvaliacao();
                $('#modalAvalMsg').html('');
                $('#avalCodAvaliacao').val(data.codAvaliacao);
                $('#avalCodAluno').val(data.codAluno);
                $('#avalTipoAvaliacao').val(data.tipoAvaliacao);
                $('#avalDataAvaliacao').val(data.dataAvaliacao);
                $('#avalNivelSuporte').val(data.nivelSuporte);
                $('#avalFazTerapia').val(data.fazTerapia);
                toggleTerapiaDetalhes(data.fazTerapia);
                $('#avalTerapiaLocal').val(data.terapiaLocal);
                $('#avalTerapiaTipos').val(data.terapiaTipos);
                $('#avalDemandasEscolares').val(data.demandasEscolares);

                if (data.habilidades) {
                    $.each(data.habilidades, function(key, hab) {
                        if (hab.nivel) {
                            $('input[name="habilidades[' + key + '][nivel]"][value="' + hab.nivel + '"]').prop('checked', true);
                        }
                        if (hab.ordem) {
                            $('select[name="habilidades[' + key + '][ordem]"]').val(hab.ordem);
                        }
                    });
                }

                if (data.comportamentosDificeis) {
                    $.each(data.comportamentosDificeis, function(i, val) {
                        $('input[name="comportamentosDificeis[]"][value="' + val + '"]').prop('checked', true);
                    });
                    if (data.comportamentosDificeis.indexOf('Outros') >= 0) {
                        $('#areaCompOutros').show();
                    }
                }
                if (data.comportamentosOutros) {
                    $('#avalCompOutros').val(data.comportamentosOutros);
                }

                $('#avalNovoCodAvaliacao').val(1);
                $('#modalAvalLabel').text('Editar Avalia\u00e7\u00e3o de Repert\u00f3rio');
                $('#modalAvaliacao').modal('show');
                $('#modalAvaliacao .modal-body').scrollTop(0);
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    var _codAvaliacaoExcluir = null;

    function excluirAvaliacao(codAvaliacao) {
        _codAvaliacaoExcluir = codAvaliacao;
        $('#modalExcluirAval').modal('show');
    }

    function confirmarExclusaoAvaliacao() {
        if (!_codAvaliacaoExcluir) return;
        $('#modalExcluirAval').modal('hide');
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codAvaliacao': _codAvaliacaoExcluir, 'c': 'GerenciarAvaliacaoInicial', 'm': 'excluirAvaliacao', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    var codAluno = $('#avalSelectAluno').val();
                    if (codAluno) carregarTabelaAvaliacoes(codAluno);
                }
            },
            complete: function() {
                stopLoading();
                _codAvaliacaoExcluir = null;
            }
        });
    }

    function limparCamposAvaliacao() {
        $('#avalCodAvaliacao').val('');
        $('#avalCodAluno').val('');
        $('#avalTipoAvaliacao').val('');
        $('#avalDataAvaliacao').val('');
        $('#avalNivelSuporte').val('');
        $('#avalFazTerapia').val('');
        toggleTerapiaDetalhes('');
        $('#avalTerapiaLocal').val('');
        $('#avalTerapiaTipos').val('');
        $('#avalDemandasEscolares').val('');
        $('#avalCompOutros').val('');
        $('#areaHabilidades input[type="radio"]').prop('checked', false);
        $('#areaHabilidades select').val('');
        $('#areaComportamentos input[type="checkbox"]').prop('checked', false);
        $('#areaCompOutros').hide();
        $('#avalNovoCodAvaliacao').val(0);
        $('#modalAvalLabel').text('Nova Avalia\u00e7\u00e3o de Repert\u00f3rio');
        avalOrigemTab = 'aluno';
    }

    function abrirModalNovaAvaliacao() {
        if (!$('#avalSelectAluno').val()) {
            $('#mensagemAval').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-exclamation-triangle"></i> Selecione um aluno primeiro.</div>');
            return;
        }
        limparCamposAvaliacao();
        $('#avalCodAluno').val($('#avalSelectAluno').val());
        avalOrigemTab = 'aluno';
        $('#modalAvalMsg').html('');
        $('#modalAvaliacao').modal('show');
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

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><i class="fa fa-clipboard"></i> Avalia&ccedil;&atilde;o de Repert&oacute;rio</h4>
        </div>
        <div class="panel-body">

            <!-- Abas -->
            <ul class="nav nav-tabs" role="tablist" style="margin-bottom:15px;">
                <li role="presentation" class="active"><a href="#tabProgramacao" data-toggle="tab" role="tab"><i class="fa fa-calendar"></i> Por Calend&aacute;rio</a></li>
                <li role="presentation"><a href="#tabAluno" id="tabAvalAluno" data-toggle="tab" role="tab"><i class="fa fa-user"></i> Por Aluno</a></li>
            </ul>

            <div class="tab-content">

                <!-- TAB 1: Por Programacao/Calendario -->
                <div role="tabpanel" class="tab-pane active" id="tabProgramacao">

                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-sm-8">
                            <label for="avalSelectProgramacao">Selecionar Calend&aacute;rio de Avalia&ccedil;&atilde;o:</label>
                            <select id="avalSelectProgramacao" class="form-control">
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                    </div>

                    <div id="avalAreaTabelaProg" style="display:none;">

                        <div id="carregando" class="shown hidden">
                            <div class="imagem">
                                <div class="frase">Carregando...</div>
                                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
                            </div>
                        </div>
                        <div id="mensagem"></div>
                        <div id="sucesso" class="hidden"></div>
                        <div id="erro" class="hidden"></div>

                        <div class="table-responsive">
                            <table id="tabelaAlunosProg" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>C&oacute;digo</th>
                                        <th>Iniciais</th>
                                        <th>S&eacute;rie/Turma</th>
                                        <th>Turno</th>
                                        <th>Col&eacute;gio</th>
                                        <th>Situa&ccedil;&atilde;o</th>
                                        <th>A&ccedil;&otilde;es</th>
                                    </tr>
                                </thead>
                                <tbody class="inputs">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <p class="text-muted" style="font-size:13px; margin-top:15px;">
                        <i class="fa fa-info-circle"></i> Selecione um calend&aacute;rio para visualizar o status das avalia&ccedil;&otilde;es de cada aluno.
                    </p>

                </div>

                <!-- TAB 2: Por Aluno -->
                <div role="tabpanel" class="tab-pane" id="tabAluno">

                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-sm-6">
                            <label for="avalSelectAluno">Selecionar Aluno:</label>
                            <select id="avalSelectAluno" class="form-control">
                                <option value="">Selecione um aluno...</option>
                            </select>
                        </div>
                    </div>

                    <div id="avalAreaTabela" style="display:none;">

                        <div style="margin-bottom:12px;">
                            <button class="btn btn-success btn-xs" onclick="abrirModalNovaAvaliacao()">
                                <i class="fa fa-plus"></i> Nova Avalia&ccedil;&atilde;o de Repert&oacute;rio
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="tabelaAvaliacoes" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Data</th>
                                        <th>N&iacute;vel de Suporte</th>
                                        <th>Faz Terapia</th>
                                        <th>Demandas Escolares</th>
                                        <th>A&ccedil;&otilde;es</th>
                                    </tr>
                                </thead>
                                <tbody class="inputs">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <p class="text-muted" style="font-size:13px; margin-top:15px;">
                        <i class="fa fa-info-circle"></i> Selecione um aluno para visualizar e gerenciar suas avalia&ccedil;&otilde;es de repert&oacute;rio.
                    </p>

                </div>

            </div><!-- tab-content -->

        </div>
    </div>
</div>

<!-- Mensagem inline para validacoes fora do modal -->
<div id="mensagemAval"></div>

<!-- Modal Confirmar Exclusao Avaliacao -->
<div class="modal fade" id="modalExcluirAval" tabindex="-1" role="dialog">
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
                <button type="button" class="btn btn-danger" onclick="confirmarExclusaoAvaliacao()"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova/Editar Avaliacao -->
<div class="modal fade" id="modalAvaliacao" tabindex="-1" role="dialog" aria-labelledby="modalAvalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formAvaliacao" name="formAvaliacao" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalAvalLabel">Nova Avalia&ccedil;&atilde;o de Repert&oacute;rio</h4>
                </div>
                <div class="modal-body">

                    <div id="modalAvalMsg"></div>

                    <!-- Tipo e Data da Avaliacao -->
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="avalTipoAvaliacao">Tipo: <span class="text-danger">*</span></label>
                                <select id="avalTipoAvaliacao" name="tipoAvaliacao" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="Inicial">Inicial</option>
                                    <option value="Acompanhamento">Acompanhamento</option>
                                    <option value="Final">Final</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="avalDataAvaliacao">Data da Avalia&ccedil;&atilde;o: <span class="text-danger">*</span></label>
                                <input type="date" id="avalDataAvaliacao" name="dataAvaliacao" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Nivel de Suporte -->
                    <div class="form-group">
                        <label for="avalNivelSuporte">N&iacute;vel de Suporte: <span class="text-danger">*</span></label>
                        <select id="avalNivelSuporte" name="nivelSuporte" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="Nivel 1">N&iacute;vel 1 - Necessita de pouco suporte</option>
                            <option value="Nivel 2">N&iacute;vel 2 - Necessita de suporte substancial</option>
                            <option value="Nivel 3">N&iacute;vel 3 - Necessita de suporte muito substancial</option>
                        </select>
                    </div>

                    <!-- Terapia -->
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="avalFazTerapia">Faz terapia (contraturno)?</label>
                                <select id="avalFazTerapia" name="fazTerapia" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Nao">N&atilde;o</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-9" id="areaTerapiaDetalhes" style="display:none;">
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="avalTerapiaLocal">Local:</label>
                                        <input type="text" id="avalTerapiaLocal" name="terapiaLocal" class="form-control" maxlength="200" placeholder="Ex: Clinica ABC">
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="form-group">
                                        <label for="avalTerapiaTipos">Tipos de terapia:</label>
                                        <input type="text" id="avalTerapiaTipos" name="terapiaTipos" class="form-control" maxlength="300" placeholder="Ex: Fono, ABA, TO, Psicoterapia">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Demandas Escolares -->
                    <div class="form-group">
                        <label for="avalDemandasEscolares">Principais demandas em contexto escolar:</label>
                        <textarea id="avalDemandasEscolares" name="demandasEscolares" class="form-control" rows="3"
                                  placeholder="Descreva as principais demandas observadas no contexto escolar..."></textarea>
                    </div>

                    <!-- Habilidades -->
                    <div class="panel panel-info" style="margin-top:15px;">
                        <div class="panel-heading" style="font-size:13px;">
                            <strong>Habilidades</strong> - Defina o n&iacute;vel de prioridade (1=Baixa, 2=M&eacute;dia, 3=Alta) e a ordem de prioridade (1 a 9)
                        </div>
                        <div class="panel-body" style="padding:8px;">
                            <div id="areaHabilidades"></div>
                        </div>
                    </div>

                    <!-- Comportamentos Dificeis -->
                    <div class="panel panel-warning" style="margin-top:15px;">
                        <div class="panel-heading" style="font-size:13px;">
                            <strong>Comportamentos Dif&iacute;ceis</strong> - Marque os que se aplicam
                        </div>
                        <div class="panel-body" style="padding:8px;">
                            <div id="areaComportamentos"></div>
                        </div>
                    </div>

                    <input type="hidden" name="codAvaliacao" id="avalCodAvaliacao" value="">
                    <input type="hidden" name="codAluno" id="avalCodAluno" value="">
                    <input type="hidden" name="novoCodAvaliacao" id="avalNovoCodAvaliacao" value="0">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarAvaliacaoInicial" name="c">
                    <input type="hidden" value="salvarAvaliacao" name="m">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarAvaliacao">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
