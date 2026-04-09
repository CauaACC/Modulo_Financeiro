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

$objSRV = new GerenciarRateiosAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
//            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarRateios();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novocodRateio').val(0);
            $('#salvarRateios').val('Adicionar');
            salvarRateios();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarRateios(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirRateios(id);
            });        
//        carregarTabelaRateioss();
    });
    //funçães globais
    function carregarTabelaRateioss() {
//        alert($('#ordenar').val())
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarRateios', 'm': 'carregarTabelaRateioss', 'ordenar': $('#ordenar').val(), '_submit_check': '1'}
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
                editarRateios(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirRateios(id);
            });
        }
        );
    }
    function salvarRateios() {
        $('#formRateios').ajaxSubmit({
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
                    carregarTabelaRateioss();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarRateios(codRateio) {
        if (codRateio) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codRateio': codRateio, 'c': 'GerenciarRateios', 'm': 'editarRateios', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codRateio').val(data.codRateio);
                    $('#nomeRateio').val(data.nomeRateio);
                    $('#descricao').val(data.descricao);
                    $('#status').val(data.status);
                    $('#percentual').val(data.percentual);
                    $('#salvarRateios').val('Salvar Alterações');
                    $('#nomeRateio').focus();
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
    function excluirRateios(codRateio) {
        if (confirm('Deseja realmente excluir Rateios?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codRateio': codRateio, 'c': 'GerenciarRateios', 'm': 'excluirRateios', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaRateioss();
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
        $('#codRateio').val('');
        $('#nomeRateio').val('');
        $('#percentual').val('');
        $('#descricao').val('');
        $('#getMessage').html('');
        // $('#novocodRateio').val(0);
        $('#salvarRateios').val('Adicionar');

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
            <h4>Gerenciar Rateios:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formRateios" name="formRateios" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <div class="form-group col-sm-5" style="padding-left: 0px;">
                            <label for="nome">Nome do Rateio:</label>
                            <input type="text" id="nomeRateio" name="nomeRateio" value=""
                                   class="form-control" placeholder="Nome Rateios" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="codRateioPai">Cod. Rateios Pai:</label>
                            <select id="codRateioPai" name="codRateioPai" class="form-control">
                                <option value="">Selecione Conta Pai se necessário</option>
                                <?php 
                                try {
                                    CarregarOptionsAC::rateiosPai($fws_);
                                } catch (Exception $e) {
                                    // echo "<option value=''>Erro ao carregar opções</option>";
                                }
                            ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-10" style="padding-left: 0px;">
                            <label for="percentual">Percentual (O número será considerado em porcentagem):</label>
                            <input id="percentual" name="percentual" value=""
                                   class="form-control" placeholder="0 - 100" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="descricao">Descrição:</label>
                            <textarea id="descricao" name="descricao" value=""
                                   class="form-control" placeholder="Descrição" required autofocus></textarea>
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
                            <input type="hidden" value="GerenciarRateios" name="c">
                            <input type="hidden" value="salvarRateios"    name="m">
                            
                            <br>&nbsp;
                            <!-- <input type="hidden" name="novocodRateio" id="novocodRateio" value="0"/> -->
                            <input type="hidden" name="codRateio"     id="codRateio"     value=""/>                
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
                <b>Lista de Rateios Cadastrados:</b>
                <table class="tablesorter" id='itens' border="0">
                    <thead>
                    <th width="10px"></th>
                    <th width="40px">Código Contábil</th>
                    <th width="40px">Nome</th>
                    <th width="10px">Percentual (%)</th>
                    <th width="100px">Descrição</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaRateioss($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
