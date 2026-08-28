<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarIsentoAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
//            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarIsento();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodIsento').val(0);
            $('#salvarIsento').val('Adicionar');
            salvarIsento();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarIsento(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirIsento(id);
            });        
//        carregarTabelaIsentos();
    });
    //funçães globais
    function carregarTabelaIsentos() {
//        alert($('#ordenar').val())
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarIsento', 'm': 'carregarTabelaIsentos', 'ordenar': $('#ordenar').val(), '_submit_check': '1'}
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
                editarIsento(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirIsento(id);
            });
        }
        );
    }
    function salvarIsento() {
        $('#formIsento').ajaxSubmit({
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
                    carregarTabelaIsentos();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarIsento(codIsento) {
        if (codIsento) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codIsento': codIsento, 'c': 'GerenciarIsento', 'm': 'editarIsento', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codIsento').val(data.codIsento);
                    $('#nomeIsento').val(data.nomeIsento);
                    $('#cpf').val(data.cpf);
                    $('#email').val(data.email);
                    $('#organizacao').val(data.organizacao);
                    $('#departamento').val(data.departamento);
                    $('#cpfCnpjAbonador').val(data.cpfCnpjAbonador);
                    $('#novoCodIsento').val(1);
                    $('#salvarIsento').val('Salvar Alterações');
                    $('#cpf').focus();
                    $('html,body').scrollTop(0);
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do isento não informado!');
        }
    }
    function excluirIsento(codIsento) {
        if (confirm('Deseja realmente excluir Isento?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codIsento': codIsento, 'c': 'GerenciarIsento', 'm': 'excluirIsento', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaIsentos();
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
        $('#codIsento').val('');
        $('#nomeIsento').val('');
        $('#cpf').val('');
        $('#email').val('');
        $('#organizacao').val('');
        $('#departamento').val('');
        $('#cpfCnpjAbonador').val('');
        $('#getMessage').html('');
        $('#novoCodIsento').val(0);
        $('#salvarIsento').val('Adicionar');

    }
function associarInscritoPerfilAdministrativo(codInscrito,codPerfil){
    var id_input_perfil= '#associar-perfil-'+codPerfil+'-'+codInscrito;
    var acao = $(id_input_perfil).prop("checked");
    
    if(codInscrito=='' || codPerfil==''){
        alert('Inscrito ou perfil não informado.');
        $(id_input_perfil).attr("checked",'false');
        return false;
    }    

    if(acao==true){
        msgAcao='Deseja associar o inscrito: '+codInscrito+' ao perfil administrativo: '+codPerfil+'?'
    }else{
        msgAcao='Deseja excluir associação entre o inscrito: '+codInscrito+' e o perfil administrativo: '+codPerfil+'?'
    }
    
    if(confirm(msgAcao)){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_perfil':codPerfil, 'acao':acao ,'c':'GerenciarIsento', 'm':'associarInscritoPerfilAdminstrativo'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                elementMsg = '#statusPresenca-'+codPerfil
                processJsonElement(data,elementMsg);
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando-'+codInscrito,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando-'+codInscrito,'hidden');
            }
        });
    }else{
        if(acao==true)
            $(id_input_perfil).prop('checked', false);
        else
            $(id_input_perfil).prop('checked', true);
    }
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
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar Isento:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formIsento" name="formIsento" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="cpf">Cpf:</label>
                            <input type="cpf" id="cpf" name="cpf" value="" maxlength="18"
                                   onkeypress='javascript: return isNumInt(getKey(event));'
                                   onblur="trim(this)"
                                   class="form-control" placeholder="cpf" required autofocus>
                        </div>
                        <div class="form-group col-sm-8" style="padding-left: 0px;">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value=""
                                   class="form-control" placeholder="Email" >
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Nome Isento:</label>
                            <input type="text" id="nomeIsento" name="nomeIsento" value=""
                                   class="form-control" placeholder="Nome Isento" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Orgão que concedeu o benefício:</label>
                            <input type="text" id="organizacao" name="organizacao" value=""
                                   class="form-control" placeholder="Organização que concedeu o benefício" autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Departamento do órgão:</label>
                            <input type="text" id="departamento" name="departamento" value=""
                                   class="form-control" placeholder="Departamento da organização que concedeu o benefício" autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Cpf Cnpj do Órgão Abonador:</label>
                            <input type="text" id="cpfCnpjAbonador" name="cpfCnpjAbonador" value=""
                                   class="form-control" placeholder="Cpj Cnpj do Órgão Abonador" autofocus>
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
                            <input type="hidden" value="GerenciarIsento" name="c">
                            <input type="hidden" value="salvarIsento"    name="m">
                            
                            <br>&nbsp;
                            <input type="hidden" name="novoCodIsento" id="novoCodIsento" value="0"/>
                            <input type="hidden" name="codIsento"     id="codIsento"     value=""/>                
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
                <form  id="filtroIsento" name="filtroIsento" action="<?php echo  WB_URL_VIEW ?>?content=view/admin/GerenciarIsento.php&dummy=" method="post">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="nome">Ordenar listagem por:</label>
                        &nbsp;<input onclick="submit()" type='radio' name='ordenar' id='ordenar' value='cod_isento'  <?php echo verifItemChecked($fws_['ordenar'], 'cod_isento') ?> > Cód.Isento
                        &nbsp;<input onclick="submit()" type='radio' name='ordenar' id='ordenar' value='nome_isento' <?php echo verifItemChecked($fws_['ordenar'], 'nome_isento') ?> >Nome do Isento
                        &nbsp;<input onclick="submit()" type='radio' name='ordenar' id='ordenar' value='cpf' <?php echo verifItemChecked($fws_['ordenar'], 'cpf') ?> >Cpf
                    </div>
                </form>
                <b>Lista de Isentos Cadastrados:</b>
                <table class="tablesorter" id='itens' border="0">
                    <thead>
                    <th width="10px"></th>
                    <th width="100px">Nome</th>
                    <th width="20px">Ja inscrito</th>
                    <th width="20px">Organização</th>
                    <th width="20px">Departamento</th>
                    <th width="20px">Cpf/Cnpj Abonador</th>
                    <th width="450px">Perfis Administrativos / Liberação de Certificados Administrativos</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaIsentos($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
