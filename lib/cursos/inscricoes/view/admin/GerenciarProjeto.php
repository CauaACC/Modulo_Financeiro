<?php
global $fws_;
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarProjetoAC();
?>
<script type="text/javascript">
    $(document).ready(function () {

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('#btnSalvar').click(function () {
            salvarProjeto();
        });
        $('#btnSalvarComoNovo').click(function () {
            $('#update').val(0);
            $('#salvarProjeto').val('Adicionar');
            salvarProjeto();
        });
        $('#btnLimpar').click(function () {
            limparCampos();
        });
        $('.editar').click(function () {
            var id = $(this).parents("tr").attr("id");
            editarProjeto(id);
        });
        $('.excluir').click(function () {
            var id = $(this).parents("tr").attr("id");
            excluirProjeto(id);
        });
        $('#cpf').on('paste', function (e) {
            var pastedText = (e.originalEvent || e).clipboardData.getData('text');
            // Remove espaços, pontos e traços
            var cleanedText = pastedText.replace(/[\s.-]+/g, '');
            // Faz trim no texto
            var trimmedText = cleanedText.trim();
            // Coloca o texto tratado no campo de destino
            $(this).val(trimmedText);
            // Impede a colagem padrão
            e.preventDefault();
        });

        $("#dataInicioEvento, #dataFimEvento, #dataInicioInscricoes, #dataFimInscricoes").datetimepicker({
            showOn: 'both',
            buttonImage: '/lib/cursos/inscricoes/resources/image/calendario.jpg',
            buttonText: 'Calendário',
            buttonImageOnly: true
        });

        $('#dataInicioEvento, #dataFimEvento, #dataInicioInscricoes, #dataFimInscricoes').datetimepicker();
    });

    function carregarTabelaProjetos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random()
            , {'c': 'GerenciarProjeto', 'm': 'carregarTabelaProjetos', '_submit_check': '1'}
            , function (html) {
                $("table tbody.inputs tr").remove();
                $("table").trigger("update");
                // append the "ajax'd" data to the table body
                $("table tbody.inputs").append(html);
                // let the plugin know that we made a update
                $("table").trigger("update");
                $('.editar').click(function () {
                    var id = $(this).parents("tr").attr("id");
                    editarProjeto(id);
                });
                $('.excluir').click(function () {
                    var id = $(this).parents("tr").attr("id");
                    excluirProjeto(id);
                });
            }
        );
    }

    function editarProjeto(codProjeto) {
        if (codProjeto) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codProjeto': codProjeto,
                    'c': 'GerenciarProjeto',
                    'm': 'editarProjeto',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    $('#codEvento').val(data.codEvento);
                    $('#codProjeto').val(data.codProjeto);
                    $('#emailCoordenador').val(data.emailCoordenador);
                    $('#nomeCoordenador').val(data.nomeCoordenador);
                    $('#tituloEvento').val(data.tituloEvento);
                    $('#sigla').val(data.sigla);
                    $('#dataInicioEvento').val(data.dataInicioEvento);
                    $('#dataFimEvento').val(data.dataFimEvento);
                    $('#dataInicioInscricoes').val(data.dataInicioInscricoes);
                    $('#dataFimInscricoes').val(data.dataFimInscricoes);
                    $('#tipoProjeto').val(data.tipoProjeto);
                    $('#trabEnvioTrabalhos').val(data.trabEnvioTrabalhos);
                    $('#trabEscolherAtividades').val(data.trabEscolherAtividades);
                    $('#trabEmissaoCertificados').val(data.trabEmissaoCertificados);
                    $('#trabReceberPagamentos').val(data.trabReceberPagamentos);
                    $('#descricaoProjeto').val(data.descricaoProjeto);
                    $('#formasPagamento').val(data.formasPagamento);
                    $('#descricaoValoresInscricao').val(data.descricaoValoresInscricao);
                    $('#listaAtividades').val(data.listaAtividades);
                    $('#descricaoAtividades').val(data.descricaoAtividades);
                    $('#infoTrabEnvioArtigo').val(data.infoTrabEnvioArtigo);
                    $('#descricaoTrabalhosArtigos').val(data.descricaoTrabalhosArtigos);
                    $('#publicoAlvo').val(data.publicoAlvo);
                    $('#quantidadeVagas').val(data.quantidadeVagas);
                    $('#cargaHoraria').val(data.cargaHoraria);
                    $('#investimento').val(data.investimento);
                    $('#programacao').val(data.programacao);
                    $('#outrasInformacoes').val(data.outrasInformacoes);
                    $('#linkInscricoes').val(data.linkInscricoes);
                    $('#pais').val(data.pais);
                    $('#expectativa').val(data.expectativa);
                    $('#tipo').val(data.tipo);
                    $('#categoria').val(data.categoria);
                    $('#telefone').val(data.telefone);
                    $('#linkInstagram').val(data.linkInstagram);
                    $('#linkFacebook').val(data.linkFacebook);
                    $('#aceitaTermos').val(data.aceitaTermos);

                    $('#update').val(1);
                    $('#salvarProjeto').val('Salvar Alterações');
                    $('html,body').scrollTop(0);
                }
                , complete: function () {
                    stopLoading();
                }
            });
        } else {
            alert('Projeto não foi alterado!');
        }
    }

    function excluirProjeto(codProjeto) {
        if (confirm('Deseja realmente excluir Novo Projeto?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codProjeto': codProjeto,
                    'c': 'GerenciarProjeto',
                    'm': 'excluirProjeto',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaProjetos();
                    }
                    limparCampos();
                }
                , complete: function () {
                    stopLoading();
                }
            });
        }
    }

    function limparCampos() {

        $('#codEvento').val('');
        $('#codProjeto').val('');
        $('#emailCoordenador').val('');
        $('#nomeCoordenador').val('');
        $('#tituloEvento').val('');
        $('#sigla').val('');
        $('#dataInicioEvento').val('');
        $('#dataFimEvento').val('');
        $('#dataInicioInscricoes').val('');
        $('#dataFimInscricoes').val('');
        $('#tipoProjeto').val('');
        $('#trabEnvioTrabalhos').val('');
        $('#trabEscolherAtividades').val('');
        $('#trabEmissaoCertificados').val('');
        $('#trabReceberPagamentos').val('');
        $('#descricaoProjeto').val('');
        $('#formasPagamento').val('');
        $('#descricaoValoresInscricao').val();
        $('#listaAtividades').val('');
        $('#descricaoAtividades').val('');
        $('#infoTrabEnvioArtigo').val('');
        $('#descricaoTrabalhosArtigos').val('');
        $('#publicoAlvo').val('');
        $('#quantidadeVagas').val('');
        $('#cargaHoraria').val('');
        $('#investimento').val('');
        $('#programacao').val('');
        $('#outrasInformacoes').val('');
        $('#linkInscricoes').val('');
        $('#pais').val('');
        $('#expectativa').val('');
        $('#tipo').val('');
        $('#categoria').val('');
        $('#telefone').val('');
        $('#linkInstagram').val('');
        $('#linkFacebook').val('');
        $('#aceita_termos').val('');


        $('#update').val(0);
        $('#salvarProjeto').val('Adicionar');
    }
</script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link type="text/css" href="resources/js/jquery-ui-1.7.3.custom/css/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet"/>
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/ui.dialog.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="resources/js/tinymce-charcount.js"></script>
<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/i18n/ui.datepicker-pt-BR.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        tinymce.init({
            selector: "textarea.editor_padrao",
            plugins: "code textcolor link table",
            width: 500,
            height: 500,
            max_height: 1200,
            resize: "both",
            menubar: false,
            statusbar: true,
            block_formats: 'Header 1=h1;Header 2=h2;Header 3=h3',
            force_br_newlines: true,
            force_p_newlines: false,
            forced_root_block: '',
            apply_source_formatting: true,
            convert_newlines_to_brs: true,
            remove_linebreaks: false,
            toolbar: "code | undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | forecolor backcolor fontsizeselect | link | table ",
            setup: function (editor) { tinyMceCharCount.attach(editor, 5000); },
            init_instance_callback: function (editor) {
                $('.mce-tinymce').show('fast');
                $(editor.getContainer()).find(".mce-path").css("display", "none");
            },
            textcolor_map: [
                "FF0000", "Red",
                "008B45", "Green",
                "0000FF", "Blue"
            ],
            fontsize_formats: "10pt 12pt 14pt 18pt 24pt 36pt",
        });
    });

    function atualizarDDD() {
        var paisSelecionado = document.getElementById("pais").value;
        var ddd;

        switch (paisSelecionado) {
            case "África do Sul":
                ddd = "+27";
                break;
            case "Angola":
                ddd = "+244";
                break;
            case "Argentina":
                ddd = "+54";
                break;
            case "Bolívia":
                ddd = "+591";
                break;
            case "Brasil":
                ddd = "+55";
                break;
            case "Chile":
                ddd = "+56";
                break;
            case "Colômbia":
                ddd = "+57";
                break;
            case "Equador":
                ddd = "+593";
                break;
            case "Estados Unidos":
                ddd = "+1";
                break;
            case "México":
                ddd = "+52";
                break;
            case "Moçambique":
                ddd = "+258";
                break;
            case "Paraguai":
                ddd = "+595";
                break;
            case "Peru":
                ddd = "+51";
                break;
            case "Portugal":
                ddd = "+351";
                break;
            case "Uruguai":
                ddd = "+598";
                break;
        }
        document.getElementById("telefone").value = ddd;
    }

    function habilitarCampos() {
        habilitarTelefone();
    }

    function habilitarTelefone() {
        var paisSelect = document.getElementById("pais");
        var telefoneInput = document.getElementById("telefone");
        telefoneInput.disabled = !paisSelect.value;

        atualizarDDD();
    }

    function atualizarBarraProgresso() {
        var abasConcluidas = $('.nav-pills li.active').index() + 1;
        var totalAbas = $('.nav-pills li').length;
        var porcentagemConcluida = (abasConcluidas / totalAbas) * 100;

        $('.progress-bar').css('width', porcentagemConcluida + '%');
        $('.progress-bar').text(abasConcluidas + '/' + totalAbas);
    }

    function salvarProjetoPorAbas() {
        if (window.tinymce) tinymce.triggerSave();
        if (window.tinyMceCharCount && !tinyMceCharCount.podeSalvar()) return;
        let update = $('#update').val();
        $('#formProjeto').ajaxSubmit({
            target: '#mensagem',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function () {
                startLoading();
            },
            timeout: 4000,
            error: function (ajax) {
                showResponse(ajax);
            },
            success: function (data) {
                processJson(data);
                if (data.statusMessage) {
                    $('#codProjeto').val(data.codProjeto);
                    $('#update').val(1);
                }
            },
        });
    }

    function proximoAba() {
        salvarProjetoPorAbas()
        // if (validatorObj()) {
            // Apenas avance para a próxima aba se a validação for bem-sucedida
            var abaAtual = $('.tab-pane.active');
            var proximaAba = abaAtual.next('.tab-pane');

            abaAtual.removeClass('active show');
            proximaAba.addClass('active show');

            var proximoLink = $('a[href="#' + proximaAba.attr('id') + '"]');
            proximoLink.tab('show');

            atualizarBarraProgresso();
        // } else {
        //     // Adicione aqui qualquer lógica adicional em caso de falha na validação
        //     console.error("A validação falhou. Não é possível avançar para a próxima aba.");
        // }
    }

    function voltarAba() {
        salvarProjetoPorAbas();
        var abas = $('.nav-pills li');
        var abaAtual = abas.filter('.active');
        var abaAnterior = abaAtual.prev('li');

        if (abaAnterior.length === 0) {
            abaAnterior = abas.last();
        }

        abaAtual.removeClass('active');
        abaAnterior.addClass('active');

        $('.tab-pane').removeClass('active show');
        var idAbaAnterior = abaAnterior.find('a').attr('href');
        $(idAbaAnterior).addClass('active show');

        atualizarBarraProgresso();
    }

</script>

<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_outros.php");
?>
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Gerenciar Novo Projeto:</h4>
    </div>
    <div style="padding:15px 20px;">
            <div class="" style="margin-bottom: 15px;">
                <div class="progress" style="margin-top: 20px;">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        0%
                    </div>
                </div>
                <ul class="nav nav-pills">
                    <li class="active"><a href="#aba1" style="cursor: default;"> 1 </a></li>
                    <li><a href="#aba2" style="cursor: default;"> 2 </a></li>
                    <li><a href="#aba3" style="cursor: default;"> 3 </a></li>
                </ul>
            </div>
            <?php
            try {
                ?>
                <form id="formProjeto" name="formProjeto" action="<?php echo WB_URL_COMMAND ?>&dummy="
                      method="post">
                    <div class="form-group col-sm-12 tab-content" style="padding-left: 0px;">
                        <div class="tab-pane active" id="aba1">
                            <?php
                            require_once(dirname(__FILE__) . "/GerenciarProjetoCampo01.php");
                            ?>
                            <div>
                               <button class="btn btn-sm" type="button" onclick="proximoAba()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;"><i class="fa fa-arrow-right"></i> Pr&oacute;ximo</button>
                            </div>
                        </div>

                        <div class="tab-pane" id="aba2" style="display: none">
                            <?php
                            require_once(dirname(__FILE__) . "/GerenciarProjetoCampo02.php");
                            ?>
                            <div style="margin-top:10px;">
                                <button type="button" class="btn btn-sm" onclick="voltarAba()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#f0f0f0; color:#555; border:1px solid #ddd; margin-right:6px;"><i class="fa fa-arrow-left"></i> Voltar</button>
                                <button type="button" class="btn btn-sm" onclick="proximoAba()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;"><i class="fa fa-arrow-right"></i> Pr&oacute;ximo</button>
                            </div>
                        </div>

                        <div class="tab-pane" id="aba3" style="display: none">
                            <?php
                            require_once(dirname(__FILE__) . "/GerenciarProjetoCampo03.php");
                            ?>
                            <div style="margin-top:10px;">
                                <button type="button" class="btn btn-sm" onclick="voltarAba()" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#f0f0f0; color:#555; border:1px solid #ddd; margin-right:6px;"><i class="fa fa-arrow-left"></i> Voltar</button>
                                <button type="button" class="btn btn-sm" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;"><i class="fa fa-check"></i> Finalizar Formul&aacute;rio</button>
                            </div>
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
                            <input type="hidden" value="GerenciarProjeto" name="c">
                            <input type="hidden" value="salvarProjeto" name="m">
                            <input type="hidden" name="update" id="update" value="0"/>
                            <input type="hidden" name="codProjeto" id="codProjeto" value=""/>
                        </div>
                    </div>
                </form>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
            <?php } ?>
        </div>
    </div>
</div>