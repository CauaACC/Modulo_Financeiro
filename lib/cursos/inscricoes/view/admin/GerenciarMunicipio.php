<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarMunicipioAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            alert('1');
            salvarMunicipio();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodMunicipio').val(0);
            $('#salvarMunicipio').val('Adicionar');
            salvarMunicipio();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            editarMunicipio(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirMunicipio(id);
        });  
        carregarTabelaMunicipios();
    });

    //funçães globais
    function carregarTabelaMunicipios() {
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarMunicipio', 'm': 'carregarTabelaMunicipios', '_submit_check': '1'}
        , function(html) {
            $("table tbody.inputs tr").remove();
            $("table").trigger("update");
            $("table tbody.inputs").append(html);
            $("table").trigger("update");
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarMunicipio(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirMunicipio(id);
            });
        }
        );
    }
    function salvarMunicipio() {
        alert('1');
        $('#formMunicipio').ajaxSubmit({
            target: '#mensagem'
                    , dataType: 'json'
                    , async: false
                    , cache: false
                    , beforeSend: function() {
                        alert('2');
                startLoading();
            }
            , timeout: 4000
            , error: function(ajax) {
                showResponse(ajax);
            }
            , success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaMunicipios();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarMunicipio(codMunicipio) {
        if (codMunicipio) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codMunicipio': codMunicipio, 'c': 'GerenciarMunicipio', 'm': 'editarMunicipio', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codMunicipio').val(data.codMunicipio);
                    $('#nome').val(data.nome);
                    $('#uf').val(data.uf);
                    $('#codigo_municipio').val(data.codigo_municipio);
                    $('#url_api').val(data.url_api);
                    $('#observacoes').val(data.observacoes);
                    $('#novoCodMunicipio').val(1);
                    $('#salvarMunicipio').val('Salvar Alterações');
                    $('html,body').scrollTop(0);
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do município não informado!');
        }
    }
    function excluirMunicipio(codMunicipio) {
        if (confirm('Deseja realmente excluir o município?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codMunicipio': codMunicipio, 'c': 'GerenciarMunicipio', 'm': 'excluirMunicipio', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaMunicipios();
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
        $('#codMunicipio').val('');
        $('#nome').val('');
        $('#uf').val('');
        $('#codigo_municipio').val('');
        $('#url_api').val('');
        $('#observacoes').val('');
        $('#novoCodMunicipio').val(0);
        $('#salvarMunicipio').val('Adicionar');
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
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar Municípios:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
                ?>
                <form  id="formMunicipio" name="formMunicipio" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <!-- Campos do formulário adaptados para a tabela fin_municipios -->
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="nome">Nome:</label>
                            <input type="text" id="nome" name="nome" value=""
                                   class="form-control" placeholder="Nome do Município" required autofocus>
                        </div>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="uf">UF:</label>
                            <input type="text" id="uf" name="uf" value=""
                                   class="form-control" placeholder="UF" required>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="codigo_municipio">Código Município:</label>
                            <input type="text" id="codigo_municipio" name="codigo_municipio" value=""
                                   class="form-control" placeholder="Código Município" required>
                        </div>
                        <div class="form-group col-sm-8" style="padding-left: 0px;">
                            <label for="url_api">URL API:</label>
                            <input type="text" id="url_api" name="url_api" value=""
                                   class="form-control" placeholder="URL da API">
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="observacoes">Observações:</label>
                            <textarea id="observacoes" name="observacoes" class="form-control" placeholder="Observações"></textarea>
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
                            <input type="hidden" value="GerenciarMunicipio" name="c">
                            <input type="hidden" value="salvarMunicipio"    name="m">

                            <input type="hidden" name="novoMunicipioId" id="novoMunicipioId" value="0"/>
                            <input type="hidden" name="municipioId"     id="municipioId"     value=""/>                
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar" >Salvar</button>
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvarComoNovo" >Salvar como novo registro</button>
                            <button class="btn btn-xs btn-primary" type="button" id="btnLimpar" >Limpar e sair</button>
                        </div>
                    </div>  
                </form>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>        
            <?php } ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <b>Lista de Municípios Cadastrados:</b>
                <table class="tablesorter" id="itens" border="0">
                    <thead>
                    <!-- Cabeçalho da tabela adaptado para a tabela fin_municipios -->
                    <th width="10px"></th>
                    <th width="250px">Nome</th>
                    <th width="50px">UF</th>
                    <th width="50px">Código Município</th>
                    <th width="50px">URL API</th>
                    <th width="50px">Observações</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaMunicipios($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
</div>    
