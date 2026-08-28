<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarRetornoHutecUploadAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({});

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
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
//            carregarRetornoHutec(id);
            carregarRetornoHutec(id);

        }); 
//        carregarTabelaArquivos();
    });
    //funçães globais
    function carregarRetornoHutec(codArquivo){
        if(codArquivo){
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codEvento': $('#codEvento').val(),'codArquivo': codArquivo,'c':'GerenciarRetornoHutecUpload', 'm':'carregarArquivoRetornoHutec','_submit_check':'1'}),
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
    function carregarRetornoHutec(codArquivo){
        if(codArquivo){
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codEvento': $('#codEvento').val(),'codArquivo': codArquivo,'c':'GerenciarRetornoHutecUpload', 'm':'carregarRetornoHutec','_submit_check':'1'}),
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
                , {'c': 'GerenciarRetornoHutecUpload', 'm': 'carregarTabelaArquivos', '_submit_check': '1'}
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
    //            carregarRetornoHutec(id);
                carregarRetornoHutec(id);

            }); 
        }
        );
    }
    // ---- Carga automatica com modal de progresso (Enviar -> Carregar -> Concluir) ----
    // Esta tela recarrega o proprio jQuery (googleapis) por cima do central, o que derruba o
    // plugin .modal() do compat E a CSS de modal do Bootstrap nao se aplica de forma confiavel.
    // Por isso o "modal" e um overlay autocontido com estilo inline (nao depende de BS).
    function _modalCarga(acao) {
        var el = document.getElementById('modalCargaProgresso');
        if (!el) { return; }
        el.style.display = (acao === 'show') ? 'flex' : 'none';
    }
    function abrirModalCarga() {
        $('#cargaConfirm').hide();
        $('#cargaSteps').show();
        $('#btnSubstituir').hide();
        $('#btnCancelarCarga').hide();
        $('#cargaMsg').hide().removeClass('alert-info alert-danger alert-success').html('');
        $('#btnFecharCarga').hide();
        setStep('enviar', 'ativa');
        setStep('carregar', 'pendente');
        setStep('concluir', 'pendente');
        _modalCarga('show');
    }
    function setStep(step, estado) {
        var $li = $('#step_' + step);
        var ic  = estado === 'ativa' ? '&#9203;' : (estado === 'ok' ? '&#10004;' : (estado === 'erro' ? '&#10006;' : '&#9675;'));
        var cor = estado === 'ok' ? '#3c763d' : (estado === 'erro' ? '#a94442' : (estado === 'ativa' ? '#31708f' : '#999'));
        $li.find('.step-ic').html(ic);
        $li.css({'color': cor, 'font-weight': estado === 'ativa' ? 'bold' : 'normal'});
    }
    function mostrarMsgCarga(tipo, texto) {
        var cores = {success: ['#dff0d8', '#3c763d'], danger: ['#f2dede', '#a94442'], info: ['#d9edf7', '#31708f']};
        var c = cores[tipo] || cores.info;
        var m = document.getElementById('cargaMsg');
        m.style.background = c[0]; m.style.color = c[1];
        m.innerHTML = texto; m.style.display = 'block';
    }
    function fecharModalCarga() {
        _modalCarga('hide');
    }
    function salvarArquivo() {
        // Antes de subir, verifica se ja existe carga no evento -> pergunta se deseja substituir
        $.ajax({
            type: "POST"
            , url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
            , data: ({'codEvento': $('#codEvento').val(), 'c': 'GerenciarRetornoHutecUpload', 'm': 'verificarCargaExistente', '_submit_check': '1'})
            , dataType: "json"
            , cache: false
            , success: function(d) {
                if (d && d.existe == '1') {
                    mostrarConfirmSubstituir(d.qtd);
                } else {
                    executarUploadECarga();
                }
            }
            , error: function() { executarUploadECarga(); }
        });
    }
    function mostrarConfirmSubstituir(qtd) {
        $('#cargaSteps').hide();
        $('#cargaMsg').hide();
        $('#btnFecharCarga').hide();
        $('#cargaConfirm').show();
        $('#cargaConfirmMsg').html('Já existe uma carga de retorno neste evento (<b>' + qtd + '</b> arquivo(s)). '
            + 'Como o código do pagamento não se repete, o recomendado é <b>substituir</b>: a carga anterior é apagada e só a nova passa a valer.');
        $('#btnSubstituir').show();
        $('#btnCancelarCarga').show();
        _modalCarga('show');
    }
    function confirmarSubstituir() {
        $('#btnSubstituir').hide();
        $('#btnCancelarCarga').hide();
        $('#cargaConfirmMsg').html('Removendo a carga anterior...');
        $.ajax({
            type: "POST"
            , url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
            , data: ({'codEvento': $('#codEvento').val(), 'c': 'GerenciarRetornoHutecUpload', 'm': 'substituirCargaEvento', '_submit_check': '1'})
            , dataType: "json"
            , cache: false
            , success: function() {
                carregarTabelaArquivos();
                executarUploadECarga();
            }
            , error: function() {
                $('#cargaConfirm').hide();
                $('#cargaSteps').show();
                setStep('enviar', 'erro');
                mostrarMsgCarga('danger', 'Falha ao remover a carga anterior. Tente novamente.');
                $('#btnFecharCarga').show();
            }
        });
    }
    function executarUploadECarga() {
        abrirModalCarga();
        $('#formArquivo').ajaxSubmit({
            dataType: 'json'
            , cache: false
            , error: function() {
                setStep('enviar', 'erro');
                mostrarMsgCarga('danger', 'Falha ao enviar o arquivo. Verifique o arquivo e tente novamente.');
                $('#btnFecharCarga').show();
            }
            , success: function(data) {
                if (data && data.statusMessage) {
                    setStep('enviar', 'ok');
                    carregarTabelaArquivos();
                    setStep('carregar', 'ativa');
                    dispararCargaAuto(data.codArquivo);
                } else {
                    setStep('enviar', 'erro');
                    mostrarMsgCarga('danger', (data && data.message) ? data.message : 'Não foi possível enviar o arquivo.');
                    $('#btnFecharCarga').show();
                }
            }
        });
    }
    function dispararCargaAuto(codArquivo) {
        if (!codArquivo) {
            setStep('carregar', 'erro');
            mostrarMsgCarga('danger', 'Arquivo enviado, mas não foi possível identificá-lo para a carga automática. Use o botão "Carregar" na lista.');
            $('#btnFecharCarga').show();
            return;
        }
        $.ajax({
            type: "POST"
            , url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
            , data: ({'codEvento': $('#codEvento').val(), 'codArquivo': codArquivo, 'c': 'GerenciarRetornoHutecUpload', 'm': 'carregarRetornoHutec', '_submit_check': '1'})
            , dataType: "json"
            , cache: false
            , success: function(data) {
                var msg = (data && data.message) ? data.message : '';
                var n = (msg.match(/gravado com sucesso/g) || []).length;
                var falhou = /n[ãa]o carregado/i.test(msg);
                setStep('carregar', 'ok');
                if (falhou && n === 0) {
                    setStep('concluir', 'erro');
                    mostrarMsgCarga('danger', 'A carga não gravou nenhum registro. Verifique o arquivo enviado.');
                } else {
                    setStep('concluir', 'ok');
                    mostrarMsgCarga('success', n > 0 ? (n + ' registro(s) carregado(s) com sucesso.') : 'Carga concluída.');
                }
                carregarTabelaArquivos();
                limparCampos();
            }
            , error: function() {
                setStep('carregar', 'erro');
                mostrarMsgCarga('danger', 'Falha ao carregar os registros. Use o botão "Carregar" na lista para tentar novamente.');
            }
            , complete: function() {
                $('#btnFecharCarga').show();
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
                data: ({'codArquivo': codArquivo, 'c': 'GerenciarRetornoHutecUpload', 'm': 'excluirArquivo', '_submit_check': '1'}),
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
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar Arquivo RetornoHutec:</h4>
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
                            <label for="tipoArquivo">Origem RetornoHutec:</label>
                            <select class='form-control' id='tipoArquivo' name='tipoArquivo'>
                                <option value='RETORNOHUTEC'>Retorno HUTEC</option>
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
                            <input type="hidden" value="GerenciarRetornoHutecUpload" name="c">
                            <input type="hidden" value="salvarRetornoHutecUpload"    name="m">
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
                <div style="margin: 6px 0 16px 0; padding: 12px 14px; background: #eaf3fb; border: 1px solid #bcdff1; border-left: 4px solid #31708f; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <span style="color:#31708f; font-size:13px; line-height:1.5;">
                        <b><i class="fa-solid fa-scale-balanced"></i> Conferência da carga</b><br>
                        Compara o <b>Balancete do HUTEC</b> (enviado aqui) com os <b>pagamentos do nosso sistema</b> e aponta as divergências.
                    </span>
                    <a class="btn btn-sm btn-info" target="_blank"
                       href="index.php?content=view/admin/GerenciarRetornoHutec.php"
                       title="Abre a conferência entre o balancete do HUTEC e os pagamentos carregados no sistema">
                        <i class="fa-solid fa-scale-balanced"></i> Conferir Balancete HUTEC &times; Sistema
                    </a>
                </div>
                <b>Lista de Arquivos Cadastrados:</b>
                <table class="tablesorter" id="itens" border="0">
                    <thead>
                    <th width="10px"></th>
                    <th width="50px">Status<br>Carga</th>
                    <th width="50px">Origem</th>
                    <th width="250px">Arquivo gerado</th>
                    <th width="250px">Arquvo enviado</th>
                    <th width="250px">Data inclusão</th>
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

<!-- Overlay de progresso da carga (autocontido, sem dependencia de Bootstrap modal) -->
<div id="modalCargaProgresso" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:8px;max-width:430px;width:90%;box-shadow:0 4px 22px rgba(0,0,0,0.35);overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid #eee;font-size:17px;font-weight:bold;color:#333;">
            Carga do Retorno HUTEC
        </div>
        <div style="padding:18px;">
            <div id="cargaConfirm" style="display:none;">
                <p id="cargaConfirmMsg" style="font-size:14px;color:#333;margin:0;line-height:1.5;"></p>
            </div>
            <ul id="cargaSteps" style="list-style:none;padding-left:0;margin:0 0 10px 0;font-size:15px;">
                <li id="step_enviar"   style="padding:5px 0;color:#999;"><span class="step-ic">&#9675;</span> &nbsp;Enviando arquivo</li>
                <li id="step_carregar" style="padding:5px 0;color:#999;"><span class="step-ic">&#9675;</span> &nbsp;Carregando registros</li>
                <li id="step_concluir" style="padding:5px 0;color:#999;"><span class="step-ic">&#9675;</span> &nbsp;Concluído</li>
            </ul>
            <div id="cargaMsg" style="display:none;padding:10px 12px;border-radius:4px;font-size:14px;"></div>
        </div>
        <div style="padding:12px 18px;border-top:1px solid #eee;text-align:right;">
            <button type="button" id="btnCancelarCarga" style="display:none;padding:7px 16px;border:1px solid #ccc;background:#f5f5f5;border-radius:4px;cursor:pointer;margin-right:6px;" onclick="fecharModalCarga()">Cancelar</button>
            <button type="button" id="btnSubstituir" style="display:none;padding:7px 18px;border:0;background:#5cb85c;color:#fff;border-radius:4px;cursor:pointer;" onclick="confirmarSubstituir()">Substituir e carregar</button>
            <button type="button" id="btnFecharCarga" style="display:none;padding:7px 18px;border:1px solid #ccc;background:#f5f5f5;border-radius:4px;cursor:pointer;" onclick="fecharModalCarga()">Fechar</button>
        </div>
    </div>
</div>

