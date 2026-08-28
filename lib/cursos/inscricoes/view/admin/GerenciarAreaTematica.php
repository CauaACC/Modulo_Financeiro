<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarAreaTematicaAC();
?>
<script type="text/javascript">
    $(document).ready(function() {

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarAreaTematica();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoId').val(0);
            $('#salvarAreaTematica').val('Adicionar');
            salvarAreaTematica();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            editarAreaTematica(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirAreaTematica(id);
        });
    });

    //funçães globais
    function carregarTabelaAreaTematica() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarAreaTematica',
            'm': 'carregarTabelaAreaTematica',
            '_submit_check': '1'
        }, function(html) {
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
                editarAreaTematica(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirAreaTematica(id);
            });
        });
    }

    function salvarAreaTematica() {
        $('#formAreaTematica').ajaxSubmit({
            target: '#mensagem',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            timeout: 4000,
            error: function(ajax) {
                showResponse(ajax);
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaAreaTematica();
                    limparCampos();
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function editarAreaTematica(codAreaTematica) {
        if (codAreaTematica) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codAreaTematica': codAreaTematica,
                    'c': 'GerenciarAreaTematica',
                    'm': 'editarAreaTematica',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    console.log('Dados obtidos:', data);
                    //Setando valores para alteração
                    $('#nome').val(data.nome);
                    $('#updateAreaTematica').val(1);

                    $('#codAreaTematica').val(data.codAreaTematica);
                    $('#salvarAreaTematica').val('Salvar Alterações');
                    $('html,body').scrollTop(0);
                },
                complete: function() {
                    stopLoading();
                },
                error: function(xhr, status, error) {
                    console.error('Erro na solicitação AJAX:', error);
                }
            });
        } else {
            alert('Código do AreaTematica não informado!');
        }
    }

    function excluirAreaTematica(codAreaTematica) {
        if (confirm('Deseja realmente excluir AreaTematica?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codAreaTematica': codAreaTematica,
                    'c': 'GerenciarAreaTematica',
                    'm': 'excluirAreaTematica',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaAreaTematica();
                    }
                    limparCampos();
                },
                complete: function() {
                    stopLoading();
                }
            });
        }
    }

    function limparCampos() {
        $('#assunto').val('');
        $('#corpo').val('');
        $('#descricao').val('');
        $('#tipoDestinatario').val('');
        $('#modeloChave').val('');
        $('#appLocal').val('');
        $('#status').val('');
    }
</script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar AreaTematica:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
            ?>
                <form id="formAreaTematica" name="formAreaTematica" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post"  class="hide">

                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="assunto">Assunto: </label>
                        <input type="text" id="assunto" name="assunto" value="" class="form-control">
                    </div>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="corpo">Corpo: </label>
                        <textarea id="corpo"  rows="6" type="text" onblur="trim(this);" name="corpo" value="" class="form-control"></textarea>
                    </div>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="descricao">Descrição: </label>
                        <textarea id="descricao" rows="4" type="text" onblur="trim(this);" name="descricao" value="" class="form-control"></textarea>
                    </div>
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="tipoDestinatario">Tipo Destinatario: </label>
                        <input type="text" id="tipoDestinatario" name="tipoDestinatario" value="" class="form-control">
                    </div>
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="modeloChave">Modelo Chave: </label>
                        <input type="text" id="modeloChave" name="modeloChave" value="" class="form-control">
                    </div>
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="appLocal">App Local: </label>
                        <input type="text" id="appLocal" name="appLocal" value="" class="form-control">
                    </div>
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="status">Status: </label>
                        <input type="text" id="status" name="status" value="" class="form-control">
                    </div>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <div id="carregando" class="shown hidden">
                            <div class="imagem">
                                <div class="frase">Carregando...</div>
                                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
                            </div>
                        </div>
                        <div id="mensagem"></div>
                        <div id="sucesso" class="hidden"></div>
                        <div id="erro" class="hidden"></div>
                        <input type="hidden" value="1" name="_submit_check">
                        <input type="hidden" value="GerenciarAreaTematica" name="c">
                        <input type="hidden" value="salvarAreaTematica" name="m">
                        <input type="hidden" name="updateAreaTematica" id="updateAreaTematica" value="0" />
                        <input type="hidden" name="codAreaTematica" id="codAreaTematica" value="" />
                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvar">Salvar</button>
                        <button class="btn btn-xs btn-primary" type="button" id="btnLimpar">Limpar Campos</button>
                    </div>
                </form>

                <?php } catch (Exception $exc) { ?>
                    <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
                <?php } ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;padding-top: 15px;">
                    <b>Lista Gerenciar Área Temática:</b>
                    <table class="tablesorter" id="itens" style="border: 0;">
                        <thead>
                            <th width="15%"></th>
                            <th width="70%">Titulo</th>
                            <th width="15%">Status</th>
                        </thead>
                        <tbody class="inputs">
                            <?php
                            $objSRV->carregarTabelaAreaTematica($fws_, $debug);
                            ?>
                        </tbody>
                    </table>
                </div>
        </div>
    </div>
</div>