<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_CERTIFICADO, 5, $cod_msg, true);
$objSRV = new GerenciarCertificadoEventoAC();
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('table').tablesorter({
//            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function () {
            salvarCertificadoEvento();
        });
        $('#btnSalvarComoNovo').click(function () {
            $('#novoCodCertificadoEvento').val(0);
            $('#salvarCertificadoEvento').val('Adicionar');
            salvarCertificadoEvento();
        });
        $('#btnFechar').click(function () {
            limparCampos();
            $('#formCertificadoEvento').addClass('hide');
            $('html,body').scrollTop(0);
        });
        /*btnLimpar*/
        $('#btnAdicionarNovo').click(function () {
            $('#formCertificadoEvento').removeClass('hide');
            limparCampos();
            $('html,body').scrollTop(0);
        });
        //carregarTabelaCertificadoEventos
        $('.editar').click(function () {
            var id = $(this).parents("tr").attr("id");
            editarCertificadoEvento(id);
        });
        $('.excluir').click(function () {
            var id = $(this).parents("tr").attr("id");
            excluirCertificadoEvento(id);
        });
        $('.desativar').click(function () {
            var id = $(this).parents("tr").attr("id");
            desativarCertificadoEvento(id);
        });
 

    });
 
    //funçães globais
    function carregarTabelaCertificadoEventos() {
        startLoading();
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarCertificadoEvento', 'm': 'carregarTabelaCertificadoEventos', 'codCertificadoEvento': $('#codCertificadoEvento').val(), '_submit_check': '1'}
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
                editarCertificadoEvento(id);
            });
            $('.excluir').click(function () {
                var id = $(this).parents("tr").attr("id");
                excluirCertificadoEvento(id);
            });
            $('.desativar').click(function () {
                var id = $(this).parents("tr").attr("id");
                desativarCertificadoEvento(id);
            });
        }
        );
        stopLoading();
    }
    function salvarCertificadoEvento() {
        $('#formCertificadoEvento').ajaxSubmit({
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
                    carregarTabelaCertificadoEventos();
                    limparCampos();
                    $('#formCertificadoEvento').addClass('hide');
                    $('html,body').scrollTop(0);
                }
            }
            , complete: function () {
                stopLoading();
            }
        });
    }
    function editarCertificadoEvento(codCertificadoEvento) {
        if (codCertificadoEvento) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codCertificadoEvento': codCertificadoEvento, 'c': 'GerenciarCertificadoEvento', 'm': 'editarCertificadoEvento', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        $('#codCertificadoEvento').val(data.codCertificadoEvento);
                        $('#nome').val(data.nome);
                        $('#foneContato').val(data.foneContato);
                        $('#patrocinador').val(data.patrocinador);
                        $('#sigla').val(data.sigla);
                        $('#emailContato').val(data.emailContato);
                        $('#dataValidade').val(data.dataValidade);
                        $('#quantidade').val(data.quantidade);
                        $('#valor').val(data.valor);
                        $('#porcentagem').val(data.porcentagem);
                        $('#descricao').val(data.descricao);
                        $('#categoria').val(data.categoria);
                        $('#novoCodCertificadoEvento').val(1);
                        $('#formCertificadoEvento').removeClass('hide');
                        $('#salvarCertificadoEvento').val('Salvar Alterações');
                        $('#nome').focus();
                        $('html,body').scrollTop(0);
                    } else {
                        alert('Problema ao carregar dados');
                    }
                }
                , complete: function () {
                    stopLoading();
                }
            });
        } else {
            alert('Código de Vouchers não informado!');
        }
    }
    function excluirCertificadoEvento(codCertificadoEvento) {
        if (confirm('Deseja realmente excluir Vouchers Cód.:' + codCertificadoEvento + '?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codCertificadoEvento': codCertificadoEvento, 'c': 'GerenciarCertificadoEvento', 'm': 'excluirCertificadoEvento', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaCertificadoEventos();
                        limparCampos();
                    }
                }
                , complete: function () {
                    stopLoading();
                }
            });
        }
    }
    function desativarCertificadoEvento(codCertificadoEvento) {
        if (confirm('Deseja realmente desativar Vouchers Cód.:' + codCertificadoEvento + '?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codCertificadoEvento': codCertificadoEvento, 'c': 'GerenciarCertificadoEvento', 'm': 'desativarCertificadoEvento', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaCertificadoEventos();
                        limparCampos();
                    }
                }
                , complete: function () {
                    stopLoading();
                }
            });
        }
    }
//    function enviarEmailAvisoPadrao(codInscrito) {
//        if (confirm('Deseja enviar e-mail de aviso de boleto com texto padrão?')) {
//            $.ajax({
//                type: "POST",
//                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
//                data: ({'codInscrito': codInscrito, 'c': 'GerenciarCertificadoEvento', 'm': 'enviarEmailAvisoPadrao', '_submit_check': '1'}),
//                dataType: "json",
//                async: false,
//                cache: false,
//                beforeSend: function () {
//                    startLoading();
//                },
//                success: function (data) {
//                    processJson(data);
//                }
//                , complete: function () {
//                    stopLoading();
//                }
//            });
//        }
//    }
    function limparCampos() {
        //Limpando campos
        $('#codCertificadoEvento').val('');
        $('#nome').val('');
        $('#sigla').val('');
        $('#patrocinador').val('');
        $('#valor').val('');
        $('#quantidade').val('');
//        $('#cpfCnpj').val('');
        $('#dataValidade').val('');
        $('#valor').val('');
        $('#porcentagem').val('');
        $('#descricao').val('');
        $('#categoria').val('');
        $('#novoCodCertificadoEvento').val(0);
        $('#salvarCertificadoEvento').val('Adicionar');

    }
</script>
<!--<script type="text/javascript" src="/<?php echo  WB_PATH ?>/view/js/util/validacoes.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<!--<script type="text/javascript" src="resources/js/jquery-ui-timepicker-addon.js"></script>-->
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_outros.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>
                Gerenciar Certificados:
            </h4>             
        </div>
        <div class="panel-body">

            <form  id="formCertificadoEvento" name="formCertificadoEvento" class="hide" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="form-group col-sm-12" style="padding-left: 0px;">

                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="nome">Nome do Responsável:</label>
                        <input type="text" id="nome" name="nome" value=""
                               class="form-control" placeholder="nome" required autofocus>
                    </div>

                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="foneContato">Fone Contato:</label>
                        <input type="text" id="foneContato" name="foneContato" value=""
                               class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);' required autofocus>
                    </div>
                    
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="patrocinador">Patrocinador:</label>
                        <input type="text" id="patrocinador" name="patrocinador" value=""
                               class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);' required autofocus>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="sigla">Sigla-Patrocinador:</label>
                        <input type="text" id="sigla" name="sigla" value=""
                               class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);' required autofocus>
                    </div>
                    
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="emailContato">E-mail Contato:</label>
                        <input type="text" id="emailContato" name="emailContato" value=""
                               class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);' required autofocus>
                    </div>
                    
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="dataValidade">Data de Validade:</label>
                        <input type="date" id="dataValidade" name="dataValidade" value="" style="line-height: 15px;"
                               class="form-control" placeholder="Data de Validade"  required autofocus>
                    </div>
                    
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="quantidade">Quantidade:</label>
                        <input type="text" id="quantidade" name="quantidade" value="" style="line-height: 15px;"
                               class="form-control" placeholder="Quantidade"  required autofocus>
                    </div>

                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="valor">Valor Desconto: (XXXX.00)</label>
                        <input type="text" id="valor" name="valor" value=""
                               class="form-control" placeholder="Não acrescente centavos"  required autofocus>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="porcentagem">ou Porcentagem de Desconto: (De 0 a 100)</label>
                        <input type="text" id="porcentagem" name="porcentagem" value=""
                               class="form-control" placeholder="Não acrescente centavos"  required autofocus>
                    </div>

                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="descricao">Descrição: (VALE 01 INSCRIÇÃO<br>CATEGORIA OURO)</label>
                        <input type="text" id="descricao" name="descricao" value=""
                               class="form-control" placeholder="Descrição" required autofocus>
                    </div>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="categoria">Categoria:</label>
                        <input type="text" id="categoria" name="categoria" value=""
                               class="form-control" placeholder="Categoria" required autofocus>
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
                        <input type="hidden" value="GerenciarCertificadoEvento" name="c">
                        <input type="hidden" value="salvarCertificadoEvento"    name="m">

                        <input type="hidden" name="novoCodCertificadoEvento" id="novoCodCertificadoEvento" value="0"/>
                        <input type="hidden" name="codCertificadoEvento"     id="codCertificadoEvento"     value=""/>                
                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvar" >Salvar</button>
                        &nbsp;&nbsp;&nbsp;                
                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvarComoNovo" >Salvar como novo registro</button>
                        &nbsp;&nbsp;&nbsp;
                        <button class="btn btn-xs btn-primary" type="button" id="btnFechar" >Fechar </button>
                    </div>
                </div>  
            </form>
            <button class="btn btn-xs btn-primary" type="button" id="btnAdicionarNovo" >Novo Grupo de Voucher </button>

            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <b>Lista de Grupos de Vouchers Cadastrados:</b>
                <table id="itens" class="tablesorter" border="0">
                    <thead>
                    <th width="10px"></th>
                    <th width="250px">Contato</th>
                    <th width="250px">Descrição</th>
                    <th width="50px">Sigla</th>
                    <th width="50px">Quantidade</th>
                    <th width="50px">Data Validade</th>
                    <th width="50px">Desconto</th>
                    <th width="50px">Impresso</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaCertificadoEventos($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
</div>    