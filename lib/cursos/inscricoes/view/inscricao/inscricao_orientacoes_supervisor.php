<?php
// Orientacoes enviadas pelos supervisores ao PES logado.
// Lista agrupada por data, cards colapsaveis.
// Ao expandir um card nao lido, grava data/hora da primeira leitura via AJAX.
?>
<div id="orientacoesSupervisorContainer">
    <div id="orientacoesLoading" class="os-msg">Carregando orienta&ccedil;&otilde;es...</div>
    <div id="orientacoesVazio" class="os-msg" style="display:none;">
        <i class="glyphicon glyphicon-info-sign"></i>
        Voc&ecirc; ainda n&atilde;o recebeu orienta&ccedil;&otilde;es de nenhum supervisor.
    </div>

    <div id="blocoOrientacoesPes" style="display:none;">
        <h4 class="os-bloco-titulo"><i class="glyphicon glyphicon-comment"></i> Orienta&ccedil;&otilde;es ao PES</h4>
        <div id="listaOrientacoes"></div>
    </div>

    <div id="blocoOrientacoesCrianca" style="display:none;">
        <h4 class="os-bloco-titulo" style="margin-top:25px;"><i class="glyphicon glyphicon-user"></i> Orienta&ccedil;&otilde;es por crian&ccedil;a</h4>
        <div id="listaOrientacoesCrianca"></div>
    </div>
</div>

<style>
.os-msg { padding:20px; text-align:center; color:#888; font-size:14px; font-style:italic; }
.os-card { background:#fff; border:1px solid #e0e0e0; border-radius:8px; margin-bottom:10px; transition:border-color 0.2s; }
.os-card.os-nao-lida { border-left:4px solid #f0ad4e; background:#fffbf0; }
.os-card-header { padding:12px 15px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
.os-card-header:hover { background:#f9f9f9; }
.os-card-header-info { flex:1; min-width:0; }
.os-card-data { font-weight:600; color:#333; font-size:14px; }
.os-card-supervisor { color:#666; font-size:12px; margin-top:2px; }
.os-card-badge-nova { background:#f0ad4e; color:#fff; font-size:11px; font-weight:600; padding:3px 8px; border-radius:10px; margin-left:8px; }
.os-card-badge-lida { color:#5cb85c; font-size:11px; margin-left:8px; }
.os-card-toggle { color:#999; margin-left:10px; font-size:16px; }
.os-card-body { display:none; padding:0 15px 15px 15px; border-top:1px solid #f0f0f0; }
.os-card-body.os-aberto { display:block; }
.os-card-texto { white-space:pre-line; color:#444; font-size:14px; line-height:1.5; padding-top:10px; }
.os-card-meta-leitura { margin-top:8px; padding-top:8px; border-top:1px dashed #eee; font-size:11px; color:#888; }
.os-bloco-titulo { font-size:15px; font-weight:600; color:#444; margin:0 0 10px 0; padding-bottom:6px; border-bottom:1px solid #eee; }
.os-crianca-ident { display:inline-block; background:#e7f3ff; color:#1e5a8a; padding:2px 8px; border-radius:3px; font-size:12px; margin-top:3px; }
</style>

<script type="text/javascript">
(function() {
    var orientacoesCarregado = false;

    function formatarDataBR(d) {
        if (!d) return '';
        var p = d.split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : d;
    }
    function formatarDataHoraBR(dh) {
        if (!dh) return '';
        // esperado: YYYY-MM-DD HH:MM:SS
        var partes = dh.split(' ');
        if (partes.length !== 2) return dh;
        var data = formatarDataBR(partes[0]);
        var hm = partes[1].substring(0, 5);
        return data + ' &agrave;s ' + hm;
    }
    function escapeHtmlOS(txt) {
        if (!txt) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(txt));
        return div.innerHTML;
    }

    function construirCardPes(o) {
        var naoLida = !o.data_leitura_pes;
        var classes = 'os-card' + (naoLida ? ' os-nao-lida' : '');
        var html = '<div class="' + classes + '" data-id="' + o.cod_registro_pes + '" data-tipo="pes" data-lida="' + (naoLida ? '0' : '1') + '">';
        html += '  <div class="os-card-header">';
        html += '    <div class="os-card-header-info">';
        html += '      <div class="os-card-data">' + formatarDataBR(o.data_registro);
        html += naoLida ? '      <span class="os-card-badge-nova">NOVA</span>'
                        : '      <span class="os-card-badge-lida"><i class="glyphicon glyphicon-ok"></i> Lida</span>';
        html += '      </div>';
        html += '      <div class="os-card-supervisor"><i class="glyphicon glyphicon-user"></i> ' + escapeHtmlOS(o.nome_supervisor || 'Supervisor') + '</div>';
        html += '    </div>';
        html += '    <i class="os-card-toggle glyphicon glyphicon-chevron-down"></i>';
        html += '  </div>';
        html += '  <div class="os-card-body">';
        html += '    <div class="os-card-texto">' + escapeHtmlOS(o.orientacoes_pes) + '</div>';
        if (!naoLida) {
            html += '    <div class="os-card-meta-leitura"><i class="glyphicon glyphicon-eye-open"></i> Lida em ' + formatarDataHoraBR(o.data_leitura_pes) + '</div>';
        }
        html += '  </div>';
        html += '</div>';
        return html;
    }

    function identificacaoCrianca(o) {
        var partes = [];
        if (o.codigo_exibicao) partes.push(o.codigo_exibicao);
        if (o.iniciais) partes.push(o.iniciais);
        var ident = partes.join(' - ');
        var turma = [];
        if (o.serie_turma) turma.push(o.serie_turma);
        if (o.turno) turma.push(o.turno);
        if (turma.length) ident += ' [' + turma.join(' / ') + ']';
        if (!ident) ident = 'Aluno não identificado';
        return ident;
    }

    function construirCardCrianca(o) {
        var naoLida = !o.data_leitura_crianca;
        var classes = 'os-card' + (naoLida ? ' os-nao-lida' : '');
        var ident = identificacaoCrianca(o);
        var html = '<div class="' + classes + '" data-id="' + o.cod_registro_crianca + '" data-tipo="crianca" data-lida="' + (naoLida ? '0' : '1') + '">';
        html += '  <div class="os-card-header">';
        html += '    <div class="os-card-header-info">';
        html += '      <div class="os-card-data">' + formatarDataBR(o.data_registro);
        html += naoLida ? '      <span class="os-card-badge-nova">NOVA</span>'
                        : '      <span class="os-card-badge-lida"><i class="glyphicon glyphicon-ok"></i> Lida</span>';
        html += '      </div>';
        html += '      <div class="os-crianca-ident"><i class="glyphicon glyphicon-user"></i> ' + escapeHtmlOS(ident) + '</div>';
        if (o.nome_colegio) {
            html += '      <div class="os-card-supervisor" style="margin-top:4px;">' + escapeHtmlOS(o.nome_colegio) + '</div>';
        }
        html += '      <div class="os-card-supervisor"><i class="glyphicon glyphicon-user"></i> ' + escapeHtmlOS(o.nome_supervisor || 'Supervisor') + '</div>';
        html += '    </div>';
        html += '    <i class="os-card-toggle glyphicon glyphicon-chevron-down"></i>';
        html += '  </div>';
        html += '  <div class="os-card-body">';
        html += '    <div class="os-card-texto">' + escapeHtmlOS(o.orientacoes_crianca) + '</div>';
        if (!naoLida) {
            html += '    <div class="os-card-meta-leitura"><i class="glyphicon glyphicon-eye-open"></i> Lida em ' + formatarDataHoraBR(o.data_leitura_crianca) + '</div>';
        }
        html += '  </div>';
        html += '</div>';
        return html;
    }

    function bindToggleCards(containerSelector) {
        $(containerSelector + ' .os-card-header').on('click', function() {
            var card = $(this).closest('.os-card');
            var body = card.find('.os-card-body');
            var toggle = card.find('.os-card-toggle');
            var aberto = body.hasClass('os-aberto');
            if (aberto) {
                body.removeClass('os-aberto');
                toggle.removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
                return;
            }
            body.addClass('os-aberto');
            toggle.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
            if (String(card.data('lida')) === '0') {
                marcarLida(card);
            }
        });
    }

    function marcarLida(card) {
        var id = card.data('id');
        var tipo = card.data('tipo');
        if (!id) return;
        var metodo = (tipo === 'crianca') ? 'marcarLidaCrianca' : 'marcarLida';
        var dados = { 'c': 'GerenciarOrientacaoInscrito', 'm': metodo, '_submit_check': '1' };
        if (tipo === 'crianca') dados.cod_registro_crianca = id;
        else dados.cod_registro_pes = id;
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados, function(resp) {
            if (resp && resp.statusMessage) {
                card.data('lida', '1');
                card.removeClass('os-nao-lida');
                card.find('.os-card-badge-nova').remove();
                var agora = new Date();
                var d = agora.getFullYear() + '-'
                      + ('0' + (agora.getMonth() + 1)).slice(-2) + '-'
                      + ('0' + agora.getDate()).slice(-2) + ' '
                      + ('0' + agora.getHours()).slice(-2) + ':'
                      + ('0' + agora.getMinutes()).slice(-2);
                var infoData = card.find('.os-card-data');
                if (!infoData.find('.os-card-badge-lida').length) {
                    infoData.append(' <span class="os-card-badge-lida"><i class="glyphicon glyphicon-ok"></i> Lida</span>');
                }
                if (!card.find('.os-card-meta-leitura').length) {
                    card.find('.os-card-body').append(
                        '<div class="os-card-meta-leitura"><i class="glyphicon glyphicon-eye-open"></i> Lida em ' + formatarDataBR(d.split(' ')[0]) + ' &agrave;s ' + d.split(' ')[1] + '</div>'
                    );
                }
                if (typeof atualizarContadorOrientacoes === 'function') {
                    atualizarContadorOrientacoes();
                }
            }
        }, 'json');
    }

    function renderLista(listaPes, listaCrianca) {
        var temPes = listaPes && listaPes.length;
        var temCrianca = listaCrianca && listaCrianca.length;

        if (!temPes && !temCrianca) {
            $('#orientacoesLoading').hide();
            $('#blocoOrientacoesPes').hide();
            $('#blocoOrientacoesCrianca').hide();
            $('#orientacoesVazio').show();
            return;
        }

        if (temPes) {
            var htmlPes = '';
            for (var i = 0; i < listaPes.length; i++) htmlPes += construirCardPes(listaPes[i]);
            $('#listaOrientacoes').html(htmlPes);
            $('#blocoOrientacoesPes').show();
            bindToggleCards('#listaOrientacoes');
        } else {
            $('#blocoOrientacoesPes').hide();
        }

        if (temCrianca) {
            var htmlCr = '';
            for (var j = 0; j < listaCrianca.length; j++) htmlCr += construirCardCrianca(listaCrianca[j]);
            $('#listaOrientacoesCrianca').html(htmlCr);
            $('#blocoOrientacoesCrianca').show();
            bindToggleCards('#listaOrientacoesCrianca');
        } else {
            $('#blocoOrientacoesCrianca').hide();
        }

        $('#orientacoesLoading').hide();
        $('#orientacoesVazio').hide();
    }

    window.carregarOrientacoesSupervisor = function(forcar) {
        if (orientacoesCarregado && !forcar) return;
        orientacoesCarregado = true;
        $('#orientacoesLoading').show();
        $('#orientacoesVazio').hide();
        $('#blocoOrientacoesPes').hide();
        $('#blocoOrientacoesCrianca').hide();

        var req1 = $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarOrientacaoInscrito', 'm': 'carregarMinhasOrientacoes', '_submit_check': '1'
        }, null, 'json');
        var req2 = $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarOrientacaoInscrito', 'm': 'carregarMinhasOrientacoesCrianca', '_submit_check': '1'
        }, null, 'json');

        $.when(req1, req2).done(function(r1, r2) {
            var respPes = r1 && r1[0] ? r1[0] : null;
            var respCr = r2 && r2[0] ? r2[0] : null;
            if ((!respPes || !respPes.statusMessage) && (!respCr || !respCr.statusMessage)) {
                $('#orientacoesLoading').html('<span style="color:#a94442;">N&atilde;o foi poss&iacute;vel carregar as orienta&ccedil;&otilde;es.</span>');
                return;
            }
            renderLista(
                (respPes && respPes.statusMessage) ? (respPes.lista || []) : [],
                (respCr && respCr.statusMessage) ? (respCr.lista || []) : []
            );
        }).fail(function() {
            $('#orientacoesLoading').html('<span style="color:#a94442;">N&atilde;o foi poss&iacute;vel carregar as orienta&ccedil;&otilde;es.</span>');
        });
    };
})();
</script>
