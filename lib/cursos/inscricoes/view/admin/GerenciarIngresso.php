<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarIngressoAC();
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
            salvarIngresso();
        });
        $('#btnSalvarComoNovo').click(function () {
            $('#novoId').val(0);
            $('#salvarIngresso').val('Adicionar');
            salvarIngresso();
        });
        $('#btnLimpar').click(function () {
            limparCampos();
        });
        $('.editar').click(function () {
            var id = $(this).parents("tr").attr("id");
            editarIngresso(id);
        });
        $('.excluir').click(function () {
            var id = $(this).parents("tr").attr("id");
            excluirIngresso(id);
        });

        $("#dataVencimento").datepicker({
            showOn: 'both',
            buttonImage: 'resources/image/calendario.jpg',
            buttonText: 'Calendário',
            buttonImageOnly: true
        });
    });

    //funçães globais
    function carregarTabelaIngressos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random()
            , {'c': 'GerenciarIngresso', 'm': 'carregarTabelaIngressos', '_submit_check': '1'}
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
                    editarIngresso(id);
                });
                $('.excluir').click(function () {
                    var id = $(this).parents("tr").attr("id");
                    excluirIngresso(id);
                });
            }
        );
    }

    function salvarIngresso() {
        $('#formIngresso').ajaxSubmit({
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
                    carregarTabelaIngressos();
                    limparCampos();
                }
            }
            , complete: function () {
                stopLoading();
            }
        });
    }

    function editarIngresso(id) {
        if (id) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'id': id,
                    'c': 'GerenciarIngresso',
                    'm': 'editarIngresso',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    console.log('Dados obtidos:', data);

                    //Setando valores para alteração
                    $('#nome').val(data.nome);
                    $('#rg').val(data.rg);
                    $('#idade').val(data.idade);
                    $('#categoria').val(data.categoria);
                    $('#valor').val(data.valor);
                    $('#dias').val(data.dias);
                    $('#quantidade').val(data.quantidade);
                    $('#novoId').val(1);
                    $('#salvarIngresso').val('Salvar Alterações');
                    $('html,body').scrollTop(0);
                },
                complete: function () {
                    stopLoading();
                },
                error: function (xhr, status, error) {
                    console.error('Erro na solicitação AJAX:', error);
                }
            });
        } else {
            alert('Código do Ingresso não informado!');
        }
    }

    //function editarIngresso(id) {
    //    if (id) {
    //        $.ajax({
    //            type: "POST",
    //            url: '<?php //echo WB_URL_COMMAND ?>//&t=' + Math.random(),
    //            data: ({
    //                'id': id,
    //                'c': 'GerenciarIngresso',
    //                'm': 'editarIngresso',
    //                '_submit_check': '1'
    //            }),
    //            dataType: "json",
    //            async: false,
    //            cache: false,
    //            beforeSend: function () {
    //                startLoading();
    //            },
    //            success: function (data) {
    //                //Setando valores para alteração
    //                $('#nome').val(data.nome);
    //                $('#rg').val(data.rg);
    //                $('#idade').val(data.idade);
    //                $('#categoria').val(data.categoria);
    //                $('#valor').val(data.valor);
    //                $('#dias').val(data.dias);
    //                $('#quantidade').val(data.quantidade);
    //                $('#novoId').val(1);
    //                $('#salvarIngresso').val('Salvar Alterações');
    //                $('html,body').scrollTop(0);
    //            }
    //            , complete: function () {
    //                stopLoading();
    //            }
    //        });
    //    } else {
    //        alert('Código do Ingresso não informado!');
    //    }
    //}

    function excluirIngresso(id) {
        if (confirm('Deseja realmente excluir Ingresso?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'id': id,
                    'c': 'GerenciarIngresso',
                    'm': 'excluirIngresso',
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
                        carregarTabelaIngressos();
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
        $('#nome').val('');
        $('#rg').val('');
        $('#idade').val('');
        $('#categoria').val('');
        $('#valor').val('');
        $('#dias').val('');
        $('#quantidade').val('');
        $('#novoId').val(0);
        $('#salvarIngresso').val('Adicionar');
    }

    function removerNaoNumericos(input) {
        // Remove caracteres não numéricos do valor do campo
        input.value = input.value.replace(/\D/g, '');

        if (input.value.length > 3) {
            input.value = input.value.slice(0, 3);
        }
    }

    function removerNumeros(input) {
        // Remove números do valor do campo
        input.value = input.value.replace(/[0-9]/g, '');
    }

    function formatarMoeda() {
        var elemento = document.getElementById('valor');
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
<!-- Inclua o jQuery primeiro -->
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-1.3.2.min.js"></script>
<!-- Em seguida, inclua os plugins do jQuery -->
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-ui-1.7.3.custom.min.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/i18n/ui.datepicker-pt-BR.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/ui.dialog.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<!-- Adicione o plugin do tablesorter após o jQuery -->
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<!-- Adicione o seu script personalizado -->
<script type="text/javascript" src="resources/js/default.js"></script>
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
            <form id="formIngresso" name="formIngresso" action="<?php echo WB_URL_COMMAND ?>&dummy="
                  method="post">

                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="nome">Nome :</label>
                        <input type="text" id="nome" name="nome" value="" class="form-control" placeholder="Nome" oninput="removerNumeros(this)">
                    </div>

                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="nome">RG :</label>
                        <input type="text" id="rg" name="rg" value="" class="form-control"
                               placeholder="RG">
                    </div>

                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="idade">Idade :</label>
                        <input type="text" id="idade" name="idade" value="" class="form-control" placeholder="Idade" oninput="removerNaoNumericos(this)" required autofocus>
                    </div>

                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="nome">Categoria :</label>
                        <select id="categoria" name="categoria" class="form-control" required>
                            <option value="">Selecione Categoria</option>
                            <option value="aprovado">Visitante</option>
                            <option value="pendente">Estudante</option>
                            <option value="recusado">Professor</option>
                        </select>
                    </div>

                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="valor">Valor (R$) :</label>
                        <input type="text" id="valor" name="valor" class="form-control"
                               placeholder="0,00" required autofocus onkeyup="formatarMoeda();" onkeypress='javascript: return isInt(getKey(event));'>
                    </div>

                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="dias">Dias :</label>
                        <select id="dias" name="dias" class="form-control" required autofocus>
                            <?php
                            for ($i = 1; $i <= 50; $i++) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="quantidade">Quantidade :</label>
                        <select id="quantidade" name="quantidade" class="form-control" required autofocus>
                            <?php
                            for ($i = 1; $i <= 25; $i++) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
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
                        <input type="hidden" value="GerenciarIngresso" name="c">
                        <input type="hidden" value="salvarIngresso" name="m">
                        <input type="hidden" name="novoId" id="novoId" value="0"/>
                        <input type="hidden" name="id" id="id" value=""/>
                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvar">Salvar Ingresso</button>
                        <button class="btn btn-xs btn-primary" type="button" id="btnLimpar">Limpar Campos</button>
                    </div>
            </form>
        </div>
        <?php } catch (Exception $exc) { ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
        <?php } ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <b>Lista de Gerenciar Ingresso Cadastrados:</b>
            <table class="tablesorter" id="itens" border="0">
                <thead>
                    <th width="15px"></th>
                    <th width="250px">Nome</th>
                    <th width="100px">RG</th>
                    <th width="80px">Idade</th>
                    <th width="120px">Categoria</th>
                    <th width="120px">Valor (R$)</th>
                    <th width="80px">Dias</th>
                    <th width="80px">Quantidade</th>
                </thead>
                <tbody class="inputs">
                <?php
                $objSRV->carregarTabelaIngressos($fws_, $debug);
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>