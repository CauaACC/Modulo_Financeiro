<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)
    && !FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_CARGA_TRANSPARENCIA, 5, false, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/acesso-negado.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
$tituloPagina = 'Carga Transpar&ecirc;ncia';
$submenu = 'area_admin_menu_transparencia.php';
require_once(dirname(__FILE__)."/../../view/admin/cabecalho-geral.php");
?>

<!-- Divs flutuantes de sucesso/erro -->
<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<style>
    .card-contador {
        display: inline-block;
        min-width: 180px;
        padding: 12px 18px;
        margin: 0 8px 10px 0;
        border-radius: 4px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .card-contador .numero {
        font-size: 28px;
        font-weight: bold;
        display: block;
    }
    .card-contador .rotulo {
        font-size: 12px;
        display: block;
        margin-top: 4px;
    }
    .card-total-projetos   { background:#d9edf7; color:#31708f; border:1px solid #bce8f1; }
    .card-rascunho-projetos { background:#fcf8e3; color:#8a6d3b; border:1px solid #faebcc; }
    .card-total-pagamentos  { background:#dff0d8; color:#3c763d; border:1px solid #d6e9c6; }
    .card-rascunho-pagamentos { background:#fcf8e3; color:#8a6d3b; border:1px solid #faebcc; }
    .tab-content { padding-top: 15px; }
    .filtro-row { margin-bottom: 12px; }
    .acoes-lote { margin-bottom: 12px; }
</style>

<!-- Submenu Transparência (mesmo do GerenciarCargaTransparencia) -->
<div class="row">
<div class="col-sm-12 submenu-area" id="submenuTransparencia">
    <a class="submenu-btn" href="index.php?content=view/admin/GerenciarCargaTransparencia.php"><i class="fa fa-upload"></i> Carga de Arquivos</a>
    <a class="submenu-btn" href="index.php?content=view/admin/GerenciarCargaTransparencia.php#modelos" onclick="localStorage.setItem('cargaTranspAba','tabModelos');"><i class="fa fa-info-circle"></i> Modelos de Arquivo</a>
    <a class="submenu-btn submenu-btn-ativo" href="#"><i class="fa fa-list-alt"></i> Gest&atilde;o de Registros</a>
</div>
</div>

<!-- Sub-abas: Projetos / Pagamentos -->
<div class="row" style="margin-top:10px; margin-bottom:5px;">
<div class="col-sm-12">
    <button type="button" class="btn btn-sm btn-primary" id="btnAbaProjetos" onclick="mostrarAbaReg('tabProjetos');"><i class="fa fa-folder-open"></i> Projetos</button>
    <button type="button" class="btn btn-sm btn-default" id="btnAbaPagamentos" onclick="mostrarAbaReg('tabPagamentos');"><i class="fa fa-money"></i> Pagamentos</button>
</div>
</div>

<!-- Cards de contadores -->
<div id="painelContadores" style="margin-bottom:15px;">
    <div class="card-contador card-total-projetos">
        <span class="numero" id="contTotalProjetos">-</span>
        <span class="rotulo">Total Projetos</span>
    </div>
    <div class="card-contador card-rascunho-projetos">
        <span class="numero" id="contRascunhoProjetos">-</span>
        <span class="rotulo">Projetos Rascunho</span>
    </div>
    <div class="card-contador card-total-pagamentos">
        <span class="numero" id="contTotalPagamentos">-</span>
        <span class="rotulo">Total Pagamentos</span>
    </div>
    <div class="card-contador card-rascunho-pagamentos">
        <span class="numero" id="contRascunhoPagamentos">-</span>
        <span class="rotulo">Pagamentos Rascunho</span>
    </div>
</div>

<!-- ===== ABA PROJETOS ===== -->
<div id="tabProjetos">
    <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 18px; border-bottom:1px solid #eee;">
            <h4 style="margin:0; font-size:16px;"><i class="fa fa-folder-open"></i> Projetos</h4>
            <div>
                <button class="btn btn-sm btn-success" type="button" onclick="publicarSelecionados('projeto')"><i class="fa fa-check"></i> Publicar Selecionados</button>
                <button class="btn btn-sm btn-danger" type="button" onclick="rejeitarSelecionados('projeto')"><i class="fa fa-times"></i> Rejeitar Selecionados</button>
            </div>
        </div>
        <div style="padding:12px 18px;">
            <div class="row" style="margin-bottom:10px;">
                <div class="col-sm-2">
                    <label>Status:</label>
                    <select class="form-control input-sm" id="filtroStatusProjeto">
                        <option value="">Todos</option>
                        <option value="RASCUNHO">Rascunho</option>
                        <option value="PUBLICADO">Publicado</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label>Arquivo:</label>
                    <select class="form-control input-sm" id="filtroArquivoProjeto">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label>Busca:</label>
                    <input type="text" class="form-control input-sm" id="filtroBuscaProjeto" placeholder="N&ordm; projeto, coordenador...">
                </div>
                <div class="col-sm-2" style="padding-top:25px;">
                    <button class="btn btn-sm btn-primary" type="button" id="btnFiltrarProjetos"><i class="fa fa-search"></i> Filtrar</button>
                </div>
            </div>
            <div id="listaProjetos">
                <div class="text-center text-muted" style="padding:30px 0;"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>
            </div>
        </div>
    </div>
</div>

<!-- ===== ABA PAGAMENTOS ===== -->
<div id="tabPagamentos" style="display:none;">
    <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 18px; border-bottom:1px solid #eee;">
            <h4 style="margin:0; font-size:16px;"><i class="fa fa-money"></i> Pagamentos</h4>
            <div>
                <button class="btn btn-sm btn-success" type="button" onclick="publicarSelecionados('pagamento')"><i class="fa fa-check"></i> Publicar Selecionados</button>
                <button class="btn btn-sm btn-danger" type="button" onclick="rejeitarSelecionados('pagamento')"><i class="fa fa-times"></i> Rejeitar Selecionados</button>
            </div>
        </div>
        <div style="padding:12px 18px;">
            <div class="row" style="margin-bottom:10px;">
                <div class="col-sm-2">
                    <label>Status:</label>
                    <select class="form-control input-sm" id="filtroStatusPagamento">
                        <option value="">Todos</option>
                        <option value="RASCUNHO">Rascunho</option>
                        <option value="PUBLICADO">Publicado</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label>Arquivo:</label>
                    <select class="form-control input-sm" id="filtroArquivoPagamento">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label>Busca:</label>
                    <input type="text" class="form-control input-sm" id="filtroBuscaPagamento" placeholder="N&ordm; projeto, fornecedor...">
                </div>
                <div class="col-sm-2" style="padding-top:25px;">
                    <button class="btn btn-sm btn-primary" type="button" id="btnFiltrarPagamentos"><i class="fa fa-search"></i> Filtrar</button>
                </div>
            </div>
            <div id="listaPagamentos">
                <div class="text-center text-muted" style="padding:30px 0;"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>
            </div>
        </div>
    </div>
</div>

</div><!-- panel-body -->
</div><!-- panel -->
</div><!-- panel-group -->

<!-- ===== MODAL EDITAR PROJETO ===== -->
<div class="modal fade" id="modalEditarProjeto" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-edit"></i> Editar Projeto</h4>
            </div>
            <div class="modal-body">
                <form id="formEditarProjeto" name="formEditarProjeto" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <input type="hidden" name="c" value="GerenciarRegistrosTransparencia">
                    <input type="hidden" name="m" value="salvarProjeto">
                    <input type="hidden" name="_submit_check" value="1">
                    <input type="hidden" name="id" id="editProjetoId">

                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editProjetoNumero">N&ordm; Projeto:</label>
                            <input type="text" class="form-control" id="editProjetoNumero" name="numero_projeto">
                        </div>
                        <div class="form-group col-sm-8">
                            <label for="editProjetoCoordenador">Coordenador:</label>
                            <input type="text" class="form-control" id="editProjetoCoordenador" name="coordenador">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editProjetoEProtocolo">E-Protocolo:</label>
                            <input type="text" class="form-control" id="editProjetoEProtocolo" name="e_protocolo">
                        </div>
                        <div class="form-group col-sm-8">
                            <label for="editProjetoProposta">Proposta:</label>
                            <input type="text" class="form-control" id="editProjetoProposta" name="proposta">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editProjetoVinculo">V&iacute;nculo:</label>
                            <input type="text" class="form-control" id="editProjetoVinculo" name="vinculo">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editProjetoTipoPagamento">Tipo Pagamento:</label>
                            <input type="text" class="form-control" id="editProjetoTipoPagamento" name="tipo_pagamento">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editProjetoDataInicio">Data In&iacute;cio:</label>
                            <input type="date" class="form-control" id="editProjetoDataInicio" name="data_inicio">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editProjetoDataFim">Data Fim:</label>
                            <input type="date" class="form-control" id="editProjetoDataFim" name="data_fim">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarProjeto()"><i class="fa fa-save"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL EDITAR PAGAMENTO ===== -->
<div class="modal fade" id="modalEditarPagamento" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-edit"></i> Editar Pagamento</h4>
            </div>
            <div class="modal-body">
                <form id="formEditarPagamento" name="formEditarPagamento" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <input type="hidden" name="c" value="GerenciarRegistrosTransparencia">
                    <input type="hidden" name="m" value="salvarPagamento">
                    <input type="hidden" name="_submit_check" value="1">
                    <input type="hidden" name="id" id="editPagamentoId">

                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoNumeroProjeto">N&ordm; Projeto:</label>
                            <input type="text" class="form-control" id="editPagamentoNumeroProjeto" name="numero_projeto">
                        </div>
                        <div class="form-group col-sm-8">
                            <label for="editPagamentoFornecedor">Fornecedor:</label>
                            <input type="text" class="form-control" id="editPagamentoFornecedor" name="fornecedor">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoValorBruto">Valor Bruto:</label>
                            <input type="text" class="form-control" id="editPagamentoValorBruto" name="valor_bruto">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoValorLiquido">Valor L&iacute;quido:</label>
                            <input type="text" class="form-control" id="editPagamentoValorLiquido" name="valor_liquido">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoVencimento">Vencimento:</label>
                            <input type="date" class="form-control" id="editPagamentoVencimento" name="vencimento">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-8">
                            <label for="editPagamentoServico">Servi&ccedil;o:</label>
                            <input type="text" class="form-control" id="editPagamentoServico" name="servico">
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoCnpj">CNPJ:</label>
                            <input type="text" class="form-control" id="editPagamentoCnpj" name="cnpj">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label for="editPagamentoTipoRpa">Tipo RPA:</label>
                            <input type="text" class="form-control" id="editPagamentoTipoRpa" name="tipo_rpa">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarPagamento()"><i class="fa fa-save"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL CONFIRMAR PUBLICACAO ===== -->
<div class="modal fade" id="modalConfirmarPublicar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-check-circle"></i> Confirmar Publica&ccedil;&atilde;o</h4>
            </div>
            <div class="modal-body" id="modalConfirmarPublicarBody">
                <p>Deseja publicar <strong><span id="qtdPublicar">0</span></strong> registro(s) selecionado(s)?</p>
                <p>Ap&oacute;s publicados, os registros ficar&atilde;o vis&iacute;veis no Portal da Transpar&ecirc;ncia.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarPublicar"><i class="fa fa-check"></i> Publicar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL CONFIRMAR REJEICAO ===== -->
<div class="modal fade" id="modalConfirmarRejeitar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalhoExclusao">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Rejei&ccedil;&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja rejeitar <strong><span id="qtdRejeitar">0</span></strong> registro(s) selecionado(s)?</p>
                <div class="alert alert-danger" style="margin-bottom:0;">
                    <i class="fa fa-warning"></i> Os registros rejeitados ser&atilde;o removidos permanentemente. Esta a&ccedil;&atilde;o &eacute; irrevers&iacute;vel.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarRejeitar"><i class="fa fa-times"></i> Rejeitar</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var _publicarTipo = null;
    var _publicarIds = [];
    var _rejeitarTipo = null;
    var _rejeitarIds = [];

    function mostrarAbaReg(abaId) {
        $('#tabProjetos, #tabPagamentos').hide();
        $('#' + abaId).show();
        $('#btnAbaProjetos, #btnAbaPagamentos').removeClass('btn-primary').addClass('btn-default');
        if (abaId === 'tabPagamentos') {
            $('#btnAbaPagamentos').removeClass('btn-default').addClass('btn-primary');
            carregarTabelaPagamentos();
        } else {
            $('#btnAbaProjetos').removeClass('btn-default').addClass('btn-primary');
            carregarTabelaProjetos();
        }
    }

    $(document).ready(function() {
        carregarContadores();
        carregarArquivos();
        carregarTabelaProjetos();

        // Filtrar projetos
        $('#btnFiltrarProjetos').click(function() {
            carregarTabelaProjetos();
        });

        // Filtrar pagamentos
        $('#btnFiltrarPagamentos').click(function() {
            carregarTabelaPagamentos();
        });

        // Checkboxes de selecionar todos sao rebindados apos cada carregamento via bindCheckboxSelecionar()

        // Confirmar publicacao
        $('#btnConfirmarPublicar').click(function() {
            $('#modalConfirmarPublicar').modal('hide');
            executarPublicacaoEmLote();
        });

        // Confirmar rejeicao
        $('#btnConfirmarRejeitar').click(function() {
            $('#modalConfirmarRejeitar').modal('hide');
            executarRejeicaoEmLote();
        });

        // Enter para filtrar
        $('#filtroBuscaProjeto').keypress(function(e) {
            if (e.which === 13) carregarTabelaProjetos();
        });
        $('#filtroBuscaPagamento').keypress(function(e) {
            if (e.which === 13) carregarTabelaPagamentos();
        });
    });

    // ===== CONTADORES =====
    function carregarContadores() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'carregarContadores',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    $('#contTotalProjetos').text(data.totalProjetos || 0);
                    $('#contRascunhoProjetos').text(data.rascunhoProjetos || 0);
                    $('#contTotalPagamentos').text(data.totalPagamentos || 0);
                    $('#contRascunhoPagamentos').text(data.rascunhoPagamentos || 0);
                }
            }
        });
    }

    // ===== ARQUIVOS (populate selects) =====
    function carregarArquivos() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'carregarArquivosCarga',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data && data.arquivos) {
                    var optsProjeto = '<option value="">Todos</option>';
                    var optsPagamento = '<option value="">Todos</option>';
                    for (var i = 0; i < data.arquivos.length; i++) {
                        var arq = data.arquivos[i];
                        var opt = '<option value="' + arq.id + '">' + arq.nome + '</option>';
                        if (arq.tipo === 'CARGA_TRANSPARENCIA_PROJETOS') {
                            optsProjeto += opt;
                        } else if (arq.tipo === 'CARGA_TRANSPARENCIA_PAGAMENTOS') {
                            optsPagamento += opt;
                        }
                    }
                    $('#filtroArquivoProjeto').html(optsProjeto);
                    $('#filtroArquivoPagamento').html(optsPagamento);
                }
            }
        });
    }

    // ===== TABELA PROJETOS =====
    function carregarTabelaProjetos() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'carregarTabelaProjetos',
                'status_publicacao': $('#filtroStatusProjeto').val(),
                'cod_arquivo': $('#filtroArquivoProjeto').val(),
                'busca': $('#filtroBuscaProjeto').val(),
                '_submit_check': '1'
            },
            dataType: "html",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(html) {
                $('#listaProjetos').html(html);
                bindCheckboxSelecionar('Projetos', 'Projeto');
            },
            error: function(xhr, status, error) {
                $('#listaProjetos').html('<div class="text-center text-danger" style="padding:20px 0;">Erro ao carregar: ' + error + '</div>');
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== TABELA PAGAMENTOS =====
    function carregarTabelaPagamentos() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'carregarTabelaPagamentos',
                'status_publicacao': $('#filtroStatusPagamento').val(),
                'cod_arquivo': $('#filtroArquivoPagamento').val(),
                'busca': $('#filtroBuscaPagamento').val(),
                '_submit_check': '1'
            },
            dataType: "html",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(html) {
                $('#listaPagamentos').html(html);
                bindCheckboxSelecionar('Pagamentos', 'Pagamento');
            },
            error: function(xhr, status, error) {
                $('#listaPagamentos').html('<div class="text-center text-danger" style="padding:20px 0;">Erro ao carregar: ' + error + '</div>');
            },
            complete: function() { stopLoading(); }
        });
    }

    // Rebind checkboxes apos carregar tabela (delegated)
    function bindCheckboxSelecionar(sufixoPlural, sufixoSingular) {
        var container = (sufixoPlural === 'Projetos') ? '#listaProjetos' : '#listaPagamentos';
        $(container).off('change', '#chkHead' + sufixoSingular).on('change', '#chkHead' + sufixoSingular, function() {
            $(container + ' input.chk-registro').prop('checked', $(this).is(':checked'));
        });
    }

    // ===== EDITAR PROJETO =====
    function abrirEditarProjeto(id) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'editarProjeto',
                'id': id,
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                stopLoading();
                if (data.statusMessage == '0') {
                    loading('div#erro', 'show');
                    $('div#erro').html(data.message);
                    return;
                }
                $('#editProjetoId').val(data.id);
                $('#editProjetoNumero').val(data.numero_projeto);
                $('#editProjetoCoordenador').val(data.coordenador);
                $('#editProjetoEProtocolo').val(data.e_protocolo);
                $('#editProjetoProposta').val(data.proposta);
                $('#editProjetoVinculo').val(data.vinculo);
                $('#editProjetoTipoPagamento').val(data.tipo_pagamento);
                $('#editProjetoDataInicio').val(data.data_inicio);
                $('#editProjetoDataFim').val(data.data_fim);
                $('#modalEditarProjeto').modal('show');
            },
            error: function(xhr, status, error) {
                stopLoading();
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao carregar projeto: ' + error);
            }
        });
    }

    // ===== SALVAR PROJETO =====
    function salvarProjeto() {
        $('#formEditarProjeto').ajaxSubmit({
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    $('#modalEditarProjeto').modal('hide');
                    carregarTabelaProjetos();
                    carregarContadores();
                }
            },
            error: function(ajax) {
                showResponse(ajax);
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== EDITAR PAGAMENTO =====
    function abrirEditarPagamento(id) {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'editarPagamento',
                'id': id,
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                stopLoading();
                if (data.statusMessage == '0') {
                    loading('div#erro', 'show');
                    $('div#erro').html(data.message);
                    return;
                }
                $('#editPagamentoId').val(data.id);
                $('#editPagamentoNumeroProjeto').val(data.numero_projeto);
                $('#editPagamentoFornecedor').val(data.fornecedor);
                $('#editPagamentoValorBruto').val(data.valor_bruto);
                $('#editPagamentoValorLiquido').val(data.valor_liquido);
                $('#editPagamentoVencimento').val(data.vencimento);
                $('#editPagamentoServico').val(data.servico);
                $('#editPagamentoCnpj').val(data.cnpj);
                $('#editPagamentoTipoRpa').val(data.tipo_rpa);
                $('#modalEditarPagamento').modal('show');
            },
            error: function(xhr, status, error) {
                stopLoading();
                loading('div#erro', 'show');
                $('div#erro').html('Erro ao carregar pagamento: ' + error);
            }
        });
    }

    // ===== SALVAR PAGAMENTO =====
    function salvarPagamento() {
        $('#formEditarPagamento').ajaxSubmit({
            dataType: 'json',
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    $('#modalEditarPagamento').modal('hide');
                    carregarTabelaPagamentos();
                    carregarContadores();
                }
            },
            error: function(ajax) {
                showResponse(ajax);
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== PUBLICAR SELECIONADOS =====
    function publicarSelecionados(tipo) {
        var ids = coletarIdsSelecionados(tipo);
        if (ids.length === 0) {
            loading('div#erro', 'show');
            $('div#erro').html('Nenhum registro selecionado.');
            return;
        }
        _publicarTipo = tipo;
        _publicarIds = ids;
        _publicarStatus = 'PUBLICADO';
        $('#qtdPublicar').text(ids.length);
        $('#modalConfirmarPublicar .modal-title').html('<i class="fa fa-check-circle"></i> Confirmar Publica\u00e7\u00e3o');
        $('#btnConfirmarPublicar').html('<i class="fa fa-check"></i> Publicar');
        $('#modalConfirmarPublicar').modal('show');
    }

    function executarPublicacaoEmLote() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'publicarEmLote',
                'tipo': _publicarTipo,
                'ids': JSON.stringify(_publicarIds),
                'status': _publicarStatus || 'PUBLICADO',
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    if (_publicarTipo === 'projeto') {
                        carregarTabelaProjetos();
                    } else {
                        carregarTabelaPagamentos();
                    }
                    carregarContadores();
                }
            },
            error: function(ajax) {
                showResponse(ajax);
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== REJEITAR SELECIONADOS =====
    function rejeitarSelecionados(tipo) {
        var ids = coletarIdsSelecionados(tipo);
        if (ids.length === 0) {
            loading('div#erro', 'show');
            $('div#erro').html('Nenhum registro selecionado.');
            return;
        }
        _rejeitarTipo = tipo;
        _rejeitarIds = ids;
        $('#qtdRejeitar').text(ids.length);
        $('#modalConfirmarRejeitar').modal('show');
    }

    function executarRejeicaoEmLote() {
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {
                'c': 'GerenciarRegistrosTransparencia',
                'm': 'rejeitarRegistro',
                'tipo': _rejeitarTipo,
                'ids': JSON.stringify(_rejeitarIds),
                '_submit_check': '1'
            },
            dataType: "json",
            cache: false,
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage == '1' || data.statusMessage === true) {
                    if (_rejeitarTipo === 'projeto') {
                        carregarTabelaProjetos();
                    } else {
                        carregarTabelaPagamentos();
                    }
                    carregarContadores();
                }
            },
            error: function(ajax) {
                showResponse(ajax);
            },
            complete: function() { stopLoading(); }
        });
    }

    // ===== PUBLICAR/DESPUBLICAR REGISTRO INDIVIDUAL =====
    var _publicarStatus = 'PUBLICADO';
    function publicarRegistro(tipo, id, novoStatus) {
        _publicarTipo = tipo;
        _publicarIds = [id];
        _publicarStatus = novoStatus || 'PUBLICADO';
        $('#qtdPublicar').text(1);
        if (_publicarStatus === 'RASCUNHO') {
            $('#modalConfirmarPublicar .modal-title').html('<i class="fa fa-eye-slash"></i> Confirmar Despublica\u00e7\u00e3o');
            $('#modalConfirmarPublicarBody').html('<p>Deseja despublicar <strong>' + _publicarIds.length + '</strong> registro(s) selecionado(s)?</p><p>Os registros ser\u00e3o removidos do Portal da Transpar\u00eancia.</p>');
            $('#btnConfirmarPublicar').removeClass('btn-success').addClass('btn-warning').html('<i class="fa fa-eye-slash"></i> Despublicar');
        } else {
            $('#modalConfirmarPublicar .modal-title').html('<i class="fa fa-check-circle"></i> Confirmar Publica\u00e7\u00e3o');
            $('#modalConfirmarPublicarBody').html('<p>Deseja publicar <strong>' + _publicarIds.length + '</strong> registro(s) selecionado(s)?</p><p>Ap\u00f3s publicados, os registros ficar\u00e3o vis\u00edveis no Portal da Transpar\u00eancia.</p>');
            $('#btnConfirmarPublicar').removeClass('btn-warning').addClass('btn-success').html('<i class="fa fa-check"></i> Publicar');
        }
        $('#modalConfirmarPublicar').modal('show');
    }

    // ===== HELPERS =====
    function coletarIdsSelecionados(tipo) {
        var ids = [];
        var container = (tipo === 'projeto') ? '#listaProjetos' : '#listaPagamentos';
        $(container + ' input[type="checkbox"].chk-registro:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }
</script>
