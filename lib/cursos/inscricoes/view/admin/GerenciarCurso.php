<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$_eadExigir = 'COORDENADOR';   // montar conteudo
require_once(dirname(__FILE__)."/../../view/admin/curso-ead-seguranca.php");

$objSRV = new GerenciarCursoAC();
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
    .tbl-fin .badge-tipo .dot-green { background:#27ae60; }
    .tbl-fin .badge-tipo .dot-red   { background:#e74c3c; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; transition:all 0.2s; font-size:13px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; border-color:#ccc; }
    .btn-acao.btn-acao-excluir:hover { background:#fef2f2; color:#c0392b; border-color:#f5c6cb; }
    .conteudo-wrap { display:flex; flex-wrap:wrap; gap:6px; align-items:center; }
    .conteudo-resumo { font-size:12px; color:#555; white-space:nowrap; }
    .conteudo-resumo strong { color:#333; }
    .badge-conteudo { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; color:#4A6785; background:#eef2f7; border:1px solid #dbe3ee; padding:2px 7px; border-radius:10px; white-space:nowrap; }
    .badge-conteudo i { font-size:11px; opacity:0.8; }
</style>
<script type="text/javascript">
    var _codCursoExcluir = null;

    $(document).ready(function() {
        bindEventosTabela();
        $('#btnSalvarCurso').click(function() { salvarCurso(); });
    });

    function bindEventosTabela() {
        $('#tabelaCurso .editar').off('click').on('click', function() {
            editarCurso($(this).parents('tr').attr('id'));
        });
        $('#tabelaCurso .excluir').off('click').on('click', function() {
            _codCursoExcluir = $(this).parents('tr').attr('id');
            $('#modalExclusaoCurso').modal('show');
        });
    }

    function abrirModalNovoCurso() {
        limparCamposCurso();
        $('#modalCursoLabel').html('<i class="fa fa-plus" style="margin-right:8px; opacity:0.8;"></i>Novo Curso');
        $('#modalCurso').modal('show');
    }

    function carregarTabelaCurso() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCurso', 'm': 'carregarTabelaCurso', '_submit_check': '1'},
            function(html) {
                $('#tabelaCurso tbody').html(html);
                bindEventosTabela();
            }
        );
    }

    function salvarCurso() {
        $('#formCurso').ajaxSubmit({
            target: '#modalCursoMsg',
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaCurso();
                    $('#modalCurso').modal('hide');
                    limparCamposCurso();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function editarCurso(codCurso) {
        if (!codCurso) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codCurso': codCurso, 'c': 'GerenciarCurso', 'm': 'editarCurso', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                $('#codCurso').val(data.codCurso);
                $('#tituloCurso').val(data.titulo);
                $('#descricaoCurso').val(data.descricao);
                $('#ementaCurso').val(data.ementa);
                $('#cargaHorariaCurso').val(data.cargaHoraria);
                $('#capaUrlCurso').val(data.capaUrl);
                $('#dtLiberacaoCurso').val(data.dtLiberacao);
                $('#dtEncerramentoCurso').val(data.dtEncerramento);
                $('#horaInicioCurso').val(data.horaInicio ? data.horaInicio.substring(0,5) : '');
                $('#horaFimCurso').val(data.horaFim ? data.horaFim.substring(0,5) : '');
                $('#ordemCurso').val(data.ordem);
                $('#statusCurso').val(data.status);
                $('#codPerfilCurso').val(data.codPerfil || '');
                $('#exibirParaCurso').val(data.exibirPara || 'TODOS');
                $('#filtroStatusCurso').val(data.filtroStatus || '');
                $('#filtroStatusProcessoCurso').val(data.filtroStatusProcesso || '');
                $('#codOficinasCurso').val((data.codOficinas || []).map(String));
                $('#modalCursoLabel').html('<i class="fa fa-pencil" style="margin-right:8px; opacity:0.8;"></i>Editar Curso');
                $('#modalCurso').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarExclusaoCurso() {
        if (!_codCursoExcluir) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codCurso': _codCursoExcluir, 'c': 'GerenciarCurso', 'm': 'excluirCurso', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) carregarTabelaCurso();
                $('#modalExclusaoCurso').modal('hide');
                _codCursoExcluir = null;
            },
            complete: function() { stopLoading(); }
        });
    }

    function limparCamposCurso() {
        $('#codCurso').val('');
        $('#tituloCurso').val('');
        $('#descricaoCurso').val('');
        $('#ementaCurso').val('');
        $('#cargaHorariaCurso').val('');
        $('#capaUrlCurso').val('');
        $('#dtLiberacaoCurso').val('');
        $('#dtEncerramentoCurso').val('');
        $('#horaInicioCurso').val('');
        $('#horaFimCurso').val('');
        $('#ordemCurso').val('0');
        $('#statusCurso').val('ATIVADA');
        $('#codPerfilCurso').val('');
        $('#exibirParaCurso').val('TODOS');
        $('#filtroStatusCurso').val('');
        $('#filtroStatusProcessoCurso').val('');
        $('#codOficinasCurso').val([]);
        $('#modalCursoMsg').html('');
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
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Cursos do Hall Virtual</h4>
        <button type="button" class="btn btn-sm abriModal" onclick="abrirModalNovoCurso()">
            <i class="fa fa-plus"></i> Novo Curso
        </button>
    </div>
    <div style="padding:0;">
        <table class="tbl-fin" id="tabelaCurso">
            <thead>
                <tr>
                    <th style="width:80px;">Ações</th>
                    <th style="width:60px;">Ordem</th>
                    <th>Título</th>
                    <th style="width:260px;">Conteúdo</th>
                    <th style="width:130px;">Janela diária</th>
                    <th style="width:200px;">Datas</th>
                    <th style="width:110px;">Carga horária</th>
                    <th style="width:90px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $objSRV->carregarTabelaCurso($fws_, $debug); ?>
            </tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:120px;"></div>

<!-- Modal Cadastro/Edição -->
<div class="modal fade" id="modalCurso" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalCursoLabel"><i class="fa fa-plus"></i>Novo Curso</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalCursoMsg"></div>
                <form id="formCurso" name="formCurso" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="row">
                        <div class="col-sm-9">
                            <div class="form-group">
                                <label for="tituloCurso">Título do curso:</label>
                                <input type="text" id="tituloCurso" name="titulo" class="form-control" maxlength="255" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="ordemCurso">Ordem:</label>
                                <input type="number" id="ordemCurso" name="ordem" class="form-control" value="0" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="descricaoCurso">Descrição curta (opcional):</label>
                        <textarea id="descricaoCurso" name="descricao" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ementaCurso">Ementa (opcional):</label>
                        <textarea id="ementaCurso" name="ementa" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="cargaHorariaCurso">Carga horária:</label>
                                <input type="text" id="cargaHorariaCurso" name="cargaHoraria" class="form-control" placeholder="ex: 40h">
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="capaUrlCurso">URL da capa (opcional):</label>
                                <input type="url" id="capaUrlCurso" name="capaUrl" class="form-control" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h5 style="margin-bottom:10px;">Janela de acesso ao curso</h5>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="dtLiberacaoCurso">Liberação a partir de:</label>
                                <input type="date" id="dtLiberacaoCurso" name="dtLiberacao" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="dtEncerramentoCurso">Encerramento em:</label>
                                <input type="date" id="dtEncerramentoCurso" name="dtEncerramento" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="horaInicioCurso">Hora início (diária):</label>
                                <input type="time" id="horaInicioCurso" name="horaInicio" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="horaFimCurso">Hora fim:</label>
                                <input type="time" id="horaFimCurso" name="horaFim" class="form-control">
                            </div>
                        </div>
                    </div>
                    <small style="color:#888;">Se não informar datas/horas, o curso fica disponível em qualquer momento. Itens dentro do curso herdam essa janela e podem restringir mais.</small>
                    <hr>
                    <h5 style="margin-bottom:10px;">Regras de divulgação <small style="color:#888; font-weight:normal;">(mesmo padrão dos avisos — combinação restringe quem vê)</small></h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="codPerfilCurso">Perfil específico:</label>
                                <select id="codPerfilCurso" name="codPerfil" class="form-control">
                                    <option value="">— Todos os perfis —</option>
                                    <?php CarregarOptionsAC::perfis($fws_, $debug); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="exibirParaCurso">Pagamento:</label>
                                <select id="exibirParaCurso" name="exibirPara" class="form-control">
                                    <option value="TODOS">Todos</option>
                                    <option value="PAGO">Apenas pagos</option>
                                    <option value="NAO_PAGO">Apenas não pagos</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="filtroStatusCurso">Status da inscrição:</label>
                                <select id="filtroStatusCurso" name="filtroStatus" class="form-control">
                                    <option value="">— Qualquer —</option>
                                    <option value="ATIVADA">ATIVADA</option>
                                    <option value="DESATIVADA">DESATIVADA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="filtroStatusProcessoCurso">Status no processo:</label>
                                <select id="filtroStatusProcessoCurso" name="filtroStatusProcesso" class="form-control">
                                    <option value="">— Qualquer —</option>
                                    <option value="INSCRITO">INSCRITO</option>
                                    <option value="PARTICIPANDO">PARTICIPANDO</option>
                                    <option value="DESISTENTE">DESISTENTE</option>
                                    <option value="CANCELADA">CANCELADA</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="codOficinasCurso">Oficinas vinculadas: <small style="color:#888; font-weight:normal;">(se marcar, só quem está inscrito na oficina vê o curso; vazio = usa as regras acima)</small></label>
                                <select id="codOficinasCurso" name="codOficinas[]" class="form-control" multiple size="6">
                                    <?php CarregarOptionsAC::oficinas(array('cod_evento'=>COD_EVENTO, 'cod_oficina'=>''), $debug); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="statusCurso">Status:</label>
                        <select id="statusCurso" name="status" class="form-control">
                            <option value="ATIVADA">Ativo</option>
                            <option value="DESATIVADA">Inativo</option>
                        </select>
                    </div>

                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarCurso" name="c">
                    <input type="hidden" value="salvarCurso" name="m">
                    <input type="hidden" name="codCurso" id="codCurso" value=""/>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarCurso">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exclusão -->
<div class="modal fade" id="modalExclusaoCurso" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:480px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclusão</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este curso?</p>
                <p><strong>Esta ação é irreversível.</strong> O curso só pode ser excluído se não tiver itens cadastrados dentro dele.</p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusaoCurso()">Excluir</button>
            </div>
        </div>
    </div>
</div>
