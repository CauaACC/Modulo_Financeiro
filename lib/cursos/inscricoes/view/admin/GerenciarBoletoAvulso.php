<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarBoletoAvulsoAC();

// Verificar se há um inscrito selecionado
$_codInscritoBoleto = !empty($fws_['codInscrito']) ? $fws_['codInscrito'] : (!empty($_GET['codInscrito']) ? $_GET['codInscrito'] : '');
if (empty($_codInscritoBoleto)) {
    // Sem inscrito: mostrar listagem geral de boletos do evento
    require_once(dirname(__FILE__) . "/../../include/config.php");
    require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
    require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
    require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_financeiro.php");
    ?>
    <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
            <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Boletos Avulsos</h4>
        </div>
        <div style="padding:12px 18px;">
            <div class="row" style="margin-bottom:15px;">
                <div class="col-sm-4">
                    <input type="text" id="filtroBoletoNome" class="form-control input-sm" placeholder="Filtrar por nome ou CPF...">
                </div>
            </div>
            <h5 style="font-size:14px; font-weight:600; color:#555; margin:0;">Boletos do Evento</h5>
        </div>
        <div>
            <table class="tbl-fin" id="tblBoletosEvento">
                <thead>
                <tr>
                    <th style="width:60px;">C&oacute;d.</th>
                    <th data-col="1" style="cursor:pointer;">Sacado <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="2" style="cursor:pointer;">Descri&ccedil;&atilde;o <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="3" style="cursor:pointer;">Vencimento <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="4" style="cursor:pointer;">Valor <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="5" style="cursor:pointer;">Status <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th style="width:50px;">Ver</th>
                </tr>
                </thead>
                <tbody>
                    <?php $objSRV->carregarTabelaBoletosEvento($fws_, $debug); ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        $('#filtroBoletoNome').on('keyup', function() {
            var filtro = $(this).val().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            $('#tblBoletosEvento tbody tr').each(function() {
                var texto = $(this).text().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                $(this).toggle(texto.indexOf(filtro) > -1);
            });
        });
    });
    </script>
    <?php
    return;
}
$objInscrito = $objSRV->carregarInscrito($fws_, $debug);
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#btnSalvar').click(function() {
            salvarBoletoAvulso();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodBoleto').val(0);
            $('#salvarBoletoAvulso').val('Adicionar');
            salvarBoletoAvulso();
        });
        $('#btnFechar').click(function() {
            limparCampos();
            $('#formBoletoAvulso').addClass('hide');
            $('html,body').scrollTop(0);
        });
        $('#btnAdicionarNovo').click(function() {
            $('#formBoletoAvulso').removeClass('hide');
            limparCampos();
            $('html,body').scrollTop(0);
        });
        // Delegated events para a tabela
        $('#tblBoletos').on('click', '.editar', function() {
            var id = $(this).closest("tr").attr("id");
            editarBoletoAvulso(id);
        });
        $('#tblBoletos').on('click', '.excluir', function() {
            var id = $(this).closest("tr").attr("id");
            _boletoAcaoPendente = {acao: 'excluir', id: id};
            $('#modalConfirmarBoleto .modal-header').removeClass('modalCabecalhoExclusao').addClass('modalCabecalhoExclusao');
            $('#modalConfirmarBoleto .modal-title').html('<i class="fa fa-exclamation-triangle"></i> Confirmar Exclus\u00e3o');
            $('#modalConfirmarBoleto .modal-body p').html('Deseja realmente excluir o Boleto C\u00f3d.: <strong>' + id + '</strong>?');
            $('#btnConfirmarBoletoAcao').removeClass('btn-success modalBtnSalvar').addClass('btn-danger modalBtnExcluir').html('<i class="fa fa-trash"></i> Excluir');
            $('#modalConfirmarBoleto').modal('show');
        });
        $('#tblBoletos').on('click', '.desativar', function() {
            var id = $(this).closest("tr").attr("id");
            _boletoAcaoPendente = {acao: 'desativar', id: id};
            $('#modalConfirmarBoleto .modal-header').removeClass('modalCabecalhoExclusao').addClass('modalCabecalho');
            $('#modalConfirmarBoleto .modal-title').html('<i class="fa fa-ban"></i> Confirmar Desativa\u00e7\u00e3o');
            $('#modalConfirmarBoleto .modal-body p').html('Deseja realmente desativar o Boleto C\u00f3d.: <strong>' + id + '</strong>?');
            $('#btnConfirmarBoletoAcao').removeClass('btn-danger modalBtnExcluir').addClass('btn-success modalBtnSalvar').html('<i class="fa fa-check"></i> Confirmar');
            $('#modalConfirmarBoleto').modal('show');
        });
        $('#btnConfirmarBoletoAcao').click(function() {
            $('#modalConfirmarBoleto').modal('hide');
            if (_boletoAcaoPendente.acao === 'excluir') excluirBoletoAvulso(_boletoAcaoPendente.id);
            else if (_boletoAcaoPendente.acao === 'desativar') desativarBoletoAvulso(_boletoAcaoPendente.id);
            else if (_boletoAcaoPendente.acao === 'enviarEmail') enviarEmailAvisoPadrao(_boletoAcaoPendente.id);
        });
        $('.enviarEmailAvisoPadrao').click(function() {
            var id = $(this).attr("data-cod_inscrito");
            _boletoAcaoPendente = {acao: 'enviarEmail', id: id};
            $('#modalConfirmarBoleto .modal-header').removeClass('modalCabecalhoExclusao').addClass('modalCabecalho');
            $('#modalConfirmarBoleto .modal-title').html('<i class="fa fa-envelope"></i> Enviar E-mail');
            $('#modalConfirmarBoleto .modal-body p').html('Deseja enviar e-mail de aviso de boleto com texto padr\u00e3o?');
            $('#btnConfirmarBoletoAcao').removeClass('btn-danger modalBtnExcluir').addClass('btn-success modalBtnSalvar').html('<i class="fa fa-check"></i> Enviar');
            $('#modalConfirmarBoleto').modal('show');
        });
        $("#estado").change(function () {
            carregarCidades(this.value);
        });
        $('#dataDesconto').change(function(){
            setInstrucao1();
        });
        $('#valorBoleto').change(function(){
            setInstrucao1();
        });
        $('#valorDesconto').change(function(){
            setInstrucao1();
        });
        
    });
    function setInstrucao1(){
//        var dataDesconto = $('#dataDesconto').datepicker({ dateFormat: 'dd/mm/yyyy' }).val()
        var dataDesconto = formatarData($('#dataDesconto').val());
        var valorComDesconto = eval($('#valorBoleto').val()-$('#valorDesconto').val());
        var instrucoes1 = '- Para pagamento até dia '+dataDesconto+', receber R$ '+valorComDesconto+',00';
        if($('#valorDesconto').val()!='' || $('#dataDesconto').val()!=''){
            if (confirm('Confirma a alteração da instrução 1 para:'+instrucoes1)){ 
                $('#instrucoes1').val(instrucoes1);
            }
        }
    }
    function formatarData(data) {
        var partes = data.split("-");
        return partes[2] + "/" + partes[1] + "/" + partes[0];
    }
    function carregarCidades(cod, selecionado)
    {
       $.ajax({
           type: "POST",
           url: '<?php echo  WB_URL_COMMAND ?>',
           data: ({'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1'}),
           dataType: "html",
           async: false,
           cache: false
           , success: function (data) {
               $("#cidade").html(data);
           }
           , error: function () {
               alert("Nao foi possivel obter informações:\n" + req.statusText);
           }
       });
    }
    //funçães globais
    var _boletoAcaoPendente = {};
    function carregarTabelaBoletosAvulsos() {
        startLoading();
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
        , {'c': 'GerenciarBoletoAvulso', 'm': 'carregarTabelaBoletosAvulsos', 'codInscrito':$('#codInscrito').val(), '_submit_check': '1'}
        , function(html) {
            $("#tblBoletos tbody").html(html);
        }
        );
        stopLoading();
    }
    function salvarBoletoAvulso() {
        $('#formBoletoAvulso').ajaxSubmit({
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
                    carregarTabelaBoletosAvulsos();
                    limparCampos();
                    $('#formBoletoAvulso').addClass('hide');
                    $('html,body').scrollTop(0);
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarBoletoAvulso(codBoleto) {
        if (codBoleto) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codBoleto': codBoleto, 'codInscrito': $('#codInscrito').val(), 'c': 'GerenciarBoletoAvulso', 'm': 'editarBoletoAvulso', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        $('#codBoleto').val(data.codBoleto);
                        $('#codInscrito').val(data.codInscrito);
                        $('#sacado').val(data.sacado);
                        $('#cpfCnpj').val(data.cpfCnpj);
                        $('#dataVencimento').val(data.dataVencimento);
                        $('#valorBoleto').val(data.valorBoleto);
                        $('#dataDesconto').val(data.dataDesconto);
                        $('#valorDesconto').val(data.valorDesconto);
                        $('#demonstrativo1').val(data.demonstrativo1);
                        $('#demonstrativo2').val(data.demonstrativo2);
                        $('#demonstrativo3').val(data.demonstrativo3);
                        $('#instrucoes1').val(data.instrucoes1);
                        $('#instrucoes2').val(data.instrucoes2);
                        $('#instrucoes3').val(data.instrucoes3);
                        $('#foneContato').val(data.foneContato);
                        $('#novoCodBoleto').val(1);
                        $('#formBoletoAvulso').removeClass('hide');
                        $('#salvarBoletoAvulso').val('Salvar Alterações');
                        $('#dataVencimento').focus();
                        $('html,body').scrollTop(0);
                    }else{
                        alert('Problema ao carregar dados');
                    }
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do Boleto Avulso não informado!');
        }
    }
    function excluirBoletoAvulso(codBoleto) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
            data: ({'codBoleto': codBoleto,'codInscrito': $('#codInscrito').val(),  'c': 'GerenciarBoletoAvulso', 'm': 'excluirBoletoAvulso', '_submit_check': '1'}),
            dataType: "json",
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaBoletosAvulsos();
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }
    function desativarBoletoAvulso(codBoleto) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
            data: ({'codBoleto': codBoleto,'codInscrito': $('#codInscrito').val(),  'c': 'GerenciarBoletoAvulso', 'm': 'desativarBoletoAvulso', '_submit_check': '1'}),
            dataType: "json",
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaBoletosAvulsos();
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }
    function enviarEmailAvisoPadrao(codInscrito) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
            data: ({'codInscrito': codInscrito,  'c': 'GerenciarBoletoAvulso', 'm': 'enviarEmailAvisoPadrao', '_submit_check': '1'}),
            dataType: "json",
            beforeSend: function() { startLoading(); },
            success: function(data) { processJson(data); },
            complete: function() { stopLoading(); }
        });
    }
    function limparCampos() {
        //Limpando campos
        $('#codBoleto').val('');
//        $('#sacado').val('');
//        $('#cpfCnpj').val('');
        $('#dataVencimento').val('');
        $('#valorBoleto').val('');
        $('#dataDesconto').val('');
        $('#valorDesconto').val('');
        $('#demonstrativo2').val('');
        $('#demonstrativo3').val('');
        $('#novoCodBoleto').val(0);
        $('#salvarBoletoAvulso').val('Adicionar');

    }
</script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");
?>
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">
                Gerenciar Boletos da Inscri&ccedil;&atilde;o: <?php echo $objInscrito->getCodInscrito() ?> -  <?php echo $objInscrito->getNomeInscrito() ?>

                <a title='Logar Como' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo  $objInscrito->getCpf() ?>&cod_inscrito=<?php echo  $objInscrito->getCodInscrito() ?>&cod_evento=<?php echo  COD_EVENTO ?>'>
                <img src='<?php echo  WB_URL_IMG ?>/icone_preview.gif' border='0'></a>

                <a title="Editar" target="_blank" href='index.php?content=view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito=<?php echo  $objInscrito->getCodInscrito() ?>' title="Abrir Inscrição">
                <img src="<?php echo  WB_URL_IMG ?>/fig-alterar.gif" border="0"></a>
                <br>
                <span style="font-size:12px;">
                    E-mail:
                    <a href="mailto:<?php echo  mb_strtolower($objInscrito->getEMail()) ?>&subject=[<?php echo ABREV_EVENTO?>]"><?php echo  mb_strtolower($objInscrito->getEMail()) ?></a>
                    &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                    <a target='_blank' href='https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&su=[<?php echo ABREV_EVENTO?>]&body=Ola <?php echo mb_strtoupper($objInscrito->getNomeInscrito())?>%0A%0A%0A%0Aatt. Comissão Organizadora%0A<?php echo ABREV_EVENTO?> - <?php echo NOME_EVENTO?>%0ADúvidas: <?php echo EMAIL_CONTATO;?>&to=<?php echo  mb_strtolower($objInscrito->getEMail()) ?>&cc=<?php echo EMAIL_CONTATO?>'>Enviar email pelo Gmail</a>
                    &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                    fone: <?php echo  $objInscrito->getFoneContato() ?>
                    &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                    <img class="enviarEmailAvisoPadrao"  data-cod_inscrito="<?php echo $objInscrito->getCodInscrito(); ?>"
                    src="<?php echo  WB_URL_IMG ?>/email-1.jpg" alt="Enviar e-mail aviso padrão" title="Enviar e-mail aviso padrão" border="0" width="22px">
                    Enviar Aviso Padrão
                </span>
            </h4>
            <div id="carregando" class="hidden">
                <div class="spinner"></div>
                <div class="imagem">
                    <div class="frase">Carregando...</div>
<!--                    <img src="<?php echo  WB_URL_IMG ?>/loading-9.gif" border="0">-->
            </div>
            </div>
            <div id="mensagem"></div>
            <div id="sucesso" class="hidden"></div>
            <div id="erro"    class="hidden"></div>
    </div>
    <div style="padding:15px 20px;">
            <?php
            try {
                ?>
            <form  id="formBoletoAvulso" name="formBoletoAvulso" class="hide" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <div class="row">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="sacado">Forçar novo registro:</label>
                            <input type="radio" name="registrarNovamente" value="1" /> Sim
                            <input type="radio" name="registrarNovamente" value="" /> Não
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="sacado">Nome do Sacado:</label>
                            <input type="text" id="sacado" name="sacado" value="<?php echo $objInscrito->getNomeInscrito()?>"
                                   class="form-control" placeholder="Sacado">
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="cpf_cnpj">Cpf/Cnpj:</label>
                            <input type="text" id="cpfCnpj" name="cpfCnpj" value="<?php echo  $objInscrito->getCpf() ?>"
                                class="form-control" placeholder="Cpf/Cnpj" required autofocus
                                 SIZE='60' maxlength='18'>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="foneContato">Fone Contato:</label>
                            <input type="tel" id="foneContato" name="foneContato" value="<?php echo $objInscrito->getFoneContato() ?>"
                                   class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="dataVencimento">Data de vencimento:</label>
                            <input type="date" id="dataVencimento" name="dataVencimento" value="" style="line-height: 15px;"
                                   class="form-control" placeholder="Data de vencimento" >
                        </div>
                        <div class="form-group col-sm-6" style="padding-left: 0px;">
                            <label for="valorBoleto">Valor: (XXXX.00)</label>
                            <input type="text" id="valorBoleto" name="valorBoleto" value=""
                                   class="form-control" placeholder="Não acrescente centavos" >
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="dataDesconto">Aplicar desconto até:</label>
                            <input type="date" id="dataDesconto" name="dataDesconto" value="" style="line-height: 15px;"
                                   class="form-control" placeholder="Aplicar desconto até" >
                        </div>
                        <div class="form-group col-sm-6" style="padding-left: 0px;">
                            <label for="valorDesconto">Valor Desconto: (XXXX.00)</label>
                            <input type="text" id="valorDesconto" name="valorDesconto" value=""
                                   class="form-control" placeholder="Não acrescente centavos" >
                        </div>
                        
                        <div class="form-group col-sm-6" style="padding-left: 0px;">
                            <label for="endereco">Rua:</label>
                            <input type="text" id="endereco" name="endereco" value="<?php echo $objInscrito->getEndereco() ?>"
                                   class="form-control" placeholder="" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                                    SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="numero">Número:</label>
                            <input type="text" id="numero" name="numero" value="<?php echo $objInscrito->getNumero() ?>"
                                   class="form-control" placeholder="Número" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                                    SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="complemento">Complemento:</label>
                            <input type="text" id="complemento" name="complemento" value="<?php echo $objInscrito->getComplemento() ?>" 
                                   class="form-control" 
                                    SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>


                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="estado">Estado:</label>
                            <select class='form-control' name='estado' id='estado' placeholder="Estado" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus>
                                <option class='form_campos' value='-1' selected>Selecione um Estado</option>
                                <?php
                                $fws_['estado'] = @$objInscrito->getEstado();
                                @CarregarOptionsAC::estados($fws_, $debug);
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-5" style="padding-left: 0px;">
                            <label for="cidade">Cidade:<?php echo $fws_['cidade']?> </label>
            <!--                    <input type="text" id="cidade" name="cidade" value="<?php echo $objInscrito->getcidade() ?>"
                                   class="form-control" placeholder="Cidade" required autofocus
                                    SIZE='60' maxlength='255' onBlur='trim(this);'>-->

                            <select class='form-control' name='cidade' id='cidade' placeholder="Cidade" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus>
                                <option class='form_campos' value='-1' selected>Selecione um Cidade</option>
                                <?php
                                $fws_['cidade'] = @$objInscrito->getCidade();
                                CarregarOptionsAC::cidades($fws_, $debug);
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="cep">Cep:</label>
                            <input type="text" id="cep" name="cep" value="<?php echo $objInscrito->getCep() ?>" 
                                   class="form-control" placeholder="Cep" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                                    SIZE='60' maxlength='9' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                        </div>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="bairro">Bairro:</label>
                            <input type="text" id="bairro" name="bairro" value="<?php echo $objInscrito->getBairro() ?>"
                                   class="form-control" placeholder="Bairro" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                                    SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>

                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="instrucoes1">Instrução 1:</label>
                            <input type="text" id="instrucoes1" name="instrucoes1" value=""
                                   class="form-control" placeholder="Descrição linha 1" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="instrucoes2">Instrução 2:</label>
                            <input type="text" id="instrucoes2" name="instrucoes2" value="- Não receber após o vencimento"
                                   class="form-control" placeholder="Descrição linha 2" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="instrucoes3">Instrução 3:</label>
                            <input type="text" id="instrucoes3" name="instrucoes3" value="- Emitido pelo sistema de inscrição"
                                   class="form-control" placeholder="Descrição linha 3" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="demonstrativo1">Descrição Linha 1:</label>
                            <input type="text" id="demonstrativo1" name="demonstrativo1" value="<?php echo ABREV_EVENTO?> - inscrição número: <?php echo $objInscrito->getCodInscrito()?> | Categoria: <?php echo $objInscrito->getNomePerfil()?>"
                                   class="form-control" placeholder="Descrição linha 1" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="demonstrativo2">Descrição Linha 2:</label>
                            <input type="text" id="demonstrativo2" name="demonstrativo2" value=""
                                   class="form-control" placeholder="Descrição linha 2" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="demonstrativo3">Descrição Linha 3:</label>
                            <input type="text" id="demonstrativo3" name="demonstrativo3" value=""
                                   class="form-control" placeholder="Descrição linha 3" required autofocus>
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
                            <input type="hidden" value="GerenciarBoletoAvulso" name="c">
                            <input type="hidden" value="salvarBoletoAvulso"    name="m">

                            <input type="hidden" name="novoCodBoleto" id="novoCodBoleto" value="0"/>
                            <input type="hidden" name="codBoleto"     id="codBoleto"     value=""/>                
                            <input type="hidden" name="codInscrito"   id="codInscrito"   value="<?php echo $objInscrito->getCodInscrito()?>"/>                
                            <button class="btn btn-sm" type="button" id="btnSalvar" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;"><i class="fa fa-save"></i> Salvar</button>
                            <button class="btn btn-sm" type="button" id="btnSalvarComoNovo" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;"><i class="fa fa-copy"></i> Salvar como novo</button>
                            <button class="btn btn-sm" type="button" id="btnFechar" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#f0f0f0; color:#555; border:1px solid #ddd;"><i class="fa fa-times"></i> Fechar</button>
                        </div>
                    </div>
                </form>
            <button class="btn btn-sm" type="button" id="btnAdicionarNovo" style="border-radius:5px; padding:5px 14px; font-size:12px; background:#2d6a4f; color:#fff; border:none;"><i class="fa fa-plus"></i> Novo Boleto</button>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
            <?php } ?>
    </div>
    <div style="padding:12px 18px;">
        <h5 style="font-size:14px; font-weight:600; color:#555; margin:0;">Boletos do Inscrito</h5>
    </div>
    <div>
        <table class="tbl-fin" id="tblBoletos">
            <thead>
            <tr>
                <th style="width:120px;">A&ccedil;&otilde;es</th>
                <th data-col="1" style="cursor:pointer;">Descri&ccedil;&atilde;o <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="2" style="cursor:pointer;">Emiss&atilde;o <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="3" style="cursor:pointer;">Vencimento <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="4" style="cursor:pointer;">Vlr. Doc. <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="5" style="cursor:pointer;">Dat. Desc. <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="6" style="cursor:pointer;">Vlr. Desc. <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="7" style="cursor:pointer;">Vlr. Pago <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="8" style="cursor:pointer;">Pagamento <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="9" style="cursor:pointer;">Registro <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="10" style="cursor:pointer;">Tipo <span class="sort-arrow">&uarr;&darr;</span></th>
            </tr>
            </thead>
            <tbody>
                <?php
                $fws_['codInscrito'] = $objInscrito->getCodInscrito();
                $objSRV->carregarTabelaBoletosAvulsos($fws_, $debug);
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Confirmar Acao Boleto -->
<div class="modal fade" id="modalConfirmarBoleto" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-question-circle"></i> Confirmar</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn modalBtnSalvar" id="btnConfirmarBoletoAcao"><i class="fa fa-check"></i> Confirmar</button>
            </div>
        </div>
    </div>
</div>
