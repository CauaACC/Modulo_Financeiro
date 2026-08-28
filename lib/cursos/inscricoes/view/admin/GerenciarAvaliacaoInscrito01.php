<?php

use Mpdf\Tag\Ins;

require_once(dirname(__FILE__) . "/../../view/admin/consulta-avaliacao-inscrito-seguranca.php");
$objSRV = new GerenciarAvaliacaoInscritoAC();
$fws_['codInscrito'] = $fwsGet_['codInscrito'];

$objAI = $objSRV->carregarAvaliacaoInscritoByIdInscrito($fws_, $debug);

?>

<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
            //            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarAvaliacaoInscrito();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodAvaliacao').val(0);
            $('#salvarAvaliacaoInscrito').val('Adicionar');
            salvarAvaliacaoInscrito();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            editarAvaliacaoInscrito(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirAvaliacaoInscrito(id);
        });

        $('#pontosInformadosTrabalho', '#sitPontos, #pontosTotalConfirmado, #codInscrito').on('input', function() {
            var value = $(this).val();
            $(this).val(value.replace(/[^0-9]/g, ''));
        });
        $('#nomeInscrito').on('input', function() {
            var value = $(this).val();
            $(this).val(value.replace(/[^a-zA-Z]/g, ''));
        });
        $('#classificacao').on('input', function() {
            var value = $(this).val();
            value = value.replace(/[^0-9Nn]/g, '').toUpperCase();
            if (value.indexOf('N') !== value.lastIndexOf('N')) {
                value = value.substring(0, value.lastIndexOf('N')) + value.substring(value.lastIndexOf('N') + 1);
            }
            $(this).val(value);
        });
        // Desabilitar a edição dos campos e adicionar estilo de cursor proibido
        // $('#nomeInscrito,#pontosInformadosTrabalho, #sitPontos, #pontosTotalConfirmado').prop('readonly', true).css('background-color', '#f2f2f2').css('cursor', 'not-allowed');
        $('#nomeInscrito,#pontosInformadosTrabalho, #sitPontos').prop('readonly', true).css('background-color', '#f2f2f2').css('cursor', 'not-allowed');

        $('#codInscrito').on('keypress blur', function(e) {
            if (e.which === 13) {
                editarAvaliacaoInscritoByIdInscrito($(this).val());
                $('html,body').scrollTop(0);
                $('#pontosTotalConfirmado').focus();
            }
        });
        $('#pontosTotalConfirmado').on('keypress', function(e) {
            if (e.which === 13) {
                $('#classificacaoStatus').focus();
            }
        });
        $('#classificacaoStatus').on('keypress', function(e) {
            if (e.which === 13) {
                $('#classificacao').focus();
            }
        });
        $('#classificacao').on('keypress', function(e) {
            if (e.which === 13) {
                $('#btnSalvar').focus();
            }
        });
    });
    function acessarAvaliacaoInscrito(codInscrito) {
        window.open('<?php echo WB_URL_DOMINIO.'/'.WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index.php?content=view/admin/GerenciarAvaliacaoInscrito.php&codInscrito='+codInscrito, '_blank');
    }
    function excluirAvaliacaoInscrito(codAvaliacao) {
        if (confirm('Deseja realmente excluir Avaliação Inscrito?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codAvaliacao': codAvaliacao,
                    'c': 'GerenciarAvaliacaoInscrito',
                    'm': 'excluirAvaliacaoInscrito',
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
                        carregarTabelaAvaliacaoInscrito();
                    }
                    limparCampos();
                },
                complete: function() {
                    stopLoading();
                }
            });
        }
    }
    //funçães globais
    function carregarTabelaAvaliacaoInscrito() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarAvaliacaoInscrito',
            'm': 'carregarTabelaAvaliacaoInscrito',
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
                editarAvaliacaoInscrito(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirAvaliacaoInscrito(id);
            });
        });
    }

    function salvarAvaliacaoInscrito() {
        if ($('#codAvaliacao').val() != '') {
            if (!confirm('Já estava avaliado, Deseja ATUALIZAR a Avaliação do Inscrito?')) {
                return false;
            }
        }
        $('#formAvaliacaoInscrito').ajaxSubmit({
            target: '#mensagem',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            timeout: 4000,
            error: function(ajax) {
                showResponse(ajax);
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaAvaliacaoInscrito();
                    limparCampos();

                    $('#codInscrito').focus();
                    $('html,body').scrollTop(0);
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function editarAvaliacaoInscrito(codAvaliacao) {
        if (codAvaliacao) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codAvaliacao': codAvaliacao,
                    'c': 'GerenciarAvaliacaoInscrito',
                    'm': 'editarAvaliacaoInscrito',
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
                    //Setando valores para alteração
                    $('#codInscrito').val(data.codInscrito);
                    $('#nomeInscrito').val(data.nomeInscrito);
                    $('#pontosInformadosTrabalho').val(data.pontosInformadosTrabalho);
                    $('#sitPontos').val(data.sitPontos);
                    $('#pontosTotalConfirmado').val(data.pontosTotalConfirmado);
                    $('#pontosTotalAposRecurso').val(data.pontosTotalAposRecurso);
                    $('#classificacao').val(data.classificacao);
                    $('#classificacaoStatus').val(data.classificacaoStatus);
                    $('#recursoTexto').val(data.recursoTexto);
                    $('#recursoParecer').val(data.recursoParecer);
                    $('#parecerAvaliador').val(data.parecerAvaliador);  
                    $('#recursoStatus').val(data.recursoStatus);  
                    $('#novoCodAvaliacao').val(1);
                    $('#codAvaliacao').val(data.codAvaliacao);
                    $('#arquivoGerado').html(data.arquivoGerado);
                    $('#salvarAvaliacaoInscrito').val('Salvar Alterações');

                    $('#pontosTotalConfirmado').focus();
                    $('html,body').scrollTop(0);
                },
                complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do avaliação não informado!');
        }
    }

    function editarAvaliacaoInscritoByIdInscrito(codInscrito) {
        if (codInscrito) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codInscrito': codInscrito,
                    'c': 'GerenciarAvaliacaoInscrito',
                    'm': 'editarAvaliacaoInscritoByIdInscrito',
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
                    //Setando valores para alteração
                    $('#codInscrito').val(data.codInscrito);
                    $('#nomeInscrito').val(data.nomeInscrito);
                    $('#pontosInformadosTrabalho').val(data.pontosInformadosTrabalho);
                    $('#sitPontos').val(data.sitPontos);
                    $('#pontosTotalConfirmado').val(data.pontosTotalConfirmado);
                    $('#pontosTotalAposRecurso').val(data.pontosTotalAposRecurso);
                    $('#classificacao').val(data.classificacao);
                    $('#classificacaoStatus').val(data.classificacaoStatus);
                    $('#recursoTexto').val(reverterTratarJson(data.recursoTexto));
                    $('#recursoParecer').val(reverterTratarJson(data.recursoParecer));
                    $('#parecerAvaliador').val(data.parecerAvaliador); 
                    $('#recursoStatus').val(data.recursoStatus);  
                    $('#codAvaliacao').val(data.codAvaliacao);
                    $('#novoCodAvaliacao').val(1);
                    $('#salvarAvaliacaoInscrito').val('Salvar Alterações');

                    $('#pontosTotalConfirmado').focus();
                    $('html,body').scrollTop(0);
                },
                complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do avaliação não informado!');
        }
    }

    function limparCampos() {
        //Limpando campos
        $('#codInscrito').val('');
        $('#nomeInscrito').val('');
        $('#sitPontos').val('');
        $('#pontosInformadosTrabalho').val('');
        $('#pontosTotalConfirmado').val('');
        $('#pontosTotalAposRecurso').val('');
        $('#classificacao').val('');
        $('#classificacaoStatus').val('');
        $('#recursoTexto').val('');
        $('#recursoParecer').val('');
        $('#parecerAvaliador').val(); 
        $('#recursoStatus').val('');    
        $('#novoCodAvaliacao').val(0);
        $('#codAvaliacao').val('');
        $('#salvarAvaliacaoInscrito').val('Adicionar');
    }

    function contarMaximoCaracter(txarea, limite, idDigitado, idRestante) {
        const total = limite;
        const tam = txarea.value.length;
        const digitado = document.getElementById(idDigitado);
        const restante = document.getElementById(idRestante);
        if (digitado && restante) {
            digitado.innerHTML = tam;
            restante.innerHTML = total - tam;
            if (tam > total) {
                alert(`Seu resumo atingiu o limite de ${limite} caracteres`);
                txarea.value = txarea.value.substring(0, total);
                digitado.innerHTML = total;
                restante.innerHTML = 0;
            }
        } else {
            console.error('Os IDs fornecidos para digitado e restante não foram encontrados no DOM.');
        }
    }
</script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_inscricoes.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar Avaliação Inscrito:</h4>
        </div>
        <div class="panel-body">
            <?php
            try {
            ?>
                <form id="formAvaliacaoInscrito" name="formAvaliacaoInscrito" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">  
                            <?php
                                $objSRV->verificarContratos($fws_, $objAI, $debug);
                            ?>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="codInscrito" style="font-size: 1.2em;">Código Inscrito :</label>
                            <input type="text" id="codInscrito" name="codInscrito" value="<?php echo $objAI->getCodInscrito() ?>" maxlength="14" onkeypress='javascript: return isNumInt(getKey(event));' class="form-control" placeholder="Código Inscrito" required autofocus style="font-size: 1.1em;">
                        </div>
                        <div class="form-group col-sm-9" style="padding-left: 0px;">
                            <label for="nome" style="font-size: 1.2em;">Nome :</label>
                            <input type="text" id="nomeInscrito" name="nomeInscrito" value="<?php echo $objAI->getNomeInscrito() ?>" class="form-control" placeholder="Nome" required autofocus style="font-size: 1.1em;">
                        </div>                        
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome" style="font-size: 1.2em;">Perfil:
                                <?php echo $objAI->getTituloPerfil()? $objAI->getTituloPerfil() : '<font color=red>Não informado</font>'; ?>
                            </label>
                        </div>                        
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome" style="font-size: 1.2em;">Número NIS :
                                <?php echo $objAI->getNis()? $objAI->getNis() : '<font color=red>Não informado</font>'; ?>
                            </label>                          
                        </div>                        

                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <!-- <h4>Avalição de Recursos:</h4> -->
                            <h4>Avalição de Solicitação:</h4>
                        </div>  
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <?php
                            if ($objEvConf->getTrabComplementoForm()==1) {
                                if($objEvConf->getTrabAvaliacaoComplementar()) {
                                    $objSRV->carregarCriterios($fws_, $objAI, $debug);
                                }
                            }
                            ?>
                            <?php if ($objAI->getResumoTrabalho()) { ?>
                                <h3>
                                <label for="nome">Descrição Solicitação:</label>
                                <?php echo $objAI->getResumoTrabalho() ?>
                                <br><br>
                                </h3>
                            <?php } ?>
                            <label for="nome">Consultar Documento :
                            <?php if ($objAI->getArquivoGerado()) { ?>
                            <input type="hidden" name="arquivoGerado" id="arquivoGerado" value="<?php echo $objAI->getArquivoGerado() ?>" />
                            <a class="show" id="btnVisualizarDocumento-<?php echo $objAI->getArquivoGerado() ?>" title="Visualizar Documento" href="javascript:void(0);" style="color:green;font-weight: bold;" onclick="javascript:visualizarDoc();">
                                Visualizar Documento <img src='<?php echo  WB_URL_IMG ?>/pdf.gif' alt='Visualizar Documento' border='0' width="15px"></a>
                            <?php
                             }else{
                                echo "Nenhum documento anexado.";
                            } ?>  
                            </label>
                            <br>
                        </div>  
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Justificativa do Avaliador:</label>
                            <?php echo '<br>' . ($objAI->getParecerAvaliador() ? $objAI->getParecerAvaliador()."<br>&nbsp;" : 'Não informado') ?>
                            <?php
                            if (!$objEvConf->getTrabAvaliacaoPermitirRecurso()) {
                            ?>
                                <FONT FACE='verdana' SIZE=1 COLOR='#555555'>(Máximo 1000 caracteres, com espaços)
                                    <br>
                                    <font id="DigitadoC" color="red">0</font> Caracteres digitados &nbsp; / &nbsp; restam <font id="RestanteC" color="red">1000</font>
                                    <br>
                                </FONT>
                                    <textarea onkeyup="contarMaximoCaracter(this, 1000, 'DigitadoC', 'RestanteC')" onkeypress="contarMaximoCaracter(this, 1000, 'DigitadoC', 'RestanteC')" rows="5" maxlength="1000" name="parecerAvaliador" id="parecerAvaliador" class="form-control" ><?php echo $objAI->getParecerAvaliador() ?></textarea><br>
                            <?php } ?>
                        </div>    
                        <?php
                        $habilitarSClassificacaoStatus = '';
                        if ($objEvConf->getTrabAvaliacaoPermitirRecurso()) {
                            $habilitarSClassificacaoStatus = ' disabled ';
                        }
                        ?>
                        <div class="form-group col-sm-6" style="padding-left: 0px;">
                            <label for="nome">Classificação Status :</label>
                            <select <?php echo $habilitarSClassificacaoStatus; ?> id="classificacaoStatus" name="classificacaoStatus" class="form-control" required autofocus >
                                <option value="">Selecione a Classificação</option>
                                <option value="H" <?php echo ($objAI->getClassificacaoStatus() == 'H') ? 'selected' : ''; ?>>Habilitado</option>
                                <option value="I" <?php echo ($objAI->getClassificacaoStatus() == 'I') ? 'selected' : ''; ?>>Não Habilitado</option>
                            </select>
                        </div>
                        <?php if ($objEvConf->getTrabAvaliacaoComplementar()) { ?>
                            <div class="form-group col-sm-6" style="padding-left: 0px;">
                                <label for="nome">Ordem de Classificação : (Informe N para não classificado)</label>
                                <input type="text" id="classificacao" size="10" name="classificacao" value="<?php echo $objAI->getClassificacao() ?>" class="form-control" placeholder="Classificação" required autofocus onBlur='trim(this);' readonly>
                            </div>
                        <?php } ?>                                            
                        <?php if ($objEvConf->getTrabAvaliacaoPermitirRecurso()) { ?>
                            <div class="form-group col-sm-12" style="padding-left: 0px;">
                                <label for="nome">Recursos Após Avaliação:</label>
                                <?php 
                                /*
                                <select id="recursoStatus" name="recursoStatus" class="form-control" required autofocus>
                                    <option value="">Status Recurso</option>
                                    <option value="SOLICITADO" <?php echo ($objAI->getRecursoStatus() == 'SOLICITADO') ? 'selected' : ''; ?>>Solicitado</option>
                                <option value="" <?php echo ($objAI->getRecursoStatus() == '') ? 'selected' : ''; ?>>Não Solicitado</option>
                                </select>
                                */
                                $objSRV->carregarTabelaRecursos($fws_, $debug);
                                ?>
                                <label for="nome">Consultar Documento :
                                <?php if ($objAI->getArquivoGerado()) { ?>
                                <input type="hidden" name="arquivoGerado" id="arquivoGerado" value="<?php echo $objAI->getArquivoGerado() ?>" />
                                <a class="show" id="btnVisualizarDocumento-<?php echo $objAI->getArquivoGerado() ?>" title="Visualizar Documento" href="javascript:void(0);" style="color:green;font-weight: bold;" onclick="javascript:visualizarDoc();">
                                    Visualizar Documento <img src='<?php echo  WB_URL_IMG ?>/pdf.gif' alt='Visualizar Documento' border='0' width="15px"></a>   
                                <?php 
                                }else{
                                    echo "Nenhum documento anexado.";
                                } ?> 
                                </label>
                                <br>      
                                <?php
                                if ($objAI->getInstitucional() == 1) {
                                    echo '<b>Inscrição Pessoa Jurídica</b>';
                                }else{
                                    echo "<b>Inscrição Pessoa Física</b>";
                                }
                                ?>
                            </div>
                            
                            <div class="form-group col-sm-12" style="padding-left: 0px;">
                                <label for="nome">Parecer do Avaliador para o Recurso:</label>
                                <FONT FACE='verdana' SIZE=1 COLOR='#555555'>(Máximo 1000 caracteres, com espaços)
                                    <br>
                                    <font id="DigitadoC" color="red">0</font> Caracteres digitados &nbsp; / &nbsp; restam <font id="RestanteC" color="red">1000</font>
                                    <br>
                                </FONT>
                                    <textarea onkeyup="contarMaximoCaracter(this, 1000, 'DigitadoC', 'RestanteC')" onkeypress="contarMaximoCaracter(this, 1000, 'DigitadoC', 'RestanteC')" rows="5" maxlength="1000" name="recursoParecer" id="recursoParecer" class="form-control" required><?php echo $objAI->getRecursoParecer() ?></textarea><br>
                            </div>
                        <?php } ?>
                        <?php if ($objEvConf->getTrabAvaliacaoComplementar()) { ?>
                            <div class="form-group col-sm-3" style="padding-left: 0px;">
                                <label for="nome">Pontos Informados Cadastro:</label>
                                <input type="text" id="pontosInformadosTrabalho" name="pontosInformadosTrabalho" value="<?php echo $objAI->getPontosInformadosTrabalho() ?>" class="form-control" placeholder="Pontos Informados" required autofocus>
                            </div>
                            <div class="form-group col-sm-3" style="padding-left: 0px;">
                                <label for="nome">Pontos Informados :</label>
                                <input type="text" id="sitPontos" name="sitPontos" value="<?php echo $objAI->getSitPontos() ?>" class="form-control" placeholder="Pontos Informados" required autofocus>
                            </div>
                            <div class="form-group col-sm-3" style="padding-left: 0px;">
                                <label for="nome">Pontos 1º Avaliação :</label>
                                <input type="text" id="pontosTotalConfirmado" name="pontosTotalConfirmado" value="<?php echo $objAI->getPontosTotalConfirmado() ?>" class="form-control" placeholder="Pontos Total Confirmados" required autofocus>
                            </div>
                            <div class="form-group col-sm-3" style="padding-left: 0px;">
                                <label for="nome">Pontos Após Recurso :</label>
                                <input type="text" id="pontosTotalAposRecurso" name="pontosTotalAposRecurso" value="<?php echo $objAI->getPontosTotalAposRecurso() ?>" class="form-control" placeholder="Pontos Após Recurso" required autofocus>
                            </div>
                        <?php } ?>    

                        <?php if ($objEvConf->getTrabAvaliacaoPermitirRecurso()) { ?>
                            <div class="form-group col-sm-6" style="padding-left: 0px;">
                                <label for="nome">Classificação Status Após Recurso :</label>
                                <select id="classificacaoStatusAposRecurso" name="classificacaoStatusAposRecurso" class="form-control" required autofocus>
                                    <option value="">Selecione a Classificação</option>
                                    <option value="H" <?php echo ($objAI->getClassificacaoStatusAposRecurso() == 'H') ? 'selected' : ''; ?>>Habilitado</option>
                                    <option value="I" <?php echo ($objAI->getClassificacaoStatusAposRecurso() == 'I') ? 'selected' : ''; ?>>Não Habilitado</option>
                                </select>
                            </div>
                        <?php } ?>
                        <?php if ($objEvConf->getTrabAvaliacaoComplementar()) { ?>
                            <div class="form-group col-sm-6" style="padding-left: 0px;">
                                <label for="nome">Ordem de Classificação Apóps Recurso: (Informe N para não classificado)</label>
                                <input type="text" id="classificacaoAposRecurso" size="10" name="classificacaoAposRecurso" value="<?php echo $objAI->getClassificacaoAposRecurso() ?>" class="form-control" placeholder="ClassificaçãoAposRecurso" required autofocus onBlur='trim(this);'>
                            </div>
                        <?php } ?>  
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <div id="carregando" class="shown hidden">
                                <div class="imagem">
                                    <div class="frase">Carregando...</div>
                                    <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
                                </div>
                            </div>
                            <div id="mensagem"></div>
                            <div id="sucesso" class="hidden"></div>
                            <div id="erro" class="hidden"></div>

                            <?php
                            $naoMostrarBtnSalvarAvaliacao = false;
                            $currentDateTime = new DateTime();
                            $endDateTime = new DateTime('2025-02-10 12:00:00');
                            if ($currentDateTime < $endDateTime) {
                                $naoMostrarBtnSalvarAvaliacao = true;
                            }
                            $naoMostrarBtnSalvarAvaliacao = true;
                            ?>

                            <?php if ($naoMostrarBtnSalvarAvaliacao) { ?>
                            <input type="hidden" value="1" name="_submit_check">
                            <input type="hidden" value="GerenciarAvaliacaoInscrito" name="c">
                            <input type="hidden" value="salvarAvaliacaoInscrito" name="m">

                            <input type="hidden" name="novoCodAvaliacao" id="novoCodAvaliacao" value="<?php echo $objAI->getCodAvaliacao()? $objAI->getCodAvaliacao() : '0' ?>" />
                            <input type="hidden" name="codAvaliacao" id="codAvaliacao" value="<?php echo $objAI->getCodAvaliacao() ?>" />
                            
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar">Salvar Avaliação
                                Inscrito
                            </button>
                            <?php }else{
                                echo '<div class="alert alert-warning">Avaliação encerrada, AGUARDANDO TERMINO DO PERÍODO DE RECURSO</div>';
                            } ?>
                            <button class="btn btn-xs btn-primary" type="button" id="btnLimpar">Limpar Campos</button>
                        </div>
                    </div>
                </form>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
            <?php } ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <b>Lista de Inscritos Avaliados :</b>
                <table class="tablesorter" id="itens" border="0">
                    <thead>
                        <?php echo $objSRV->carregarCabecalhoTrabelaAvaliacaoInscrito($fws_, $debug); ?>
                    </thead>
                    <tbody class="inputs">
                        <?php
                        $objSRV->carregarTabelaAvaliacaoInscrito($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<form name="getdoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirDocumentoTela">
    <input type="hidden" value="" name="cod">
    <input type="hidden" value="" name="codEvento">
</form>
<script language="JavaScript" type="text/javascript">
    function visualizarDoc(){
        if($('#arquivoGerado').val()==''){
            alert('Documento não ecnontrato');
            return;
        }
        document.getdoc.cod.value=$('#arquivoGerado').val();
        document.getdoc.submit();
    }
    function visualizarDocAvaliado(idDoc,codEvento){
        if(idDoc==''){
            alert('Documento não ecnontrato');
            return;
        }
        document.getdoc.cod.value=idDoc;
        document.getdoc.codEvento.value=codEvento;
        document.getdoc.submit();
    }
</script>