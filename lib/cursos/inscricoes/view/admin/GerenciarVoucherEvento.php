<?php
$moduloIndisponivel = ($objEvConf->getEmitirVoucher()!=1);
if (!$moduloIndisponivel) {
    require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
    FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_VOUCHER, 5, $cod_msg, true);
    $objSRV = new GerenciarVoucherEventoAC();
}
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('table').tablesorter({
//            sortList: [[1, 0]]
        });

        $('#itens tbody tr:odd').css('background', '#F4F4F4');
        $('#itens tbody tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function () {
            salvarVoucherEvento();
        });
        $('#btnSalvarComoNovo').click(function () {
            $('#novoCodVoucherEvento').val(0);
            $('#salvarVoucherEvento').val('Adicionar');
            salvarVoucherEvento();
        });
        $('#btnSalvarAtualizar').click(function () {
            $('#_atualizar_lista').val(1);
            salvarVoucherEvento();
        });
        $('#btnFechar').click(function () {
            limparCampos();
            $('#formVoucherEvento').addClass('hide');
            $('html,body').scrollTop(0);
        });
        /*btnLimpar*/
        $('#btnAdicionarNovo').click(function () {
            $('#formVoucherEvento').removeClass('hide');
            limparCampos();
            $('html,body').scrollTop(0);
        });
        //carregarTabelaVoucherEventos
        $('.editar').click(function () {
            var id = $(this).parents("tr").attr("id");
            editarVoucherEvento(id);
        });
        $('.excluir').click(function () {
            var id = $(this).parents("tr").attr("id");
            excluirVoucherEvento(id);
        });
        $('.desativar').click(function () {
            var id = $(this).parents("tr").attr("id");
            desativarVoucherEvento(id);
        });
 

    });
 
    //funçães globais
    function carregarTabelaVoucherEventos() {
        startLoading();
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarVoucherEvento', 'm': 'carregarTabelaVoucherEventos', 'codVoucherEvento': $('#codVoucherEvento').val(), '_submit_check': '1'}
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
                editarVoucherEvento(id);
            });
            $('.excluir').click(function () {
                var id = $(this).parents("tr").attr("id");
                excluirVoucherEvento(id);
            });
            $('.desativar').click(function () {
                var id = $(this).parents("tr").attr("id");
                desativarVoucherEvento(id);
            });
        }
        );
        stopLoading();
    }
    function salvarVoucherEvento() {
        $('#formVoucherEvento').ajaxSubmit({
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
                    carregarTabelaVoucherEventos();
                    limparCampos();
                    $('#formVoucherEvento').addClass('hide');
                    $('html,body').scrollTop(0);
                }
            }
            , complete: function () {
                stopLoading();
            }
        });
    }
    function editarVoucherEvento(codVoucherEvento) {
        if (codVoucherEvento) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codVoucherEvento': codVoucherEvento, 'c': 'GerenciarVoucherEvento', 'm': 'editarVoucherEvento', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        $('#codVoucherEvento').val(data.codVoucherEvento);
                        $('#nome').val(data.nome);
                        $('#foneContato').val(data.foneContato);
                        $('#patrocinador').val(data.patrocinador);
                        $('#sigla').val(data.sigla);
                        $('#emailContato').val(data.emailContato);
                        $('#dataValidade').val(data.dataValidade);
                        $('#quantidade').val(data.quantidade);
                        $('#valor').val(data.valor);
                        $('#valorInscricao').val(data.valorInscricao);
                        $('#porcentagem').val(data.porcentagem);
                        $('#descricao').val(data.descricao);
                        $('#categoria').val(data.categoria);
                        $('#novoCodVoucherEvento').val(1);
                        $('#formVoucherEvento').removeClass('hide');
                        $('#salvarVoucherEvento').val('Salvar Alterações');
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
    function excluirVoucherEvento(codVoucherEvento) {
        if (confirm('Deseja realmente excluir Vouchers Cód.:' + codVoucherEvento + '?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codVoucherEvento': codVoucherEvento, 'c': 'GerenciarVoucherEvento', 'm': 'excluirVoucherEvento', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaVoucherEventos();
                        limparCampos();
                    }
                }
                , complete: function () {
                    stopLoading();
                }
            });
        }
    }
    function desativarVoucherEvento(codVoucherEvento) {
        if (confirm('Deseja realmente desativar Vouchers Cód.:' + codVoucherEvento + '?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codVoucherEvento': codVoucherEvento, 'c': 'GerenciarVoucherEvento', 'm': 'desativarVoucherEvento', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaVoucherEventos();
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
//                data: ({'codInscrito': codInscrito, 'c': 'GerenciarVoucherEvento', 'm': 'enviarEmailAvisoPadrao', '_submit_check': '1'}),
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
        $('#codVoucherEvento').val('');
        $('#nome').val('');
        $('#sigla').val('');
        $('#patrocinador').val('');
        $('#valor').val('');
        $('#valorInscricao').val('');
        $('#quantidade').val('');
//        $('#cpfCnpj').val('');
        $('#dataValidade').val('');
        $('#valor').val('');
        $('#porcentagem').val('');
        $('#descricao').val('');
        $('#_atualizar_lista').val('');
        $('#categoria').val('');
        $('#novoCodVoucherEvento').val(0);
        $('#salvarVoucherEvento').val('Adicionar');

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
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_inscricoes.php");

if ($moduloIndisponivel) {
    require_once(dirname(__FILE__)."/modulo-indisponivel.php");
    return;
}
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>
                Gerenciar Grupos de Vouchers:
            </h4>             
        </div>
        <div class="panel-body">

            <form  id="formVoucherEvento" name="formVoucherEvento" class="hide" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
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
                        <label for="valorInscricao">Valor Inscrição: (XXXX.00)</label>
                        <input type="text" id="valorInscricao" name="valorInscricao" value=""
                               class="form-control" placeholder="Não acrescente centavos"  required autofocus>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="valor">Valor de Desconto: (XXXX.00)</label>
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
                        <input type="hidden" value="" id="_atualizar_lista" name="_atualizar_lista">
                        <input type="hidden" value="GerenciarVoucherEvento" name="c">
                        <input type="hidden" value="salvarVoucherEvento"    name="m">

                        <input type="hidden" name="novoCodVoucherEvento" id="novoCodVoucherEvento" value="0"/>
                        <input type="hidden" name="codVoucherEvento"     id="codVoucherEvento"     value=""/>                
                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvar" >Salvar</button>
                        &nbsp;&nbsp;&nbsp;                
                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvarAtualizar" >Salvar e Atualizar Lista</button>
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
                    <th width="50px">Qtd Utilizada/Discponível</th>
                    <th width="50px">Data Validade</th>
                    <th width="50px">Desconto</th>
                    <th width="50px">Impresso</th>
                    </thead> 
                    <tbody class="inputs">         
                        <?php
                        $objSRV->carregarTabelaVoucherEventos($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
</div>    
