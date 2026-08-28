<?php
// Consulta consolidada de orientações (somente admin: coordenador/gerente).
// Incluído por GerenciarRegistroOrientacao.php dentro do ramo $isAdmin.
// Reaproveita helpers JS globais (escapeHtml, formatarData, formatarDataHora,
// renderStatusLeituraOrientacao) e os estilos .ro-card já definidos na tela principal.
// Objetivo: em vez de "caçar" dia a dia, ver empilhado tudo de um PES ou de um aluno.
?>
<style>
.ro-consulta-info { font-size:13px; color:#666; margin:4px 0 12px; }
.ro-consulta-vazio { font-size:13px; color:#888; font-style:italic; padding:15px; text-align:center; }
.ro-consulta-total { font-size:12px; color:#999; margin-bottom:10px; }
.ro-card-supervisor { color:#666; font-size:12px; }
</style>

<!-- === MODO: POR PES === -->
<div id="modoConsultaPes" class="ro-container" style="display:none;">
    <div class="form-group" style="margin-bottom:5px;">
        <label for="consultaPesSelect"><strong>Selecione o PES:</strong></label>
        <select id="consultaPesSelect" class="form-control" style="max-width:400px;">
            <option value="">-- Selecione --</option>
        </select>
    </div>
    <div class="ro-consulta-info">
        <i class="fa fa-info-circle"></i> Mostra todas as orienta&ccedil;&otilde;es recebidas por este PES, de <strong>todos os supervisores</strong>, em ordem de data.
    </div>
    <div id="consultaPesLista"></div>
</div>

<!-- === MODO: POR ALUNO === -->
<div id="modoConsultaAluno" class="ro-container" style="display:none;">
    <div class="form-group" style="margin-bottom:5px;">
        <label for="consultaAlunoSelect"><strong>Selecione o Aluno:</strong></label>
        <select id="consultaAlunoSelect" class="form-control" style="max-width:500px;">
            <option value="">-- Selecione --</option>
        </select>
    </div>
    <div class="ro-consulta-info">
        <i class="fa fa-info-circle"></i> Mostra todas as orienta&ccedil;&otilde;es registradas sobre este aluno, de <strong>todos os supervisores</strong>, em ordem de data.
    </div>
    <div id="consultaAlunoLista"></div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var pesSelectCarregado = false;
    var alunoSelectCarregado = false;

    // ----- Alternância entre os três modos -----
    function ativarModo(modo) {
        $('.ro-modo-btn').removeClass('ro-modo-ativo');
        $('#btnModo' + modo).addClass('ro-modo-ativo');
        $('#modoSupervisor, #modoConsultaPes, #modoConsultaAluno').hide();
        if (modo === 'Supervisor') {
            $('#modoSupervisor').show();
        } else if (modo === 'Pes') {
            $('#modoConsultaPes').show();
            if (!pesSelectCarregado) { carregarSelectPes(); pesSelectCarregado = true; }
        } else if (modo === 'Aluno') {
            $('#modoConsultaAluno').show();
            if (!alunoSelectCarregado) { carregarSelectAluno(); alunoSelectCarregado = true; }
        }
    }
    $('#btnModoSupervisor').on('click', function() { ativarModo('Supervisor'); });
    $('#btnModoPes').on('click', function() { ativarModo('Pes'); });
    $('#btnModoAluno').on('click', function() { ativarModo('Aluno'); });

    // ----- Carregar seletores -----
    function carregarSelectPes() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarRegistroOrientacao', 'm': 'consultarPesComOrientacoes', '_submit_check': '1'
        }, function(data) {
            if (!data.statusMessage) { processJson(data); return; }
            var sel = $('#consultaPesSelect');
            sel.find('option:not(:first)').remove();
            if (!data.lista || !data.lista.length) {
                sel.append('<option value="" disabled>Nenhum PES com orientações</option>');
                return;
            }
            for (var i = 0; i < data.lista.length; i++) {
                var p = data.lista[i];
                var nome = p.nome_inscrito || ('PES ' + p.cod_agente);
                sel.append('<option value="' + p.cod_agente + '">' + escapeHtml(nome) + '</option>');
            }
        }, 'json');
    }

    function carregarSelectAluno() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarRegistroOrientacao', 'm': 'consultarAlunosComOrientacoes', '_submit_check': '1'
        }, function(data) {
            if (!data.statusMessage) { processJson(data); return; }
            var sel = $('#consultaAlunoSelect');
            sel.find('option:not(:first)').remove();
            if (!data.lista || !data.lista.length) {
                sel.append('<option value="" disabled>Nenhum aluno com orientações</option>');
                return;
            }
            for (var i = 0; i < data.lista.length; i++) {
                var a = data.lista[i];
                var partes = [];
                if (a.codigo_exibicao) partes.push(a.codigo_exibicao);
                if (a.iniciais) partes.push(a.iniciais);
                var label = partes.join(' - ');
                if (a.nome_colegio) label += ' (' + a.nome_colegio + ')';
                var turma = [];
                if (a.serie_turma) turma.push(a.serie_turma);
                if (a.turno) turma.push(a.turno);
                if (turma.length) label += ' [' + turma.join(' / ') + ']';
                if (!label) label = 'Aluno ' + a.cod_aluno;
                sel.append('<option value="' + a.cod_aluno + '">' + escapeHtml(label) + '</option>');
            }
        }, 'json');
    }

    // ----- Carregar orientações ao selecionar -----
    $('#consultaPesSelect').on('change', function() {
        var codPes = $(this).val();
        if (!codPes) { $('#consultaPesLista').html(''); return; }
        $('#consultaPesLista').html('<div class="ro-loading">Carregando...</div>');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarRegistroOrientacao', 'm': 'consultarOrientacoesPorPes', 'codPes': codPes, '_submit_check': '1'
        }, function(data) {
            if (!data.statusMessage) { processJson(data); $('#consultaPesLista').html(''); return; }
            renderListaOrientacoes($('#consultaPesLista'), data.lista, 'orientacoes_pes', 'data_leitura_pes');
        }, 'json');
    });

    $('#consultaAlunoSelect').on('change', function() {
        var codAluno = $(this).val();
        if (!codAluno) { $('#consultaAlunoLista').html(''); return; }
        $('#consultaAlunoLista').html('<div class="ro-loading">Carregando...</div>');
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarRegistroOrientacao', 'm': 'consultarOrientacoesPorAluno', 'codAluno': codAluno, '_submit_check': '1'
        }, function(data) {
            if (!data.statusMessage) { processJson(data); $('#consultaAlunoLista').html(''); return; }
            renderListaOrientacoes($('#consultaAlunoLista'), data.lista, 'orientacoes_crianca', 'data_leitura_crianca');
        }, 'json');
    });

    // ----- Render de lista empilhada (reaproveita cards .ro-card da tela principal) -----
    function renderListaOrientacoes($container, lista, campoTexto, campoLeitura) {
        if (!lista || !lista.length) {
            $container.html('<div class="ro-consulta-vazio"><i class="fa fa-info-circle"></i> Nenhuma orientação registrada ainda.</div>');
            return;
        }
        var html = '<div class="ro-consulta-total"><strong>' + lista.length + '</strong> orientação(ões) encontrada(s)</div>';
        for (var i = 0; i < lista.length; i++) {
            var o = lista[i];
            html += '<div class="ro-card">';
            html += '<div class="ro-card-title"><i class="fa fa-calendar"></i> ' + formatarData(o.data_registro);
            if (o.nome_supervisor) {
                html += ' <span class="ro-card-supervisor">&mdash; <i class="fa fa-user-md"></i> ' + escapeHtml(o.nome_supervisor) + '</span>';
            }
            html += '</div>';
            html += '<div class="ro-card-text ro-card-orientacoes"><span style="white-space:pre-line;">' + escapeHtml(o[campoTexto]) + '</span>';
            html += renderStatusLeituraOrientacao(o[campoLeitura]);
            html += '</div>';
            html += '</div>';
        }
        $container.html(html);
    }
});
</script>
