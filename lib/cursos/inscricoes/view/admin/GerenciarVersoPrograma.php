<?php
// Gate de seguranca da view (mesmo do modulo Midia). Redireciona ao login
// quando nao autenticado / sem permissao.
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarVersoProgramaAC();
$debug = isset($debug) ? $debug : false;
?>
<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_configuracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_certificados.php");
?>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Editor de Programa (verso do certificado)</h4>
    </div>

    <div style="padding:16px 20px;">

        <!-- Escopo -->
        <div class="form-group mb-3">
            <label for="codOficina">Escopo:</label>
            <select name="codOficina" id="codOficina" class="form-control">
                <option value="">Geral do evento (todas as oficinas)</option>
                <?php CarregarOptionsAC::oficinas($fws_, $debug); ?>
            </select>
            <small class="text-muted">Deixe em "Geral" para o programa padr&atilde;o do evento, ou escolha uma oficina para um programa espec&iacute;fico.</small>
        </div>

        <!-- Fundo do verso -->
        <div class="row">
            <div class="col-sm-4 form-group mb-3">
                <label for="modoFundo">Fundo:</label>
                <select name="modoFundo" id="modoFundo" class="form-control">
                    <option value="branco">Branco (sem arte)</option>
                    <option value="completo">PDF/imagem de fundo (A4)</option>
                </select>
            </div>
        </div>

        <!-- Upload do fundo (aparece quando o modo e "PDF/imagem de fundo") -->
        <div id="boxFundoCompleto" style="display:none; background:#f6f9fc; border:1px solid #e3ebf3; border-radius:8px; padding:12px 14px; margin-bottom:14px;">
            <label style="font-weight:600;">Fundo do verso &mdash; <b>PDF A4 paisagem</b> (recomendado) ou imagem
                <span class="vp-img-status" id="statusFundo" style="font-weight:normal; margin-left:8px; color:#999;">nenhum enviado</span>
                <button type="button" id="btnVerFundo" class="btn btn-xs btn-default" style="display:none; margin-left:8px; font-weight:normal;">
                    <i class="fa fa-eye"></i> Ver fundo enviado
                </button>
            </label>
            <div class="input-group">
                <input type="file" id="arqFundo" accept=".pdf,image/*" class="form-control">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default" onclick="uploadFundo()"><i class="fa fa-upload"></i> Enviar fundo</button>
                </span>
            </div>
            <small class="text-muted">O PDF deve ser <b>A4 paisagem, 1 p&aacute;gina</b>. O texto do programa &eacute; escrito por cima; use as margens para encaix&aacute;-lo na &aacute;rea livre da arte.</small>
        </div>

        <!-- ============================================================
             Os 3 blocos abaixo espelham a pagina, de cima para baixo:
             TITULO (faixa) -> CONTEUDO (corpo) -> RODAPE (faixa).
             Cada bloco carrega SOMENTE os controles que afetam ele.
             ============================================================ -->

        <!-- ---------- 1. TITULO (faixa superior) ---------- -->
        <div style="border:1px solid #e3ebf3; border-left:4px solid #4a90d9; border-radius:8px; padding:12px 14px; margin-bottom:12px;">
            <div style="font-weight:600; color:#2c5f8d; margin-bottom:8px;">
                1. T&iacute;tulo &mdash; faixa no topo da p&aacute;gina
                <span style="font-weight:normal; color:#888; font-size:12px;">(opcional: vazio = n&atilde;o sai no PDF)</span>
            </div>
            <div class="row">
                <div class="col-sm-5 form-group mb-2">
                    <label for="titulo">Texto do t&iacute;tulo:</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ex.: Curso de Tratamento de Feridas...">
                </div>
                <div class="col-sm-2 form-group mb-2">
                    <label for="fonteTitulo">Fonte (pt):</label>
                    <input type="number" id="fonteTitulo" name="fonteTitulo" class="form-control" value="14" min="6" max="60">
                </div>
                <div class="col-sm-5 form-group mb-2">
                    <label for="margemTopo">Margem superior (mm):</label>
                    <input type="number" id="margemTopo" name="margemTopo" class="form-control" value="15" min="0" max="190">
                </div>
            </div>
            <small class="text-muted">
                Centralizado em negrito, ocupando a <b>linha toda</b> (nunca entra nas colunas).
                A margem &eacute; a dist&acirc;ncia da <b>borda de cima da p&aacute;gina</b> &mdash; o mesmo n&uacute;mero que voc&ecirc; l&ecirc; na <b>R&eacute;gua (mm)</b>.
            </small>
        </div>

        <!-- ---------- 2. CONTEUDO (corpo) ---------- -->
        <div style="border:1px solid #e3ebf3; border-left:4px solid #5cb85c; border-radius:8px; padding:12px 14px; margin-bottom:12px;">
            <div style="font-weight:600; color:#3d7a3d; margin-bottom:8px;">
                2. Conte&uacute;do &mdash; corpo da p&aacute;gina
                <span style="font-weight:normal; color:#888; font-size:12px;">(recebe a altura que sobra entre as duas faixas)</span>
            </div>
            <div class="row">
                <div class="col-sm-3 form-group mb-2">
                    <label for="fonteTamanho">Fonte (pt):</label>
                    <input type="number" id="fonteTamanho" name="fonteTamanho" class="form-control" value="10" min="6" max="40">
                </div>
                <div class="col-sm-3 form-group mb-2">
                    <label for="numColunas">Colunas:</label>
                    <select name="numColunas" id="numColunas" class="form-control">
                        <option value="1">1 coluna</option>
                        <option value="2">2 colunas</option>
                        <option value="3">3 colunas</option>
                    </select>
                </div>
                <div class="col-sm-3 form-group mb-2">
                    <label for="margemLateral">Margem lateral (mm):</label>
                    <input type="number" id="margemLateral" name="margemLateral" class="form-control" value="15" min="0" max="120">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 form-group mb-2">
                    <label for="margemConteudoTopo">Margem superior (mm):</label>
                    <input type="number" id="margemConteudoTopo" name="margemConteudoTopo" class="form-control" value="25" min="0" max="190">
                </div>
                <div class="col-sm-3 form-group mb-2">
                    <label for="margemConteudoBaixo">Margem inferior (mm):</label>
                    <input type="number" id="margemConteudoBaixo" name="margemConteudoBaixo" class="form-control" value="25" min="0" max="190">
                </div>
                <div class="col-sm-6 form-group mb-2">
                    <label style="visibility:hidden;">.</label>
                    <div id="alturaConteudo" class="form-control" style="background:#f6f9fc; border-style:dashed; color:#3d7a3d;"></div>
                </div>
            </div>
            <small class="text-muted" style="display:block; margin-bottom:8px;">
                Dist&acirc;ncias medidas das <b>bordas da p&aacute;gina</b> (A4 paisagem = 210mm de altura) &mdash; os mesmos n&uacute;meros da <b>R&eacute;gua (mm)</b>.
                Se o conte&uacute;do n&atilde;o couber em 1 p&aacute;gina, o sistema recusa a gera&ccedil;&atilde;o &mdash; reduza a fonte, use mais colunas ou diminua estas margens.
            </small>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <label style="margin:0; font-weight:600;">Programa:</label>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-default" data-cmd="bold" title="Negrito"><b>N</b></button>
                    <button type="button" class="btn btn-sm btn-default" data-cmd="italic" title="It&aacute;lico"><i>I</i></button>
                    <button type="button" class="btn btn-sm btn-default" data-cmd="underline" title="Sublinhado"><u>S</u></button>
                    <button type="button" class="btn btn-sm btn-default" data-cmd="insertUnorderedList" title="Lista"><i class="fa fa-list-ul"></i></button>
                    <button type="button" class="btn btn-sm btn-default" data-cmd="removeFormat" title="Limpar formata&ccedil;&atilde;o"><i class="fa fa-eraser"></i></button>
                    <button type="button" id="btnToggleHtml" class="btn btn-sm btn-default" title="Alternar edi&ccedil;&atilde;o de HTML"><i class="fa fa-code"></i> Editar HTML</button>
                </div>
            </div>
            <div id="vpEditor" contenteditable="true"
                 style="min-height:360px; border:1px solid #ccc; border-radius:6px; padding:14px; background:#fff; overflow:auto; font-size:14px; line-height:1.5;">
            </div>
            <textarea id="vpHtml" class="form-control" spellcheck="false"
                      style="display:none; min-height:360px; font-family:Consolas,monospace; font-size:13px; line-height:1.5;"></textarea>
            <small class="text-muted">Dica: voc&ecirc; pode <b>colar (Ctrl+V) direto do Word</b> que a formata&ccedil;&atilde;o &eacute; mantida.</small>
        </div>

        <!-- ---------- 3. RODAPE (faixa inferior) ---------- -->
        <div style="border:1px solid #e3ebf3; border-left:4px solid #d9a441; border-radius:8px; padding:12px 14px; margin-bottom:12px;">
            <div style="font-weight:600; color:#8d6a2c; margin-bottom:8px;">
                3. Rodap&eacute; &mdash; faixa no p&eacute; da p&aacute;gina
                <span style="font-weight:normal; color:#888; font-size:12px;">(opcional: vazio = n&atilde;o sai no PDF)</span>
            </div>
            <div class="row">
                <div class="col-sm-5 form-group mb-2">
                    <label for="rodape">Texto do rodap&eacute;:</label>
                    <input type="text" id="rodape" name="rodape" class="form-control" placeholder="Ex.: Carga hor&aacute;ria total: 16 horas">
                </div>
                <div class="col-sm-2 form-group mb-2">
                    <label for="fonteRodape">Fonte (pt):</label>
                    <input type="number" id="fonteRodape" name="fonteRodape" class="form-control" value="9" min="6" max="40">
                </div>
                <div class="col-sm-5 form-group mb-2">
                    <label for="margemBaixo">Margem inferior (mm):</label>
                    <input type="number" id="margemBaixo" name="margemBaixo" class="form-control" value="15" min="0" max="190">
                </div>
            </div>
            <small class="text-muted">
                Centralizado, ocupando a <b>linha toda</b> (nunca entra nas colunas).
                A margem &eacute; a dist&acirc;ncia da <b>borda de baixo da p&aacute;gina</b> &mdash; o mesmo n&uacute;mero que voc&ecirc; l&ecirc; na <b>R&eacute;gua (mm)</b>.
            </small>
        </div>

        <div id="carregando" class="hidden">
            <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border:0;"> Carregando...
        </div>

        <div style="display:flex; gap:8px; margin-top:14px;">
            <button type="button" id="btnVisualizar" class="btn btn-default"><i class="fa fa-eye"></i> Visualizar</button>
            <button type="button" id="btnRegua" class="btn btn-default" title="Abre um PDF com grade em mm por cima da arte, mostrando onde cada faixa esta caindo"><i class="fa fa-arrows-v"></i> R&eacute;gua (mm)</button>
            <button type="button" id="btnSalvar" class="btn btn-default"><i class="fa fa-save"></i> Salvar rascunho</button>
            <button type="button" id="btnGerar" class="btn abriModal"><i class="fa fa-file-pdf-o"></i> Gerar verso (PDF)</button>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {

    // Toolbar de formatacao (execCommand simples, sem dependencia externa)
    $('.btn-group [data-cmd]').on('click', function () {
        document.execCommand($(this).data('cmd'), false, null);
        $('#vpEditor').focus();
    });

    // Alterna entre editor visual e HTML cru
    var modoHtml = false;
    $('#btnToggleHtml').on('click', function () {
        modoHtml = !modoHtml;
        if (modoHtml) {
            $('#vpHtml').val($('#vpEditor').html()).show();
            $('#vpEditor').hide();
            $(this).addClass('active');
        } else {
            $('#vpEditor').html($('#vpHtml').val()).show();
            $('#vpHtml').hide();
            $(this).removeClass('active');
        }
    });

    function conteudoAtual() {
        return modoHtml ? $('#vpHtml').val() : $('#vpEditor').html();
    }

    function coletarDados(metodo) {
        return {
            'c': 'GerenciarVersoPrograma', 'm': metodo, '_submit_check': '1',
            'codOficina': $('#codOficina').val(),
            'titulo': $('#titulo').val(),
            'rodape': $('#rodape').val(),
            'conteudoHtml': conteudoAtual(),
            'modoFundo': $('#modoFundo').val(),
            'fonteTamanho': $('#fonteTamanho').val(),
            'fonteTitulo': $('#fonteTitulo').val(),
            'fonteRodape': $('#fonteRodape').val(),
            'margemConteudoTopo': $('#margemConteudoTopo').val(),
            'margemConteudoBaixo': $('#margemConteudoBaixo').val(),
            'numColunas': $('#numColunas').val(),
            'margemTopo': $('#margemTopo').val(),
            'margemBaixo': $('#margemBaixo').val(),
            'margemLateral': $('#margemLateral').val()
        };
    }

    // Mostra/oculta a caixa de upload do fundo conforme o modo
    function atualizarBoxesFundo() {
        $('#boxFundoCompleto').toggle($('#modoFundo').val() === 'completo');
    }
    $('#modoFundo').on('change', atualizarBoxesFundo);

    // Altura disponivel para o conteudo, ao vivo. Como as margens agora sao
    // absolutas, e uma subtracao simples — e o usuario ve na hora quando um
    // valor alto demais come a pagina (foi assim que um 90mm passou batido).
    function atualizarAlturaConteudo() {
        var A = 210; // A4 paisagem
        var topo = parseInt($('#margemConteudoTopo').val(), 10) || 0;
        var base = parseInt($('#margemConteudoBaixo').val(), 10) || 0;
        var h = A - topo - base;
        var $c = $('#alturaConteudo');
        if (h <= 0) {
            $c.html('&#9888; margens maiores que a p&aacute;gina').css('color', '#c00');
        } else {
            $c.html('&#8597; <b>' + h + 'mm</b> de altura (de ' + topo + 'mm a ' + (A - base) + 'mm)')
              .css('color', h < 80 ? '#c00' : '#3d7a3d');
        }
    }
    $('#margemConteudoTopo, #margemConteudoBaixo').on('input change', atualizarAlturaConteudo);


    // Carrega o programa salvo ao trocar de escopo (e no load)
    function carregar() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarVersoPrograma', 'm': 'carregar', '_submit_check': '1', 'codOficina': $('#codOficina').val()},
            function (data) {
                $('#titulo').val(data.titulo || '');
                $('#rodape').val(data.rodape || '');
                $('#vpEditor').html(data.conteudoHtml || '');
                $('#vpHtml').val(data.conteudoHtml || '');
                $('#modoFundo').val(data.modoFundo || 'branco');
                $('#numColunas').val(data.numColunas || 1);
                $('#fonteTamanho').val(data.fonteTamanho || 10);
                $('#fonteTitulo').val(data.fonteTitulo || 14);
                $('#fonteRodape').val(data.fonteRodape || 9);
                $('#margemConteudoTopo').val(data.margemConteudoTopo || 25);
                $('#margemConteudoBaixo').val(data.margemConteudoBaixo || 25);
                $('#margemTopo').val(data.margemTopo || 15);
                $('#margemBaixo').val(data.margemBaixo || 15);
                $('#margemLateral').val(data.margemLateral || 15);
                // status do fundo (PDF tem prioridade)
                var $s = $('#statusFundo');
                if (data.temFundoPdf) { $s.text('✓ PDF enviado').css('color', '#2e7d32'); }
                else if (data.temFundoImg) { $s.text('✓ imagem enviada').css('color', '#2e7d32'); }
                else { $s.text('nenhum enviado').css('color', '#999'); }
                // So faz sentido oferecer "ver" quando ha algo enviado.
                $('#btnVerFundo').toggle(!!(data.temFundoPdf || data.temFundoImg));
                atualizarBoxesFundo();
                atualizarAlturaConteudo();
            }, 'json'
        );
    }
    $('#codOficina').on('change', carregar);

    // Escopo pre-selecionado pela URL (?codOficina=X), quando o gerador e
    // aberto pelo botao "Gerar" da tela Artes & Assinaturas. So aplica se a
    // opcao existir no select (a atividade pertence ao evento).
    <?php $codOfIni = isset($fwsGet_['codOficina']) ? (int) $fwsGet_['codOficina'] : (isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0); ?>
    var codOficinaIni = <?php echo (int) $codOfIni; ?>;
    if (codOficinaIni > 0 && $('#codOficina option[value="' + codOficinaIni + '"]').length) {
        $('#codOficina').val(String(codOficinaIni));
    }
    carregar();

    // Upload do fundo: PDF -> slot PDF; imagem -> slot imagem (escolhe por extensao)
    window.uploadFundo = function () {
        var input = document.getElementById('arqFundo');
        if (!input.files || !input.files.length) {
            loading('div#erro', 'show');
            $('div#erro').html('Selecione um arquivo (PDF A4 ou imagem) primeiro.');
            return;
        }
        var nome = input.files[0].name.toLowerCase();
        var slot = nome.endsWith('.pdf') ? 'CERTIFICADO-VERSO-FUNDO-PDF' : 'CERTIFICADO-VERSO-FUNDO';

        var fd = new FormData();
        fd.append('c', 'GerenciarMidiaUpload');
        fd.append('m', 'salvarMidiaUpload');
        fd.append('_submit_check', '1');
        fd.append('novoCodArquivo', '0');
        fd.append('codArquivo', '');
        fd.append('localMidia', slot);
        fd.append('codPerfil', '');
        fd.append('codOficina', '');
        fd.append('arquivo', input.files[0]);

        startLoading();
        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: fd, processData: false, contentType: false, dataType: 'json', cache: false,
            success: function (data) {
                processJson(data);
                if (data.statusMessage) {
                    var isPdf = slot.indexOf('PDF') > -1;
                    $('#statusFundo').text(isPdf ? '✓ PDF enviado' : '✓ imagem enviada').css('color', '#2e7d32');
                    $('#btnVerFundo').show();
                    $('#arqFundo').val('');
                }
            },
            error: function () {
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao enviar o fundo. Verifique o console (F12).');
            },
            complete: function () { stopLoading(); }
        });
    };

    // Abre um PDF numa nova aba (POST com target _blank). Usado pelo Visualizar
    // e pela Regua — os dois devolvem PDF inline em vez de JSON.
    function abrirPdfEmNovaAba(metodo) {
        var d = coletarDados(metodo);
        var $f = $('<form>', {method: 'post', action: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), target: '_blank'});
        $.each(d, function (k, v) { $f.append($('<textarea>', {name: k, style: 'display:none'}).val(v)); });
        $f.appendTo('body').submit().remove();
    }

    $('#btnVisualizar').on('click', function () { abrirPdfEmNovaAba('visualizar'); });
    $('#btnRegua').on('click', function () { abrirPdfEmNovaAba('regua'); });
    $('#btnVerFundo').on('click', function () { abrirPdfEmNovaAba('verFundo'); });

    // Salvar rascunho
    $('#btnSalvar').on('click', function () {
        startLoading();
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), coletarDados('salvar'),
            function (data) { processJson(data); }, 'json'
        ).always(function () { stopLoading(); });
    });

    // Gerar verso
    $('#btnGerar').on('click', function () {
        startLoading();
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), coletarDados('gerar'),
            function (data) { processJson(data); }, 'json'
        ).always(function () { stopLoading(); });
    });
});
</script>
