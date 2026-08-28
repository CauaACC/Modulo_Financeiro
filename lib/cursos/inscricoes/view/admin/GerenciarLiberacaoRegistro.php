<?php
require_once(dirname(__FILE__) . "/../../view/admin/seguranca-adm-geral.php");
$objSRV = new GerenciarLiberacaoRegistroAC();
$objSRVBloqueio = new GerenciarBloqueioAvaliacaoAC();
?>
<link href="resources/css/select2.min.css" rel="stylesheet" />
<script src="resources/js/select2/select2.min.js"></script>
<script src="resources/js/select2/pt-BR.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

        // === Aba Liberação de Registro ===
        carregarTabelaLiberacoes();

        // Select2 AJAX para busca de inscrito por nome
        if ($.fn.select2) {
            $('#codInscrito').select2({
                placeholder: 'Digite para buscar um inscrito',
                allowClear: true,
                minimumInputLength: 3,
                language: {
                    noResults: function() { return 'Nenhum inscrito encontrado'; },
                    inputTooShort: function(args) { return 'Digite ' + (args.minimum - args.input.length) + ' ou mais caracteres'; },
                    searching: function() { return 'Buscando\u2026'; }
                },
                ajax: {
                    url: '<?php echo WB_URL_COMMAND ?>',
                    type: 'POST',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            'termo': params.term,
                            'c': 'GerenciarLiberacaoRegistro',
                            'm': 'buscarInscritos',
                            '_submit_check': '1'
                        };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    }
                },
                width: '100%'
            });
        }

        $('#listaLiberacoes').on('click', '.editar', function(event) {
            event.preventDefault();
            var id = $(this).closest('tr').data('id');
            editarLiberacao(id);
        });
        $('#listaLiberacoes').on('click', '.excluir', function(event) {
            event.preventDefault();
            var id = $(this).closest('tr').data('id');
            excluirLiberacao(id);
        });

        $('#formLiberacao').submit(function(event) {
            event.preventDefault();
            salvarLiberacao();
        });

        $('#btnLimpar').click(function() {
            limparFormulario();
        });

        // === Aba Bloqueio de Avaliação ===
        carregarPainelBloqueio();

        $('#painelBloqueio').on('click', '.btn-toggle-bloqueio', function() {
            var btn = $(this);
            var mes = btn.data('mes');
            var ano = btn.data('ano');
            var statusAtual = btn.data('status');
            var novoStatus = (statusAtual === 'BLOQUEADO') ? 'LIBERADO' : 'BLOQUEADO';
            var acao = (novoStatus === 'BLOQUEADO') ? 'bloquear' : 'liberar';
            var meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                         'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

            $('#modalConfirmaBloqueio .modal-title').text('Confirmar ' + (novoStatus === 'BLOQUEADO' ? 'Bloqueio' : 'Liberação'));
            $('#modalConfirmaBloqueioMsg').html(
                'Deseja <strong>' + acao + '</strong> a avaliação de registros de atividade do mês <strong>' + meses[mes] + '/' + ano + '</strong>?'
            );
            $('#modalConfirmaBloqueio').data('mes', mes).data('ano', ano).data('novoStatus', novoStatus);
            $('#bloqueioObservacao').val('');
            $('#modalConfirmaBloqueio').modal('show');
        });

        $('#btnConfirmarBloqueio').click(function() {
            var modal = $('#modalConfirmaBloqueio');
            var mes = modal.data('mes');
            var ano = modal.data('ano');
            var novoStatus = modal.data('novoStatus');
            var obs = $('#bloqueioObservacao').val();

            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: {
                    'mes': mes,
                    'ano': ano,
                    'novoStatus': novoStatus,
                    'observacao': obs,
                    'c': 'GerenciarBloqueioAvaliacao',
                    'm': 'alternarStatus',
                    '_submit_check': '1'
                },
                dataType: "json",
                success: function(data) {
                    modal.modal('hide');
                    processJson(data);
                    if (data.statusMessage == '1') {
                        carregarPainelBloqueio();
                    }
                }
            });
        });
    });

    // === Funções Liberação de Registro ===
    function carregarTabelaLiberacoes() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarLiberacaoRegistro',
            'm': 'carregarTabela',
            '_submit_check': '1'
        }, function(html) {
            $('#listaLiberacoes').html(html);
        });
    }

    function salvarLiberacao() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codLiberacao': $('#codLiberacao').val(),
                'codInscrito': $('#codInscrito').val(),
                'dataInicio': $('#dataInicio').val(),
                'dataFim': $('#dataFim').val(),
                'observacao': $('#observacao').val(),
                'updateLiberacao': $('#updateLiberacao').val(),
                'c': 'GerenciarLiberacaoRegistro',
                'm': 'salvar',
                '_submit_check': '1'
            },
            dataType: "json",
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1') {
                    limparFormulario();
                    carregarTabelaLiberacoes();
                }
            }
        });
    }

    function editarLiberacao(codLiberacao) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codLiberacao': codLiberacao,
                'c': 'GerenciarLiberacaoRegistro',
                'm': 'editar',
                '_submit_check': '1'
            },
            dataType: "json",
            success: function(data) {
                if (data.statusMessage == 1) {
                    $('#codLiberacao').val(data.codLiberacao);
                    // Setar o Select2 com o inscrito carregado
                    var option = new Option(data.nomeInscrito + ' (#' + data.codInscrito + ')', data.codInscrito, true, true);
                    $('#codInscrito').append(option).trigger('change');
                    $('#dataInicio').val(data.dataInicio);
                    $('#dataFim').val(data.dataFim);
                    $('#observacao').val(data.observacao);
                    $('#updateLiberacao').val('1');
                    $('#btnSalvar').text('Atualizar');
                }
            }
        });
    }

    function excluirLiberacao(codLiberacao) {
        if (confirm('Deseja realmente excluir esta liberação?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: {
                    'codLiberacao': codLiberacao,
                    'c': 'GerenciarLiberacaoRegistro',
                    'm': 'excluir',
                    '_submit_check': '1'
                },
                dataType: "json",
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage == '1') {
                        carregarTabelaLiberacoes();
                    }
                }
            });
        }
    }

    function limparFormulario() {
        $('#codLiberacao').val('');
        $('#codInscrito').val(null).trigger('change');
        $('#dataInicio').val('');
        $('#dataFim').val('');
        $('#observacao').val('');
        $('#updateLiberacao').val('0');
        $('#btnSalvar').text('Salvar');
    }

    // === Funções Bloqueio de Avaliação ===
    function carregarPainelBloqueio() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarBloqueioAvaliacao',
            'm': 'carregarPainel',
            '_submit_check': '1'
        }, function(html) {
            $('#painelBloqueio').html(html);
        });
    }
</script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_avaliacao.php");
?>
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Controles de Registro de Atividade</h4>
    </div>
    <div style="padding:15px 20px;">

            <ul class="nav nav-tabs" style="margin-bottom:15px;">
                <li role="presentation" class="active">
                    <a data-toggle="tab" href="#abaLiberacao"><i class="fa fa-calendar-check-o"></i> Libera&ccedil;&atilde;o de Registro</a>
                </li>
                <li role="presentation">
                    <a data-toggle="tab" href="#abaBloqueio"><i class="fa fa-lock"></i> Bloqueio de Avalia&ccedil;&atilde;o</a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Aba 1: Liberação de Registro -->
                <div id="abaLiberacao" class="tab-pane fade in active">
                    <p class="text-muted small">Libere o cadastro de registro de atividade fora do per&iacute;odo padr&atilde;o para inscritos espec&iacute;ficos.</p>
                    <form id="formLiberacao" name="formLiberacao">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="codInscrito">Inscrito:</label>
                                    <select class="form-control" id="codInscrito" name="codInscrito" required>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="dataInicio">Data In&iacute;cio:</label>
                                    <input type="date" class="form-control" id="dataInicio" name="dataInicio" required>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="dataFim">Data Fim:</label>
                                    <input type="date" class="form-control" id="dataFim" name="dataFim" required>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="observacao">Observa&ccedil;&atilde;o:</label>
                                    <input type="text" class="form-control" id="observacao" name="observacao">
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top:10px;">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-sm" id="btnSalvar" style="border-radius:5px; padding:7px 18px; font-size:13px; background:#2d6a4f; color:#fff; border:none;">Salvar</button>
                                <button type="button" class="btn btn-sm" id="btnLimpar" style="border-radius:5px; padding:7px 18px; font-size:13px; background:#f0f0f0; color:#555; border:1px solid #ddd;">Limpar</button>
                            </div>
                        </div>
                        <input type="hidden" id="codLiberacao" name="codLiberacao" value="">
                        <input type="hidden" id="updateLiberacao" name="updateLiberacao" value="0">
                    </form>
                    <hr>
                    <div id="listaLiberacoes">
                        <?php $objSRV->carregarTabela($fws_, $debug); ?>
                    </div>
                </div>

                <!-- Aba 2: Bloqueio de Avaliação -->
                <div id="abaBloqueio" class="tab-pane fade">
                    <p class="text-muted small">Controle quais meses est&atilde;o dispon&iacute;veis para avalia&ccedil;&atilde;o de registros de atividade pelos avaliadores dos n&uacute;cleos.</p>
                    <div id="painelBloqueio">
                        <?php $objSRVBloqueio->carregarPainel($fws_, $debug); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal de Confirmação de Bloqueio/Liberação -->
<div class="modal fade" id="modalConfirmaBloqueio" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirmar</h4>
            </div>
            <div class="modal-body">
                <p id="modalConfirmaBloqueioMsg"></p>
                <div class="form-group">
                    <label for="bloqueioObservacao">Observa&ccedil;&atilde;o (opcional):</label>
                    <input type="text" class="form-control" id="bloqueioObservacao" placeholder="Motivo do bloqueio/libera&ccedil;&atilde;o">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarBloqueio">Confirmar</button>
            </div>
        </div>
    </div>
</div>
