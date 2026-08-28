<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarAvaliacaoPESAC();
?>
<style>
    .campo-invalido { border-color: #d9534f !important; box-shadow: 0 0 4px rgba(217,83,79,0.4) !important; }
    .campo-invalido-grupo { outline: 2px solid #d9534f; outline-offset: 2px; border-radius: 4px; }
    #modalAvalPES .modal-body { max-height: 70vh; overflow-y: auto; }
    .painel-sup-colegio { margin-bottom:20px; }
    .painel-sup-colegio h5 { background:#E3F2FD; padding:8px 12px; border-radius:4px; color:#1565C0; font-weight:600; margin-bottom:8px; }
    .media-boa { color:#2E7D32; }
    .media-media { color:#E65100; }
    .media-baixa { color:#C62828; }
</style>
<script type="text/javascript">
    var isAdminPedagogico = <?php echo $_isAdminPedagogico ? 'true' : 'false' ?>;

    var listaPerguntas = [
        {campo: 'segueOrientacoes',        texto: 'Segue as orienta\u00e7\u00f5es fornecidas pelo(a) supervisor(a)'},
        {campo: 'aberturaFeedback',        texto: 'Demonstra abertura para receber feedback e ajustar sua atua\u00e7\u00e3o'},
        {campo: 'comunicaDificuldades',    texto: 'Comunica d\u00favidas ou dificuldades antes de tomar decis\u00f5es que podem impactar o trabalho'},
        {campo: 'posturaProfissional',     texto: 'Mant\u00e9m postura profissional adequada na escola'},
        {campo: 'relacionamentoEquipe',    texto: 'Mant\u00e9m bom relacionamento com professores e equipe escolar'},
        {campo: 'desempenhoAdequado',      texto: 'Desempenho geral deste PES est\u00e1 adequado para a fun\u00e7\u00e3o'},
        {campo: 'necessitaAcompanhamento', texto: 'Considera necess\u00e1rio que esse PES tenha acompanhamento mais de perto da coordena\u00e7\u00e3o'}
    ];

    var listaOpcoes = [
        {valor: 'Nao', label: 'N\u00e3o'},
        {valor: 'Parcialmente', label: 'Parcialmente'},
        {valor: 'Sim', label: 'Sim'},
        {valor: 'NaoObservado', label: 'N\u00e3o observado'}
    ];

    $(document).ready(function() {
        if (isAdminPedagogico) {
            $('#areaSelectorSupervisor').show();
            carregarListaSupervisores();

            $('#avalPesSelectSupervisor').on('change', function() {
                var codSupervisor = $(this).val();
                if (codSupervisor) {
                    carregarTabelaPES(codSupervisor);
                    $('#avalPesAreaTabela').show();
                } else {
                    $('#avalPesAreaTabela').hide();
                }
            });
        } else {
            // Supervisor logado - carregar diretamente
            $('#areaSelectorSupervisor').hide();
            carregarTabelaPES(0); // 0 = backend usa o usuario logado
            $('#avalPesAreaTabela').show();
        }

        montarPerguntasHTML();

        $('#btnSalvarAvalPES').click(function() {
            salvarAvaliacaoPES();
        });

        // Limpar marcacao de erro ao interagir
        $(document).on('change keyup', '#formAvalPES select, #formAvalPES input, #formAvalPES textarea', function() {
            $(this).removeClass('campo-invalido');
            $(this).closest('tr').removeClass('campo-invalido-grupo');
        });

        // Tab painel comparativo
        $('#tabPainelSupLink').on('shown.bs.tab click', function() {
            carregarPainelComparativoSup();
        });
    });

    // ===================== PAINEL COMPARATIVO =====================

    function carregarPainelComparativoSup() {
        $('#painelComparativoSupConteudo').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarAvaliacaoPES', 'm': 'carregarPainelComparativo', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            success: function(data) {
                if (!data.statusMessage || !data.porColegio || Object.keys(data.porColegio).length === 0) {
                    $('#painelComparativoSupConteudo').html('<div class="alert alert-info"><i class="fa fa-info-circle"></i> Nenhuma avalia\u00e7\u00e3o encontrada.</div>');
                    return;
                }

                var html = '';
                $.each(data.porColegio, function(nomeColegio, agentes) {
                    html += '<div class="painel-sup-colegio">';
                    html += '<h5><i class="fa fa-building"></i> ' + nomeColegio + '</h5>';
                    html += '<table class="table table-bordered table-condensed" style="font-size:12px;">';
                    html += '<thead><tr><th>PES</th><th>Supervisor</th><th class="text-center">Aval.</th><th class="text-center">Segue Orient.</th><th class="text-center">Feedback</th><th class="text-center">Comunica</th><th class="text-center">Postura</th><th class="text-center">Relacion.</th><th class="text-center">Desemp.</th><th class="text-center">Acomp.?</th></tr></thead><tbody>';

                    for (var i = 0; i < agentes.length; i++) {
                        var ag = agentes[i];
                        var acomp = ag.necessitaAcompanhamento === 'Sim' ? '<span class="label label-danger">Sim</span>' : '<span class="label label-success">N\u00e3o</span>';
                        html += '<tr>';
                        html += '<td>' + ag.nomeAgente + '</td>';
                        html += '<td>' + ag.nomeSupervisor + '</td>';
                        html += '<td class="text-center">' + ag.totalAvaliacoes + '</td>';
                        html += '<td class="text-center">' + formatMediaSup(ag.mediaSegue) + '</td>';
                        html += '<td class="text-center">' + formatMediaSup(ag.mediaAbertura) + '</td>';
                        html += '<td class="text-center">' + formatMediaSup(ag.mediaComunica) + '</td>';
                        html += '<td class="text-center">' + formatMediaSup(ag.mediaPostura) + '</td>';
                        html += '<td class="text-center">' + formatMediaSup(ag.mediaRelacionamento) + '</td>';
                        html += '<td class="text-center">' + formatMediaSup(ag.mediaDesempenho) + '</td>';
                        html += '<td class="text-center">' + acomp + '</td>';
                        html += '</tr>';
                    }

                    html += '</tbody></table></div>';
                });

                $('#painelComparativoSupConteudo').html(html);
            }
        });
    }

    function formatMediaSup(valor) {
        if (!valor || valor == 0) return '-';
        var classe = valor >= 2.5 ? 'media-boa' : (valor >= 1.5 ? 'media-media' : 'media-baixa');
        return '<span class="' + classe + '">' + valor + '/3</span>';
    }

    // ===================== CARREGAR SUPERVISORES (ADMIN) =====================

    function carregarListaSupervisores() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarAvaliacaoPES', 'm': 'carregarListaSupervisoresParaSelect', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            success: function(data) {
                var select = $('#avalPesSelectSupervisor');
                select.find('option:not(:first)').remove();
                if (data.supervisores && data.supervisores.length > 0) {
                    $.each(data.supervisores, function(i, s) {
                        select.append('<option value="' + s.codUsuario + '">' + s.nome + '</option>');
                    });
                }
            }
        });
    }

    // ===================== CARREGAR TABELA DE PES =====================

    function carregarTabelaPES(codSupervisor) {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarAvaliacaoPES', 'm': 'carregarTabelaPES', 'codSupervisor': codSupervisor, '_submit_check': '1'},
            function(html) {
                $("table#tabelaPES tbody.inputs tr").remove();
                $("table#tabelaPES tbody.inputs").append(html);

                // Botao nova avaliacao
                $('.nova-avaliacao-pes').click(function() {
                    var codAgente = $(this).data('cod-agente');
                    var nome = $(this).data('nome');
                    abrirModalNovaAvaliacaoPES(codAgente, nome);
                });

                // Botao ver/editar avaliacao
                $('.ver-avaliacao-pes').click(function() {
                    var codAval = $(this).data('cod-avaliacao');
                    editarAvaliacaoPES(codAval);
                });

                // Botao excluir avaliacao
                $('.excluir-avaliacao-pes').click(function() {
                    var codAval = $(this).data('cod-avaliacao');
                    excluirAvaliacaoPES(codAval);
                });
            }
        );
    }

    // ===================== MONTAR PERGUNTAS HTML =====================

    function montarPerguntasHTML() {
        var html = '';
        html += '<table class="table table-bordered table-condensed" style="font-size:13px;">';
        html += '<thead><tr><th style="width:50%;">Crit&eacute;rio</th>';
        for (var o = 0; o < listaOpcoes.length; o++) {
            html += '<th class="text-center" style="width:12.5%;">' + listaOpcoes[o].label + '</th>';
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
        $('#areaPerguntas').html(html);
    }

    // ===================== MODAL =====================

    function abrirModalNovaAvaliacaoPES(codAgente, nome) {
        limparCamposAvalPES();
        $('#modalAvalPESMsg').html('');
        $('#avalPesCodAgente').val(codAgente);
        $('#avalPesNomeAgente').text(nome);
        // Preencher data automaticamente com a data de hoje
        var hoje = new Date();
        var dataFormatada = hoje.getFullYear() + '-' + String(hoje.getMonth() + 1).padStart(2, '0') + '-' + String(hoje.getDate()).padStart(2, '0');
        $('#avalPesDataAvaliacao').val(dataFormatada);
        $('#modalAvalPESLabel').text('Avalia\u00e7\u00e3o do PES - ' + nome);
        $('#modalAvalPES').modal('show');
    }

    function editarAvaliacaoPES(codAvaliacao) {
        if (!codAvaliacao) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codAvaliacao': codAvaliacao, 'c': 'GerenciarAvaliacaoPES', 'm': 'editarAvaliacaoPES', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                limparCamposAvalPES();
                $('#modalAvalPESMsg').html('');
                $('#avalPesCodAvaliacao').val(data.codAvaliacao);
                $('#avalPesCodAgente').val(data.codAgente);
                $('#avalPesDataAvaliacao').val(data.dataAvaliacao);
                $('#avalPesObservacoes').val(data.observacoes);

                for (var i = 0; i < listaPerguntas.length; i++) {
                    var campo = listaPerguntas[i].campo;
                    if (data[campo]) {
                        $('input[name="' + campo + '"][value="' + data[campo] + '"]').prop('checked', true);
                    }
                }

                $('#avalPesNovoCodAvaliacao').val(1);
                $('#modalAvalPESLabel').text('Editar Avalia\u00e7\u00e3o do PES');
                $('#modalAvalPES').modal('show');
                $('#modalAvalPES .modal-body').scrollTop(0);
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    var _codAvalPESExcluir = null;

    function excluirAvaliacaoPES(codAvaliacao) {
        _codAvalPESExcluir = codAvaliacao;
        $('#modalExcluirAvalPES').modal('show');
    }

    function confirmarExclusaoAvalPES() {
        if (!_codAvalPESExcluir) return;
        $('#modalExcluirAvalPES').modal('hide');
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codAvaliacao': _codAvalPESExcluir, 'c': 'GerenciarAvaliacaoPES', 'm': 'excluirAvaliacaoPES', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    var codSupervisor = isAdminPedagogico ? ($('#avalPesSelectSupervisor').val() || 0) : 0;
                    carregarTabelaPES(codSupervisor);
                }
            },
            complete: function() {
                stopLoading();
                _codAvalPESExcluir = null;
            }
        });
    }

    // ===================== VALIDACAO =====================

    function validarFormAvalPES() {
        var erros = [];
        $('#formAvalPES .campo-invalido').removeClass('campo-invalido');
        $('#formAvalPES .campo-invalido-grupo').removeClass('campo-invalido-grupo');

        if (!$('#avalPesDataAvaliacao').val()) {
            erros.push('Data da Avalia\u00e7\u00e3o');
            $('#avalPesDataAvaliacao').addClass('campo-invalido');
        }

        for (var i = 0; i < listaPerguntas.length; i++) {
            var campo = listaPerguntas[i].campo;
            if ($('input[name="' + campo + '"]:checked').length === 0) {
                erros.push('Pergunta ' + (i + 1) + ': ' + listaPerguntas[i].texto);
                $('input[name="' + campo + '"]').closest('tr').addClass('campo-invalido-grupo');
            }
        }

        return erros;
    }

    // ===================== SALVAR =====================

    function salvarAvaliacaoPES() {
        var codAgente = $('#avalPesCodAgente').val();
        if (!codAgente) {
            $('#modalAvalPESMsg').html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Nenhum PES selecionado.</div>');
            return;
        }

        var erros = validarFormAvalPES();
        if (erros.length > 0) {
            var html = '<div class="alert alert-danger"><strong>Preencha os campos obrigat\u00f3rios:</strong><ul style="margin:5px 0 0 0;">';
            for (var i = 0; i < erros.length; i++) {
                html += '<li>' + erros[i] + '</li>';
            }
            html += '</ul></div>';
            $('#modalAvalPESMsg').html(html);
            $('#modalAvalPES .modal-body').scrollTop(0);
            return;
        }

        $('#modalAvalPESMsg').html('');

        // Definir codSupervisor para admin
        if (isAdminPedagogico) {
            $('#avalPesCodSupervisor').val($('#avalPesSelectSupervisor').val());
        }

        $('#formAvalPES').ajaxSubmit({
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            timeout: 30000,
            error: function(ajax) {
                $('#modalAvalPESMsg').html('<div class="alert alert-danger">' + (ajax.responseText || 'Erro ao processar.') + '</div>');
            },
            success: function(data) {
                if (data.statusMessage) {
                    var codSupervisor = isAdminPedagogico ? ($('#avalPesSelectSupervisor').val() || 0) : 0;
                    carregarTabelaPES(codSupervisor);
                    limparCamposAvalPES();
                    $('#modalAvalPES').modal('hide');
                    processJson(data);
                } else {
                    $('#modalAvalPESMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    // ===================== LIMPAR =====================

    function limparCamposAvalPES() {
        $('#avalPesCodAvaliacao').val('');
        $('#avalPesCodAgente').val('');
        $('#avalPesCodSupervisor').val('');
        $('#avalPesDataAvaliacao').val('');
        $('#avalPesObservacoes').val('');
        $('#areaPerguntas input[type="radio"]').prop('checked', false);
        $('#avalPesNovoCodAvaliacao').val(0);
        $('#avalPesNomeAgente').text('');
        $('#modalAvalPESLabel').text('Avalia\u00e7\u00e3o do PES');
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
            <h4><i class="fa fa-clipboard"></i> Avalia&ccedil;&atilde;o PES por Supervisor</h4>
        </div>
        <div class="panel-body">

            <p class="text-muted" style="font-size:13px; margin-bottom:15px;">
                <i class="fa fa-info-circle"></i>
                Objetivo de acompanhar se o trabalho do Supervisor e do PES est&aacute; fluindo, se h&aacute; atritos, e identificar necessidades de coordena&ccedil;&atilde;o.
            </p>

            <!-- Tabs -->
            <ul class="nav nav-tabs" style="margin-bottom:15px;">
                <li class="active"><a data-toggle="tab" href="#tabAvalSup">Avalia&ccedil;&otilde;es</a></li>
                <?php if ($_isAdminPedagogico): ?>
                <li><a data-toggle="tab" href="#tabPainelSup" id="tabPainelSupLink">Painel Comparativo</a></li>
                <?php endif; ?>
            </ul>

            <div class="tab-content">

            <!-- Tab Avaliacoes -->
            <div id="tabAvalSup" class="tab-pane fade in active">

            <!-- Seletor de Supervisor (somente para admins) -->
            <div id="areaSelectorSupervisor" style="display:none;">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-sm-6">
                        <label for="avalPesSelectSupervisor">Selecionar Supervisor:</label>
                        <select id="avalPesSelectSupervisor" class="form-control">
                            <option value="">Selecione um supervisor...</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabela de PES -->
            <div id="avalPesAreaTabela" style="display:none;">

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
                    <table id="tabelaPES" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nome do PES</th>
                                <th class="text-center">Alunos</th>
                                <th class="text-center">Situa&ccedil;&atilde;o (M&ecirc;s Atual)</th>
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
            <div id="tabPainelSup" class="tab-pane fade">
                <div id="painelComparativoSupConteudo">
                    <div class="alert alert-info"><i class="fa fa-info-circle"></i> Clique na aba para carregar o painel comparativo.</div>
                </div>
            </div>
            <?php endif; ?>

            </div>

        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusao -->
<div class="modal fade" id="modalExcluirAvalPES" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
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
                <button type="button" class="btn btn-danger" onclick="confirmarExclusaoAvalPES()"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Avaliacao do PES -->
<div class="modal fade" id="modalAvalPES" tabindex="-1" role="dialog" aria-labelledby="modalAvalPESLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formAvalPES" name="formAvalPES" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalAvalPESLabel">Avalia&ccedil;&atilde;o do PES</h4>
                </div>
                <div class="modal-body">

                    <div id="modalAvalPESMsg"></div>

                    <!-- Nome do PES (informativo) -->
                    <div class="well well-sm" style="margin-bottom:15px;">
                        <strong>PES:</strong> <span id="avalPesNomeAgente"></span>
                    </div>

                    <!-- Data da Avaliacao -->
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="avalPesDataAvaliacao">Data da Avalia&ccedil;&atilde;o: <span class="text-danger">*</span></label>
                                <input type="date" id="avalPesDataAvaliacao" name="dataAvaliacao" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Perguntas -->
                    <div class="panel panel-info" style="margin-top:10px;">
                        <div class="panel-heading" style="font-size:13px;">
                            <strong>Avalia&ccedil;&atilde;o</strong> - Marque uma op&ccedil;&atilde;o para cada crit&eacute;rio <span class="text-danger">*</span>
                        </div>
                        <div class="panel-body" style="padding:8px;">
                            <div id="areaPerguntas"></div>
                        </div>
                    </div>

                    <!-- Observacoes -->
                    <div class="form-group">
                        <label for="avalPesObservacoes">Observa&ccedil;&otilde;es (opcional):</label>
                        <textarea id="avalPesObservacoes" name="observacoes" class="form-control" rows="3"
                                  placeholder="Observa&ccedil;&otilde;es adicionais sobre o desempenho deste PES..."></textarea>
                    </div>

                    <input type="hidden" name="codAvaliacao" id="avalPesCodAvaliacao" value="">
                    <input type="hidden" name="codAgente" id="avalPesCodAgente" value="">
                    <input type="hidden" name="codSupervisor" id="avalPesCodSupervisor" value="">
                    <input type="hidden" name="novoCodAvaliacao" id="avalPesNovoCodAvaliacao" value="0">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarAvaliacaoPES" name="c">
                    <input type="hidden" value="salvarAvaliacaoPES" name="m">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarAvalPES"><i class="fa fa-save"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
