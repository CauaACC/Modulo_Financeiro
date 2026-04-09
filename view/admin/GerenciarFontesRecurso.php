<?php
require_once(dirname(__FILE__)."/../../include/config.php");

@$cod_msg = false;
if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

$objSRV = new GerenciarFontesRecursoAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
//            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarFontesRecurso();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodFontesRecurso').val(0);
            $('#salvarFontesRecurso').val('Adicionar');
            salvarFontesRecurso();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarFontesRecurso(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirFontesRecurso(id);
            });        
//        carregarTabelaFontesRecursos();
    });
    //funçães globais
    function carregarTabelaFontesRecursos() {
//        alert($('#ordenar').val())
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarFontesRecurso', 'm': 'carregarTabelaFontesRecursos', 'ordenar': $('#ordenar').val(), '_submit_check': '1'}
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
                editarFontesRecurso(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirFontesRecurso(id);
            });
        }
        );
    }
    function salvarFontesRecurso() {
        $('#formFontesRecurso').ajaxSubmit({
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
                    carregarTabelaFontesRecursos();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarFontesRecurso(codFontesRecurso) {
        if (codFontesRecurso) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codFontesRecurso': codFontesRecurso, 'c': 'GerenciarFontesRecurso', 'm': 'editarFontesRecurso', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codFontesRecurso').val(data.codFontesRecurso);
                    $('#nomeFontesRecurso').val(data.nomeFontesRecurso);
                    $('#tipoFontesRecurso').val(data.tipoFontesRecurso);
                    $('#descricao').val(data.descricao);
                    $('#status').val(data.status);
                    $('#salvarFontesRecurso').val('Salvar Alterações');
                    $('#nomeFontesRecurso').focus();
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
    function excluirFontesRecurso(codFontesRecurso) {
        if (confirm('Deseja realmente excluir FontesRecurso?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codFontesRecurso': codFontesRecurso, 'c': 'GerenciarFontesRecurso', 'm': 'excluirFontesRecurso', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaFontesRecursos();
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
        $('#codFontesRecurso').val('');
        $('#nomeFontesRecurso').val('');
        $('#status').val('');
        $('#tipoFontesRecurso').val('');
        $('#descricao').val('');
        $('#getMessage').html('');
        // $('#novoCodFontesRecurso').val(0);
        $('#salvarFontesRecurso').val('Adicionar');

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
            <h4>Gerenciar FontesRecursos:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formFontesRecurso" name="formFontesRecurso" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Nome do Plano de Contas:</label>
                            <input type="text" id="nomeFontesRecurso" name="nomeFontesRecurso" value=""
                                   class="form-control" placeholder="Nome FontesRecurso" required autofocus>
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
                                <option value="ativada">ATIVADA</option>
                                <option value="desativada">DESATIVADA</option>
                            </select>
                        </div> -->
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="tipoFontesRecurso">Tipo:</label>
                            <select id="tipoFontesRecurso" name="tipoFontesRecurso" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="INTERNA">INTERNA</option>
                                <option value="EXTERNA">EXTERNA</option>
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
                            <input type="hidden" value="GerenciarFontesRecurso" name="c">
                            <input type="hidden" value="salvarFontesRecurso"    name="m">
                            
                            <br>&nbsp;
                            <!-- <input type="hidden" name="novoCodFontesRecurso" id="novoCodFontesRecurso" value="0"/> -->
                            <input type="hidden" name="codFontesRecurso"     id="codFontesRecurso"     value=""/>                
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
                <b>Lista de FontesRecursos Cadastrados:</b>
                <table class="tablesorter" id='itens' border="0">
                    <thead>
                    <th width="10px"></th>
                    <th width="40px">Nome</th>
                    <th width="40px">Tipo</th>
                    <th width="100px">Descrição</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaFontesRecursos($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
