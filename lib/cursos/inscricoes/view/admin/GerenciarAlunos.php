<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarAlunoAC();
?>
<style>
    th.sortable { cursor: pointer; user-select: none; }
    th.sortable:hover { background-color: #e8e8e8; }
    th.sortable::after { content: ' \f0dc'; font-family: FontAwesome; color: #ccc; margin-left: 5px; }
    th.sortable.asc::after { content: ' \f0de'; color: #333; }
    th.sortable.desc::after { content: ' \f0dd'; color: #333; }
</style>
<script type="text/javascript">
    function toggleNome(btn) {
        var td = $(btn).closest('td');
        var iniciais = td.find('.nome-iniciais');
        var nome = td.find('.nome-completo');
        var icon = $(btn).find('i');

        if (nome.is(':visible')) {
            nome.text('').hide();
            iniciais.show();
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            var codAluno = $(btn).closest('tr').attr('id');
            if (!codAluno) return;
            $(btn).prop('disabled', true);
            $.post('<?php echo WB_URL_COMMAND ?>', {
                'c': 'GerenciarAluno', 'm': 'registrarAcessoNome',
                'codAluno': codAluno, '_submit_check': '1'
            }, function(data) {
                $(btn).prop('disabled', false);
                if (data.statusMessage && data.nomeCompleto) {
                    iniciais.hide();
                    nome.text(data.nomeCompleto).show();
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                }
            }, 'json').fail(function() {
                $(btn).prop('disabled', false);
            });
        }
    }
    var seriesPorNivel = {
        'Infantil': [
            {value: 'P4', label: 'P4'},
            {value: 'P5', label: 'P5'}
        ],
        'Fundamental I': [
            {value: '1ano', label: '1\u00ba ano'},
            {value: '2ano', label: '2\u00ba ano'},
            {value: '3ano', label: '3\u00ba ano'},
            {value: '4ano', label: '4\u00ba ano'},
            {value: '5ano', label: '5\u00ba ano'}
        ]
    };

    $(document).ready(function() {
        carregarListaColegios();
        carregarTabelaAlunos();

        $('#btnSalvarAluno').click(function() {
            salvarAluno();
        });
        $('#btnLimparAluno').click(function() {
            limparCamposAluno();
        });

        $('#nivel').on('change', function() {
            atualizarSeries($(this).val());
        });

        $('#tabelaAlunos thead').on('click', 'th.sortable', function() {
            var table = $(this).closest('table');
            var colIndex = $(this).index();
            var isAsc = $(this).hasClass('asc');

            table.find('th.sortable').removeClass('asc desc');
            $(this).addClass(isAsc ? 'desc' : 'asc');

            var rows = table.find('tbody.inputs tr').not(':last').get();
            var direction = isAsc ? -1 : 1;

            rows.sort(function(a, b) {
                var valA = $(a).children('td').eq(colIndex).text().trim().toLowerCase();
                var valB = $(b).children('td').eq(colIndex).text().trim().toLowerCase();

                var numA = parseFloat(valA);
                var numB = parseFloat(valB);
                if (!isNaN(numA) && !isNaN(numB)) {
                    return (numA - numB) * direction;
                }
                return valA.localeCompare(valB, 'pt-BR') * direction;
            });

            var tbody = table.find('tbody.inputs');
            var lastRow = tbody.find('tr:last');
            $.each(rows, function(i, row) {
                lastRow.before(row);
            });
        });
    });

    function atualizarSeries(nivel) {
        var select = $('#serieTurma');
        var valorAtual = select.val();
        select.find('option:not(:first)').remove();
        if (nivel && seriesPorNivel[nivel]) {
            $.each(seriesPorNivel[nivel], function(i, s) {
                select.append('<option value="' + s.value + '">' + s.label + '</option>');
            });
        }
        if (valorAtual) {
            select.val(valorAtual);
        }
    }

    function carregarListaColegios() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarColegio', 'm': 'carregarListaColegios', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            success: function(data) {
                var select = $('#codColegio');
                select.find('option:not(:first)').remove();
                if (data.colegios) {
                    $.each(data.colegios, function(i, col) {
                        select.append('<option value="' + col.codColegio + '">' + col.nome + '</option>');
                    });
                }
            }
        });
    }

    function carregarTabelaAlunos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarAluno', 'm': 'carregarTabelaAlunos', '_submit_check': '1'},
            function(html) {
                $("table#tabelaAlunos tbody.inputs tr").remove();
                $("table#tabelaAlunos tbody.inputs").append(html);

                $('.editar').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    editarAluno(id);
                });
                $('.excluir').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    excluirAluno(id);
                });
            }
        );
    }

    function salvarAluno() {
        $('#modalAlunoMsg').html('').removeClass('sucesso erro sucessoLeve erroLeve');
        $('#formAluno').ajaxSubmit({
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            timeout: 4000,
            error: function(ajax) {
                $('#modalAlunoMsg').html('<div class="alert alert-danger">' + (ajax.responseText || 'Erro ao processar.') + '</div>');
            },
            success: function(data) {
                if (data.statusMessage) {
                    carregarTabelaAlunos();
                    limparCamposAluno();
                    $('#modalAluno').modal('hide');
                    processJson(data);
                } else {
                    $('#modalAlunoMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function editarAluno(codAluno) {
        if (!codAluno) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codAluno': codAluno, 'c': 'GerenciarAluno', 'm': 'editarAluno', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                $('#modalAlunoMsg').html('');
                $('#codAluno').val(data.codAluno);
                $('#codigoExibicao').val(data.codigoExibicao);
                $('#nomeCompleto').val(data.nomeCompleto);
                $('#dataNascimento').val(data.dataNascimento);
                $('#codColegio').val(data.codColegio);
                $('#nivel').val(data.nivel);
                atualizarSeries(data.nivel);
                $('#serieTurma').val(data.serieTurma);
                $('#turno').val(data.turno);
                $('#professoraRegente').val(data.professoraRegente);
                $('#novoCodAluno').val(1);
                $('#modalAlunoLabel').text('Editar Aluno');
                $('#modalAluno').modal('show');
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    var _codAlunoExcluir = null;

    function excluirAluno(codAluno) {
        _codAlunoExcluir = codAluno;
        $('#modalExcluirAluno').modal('show');
    }

    function confirmarExclusaoAluno() {
        if (!_codAlunoExcluir) return;
        $('#modalExcluirAluno').modal('hide');
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codAluno': _codAlunoExcluir, 'c': 'GerenciarAluno', 'm': 'excluirAluno', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaAlunos();
                }
            },
            complete: function() {
                stopLoading();
                _codAlunoExcluir = null;
            }
        });
    }

    function limparCamposAluno() {
        $('#codAluno').val('');
        $('#codigoExibicao').val('');
        $('#nomeCompleto').val('');
        $('#dataNascimento').val('');
        $('#codColegio').val('');
        $('#nivel').val('');
        $('#serieTurma').find('option:not(:first)').remove();
        $('#turno').val('');
        $('#professoraRegente').val('');
        $('#novoCodAluno').val(0);
        $('#modalAlunoLabel').text('Novo Aluno');
    }

    function abrirModalNovoAluno() {
        limparCamposAluno();
        $('#modalAlunoMsg').html('');
        $('#modalAluno').modal('show');
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
            <h4>Gerenciar Alunos</h4>
        </div>
        <div class="panel-body">

            <div style="margin-bottom:12px;">
                <button class="btn btn-success btn-xs" onclick="abrirModalNovoAluno()">
                    <i class="fa fa-plus"></i> Novo Aluno
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
                <table id="tabelaAlunos" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="sortable">C&oacute;digo</th>
                            <th class="sortable">Nome</th>
                            <th class="sortable">Col&eacute;gio</th>
                            <th class="sortable">S&eacute;rie/Turma</th>
                            <th class="sortable">Turno</th>
                            <th class="sortable">Agente (PES)</th>
                            <th class="text-center sortable">Registros</th>
                            <th class="text-center sortable">Documentos</th>
                            <th>A&ccedil;&otilde;es</th>
                        </tr>
                    </thead>
                    <tbody class="inputs">
                        <?php $objSRV->carregarTabelaAlunos($fws_, $debug); ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusao Aluno -->
<div class="modal fade" id="modalExcluirAluno" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este aluno?</p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusaoAluno()"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo/Editar Aluno -->
<div class="modal fade" id="modalAluno" tabindex="-1" role="dialog" aria-labelledby="modalAlunoLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formAluno" name="formAluno" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalAlunoLabel">Novo Aluno</h4>
                </div>
                <div class="modal-body">

                    <div id="modalAlunoMsg"></div>

                    <div class="row">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="nomeCompleto">Nome Completo: <span class="text-danger">*</span></label>
                                <input type="text" id="nomeCompleto" name="nomeCompleto" class="form-control" maxlength="200" required>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="dataNascimento">Data de Nascimento: <span class="text-danger">*</span></label>
                                <input type="date" id="dataNascimento" name="dataNascimento" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="codColegio">Col&eacute;gio:</label>
                                <select id="codColegio" name="codColegio" class="form-control">
                                    <option value="">Selecione...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="codigoExibicao">C&oacute;digo de Exibi&ccedil;&atilde;o:</label>
                                <input type="text" id="codigoExibicao" name="codigoExibicao" class="form-control" maxlength="50" placeholder="Gerado automaticamente (ex: ALU-001)">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="nivel">N&iacute;vel: <span class="text-danger">*</span></label>
                                <select id="nivel" name="nivel" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="Infantil">Infantil</option>
                                    <option value="Fundamental I">Fundamental I</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="serieTurma">S&eacute;rie/Turma: <span class="text-danger">*</span></label>
                                <select id="serieTurma" name="serieTurma" class="form-control" required>
                                    <option value="">Selecione o n&iacute;vel primeiro...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="turno">Turno: <span class="text-danger">*</span></label>
                                <select id="turno" name="turno" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="Matutino">Matutino</option>
                                    <option value="Vespertino">Vespertino</option>
                                    <option value="Integral">Integral</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="professoraRegente">Professora Regente:</label>
                        <input type="text" id="professoraRegente" name="professoraRegente" class="form-control" maxlength="150">
                    </div>

                    <input type="hidden" name="codAluno" id="codAluno" value="">
                    <input type="hidden" name="novoCodAluno" id="novoCodAluno" value="0">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarAluno" name="c">
                    <input type="hidden" value="salvarAluno" name="m">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarAluno">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
