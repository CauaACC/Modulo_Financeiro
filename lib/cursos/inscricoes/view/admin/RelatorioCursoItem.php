<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$_eadExigir = 'PROFESSOR';   // acompanhar: relatorios, folha de ponto, mensagens
require_once(dirname(__FILE__)."/../../view/admin/curso-ead-seguranca.php");

$objSRV = new GerenciarCursoItemAC();
?>
<script type="text/javascript" src="resources/js/default.js"></script>
<style>
    .tbl-fin { width:100%; border-collapse:collapse; font-size:13px; }
    .tbl-fin thead th { font-size:11px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.3px; padding:12px 10px; border-bottom:1px solid #e0e0e0; background:#f5f6f8; white-space:nowrap; }
    .tbl-fin tbody tr { border-bottom:1px solid #f0f0f0; transition:background 0.15s; }
    .tbl-fin tbody tr:hover { background:#f9fafb; }
    .tbl-fin tbody td { padding:8px 10px; vertical-align:middle; color:#333; }
    .tbl-fin tbody td.col-nome { font-weight:600; }
    .tbl-fin .badge-tipo { display:inline-block; font-size:11px; font-weight:600; text-transform:uppercase; color:#6c757d; background:#f0f1f3; padding:2px 8px; border-radius:10px; }
    .nav-tabs-rel { display:flex; gap:2px; border-bottom:2px solid #e0e0e0; margin-bottom:0; padding:0 16px; }
    .nav-tabs-rel a { padding:10px 18px; color:#666; text-decoration:none; font-weight:500; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer; }
    .nav-tabs-rel a.active { color:#2d6a4f; border-bottom-color:#2d6a4f; font-weight:600; }
    .nav-tabs-rel a:hover { color:#2d6a4f; }
    .tab-content > div { display:none; }
    .tab-content > div.active { display:block; }
    .progress-bar-mini { display:inline-block; vertical-align:middle; width:120px; height:8px; background:#e9ecef; border-radius:4px; overflow:hidden; }
    .progress-bar-mini > div { height:100%; background:linear-gradient(90deg,#2d6a4f,#4caf50); }
    /* Aba Mensagens (inbox do professor) */
    #tab-msg .msg-wrap { display:grid; grid-template-columns:280px 1fr; min-height:420px; }
    #tab-msg .msg-left { border-right:1px solid #eee; overflow:auto; max-height:520px; }
    #tab-msg .msg-aluno { width:100%; text-align:left; border:0; background:transparent; border-bottom:1px solid #f2f2f2; padding:10px 14px; cursor:pointer; display:flex; flex-direction:column; gap:2px; }
    #tab-msg .msg-aluno:hover { background:#f7f9f8; }
    #tab-msg .msg-aluno.sel { background:#eef6f1; box-shadow:inset 3px 0 0 #2d6a4f; }
    #tab-msg .msg-aluno .nm { font-weight:600; font-size:13px; color:#1f2937; }
    #tab-msg .msg-aluno .mt { font-size:11px; color:#888; }
    #tab-msg .msg-badge { display:inline-block; min-width:16px; padding:0 5px; height:16px; line-height:16px; text-align:center; font-size:10px; font-weight:700; background:#e8842a; color:#fff; border-radius:9px; margin-left:4px; }
    #tab-msg .msg-right { padding:14px 16px; display:flex; flex-direction:column; }
    #tab-msg .mhead { font-weight:700; font-size:14px; margin-bottom:10px; color:#1f2937; }
    #tab-msg .mthread { display:flex; flex-direction:column; gap:8px; overflow-y:auto; max-height:400px; padding:4px; }
    #tab-msg .mbub { max-width:80%; padding:9px 12px; border-radius:12px; font-size:13px; line-height:1.4; white-space:pre-wrap; word-break:break-word; }
    #tab-msg .mbub .who { font-size:11px; font-weight:700; margin-bottom:3px; opacity:.8; }
    #tab-msg .mbub .when { font-size:10px; margin-top:5px; opacity:.7; }
    #tab-msg .mbub.them { align-self:flex-start; background:#f1f3f6; color:#1f2937; border-bottom-left-radius:4px; }
    #tab-msg .mbub.me { align-self:flex-end; background:#2d6a4f; color:#fff; border-bottom-right-radius:4px; }
    #tab-msg .mcomposer { display:flex; gap:8px; margin-top:12px; }
    #tab-msg .mcomposer input { flex:1; padding:9px 12px; border:1px solid #e0e0e0; border-radius:8px; font-size:13px; }
    #tab-msg .msg-hint { color:#999; font-size:13px; text-align:center; padding:30px 10px; }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        $('.nav-tabs-rel a').on('click', function() {
            var tab = $(this).data('tab');
            $('.nav-tabs-rel a').removeClass('active');
            $(this).addClass('active');
            $('.tab-content > div').removeClass('active');
            $('#tab-' + tab).addClass('active');
            // Lazy load
            if (tab === 'aluno' && !$('#tabelaAluno').data('loaded')) carregarPainelAluno();
            if (tab === 'ponto' && !$('#tabelaPonto').data('loaded')) carregarFolhaPonto();
        });

        bindBotoesItem();
        bindAlunoLinks();
    });

    function bindBotoesItem() {
        $('.verDetalhes').off('click').on('click', function() {
            var cod = $(this).data('cod');
            var titulo = $(this).data('titulo');
            abrirDetalhesItem(cod, titulo);
        });
    }

    function bindAlunoLinks() {
        $('.abrirDetalheAluno').off('click').on('click', function() {
            var cod = $(this).data('cod');
            var nome = $(this).data('nome');
            abrirDetalhesAluno(cod, nome);
        });
    }

    function abrirDetalhesItem(codCursoItem, titulo) {
        $('#modalDetalhesLabel').html('<i class="fa fa-users"></i> ' + titulo);
        $('#tabelaDetalhes tbody').html('<tr><td colspan="8" style="text-align:center; padding:20px;">Carregando...</td></tr>');
        $('#modalDetalhes').modal('show');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCursoItem', 'm': 'carregarRelatorioInscritosPorItem', 'codCursoItem': codCursoItem, '_submit_check': '1'},
            function(html) { $('#tabelaDetalhes tbody').html(html); }
        );
    }

    function abrirDetalhesAluno(codInscrito, nome) {
        $('#modalAlunoLabel').html('<i class="fa fa-user"></i> ' + nome);
        $('#modalAlunoBody').html('<div style="padding:20px;">Carregando...</div>');
        $('#modalAluno').modal('show');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCursoItem', 'm': 'carregarDetalheAluno', 'codInscrito': codInscrito, '_submit_check': '1'},
            function(data) {
                if (!data || !data.statusMessage) { $('#modalAlunoBody').html('Erro ao carregar.'); return; }
                var html = '<table class="tbl-fin"><thead><tr><th>Curso</th><th>Progresso</th><th>Tempo efetivo</th><th>Última atividade</th></tr></thead><tbody>';
                $.each(data.progresso, function(i, c) {
                    var t = parseInt(c.total_folhas) || 0;
                    var f = parseInt(c.folhas_concluidas) || 0;
                    var pct = t > 0 ? Math.round(100 * f / t) : 0;
                    var seg = parseInt(c.tempo_efetivo_seg) || 0;
                    var hh = Math.floor(seg/3600), mm = Math.floor((seg%3600)/60), ss = seg%60;
                    var tFmt = (hh<10?'0':'') + hh + ':' + (mm<10?'0':'') + mm + ':' + (ss<10?'0':'') + ss;
                    html += '<tr><td><strong>' + $('<div>').text(c.titulo).html() + '</strong></td>';
                    html += '<td><div class="progress-bar-mini"><div style="width:' + pct + '%"></div></div> ' + pct + '% (' + f + '/' + t + ')</td>';
                    html += '<td style="font-family:monospace;">' + tFmt + '</td>';
                    html += '<td><small>' + (c.ultima_atividade || '—') + '</small></td></tr>';
                });
                html += '</tbody></table><h5 style="margin:18px 0 8px;">Itens detalhados</h5>';
                html += '<table class="tbl-fin"><thead><tr><th>Curso</th><th>Item</th><th>Tipo</th><th>Status</th><th>Tempo</th><th>Última visita</th><th>Timeline</th></tr></thead><tbody>';
                $.each(data.itens, function(i, it) {
                    var seg = parseInt(it.tempo_seg) || 0;
                    var hh = Math.floor(seg/3600), mm = Math.floor((seg%3600)/60), ss = seg%60;
                    var tFmt = (hh<10?'0':'') + hh + ':' + (mm<10?'0':'') + mm + ':' + (ss<10?'0':'') + ss;
                    var status = parseInt(it.concluido) === 1 ? '<span style="color:#1f7a1f; font-weight:600;">✓ concluído</span>' : '<span style="color:#999;">pendente</span>';
                    html += '<tr><td><small>' + $('<div>').text(it.curso_titulo || '').html() + '</small></td>';
                    html += '<td>' + $('<div>').text(it.titulo).html() + '</td>';
                    html += '<td><span class="badge-tipo">' + it.tipo + '</span></td>';
                    html += '<td>' + status + '</td>';
                    html += '<td style="font-family:monospace;">' + tFmt + '</td>';
                    html += '<td><small>' + (it.ultima || '—') + '</small></td>';
                    html += '<td><button type="button" class="btn btn-xs btn-default" onclick="abrirTimeline(' + codInscrito + ',' + it.cod_curso_item + ',\'' + $('<div>').text(it.titulo).html().replace(/\'/g, "\\'") + '\')"><i class="fa fa-history"></i></button></td></tr>';
                });
                html += '</tbody></table>';
                $('#modalAlunoBody').html(html);
            }, 'json');
    }

    function abrirTimeline(codInscrito, codCursoItem, titulo) {
        $('#modalTimelineLabel').html('<i class="fa fa-history"></i> Timeline — ' + titulo);
        $('#modalTimelineBody').html('Carregando...');
        $('#modalTimeline').modal('show');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCursoItem', 'm': 'carregarTimeline', 'codInscrito': codInscrito, 'codCursoItem': codCursoItem, '_submit_check': '1'},
            function(data) {
                if (!data || !data.eventos || data.eventos.length === 0) {
                    $('#modalTimelineBody').html('<div style="padding:20px; color:#999;">Nenhum evento registrado.</div>');
                    return;
                }
                var html = '<table class="tbl-fin"><thead><tr><th>#</th><th>Hora</th><th>Tipo</th><th>Tempo acumulado</th><th>Sessão</th></tr></thead><tbody>';
                var corPorTipo = {'INICIO':'#2980b9','PING':'#999','CONFIRMACAO_OK':'#27ae60','CONFIRMACAO_EXPIRADA':'#e74c3c','PAUSA':'#f39c12','RETOMADA':'#2980b9','FIM':'#666','FORA_HORARIO':'#c0392b'};
                $.each(data.eventos, function(i, e) {
                    var seg = parseInt(e.tempo_acumulado) || 0;
                    var hh = Math.floor(seg/3600), mm = Math.floor((seg%3600)/60), ss = seg%60;
                    var tFmt = (hh<10?'0':'') + hh + ':' + (mm<10?'0':'') + mm + ':' + (ss<10?'0':'') + ss;
                    var cor = corPorTipo[e.tipo] || '#666';
                    html += '<tr><td>' + (i+1) + '</td><td><small>' + e.data_hora + '</small></td><td><span class="badge-tipo" style="color:' + cor + ';">' + e.tipo + '</span></td><td style="font-family:monospace;">' + tFmt + '</td><td><small style="color:#aaa;">' + (e.cod_sessao || '—').substring(0,8) + '...</small></td></tr>';
                });
                html += '</tbody></table>';
                $('#modalTimelineBody').html(html);
            }, 'json');
    }

    function carregarPainelAluno() {
        var codCurso = $('#filtroCurso').val();
        var diasAtraso = $('#diasAtraso').val() || 7;
        $('#tabelaAluno tbody').html('<tr><td colspan="6" style="text-align:center; padding:20px;">Carregando...</td></tr>');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCursoItem', 'm': 'carregarPainelAlunos', 'codCurso': codCurso, 'diasAtraso': diasAtraso, '_submit_check': '1'},
            function(html) {
                $('#tabelaAluno tbody').html(html);
                $('#tabelaAluno').data('loaded', true);
                bindAlunoLinks();
            }
        );
    }

    function carregarFolhaPonto() {
        $('#tabelaPonto tbody').html('<tr><td colspan="6" style="text-align:center; padding:20px;">Carregando...</td></tr>');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCursoItem', 'm': 'carregarFolhaPonto', '_submit_check': '1'},
            function(html) {
                $('#tabelaPonto tbody').html(html);
                $('#tabelaPonto').data('loaded', true);
            }
        );
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
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Relatórios do Hall Virtual</h4>
        <div>
            <a class="btn btn-sm btn-default" href="<?php echo WB_URL_COMMAND ?>&c=GerenciarCursoItem&m=exportarFolhaPontoCsv" target="_blank"><i class="fa fa-download"></i> Exportar Folha de Ponto (CSV)</a>
        </div>
    </div>
    <div class="nav-tabs-rel">
        <a data-tab="item" class="active">Por Item</a>
        <a data-tab="aluno">Por Aluno (Painel do Coordenador)</a>
        <a data-tab="ponto">Folha de Ponto</a>
        <a data-tab="regponto">Registro de Ponto</a>
        <a data-tab="msg">Mensagens</a>
    </div>
    <div class="tab-content">
        <!-- Por Item -->
        <div id="tab-item" class="active">
            <table class="tbl-fin" id="tabelaResumo">
                <thead>
                    <tr>
                        <th style="width:80px;">Tipo</th>
                        <th>Item</th>
                        <th style="width:90px; text-align:center;">Inscritos</th>
                        <th style="width:80px; text-align:center;">OK</th>
                        <th style="width:90px; text-align:center;">Expiradas</th>
                        <th style="width:110px; text-align:center;">Fora horário</th>
                        <th style="width:120px; text-align:center;">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $objSRV->carregarRelatorioGeral($fws_, $debug); ?>
                </tbody>
            </table>
        </div>

        <!-- Por Aluno -->
        <div id="tab-aluno">
            <div style="padding:10px 16px; display:flex; gap:12px; align-items:end; background:#fafbfc; border-bottom:1px solid #f0f0f0;">
                <div>
                    <label style="font-size:11px; color:#888; display:block;">Curso (opcional)</label>
                    <select id="filtroCurso" class="form-control input-sm" style="width:240px;" onchange="carregarPainelAluno()">
                        <option value="">— Todos os cursos —</option>
                        <?php
                        $cursos = new Curso();
                        $cursos->carregarLista(false, $debug);
                        foreach (($cursos->getListaCurso() ?: array()) as $c) {
                            echo '<option value="' . (int) $c->getCodCurso() . '">' . htmlspecialchars($c->getTitulo()) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px; color:#888; display:block;">Atrasado se sem atividade há (dias)</label>
                    <input type="number" id="diasAtraso" value="7" min="1" class="form-control input-sm" style="width:100px;" onchange="carregarPainelAluno()">
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-default" onclick="carregarPainelAluno()"><i class="fa fa-refresh"></i> Atualizar</button>
                </div>
            </div>
            <table class="tbl-fin" id="tabelaAluno">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th style="width:80px; text-align:center;">Cursos</th>
                        <th style="width:100px; text-align:center;">Concluídos</th>
                        <th style="width:120px; text-align:center;">Tempo total</th>
                        <th>Última atividade</th>
                        <th style="width:120px; text-align:center;"></th>
                    </tr>
                </thead>
                <tbody><tr><td colspan="6" style="padding:20px; text-align:center; color:#999;">Clique na aba "Por Aluno" pra carregar.</td></tr></tbody>
            </table>
        </div>

        <!-- Folha de Ponto -->
        <div id="tab-ponto">
            <div style="padding:10px 16px; background:#fafbfc; border-bottom:1px solid #f0f0f0; font-size:12px; color:#666;">
                <i class="fa fa-info-circle"></i> Tempo efetivo só conta PINGs e confirmações respondidas <strong>dentro do horário do curso</strong>. Eventos <code>FORA_HORARIO</code> não somam tempo.
            </div>
            <table class="tbl-fin" id="tabelaPonto">
                <thead>
                    <tr>
                        <th></th>
                        <th>Dia</th>
                        <th style="width:90px; text-align:center;">Sessões</th>
                        <th style="width:130px; text-align:center;">Tempo efetivo</th>
                        <th>Primeira entrada</th>
                        <th>Última saída</th>
                    </tr>
                </thead>
                <tbody><tr><td colspan="6" style="padding:20px; text-align:center; color:#999;">Clique na aba "Folha de Ponto" pra carregar.</td></tr></tbody>
            </table>
        </div>

        <!-- Mensagens (inbox do professor por tarefa) -->
        <div id="tab-msg">
            <div style="padding:10px 16px; background:#fafbfc; border-bottom:1px solid #f0f0f0; display:flex; gap:10px; align-items:end;">
                <div>
                    <label style="font-size:11px; color:#888; display:block;">Tarefa</label>
                    <select id="msgTarefaSel" class="form-control input-sm" style="width:380px;" onchange="carregarAlunosMsg()">
                        <option value="">— Selecione a tarefa —</option>
                        <?php
                        $ciMsg = new CursoItem();
                        $itensMsg = $ciMsg->carregarArvore(null, $debug);
                        $mapaCursosMsg = array();
                        $clMsg = new Curso();
                        $clMsg->carregarLista(false, $debug);
                        foreach (($clMsg->getListaCurso() ?: array()) as $cMsg) {
                            $mapaCursosMsg[(int) $cMsg->getCodCurso()] = $cMsg->getTitulo();
                        }
                        foreach ($itensMsg as $itMsg) {
                            if ($itMsg['tipo'] !== 'TAREFA' || $itMsg['status'] !== 'ATIVADA') continue;
                            $cursoNome = isset($mapaCursosMsg[(int) $itMsg['cod_curso']]) ? $mapaCursosMsg[(int) $itMsg['cod_curso']] . ' › ' : '';
                            echo '<option value="' . (int) $itMsg['cod_curso_item'] . '">' . htmlspecialchars($cursoNome . $itMsg['titulo']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="msg-wrap">
                <div class="msg-left" id="msgAlunos"><div class="msg-hint">Escolha uma tarefa acima.</div></div>
                <div class="msg-right" id="msgThreadPane"><div class="msg-hint">Selecione um aluno à esquerda.</div></div>
            </div>
        </div>

        <!-- Registro de Ponto (folha de ponto detalhada do aluno) -->
        <div id="tab-regponto">
            <div style="padding:10px 16px; background:#fafbfc; border-bottom:1px solid #f0f0f0; display:flex; gap:10px; align-items:end;">
                <div>
                    <label style="font-size:11px; color:#888; display:block;">Aluno</label>
                    <select id="regPontoAluno" class="form-control input-sm" style="width:380px;" onchange="carregarFolhaPonto2()">
                        <option value="">— Selecione o aluno —</option>
                        <?php
                        $daoPontoRel = new PontoSessaoDAO();
                        foreach ($daoPontoRel->alunosComPonto(COD_EVENTO, $debug) as $al) {
                            $nomeAl = $al['nome_inscrito'] ? $al['nome_inscrito'] : ('#' . $al['cod_inscrito']);
                            echo '<option value="' . (int) $al['cod_inscrito'] . '">' . htmlspecialchars($nomeAl . ' — ' . (int) $al['dias'] . ' dia(s)') . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div id="regPontoBody"><div style="padding:24px; text-align:center; color:#999;">Selecione um aluno acima.</div></div>
        </div>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:120px;"></div>

<!-- Modal Detalhes por item (existente) -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document" style="max-width:1100px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalDetalhesLabel"><i class="fa fa-users"></i></h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="padding:0;">
                <table class="tbl-fin" id="tabelaDetalhes">
                    <thead>
                        <tr>
                            <th>Inscrito</th>
                            <th style="width:80px; text-align:center;">Sessões</th>
                            <th style="width:80px; text-align:center;">OK</th>
                            <th style="width:90px; text-align:center;">Expiradas</th>
                            <th style="width:110px; text-align:center;">Fora horário</th>
                            <th style="width:110px; text-align:center;">Tempo total</th>
                            <th>Primeira visita</th>
                            <th>Última visita</th>
                        </tr>
                    </thead>
                    <tbody><tr><td colspan="8" style="text-align:center; padding:20px;">Carregando...</td></tr></tbody>
                </table>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhe do Aluno (drill-down) -->
<div class="modal fade" id="modalAluno" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document" style="max-width:1100px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalAlunoLabel"><i class="fa fa-user"></i></h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="modalAlunoBody" style="padding:14px 20px;">Carregando...</div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Timeline -->
<div class="modal fade" id="modalTimeline" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document" style="max-width:850px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title" id="modalTimelineLabel"><i class="fa fa-history"></i></h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="modalTimelineBody" style="padding:14px 20px;">Carregando...</div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    var msgSel = { cod: null, codInscrito: null, nome: '' };
    function escHtml(s) { return $('<div>').text(s == null ? '' : s).html(); }

    window.carregarAlunosMsg = function() {
        var cod = $('#msgTarefaSel').val();
        $('#msgThreadPane').html('<div class="msg-hint">Selecione um aluno à esquerda.</div>');
        if (!cod) { $('#msgAlunos').html('<div class="msg-hint">Escolha uma tarefa acima.</div>'); return; }
        msgSel.cod = cod;
        $('#msgAlunos').html('<div class="msg-hint">Carregando…</div>');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            { c: 'GerenciarCursoTarefaMensagem', m: 'listarAlunosComThread', codCursoItem: cod, _submit_check: '1' },
            function(data) {
                if (!data || !data.statusMessage) { $('#msgAlunos').html('<div class="msg-hint">' + escHtml((data && data.message) || 'Erro.') + '</div>'); return; }
                var al = data.alunos || [];
                if (!al.length) { $('#msgAlunos').html('<div class="msg-hint">Nenhuma conversa nesta tarefa ainda.</div>'); return; }
                $('#msgAlunos').html(al.map(function(a) {
                    var nl = parseInt(a.nao_lidas || 0, 10);
                    var badge = nl > 0 ? ' <span class="msg-badge">' + nl + '</span>' : '';
                    var nome = a.nome_inscrito || ('#' + a.cod_inscrito);
                    return '<button type="button" class="msg-aluno" data-cod="' + a.cod_inscrito + '" data-nome="' + escHtml(nome).replace(/"/g, '&quot;') + '">'
                        + '<span class="nm">' + escHtml(nome) + '</span>'
                        + '<span class="mt">' + a.total + ' msg' + (a.total > 1 ? 's' : '') + badge + '</span></button>';
                }).join(''));
            }, 'json');
    };

    function abrirThreadProf(codInscrito, nome) {
        msgSel.codInscrito = codInscrito; msgSel.nome = nome;
        $('#msgThreadPane').html('<div class="msg-hint">Carregando…</div>');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            { c: 'GerenciarCursoTarefaMensagem', m: 'listarThread', codCursoItem: msgSel.cod, codInscrito: codInscrito, _submit_check: '1' },
            function(data) {
                if (!data || !data.statusMessage) { $('#msgThreadPane').html('<div class="msg-hint">' + escHtml((data && data.message) || 'Erro.') + '</div>'); return; }
                var msgs = data.mensagens || [];
                var thread = msgs.length ? msgs.map(function(m) {
                    var prof = (m.autor_tipo === 'PROFESSOR');
                    var quem = prof ? 'Você (prof.)' : (m.autor_aluno || nome);
                    return '<div class="mbub ' + (prof ? 'me' : 'them') + '"><div class="who">' + escHtml(quem) + '</div>' + escHtml(m.texto) + '<div class="when">' + escHtml(m.dat_incl || '') + '</div></div>';
                }).join('') : '<div class="msg-hint">Sem mensagens. Responda abaixo.</div>';
                $('#msgThreadPane').html('<div class="mhead">' + escHtml(nome) + '</div><div class="mthread">' + thread + '</div>'
                    + '<div class="mcomposer"><input type="text" id="msgProfInput" placeholder="Responder ao aluno…" maxlength="2000"><button type="button" class="btn btn-sm" style="background:#2d6a4f; color:#fff;" onclick="enviarRespostaProf()">Enviar</button></div>');
                var t = document.querySelector('#msgThreadPane .mthread'); if (t) t.scrollTop = t.scrollHeight;
                var b = $('.msg-aluno.sel .msg-badge'); if (b.length) b.remove();
            }, 'json');
    }

    window.enviarRespostaProf = function() {
        var inp = document.getElementById('msgProfInput'); if (!inp) return;
        var txt = (inp.value || '').trim(); if (!txt) return;
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            { c: 'GerenciarCursoTarefaMensagem', m: 'enviar', codCursoItem: msgSel.cod, codInscrito: msgSel.codInscrito, texto: txt, _submit_check: '1' },
            function(data) {
                if (data && data.statusMessage) { abrirThreadProf(msgSel.codInscrito, msgSel.nome); }
                else alert((data && data.message) || 'Erro ao enviar.');
            }, 'json');
    };

    $(function() {
        $(document).on('click', '.msg-aluno', function() {
            $('.msg-aluno').removeClass('sel'); $(this).addClass('sel');
            abrirThreadProf($(this).data('cod'), $(this).data('nome'));
        });
    });
})();
</script>

<script type="text/javascript">
    window.carregarFolhaPonto2 = function() {
        var cod = document.getElementById('regPontoAluno').value;
        var box = document.getElementById('regPontoBody');
        if (!cod) { box.innerHTML = '<div style="padding:24px; text-align:center; color:#999;">Selecione um aluno acima.</div>'; return; }
        box.innerHTML = '<div style="padding:24px; text-align:center; color:#999;">Carregando…</div>';
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            { c: 'GerenciarCursoPonto', m: 'carregarFolha', codInscrito: cod, _submit_check: '1' },
            function(html) { box.innerHTML = html; }, 'html');
    };
</script>
