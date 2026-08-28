<?php
//ControleAcesso::ifUserInRole('gerenciarEventos,coordenarEventos','url');
$objSRV = new GerenciarPrevisaoOrcamentariaAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
            sortList: [[1, 2]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarPrevisaoOrcamentaria();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodPrevisaoOrcamentaria').val(0);
            $('#salvarPrevisaoOrcamentaria').val('Adicionar');
            salvarPrevisaoOrcamentaria();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        carregarTabelaPrevisaoOrcamentarias();
        $('#vlrUnitario').priceFormat();
        $('#vlrTotal').priceFormat();
        $('#tipo').change(function() {
            acaoCarregaReceitaDespesa();
        });
        $('#vlrUnitario').blur(function() {
            calculaTotal();
        });
        $('#quantidade').change(function() {
            calculaTotal();
        });
    });
    function acaoCarregaReceitaDespesa() {
        $.ajax({
            type: 'POST'
                    , url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                    , data: ({'c': 'GerenciarPrevisaoOrcamentaria', 'm': 'getListaTipoReceitaOuDespesa', 'tipo': $("#tipo").val(), 'nomTipoReceitaDespesa': $("#nomTipoReceitaDespesa").val(), '_submit_check': '1'})
                    , dataType: 'html'
                    , async: false
                    , cache: false
                    , timeout: 4000
                    , beforeSend: function() {
                startLoading();
            }
            , error: function(ajax) {
                showResponse(ajax);
            }
            , success: function(data) {
            }
            , complete: function(data) {
                var options = data.responseText;
                $("#codTipoReceitaDespesa").html(options).show();
                stopLoading();
            }
        });
    }
    //funçães globais
    function carregarTabelaPrevisaoOrcamentarias() {
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , { 'c': 'GerenciarPrevisaoOrcamentaria', 'm': 'carregarTabelaPrevisaoOrcamentarias', '_submit_check': '1'}
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
                editarPrevisaoOrcamentaria(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirPrevisaoOrcamentaria(id);
            });
        }
        );
    }
    function salvarPrevisaoOrcamentaria() {
        calculaTotal();
        $('#formPrevisaoOrcamentaria').ajaxSubmit({
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
                    carregarTabelaPrevisaoOrcamentarias();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarPrevisaoOrcamentaria(codPrevisaoOrcamentaria) {
        if (codPrevisaoOrcamentaria) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codPrevisaoOrcamentaria': codPrevisaoOrcamentaria, 'c': 'GerenciarPrevisaoOrcamentaria', 'm': 'editarPrevisaoOrcamentaria', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codPrevisaoOrcamentaria').val(data.codPrevisaoOrcamentaria);
                    $('#desPrevisaoOrcamentaria').val(data.desPrevisaoOrcamentaria);
                    $('#tipo').val(data.tipo);
                    acaoCarregaReceitaDespesa();
                    $('#codTipoReceitaDespesa').val(data.codTipoReceitaDespesa);
                    $('#quantidade').val(data.quantidade);
                    $('#vlrUnitario').val(data.vlrUnitario);
                    $('#vlrTotal').val(data.vlrTotal);
                    $('#novoCodPrevisaoOrcamentaria').val(1);
                    $('#desPrevisaoOrcamentaria').focus();
                    $('#salvarPrevisaoOrcamentaria').val('Salvar Alteração');
                }
                , complete: function() {
                    stopLoading();
                    //alert('Cãdigo não econtrado!');
                    //                return false;
                }
            });
        } else {
            alert('Cãdigo da ocorrãncia não foi informado!');
        }
    }
    function excluirPrevisaoOrcamentaria(codPrevisaoOrcamentaria) {
        if (confirm('Deseja realmente excluir Previsão Orçamentária?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codPrevisaoOrcamentaria': codPrevisaoOrcamentaria, 'c': 'GerenciarPrevisaoOrcamentaria', 'm': 'excluirPrevisaoOrcamentaria', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaPrevisaoOrcamentarias();
                    }
                    limparCampos();
                }
                , complete: function() {
                    stopLoading();
                }
            });
        }
    }
    function calculaTotal() {
        quantidade = parseInt($("#quantidade").val());
        vlrUnitario = $("#vlrUnitario").val().replace('.', '');
        vlrUnitario = parseFloat(vlrUnitario.replace(',', '.'));

        if (isNaN(quantidade)) {
            quantidade = 1;
            $("#quantidade").val(quantidade);
        }
        if (isNaN(vlrUnitario)) {
            vlrUnitario = 0;
        }
        vlrTotal = (vlrUnitario * quantidade).toFixed(2);
        $("#vlrTotal").val(vlrTotal);
        $('#vlrTotal').priceFormat();
    }
    function limparCampos() {
        //Limpando campos
        $('#codPrevisaoOrcamentaria').val('');
        $('#desPrevisaoOrcamentaria').val('');
        $('#tipo').val('');
        $('#codTipoReceitaDespesa').val('');
        $('#quantidade').val('');
        $('#vlrUnitario').val('');
        $('#vlrTotal').val('');
        $('#novoCodPrevisaoOrcamentaria').val(0);
        $('#salvarPrevisaoOrcamentaria').val('Adicionar');

    }
</script>
<!--<script type="text/javascript" src="/<?php echo  WB_PATH ?>/view/js/util/validacoes.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
<h3>Área Administrativa</h3>  
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar Previsão Orçamentária:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formPrevisaoOrcamentaria" name="formPrevisaoOrcamentaria" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="tipo">Classificação Contábil:</label>
                            <select id="tipo" name="tipo" class="form-control" placeholder="Nome para Certificado" required autofocus>
                                <option></option>
                                <option value="Receita">Receita</option>
                                <option value="Despesa">Despesa</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-8" style="padding-left: 0px;">
                            <label for="codTipoReceitaDespesa">Despesa com:</label>
                            <select id="codTipoReceitaDespesa" name="codTipoReceitaDespesa" class="form-control" placeholder="Despesa com..." required autofocus>
                                <option>Selecione uma classificação contábil</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="descricao">Descrição:</label>
                            <textarea class="element textarea form-control"
                                      id="desPrevisaoOrcamentaria"
                                      placeholder="Descricao" required autofocus
                                      name="desPrevisaoOrcamentaria" maxlength="100" rows="3" cols="45"></textarea>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="quantidade">Quantidade:</label>
                            <input type="text" id="quantidade" name="quantidade" value=""  onkeypress="javascript: return isQtde(getKey(event));" onchange="calculaTotal()" 
                                   class="form-control" placeholder="Quantidade" required autofocus>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="vlrUnitario">Valor unitário:</label>
                            <input type="text" id="vlrUnitario" name="vlrUnitario" value=""  onchange="calculaTotal()"
                                   class="form-control" placeholder="Valor unitário" required autofocus>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="vlrTotal">Valor Total:</label>
                            <input type="text" id="vlrTotal" name="vlrTotal" value=""
                                   class="form-control" placeholder="Valor Total" readonly="readonly">
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
                            <input type="hidden" value="GerenciarPrevisaoOrcamentaria" name="c">
                            <input type="hidden" value="salvarPrevisaoOrcamentaria"    name="m">

                            <input type="hidden" name="novoCodPrevisaoOrcamentaria" id="novoCodPrevisaoOrcamentaria" value="0"/>
                            <input type="hidden" name="codPrevisaoOrcamentaria"     id="codPrevisaoOrcamentaria"     value=""/>                
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar" >Salvar</button>
                            &nbsp;&nbsp;&nbsp;                
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvarComoNovo" >Salvar como novo registro</button>
                            &nbsp;&nbsp;&nbsp;                
                            <button class="btn btn-xs btn-primary" type="button" id="btnLimpar" >Limpar e sair</button>
                        </div>
                    </div>  
                </form>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>        
            <?php } ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                Lista de Previsão Orçamentária:
                <table class="tablesorter" border="0">
                    <?php //$objSRV->getListaTipoReceitaDespesa();  ?>
                    <thead>
                    <th width="10px"></th>
                    <th width="10px">Tipo</th>
                    <th width="50px">Nome</th>
                    <th width="250px">Descrição</th>
                    <th width="6px">Qtde.</th>
                    <th width="80px">Valor Unitário (R$)</th>
                    <th width="80px">Valor Total (R$)</th>
                    </thead> 
                    <tbody class="inputs">         
                        <tr><td></td><td> </td><td> </td><td> </td><td></td><td> </td><td> </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
