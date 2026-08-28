/**
 * Lógica de suporte à geração de N mensalidades em lote.
 * - Controla visibilidade do bloco #divLoteMensalidade
 * - Botões de inserir tag ({MES}, {MES_NUM}, {ANO}, {ANO2}) no padrão da referência
 * - Preview ao vivo da 1ª, 2ª e última parcela (venc. + referência + numeração)
 *
 * Depende de elementos existentes em CarregarComponentesAC::campoAcoes():
 *   #divSelectTaxas, #tipoPedido, #datVencimento, #prefixoPedido, #valorPedido,
 *   select#acaoEmLote (fallback: input[name="acaoEmLote"])
 */
(function () {
    'use strict';

    var MESES = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril',
        'Maio', 'Junho', 'Julho', 'Agosto',
        'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];

    function substituirTags(template, data) {
        if (!template || !data) return template || '';
        var mesIdx  = data.getMonth();
        var mes     = MESES[mesIdx];
        var mesNum  = (mesIdx + 1 < 10 ? '0' : '') + (mesIdx + 1);
        var ano     = data.getFullYear();
        var ano2    = String(ano).slice(-2);
        return template
            .replace(/\{MES_NUM\}/g, mesNum)
            .replace(/\{MES\}/g,     mes)
            .replace(/\{ANO2\}/g,    ano2)
            .replace(/\{ANO\}/g,     ano);
    }

    function parseDataInput(valor) {
        if (!valor) return null;
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(valor);
        if (!m) return null;
        return new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
    }

    function addMeses(data, n) {
        var d = new Date(data.getTime());
        var diaOriginal = d.getDate();
        d.setDate(1);
        d.setMonth(d.getMonth() + n);
        var ultimoDia = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
        d.setDate(Math.min(diaOriginal, ultimoDia));
        return d;
    }

    function formatarDataBR(d) {
        var dd = (d.getDate() < 10 ? '0' : '') + d.getDate();
        var mm = (d.getMonth() + 1 < 10 ? '0' : '') + (d.getMonth() + 1);
        return dd + '/' + mm + '/' + d.getFullYear();
    }

    function escaparHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function atualizarPreview() {
        var $preview = $('#previewLoteMensalidade');
        if (!$preview.length) return;

        var qtd            = parseInt($('#qtdParcelasGerar').val(), 10);
        var parcelaInicial = parseInt($('#parcelaInicial').val(), 10);
        var total          = parseInt($('#totalParcelas').val(), 10);
        var referencia     = $('#referencia').val() || '';
        var prefixo        = $('#prefixoPedido').val() || '';
        var datVencBase    = parseDataInput($('#datVencimento').val());

        if (isNaN(qtd) || qtd < 1)                     qtd = 1;
        if (isNaN(parcelaInicial) || parcelaInicial < 1) parcelaInicial = 1;
        if (isNaN(total))                              total = 0;

        // Defaults leves quando qtd > 1: espelham o backend
        var referenciaEfetiva = referencia || (qtd > 1 ? '{MES}/{ANO}' : '');
        var prefixoEfetivo    = prefixo    || (qtd > 1 ? 'Mensalidade' : '');

        if (!datVencBase) {
            $preview.html('<span style="color:#999;">Preencha a <b>Data de Vencimento</b> para ver a prévia.</span>');
            return;
        }

        var indices = [];
        if (qtd === 1) {
            indices = [0];
        } else if (qtd === 2) {
            indices = [0, 1];
        } else if (qtd === 3) {
            indices = [0, 1, 2];
        } else {
            indices = [0, 1, qtd - 1];
        }

        var linhas = [];
        for (var k = 0; k < indices.length; k++) {
            var i          = indices[k];
            var numParcela = parcelaInicial + i;
            var datParcela = addMeses(datVencBase, i);
            var ref        = referenciaEfetiva ? substituirTags(referenciaEfetiva, datParcela) : '(sem referência)';
            var sufixoNum  = total > 0 ? (numParcela + '/' + total) : String(numParcela);
            var descPrev   = (prefixoEfetivo ? prefixoEfetivo + ' ' : '') + sufixoNum;
            linhas.push(
                '<div>' +
                    '<b>' + escaparHtml(descPrev) + '</b> ' +
                    '— Venc: <b>' + formatarDataBR(datParcela) + '</b> ' +
                    '— Ref: <b>' + escaparHtml(ref) + '</b>' +
                '</div>'
            );
        }

        var pularIntermediario = (qtd > 3) ? '<div style="color:#999;">... ' + (qtd - 2) + ' parcelas intermediárias ...</div>' : '';
        var titulo = '<b>Prévia — ' + qtd + ' parcela' + (qtd > 1 ? 's' : '') + ' a gerar:</b>';

        var html = titulo;
        if (qtd > 3) {
            html += linhas[0] + linhas[1] + pularIntermediario + linhas[2];
        } else {
            html += linhas.join('');
        }
        $preview.html(html);
    }

    function inserirTagNoInput(texto) {
        var el = document.getElementById('referencia');
        if (!el) return;
        var start = el.selectionStart || 0;
        var end   = el.selectionEnd   || 0;
        var valor = el.value || '';
        el.value = valor.substring(0, start) + texto + valor.substring(end);
        var nova = start + texto.length;
        try { el.setSelectionRange(nova, nova); } catch (e) { /* no-op */ }
        el.focus();
        $(el).trigger('input');
    }

    var REF_DEFAULT    = '{MES}/{ANO}';
    var PREFIX_DEFAULT = 'Mensalidade';

    // A acao em lote virou <select id="acaoEmLote"> (antes eram radios). Le o select
    // e mantem o fallback dos radios para nao quebrar se alguma tela ainda os usar.
    function acaoEmLoteAtual() {
        var $sel = $('#acaoEmLote');
        if ($sel.length) return $sel.val();
        return $('input[name="acaoEmLote"]:checked').val();
    }

    function controlarVisibilidade() {
        var $divs = $('#divLoteMensalidade, #divLoteMensalidadeDica');
        if (!$divs.length) return;
        var tipo = $('#tipoPedido').val();
        var acao = acaoEmLoteAtual();
        var $ref  = $('#referencia');
        var $pref = $('#prefixoPedido');
        var ativar = (acao === 'gerarMensalidade' && tipo === 'mensalidade');

        if (ativar) {
            $divs.show();
            // Pré-preenche de fato quando vazio; respeita edição manual do admin
            if ($ref.length && $ref.val() === '') {
                $ref.val(REF_DEFAULT).attr('data-auto-preenchido', '1');
            }
            if ($pref.length && $pref.val() === '') {
                $pref.val(PREFIX_DEFAULT).attr('data-auto-preenchido', '1');
            }
        } else {
            $divs.hide();
            // Se o admin não alterou o valor auto-preenchido, limpa ao sair do modo lote
            if ($ref.length && $ref.attr('data-auto-preenchido') === '1' && $ref.val() === REF_DEFAULT) {
                $ref.val('').removeAttr('data-auto-preenchido');
            }
            if ($pref.length && $pref.attr('data-auto-preenchido') === '1' && $pref.val() === PREFIX_DEFAULT) {
                $pref.val('').removeAttr('data-auto-preenchido');
            }
        }
    }

    $(document).ready(function () {
        if (!$('#divLoteMensalidade').length) return;

        $(document).on('click', '.btn-inserir-tag-referencia', function (e) {
            e.preventDefault();
            inserirTagNoInput($(this).data('tag'));
            atualizarPreview();
        });

        $(document).on('click', '#btnToggleDicaLote', function (e) {
            e.preventDefault();
            $('#textoDicaLoteMensalidade').toggle();
        });

        var camposMonitorados = [
            '#qtdParcelasGerar',
            '#parcelaInicial',
            '#totalParcelas',
            '#referencia',
            '#datVencimento',
            '#prefixoPedido'
        ].join(', ');

        $(document).on('input change', camposMonitorados, atualizarPreview);

        // Se o admin digita manualmente em #referencia ou #prefixoPedido,
        // marca como "não auto" para não ser limpo ao sair do modo lote
        $(document).on('input', '#referencia, #prefixoPedido', function () {
            $(this).removeAttr('data-auto-preenchido');
        });
        $(document).on('change', '#tipoPedido, #acaoEmLote, input[name="acaoEmLote"]', function () {
            controlarVisibilidade();
            atualizarPreview();
        });

        controlarVisibilidade();
        atualizarPreview();
    });
})();
