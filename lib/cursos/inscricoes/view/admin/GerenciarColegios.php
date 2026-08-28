<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
$objSRV = new GerenciarColegioAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        carregarTabelaColegios();

        $('#btnSalvarColegio').click(function() {
            salvarColegio();
        });
        $('#btnLimparColegio').click(function() {
            limparCamposColegio();
        });
    });

    function carregarTabelaColegios() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarColegio', 'm': 'carregarTabelaColegios', '_submit_check': '1'},
            function(html) {
                $("table#tabelaColegios tbody.inputs tr").remove();
                $("table#tabelaColegios tbody.inputs").append(html);

                $('.editar').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    editarColegio(id);
                });
                $('.excluir').click(function() {
                    var id = $(this).parents("tr").attr("id");
                    excluirColegio(id);
                });
            }
        );
    }

    function salvarColegio() {
        $('#modalColegioMsg').html('');
        $('#formColegio').ajaxSubmit({
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            timeout: 4000,
            error: function(ajax) {
                $('#modalColegioMsg').html('<div class="alert alert-danger">' + (ajax.responseText || 'Erro ao processar.') + '</div>');
            },
            success: function(data) {
                if (data.statusMessage) {
                    carregarTabelaColegios();
                    limparCamposColegio();
                    $('#modalColegio').modal('hide');
                    processJson(data);
                } else {
                    $('#modalColegioMsg').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function editarColegio(codColegio) {
        if (!codColegio) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codColegio': codColegio, 'c': 'GerenciarColegio', 'm': 'editarColegio', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                $('#modalColegioMsg').html('');
                $('#codColegio').val(data.codColegio);
                $('#nomeColegio').val(data.nome);
                $('#necessidadeManha').val(data.necessidadeManha || 0);
                $('#necessidadeTarde').val(data.necessidadeTarde || 0);
                $('#alocacaoManha').val(data.alocacaoManha || 0);
                $('#alocacaoTarde').val(data.alocacaoTarde || 0);
                $('#novoCodColegio').val(1);
                $('#modalColegioLabel').text('Editar Col\u00e9gio');
                $('#modalColegio').modal('show');
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    var _codColegioExcluir = null;

    function excluirColegio(codColegio) {
        _codColegioExcluir = codColegio;
        $('#modalExcluirColegio').modal('show');
    }

    function confirmarExclusaoColegio() {
        if (!_codColegioExcluir) return;
        $('#modalExcluirColegio').modal('hide');
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codColegio': _codColegioExcluir, 'c': 'GerenciarColegio', 'm': 'excluirColegio', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaColegios();
                }
            },
            complete: function() {
                stopLoading();
                _codColegioExcluir = null;
            }
        });
    }

    function limparCamposColegio() {
        $('#codColegio').val('');
        $('#nomeColegio').val('');
        $('#necessidadeManha').val('');
        $('#necessidadeTarde').val('');
        $('#alocacaoManha').val('');
        $('#alocacaoTarde').val('');
        $('#novoCodColegio').val(0);
        $('#modalColegioLabel').text('Novo Col\u00e9gio');
    }

    function abrirModalNovoColegio() {
        limparCamposColegio();
        $('#modalColegioMsg').html('');
        $('#modalColegio').modal('show');
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
            <h4><i class="fa fa-institution"></i> Unidades Escolares</h4>
        </div>
        <div class="panel-body">

            <div style="margin-bottom:12px;">
                <button class="btn btn-success btn-xs" onclick="abrirModalNovoColegio()">
                    <i class="fa fa-plus"></i> Novo Col&eacute;gio
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
                <table id="tabelaColegios" class="table table-bordered table-striped table-hover" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="vertical-align:middle;">Unidade Escolar</th>
                            <th colspan="2" class="text-center" style="background-color:#d4edda;">Necessidade PES</th>
                            <th colspan="2" class="text-center" style="background-color:#cce5ff;">Aloca&ccedil;&atilde;o PES</th>
                            <th rowspan="2" style="vertical-align:middle;">A&ccedil;&otilde;es</th>
                        </tr>
                        <tr>
                            <th class="text-center" style="background-color:#d4edda;">Manh&atilde;</th>
                            <th class="text-center" style="background-color:#d4edda;">Tarde</th>
                            <th class="text-center" style="background-color:#cce5ff;">Manh&atilde;</th>
                            <th class="text-center" style="background-color:#cce5ff;">Tarde</th>
                        </tr>
                    </thead>
                    <tbody class="inputs">
                        <?php $objSRV->carregarTabelaColegios($fws_, $debug); ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusao Colegio -->
<div class="modal fade" id="modalExcluirColegio" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este col&eacute;gio?</p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusaoColegio()"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo/Editar Colegio -->
<div class="modal fade" id="modalColegio" tabindex="-1" role="dialog" aria-labelledby="modalColegioLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formColegio" name="formColegio" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalColegioLabel">Novo Col&eacute;gio</h4>
                </div>
                <div class="modal-body">
                    <div id="modalColegioMsg"></div>

                    <div class="form-group">
                        <label for="nomeColegio">Nome da Unidade Escolar: <span class="text-danger">*</span></label>
                        <input type="text" id="nomeColegio" name="nome" class="form-control" required maxlength="150" placeholder="Ex: E.M. Nome do Col&eacute;gio">
                    </div>

                    <hr style="margin:15px 0;">
                    <h5 style="color:#555;"><i class="fa fa-users"></i> Necessidade de PES</h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="necessidadeManha">Manh&atilde;:</label>
                                <input type="number" id="necessidadeManha" name="necessidadeManha" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="necessidadeTarde">Tarde:</label>
                                <input type="number" id="necessidadeTarde" name="necessidadeTarde" class="form-control" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <h5 style="color:#555;"><i class="fa fa-check-circle"></i> Aloca&ccedil;&atilde;o de PES</h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="alocacaoManha">Manh&atilde;:</label>
                                <input type="number" id="alocacaoManha" name="alocacaoManha" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="alocacaoTarde">Tarde:</label>
                                <input type="number" id="alocacaoTarde" name="alocacaoTarde" class="form-control" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="codColegio" id="codColegio" value="">
                    <input type="hidden" name="novoCodColegio" id="novoCodColegio" value="0">
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarColegio" name="c">
                    <input type="hidden" value="salvarColegio" name="m">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarColegio">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
