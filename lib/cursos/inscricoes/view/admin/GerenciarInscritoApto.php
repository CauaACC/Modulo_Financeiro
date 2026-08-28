<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarInscritoAptoAC();
?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');
        
        $('table').tablesorter({
//            sortList: [[1, 0]]
        });

        $('#btnSalvar').click(function() {
            salvarInscritoApto();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodInscritoApto').val(0);
            $('#salvarInscritoApto').val('Adicionar');
            salvarInscritoApto();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            editarInscritoApto(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirInscritoApto(id);
        });
        $('.desativar').click(function() {
            var id = $(this).parents("tr").attr("id");
            desativarInscritoApto(id);
        });
//        carregarTabelaInscritoAptos();
    });
    //funçães globais
    function carregarTabelaInscritoAptos() {
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarInscritoApto', 'm': 'carregarTabelaInscritoAptos', '_submit_check': '1'}
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
                editarInscritoApto(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirInscritoApto(id);
            });
            $('.desativar').click(function() {
                var id = $(this).parents("tr").attr("id");
                desativarInscritoApto(id);
            });
        }
        );
    }
    function salvarInscritoApto() {
        $('#formInscritoApto').ajaxSubmit({
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
                    carregarTabelaInscritoAptos();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarInscritoApto(codInscritoApto) {
        if (codInscritoApto) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codInscritoApto': codInscritoApto, 'c': 'GerenciarInscritoApto', 'm': 'editarInscritoApto', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codInscritoApto').val(data.codInscritoApto);
                    $('#nomeInscritoApto').val(data.nomeInscritoApto);
                    $('#cpf').val(data.cpf);
                    $('#email').val(data.email);
                    $('#telefone').val(data.telefone);
                    $('#codPerfil').val(data.codPerfil);
                    $('#status').val(data.status);
                    $('#novoCodInscritoApto').val(1);
                    $('#salvarInscritoApto').val('Salvar Alterações');
                    $('#cpf').focus();
                    $('html,body').scrollTop(0);
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do inscritoapto não informado!');
        }
    }
    function excluirInscritoApto(codInscritoApto) {
        if (confirm('Deseja realmente excluir Inscrito Apto cód.: '+codInscritoApto+'?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codInscritoApto': codInscritoApto, 'c': 'GerenciarInscritoApto', 'm': 'excluirInscritoApto', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaInscritoAptos();
                    }
                    limparCampos();
                }
                , complete: function() {
                    stopLoading();
                }
            });
        }
    }
    function desativarInscritoApto(codInscritoApto) {
        if (confirm('Deseja realmente desativar Inscrito Apto cód.: '+codInscritoApto+'?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codInscritoApto': codInscritoApto, 'c': 'GerenciarInscritoApto', 'm': 'desativarInscritoApto', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaInscritoAptos();
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
        //Limpando campos
        $('#codInscritoApto').val('');
        $('#nomeInscritoApto').val('');
        $('#cpf').val('');
        $('#email').val('');
        $('#telefone').val('');
        $('#codPerfil').val('');
        $('#novoCodInscritoApto').val(0);
        $('#salvarInscritoApto').val('Adicionar');

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
<!-- <h3>Área Administrativa</h3>   -->
<?php
// require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");
    require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");        
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_inscricoes.php");
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Gerenciar Inscritos Aptos:</h4></div>
    <div class="panel-body">
        <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formInscritoApto" name="formInscritoApto" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="cpf">Cpf:</label>
                            <input type="text" id="cpf" name="cpf" value="" maxlength="11"
                                   onkeypress='javascript: return isNumInt(getKey(event));'
                                   class="form-control" placeholder="cpf" required autofocus>
                        </div>
                        <div class="form-group col-sm-5" style="padding-left: 0px;">
                            <label for="email">Email:</label>
                            <input type="text" id="email" name="email" value=""
                                   class="form-control" placeholder="Email" >
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="telefone">Telefone:</label>
                            <input type="text" id="telefone" name="telefone" value=""
                                   class="form-control" placeholder="Telefone" >
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Nome Inscrito Apto:</label>
                            <input type="text" id="nomeInscritoApto" name="nomeInscritoApto" value=""
                                   class="form-control" placeholder="Nome InscritoApto" required autofocus>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="codPerfil">Selecionar Perfil:</label>
                            <select class='form-control' id='codPerfil' name='codPerfil'>
                                <option value=""></option>
                                <?php
                                $fws_['codEvento'] = $objEv->getCodEvento();
                                $fws_['semRestricao']=true;
                                CarregarOptionsAC::perfis($fws_, $debug);
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="status">Selecionar Status:</label>
                            <select class='form-control' id='status' name='status'>
                                <option value=""></option>
                                <?php
                                CarregarOptionsAC::status($fws_['status'], $debug);
                                ?>
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
                            <input type="hidden" value="GerenciarInscritoApto" name="c">
                            <input type="hidden" value="salvarInscritoApto"    name="m">

                            <input type="hidden" name="novoCodInscritoApto" id="novoCodInscritoApto" value="0"/>
                            <input type="hidden" name="codInscritoApto"     id="codInscritoApto"     value=""/>                
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar" >Salvar</button>
                            <!--                            &nbsp;&nbsp;&nbsp;                
                                                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvarComoNovo" >Salvar como novo registro</button>-->
                            &nbsp;&nbsp;&nbsp;                
                            <button class="btn btn-xs btn-primary" type="button" id="btnLimpar" >Limpar e sair</button>
                        </div>
                    </div>  
                </form>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>        
            <?php } ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <b>Lista de Inscritos Aptos Cadastrados:</b>
                <table class="tablesorter" id='itens' border="0">
                    <thead>
                    <th width="20px"></th>
                    <th width="250px">Nome</th>
                    <th width="50px">Cpf</th>
                    <th width="50px">Email</th>
                    <th width="50px">Telefone</th>
                    <th width="50px">Cód.Perfil</th>
                    <th width="50px">Sit.Como.Apto</th>
                    <th width="50px">Ja inscrito</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaInscritoAptos($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
</div>    
