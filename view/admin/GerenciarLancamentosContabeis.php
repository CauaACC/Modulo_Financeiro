<?php
require_once(dirname(__FILE__)."/../../include/config.php");

// Verificar permissão de financeiro
@$cod_msg = false;
if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

$objSRV = new GerenciarLancamentosContabeisAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
//            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarLancamentosContabeis();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novocodLancamentosContabeis').val(0);
            $('#salvarLancamentosContabeis').val('Adicionar');
            salvarLancamentosContabeis();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            editarLancamentosContabeis(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirLancamentosContabeis(id);
        });
        $('#tipoLancamento').change(function () {

            var tipoLancamento = $(this).val();

            esconderLimparCD()

            if (tipoLancamento === 'PAGAMENTO_FORNECEDOR' || tipoLancamento === 'DESPESA_PAGA') {
                $('#campoDebito').show();
                $('#campoCredito').hide();
                $('#debito').prop('required', true);
            }

            if (tipoLancamento === 'VENDA') {
                $('#campoCredito').show();
                $('#campoDebito').hide();
                $('#credito').prop('required', true);
            }

        });
//        carregarTabelaLancamentosContabeis();
    });

    function esconderLimparCD() {
            $('#campoDebito').hide();
            $('#campoCredito').hide();

            // Remove obrigatoriedade e limpa valores
            $('#debito').prop('required', false).val('');
            $('#credito').prop('required', false).val('');
    }
    //funçães globais
    function carregarTabelaLancamentosContabeis() {
//        alert($('#ordenar').val())
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarLancamentosContabeis', 'm': 'carregarTabelaLancamentosContabeis', 'ordenar': $('#ordenar').val(), '_submit_check': '1'}
        , function(html) {
            $("table tbody.inputs tr").remove();
            $("table").trigger("update");
            // append the "ajax'd" data to the table body 
            $("table tbody.inputs").append(html);
            // let the plugin know that we made a update 
            $("table").trigger("update");
            //            $("table").tablesorter({
            //                sortList: [[1,2]]                    
            //            });            
            //eventos para manipulação de registros
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarLancamentosContabeis(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirLancamentosContabeis(id);
            });
        }
        );
    }
    function salvarLancamentosContabeis() {
        $('#formLancamentosContabeis').ajaxSubmit({
            target: '#mensagem'
                    , dataType: 'json'
                    , async: false
                    , cache: false
                    , beforeSend: function() {
                startLoading();
            }
            , timeout: 4000
                    , error: function(ajax) {
                showResponse(ajax);
            }
            , success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaLancamentosContabeis();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarLancamentosContabeis(codLancamentosContabeis) {
        if (codLancamentosContabeis) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codLancamentosContabeis': codLancamentosContabeis, 'c': 'GerenciarLancamentosContabeis', 'm': 'editarLancamentosContabeis', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    esconderLimparCD()

                    //Setando valores para alteração
                    if (data.flgLancamento === 'C'){
                        $('#credito').val(data.codPlanoContas);  
                        $('#campoCredito').show();
                        $('#campoDebito').hide();
                        $('#credito').prop('required', true);
                    } else if (data.flgLancamento === 'D'){
                        $('#debito').val(data.codPlanoContas);
                        $('#campoDebito').show();
                        $('#campoCredito').hide();
                        $('#debito').prop('required', true);
                    }
                    $('#codLancamentosContabeis').val(data.codLancamentosContabeis);
                    $('#historico').val(data.historico);
                    $('#origemLancamento').val(data.origemLancamento);
                    $('#valor').val(data.valor);
                    $('#dataFinanceira').val(data.dataFinanceira);
                    $('#dataCompetencia').val(data.dataCompetencia);
                    $('#tipoLancamento').val(data.tipoLancamento);
                    $('#conciliado').val(data.conciliado);
                    $('#salvarLancamentosContabeis').val('Salvar Alterações');
                    $('#nomeLancamentosContabeis').focus();
                    $('html,body').scrollTop(0);
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do Lançamento não informado!');
        }
    }
    function excluirLancamentosContabeis(codLancamentosContabeis) {
        if (confirm('Deseja realmente excluir LancamentosContabeis?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codLancamentosContabeis': codLancamentosContabeis, 'c': 'GerenciarLancamentosContabeis', 'm': 'excluirLancamentosContabeis', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaLancamentosContabeis();
                    }
                    limparCampos();
                }
                , complete: function() {
                    stopLoading();
                }
            });
        }
    }
    function limparCampos() {
        esconderLimparCD()
        
        //Limpando campos
        $('#codLancamentosContabeis').val('');
        $('#tipoLancamento').val('');
        $('#campoCredito').val('');
        $('#campoDebito').val('');
        $('#valor').val('');
        $('#dataFinanceira').val('');
        $('#dataCompetencia').val('');
        $('#origemLancamento').val('');
        $('#historico').val('');
        $('#conciliado').val('');
        $('#getMessage').html('');
        // $('#novocodLancamentosContabeis').val(0);
        $('#salvarLancamentosContabeis').val('Adicionar');

    }
</script>
<!--<script type="text/javascript" src="/<?php echo  WB_PATH ?>/view/js/util/validacoes.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link   rel="stylesheet"      href="resources/css/tablesorter.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="resources/js/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/titulo_area_admin.php");
require_once(dirname(__FILE__)."/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/area_admin_menu_financeiro.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar LancamentosContabeis:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
                //                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formLancamentosContabeis" name="formLancamentosContabeis" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="dataCompetencia">Data de Competência:</label>
                            <input type="date" id="dataCompetencia" name="dataCompetencia" value=""
                                   class="form-control" placeholder="" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="dataFinanceira">Data Financeira:</label>
                            <input type="date" id="dataFinanceira" name="dataFinanceira" value=""
                            class="form-control" placeholder="" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="tipoLancamento">Tipo:</label>
                            <select id="tipoLancamento" name="tipoLancamento" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="PAGAMENTO_FORNECEDOR">Pagamento de Fornecedor</option>
                                <option value="VENDA">Venda</option>
                                <option value="DESPESA_PAGA">Despesa Paga</option>
                            </select>
                        </div>
                        <div id="campoDebito" style="display: none;">
                            <div class="form-group col-sm-12" style="padding-left: 0px;">
                                <label for="debito">Débito:</label>
                                <select id="debito" name="debito" class="form-control">
                                    <option value="">Selecione...</option>
                                    <?php CarregarOptionsAC::planoContasDebito($fws_) ?>
                                </select>
                            </div>
                        </div>
                        <div id="campoCredito" style="display: none;">
                            <div class="form-group col-sm-12" style="padding-left: 0px;">
                                <label for="credito">Crédito:</label>
                                <select id="credito" name="credito" class="form-control">
                                    <option value="">Selecione...</option>
                                    <?php CarregarOptionsAC::planoContasCredito($fws_) ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="valor">Valor:</label>
                            <input type="text" id="valor" name="valor" value=""
                            class="form-control" placeholder="Valor" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="origemLancamento">Origem do Lançamento:</label>
                            <select id="origemLancamento" name="origemLancamento" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="MANUAL">Manual</option>
                                <option value="FINANCEIRO">Financeiro</option>
                                <option value="INTEGRACAO">Integração</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="historico">Histórico:</label>
                            <textarea id="historico" name="historico" value=""
                            class="form-control" placeholder="Descrição" required autofocus></textarea>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="conciliado">Conciliado:</label>
                            <select id="conciliado" name="conciliado" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="1">SIM</option>
                                <option value="0">NÃO</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">                  
                            <div id="carregando" class="shown hidden">
                                <div class="imagem">
                                    <div class="frase">Carregando...</div>
                                    <img src="<?php echo  WB_PATH ?>/resources/image/loading-9.gif" border="0">

                                </div>
                            </div>
                            <div id="mensagem"></div>
                            <div id="sucesso"    class="hidden"></div>
                            <div id="erro"       class="hidden"></div>                
                            <input type="hidden" value="1"               name="_submit_check">
                            <input type="hidden" value="GerenciarLancamentosContabeis" name="c">
                            <input type="hidden" value="salvarLancamentosContabeis"    name="m">
                            
                            <br>&nbsp;
                            <!-- <input type="hidden" name="novocodLancamentosContabeis" id="novocodLancamentosContabeis" value="0"/> -->
                            <input type="hidden" name="codLancamentosContabeis"     id="codLancamentosContabeis"     value=""/>                
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar" >Salvar</button>
                            <!--                            &nbsp;&nbsp;&nbsp;                
                                                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvarComoNovo" >Salvar como novo registro</button>-->
                            &nbsp;&nbsp;&nbsp;                
                            <button class="btn btn-xs btn-primary" type="button" id="btnLimpar" >Limpar Campos</button>
                        </div>
                    </div>  
                </form>
            <?php } catch (Exception $exc) { ?>
                <div id="getMessage" class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>        
            <?php } ?>
                <br><br>&nbsp;<hr>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <b>Lista de LancamentosContabeis Cadastrados:</b>
                <table class="tablesorter" id='itens' border="0">
                    <thead>
                    <th width="10px"></th>
                    <th width="10px">Data da Competência</th>
                    <th width="20px">Data Financeira</th>
                    <th width="10px">Tipo</th>
                    <th width="10px">valor</th>
                    <th width="10px">Origem do Lançamento</th>
                    <th width="20px">Histórico</th>
                    <th width="20px">Conciliado</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaLancamentosContabeis($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
