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

$objSRV = new GerenciarCentroCustoAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
//            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarCentroCusto();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodCentroCusto').val(0);
            $('#salvarCentroCusto').val('Adicionar');
            salvarCentroCusto();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarCentroCusto(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirCentroCusto(id);
            });        
//        carregarTabelaCentroCustos();
    });
    //funçães globais
    function carregarTabelaCentroCustos() {
//        alert($('#ordenar').val())
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarCentroCusto', 'm': 'carregarTabelaCentroCustos', 'ordenar': $('#ordenar').val(), '_submit_check': '1'}
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
                editarCentroCusto(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirCentroCusto(id);
            });
        }
        );
    }
    function salvarCentroCusto() {
        $('#formCentroCusto').ajaxSubmit({
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
                    carregarTabelaCentroCustos();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarCentroCusto(codCentroCusto) {
        if (codCentroCusto) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codCentroCusto': codCentroCusto, 'c': 'GerenciarCentroCusto', 'm': 'editarCentroCusto', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codCentroCusto').val(data.codCentroCusto);
                    $('#nomeCentroCusto').val(data.nomeCentroCusto);
                    $('#descricao').val(data.descricao);
                    $('#status').val(data.status);
                    $('#salvarCentroCusto').val('Salvar Alterações');
                    $('#nomeCentroCusto').focus();
                    $('html,body').scrollTop(0);
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do centro de custo não informado!');
        }
    }
    function excluirCentroCusto(codCentroCusto) {
        if (confirm('Deseja realmente excluir CentroCusto?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codCentroCusto': codCentroCusto, 'c': 'GerenciarCentroCusto', 'm': 'excluirCentroCusto', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaCentroCustos();
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
        $('#codCentroCusto').val('');
        $('#nomeCentroCusto').val('');
        $('#descricao').val('');
        $('#getMessage').html('');
        // $('#novoCodCentroCusto').val(0);
        $('#salvarCentroCusto').val('Adicionar');

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
            <h4>Gerenciar CentroCustos:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formCentroCusto" name="formCentroCusto" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Nome CentroCusto:</label>
                            <input type="text" id="nomeCentroCusto" name="nomeCentroCusto" value=""
                                   class="form-control" placeholder="Nome CentroCusto" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="codCentroCustoPai">Cod. CnetroCusto Pai:</label>
                            <select id="codCentroCustoPai" name="codCentroCustoPai" class="form-control">
                                <option value="">Selecione Conta Pai se necessário</option>
                                <?php CarregarOptionsAC::centroCustoPai($fws_) ?>
                            </select>
                        </div>                        
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="descricao">Descrição:</label>
                            <textarea id="descricao" name="descricao" value=""
                                   class="form-control" placeholder="Descrição" required autofocus></textarea>
                        </div>
                        <!-- <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="status">Status:</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="ATIVO">ATIVADA</option>
                                <option value="INATIVO">DESATIVADA</option>
                            </select>
                        </div> -->
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
                            <input type="hidden" value="GerenciarCentroCusto" name="c">
                            <input type="hidden" value="salvarCentroCusto"    name="m">
                            
                            <br>&nbsp;
                            <!-- <input type="hidden" name="novoCodCentroCusto" id="novoCodCentroCusto" value="0"/> -->
                            <input type="hidden" name="codCentroCusto"     id="codCentroCusto"     value=""/>                
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
                <b>Lista de CentroCustos Cadastrados:</b>
                <table class="tablesorter" id='itens' border="0">
                    <thead>
                    <th width="10px"></th>
                    <th width="40px">Código Contábil</th>
                    <th width="40px">Nome</th>
                    <th width="140px">Descrição</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaCentroCustos($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
