<?php
require_once(dirname(__FILE__) . "/../../include/config.php");

/* Tela exclusiva do super admin (mesmo criterio 668 do definir-senha-manual).
 * O controller repete o bloqueio — aqui e so para redirecionar com mensagem. */
if ((int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0) !== 668) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<blockquote><br><br><br>" . $_SESSION['MSG'] . "<br><br><br>";
    echo "<a href='" . $URL . "'>Clique aqui para efetuar login</a><br><br><br>&nbsp;</blockquote>";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}
$objSRV = new GerenciarEventoDivulgacaoAC();
?>
<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<style type="text/css">
    .ed-abas { display:flex; gap:4px; border-bottom:2px solid #e8e8e8; margin-top:10px; }
    .ed-aba { background:#f4f6f7; border:1px solid #e8e8e8; border-bottom:none; border-radius:8px 8px 0 0;
              padding:9px 22px; font-size:13px; color:#666; cursor:pointer; margin-bottom:-2px; }
    .ed-aba.ativa { background:#fff; color:#222; font-weight:600; border-bottom:2px solid #fff; }
    .ed-card { background:#fff; border:1px solid #e8e8e8; border-radius:0 12px 12px 12px; padding:16px 20px; }
    .ed-secao { background:#fff; border:1px solid #e8e8e8; border-radius:12px; margin-top:12px; overflow:hidden; }
    .ed-secao-titulo { padding:10px 20px; border-bottom:1px solid #eee; }
    .ed-secao-titulo h4 { margin:0; font-size:15px; font-weight:600; color:#333; }
    .ed-secao-titulo p { margin:4px 0 0; color:#888; font-size:12px; }
    .ed-pill { display:inline-block; border-radius:20px; padding:2px 12px; font-size:11px; font-weight:600; }
    .ed-pill-on { background:#eafaf1; color:#1b7a4b; }
    .ed-pill-off { background:#fdecea; color:#b03a2e; }
    .ed-pill-draft { background:#fff4e5; color:#9c6500; }
</style>
<script type="text/javascript">
var edCampos = ['titulo','periodo_de_inscricoes','publico_alvo','periodo_do_curso','vagas','local',
                'carga_horaria','investimento','descricao','programacao','outras_informacoes',
                'link_inscricoes','tags'];
var edEditoresProntos = false;

$(document).ready(function() {
    edCarregar();
    $('#edArquivo').change(edPreviewLocal);
    $('.ed-aba').click(function() { edAba($(this).data('aba')); });
});

function edAba(nome) {
    $('.ed-aba').removeClass('ativa');
    $('.ed-aba[data-aba="' + nome + '"]').addClass('ativa');
    $('.ed-painel').hide();
    $('#edPainel_' + nome).show();
    if (nome === 'view') { edCarregarPreview(); }
}

function edUrlArte() {
    return '<?php echo WB_URL_COMMAND ?>&c=GerenciarEventoDivulgacao&m=baixarArte&_submit_check=1&t=' + Math.random();
}

/* Editor visual identico ao padrao da casa (evento-configurar-campos), com a
 * aba "code" para editar o HTML cru. valid_elements '*[*]' impede o TinyMCE
 * de remover tags/atributos do HTML que vai para o site. */
function edConfigEditor(altura) {
    return {
        plugins: "code textcolor link table",
        width: '100%',
        height: altura,
        resize: "both",
        menubar: false,
        statusbar: true,
        block_formats: 'Header 3=h3;Header 4=h4;Header 5=h5;Paragrafo=p',
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

function edInitEditores() {
    if (edEditoresProntos) return;
    edEditoresProntos = true;
    var cfgP = edConfigEditor(200); cfgP.selector = "textarea.editor_html";
    var cfgG = edConfigEditor(420); cfgG.selector = "textarea.editor_html_grande";
    tinymce.init(cfgP);
    tinymce.init(cfgG);
}

function edCarregar() {
    $('#edMsg').html('<i class="fa fa-spinner fa-spin"></i> Carregando...');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarEventoDivulgacao', 'm': 'carregar', '_submit_check': '1'
    }, function(resp) {
        var d; try { d = (typeof resp === 'object') ? resp : JSON.parse(resp); } catch(e) { d = null; }
        if (!d || d.statusMessage !== '1') { $('#edMsg').html('<span style="color:#c0392b;">Erro ao carregar os dados.</span>'); return; }
        $.each(edCampos, function(_, campo) {
            var valor = d.campos[campo] || '';
            $('#ed_' + campo).val(valor);
            var editor = (typeof tinymce !== 'undefined') ? tinymce.get('ed_' + campo) : null;
            if (editor) { editor.setContent(valor); }
        });
        edInitEditores();

        $('#ed_status_conteudo').val(d.statusConteudo || 'ATIVADA');
        edPintarStatus(d.statusConteudo || 'ATIVADA', d.wpStatus || '', d.wpPostId || '');

        var aviso = d.existe === '1' ? '' : ' <span style="color:#2980b9;">(campos sugeridos a partir dos dados do evento — revise antes de enviar)</span>';
        $('#edMsg').html('<span style="color:#555;">Dados carregados.' + aviso + '</span>');

        if (d.arteArquivo) {
            $('#edArtePreview').attr('src', edUrlArte()).show();
            $('#edArteNome').text(d.arteArquivo);
        } else {
            $('#edArtePreview').hide();
            $('#edArteNome').text('Nenhuma arte enviada.');
        }

        $('#ed_wp_url').val(d.wpUrl || '');
        $('#ed_wp_user').val(d.wpUser || '');
        $('#ed_wp_app_pass').val('');
        if (d.wpAppPassDefinida === '1') {
            $('#ed_wp_app_pass').attr('placeholder', '•••••••• (guardada) — em branco mant&eacute;m a atual');
            $('#edSenhaSit').html('<i class="fa fa-lock" style="color:#1b7a4b;"></i> <span style="color:#1b7a4b;">Senha guardada e criptografada.</span>');
        } else if (d.fallbackEnv === '1' && !d.wpUrl) {
            $('#ed_wp_app_pass').attr('placeholder', 'usando a configura&ccedil;&atilde;o padr&atilde;o do servidor');
            $('#edSenhaSit').html('<i class="fa fa-info-circle" style="color:#2980b9;"></i> <span style="color:#2980b9;">Sem configura&ccedil;&atilde;o pr&oacute;pria: usa o site padr&atilde;o do servidor.</span>');
        } else {
            $('#ed_wp_app_pass').attr('placeholder', 'cole a senha de aplicativo do WordPress');
            $('#edSenhaSit').html('<i class="fa fa-exclamation-triangle" style="color:#b7791f;"></i> <span style="color:#b7791f;">Nenhuma senha configurada para este evento.</span>');
        }
    });
}

function edPintarStatus(statusConteudo, wpStatus, wpPostId) {
    var html = statusConteudo === 'INATIVADA'
        ? '<span class="ed-pill ed-pill-off">Conte&uacute;do inativo no InscriTec</span>'
        : '<span class="ed-pill ed-pill-on">Conte&uacute;do ativo no InscriTec</span>';
    if (wpPostId) {
        html += ' &nbsp; ' + (wpStatus === 'publish'
            ? '<span class="ed-pill ed-pill-on">WordPress: publicado</span>'
            : '<span class="ed-pill ed-pill-draft">WordPress: rascunho</span>')
            + ' <span style="color:#999; font-size:11px;">(post ' + wpPostId + ')</span>';
        $('#edBotoesWp').show();
        $('#edBtnPublicarWp').toggle(wpStatus !== 'publish');
        $('#edBtnDespublicarWp').toggle(wpStatus === 'publish');
    } else {
        html += ' &nbsp; <span class="ed-pill ed-pill-draft">Ainda n&atilde;o enviado ao WordPress</span>';
        $('#edBotoesWp').hide();
    }
    $('#edStatusBarra').html(html);
}

function edDados(m) {
    if (typeof tinymce !== 'undefined') { tinymce.triggerSave(); }
    var dados = { 'c': 'GerenciarEventoDivulgacao', 'm': m, '_submit_check': '1' };
    $.each(edCampos, function(_, campo) { dados[campo] = $('#ed_' + campo).val(); });
    dados['status_conteudo'] = $('#ed_status_conteudo').val();
    dados['wp_url'] = $('#ed_wp_url').val();
    dados['wp_user'] = $('#ed_wp_user').val();
    // Senha so trafega quando digitada — em branco o servidor mantem a atual.
    var pass = $.trim($('#ed_wp_app_pass').val());
    if (pass !== '') { dados['wp_app_pass'] = pass; }
    return dados;
}

function edSalvar() {
    $('#edMsg').html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), edDados('salvar'), function(resp) {
        var d; try { d = (typeof resp === 'object') ? resp : JSON.parse(resp); } catch(e) { d = null; }
        var ok = d && d.statusMessage === '1';
        $('#edMsg').html('<span style="color:' + (ok ? '#2d6a4f' : '#c0392b') + ';">' + (d && d.message ? d.message : (ok ? 'Salvo.' : 'Erro ao salvar.')) + '</span>');
        if (ok) { $('#ed_wp_app_pass').val(''); edCarregar(); }
    });
}

function edCarregarPreview() {
    if (typeof tinymce !== 'undefined') { tinymce.triggerSave(); }
    var dados = edDados('preview');
    $('#edPreviewArea').html('<div style="text-align:center; padding:40px; color:#999;"><i class="fa fa-spinner fa-spin"></i> Montando pr&eacute;via...</div>');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados, function(html) {
        $('#edPreviewArea').html(html);
    });
}

function edPublicar() {
    if (!confirm('Enviar o conteúdo para o WordPress configurado?\n\nSe já foi enviado antes, o conteúdo de lá será SOBRESCRITO por este.')) return;
    $('#edMsg').html('<i class="fa fa-spinner fa-spin"></i> Enviando para o WordPress...');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), edDados('publicarNoWordPress'), function(resp) {
        var d; try { d = (typeof resp === 'object') ? resp : JSON.parse(resp); } catch(e) { d = null; }
        var ok = d && d.statusMessage === '1';
        var msg = d && d.message ? d.message : (ok ? 'Enviado.' : 'Erro ao enviar.');
        if (ok && d.linkRevisao) {
            msg += ' <a href="' + d.linkRevisao + '" target="_blank" rel="noopener">Abrir no WP-admin</a>';
        }
        $('#edMsg').html('<span style="color:' + (ok ? '#2d6a4f' : '#c0392b') + ';">' + msg + '</span>');
        if (ok) { edCarregar(); }
    });
}

function edStatusWp(novo) {
    var texto = novo === 'publish' ? 'PUBLICAR a página no WordPress?' : 'DESPUBLICAR a página (voltar a rascunho)?';
    if (!confirm(texto)) return;
    $('#edMsg').html('<i class="fa fa-spinner fa-spin"></i> Alterando status no WordPress...');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c':'GerenciarEventoDivulgacao','m':'alterarStatusWp','wp_novo_status':novo,'_submit_check':'1'
    }, function(resp) {
        var d; try { d = (typeof resp === 'object') ? resp : JSON.parse(resp); } catch(e) { d = null; }
        var ok = d && d.statusMessage === '1';
        $('#edMsg').html('<span style="color:' + (ok ? '#2d6a4f' : '#c0392b') + ';">' + (d && d.message ? d.message : 'Erro.') + '</span>');
        if (ok) { edCarregar(); }
    });
}

function edTestarConexao() {
    $('#edWpTesteMsg').html('<i class="fa fa-spinner fa-spin"></i> Testando...');
    var pass = $.trim($('#ed_wp_app_pass').val());
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c':'GerenciarEventoDivulgacao','m':'testarConexao','_submit_check':'1',
        'wp_url': $('#ed_wp_url').val(), 'wp_user': $('#ed_wp_user').val(), 'wp_app_pass': pass
    }, function(resp) {
        var d; try { d = (typeof resp === 'object') ? resp : JSON.parse(resp); } catch(e) { d = null; }
        var ok = d && d.statusMessage === '1';
        $('#edWpTesteMsg').html('<span style="color:' + (ok ? '#2d6a4f' : '#c0392b') + ';">'
            + (ok ? '<i class="fa fa-check"></i> ' : '<i class="fa fa-times"></i> ')
            + (d && d.message ? d.message : 'Erro no teste.') + '</span>');
    });
}

function edVerSenha() {
    var campo = $('#ed_wp_app_pass');
    var mostrando = campo.attr('type') === 'text';
    campo.attr('type', mostrando ? 'password' : 'text');
    $('#edOlhoSenha').attr('class', mostrando ? 'fa fa-eye' : 'fa fa-eye-slash');
}

function edPreviewLocal() {
    var f = this.files && this.files[0];
    if (!f) return;
    var reader = new FileReader();
    reader.onload = function(ev) { $('#edArtePreview').attr('src', ev.target.result).show(); };
    reader.readAsDataURL(f);
}

function edEnviarArte() {
    var f = $('#edArquivo')[0].files[0];
    if (!f) { alert('Selecione o arquivo da arte.'); return; }
    var fd = new FormData();
    fd.append('c', 'GerenciarEventoDivulgacao');
    fd.append('m', 'salvarArte');
    fd.append('_submit_check', '1');
    fd.append('arquivo', f);
    $('#edArteMsg').html('<i class="fa fa-spinner fa-spin"></i> Enviando arte...');
    $.ajax({
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        type: 'POST', data: fd, processData: false, contentType: false,
        success: function(resp) {
            var d; try { d = (typeof resp === 'object') ? resp : JSON.parse(resp); } catch(e) { d = null; }
            var ok = d && d.statusMessage === '1';
            $('#edArteMsg').html('<span style="color:' + (ok ? '#2d6a4f' : '#c0392b') + ';">' + (d && d.message ? d.message : (ok ? 'Arte enviada.' : 'Erro no envio.')) + '</span>');
            if (ok) { $('#edArtePreview').attr('src', edUrlArte()).show(); $('#edArteNome').text(f.name); }
        },
        error: function() { $('#edArteMsg').html('<span style="color:#c0392b;">Erro no envio da arte.</span>'); }
    });
}
</script>
<?php
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_configuracao.php");
?>

<div class="ed-abas">
    <div class="ed-aba" data-aba="view"><i class="fa fa-eye"></i> Visualizar</div>
    <div class="ed-aba ativa" data-aba="editar"><i class="fa fa-pencil"></i> Editar</div>
    <div class="ed-aba" data-aba="config"><i class="fa fa-cog"></i> Config WordPress</div>
</div>

<div class="ed-card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
        <div id="edStatusBarra" style="font-size:12px;"></div>
        <div>
            <label style="font-weight:normal; font-size:12px; margin:0;">
                Conte&uacute;do:
                <select id="ed_status_conteudo" class="form-control input-sm" style="width:auto; display:inline-block;">
                    <option value="ATIVADA">Ativo</option>
                    <option value="INATIVADA">Inativo</option>
                </select>
            </label>
        </div>
    </div>
    <div id="edMsg" style="margin-bottom:10px; font-size:12px;"></div>

    <!-- ================= ABA VISUALIZAR ================= -->
    <div class="ed-painel" id="edPainel_view" style="display:none;">
        <p style="color:#888; font-size:12px;">
            Assim a p&aacute;gina aparece para quem est&aacute; se inscrevendo <b>pelo pr&oacute;prio InscriTec</b>.
            A pr&eacute;via usa o que est&aacute; na aba Editar agora (salvo ou n&atilde;o).
        </p>
        <div id="edPreviewArea" style="border:1px solid #eceff1; border-radius:10px; padding:24px; background:#fff;"></div>
    </div>

    <!-- ================= ABA EDITAR ================= -->
    <div class="ed-painel" id="edPainel_editar">
        <p style="color:#888; font-size:12px;">
            Nos editores, o bot&atilde;o <b>&lt;/&gt;</b> mostra e edita o HTML cru &mdash; o conte&uacute;do
            aparece exatamente como estiver l&aacute;.
        </p>
        <div class="row">
            <div class="form-group col-sm-8">
                <label for="ed_titulo">T&iacute;tulo (com subt&iacute;tulo, se houver &mdash; texto puro):</label>
                <span style="color: red;">*</span>
                <textarea id="ed_titulo" name="titulo" class="form-control" rows="2"
                          placeholder="Ex.: II JORNADA DE ... — subt&iacute;tulo na linha de baixo"></textarea>
            </div>
            <div class="form-group col-sm-4">
                <label for="ed_tags">Tags (separadas por v&iacute;rgula):</label>
                <input id="ed_tags" name="tags" class="form-control" placeholder="psicologia, jornada, avalia&ccedil;&atilde;o">
            </div>

            <div class="form-group col-sm-6">
                <label for="ed_periodo_de_inscricoes">Per&iacute;odo de inscri&ccedil;&otilde;es:</label>
                <textarea id="ed_periodo_de_inscricoes" name="periodo_de_inscricoes" class="editor_html"></textarea>
            </div>
            <div class="form-group col-sm-6">
                <label for="ed_periodo_do_curso">Per&iacute;odo do evento/curso:</label>
                <textarea id="ed_periodo_do_curso" name="periodo_do_curso" class="editor_html"></textarea>
            </div>

            <div class="form-group col-sm-6">
                <label for="ed_vagas">Vagas:</label>
                <textarea id="ed_vagas" name="vagas" class="editor_html"></textarea>
            </div>
            <div class="form-group col-sm-6">
                <label for="ed_local">Local:</label>
                <textarea id="ed_local" name="local" class="editor_html"></textarea>
            </div>

            <div class="form-group col-sm-6">
                <label for="ed_carga_horaria">Carga hor&aacute;ria:</label>
                <textarea id="ed_carga_horaria" name="carga_horaria" class="editor_html"></textarea>
            </div>
            <div class="form-group col-sm-6">
                <label for="ed_publico_alvo">P&uacute;blico-alvo (texto puro):</label>
                <textarea id="ed_publico_alvo" name="publico_alvo" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group col-sm-12">
                <label for="ed_investimento">Investimento (valores de inscri&ccedil;&atilde;o):</label>
                <textarea id="ed_investimento" name="investimento" class="editor_html"></textarea>
            </div>
            <div class="form-group col-sm-12">
                <label for="ed_descricao">Descri&ccedil;&atilde;o (objetivo, realiza&ccedil;&atilde;o, apoio, coordena&ccedil;&atilde;o):</label>
                <textarea id="ed_descricao" name="descricao" class="editor_html"></textarea>
            </div>
            <div class="form-group col-sm-12">
                <label for="ed_programacao">Programa&ccedil;&atilde;o (cronograma completo):</label>
                <textarea id="ed_programacao" name="programacao" class="editor_html_grande"></textarea>
            </div>
            <div class="form-group col-sm-12">
                <label for="ed_outras_informacoes">Outras informa&ccedil;&otilde;es:</label>
                <textarea id="ed_outras_informacoes" name="outras_informacoes" class="editor_html"></textarea>
            </div>
            <div class="form-group col-sm-6">
                <label for="ed_link_inscricoes">Link de inscri&ccedil;&otilde;es:</label>
                <input id="ed_link_inscricoes" name="link_inscricoes" class="form-control"
                       placeholder="https://opctec.net.br/hutec/.../inscricoes">
            </div>
        </div>

        <div class="ed-secao">
            <div class="ed-secao-titulo">
                <h4><i class="fa fa-image"></i> Arte de divulga&ccedil;&atilde;o</h4>
                <p>Especifica&ccedil;&atilde;o: <b>quadrada (1:1), 1080 &times; 1080 pixels, PNG ou JPG</b>.</p>
            </div>
            <div style="padding:14px 20px; display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap;">
                <img id="edArtePreview" src="" alt="" style="display:none; width:150px; height:150px; object-fit:cover; border:1px solid #ddd; border-radius:8px;">
                <div>
                    <div id="edArteNome" style="font-size:12px; color:#555; margin-bottom:8px;"></div>
                    <input type="file" id="edArquivo" accept=".png,.jpg,.jpeg" style="font-size:12px;">
                    <button class="btn btn-sm" onclick="edEnviarArte()" style="margin-top:8px; background:#2980b9; color:#fff; border:none; border-radius:6px; padding:6px 16px; font-size:12px;">
                        <i class="fa fa-upload"></i> Enviar arte
                    </button>
                    <div id="edArteMsg" style="margin-top:8px; font-size:12px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= ABA CONFIG WORDPRESS ================= -->
    <div class="ed-painel" id="edPainel_config" style="display:none;">
        <p style="color:#888; font-size:12px;">
            <b>Opcional.</b> A p&aacute;gina do evento j&aacute; funciona dentro do InscriTec sem nada disso.
            Configure aqui apenas se este evento tamb&eacute;m deve ser publicado num site WordPress pr&oacute;prio.
            Em branco, usa o site padr&atilde;o do servidor (quando houver).
        </p>
        <div class="row">
            <div class="form-group col-sm-5">
                <label for="ed_wp_url">URL do site:</label>
                <input id="ed_wp_url" name="wp_url" class="form-control" placeholder="https://meusite.org.br" autocomplete="off">
            </div>
            <div class="form-group col-sm-3">
                <label for="ed_wp_user">Usu&aacute;rio:</label>
                <input id="ed_wp_user" name="wp_user" class="form-control" autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label for="ed_wp_app_pass">Senha de aplicativo:</label>
                <div style="display:flex;">
                    <input type="password" id="ed_wp_app_pass" name="wp_app_pass" class="form-control"
                           autocomplete="new-password" style="border-radius:4px 0 0 4px;">
                    <button type="button" class="btn btn-sm" onclick="edVerSenha()" title="Mostrar/ocultar"
                            style="border:1px solid #ccc; border-left:none; border-radius:0 4px 4px 0; background:#f7f7f7;">
                        <i class="fa fa-eye" id="edOlhoSenha"></i>
                    </button>
                </div>
                <div id="edSenhaSit" style="font-size:11px; margin-top:5px;"></div>
            </div>
        </div>
        <div style="background:#f9fbfc; border:1px solid #eceff1; border-radius:8px; padding:10px 14px; font-size:12px; color:#666;">
            <i class="fa fa-shield" style="color:#1b7a4b;"></i>
            A senha &eacute; gerada no WP-admin do site de destino (<b>Usu&aacute;rios &rsaquo; Perfil &rsaquo; Senhas de aplicativo</b>),
            fica <b>criptografada</b> no servidor e nunca &eacute; reexibida aqui.
            <div style="margin-top:10px;">
                <button class="btn btn-sm" onclick="edTestarConexao()" style="background:#2980b9; color:#fff; border:none; border-radius:6px; padding:6px 16px; font-size:12px;">
                    <i class="fa fa-plug"></i> Testar conex&atilde;o
                </button>
                <span id="edWpTesteMsg" style="margin-left:10px;"></span>
            </div>
        </div>
    </div>

    <!-- ================= ACOES ================= -->
    <div style="margin:18px 0 4px; display:flex; gap:10px; flex-wrap:wrap; border-top:1px solid #eee; padding-top:14px;">
        <button class="btn" onclick="edSalvar()" style="background:#2d6a4f; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-size:13px;">
            <i class="fa fa-save"></i> Salvar
        </button>
        <button class="btn" onclick="edPublicar()" style="background:#34495e; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-size:13px;">
            <i class="fa fa-cloud-upload"></i> Enviar conte&uacute;do ao WordPress
        </button>
        <span id="edBotoesWp" style="display:none;">
            <button class="btn" id="edBtnPublicarWp" onclick="edStatusWp('publish')" style="background:#1b7a4b; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-size:13px;">
                <i class="fa fa-globe"></i> Publicar no site
            </button>
            <button class="btn" id="edBtnDespublicarWp" onclick="edStatusWp('draft')" style="background:#b03a2e; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-size:13px;">
                <i class="fa fa-eye-slash"></i> Despublicar
            </button>
        </span>
    </div>
</div>
<div style="height:30px;"></div>
