<?php
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
$objSRV = new GerenciarInscritoDependentesAC();
$objInscrito = new Inscrito();
$objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
$objInscrito->setCodEvento($_SESSION['_cod_evento']);
$objInscrito->getInscritoById($debug);
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#btnSalvar').click(function() {
            salvarInscritoDependentes();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
        $('.editar').click(function() {
            var id = $(this).parents("tr").attr("id");
            editarInscritoDependentes(id);
        });
        $('.excluir').click(function() {
            var id = $(this).parents("tr").attr("id");
            excluirInscritoDependentes(id);
        });
    });

    function carregarTabelaInscritoDependentes() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
            'c': 'GerenciarInscritoDependentes',
            'm': 'carregarTabelaInscritoDependentes',
            '_submit_check': '1'
        }, function(html) {
            $("table tbody.inputs tr").remove();
            $("table").trigger("update");
            $("table tbody.inputs").append(html);
            $("table").trigger("update");
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarInscritoDependentes(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirInscritoDependentes(id);
            });
        });
    }

    function salvarInscritoDependentes() {
        $('#formInscritoDependentes').ajaxSubmit({
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
                    carregarTabelaInscritoDependentes();
                    limparCampos();
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function editarInscritoDependentes(codDependente) {
        if (codDependente) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: {
                    'codDependente': codDependente,
                    'c': 'GerenciarInscritoDependentes',
                    'm': 'editarInscritoDependentes',
                    '_submit_check': '1'
                },
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    $('#codDependente').val(data.codDependente);
                    $('#nomeDependente').val(data.nomeDependente);
                    $('#cpfDependente').val(data.cpfDependente);
                    $('#dataNascimento').val(data.dataNascimento);
                    $('#grauParentesco').val(data.grauParentesco);
                    $('#sexo').val(data.sexo);
                    $('#possuiDeficiencia').val(data.possuiDeficiencia);
                    $('#descricaoDeficiencia').val(data.descricaoDeficiencia);
                    $('#dependenteIrpf').val(data.dependenteIrpf);
                    $('#dependentePlanoSaude').val(data.dependentePlanoSaude);
                    $('#dependenteSalarioFamilia').val(data.dependenteSalarioFamilia);
                    $('#observacoes').val(data.observacoes);
                    $('#status').val(data.status);
                    $('#btnSalvar').text('Salvar Alterações');
                    $('html,body').animate({ scrollTop: $("#formInscritoDependentes").offset().top }, 'slow');
                },
                complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do dependente não informado!');
        }
    }

    function excluirInscritoDependentes(codDependente) {
        if (confirm('Deseja realmente excluir este dependente?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: {
                    'codDependente': codDependente,
                    'c': 'GerenciarInscritoDependentes',
                    'm': 'excluirInscritoDependentes',
                    '_submit_check': '1'
                },
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaInscritoDependentes();
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
        $('#codDependente').val('');
        $('#nomeDependente').val('');
        $('#cpfDependente').val('');
        $('#dataNascimento').val('');
        $('#grauParentesco').val('');
        $('#sexo').val('');
        $('#possuiDeficiencia').val('0');
        $('#descricaoDeficiencia').val('');
        $('#dependenteIrpf').val('0');
        $('#dependentePlanoSaude').val('0');
        $('#dependenteSalarioFamilia').val('0');
        $('#observacoes').val('');
        $('#getMessage').html('');
        $('#btnSalvar').text('Salvar e Adicionar');
    }
</script>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <br>
        </div>
        <div class="panel-body">
            <?php
            try {
                if($objInscrito->getStatusProcesso()!='PARTICIPANDO' and $objInscrito->getStatusProcesso()!='INSCRITO'){
                    throw new Exception('Cadastro de Dependentes bloqueado, situação no processo: '.$objInscrito->getStatusProcesso().'!');
                }
            ?>
                <form id="formInscritoDependentes" name="formInscritoDependentes" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <div class="form-group col-sm-6" style="padding-left: 0px;">
                            <label for="nomeDependente">Nome do Dependente:</label>
                            <input type="text" id="nomeDependente" name="nomeDependente" value=""
                                   class="form-control" placeholder="Nome completo" required>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="cpfDependente">CPF do Dependente:</label>
                            <input type="text" id="cpfDependente" name="cpfDependente" value=""
                                   class="form-control" placeholder="000.000.000-00" required>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="dataNascimento">Data de Nascimento:</label>
                            <input type="date" id="dataNascimento" name="dataNascimento" value=""
                                   class="form-control" required>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="grauParentesco">Grau de Parentesco:</label>
                            <select id="grauParentesco" name="grauParentesco" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="FILHO">FILHO</option>
                                <option value="FILHA">FILHA</option>
                                <option value="ENTEADO">ENTEADO</option>
                                <option value="ENTEADA">ENTEADA</option>
                                <option value="CONJUGE">CÔNJUGE</option>
                                <option value="COMPANHEIRO">COMPANHEIRO</option>
                                <option value="COMPANHEIRA">COMPANHEIRA</option>
                                <option value="PAI">PAI</option>
                                <option value="MAE">MÃE</option>
                                <option value="OUTRO">OUTRO</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="sexo">Sexo:</label>
                            <select id="sexo" name="sexo" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="MASCULINO">MASCULINO</option>
                                <option value="FEMININO">FEMININO</option>
                                <option value="OUTRO">OUTRO</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="possuiDeficiencia">Possui Deficiência:</label>
                            <select id="possuiDeficiencia" name="possuiDeficiencia" class="form-control" required>
                                <option value="0" selected>NÃO</option>
                                <option value="1">SIM</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="dependenteIrpf">Dependente IRPF:</label>
                            <select id="dependenteIrpf" name="dependenteIrpf" class="form-control" required>
                                <option value="0" selected>NÃO</option>
                                <option value="1">SIM</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="dependentePlanoSaude">Dependente Plano de Saúde:</label>
                            <select id="dependentePlanoSaude" name="dependentePlanoSaude" class="form-control" required>
                                <option value="0" selected>NÃO</option>
                                <option value="1">SIM</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="dependenteSalarioFamilia">Dependente Salário Família:</label>
                            <select id="dependenteSalarioFamilia" name="dependenteSalarioFamilia" class="form-control" required>
                                <option value="0" selected>NÃO</option>
                                <option value="1">SIM</option>
                            </select>
                        </div>
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
                            <input type="hidden" value="1" name="_submit_check">
                            <input type="hidden" value="GerenciarInscritoDependentes" name="c">
                            <input type="hidden" value="salvarInscritoDependentes" name="m">
                            <input type="hidden" name="codDependente" id="codDependente" value=""/>
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar">Salvar e Adicionar</button>
                            &nbsp;&nbsp;&nbsp;
                            <button class="btn btn-xs btn-primary" type="button" id="btnLimpar">Limpar e sair</button>
                        </div>
                    </div>
                </form>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
            <?php } ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <b>Lista de Dependentes Cadastrados:</b>
                <table class="table table-striped tablesorter" id="itens" border="0">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Nascimento</th>
                            <th>Parentesco</th>
                            <th>Sexo</th>
                            <th>Deficiência</th>
                            <th>IRPF</th>
                            <th>Plano Saúde</th>
                            <th>Salário Família</th>
                        </tr>
                    </thead>
                    <tbody class="inputs">
                        <?php
                        $objSRV->carregarTabelaInscritoDependentes($fws_, $debug);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
