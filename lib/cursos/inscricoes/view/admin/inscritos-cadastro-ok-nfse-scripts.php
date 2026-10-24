<?php
/**
 * JS e modais da emissao de NFS-e na aba Pedidos. Incluido UMA vez por pagina,
 * depois do laco dos pedidos.
 */

// Mesmas condicoes do bloco por pedido: sem permissao ou sem emissor
// cadastrado, nada de JS/modal — a tela fica como sempre foi.
if (empty($__podeEmitirNfse) || empty($__temEmissorNfse)) {
    return;
}

// O aviso "esta nota tem/nao tem valor fiscal" precisa falar do ambiente do
// EMISSOR deste gestor, nao do padrao da instalacao: com um gestor em producao
// e o padrao em homologacao, a tela diria "nota de teste" e a nota sairia
// valendo. $objEmissorNfse ja foi carregado pelo -nfse-item.php.
$__ambienteNfse = defined('WB_FOCUS_NFE_AMBIENTE') ? WB_FOCUS_NFE_AMBIENTE : 'homologacao';
if (isset($objEmissorNfse) && trim((string) $objEmissorNfse->getAmbiente()) !== '') {
    $__ambienteNfse = trim((string) $objEmissorNfse->getAmbiente());
}
?>
<style>
    .nfse-nota { display:block; margin-top:8px; font-size:12px; padding:8px 10px;
                 border:1px solid #eee; border-radius:6px; background:#fff; }
    .nfse-nota-cab { margin-bottom:6px; }
    /* display:contents faz os botoes da nota participarem do flex da barra de
       acoes do cartao, como se estivessem escritos direto la. */
    .nfse-barra-alvo { display:contents; }
    .ped-acoes a.btn[disabled] { pointer-events:none; opacity:.45; }
    .nfse-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; margin-right:6px; }
    .nfse-badge-ok { background:#e8f5e9; color:#1b5e20; }
    .nfse-badge-proc { background:#fff8e1; color:#7a5c00; }
    .nfse-badge-erro { background:#fdecea; color:#b71c1c; }
    .nfse-badge-canc { background:#eceff1; color:#546e7a; }
    .nfse-erro-txt { color:#b71c1c; display:block; margin-left:2px; }
    .nfse-tomador { margin-top:14px; border:1px solid #eee; border-radius:6px; padding:10px 12px; background:#fafafa; }
    .nfse-tomador label { font-size:11px; color:#777; font-weight:600; margin-bottom:2px; }
    .nfse-tomador .form-control { height:30px; font-size:12px; padding:4px 8px; }
    .nfse-tomador .linha { display:flex; gap:8px; margin-bottom:8px; flex-wrap:wrap; }
    .nfse-tomador .c1 { flex:1 1 100%; } .nfse-tomador .c2 { flex:1 1 46%; }
    .nfse-tomador .c3 { flex:1 1 30%; } .nfse-tomador .c4 { flex:1 1 21%; }
    .nfse-toggle { font-size:12px; cursor:pointer; color:#1976d2; }
    .nfse-busca-wrap { position:relative; }
    .nfse-sugestoes { position:absolute; z-index:1080; left:0; right:0; background:#fff;
                      border:1px solid #ddd; border-top:none; max-height:230px; overflow-y:auto;
                      box-shadow:0 4px 10px rgba(0,0,0,.12); display:none; }
    .nfse-sugestao { padding:7px 10px; cursor:pointer; font-size:12px; border-bottom:1px solid #f2f2f2; }
    .nfse-sugestao:hover, .nfse-sugestao.ativa { background:#e3f2fd; }
    .nfse-sugestao b { color:#0d47a1; }
    .nfse-sugestao .freq { float:right; font-size:10px; color:#1b5e20; background:#e8f5e9;
                           padding:1px 6px; border-radius:8px; }
    .nfse-desc-trib { display:block; margin-top:4px; font-size:11px; color:#0d47a1; }
    .nfse-previa { width:100%; font-size:12px; border-collapse:collapse; }
    .nfse-previa th { text-align:left; vertical-align:top; width:130px; color:#888; font-weight:600;
                      padding:6px 8px 6px 0; border-bottom:1px solid #f0f0f0; white-space:nowrap; }
    .nfse-previa td { vertical-align:top; padding:6px 0; border-bottom:1px solid #f0f0f0; color:#333; overflow:hidden; }
    .nfse-previa td a.nfse-editar { float:right; font-size:11px; margin-left:10px; }
    .nfse-modo-cab { display:flex; justify-content:space-between; align-items:center;
                     border-bottom:1px solid #eee; padding-bottom:6px; margin-bottom:4px;
                     font-size:12px; text-transform:uppercase; color:#999; font-weight:600;
                     letter-spacing:0.4px; }
    .nfse-grupo { border:1px solid #e8e8e8; border-radius:6px; padding:12px; margin-top:12px; background:#fafafa; }
    .nfse-grupo-tit { font-size:11px; text-transform:uppercase; color:#888; font-weight:700;
                      letter-spacing:0.5px; margin-bottom:10px; }
    .nfse-grupo label { font-size:11px; color:#777; font-weight:600; margin-bottom:2px; display:block; }
    .nfse-grupo .form-control { font-size:12px; }
    .nfse-grupo input.form-control { height:30px; padding:4px 8px; }
    .nfse-grupo .linha { display:flex; gap:8px; margin-bottom:8px; flex-wrap:wrap; }
    .nfse-grupo .c1 { flex:1 1 100%; } .nfse-grupo .c2 { flex:1 1 40%; }
    .nfse-grupo .c3 { flex:1 1 26%; } .nfse-grupo .c4 { flex:1 1 15%; }
    .nfse-link { font-weight:normal; color:#1976d2; margin-left:8px; font-size:11px; }
    .nfse-previa small { color:#777; }
    .nfse-previa-avisos { background:#fdecea; border-left:3px solid #b71c1c; color:#7f1d1d;
                          padding:8px 12px; font-size:12px; margin-bottom:12px; }
    .amb-aviso-modal { padding:8px 12px; border-radius:6px; font-size:13px; margin-bottom:14px; }
    .amb-aviso-modal.amb-hom  { background:#fff8e1; border:1px solid #ffe082; color:#7a5c00; }
    .amb-aviso-modal.amb-prod { background:#e8f5e9; border:1px solid #a5d6a7; color:#1b5e20; }
    .nfse-solicitacao { display:block; margin:4px 0 6px; padding:6px 10px; font-size:12px;
                        background:#e3f2fd; border-left:3px solid #1976d2; color:#0d47a1; }
</style>
<script type="text/javascript">
    var _nfseAmbiente = '<?php echo $__ambienteNfse; ?>';
    var _nfseCodCancelar = null;
    var _nfsePedidoEmitir = null;
    var _nfseCodigoTributacao = {};   // padrao do emissor, por pedido

    $(document).ready(function() {
        $('.nfse-lista').each(function() {
            carregarNotasDoPedido($(this).data('cod-pedido'));
        });

        // Confirmar a emissao abre um modal em vez de um confirm() — mesmo
        // numero de cliques, mas com o codigo de tributacao a vista: o mesmo
        // CNPJ fatura servicos diferentes (mensalidade, inscricao, ingresso)
        // e cada um tem seu codigo.
        $(document).on('click', '.btn-emitir-nfse', function(e) {
            e.preventDefault();
            var codPedido = $(this).data('cod-pedido');
            _nfsePedidoEmitir = codPedido;

            var padrao = _nfseCodigoTributacao[codPedido] || '';
            $('#nfseCodTributacao').val(padrao);
            $('#nfseBuscaTributacao').val(padrao);
            $('#nfseDescTributacao').text('');
            _nfseTomadorPreenchido = false;
            _nfseDescricaoPreenchida = false;
            _nfseDescricaoOriginal = '';
            _nfseComplementoOriginal = '';
            _nfseDiscEditada = false;
            $('#nfseDescricaoServico').val('');
            $('#nfseComplemento').val('');
            $('#tomCepStatus').text('');
            $('#tomCompletarCadastro').prop('checked', true);
            $('#nfseSugestoes').hide().html('');
            $('#discServico').val('');
            $('#discComplemento').val('');
            mostrarModo('previa');
            $('#nfseMsgEmitir').html('');

            if (_nfseAmbiente !== 'producao') {
                $('#nfseAvisoAmbiente')
                    .attr('class', 'amb-aviso-modal amb-hom')
                    .html('<b>Ambiente de HOMOLOGAÇÃO.</b> A nota não tem valor fiscal.');
            } else {
                $('#nfseAvisoAmbiente')
                    .attr('class', 'amb-aviso-modal amb-prod')
                    .html('<b>Ambiente de PRODUÇÃO.</b> A nota terá valor fiscal e gera ISS.');
            }
            $('#previaNfse').html('<i style="color:#888;">carregando prévia...</i>');
            $('#btnConfirmarEmitirNfse').prop('disabled', true);
            $('#modalEmitirNfse').modal('show');
            carregarPreviaNfse(codPedido);
        });

        // O modal e pequeno: prever e editar ao mesmo tempo nao cabe. Entao ele
        // alterna entre DOIS MODOS — ou voce ve a nota como vai sair, ou edita
        // todos os campos de uma vez. Nada de painel por campo.
        $(document).on('click', '#btnIrParaEdicao, .nfse-editar', function(e) {
            e.preventDefault();
            mostrarModo('edicao');
        });

        $(document).on('click', '#btnIrParaPrevia', function(e) {
            e.preventDefault();
            aplicarEdicaoNaNota();
            mostrarModo('previa');
            if (_nfsePedidoEmitir) { carregarPreviaNfse(_nfsePedidoEmitir); }
        });

        // Campos do tomador: o CEP tem tratamento proprio (busca o endereco).
        $(document).on('change', '.nfse-tom', function() {
            if ($(this).attr('id') === 'tomCep') { return; }
        });

        $(document).on('input', '.nfse-disc', function() {
            _nfseDiscEditada = true;
        });

        $(document).on('click', '#discRestaurarServico', function(e) {
            e.preventDefault();
            $('#discServico').val(_nfseDescricaoOriginal);
            _nfseDiscEditada = true;
        });

        $(document).on('click', '#discRestaurarComplemento', function(e) {
            e.preventDefault();
            $('#discComplemento').val(_nfseComplementoOriginal);
            _nfseDiscEditada = true;
        });

        // So dispara quando o CEP muda: passar pelo campo sem alterar nao pode
        // reescrever um endereco que ja estava certo.
        $(document).on('focus', '#tomCep', function() {
            $(this).data('cepAnterior', ($(this).val() || '').replace(/\D/g, ''));
        });
        $(document).on('blur', '#tomCep', function() {
            var atual = ($(this).val() || '').replace(/\D/g, '');
            if ($(this).data('cepAnterior') === atual) { return; }
            buscaCepTomador($(this).val());
        });

        // Digitou: busca com um respiro, para nao disparar a cada tecla.
        $(document).on('input', '#nfseBuscaTributacao', function() {
            var termo = $(this).val();
            clearTimeout(_nfseBuscaTimer);
            _nfseBuscaTimer = setTimeout(function() { buscarTributacao(termo); }, 300);
        });

        $(document).on('focus', '#nfseBuscaTributacao', function() {
            if ($('#nfseSugestoes').html() === '') { buscarTributacao(''); }
            else { $('#nfseSugestoes').show(); }
        });

        $(document).on('click', '.nfse-sugestao', function() {
            selecionarTributacao($(this).data('codigo'), $(this).data('descricao'));
        });

        // Clicar fora fecha a lista
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.nfse-busca-wrap').length) { $('#nfseSugestoes').hide(); }
        });

        $(document).on('click', '.nfse-consultar', function(e) {
            e.preventDefault();
            consultarNfse($(this).data('cod-nota'), $(this).data('cod-pedido'));
        });

        $(document).on('click', '.nfse-ver-antigas', function(e) {
            e.preventDefault();
            $(this).next('.nfse-antigas').toggle();
        });

        $(document).on('click', '.nfse-cancelar', function(e) {
            e.preventDefault();
            _nfseCodCancelar = { nota: $(this).data('cod-nota'), pedido: $(this).data('cod-pedido') };
            $('#motivoCancelamentoNfse').val('');
            $('#modalCancelarNfse').modal('show');
        });

        $(document).on('change', '#tomUf', function() {
            carregarCidadesTomador($(this).val(), '');
        });

        $(document).on('click', '.nfse-enviar-email', function(e) {
            e.preventDefault();
            enviarNfsePorEmail($(this), $(this).data('cod-nota'), $(this).data('cod-pedido'));
        });

        $(document).on('click', '#btnConfirmarEnvioNfse', function(e) {
            e.preventDefault();
            confirmarEnvioNfse();
        });
    });

    // Guarda quem pediu o envio, para o modal saber o que confirmar.
    var _nfseEnvio = { $botao: null, codNota: 0, codPedido: 0, textoBotao: '' };

    /**
     * Abre a confirmacao do envio. Os e-mails de copia moram AQUI, e nao no
     * modal de emissao: campo que nao faz nada no momento em que e preenchido
     * confunde — quem digita a copia na emissao espera que ela seja enviada
     * ali. Aqui ele aparece junto da acao, e nao precisa de aviso nenhum.
     */
    function enviarNfsePorEmail($botao, codNota, codPedido) {
        _nfseEnvio = { $botao: $botao, codNota: codNota, codPedido: codPedido,
                       textoBotao: $botao.html() };

        $('#envioMsg').hide().empty();
        $('#envioPara').text('carregando...');
        $('#envioCopia').val('');
        $('#btnConfirmarEnvioNfse').prop('disabled', true);
        $('#modalEnvioNfse').modal('show');

        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'cod_nota_fiscal': codNota,
                'c': 'GerenciarNotaFiscal', 'm': 'dadosDoEnvio', '_submit_check': '1'
            },
            dataType: 'json', cache: false,
            success: function(data) {
                if (!data.statusMessage) {
                    $('#envioMsg').html('<div class="nfse-erro">' + esc(data.message) + '</div>').show();
                    return;
                }
                $('#envioPara').text(data.para || '(sem e-mail cadastrado)');
                $('#envioCopia').val(data.copia || '');
                $('#envioNumero').text(data.numero ? ('NF ' + data.numero) : 'a nota');
                $('#btnConfirmarEnvioNfse').prop('disabled', !data.para);
                if (!data.para) {
                    $('#envioMsg').html('<div class="nfse-erro">Sem e-mail do tomador e sem e-mail '
                        + 'no cadastro do inscrito. Baixe o PDF e envie por fora, ou cadastre o '
                        + 'e-mail antes.</div>').show();
                }
            },
            error: function() {
                $('#envioMsg').html('<div class="nfse-erro">Não foi possível carregar os dados do '
                    + 'envio.</div>').show();
            }
        });
    }

    // Confirmado: envia. O botao trava durante a chamada para o duplo clique
    // nao mandar duas vezes.
    function confirmarEnvioNfse() {
        var $btn = $('#btnConfirmarEnvioNfse');
        $btn.prop('disabled', true).text('Enviando...');
        _nfseEnvio.$botao.prop('disabled', true)
            .html('<i class="glyphicon glyphicon-envelope"></i> Enviando...');

        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'cod_nota_fiscal': _nfseEnvio.codNota,
                'email_copia': $('#envioCopia').val() || '',
                'c': 'GerenciarNotaFiscal', 'm': 'enviarPorEmail', '_submit_check': '1'
            },
            dataType: 'json', cache: false,
            success: function(data) {
                if (data.statusMessage) {
                    $('#modalEnvioNfse').modal('hide');
                    carregarNotasDoPedido(_nfseEnvio.codPedido);
                } else {
                    $('#envioMsg').html('<div class="nfse-erro">' + esc(data.message) + '</div>').show();
                    $btn.prop('disabled', false).text('Enviar agora');
                    _nfseEnvio.$botao.prop('disabled', false).html(_nfseEnvio.textoBotao);
                }
            },
            error: function() {
                $('#envioMsg').html('<div class="nfse-erro">Não foi possível enviar a nota. '
                    + 'Tente novamente.</div>').show();
                $btn.prop('disabled', false).text('Enviar agora');
                _nfseEnvio.$botao.prop('disabled', false).html(_nfseEnvio.textoBotao);
            }
        });
    }

    function carregarNotasDoPedido(codPedido) {
        var $alvo = $('.nfse-lista[data-cod-pedido="' + codPedido + '"]');
        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'cod_pedido': codPedido, 'c': 'GerenciarNotaFiscal', 'm': 'carregarNotasDoPedido', '_submit_check': '1'},
            dataType: 'json', cache: false,
            success: function(data) {
                if (!data.statusMessage) { $alvo.html(''); return; }

                if (data.emissor && data.emissor.codigoTributacaoNacionalIss) {
                    _nfseCodigoTributacao[codPedido] = data.emissor.codigoTributacaoNacionalIss;
                }

                var html = '';
                if (data.solicitacao) {
                    html += montarAvisoSolicitacao(data.solicitacao);
                }
                // Nota vigente (autorizada ou em processamento) trava o botao de
                // emitir: nao se emite duas vezes o mesmo pedido, e mexer na
                // previa depois de emitida so confunde — para trocar algo, o
                // caminho e cancelar a nota primeiro.
                var vigente = null;
                $.each(data.notas || [], function(i, n) {
                    if (!vigente && (n.status === 'AUTORIZADA' || n.status === 'PROCESSANDO')) { vigente = n; }
                });

                // A prefeitura leva alguns segundos para responder. Em vez de
                // deixar o gestor clicando em "Consultar status", a tela mesma
                // pergunta enquanto estiver PROCESSANDO — o botao continua ali
                // como saida manual quando a demora passa do normal.
                agendarConsultaAutomatica(vigente, codPedido);

                // Os botoes vao para DENTRO da barra de acoes do cartao, junto
                // de Alterar/Excluir/Whats — e o que o gestor le como "acoes
                // deste pedido". As notas ficam abaixo, que e informacao.
                $('.nfse-barra-alvo[data-cod-pedido="' + codPedido + '"]')
                    .html(montarBarraAcoes(vigente, codPedido));

                // Sem nota nao se escreve nada: repetido em 18 pedidos, o
                // "sem nota fiscal" so criava ruido. O proprio botao Emitir
                // ja diz que nao ha nota.
                if (!data.notas || data.notas.length === 0) {
                    $alvo.html(html);
                    return;
                } else {
                    // Tentativas rejeitadas se acumulam e afogam a informacao que
                    // importa. Mostra tudo que nao e rejeicao + a rejeicao mais
                    // recente; o resto vira um contador que se expande no clique.
                    var relevantes = [], rejeitadasAntigas = [];
                    var jaMostrouRejeitada = false;
                    $.each(data.notas, function(i, n) {
                        if (n.status !== 'ERRO') { relevantes.push(n); return; }
                        if (!jaMostrouRejeitada) { relevantes.push(n); jaMostrouRejeitada = true; }
                        else { rejeitadasAntigas.push(n); }
                    });

                    $.each(relevantes, function(i, n) {
                        html += montarLinhaNota(n, codPedido);
                    });

                    if (rejeitadasAntigas.length > 0) {
                        html += '<a href="#" class="nfse-ver-antigas" style="font-size:11px;">+ '
                             + rejeitadasAntigas.length + ' tentativa(s) anterior(es) rejeitada(s)</a>'
                             + '<span class="nfse-antigas" style="display:none;">';
                        $.each(rejeitadasAntigas, function(i, n) {
                            html += montarLinhaNota(n, codPedido);
                        });
                        html += '</span>';
                    }
                }
                $alvo.html(html);
            },
            error: function() { $alvo.html(''); }
        });
    }

    // O inscrito pediu a nota e ainda nao foi atendido — e o motivo de emitir agora.
    function montarAvisoSolicitacao(s) {
        var html = '<span class="nfse-solicitacao">';
        html += '<b>O inscrito solicitou nota fiscal</b>';
        if (s.dataSolicitacao) { html += ' em ' + esc(s.dataSolicitacao); }
        if (s.tomadorNome) {
            html += '<br>Em nome de: ' + esc(s.tomadorNome);
            if (s.tomadorCpfCnpj) { html += ' (' + esc(s.tomadorCpfCnpj) + ')'; }
        }
        if (s.observacao) { html += '<br>Obs.: ' + esc(s.observacao); }
        html += '</span>';
        return html;
    }

    /**
     * Barra unica de acoes do pedido — todos os botoes na MESMA linha, no
     * mesmo tamanho, na mesma ordem: emitir, baixar, enviar, cancelar.
     * A ordem segue o fluxo de trabalho, nao a estrutura dos dados.
     */
    function montarBarraAcoes(vigente, codPedido) {
        var base = '<?php echo WB_URL . "/" . WB_GRUPO . "/" . WB_PROJETO; ?>/api/nfse/';
        var b = '';

        // Emitir: primeira acao do fluxo, entao vem primeiro. Fica desabilitado
        // enquanto houver nota valendo — o caminho para trocar algo e cancelar.
        var podeEmitir = !vigente;
        var tituloEmitir = '';
        if (vigente) {
            tituloEmitir = (vigente.status === 'AUTORIZADA')
                ? 'Este pedido já tem nota autorizada. Cancele-a para emitir outra.'
                : 'A nota deste pedido está em processamento na prefeitura.';
        }
        b += '<button type="button" class="btn btn-sm btn-primary btn-emitir-nfse"'
          + ' data-cod-pedido="' + codPedido + '"'
          + (podeEmitir ? '' : ' disabled')
          + (tituloEmitir ? ' title="' + esc(tituloEmitir) + '"' : '')
          + '>Emitir NFS-e</button>';

        if (vigente && vigente.status === 'PROCESSANDO') {
            b += '<button type="button" class="btn btn-sm btn-default nfse-consultar"'
              + ' data-cod-nota="' + vigente.codNotaFiscal + '" data-cod-pedido="' + codPedido + '"'
              + ' title="A tela consulta sozinha; use se a prefeitura demorar.">Consultar status</button>';
        }

        if (vigente && vigente.status === 'AUTORIZADA') {
            b += '<a class="btn btn-sm btn-default" target="_blank" href="' + base + 'pdf?cod_nota_fiscal='
              + vigente.codNotaFiscal + '"' + (vigente.temPdf == 1 ? '' : ' disabled onclick="return false;"')
              + '>PDF</a>';
            b += '<a class="btn btn-sm btn-default" target="_blank" href="' + base + 'xml?cod_nota_fiscal='
              + vigente.codNotaFiscal + '"' + (vigente.temXml == 1 ? '' : ' disabled onclick="return false;"')
              + '>XML</a>';

            // Envio ao inscrito: uma vez so. Depois vira rotulo com data e
            // destinatario — reenviar a mesma nota so confunde quem recebe.
            if (vigente.dataEnvioEmail) {
                b += '<button type="button" class="btn btn-sm btn-default" disabled'
                  + ' title="Enviada em ' + esc(vigente.dataEnvioEmail) + ' para ' + esc(vigente.emailEnvio) + '"'
                  + '>Enviada por e-mail</button>';
            } else {
                b += '<button type="button" class="btn btn-sm btn-primary nfse-enviar-email"'
                  + ' data-cod-nota="' + vigente.codNotaFiscal + '" data-cod-pedido="' + codPedido + '"'
                  + '>Enviar por e-mail</button>';
            }

            b += '<button type="button" class="btn btn-sm btn-danger nfse-cancelar"'
              + ' data-cod-nota="' + vigente.codNotaFiscal + '" data-cod-pedido="' + codPedido + '"'
              + '>Cancelar nota</button>';
        }

        // Sem wrapper: o .nfse-barra-alvo usa display:contents, entao estes
        // botoes viram itens diretos do flex .ped-acoes e alinham com
        // Alterar/Excluir/Whats. Com um <div> no meio eles virariam UM
        // item so, desalinhado dos demais.
        return b;
    }

    /**
     * Linha da nota: so INFORMACAO. Os botoes vivem todos na barra — antes cada
     * nota carregava os seus e a tela virava um amontoado de botoes repetidos.
     */
    function montarLinhaNota(n, codPedido) {
        var classe = 'nfse-badge-proc';
        if (n.status === 'AUTORIZADA') { classe = 'nfse-badge-ok'; }
        else if (n.status === 'ERRO')  { classe = 'nfse-badge-erro'; }
        else if (n.status === 'CANCELADA') { classe = 'nfse-badge-canc'; }

        var html = '<div class="nfse-nota">';
        html += '<div class="nfse-nota-cab">';
        html += '<span class="nfse-badge ' + classe + '">' + esc(n.descricaoStatus) + '</span>';
        if (n.numero) { html += '<b>NF ' + esc(n.numero) + '</b> '; }
        if (n.ambiente && n.ambiente !== 'producao') { html += '<i style="color:#7a5c00;">(homologação)</i> '; }
        if (n.tomadorNome) { html += '<span style="color:#666;">— ' + esc(n.tomadorNome) + '</span>'; }
        html += '</div>';

        if (n.status === 'AUTORIZADA' && n.dataEnvioEmail) {
            html += '<span style="color:#2e7d32; display:block;">'
                 + 'enviada em ' + esc(n.dataEnvioEmail) + ' para ' + esc(n.emailEnvio) + '</span>';
        }
        if (n.status === 'ERRO' && n.mensagemErro) {
            html += '<span class="nfse-erro-txt">' + esc(n.mensagemErro) + '</span>';
        }
        if (n.status === 'CANCELADA' && n.motivoCancelamento) {
            html += '<span style="color:#546e7a; display:block;">motivo: ' + esc(n.motivoCancelamento) + '</span>';
        }
        html += '</div>';
        return html;
    }

    var _nfseBuscaTimer = null;

    // Autocomplete do codigo de tributacao: por numero OU por nome do servico.
    // Sao 335 codigos oficiais (LC 116) — ninguem decora, mas todo mundo sabe
    // o nome do que vende.
    function buscarTributacao(termo) {
        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'termo': termo, 'c': 'GerenciarNotaFiscal', 'm': 'buscarTributacao', '_submit_check': '1'},
            dataType: 'json', cache: false,
            success: function(data) {
                var $box = $('#nfseSugestoes');
                if (!data.statusMessage || !data.itens || data.itens.length === 0) {
                    $box.hide().html('');
                    return;
                }
                var html = '';
                $.each(data.itens, function(i, it) {
                    html += '<div class="nfse-sugestao" data-codigo="' + it.codigo + '"'
                         + ' data-descricao="' + $('<div>').text(it.descricao).html() + '">'
                         + (it.qtdUso > 0 ? '<span class="freq">usado ' + it.qtdUso + '×</span>' : '')
                         + '<b>' + it.codigo + '</b> — ' + $('<div>').text(it.descricao).html()
                         + '</div>';
                });
                $box.html(html).show();
            },
            error: function() { $('#nfseSugestoes').hide(); }
        });
    }

    function selecionarTributacao(codigo, descricao) {
        $('#nfseCodTributacao').val(codigo);
        $('#nfseBuscaTributacao').val(codigo);
        $('#nfseDescTributacao').text(descricao || '');
        $('#nfseSugestoes').hide();
        if (_nfsePedidoEmitir) { carregarPreviaNfse(_nfsePedidoEmitir); }
    }

    // Previa: mostra o que vai sair ANTES de gerar. Nao reserva numero de DPS
    // nem chama a Focus — so olhar nao pode queimar numero.
    function carregarPreviaNfse(codPedido) {
        var codTrib = ($('#nfseCodTributacao').val() || '').replace(/[^0-9]/g, '');
        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: $.extend({
                'cod_pedido': codPedido,
                'codigo_tributacao_nacional_iss': codTrib,
                'c': 'GerenciarNotaFiscal', 'm': 'previaEmissao', '_submit_check': '1'
            }, _nfseDiscEditada ? dadosDiscriminacaoDoForm() : {},
               _nfseTomadorPreenchido ? dadosTomadorDoForm() : {}),
            dataType: 'json', cache: false,
            success: function(data) {
                if (!data.statusMessage) {
                    $('#previaNfse').html('<span style="color:#b71c1c;">' + data.message + '</span>');
                    $('#btnConfirmarEmitirNfse').prop('disabled', true);
                    return;
                }
                $('#previaNfse').html(montarPreviaHtml(data));
                if (data.servico && data.servico.descricaoTributacao) {
                    $('#nfseDescTributacao').text(data.servico.descricaoTributacao);
                }
                preencherCamposTomador(data.tomador, data.avisos);
                if (!_nfseDescricaoPreenchida && data.servico) {
                    $('#nfseDescricaoServico').val(data.servico.parteServico || '');
                    $('#nfseComplemento').val(data.servico.parteComplemento || '');
                    $('#discServico').val(data.servico.parteServico || '');
                    $('#discComplemento').val(data.servico.parteComplemento || '');
                    _nfseDescricaoOriginal = data.servico.descricaoPedido || '';
                    _nfseComplementoOriginal = data.servico.complementoEmissor || '';
                    _nfseDescricaoPreenchida = true;
                }
                $('#btnConfirmarEmitirNfse').prop('disabled', false);
            },
            error: function() {
                $('#previaNfse').html('<span style="color:#b71c1c;">Falha ao carregar a prévia.</span>');
                $('#btnConfirmarEmitirNfse').prop('disabled', true);
            }
        });
    }

    function montarPreviaHtml(d) {
        var h = '';

        if (d.avisos && d.avisos.length > 0) {
            h += '<div class="nfse-previa-avisos"><b>Confira antes de emitir:</b><ul style="margin:6px 0 0 18px;">';
            $.each(d.avisos, function(i, a) { h += '<li>' + esc(a) + '</li>'; });
            h += '</ul></div>';
        }

        h += '<table class="nfse-previa">';
        h += linhaPrevia('Emissor', esc(d.emissor.razaoSocial) + '<br><small>CNPJ ' + esc(d.emissor.cnpj)
             + (d.emissor.serieDps ? ' &middot; série ' + esc(d.emissor.serieDps)
                + ', próximo nº ' + esc(d.emissor.proximoNumero) : '') + '</small>');
        h += linhaPrevia('Tomador',
               '<a href="#" class="nfse-editar">editar</a>'
             + (d.tomador.nome ? esc(d.tomador.nome) : '<i>não informado</i>')
             + '<br><small>' + (d.tomador.documento ? esc(d.tomador.documento) : 'sem CPF/CNPJ')
             + (d.tomador.endereco ? ' &middot; ' + esc(d.tomador.endereco) : '')
             + (d.tomador.cep ? ' &middot; CEP ' + esc(d.tomador.cep) : '')
             + (d.tomador.uf ? ' &middot; ' + esc(d.tomador.uf) : '')
             + (d.tomador.municipio ? ' &middot; IBGE ' + esc(d.tomador.municipio)
                : ' &middot; <span style="color:#b71c1c;">município não identificado</span>')
             // O e-mail entra na propria linha do tomador porque e para onde a
             // nota sera enviada depois — sem ele a vista, so se descobria o
             // destino no momento do envio.
             + '<br>' + (d.tomador.email
                ? '&#9993; ' + esc(d.tomador.email)
                : '<span style="color:#b71c1c;">sem e-mail — não será possível enviar a nota</span>')
             + '</small>');
        // Discriminacao e complemento em linhas separadas: e mais facil conferir
        // cada parte. Na nota os dois viram um campo so — por isso a ressalva.
        h += linhaPrevia('Discriminação',
               '<a href="#" class="nfse-editar">editar</a>'
             + '<span style="white-space:pre-wrap;">' + esc(d.servico.parteServico) + '</span>');

        h += linhaPrevia('Complemento',
               '<a href="#" class="nfse-editar">editar</a>'
             + (d.servico.parteComplemento
                ? '<span style="white-space:pre-wrap;">' + esc(d.servico.parteComplemento) + '</span>'
                : '<i style="color:#aaa;">sem informações complementares</i>')
             + '<br><small>Vai em campo próprio da nota (informações complementares), separado da discriminação.</small>');
        h += linhaPrevia('Valor', '<b>R$ ' + esc(d.servico.valor) + '</b>'
             + '<small> &middot; competência ' + esc(d.servico.competencia) + '</small>');
        h += linhaPrevia('Cód. tributação',
               '<a href="#" class="nfse-editar">editar</a>'
             + (d.servico.codigoTributacao ? esc(d.servico.codigoTributacao) : '<i>não informado</i>')
             + (d.servico.descricaoTributacao ? '<br><small>' + esc(d.servico.descricaoTributacao) + '</small>' : ''));
        h += '</table>';

        return h;
    }

    function linhaPrevia(rotulo, valor) {
        return '<tr><th>' + rotulo + '</th><td>' + valor + '</td></tr>';
    }

    // So preenche os campos na PRIMEIRA carga da previa: se o usuario ja digitou
    // algo, recarregar a previa nao pode apagar o que ele escreveu.
    var _nfseTomadorPreenchido = false;
    var _nfseDescricaoPreenchida = false;
    var _nfseDescricaoOriginal = '';
    var _nfseComplementoOriginal = '';
    // So passa a true quando o usuario aplica o modal de discriminacao. Antes
    // disso a tela NAO envia esses campos: string vazia seria lida como
    // "apagou de proposito" e a nota sairia sem descricao e sem rodape.
    var _nfseDiscEditada = false;

    function preencherCamposTomador(tom, avisos) {
        if (!tom || _nfseTomadorPreenchido) { return; }
        $('#tomNome').val(tom.nome || '');
        $('#tomDoc').val(tom.documento || '');
        $('#tomEmail').val(tom.email || '');
        $('#tomLogradouro').val(tom.logradouro || '');
        $('#tomNumero').val(tom.numeroEnd || '');
        $('#tomComplemento').val(tom.complemento || '');
        $('#tomBairro').val(tom.bairro || '');
        $('#tomCep').val(tom.cep || '');
        // UF manda na lista de cidades: seta a UF, carrega as cidades dela e
        // so entao seleciona a cidade. Fora dessa ordem o option nem existe.
        $('#tomUf').val(tom.uf || '');
        carregarCidadesTomador(tom.uf || '', tom.cidade || '');
        _nfseTomadorPreenchido = true;

        // Havendo aviso, a secao abre sozinha: o problema esta ali e o gestor
        // nao deveria ter que procurar onde resolver.
        if (avisos && avisos.length > 0) { mostrarModo('edicao'); }
    }

    /**
     * Carrega as cidades da UF no select do tomador e tenta selecionar uma.
     *
     * Existe porque digitar o municipio a mao era a porta de entrada do erro
     * mais caro da nota: municipio errado vai para a NFS-e e so se corrige
     * cancelando. O nome agora sai da nossa tabela, exato.
     *
     * async:false de proposito — a selecao depende dos options existirem.
     * Mesmo criterio do cadastro do inscrito (CarregarOptions@cidades).
     */
    function carregarCidadesTomador(uf, cidadeSelecionar) {
        var $sel = $('#tomCidade');
        if (!uf) {
            $sel.html('<option value="">Selecione a UF primeiro</option>');
            return;
        }
        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>',
            data: {'uf': uf, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1'},
            dataType: 'html', async: false, cache: false,
            success: function (html) {
                $sel.html(html);
                if (!cidadeSelecionar) { return; }

                // 1) casa exato
                $sel.val(cidadeSelecionar);
                if ($sel.val()) { return; }

                // 2) casa ignorando acento e caixa (ViaCEP x nossa tabela)
                var alvo = normalizarNome(cidadeSelecionar);
                var achou = '';
                $sel.find('option').each(function () {
                    if (!achou && normalizarNome($(this).val()) === alvo) { achou = $(this).val(); }
                });
                if (achou) { $sel.val(achou); }
            }
        });
    }

    function normalizarNome(s) {
        return (s || '').toString().trim().toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '');
    }

    // Mesma busca de CEP do cadastro do inscrito, aqui tambem: digitar rua,
    // bairro e cidade a mao no meio de uma emissao e onde o erro entra — e o
    // municipio errado vai direto para a nota fiscal.
    //
    // Aqui cidade e campo de TEXTO (nao select como no cadastro), entao basta
    // escrever; o codigo IBGE e resolvido no servidor a partir de cidade + UF.
    function buscaCepTomador(cep) {
        var cepLimpo = (cep || '').replace(/\D/g, '');
        if (cepLimpo === '') { return; }

        if (!/^[0-9]{8}$/.test(cepLimpo)) {
            $('#tomCepStatus').css('color', '#b71c1c').text('— formato inválido');
            return;
        }

        $('#tomCepStatus').css('color', '#1976d2').text('— buscando...');

        $.getJSON("https://viacep.com.br/ws/" + cepLimpo + "/json/?callback=?", function (dados) {
            if ("erro" in dados) {
                $('#tomCepStatus').css('color', '#b71c1c').text('— CEP não encontrado');
                return;
            }
            // Endereco novo vem inteiro: misturar rua antiga com cidade nova
            // produz endereco que nao existe.
            if (dados.logradouro) { $('#tomLogradouro').val(dados.logradouro); }
            if (dados.bairro)     { $('#tomBairro').val(dados.bairro); }
            if (dados.uf) {
                $('#tomUf').val(dados.uf);
                // O ViaCEP devolve o nome sem acento em alguns casos; quem casa
                // com a nossa tabela e o carregarCidadesTomador, que tenta
                // exato e depois sem acento/caixa.
                carregarCidadesTomador(dados.uf, dados.localidade || '');
            }

            $('#tomCepStatus').css('color', '#1b5e20').text('— ' + dados.localidade + '/' + dados.uf);
            $('#tomNumero').focus();

            // Recarrega a previa: o municipio (IBGE) so aparece depois que o
            // servidor resolve cidade + UF.
            if (_nfsePedidoEmitir) { carregarPreviaNfse(_nfsePedidoEmitir); }
        }).fail(function () {
            $('#tomCepStatus').css('color', '#b71c1c').text('— não foi possível consultar');
        });
    }


    // Tudo que vem de dado do usuario passa por aqui antes de virar HTML.
    // A observacao da solicitacao, por exemplo, e texto digitado pelo INSCRITO:
    // sem escapar, ele injetaria script que roda na sessao do gestor.
    function esc(v) {
        if (v === null || v === undefined) { return ''; }
        return $('<div>').text(v).html();
    }

    function mostrarModo(modo) {
        if (modo === 'edicao') {
            $('#nfseModoPrevia').hide();
            $('#nfseModoEdicao').show();
            $('#tomNome').focus();
        } else {
            $('#nfseModoEdicao').hide();
            $('#nfseModoPrevia').show();
        }
        $('#nfseSugestoes').hide();
    }

    // Leva o que foi digitado para os campos que a emissao envia. Roda ao
    // voltar para a previa e antes de emitir, para nao depender de o usuario
    // ter saido do ultimo campo.
    function aplicarEdicaoNaNota() {
        $('#nfseDescricaoServico').val($('#discServico').val() || '');
        $('#nfseComplemento').val($('#discComplemento').val() || '');
    }

    function dadosDiscriminacaoDoForm() {
        return {
            'descricao_servico': $('#nfseDescricaoServico').val() || '',
            'informacoes_complementares': $('#nfseComplemento').val() || ''
        };
    }

    function dadosTomadorDoForm() {
        return {
            'tomador_nome':        $('#tomNome').val() || '',
            'tomador_cpf_cnpj':    $('#tomDoc').val() || '',
            'tomador_email':       $('#tomEmail').val() || '',
            'tomador_logradouro':  $('#tomLogradouro').val() || '',
            'tomador_numero':      $('#tomNumero').val() || '',
            'tomador_complemento': $('#tomComplemento').val() || '',
            'tomador_bairro':      $('#tomBairro').val() || '',
            'tomador_cep':         $('#tomCep').val() || '',
            'tomador_cidade':      $('#tomCidade').val() || '',
            'tomador_uf':          $('#tomUf').val() || ''
        };
    }

    function confirmarEmissaoNfse() {
        var codPedido = _nfsePedidoEmitir;
        if (!codPedido) { return; }

        // Pega o que estiver digitado agora, mesmo que o foco ainda esteja num campo.
        aplicarEdicaoNaNota();

        var codTrib = ($('#nfseCodTributacao').val() || '').replace(/[^0-9]/g, '');
        if (codTrib !== '' && codTrib.length !== 6) {
            $('#nfseMsgEmitir').html('<span style="color:#b71c1c;">O codigo de tributacao deve ter 6 digitos.</span>');
            return;
        }

        var $btn = $('#btnConfirmarEmitirNfse');
        $btn.prop('disabled', true).text('Emitindo...');

        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: $.extend({
                'cod_pedido': codPedido,
                'codigo_tributacao_nacional_iss': codTrib,
                'c': 'GerenciarNotaFiscal', 'm': 'emitir', '_submit_check': '1',
                'completar_cadastro': $('#tomCompletarCadastro').is(':checked') ? '1' : '0'
            }, _nfseDiscEditada ? dadosDiscriminacaoDoForm() : {}, dadosTomadorDoForm()),
            dataType: 'json', cache: false,
            success: function(data) {
                $('#modalEmitirNfse').modal('hide');
                alert(data.message);
                carregarNotasDoPedido(codPedido);
            },
            error: function() { alert('Falha na comunicação ao emitir a nota.'); },
            complete: function() {
                $btn.prop('disabled', false).text('Emitir nota');
            }
        });
    }

    // Consulta automatica enquanto a nota esta PROCESSANDO.
    // Intervalos crescentes: a prefeitura costuma responder nos primeiros
    // segundos, mas quando demora nao adianta martelar — vai espacando ate
    // desistir e deixar o botao manual assumir.
    var _nfseTimers = {};
    var _nfseTentativas = {};
    var NFSE_INTERVALOS = [3000, 4000, 6000, 8000, 10000, 15000, 20000, 30000];

    function agendarConsultaAutomatica(vigente, codPedido) {
        if (_nfseTimers[codPedido]) {
            clearTimeout(_nfseTimers[codPedido]);
            _nfseTimers[codPedido] = null;
        }

        // Saiu de PROCESSANDO (autorizou, rejeitou ou nao ha nota): zera o
        // contador para a proxima emissao comecar do inicio.
        if (!vigente || vigente.status !== 'PROCESSANDO') {
            _nfseTentativas[codPedido] = 0;
            return;
        }

        var tentativa = _nfseTentativas[codPedido] || 0;
        if (tentativa >= NFSE_INTERVALOS.length) {
            return; // desiste em silencio; o botao "Consultar status" continua la
        }
        _nfseTentativas[codPedido] = tentativa + 1;

        _nfseTimers[codPedido] = setTimeout(function() {
            // A aba pode ter sido fechada/trocada no meio do caminho.
            if (!$('.nfse-lista[data-cod-pedido="' + codPedido + '"]').length) { return; }
            consultarNfse(vigente.codNotaFiscal, codPedido, true);
        }, NFSE_INTERVALOS[tentativa]);
    }

    /**
     * @param silencioso true = consulta de fundo, sem alert. So avisa quando
     *        o gestor clicou no botao — alert automatico atrapalharia quem
     *        esta fazendo outra coisa na tela.
     */
    function consultarNfse(codNota, codPedido, silencioso) {
        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'cod_nota_fiscal': codNota, 'c': 'GerenciarNotaFiscal', 'm': 'consultarStatus', '_submit_check': '1'},
            dataType: 'json', cache: false,
            success: function(data) {
                if (!silencioso) { alert(data.message); }
                carregarNotasDoPedido(codPedido);
            },
            error: function() {
                if (!silencioso) { alert('Falha ao consultar o status da nota.'); }
            }
        });
    }

    function confirmarCancelamentoNfse() {
        if (!_nfseCodCancelar) { return; }
        var motivo = $('#motivoCancelamentoNfse').val();
        if (!motivo || motivo.trim().length < 15) {
            alert('Descreva o motivo do cancelamento (mínimo 15 caracteres).');
            return;
        }

        // Cancelamento vai para a prefeitura e tem prazo legal: uma vez feito,
        // nao se desfaz. Vale a pergunta a mais.
        if (!confirm('Cancelar esta nota fiscal na prefeitura?\n\n'
                   + 'A ação não pode ser desfeita e tem prazo legal — fora do prazo,'
                   + ' só é possível substituir.')) {
            return;
        }
        $.ajax({
            type: 'POST', url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'cod_nota_fiscal': _nfseCodCancelar.nota,
                'motivo_cancelamento': motivo,
                'c': 'GerenciarNotaFiscal', 'm': 'cancelar', '_submit_check': '1'
            },
            dataType: 'json', cache: false,
            success: function(data) {
                alert(data.message);
                if (data.statusMessage) {
                    carregarNotasDoPedido(_nfseCodCancelar.pedido);
                    $('#modalCancelarNfse').modal('hide');
                }
            },
            error: function() { alert('Falha ao cancelar a nota.'); }
        });
    }
</script>

<div class="modal fade" id="modalEmitirNfse" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Emitir NFS-e</h4>
            </div>

            <div class="modal-body">
                <div id="nfseAvisoAmbiente"></div>
                <div id="nfseMsgEmitir" style="margin-bottom:8px;"></div>

                <input type="hidden" id="nfseCodTributacao">
                <input type="hidden" id="nfseDescricaoServico">
                <input type="hidden" id="nfseComplemento">

                <!-- ================= MODO PREVIA ================= -->
                <div id="nfseModoPrevia">
                    <div class="nfse-modo-cab">
                        <span>Prévia da nota</span>
                        <button type="button" class="btn btn-default btn-xs" id="btnIrParaEdicao">
                            <i class="glyphicon glyphicon-pencil"></i> Editar dados
                        </button>
                    </div>
                    <div id="previaNfse" style="margin-top:10px;"></div>
                </div>

                <!-- ================= MODO EDICAO ================= -->
                <div id="nfseModoEdicao" style="display:none;">
                    <div class="nfse-modo-cab">
                        <span>Editando os dados da nota</span>
                        <button type="button" class="btn btn-primary btn-xs" id="btnIrParaPrevia">
                            <i class="glyphicon glyphicon-eye-open"></i> Ver prévia
                        </button>
                    </div>

                    <div class="nfse-grupo">
                        <div class="nfse-grupo-tit">Tomador</div>
                        <div class="linha">
                            <div class="c2"><label>Nome / razão social</label>
                                <input type="text" class="form-control nfse-tom" id="tomNome" maxlength="150"></div>
                            <div class="c2"><label>CPF / CNPJ</label>
                                <input type="text" class="form-control nfse-tom" id="tomDoc" maxlength="20"></div>
                            <?php // Aqui so o e-mail que vai DENTRO do documento fiscal. Os
                                  // enderecos de copia ficam no modal de envio, junto da acao
                                  // que os usa — na emissao seriam um campo que nao faz nada
                                  // no momento em que e preenchido. ?>
                            <div class="c2"><label>E-mail <small style="font-weight:normal; color:#999;">(vai na nota)</small></label>
                                <input type="email" class="form-control nfse-tom" id="tomEmail" maxlength="150"></div>
                        </div>
                        <div class="linha">
                            <div class="c4"><label>CEP <span id="tomCepStatus" style="font-weight:normal; color:#1976d2;"></span></label>
                                <input type="text" class="form-control nfse-tom" id="tomCep" maxlength="10"
                                       placeholder="preenche o resto"></div>
                            <div class="c2"><label>Logradouro</label>
                                <input type="text" class="form-control nfse-tom" id="tomLogradouro" maxlength="255"></div>
                            <div class="c4"><label>Número</label>
                                <input type="text" class="form-control nfse-tom" id="tomNumero" maxlength="20"></div>
                        </div>
                        <div class="linha">
                            <div class="c4"><label>Complemento</label>
                                <input type="text" class="form-control nfse-tom" id="tomComplemento" maxlength="100"></div>
                            <div class="c3"><label>Bairro</label>
                                <input type="text" class="form-control nfse-tom" id="tomBairro" maxlength="100"></div>
                            <div class="c4"><label>UF</label>
                                <select class="form-control nfse-tom" id="tomUf">
                                    <option value="">--</option>
                                    <?php CarregarOptionsAC::estados(array('estado' => '')); ?>
                                </select></div>
                            <div class="c3"><label>Cidade</label>
                                <select class="form-control nfse-tom" id="tomCidade">
                                    <option value="">Selecione a UF primeiro</option>
                                </select></div>
                        </div>
                        <label style="font-weight:normal; color:#555; font-size:12px; margin-top:2px;">
                            <input type="checkbox" id="tomCompletarCadastro" checked>
                            Completar o cadastro do inscrito com o que estiver <b>vazio</b> lá
                        </label>
                    </div>

                    <div class="nfse-grupo">
                        <div class="nfse-grupo-tit">Serviço</div>
                        <div class="linha">
                            <div class="c1">
                                <label>Discriminação
                                    <a href="#" id="discRestaurarServico" class="nfse-link">restaurar a do pedido</a>
                                </label>
                                <textarea id="discServico" class="form-control nfse-disc" rows="4" maxlength="1800"></textarea>
                            </div>
                        </div>
                        <div class="linha">
                            <div class="c1">
                                <label>Informações complementares
                                    <a href="#" id="discRestaurarComplemento" class="nfse-link">restaurar a do emissor</a>
                                </label>
                                <textarea id="discComplemento" class="form-control nfse-disc" rows="2" maxlength="900"
                                          placeholder="dados bancários, chave PIX, observações"></textarea>
                            </div>
                        </div>
                        <div class="linha">
                            <div class="c1">
                                <label for="nfseBuscaTributacao">Código de tributação nacional do ISS</label>
                                <div class="nfse-busca-wrap">
                                    <input type="text" id="nfseBuscaTributacao" class="form-control" autocomplete="off"
                                           placeholder="digite o código ou o nome do serviço (ex.: 0107 ou treinamento)">
                                    <div id="nfseSugestoes" class="nfse-sugestoes"></div>
                                </div>
                                <span id="nfseDescTributacao" class="nfse-desc-trib"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEmitirNfse"
                        onclick="confirmarEmissaoNfse();">Emitir nota</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCancelarNfse" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Cancelar nota fiscal</h4>
            </div>
            <div class="modal-body">
                <p style="font-size:13px; color:#555;">
                    O cancelamento é enviado à prefeitura e tem prazo legal — fora do prazo,
                    só substituição. Descreva o motivo (fica registrado na nota).
                </p>
                <textarea id="motivoCancelamentoNfse" class="form-control" rows="3"
                          maxlength="500" placeholder="Motivo do cancelamento"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Voltar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarCancelamentoNfse();">
                    Cancelar a nota
                </button>
            </div>
        </div>
    </div>
</div>

<!--
  Confirmacao do envio por e-mail.
  Substituiu o confirm() do navegador porque a decisao aqui tem dado que
  precisa ser VISTO e ate alterado: para quem vai, com copia para quem. E o
  lugar dos e-mails de copia — na emissao eles seriam um campo que nao faz
  nada no momento em que e preenchido.
-->
<div class="modal fade" id="modalEnvioNfse" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Enviar <span id="envioNumero">a nota</span> por e-mail</h4>
            </div>
            <div class="modal-body">
                <div id="envioMsg" style="display:none;"></div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:12px; color:#888; margin-bottom:2px;">Para</label>
                    <div id="envioPara" style="font-weight:600;"></div>
                    <small class="text-muted">E-mail do tomador da nota. Para trocar, cancele e
                        edite o cadastro do inscrito.</small>
                </div>

                <div class="form-group">
                    <label for="envioCopia">Enviar cópia para <small style="font-weight:normal; color:#999;">(opcional)</small></label>
                    <input type="text" class="form-control" id="envioCopia" maxlength="500"
                           placeholder="financeiro@empresa.com.br, financeiro@empresa.com.br">
                    <small class="text-muted">Vários endereços separados por vírgula. Fica guardado
                        para as próximas notas deste cliente.</small>
                </div>

                <p style="font-size:12px; color:#666; margin-bottom:0;">
                    Vão anexados o <b>PDF</b> e o <b>XML</b> da nota.
                    Esta nota é enviada <b>uma única vez</b> — depois o botão fica travado.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEnvioNfse" disabled>
                    Enviar agora
                </button>
            </div>
        </div>
    </div>
</div>
