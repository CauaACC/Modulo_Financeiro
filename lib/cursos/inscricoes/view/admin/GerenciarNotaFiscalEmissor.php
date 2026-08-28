<?php
// Gate da propria view: nao depender so do controller, senao a tela renderiza
// (e vaza estrutura/labels) para quem nao tem permissao.
// Cadastro do emissor (CNPJ, certificado, serie, tributacao) e configuracao fiscal
// da empresa -- nao e operacao do dia a dia do financeiro. So ADM_GERAL.
try {
    FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
} catch (Exception $e) {
    echo "<div class='alert alert-danger' style='margin:20px;'>"
       . "<b>Acesso nao permitido.</b> Esta tela e restrita ao Administrador Geral."
       . "</div>";
    return;
}

$objSRV = new GerenciarNotaFiscalEmissorAC();
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<style>
    .tbl-fin { width:100%; border-collapse:collapse; font-size:13px; table-layout:auto; }
    .tbl-fin thead th { font-size:11px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.3px; padding:12px 10px; border-bottom:1px solid #e0e0e0; background:#f5f6f8; white-space:nowrap; }
    .tbl-fin tbody tr { border-bottom:1px solid #f0f0f0; }
    .tbl-fin tbody tr:hover { background:#f9fafb; }
    .tbl-fin tbody td { padding:6px 10px; vertical-align:middle; color:#333; white-space:normal; word-break:break-word; }
    .tbl-fin td.col-codigo, .tbl-fin th.col-codigo { white-space:nowrap; width:1%; }
    .tbl-fin td.col-badge, .tbl-fin th.col-badge { white-space:nowrap; width:1%; }
    .tbl-fin tbody td.col-nome { font-weight:600; }
    .tbl-fin .badge-tipo { display:inline-flex; align-items:center; font-size:12px; font-weight:500; padding:3px 10px; border-radius:12px; background:#f0f1f3; }
    .tbl-fin .badge-tipo .dot { width:8px; height:8px; border-radius:50%; margin-right:6px; display:inline-block; }
    .tbl-fin .badge-tipo .dot-green { background:#27ae60; }
    .tbl-fin .badge-tipo .dot-blue { background:#95a5a6; }
    .btn-acao { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#888; cursor:pointer; margin-right:4px; }
    .btn-acao:hover { background:#f5f5f5; color:#333; }
    .cx-nf { background:#fff; border:1px solid #e5e5e5; border-radius:8px; padding:18px; margin-bottom:26px; }
    .cx-nf h3 { margin-top:0; font-size:18px; }
    .ajuda-campo { font-size:11px; color:#888; display:block; margin-top:2px; }
    .sec-titulo { font-size:12px; text-transform:uppercase; color:#999; font-weight:600; letter-spacing:0.4px; margin:18px 0 8px; border-bottom:1px solid #eee; padding-bottom:4px; }
    .amb-aviso { padding:8px 12px; border-radius:6px; font-size:13px; margin-bottom:14px; }
    .amb-homologacao { background:#fff8e1; border:1px solid #ffe082; color:#7a5c00; }
    .amb-producao { background:#e8f5e9; border:1px solid #a5d6a7; color:#1b5e20; }
    .bloco-nfe { background:#fafbfc; border:1px solid #eceef0; border-radius:6px; padding:14px; }
    .aviso-serie { background:#fff8e1; border:1px solid #ffe082; color:#7a5c00; padding:8px 12px; border-radius:6px; font-size:12px; }
</style>
<script type="text/javascript">
    var _codAlternar = null;

    $(document).ready(function() {
        carregarTabelaEmissores();
        carregarOptionsGestor('');
        $('#btnSalvarEmissor').click(function() { salvarEmissor(); });
        $('#btnTestarConexao').click(function() { testarConexao(); });
    });

    function bindEventosTabela() {
        $('.editar').off('click').on('click', function(e) {
            e.preventDefault();
            editarEmissor($(this).parents("tr").attr("id"));
        });
        $('.alternar').off('click').on('click', function(e) {
            e.preventDefault();
            _codAlternar = $(this).parents("tr").attr("id");
            $('#modalAlternar').modal('show');
        });
    }

    function carregarTabelaEmissores() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarNotaFiscalEmissor', 'm': 'carregarTabelaEmissores', '_submit_check': '1'},
            function(html) {
                $('#itens').find('tbody.inputs').remove();
                $('#itens').append('<tbody class="inputs">' + html + '</tbody>');
                bindEventosTabela();
            }
        );
    }

    function carregarOptionsGestor(codGestor) {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'GerenciarNotaFiscalEmissor', 'm': 'carregarOptionsGestor', 'codGestor': codGestor, '_submit_check': '1'},
            function(html) { $('#codGestor').html(html); }
        );
    }

    function abrirModalNovo() {
        limparCampos();
        carregarOptionsGestor('');
        $('#modalEmissorLabel').html('Novo emissor de Nota Fiscal');
        $('#modalEmissor').modal('show');
    }

    function salvarEmissor() {
        $('#formEmissor').ajaxSubmit({
            target: '#modalEmissorMsg', dataType: 'json', async: false, cache: false,
            beforeSend: function() { startLoading(); },
            error: function(ajax) { showResponse(ajax); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaEmissores();
                    $('#modalEmissor').modal('hide');
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }

    function editarEmissor(codEmissor) {
        if (!codEmissor) return;
        $.ajax({
            type: "POST", url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codEmissor': codEmissor, 'c': 'GerenciarNotaFiscalEmissor', 'm': 'editarEmissor', '_submit_check': '1'},
            dataType: "json", async: false, cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                $('#codEmissor').val(data.codEmissor);
                $('#razaoSocial').val(data.razaoSocial);
                $('#cnpj').val(data.cnpj);
                $('#inscricaoMunicipal').val(data.inscricaoMunicipal);
                $('#codigoMunicipio').val(data.codigoMunicipio);
                $('#logradouro').val(data.logradouro);
                $('#numero').val(data.numero);
                $('#complemento').val(data.complemento);
                $('#bairro').val(data.bairro);
                $('#cep').val(data.cep);
                $('#uf').val(data.uf);
                $('#telefone').val(data.telefone);
                $('#email').val(data.email);
                // '' cai no "Padrao do sistema" (usa WB_FOCUS_NFE_AMBIENTE)
                $('#ambiente').val(data.ambiente || '');
                $('#codigoOpcaoSimplesNacional').val(data.codigoOpcaoSimplesNacional);
                $('#regimeEspecialTributacao').val(data.regimeEspecialTributacao);
                $('#codigoTributacaoNacionalIss').val(data.codigoTributacaoNacionalIss);
                $('#codigoNbs').val(data.codigoNbs);
                $('#tributacaoIss').val(data.tributacaoIss);
                $('#tipoRetencaoIss').val(data.tipoRetencaoIss);
                $('#percentualAliquota').val(data.percentualAliquota);
                $('#informacoesComplementares').val(data.informacoesComplementares);
                $('#serieDps').val(data.serieDps);
                $('#proximoNumeroDps').val(data.proximoNumeroDps);
                // Bloco de NF-e: vem vazio nos emissores que so fazem servico.
                $('#inscricaoEstadual').val(data.inscricaoEstadual);
                $('#codigoRegimeTributario').val(data.codigoRegimeTributario);
                $('#csosnPadrao').val(data.csosnPadrao);
                $('#cstPisCofins').val(data.cstPisCofins);
                $('#cfopDentroEstado').val(data.cfopDentroEstado);
                $('#cfopForaEstado').val(data.cfopForaEstado);
                $('#naturezaOperacao').val(data.naturezaOperacao);
                $('#indicadorPresenca').val(data.indicadorPresenca);
                $('#indicadorConsumidorFinal').val(data.indicadorConsumidorFinal);
                $('#indicadorIeDestinatario').val(data.indicadorIeDestinatario);
                $('#indicadorIntermediador').val(data.indicadorIntermediador);
                $('#tipoPagamento').val(data.tipoPagamento);
                $('#modalidadeFrete').val(data.modalidadeFrete);
                $('#serieNfe').val(data.serieNfe);
                $('#proximoNumeroNfe').val(data.proximoNumeroNfe);
                $('#informacoesComplementaresNfe').val(data.informacoesComplementaresNfe);
                carregarOptionsGestor(data.codGestor);
                $('#modalEmissorLabel').html('Editar emissor de Nota Fiscal');
                $('#modalEmissor').modal('show');
            },
            complete: function() { stopLoading(); }
        });
    }

    function confirmarAlternar() {
        if (!_codAlternar) return;
        $.ajax({
            type: "POST", url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codEmissor': _codAlternar, 'c': 'GerenciarNotaFiscalEmissor', 'm': 'alternarStatusEmissor', '_submit_check': '1'},
            dataType: "json", async: false, cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) { carregarTabelaEmissores(); }
                $('#modalAlternar').modal('hide');
                _codAlternar = null;
            },
            complete: function() { stopLoading(); }
        });
    }

    function testarConexao() {
        $('#resultadoTeste').html('Testando...');
        $.ajax({
            type: "POST", url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'c': 'GerenciarNotaFiscal', 'm': 'testarConexao', '_submit_check': '1'},
            dataType: "json", cache: false,
            success: function(data) {
                var cor = data.statusMessage ? '#1b5e20' : '#b71c1c';
                var html = '<div style="color:' + cor + '; font-weight:600;">' + data.message + '</div>';
                if (data.emissor)  { html += '<div>Emissor: ' + data.emissor + '</div>'; }
                // O ambiente e a informacao que decide se a nota vale ou nao vale
                // fiscalmente. Antes o teste dizia "conexao ok" sem contar ONDE --
                // e agora cada emissor pode estar num ambiente diferente.
                if (data.ambiente) {
                    var teste = (data.ambiente !== 'producao');
                    html += '<div style="margin-top:6px; padding:6px 10px; border-radius:4px; font-weight:600;'
                          + (teste ? 'background:#fcf8e3; color:#8a6d3b; border:1px solid #faebcc;'
                                   : 'background:#dff0d8; color:#3c763d; border:1px solid #d6e9c6;') + '">'
                          + 'Ambiente: ' + data.ambiente
                          + (teste ? ' — nota SEM validade fiscal' : ' — nota com validade fiscal')
                          + '</div>';
                }
                if (data.municipio) {
                    html += '<div>Municipio: ' + data.municipio + ' — provedor ' + (data.provedor || '?') + '</div>';
                    html += '<div>NFS-e habilitada: ' + (data.nfseHabilitada == 1 ? 'sim' : 'nao')
                          + ' | exige certificado: ' + (data.requerCertificado == 1 ? 'sim' : 'nao') + '</div>';
                }
                $('#resultadoTeste').html(html);
            },
            error: function() { $('#resultadoTeste').html('<span style="color:#b71c1c;">Falha ao testar a conexao.</span>'); }
        });
    }

    function limparCampos() {
        $('#formEmissor')[0].reset();
        $('#codEmissor').val('');
        $('#getMessage').html('');
        $('#modalEmissorMsg').html('');
    }
</script>

<?php
// Cabecalho e menus da area administrativa: sem isto a tela abre "solta",
// fora do modulo, sem submenu. Cada view do admin inclui os seus.
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
// A tela mudou de casa: agora vive em Financeiro > Fiscal, ao lado de Notas Fiscais.
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");
?>

<div class="cx-nf">
    <h3>Emissores de Nota Fiscal</h3>
    <p style="color:#666; font-size:13px;">
        Dados fiscais de quem emite a nota. Cada gestor tem o seu — o evento usa o emissor do gestor dele.
    </p>

    <?php
    // Esta faixa fala do PADRAO da instalacao (env.php). Desde que cada emissor
    // pode ter ambiente proprio, ela nao pode mais ser lida como "o ambiente de
    // todo mundo" — quem manda na emissao e a coluna do emissor. O ambiente real
    // de cada um aparece na coluna Ambiente da listagem abaixo.
    $ambiente = defined('WB_FOCUS_NFE_AMBIENTE') ? WB_FOCUS_NFE_AMBIENTE : 'homologacao';
    if ($ambiente == 'producao') { ?>
        <div class="amb-aviso amb-producao">
            <b>Padrão do sistema: PRODUÇÃO.</b> Vale para os emissores marcados como
            "Padrão do sistema" — nesses, a nota tem valor fiscal.
        </div>
    <?php } else { ?>
        <div class="amb-aviso amb-homologacao">
            <b>Padrão do sistema: HOMOLOGAÇÃO.</b> Vale para os emissores marcados como
            "Padrão do sistema" — nesses, a nota é de teste e não tem valor fiscal.
        </div>
    <?php } ?>

    <div style="margin-bottom:14px;">
        <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalNovo();">
            <i class="fa fa-plus"></i> Novo emissor
        </button>
        <button type="button" class="btn btn-default btn-sm" id="btnTestarConexao">
            <i class="fa fa-plug"></i> Testar conexão com a Focus NFe
        </button>
        <div id="resultadoTeste" style="margin-top:10px; font-size:13px;"></div>
    </div>

    <table class="tbl-fin" id="itens">
        <thead>
            <tr>
                <th class="col-codigo">Ações</th>
                <th>Razão social</th>
                <th class="col-codigo">CNPJ</th>
                <th>Gestor</th>
                <th class="col-codigo">Insc. municipal</th>
                <th class="col-badge">Ambiente</th>
                <th class="col-badge">Status</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal de cadastro -->
<div class="modal fade" id="modalEmissor" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formEmissor" name="formEmissor" method="post"
                  action="<?php echo WB_URL_COMMAND ?>" enctype="multipart/form-data">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modalEmissorLabel">Novo emissor de Nota Fiscal</h4>
                </div>

                <div class="modal-body">
                    <div id="modalEmissorMsg"></div>

                    <div class="sec-titulo">Identificação</div>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Gestor *</label>
                            <select name="codGestor" id="codGestor" class="form-control"></select>
                        </div>
                        <div class="col-md-5">
                            <label>Razão social *</label>
                            <input type="text" name="razaoSocial" id="razaoSocial" class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-3">
                            <label>CNPJ *</label>
                            <input type="text" name="cnpj" id="cnpj" class="form-control" maxlength="20">
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-4">
                            <label>Ambiente de emissão</label>
                            <select name="ambiente" id="ambiente" class="form-control">
                                <option value="">Padrão do sistema</option>
                                <option value="homologacao">Homologação (teste)</option>
                                <option value="producao">Produção (nota com validade fiscal)</option>
                            </select>
                            <span class="ajuda-campo">Vale só para este emissor: dá para testar um gestor em homologação enquanto os outros seguem emitindo em produção. "Padrão do sistema" usa o ambiente configurado no env.php. A numeração de teste é separada da de produção.</span>
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-4">
                            <label>Inscrição municipal</label>
                            <input type="text" name="inscricaoMunicipal" id="inscricaoMunicipal" class="form-control" maxlength="30">
                            <span class="ajuda-campo">Deixe em branco se a prefeitura não registrou a IM no ambiente nacional — em alguns municípios preencher derruba a emissão.</span>
                        </div>
                        <div class="col-md-4">
                            <label>Código do município (IBGE) *</label>
                            <input type="text" name="codigoMunicipio" id="codigoMunicipio" class="form-control" maxlength="7">
                            <span class="ajuda-campo">7 dígitos. Londrina = 4113700.</span>
                        </div>
                        <div class="col-md-4">
                            <label>UF</label>
                            <input type="text" name="uf" id="uf" class="form-control" maxlength="2">
                        </div>
                    </div>

                    <div class="sec-titulo">Endereço e contato</div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Logradouro</label>
                            <input type="text" name="logradouro" id="logradouro" class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-2">
                            <label>Número</label>
                            <input type="text" name="numero" id="numero" class="form-control" maxlength="20">
                        </div>
                        <div class="col-md-4">
                            <label>Complemento</label>
                            <input type="text" name="complemento" id="complemento" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-4">
                            <label>Bairro</label>
                            <input type="text" name="bairro" id="bairro" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-2">
                            <label>CEP</label>
                            <input type="text" name="cep" id="cep" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-3">
                            <label>Telefone</label>
                            <input type="text" name="telefone" id="telefone" class="form-control" maxlength="20">
                        </div>
                        <div class="col-md-3">
                            <label>E-mail</label>
                            <input type="text" name="email" id="email" class="form-control" maxlength="255">
                        </div>
                    </div>

                    <div class="sec-titulo">Regime tributário</div>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Opção pelo Simples Nacional *</label>
                            <select name="codigoOpcaoSimplesNacional" id="codigoOpcaoSimplesNacional" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="1">1 - Não optante</option>
                                <option value="2">2 - Optante MEI</option>
                                <option value="3">3 - Optante ME/EPP</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Regime especial de tributação</label>
                            <select name="regimeEspecialTributacao" id="regimeEspecialTributacao" class="form-control">
                                <option value="0">0 - Nenhum</option>
                                <option value="1">1 - Ato cooperado</option>
                                <option value="2">2 - Estimativa</option>
                                <option value="3">3 - Microempresa municipal</option>
                                <option value="4">4 - Notário/registrador</option>
                                <option value="5">5 - Profissional autônomo</option>
                                <option value="6">6 - Sociedade de profissionais</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Alíquota do ISS (%)</label>
                            <input type="text" name="percentualAliquota" id="percentualAliquota" class="form-control" maxlength="6">
                            <span class="ajuda-campo">Deixe em branco quando não se aplica (MEI recolhe no DAS).</span>
                        </div>
                    </div>

                    <div class="sec-titulo">Padrões do serviço</div>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Código de tributação nacional do ISS *</label>
                            <input type="text" name="codigoTributacaoNacionalIss" id="codigoTributacaoNacionalIss" class="form-control" maxlength="10">
                            <span class="ajuda-campo">6 dígitos (cTribNac). Confirme com o contador o código da atividade.</span>
                        </div>
                        <div class="col-md-2">
                            <label>Código NBS</label>
                            <input type="text" name="codigoNbs" id="codigoNbs" class="form-control" maxlength="20">
                        </div>
                        <div class="col-md-3">
                            <label>Tributação do ISS *</label>
                            <select name="tributacaoIss" id="tributacaoIss" class="form-control">
                                <option value="1">1 - Operação tributável</option>
                                <option value="2">2 - Imunidade</option>
                                <option value="3">3 - Exportação de serviço</option>
                                <option value="4">4 - Não incidência</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Retenção do ISS *</label>
                            <select name="tipoRetencaoIss" id="tipoRetencaoIss" class="form-control">
                                <option value="1">1 - Não retido</option>
                                <option value="2">2 - Retido pelo tomador</option>
                                <option value="3">3 - Retido pelo intermediário</option>
                            </select>
                        </div>
                    </div>

                    <div class="sec-titulo">Numeração do DPS</div>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Série do DPS</label>
                            <input type="text" name="serieDps" id="serieDps" class="form-control" maxlength="10">
                            <span class="ajuda-campo">Use uma série <b>só deste sistema</b>, diferente da que você usa no Emissor Web do gov.br — assim as duas numerações nunca colidem.</span>
                        </div>
                        <div class="col-md-3">
                            <label>Próximo número</label>
                            <input type="text" name="proximoNumeroDps" id="proximoNumeroDps" class="form-control" maxlength="10">
                            <span class="ajuda-campo">Numa série nova, comece em 1. Cada emissão consome um número, inclusive as rejeitadas.</span>
                        </div>
                        <div class="col-md-6" style="padding-top:22px;">
                            <span class="ajuda-campo">
                                Deixe a série em branco para não enviar numeração (a prefeitura numera).
                                Se preencher, o sistema controla a sequência.
                            </span>
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-12">
                            <label>Informações complementares</label>
                            <textarea name="informacoesComplementares" id="informacoesComplementares" class="form-control" rows="3" maxlength="1000"></textarea>
                            <span class="ajuda-campo">Texto fixo no rodapé da descrição do serviço (dados bancários, chave PIX).</span>
                        </div>
                    </div>

                    <div class="sec-titulo">NF-e — venda de mercadoria (modelo 55)</div>
                    <div class="bloco-nfe">
                        <p style="font-size:12px; color:#777; margin-bottom:12px;">
                            Preencha <b>só se esta empresa vende mercadoria</b>. Quem emite apenas nota de
                            serviço pode deixar tudo em branco — o bloco fica inativo e nada muda na NFS-e.
                            O conteúdo destes campos é <b>decisão do contador</b>; o sistema só guarda o padrão.
                        </p>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Inscrição estadual</label>
                                <input type="text" name="inscricaoEstadual" id="inscricaoEstadual" class="form-control" maxlength="20">
                                <span class="ajuda-campo">Obrigatória na NF-e de mercadoria. A NFS-e não usa.</span>
                            </div>
                            <div class="col-md-4">
                                <label>Regime tributário (CRT)</label>
                                <select name="codigoRegimeTributario" id="codigoRegimeTributario" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="1">1 - Simples Nacional</option>
                                    <option value="2">2 - Simples Nacional, excesso de sublimite</option>
                                    <option value="3">3 - Regime normal</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>CSOSN padrão</label>
                                <input type="text" name="csosnPadrao" id="csosnPadrao" class="form-control" maxlength="4">
                                <span class="ajuda-campo">Ex: 102.</span>
                            </div>
                            <div class="col-md-2">
                                <label>CST PIS/COFINS</label>
                                <input type="text" name="cstPisCofins" id="cstPisCofins" class="form-control" maxlength="2">
                                <span class="ajuda-campo">Ex: 49.</span>
                            </div>
                        </div>

                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-2">
                                <label>CFOP dentro do estado</label>
                                <input type="text" name="cfopDentroEstado" id="cfopDentroEstado" class="form-control" maxlength="4">
                                <span class="ajuda-campo">Ex: 5108.</span>
                            </div>
                            <div class="col-md-2">
                                <label>CFOP fora do estado</label>
                                <input type="text" name="cfopForaEstado" id="cfopForaEstado" class="form-control" maxlength="4">
                                <span class="ajuda-campo">Ex: 6108.</span>
                            </div>
                            <div class="col-md-8">
                                <label>Natureza da operação</label>
                                <input type="text" name="naturezaOperacao" id="naturezaOperacao" class="form-control" maxlength="255">
                                <span class="ajuda-campo">Texto impresso na DANFE. Ex: "Venda de mercadoria adquirida ou recebida de terceiros".</span>
                            </div>
                        </div>

                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-3">
                                <label>Presença do comprador</label>
                                <select name="indicadorPresenca" id="indicadorPresenca" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="1">1 - Presencial (balcão)</option>
                                    <option value="2">2 - Internet / marketplace</option>
                                    <option value="9">9 - Não presencial, outros</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Consumidor final</label>
                                <select name="indicadorConsumidorFinal" id="indicadorConsumidorFinal" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="1">1 - Sim</option>
                                    <option value="0">0 - Não</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>IE do destinatário</label>
                                <select name="indicadorIeDestinatario" id="indicadorIeDestinatario" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="1">1 - Contribuinte de ICMS</option>
                                    <option value="2">2 - Isento de inscrição</option>
                                    <option value="9">9 - Não contribuinte</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Intermediador</label>
                                <select name="indicadorIntermediador" id="indicadorIntermediador" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="0">0 - Sem intermediador</option>
                                    <option value="1">1 - Marketplace / site de terceiro</option>
                                </select>
                                <span class="ajuda-campo"><b>Confirme com o contador.</b> Venda por marketplace costuma exigir 1.</span>
                            </div>
                        </div>

                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-3">
                                <label>Forma de pagamento padrão</label>
                                <input type="text" name="tipoPagamento" id="tipoPagamento" class="form-control" maxlength="2">
                                <span class="ajuda-campo">tPag. 01 = dinheiro, 03 = cartão de crédito, 17 = PIX. <b>Confirme com o contador.</b></span>
                            </div>
                            <div class="col-md-3">
                                <label>Modalidade do frete</label>
                                <select name="modalidadeFrete" id="modalidadeFrete" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="0">0 - Por conta do emitente</option>
                                    <option value="1">1 - Por conta do destinatário</option>
                                    <option value="2">2 - Por conta de terceiros</option>
                                    <option value="9">9 - Sem ocorrência de transporte</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Série da NF-e</label>
                                <input type="text" name="serieNfe" id="serieNfe" class="form-control" maxlength="3">
                                <span class="ajuda-campo">Em branco = este emissor <b>não</b> emite NF-e.</span>
                            </div>
                            <div class="col-md-3">
                                <label>Próximo número da NF-e</label>
                                <input type="text" name="proximoNumeroNfe" id="proximoNumeroNfe" class="form-control" maxlength="10">
                                <span class="ajuda-campo">Cada emissão consome um número, inclusive as rejeitadas.</span>
                            </div>
                        </div>

                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-12">
                                <div class="aviso-serie">
                                    <b>Sobre a série:</b> a numeração da NF-e é do <b>CNPJ + série</b>, não do canal de venda —
                                    todos os canais desta empresa (Shopee, Amazon, balcão) usam a mesma sequência.
                                    Enquanto outro sistema ainda emitir por fora, use aqui uma <b>série diferente da dele</b>.
                                    Dois sistemas na mesma série ao mesmo tempo geram número duplicado e a SEFAZ rejeita a segunda nota.
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-12">
                                <label>Informações complementares da NF-e</label>
                                <textarea name="informacoesComplementaresNfe" id="informacoesComplementaresNfe" class="form-control" rows="2" maxlength="2000"></textarea>
                                <span class="ajuda-campo">Campo próprio da NF-e (infCpl), separado do texto da nota de serviço. Ex: dizeres do Simples Nacional.</span>
                            </div>
                        </div>
                    </div>

                    <?php // _submit_check precisa vir DEPOIS dos campos: PadraoVO::setAll() da break nele. ?>
                    <input type="hidden" name="_submit_check" value="1">
                    <input type="hidden" name="c" value="GerenciarNotaFiscalEmissor">
                    <input type="hidden" name="m" value="salvarEmissor">
                    <input type="hidden" name="codEmissor" id="codEmissor">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarEmissor">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de ativar/inativar -->
<div class="modal fade" id="modalAlternar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirmar</h4>
            </div>
            <div class="modal-body">
                <p>Alternar o status deste emissor?</p>
                <p style="font-size:12px; color:#777;">
                    Emissor inativo não aparece para novas emissões. As notas já emitidas continuam intactas.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarAlternar();">Confirmar</button>
            </div>
        </div>
    </div>
</div>
