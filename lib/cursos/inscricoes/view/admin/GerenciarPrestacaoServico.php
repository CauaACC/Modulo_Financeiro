<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarPrestacaoServicoAC();
?>
<script type="text/javascript">
    $(document).ready(function () {
        // se eu deixo descomentado, o datepicker não funciona
        // $('table').tablesorter({
        //     sortList: [[1, 0]]
        // });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function () {

            salvarPrestacaoServico();
        });
        $('#btnSalvarComoNovo').click(function () {
            $('#novoCodPrestacaoServico').val(0);
            $('#salvarPrestacaoServico').val('Adicionar');
            salvarPrestacaoServico();
        });
        $('#btnLimpar').click(function () {
            limparCampos();
        });
        $('.editar').click(function () {
            var id = $(this).parents("tr").attr("id");
            editarPrestacaoServico(id);
        });
        $('.excluir').click(function () {
            var id = $(this).parents("tr").attr("id");
            excluirPrestacaoServico(id);
        });

        $("#dataVencimento").datepicker({
            showOn: 'both',
            buttonImage: 'resources/image/calendario.jpg',
            buttonText: 'Calendário',
            buttonImageOnly: true
        });
    });

    //funçães globais
    function carregarTabelaPrestacaoServicos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random()
            , {'c': 'GerenciarPrestacaoServico', 'm': 'carregarTabelaPrestacaoServicos', '_submit_check': '1'}
            , function (html) {
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
                $('.editar').click(function () {
                    var id = $(this).parents("tr").attr("id");
                    editarPrestacaoServico(id);
                });
                $('.excluir').click(function () {
                    var id = $(this).parents("tr").attr("id");
                    excluirPrestacaoServico(id);
                });
            }
        );
    }

    function salvarPrestacaoServico() {
        $('#formPrestacaoServico').ajaxSubmit({
            target: '#mensagem'
            , dataType: 'json'
            , async: false
            , cache: false
            , beforeSend: function () {
                startLoading();
            }
            , timeout: 4000
            , error: function (ajax) {
                showResponse(ajax);
            }
            , success: function (data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaPrestacaoServicos();
                    limparCampos();
                }
            }
            , complete: function () {
                stopLoading();
            }
        });
    }

    function editarPrestacaoServico(codPrestacaoServico) {
        if (codPrestacaoServico) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codPrestacaoServico': codPrestacaoServico,
                    'c': 'GerenciarPrestacaoServico',
                    'm': 'editarPrestacaoServico',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    //Setando valores para alteração
                    $('#nomeIdentificador').val(data.nomeIdentificador);
                    $('#valorPrestacaoServico').val(data.valorPrestacaoServico);
                    $('#dataVencimento').val(data.dataVencimento);
                    $('#nomePrestador').val(data.nomePrestador);
                    $('#situacaoPago').val(data.situacaoPago);
                    $('#codPrestacaoServico').val(data.codPrestacaoServico);
                    $('#novoCodPrestacaoServico').val(1);
                    $('#salvarPrestacaoServico').val('Salvar Alterações');
                    $('html,body').scrollTop(0);
                }
                , complete: function () {
                    stopLoading();
                }
            });
        } else {
            alert('Código do prestação serviço não informado!');
        }
    }

    function excluirPrestacaoServico(codPrestacaoServico) {
        if (confirm('Deseja realmente excluir Prestação Serviço?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codPrestacaoServico': codPrestacaoServico,
                    'c': 'GerenciarPrestacaoServico',
                    'm': 'excluirPrestacaoServico',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaPrestacaoServicos();
                    }
                    limparCampos();
                }
                , complete: function () {
                    stopLoading();
                }
            });
        }
    }

    function limparCampos() {
        //Limpando campos
        $('#nomeIdentificador').val('');
        $('#valorPrestacaoServico').val('');
        $('#dataVencimento').val('');
        $('#nomePrestador').val('');
        $('#situacaoPago').val('');
        $('#codPrestacaoServico').val('');

        $('#novoCodPrestacaoServico').val(0);
        $('#salvarPrestacaoServico').val('Adicionar');
    }

    function formatarMoeda() {
        var elemento = document.getElementById('valorPrestacaoServico');
        var valor = elemento.value;

        // Remove caracteres não numéricos
        valor = valor.replace(/\D/g, '');

        // Adiciona vírgula para separar os centavos
        valor = valor.replace(/([0-9]{2})$/g, ",$1");

        // Adiciona ponto para separar milhares
        if (valor.length > 6) {
            valor = valor.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
        }

        // Atualiza o valor no campo
        elemento.value = valor;
    }

</script>
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>

<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-1.3.2.min.js"></script>
<link type="text/css" href="resources/js/jquery-ui-1.7.3.custom/css/smoothness/jquery-ui-1.7.3.custom.css"
      rel="stylesheet"/>
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-ui-1.7.3.custom.min.js"></script>
<script type="text/javascript"
        src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/i18n/ui.datepicker-pt-BR.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/ui.dialog.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_financeiro.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar Prestação Serviço:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
            ?>
            <form id="formPrestacaoServico" name="formPrestacaoServico" action="<?php echo WB_URL_COMMAND ?>&dummy="
                  method="post">
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="nome">Nome Identificador :</label>
                        <input type="text" id="nomeIdentificador" name="nomeIdentificador" value="" class="form-control"
                               placeholder="Nome Identificador">
                    </div>
                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="valorPrestacaoServico">Valor Prestação Serviço (R$)</label>
                        <input type="text" id="valorPrestacaoServico" name="valorPrestacaoServico" class="form-control"
                               placeholder="0,00" required autofocus onkeyup="formatarMoeda();" onkeypress='javascript: return isInt(getKey(event));'>
                    </div>
                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="dataVencimento">Data Vencimento :</label>
                        <input type="text" id="dataVencimento" name="dataVencimento"
                               value="<?php echo $fws_['dataVencimento'] ?>">
                    </div>
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="nome">Nome do Prestador :</label>
                        <input type="text" id="nomePrestador" name="NomePrestador" value="" class="form-control"
                               placeholder="Nome do Prestador" required autofocus>
                    </div>
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="nome">Situação Pago :</label>
                        <select id="situacaoPago" name="situacaoPago" class="form-control" required>
                            <option value="">Selecione a Situação</option>
                            <option value="aprovado">Aprovado</option>
                            <option value="pendente">Pendente</option>
                            <option value="recusado">Recusado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="estornando">Estornando</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <div id="carregando" class="shown hidden">
                            <div class="imagem">
                                <div class="frase">Carregando...</div>
                                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
                            </div>
                        </div>
                        <div id="mensagem"></div>
                        <div id="sucesso" class="hidden"></div>
                        <div id="erro" class="hidden"></div>
                        <input type="hidden" value="1" name="_submit_check">
                        <input type="hidden" value="GerenciarPrestacaoServico" name="c">
                        <input type="hidden" value="salvarPrestacaoServico" name="m">
                        <input type="hidden" name="novoCodPrestacaoServico" id="novoCodPrestacaoServico" value="0"/>
                        <input type="hidden" name="codPrestacaoServico" id="codPrestacaoServico" value=""/>
                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvar">Salvar</button>
                        <button class="btn btn-xs btn-primary" type="button" id="btnLimpar">Limpar Campos</button>
                    </div>
            </form>
        </div>
        <?php } catch (Exception $exc) { ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
        <?php } ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <b>Lista de Prestação Serviço Cadastrados:</b>
            <table class="tablesorter" id="itens" border="0">
                <thead>
                    <th width="15px"></th>
                    <th width="250px">Nome identificador</th>
                    <th width="120px">Valor Prestação Serviço (R$)</th>
                    <th width="120px">Data Vencimento</th>
                    <th width="250px">Nome do Prestador</th>
                    <th width="80px">Situação Pago</th>
<!--                    <th width="15px">Abir Como</th>-->
                </thead>
                <tbody class="inputs">
                <?php
                $objSRV->carregarTabelaPrestacaoServicos($fws_, $debug);
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>