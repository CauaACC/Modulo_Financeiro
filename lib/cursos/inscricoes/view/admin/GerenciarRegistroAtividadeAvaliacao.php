<?php
    //error_reporting(E_ALL);
    //ini_set("display_errors", "On");
// require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarRegistroAtividadeAvaliacaoAC();
if (!$objSRV->temAcessoAvaliacaoRegistro($objInscrito->getCodPerfil())) { return; }
?>
<script type="text/javascript">
    var lastEditedId;
    $(document).ready(function() {
        $('table').tablesorter({
            sortList: [[1, 0]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvarAvaliacao').click(function() {
            salvarRegistroAtividadeAvaliacao();
        });
        // Limpar campos ao fechar o modal
        $('#modalAvaliacao').on('hidden.bs.modal', function() {
            limparCampos();
        });
        //eventos para edição e exclusão de registros
        $('.avaliarAtividade').click(function() {
            var id = $(this).parents("tr").attr("id"); 
            lastEditedId = id;               // grava aqui
            editarRegistroAtividadeAvaliacao(id);
        });
        //        carregarTabelaRegistroAtividades();

        // Hover nas linhas de atividade
        $('#itens tbody.inputs').on('mouseenter', 'tr[id]', function() {
            $(this).data('bg', this.style.backgroundColor);
            $(this).css('background-color', '#d9edf7');
        }).on('mouseleave', 'tr[id]', function() {
            $(this).css('background-color', $(this).data('bg') || '');
        });

        // Botão "Atualizar Lista" — reaplica os filtros
        $('#btnAtualizarLista').click(function() {
            $('#filtro-status, #filtro-mes, #filtro-ano').trigger('change');
        });

        // ao mudar qualquer filtro
        $('#filtro-status, #filtro-mes, #filtro-ano').on('change', function() {
            var status = $('#filtro-status').val();
            var mes    = $('#filtro-mes').val();
            var ano    = $('#filtro-ano').val();
            var classMes = mes ? 'mes-' + mes : '';
            var classAno = ano ? 'ano-' + ano : '';
            $('tbody.inputs tr').each(function() {
                var $tr = $(this);
                var okMes = !classMes || $tr.hasClass(classMes);
                var okAno = !classAno || $tr.hasClass(classAno);
                // total do inscrito: filtra por mês e ano
                if ($tr.hasClass('total-inscrito')) {
                    $tr.toggle(okMes && okAno);
                    return;
                }
                // cabeçalho/colaborador: filtra por mês e ano
                if (!$tr.attr('id')) {
                    $tr.toggle(okMes && okAno);
                    return;
                }
                // linhas de dado: aplica mês, ano E status
                var okStatus = !status || $tr.hasClass(status);
                $tr.toggle(okMes && okAno && okStatus);
            });

            // Esconde grupo do inscrito (nome + total) quando nenhuma atividade está visível
            $('tbody.inputs tr.total-inscrito:visible').each(function() {
                var $total = $(this);
                var hasVisibleData = false;
                var $prev = $total.prev();
                var $hideIfEmpty = [$total];
                while ($prev.length && !$prev.hasClass('total-inscrito')) {
                    if ($prev.find('th').length) break;
                    if ($prev.attr('id')) {
                        if ($prev.is(':visible')) hasVisibleData = true;
                    } else {
                        $hideIfEmpty.push($prev);
                    }
                    $prev = $prev.prev();
                }
                if (!hasVisibleData) {
                    for (var i = 0; i < $hideIfEmpty.length; i++) {
                        $hideIfEmpty[i].hide();
                    }
                }
            });
        });
        var now = new Date();
        var mesAtual = ("0" + (now.getMonth() + 1)).slice(-2);
        var anoAtual = now.getFullYear().toString();
        $('#filtro-status').val('status-aguardando');
        $('#filtro-mes').val(mesAtual);
        $('#filtro-ano').val(anoAtual);
        $('#filtro-status, #filtro-mes, #filtro-ano').trigger('change');       
    });

    //funçães globais
    function carregarTabelaRegistroAtividades() {
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarRegistroAtividadeAvaliacao', 'm': 'carregarTabelaRegistroAtividadesAvaliacao', '_submit_check': '1'}
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
            //eventos para edição e exclusão de registros
            $('.avaliarAtividade').click(function() {
                var id = $(this).parents("tr").attr("id");
                lastEditedId = id;
                editarRegistroAtividadeAvaliacao(id);
            });
            
            if (lastEditedId) {
                var $row = $('#itens tr#' + lastEditedId);
                if ($row.length) {
                    $row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }            
            // *** Aqui reaplica o filtro e faz scroll ***
            $('#filtro-status, #filtro-mes, #filtro-ano').trigger('change');
        });
    }
    function salvarRegistroAtividadeAvaliacao() {
        $('#formRegistroAtividadeAvaliacao').ajaxSubmit({
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
                if (data.statusMessage) {
                    // Capturar valores ANTES de fechar o modal
                    var status = $('#aavaliacaoStatus').val();
                    var rowId = lastEditedId;

                    // Atualizar a linha só após o modal fechar completamente
                    $('#modalAvaliacao').one('hidden.bs.modal', function() {
                        var $row = $('#itens tr#' + rowId);
                        if ($row.length) {
                            var statusLower = status.toLowerCase();
                            var now = new Date();
                            var dataFormatada = ("0"+now.getDate()).slice(-2) + '/'
                                              + ("0"+(now.getMonth()+1)).slice(-2) + '/'
                                              + now.getFullYear() + ' '
                                              + ("0"+now.getHours()).slice(-2) + ':'
                                              + ("0"+now.getMinutes()).slice(-2);

                            // Mapear status → classe, cor
                            var classeStatus, cor;
                            if (statusLower === 'validado') {
                                classeStatus = 'status-validado';
                                cor = 'green';
                            } else {
                                classeStatus = 'status-nao-validado';
                                cor = 'red';
                            }

                            // Atualizar classe da <tr>
                            $row.removeClass('status-aguardando status-validado status-nao-validado')
                                .addClass(classeStatus);

                            // Atualizar célula de status (coluna 5)
                            var $tds = $row.find('td');
                            $tds.eq(5).attr('style', 'color: ' + cor).html(status + '<br>' + dataFormatada);

                            // Atualizar sit. pagamento (coluna 6)
                            $tds.eq(6).text(statusLower === 'validado' ? 'Aguardando Fechamento Mês' : 'Aguardando Validação');

                            // Atualizar botão para "Revalidar"
                            $tds.eq(0).find('button').text('Revalidar');

                            // Destaque temporário na linha alterada
                            var corDestaque = statusLower === 'validado' ? '#b3e6b3' : '#f5b7b1';
                            $row.css({
                                'outline': '3px solid ' + corDestaque,
                                'background-color': corDestaque
                            });
                            setTimeout(function() {
                                $row.css({
                                    'outline': '',
                                    'background-color': ''
                                });
                            }, 3000);
                            // Sem scroll — manter a posição onde o usuário estava
                        }
                    });

                    $('#modalAvaliacao').modal('hide');
                    limparCampos();
                } else {
                    processJson(data);
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarRegistroAtividadeAvaliacao(codRegAtividade) {
        if (codRegAtividade) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codRegAtividade': codRegAtividade, 'c': 'GerenciarRegistroAtividadeAvaliacao', 'm': 'editarRegistroAtividadeAvaliacao', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    // startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codRegAtividade').val(data.codRegAtividade);
                     $('#novoCodRegAtividade').val(1);
                    // $('#resumoRegistroAtividade').hmtl(data.resumoRegistroAtividade);
                    $('#adia').val(data.dia);
                    $('#adiaSemana').val(data.diaSemana);
                    // $('#presenca').val(data.presenca);
                    // $('#motivo').val(data.motivo);
                    $('#aobservacao').val(data.observacao);
                    $('#adescricao').val(data.descricao);
                    $('#ajustificativa').val(data.justificativa);
                    $('#ahorasTrabalhadas').val(data.horasTrabalhadas);
                    $('#anomeInscrito').val(data.nomeInscrito);
                    $('#acodInscrito').val(data.codInscrito);
                    $('#aparecerAvaliador').val(data.parecerAvaliador);
                    $('#aavaliacaoResponsavel').val(data.avaliacaoResponsavel);
                    $('#aavaliacaoStatus').val(data.avaliacaoStatus);
                    $('#aavaliacaoData').val(data.avaliacaoData);
                    $('#acodRegAtividade').val(data.codRegAtividade);
                    // $('#salvarRegistroAtividadeAvaliacao').val('Salvar Alterações');
                    $('#modalAvaliacao').modal('show');
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do Registro Atividade não informado!');
        }
    }
    
    function limparCampos() {
        //Limpando campos
        $('#adia').val('');
        $('#adiaSemana').val('');
        // $('#presenca').val('');
        // $('#motivo').val('');
        $('#aobservacao').val('');
        $('#descricao').val('');
        $('#justificativa').val('');
        $('#ahorasTrabalhadas').val('');
        $('#anomeInscrito').val('');
        $('#acodInscrito').val('');
        $('#aparecerAvaliador').val('');
        $('#aavaliacaoResponsavel').val('');
        $('#aavaliacaoStatus').val('');
        $('#aavaliacaoData').val('');
        $('#novocodRegAtividade').val(0);
        // $('#salvarRegistroAtividadeAvaliacao').val('Adicionar');
        processJsonLimpar();
    }
</script>
<!--<script type="text/javascript" src="/<?php echo  WB_PATH ?>/view/js/util/validacoes.js"></script>-->
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
// require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
// require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
// require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_outros.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <br>
            <!-- <h4>Gerenciar Validação de Registro de Atividades:</h4> -->
        </div>
        <div class="panel-body">
        <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                if($objInscrito->getStatusProcesso()!='PARTICIPANDO' and $objInscrito->getStatusProcesso()!='INSCRITO'){
                    throw new Exception('Avaliação de Atividade bloqueado, situação no processo:'.$objInscrito->getStatusProcesso().'!');
                }
            } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
            <?php } ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <br>
                <b>Lista de Registro de Atividades do NRE do Avaliador:</b>
                <br>
                <!-- filtro de status -->
                <label>Status Validação:
                <select id="filtro-status">
                    <option value="">Todos</option>
                    <option value="status-validado">Validado</option>
                    <option value="status-aguardando">Aguardando Validação</option>
                    <option value="status-nao-validado">Não validado</option>
                </select>
                </label>

                <!-- filtro de mês -->
                <label>Mês:
                <select id="filtro-mes">
                    <option value="">Todos</option>
                    <option value="01">Janeiro</option>
                    <option value="02">Fevereiro</option>
                    <option value="03">Março</option>
                    <option value="04">Abril</option>
                    <option value="05">Maio</option>
                    <option value="06">Junho</option>
                    <option value="07">Julho</option>
                    <option value="08">Agosto</option>
                    <option value="09">Setembro</option>
                    <option value="10">Outubro</option>
                    <option value="11">Novembro</option>
                    <option value="12">Dezembro</option>
                </select>
                </label>
                <!-- filtro de ano -->
                <label>Ano:
                <select id="filtro-ano">
                    <option value="">Todos</option>
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                </select>
                </label>

                <button type="button" class="btn btn-default btn-sm" id="btnAtualizarLista" style="margin-left: 10px;">Atualizar Lista</button>

                <?php
                /*
                
                <div>
                    <label><input type="radio" name="filter" value="aguardando"> Aguardando</label>
                    <label><input type="radio" name="filter" value="Validado"> Validado</label>
                    <label><input type="radio" name="filter" value="Não Validado"> Não Validados</label>
                    <label><input type="radio" name="filter" value="all"  checked> Todos</label>
                </div>
                <div>
                    <label>
                        Filtro por Mês: 
                    </label>
                    <select id="filtroMes" name="filtroMes">
                        <option value='all'>Todos</option>
                        <?php foreach (range(1, 12) as $mes): ?>
                        <?php
                            $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
                        ?>
                             <option value="<?php echo $mes; ?>"><?php echo getMonthName($mes); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                        
                <!-- <div>
                    <label>
                        Filtro por NRE: 
                    </label>
                    <select id="filtroMes" name="filtroMes">
                        <option value='all'>Todos</option>
                        <option value='all'>Londrina</option>
                        <option value='all'>Curitiba</option> 
                    </select>
                </div> -->
                <!-- <div>
                    <label>
                        Por nome do colaborador: 
                    </label>
                    <input type="text" id="filtroNome" name="filtroNome" class="form-control" placeholder="Digite o nome do colaborador">
                </div> -->    
                */
                ?>            
                <table class="table table-striped tablesorter" id="itens" border="0">
                    <tbody class="inputs">
                        <?php
                        // $objSRV->carregarTabelaRegistroAtividades($fws_, $debug);
                        $objSRV->carregarTabelaRegistroAtividadesAvaliacao($fws_, $debug);
                        ?>
                    </tbody>
                </table>
                <style>
                .status-aguardando { background-color: #ffeb99; } /* Amarelo */
                .status-validado { background-color: #b3ffb3; } /* Verde */
                .status-nao-validado { background-color: #ffb3b3; } /* Vermelho */
                #itens tbody.inputs tr[id]:hover {
                    background-color: #d9edf7 !important;
                    cursor: pointer;
                    outline: 2px solid #bce8f1;
                }
                /* Mensagens dentro do modal: alertas compactos */
                #modalAvaliacao #erro,
                #modalAvaliacao #sucesso {
                    border-radius: 4px;
                    margin: 10px 0;
                    padding: 8px 12px;
                    clear: both;
                }
                </style>
            </div>            
        </div>
    </div>
</div>

<!-- Modal de Avaliação -->
<div class="modal fade" id="modalAvaliacao" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formRegistroAtividadeAvaliacao" name="formRegistroAtividadeAvaliacao" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Avaliar Registro de Atividade</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="nomeInscrito">Nome do Colaborador a ser Avaliado:</label>
                        <input type="text" id="anomeInscrito" name="nomeInscrito" value=""
                               class="form-control" placeholder="Nome do Avaliado" readonly>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="dia">Data da atividade:</label>
                        <input type="text" id="adia" name="dia" value=""
                               class="form-control" placeholder="Dia da atividade" readonly>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="diaSemana">Dia da Semana:</label>
                        <input type="text" id="adiaSemana" name="diaSemana" value=""
                               class="form-control" placeholder="Dia da Semana" readonly>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="horasTrabalhadas">Horas Trabalhadas:</label>
                        <input type="text" id="ahorasTrabalhadas" name="horasTrabalhadas" value=""
                               class="form-control" placeholder="Horas Trabalhadas" readonly>
                    </div>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="descricao">Descrição das atividades:</label>
                        <textarea id="adescricao" name="descricao"
                               class="form-control" style="height: 150px;" placeholder="Descrição das atividades" readonly></textarea>
                    </div>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="justificativa">Justificativas do Avaliado:</label>
                        <textarea id="ajustificativa" name="justificativa"
                               class="form-control" placeholder="Justificativa do avaliado" readonly></textarea>
                    </div>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="parecerAvaliador">Parecer do Avaliador (necessário em caso de não aprovação):</label>
                        <textarea id="aparecerAvaliador" name="parecerAvaliador"
                                class="form-control" placeholder="Observação dos avaliadores" required></textarea>
                    </div>
                    <div class="form-group col-sm-12" style="padding-left: 0px;display: none;">
                        <label for="avaliacaoResponsavel">Responsavel:</label>
                        <input type="text" id="aavaliacaoResponsavel" name="avaliacaoResponsavel" value=""
                                class="form-control" placeholder="Nome do Responsavel">
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="avaliacaoStatus">Decisão do Avaliador:</label>
                        <select class='form-control' name="avaliacaoStatus" id="aavaliacaoStatus" placeholder="Status" required>
                            <option class="form-campos" value="" selected></option>
                            <?php
                                CarregarOptionsAC::avaliacaoStatus($fws_, $debug);
                            ?>
                        </select>
                    </div>
                    <div id="carregando" class="shown hidden">
                        <div class="imagem">
                            <div class="frase">Carregando...</div>
                            <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
                        </div>
                    </div>
                    <div id="mensagem"></div>
                    <div id="sucesso" class="hidden"></div>
                    <div id="erro" class="hidden"></div>
                    <input type="hidden" value="1" name="_submit_check">
                    <input type="hidden" value="GerenciarRegistroAtividadeAvaliacao" name="c">
                    <input type="hidden" value="salvarRegistroAtividadeAvaliacao" name="m">
                    <input type="hidden" name="novoCodRegAtividade" id="novoCodRegAtividade" value="0"/>
                    <input type="hidden" name="codRegAtividade" id="acodRegAtividade" value=""/>
                    <input type="hidden" name="codInscrito" id="acodInscrito" value=""/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarAvaliacao">Salvar Decisão de Validação</button>
                </div>
            </form>
        </div>
    </div>
</div>

