<?php
// Gate de seguranca da view (mesmo padrao das demais telas de certificado).
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$debug = isset($debug) ? $debug : false;

// Arte de fundo da frente: a MESMA que o certificado ja usa hoje. O editor nao
// troca a arte -- ele posiciona os blocos sobre ela.
$urlArte = 'view/certificados/modelos/img/certificado-frente-unica.php';
?>
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_configuracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_certificados.php");
?>

<style>
    /* A folha e sempre A4 paisagem (297x210mm). A escala converte mm -> px na
       tela; o PDF continua em mm, entao o que se ve aqui e proporcional. */
    #folha {
        position: relative;
        background: #fff no-repeat center center;
        background-size: 100% 100%;
        border: 1px solid #ccc;
        box-shadow: 0 2px 10px rgba(0,0,0,.15);
        margin: 0 auto;
    }
    .bloco-edit {
        position: absolute;
        border: 1px dashed rgba(0,0,0,.45);
        background: rgba(46,125,50,.07);
        cursor: move;
        font-size: 11px;
        color: #1b5e20;
        overflow: hidden;
        user-select: none;
    }
    .bloco-edit.sel { border-color: #2e7d32; border-style: solid; background: rgba(46,125,50,.18); }
    .bloco-edit b { background: rgba(255,255,255,.75); padding: 0 3px; }
    .campo-mm { width: 70px; }
    .linha-campo { margin-bottom: 6px; }
    .linha-campo label { font-weight: normal; font-size: 12px; color: #555; display: block; margin: 0; }
</style>

<div class="panel panel-default" style="margin-top:10px;">
    <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h4 style="margin:0 0 3px;">Modelo da frente do certificado</h4>
            <small class="text-muted">
                Arraste os blocos sobre a arte para posicionar. O <b>Visualizar</b> gera o certificado
                pelo mesmo caminho da emiss&atilde;o real &mdash; &eacute; o que vai sair de verdade.
            </small>
        </div>
        <div>
            <select id="selModelo" class="form-control input-sm" style="width:240px; display:inline-block;"></select>
            <button type="button" id="btnNovo" class="btn btn-sm btn-default"><i class="fa fa-plus"></i> Novo</button>
        </div>
    </div>

    <div class="panel-body">
        <div class="row">
            <!-- ---------------- Sem modelo: estado simples (sem posicionamento) ---------------- -->
            <div class="col-sm-12" id="semModeloPanel" style="display:none;">
                <div style="background:#fdf6e8; border:1px solid #f0e0b8; border-radius:8px; padding:16px 18px;">
                    <div style="font-weight:600; color:#8a6d1f; margin-bottom:4px;">
                        <i class="fa fa-info-circle"></i> Este evento usa o <b>layout antigo</b> (padr&atilde;o do sistema)
                    </div>
                    <p class="text-muted" style="font-size:13px; margin:6px 0 12px;">
                        Nenhum modelo customizado foi definido &mdash; o certificado sai como sempre saiu, sem
                        posi&ccedil;&otilde;es configur&aacute;veis. S&oacute; crie um modelo se precisar mudar o layout.
                    </p>
                    <button type="button" id="btnCriarModelo" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Criar modelo customizado</button>
                </div>
            </div>

            <div id="editorModelo" style="display:contents;">
            <!-- ---------------- Editor visual ---------------- -->
            <div class="col-sm-8">
                <div id="folha"></div>
                <p class="text-muted" style="margin-top:8px; font-size:12px;">
                    P&aacute;gina A4 paisagem: 297mm &times; 210mm. As posi&ccedil;&otilde;es s&atilde;o medidas
                    em mil&iacute;metros a partir da borda da p&aacute;gina.
                </p>
            </div>

            <!-- ---------------- Campos ---------------- -->
            <div class="col-sm-4">
                <input type="hidden" id="cod_certificado_modelo" value="0">

                <div class="linha-campo">
                    <label>Nome do modelo</label>
                    <input type="text" id="titulo" class="form-control input-sm" maxlength="100" placeholder="ex.: Padr&atilde;o 2026">
                </div>

                <div id="painelCampos"></div>

                <hr>
                <button type="button" id="btnSalvar" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Salvar</button>
                <button type="button" id="btnVisualizar" class="btn btn-default btn-sm"><i class="fa fa-file-pdf-o"></i> Visualizar</button>
                <button type="button" id="btnExcluir" class="btn btn-danger btn-sm pull-right"><i class="fa fa-trash"></i></button>

                <hr>
                <div style="background:#f6f8fa; border:1px solid #e3e8ee; border-radius:6px; padding:10px;">
                    <div style="font-weight:600; font-size:13px; margin-bottom:6px;">Padr&atilde;o do evento</div>
                    <p class="text-muted" style="font-size:12px; margin-bottom:8px;">
                        Enquanto nenhum modelo for o padr&atilde;o, o certificado sai no <b>layout antigo</b>,
                        exatamente como hoje.
                    </p>
                    <div id="statusPadrao" style="font-size:12px; margin-bottom:8px;"></div>
                    <button type="button" id="btnDefinirPadrao" class="btn btn-success btn-xs">Usar este como padr&atilde;o</button>
                    <button type="button" id="btnLimparPadrao" class="btn btn-default btn-xs">Voltar ao layout antigo</button>
                </div>
            </div>
            </div><!-- /editorModelo -->
        </div>
    </div>
</div>

<form id="formPreview" method="post" target="_blank" action="<?php echo WB_URL_COMMAND ?>">
    <input type="hidden" name="c" value="GerenciarCertificadoModelo">
    <input type="hidden" name="m" value="visualizar">
    <input type="hidden" name="_submit_check" value="1">
    <div id="camposPreview"></div>
</form>

<script type="text/javascript">
$(document).ready(function () {

    var LARGURA_MM = 297, ALTURA_MM = 210;
    // Escala (px por mm) tirada da largura real da coluna: com valor fixo a
    // folha invadia o painel de campos em telas menores.
    var ESCALA = Math.max(1.2, ($('#folha').parent().width() - 4) / LARGURA_MM);
    var URL_ARTE = '<?php echo $urlArte ?>';

    // Cada bloco: campo-base no banco + rotulo + se tem altura de fonte propria.
    var BLOCOS = [
        {id:'texto', nome:'Texto do certificado', altura:60, fonte:true,  linha:true, alinha:true},
        {id:'ass1',  nome:'Assinatura 1',          altura:20},
        {id:'ass2',  nome:'Assinatura 2',          altura:20},
        {id:'selo',  nome:'Selo / logo',           altura:20},
        {id:'qr',    nome:'QR code',               altura:null},   // quadrado: altura = largura
        {id:'data',  nome:'Data de emiss&atilde;o',      altura:8,  fonte:true}
    ];

    var dados = {};        // estado atual do formulario
    var padraoEvento = 0;
    // Controla qual bloco aparece: o editor de posicoes (canvas + campos) so
    // faz sentido quando ha um modelo em edicao. Enquanto o evento usa o
    // layout antigo (sem modelo padrao) e nada foi selecionado, mostra so o
    // resumo em #semModeloPanel -- nao ha posicao nenhuma pra configurar.
    var editando = false;

    function atualizarVisibilidade() {
        // 'contents' (nao 'block'): os filhos precisam continuar irmaos
        // diretos de .row para o grid Bootstrap (col-sm-8/col-sm-4) funcionar.
        $('#editorModelo').css('display', editando ? 'contents' : 'none');
        $('#semModeloPanel').toggle(!editando);
    }

    /* ---------- montagem da tela ---------- */

    $('#folha').css({
        width:  (LARGURA_MM * ESCALA) + 'px',
        height: (ALTURA_MM  * ESCALA) + 'px',
        backgroundImage: "url('" + URL_ARTE + "')"
    });

    function alturaBloco(b) {
        // QR e quadrado; os demais tem altura fixa so para dar area de arraste.
        return b.altura === null ? dados[b.id + '_largura'] : b.altura;
    }

    function desenharBlocos() {
        $('#folha').empty();
        $.each(BLOCOS, function (_, b) {
            var $d = $('<div class="bloco-edit" data-bloco="' + b.id + '"><b>' + b.nome + '</b></div>');
            $d.css({
                left:   (dados[b.id + '_esquerda'] * ESCALA) + 'px',
                top:    (dados[b.id + '_topo']     * ESCALA) + 'px',
                width:  (dados[b.id + '_largura']  * ESCALA) + 'px',
                height: (alturaBloco(b)            * ESCALA) + 'px'
            });
            $('#folha').append($d);
        });
        ativarArraste();
    }

    function montarCampos() {
        var html = '';
        $.each(BLOCOS, function (_, b) {
            html += '<div style="border-top:1px solid #eee; padding-top:8px; margin-top:8px;">';
            html += '<div style="font-weight:600; font-size:12px; margin-bottom:4px;">' + b.nome + '</div>';
            html += '<div style="display:flex; gap:6px; flex-wrap:wrap;">';
            html += campoMm(b.id + '_topo',     'Topo (mm)');
            html += campoMm(b.id + '_esquerda', 'Esquerda (mm)');
            html += campoMm(b.id + '_largura',  'Largura (mm)');
            if (b.fonte) { html += campoMm(b.id + '_fonte_tamanho', 'Fonte (pt)'); }
            if (b.linha) { html += campoMm(b.id + '_altura_linha',  'Entrelinha (pt)'); }
            html += '</div>';
            if (b.alinha) {
                html += '<div class="linha-campo" style="margin-top:6px;"><label>Alinhamento</label>'
                     +  '<select id="texto_alinhamento" class="form-control input-sm" style="width:120px;">'
                     +  '<option value="left">Esquerda</option><option value="center">Centro</option>'
                     +  '<option value="right">Direita</option></select></div>';
            }
            html += '</div>';
        });
        $('#painelCampos').html(html);

        $('#painelCampos').on('change keyup', 'input,select', function () {
            dados[$(this).attr('id')] = $(this).val();
            desenharBlocos();
        });
    }

    function campoMm(id, rotulo) {
        return '<div class="linha-campo"><label>' + rotulo + '</label>'
             + '<input type="number" id="' + id + '" class="form-control input-sm campo-mm"></div>';
    }

    function preencherCampos() {
        $.each(dados, function (k, v) { $('#' + k).val(v); });
        $('#titulo').val(dados.titulo || '');
        $('#cod_certificado_modelo').val(dados.cod_certificado_modelo || 0);
    }

    /* ---------- arrastar ---------- */

    function ativarArraste() {
        $('.bloco-edit').on('mousedown', function (e) {
            e.preventDefault();
            var $b = $(this), id = $b.data('bloco');
            $('.bloco-edit').removeClass('sel'); $b.addClass('sel');

            var x0 = e.pageX, y0 = e.pageY;
            var e0 = parseFloat(dados[id + '_esquerda']), t0 = parseFloat(dados[id + '_topo']);

            $(document).on('mousemove.arr', function (ev) {
                // Converte o deslocamento de px de volta para mm e trava na pagina.
                var nx = Math.round(e0 + (ev.pageX - x0) / ESCALA);
                var ny = Math.round(t0 + (ev.pageY - y0) / ESCALA);
                nx = Math.max(0, Math.min(LARGURA_MM, nx));
                ny = Math.max(0, Math.min(ALTURA_MM,  ny));
                dados[id + '_esquerda'] = nx;
                dados[id + '_topo']     = ny;
                $('#' + id + '_esquerda').val(nx);
                $('#' + id + '_topo').val(ny);
                $b.css({left: (nx * ESCALA) + 'px', top: (ny * ESCALA) + 'px'});
            });
            $(document).on('mouseup.arr', function () { $(document).off('.arr'); });
        });
    }

    /* ---------- servidor ---------- */

    function post(metodo, extra, cb) {
        var d = $.extend({'c':'GerenciarCertificadoModelo', 'm':metodo, '_submit_check':'1'}, extra || {});
        startLoading && startLoading();
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), d, cb, 'json')
            .always(function () { stopLoading && stopLoading(); });
    }

    function carregarLista(selecionar) {
        post('listar', {}, function (r) {
            padraoEvento = r.padrao || 0;
            var opts = '<option value="0">-- novo modelo --</option>';
            $.each(r.itens || [], function (_, m) {
                opts += '<option value="' + m.cod + '">' + m.titulo + (m.cod === padraoEvento ? ' (padr&atilde;o)' : '') + '</option>';
            });
            $('#selModelo').html(opts);
            // Sem selecao explicita: abre direto no modelo padrao do evento,
            // se houver -- a tela passa a refletir o layout realmente em uso,
            // em vez de sempre abrir num rascunho em branco.
            if (!selecionar && padraoEvento > 0) { selecionar = padraoEvento; }
            if (selecionar) { $('#selModelo').val(selecionar); }
            editando = ($('#selModelo').val() != '0');
            carregarModelo($('#selModelo').val());
            mostrarPadrao();
            atualizarVisibilidade();
        });
    }

    function mostrarPadrao() {
        $('#statusPadrao').html(padraoEvento > 0
            ? '<span style="color:#2e7d32;">Este evento usa o modelo <b>#' + padraoEvento + '</b>.</span>'
            : '<span style="color:#a06000;">Nenhum modelo definido &mdash; usando o layout antigo.</span>');
    }

    function carregarModelo(cod) {
        post('carregar', {'cod_certificado_modelo': cod || 0}, function (r) {
            dados = r;
            preencherCampos();
            desenharBlocos();
        });
    }

    function coletar() {
        var d = $.extend({}, dados);
        d.titulo = $('#titulo').val();
        d.cod_certificado_modelo = $('#cod_certificado_modelo').val();
        return d;
    }

    /* ---------- botoes ---------- */

    $('#selModelo').on('change', function () {
        editando = ($(this).val() != '0');
        carregarModelo($(this).val());
        atualizarVisibilidade();
    });
    $('#btnNovo').on('click', function () {
        $('#selModelo').val(0);
        editando = true;
        carregarModelo(0);
        atualizarVisibilidade();
    });
    $('#btnCriarModelo').on('click', function () {
        // Mesma acao do "Novo": abre o editor com um rascunho em branco.
        $('#selModelo').val(0);
        editando = true;
        carregarModelo(0);
        atualizarVisibilidade();
    });

    $('#btnSalvar').on('click', function () {
        post('salvar', coletar(), function (r) {
            processJson && processJson(r);
            if (r.statusMessage) { carregarLista(r.cod_certificado_modelo); }
        });
    });

    $('#btnExcluir').on('click', function () {
        var cod = $('#cod_certificado_modelo').val();
        if (!cod || cod == 0) { return; }
        if (!confirm('Excluir este modelo? Os eventos que apontam para ele voltam ao layout antigo.')) { return; }
        post('excluir', {'cod_certificado_modelo': cod}, function (r) {
            processJson && processJson(r);
            if (r.statusMessage) { carregarLista(0); }
        });
    });

    $('#btnDefinirPadrao').on('click', function () {
        var cod = $('#cod_certificado_modelo').val();
        if (!cod || cod == 0) { alert('Salve o modelo antes de defini-lo como padrao.'); return; }
        post('definirPadraoEvento', {'cod_certificado_modelo': cod}, function (r) {
            processJson && processJson(r);
            if (r.statusMessage) { carregarLista(cod); }
        });
    });

    $('#btnLimparPadrao').on('click', function () {
        post('definirPadraoEvento', {'cod_certificado_modelo': 0}, function (r) {
            processJson && processJson(r);
            if (r.statusMessage) { carregarLista($('#selModelo').val()); }
        });
    });

    // Visualizar: manda o estado ATUAL do formulario (sem precisar salvar) por
    // POST em aba nova, e o servidor devolve o PDF.
    $('#btnVisualizar').on('click', function () {
        var d = coletar(), html = '';
        $.each(d, function (k, v) {
            html += '<input type="hidden" name="' + k + '" value="' + String(v).replace(/"/g, '&quot;') + '">';
        });
        $('#camposPreview').html(html);
        $('#formPreview').submit();
    });

    montarCampos();     // os campos existem antes de qualquer carregamento
    carregarLista(0);
});
</script>
