<?php
require_once(dirname(__FILE__) . "/../../include/config.php");

/* Coordenador do evento OU admin geral — o coordenador monta a propria proposta.
 * O controller repete o bloqueio; aqui e so para redirecionar com mensagem. */
@$cod_msg = false;
if (@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_EVENTO, 5, $cod_msg, false) == false
    and @FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, $cod_msg, false) == false
) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<blockquote><br><br><br>" . $_SESSION['MSG'] . "<br><br><br>";
    echo "<a href='" . $URL . "'>Clique aqui para efetuar login</a><br><br><br>&nbsp;</blockquote>";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}
$objSRV = new GerenciarPropostaAC();
?>
<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<style type="text/css">
    .pp-secao { background:#fff; border:1px solid #e8e8e8; border-radius:12px; margin-top:12px; overflow:hidden; }
    .pp-secao-titulo { padding:10px 20px; border-bottom:1px solid #eee; }
    .pp-secao-titulo h4 { margin:0; font-size:15px; font-weight:600; color:#333; }
    .pp-secao-titulo p { margin:4px 0 0; color:#888; font-size:12px; }
    .pp-herdado { background:#f7f9fa; border:1px solid #eceff1; border-radius:10px; padding:12px 14px; }
    .pp-herdado dt { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#8a959c; font-weight:600; }
    .pp-herdado dd { font-size:13px; margin:0 0 8px; color:#333; }
    .pp-passo { display:none; }
    .pp-passo.ativo { display:block; }

    /* Trilha de etapas: bolinha numerada + rotulo, ligadas por uma linha fina.
       Concluida = verde com check; atual = azul; futura = cinza. */
    .pp-trilha { display:flex; list-style:none; margin:18px 0 6px; padding:0; }
    .pp-etapa { flex:1; position:relative; text-align:center; cursor:pointer; padding-top:26px; }
    .pp-etapa:before { content:''; position:absolute; top:11px; left:0; right:50%; height:2px; background:#e3e8ea; }
    .pp-etapa:after  { content:''; position:absolute; top:11px; left:50%; right:0;  height:2px; background:#e3e8ea; }
    .pp-etapa:first-child:before, .pp-etapa:last-child:after { display:none; }
    .pp-etapa.feita:before, .pp-etapa.feita:after, .pp-etapa.atual:before { background:#2d6a4f; }
    .pp-bola { position:absolute; top:0; left:50%; transform:translateX(-50%); width:24px; height:24px;
               border-radius:50%; background:#fff; border:2px solid #e3e8ea; color:#9aa5ab;
               font-size:12px; font-weight:700; line-height:20px; z-index:1; }
    .pp-etapa.atual .pp-bola { border-color:#2980b9; background:#2980b9; color:#fff; }
    .pp-etapa.feita .pp-bola { border-color:#2d6a4f; background:#2d6a4f; color:#fff; }
    .pp-rotulo { font-size:12px; color:#8a959c; padding:0 4px; }
    .pp-etapa.atual .pp-rotulo { color:#222; font-weight:600; }
    .pp-etapa.feita .pp-rotulo { color:#2d6a4f; }
    @media (max-width: 1200px) { .pp-rotulo { display:none; } .pp-etapa { padding-top:24px; } }
</style>
<script type="text/javascript">
var ppCampos = ['justificativa','objetivo','cronograma','previsao_orcamentaria','comissao_organizadora',
                'partes_envolvidas','apoio','departamentos','declaracao_atuacao',
                'orgao_interveniente',
                'acordo_vigencia_ini','acordo_vigencia_fim','origem_proposta','proposta_de',
                'ambito','modalidade','carga_horaria_distancia','area_tematica_predominante',
                'vinculado_programa','vinculado_projeto_numero','sistema_inscricao',
                'enviar_email_inscricao','recebimento_relatorios'];
var ppMaxOds = 3, ppPasso = 1, ppTotalPassos = 9;
// So gravamos depois que a carga terminou. Sem isto, trocar de etapa
// enquanto o AJAX de carregar ainda esta em voo salvaria os campos
// VAZIOS por cima do que ja estava no banco.
var ppPronto = false;

$(document).ready(function() {
    ppCarregar();
    $('.pp-etapa').click(function() { ppIr(parseInt($(this).data('passo'), 10)); });
});

function ppIr(n) {
    if (n < 1 || n > ppTotalPassos) return;
    // Cada troca de passo salva o que ja foi preenchido — o coordenador nao
    // perde o trabalho ao navegar.
    if (n !== ppPasso) { ppSalvar(true); }
    ppPasso = n;
    $('.pp-passo').removeClass('ativo');
    $('#ppPasso' + n).addClass('ativo');
    $('.pp-etapa').each(function() {
        var i = parseInt($(this).data('passo'), 10);
        $(this).removeClass('atual feita');
        if (i < n) { $(this).addClass('feita'); $(this).find('.pp-bola').html('<i class="fa fa-check"></i>'); }
        else { $(this).find('.pp-bola').text(i); if (i === n) { $(this).addClass('atual'); } }
    });
    ppInitEditores(n);
    $('#ppBtnVoltar').toggle(n > 1);
    $('#ppBtnProximo').toggle(n < ppTotalPassos);
    $('html, body').animate({ scrollTop: 0 }, 150);
}

/* Editor HTML nos campos longos. Init POR ETAPA: TinyMCE iniciado dentro de uma
   div display:none nasce sem altura util, entao so criamos o editor quando a
   etapa aparece. valid_elements '*[*]' preserva o HTML que o coordenador colar
   (tabelas de orcamento, listas da comissao) sem o editor "limpar" nada. */
function ppConfigEditor(altura) {
    return {
        plugins: "code textcolor link table lists",
        width: '100%',
        height: altura,
        resize: "both",
        menubar: false,
        statusbar: true,
        block_formats: 'Par&aacute;grafo=p;T&iacute;tulo 3=h3;T&iacute;tulo 4=h4;T&iacute;tulo 5=h5',
        force_br_newlines: true,
        force_p_newlines: false,
        forced_root_block: '',
        convert_newlines_to_brs: false,
        remove_linebreaks: false,
        valid_elements: '*[*]',
        verify_html: false,
        toolbar: "code | undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | forecolor | link | table",
        textcolor_map: ["FF6600", "Laranja", "005082", "Azul", "FF0000", "Vermelho", "008B45", "Verde"]
    };
}

function ppInitEditores(passo) {
    if (typeof tinymce === 'undefined') return;
    $('#ppPasso' + passo).find('textarea.editor_html, textarea.editor_html_grande').each(function() {
        var el = $(this);
        if (el.data('mce-pronto')) return;
        el.data('mce-pronto', true);
        var cfg = ppConfigEditor(el.hasClass('editor_html_grande') ? 420 : 260);
        cfg.selector = '#' + this.id;
        var conteudo = el.val();
        cfg.init_instance_callback = function(ed) { ed.setContent(conteudo || ''); };
        tinymce.init(cfg);
    });
}

function ppOpcoes(id, lista, valor, comVazio) {
    var el = $('#' + id).empty();
    if (comVazio) { el.append($('<option>').val('').text('— selecione —')); }
    if ($.isArray(lista)) {
        $.each(lista, function(_, v) { el.append($('<option>').val(v).text(v)); });
    } else {
        $.each(lista, function(k, v) { el.append($('<option>').val(k).text(v)); });
    }
    el.val(valor || '');
}

function ppTexto(v) { return (v === null || v === undefined || v === '') ? '—' : v; }

function ppCarregar() {
    $('#ppMsg').html('<i class="fa fa-spinner fa-spin"></i> Carregando...');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarProposta', 'm': 'carregar', '_submit_check': '1'
    }, function(resp) {
        var d; try { d = (typeof resp === 'object') ? resp : JSON.parse(resp); } catch(e) { d = null; }
        if (!d || d.statusMessage !== '1') { ppPronto = false; $('#ppMsg').html('<span style="color:#c0392b;">Erro ao carregar a proposta &mdash; nada ser&aacute; gravado. Recarregue a p&aacute;gina.</span>'); return; }
        ppMaxOds = d.maxOds || 3;

        $.each(ppCampos, function(_, campo) {
            var valor = d.campos[campo] || '';
            $('#pp_' + campo).val(valor);
            var ed = (typeof tinymce !== 'undefined') ? tinymce.get('pp_' + campo) : null;
            if (ed) { ed.setContent(valor); }
        });
        ppOpcoes('pp_proposta_de', d.listaPropostaDe, d.campos.proposta_de, true);
        ppOpcoes('pp_ambito', d.listaAmbito, d.campos.ambito, true);
        ppOpcoes('pp_modalidade', d.listaModalidade, d.campos.modalidade, true);
        ppOpcoes('pp_area_tematica_predominante', d.listaAreaTematica, d.campos.area_tematica_predominante, true);
        ppOpcoes('pp_sistema_inscricao', d.listaSistemaInscricao, d.campos.sistema_inscricao, true);
        $('#pp_enviar_email_inscricao').val(d.campos.enviar_email_inscricao || 'SIM');

        // Identificacao: os campos abaixo pertencem a tab_eventos e sao gravados
        // de volta la (whitelist no DAO). O que nao e editavel aqui fica no
        // quadro cinza, so para consulta.
        var r = d.resumo || {};
        $('.campo-evento').each(function() { $(this).val(r[$(this).data('col')] || ''); });
        var esc = function(v) { return $('<span>').text(ppTexto(v)).html(); };
        $('#ppHerdado').html(
            '<dl style="margin:0; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:0 18px;">'
            + '<div><dt>Gestor</dt><dd>' + esc(r.gestor) + '</dd></div>'
            + '<div><dt>Tipo</dt><dd>' + esc(r.tipo_projeto) + '</dd></div>'
            + '<div><dt>In&iacute;cio</dt><dd>' + esc(r.data_ini) + '</dd></div>'
            + '<div><dt>T&eacute;rmino</dt><dd>' + esc(r.data_fim) + '</dd></div>'
            + '<div><dt>Visibilidade</dt><dd>' + esc(r.visibilidade) + '</dd></div>'
            + '</dl>'
            + '<div style="font-size:11px; color:#8a959c; margin-top:8px;">'
            + 'Datas, gestor e visibilidade mudam em <b>Configura&ccedil;&atilde;o &rsaquo; Editar Evento</b>.</div>');

        var falta = function(qtd, nome, tela) {
            return qtd > 0
                ? '<li style="color:#1b7a4b;"><i class="fa fa-check"></i> ' + nome + ': <b>' + qtd + '</b> item(ns) cadastrado(s).</li>'
                : '<li style="color:#b7791f;"><i class="fa fa-exclamation-triangle"></i> ' + nome + ' ainda sem cadastro — preencha em <b>' + tela + '</b>.</li>';
        };
        $('#ppVindoDeOutras').html('<ul style="margin:0; padding-left:18px; font-size:13px;">'
            + falta(r.qtd_atividades, 'Programação', 'Configuração &rsaquo; Atividades')
            + falta(r.qtd_orcamento, 'Previsão orçamentária', 'Previsão Orçamentária')
            + '</ul>');

        // ODS: 17 caixas, limite de 3
        var marcados = d.ods || [], html = '';
        $.each(d.listaOds, function(cod, nome) {
            var chk = $.inArray(cod, marcados) >= 0 ? ' checked' : '';
            html += '<label style="display:block; font-weight:normal; font-size:13px; margin:2px 0;">'
                 + '<input type="checkbox" class="pp-ods" value="' + cod + '"' + chk + '> '
                 + cod + ' - ' + nome + '</label>';
        });
        $('#ppOdsLista').html(html);
        $('.pp-ods').change(ppOdsLimite);
        ppOdsLimite();

        // Pre-avaliacao: so aparece se o gestor deste evento exigir
        if (d.preAvaliacaoCampos && d.preAvaliacaoCampos.length) {
            $('#ppPreAvaliacaoInfo').html('Formul&aacute;rio exigido pelo gestor'
                + (d.nomeGestor ? ' <b>' + $('<span>').text(d.nomeGestor).html() + '</b>' : '') + '.');
            var fh = '';
            $.each(d.preAvaliacaoCampos, function(_, c) {
                var val = (d.preAvaliacaoRespostas && d.preAvaliacaoRespostas[c.campo]) || '';
                var id = 'pa_' + c.campo;
                var obrig = c.obrigatorio ? ' <span style="color:red;">*</span>' : '';
                fh += '<div class="form-group col-sm-6"><label for="' + id + '">'
                    + $('<span>').text(c.rotulo).html() + obrig + '</label>';
                if (c.tipo === 'textarea') {
                    fh += '<textarea id="' + id + '" class="form-control pa-campo" data-campo="' + c.campo + '" rows="3"></textarea>';
                } else if (c.tipo === 'radio' && c.opcoes) {
                    fh += '<div class="pa-campo" data-campo="' + c.campo + '" id="' + id + '">';
                    $.each(c.opcoes, function(_, o) {
                        fh += '<label style="font-weight:normal; margin-right:14px;">'
                            + '<input type="radio" name="' + id + '" value="' + o + '"' + (val === o ? ' checked' : '') + '> ' + o + '</label>';
                    });
                    fh += '</div>';
                } else if (c.tipo === 'select' && c.opcoes) {
                    fh += '<select id="' + id + '" class="form-control pa-campo" data-campo="' + c.campo + '"><option value=""></option>';
                    $.each(c.opcoes, function(_, o) { fh += '<option' + (val === o ? ' selected' : '') + '>' + o + '</option>'; });
                    fh += '</select>';
                } else {
                    fh += '<input id="' + id + '" class="form-control pa-campo" data-campo="' + c.campo + '">';
                }
                fh += '</div>';
            });
            $('#ppPreAvaliacaoCampos').html(fh);
            $.each(d.preAvaliacaoCampos, function(_, c) {
                if (c.tipo !== 'radio') {
                    $('#pa_' + c.campo).val((d.preAvaliacaoRespostas && d.preAvaliacaoRespostas[c.campo]) || '');
                }
            });
            $('#ppPreAvaliacaoSecao').show();
        } else {
            $('#ppPreAvaliacaoSecao').hide();
        }

        $('#ppMsg').html('<span style="color:#555;">'
            + (d.existe === '1' ? 'Proposta carregada.' : 'Proposta ainda não preenchida — comece pelo passo 1.')
            + '</span>');
        ppPronto = true;
        ppIr(1);
    });
}

function ppOdsLimite() {
    var n = $('.pp-ods:checked').length;
    $('.pp-ods').not(':checked').prop('disabled', n >= ppMaxOds);
    $('#ppOdsConta').html('<b>' + n + '</b> de ' + ppMaxOds + ' selecionado(s).');
}

function ppSalvar(silencioso) {
    if (!ppPronto) {
        if (!silencioso) { $('#ppMsg').html('<span style="color:#b7791f;">Aguarde o carregamento terminar.</span>'); }
        return;
    }
    if (typeof tinymce !== 'undefined') { tinymce.triggerSave(); }
    var dados = { 'c': 'GerenciarProposta', 'm': 'salvar', '_submit_check': '1' };
    $.each(ppCampos, function(_, campo) { dados[campo] = $('#pp_' + campo).val(); });
    var ods = [];
    $('.pp-ods:checked').each(function() { ods.push($(this).val()); });
    dados['ods'] = ods.join(',');
    // Campos de tab_eventos vao com prefixo ev_ — o servidor so aceita os da whitelist.
    $('.campo-evento').each(function() { dados['ev_' + $(this).data('col')] = $(this).val(); });
    $('.pa-campo').each(function() {
        var campo = $(this).data('campo');
        dados['pa_' + campo] = this.tagName === 'DIV'
            ? ($(this).find('input[type=radio]:checked').val() || '')
            : $(this).val();
    });
    if (!silencioso) { $('#ppMsg').html('<i class="fa fa-spinner fa-spin"></i> Salvando...'); }
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados, function(resp) {
        var d; try { d = (typeof resp === 'object') ? resp : JSON.parse(resp); } catch(e) { d = null; }
        var ok = d && d.statusMessage === '1';
        if (!ok || !silencioso) {
            $('#ppMsg').html('<span style="color:' + (ok ? '#2d6a4f' : '#c0392b') + ';">'
                + (d && d.message ? d.message : (ok ? 'Salvo.' : 'Erro ao salvar.')) + '</span>');
        }
    });
}
</script>
<?php
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_outros.php");
?>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:16px 20px; margin-top:10px;">
    <h4 style="margin:0 0 4px; font-size:15px; font-weight:600; color:#333;">
        <i class="fa fa-file-text-o"></i> Proposta do Evento
    </h4>
    <p style="margin:0; color:#888; font-size:12px;">
        O documento que voc&ecirc; leva para institui&ccedil;&otilde;es, editais p&uacute;blicos e protocolos.
        O que j&aacute; est&aacute; cadastrado em outras telas aparece aqui automaticamente &mdash; n&atilde;o precisa redigitar.
    </p>

    <ul class="pp-trilha">
        <li class="pp-etapa atual" data-passo="1"><span class="pp-bola">1</span><span class="pp-rotulo">Identifica&ccedil;&atilde;o</span></li>
        <li class="pp-etapa" data-passo="2"><span class="pp-bola">2</span><span class="pp-rotulo">Institui&ccedil;&otilde;es e equipe</span></li>
        <li class="pp-etapa" data-passo="3"><span class="pp-bola">3</span><span class="pp-rotulo">Justificativa</span></li>
        <li class="pp-etapa" data-passo="4"><span class="pp-bola">4</span><span class="pp-rotulo">Objetivos</span></li>
        <li class="pp-etapa" data-passo="5"><span class="pp-bola">5</span><span class="pp-rotulo">Cronograma</span></li>
        <li class="pp-etapa" data-passo="6"><span class="pp-bola">6</span><span class="pp-rotulo">Or&ccedil;amento</span></li>
        <li class="pp-etapa" data-passo="7"><span class="pp-bola">7</span><span class="pp-rotulo">Acordo de coopera&ccedil;&atilde;o</span></li>
        <li class="pp-etapa" data-passo="8"><span class="pp-bola">8</span><span class="pp-rotulo">Enquadramento</span></li>
        <li class="pp-etapa" data-passo="9"><span class="pp-bola">9</span><span class="pp-rotulo">Confer&ecirc;ncia</span></li>
    </ul>
    <div id="ppMsg" style="font-size:12px; margin-bottom:6px;"></div>

    <!-- ============ 1. IDENTIFICAO ============ -->
    <div class="pp-passo ativo" id="ppPasso1">
<div class="pp-secao">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-id-card-o"></i> Identifica&ccedil;&atilde;o do evento</h4>
                <p>Estes campos s&atilde;o do <b>cadastro do evento</b> &mdash; editar aqui altera l&aacute; tamb&eacute;m.</p>
            </div>
            <div style="padding:14px 20px;" class="row">
                <div class="form-group col-sm-8">
                    <label for="ev_titulo">T&iacute;tulo do evento:</label>
                    <input id="ev_titulo" class="form-control campo-evento" data-col="titulo">
                </div>
                <div class="form-group col-sm-4">
                    <label for="ev_sigla">Sigla:</label>
                    <input id="ev_sigla" class="form-control campo-evento" data-col="sigla">
                </div>
                <div class="form-group col-sm-4">
                    <label for="ev_finalidade">Finalidade:</label>
                    <input id="ev_finalidade" class="form-control campo-evento" data-col="finalidade">
                </div>
                <div class="form-group col-sm-4">
                    <label for="ev_categoria">Classifica&ccedil;&atilde;o:</label>
                    <input id="ev_categoria" class="form-control campo-evento" data-col="categoria"
                           placeholder="Ex.: Minicurso, Curso, Congresso">
                </div>
                <div class="form-group col-sm-4">
                    <label for="ev_duracao">Carga hor&aacute;ria:</label>
                    <input id="ev_duracao" class="form-control campo-evento" data-col="duracao">
                </div>
                <div class="form-group col-sm-4">
                    <label for="ev_periodo">Per&iacute;odo:</label>
                    <input id="ev_periodo" class="form-control campo-evento" data-col="periodo">
                </div>
                <div class="form-group col-sm-5">
                    <label for="ev_desc_local_realizacao">Local de realiza&ccedil;&atilde;o:</label>
                    <input id="ev_desc_local_realizacao" class="form-control campo-evento" data-col="desc_local_realizacao">
                </div>
                <div class="form-group col-sm-3">
                    <label for="ev_cidade_uf">Cidade / UF:</label>
                    <input id="ev_cidade_uf" class="form-control campo-evento" data-col="cidade_uf">
                </div>
                <div class="form-group col-sm-4">
                    <label for="ev_coordenadores">Coordena&ccedil;&atilde;o:</label>
                    <input id="ev_coordenadores" class="form-control campo-evento" data-col="coordenadores">
                </div>
                <div class="form-group col-sm-4">
                    <label for="ev_nome_contato">Contato &mdash; nome:</label>
                    <input id="ev_nome_contato" class="form-control campo-evento" data-col="nome_contato">
                </div>
                <div class="form-group col-sm-4">
                    <label for="ev_email_contato">Contato &mdash; e-mail:</label>
                    <input id="ev_email_contato" class="form-control campo-evento" data-col="email_contato">
                </div>
                <div class="form-group col-sm-12">
                    <div class="pp-herdado" id="ppHerdado"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ 2. INSTITUIES E EQUIPE ============ -->
    <div class="pp-passo" id="ppPasso2">
<div class="pp-secao">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-university"></i> Institui&ccedil;&otilde;es e equipe</h4>
                <p>Blocos livres &mdash; escreva como o edital ou a institui&ccedil;&atilde;o pedir.</p>
            </div>
            <div style="padding:14px 20px;" class="row">
                <div class="form-group col-sm-6">
                    <label for="pp_departamentos">Centro / Departamento de origem:</label>
                    <input id="pp_departamentos" name="departamentos" class="form-control"
                           placeholder="Ex.: Centro de Ci&ecirc;ncias Biol&oacute;gicas / Departamento de Psicologia">
                </div>
                <div class="form-group col-sm-6">
                    <label for="pp_apoio">Apoio:</label>
                    <textarea id="pp_apoio" name="apoio" class="editor_html"
                              placeholder="Quem apoia sem ser parte. Ex.: UEL, PROEX"></textarea>
                </div>
                <div class="form-group col-sm-12">
                    <label for="pp_partes_envolvidas">Partes envolvidas:</label>
                    <textarea id="pp_partes_envolvidas" name="partes_envolvidas" class="editor_html"
                              placeholder="Institui&ccedil;&atilde;o proponente e funda&ccedil;&atilde;o de apoio respons&aacute;vel pela gest&atilde;o financeira"></textarea>
                </div>
                <div class="form-group col-sm-12">
                    <label for="pp_comissao_organizadora">Comiss&atilde;o organizadora:</label>
                    <textarea id="pp_comissao_organizadora" name="comissao_organizadora" class="editor_html_grande"
                              placeholder="Uma pessoa por linha: nome &mdash; fun&ccedil;&atilde;o no evento &mdash; celular &mdash; e-mail"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ 3. JUSTIFICATIVA ============ -->
    <div class="pp-passo" id="ppPasso3">
        <div class="pp-secao">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-comment-o"></i> Justificativa</h4>
                <p>Por que o evento deve acontecer, que lacuna preenche, a quem beneficia.</p>
            </div>
            <div style="padding:14px 20px;" class="row">
<div class="form-group col-sm-12">
                    <label for="pp_justificativa">Justificativa:</label>
                    <textarea id="pp_justificativa" name="justificativa" class="editor_html_grande"
                              placeholder="Por que o evento deve acontecer, que lacuna preenche, a quem beneficia"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ 4. OBJETIVOS ============ -->
    <div class="pp-passo" id="ppPasso4">
        <div class="pp-secao">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-bullseye"></i> Objetivos</h4>
                <p>O que o evento se prop&otilde;e a alcan&ccedil;ar.</p>
            </div>
            <div style="padding:14px 20px;" class="row">
<div class="form-group col-sm-12">
                    <label for="pp_objetivo">Objetivos:</label>
                    <textarea id="pp_objetivo" name="objetivo" class="editor_html"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ 5. CRONOGRAMA ============ -->
    <div class="pp-passo" id="ppPasso5">
<div class="pp-secao">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-calendar"></i> Cronograma</h4>
                <p>Tramita&ccedil;&atilde;o da proposta, inscri&ccedil;&otilde;es, submiss&atilde;o de trabalhos e realiza&ccedil;&atilde;o.</p>
            </div>
            <div style="padding:14px 20px;" class="row">
                <div class="form-group col-sm-12">
                    <label for="pp_cronograma">Cronograma:</label>
                    <textarea id="pp_cronograma" name="cronograma" class="editor_html_grande"
                              placeholder="Uma atividade por linha, com os meses"></textarea>
                    <span style="font-size:11px; color:#999;">Sugerido a partir das datas do evento quando est&aacute; em branco &mdash; ajuste &agrave; vontade.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ 6. ORAMENTO ============ -->
    <div class="pp-passo" id="ppPasso6">
<div class="pp-secao">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-calculator"></i> Previs&atilde;o or&ccedil;ament&aacute;ria</h4>
                <p>Demonstrativo de receitas e despesas como vai sair no documento. Se preferir
                   lan&ccedil;ar linha a linha, use a tela <b>Previs&atilde;o Or&ccedil;ament&aacute;ria</b> &mdash; aqui &eacute; o
                   texto/tabela da proposta.</p>
            </div>
            <div style="padding:14px 20px;" class="row">
                <div class="form-group col-sm-12">
                    <label for="pp_previsao_orcamentaria">Receitas e despesas:</label>
                    <textarea id="pp_previsao_orcamentaria" name="previsao_orcamentaria" class="editor_html_grande"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ 7. ACORDO DE COOPERAO ============ -->
    <div class="pp-passo" id="ppPasso7">
<div class="pp-secao">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-handshake-o"></i> Acordo de coopera&ccedil;&atilde;o</h4>
                <p>&Oacute;rg&atilde;o interveniente e como as inscri&ccedil;&otilde;es ser&atilde;o feitas.</p>
            </div>
            <div style="padding:14px 20px;" class="row">
                <div class="form-group col-sm-6">
                    <label for="pp_orgao_interveniente">&Oacute;rg&atilde;o interveniente:</label>
                    <input id="pp_orgao_interveniente" name="orgao_interveniente" class="form-control">
                </div>
                <div class="form-group col-sm-3">
                    <label for="pp_acordo_vigencia_ini">Vig&ecirc;ncia &mdash; in&iacute;cio:</label>
                    <input type="date" id="pp_acordo_vigencia_ini" name="acordo_vigencia_ini" class="form-control">
                </div>
                <div class="form-group col-sm-3">
                    <label for="pp_acordo_vigencia_fim">Vig&ecirc;ncia &mdash; fim:</label>
                    <input type="date" id="pp_acordo_vigencia_fim" name="acordo_vigencia_fim" class="form-control">
                </div>
                <div class="form-group col-sm-6">
                    <label for="pp_sistema_inscricao">Sistema de inscri&ccedil;&atilde;o:</label>
                    <select id="pp_sistema_inscricao" name="sistema_inscricao" class="form-control"></select>
                </div>
                <div class="form-group col-sm-3">
                    <label for="pp_enviar_email_inscricao">Enviar e-mail de inscri&ccedil;&atilde;o ao contato?</label>
                    <select id="pp_enviar_email_inscricao" name="enviar_email_inscricao" class="form-control">
                        <option value="SIM">Sim</option>
                        <option value="NAO">N&atilde;o</option>
                    </select>
                </div>
                <div class="form-group col-sm-3">
                    <label for="pp_recebimento_relatorios">Recebimento de relat&oacute;rios:</label>
                    <input id="pp_recebimento_relatorios" name="recebimento_relatorios" class="form-control">
                </div>
                <div class="form-group col-sm-12">
                    <label for="pp_declaracao_atuacao">Declara&ccedil;&atilde;o de atua&ccedil;&atilde;o da equipe:</label>
                    <textarea id="pp_declaracao_atuacao" name="declaracao_atuacao" class="editor_html"
                              placeholder="Ex.: Coordena&ccedil;&atilde;o, ministrantes e professores auxiliares atuar&atilde;o de forma volunt&aacute;ria, sem remunera&ccedil;&atilde;o."></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ 8. ENQUADRAMENTO ============ -->
    <div class="pp-passo" id="ppPasso8">
<div class="pp-secao">
            <div class="pp-secao-titulo"><h4><i class="fa fa-sitemap"></i> Enquadramento</h4></div>
            <div style="padding:14px 20px;" class="row">
                <div class="form-group col-sm-6">
                    <label for="pp_origem_proposta">Origem da proposta (estrutura administrativa):</label>
                    <input id="pp_origem_proposta" name="origem_proposta" class="form-control">
                </div>
                <div class="form-group col-sm-3">
                    <label for="pp_proposta_de">Proposta de:</label>
                    <select id="pp_proposta_de" name="proposta_de" class="form-control"></select>
                </div>
                <div class="form-group col-sm-3">
                    <label for="pp_ambito">&Acirc;mbito:</label>
                    <select id="pp_ambito" name="ambito" class="form-control"></select>
                </div>
                <div class="form-group col-sm-3">
                    <label for="pp_modalidade">Modalidade:</label>
                    <select id="pp_modalidade" name="modalidade" class="form-control"></select>
                </div>
                <div class="form-group col-sm-2">
                    <label for="pp_carga_horaria_distancia">Carga hor&aacute;ria a dist&acirc;ncia:</label>
                    <input id="pp_carga_horaria_distancia" name="carga_horaria_distancia" class="form-control" placeholder="horas">
                </div>
                <div class="form-group col-sm-4">
                    <label for="pp_area_tematica_predominante">&Aacute;rea tem&aacute;tica predominante:</label>
                    <select id="pp_area_tematica_predominante" name="area_tematica_predominante" class="form-control"></select>
                </div>
                <div class="form-group col-sm-3">
                    <label for="pp_vinculado_programa">Vinculado a programa/projeto:</label>
                    <input id="pp_vinculado_programa" name="vinculado_programa" class="form-control">
                </div>
                <div class="form-group col-sm-3">
                    <label for="pp_vinculado_projeto_numero">N&uacute;mero do projeto:</label>
                    <input id="pp_vinculado_projeto_numero" name="vinculado_projeto_numero" class="form-control">
                </div>
            </div>
        </div>

<div class="pp-secao" style="display:none;">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-globe"></i> Objetivos de Desenvolvimento Sustent&aacute;vel</h4>
                <p>Selecione no m&aacute;ximo <b>3</b>. <span id="ppOdsConta"></span></p>
            </div>
            <div style="padding:14px 20px;" id="ppOdsLista"></div>
        </div>
    </div>

    <!-- ============ 9. CONFERNCIA ============ -->
    <div class="pp-passo" id="ppPasso9">
<div class="pp-secao">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-list-ul"></i> O que vem de outras telas</h4>
                <p>A proposta usa esses dados direto da origem &mdash; conferir antes de exportar.</p>
            </div>
            <div style="padding:14px 20px;" id="ppVindoDeOutras"></div>
        </div>

<div class="pp-secao" id="ppPreAvaliacaoSecao" style="display:none;">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-check-square-o"></i> Pr&eacute;-avalia&ccedil;&atilde;o</h4>
                <p id="ppPreAvaliacaoInfo"></p>
            </div>
            <div style="padding:14px 20px;" class="row" id="ppPreAvaliacaoCampos"></div>
        </div>

<div class="pp-secao">
            <div class="pp-secao-titulo">
                <h4><i class="fa fa-download"></i> Exporta&ccedil;&atilde;o</h4>
                <p>Gerar o documento da proposta para protocolo em prefeituras, institui&ccedil;&otilde;es e editais.</p>
            </div>
            <div style="padding:14px 20px; color:#888; font-size:12px;">
                <i class="fa fa-clock-o"></i> Em constru&ccedil;&atilde;o &mdash; a exporta&ccedil;&atilde;o em PDF e DOCX ser&aacute; ligada aqui.
            </div>
        </div>
    </div>

    <div style="margin-top:16px; display:flex; gap:10px; border-top:1px solid #eee; padding-top:14px;">
        <button class="btn" id="ppBtnVoltar" onclick="ppIr(ppPasso - 1)" style="display:none; background:#f0f0f0; color:#555; border:1px solid #ddd; border-radius:6px; padding:8px 20px; font-size:13px;">
            <i class="fa fa-arrow-left"></i> Voltar
        </button>
        <button class="btn" onclick="ppSalvar(false)" style="background:#2d6a4f; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-size:13px;">
            <i class="fa fa-save"></i> Salvar
        </button>
        <button class="btn" id="ppBtnProximo" onclick="ppIr(ppPasso + 1)" style="background:#34495e; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-size:13px;">
            Pr&oacute;ximo <i class="fa fa-arrow-right"></i>
        </button>
    </div>
</div>
<div style="height:30px;"></div>
