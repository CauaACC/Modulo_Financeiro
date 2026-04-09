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

$objSRV = new GerenciarPlanoContasAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
//            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarPlanoContas();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodPlanoContas').val(0);
            $('#salvarPlanoContas').val('Adicionar');
            salvarPlanoContas();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            editarPlanoContas(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirPlanoContas(id);
        });
        $('#codPlanoContasPai').change(function () {
            
            var tipoPai = $('#codPlanoContasPai option:selected').data('tipo');

            // Se não escolheu pai (conta raiz)
            if (!tipoPai) {
                $('#tipoConta')
                    .prop('disabled', false)
                    .val('')
                    .css('background', '');
                return;
            }

            // Herda o tipo do pai
            $('#tipoConta').val(tipoPai);
                   
            // Bloqueia para não permitir mudança
            $('#tipoConta')
                .prop('readonly', true)
                .css('background', '#eee');
        });        
//        carregarTabelaPlanoContass();
    });
    //funçães globais
    function carregarTabelaPlanoContass() {
//        alert($('#ordenar').val())
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarPlanoContas', 'm': 'carregarTabelaPlanoContass', 'ordenar': $('#ordenar').val(), '_submit_check': '1'}
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
                editarPlanoContas(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirPlanoContas(id);
            });
        }
        );
    }
    function salvarPlanoContas() {
        $('#formPlanoContas').ajaxSubmit({
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
                    carregarTabelaPlanoContass();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarPlanoContas(codPlanoContas) {
        if (codPlanoContas) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codPlanoContas': codPlanoContas, 'c': 'GerenciarPlanoContas', 'm': 'editarPlanoContas', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codPlanoContas').val(data.codPlanoContas);
                    $('#codPlanoContasPai').val(data.codPlanoContasPai);
                    $('#nomePlanoContas').val(data.nomePlanoContas);
                    $('#tipoConta').val(data.tipoConta);
                    $('#naturezaSaldo').val(data.naturezaSaldo);
                    $('#codigoContabil').val(data.codigoContabil);
                    $('#descricao').val(data.descricao);
                    $('#aceitaLancamento').val(data.aceitaLancamento);
                    $('#status').val(data.status);
                    $('#salvarPlanoContas').val('Salvar Alterações');
                    $('#nomePlanoContas').focus();
                    $('html,body').scrollTop(0);
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do Plano de contas não informado!');
        }
    }
    function excluirPlanoContas(codPlanoContas) {
        if (confirm('Deseja realmente excluir PlanoContas?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codPlanoContas': codPlanoContas, 'c': 'GerenciarPlanoContas', 'm': 'excluirPlanoContas', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaPlanoContass();
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
        $('#codPlanoContas').val('');
        $('#codPlanoContasPai').val('');
        $('#nomePlanoContas').val('');
        $('#tipoConta').val('');
        $('#naturezaSaldo').val('');
        $('#codigoContabil').val('');
        $('#descricao').val('');
        $('#aceitaLancamento').val(1);
        $('#getMessage').html('');
        // $('#novoCodPlanoContas').val(0);
        $('#salvarPlanoContas').val('Adicionar');

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
            <h4>Gerenciar PlanoContass:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formPlanoContas" name="formPlanoContas" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Nome do Plano de Contas:</label>
                            <input type="text" id="nomePlanoContas" name="nomePlanoContas" value=""
                                   class="form-control" placeholder="Nome PlanoContas" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="descricao">Descrição:</label>
                            <textarea id="descricao" name="descricao" value=""
                                   class="form-control" placeholder="Descrição" required autofocus></textarea>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="codPlanoContasPai">Cod. Plano de Contas Pai:</label>
                            <select id="codPlanoContasPai" name="codPlanoContasPai" class="form-control">
                                <option value="">Selecione Conta Pai se necessário</option>
                                <?php CarregarOptionsAC::planoContasPai($fws_) ?>
                            </select>
                        </div>
                        <!-- <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="codigo">Código Contabil:</label>
                            <input type="text" id="codigoContabil" name="codigoContabil" value=""
                                   class="form-control" placeholder="Codigo Contábil" required autofocus>
                        </div> -->
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="tipoConta">Tipo da Conta:</label>
                            <select id="tipoConta" name="tipoConta" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="RECEITA">RECEITA</option>
                                <option value="DESPESA">DESPESA</option>
                                <option value="ATIVO">ATIVO</option>
                                <option value="PASSIVO">PASSIVO</option>
                                <option value="PATRIMONIO_LIQUIDO">PATRIMÔNIO LÍQUIDO</option>
                            </select>
                        </div>
                        <!-- <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="naturezaSaldo">Natureza do Saldo:</label>
                            <select id="naturezaSaldo" name="naturezaSaldo" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="CREDITO">CRÉDITO</option>
                                <option value="DEBITO">DÉBITO</option>
                            </select>
                        </div> -->
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="aceitaLancamento">Aceita Lançamento:</label>
                            <select id="aceitaLancamento" name="aceitaLancamento" class="form-control" required>
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
                            <input type="hidden" value="GerenciarPlanoContas" name="c">
                            <input type="hidden" value="salvarPlanoContas"    name="m">
                            
                            <br>&nbsp;
                            <!-- <input type="hidden" name="novoCodPlanoContas" id="novoCodPlanoContas" value="0"/> -->
                            <input type="hidden" name="codPlanoContas"     id="codPlanoContas"     value=""/>                
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
                <b>Lista de PlanoContass Cadastrados:</b>
                <table class="tablesorter" id='itens' border="0">
                    <thead>
                    <th width="10px"></th>
                    <th width="40px">Código Contábil</th>
                    <th width="40px">Nome</th>
                    <th width="40px">Descrição</th>
                    <th width="20px">Tipo de Conta</th>
                    <th width="40px">Natureza do Saldo</th>
                    <th width="40px">Aceita Lançamento</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaPlanoContass($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
