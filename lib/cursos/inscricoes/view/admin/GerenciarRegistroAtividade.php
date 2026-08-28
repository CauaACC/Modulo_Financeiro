<?php
//require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarRegistroAtividadeAC();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script type="text/javascript">
    var $j = jQuery.noConflict();

    $j(document).ready(function() {
        /**eu comentei aqui, pois se a tabela la embaixo não tem linha, esse parte do código da problema */
        // $('table').tablesorter({
        //     sortList: [[1, 0]]
        // });

        // $('#itens tr:odd').css('background', '#F4F4F4');
        // $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function() {
            salvarRegistroAtividade();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodRegAtividade').val(0);
            $('#salvarRegistroAtividade').val('Adicionar');
            salvarRegistroAtividade();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            //alert(id+'<-')
            editarRegistroAtividade(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirRegistroAtividade(id);
        });
        //        carregarTabelaRegistroAtividades();
    });
    //funçães globais
    function carregarTabelaRegistroAtividades() {
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarRegistroAtividade',
            'm': 'carregarTabelaRegistroAtividades',
            '_submit_check': '1'
        }, function(html) {
            $("table tbody.inputs tr").remove();
            $("table").trigger("update");
            // append the "ajax'd" data to the table body 
            $("table tbody.inputs").append(html);
            // let the plugin know that we made a update 
            $("table").trigger("update");
            // $("table").tablesorter({
            //     sortList: [[1,2]]                    
            // });            
            //eventos para manipulação de registros
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarRegistroAtividade(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirRegistroAtividade(id);
            });
        });
    }

    function salvarRegistroAtividade() {
        $('#formRegistroAtividade').ajaxSubmit({
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
                    carregarTabelaRegistroAtividades();
                    limparCampos();
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function editarRegistroAtividade(codRegAtividade) {
        if (codRegAtividade) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codRegAtividade': codRegAtividade,
                    'c': 'GerenciarRegistroAtividade',
                    'm': 'editarRegistroAtividade',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codRegAtividade').val(data.codRegAtividade);
                    $('#dia').val(data.dia);
                    $('#diaSemana').val(data.diaSemana);
                    $('#descricao').val(data.descricao);
                    $('#justificativa').val(data.justificativa);
                    $('#horasTrabalhadas').val(data.horasTrabalhadas);
                    $('#cumprimentoHorario').val(data.cumprimentoHorario);
                    $('#avaliacaoData').val(data.avaliacaoData);
                    $('#novoCodRegAtividade').val(1);
                    $('#salvarRegistroAtividade').val('Salvar Alterações');
                    $('html,body').animate({
                        scrollTop: $("#registroAtividades").offset().top
                    }, 'slow');
                },
                complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do RegistroAtividade não informado!');
        }
    }

    function excluirRegistroAtividade(codRegAtividade) {
        if (confirm('Deseja realmente excluir RegistroAtividade?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codRegAtividade': codRegAtividade,
                    'c': 'GerenciarRegistroAtividade',
                    'm': 'excluirRegistroAtividade',
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
                        carregarTabelaRegistroAtividades();
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
        $('#codRegAtividade').val('');
        $('#dia').val('');
        $('#diaSemana').val('');
        $('#descricao').val('');
        $('#justificativa').val('');
        $('#horasTrabalhadas').val('');
        $('#cumprimentoHorario').val('');
        //$('#avaliacaoData').val('');
        $('#novoCodRegAtividade').val(0);
        $('#salvarRegistroAtividade').val('Adicionar');

    }
    async function gerarPDF() {
        const {
            jsPDF
        } = window.jspdf;

        const doc = new jsPDF();
        const elemento = document.querySelector("#itens");
        const canvas = await html2canvas(elemento);

        const imgData = canvas.toDataURL("image/png");
        const imgProps = doc.getImageProperties(imgData);
        const pdfWidth = doc.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

        doc.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
        doc.save("Registro_Atividade_Mensal.pdf");
    }

    function gerarRegistroAtividadePdf(tipoRegistroAtividadePdf, codTrabalho) {
        $('#tipoRegistroAtividadePdf').val(tipoRegistroAtividadePdf);
        $('#cod_trabalho_aceite').val(codTrabalho);
        $('#abrirRegistroAtividadePdf').submit();
    }
</script>
<!--<script type="text/javascript" src="/<?php echo  WB_PATH ?>/view/js/util/validacoes.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
// require_once(dirname(__FILE__)."/../../include/config.php");
// require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
// require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
// require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_beneficios.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <br>
            <!-- <h4>Gerenciar Registro de Atividades:</h4> -->
        </div>
         <div class="panel-body">
            <?php
            try {
                //                $objEv->controlarAcessoProposta('mensagem');
                if($objInscrito->getStatusProcesso()!='PARTICIPANDO' and $objInscrito->getStatusProcesso()!='INSCRITO'){
                    throw new Exception('Registro de Atividade bloqueado, situação no processo:'.$objInscrito->getStatusProcesso().'!');
                }
                $hoje = new DateTime();
                $objLiberacao = new LiberacaoRegistro();
                $objLiberacao->setCodInscrito($objInscrito->getCodInscrito());
                $objLiberacao->setCodEvento(COD_EVENTO);
                $liberacao = $objLiberacao->buscarLiberacaoAtiva();
                // if ($liberacao) {
                //     $dataMin = $liberacao['data_inicio'];
                // } else {
                    // Dia 1 do mês atual
                    $dataMin = $hoje->modify('first day of this month')->format('Y-m-d');
                    // 25 dias antes do início do mês atual
                    $dataMin = $hoje->modify('-31 days')->format('Y-m-d');
                // } 
                
                // Último dia do mês atual
                $dataMax = (new DateTime())->modify('last day of this month')->format('Y-m-d');
            ?>
                <form id="formRegistroAtividade" name="formRegistroAtividade" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="dia">Data da atividade:</label>
                            <input type="date" id="dia" name="dia" value=""
                                class="form-control" placeholder="Dia numerado" required autofocus
                                min="<?php echo $dataMin ?>" max="<?php echo$dataMax ?>">
                            <script>
                                document.getElementById("dia").addEventListener("change", function() {
                                    let dataSelecionada = new Date(this.value);
                                    // let diaSemana = dataSelecionada.getDay(); // Retorna de 0 (Domingo) a 6 (Sábado)
                                    let diasDaSemana = ["Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado","Domingo"];
                                    let diaSemana = diasDaSemana[dataSelecionada.getDay()];

                                    let selectDiaSemana = document.getElementById("diaSemana");
                                    selectDiaSemana.value = diaSemana;
                                });
                            </script>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="diaSemana">Dia da semana:</label>
                            <select class='form-control' name="diaSemana" id="diaSemana" placeholder="Dia da semana" required autofocus>
                                <option class='form_campos' value='' selected>Dia da semana</option>
                                <?php
                                CarregarOptionsAC::diaSemana($fws_, $debug);
                                ?>
                            </select>
                        </div>
                        <!-- <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="horasTrabalhadas">O cumprimento do horário foi: Parcial ou Integral?</label>
                                <label class="radio-inline">
                                    <input type="radio" name="cumprimentoHorario" id="cumprimentoIntegral" value="integral" required> Integral
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="cumprimentoHorario" id="cumprimentoParcial" value="parcial" required> Parcial
                                </label>
                                </div> -->
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <div class='col-sm-12'>
                                <label for="horasTrabalhadas" class="me-2">Horas Trabalhadas (máximo de 6h diárias permitidas):</label>
                            </div>
                            <div class='col-sm-6'>
                                <select id="horasTrabalhadas" name="horasTrabalhadas" class="form-control w-auto" required autofocus>
                                    <option value="" disabled selected>Selecione as horas trabalhadas</option>
                                    <option value="0" <?php echo verifItemSelected($fws['horasTrabalhadas'], 0); ?>>0 min</option>
                                    <option value="15" <?php echo verifItemSelected($fws['horasTrabalhadas'], 15); ?>>15 min</option>
                                    <option value="30" <?php echo verifItemSelected($fws['horasTrabalhadas'], 30); ?>>30 min</option>
                                    <option value="45" <?php echo verifItemSelected($fws['horasTrabalhadas'], 45); ?>>45 min</option>
                                    <option value="60" <?php echo verifItemSelected($fws['horasTrabalhadas'], 60); ?>>1 hora</option>
                                    <option value="75" <?php echo verifItemSelected($fws['horasTrabalhadas'], 75); ?>>1 hora e e 15 min</option>
                                    <option value="90" <?php echo verifItemSelected($fws['horasTrabalhadas'], 90); ?>>1 hora e 30 min</option>
                                    <option value="105" <?php echo verifItemSelected($fws['horasTrabalhadas'], 105); ?>>1 hora e 45 min</option>
                                    <option value="120" <?php echo verifItemSelected($fws['horasTrabalhadas'], 120); ?>>2 horas</option>
                                    <option value="135" <?php echo verifItemSelected($fws['horasTrabalhadas'], 135); ?>>2 horas e 15 min</option>
                                    <option value="150" <?php echo verifItemSelected($fws['horasTrabalhadas'], 150); ?>>2 horas e 30 min</option>
                                    <option value="165" <?php echo verifItemSelected($fws['horasTrabalhadas'], 165); ?>>2 horas e 45 min</option>
                                    <option value="180" <?php echo verifItemSelected($fws['horasTrabalhadas'], 180); ?>>3 horas</option>
                                    <option value="195" <?php echo verifItemSelected($fws['horasTrabalhadas'], 195); ?>>3 horas e 15 min</option>
                                    <option value="210" <?php echo verifItemSelected($fws['horasTrabalhadas'], 210); ?>>3 horas e 30 min</option>
                                    <option value="225" <?php echo verifItemSelected($fws['horasTrabalhadas'], 225); ?>>3 horas e 45 min</option>
                                    <option value="240" <?php echo verifItemSelected($fws['horasTrabalhadas'], 240); ?>>4 horas</option>
                                    <option value="255" <?php echo verifItemSelected($fws['horasTrabalhadas'], 255); ?>>4 horas e 15 min</option>
                                    <option value="270" <?php echo verifItemSelected($fws['horasTrabalhadas'], 270); ?>>4 horas e 30 min</option>
                                    <option value="285" <?php echo verifItemSelected($fws['horasTrabalhadas'], 285); ?>>4 horas e 45 min</option>
                                    <option value="300" <?php echo verifItemSelected($fws['horasTrabalhadas'], 300); ?>>5 horas</option>
                                    <option value="315" <?php echo verifItemSelected($fws['horasTrabalhadas'], 315); ?>>5 horas e 15 min</option>
                                    <option value="330" <?php echo verifItemSelected($fws['horasTrabalhadas'], 330); ?>>5 horas e 30 min</option>
                                    <option value="345" <?php echo verifItemSelected($fws['horasTrabalhadas'], 345); ?>>5 horas e 45 min</option>
                                    <option value="360" selected <?php echo verifItemSelected($fws['horasTrabalhadas'], 360); ?>>6 horas</option>
                                    <!--
                                    <option value="375" <?php echo verifItemSelected($fws['horasTrabalhadas'], 375); ?>>6 horas e 15 min</option>
                                    <option value="390" <?php echo verifItemSelected($fws['horasTrabalhadas'], 390); ?>>6 horas e 30 min</option>
                                    <option value="405" <?php echo verifItemSelected($fws['horasTrabalhadas'], 405); ?>>6 horas e 45 min</option>
                                    <option value="420" <?php echo verifItemSelected($fws['horasTrabalhadas'], 420); ?>>7 horas</option>
                                    <option value="435" <?php echo verifItemSelected($fws['horasTrabalhadas'], 435); ?>>7 horas e 15 min</option>
                                    <option value="450" <?php echo verifItemSelected($fws['horasTrabalhadas'], 450); ?>>7 horas e 30 min</option>
                                    <option value="465" <?php echo verifItemSelected($fws['horasTrabalhadas'], 465); ?>>7 horas e 45 min</option>
                                    <option value="480" <?php echo verifItemSelected($fws['horasTrabalhadas'], 480); ?>>8 horas</option>-->
                                </select>

                            </div>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="descricao">Descrição das atividades:</label>
                            <textarea type="textarea" id="descricao" name="descricao" value=""
                                class="form-control" style="height: 150px;" placeholder="Descrição das atividades" required autofocus></textarea>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="justificativa">Justificativas:</label>
                            <textarea type="textarea" id="justificativa" name="justificativa" value=""
                                class="form-control" placeholder="Justificativas" required autofocus></textarea>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <div id="carregando" class="shown hidden">
                                <div class="imagem">
                                    <div class="frase">Carregando...</div>
                                    <img src="<?php echo  WB_PATH ?>/resources/image/loading-9.gif" border="0">
                                </div>
                            </div>
                            <div id="mensagem"></div>
                            <div id="sucesso" class="hidden"></div>
                            <div id="erro" class="hidden"></div>
                            <input type="hidden" value="1" name="_submit_check">
                            <input type="hidden" value="GerenciarRegistroAtividade" name="c">
                            <input type="hidden" value="salvarRegistroAtividade" name="m">

                            <input type="hidden" name="novoCodRegAtividade" id="novoCodRegAtividade" value="0" />
                            <input type="hidden" name="codRegAtividade" id="codRegAtividade" value="" />
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar">Salvar e Adicionar Registro</button>
                            <!--                            &nbsp;&nbsp;&nbsp;                
                                                        <button class="btn btn-xs btn-primary" type="button" id="btnSalvarComoNovo" >Salvar como novo registro</button>-->
                            &nbsp;&nbsp;&nbsp;
                            <button class="btn btn-xs btn-primary" type="button" id="btnLimpar">Limpar e sair</button>
                            &nbsp;&nbsp;&nbsp;
                            <!-- <button class="btn btn-xs btn-primary" type="button" onclick="gerarPDF()"> PDF Mensal (js)</button> -->
                        </div>
                    </div>
                </form>
                <form name="abrirRegistroAtividadePdf" id="abrirRegistroAtividadePdf" method="post" action="<?php echo WB_URL_ACTION ?>?content=view/carta_aceite/registro-atividade.php" target="_blank">
                    <input type="hidden" value="" name="tipoRegistroAtividadePdf" id="tipoRegistroAtividadePdf">
                    <input type="hidden" value="" name="cod_trabalho" id="cod_trabalho_aceite">
                </form>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
            <?php } ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <b>Lista de Atividades Informadas:</b>
                <table class="table table-striped tablesorter" id="itens" border="0">
                    <tbody class="inputs">
                        <?php
                        $objSRV->carregarTabelaRegistroAtividades($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>