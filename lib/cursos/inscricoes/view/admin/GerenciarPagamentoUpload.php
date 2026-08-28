<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarPagamentoUploadAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({});

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            if ($('#tipoArquivo').val() === '') {
                alert('O origem do pagamento não pode estar vazio.');
                return false;
            }
            salvarArquivo();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodArquivo').val(0);
            $('#salvarArquivo').val('Adicionar');
            salvarArquivo();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            editarArquivo(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirArquivo(id);
        });  
        $('.botaoCarga').click(function() {    
            $('html,body').scrollTop(0);
            var id = $(this).parents("tr").attr("id");   
//            carregarPagamento(id);
            carregarInscritoPagamento(id);

        }); 
        $('.botaoAssociar').click(function() {    
            $('html,body').scrollTop(0);
            var id = $(this).parents("tr").attr("id");   
            associarInscritoPagamento(id);

        }); 
        $('.mostrarLog').on('click', function(event) {
            event.preventDefault();
            $(this).closest('tr').find('.messageLog').toggle();
        });        
//        carregarTabelaArquivos();
    });
    //funçães globais
    function carregarPagamento(codArquivo){
        if(codArquivo){
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codEvento': $('#codEvento').val(),'codArquivo': codArquivo,'c':'GerenciarPagamentoUpload', 'm':'carregarArquivoPagamento','_submit_check':'1'}),
                dataType: "json",
                async: false,
                cache: false, 
                beforeSend: function() {
                    startLoading();
                },
                success: function(data){
                    processJsonSemFade(data);
                        if(data.statusMessage){ 
                            carregarTabelaArquivos();
                            limparCampos();
                        }
                    }
                ,complete: function(data){
                    stopLoading();
                }
            });
        }else{
            alert('Código do arquivo não foi informado!');
        }        
    }   
    function carregarInscritoPagamento(codArquivo){
        if(codArquivo){
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codEvento': $('#codEvento').val(),'codArquivo': codArquivo,'c':'GerenciarPagamentoUpload', 'm':'carregarInscritoPagamento','_submit_check':'1'}),
                dataType: "json",
                async: false,
                cache: false, 
                beforeSend: function() {
                    startLoading();
                },
                success: function(data){
                    processJsonSemFade(data);
                    if(data.statusMessage){ 
                        carregarTabelaArquivos();
                        limparCampos();
                    }
                }
                ,complete: function(data){
                    stopLoading();
                }
            });
        }else{
            alert('Código do arquivo não foi informado!');
        }        
    }  
    function associarInscritoPagamento(codArquivo){
        if(codArquivo){
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codEvento': $('#codEvento').val(),'codArquivo': codArquivo,'c':'GerenciarPagamentoUpload', 'm':'associarInscritoPagamento','_submit_check':'1'}),
                dataType: "json",
                async: false,
                cache: false, 
                beforeSend: function() {
                    startLoading();
                },
                success: function(data){
                    processJsonSemFade(data);
                    if(data.statusMessage){ 
                        carregarTabelaArquivos();
                        limparCampos();
                    }
                }
                ,complete: function(data){
                    stopLoading();
                }
            });
        }else{
            alert('Código do arquivo não foi informado!');
        }        
    }  
    function carregarTabelaArquivos() {
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarPagamentoUpload', 'm': 'carregarTabelaArquivos', '_submit_check': '1'}
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
                editarArquivo(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirArquivo(id);
            });
            $('.botaoCarga').click(function() {    
                $('html,body').scrollTop(0);
                var id = $(this).parents("tr").attr("id");   
    //            carregarPagamento(id);
                carregarInscritoPagamento(id);

            }); 
            $('.botaoAssociar').click(function() {    
                $('html,body').scrollTop(0);
                var id = $(this).parents("tr").attr("id");   
                associarInscritoPagamento(id);

            }); 
            $('.mostrarLog').on('click', function(event) {
                event.preventDefault();
                $(this).closest('tr').find('.messageLog').toggle();
            });            
        });
    }
    function salvarArquivo() {
        $('#formArquivo').ajaxSubmit({
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
                    carregarTabelaArquivos();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarArquivo(codArquivo) {
        if (codArquivo) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codArquivo': codArquivo, 'c': 'GerenciarArquivo', 'm': 'editarArquivo', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codArquivo').val(data.codArquivo);
                    $('#tipoAarquivo').val(data.tipoArquivo);
                    $('#novoCodArquivo').val(1);
                    $('#salvarArquivo').val('Salvar Alterações');
                    $('#arquvo').focus();
                    $('html,body').scrollTop(0);
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do desconto não informado!');
        }
    }
    function excluirArquivo(codArquivo) {
        if (confirm('Deseja realmente excluir Arquivo?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codArquivo': codArquivo, 'c': 'GerenciarPagamentoUpload', 'm': 'excluirArquivo', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaArquivos();
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
        $('#codArquivo').val('');
        $('#tipoArquivo').val('');
        $('#arquivo').val('');
        $('#novoCodArquivo').val(0);
        $('#salvarArquivo').val('Adicionar');

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
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar Arquivo Pagamento:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formArquivo" name="formArquivo"
                   action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="tipoArquivo">Origem Pagamento:</label>
                            <select class='form-control' id='tipoArquivo' name='tipoArquivo' required>
                                <option value=''></option>
                                <?php
                                CarregarOptionsAC::tipoArquivoPagamento($fws_, $debug);
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="codPerfil">Perfil:</label>
                            <select class='form-control' id='codPerfil' name='codPerfil'>
                                <option value=''></option>
                                <?php
                                $fws_['codEvento'] = $objEv->getCodEvento();
                                $fws_['semRestricao']=true;
                                CarregarOptionsAC::perfis($fws_, $debug);
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="arquivo">Selecionar Arquivo:</label>
                            <input type="file" name="arquivo" id="arquivo" value=""
                                   placeholder="Inserir arquivo"  class="btn-xs" required="">   
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
                            <input type="hidden" value="GerenciarPagamentoUpload" name="c">
                            <input type="hidden" value="salvarPagamentoUpload"    name="m">
                            <input type="hidden" name="codEvento" id="codEvento" value="<?php echo COD_EVENTO?>">

                            <input type="hidden" name="novoCodArquivo" id="novoCodArquivo" value="0"/>
                            <input type="hidden" name="codArquivo"     id="codArquivo"     value=""/>                
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar" >Enviar Arquivo</button>
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
                <b>Lista de Arquivos Cadastrados:</b>
                <table class="tablesorter" id="itens" border="0">
                    <thead>
                    <th width="10px"></th>
                    <th width="50px">Status<br>Carga</th>
                    <th width="50px">Origem</th>
                    <th width="100px">Arquivo gerado</th>
                    <th width="400px">Arquvo enviado</th>
                    <th width="50px">Data inclusão</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaArquivos($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
</div>    

