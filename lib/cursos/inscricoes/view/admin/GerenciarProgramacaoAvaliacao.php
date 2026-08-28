<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarProgramacaoAvaliacaoAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        carregarTabelaProgramacoes();

        $('#btnSalvarProg').click(function() {
            salvarProgramacao();
        });
        $('#btnLimparProg').click(function() {
            limparCamposProg();
        });
    });

    function carregarTabelaProgramacoes() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarProgramacaoAvaliacao', 'm': 'carregarTabelaProgramacoes', '_submit_check': '1'},
            function(html) {
                $("table#tabelaProgramacoes tbody.inputs tr").remove();
                $("table#tabelaProgramacoes tbody.inputs").append(html);

                $('.editar-prog').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    editarProgramacao(id);
                });
                $('.excluir-prog').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    excluirProgramacao(id);
                });
            }
        );
    }

    function salvarProgramacao() {
        $('#modalProgMsg').html('');
        $('#formProgramacao').ajaxSubmit({
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            timeout: 4000,
            error: function(ajax) {
                $('#modalProgMsg').html('<div class="alert alert-danger">' + (ajax.responseText || 'Erro ao processar.') + '</div>');
            },
            success: function(data) {
                if (data.statusMessage) {
                    carregarTabelaProgramacoes();
                    limparCamposProg();
                    $('#modalProgramacao').modal('hide');
                    processJson(data);
                } else {
                    $('#modalProgMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function editarProgramacao(codProgramacao) {
        if (!codProgramacao) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codProgramacao': codProgramacao, 'c': 'GerenciarProgramacaoAvaliacao', 'm': 'editarProgramacao', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                $('#modalProgMsg').html('');
                $('#progCodProgramacao').val(data.codProgramacao);
                $('#progDescricao').val(data.descricao);
                $('#progTipoAvaliacao').val(data.tipoAvaliacao);
                $('#progDataLiberacao').val(data.dataLiberacao);
                $('#progDataFechamento').val(data.dataFechamento);
                $('#progNovoCodProgramacao').val(1);
                $('#modalProgLabel').text('Editar Per\u00edodo');
                $('#modalProgramacao').modal('show');
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    var _codProgramacaoExcluir = null;

    function excluirProgramacao(codProgramacao) {
        _codProgramacaoExcluir = codProgramacao;
        $('#modalExcluirProg').modal('show');
    }

    function confirmarExclusaoProgramacao() {
        if (!_codProgramacaoExcluir) return;
        $('#modalExcluirProg').modal('hide');
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codProgramacao': _codProgramacaoExcluir, 'c': 'GerenciarProgramacaoAvaliacao', 'm': 'excluirProgramacao', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaProgramacoes();
                }
            },
            complete: function() {
                stopLoading();
                _codProgramacaoExcluir = null;
            }
        });
    }

    function limparCamposProg() {
        $('#progCodProgramacao').val('');
        $('#progDescricao').val('');
        $('#progTipoAvaliacao').val('');
        $('#progDataLiberacao').val('');
        $('#progDataFechamento').val('');
        $('#progNovoCodProgramacao').val(0);
        $('#modalProgLabel').text('Novo Per\u00edodo');
    }

    function abrirModalNovaProg() {
        limparCamposProg();
        $('#modalProgMsg').html('');
        $('#modalProgramacao').modal('show');
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
            <h4><i class="fa fa-calendar"></i> Calend&aacute;rio de Avalia&ccedil;&atilde;o</h4>
        </div>
        <div class="panel-body">

            <div style="margin-bottom:12px;">
                <button class="btn btn-success btn-xs" onclick="abrirModalNovaProg()">
                    <i class="fa fa-plus"></i> Novo Per&iacute;odo
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
                <table id="tabelaProgramacoes" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Descri&ccedil;&atilde;o</th>
                            <th>Tipo</th>
                            <th>Libera&ccedil;&atilde;o</th>
                            <th>Fechamento</th>
                            <th>Situa&ccedil;&atilde;o</th>
                            <th>A&ccedil;&otilde;es</th>
                        </tr>
                    </thead>
                    <tbody class="inputs">
                        <?php $objSRV->carregarTabelaProgramacoes($fws_, $debug); ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusao Programacao -->
<div class="modal fade" id="modalExcluirProg" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este per&iacute;odo?</p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusaoProgramacao()"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova/Editar Programacao -->
<div class="modal fade" id="modalProgramacao" tabindex="-1" role="dialog" aria-labelledby="modalProgLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formProgramacao" name="formProgramacao" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalProgLabel">Novo Per&iacute;odo</h4>
                </div>
                <div class="modal-body">

                    <div id="modalProgMsg"></div>

                    <div class="form-group">
                        <label for="progDescricao">Descri&ccedil;&atilde;o: <span class="text-danger">*</span></label>
                        <input type="text" id="progDescricao" name="descricao" class="form-control" maxlength="200" required
                               placeholder="Ex: Avaliacao Inicial - 1o Semestre 2026">
                    </div>

                    <div class="form-group">
                        <label for="progTipoAvaliacao">Tipo de Avalia&ccedil;&atilde;o:</label>
                        <select id="progTipoAvaliacao" name="tipoAvaliacao" class="form-control">
                            <option value="">Selecione...</option>
                            <option value="Inicial">Inicial</option>
                            <option value="Acompanhamento">Acompanhamento</option>
                            <option value="Final">Final</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="progDataLiberacao">Data de Libera&ccedil;&atilde;o: <span class="text-danger">*</span></label>
                                <input type="date" id="progDataLiberacao" name="dataLiberacao" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="progDataFechamento">Data de Fechamento: <span class="text-danger">*</span></label>
                                <input type="date" id="progDataFechamento" name="dataFechamento" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="codProgramacao" id="progCodProgramacao" value="">
                    <input type="hidden" name="novoCodProgramacao" id="progNovoCodProgramacao" value="0">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarProgramacaoAvaliacao" name="c">
                    <input type="hidden" value="salvarProgramacao" name="m">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarProg">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
