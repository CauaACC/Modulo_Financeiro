<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$_eadExigir = 'COORDENADOR';   // montar conteudo
require_once(dirname(__FILE__)."/../../view/admin/curso-ead-seguranca.php");

$objSRV = new GerenciarCursoItemAC();
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<style>
    .tbl-fin { width:100%; border-collapse:collapse; font-size:13px; }
    .tbl-fin thead th { font-size:11px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.3px; padding:12px 10px; border-bottom:1px solid #e0e0e0; background:#f5f6f8; white-space:nowrap; user-select:none; }
    .tbl-fin tbody tr { border-bottom:1px solid #f0f0f0; transition:background 0.15s; }
    .tbl-fin tbody tr:hover { background:#f9fafb; }
    .tbl-fin tbody td { padding:8px 10px; vertical-align:middle; color:#333; }
    .tbl-fin tbody td.col-nome { font-weight:600; }
    .tbl-fin .badge-tipo { display:inline-flex; align-items:center; font-size:12px; font-weight:500; padding:3px 10px; border-radius:12px; background:#f0f1f3; }
    .tbl-fin .badge-tipo .dot { width:8px; height:8px; border-radius:50%; margin-right:6px; display:inline-block; flex-shrink:0; }
    .tbl-fin .badge-tipo .dot-green  { background:#27ae60; }
    .tbl-fin .badge-tipo .dot-blue   { background:#2980b9; }
    .tbl-fin .badge-tipo .dot-orange { background:#f39c12; }
    .tbl-fin .badge-tipo .dot-red    { background:#e74c3c; }
    .tbl-fin .badge-tipo .dot-purple { background:#8e44ad; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; transition:all 0.2s; font-size:13px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; border-color:#ccc; }
    .btn-acao.btn-acao-excluir:hover { background:#fef2f2; color:#c0392b; border-color:#f5c6cb; }
    .form-check { display:inline-flex; align-items:center; gap:6px; font-weight:500; }
</style>
<script type="text/javascript">
    var _codCursoItemExcluir = null;
    var _codCursoItemInativar = null;

    $(document).ready(function() {
        bindEventosTabela();
        $('#btnSalvarCursoItem').click(function() { salvarCursoItem(); });
        $('#tipoHV').change(function () {
            toggleCamposPorTipo();
            ajustarFlagsPorTipo();
        });
        $('#cursoHV').change(function() {
            atualizarOpcoesPaiPreReq();
        });
    });

    function bindEventosTabela() {
        $('#tabelaCursoItem .editar').off('click').on('click', function() {
            editarCursoItem($(this).parents('tr').data('cod-curso-item'));
        });
        $('#tabelaCursoItem .excluir').off('click').on('click', function() {
            var $tr = $(this).parents('tr');
            _codCursoItemExcluir = $tr.data('cod-curso-item');
            $('#nomeItemExcluir').text($tr.data('titulo') || 'este item');
            $('#modalExclusaoCursoItem').modal('show');
        });
        $('#tabelaCursoItem .inativar').off('click').on('click', function() {
            var $tr = $(this).parents('tr');
            _codCursoItemInativar = $tr.data('cod-curso-item');
            $('#nomeItemInativar').text($tr.data('titulo') || 'este item');
            var bloqueios = $tr.data('bloqueios');
            $('#avisoDependentesInativar').html(bloqueios ? 'Ele tem ' + bloqueios + '. Nada disso é apagado.' : '');
            $('#modalInativarCursoItem').modal('show');
        });
        $('#tabelaCursoItem .reativar').off('click').on('click', function() {
            acaoStatusCursoItem($(this).parents('tr').data('cod-curso-item'), 'reativarCursoItem');
        });
    }

    function toggleCamposPorTipo() {
        var t = $('#tipoHV').val();
        var precisaURL = (t === 'VIDEO' || t === 'PDF' || t === 'MEET' || t === 'SLIDES' || t === 'LINK' || t === 'FORMULARIO');
        $('#campoUrlHV').toggle(precisaURL);

        var ehCapitulo = (t === 'CAPITULO');
        $('#blocoConfirmacao').toggle(!ehCapitulo);
    }

    function ajustarFlagsPorTipo() {
        var t = $('#tipoHV').val();
        var presenca = (t === 'VIDEO' || t === 'MEET') ? 1 : 0;
        var coment = (t === 'LINK' || t === 'FORMULARIO' || t === 'CAPITULO') ? 0 : 1;
        $('#controlePresencaHV').prop('checked', presenca === 1);
        $('#permiteComentariosHV').prop('checked', coment === 1);
    }

    function carregarSelectCursos(selecionado, cb) {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCursoItem', 'm': 'carregarOpcoesCurso', '_submit_check': '1'},
            function(opts) {
                var $sel = $('#cursoHV').empty().append('<option value="">Selecione o curso...</option>');
                $.each(opts, function(i, o) {
                    $sel.append('<option value="' + o.cod + '">' + o.titulo + '</option>');
                });
                if (selecionado) $sel.val(selecionado);
                if (cb) cb();
            }, 'json');
    }

    function atualizarOpcoesPaiPreReq(paiSelecionado, preReqSelecionado) {
        var codCurso = $('#cursoHV').val();
        var excluir = $('#codCursoItem').val();
        var data = {
            c: 'GerenciarCursoItem', m: 'carregarOpcoesPai', _submit_check: '1',
            codCurso: codCurso || 0, excluirCodItem: excluir || 0
        };
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), data, function(opts) {
            var $sel = $('#paiHV').empty().append('<option value="">— Item raiz —</option>');
            $.each(opts, function(i, o) {
                $sel.append('<option value="' + o.cod_curso_item + '">' + o.titulo + '</option>');
            });
            if (paiSelecionado) $sel.val(paiSelecionado);
        }, 'json');

        var data2 = {
            c: 'GerenciarCursoItem', m: 'carregarOpcoesPreRequisito', _submit_check: '1',
            codCurso: codCurso || 0, excluirCodItem: excluir || 0
        };
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), data2, function(opts) {
            var $sel = $('#preReqHV').empty().append('<option value="">— Sem pré-requisito —</option>');
            $.each(opts, function(i, o) {
                $sel.append('<option value="' + o.cod_curso_item + '">' + o.titulo + ' (' + o.tipo + ')</option>');
            });
            if (preReqSelecionado) $sel.val(preReqSelecionado);
        }, 'json');
    }

    function abrirModalNovoCursoItem() {
        limparCamposCursoItem();
        $('#modalCursoItemLabel').html('<i class="fa fa-plus" style="margin-right:8px; opacity:0.8;"></i>Novo Item');
        carregarSelectCursos(null, function() { atualizarOpcoesPaiPreReq(); });
        $('#modalCursoItem').modal('show');
        toggleCamposPorTipo();
        ajustarFlagsPorTipo();
    }

    function carregarTabelaCursoItem() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCursoItem', 'm': 'carregarTabelaCursoItem', '_submit_check': '1'},
            function(html) {
                $('#tabelaCursoItem tbody').html(html);
                bindEventosTabela();
            }
        );
    }

    function salvarCursoItem() {
        // Garantir que os checkboxes vão como 0/1
        $('#hidControlePresenca').val($('#controlePresencaHV').is(':checked') ? 1 : 0);
        $('#hidPermiteComentarios').val($('#permiteComentariosHV').is(':checked') ? 1 : 0);

        $('#formCursoItem').ajaxSubmit({
            target: '#modalCursoItemMsg',
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaCursoItem();
                    $('#modalCursoItem').modal('hide');
                    limparCamposCursoItem();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function editarCursoItem(codCursoItem) {
        if (!codCursoItem) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codCursoItem': codCursoItem, 'c': 'GerenciarCursoItem', 'm': 'editarCursoItem', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                $('#codCursoItem').val(data.codCursoItem);
                $('#tipoHV').val(data.tipo);
                $('#tituloHV').val(data.titulo);
                $('#descricaoHV').val(data.descricao);
                $('#urlHV').val(data.url);
                $('#dtLiberacaoHV').val(data.dtLiberacao);
                $('#horaInicioHV').val(data.horaInicio ? data.horaInicio.substring(0,5) : '');
                $('#horaFimHV').val(data.horaFim ? data.horaFim.substring(0,5) : '');
                $('#ordemHV').val(data.ordem);
                $('#intervaloMinHV').val(data.intervaloConfirmacaoMin);
                $('#intervaloMaxHV').val(data.intervaloConfirmacaoMax);
                $('#timeoutSegHV').val(data.timeoutConfirmacaoSeg);
                $('#statusHV').val(data.status);
                $('#controlePresencaHV').prop('checked', data.controlePresenca == 1);
                $('#permiteComentariosHV').prop('checked', data.permiteComentarios == 1);
                $('#modalCursoItemLabel').html('<i class="fa fa-pencil" style="margin-right:8px; opacity:0.8;"></i>Editar Item');
                carregarSelectCursos(data.codCurso, function() {
                    atualizarOpcoesPaiPreReq(data.codPai, data.codPreRequisito);
                });
                $('#modalCursoItem').modal('show');
                toggleCamposPorTipo();
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarExclusaoCursoItem() {
        if (!_codCursoItemExcluir) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codCursoItem': _codCursoItemExcluir, 'c': 'GerenciarCursoItem', 'm': 'excluirCursoItem', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) carregarTabelaCursoItem();
                $('#modalExclusaoCursoItem').modal('hide');
                _codCursoItemExcluir = null;
            },
            complete: function() { stopLoading(); }
        });
    }

    // Inativar / reativar: mesma rota, mesmo envelope JSON da exclusao.
    function acaoStatusCursoItem(codCursoItem, metodo) {
        if (!codCursoItem) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codCursoItem': codCursoItem, 'c': 'GerenciarCursoItem', 'm': metodo, '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) carregarTabelaCursoItem();
                $('#modalInativarCursoItem').modal('hide');
                _codCursoItemInativar = null;
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarInativacaoCursoItem() {
        acaoStatusCursoItem(_codCursoItemInativar, 'inativarCursoItem');
    }

    function limparCamposCursoItem() {
        $('#codCursoItem').val('');
        $('#cursoHV').val('');
        $('#paiHV').empty().append('<option value="">— Item raiz —</option>');
        $('#preReqHV').empty().append('<option value="">— Sem pré-requisito —</option>');
        $('#tipoHV').val('VIDEO');
        $('#tituloHV').val('');
        $('#descricaoHV').val('');
        $('#urlHV').val('');
        $('#dtLiberacaoHV').val('');
        $('#horaInicioHV').val('');
        $('#horaFimHV').val('');
        $('#ordemHV').val('0');
        $('#intervaloMinHV').val('5');
        $('#intervaloMaxHV').val('5');
        $('#timeoutSegHV').val('60');
        $('#statusHV').val('ATIVADA');
        $('#controlePresencaHV').prop('checked', true);
        $('#permiteComentariosHV').prop('checked', true);
        $('#modalCursoItemMsg').html('');
    }
</script>
<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Itens dos Cursos</h4>
        <button type="button" class="btn btn-sm abriModal" onclick="abrirModalNovoCursoItem()">
            <i class="fa fa-plus"></i> Novo Item
        </button>
    </div>
    <div style="padding:0;">
        <table class="tbl-fin" id="tabelaCursoItem">
            <thead>
                <tr>
                    <th style="width:80px;">Ações</th>
                    <th style="width:60px;">Ordem</th>
                    <th style="width:110px;">Tipo</th>
                    <th>Título / Hierarquia</th>
                    <th>Janela (data / hora)</th>
                    <th style="width:90px;">Flags</th>
                    <th style="width:90px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $objSRV->carregarTabelaCursoItem($fws_, $debug); ?>
            </tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:150px;"></div>

<!-- Modal Cadastro/Edição -->
<div class="modal fade" id="modalCursoItem" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalCursoItemLabel"><i class="fa fa-plus"></i>Novo Item</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalCursoItemMsg"></div>
                <form id="formCursoItem" name="formCursoItem" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="cursoHV"><strong>Curso:</strong></label>
                                <select id="cursoHV" name="codCurso" class="form-control" required>
                                    <option value="">Selecione o curso...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="paiHV">Pai (capítulo onde fica):</label>
                                <select id="paiHV" name="codPai" class="form-control">
                                    <option value="">— Item raiz —</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="preReqHV">Pré-requisito:</label>
                                <select id="preReqHV" name="codPreRequisito" class="form-control">
                                    <option value="">— Sem pré-requisito —</option>
                                </select>
                                <small style="color:#888;">Item que precisa estar concluído antes de liberar este.</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="tipoHV">Tipo:</label>
                                <select id="tipoHV" name="tipo" class="form-control" required>
                                    <option value="CAPITULO">📁 Capítulo (container)</option>
                                    <option value="VIDEO">🎬 Vídeo</option>
                                    <option value="PDF">📄 PDF</option>
                                    <option value="MEET">🎥 Reunião</option>
                                    <option value="SLIDES">📊 Slides</option>
                                    <option value="TEXTO">📝 Texto</option>
                                    <option value="TAREFA">✅ Tarefa</option>
                                    <option value="LINK">🔗 Link</option>
                                    <option value="FORMULARIO">📋 Formulário</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <label for="tituloHV">Título:</label>
                                <input type="text" id="tituloHV" name="titulo" class="form-control" maxlength="255" required>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="ordemHV">Ordem:</label>
                                <input type="number" id="ordemHV" name="ordem" class="form-control" value="0" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descricaoHV">Descrição (opcional):</label>
                        <textarea id="descricaoHV" name="descricao" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group" id="campoUrlHV">
                        <label for="urlHV">URL (Vimeo, YouTube, PDF, link Meet, etc.):</label>
                        <input type="url" id="urlHV" name="url" class="form-control" maxlength="500" placeholder="https://...">
                        <small style="color:#888;">Vimeo e YouTube são detectados automaticamente para o player com controle de presença.</small>
                    </div>

                    <hr>
                    <h5 style="margin-bottom:10px;">Janela de acesso</h5>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="dtLiberacaoHV">Data de liberação (≥):</label>
                                <input type="date" id="dtLiberacaoHV" name="dtLiberacao" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="horaInicioHV">Hora início:</label>
                                <input type="time" id="horaInicioHV" name="horaInicio" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="horaFimHV">Hora fim:</label>
                                <input type="time" id="horaFimHV" name="horaFim" class="form-control">
                            </div>
                        </div>
                    </div>
                    <small style="color:#888;">Se não informar, o item herda a janela do curso.</small>

                    <hr>
                    <h5 style="margin-bottom:10px;">Comportamento</h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="form-check">
                                <input type="checkbox" id="controlePresencaHV" checked>
                                Controle de presença DETRAN (ping + modal "você está aí?")
                            </label>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-check">
                                <input type="checkbox" id="permiteComentariosHV" checked>
                                Permite comentários do aluno
                            </label>
                        </div>
                    </div>
                    <input type="hidden" id="hidControlePresenca" name="controlePresenca" value="1">
                    <input type="hidden" id="hidPermiteComentarios" name="permiteComentarios" value="1">

                    <div id="blocoConfirmacao" style="margin-top:14px;">
                        <h5 style="margin-bottom:10px;">Tempos do controle DETRAN</h5>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="intervaloMinHV">Intervalo mín. (min):</label>
                                    <input type="number" id="intervaloMinHV" name="intervaloConfirmacaoMin" class="form-control" value="5" min="1">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="intervaloMaxHV">Intervalo máx. (min):</label>
                                    <input type="number" id="intervaloMaxHV" name="intervaloConfirmacaoMax" class="form-control" value="5" min="1">
                                    <small style="color:#888;">Igual ao mín = fixo.</small>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="timeoutSegHV">Timeout (s):</label>
                                    <input type="number" id="timeoutSegHV" name="timeoutConfirmacaoSeg" class="form-control" value="60" min="10">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="form-group">
                        <label for="statusHV">Status:</label>
                        <select id="statusHV" name="status" class="form-control">
                            <option value="ATIVADA">Ativo</option>
                            <option value="DESATIVADA">Inativo</option>
                        </select>
                    </div>

                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarCursoItem" name="c">
                    <input type="hidden" value="salvarCursoItem" name="m">
                    <input type="hidden" name="codCursoItem" id="codCursoItem" value=""/>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarCursoItem">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exclusão -->
<div class="modal fade" id="modalExclusaoCursoItem" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:450px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclusão</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Excluir <strong id="nomeItemExcluir">este item</strong> definitivamente?</p>
                <p>Uma cópia do item é guardada para auditoria, mas <strong>subitens, presenças, conclusões,
                tarefas, comentários e mensagens não são recuperados</strong>.</p>
                <p style="color:#666;">Item que já tem histórico não pode ser excluído — nesse caso use
                <strong>Inativar</strong>, que tira do ar e preserva tudo.</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusaoCursoItem()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Inativação -->
<div class="modal fade" id="modalInativarCursoItem" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:450px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-power-off" style="margin-right:8px; opacity:.8;"></i>Inativar item</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Inativar <strong id="nomeItemInativar">este item</strong>?</p>
                <p>Ele deixa de aparecer para os alunos e <strong>todo o histórico é preservado</strong>.
                Você pode reativá-lo depois.</p>
                <p id="avisoDependentesInativar" style="color:#666;"></p>
                <p style="color:#666;">Se outros itens dependiam dele como pré-requisito, eles são liberados —
                senão ficariam esperando a conclusão de um item que não aparece mais.</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarInativacaoCursoItem()">Inativar</button>
            </div>
        </div>
    </div>
</div>
