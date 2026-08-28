<?php
require_once(dirname(__FILE__)."/../../view/admin/seguranca-adm-geral.php");
$objSRV = new GerenciarCargaPessoaUploadAC();
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<style>
    .tbl-fin { width:100%; border-collapse:collapse; font-size:13px; }
    .tbl-fin thead th { font-size:11px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.3px; padding:12px 10px; border-bottom:1px solid #e0e0e0; background:#f5f6f8; cursor:pointer; white-space:nowrap; user-select:none; }
    .tbl-fin thead th:first-child { cursor:default; }
    .tbl-fin thead th[data-col]:hover { color:#555; }
    .tbl-fin thead th[data-col]:hover .sort-arrow { color:#666 !important; }
    .tbl-fin thead th .sort-arrow { color:#ccc; margin-left:4px; font-size:10px; }
    .tbl-fin tbody tr { border-bottom:1px solid #f0f0f0; transition:background 0.15s; }
    .tbl-fin tbody tr:hover { background:#f9fafb; }
    .tbl-fin tbody td { padding:5px 10px; vertical-align:middle; color:#333; }
    .tbl-fin .badge-tipo { display:inline-flex; align-items:center; font-size:12px; font-weight:500; padding:3px 10px; border-radius:12px; background:#f0f1f3; }
    .tbl-fin .badge-tipo .dot { width:8px; height:8px; border-radius:50%; margin-right:6px; display:inline-block; flex-shrink:0; }
    .tbl-fin .badge-tipo .dot-green { background:#27ae60; }
    .tbl-fin .badge-tipo .dot-blue { background:#2980b9; }
    .tbl-fin .badge-tipo .dot-orange { background:#f39c12; }
    .tbl-fin .badge-tipo .dot-red { background:#e74c3c; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; transition:all 0.2s; font-size:13px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; border-color:#ccc; }
    .btn-acao.btn-acao-excluir:hover { background:#fef2f2; color:#c0392b; border-color:#f5c6cb; }
    .tbl-fin tbody td:first-child { white-space:nowrap; }
    .btn-mini-acao { font-size:11px; padding:3px 10px; border-radius:20px; border:1px solid #ddd; background:#fff; color:#555; cursor:pointer; margin:2px 0; transition:all 0.2s; }
    .btn-mini-acao:hover { background:#2d6a4f; color:#fff; border-color:#2d6a4f; }
</style>
<script type="text/javascript">
    var _codExcluir = null;
    var _sortCol = -1;
    var _sortAsc = true;

    $(document).ready(function() {
        bindEventosTabela();

        $('#btnSalvar').click(function() {
            salvarArquivo();
        });

        // Sort por coluna
        $('.tbl-fin thead th[data-col]').on('click', function() {
            var col = parseInt($(this).data('col'));
            if (_sortCol === col) { _sortAsc = !_sortAsc; } else { _sortCol = col; _sortAsc = true; }
            sortTable(col, _sortAsc);
            $('.tbl-fin thead th').css('font-weight', '600');
            $('.tbl-fin thead th .sort-arrow').html('↑↓').css('color', '#ccc');
            $(this).css('font-weight', '700');
            $(this).find('.sort-arrow').html(_sortAsc ? '↑' : '↓').css('color', '#333');
        });
    });

    function sortTable(colIndex, asc) {
        var tbody = $('#itens tbody.inputs').first();
        var rows = tbody.find('tr').not('tfoot tr').toArray();
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(colIndex).text().trim().toLowerCase();
            var bVal = $(b).find('td').eq(colIndex).text().trim().toLowerCase();
            var aNum = parseFloat(aVal); var bNum = parseFloat(bVal);
            if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        $.each(rows, function(i, row) { tbody.append(row); });
    }

    function bindEventosTabela() {
        $('.excluir').off('click').on('click', function() {
            _codExcluir = $(this).parents("tr").attr("id");
            $('#modalExclusao').modal('show');
        });
        $('.botaoCarga').off('click').on('click', function() {
            $('html,body').scrollTop(0);
            var id = $(this).parents("tr").attr("id");
            carregarInscritoCargaPessoa(id);
        });
        $('.botaoAssociar').off('click').on('click', function() {
            $('html,body').scrollTop(0);
            var id = $(this).parents("tr").attr("id");
            associarInscritoCargaPessoa(id);
        });
        $('.botaoMapear').off('click').on('click', function() {
            var id = $(this).parents("tr").attr("id");
            abrirMapeamento(id);
        });
    }

    var _codArquivoMapear = null;
    var _separadorMapear = ';';
    var _mapeamentoEscolhido = [];   // guardado entre a critica e a carga

    function abrirMapeamento(codArquivo) {
        _codArquivoMapear = codArquivo;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codArquivo': codArquivo, 'c': 'GerenciarCargaPessoaUpload', 'm': 'lerCabecalhoCarga', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                if (!data.statusMessage) {
                    exibirResultadoCarga(data);
                    return;
                }
                _separadorMapear = data.separador;
                montarModalMapeamento(data);
                $('#modalMapeamento').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    function montarModalMapeamento(data) {
        var html = '';
        html += '<p style="font-size:13px; color:#666; margin-bottom:10px;">Total de linhas de dados: <strong>' + data.totalLinhas + '</strong></p>';
        html += '<table class="table table-bordered table-condensed" style="font-size:13px;">';
        html += '<thead><tr><th>Coluna da planilha</th><th>Campo do Sistema</th>';
        if (data.preview && data.preview.length > 0) {
            html += '<th>Amostra</th>';
        }
        html += '</tr></thead><tbody>';

        for (var i = 0; i < data.colunasCsv.length; i++) {
            html += '<tr>';
            html += '<td><strong>' + data.colunasCsv[i] + '</strong></td>';
            html += '<td><select class="form-control input-sm mapeamento-select" data-idx="' + i + '">';
            for (var campo in data.camposDisponiveis) {
                var sel = (data.sugestoes[i] == campo) ? ' selected' : '';
                html += '<option value="' + campo + '"' + sel + '>' + data.camposDisponiveis[campo] + '</option>';
            }
            html += '</select></td>';
            if (data.preview && data.preview.length > 0) {
                var amostras = [];
                for (var p = 0; p < data.preview.length; p++) {
                    if (data.preview[p][i]) {
                        var val = data.preview[p][i];
                        if (val.length > 40) val = val.substring(0, 37) + '...';
                        amostras.push(val);
                    }
                }
                html += '<td style="font-size:11px; color:#888; max-width:200px; word-break:break-all;">' + amostras.join('<br>') + '</td>';
            }
            html += '</tr>';
        }

        html += '</tbody></table>';
        $('#mapeamentoConteudo').html(html);
        $('#mapeamentoMsg').html('');
    }

    function confirmarMapeamento() {
        var mapeamento = [];
        var temNomeOuCpf = false;
        $('.mapeamento-select').each(function() {
            var val = $(this).val();
            mapeamento.push(val);
            if (val == 'nome_inscrito' || val == 'cpf') temNomeOuCpf = true;
        });

        if (!temNomeOuCpf) {
            $('#mapeamentoMsg').html('<div class="alert alert-danger">Selecione pelo menos Nome ou CPF no mapeamento.</div>');
            return;
        }

        _mapeamentoEscolhido = mapeamento;
        $('#modalMapeamento').modal('hide');

        // Critica antes de gravar: nada e carregado enquanto o usuario nao decidir
        // o que fazer com quem ja esta inscrito.
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codArquivo': _codArquivoMapear,
                'c': 'GerenciarCargaPessoaUpload',
                'm': 'analisarConflitosCarga',
                'mapeamento': mapeamento,
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                if (!data.statusMessage) {
                    exibirResultadoCarga(data);
                    return;
                }
                var temCritica = (data.conflitos && data.conflitos.length > 0)
                              || (data.repetidosNoArquivo && data.repetidosNoArquivo.length > 0)
                              || (data.problemas && data.problemas.length > 0)
                              || !data.temColunaCpf;
                if (!temCritica) {
                    processarCargaDinamica('');
                    return;
                }
                montarModalCritica(data);
                $('#modalCritica').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    function montarModalCritica(data) {
        var html = '';
        var temConflito = data.conflitos && data.conflitos.length > 0;

        if (temConflito) {
            html += '<div class="alert alert-warning" style="margin-bottom:12px;"><strong>' + data.conflitos.length + ' pessoa(s) do arquivo j&aacute; est&atilde;o inscritas neste evento</strong> (mesmo CPF).<br>';
            html += 'Se continuar, os dados dessas pessoas ser&atilde;o <strong>substitu&iacute;dos</strong> pelo que veio na planilha &mdash; s&oacute; as colunas mapeadas; o resto do cadastro fica como est&aacute;. Nenhum cadastro novo &eacute; criado para elas.<br>';
            html += 'Se preferir, saia, remova essas linhas do arquivo e suba de novo.</div>';

            html += '<table class="table table-bordered table-condensed" style="font-size:12px;">';
            html += '<thead><tr><th>Linha</th><th>CPF</th><th>Nome na planilha</th><th>J&aacute; inscrito como</th><th>Situa&ccedil;&atilde;o</th></tr></thead><tbody>';
            for (var i = 0; i < data.conflitos.length; i++) {
                var c = data.conflitos[i];
                html += '<tr>';
                html += '<td>' + c.linha + '</td>';
                html += '<td>' + c.cpf + '</td>';
                html += '<td>' + (c.nomeArquivo || '<span style="color:#999;">&mdash;</span>') + '</td>';
                html += '<td>' + c.nomeSistema + '<br><small style="color:#888;">' + (c.eMailSistema || '') + ' &middot; inscrito em ' + c.inscritoDesde + '</small></td>';
                html += '<td>' + (c.statusProcesso || '') + '</td>';
                html += '</tr>';
            }
            html += '</tbody></table>';
        }

        if (data.repetidosNoArquivo && data.repetidosNoArquivo.length > 0) {
            html += '<div class="alert alert-danger" style="margin-bottom:12px;"><strong>CPF repetido dentro do pr&oacute;prio arquivo:</strong><br>';
            for (var r = 0; r < data.repetidosNoArquivo.length; r++) {
                var d = data.repetidosNoArquivo[r];
                html += 'linha ' + d.linha + ' repete o CPF da linha ' + d.linhaAnterior + ' (' + (d.nome || d.cpf) + ')<br>';
            }
            html += 'Corrija o arquivo &mdash; a linha repetida seria carregada duas vezes.</div>';
        }

        if (data.problemas && data.problemas.length > 0) {
            html += '<div class="alert alert-danger" style="margin-bottom:12px;"><strong>Linhas com problema:</strong><br>';
            for (var p = 0; p < data.problemas.length; p++) {
                html += 'linha ' + data.problemas[p].linha + ': ' + data.problemas[p].mensagem + '<br>';
            }
            html += 'Essas linhas v&atilde;o falhar na carga.</div>';
        }

        if (!data.temColunaCpf) {
            html += '<div class="alert alert-info" style="margin-bottom:12px;">Nenhuma coluna foi mapeada como <strong>CPF</strong>, ent&atilde;o n&atilde;o d&aacute; para conferir quem j&aacute; est&aacute; inscrito. Se algu&eacute;m do arquivo j&aacute; tiver cadastro no evento, vai ficar duplicado.</div>';
        } else if (data.semCpf > 0) {
            html += '<div class="alert alert-info" style="margin-bottom:12px;">' + data.semCpf + ' linha(s) sem CPF preenchido &mdash; nessas n&atilde;o d&aacute; para conferir duplicidade.</div>';
        }

        html += '<p style="font-size:12px; color:#666; margin:0;">Total de linhas com dados: <strong>' + data.totalRegistros + '</strong></p>';

        $('#criticaConteudo').html(html);
        // sem CPF conflitante nao ha o que substituir -- o botao vira "carregar assim mesmo"
        if (temConflito) {
            $('#btnCriticaSubstituir').html('<i class="fa fa-refresh"></i> Substituir os dados atuais');
        } else {
            $('#btnCriticaSubstituir').html('<i class="fa fa-cog"></i> Carregar assim mesmo');
        }
    }

    function processarCargaDinamica(acaoConflito) {
        $('#modalCritica').modal('hide');
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'codArquivo': _codArquivoMapear,
                'c': 'GerenciarCargaPessoaUpload',
                'm': 'processarCargaDinamica',
                'mapeamento': _mapeamentoEscolhido,
                'acaoConflito': acaoConflito,
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                exibirResultadoCarga(data);
                if (data.statusMessage) {
                    carregarTabelaArquivos();
                    // carga gravada: fecha a sequencia com o passo de associar
                    associarInscritoCargaPessoa(_codArquivoMapear);
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function exibirResultadoCarga(data, acumular) {
        var tipo = data.statusMessage ? 'alert-success' : 'alert-danger';
        var html = '<div class="alert ' + tipo + ' alert-dismissible" style="margin-top:10px; max-height:300px; overflow-y:auto;">';
        html += '<button type="button" class="close" data-dismiss="alert" onclick="$(this).closest(\'.alert\').remove();"><span>&times;</span></button>';
        html += data.message;
        html += '</div>';
        // passos encadeados somam os avisos em vez de um apagar o do anterior
        if (acumular) { $('#resultadoCarga').append(html); } else { $('#resultadoCarga').html(html); }
        $('html,body').animate({ scrollTop: $('#resultadoCarga').offset().top }, 'fast');
    }

    function carregarInscritoCargaPessoa(codArquivo, aoConcluir) {
        if (!codArquivo) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codEvento': $('#codEvento').val(), 'codArquivo': codArquivo, 'c': 'GerenciarCargaPessoaUpload', 'm': 'carregarInscritoCargaPessoa', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                exibirResultadoCarga(data);
                if (data.statusMessage) {
                    carregarTabelaArquivos();
                    limparCampos();
                    if (typeof aoConcluir === 'function') { aoConcluir(data); }
                }
                // sem sucesso a sequencia para aqui -- o proximo passo nao roda
            },
            complete: function() { stopLoading(); }
        });
    }

    function associarInscritoCargaPessoa(codArquivo, aoConcluir) {
        if (!codArquivo) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codEvento': $('#codEvento').val(), 'codArquivo': codArquivo, 'c': 'GerenciarCargaPessoaUpload', 'm': 'associarInscritoCargaPessoa', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                exibirResultadoCarga(data, true);
                if (data.statusMessage) {
                    carregarTabelaArquivos();
                    limparCampos();
                    if (typeof aoConcluir === 'function') { aoConcluir(data); }
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function carregarTabelaArquivos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarCargaPessoaUpload', 'm': 'carregarTabelaArquivos', '_submit_check': '1'},
            function(html) {
                $("#itens tbody.inputs").html(html);
                bindEventosTabela();
            }
        );
    }

    function salvarArquivo() {
        $('#formArquivo').ajaxSubmit({
            target: '#modalUploadMsg',
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            timeout: 30000,
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                if (data.statusMessage) {
                    $('#modalUpload').modal('hide');
                    carregarTabelaArquivos();
                    limparCampos();
                    exibirResultadoCarga(data);
                    // segue sozinho para os proximos passos
                    encadearCarga(data.codArquivo, data.tipoArquivo);
                } else {
                    $('#modalUploadMsg').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' + data.message + '</div>');
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    /**
     * Depois do upload, executa a sequencia da carga sem o usuario ter que clicar
     * botao por botao: carregar -> associar. Cada passo so dispara se o anterior
     * deu certo; qualquer falha para a sequencia e mostra o erro.
     *
     * O tipo dinamico e a excecao: abre o mapeamento, porque a conferencia das
     * colunas e da critica de conflito e decisao de quem esta carregando.
     */
    function encadearCarga(codArquivo, tipoArquivo) {
        if (!codArquivo) return;
        if (tipoArquivo == 'INSCRITOS-DINAMICO') {
            abrirMapeamento(codArquivo);
            return;
        }
        carregarInscritoCargaPessoa(codArquivo, function() {
            associarInscritoCargaPessoa(codArquivo);
        });
    }

    function confirmarExclusao() {
        if (!_codExcluir) return;
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codArquivo': _codExcluir, 'c': 'GerenciarCargaPessoaUpload', 'm': 'excluirArquivo', '_submit_check': '1'},
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) { carregarTabelaArquivos(); }
                $('#modalExclusao').modal('hide');
                _codExcluir = null;
            },
            complete: function() { stopLoading(); }
        });
    }

    function abrirModalUpload() {
        limparCampos();
        $('#modalUpload').modal('show');
    }

    function limparCampos() {
        $('#codArquivo').val('');
        $('#tipoArquivo').val('');
        $('#arquivo').val('');
        $('#codPerfil').val('');
        $('#novoCodArquivo').val(0);
        $('#modalUploadMsg').html('');
    }
</script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_inscricoes.php");
?>

<!-- Mensagens flutuantes -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<!-- Resultado da carga (com botao fechar) -->
<div id="resultadoCarga"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <div>
            <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Carga de Pessoas</h4>
            <a href="#" onclick="$('#exemploplanilha').toggle(); return false;" style="font-size:12px; color:#2d6a4f; text-decoration:none;"><i class="fa fa-info-circle"></i> Ver formato da planilha</a>
            <span style="color:#ccc; font-size:12px;">|</span>
            <a href="<?php echo WB_URL_COMMAND ?>&c=GerenciarCargaPessoaUpload&m=baixarModeloEnsalamento&_submit_check=1" style="font-size:12px; color:#2d6a4f; text-decoration:none;"><i class="fa fa-download"></i> Baixar modelo de ensalamento (.xlsx)</a>
            <span style="color:#ccc; font-size:12px;">|</span>
            <a href="<?php echo WB_URL_COMMAND ?>&c=GerenciarCargaPessoaUpload&m=baixarModeloPresenca&_submit_check=1" style="font-size:12px; color:#2d6a4f; text-decoration:none;"><i class="fa fa-download"></i> Baixar modelo de presença (.xlsx)</a>
            <div id="exemploplanilha" style="display:none; margin-top:8px; font-size:12px; color:#666; background:#f9fafb; border:1px solid #eee; border-radius:8px; padding:12px; max-width:700px;">
                <p style="margin:0 0 8px; font-weight:600; color:#555;">Formato esperado (separado por ponto e vírgula):</p>
                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                    <thead><tr style="background:#eef0f4;">
                        <th style="padding:4px 8px; border:1px solid #ddd;">Nome</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">CPF</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">E-mail</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">Valor Fixo*</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">Valor Doado*</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">Valor Desconto*</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">% Desconto*</th>
                    </tr></thead>
                    <tbody><tr>
                        <td style="padding:4px 8px; border:1px solid #eee;">João Silva</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">123.456.789-00</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">joao@email.com</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">100.00</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">0.00</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">20.00</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">20</td>
                    </tr></tbody>
                </table>
                <p style="margin:8px 0 0; color:#999; font-size:11px;">* Colunas de valores só são necessárias para o tipo INSCRITOS-DESCONTOS.</p>
                <hr style="margin:12px 0; border:none; border-top:1px solid #eee;">
                <p style="margin:0 0 8px; font-weight:600; color:#555;">ENSALAMENTO_INSCRITO (use o botão "Baixar modelo de ensalamento"):</p>
                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                    <thead><tr style="background:#eef0f4;">
                        <th style="padding:4px 8px; border:1px solid #ddd;">cod_inscrito**</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">cpf**</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">email**</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">sala</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">bloco</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">dia</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">hora</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">obs</th>
                    </tr></thead>
                    <tbody><tr>
                        <td style="padding:4px 8px; border:1px solid #eee;">4821</td>
                        <td style="padding:4px 8px; border:1px solid #eee;"></td>
                        <td style="padding:4px 8px; border:1px solid #eee;"></td>
                        <td style="padding:4px 8px; border:1px solid #eee;">Sala 12</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">Bloco B</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">10/08/2026</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">14h00</td>
                        <td style="padding:4px 8px; border:1px solid #eee;"></td>
                    </tr></tbody>
                </table>
                <p style="margin:8px 0 0; color:#999; font-size:11px;">** Identifique o inscrito por <b>uma</b> das colunas: cod_inscrito (mais seguro), cpf ou email. Reenviar atualiza o ensalamento existente.</p>
                <hr style="margin:12px 0; border:none; border-top:1px solid #eee;">
                <p style="margin:0 0 8px; font-weight:600; color:#555;">PRESENCA_INSCRITO (use o botão "Baixar modelo de presença"):</p>
                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                    <thead><tr style="background:#eef0f4;">
                        <th style="padding:4px 8px; border:1px solid #ddd;">cod_inscrito**</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">nome</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">cpf**</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">email**</th>
                        <th style="padding:4px 8px; border:1px solid #ddd;">presenca</th>
                    </tr></thead>
                    <tbody><tr>
                        <td style="padding:4px 8px; border:1px solid #eee;"></td>
                        <td style="padding:4px 8px; border:1px solid #eee;">Fulano de Tal</td>
                        <td style="padding:4px 8px; border:1px solid #eee;">123.456.789-00</td>
                        <td style="padding:4px 8px; border:1px solid #eee;"></td>
                        <td style="padding:4px 8px; border:1px solid #eee;">1</td>
                    </tr></tbody>
                </table>
                <p style="margin:8px 0 0; color:#999; font-size:11px;">** Identifique o inscrito por <b>uma</b> das colunas: cod_inscrito (mais seguro), cpf ou email. Na coluna <b>presenca</b>: vazio ou 0 = ausente; qualquer outro valor = presente. Reenviar sobrescreve a presença.</p>
            </div>
        </div>
        <button type="button" class="btn btn-sm abriModal" onclick="abrirModalUpload()">
            <i class="fa fa-upload"></i> Enviar Arquivo
        </button>
    </div>
    <div style="padding:0;">
        <table class="tbl-fin" id="itens">
            <thead>
                <tr>
                    <th style="width:160px;">A&ccedil;&otilde;es</th>
                    <th data-col="1">Status Carga <span class="sort-arrow">↑↓</span></th>
                    <th data-col="2">Origem / Arquivo Gerado <span class="sort-arrow">↑↓</span></th>
                    <th data-col="3">Arquivo Enviado <span class="sort-arrow">↑↓</span></th>
                    <th data-col="4">Data Inclusão <span class="sort-arrow">↑↓</span></th>
                </tr>
            </thead>
            <tbody class="inputs">
                <?php
                $objSRV->carregarTabelaArquivos($fws_, $debug);
                ?>
            </tbody>
        </table>
    </div>
    <div style="height:20px;"></div>
</div>
<div style="height:150px;"></div>

<!-- Modal Upload -->
<div class="modal fade" id="modalUpload" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:550px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-upload"></i>Enviar Arquivo de Carga</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalUploadMsg"></div>
                <form id="formArquivo" name="formArquivo" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group">
                        <label for="tipoArquivo">Origem da Carga:</label>
                        <select class="form-control" id="tipoArquivo" name="tipoArquivo" required>
                            <option value="">Selecione...</option>
                            <option value="LOGIN">LOGIN</option>
                            <option value="INSCRITOS">INSCRITOS</option>
                            <option value="INSCRITOS-APTOS">INSCRITOS-APTOS</option>
                            <option value="INSCRITOS-DESCONTOS">INSCRITOS-DESCONTOS</option>
                            <?php // o value continua sendo o tipo gravado no banco; o texto vem do rotulo ?>
                            <option value="INSCRITOS-DINAMICO"><?php echo GerenciarCargaPessoaUploadAC::rotuloTipoArquivo('INSCRITOS-DINAMICO') ?></option>
                            <option value="ENSALAMENTO_INSCRITO"><?php echo GerenciarCargaPessoaUploadAC::rotuloTipoArquivo('ENSALAMENTO_INSCRITO') ?></option>
                            <option value="PRESENCA_INSCRITO"><?php echo GerenciarCargaPessoaUploadAC::rotuloTipoArquivo('PRESENCA_INSCRITO') ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="codPerfil">Perfil:</label>
                        <select class="form-control" id="codPerfil" name="codPerfil">
                            <option value="">Selecione...</option>
                            <?php
                            $fws_['codEvento'] = $objEv->getCodEvento();
                            $fws_['semRestricao'] = true;
                            CarregarOptionsAC::perfis($fws_, $debug);
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="arquivo">Selecionar Arquivo:</label>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <button type="button" onclick="$('#arquivo').click();" style="border-radius:8px; padding:8px 16px; font-size:13px; background:#f0f0f0; color:#555; border:1px solid #ddd; cursor:pointer; white-space:nowrap;"><i class="fa fa-folder-open" style="margin-right:6px;"></i>Procurar...</button>
                            <span id="nomeArquivo" style="font-size:13px; color:#888; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">Nenhum arquivo selecionado</span>
                            <input type="file" name="arquivo" id="arquivo" required style="display:none;" onchange="$('#nomeArquivo').text(this.files.length ? this.files[0].name : 'Nenhum arquivo selecionado');">
                        </div>
                    </div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarCargaPessoaUpload" name="c">
                    <input type="hidden" value="salvarCargaPessoaUpload" name="m">
                    <input type="hidden" name="codEvento" id="codEvento" value="<?php echo COD_EVENTO ?>">
                    <input type="hidden" name="novoCodArquivo" id="novoCodArquivo" value="0"/>
                    <input type="hidden" name="codArquivo" id="codArquivo" value=""/>
                </form>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvar">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exclusão -->
<div class="modal fade" id="modalExclusao" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="max-width:450px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclusão</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este arquivo?</p>
                <p><strong>Esta ação é irreversível.</strong></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Log -->
<div class="modal fade" id="modalLog" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Log da Carga</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="max-height:500px; overflow-y:auto;">
                <pre id="modalLogConteudo" style="white-space:pre-wrap; word-break:break-word; font-size:12px; color:#333; background:#f9fafb; border:1px solid #eee; border-radius:4px; padding:12px; margin:0;"></pre>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Critica de Conflito (roda entre o mapeamento e a carga) -->
<div class="modal fade" id="modalCritica" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document" style="max-width:820px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Crítica da Carga</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="max-height:520px; overflow-y:auto;">
                <div id="criticaConteudo"></div>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Sair sem carregar</button>
                <button type="button" class="btn btn-warning" id="btnCriticaSubstituir" onclick="processarCargaDinamica('SUBSTITUIR')"><i class="fa fa-refresh"></i> Substituir os dados atuais</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mapeamento Dinamico -->
<div class="modal fade" id="modalMapeamento" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document" style="max-width:750px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-columns"></i> Mapeamento de Colunas</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="max-height:500px; overflow-y:auto;">
                <div id="mapeamentoMsg"></div>
                <div id="mapeamentoConteudo"></div>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary modalBtnSalvar" onclick="confirmarMapeamento()"><i class="fa fa-cog"></i> Processar Carga</button>
            </div>
        </div>
    </div>
</div>
