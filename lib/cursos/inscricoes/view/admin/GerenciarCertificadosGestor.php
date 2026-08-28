<?php
/**
 * GESTOR -> Certificados. Tela FORA do evento: lista e filtra por gestor o que
 * as views de certificado devolvem. Somente leitura.
 *
 *   aba Cobranca -> vw_certificados_cobranca (1 linha por evento)
 *   aba Emitidos -> vw_certificados_emitidos (1 linha por atividade)
 *
 * Acesso: ADM_GERAL, verificado de forma GERAL (cod_evento='TODOS'), porque a
 * tela nao pertence a nenhum evento.
 */
require_once(dirname(__FILE__) . "/../../include/config.php");

if (!FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

$tituloPagina = 'Certificados por Gestor';
$submenu = 'area_admin_menu_gestor.php';
require_once(dirname(__FILE__) . "/../../view/admin/cabecalho-geral.php");

// Gestores para o select -- leitura direta, a tela ja esta gateada acima.
$objCertDAO = new CertificadoGestorDAO();
$listaGestores = $objCertDAO->listarGestores();
?>

<style>
    .cg-aba { display:inline-block; padding:6px 18px; font-size:13px; color:#888; text-decoration:none;
              border:1px solid transparent; border-bottom:3px solid transparent; cursor:pointer; margin-right:2px; }
    .cg-aba:hover { color:#555; }
    .cg-aba.cg-aba-ativa { color:#333; font-weight:bold; border-bottom:3px solid #333; }
    .cg-card { flex:1; min-width:150px; border:1px solid #e3ebf3; border-radius:10px; padding:10px 14px; background:#f6faf7; }
    .cg-card-num { font-size:22px; font-weight:700; line-height:1; }
    .cg-card-lbl { font-size:12px; color:#555; margin-top:3px; }
    #cgTabela th { background:#fafafa; white-space:nowrap; }
    #cgTabela td, #cgTabela th { padding:7px 12px; }
    .cg-num { text-align:right; white-space:nowrap; }
    /* colunas informativas (contam documentos, nao entram na cobranca) */
    .cg-info { color:#8a8a8a; font-weight:normal; background:#fcfcfc; }
    /* cabecalho ordenavel */
    #cgTabela th.cg-sort { cursor:pointer; user-select:none; }
    #cgTabela th.cg-sort:hover { background:#f0f0f0; }
    #cgTabela th.cg-sort i.cg-seta { color:#bbb; margin-left:4px; }
    #cgTabela th.cg-sort.cg-sort-on i.cg-seta { color:#333; }
</style>

<div class="row" style="margin-bottom:10px;">
    <div class="col-sm-3">
        <label for="cg_gestor">Gestor:</label>
        <select id="cg_gestor" class="form-control input-sm">
            <option value="">-- Todos --</option>
            <?php foreach ($listaGestores as $g): ?>
            <option value="<?php echo htmlspecialchars($g) ?>"><?php echo htmlspecialchars($g) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-4">
        <label for="cg_busca">Evento (c&oacute;digo ou parte do t&iacute;tulo):</label>
        <input type="text" id="cg_busca" class="form-control input-sm" placeholder="ex.: 115 ou Qualifica&ccedil;&atilde;o">
    </div>
    <div class="col-sm-5" style="padding-top:24px;">
        <button type="button" class="btn btn-sm btn-primary" id="cgBuscar"><i class="fa fa-search"></i> Buscar</button>
        <button type="button" class="btn btn-sm btn-default" id="cgLimpar">Limpar</button>
        <button type="button" class="btn btn-sm btn-default" id="cgExportar" title="Baixar a listagem atual em CSV">
            <i class="fa fa-download"></i> Exportar CSV
        </button>
        <label style="font-weight:normal; margin-left:10px;" id="cgWrapSaldo">
            <input type="checkbox" id="cg_so_a_cobrar"> s&oacute; com saldo a cobrar
        </label>
    </div>
</div>

<div style="border-bottom:1px solid #ddd; margin-bottom:12px;">
    <a class="cg-aba cg-aba-ativa" data-aba="cobranca" href="javascript:void(0);"><i class="fa fa-usd"></i> Cobran&ccedil;a</a>
    <a class="cg-aba" data-aba="emitidos" href="javascript:void(0);"><i class="fa fa-certificate"></i> Emitidos</a>
    <span id="cgFiltroEvento" style="display:none; font-size:12px; color:#666; margin-left:10px;">
        filtrando o evento <b><span id="cgFiltroEventoNum"></span></b>
        <a href="javascript:void(0);" id="cgLimparEvento" title="Voltar a ver todos os eventos">(limpar)</a>
    </span>
</div>

<div id="cgCards" style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:12px;"></div>

<?php /* Regra da cobranca -- ver comentario no CertificadoGestorDAO. */ ?>
<div id="cgNota" class="alert alert-info" style="padding:8px 12px; font-size:12px; margin-bottom:12px;">
    <i class="fa fa-info-circle"></i>
    Cobran&ccedil;a <b>por pessoa</b>: quem tem direito a pelo menos um certificado no evento conta
    <b>uma vez</b>, mesmo recebendo v&aacute;rios. As colunas <b>Atividade</b>, <b>Geral</b> e <b>Perfil</b>
    contam <i>documentos</i> e s&atilde;o apenas informativas &mdash; por isso a soma delas n&atilde;o bate
    com a coluna Pessoas. Inscri&ccedil;&atilde;o cancelada n&atilde;o entra.
</div>

<div id="cgResultado"></div>

<script type="text/javascript">
$(document).ready(function () {
    var abaAtual = 'cobranca';       // cobranca | emitidos | pessoas
    var eventoDrill = 0;   // aba Emitidos filtrada por um evento (veio da Cobranca)
    var eventoPessoas = 0; // evento aberto no drill-down de PESSOAS

    function fmt(n) { return (n === null || n === undefined) ? '0' : String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    function esc(s) { return $('<div>').text(s === null || s === undefined ? '' : s).html(); }

    /* Cabecalho ordenavel. extra = classe adicional (cg-num / cg-info). */
    function th(rotulo, extra, dica) {
        return '<th class="cg-sort ' + (extra || '') + '"' + (dica ? ' title="' + dica + '"' : '') + '>'
             + rotulo + '<i class="fa fa-sort cg-seta"></i></th>';
    }

    function card(num, label, hint, cor) {
        return '<div class="cg-card">'
             + '<div class="cg-card-num" style="color:' + (cor || '#1e7e34') + ';">' + fmt(num) + '</div>'
             + '<div class="cg-card-lbl">' + label + '</div>'
             + (hint ? '<div style="font-size:11px; color:#999;">' + hint + '</div>' : '')
             + '</div>';
    }

    function atualizarChips() {
        // "so com saldo a cobrar" so faz sentido na aba Cobranca
        $('#cgWrapSaldo').toggle(abaAtual === 'cobranca');
        // gestor/busca nao se aplicam a lista de pessoas de UM evento
        $('#cgNota').toggle(abaAtual === 'cobranca');
        if (eventoDrill > 0 && abaAtual === 'emitidos') {
            $('#cgFiltroEventoNum').text(eventoDrill);
            $('#cgFiltroEvento').show();
        } else {
            $('#cgFiltroEvento').hide();
        }
    }

    function buscar() {
        atualizarChips();
        $('#cgResultado').html('<div class="alert alert-info">Buscando...</div>');
        $('#cgCards').html('');

        var metodo = 'listarCobranca';
        if (abaAtual === 'emitidos') { metodo = 'listarEmitidos'; }
        if (abaAtual === 'pessoas')  { metodo = 'listarPessoasEvento'; }

        var dados = {
            c: 'GerenciarCertificadosGestor',
            m: metodo,
            gestor: $('#cg_gestor').val(),
            busca: $.trim($('#cg_busca').val())
        };
        if (abaAtual === 'cobranca') {
            dados.somente_a_cobrar = $('#cg_so_a_cobrar').is(':checked') ? 1 : '';
        } else if (abaAtual === 'pessoas') {
            dados.cod_evento = eventoPessoas;
        } else if (eventoDrill > 0) {
            dados.cod_evento = eventoDrill;
        }

        $.ajax({
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            type: 'POST', data: dados, dataType: 'json',
            success: function (resp) {
                if (!resp || !resp.sucesso) {
                    $('#cgResultado').html('<div class="alert alert-warning">' + esc((resp && resp.mensagem) || 'Nada encontrado.') + '</div>');
                    return;
                }
                if (abaAtual === 'cobranca')      { renderCobranca(resp); }
                else if (abaAtual === 'pessoas')  { renderPessoas(resp); }
                else                              { renderEmitidos(resp); }
            },
            error: function () {
                $('#cgResultado').html('<div class="alert alert-danger">Erro ao consultar. Tente novamente.</div>');
            }
        });
    }

    function renderCobranca(resp) {
        $('#cgCards').html(
            card(resp.totais.pessoas, 'Pessoas com certificado', 'base da cobran&ccedil;a', '#1e7e34')
          + card(resp.totais.cobrados, 'J&aacute; cobradas', 'notas em fin_certificados_notafiscal', '#31708f')
          + card(resp.totais.a_cobrar, 'A cobrar', 'pessoas menos j&aacute; cobradas', resp.totais.a_cobrar > 0 ? '#a94442' : '#999')
          + card(resp.total, 'Eventos na lista', '', '#555')
        );

        if (!resp.registros.length) {
            $('#cgResultado').html('<div class="alert alert-warning">Nenhum evento com os filtros informados.</div>');
            return;
        }
        var h = '';
        if (resp.limitado) {
            h += '<div class="alert alert-warning">Exibindo as primeiras ' + fmt(resp.total) + ' linhas. Refine os filtros.</div>';
        }
        h += '<table id="cgTabela" class="table table-bordered table-striped table-hover" style="font-size:13px;">';
        h += '<thead><tr>'
           + th('Ev.') + th('T&iacute;tulo do evento') + th('Gestor') + th('Status')
           + th('Libera&ccedil;&atilde;o', '', 'A partir de quando o inscrito consegue emitir o certificado'
                + ' (certificado_dat_ini). N&atilde;o &eacute; a data impressa no certificado.')
           + th('Pessoas', 'cg-num', 'Base da cobran&ccedil;a: pessoas distintas com direito a certificado')
           + th('Atividade', 'cg-num cg-info', 'Informativo: documentos emitidos por atividade')
           + th('Geral', 'cg-num cg-info', 'Informativo: certificados gerais de participa&ccedil;&atilde;o')
           + th('Perfil', 'cg-num cg-info', 'Informativo: certificados emitidos por perfil')
           + th('Cobradas', 'cg-num') + th('A cobrar', 'cg-num')
           + '</tr></thead><tbody>';
        for (var i = 0; i < resp.registros.length; i++) {
            var r = resp.registros[i];
            h += '<tr>'
               + '<td>' + r.cod_evento + '</td>'
               // TODO evento e clicavel: abre as PESSOAS com direito a certificado -- serve
               // tambem para quem so emite geral/perfil (a aba Emitidos, por atividade,
               // nao ajudava nesses). Quem tem atividade ganha o atalho extra no icone.
               + '<td><a href="javascript:void(0);" class="cg-pessoas" data-evento="' + r.cod_evento + '"'
               + ' title="Ver as pessoas com direito a certificado neste evento">' + esc(r.titulo) + '</a>'
               + (r.atividade > 0
                    ? ' <a href="javascript:void(0);" class="cg-drill" data-evento="' + r.cod_evento + '"'
                      + ' title="Ver as atividades deste evento"><i class="fa fa-list-ul" style="color:#aaa;"></i></a>'
                    : '')
               + '</td>'
               + '<td>' + esc(r.gestor) + '</td>'
               + '<td>' + esc(r.status) + '</td>'
               + '<td style="white-space:nowrap;">' + (r.liberacao
                    ? esc(r.liberacao)
                    : '<span style="color:#c9a227;" title="Data de libera&ccedil;&atilde;o n&atilde;o configurada neste evento">'
                      + '<i class="fa fa-exclamation-triangle"></i> n&atilde;o definida</span>') + '</td>'
               + '<td class="cg-num" style="font-weight:700;">' + fmt(r.pessoas) + '</td>'
               + '<td class="cg-num cg-info">' + fmt(r.atividade) + '</td>'
               + '<td class="cg-num cg-info">' + fmt(r.geral) + '</td>'
               + '<td class="cg-num cg-info">' + fmt(r.perfil) + '</td>'
               + '<td class="cg-num">' + fmt(r.cobrados) + '</td>'
               + '<td class="cg-num" style="font-weight:700; color:' + (r.a_cobrar > 0 ? '#a94442' : '#999') + ';">' + fmt(r.a_cobrar) + '</td>'
               + '</tr>';
        }
        h += '</tbody><tfoot><tr style="background:#f5f5f5; font-weight:700;">'
           + '<td colspan="5">Total (' + fmt(resp.total) + ' evento(s))</td>'
           + '<td class="cg-num">' + fmt(resp.totais.pessoas) + '</td>'
           + '<td class="cg-num cg-info">' + fmt(resp.totais.atividade) + '</td>'
           + '<td class="cg-num cg-info">' + fmt(resp.totais.geral) + '</td>'
           + '<td class="cg-num cg-info">' + fmt(resp.totais.perfil) + '</td>'
           + '<td class="cg-num">' + fmt(resp.totais.cobrados) + '</td>'
           + '<td class="cg-num">' + fmt(resp.totais.a_cobrar) + '</td>'
           + '</tr></tfoot></table>';
        $('#cgResultado').html(h);
    }

    /* Drill-down: pessoas com direito a certificado no evento. O total daqui bate
       com a coluna Pessoas da aba Cobranca -- e para conferir antes de cobrar. */
    function renderPessoas(resp) {
        $('#cgCards').html(
            card(resp.total, 'Pessoas com certificado', 'evento ' + resp.cod_evento, '#1e7e34')
          + card(resp.totais.atividade, 'por atividade', '', '#8a8a8a')
          + card(resp.totais.geral, 'pelo geral', '', '#8a8a8a')
          + card(resp.totais.perfil, 'por perfil', '', '#8a8a8a')
        );

        var voltar = '<a href="javascript:void(0);" id="cgVoltarCobranca">'
                   + '<i class="fa fa-arrow-left"></i> voltar para a Cobran&ccedil;a</a>';

        if (!resp.registros.length) {
            $('#cgResultado').html('<div class="alert alert-warning">Nenhuma pessoa com direito a certificado neste evento.</div>'
                                 + '<div style="margin-top:8px;">' + voltar + '</div>');
            return;
        }
        var h = '<div style="margin-bottom:8px;">' + voltar
              + ' <span style="color:#888; font-size:12px; margin-left:8px;">'
              + 'uma pessoa pode ter mais de um certificado, mas conta uma vez na cobran&ccedil;a</span></div>';
        if (resp.limitado) {
            h += '<div class="alert alert-warning">Exibindo as primeiras ' + fmt(resp.total) + ' linhas.</div>';
        }
        h += '<table id="cgTabela" class="table table-bordered table-striped table-hover" style="font-size:13px;">';
        h += '<thead><tr>'
           + th('Inscri&ccedil;&atilde;o') + th('Nome') + th('CPF') + th('E-mail')
           + th('Atividade', 'cg-num') + th('Geral', 'cg-num') + th('Perfil', 'cg-num')
           + '</tr></thead><tbody>';
        function marca(v) {
            return v ? '<i class="fa fa-check" style="color:#1e7e34;"></i>' : '<span style="color:#ddd;">&mdash;</span>';
        }
        for (var i = 0; i < resp.registros.length; i++) {
            var r = resp.registros[i];
            h += '<tr>'
               + '<td>' + r.cod_inscrito + '</td>'
               + '<td>' + esc(r.nome) + '</td>'
               + '<td style="white-space:nowrap;">' + esc(r.cpf) + '</td>'
               + '<td>' + esc(r.email) + '</td>'
               + '<td class="cg-num">' + marca(r.por_atividade) + '</td>'
               + '<td class="cg-num">' + marca(r.por_geral) + '</td>'
               + '<td class="cg-num">' + marca(r.por_perfil) + '</td>'
               + '</tr>';
        }
        h += '</tbody><tfoot><tr style="background:#f5f5f5; font-weight:700;">'
           + '<td colspan="4">Total (' + fmt(resp.total) + ' pessoa(s) a cobrar)</td>'
           + '<td class="cg-num">' + fmt(resp.totais.atividade) + '</td>'
           + '<td class="cg-num">' + fmt(resp.totais.geral) + '</td>'
           + '<td class="cg-num">' + fmt(resp.totais.perfil) + '</td>'
           + '</tr></tfoot></table>';
        $('#cgResultado').html(h);
    }

    function renderEmitidos(resp) {
        $('#cgCards').html(
            card(resp.totais.certificados, 'Certificados emitidos', 'soma das atividades listadas', '#1e7e34')
          + card(resp.total, 'Atividades na lista', '', '#555')
        );

        if (!resp.registros.length) {
            $('#cgResultado').html('<div class="alert alert-warning">Nenhuma atividade com os filtros informados.</div>');
            return;
        }
        var h = '';
        if (resp.limitado) {
            h += '<div class="alert alert-warning">Exibindo as primeiras ' + fmt(resp.total) + ' linhas. Refine os filtros.</div>';
        }
        h += '<table id="cgTabela" class="table table-bordered table-striped table-hover" style="font-size:13px;">';
        h += '<thead><tr>'
           + th('Ev.') + th('T&iacute;tulo do evento') + th('Atividade') + th('Gestor')
           + th('Certificados', 'cg-num')
           + '</tr></thead><tbody>';
        for (var i = 0; i < resp.registros.length; i++) {
            var r = resp.registros[i];
            h += '<tr>'
               + '<td>' + r.cod_evento + '</td>'
               + '<td>' + esc(r.titulo_evento) + '</td>'
               + '<td>' + esc(r.titulo_atividade) + ' <small style="color:#bbb;">#' + r.cod_oficina + '</small></td>'
               + '<td>' + esc(r.gestor) + '</td>'
               + '<td class="cg-num">' + fmt(r.total) + '</td>'
               + '</tr>';
        }
        h += '</tbody><tfoot><tr style="background:#f5f5f5; font-weight:700;">'
           + '<td colspan="4">Total (' + fmt(resp.total) + ' atividade(s))</td>'
           + '<td class="cg-num">' + fmt(resp.totais.certificados) + '</td>'
           + '</tr></tfoot></table>';
        $('#cgResultado').html(h);
    }

    /* ---------------- Ordenacao por clique no cabecalho ----------------
       Delegado no #cgResultado porque a tabela e refeita a cada busca.
       Ordena so o tbody -- a linha de totais fica no tfoot e nao se mexe. */
    function chaveOrdenacao(txt) {
        var t = (txt || '').replace(/\s+/g, ' ').trim();
        if (t === '' || t === '—') { return { vazio: true }; }
        // data pt-BR "27/06/2026" ou "27/06/2026 18:30" -> ordenar cronologicamente.
        // Como texto, "27/06" viria antes de "03/07" -- agruparia por dia, nao por data.
        var d = t.match(/^(\d{2})\/(\d{2})\/(\d{4})(?: (\d{2}):(\d{2}))?$/);
        if (d) {
            return { num: new Date(+d[3], +d[2] - 1, +d[1], +(d[4] || 0), +(d[5] || 0)).getTime() };
        }
        // numero pt-BR: "1.700", "-141", "0" (as colunas numericas sao inteiras)
        if (/^-?\d{1,3}(\.\d{3})*$/.test(t) || /^-?\d+$/.test(t)) {
            return { num: parseFloat(t.replace(/\./g, '')) };
        }
        return { txt: t.toLowerCase() };
    }

    $('#cgResultado').on('click', 'th.cg-sort', function () {
        var $th = $(this);
        var col = $th.index();
        var asc = !$th.hasClass('cg-asc');       // 1o clique = crescente
        var $tbody = $('#cgTabela tbody');
        var rows = $tbody.find('tr').get();

        rows.sort(function (a, b) {
            var ka = chaveOrdenacao($(a).find('td').eq(col).text());
            var kb = chaveOrdenacao($(b).find('td').eq(col).text());
            // vazio sempre por ultimo, independente da direcao
            if (ka.vazio && kb.vazio) { return 0; }
            if (ka.vazio) { return 1; }
            if (kb.vazio) { return -1; }
            var r;
            if (ka.num !== undefined && kb.num !== undefined) {
                r = ka.num - kb.num;
            } else {
                var sa = ka.txt !== undefined ? ka.txt : String(ka.num);
                var sb = kb.txt !== undefined ? kb.txt : String(kb.num);
                r = sa.localeCompare(sb, 'pt-BR');
            }
            return asc ? r : -r;
        });
        $tbody.empty().append(rows);

        // estado visual: so a coluna clicada fica marcada
        $('#cgTabela th.cg-sort').removeClass('cg-sort-on cg-asc cg-desc')
            .find('i.cg-seta').removeClass('fa-sort-asc fa-sort-desc').addClass('fa-sort');
        $th.addClass('cg-sort-on').addClass(asc ? 'cg-asc' : 'cg-desc')
            .find('i.cg-seta').removeClass('fa-sort').addClass(asc ? 'fa-sort-asc' : 'fa-sort-desc');
    });

    // Trocar de aba
    $('.cg-aba').click(function () {
        $('.cg-aba').removeClass('cg-aba-ativa');
        $(this).addClass('cg-aba-ativa');
        abaAtual = $(this).data('aba');
        eventoPessoas = 0;                                  // sai do drill-down de pessoas
        if (abaAtual === 'cobranca') { eventoDrill = 0; }
        buscar();
    });

    // Clique no titulo do evento -> PESSOAS com direito a certificado naquele evento
    $('#cgResultado').on('click', '.cg-pessoas', function () {
        eventoPessoas = parseInt($(this).data('evento'), 10) || 0;
        abaAtual = 'pessoas';
        $('.cg-aba').removeClass('cg-aba-ativa');
        buscar();
    });

    // Voltar da lista de pessoas para a Cobranca
    $('#cgResultado').on('click', '#cgVoltarCobranca', function () {
        eventoPessoas = 0;
        abaAtual = 'cobranca';
        $('.cg-aba').removeClass('cg-aba-ativa');
        $('.cg-aba[data-aba="cobranca"]').addClass('cg-aba-ativa');
        buscar();
    });

    // Clique no icone de lista (aba Cobranca) -> abre as atividades daquele evento
    $('#cgResultado').on('click', '.cg-drill', function () {
        eventoDrill = parseInt($(this).data('evento'), 10) || 0;
        $('.cg-aba').removeClass('cg-aba-ativa');
        $('.cg-aba[data-aba="emitidos"]').addClass('cg-aba-ativa');
        abaAtual = 'emitidos';
        buscar();
    });

    $('#cgLimparEvento').click(function () { eventoDrill = 0; buscar(); });
    $('#cgBuscar').click(buscar);
    $('#cg_gestor, #cg_so_a_cobrar').change(buscar);
    $('#cg_busca').keypress(function (e) { if (e.which === 13) { buscar(); } });
    $('#cgLimpar').click(function () {
        $('#cg_gestor').val(''); $('#cg_busca').val(''); $('#cg_so_a_cobrar').prop('checked', false);
        eventoDrill = 0;
        buscar();
    });

    // Exportar o que esta na tela (inclusive a linha de total)
    $('#cgExportar').click(function () {
        var $t = $('#cgTabela');
        if (!$t.length) { alert('Nada para exportar.'); return; }
        var linhas = [];
        $t.find('tr').each(function () {
            var cols = [];
            $(this).find('th, td').each(function () {
                cols.push('"' + $(this).text().replace(/\s+/g, ' ').trim().replace(/"/g, '""') + '"');
            });
            if (cols.length) { linhas.push(cols.join(';')); }
        });
        // BOM para o Excel abrir os acentos certos
        var blob = new Blob(["﻿" + linhas.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'certificados-' + abaAtual + '.csv';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
    });

    buscar();
});
</script>

<div style="margin-bottom:28px;"></div>
    </div><!-- panel-body -->
</div><!-- panel -->
</div><!-- panel-group -->
