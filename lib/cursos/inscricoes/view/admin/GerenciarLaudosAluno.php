<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarLaudoAlunoAC();
$codAlunoParam = isset($fwsGet_['codAluno']) ? $fwsGet_['codAluno'] : '';
?>
<style>
    #modalLaudo .modal-body { max-height: 70vh; overflow-y: auto; }
    .campo-invalido { border-color: #d9534f !important; box-shadow: 0 0 4px rgba(217,83,79,0.4) !important; }
</style>
<script type="text/javascript">
    var laudoCodAluno = '<?php echo addslashes($codAlunoParam) ?>';
    var _todosAlunos = [];

    $(document).ready(function() {
        $('#btnSalvarLaudo').click(function() {
            salvarLaudo();
        });
        $('#btnLimparLaudo').click(function() {
            limparCamposLaudo();
        });

        // Limpar marcacao de erro ao interagir com o campo
        $(document).on('change keyup', '#formLaudo select, #formLaudo input, #formLaudo textarea', function() {
            $(this).removeClass('campo-invalido');
        });

        // Filtro de colégio
        $('#laudoSelectColegio').on('change', function() {
            filtrarAlunosPorColegio();
        });

        // Registrar handler ANTES de carregar lista, pois o trigger('change') dispara dentro do AJAX
        $('#laudoSelectAluno').on('change', function() {
            var codAluno = $(this).val();
            if (codAluno) {
                carregarTabelaLaudos(codAluno);
                $('#laudoAreaTabela').show();
                $('#laudoLinkRegistros').attr('href', 'index.php?content=view/admin/ConsultarRegistrosPedagogicosAluno.php&codAluno=' + codAluno);
                $('#laudoLinkAvaliacao').attr('href', 'index.php?content=view/admin/GerenciarAvaliacaoInicial.php&codAluno=' + codAluno);
                $('#laudoLinksAluno').show();
            } else {
                $('#laudoAreaTabela').hide();
                $('#laudoLinksAluno').hide();
            }
        });

        carregarListaAlunosLaudo();
    });

    function carregarListaAlunosLaudo() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarAluno', 'm': 'carregarListaAlunos', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            success: function(data) {
                _todosAlunos = data.alunos || [];
                montarSelectColegios();
                if (laudoCodAluno) {
                    // Auto-selecionar o colégio do aluno que veio por parâmetro
                    for (var i = 0; i < _todosAlunos.length; i++) {
                        if (_todosAlunos[i].codAluno == laudoCodAluno && _todosAlunos[i].codColegio) {
                            $('#laudoSelectColegio').val(_todosAlunos[i].codColegio);
                            break;
                        }
                    }
                }
                filtrarAlunosPorColegio();
                if (laudoCodAluno) {
                    $('#laudoSelectAluno').val(laudoCodAluno).trigger('change');
                }
            }
        });
    }

    function montarSelectColegios() {
        var colegios = {};
        for (var i = 0; i < _todosAlunos.length; i++) {
            var al = _todosAlunos[i];
            if (al.codColegio && al.nomeColegio && !colegios[al.codColegio]) {
                colegios[al.codColegio] = al.nomeColegio;
            }
        }
        var select = $('#laudoSelectColegio');
        select.find('option:not(:first)').remove();
        var sorted = Object.keys(colegios).sort(function(a, b) {
            return colegios[a].localeCompare(colegios[b]);
        });
        for (var j = 0; j < sorted.length; j++) {
            select.append('<option value="' + sorted[j] + '">' + colegios[sorted[j]] + '</option>');
        }
    }

    function filtrarAlunosPorColegio() {
        var codColegio = $('#laudoSelectColegio').val();
        var select = $('#laudoSelectAluno');
        var valorAtual = select.val();
        select.find('option:not(:first)').remove();

        for (var i = 0; i < _todosAlunos.length; i++) {
            var al = _todosAlunos[i];
            if (codColegio && al.codColegio != codColegio) continue;
            var label = al.codigoExibicao;
            if (al.iniciais) label += ' - ' + al.iniciais;
            if (!codColegio && al.nomeColegio) label += ' (' + al.nomeColegio + ')';
            select.append('<option value="' + al.codAluno + '">' + label + '</option>');
        }

        // Restaurar seleção se o aluno ainda estiver na lista filtrada
        if (valorAtual && select.find('option[value="' + valorAtual + '"]').length) {
            select.val(valorAtual);
        } else {
            select.val('').trigger('change');
        }
    }

    function carregarTabelaLaudos(codAluno) {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarLaudoAluno', 'm': 'carregarTabelaLaudos', 'codAluno': codAluno, '_submit_check': '1'},
            function(html) {
                $("table#tabelaLaudos tbody.inputs tr").remove();
                $("table#tabelaLaudos tbody.inputs").append(html);

                $('.editar-laudo').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    editarLaudo(id);
                });
                $('.excluir-laudo').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    excluirLaudo(id);
                });
                $('.visualizar-laudo').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    visualizarLaudo(id);
                });
                $('.historico-laudo').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    verHistoricoLaudo(id);
                });
            }
        );
    }

    function validarFormLaudo() {
        var erros = [];
        $('#formLaudo .campo-invalido').removeClass('campo-invalido');

        if (!$('#laudoDataLaudo').val()) {
            erros.push('Data do Documento');
            $('#laudoDataLaudo').addClass('campo-invalido');
        }
        if (!$('#laudoFinalidade').val()) {
            erros.push('Finalidade');
            $('#laudoFinalidade').addClass('campo-invalido');
        }
        if (!$.trim($('#laudoDescricao').val())) {
            erros.push('Descri\u00e7\u00e3o');
            $('#laudoDescricao').addClass('campo-invalido');
        }
        if (!$.trim($('#laudoConclusao').val())) {
            erros.push('Conclus\u00e3o');
            $('#laudoConclusao').addClass('campo-invalido');
        }
        if (!$.trim($('#laudoRecomendacao').val())) {
            erros.push('Recomenda\u00e7\u00e3o');
            $('#laudoRecomendacao').addClass('campo-invalido');
        }
        return erros;
    }

    function salvarLaudo() {
        var codAluno = $('#laudoSelectAluno').val();
        if (!codAluno) {
            $('#modalLaudoMsg').html('<div class="alert alert-danger">Selecione um aluno antes de salvar.</div>');
            $('#modalLaudo .modal-body').scrollTop(0);
            return;
        }

        var erros = validarFormLaudo();
        if (erros.length > 0) {
            var html = '<div class="alert alert-danger"><strong>Preencha os campos obrigat\u00f3rios:</strong><ul style="margin:5px 0 0 0;">';
            for (var i = 0; i < erros.length; i++) {
                html += '<li>' + erros[i] + '</li>';
            }
            html += '</ul></div>';
            $('#modalLaudoMsg').html(html);
            $('#modalLaudo .modal-body').scrollTop(0);
            return;
        }

        $('#laudoCodAluno').val(codAluno);
        $('#modalLaudoMsg').html('');

        $('#formLaudo').ajaxSubmit({
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            timeout: 30000,
            error: function(ajax) {
                $('#modalLaudoMsg').html('<div class="alert alert-danger">' + (ajax.responseText || 'Erro ao processar.') + '</div>');
                $('#modalLaudo .modal-body').scrollTop(0);
            },
            success: function(data) {
                if (data.statusMessage) {
                    carregarTabelaLaudos(codAluno);
                    limparCamposLaudo();
                    $('#modalLaudo').modal('hide');
                    processJson(data);
                } else {
                    $('#modalLaudoMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
                    $('#modalLaudo .modal-body').scrollTop(0);
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function editarLaudo(codLaudo) {
        if (!codLaudo) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codLaudo': codLaudo, 'c': 'GerenciarLaudoAluno', 'm': 'editarLaudo', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                $('#modalLaudoMsg').html('');
                $('#laudoCodLaudo').val(data.codLaudo);
                $('#laudoDataLaudo').val(data.dataLaudo);
                $('#laudoFinalidade').val(data.finalidade);
                $('#laudoDescricao').val(data.descricao);
                $('#laudoConclusao').val(data.conclusao);
                $('#laudoRecomendacao').val(data.recomendacao);
                $('#laudoArquivoInput').val('');
                if (data.arquivoNome) {
                    $('#laudoArquivoAtual').html('<i class="fa fa-paperclip"></i> ' + data.arquivoNome + ' <span class="text-muted">(envie outro para substituir)</span>').show();
                } else {
                    $('#laudoArquivoAtual').html('').hide();
                }
                $('#laudoNovoCodLaudo').val(1);

                if (data.ehAutor) {
                    $('#modalLaudoLabel').text('Editar Documento');
                    $('#formLaudo input, #formLaudo textarea').prop('readonly', false);
                    $('#formLaudo select').prop('disabled', false);
                    $('#laudoArquivoInput').prop('disabled', false);
                    $('#btnSalvarLaudo').show();
                } else {
                    $('#modalLaudoLabel').text('Visualizar Documento');
                    $('#formLaudo input, #formLaudo textarea').prop('readonly', true);
                    $('#formLaudo select').prop('disabled', true);
                    $('#laudoArquivoInput').prop('disabled', true);
                    $('#btnSalvarLaudo').hide();
                }

                $('#modalLaudo').modal('show');
                $('#modalLaudo .modal-body').scrollTop(0);
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function visualizarLaudo(codLaudo) {
        if (!codLaudo) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codLaudo': codLaudo, 'c': 'GerenciarLaudoAluno', 'm': 'editarLaudo', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                $('#modalLaudoMsg').html('');
                $('#laudoCodLaudo').val(data.codLaudo);
                $('#laudoDataLaudo').val(data.dataLaudo);
                $('#laudoFinalidade').val(data.finalidade);
                $('#laudoDescricao').val(data.descricao);
                $('#laudoConclusao').val(data.conclusao);
                $('#laudoRecomendacao').val(data.recomendacao);
                $('#laudoArquivoInput').val('');
                if (data.arquivoNome) {
                    $('#laudoArquivoAtual').html('<i class="fa fa-paperclip"></i> ' + data.arquivoNome).show();
                } else {
                    $('#laudoArquivoAtual').html('').hide();
                }
                $('#modalLaudoLabel').text('Visualizar Documento');
                $('#formLaudo input, #formLaudo textarea').prop('readonly', true);
                $('#formLaudo select').prop('disabled', true);
                $('#laudoArquivoInput').prop('disabled', true);
                $('#btnSalvarLaudo').hide();
                $('#modalLaudo').modal('show');
                $('#modalLaudo .modal-body').scrollTop(0);
            },
            complete: function() { stopLoading(); }
        });
    }

    function excluirLaudo(codLaudo) {
        if (!codLaudo) return;
        $('#excluirLaudoCodLaudo').val(codLaudo);
        $('#excluirLaudoJustificativa').val('');
        $('#modalExcluirLaudoMsg').html('');
        $('#modalExcluirLaudo').modal('show');
    }

    function confirmarExclusaoLaudo() {
        var codLaudo = $('#excluirLaudoCodLaudo').val();
        var justificativa = $.trim($('#excluirLaudoJustificativa').val());
        if (!justificativa) {
            $('#modalExcluirLaudoMsg').html('<div class="alert alert-danger">Informe a justificativa para exclus\u00e3o.</div>');
            return;
        }
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codLaudo': codLaudo,
                'justificativa': justificativa,
                'c': 'GerenciarLaudoAluno',
                'm': 'excluirLaudo',
                '_submit_check': '1'
            },
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    $('#modalExcluirLaudo').modal('hide');
                    carregarTabelaLaudos($('#laudoSelectAluno').val());
                } else {
                    $('#modalExcluirLaudoMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro ao excluir.') + '</div>');
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function limparCamposLaudo() {
        $('#laudoCodLaudo').val('');
        $('#laudoDataLaudo').val('');
        $('#laudoFinalidade').val('');
        $('#laudoDescricao').val('');
        $('#laudoConclusao').val('');
        $('#laudoRecomendacao').val('');
        $('#laudoArquivoInput').val('');
        $('#laudoArquivoAtual').html('').hide();
        $('#laudoNovoCodLaudo').val(0);
        $('#modalLaudoLabel').text('Novo Documento');
        $('#formLaudo input, #formLaudo textarea').prop('readonly', false);
        $('#formLaudo select').prop('disabled', false);
        $('#laudoArquivoInput').prop('disabled', false);
        $('#btnSalvarLaudo').show();
    }

    function verHistoricoLaudo(codLaudo) {
        if (!codLaudo) return;
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarLaudoAluno', 'm': 'carregarHistoricoAcesso', 'codLaudo': codLaudo, '_submit_check': '1'},
            function(html) {
                $('#tabelaHistoricoAcesso tbody').html(html);
                $('#modalHistoricoAcesso').modal('show');
            }
        );
    }

    function abrirModalNovoLaudo() {
        if (!$('#laudoSelectAluno').val()) {
            $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-exclamation-triangle"></i> Selecione um aluno primeiro.</div>');
            return;
        }
        limparCamposLaudo();
        $('#modalLaudoMsg').html('');
        $('#modalLaudo').modal('show');
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
            <h4><i class="fa fa-file-text-o"></i> Documentos do Aluno</h4>
        </div>
        <div class="panel-body">

            <div class="row" style="margin-bottom: 15px;">
                <div class="col-sm-4">
                    <label for="laudoSelectColegio">Col&eacute;gio:</label>
                    <select id="laudoSelectColegio" class="form-control">
                        <option value="">Todos os col&eacute;gios</option>
                    </select>
                </div>
                <div class="col-sm-5">
                    <label for="laudoSelectAluno">Aluno:</label>
                    <select id="laudoSelectAluno" class="form-control">
                        <option value="">Selecione um aluno...</option>
                    </select>
                </div>
            </div>

            <div id="laudoAreaTabela" style="display:none;">

                <div style="margin-bottom:12px;">
                    <button class="btn btn-success btn-xs" onclick="abrirModalNovoLaudo()">
                        <i class="fa fa-plus"></i> Novo Documento
                    </button>
                </div>

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
                    <table id="tabelaLaudos" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Finalidade</th>
                                <th>Anexo</th>
                                <th>A&ccedil;&otilde;es</th>
                            </tr>
                        </thead>
                        <tbody class="inputs">
                        </tbody>
                    </table>
                </div>

            </div>

            <p class="text-muted" style="font-size:13px; margin-top:15px;">
                <i class="fa fa-info-circle"></i> Selecione um aluno para visualizar e gerenciar seus documentos (laudos, diagn&oacute;sticos, PEI, planos).
            </p>

        </div>
    </div>
</div>

<!-- Modal Novo/Editar Laudo -->
<div class="modal fade" id="modalLaudo" tabindex="-1" role="dialog" aria-labelledby="modalLaudoLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formLaudo" name="formLaudo" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalLaudoLabel">Novo Documento</h4>
                </div>
                <div class="modal-body">

                    <div id="modalLaudoMsg"></div>

                    <div class="row">
                        <div class="col-sm-5">
                            <div class="form-group">
                                <label for="laudoDataLaudo">Data do Documento: <span class="text-danger">*</span></label>
                                <input type="date" id="laudoDataLaudo" name="dataLaudo" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <label for="laudoFinalidade">Finalidade: <span class="text-danger">*</span></label>
                                <select id="laudoFinalidade" name="finalidade" class="form-control">
                                    <option value="">Selecione...</option>
                                    <optgroup label="Laudos / Diagn&oacute;sticos">
                                        <option value="Diagnostico">Diagn&oacute;stico</option>
                                        <option value="Avaliacao">Avalia&ccedil;&atilde;o</option>
                                        <option value="Encaminhamento">Encaminhamento</option>
                                        <option value="Acompanhamento">Acompanhamento</option>
                                        <option value="Parecer">Parecer</option>
                                    </optgroup>
                                    <optgroup label="Planos">
                                        <option value="PEI">PEI - Plano de Ensino Individualizado</option>
                                        <option value="PlanoAEE">Plano de AEE - Atendimento Educacional Especializado</option>
                                    </optgroup>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="laudoDescricao">Descri&ccedil;&atilde;o: <span class="text-danger">*</span></label>
                        <textarea id="laudoDescricao" name="descricao" class="form-control" rows="5" required
                                  placeholder="Descreva o conte&uacute;do do documento..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="laudoConclusao">Conclus&atilde;o: <span class="text-danger">*</span></label>
                        <textarea id="laudoConclusao" name="conclusao" class="form-control" rows="4"
                                  placeholder="Conclus&atilde;o do documento..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="laudoRecomendacao">Recomenda&ccedil;&atilde;o: <span class="text-danger">*</span></label>
                        <textarea id="laudoRecomendacao" name="recomendacao" class="form-control" rows="4"
                                  placeholder="Recomenda&ccedil;&otilde;es (opcional)..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="laudoArquivoInput">Anexar Arquivo:</label>
                        <input type="file" id="laudoArquivoInput" name="arquivoLaudo" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <p class="help-block" style="font-size:12px; color:#888;">Formatos aceitos: PDF, JPG, PNG, DOC, DOCX. M&aacute;ximo 10MB.</p>
                        <div id="laudoArquivoAtual" style="display:none; font-size:13px; color:#337ab7; margin-top:4px;"></div>
                    </div>

                    <input type="hidden" name="codLaudo" id="laudoCodLaudo" value="">
                    <input type="hidden" name="codAluno" id="laudoCodAluno" value="">
                    <input type="hidden" name="novoCodLaudo" id="laudoNovoCodLaudo" value="0">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarLaudoAluno" name="c">
                    <input type="hidden" value="salvarLaudo" name="m">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarLaudo">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusao -->
<div class="modal fade" id="modalExcluirLaudo" tabindex="-1" role="dialog" aria-labelledby="modalExcluirLaudoLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#f2dede;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalExcluirLaudoLabel"><i class="fa fa-exclamation-triangle text-danger"></i> Confirmar Exclus&atilde;o de Documento</h4>
            </div>
            <div class="modal-body">
                <div id="modalExcluirLaudoMsg"></div>
                <div class="alert alert-danger" style="font-size:13px;">
                    <strong>Aten&ccedil;&atilde;o:</strong> Esta a&ccedil;&atilde;o &eacute; irrevers&iacute;vel. O documento e seu arquivo anexado ser&atilde;o exclu&iacute;dos permanentemente.
                </div>
                <div class="form-group">
                    <label for="excluirLaudoJustificativa">Justificativa da exclus&atilde;o: <span class="text-danger">*</span></label>
                    <textarea id="excluirLaudoJustificativa" class="form-control" rows="3" required
                              placeholder="Informe o motivo da exclus&atilde;o deste documento..."></textarea>
                </div>
                <input type="hidden" id="excluirLaudoCodLaudo" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusaoLaudo()">
                    <i class="fa fa-trash"></i> Confirmar Exclus&atilde;o
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historico de Acesso -->
<div class="modal fade" id="modalHistoricoAcesso" tabindex="-1" role="dialog" aria-labelledby="modalHistoricoAcessoLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalHistoricoAcessoLabel"><i class="fa fa-history"></i> Hist&oacute;rico de Acesso ao Documento</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tabelaHistoricoAcesso" class="table table-bordered table-striped table-hover" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Usu&aacute;rio</th>
                                <th>A&ccedil;&atilde;o</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
