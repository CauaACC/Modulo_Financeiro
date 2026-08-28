<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarArquivoAtividadeAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({});

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarArquivoAtividade();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodArquivoAtividadeAtividade').val(0);
            $('#salvarArquivo').val('Adicionar');
            salvarArquivoAtividade();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            editarArquivoAtividade(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirArquivoAtividade(id);
        });
        $('.botaoCarga').click(function() {
            $('html,body').scrollTop(0);
            var id = $(this).parents("tr").attr("id");
            //            carregarAtividade(id);
            carregarInscritoAtividade(id);

        });
        $('.botaoAssociar').click(function() {
            $('html,body').scrollTop(0);
            var id = $(this).parents("tr").attr("id");
            associarInscritoAtividade(id);

        });
        //        carregarTabelaArquivos();
    });
    //funçães globais

    function visualizarAtividade(cod) {
        document.getdocumento.cod.value = cod;
        document.getdocumento.submit();
    }

    function carregarAtividade(codArquivoAtividade) {
        if (codArquivoAtividade) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codEvento': $('#codEvento').val(),
                    'codArquivoAtividade': codArquivoAtividade,
                    'c': 'GerenciarArquivoAtividade',
                    'm': 'carregarArquivoAtividade',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJsonSemFade(data);
                    if (data.statusMessage) {
                        carregarTabelaArquivos();
                        limparCampos();
                    }
                },
                complete: function(data) {
                    stopLoading();
                }
            });
        } else {
            alert('Código do arquivo não foi informado!');
        }
    }

    function carregarInscritoAtividade(codArquivoAtividade) {
        if (codArquivoAtividade) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codEvento': $('#codEvento').val(),
                    'codArquivoAtividade': codArquivoAtividade,
                    'c': 'GerenciarArquivoAtividade',
                    'm': 'carregarInscritoAtividade',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJsonSemFade(data);
                    if (data.statusMessage) {
                        carregarTabelaArquivos();
                        limparCampos();
                    }
                },
                complete: function(data) {
                    stopLoading();
                }
            });
        } else {
            alert('Código do arquivo não foi informado!');
        }
    }

    function associarInscritoAtividade(codArquivoAtividade) {
        if (codArquivoAtividade) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codEvento': $('#codEvento').val(),
                    'codArquivoAtividade': codArquivoAtividade,
                    'c': 'GerenciarArquivoAtividade',
                    'm': 'associarInscritoAtividade',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJsonSemFade(data);
                    if (data.statusMessage) {
                        carregarTabelaArquivos();
                        limparCampos();
                    }
                },
                complete: function(data) {
                    stopLoading();
                }
            });
        } else {
            alert('Código do arquivo não foi informado!');
        }
    }

    function carregarTabelaArquivos() {
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarArquivoAtividade',
            'm': 'carregarTabelaArquivos',
            '_submit_check': '1'
        }, function(html) {
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
                editarArquivoAtividade(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirArquivoAtividade(id);
            });
            $('.botaoCarga').click(function() {
                $('html,body').scrollTop(0);
                var id = $(this).parents("tr").attr("id");
                //            carregarAtividade(id);
                carregarInscritoAtividade(id);

            });
            $('.botaoAssociar').click(function() {
                $('html,body').scrollTop(0);
                var id = $(this).parents("tr").attr("id");
                associarInscritoAtividade(id);

            });
        });
    }

    function salvarArquivoAtividade() {
        $('#formArquivo').ajaxSubmit({
            target: '#mensagem',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function(xhr) {
                startLoading();
                var startTime = Date.now();
                xhr.upload.onprogress = function(event) {
                    if (event.lengthComputable) {
                        var percentComplete = (event.loaded / event.total) * 100;
                        document.getElementById('progressBar').style.width = percentComplete + '%';
                        document.getElementById('progressText').innerText = Math.round(percentComplete) + '% enviado';
                        var elapsedTime = (Date.now() - startTime) / 1000; // Tempo decorrido em segundos
                        var estimatedTotalTime = (elapsedTime / percentComplete) * 100; // Tempo total estimado em segundos
                        var timeRemaining = estimatedTotalTime - elapsedTime; // Tempo restante em segundos
                        var minutes = Math.floor(timeRemaining / 60);
                        var seconds = Math.floor(timeRemaining % 60);
                        document.getElementById('timeRemaining').innerText = 'Tempo restante: ' + minutes + 'm ' + seconds + 's';
                    }
                };
                xhr.upload.onloadstart = function() {
                    document.getElementById('progressWrapper').style.display = 'block';
                };
                xhr.upload.onloadend = function() {
                    document.getElementById('progressWrapper').style.display = 'none';
                };        
            },
            timeout: 100000 // Tempo limite de 1 minuto (60000 milissegundos)
                ,
            error: function(ajax) {
                showResponse(ajax);
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaArquivos();
                    limparCampos();
                }
            },
            complete: function(ajax, textStatus) {
                stopLoading();
                if (textStatus === 'timeout') {
                    alert('A requisição expirou. Por favor, tente novamente ou entre em contato com administrador.');
                }
            }
        });
    }

    function editarArquivoAtividade(codArquivoAtividade) {
        if (codArquivoAtividade) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codArquivoAtividade': codArquivoAtividade, 'c': 'GerenciarArquivo', 'm': 'editarArquivo', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codArquivoAtividade').val(data.codArquivoAtividade);
                    $('#tipoAarquivo').val(data.tipoArquivo);
                    $('#novoCodArquivoAtividade').val(1);
                    $('#salvarArquivo').val('Salvar Alterações');
                    $('#arquvo').focus();
                    $('html,body').scrollTop(0);
                },
                complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do desconto não informado!');
        }
    }

    function excluirArquivoAtividade(codArquivoAtividade) {
        if (confirm('Deseja realmente excluir Arquivo?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codArquivoAtividade': codArquivoAtividade,
                    'c': 'GerenciarArquivoAtividade',
                    'm': 'excluirArquivoAtividade',
                    '_submit_check': '1'
                }),
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
                },
                complete: function() {
                    stopLoading();
                }
            });
        }
    }

    function limparCampos() {
        //Limpando campos
        $('#codArquivoAtividade').val('');
        $('#tipoArquivo').val('');
        $('#arquivo').val('');
        $('#novoCodArquivoAtividade').val(0);
        $('#salvarArquivo').val('Adicionar');

    }
    function uploadArquivoComProgresso(form, timeOut, xhr) {
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        var startTime = Date.now();
        xhr.upload.onprogress = function(event) {
            if (event.lengthComputable) {
                var percentComplete = (event.loaded / event.total) * 100;
                document.getElementById('progressBar').style.width = percentComplete + '%';
                document.getElementById('progressText').innerText = Math.round(percentComplete) + '% enviado';
                var elapsedTime = (Date.now() - startTime) / 1000; // Tempo decorrido em segundos
                var estimatedTotalTime = (elapsedTime / percentComplete) * 100; // Tempo total estimado em segundos
                var timeRemaining = estimatedTotalTime - elapsedTime; // Tempo restante em segundos
                var minutes = Math.floor(timeRemaining / 60);
                var seconds = Math.floor(timeRemaining % 60);
                document.getElementById('timeRemaining').innerText = 'Tempo restante: ' + minutes + 'm ' + seconds + 's';
            }
        };
        xhr.onloadstart = function() {
            document.getElementById('progressWrapper').style.display = 'block';
            startLoading();
        };
        xhr.onloadend = function() {
            stopLoading();
            document.getElementById('progressWrapper').style.display = 'none';
        };
        xhr.onerror = function() {
            console.log('Erro na requisição:', xhr);
            showResponse(xhr);
        };
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var data = JSON.parse(xhr.responseText);
                // processJson(data);
                // if (data.statusMessage) {
                //     carregarTabelaArquivos();
                //     limparCampos();
                // }
            } else {
                console.log('Erro na resposta:', xhr);
            }
        };
        xhr.ontimeout = function() {
            alert('A requisição expirou. Por favor, tente novamente.');
        };
        xhr.timeout = timeOut; 
        xhr.send(formData);
    }  
</script>
<!--<script type="text/javascript" src="/<?php echo  WB_PATH ?>/view/js/util/validacoes.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_configuracao.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar Arquivo Atividade:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
                //                $objEv->controlarAcessoProposta('mensagem');
            ?>
                <form id="formArquivo" name="formArquivo" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="descricao">Descrição do link para arquivo:</label>
                            <input type="type" name="descricao" id="descricao" value="" placeholder="Informe descrição para link" size="120" required="">
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="codOficina">Atividade:</label>
                            <select class='form-control' id='codOficina' name='codOficina'>
                                <option value="-1">Selecionar Atividade</option>
                                <?php
                                $fws_['codEvento'] = $objEv->getCodEvento();
                                $fws_['semRestricao'] = true;
                                CarregarOptionsAC::oficinas($fws_, $debug);
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="codPerfil">Perfil:</label>
                            <select class='form-control' id='codPerfil' name='codPerfil'>
                                <option value="-1">Selecionar Perfil</option>
                                <?php
                                $fws_['codEvento'] = $objEv->getCodEvento();
                                $fws_['semRestricao'] = true;
                                CarregarOptionsAC::perfis($fws_, $debug);
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="arquivo">Selecionar Arquivo:</label>
                            <input type="file" name="arquivo" id="arquivo" value="" placeholder="Inserir arquivo" class="btn-xs" required="">
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <div id="carregando" class="shown hidden">
                                <div class="imagem">
                                    <div class="frase">Carregando...</div>
                                    <img src="<?php echo  WB_PATH ?>/resources/image/loading-9.gif" border="0">

                                </div>
                            </div>
                            <div id="progressWrapper" style="display: none;">
                                <div id="progressBar" style="width: 0%; height: 20px; background-color: green;"></div>
                                <div id="progressText"></div>
                                <div id="timeRemaining"></div>
                            </div>

                            <div id="mensagem"></div>
                            <div id="sucesso" class="hidden"></div>
                            <div id="erro" class="hidden"></div>
                            <input type="hidden" value="1" name="_submit_check">
                            <input type="hidden" value="GerenciarArquivoAtividade" name="c">
                            <input type="hidden" value="salvarArquivoAtividade" name="m">
                            <input type="hidden" value="ARQUIVO ATIVIDADE" id='tipoArquivo' name='tipoArquivo'>
                            <input type="hidden" name="codEvento" id="codEvento" value="<?php echo COD_EVENTO ?>">

                            <input type="hidden" name="novoCodArquivoAtividade" id="novoCodArquivoAtividade" value="0" />
                            <input type="hidden" name="codArquivoAtividade" id="codArquivoAtividade" value="" />
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar">Enviar Arquivo</button>
                            <!--                            &nbsp;&nbsp;&nbsp;                
                                                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvarComoNovo" >Salvar como novo registro</button>-->
                            &nbsp;&nbsp;&nbsp;
                            <button class="btn btn-xs btn-primary" type="button" id="btnLimpar">Limpar e sair</button>
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
                        <th width="30px"></th>
                        <th width="250px">Descrição do link</th>
                        <th width="50px">Arquivo gerado</th>
                        <th width="250px">Arquvo enviado</th>
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
<form name="getdocumento" method="post" action="<?php echo WB_URL_COMMAND ?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarArquivoAtividade">
    <input type="hidden" name="m" value="abrirArquivoAtividade">
    <input type="hidden" value="" name="cod">
</form>